# 对外契约

> 本模块**不提供 HTTP 路由**，对外契约集中在：事件、容器绑定、中间件别名、Service/Helper 公共 API、Artisan 命令。

## API 路由（api_v1.php）

| HTTP方法 | URI | 请求类/控制器 | 中间件 | 说明 |
|---|---|---|---|---|
| — | — | — | — | 无 |

## 管理后台路由（backend.php）

| HTTP方法 | URI | 请求类/控制器 | 说明 |
|---|---|---|---|
| — | — | — | 无 |

## Web 路由（web.php）

| HTTP方法 | URI | 请求类/控制器 | 说明 |
|---|---|---|---|
| — | — | — | 无 |

## 其他路由文件

无。本模块无任何 HTTP 入口。

## 中间件别名

由 `Poppy\Core\Http\MiddlewareServiceProvider::boot(Router $router)` 注册：

| 别名 | 中间件类 | 路径 | 备注 |
|---|---|---|---|
| `sys-rbac` | `Poppy\Core\Rbac\Middlewares\RbacPermission` | `src/Rbac/Middlewares/RbacPermission.php` | 业务模块在 `Kernel.php` 中 `$routeMiddleware` 引用此别名 |

另外两个中间件（`RbacRole`、`RbacAbility`）**未在 `MiddlewareServiceProvider` 中注册别名**，需要业务模块自行注册或直接通过类名引用。

## 发布的事件（本模块对外发布）

| 事件类 | 携带数据 | 触发时机 | 监听方 |
|---|---|---|---|
| `Poppy\Core\Events\PermissionInitEvent` | `Collection $permissions`（key = `type:root.group.slug`，value = `Poppy\Core\Rbac\Permission\Permission`） | `py-core:permission init` 命令处理过程中，调用 `event(new PermissionInitEvent($permissions))` 时触发 | `Poppy\System\Listeners\PermissionInit\InitToDbListener`（见 `poppy/system/src/ServiceProvider.php` `$listens`） |

## 监听的事件（本模块消费）

| 监听器类 | 监听的事件 | 业务动作 | 产生的事件/任务 |
|---|---|---|---|
| `Poppy\Core\Listeners\PoppyOptimized\ClearCacheListener` | `Poppy\Framework\Events\PoppyOptimized`（框架在配置变更时发布） | 1. `sys_tag('py-core')->clear()`；2. 删除 `framework/{classes,packages,services}.php`；3. 删除 5 份以外的 `console-*.log`；4. `opcache_reset()` | — |

## 容器绑定（Service Provider 注册）

`Poppy\Core\ServiceProvider::register()` 与各子 ServiceProvider 注册：

| 容器 key | 类型 | 绑定实现 | 别名 | 提供者 |
|---|---|---|---|---|
| `poppy.core.module` | singleton | `Module\ModuleManager` | — | `Module\ModuleServiceProvider` |
| `poppy.core.rbac` | bind | `Rbac\Rbac` | `Rbac\Rbac::class` | `Rbac\RbacServiceProvider` |
| `poppy.core.permission` | singleton | `Rbac\Permission\PermissionManager` | — | `Rbac\RbacServiceProvider` |

## Facade

| Facade 类 | 指向 | 引入方式 |
|---|---|---|
| `Poppy\Core\Rbac\Facades\RbacFacade` | `poppy.core.rbac` | `use Poppy\Core\Rbac\Facades\RbacFacade;` |
| `Poppy\Core\Rbac\Facades\PermissionFacade` | `poppy.core.permission` | `use Poppy\Core\Rbac\Facades\PermissionFacade;` |

> 注：以上 Facade 在业务模块使用前需要在 `config/app.php` 的 `aliases` 中注册（默认在 framework bootstrap 时已注册，待确认）。

## Contracts 接口

