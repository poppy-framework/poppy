# 业务逻辑

## 多级分类管理

### 业务规则

- **按 `type` 分组的通用分类字典**：`SysCategory.type` 是分类分组的"业务维度"（如商品分类、文章分类等），同一维度内有自己的树、排序和唯一性约束；不同 `type` 之间互不影响。这条规则的意义在于用一张表承载多个业务线的分类，避免每个模块建一套分类表——这也是本模块能被 `poppy/content` 等模块直接 `use` 引用的原因。
- **树形靠 `parent_id` 邻接表自引用**：模型只存 `parent_id`，层级通过 `TreeHelper` 在读取时现算。换言之，本模块不持久化 `level`、不存路径枚举（`a/b/c`），树深理论上无限（实际由 UI 决定）。
- **同 `type` 内 `(title, name)` 唯一**：校验在 `CategoryEstablishRequest`，`unique` 规则用 `where('type', $type)` 限定 scope，且编辑模式 `$query->where('id', '!=', $id)` 排除自身。`title` 用于展示，`name` 用于反向引用 KV（程序员友好的别名，必须形如 `default-help`）。
- **`top_id` 是预留字段**：模型有 `top_id`，但 Action 层在 `establish/update` 时未写入它（见边界）。它的存在意味着历史/外部流程期望每个节点都能直接定位到所属的树根（待确认写入责任方）。
- **`is_enable` 软启用**：迁移在 2023-07 增加 `is_enable`。Action 的 `status($id, $status)` 是"启用/停用"开关，二者都用 `SysConfig::YES/NO`。需要强调：**停用不等于删除**，树接口 `SysCategory::tree()` 仅在 `is_enable = SysConfig::ENABLE` 时返回数据，停用项不出现在选择器里。
- **`list_order` 整数序号**：`list_order` 是同 `type` 全局自增序号，不区分父节点——即**所有兄弟节点跨层级排成一个线性序列**。这点决定了排序动作是一次"占位+平移"的整体调整，而不是局部调换。
- **删除前的协调机会**：相关事件见 [contracts.md](contracts.md) 中 `SysCategoryBeforeDeleteEvent`。本模块当前**不内置任何 Listener**，删除是同步前置触发事件后立刻执行 `whereKey($id)->delete()`，不做级联，也不阻止删除——是否阻止/级联完全交由挂监听器的业务模块。

### 路由/分发规则

| 条件                                       | 处理路径                                                                  |
|------------------------------------------|---------------------------------------------------------------------|
| 前端 H5 / App 调用排序                          | `POST api_v1/category/category/sort`（`api-sign` 中间件，需要签名）             |
| 后台列表页/查询                                  | `GET {prefix}/py-category/category`（`backend-auth`）                |
| 后台新建/编辑                                   | `GET/POST {prefix}/py-category/category/establish/{id?}`            |
| 后台单条删除                                    | `DELETE/POST {prefix}/py-category/category/delete/{id}`              |
| 后台启用/停用                                   | `GET/POST {prefix}/py-category/category/status/{id}/{status}`        |

> 注：路由都用 `any()` 注册，HTTP 方法在 MgrPage 中由前端决定，后端允许任意动词。

### 排序状态机

排序动作以"目标位置 `aim_id` + 相对位置 `compare ∈ {gt, lt}`"为输入，最终统一映射到 `list_order` 序列：

```
[before]
id: 1(lo=1)  2(lo=2)  3(lo=3)
action: sort(id=1, compare=lt, aim_id=3)
   => 所有 lo >= 3 的项 lo+1 (即 3+1=4)
   => id=1 的 lo 设为 3

[after]
id: 1(lo=3)  2(lo=2)  3(lo=4)
```

```
[before]
id: 1(lo=1)  2(lo=2)  3(lo=3)
action: sort(id=1, compare=gt, aim_id=3)
   => 所有 lo <= 3 的项 lo-1 (即 1,2,3 -> 0,1,2)
   => id=1 的 lo 设为 3
   => 检测到全局 min(lo) = 0 < 1，整体 +1 修正（0,1,2 -> 1,2,3）

[after]
id: 1(lo=3)  2(lo=2)  3(lo=3)   ← 会触发 normalize
最终: id: 1(lo=3)  2(lo=2)  3(lo=4) ← normalize 后
```

关键解释：
- **gt** 表示"我要排在 `aim_id` 之后"（id 更大 / 位置更靠后）。
- **lt** 表示"我要排在 `aim_id` 之前"（id 更小 / 位置更靠前）。
- `gt` 分支有 normalize 兜底，因为递减可能跌破 1；`lt` 分支只会增加，不会越界。
- 校验阶段：`type`、`id`、`aim_id` 必填；`compare` 必须为 `gt/lt`。任一缺失返回业务错误（`setError`）。
- 排序只调整 `list_order`，不改 `parent_id`，所以**只在同层级内重排**或跨层级维持原有父子关系，最终呈现的"树顺序"取决于 `parent_id` 与 `list_order` 的组合（详见下文"关键算法"）。

