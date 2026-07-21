# 对外契约

## API 路由（api_v1.php）

> 路由文件：`poppy/category/src/Http/Routes/api_v1.php`
> 路由注册：`RouteServiceProvider::map()` 中 `prefix=api_v1/category`、`middleware=api-sign`
> 控制器命名空间：`Poppy\Category\Http\Request\ApiV1\Web`

| HTTP方法                | URI                                | 请求类/控制器                                       | 中间件      | 说明                                                |
|----------------------|------------------------------------|-----------------------------------------------|----------|---------------------------------------------------|
| ANY（API 文档标注 POST）       | `/api_v1/category/category/sort`   | `ApiV1\Web\CategoryController::sort` (`CategorySortRequest`) | api-sign  | 调整分类排序位置，入参 `type/id/position/aim_id`，业务细节见 [business.md](business.md) |

> 备注：`api_v1.php` 当前仅 1 个路由，未显式 `name()`，调用方按 path 引用。

## 管理后台路由（backend.php）

> 路由文件：`poppy/category/src/Http/Routes/backend.php`
> 路由注册：`RouteServiceProvider::map()` 中 `prefix={prefix}/py-category`、`middleware=backend-auth`
> 控制器命名空间：`Poppy\Category\Http\Request\Backend`

| HTTP方法 | URI                                       | 请求类/控制器                          | 路由名                                            | 中间件           | 说明                |
|--------|-------------------------------------------|------------------------------------|-------------------------------------------------|---------------|-------------------|
| ANY    | `{prefix}/py-category/category`           | `Backend\CategoryController::index`        | `py-category:backend.category.index`            | backend-auth  | MgrPage 列表页（ListSysCategory） |
| ANY    | `{prefix}/py-category/category/establish/{id?}` | `Backend\CategoryController::establish` | `py-category:backend.category.establish`        | backend-auth  | MgrPage 表单页（FormCategoryEstablish） |
| ANY    | `{prefix}/py-category/category/delete/{id}`    | `Backend\CategoryController::delete`     | `py-category:backend.category.delete`           | backend-auth  | 删除单条分类          |
| ANY    | `{prefix}/py-category/category/status/{id}/{status}` | `Backend\CategoryController::status`  | `py-category:backend.category.status`           | backend-auth  | 启用/停用分类        |

> `{prefix}` 由宿主项目 `Poppy\Framework\Application\RouteServiceProvider::$prefix` 决定（待确认实际值）。

权限说明（`Backend\CategoryController::__construct()`）：
- 全局 `global` = `backend:py-category.category.index`

## 发布的事件（本模块对外发布）

| 事件类                              | 携带数据                       | 触发时机                                                  | 监听方                |
|----------------------------------|----------------------------|---------------------------------------------------------|--------------------|
| `SysCategoryBeforeDeleteEvent`   | 构造函数：`SysCategory $category`（公开属性 `category`） | `Action\Category::delete(int $id)` 中，先于 `whereKey($id)->delete()` 同步触发 | **当前无监听方**（见下文） |

事件类路径：`poppy/category/src/Events/SysCategoryBeforeDeleteEvent.php`，继承自 `Poppy\Framework\Application\Event`。

### 监听方扫描结论

通过 `grep -rln "SysCategoryBeforeDeleteEvent"` 扫描整个 `poppy/*/src/Listeners/` 目录（含 `area`、`core`、`sensitive-word`、`system`、`version`），结果**只命中本模块自身的两个文件**（定义 + `Action\Category` 内的事件触发点），未发现任何 Listener 注册。

- 事件监听方：**0（已扫描全部模块的 `Listeners/` 目录）**
- 是否使用全项目通用的 `EventServiceProvider` 订阅方式注册监听器：未在本模块代码中发现（待确认其他模块是否使用了动态订阅）。
- 已勾选范围：扫描排除了 `vendor/` 与 `node_modules/`，未发现第三方订阅。

> 实用含义：当前删除行为是"广播事件 → 无监听 → 物理删除"，属于预留式耦合点。后续若有第三方模块需要"删除分类时联动清理"，应在其 `ServiceProvider::$listen` 中显式订阅 `Poppy\Category\Events\SysCategoryBeforeDeleteEvent`。

## 监听的事件（本模块消费）