| 接口 | 文件 | 关键方法 | 实现方 |
|---|---|---|---|
| `Poppy\Core\Rbac\Contracts\RbacUserContract` | `src/Rbac/Contracts/RbacUserContract.php` | `roles()`, `hasRole()`, `capable()`, `ability()`, `attachRole()`, `detachRole()` | `poppy/system` 的 `PamAccount` 模型（实现方式：`use RbacUserTrait`） |
| `Poppy\Core\Rbac\Contracts\RbacRoleContract` | `src/Rbac/Contracts/RbacRoleContract.php` | `users()`, `perms()`, `savePermissions()`（@deprecated）, `syncPermission()`, `attachPermission()`, `detachPermission()`, `attachPermissions()`（@deprecated）, `detachPermissions()`（@deprecated） | `poppy/system` 的 `PamRole` |
| `Poppy\Core\Rbac\Contracts\RbacPermissionContract` | `src/Rbac/Contracts/RbacPermissionContract.php` | `roles()` | `poppy/system` 的 `PamPermission` |

## 公共 API（`Module\ModuleManager`）

```php
app('poppy.core.module')->enabled(): Collection        // 启用模块集合
app('poppy.core.module')->modules(): Modules           // 全量模块仓库（按 slug）
app('poppy.core.module')->get(string $name): Module
app('poppy.core.module')->has(string $name): bool
app('poppy.core.module')->path(): ModulesPath          // 聚合所有模块 path 配置
app('poppy.core.module')->menus(): ModulesMenu         // 聚合所有模块 menus 配置
app('poppy.core.module')->hooks(): ModulesHook         // 聚合所有模块 hooks 配置
app('poppy.core.module')->services(): ModulesService   // 聚合所有模块 services 配置
```

`Module` 对象支持 `ArrayAccess`、`JsonSerializable`、`directory()`、`namespace()`、`slug()`、`isEnabled()`、`validate()`。

`ModulesMenu` / `ModulesPath` 公共 API：

```php
$menus->withPermission(string $type, bool $is_full_permission = false, $pam = null): Collection
$menus->withType(string $type, array $perms = []): Collection
$paths->withPermission(string $type, bool $is_full_permission = false, $pam = null): Collection
$paths->withType(string $type, array $perms): Collection
```

## 公共 API（`Rbac\Permission\PermissionManager`）

```php
$pm = app('poppy.core.permission');

$pm->check(string $permission, string $guard): bool                 // 校验当前 guard 用户是否具备权限
$pm->repository(): PermissionRepository
$pm->permissions(): Collection                                      // 全量权限对象集合（key=id, value=Permission）
$pm->has(string $permission): bool                                  // 权限 ID 是否已注册
$pm->cachedPermissionKv(string $key = null): array|string           // 权限 ID → 描述 的 KV 缓存
$pm->cachedPermissionNames(): Collection                            // 权限 ID 列表（缓存）
$pm->clearCachedPermissionNames(): void                             // 清缓存
$pm->defaultPermissions(string $group): Collection                  // @deprecated 4.2
```

## 公共 API（`Rbac\Rbac`）

```php
$rbac = app('poppy.core.rbac');

$rbac->hasRole(string $guard, $role, bool $requireAll = false): bool
$rbac->capable(string $guard, $permission, bool $requireAll = false): bool
$rbac->ability(string $guard, $roles, $permissions, array $options = []): bool
$rbac->user(string $guard)
$rbac->routeNeedsRole(string $route, $roles, $result = null, bool $requireAll = true): mixed
$rbac->routeNeedsPermission(string $route, $permissions, $result = null, bool $requireAll = true): mixed
$rbac->routeNeedsRoleOrPermission(string $route, $roles, $permissions, $result = null, bool $requireAll = false): void
```

> 三个 `routeNeeds*` 方法使用 Laravel 已废弃的 `$router->filter()` API，请优先使用中间件别名。

## 公共 API（Redis 工具）

