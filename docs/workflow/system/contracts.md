# 对外契约

> 路由前缀：`/api_v1/system`（由 `Http/RouteServiceProvider::mapApiRoutes()` 设置）。
> 中间件分组：由 `Http/MiddlewareServiceProvider::boot()` 定义：`api-sign` / `api-sso` / `web-base` / `web-auth` / `web-with-auth`，均以 `sys-*` 别名组合而成。

## API 路由（`api_v1_web.php`）

> 全部为 `POST` 接口，请求与响应 Body 均在 `src/Http/Request/ApiV1/*/` 子目录（Request 类 + ResponseBody 类）。

### 公开接口（中间件：`api-sign`）

| HTTP方法 | URI                                | 请求类/控制器                   | 中间件                          | 说明                                                  |
|--------|------------------------------------|---------------------------|-----------------------------|-----------------------------------------------------|
| POST   | `/api_v1/system/auth/login`        | `AuthController::login`   | `api-sign`                  | 通行证登录 / 注册（密码或验证码），返回 JWT。路由名 `py-system:pam.auth.login`     |
| POST   | `/api_v1/system/auth/exists`       | `AuthController::exists`  | `api-sign`                  | 检查通行证是否存在，ResponseBody `AuthExistsResponseBody`     |
| POST   | `/api_v1/system/captcha/send`      | `CaptchaController::send` | `api-sign`                  | 发验证码，触发 `CaptchaSendEvent`                          |
| POST   | `/api_v1/system/captcha/verify_code` | `CaptchaController::verifyCode` | `api-sign`             | 用 captcha 兑换一次性 `verify_code`，隐藏字段 = passport       |
| POST   | `/api_v1/system/auth/reset_password` | `AuthController::resetPassword` | `api-sign`            | 重设密码（验证方式二选一）                                       |
| POST   | `/api_v1/system/auth/bind_mobile`  | `AuthController::bindMobile` | `api-sign`               | 换绑手机（`rebind`）                                     |

> 注：`captcha/send` 路由名未命名（路由组仅 `api-sign`），其它上述均按 `py-system:pam.auth.*` 命名。

### 签名 + 站点开关（中间件：`sys-app_sign`，单独分组）

| HTTP方法 | URI                                 | 请求类/控制器                | 中间件            | 说明                                                       |
|--------|-------------------------------------|------------------------|----------------|----------------------------------------------------------|
| POST   | `/api_v1/system/core/info`          | `CoreController::info` | `sys-app_sign` | 系统信息（数据由 hook `poppy.system.api_info` 注入）             |
| POST   | `/api_v1/system/core/translate`     | `CoreController::translate` | `sys-app_sign` | 获取当前 zh 语言翻译键值对                                |

### Jwt 校验接口（中间件：`sys-jwt`）

| HTTP方法 | URI                                  | 请求类/控制器                  | 中间件       | 说明                                                                                      |
|--------|--------------------------------------|--------------------------|------------|-----------------------------------------------------------------------------------------|
| POST   | `/api_v1/system/upload/image`        | `UploadController::image`| `sys-jwt`  | 图片上传，`type=form/base64/url`；路由名 `py-system:api_v1.upload.image`                          |
| POST   | `/api_v1/system/upload/file`         | `UploadController::file` | `sys-jwt`  | 文件上传，`type=images/file/video/audio`；路由名 `py-system:api_v1.upload.file`               |

### SSO 接口（中间件：`api-sso`）

| HTTP方法 | URI                              | 请求类/控制器                  | 中间件        | 说明                                                                        |
|--------|----------------------------------|--------------------------|-------------|---------------------------------------------------------------------------|
| POST   | `/api_v1/system/auth/access`     | `AuthController::access` | `api-sso`   | 校验 JWT，返回 PAM 账号信息；路由名 `py-system:pam.auth.access`                       |
| POST   | `/api_v1/system/auth/renew`      | `AuthController::renew`  | `api-sso`   | 续签 JWT；触发 `TokenRenewEvent` + `LoginSuccessEvent(type=renew)`；路由名 `py-system:pam.auth.renew` |
| POST   | `/api_v1/system/auth/logout`     | `AuthController::logout` | `api-sso`   | 触发 `PamLogoutEvent`，仅前台 `TYPE_USER` 走 `Sso::logout()`；路由名 `py-system:pam.auth.logout`  |

