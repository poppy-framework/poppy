# 对外契约

> **本模块无独立路由文件**：没有 `api_v1.php` / `backend.php` / `web.php`。
> 业务方通过实现 `Contracts\AliPushChannel` 接口生成 Laravel Notification，再走 `poppy/mgr-page` 通用后台设置入口完成密钥配置。

## API 路由

| HTTP方法 | URI | 请求类/控制器 | 中间件 | 说明 |
|--------|-----|----------|----|----|
| —      | —   | —        | —  | **无对外 API**；业务方自行在自家模块的 Notification 中引用 `Poppy\AliyunPush\Channels\AliPushChannel` |

## 管理后台路由（无独立文件）

| HTTP方法 | URI | 请求类/控制器 | 说明 |
|--------|-----|----------|----|
| —      | —   | —        | **无独立 backend.php**；配置入口由 `Hooks\MgrPage\SettingsAliyunPush` 挂入 `poppy.mgr-page.settings` Hook，在 `poppy/mgr-page` 的设置中心统一渲染（设置 key：`poppy.aliyun-push`） |

### 设置面板（`Hooks\MgrPage\SettingsAliyunPush`）

| 设置 key | 控件类型 | 必填 | 帮助文本 | 来源/约束 |
|--------|-------|---|----|------|
| `access_key` | text | 否 | `AccessKey(阿里云)` | `Rule::nullable()` |
| `access_secret` | text | 否 | `AccessSecret(阿里云)` | `Rule::nullable()` |
| `android_is_open` | switch | — | `是否开启 Android 推送` | 默认 `false` |
| `android_app_key` | text | 否 | `Android AppKey` | `Rule::nullable()` |
| `android_channel` | text | — | `Android 通道` | 帮助：`Android 8.0 之后需要` |
| `android_activity` | text | — | `Android Activity` | 帮助：`Android Activity` |
| `ios_is_open` | switch | — | `是否开启 iOS 推送` | 默认 `false` |
| `ios_app_key` | text | 否 | `iOS AppKey` | `Rule::nullable()` |

> 所有设置归入配置组 `py-aliyun-push::push`（由 `FormSettingAliyunPush::$group` 指定），由 `poppy/system` 的设置仓库持久化，并通过 `PyAliyunPushDef::fillConfig()` 实时同步到 `poppy.aliyun-push.*` config 键。

## Web 路由

| HTTP方法 | URI | 请求类/控制器 | 说明 |
|--------|-----|----------|----|
| —      | —   | —        | 无 |

## 其他路由文件

> 无。

## 发布的事件（本模块对外发布）

| 事件类 | 携带数据 | 触发时机 | 监听方 |
|-----|------|------|-----|
| —    | —    | —    | —   |

> **本模块不发布任何领域事件**；推送是一次性动作，无后续回调事件。

## 监听的事件（本模块消费）

| 监听器类 | 监听的事件 | 业务动作 | 产生的事件/任务 |
|------|------|------|---------|
| —    | —    | —    | —       |

> **本模块不消费任何事件**。

## 队列任务

| Job 类             | 队列名 | 延迟 | 触发来源                          | 业务动作                              |
|-------------------|-----|----|-------------------------------|-------------------------------------|
| `Jobs\SenderJob`  | **default**（沿用 Laravel 默认） | 无（`dispatch()` 直接入队） | `Classes\AliPush::send()` 末尾 `dispatch(new SenderJob($message, $config))` | 调用 `Sender\PushSender::send()` 真正下发到阿里云 Push API；返回结果写入 `PushSender::$result`（仅内存，未持久化） |

> `SenderJob` 不带 `$tries` / `$backoff` / `$maxExceptions` 属性，**沿用 Laravel 默认**（`tries=1`）。若需要重试，**消费方需在运行时通过队列驱动配置或子类化覆盖**。详见 `## 待确认`。

## Artisan 命令

| 命令签名 | 说明 | 调度方式 |
|--------|----|------|
| —      | —  | —    |