```php
sys_cache(?string $tag = null)                       // Laravel TaggedCache
sys_tag(string $tag, string $db = ''): RdsDb         // 标签化单例
sys_cacher(string $key, $value, int $second = 30)    // 防雪崩随机 TTL
sys_hook(string $id, array $params = [])             // 调 ServiceFactory
sys_gen_mk(string $tag, $info, bool $request = false): string  // 日志前缀
sys_info/sys_debug/sys_warning/sys_error/sys_emergency(string $tag, $info, bool $with_request = false)
```

`RdsDb` 通过 `__call` 转发任意 predis 原生命令（`get/set/setEx/del/hGet/hSet/lPush/lRange/zAdd/zRangeByScore` 等），并对 `get` 操作派发 `CacheHit/CacheMissed` 事件。

`RdsStore::seconds(string $key, $value, int $second = 30)`：固定 TTL 缓存 + 过期时间戳。
`RdsStore::at/set/unset/clear(string $key, ...)`：以 `.` 前的字符串为 tag 的 KV 存储。
`RdsStore::inLock(string $key, int $seconds): bool`：原子锁（Redis SETNX / 文件锁）。

`RdsPersist`：

```php
RdsPersist::where($table, array $where): array             // 从 Redis 取缓冲的 where 行
RdsPersist::exec(): void                                   // 刷库全部缓冲
RdsPersist::execTable(string $table): void                 // 刷库指定表
RdsPersist::update(string $table, array $where, array $update): void  // 写缓冲（更新）
RdsPersist::insert(string $table, array $values): bool     // 写缓冲（插入）
```

`RdsFieldExpired::setFieldExpireTime(string $key, $field, string $type, string $database='default', $expireTime=86400): bool` 给单个 field 设置过期（hash/set/zset 任一）。
`RdsFieldExpired::clearExpiredField(): bool` 由 `py-core:persist` 周期性触发。

`RdsList::__construct($database='default', $cacheKey, $max_length=0)`：固定长度队列，push 超长自动 `shift`。

## Service 契约

| 接口 | 方法 | 用途 | 实现示例 |
|---|---|---|---|
| `Poppy\Core\Services\Contracts\ServiceArray` | `key(): string`、`data()` | 字典/枚举类 hook | `poppy/sms/src/Hooks/Sms/SendTypeAliyun.php` 等；`poppy/aliyun-oss/src/Hooks/System/UploadTypeAliyun.php` |
| `Poppy\Core\Services\Contracts\ServiceHtml` | `output()` | HTML 片段 hook | — |
| `Poppy\Core\Services\Contracts\ServiceForm` | `builder(array $params = [])` | 表单生成 hook | `poppy/ad/src/Hooks/FormPlaceSelect.php`、`poppy/category/src/Hooks/FormCategorySelect.php` |

`Poppy\Core\Services\Factory\ServiceFactory::parse(string $id, array $params = [])` 是统一入口。

## Setting 契约

| 接口 | 方法 | 用途 |
|---|---|---|
| `Poppy\Core\Classes\Contracts\SettingContract` | `delete(string $key): bool`、`get(string $key, $default = null)`、`set(string $key, $value = ''): bool`、`clear(): void` | 业务模块通过实现此接口对接"设置项"存储 |

> 实现示例：`poppy/sms/src/Http/MgrPage/FormSettingSms.php`。

## Artisan 命令

| 命令签名 | 说明 | 调度方式 |
|---|---|---|
| `py-core:permission {do : list\|init\|menus}` | 权限管理：`list` 打印权限表；`init` 清空缓存后触发 `PermissionInitEvent`；`menus` 校验 menu yaml 中 permission 是否已注册 | 手动（CLI） |
| `py-core:persist {table : all\|<table>}` | 将 Redis 持久化缓冲刷到数据库 | 手动（CLI） |
| `py-core:inspect {type?} {--module=} {--export=} {--class_load_only} {--log}` | 静态代码规范检查（class / file / controller / action / util / perms / validation / method / env） | 手动（CLI） |
| `py-core:doc {type : openapi\|api\|cs\|cs-pf\|log}` | 生成 API 文档（openapi 或 apidoc 进程调度） | 手动（CLI） |

