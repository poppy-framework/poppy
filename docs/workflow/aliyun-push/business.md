# 业务逻辑

## 推送通道分发（设备 × 类型）

### 业务规则

- **设备二分**：iOS（`PushMessage::DEVICE_TYPE_IOS = 'IOS'`）与 Android（`DEVICE_TYPE_ANDROID = 'ANDROID'`）走两套独立链路；iOS 推送需 `iOSApnsEnv`（`is_production()` 为真时 `PRODUCT`，否则 `DEV`），Android 推送需 `androidNotificationChannel`（Android 8.0 之后强制要求）
- **推送类型二选一**：通知 `NOTICE`（带标题/内容/扩展参数，会出现在系统通知栏）vs 消息 `MESSAGE`（静默下发，`body` 为 JSON Map，`extParameters` 失效）；`device_type` 字符串形如 `android|notice;ios|message`，按 `;` 拆出多组 `device|pushType`
- **推送开关由后台配置决定**：iOS 是否下发取决于 `config('poppy.aliyun-push.ios_is_open')`；Android 同理 `android_is_open`。即便代码逻辑成立，开关关闭时也不会实际调用 SDK
- **目标（target）三选一**：`DEVICE`（按 deviceId 列表，自动按 1000 个/批切分）/ `TAG`（按阿里云标签，依赖 `BindTag` 注册过的 deviceId）/ `ALL`（全员广播，`targetValue` 固定为 `ALL`）。代码里还预留 `ACCOUNT` / `ALIAS` 两个常量但 `AliPush::toBatches()` 中未实现对应分支，传值时会落到 `switch` 的 default → 返回空数组
- **target 兼容字段**：`broadcast_type` 兼容旧字段名（→ `target`），`content` 兼容旧字段名（→ `body`），由 `AliPush::compat()` 完成转换
- **扩展参数（extra）**：业务方在 Notification `toAliPush()` 里传 `extra` 数组，最终以 JSON 写入 `extParameters`（Android 通知）或 `iOSExtParameters`（iOS 通知）；iOS 模式下若 `extParameters` 为空则强制写 `'{}'`

### 路由/分发规则

| 条件                                  | 处理路径                                                |
|-------------------------------------|-----------------------------------------------------|
| `device_type` 解析后 iOS 段非空 + `ios_is_open=true` | 按 `cutNum=1000`/批切分，每批一个 `PushMessage` → `SenderJob` |
| `device_type` 解析后 Android 段非空 + `android_is_open=true` | 同上，按 `ANDROID` 设备类型分发                              |
| `target=DEVICE` 但 `ids` 为空             | `PushException('用户设备号不能为空')`                        |
| `target=TAG` 但 `regTags` 为空              | `PushException('用户标签不能为空')`                          |
| `pushType` 既非 `MESSAGE` 也非 `NOTICE`       | `PushException('推送类型仅支持 MESSAGE/NOTICE')`            |
| Android + `NOTICE` 但 `androidChannel` 未设置 | `PushException('Android 应用通知频道未设置')`（来自 `PushSender::checkEnv()`） |
| `iOSAppKey` 为空但调用 iOS                 | `PushException('IOS 应用KEY 未设置')`                    |

### 状态机

本模块没有持久化状态流转，推送是一次性下发动作。可观察的状态节点：

```
PENDING (AliPush::send 入口)
   ↓ 校验 params 非空 + 解析 device_type + 检查开关
   ↓ toBatches() 按 target 拆出 N 个 PushMessage
   ↓ dispatch(SenderJob) —— 入队
QUEUED (SenderJob 等待 worker)
   ↓ handle() → PushSender::send()
   ↓ checkEnv() 校验密钥 / 通道
   ↓ 组装 PushRequest → SDK Push->push()
SUCCESS (response->body->toMap() 写入 $this->result)
   或
FAILED (PushException / SDK 异常)
   ↓ Laravel Queue 默认重试（依赖队列驱动配置）
```

> 队列的 `tries` / `backoff` / `maxExceptions` 未在 `SenderJob` 中显式声明，沿用 Laravel 默认（`tries=1`），详见 [contracts.md](contracts.md) 的「待确认」。

### 关键算法

- **批量切分**：`AliPush::$cutNum = 1000`，对 `DEVICE` 目标的设备号数组按 1000 个一组切分，每组装成独立 `PushMessage`（`targetValue` = 逗号拼接），最终每组 dispatch 一个 `SenderJob`——这是为了避免单次 `PushRequest` 的 deviceId 列表过长触发阿里云 API 限制
- **`is_production()` 派生 iOS ApnsEnv**：`PushSender::send()` 中通过全局函数 `is_production()`（来自 `poppy/framework`）动态选 `PRODUCT` / `DEV`，避免手工改代码
- **Android 厂商通道分级**：`androidNotificationHuaweiChannel` 固定 `NORMAL`（服务与通讯类）、`androidNotificationHonorChannel` 固定 `NORMAL`、`androidNotificationVivoChannel` 固定 `1`（系统类消息），不做下游配置开关——因为这些是华为/荣耀/vivo ROM 厂商的消息分类，**默认走高优先级通道**以提高到达率；营销类消息若要使用，应改用 `LOW` / `0`
- **Android Activity 自定义打开页**：若 `config.android_activity` 非空，自动附加 `androidOpenType=ACTIVITY`、`androidPopupActivity`、`storeOffline=true`（离线存储），用于点击通知时跳转到指定 Activity

## 通知 vs 消息（payload 结构）

### 业务规则

