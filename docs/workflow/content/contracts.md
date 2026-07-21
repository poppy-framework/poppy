# 对外契约

## API 路由（`src/Http/Routes/api_v1.php`，前缀来自 `RouteServiceProvider::map()`）

> 中间件组：`api-sign`；前缀：`api_v1/content`；命名空间：`Poppy\Content\Http\Request\ApiV1\Web`；控制器：`Poppy\Content\Http\Request\ApiV1\Web\ContentController`（继承 `Poppy\System\Http\Request\ApiV1\JwtApiController`）。
> 注：实际最终 URL 由 `route file` 内 `$route->any('content/lists', …)` 与外部组前缀 `api_v1/content` 拼接得到 `api_v1/content/content/lists`（刻意保留 `content/content` 双段）。

| HTTP方法 | URI | 请求类/控制器 | 中间件 | 说明 |
|----------|-----|----------------|--------|------|
| `*（any）` | `api_v1/content/content/lists` | `Poppy\Content\Http\Request\ApiV1\Web\Content\ContentListsRequest` → `ContentController@lists` | `api-sign` | 内容列表（分页），可按 `cat_slug`/`cat_id` 过滤；仅返回 `is_enable=SysConfig::YES` 的数据 |
| `*（any）` | `api_v1/content/content/detail` | `Poppy\Content\Http\Request\ApiV1\Web\Content\ContentDetailRequest` → `ContentController@detail` | `api-sign` | 内容详情；返回正文 + 分类标题 + 分类上下文内的 `prev`/`next` |

OpenAPI Schema 名（由 `@OA\Schema` 注解声明）：

- 请求：`PoppyContentContentListsRequest`、`PoppyContentContentDetailRequest`
- 响应：`PoppyContentContentListsResponseBody`、`PoppyContentContentDetailResponseBody`（均继承 `Poppy\System\Http\OpenApi\BaseResponseBody`）

## 管理后台路由（`src/Http/Routes/backend.php`，前缀来自 `RouteServiceProvider::map()`）

> 前缀：`{mgr-page}/py-content`，其中 `{mgr-page}` 取自 `config('poppy.framework.prefix')` 默认为 `mgr-page`（即最终前缀 `mgr-page/py-content`）。
> 中间件：`backend-auth`；命名空间：`Poppy\Content\Http\Request\Backend`；控制器：`Poppy\Content\Http\Request\Backend\ContentController`（继承 `Poppy\MgrPage\Http\Request\Backend\BackendController`）。
> 路由名均以 `py-content:` 为前缀（保留）。

| HTTP方法 | URI | 路由名 | 请求类/控制器方法 | 说明 |
|----------|-----|--------|------------------|------|
| `*（any）` | `{mgr-page}/py-content/content` | `py-content:backend.content.index` | `ContentController@index` | 后台内容列表（MgrPage `Grid` + `ListSysContent`） |
| `*（any）` | `{mgr-page}/py-content/content/establish/{id?}` | `py-content:backend.content.establish` | `ContentController@establish` | 创建或编辑内容（POST 表单校验 → `Action\Content::establish`） |
| `*（any）` | `{mgr-page}/py-content/content/delete/{id}` | `py-content:backend.content.delete` | `ContentController@delete` | 删除内容（`Action\Content::delete`） |
| `*（any）` | `{mgr-page}/py-content/content/toggle/{id}` | `py-content:backend.content.toggle` | `ContentController@toggle` | 启用/隐藏切换（`Action\Content::toggle`） |

## Web 路由

无。本模块不暴露任何浏览器直访路由；后台页面通过 MgrPage 渲染 Blade 模板 `py-content::backend.content.establish`（位置：`resources/views/backend/content/establish.blade.php`）。

## 其他路由文件

无（除 `api_v1.php` 与 `backend.php` 外，路由服务提供者未 require 其他路由文件）。

## 发布的事件（本模块对外发布）

| 事件类 | 携带数据 | 触发时机 | 监听方 |
|--------|----------|----------|--------|
| — | — | — | — |

> 本模块未定义任何事件；`Action\Content` 内没有任何 `event(…)` 调用。

## 监听的事件（本模块消费）

| 监听器类 | 监听的事件 | 业务动作 | 产生的事件/任务 |
|----------|------------|----------|-----------------|
| — | — | — | — |

> 本模块未注册任何事件监听器；`src/Hooks/` 为空。

## 队列任务

| Job 类 | 队列名 | 延迟 | 触发来源 | 业务动作 |
|--------|--------|------|----------|----------|
| — | — | — | — | — |

> 模块内不包含 `Jobs/` 目录，且 `Action\Content` 不调用 `dispatch(…)`。

## Artisan 命令

| 命令签名 | 说明 | 调度方式 |
|----------|------|----------|
| — | — | — |