## 跨模块调用（本模块调用其他模块）

| 本模块调用方 | 目标模块 | 目标类 | 调用方法 | 场景 |
|---|---|---|---|---|
| `Rbac\Traits\RbacUserTrait::clearCachedRoles` | `poppy/system` | `Models\PamAccount` | `PamAccount::TYPE_BACKEND` 常量 | 判断账号类型以决定是否清理 RBAC 缓存 |
| `Events\PermissionInitEvent`（被 `py-core:permission init` 触发） | `poppy/system` | `Listeners\PermissionInit\InitToDbListener` | `handle(PermissionInitEvent $event)` | 把权限清单持久化到 `pam_permission` 表 |

## 被其他模块调用（本模块被引用）

| 调用方模块 | 调用方类 | 本模块目标类 | 调用方法 | 场景 |
|---|---|---|---|---|
| `poppy/system` | `ServiceProvider` | `Events\PermissionInitEvent` | `$listens[PermissionInitEvent::class] = [InitToDbListener::class]` | 监听权限初始化事件 |
| `poppy/system` | `Models\PamAccount` | `Rbac\Traits\RbacUserTrait` + `Contracts\RbacUserContract` | `use` Trait | 账号模型接入 RBAC |
| `poppy/system` | `Models\PamRole` | `Rbac\Traits\RbacRoleTrait` + `Contracts\RbacRoleContract` | `use` Trait | 角色模型接入 RBAC |
| `poppy/system` | `Models\PamPermission` | `Rbac\Traits\RbacPermissionTrait` + `Contracts\RbacPermissionContract` | `use` Trait | 权限模型接入 RBAC |
| `poppy/system` | `Classes\Traits\PamTrait` | `Rbac\Permission\PermissionManager` | `app('poppy.core.permission')->...` | 业务侧权限查询 |
| `poppy/system` | `Action\Ban`、`Action\Verification` | `Redis\RdsDb` | 通过 `sys_tag(...)` / `new RdsDb()` | Redis 工具 |
| `poppy/area` | `Models\SysArea` | `Classes\PyCoreDef` | `use Poppy\Core\Classes\PyCoreDef` | 缓存 key 常量 |
| `poppy/area` | `Commands\InitCommand` | `Redis\RdsDb` | 通过 `sys_tag` / `new RdsDb` | 数据初始化时落 Redis |
| `poppy/sms` | `Http\MgrPage\FormSettingSms` | `Classes\Contracts\SettingContract` | `implements` | 设置项存储 |
| `poppy/sms` | `Hooks/Sms/*`（4 个渠道） | `Services\Contracts\ServiceArray` | `implements` | SMS 渠道字典 |
| `poppy/ad` | `Hooks/FormPlaceSelect` | `Services\Contracts\ServiceForm` | `implements` | 广告位下拉 |
| `poppy/category` | `Hooks/FormCategorySelect` | `Services\Contracts\ServiceForm` | `implements` | 分类下拉 |
| `poppy/aliyun-oss` | `Hooks/System/UploadTypeAliyun` | `Services\Contracts\ServiceArray` | `implements` | OSS 上传方式字典 |
| `poppy/app` | `Http\MgrPage\FormAppEstablish` | `Classes\Traits\CoreTrait`、`Rbac\Permission\Permission` | `use` | 应用创建表单的权限校验 |

## 待确认

- `Poppy\Core\Rbac\Facades\RbacFacade` 与 `PermissionFacade` 是否在 framework 的默认 `config/app.php` aliases 中已注册（未在本模块代码中找到 `aliases` 注册代码）。
- `py-core:permission init` 是否要求 `poppy/system` 必须启用，否则 `PermissionInitEvent` 没有监听者，权限不会被持久化（流程可工作，但 `pam_permission` 表为空）。
- `py-core:persist` 是否在 framework 的 Kernel schedule 中注册了定时任务；当前 `ServiceProvider::registerSchedule()` 是空闭包。
