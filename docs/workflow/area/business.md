# 业务逻辑

## 地区树（行政区划）

### 业务规则

- **树形结构（parent_id 自引用）**：`sys_area` 通过 `parent_id` 形成多级树；`top_parent_id` 表示祖级根节点 ID，省级为 `parent_id = 0`。**为什么**：地区本质是树，用自引用外键比建邻接表或闭包表更轻量，且只三级深度（市/区/县在业务查询中并不要求快速反查任意祖先，靠 `children` 冗余字段兜底）。
- **层级（level）枚举**：`SysArea::LEVEL_PROVINCE = 1`、`LEVEL_CITY = 2`、`LEVEL_COUNTY = 4`。**注意**：等级 3 故意保留空缺（=4 而非 3 是为了位运算兼容，但本模块未启用位运算）。`Action\Area::level()` 仅维护 1（省）和 2（市）两级，区/县的 level 不在 fix 中显式计算。
- **`code` 字段语义**：12 位字符串，遵循 GB/T 2260 行政区划代码。末位规则——省 `endsWith 0000000000`、市 `endsWith 00000000`、区/县 `endsWith 000000`。**为什么**：利用字符串尾匹配可以在不维护显式 `level` 字段的情况下推断层级，给 `InitCommand` 提供了"灌完后用 whereRaw right(code, N) 取数据"的简单实现路径。
- **`children` 冗余字段**：`children` 存当前节点的全部后代 ID（逗号分隔），由 `Action\Area::fix()` 维护。**为什么**：方便后台一次拉出整棵子树做筛选，避免递归查询。
- **`has_child` 冗余字段**：`initCity/initCounty` 时打标，`Action\Area::hasChild()` 修复。**为什么**：前端级联选择器需要先判断节点是否可展开，避免无谓请求。
- **`kvProvince` / `kvCity` 使用身份证前缀匹配**：`SysArea::kvProvince($code)` 取 `left(code, 2)`、`SysArea::kvCity($code)` 取 `left(code, 4)`。**为什么**：身份证前 6 位即出生地行政区划码，模块对外提供该 KV 接口用于身份证解析场景。
- **`country()` 国家数据**：来源于静态 PHP 文件 `resources/def/country.php`（含 en / iso / py / zh / cty 字段），按 `py` 拼音首字母排序并大写化。**为什么**：国家码变动频次低、且需要拼音排序、ISO 与中文名同时返回，写死在文件里最稳。

### 路由/分发规则

| 条件                | 处理路径                                                    |
|-------------------|---------------------------------------------------------|
| 前端调用 `area/code` | `ApiV1\AreaController::code()` 直接查表 → `UtilHelper::genTree` 组装树（无缓存） |
| 前端调用 `area/country` | `ApiV1\AreaController::country()` 调用 `SysArea::country()`（命中 30 天缓存）   |
| 后台列表 `backend/area/` | `ContentController::index()` 渲染 MgrPage Grid + Filter       |
| 后台编辑 `backend/area/establish/{id?}` | `ContentController::establish()` 渲染 MgrPage 表单 + Action 持久化 |
| 后台删除 `backend/area/delete/{id?}` | `ContentController::delete()` 调用 `Action\Area::delete()`    |
| 后台修复 `backend/area/fix` | `ContentController::fix()` 调用 `Action\Area::fixHandle()` 走分页批处理 |

### 状态机

地区无显式状态字段（启用/停用未在表中建模），但 fix 流程存在推进点：

```
INIT_DB → HAS_CHILD=0 → (建立子节点) → HAS_CHILD=1
                          ↘ fix() 重算 children / top_parent_id / level
```

### 关键算法/计算

- **`Action\Area::level($id)`**：根据 `parent_id == 0` 设省级 level=1；其父为省级则设市级 level=2。区/县不在此处计算（依赖初始化阶段写入）。
- **`Action\Area::fix($id)`**：先用 `getChildren()` 递归收集所有后代 ID 列表，存到 `children`；再由 `topParentId()` 取祖父 ID 写到 `top_parent_id`。
- **`Action\Area::getChildren($id)`**：基于 `matchKv`（`id => parent_id` 映射，缓存 10 秒）反向查询键来递归聚合后代。**为什么用反向查询而非 `where parent_id = $id`**：避免一次只查一层的 N+1。
- **`Action\Area::fixHandle()`**：分页（section=100）扫描 `sys_area` 全表，每条记录跑 `fix()` + `hasChild()` + `level()`。**为什么**：数据规模数千到上万条，单事务批量 UPDATE 会锁表太久，分段修复更友好。