> 模块内不包含 `Console/Commands/` 目录。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方 | 目标模块 | 目标类 | 调用方法 | 场景 |
|--------------|----------|--------|----------|------|
| `Action\Content` | `poppy.system` | `Classes\Traits\PamTrait` | `checkPam()`, `getPam()`, `setPam()` | 操作前鉴权；创建时写当前账号 `account_id` |
| `Action\Content` | `poppy.system` | `Models\SysConfig` | `SysConfig::YES` | 创建/前端过滤使用的"启用"常量 |
| `Models\SysContent` | `poppy.system` | `Models\PamAccount` | `hasOne(PamAccount::class, 'id', 'account_id')` | 通过 `pam` 关联取回作者账号 |
| `Models\SysContent` | `poppy.system` | `Classes\Traits\FilterTrait` | `filter / pageFilter / paginateFilter / whereBeginsWith / …` | 列表筛选/分页 |
| `Models\Policies\SysContentPolicy` | `poppy.system` | `Classes\Traits\PolicyTrait`、`Models\PamAccount` | `edit(PamAccount $pam, SysContent $content)` | 策略鉴权基座 |
| `Models\SysContent` | `poppy.framework` | `Http\Pagination\PageInfo` | `@method pageFilter(PageInfo $pageInfo)` | 分页参数模型 |
| `Action\Content` | `poppy.framework` | `Classes\Traits\AppTrait` | `setError()` 等 | 业务错误传递 |
| `ApiV1\Web\ContentController` | `poppy.system` | `Http\Request\ApiV1\JwtApiController` | 继承 | JWT 上下文基座 |
| `ApiV1\Web\ContentController` | `poppy.framework` | `Classes\Resp` | `Resp::success(…)` | 响应封装 |
| `ApiV1\Web\ContentController` | `poppy.category` | `Models\SysCategory` | `kvNameRefId($catSlug)`、`kvTitle($catId)`、`kvSlug($catId)` | slug/id 互转、读取分类展示字段 |
| `ApiV1\Web\Content\ContentListsResponseBody`、`ContentDetailResponseBody` | `poppy.system` | `Http\OpenApi\BaseResponseBody` | 继承 | OpenAPI 响应基类 |
| `Backend\ContentController` | `poppy.mgr-page` | `Classes\Grid`、`Grid\Displayer\Actions` 等 | 构造 `(new Grid(new SysContent()))->setLists(ListSysContent::class)->render()` | 后台列表渲染 |
| `Backend\ContentController` | `poppy.framework` | `Classes\Resp`、`Exceptions\ApplicationException` | `Resp::success / Resp::error` | 后台响应 |
| `Http\Validation\ContentRequest` | `poppy.framework` | `Validation\Rule` | `Rule::required/string/nullable/integer/unique/dateFormat` | 后台表单校验 |
| `MgrPage\ListSysContent` | `poppy.mgr-page` | `Classes\*` | `Grid\Column / Filter / Filter\Scope / ListBase / Operations / Displayer\Actions` | 列表定义、过滤、按钮 |
| `MgrPage\ListSysContent` | `poppy.category` | `Models\SysCategory` | `SysCategory::kvTitle($value)` | 分类列展示 |
| `resources/views/backend/content/establish.blade.php` | `poppy.category` | `Models\SysCategory::tree($type)` | 模板内调用 | 分类下拉数据 |
| `resources/views/backend/content/establish.blade.php` | `poppy.mgr-page` | `py-mgr-page.form` 服务（`thumb/editor/text/textarea/datetimePicker/select`） | 模板内调用 | 后台表单控件 |

## 被其他模块调用（本模块被引用）

扫描 `poppy/*` 源码目录（不含 `poppy/content`）后，**未发现**其他模块以 `use Poppy\Content\…` 形式直接引用本模块类。外部对本模块的访问完全通过：

- 路由名 `py-content:backend.content.*`（由 `configurations/menus.yaml` 在 MgrPage 导航中引用）。
- URL：`api_v1/content/content/{lists,detail}`、`{mgr-page}/py-content/content/...`。

## 配置契约

- `configurations/menus.yaml`：在 MgrPage 后台注入一个"内容管理"顶级菜单项（含图标 `bi bi-paragraph`、injection `'poppy.mgr-page/backend||setting'`）。
- `configurations/permissions.yaml`：声明权限组 `backend:py-content` → 子项 `content.manage`（描述：管理分类/内容）。
- `configurations/module.yaml`：声明模块 title=`分类管理`、description=`分类管理模块`、author=`duoli`、version=`1.0.0`。
  - 待确认：`module.yaml` 中"分类管理"标题文案疑似复用了 category 模块的旧文案，建议核对实际展示位置（menus.yaml 实际显示"内容管理"）。
- `config('poppy.content.types', [])`：可在宿主机 `config/poppy.php` 中向 `content.types` 注入额外的简易 `type` 项；`SysContent::kvType()` 会与 `TYPE_DEFAULT` 合并输出。

## 资源文件契约

- Migrations（按时间顺序）：
  1. `2023_03_26_144119_create_sys_content_table.php` — 建表 `sys_content`（`id/title(100)/type(40)/cat_id/thumb(255)/list_order/is_enable/content/timestamps`）。
  2. `2023_04_16_223557_alter_sys_content_add_account_id_field.php` — 增加 `account_id`。
  3. `2023_08_30_103557_alter_sys_content_add_account_author_field.php` — 增加 `author(100)/keyword(255)/description(255)/create_at(datetime, nullable)`，并对 `slug` 建索引（备注：`slug` 列未在本次扫描迁移的 schema 中显式出现，待确认是否由更早迁移或与其他模块共享）。
- Lang（`resources/lang/zh/`）：
  - `seo.php`：`backend_content_index/establish/delete/toggle` → `内容管理/管理内容/删除内容/切换展示`。
  - `util.php`：`models.sys_category` → `分类`。
- Views：`py-content::backend.content.establish`（基于 `py-mgr-page::backend.tpl.default` 模板）。