> 本模块**无 Artisan 命令**。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方              | 目标模块        | 目标类 / 函数                       | 调用方式                  | 场景                                  |
|----------------------|-------------|--------------------------------|-----------------------|-------------------------------------|
| `Classes\AliPush`    | `poppy/framework` | `Helper\StrHelper::parseKey()` | `StrHelper::parseKey()` | 解析 `device_type` 字符串（`android\|notice;ios\|notice` → key/value）  |
| `Classes\Sender\BaseClient` | `poppy/framework` | `AppTrait`                | `use AppTrait;`        | 框架级 helper trait                       |
| `Classes\Sender\PushSender` | `poppy/framework` | `is_production()`         | `is_production()` 函数   | iOS `iOSApnsEnv` 派生 PRODUCT/DEV       |
| `Classes\AliPush`    | Laravel       | `dispatch()` 全局 helper        | `dispatch(new SenderJob(...))` | 异步下发推送                          |
| `Channels\AliPushChannel` | Laravel       | `config()` 全局 helper        | `config('poppy.aliyun-push.*')` | 读取运行时配置                    |
| `Channels\AliPushChannel` | `poppy/system` | `sys_setting()` 全局 helper     | `PyAliyunPushDef::fillConfig()` 间接调用 | 从系统设置同步配置到 config |

> `PyAliyunPushDef::fillConfig()` 不直接调用 `sys_setting`，而是**消费方调用** `fillConfig()` 后由该函数读取 `sys_setting('py-aliyun-push::push.*')` 写入 config。

## 被其他模块调用（本模块被引用）

> 当前仓库内 `grep -rn "AliyunPush\\\\Jobs\\\\SenderJob\|AliyunPush\\\\Channels\\\\AliPushChannel\|AliyunPush\\\\Classes\\\\AliPush" poppy/*/src/` 仅命中**本模块自身与 tests**，**未发现外部业务模块直接调用**。下方表格为契约层定义。

| 调用方模块      | 调用方类                                | 本模块目标类                                    | 调用方法                          | 场景                                  |
|------------|-------------------------------------|---------------------------------------------|-------------------------------|-------------------------------------|
| 业务模块（待确认） | 业务 Notification（实现 `Contracts\AliPushChannel`） | `Channels\AliPushChannel`                   | `->send($notifiable, $notification)` | Laravel `Notification::send()` 调用，触发推送 |
| 业务模块（待确认） | 业务 Notification                      | `Contracts\AliPushChannel`                  | `->toAliPush(): array`         | 业务方提供推送载荷（title/body/extra/registration_ids 等） |
| 业务模块（待确认） | 设备注册流程                            | `Classes\BindTag`                           | `->bindDevice($deviceType, $tag, $client_key): bool` | 设备号绑定阿里云标签，便于后续按 TAG 推送  |
| 业务模块（待确认） | 推送触发方                              | `Classes\AliPush`                           | `AliPush::getInstance()->setConfig(...)->send($params): bool` | 直接调用门面（无需 Notification），绕过 channel 入口 |
| 业务模块（待确认） | 任意代码                                 | `Jobs\SenderJob`                            | `dispatch(new SenderJob($message, $config))` | 通常**不应**由外部模块直接 dispatch，本模块内部已自动 dispatch |

## 类与公共方法清单

### `Channels\AliPushChannel`

| 方法 | 签名 | 抛出 | 说明 |
|----|----|----|----|
| `send` | `public function send($notifiable, Notification $notification): void` | `PushException`、`ApplicationException` | Laravel Notification 频道入口：调用 `$notification->toAliPush()` 拿载荷，`PyAliyunPushDef::fillConfig()` 同步配置，再 `AliPush::getInstance()->setConfig(Config::default())->send($notify)`；失败时抛 `ApplicationException` 带 `notify` context |

### `Contracts\AliPushChannel`（接口）

| 方法 | 签名 | 说明 |
|----|----|----|
| `toAliPush` | `public function toAliPush();` | 业务方 Notification 必须实现，返回推送载荷数组（详见 [business.md](business.md) 的字段映射） |

### `Classes\AliPush`