### 关键算法/计算

- **`SysCategory::tree($type, $replace_space=false)`**：
  - 拉取启用项（`is_enable = SysConfig::ENABLE`），`orderBy('list_order', 'desc')`（注意是倒序，取较高值在前）。
  - 通过 `Poppy\Framework\Helper\TreeHelper::init($rows, 'id', 'parent_id', 'title')` 构建邻接表，再 `getTreeArray(0)` 返回根节点列表。
  - `replace_space=true` 时替换标题中的空格（用于 MgrPage 选择器的占位）。
- **`SysCategory::kvNameRefId($key)`**：先读 Redis Hash `py-category:name-ref-key`，空则全表回填（`type-name → id`）。这是"`name` 别名反向引用"路径，关键调用方是 Hook `FormCategorySelect` 和测试。
- **`SysCategory::kvTitle($id)`** / **`kvSlug($id)`** / **`kvTypeSlug($id)`**：分别缓存 `id → title`、`id → name`、`id → type-name`，写操作（`establish/delete`）后会清空。
- **`list_order` 初值**：新建分类时 `list_order = $item->id`（即"按 id 大小给定初始顺序"），与排序算法配合可保证初始序列一致。
- **`name` 别名规范**：可空，但若填写则参与 `(type, name)` 唯一约束并作为"程序可读 key"。

### PyCategoryDef 常量含义

`Poppy\Category\Classes\PyCategoryDef` 仅定义 Redis Hash 的缓存键名（与 Tag `py-category` 配合使用）：

| 方法                  | 缓存键字符串           | 数据形状                       | 写入方                                          |
|---------------------|------------------|----------------------------|----------------------------------------------|
| `ckNameRefKey()`    | `name-ref-key`   | key=`type-name`, value=`id` | `SysCategory::kvNameRefId()` 首次缓存回填；`Category::establish/delete` 删除 |
| `ckIdRefTitle()`    | `id-ref-title`   | key=`id`, value=`title`     | `SysCategory::kvTitle()`；`Category::establish/delete` 删除  |
| `ckIdRefName()`     | `id-ref-name`    | key=`id`, value=`name`      | `SysCategory::kvSlug/kvTypeSlug()`；`Category::establish/delete` 删除 |
| `ckIdRefTypeName()` | `id-ref-type-name` | key=`id`, value=`type-name` | 常量在模型里**当前未被使用**——仅预留键名（待确认是否已被废弃/未启用）             |

> 所有缓存都属于"读穿透"，命中即返回；任何写操作统一通过 `Category::clearRefCache()` 清空四个键。

### Hook（MgrPage 服务表单）

- **`poppy.category.form_category_select`** → `Poppy\Category\Hooks\FormCategorySelect::builder($params)`：
  - 入参：`name`（表单字段名）、`type`（分类分组）、`value`（默认值，可选）、`options`（额外下拉选项，可选）。
  - 输出：调用 `SysCategory::tree($type)`，渲染 `layui` 风格 select 组件。
  - 这意味着后台表单若需要分类下拉，无需直接 `use SysCategory`，按 Poppy 的 Hook 协议注册即可被 MgrPage 渲染层引用。

## 状态机

本模块无显式状态字段。`is_enable` 是二元开关（启用/停用），没有更细粒度的"草稿/审核/上架"等状态机：

ENABLED ⇄ DISABLED（通过 `status($id, $status)` 切换）

删除是终态，独立于以上状态机——停用的分类仍可被显式删除。

## 定时任务 / Artisan 命令

无。本模块未注册任何 Command、JOB、定时调度；所有操作均按需同步触发。

## 中间件规则

| 中间件             | 应用范围                                  | 规则                                          |
|-----------------|---------------------------------------|---------------------------------------------|
| `backend-auth`  | `backend.php` 整组路由                  | 后台会话/权限校验（宿主项目策略）                       |
| `api-sign`      | `api_v1.php` 整组路由                   | API 签名校验（宿主项目策略），排序接口对外仅暴露给签名校验通过的客户端 |

## 待确认

- `top_id` 列是否应由本模块 Action 在 `establish()`/`sort()` 写入；当前代码未维护该列（仅迁移建表）。
- `PyCategoryDef::ckIdRefTypeName()` 常量已定义但模型未使用，是否计划启用？
- `SysCategoryBeforeDeleteEvent` 当前**无任何内置/外部监听器**，是否为有意预留（事件名暗示"删除前"语义，但当前删除流程是"事件触发后立即物理删除"）？
- 是否需要扩展 `establish()` 以禁止将分类挂到非自身 `type` 的父节点下（可能产生跨 `type` 树）？
- `{prefix}` 在宿主项目中的实际值（与 `RouteServiceProvider::$prefix` 相关）。
