# app（Poppy\App）

## 模块职责

`poppy/app` 是 Poppy 框架的**第三方应用接入管理模块**。每个外部应用（接入方）在此模块中登记成一条 `SysApp` 记录，获得独立的 `appid` 与 32 位 `secret` 密钥，用于在调用系统 API 时通过 `AppSignMiddleware` 完成签名校验，从而保证 API 调用的合法性与不可抵赖性。

模块同时提供后台管理界面与权限点，可对应用进行**创建、编辑、启用/禁用、权限授权**等操作。应用可绑定到任意 `PamAccount`（PAM 账号），使该账号获得对应的应用层权限。

## 目录结构

| 目录                    | 职责                                                                 | 文件数 |
|-----------------------|--------------------------------------------------------------------|-----|
| Action                | 业务逻辑层（`App`：创建/更新/切换状态）                                              | 1   |
| Models                | Eloquent 模型：`SysApp`（带 `FilterTrait`）                                 | 1   |
| Classes               | 工具/契约类：`AppDef`（缓存键）、`Sign/DefaultAppSign`（默认签名算法实现）               | 2   |
| Classes/Sign          | 签名算法实现（`DefaultAppSign`：HMAC over kv-str）                           | 1   |
| Http/Middlewares      | `AppSignMiddleware`（应用验签中间件）                                          | 1   |
| Http/MgrPage          | 后台 MgrPage：`ListSysApp`（列表）、`FormAppEstablish`（新建/编辑表单）             | 2   |
| Http/Request/Backend  | 后台控制器：`AppController`                                                | 1   |
| Http/Validation       | `AppEstablishRequest`（FormRequest 校验）                                  | 1   |
| Http/Routes           | `backend.php`（仅后台路由，无对外 API）                                          | 1   |
| Http                  | `RouteServiceProvider`（后台路由挂载）+ `MiddlewareServiceProvider`（`py-app.sign` 别名） | 2   |
| Events                | 领域事件定义（当前仅占位 `.gitkeep`）                                              | 0   |
| Listeners             | 事件监听器（无）                                                              | 0   |
| Jobs                  | 队列任务（无）                                                                | 0   |
| Commands              | Artisan 命令（无）                                                          | 0   |
| Hooks                 | 框架扩展点（无）                                                               | 0   |
| Exceptions            | `AppNotExistsException`（应用不存在异常）                                     | 1   |
| resources/migrations  | `sys_app` 表迁移 + `permissions` 字段补充迁移                                  | 2   |
| tests/Classes         | 单元测试：`TestSign`（签名/权限）、`TestApp`（API 调用示例）                       | 2   |

## 技术栈

| 技术      | 版本/说明                                                                |
|---------|----------------------------------------------------------------------|
| PHP     | `>=7.4`（来自 `composer.json` `require.php`）                                 |
| Laravel | 基于 `poppy/core: 4.3.*`，向下兼容 Laravel 6                                  |
| 模块框架    | `poppy/core 4.3.*`（由 `framework` 间接升级）                                  |
| ORM     | Eloquent + `tucker-eric/eloquentfilter: 3.*`（通过 `FilterTrait` 引入）         |
| 认证      | 后台：Laravel Session Guard（继承 `MgrPage` 的 `BackendController`）            |
| 签名      | `md5(md5(kvStr).secret)` 自研 HMAC-like 算法                                |
| 缓存      | `sys_tag('py-app')` Redis 标签缓存（应用条目按 `appid` 缓存 1 个月）                  |
| 队列      | 不使用队列（签名为同步校验）                                                      |
| 其它依赖    | `poppy/system`（`PamAccount::kvType()`、`SysConfig::YES/NO` 常量）           |

## 路由概览

| 路由文件        | 类型    | 前缀                       | 路由数 | 说明                       |
|-------------|-------|--------------------------|-----|--------------------------|
| backend.php | 管理后台  | `/{prefix}/py-app`（由 RouteServiceProvider 设置 `middleware=backend-auth`） | 3   | 应用管理后台操作（CRUD + 状态切换） |