| 方法 | 签名 | 抛出 | 说明 |
|----|----|----|----|
| `getInstance` | `public static function getInstance(): self` | — | 单例 facade |
| `setConfig` | `public function setConfig(Config $config): self` | — | 注入运行时配置 |
| `send` | `public function send(array $params): bool` | `PushException` | 主入口：`compat()` → 解析 `device_type` → 检查 `*_is_open` → `toBatches()` 拆批 → `dispatch(SenderJob)`；返回 `false` 表示入口校验失败（参数空 / 设备类型未指定） |
| `getResult` | （继承自 `AppTrait`） | — | 错误字符串（仅入口校验失败时有意义） |

### `Classes\BindTag`

| 方法 | 签名 | 抛出 | 说明 |
|----|----|----|----|
| `bindDevice` | `public function bindDevice(string $device_type, string $tag, $client_key): bool` | SDK 异常（未捕获） | 根据 `device_type`（`ANDROID` / `IOS`）选 `appKey`，`client_key` 支持数组（自动 `implode(',')`），调用 `Push::bindTag(BindTagRequest)`，响应写入 `$this->result` |

### `Classes\Sender\BaseClient`（抽象类）

| 方法 | 签名 | 抛出 | 说明 |
|----|----|----|----|
| `__construct` | `public function __construct(Config $config)` | — | 从 `Config` 读取 7 个字段到 protected 属性 |
| `setAppConfig` | `public function setAppConfig($ak, $sk, $android_app_id, $android_channel = '', $ios_key = ''): self` | — | 运行时覆写 AK/SK/AppKey（一般不直接使用，走构造函数） |
| `getResult` | `public function getResult(): mixed` | — | 获取上次 SDK 响应的 `body->toMap()` 数组 |
| `initClient` | `protected function initClient(): Push`（仅 protected） | — | 初始化阿里云 `Push` 客户端，端点固定 `cloudpush.aliyuncs.com` |

### `Classes\Sender\PushMessage`（DTO）

常量（与业务字段）：

| 常量                     | 值          | 含义               |
|------------------------|------------|------------------|
| `DEVICE_TYPE_ANDROID`  | `ANDROID`  | Android 设备类型    |
| `DEVICE_TYPE_IOS`      | `IOS`      | iOS 设备类型        |
| `PUSH_TYPE_MESSAGE`    | `MESSAGE`  | 透传消息（无通知栏展示）   |
| `PUSH_TYPE_NOTICE`     | `NOTICE`   | 系统通知（带通知栏展示）   |
| `TARGET_DEVICE`        | `DEVICE`   | 按 deviceId 列表     |
| `TARGET_ACCOUNT`       | `ACCOUNT`  | 按账号（**未实现**，预留） |
| `TARGET_ALIAS`         | `ALIAS`    | 按别名（**未实现**，预留） |
| `TARGET_TAG`           | `TAG`      | 按标签              |
| `TARGET_ALL`           | `ALL`      | 全员广播            |
| `TARGET_VALUE_ALL`     | `ALL`      | `target=ALL` 时固定值 |

公共方法（getter / setter 链）：

| 方法 | 签名 | 说明 |
|----|----|----|
| `getDeviceType` / `setDeviceType` | `: string` / `void` | 设备类型 |
| `getTitle` / `setTitle` | `: string` / `: self` | 标题 |
| `getBody` / `setBody` | `: string` / `: self` | 通知内容 / 消息 JSON |
| `getPushType` / `setPushType` | `: string` / `: self` | `MESSAGE` / `NOTICE` |
| `getTarget` / `setTarget` | `: string` / `: self` | 推送目标 |
| `getTargetValue` / `setTargetValue` | `: string` / `: self` | 推送目标值（设备号列表 / 标签 / `ALL`） |
| `getExtParameters` / `setExtParameters` | `: string` / `void` | 扩展参数 JSON 字符串 |
| `getQuery` / `setQuery` | `: array` / `void` | 附加推送参数（结构：`['base' => [], 'android' => [], 'ios' => []]`） |

