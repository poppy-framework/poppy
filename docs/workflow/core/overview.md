# core（Poppy\Core）

## 模块职责

`poppy/core` 是 Poppy 框架的基础设施模块。它**不定义业务模型**，而是提供所有上层模块共用的运行底座：模块加载与清单解析、RBAC 权限体系、Redis 缓存封装、Service/Hook 工厂，以及通用的 CLI 与工具类。本模块既是"框架级基建"也是"权限中心"，所有业务模块（system、ad、app、area 等）都通过 `CoreTrait::coreModule()` / `corePermission()` 访问它的能力。

## 目录结构

| 目录 | 职责 | 文件数 |
|---|---|---|
| Classes | 工具类、配置常量、Trait | 4 |
| Commands | Artisan 命令（py-core:*） | 4 |
| Events | 领域事件 | 1 |
| Exceptions | 模块/权限/Redis/Setting 异常 | 4 |
| Http | 中间件 ServiceProvider | 1 |
| Listeners | 框架监听器 | 1 |
| Module | 模块加载、清单解析、菜单/路径/钩子仓库 | 7 |
| Rbac | 权限模型、Contracts、Traits、Middleware、Facades | 16 |
| Redis | Redis 缓存封装、List、Persist、FieldExpired | 7 |
| Services | Service 工厂 + Form/Html/Array 契约 | 4 |
| Support | 全局 helper 函数（自动加载） | 1 |

## 技术栈

| 技术 | 版本/说明 |
|---|---|
| PHP | >= 7.4（来自 `poppy/core/composer.json`） |
| Laravel | 由 `poppy/framework` 提供，Laravel 6 兼容入口 |
| 模块框架 | `poppy/framework`（vendor/poppy/framework） |
| Redis 客户端 | `predis/predis ~1.1`（来自 composer.json require） |
| HTTP 客户端 | `guzzlehttp/guzzle ^6.3\|^7.3` |
| OpenAPI（dev） | `zircote/swagger-php ^4.11` |
| 注释解析（dev） | `doctrine/annotations ^1.14` |

## 路由概览

本模块**不提供 HTTP 路由**。它只注册一个中间件别名：

| 别名 | 中间件类 | 说明 |
|---|---|---|
| `sys-rbac` | `Poppy\Core\Rbac\Middlewares\RbacPermission` | 在 `MiddlewareServiceProvider::boot()` 中通过 `$router->aliasMiddleware()` 注册 |

## 服务容器绑定

由 `ServiceProvider` 与 `ModuleServiceProvider` / `RbacServiceProvider` 注册：

| 容器 key | 绑定类 | 别名 | 提供者 |
|---|---|---|---|
| `poppy.core.module` | `Module\ModuleManager`（singleton） | `Poppy\Core\Module\ModuleManager` | ModuleServiceProvider |
| `poppy.core.rbac` | `Rbac\Rbac`（bind） | `Poppy\Core\Rbac\Rbac` | RbacServiceProvider |
| `poppy.core.permission` | `Rbac\Permission\PermissionManager`（singleton） | `Poppy\Core\Rbac\Permission\PermissionManager` | RbacServiceProvider |

框架已发布的辅助 facade：

| Facade | 指向 | 路径 |
|---|---|---|
| `Poppy\Core\Rbac\Facades\RbacFacade` | `poppy.core.rbac` | `Rbac/Facades/RbacFacade.php` |
| `Poppy\Core\Rbac\Facades\PermissionFacade` | `poppy.core.permission` | `Rbac/Facades/PermissionFacade.php` |

## 模型清单

| 模型 | 数据表 | 说明 |
|---|---|---|
| — | — | 本模块不定义 Eloquent 模型；RBAC 关系映射交由 `poppy/system` 的 `PamAccount/PamRole/PamPermission/PamPermissionRole/PamRoleAccount` 实现，配置项见 `poppy/core/resources/config/core.php` |

## 依赖的其他模块

| 模块 | 引用方式 | 说明 |
|---|---|---|
| `poppy/framework` | `use Poppy\Framework\Application\Event` 等 | 框架基类、AppTrait、Helper |
| `poppy/system` | `use Poppy\System\Models\PamAccount`（在 `Rbac/Traits/RbacUserTrait.php`） | 账号类型常量用于判断是否清理缓存；InitToDbListener 实现 `PermissionInitEvent` 持久化 |

> 说明：`RbacUserTrait::clearCachedRoles()` 内引用了 `PamAccount::TYPE_BACKEND`，是为了在前台用户更新时跳过清理 RBAC 缓存。

## 被其他模块依赖

| 模块 | 引用方式 | 说明 |
|---|---|---|
| `poppy/system` | `use Poppy\Core\Events\PermissionInitEvent` + 监听器 `InitToDbListener` | 在 `py-core:permission init` 时把权限写库 |
| `poppy/system`（Models） | `PamAccount` 用 `RbacUserTrait`+`RbacUserContract`；`PamRole` 用 `RbacRoleTrait`+`RbacRoleContract`；`PamPermission` 用 `RbacPermissionTrait`+`RbacPermissionContract` | RBAC 模型继承 |
| `poppy/system`（Action） | `Action\Ban`、`Action\Verification` 使用 `RdsDb` | Redis 工具 |
| `poppy/system`（PamTrait） | `app('poppy.core.permission')` | 权限检查 |
| `poppy/area` | `Models\SysArea` 引用 `PyCoreDef`；`Commands\InitCommand` 引用 `RdsDb` | |
| `poppy/sms` | `Http\MgrPage\FormSettingSms` 实现 `SettingContract`；`Hooks/Sms/*` 实现 `ServiceArray` | |
| `poppy/ad` | `Hooks/FormPlaceSelect` 实现 `ServiceForm` | |
| `poppy/category` | `Hooks/FormCategorySelect` 实现 `ServiceForm` | |
| `poppy/aliyun-oss` | `Hooks/System/UploadTypeAliyun` 实现 `ServiceArray` | |
| `poppy/app` | `FormAppEstablish` 使用 `CoreTrait`+`Permission` | |

## 边界说明（不负责的事项）

- **不负责具体的 RBAC 数据模型**：权限/角色/账号表由 `poppy/system` 提供；本模块只通过 `poppy.core.rbac.*` 配置项接入。
- **不提供 HTTP 路由**：本模块仅提供 `sys-rbac` 中间件别名。
- **不暴露业务 Action / Model**：与 `poppy/system`、`poppy/ad` 等业务模块清晰分层。
- **不接管完整的 Redis 操作**：底层命令通过 `RdsNative`（predis 包装）的 `__call` 转发；本模块主要做"标签化缓存 + 持久化缓冲 + field 过期"三件事。
- **不支持 4.x → 5.0 兼容**：文件头注释与 `Support/functions.php` 中标注 `@deprecated 4.3`/`@removed 5.0` 的函数（`sys_db`、`sys_mark`）属于过渡期旧 API。

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
