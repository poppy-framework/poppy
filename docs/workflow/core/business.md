# 业务逻辑

> 本模块是 Poppy 的"基础设施层"。下面描述的是**代码读不出来**的规则与触发条件。

## 模块清单（manifest）加载

### 业务规则

- **清单驱动**：每个 `poppy/{slug}` 模块通过 `manifest.json` + `configurations/*.yaml` 自我描述，由 `Module\Module` 包装为支持 `ArrayAccess` 的对象。
- **入口聚合**：`ModuleManager::modules()` 接收 `app('poppy')->enabled()` 的 slug 集合，按 slug 一次性把每个模块的 manifest 配置聚合为 `Modules` Repository。
- **缓存期**：模块清单写入 `py-core` 标签下的 Redis key `module-module`（通过 `PyCoreDef::ckModule('module')`），TTL 为 `MIN_HALF_DAY * 60` 秒 = 30 天。**为什么这么长**：manifest 在生产环境基本不变，且一次扫描 N 个 yaml 文件 I/O 较重；长 TTL 配合 `py-core:permission init` 等命令显式 `del` 强制刷新。
- **启用判定**：每个模块在 manifest 之外还会写入 `enabled = app('poppy')->isEnabled($slug)`，使得仓库条目既包含配置又包含运行态。
- **校验规则**：`Module::validate()` 检查必须包含 `name / identification / description / authors` 四项。这是发布前 `py-core:inspect` 静态检查的基础。

### 路由/分发规则

| 条件 | 处理路径 |
|---|---|
| 模块目录存在 `manifest.json` | 视为合法模块，加入仓库 |
| `manifest.json` 缺失 | 跳过（不抛错） |
| `configurations/module.yaml` 之外的 yaml 文件 | 全部并入模块属性（key=文件名，value=yaml 内容） |
| `configurations/module.yaml` | 不读取（保留给框架自身语义） |

### 关键算法/计算

- **聚合菜单（`ModulesMenu::withPermission`）**：根据用户权限过滤菜单——`$is_full_permission=true` 时全量返回；否则只保留 `$pam->capable($link['permission'])` 命中的菜单项，否则把无权限的菜单项裁掉。三级菜单需要 group→link→children 三层全部走通过滤才保留。
- **聚合路由路径（`ModulesPath::withPermission`）**：与菜单同构，按 type（账号类型）+ permission 双重过滤。

## RBAC 权限体系

### 业务规则

- **三层模型**：User（账号）↔ Role（角色）多对多 ↔ Permission（权限）多对多。本模块不实现具体表，只提供 `RbacUserContract` / `RbacRoleContract` / `RbacPermissionContract` 三个接口与对应的 Trait（`RbacUserTrait` / `RbacRoleTrait` / `RbacPermissionTrait`）。
- **模型类由配置注入**：`resources/config/core.php` 中的 `poppy.core.rbac.*` 指定 `role/account/role_account/permission/role_permission` 五个具体类及外键。这样业务模块（如 `poppy/system`）可以替换为自己的模型实现。
- **角色优先匹配（Entrust 风格）**：`RbacUserTrait::capable()` 不直接查 user_permission 中间表，而是先 `cachedRoles()`，再遍历 `role.cachedPermissions()`；权限匹配使用 `Str::is()` 支持通配符（如 `module:*`）。
- **缓存分层**：
  - 用户角色缓存 key：`roles-user-{id}`（标签 `py-core-rbac`），TTL = `config('cache.ttl')`
  - 角色权限缓存 key：`permission-role-{id}`（标签 `py-core-rbac`），TTL 同上
  - 全部权限清单缓存 key：`permission-names` / `permission-kv`（标签 `py-core`），TTL = `config('cache.ttl', 600)`
- **缓存失效时机**：
  - User `saved/deleted/restored` → `clearCachedRoles` 清除 `roles-user-*`
  - Role `saved/deleted/restored` → `clearCachedPermissions` 清除 `permission-role-*`
  - Role `syncPermission/attachPermission/detachPermission` → `clearCachedPivotPermissions(role_id)` 仅清该角色 key
  - **前台用户优化**：`PamAccount::TYPE_BACKEND` 之外的用户 `saved` 不会清缓存（避免前台用户编辑密码导致全量 RBAC 失效）
- **超级管理员**：当 `is_full_permission=true` 透传给 `ModulesMenu::withPermission()` 时，跳过所有 capability 检查（全菜单可见）。
- **Blade 指令**：`@role/@endrole`、`@permission/@endpermission`、`@ability/@endability` 由 `RbacServiceProvider::bootRbacBladeDirectives()` 注册，转发到 `\Rbac::hasRole/capable/ability`。这些指令在 Laravel 6 上只是字符串生成，调用方法不会自动注入 guard，使用时需手动传入 guard。