> 仅后台路由，**无对外 API**：所有需要走应用签名的 API 由其他模块提供，业务方在那些模块的路由组上挂载 `py-app.sign` 中间件。
> 路由前缀在 `Http/RouteServiceProvider::map()` 中以 `{prefix}/py-app` 挂载，所有路由都需通过 `backend-auth` 后台鉴权。

## 模型清单

| 模型         | 数据表        | 关键关联                                                              | 说明                                        |
|------------|------------|-------------------------------------------------------------------|-------------------------------------------|
| `SysApp`   | `sys_app`  | 无 Eloquent 关联；通过 `account_id` + `account_type` 关联到 `PamAccount` | 接入应用主表（含 `title` / `name` / `secret` / `permissions`） |

> **字段细节**（`migrations/2023_02_10_...`）：`id`（应用 ID）、`title`（名称 100）、`secret`（密钥 50）、`name`（标识 20，唯一）、`account_id`（绑定账号 ID）、`account_type`（绑定账号类型）、`is_enable`（启用 0/1）、`note`（备注 255）、`timestamps`。
> **补充字段**（`migrations/2023_06_14_...`）：`permissions`（text，`type='app'` 的权限 key 列表，逗号分隔）。

## 依赖的其他模块

| 模块                  | 引用方式                                                  | 说明                                        |
|---------------------|-------------------------------------------------------|-------------------------------------------|
| `poppy/system`      | `use Poppy\System\Models\PamAccount`                  | 绑定用户时读取账号类型（`PamAccount::kvType()`）        |
| `poppy/system`      | `use Poppy\System\Models\SysConfig`                    | 状态值 `YES`/`NO` 常量                          |
| `poppy/system`      | `use Poppy\System\Models\PamAccount`（`kvType()`）      | 后台列表显示「账号类型 + 账号 ID」                       |
| `poppy/framework`   | `AppTrait`、`Resp`、`Rule`                              | 错误响应、验证规则、Trait 助手                          |
| `poppy/core`        | `use Poppy\Core\Classes\Traits\CoreTrait`、`corePermission()`、`Permission` | 后台表单加载 `type='app'` 的权限点列表                 |
| `poppy/mgr-page`    | `ListBase`、`FormWidget`、`Grid`、`Column`、`Actions`、`Operations`、`Filter` | 后台列表与表单渲染                                |
| `poppy/mgr-page`    | `BackendController`                                    | 后台控制器基类                                   |

## 被其他模块依赖

> **本模块不主动被其他业务模块 `use`**：`App`/`DefaultAppSign`/`SysApp` 均对外暴露，但消费方主要通过**挂载 `py-app.sign` 中间件**使用签名校验能力，而不是直接调用 Action。

| 模块                              | 引用方式                                                                       | 使用场景                                              |
|---------------------------------|----------------------------------------------------------------------------|---------------------------------------------------|
| 任意业务模块（接入 API）                  | 在路由组挂载 `'middleware' => 'py-app.sign'` 别名（即 `AppSignMiddleware`）            | 对外开放 API 时强制验签                                  |
| 任意业务模块（消费应用数据）                | `use Poppy\App\Models\SysApp; SysApp::item($appid)` 或 `SysApp::check($appid, $perm)` | 读取应用信息 / 校验应用权限                                  |
| （`poppy/system` 等模块可消费 `PamAccount`） | —                                                                          | 本模块不直接耦合 system，但通过 `PamAccount::kvType()` 共享账号类型枚举 |

## 边界说明（不负责的事项）

- **不做**：对外开放的 API 接口（仅提供签名校验中间件 + 后台管理页）
- **不做**：第三方 OAuth 流程 / Token 颁发（签名为请求级 HMAC，非会话级 token）
- **不做**：应用调用日志与计量统计
- **不做**：应用市场 / 公开接入注册（应用由后台手工建立）
- **不做**：定时清理失效应用 / 自动禁用过期应用

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)