无。本模块没有 `src/Listeners/`，也未在任何 `ServiceProvider` 中声明 `$listen`。

## 队列任务

无。本模块没有 `src/Jobs/`，不消费队列。

## Artisan 命令

无。本模块没有 `src/Commands/`。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方       | 目标模块          | 目标类                                            | 调用方法                                  | 场景                                       |
|--------------|---------------|------------------------------------------------|---------------------------------------|------------------------------------------|
| Models\SysCategory | poppy/system  | `Poppy\System\Classes\Traits\FilterTrait`     | `filter()/paginateFilter()/pageFilter()` | 为 MgrPage List/Form 提供统一过滤、分页能力 |
| Models\SysCategory | poppy/system  | `Poppy\System\Models\SysConfig`               | 常量 `YES/NO/ENABLE`                    | `is_enable` 状态判断与默认值                  |
| Action\Category   | poppy/system  | `Poppy\System\Models\SysConfig`               | 常量 `YES/NO`                            | `status($id, $status)` 写入启用位           |

> 另外，模型与 Action 间接依赖 `poppy/framework`（`TreeHelper`、`Event` 基类、`sys_tag()` 等），属于框架级基础设施调用，不计入跨业务模块调用。

## 被其他模块调用（本模块被引用）

| 调用方模块           | 调用方类                                                                                     | 本模块目标类                | 调用方法                            | 场景                                                                 |
|------------------|------------------------------------------------------------------------------------------|------------------------|---------------------------------|--------------------------------------------------------------------|
| poppy/content    | `Http\Request\ApiV1\Web\ContentController` / `Http\MgrPage\ListSysContent` / 后台视图 `establish.blade.php` | `Models\SysCategory` 等 | 直接 `use` 模型；路由引用 `py-category:backend.category.*`    | 内容模块在 MgrPage 与视图中枚举或联动分类                                         |
| 其他模块（运行期）       | 调用 MgrPage Hook `poppy.category.form_category_select`                                        | `Hooks\FormCategorySelect::builder()` | MgrPage 注册的服务表单 Hook | 后台表单注册分类下拉                                                  |

> 其他业务模块是否直接消费 `SysCategory` 待逐一确认（已知 `poppy/content` 有引用）。

## 验证规则（FormRequest）

`Http\Validation\CategoryEstablishRequest`（后台 `establish` 表单）：

| 字段          | 规则                                                                                            | 中文属性名     |
|-------------|-----------------------------------------------------------------------------------------------|------------|
| `type`      | `required`、`in(array_keys(SysCategory::kvType()))`                                              | 分类类型       |
| `title`     | `required`、`string`、`unique:sys_category,title`（scope 限定同 `type`，编辑模式排除自身 `id`）                  | 分类名称       |
| `parent_id` | `numeric`                                                                                      | 父分类ID      |
| `name`      | `string`、`unique:sys_category,name`（scope 限定同 `type`，编辑模式排除自身 `id`）                          | 分类名称（别名）  |

`Http\Request\ApiV1\Web\Category\CategorySortRequest`（API 排序接口）：

| 字段          | 规则                            | 中文属性名 |
|-------------|-------------------------------|--------|
| `type`      | `required`                     | 分类分组   |
| `id`        | `required`                     | ID     |
| `position`  | `required`、`in(['gt', 'lt'])` | 位置     |
| `aim_id`    | `required`                     | 目标ID   |

OpenAPI 标注：
- `PoppyCategoryCategorySortRequest` 由 `ApiV1\Web\Category\CategorySortRequest` 的 `@OA\Schema` 定义（位于该类头注释中）。
- 响应体使用 `PoppySystemResponseBody`（宿主系统定义）。
- `ApiV1\Web\CategoryController` 整体 `@OA\Tag(name="Category")`。

## Hook（MgrPage ServiceForm）

| Hook key                                  | Builder 类                          | 参数                                                  | 输出                              |
|-------------------------------------------|------------------------------------|-----------------------------------------------------|---------------------------------|
| `poppy.category.form_category_select`     | `Poppy\Category\Hooks\FormCategorySelect` | `name` (string, 必填)、`type` (string, 必填)、`value` (default)、`options` (array, default) | MgrPage `select` 渲染（layui 风格） |

注册位置：`configurations/hooks.yaml`，1 条记录（详见源代码）。
