# 业务逻辑

## 内容主数据管理（SysContent）

### 业务规则

- **标题在同一 `type` 内唯一**（`ContentRequest::rules()`）：DB 层通过 `Rule::unique('sys_content', 'title')->where('type', ...)` 强制约束。更新时排除自己（`where('id', '!=', $id)`）。
  - 为什么：用 `type`（简易字符串分类）+ `title` 作为"内容名称"对外可读标识；若不强制唯一，前端按分类拉取时会因 slug/title 二义性导致路由定位异常。
  - 例外：本约束只在"按当前 type"生效；同一标题在不同 `type` 下允许共存。
- **正文长度上限 65500 字节**（`Action\Content::establish()` 内 `strlen($data['content'])` 校验）：超出时返回业务错误"输入内容超出最大限制, 请清理格式或者移除部分内容"，不写库。
  - 为什么：与数据库列 `text` 上限相关，避免超长写入截断/失败。
- **建立时自动绑定当前账号**（`Content::establish()`）：仅创建分支会写 `account_id = $this->pam->id`，更新分支不重写 `account_id`（`initDb` 直接调 `update`）。
  - 为什么：保留原作者；运营改稿通常不会变更作者。
- **首次保存自动设排序与启用**（`Content::establish()` 创建分支）：`list_order = $item->id`；`is_enable = SysConfig::YES`。
  - 为什么：新内容默认展示，使用自增 id 作为粗略倒序依据。
- **`create_at` 缺失时兜底为 `Carbon::now()`**（创建分支）；保留字段（区别于 `created_at`）作为"展示用创作时间"（参见迁移注释）。
- **启用/隐藏即取反**（`Content::toggle()`）：`is_enable = (int) !$this->item->is_enable`；前端仅消费 `is_enable = SysConfig::YES`（见 ApiV1 控制器）。
- **后端建立页对作者不强制校验**：`Poppy\Content\Http\Validation\ContentRequest` 仅把 `title/content/type/cat_id/keyword/description/author/create_at/thumb` 等基础字段列出，`author` 单独作为文本字段。

### 路由/分发规则

后台只有一个统一入口：

| 条件 | 处理路径 |
|------|----------|
| `is_post()` 为真 | 走 `ContentController::establish()` 的 `POST` 分支：`app(ContentRequest::class)` 校验后调用 `Action\Content::establish()` |
| `is_post()` 为假 且 URL 含 `id` | 读取 `SysContent::findOrFail($id)`，共享到 Blade 模板 `$item`；保留原 `type` |
| `is_post()` 为假 且 URL 不含 `id` | 渲染空表单，`$type = input('type')` 决定是否在表单中显示"分类"选择器 |

ApiV1 的 `lists/detail` 都接受 `cat_slug` 或 `cat_id`：

| 条件 | 处理路径 |
|------|----------|
| `cat_slug` 提供且 `cat_id` 为空 | 通过 `SysCategory::kvNameRefId($catSlug)` 反查 `cat_id` |
| `cat_id` 提供（任意来源） | 用 `cat_id` 直接过滤 |
| 都未提供 | 不过滤分类 |

### 状态机

本模块只有"启用"这一个布尔状态字段 `is_enable`，状态机极简：

```
            toggle()
DISABLED ──────────────▶ ENABLED
    ▲                          │
    └──────────────────────────┘
                toggle()
```

`SysConfig::YES`（在 system 模块定义为启用）作为前端可见的硬条件。后台写入并不要求当前内容必须 ENABLED，因为 `establish` 在编辑时只更新字段。

### 关键算法/计算

- **上下篇定位**（`ApiV1\Web\ContentController::detail()`）：
  - 拷贝 `select(['cat_id','title','id'])` 两份 query，分别为 `id > 当前.id`（取最早一条：`orderBy('id')`）与 `id < 当前.id`（取最近一条：`orderBy('id','desc')`）。
  - 若提供 `cat_id`/`cat_slug`，则在两份 query 上 `where('cat_id', $catId)`。
  - 任何一个方向未命中返回 `(object) {}`，前端据此隐藏上下篇按钮。
- **列表排序**：`orderBy('list_order', 'desc')`（取高权重在前），分页通过 `SysContent::paginationInfo()`。
- **描述摘要兜底**：`description` 为空时取 `Str::substr(strip_tags($item->content), 0, 150)`。

## 类型与分类（type / cat_id）

`type` 是模块内置的简易分类字符串，`TYPE_DEFAULT = 'default'`。新类型可通过 `config('poppy.content.types', [])` 注入，再在 `SysContent::kvType()` 中合入返回 `['type'=>'title']` 选项。`route('py-content:backend.content.establish', null, ['type' => $scope])` 的 quickButton 会把当前过滤 scope 传为 type。

`cat_id` 是真正的层级分类外键，**与 `type` 互不冲突**：type 控制后台过滤与表单"分类选择器"展示（依赖 `poppy.category`）；`cat_id` 控制前台列表/详情/上下篇归类。

## 中间件规则

| 中间件 | 应用范围 | 规则 |
|--------|----------|------|
| `backend-auth`（来自 `poppy.system`） | `{mgr-page}/py-content/...` 全部后台路由 | 需要登录后台（权限 key 由控制器 `$permission` 与 `SysContentPolicy::$permissionMap` 共同约束） |
| `api-sign`（来自 `poppy.system`） | `api_v1/content/...` 全部 API 路由 | 请求签名校验（不强制登录，沿用 `JwtApiController` 可选 JWT 上下文） |

控制器级权限：

- `Backend\ContentController::$permission = ['global' => 'backend:py-content.content.index']`（覆盖 index/establish/delete/toggle 这 4 个方法）。
- `Models\Policies\SysContentPolicy::$permissionMap['edit'] = 'backend:py-content.content.manage'`。
- 路由名 `py-content:backend.content.{index,establish,delete,toggle}` 与菜单中 `permission: backend:py-content.content.manage` 共同形成权限校验。

## 定时任务 / Artisan 命令

| 命令/任务 | 调度频率 | 业务动作 |
|-----------|----------|----------|
| — | — | 本模块未注册任何 Artisan 命令或定时任务；未派发任何 Job |

> 详见 [contracts.md](contracts.md) 中"Artisan 命令"与"队列任务"两节，均为"无"。
