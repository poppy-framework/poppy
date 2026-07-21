# system（Poppy\System）

## 模块职责

`poppy/system` 是 Poppy 框架的**核心账号/认证/SSO/上传/设置/通知基石模块**，覆盖：
PAM（账号、角色、权限、Token）模型层与 RBAC 三元组；账号登录 / 注册 / 验证码 / 续期 / 退出全流程；
单点登录（设备唯一 / 分组踢人 / 数量限制）；后台 IP + 设备黑白名单封禁；多层级系统设置仓库；
多 Provider 文件上传（Hook 选择）；以及`py-system:user` 定时维护脚本。

## 目录结构

| 目录             | 职责                                                                       | 文件数 |
|----------------|--------------------------------------------------------------------------|-----|
| Action         | 业务逻辑层：账号、角色、SSO、验证、封禁、DB 优化                                                 | 6   |
| Classes        | 工具 / 契约 / Provider / Traits / Logger                                          | 38+ |
| Commands       | Artisan 命令：`py-system:user`、`py-system:install`、`py-system:ban`、`py-system:op`、`py-system:sys_config_convert` | 5   |
| Events         | 领域事件（登录/SSO/Token/设置/角色/封禁/注册 等）                                              | 22  |
| Listeners      | 事件监听器（登录成功级联、SSO、权限初始化、缓存清理、查询日志 等）                                       | 12  |
| Jobs           | 队列任务：`NotifyJob`（Guzzle 回调 + 重试）、`NotifyProJob`（增强型）、`DeleteUploadFileJob` | 3   |
| Http/Request   | API V1 控制器（Auth、Upload、Captcha、Core、WebApi、JwtApi）；请求 / 响应 Body               | 28+ |
| Http/Middlewares | 鉴权 / JWT / Ban / SSO / 站点开关 / CORS 等 11 个中间件                                    | 11  |
| Http/Routes    | 路由文件：`api_v1_web.php`                                                       | 1   |
| Models         | Eloquent 模型：账号 / 角色 / 权限 / Token / Ban / Log / SysConfig + Policy + Resource    | 12  |
| Hooks/System   | 框架扩展点（`ApiInfo`、`UploadTypeDefault`）                                          | 2   |
| Setting        | `SettingServiceProvider` + Repository + Facade                                   | 3   |
| Support        | 全局辅助函数（`functions.php`）                                                    | 1   |
| Exceptions     | `FormException` / `SettingKeyNotMatchException` / `SettingValueOutOfRangeException` | 3   |

## 技术栈

| 技术      | 版本/说明                                                                |
|---------|----------------------------------------------------------------------|
| PHP     | `>=7.4`（来自 `composer.json` `require.php`）                                 |
| Laravel | 基于 `poppy/core: 4.3.*`，向下兼容 Laravel 6                                  |
| 模块框架    | `poppy/core 4.3.*`（由 `framework` 间接升级）                                  |
| ORM     | Eloquent + `tucker-eric/eloquentfilter: 3.*`                          |
| 认证      | `tymon/jwt-auth: 1.0.*` + 多 Guard 桥接                                   |
| 队列      | 同步 `Job`（Laravel 队列驱动由宿主决定）                                            |
| 缓存      | `poppy.ext.ip_store` 区域查询 + `sys_tag` Redis 哈希缓存                       |
| 哈希      | `md5(sha1($password.$reg_datetime).$password_key)` 自研策略                |
| 其它依赖    | `mews/captcha ^3.2`、`intervention/image ^2`、`ezyang/htmlpurifier 4.16.*` |

## 路由概览

| 路由文件           | 类型   | 前缀                       | 路由数 | 说明                                                |
|----------------|------|--------------------------|-----|---------------------------------------------------|
| api_v1_web.php | API  | `/api_v1/system`（RouteServiceProvider 设置） | 11  | 鉴权、SSO、Token、上传、验证码、Core 信息                      |