## 其它路由文件

模块当前仅维护 `api_v1_web.php` 一个路由文件，无 `backend.php` / `web.php` / `mgr.php` 等。后台入口由宿主应用（`poppy/mgr-page`）提供。

## 发布的（本模块对外）事件

| 事件类                              | 携带数据                                                                                                                                                      | 触发时机                                                          | 监听方                                                                                                |
|----------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------|----------------------------------------------------------------------------------------------------|
| `LoginSuccessEvent`              | `PamAccount $pam, string $guard, string $type`                                                                                                          | `Action\Pam::captchaLogin` / `loginCheck`；`AuthController::renew`（type=renew）    | `Listeners\LoginSuccess\{UpdatePasswordHashListener, LogListener, UpdateLastLoginListener}` |
| `LoginFailedEvent`               | `string $type, string $passport, string $password`                                                                                                       | `Action\Pam::loginCheck` 中 `guard->attempt` 失败时                          | （本模块未注册监听；宿主使用）                                                                                  |
| `LoginBannedEvent`               | `PamAccount $pam, string $guard`                                                                                                                          | `Action\Pam::captchaLogin` / `beCaptchaLogin` / `loginCheck` 触发前        | （本模块未注册监听；宿主使用）                                                                                  |
| `LoginTokenPassedEvent`          | `PamAccount $pam, string $token, string $deviceId, string $deviceType`                                                                                    | `AuthController::login` 颁发 JWT 后立即触发                                  | `Listeners\LoginTokenPassed\SsoListener`                                                           |
| `PamRegisteredEvent`             | `PamAccount $pam`                                                                                                                                         | `Action\Pam::register()` 事务内创建账号后                                        | （本模块未注册监听；宿主使用）                                                                                  |
| `PamLogoutEvent`                 | `PamAccount $pam`                                                                                                                                          | `Action\Pam::logout()`                                            | `Listeners\PamLogout\SsoListener`                                                                   |
| `BePamLogoutEvent`               | `int $accountId`                                                                                                                                           | （未被本模块 Action 直接触发，业务侧可能要消费）                                                    | 无注册监听                                                                                              |
| `PamPasswordModifiedEvent`       | `PamAccount $pam`                                                                                                                                          | `Action\Pam::setPassword()`                                       | `Listeners\PamPasswordModified\SsoListener`                                                       |
| `PamRebindEvent`                 | `PamAccount $pam`                                                                                                                                          | `Action\Pam::rebind()`                                            | 无注册监听                                                                                              |
| `PamSsoEvent`                    | `PamAccount $pam, Collection< PamToken > $tokens`                                                                                                        | `Action\Sso::handle` 内踢人后删除旧 Token 触发                                  | 无注册监听（宿主推送"被踢下线"通知使用）                                                                              |
| `PamSsoLogoutEvent`              | `int $accountId, PamToken $token`                                                                                                                         | `Action\Sso::logout`                                              | 无注册监听                                                                                              |
| `PamTokenBanEvent`               | `PamToken $token, string $type` (ip / device / token)                                                                                                    | `Action\Ban::type`                                                | 无注册监听                                                                                              |
| `PamDisableEvent`                | `PamAccount $pam, PamAccount $editor, string $reason`                                                                                                    | `Action\Pam::disable`                                             | 无注册监听                                                                                              |
| `PamEnableEvent`                 | `PamAccount $pam, ?PamAccount $editor, string $reason`                                                                                                   | `Action\Pam::enable` / `autoEnable()`                             | 无注册监听                                                                                              |
| `TokenRenewEvent`                | `PamAccount $pam, string $token, string $deviceId, string $deviceType`                                                                                   | `AuthController::renew`                                            | `Listeners\TokenRenew\TokenRenewListener`                                                          |
| `TokenRenewAfterEvent`           | `PamToken $pamToken, string $oldTokenHash`                                                                                                               | `Action\Sso::renew()`                                              | 无注册监听                                                                                              |
| `RolePermissionUpdatedEvent`     | `PamRole $role`                                                                                                                                            | `Action\Role::savePermission`                                     | 无注册监听                                                                                              |
| `SysConfigSavedEvent`            | `SysConfig $config`                                                                                                                                        | `SettingRepository::set()`                                        | 无注册监听                                                                                              |
| `SettingUpdatedEvent`            | （继承 `Poppy\Framework\Application\Event`，无字段）                                                                                                          | 由框架或第三方触发                                                       | 无注册监听                                                                                              |
| `CaptchaSendEvent`               | `string $passport, string $captcha`                                                                                                                       | `CaptchaController::send` 验证码生成成功后                                  | （宿主发送短信 / 邮件的实际下发）                                                                                |
| `PassportVerifyEvent`            | `string $passport, string $type`                                                                                                                          | 供宿主扩展验证前的钩子，未在模块内触发                                              | 无注册监听                                                                                              |

