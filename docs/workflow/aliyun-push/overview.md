# aliyun-push（Poppy\AliyunPush）

## 模块职责

`poppy/aliyun-push` 是 Poppy 框架的**阿里云移动推送（EMAS/HMS-like）集成模块**。

通过封装 [alibabacloud/push-20160801](https://github.com/alibabacloud/sdk-php) SDK，向 iOS / Android 终端发送推送（通知 / 消息），提供：

- Laravel Notification 频道 `AliPushChannel`（实现 `Contracts\AliPushChannel`），让业务方一行 `Notification::send()` 即可推送到指定设备
- 后台 MgrPage 设置面板 `FormSettingAliyunPush`（`py-aliyun-push::push.*` 配置组），由 `Hooks\MgrPage\SettingsAliyunPush` 挂入 `poppy.mgr-page.settings` Hook
- 异步队列任务 `Jobs\SenderJob`（将推送真正下发到阿里云，**消费方需自行配置队列驱动**）
- 设备标签绑定工具 `Classes\BindTag`（基于阿里云 `BindTagRequest`，用于按标签批量推送）

模块**不负责**：设备注册（deviceId 登记由业务 App 完成）、推送到达率统计、推送回执处理。

## 目录结构

| 目录                | 职责                                                                                   | 文件数 |
|-------------------|--------------------------------------------------------------------------------------|-----|
| Channels          | Laravel Notification 频道：`AliPushChannel`（通知 → `toAliPush()` → `AliPush::send()`）     | 1   |
| Contracts         | Notification 契约：`AliPushChannel::toAliPush()`，业务方实现此接口以提供推送载荷                              | 1   |
| Classes           | 核心类：`AliPush`（门面 + 批量分发）、`BindTag`（设备标签绑定）、`PyAliyunPushDef`（从 `sys_setting` 同步配置到 `config`）、`Config\Config` | 4   |
| Classes/Sender    | SDK 封装：`BaseClient`（初始化 `Push` 客户端）、`PushMessage`（DTO）、`PushSender`（组装 `PushRequest` 并下发） | 3   |
| Jobs              | 队列任务：`SenderJob`（调用 `PushSender::send()`）                                              | 1   |
| Http/MgrPage      | 后台表单：`FormSettingAliyunPush`（配置项定义）                                                   | 1   |
| Hooks/MgrPage     | 框架扩展点：`SettingsAliyunPush`（`ServiceArray`，挂入 `poppy.mgr-page.settings`）                 | 1   |
| Exceptions        | `PushException`（继承 `BaseException`）                                                   | 1   |
| resources/config  | 默认配置 `aliyun-push.php`（键名前缀 `poppy.aliyun-push.*`）                                    | 1   |
| tests/Push        | 集成测试：`PushTest`（8 个场景）+ `tests/Sample/*Notification`（8 个 Notification 示例）              | 9   |

> 该模块**无 Model**、**无 Action**、**无 Http/Request/Response**、**无 Events/Listeners**、**无 Commands**、**无独立 Http/Routes**。
> 后台配置走 `poppy/mgr-page` 通用设置面板，对外 API 由消费方模块自行实现 Notification。

## 技术栈

| 技术     | 版本/说明                                                                  |
|--------|------------------------------------------------------------------------|
| PHP    | `>=7.4`（来自 `composer.json` `require.php`）                               |
| Laravel | 基于 `poppy/core: 4.3.*`，向下兼容 Laravel 6                                |
| 模块框架   | `poppy/core 4.3.*`（由 `framework` 间接升级）                                |
| 推送SDK  | `alibabacloud/push-20160801: 1.*`（阿里云 Push V20160801）                     |
| 队列     | Laravel Queue（**Job 默认同步触发** `dispatch()`，消费方需注册 `default` 队列连接） |
| 缓存     | `sys_setting('py-aliyun-push::push.*')` 同步到 `poppy.aliyun-push.*` 配置键     |
| 其它依赖   | `poppy/framework`（`AppTrait`、`BaseException`、`is_production()`）、`poppy/core`（`PoppyServiceProvider`、`ServiceArray`） |

## 路由概览

| 路由文件        | 类型    | 前缀    | 路由数 | 说明                       |
|-------------|-------|-------|-----|--------------------------|
| —           | —     | —     | 0   | **无独立路由文件**；后台配置走 `poppy/mgr-page` 通用设置入口，业务 API 由消费方模块自行实现 Notification |

## 模型清单

| 模型 | 数据表 | 关键关联 | 说明 |
|----|------|------|----|
| —  | —    | —    | 该模块**无 Eloquent 模型**；所有运行时配置通过 `Config\Config` 对象或 `poppy.aliyun-push.*` config 键读取 |

## 依赖的其他模块

| 模块                  | 引用方式                                                 | 说明                                                                |
|---------------------|------------------------------------------------------|-------------------------------------------------------------------|
| `poppy/framework`   | `AppTrait`、`BaseException`、`is_production()`        | `BaseClient` / `AliPush` 引用 `AppTrait`；`PushException` 继承 `BaseException`；iOS ApnsEnv 判定用 `is_production()` |
| `poppy/core`        | `PoppyServiceProvider`、`ServiceArray` 契约           | `ServiceProvider` 继承 `PoppyServiceProvider`；`SettingsAliyunPush` 实现 `ServiceArray` |
| `poppy/mgr-page`    | `FormSettingBase`                                     | `FormSettingAliyunPush` 继承表单基类                                  |
| `poppy/system`      | `sys_setting()` / `sys_hook()` 全局辅助函数               | 从系统设置读取 `py-aliyun-push::push.*`；挂入 `poppy.mgr-page.settings` Hook |
| `alibabacloud/push-20160801` | `AlibabaCloud\SDK\Push\V20160801\Push` / `Models\PushRequest` / `BindTagRequest` | SDK 客户端与请求对象 |

## 被其他模块依赖

> 当前仓库中**未发现其他业务模块直接 `use` 本模块**（`grep -rn "AliyunPush\\Jobs\\SenderJob\|AliyunPush\\Channels\\AliPushChannel\|AliyunPush\\Classes\\AliPush" poppy/*/src/` 仅命中本模块自身与 tests）。

| 模块      | 引用方式                                                | 使用场景                                                                     |
|---------|-----------------------------------------------------|--------------------------------------------------------------------------|
| 业务模块（待确认） | `use Poppy\AliyunPush\Channels\AliPushChannel;`     | 在 Notification 的 `via()` 中返回 `[AliPushChannel::class]`，实现 `Contracts\AliPushChannel::toAliPush()` 提供推送载荷 |
| 业务模块（待确认） | `use Poppy\AliyunPush\Jobs\SenderJob;`              | **本模块内部** `AliPush::send()` 末尾会 `dispatch(new SenderJob($message, $config))`；外部业务模块通常**不直接 dispatch** 这个 Job |
| 业务模块（待确认） | `use Poppy\AliyunPush\Classes\BindTag;`             | 设备注册时绑定阿里云标签（如性别、地区、版本），用于后续按 TAG 推送                            |

## 边界说明（不负责的事项）

- **不做**：设备注册（deviceId 登记由业务 App 自行上报，本模块只接收设备号字符串）
- **不做**：推送回执 / 到达率统计 / 用户分群运营
- **不做**：iOS 推送证书管理（依赖阿里云控制台配置 APNs 证书）
- **不做**：定时清理失效设备号（依赖阿里云控制台）
- **不做**：多通道适配（仅集成阿里云 Push，不含 TPNS、个推、极光等）
- **不做**：独立的 Http API 路由文件（业务方按需在自家模块中通过 Notification 调用）
- **不做**：iOS/Android 推送开关之外的灰度、A/B 测试
- **不做**：自动重试（`SenderJob::handle()` 不抛异常时由 Laravel Queue 触发重试，详见 [contracts.md](contracts.md)）

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)