### 路由/分发规则（中间件）

| 中间件 | 参数 | 行为 |
|---|---|---|
| `RbacPermission`（别名 `sys-rbac`） | 从 controller 的静态属性 `$permission` 读取 | 先看 method 级权限（`$permission[$method]`），再看 `global`；任一命中且 `user->capable()` 通过即放行；否则 `Resp::error("用户无 [权限名] 权限, 无法访问")` |
| `RbacRole` | 角色名 `editor\|admin`（`\|` 分隔） | 不通过 → `abort(403)`；通过 → 放行 |
| `RbacAbility` | 角色 + 权限（`\|` 分隔） + `validateAll` | 调 `user->ability()`，按 `validate_all` 取"任一"或"全部"，失败 `abort(403)` |

> `RbacPermission` 是基于 controller 静态属性的"约定式"鉴权；`RbacRole` / `RbacAbility` 是基于路由参数的"显式"鉴权。`sys-rbac` 在多数管理后台控制器中使用。

### 关键算法/计算

- **权限 ID 拼接**（`PermissionManager::permissions()`）：每个 permission 最终的 cache key 是 `"{type}:{root}.{group}.{slug}"`。`type` 来自 root key 的 `:` 前缀（如 `mgr:home.index` 中的 `mgr`），便于区分前后台权限。
- **权限清单缓存**：第一次调用 `cachedPermissionNames()` 会把"全量权限 key"加载到 Redis；后续任何 `PermissionCommand::init()`、Redis 失效等场景都需要 `clearCachedPermissionNames()` 主动清理。

## Redis 缓存

### 业务规则

- **标签化（tagged cache）**：`RdsNative` 内部把每个 key 前缀化（`tag:{tag}:`），并通过 `Cache::tags($tag)` 提供 Laravel 原生标签缓存能力。`sys_tag('py-core')` 返回的是绑定了 `tag:py-core` 的 `RdsDb` 单例。
- **缓存事件桥接**：`RdsDb::__call('get', ...)` 会在 miss/hit 时分别派发 `Illuminate\Cache\Events\CacheMissed` / `CacheHit` 事件，从而让 Laravel Telescope / APM 工具能观察到。
- **预置标签**：
  - `py-core` — 框架核心缓存（模块清单、权限清单、cacher）
  - `py-core-rbac` — RBAC 用户/角色权限缓存
  - `py-core-persist` — Redis 持久化缓冲（`RdsPersist` 用）
- **RdsDb 单例**：相同 `(db, tag)` 只生成一份 `RdsNative`，避免重复建立 predis 连接。析构时调用 `disconnect()`，但全局缓存持有的引用使连接保持活跃。

### 路由/分发规则

| Helper | 用途 | 备注 |
|---|---|---|
| `sys_cache($tag)` | Laravel `TaggedCache` 包装 | tag 含 `\` 时取首段作为标签名 |
| `sys_tag($tag, $db)` | `RdsDb::instance($db, 'tag:' . $tag)` | 单例 |
| `sys_cacher($key, $value, $sec=30)` | "随机秒数"防雪崩缓存 | 写入 `py-core:cacher-{key}`，含 `expired` 时间戳 |
| `sys_hook($id, $params)` | 调用 ServiceFactory | 见下节 |
| `sys_db()` | **已废弃**（4.3 deprecated / 5.0 removed） | 占位返回 `[]` |
| `sys_mark()` | **已废弃**（4.1 deprecated / 5.0 removed） | 调试标识符 |
| `sys_gen_mk/info/debug/warning/error/emergency` | 日志前缀 + JSON 序列化 | 4.1 起统一用 `gen_mk` |

### 关键算法/计算

- **持久化缓冲（`RdsPersist`）**：把高频写入批量落到 Redis 哈希/list，再用 `py-core:persist {table|all}` 命令异步刷库。
  - `update(table, where, update)` → 写入 `persist:{table}_update`（hash），update 中字段支持 `+/-/.//>/<` 运算符。
  - `insert(table, values)` → RPUSH 到 `persist:{table}_insert`（list）。
  - `exec()` / `execTable()` 把 Redis 中的数据批量 `DB::table($table)->insert/update`，完成后 `del` 缓冲 key。