## 监听的事件（本模块消费）

| 监听器类                                                   | 监听的事件                                | 业务动作                                                                                                | 产生的事件/任务                                                                                                                |
|--------------------------------------------------------|---------------------------------------|----------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------|
| `Listeners\LoginSuccess\UpdatePasswordHashListener`     | `LoginSuccessEvent`                    | 把当前账号的 `getAuthPassword()` 写入 Session（`AuthenticateSession::hashGuard($guard)`），供后续 `sys-auth_session` 校验 | —                                                                                                                       |
| `Listeners\LoginSuccess\LogListener`                    | `LoginSuccessEvent`                    | 写 `PamLog` 记录（含 `area_text`，调用 `poppy.ext.ip_store` 区域解析）                                              | —                                                                                                                       |
| `Listeners\LoginSuccess\UpdateLastLoginListener`        | `LoginSuccessEvent`                    | 更新 `logined_at` / `login_times++` / `login_ip`                                                       | —                                                                                                                       |
| `Listeners\LoginTokenPassed\SsoListener`                | `LoginTokenPassedEvent`                | 调 `Action\Sso::handle($pam, $deviceId, $deviceType, $token)` 写入 Token / 按模式踢人                       | 内部可能发 `PamSsoEvent`（Sso 内踢人时）                                                                                          |
| `Listeners\PamLogout\SsoListener`                       | `PamLogoutEvent`                       | 仅 `TYPE_USER` 调 `Action\Sso::logout()`                                                              | 内部可能发 `PamSsoLogoutEvent`                                                                                                |
| `Listeners\PamPasswordModified\SsoListener`             | `PamPasswordModifiedEvent`             | 仅 `TYPE_USER` 调 `Action\Sso::banUser($pam->id)` 清 `PamToken` 与 `sso-valid` 哈希                       | —                                                                                                                       |
| `Listeners\TokenRenew\TokenRenewListener`               | `TokenRenewEvent`                      | 调 `Action\Sso::renew()` 更新过期时间 / token_hash                                                      | 内部发 `TokenRenewAfterEvent`                                                                                              |
| `Listeners\PermissionInit\InitToDbListener`            | `Poppy\Core\Events\PermissionInitEvent`（跨模块）| `whereNotIn(name, $keys)->delete()` 清理 + `updateOrCreate` + 把全 `TYPE_BACKEND` 权限赋予 `PamRole::BE_ROOT` | —                                                                                                                       |
| `Listeners\PoppyOptimized\ClearCacheListener`           | `Poppy\Framework\Events\PoppyOptimized` (跨模块) | 清空 `sys_tag('py-system')` 缓存集合                                                                       | —                                                                                                                       |
| `Listeners\PoppyOptimized\SystemInitListener`           | `Poppy\Framework\Events\PoppyOptimized` | 调 `Action\Ban::initCache()` 重新初始化 `ban-one-{type}` 与 `ban-ip-range-{type}`                          | —                                                                                                                       |
| `Listeners\QueryExecuted\LogListener`                  | `Illuminate\Database\Events\QueryExecuted` | 调 `Action\DbOptimize::log($event)`：按 `db-optimize:{table}` 配置落 Redis 哈希                              | —                                                                                                                       |
| `Listeners\AuthLogout\LogoutLogListener`               | Laravel `Illuminate\Auth\Events\Logout`（推断；`handle($user)`） | 写 `PamLog` 登出日志（⚠ 已写但未在 `$listens` 中注册）                                                         | —                                                                                                                       |