### `Classes\Sender\PushSender`（继承 `BaseClient`）

| 方法 | 签名 | 抛出 | 说明 |
|----|----|----|----|
| `send` | `public function send(PushMessage $message): void` | `PushException` | 组装 `PushRequest` 并调用 `$client->push($request)`；`checkEnv()` 校验 AppKey/Channel；按 iOS/Android/NOTICE 分支附加平台特定参数；响应写入 `$this->result` |
| `checkEnv` | `private function checkEnv(): void` | `PushException` | Android 校验 `androidAppKey` + `androidChannel`；iOS 校验 `iosAppKey` |
| `isAndroid` / `isIos` / `isNotice` | `private function ...(): bool` | — | 内部判断 helper |

### `Classes\Config\Config`

| 方法 | 签名 | 说明 |
|----|----|----|
| `__construct` | `public function __construct($ak, $sk, $android_app_id, $android_channel = '', $android_activity = '', $ios_key = '', $clientName = '')` | 7 字段构造 |
| `getAccessKey` / `getAccessSecret` / `getAndroidAppKey` / `getAndroidChannel` / `getAndroidActivity` / `getIosAppKey` / `getClientName` | `: string` | getter |
| `default` | `public static function default(): self` | 从 `config('poppy.aliyun-push.*')` 构造默认实例 |

### `Classes\PyAliyunPushDef`

| 方法 | 签名 | 说明 |
|----|----|----|
| `fillConfig` | `public static function fillConfig(): void` | 从 `sys_setting('py-aliyun-push::push.*')` 同步 8 个键到 `config('poppy.aliyun-push.*')` |

### `Jobs\SenderJob`

| 方法 | 签名 | 抛出 | 说明 |
|----|----|----|----|
| `__construct` | `public function __construct(PushMessage $message, Config $config)` | — | 持有待下发的 `PushMessage` 和运行时的 `Config` |
| `handle` | `public function handle(): void` | `PushException` | 实例化 `PushSender($this->config)`，调用 `->send($this->message)`；**不返回结果、不捕获异常** |

### `Exceptions\PushException`

继承 `Poppy\Framework\Exceptions\BaseException`，**无自定义字段**。

## 设置面板（FormSettingAliyunPush）路由

> 该 Form 不是 Controller、不挂独立路由；它**只在后台设置中心**（由 `poppy/mgr-page` 渲染）出现。
> 设置中心 URL 由 `poppy/mgr-page` 决定，命名遵循 `py-aliyun-push::push.*` 路由的 sys_setting key 命名空间（**注意：key 是 `sys_setting` 命名空间，不是 URL 路由前缀**）。

## 待确认

- **队列配置**：`SenderJob` 在 `dispatch()` 时未指定连接/队列，**实际运行队列名取决于 `config('queue.default')` 与各连接配置**（发现位置：`Classes\AliPush.php` line 113）
- **`SenderJob` 重试/超时**：未显式声明 `$tries` / `$backoff` / `$timeout` / `$maxExceptions`，**默认 `tries=1`、无 backoff**；如需重试需在运行时通过 `queue.connections.*.retry_after` 或子类化覆盖（发现位置：`Jobs\SenderJob.php` 全文）
- **`AliyunPush\Jobs\SenderJob` 是否会被外部 dispatch**：`grep poppy/*/src/` 未发现外部调用方，**外部业务模块是否引用需要人工确认**（发现位置：`grep -rn "AliyunPush\\\\Jobs\\\\SenderJob" poppy/*/src/`）
- **`toAliPush()` 返回值校验**：Channel 端只检查 `if (!$notify) return;`（即 `null` / `false`），**不校验字段完整性**——业务方若返回空数组会进入 `AliPush::send()` 的「请求参数为空, 无法发送通知」分支（发现位置：`Channels\AliPushChannel.php` line 31）
- **`BindTag::bindDevice()` 是否需要事务/批量绑定**：当前 API 一次只绑定一个标签到一个或多个 device，**无批量方法**（发现位置：`Classes\BindTag.php`）