> 所有路由分组通过 `MiddlewareServiceProvider` 别名（`sys-*`）和分组（`api-sign` / `api-sso`）管控。
> 路由前缀在 `Http/RouteServiceProvider::mapApiRoutes()` 中以 `api_v1/system` 挂载，详见 contracts.md。

## 模型清单

| 模型                  | 数据表                  | 关键关联                                                                | 说明                       |
|---------------------|----------------------|---------------------------------------------------------------------|--------------------------|
| `PamAccount`        | `pam_account`        | `roles()`、`cachedRoles`、`RbacUserContract`、`JWTSubject`               | 账号主表（含手机/邮箱/用户名/密码/密钥）   |
| `PamRole`           | `pam_role`           | `RbacRoleContract`、`cachedPermissions()`、`syncPermission()`            | 角色定义，内置 `root` 与 `user`     |
| `PamPermission`     | `pam_permission`     | `RbacPermissionContract`                                             | 权限点表（由 `PermissionInitEvent` 同步）  |
| `PamPermissionRole` | `pam_permission_role`| 多对多中间表                                                              | RBAC 角色-权限关联               |
| `PamRoleAccount`    | `pam_role_account`   | -                                                                    | 账号-角色映射                   |
| `PamToken`          | `pam_token`          | -                                                                    | 设备级 Token（md5 哈希）         |
| `PamBan`            | `pam_ban`            | -                                                                    | IP / 设备黑白名单                |
| `PamLog`            | `pam_log`            | `pam()` BelongsTo `PamAccount`                                      | 登录 / 登出日志表                |
| `SysConfig`         | `sys_config`         | `scopeApplyKey`                                                      | 配置存储（含 `namespace.group.item` 三段）|

> Policy：`PamAccountPolicy` / `PamRolePolicy`，注册于 `ServiceProvider::$policies`。

## 依赖的其他模块

| 模块         | 引用方式                                                                  | 说明                                                            |
|------------|-----------------------------------------------------------------------|---------------------------------------------------------------|
| `poppy/framework` | `PoppyServiceProvider`、`PoppyTrait`、`Helper/EnvHelper`、`Rule`、`Resp` | 基础服务提供者、Trait、验证规则、响应助手                            |
| `poppy/core`    | `PermissionInitEvent`、`RbacUserContract`、`SettingContract`、`corePermission()` | 框架级事件、RBAC 契约、设置接口、权限集合                              |
| `poppy/mgr-page`| `FormSettingLog::DAYS_FOREVER`                                        | 后台模板常量（清除登录日志兜底）                                          |
| `poppy/system` 内部耦合 | `Action\{Pam, Sso, Ban, Verification, Role, DbOptimize}` 互相调用 | 6 个 Action 互调（见 contracts.md / flows.md） |

## 被其他模块依赖

> `system` 是 Poppy 项目中**最底层**的账号 / 权限 / SSOToken / 设置基础模块，几乎所有业务模块通过 `PamAccount` 模型和 `sys_setting` / `sys_hook` 等辅助函数读取数据。

| 模块              | 引用方式                                  | 使用场景            |
|-----------------|---------------------------------------|-----------------|
| 所有业务模块（`category` / `content` / `ad` / `mgr-page` 等） | `use Poppy\System\Models\PamAccount` | 后台账号模型、鉴权中间件  |
| `mgr-page`      | `PamAccount::GUARD_BACKEND`           | 后台登录态               |
| `aliyun-push` 等 | `use Poppy\System\Jobs\NotifyJob`     | 第三方推送通知调用           |

## 边界说明（不负责的事项）

- **不做**：短信 / 邮件真实下发（只生成 `CaptchaSendEvent`，由宿主模块发）；OSS / 七牛存储上传（只提供 `FileContract` 抽象）
- **不做**：业务模块自己的权限定义（仅消费 `core` 模块的 `PermissionInitEvent` + 将权限落入本模块的 `pam_permission` 表）
- **不做**：WEB 端页面模板（纯 JSON API + 中间件编排）
- **不做**：多语言的翻译资源收集（`core/translate` 仅拉框架 `translator` 的 zh 包，业务模块自行翻译键）

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