## 队列任务

| Job 类                       | 队列名                     | 延迟                                                                                                                            | 触发来源                                              | 业务动作                                                                                                    |
|-----------------------------|--------------------------|-------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `Jobs\NotifyJob`            | 默认（由 `poppy.queue.connection` 决定） | `dispatch((new self(...))->delay($timeMap[$execNum]))`，默认重试延迟 `[10, 30, 60]` 秒（`py-system::callback.exam_time` 可配）           | 业务模块 dispatch                                    | 增强型 Guzzle 回调（带重试）；`@deprecated 4.1，建议 `NotifyProJob``                                                         |
| `Jobs\NotifyProJob`         | 默认                       | 重试延迟 map `[15, 45, 120, 300]` 秒（最多 4 次），第 N 次失败 `delay(...)->setExecAt(N+1)`                                       | 业务模块 dispatch                                    | 增强型 Guzzle（任意 method + Guzzle options）                                                                |
| `Jobs\DeleteUploadFileJob`  | 默认                       | —                                                                                                                               | 业务模块 dispatch                                    | 解析 URL → 设置 `FileContract::setDestination` → `delete()`                                                    |

> 三者均继承 `Poppy\Framework\Application\Job` + `Illuminate\Contracts\Queue\ShouldQueue` + `Queueable` Trait；
> 模块内 `$tries` / `$backoff` / `$timeout` 没有显式声明，沿用框架默认。

## Artisan 命令

| 命令签名                                                                                          | 说明                                              | 调度方式               |
|------------------------------------------------------------------------------------------------|-------------------------------------------------|--------------------|
| `py-system:install`                                                                             | 安装脚本（数据迁移 / 角色初始）                                | 手动                  |
| `py-system:ban`                                                                                 | Ban 维护                                          | 手动                  |
| `py-system:op`                                                                                  | 运维操作                                            | 手动                  |
| `py-system:sys_config_convert`                                                                  | 配置格式迁移                                          | 手动                  |
| `py-system:user {do} {--account=} {--pwd=} {--perm=}`                                          | 账号 / 角色 / 权限综合工具（已实现子命令：`reset_pwd` / `create_user` / `auto_fill` / `clear_expired` / `init_role` / `auto_enable` / `user` / `assign` / `clear_log` / `ban_init` / `check_perm`）  | `auto_enable`/`clear_log`/`clear_expired` 已挂调度，其余手动 |

## 定时调度（注册于 `ServiceProvider::registerSchedule()`，监听 `console.schedule` 事件）

| 命令                                   | 调度频率                       |
|--------------------------------------|----------------------------|
| `py-system:user auto_enable`         | 每 15 分钟                     |
| `py-system:user clear_log`           | 每日 04:00                    |
| `py-system:user clear_expired`       | 每日 06:00                    |