## 国别（Country）

### 业务规则

- **静态数据 + 长缓存**：`resources/def/country.php` 是 commit 进仓库的静态文件，`SysArea::country()` 用 `sys_tag('py-area')->remember(…, MIN_ONE_MONTH * 60, …)` 缓存 30 天。**为什么**：国家数据极少变化，长缓存+运维期 `PoppyOptimized` 触发清缓存即可保持新鲜度。
- **拼音排序**：`country()` 按 `py` 字段排序并 `strtoupper`，便于前端做 A-Z 字母索引。**为什么**：前端按拼音分组展示比按 ISO 码或中文更符合用户直觉。
- **`kvCountry($code)`**：在 `country()` 之上构造 `iso => zh` 映射，支持根据 ISO 二字码查中文名。

## 缓存策略

### 业务规则

- **统一标签缓存**：`sys_tag('py-area')` 作为命名空间，所有本模块缓存（`tree-level-2`、`kv-province`、`kv-city`、`kv-area`、`kv-country`、`kv-area`、`kv-province-id`、`kv-city-id-*`、`cascader-*`、`match_id_pid`）都挂此标签。**为什么**：监听 `PoppyOptimized` 时 `clear()` 一行清空整个标签，避免按 key 列表逐个 `del()`。
- **`matchKv` 短 TTL（10 秒）**：`Action\Area::matchKv()` 用 `remember(…, 10, …)` 缓存 `id => parent_id` 映射。**为什么**：CRUD 期间需要立即看到 `parent_id` 变更，但又不能每次都查全表；10 秒过期 + 显式 `matchKv(true)` 主动失效是折中。
- **PoppyOptimized 触发整标签清空**：`Listeners\PoppyOptimized\ClearCacheListener::handle()` 调用 `sys_tag('py-area')->clear()`。**为什么**：框架发布 `PoppyOptimized` 事件代表模块/缓存发生重建（典型时机：artisan 优化、部署、缓存预热），此时模块所有长缓存都需要失效——比列出所有 key 更安全。

## 数据初始化

### 业务规则

- **三阶段导入**：`py-area:init` 依次执行 `initProvince → initCity → initCounty`，每一阶段都先校验数据是否已存在（`where code exists` / `where parent_id exists`），避免重复灌入。**为什么**：命令可重复运行（幂等），允许增量数据源更新后再次执行。
- **Redis Hash 作为父子映射中间表**：`initProvince` 写 `py-area:import-province`（code → id）、`initCity` 写 `py-area:import-city`（code → id）；后续步骤通过 `hGet` 拿父节点 ID。**为什么**：省/市节点的自增 ID 在批量插入后才能确定，用 Redis Hash 作为一次性 O(1) 查表比每条 SQL 查询高效。
- **末位清零后导入完成**：`handle()` 末尾 `rds->del([ckProvince(), ckCity()])` 清理这两个临时 Hash。**为什么**：临时数据不应长留 Redis 干扰其他业务查询。

## 定时任务 / Artisan 命令

| 命令/任务                              | 调度频率     | 业务动作                                                                 |
|------------------------------------|----------|----------------------------------------------------------------------|
| `py-area:init`（`InitCommand`）         | 手动触发     | 从 `resources/def/*.json` 灌入省/市/区/县到 `sys_area`，并写 Redis Hash 临时映射，结束后清缓存   |
| `backend/area/fix`（`Action\Area::fixHandle`） | 手动触发（后台按钮） | 分页扫描 `sys_area`，逐条 `fix()` + `hasChild()` + `level()` 重算 children / top_parent_id / level / has_child |

## 中间件规则

| 中间件             | 应用范围                            | 规则                                                                  |
|-----------------|---------------------------------|---------------------------------------------------------------------|
| `api-sign`      | `/api_v1/area/*`（api_v1.php）    | 框架统一的 API 签名校验（外部请求需带签名字段），具体签名算法由 poppy 框架定义              |
| `backend-auth`  | `/backend/area/*`（backend.php）   | 后台登录态校验；`ContentController` 构造函数额外声明 `permission = 'backend:py-area.main.manage'`，走 PAM 权限点 |