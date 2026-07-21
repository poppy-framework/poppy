# content（Poppy\Content）

> 本文档由 `poppy/content` 模块代码扫描自动生成。模块主要承载后端文章（CMS）管理与前端内容读取。
> 模块路径：`poppy/content/` ｜ 命名空间：`Poppy\Content\` ｜ 标识：`poppy.content` ｜ order `1009`

## 模块职责

`content` 模块负责"文章/单页类内容"的完整生命周期：后端**增删改查 + 启用切换**，前端（API）**列表分页 + 详情（含上下篇导航）**。

适用范围：

- 单篇内容（标题、缩略图、富文本正文、作者、SEO 关键词/描述、创作时间）。
- 内容归类：使用可空 `cat_id` 关联外部 `poppy.category`，并叠加字符串简易分类 `type`（默认 `default`）。
- 内容启用/隐藏的运营动作。
- 前端只读消费 `is_enable = 1` 的内容。

**不负责**：

- 内容分类本身的维护（见 `poppy.category`）。
- 账号/认证/权限基础设施（见 `poppy.system`）。
- 富文本上传与文件存储（由 `py-mgr-page` 表单组件 `thumb/editor` 间接调用 `py-system` 上传，见 `Poppy\System\Http\Request\ApiV1\UploadController`）。

## 目录结构

| 目录 | 职责 | 文件数 |
|------|------|-------|
| `src/Action/` | 业务逻辑层（`Content` Action） | 1 |
| `src/Models/` | Eloquent 模型 + Policies | 2（1 个模型 + 1 个策略） |
| `src/Http/MgrPage/` | 后台 Grid/列表页配置 | 1 |
| `src/Http/Request/` | 请求类、控制器、响应体（含 API v1 与 Backend 两组） | 6 |
| `src/Http/Request/ApiV1/Web/` | 前端 API 控制器 | 1 |
| `src/Http/Request/Backend/` | 后台控制器 | 1 |
| `src/Http/Request/ApiV1/Web/Content/` | API v1 请求/响应体 | 4（2 Request + 2 ResponseBody） |
| `src/Http/Validation/` | 后台校验类 `ContentRequest` | 1 |
| `src/Http/Routes/` | 路由定义文件 | 2（`api_v1.php`、`backend.php`） |
| `src/Http/RouteServiceProvider.php` | 模块级路由服务提供者 | 1 |
| `src/ServiceProvider.php` | 模块入口（仅注册 RouteServiceProvider） | 1 |
| `src/Hooks/` | 框架扩展点（菜单/权限等） | 0（目录为空） |
| `configurations/` | 模块配置（菜单/权限/模块元信息） | 3 |
| `resources/migrations/` | 数据库迁移 | 3 |
| `resources/views/` | Blade 视图（后台编辑页） | 1 |
| `resources/lang/zh/` | 中文语言包（SEO 键与 Util 键） | 2 |
| `tests/Action/` | Action 单元测试 | 1 |
| `composer.json` | Composer 描述 | 1 |
| `manifest.json` | 模块 manifest | 1 |

> 说明：模型"清单"实际只有 1 个 `SysContent`；目录计数为 2 是因为 `Models/Policies/SysContentPolicy.php` 同属 Models 子树。

## 技术栈

| 技术 | 版本/说明 |
|------|-----------|
| PHP | `>=7.4`（`composer.json`） |
| Laravel | 通过 `poppy/framework`（项目实际使用 Laravel 6 LTS；见 `composer.json` of root） |
| 模块框架 | `poppy/framework`（`Poppy\Framework\…`） |
| ORM | Eloquent |
| 认证（后台） | `backend-auth`（基于中间件名，定义于 `poppy.system`） |
| 认证（API） | `api-sign`（签名校验中间件；`JwtApiController` 提供的 JWT 基座） |
| 后台 UI 框架 | `py-mgr-page`（Grid / Filter / Displayers） |
| 富文本与上传 | 通过 `py-mgr-page.form` 的 `editor` / `thumb` 组件 |
| 队列/缓存 | 模块自身未直接使用 |

## 路由概览

| 路由文件 | 类型 | 前缀（最终 URL 前缀） | 路由数 | 说明 |
|----------|------|-----------------------|--------|------|
| `src/Http/Routes/api_v1.php` | API | `api_v1/content` | 2 | 面向前端 App/页面（签名 + JWT） |
| `src/Http/Routes/backend.php` | 管理后台 | `{mgr-page-prefix}/py-content`，其中 `{mgr-page-prefix}` 来自 `config('poppy.framework.prefix')` 默认为 `mgr-page` | 4 | 管理后台操作（`backend-auth`） |
| `web.php` | Web 页面路由 | — | 0 | 不存在 web 路由文件；后台页面通过 MgrPage 渲染 Blade |

最终 URL 示例：

- `POST/GET api_v1/content/content/lists`
- `POST/GET api_v1/content/content/detail`
- `POST/GET {prefix}/py-content/content`
- `POST/GET {prefix}/py-content/content/establish/{id?}`
- `POST/GET {prefix}/py-content/content/delete/{id}`
- `POST/GET {prefix}/py-content/content/toggle/{id}`

## 模型清单

| 模型 | 数据表 | 关键关联 | 说明 |
|------|--------|----------|------|
| `Poppy\Content\Models\SysContent` | `sys_content` | `hasOne(PamAccount::class, 'id', 'account_id')` 命名为 `pam` | 内容主表，含 `title/keyword/description/slug/type/cat_id/thumb/list_order/account_id/is_enable/content/author/create_at` |

策略：

- `Poppy\Content\Models\Policies\SysContentPolicy::$permissionMap['edit'] = 'backend:py-content.content.manage'`。

## 依赖的其他模块

| 模块 | 引用方式 | 说明 |
|------|----------|------|
| `poppy.system` | `use Poppy\System\Classes\Traits\{PamTrait, FilterTrait, PolicyTrait}; use Poppy\System\Models\{PamAccount, SysConfig}; use Poppy\System\Http\Request\ApiV1\JwtApiController; use Poppy\System\Http\OpenApi\BaseResponseBody;` | 账号上下文、过滤能力、策略、JWT 基座、OpenAPI 响应基类、是否启用常量 `SysConfig::YES` |
| `poppy.framework` | `use Poppy\Framework\Classes\Traits\AppTrait; use Poppy\Framework\Classes\Resp; use Poppy\Framework\Validation\Rule; use Poppy\Framework\Http\Pagination\PageInfo; use Poppy\Framework\Exceptions\ApplicationException; use Poppy\Framework\Application\Request;` | 错误/响应封装、校验规则、通用 Request 基类、分页类型 |
| `poppy.category` | `use Poppy\Category\Models\SysCategory;`（Controller、MgrPage、Blade） | 读取分类标题/标识，并以 `kvNameRefId` 把 `cat_slug` 转 `cat_id` |
| `poppy.mgr-page` | `Grid / Filter / Operations / Displayer/Actions / Column / Scope` | 后台列表、过滤、操作按钮、视图渲染 |

## 被其他模块依赖

经扫描 `poppy/*` 源码目录，未发现其他模块直接 `use Poppy\Content\…`。本模块对外暴露主要通过路由名 `py-content:backend.content.*` 与后端菜单 `configurations/menus.yaml` 注入到 `poppy.mgr-page` 的导航。

## 边界说明（不负责的事项）

- **分类增删改**：`cat_id` 仅作可空关联，分类本体由 `poppy.category` 提供；`poppy.category` 未启用时（`app('poppy')->exists('poppy.category') === false`）后台列表页不显示"分类"列。
- **账号体系**：`account_id` 字段记录作者，但账号增删改查由 `poppy.system` 负责；本模块仅通过 `PamTrait` 写入当前账号。
- **富文本上传**：不直接接管；调用方接入 `py-system` 的上传接口。
- **权限基础设施**：本模块只声明 `backend:py-content.content.{manage,index}`，配置存储在 `configurations/permissions.yaml`。
- **Hook 点**：`src/Hooks/` 目录为空，不发布任何模块级钩子（菜单/权限通过 `configurations/*.yaml` 注入）。

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