- **Field 过期（`RdsFieldExpired`）**：对 hash/set/zset 的单个 field 设置过期时间——把 `(database, key, field, type, expiredAt)` 写入 ZSET `rds-key-field-expired`；扫描时 `zRangeByScore(0, now())` 取出到期 field，分组后调用 `hdel/srem/zrem`。
- **原子锁（`RdsStore::inLock`）**：根据 `cache.default` 自动选择 Redis `SET NX EX` 或文件锁（`Cache::forever`）。

## Service / Hook 工厂

### 业务规则

- **三段式契约**：业务模块实现 `ServiceArray` / `ServiceHtml` / `ServiceForm` 三种接口中的一种，框架根据 manifest 中 `services[].type` 选择对应的 `parse*` 解析器（`parseArray` / `parseSimpleArray` / `parseHtml` / `parseForm`）。
- **查找流程**：`sys_hook('hook-id', $params)` → `ServiceFactory::parse` → 先查 `coreModule()->services()->get($id)` 拿到 `type`，再查 `coreModule()->hooks()->get($id)` 拿到具体类名数组。
- **不存在处理**：服务未注册 → 返回 `null`（不抛错），调用方需自行兜底。
- **Form 兜底**：当 `$builder` 类不存在或未实现 `ServiceForm` 时，自动回退到 `Form::text($name, $value, $options)`，保证页面不会白屏。

### 路由/分发规则

| `services[].type` | 解析方法 | 输出 | 典型用途 |
|---|---|---|---|
| `array` | `parseArray` | `array` | 字典/枚举（如 SMS 渠道、对象存储类型） |
| `simple_array` | `parseSimpleArray` | `array<string>` | 纯类名列表（不实例化） |
| `html` | `parseHtml` | `string`（拼接） | 多片段 HTML 注入 |
| `form` | `parseForm` | `HtmlString\|mixed` | 表单生成（builder 类实现） |

> 解析方法名由 `Str::studly($type)` 生成，例如 `simple_array` → `parseSimpleArray`。

## 框架优化（PoppyOptimized）

### 业务规则

- `Poppy\Framework\Events\PoppyOptimized` 是框架在 opcode 缓存重建、env 变化等场景下发出的"全量失效"事件。
- 本模块的 `ClearCacheListener::handle()` 接到事件后会：
  1. `sys_tag('py-core')->clear()` 清空所有 py-core 标签缓存
  2. 删除 `framework/classes.php`、`framework/packages.php`、`framework/services.php` 三个由框架预编译的反射缓存文件
  3. 删除超过 5 个以外的 `console-*.log`（保留最近 5 份）
  4. 调用 `opcache_reset()`（如果可用）

## 命令（py-core:*）调度

| 命令 | 调度频率 | 业务动作 |
|---|---|---|
| `py-core:permission {do=list|init|menus}` | 手动 | `init` 时清空 `module-module`、清空 `py-core-rbac` 全部、清缓存、触发 `PermissionInitEvent`；`menus` 子命令校验 menu yaml 中引用的 permission 是否都已注册 |
| `py-core:persist {table=all|<table>}` | 手动 / schedule（待确认） | 调用 `RdsPersist::exec()`/`execTable()` 把 Redis 缓冲刷到 DB |
| `py-core:inspect {type} {--module=} {--export=}` | 手动 | 代码规范检查（class/file/controller/action/util/perms/validation/method/env） |
| `py-core:doc {type=openapi|api|cs|cs-pf|log}` | 手动 | openapi 扫描或 apidoc 进程调度 |

## 中间件规则

| 中间件 | 应用范围 | 规则 |
|---|---|---|
| `sys-rbac`（=`RbacPermission`） | 后台 manager 控制器（约定 `static $permission = [...]`） | 未配置 → 放行；配置 method 权限 → user 必须 capable；未配 method 但配 global → user 必须 capable(global)；都不通过返回 `Resp::error` |

## 待确认

- `py-core:persist` 是否在 framework Kernel 中注册了 schedule？目前 `ServiceProvider::registerSchedule()` 是空闭包，未发现显式 cron。
- `py-core:permission init` 触发 `PermissionInitEvent` 后，是否依赖 `poppy/system` 的 `InitToDbListener` 必须可用？若 system 模块未启用，权限清单只会存在于内存中（`cachedPermissionNames`），不会持久化到 `pam_permission` 表。
- `Rbac::routeNeedsRole/NeedsPermission/NeedsRoleOrPermission` 方法使用 Laravel 6 已废弃的 `$router->filter()` API，在 Laravel 6+ 上是否仍生效需验证。
- `RbacRole` / `RbacAbility` 中间件依赖 `$request->user()->hasRole()` 等方法，当前默认 guard 是否一定注入？