> 调度通过 `appendOutputTo($this->consoleLog())` 输出到 `py-system` 的 `storage/logs/console.log`。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方                                              | 目标模块       | 目标类                                       | 调用方法                                                                | 场景               |
|------------------------------------------------------|------------|-------------------------------------------|---------------------------------------------------------------------|------------------|
| `Listeners\PermissionInit\InitToDbListener`          | `poppy/core` | `Poppy\Core\Events\PermissionInitEvent` | `event()` 监听（即被动接收 `getPermissions()`）                              | 框架初始化权限集合          |
| `Listeners\PoppyOptimized\ClearCacheListener`        | `poppy/framework` | `Poppy\Framework\Events\PoppyOptimized` | 监听                                                                   | 框架优化钩子           |
| `Listeners\PoppyOptimized\SystemInitListener`        | `poppy/framework` | `Poppy\Framework\Events\PoppyOptimized` | 监听                                                                   | 框架优化钩子           |
| `Action\Role::permissions()`                         | `poppy/core` | `CoreTrait::corePermission()`           | 获取宿主注册的权限对象集合                                                       | 角色授权页            |
| `Action\Pam::clearLog()`                             | `poppy/mgr-page` | `Poppy\MgrPage\Http\MgrPage\FormSettingLog` | 读取 `FormSettingLog::DAYS_FOREVER` 常量                              | 清理登录日志兜底         |
| `Listeners\LoginSuccess\LogListener`                | 宿主扩展        | `poppy.ext.ip_store` (ServiceProvider 注册) | `area($ip)`                                                       | 登录日志地区反查         |
| `CaptchaController::send`                            | 宿主           | 宿主必须监听 `CaptchaSendEvent` 以真正下发短信 / 邮件 | `event()` 触发                                                    | 验证码发送            |

## 被其他模块调用（本模块被引用）

> 跨模块依赖扫描结果（其他模块对 `Poppy\System` 的 `use`）：

| 调用方模块                | 调用方类                                                              | 本模块目标类                                                  | 调用方法                                              | 场景              |
|----------------------|--------------------------------------------------------------------|----------------------------------------------------------|--------------------------------------------------|-----------------|
| `mgr-page` / `category` 等业务模块 | `Http\Middlewares\Authenticate` 等                                | `Poppy\System\Models\PamAccount`                         | `$user->is_enable`, `$user->roles()`                | 后台鉴权           |
| `mgr-page` 等         | 后台 Form 表单                                                       | `Poppy\System\Models\PamAccount::GUARD_BACKEND`          | 路由 / 中间件 Guard 名                                  | 后台守卫           |
| 业务模块                | 任意                                                                  | `Poppy\System\Models\PamRole` / `PamPermission` / `PamRoleAccount` | `hasRole`、`cachedPermissions`               | RBAC 鉴权         |
| 业务模块                | `UserCommand` 等                                                     | `Poppy\System\Jobs\{NotifyJob, NotifyProJob}`            | `dispatch(new ...)`                                | 异步回调 / 通知       |
| 业务模块                | 任意                                                                  | `Poppy\System\Models\PamAccount::passportType()`         | 静态方法                                              | 通行证自动识别        |
| 宿主应用                  | `composer require poppy/system`                                    | ServiceProvider                                          | `poppy.system`                                    | 模块启动           |

> 完整模块依赖矩阵位于 `docs/workflow/_index/`（宿主按规范生成）。

## 待确认

- `NotifyJob` / `NotifyProJob` 内的 `$tries` / `$backoff` / `$timeout` 没有在源码中显式声明，依赖 `Poppy\Framework\Application\Job` 默认值（需在 framework 中确认）
- `Listeners\AuthLogout\LogoutLogListener` 已实现但未在 `ServiceProvider::$listens` 中绑定，需确认业务上是否仍希望保留（发现位置：`src/Listeners/AuthLogout/LogoutLogListener.php` + `src/ServiceProvider.php::$listens`）
- `PamDisableEvent` / `PamEnableEvent` / `PamRebindEvent` / `BePamLogoutEvent` 等事件本模块触发，但本模块内没有注册 Listener，是否有其他模块监听待确认（无现成扫描列表）