- **NOTICE（通知）**：`title` + `body` 必填，`extra` 序列化为 JSON 写入 `extParameters`；Android 额外写入 `androidExtParameters`，iOS 额外写入 `iOSExtParameters`
- **MESSAGE（消息）**：仅 `title` 必填，`body` 字段被强制赋值为 `extras`（JSON 字符串），`extParameters` 不生效——这是阿里云 Push 对透传消息的特殊处理（消息体本身即为 JSON 数据）
- **兼容性**：旧字段名 `broadcast_type` / `content` 仍被识别，由 `AliPush::compat()` 在 `send()` 入口转换

### 路由/分发规则

| 字段来源         | 映射目标                                     |
|--------------|------------------------------------------|
| `title`      | `PushRequest.title`                       |
| `body`       | NOTICE → `PushRequest.body`；MESSAGE → 被 extras 覆盖 |
| `extra`      | NOTICE → JSON 序列化为 `extParameters`（Android/iOS 双写） |
| `registration_ids.ios` | iOS 设备号数组，仅当 `ios_is_open=true` 时下发 |
| `registration_ids.android` | Android 设备号数组，仅当 `android_is_open=true` 时下发 |
| `registration_tags` | `target=TAG` 时的 `targetValue`         |

> iOS 在 `body` 为空时仍允许下发（通知必须 body 非空，message 模式下 body 被强制覆盖），但 `extras` 为空时强制写 `'{}'`，避免 SDK 报错。

## 后台配置（py-aliyun-push::push）

### 业务规则

- **配置组**：所有设置归入 `py-aliyun-push::push` 配置组（见 `FormSettingAliyunPush::$group`），由 `poppy/system` 的设置仓库统一持久化
- **必填与可选**：`access_key` / `access_secret` / `android_app_key` / `ios_app_key` 均为可空（`Rule::nullable()`），`android_channel` / `android_activity` / 开关位无 nullable 约束——但若 Android 通知实际下发但未配置 `android_channel`，`PushSender::checkEnv()` 会抛 `PushException` 阻断
- **开关位语义**：`ios_is_open` / `android_is_open` 仅控制**代码是否会真正调用 SDK**；关闭时 `AliPush::send()` 会跳过对应设备类型且**不抛异常**——业务方需要自行保证关闭期间不依赖推送结果

### 路由/分发规则

| 设置项                | 取值                 | 影响                                                                  |
|--------------------|--------------------|---------------------------------------------------------------------|
| `access_key`       | sys_setting 取       | SDK 鉴权 AK                                                           |
| `access_secret`    | sys_setting 取       | SDK 鉴权 SK                                                           |
| `ios_is_open`      | true/false          | iOS 设备类型是否进入 `dispatch` 分支                                            |
| `ios_app_key`      | sys_setting 取       | iOS 推送请求 `appKey` 字段                                                |
| `android_is_open`  | true/false          | Android 同上                                                          |
| `android_app_key`  | sys_setting 取       | Android 推送请求 `appKey`                                                |
| `android_channel`  | sys_setting 取       | Android `androidNotificationChannel`（必须，否则抛 `PushException`）          |
| `android_activity` | sys_setting 取（可空）   | 非空时启用 `androidOpenType=ACTIVITY` + 离线存储                              |

> 配置入口在 `PyAliyunPushDef::fillConfig()`，**每次** `AliPushChannel::send()` 入口处被调用，**实时**从 `sys_setting('py-aliyun-push::push.*')` 同步到 `poppy.aliyun-push.*` config，避免后台改设置后缓存失效。

## 待确认

- **`SenderJob` 重试策略未显式声明**：未在 `Jobs/SenderJob.php` 中看到 `$tries` / `$backoff` / `$maxExceptions` 属性，推断沿用 Laravel 默认值，但生产环境**确切的重试次数与退避策略需到运行时队列驱动配置确认**（发现位置：`Jobs/SenderJob.php` 全文）
- **`target=ACCOUNT` / `target=ALIAS` 未实现**：`PushMessage` 常量已定义（`TARGET_ACCOUNT` / `TARGET_ALIAS`），但 `AliPush::toBatches()` 的 `switch` 仅处理 `DEVICE` / `TAG` / `ALL`——传这两个值会得到空数组、不会抛错（发现位置：`Classes/AliPush.php::toBatches()` 的 switch 语句）
- **`SenderJob` 是否真的入队**：`AliPush::send()` 末尾 `dispatch(new SenderJob(...))` 默认会进入 `default` 队列连接，**消费方需要确认运行环境是否配置了 `queue.default` 与 `queue.connections`**（发现位置：`Classes/AliPush.php` line 113）
- **跨模块调用方的实际位置**：本仓库内未发现 `poppy/*/src/` 中调用 `SenderJob` 或 `AliPushChannel` 的代码，**业务方是否在外部模块中调用需要后续人工确认**（发现位置：`grep -rn "AliyunPush\\\\Jobs\\\\SenderJob" poppy/*/src/` 仅返回本模块自身）
- **`AliPush::send()` 返回 `false` 的语义**：`send()` 返回 `false` 时（参数空、设备类型未指定），`AliPushChannel::send()` 会**直接抛 `ApplicationException`** 而**不是**返回错误码——若 Notification 发送方依赖异常捕获，需要明确抛点（发现位置：`Channels/AliPushChannel.php`）
- **`BindTag::bindDevice()` 错误处理**：`bindDevice()` 成功时返回 `true`，但**不捕获 SDK 异常**——若阿里云接口失败，异常会直接冒泡到调用方（发现位置：`Classes/BindTag.php::bindDevice()`）