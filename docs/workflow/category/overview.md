# 分类管理（Poppy\Category）

## 模块职责

通用多级分类（树形）管理模块，向系统其他模块提供按 `type` 分组的分类字典能力。负责分类的增删改、树形查询、跨 `type` 维度的排序、KV 缓存维护，并为第三方模块在被删除分类前提供事件接入点。

## 目录结构

| 目录                   | 职责                                     | 文件数 |
|----------------------|----------------------------------------|-----|
| Action               | 业务逻辑层（Category）                       | 1   |
| Models               | Eloquent 模型（SysCategory）                | 1   |
| Events               | 领域事件（SysCategoryBeforeDeleteEvent）     | 1   |
| Hooks                | MgrPage 服务表单扩展（FormCategorySelect）    | 1   |
| Http/Routes          | 路由定义（api_v1.php、backend.php）           | 2   |
| Http/Request         | 控制器层（Backend、ApiV1）                   | 2   |
| Http/Validation      | FormRequest 请求校验                        | 1   |
| Http/MgrPage         | 后台 MgrPage（List/Form）                   | 2   |
| Http/RouteServiceProvider | 模块路由 Provider                    | 1   |
| Classes              | 常量/键定义（PyCategoryDef）                 | 1   |
| resources/migrations | 数据库迁移                                  | 2   |
| resources/lang       | i18n 文案（zh）                              | 3   |
| configurations       | Hooks/SEO/Util 配置                          | 1   |
| tests                | 单元测试                                     | 2   |

## 技术栈

| 技术      | 版本/说明                                                  |
|---------|----------------------------------------------------------|
| PHP     | >=7.4（composer.json require.php）                          |
| Laravel | 框架版本继承宿主项目（Laravel 6）                                |
| 模块框架    | poppy/framework（基于 PoppyServiceProvider + RouteServiceProvider） |
| ORM     | Eloquent                                                  |
| 缓存      | Redis Hash（通过 `sys_tag('py-category')` 统一 Tag）           |
| 队列      | 与宿主项目共享（此模块无队列任务）                                   |

## 路由概览

| 路由文件        | 类型    | 前缀                       | 中间件         | 路由数 | 说明               |
|-------------|-------|--------------------------|------------|-----|------------------|
| api_v1.php  | API   | `api_v1/category/`       | api-sign   | 1   | 外部 H5/App 调用分类排序 |
| backend.php | 管理后台  | `{prefix}/py-category/`  | backend-auth | 4   | MgrPage 后台分类管理   |

> `{prefix}` 来自 `Poppy\Framework\Application\RouteServiceProvider` 基类的 `$prefix`，通常为后台管理前缀（如 `py-` 或 `manage/`），与宿主项目配置相关（待确认）。

## 模型清单

| 模型           | 数据表             | 关键关联                        | 说明                                    |
|--------------|-----------------|-----------------------------|---------------------------------------|
| SysCategory  | sys_category    | 自引用树（parent_id → id）        | 多级分类主表，按 `type` 分组，`list_order` 控制排序 |

字段：`id`、`title`、`name`（标识/别名，2023-07 迁移新增）、`parent_id`、`top_id`、`type`、`list_order`、`is_enable`（2023-07 迁移新增）、`created_at`、`updated_at`。

## 依赖的其他模块

| 模块            | 引用方式                                              | 说明                                                |
|---------------|---------------------------------------------------|---------------------------------------------------|
| poppy/system  | `use Poppy\System\Classes\Traits\FilterTrait;`     | SysCategory 使用 FilterTrait 提供过滤/分页能力              |
| poppy/system  | `use Poppy\System\Models\SysConfig;`              | 借用常量 `SysConfig::YES/NO/ENABLE` 用于 `is_enable` 状态判断 |
| poppy/system  | `use Poppy\System\Http\Request\ApiV1\JwtApiController;` | API 控制器继承 JwtApiController                   |
| poppy/framework | `use Poppy\Framework\Helper\TreeHelper;` 等若干类 | 框架级工具（请求、事件、响应、Tag 缓存助手等）                       |

## 被其他模块依赖

| 模块                  | 引用方式                                                                 | 说明                                                       |
|---------------------|----------------------------------------------------------------------|----------------------------------------------------------|
| poppy/content       | 视图/MgrPage 引用 `SysCategory` / `py-category:backend.*` 路由          | 内容模块在 MgrPage/视图中引用分类（枚举、关联字段填充）                   |
| 其他业务模块（通过 Hook）      | 调用 `poppy.category.form_category_select` Hook 选择分类                   | 通过 MgrPage 的 ServiceForm 接口渲染分类选择下拉                    |

> 跨模块事件订阅扫描结论：`SysCategoryBeforeDeleteEvent` 在 `poppy/*/src/Listeners/` 下**没有任何监听器**注册。当前为预留事件，由调用方按需挂载（待确认是否有意如此设计）。

## 边界说明（不负责的事项）

- **不做任何级联约束检查**：删除分类时不验证是否存在子节点、是否被其他模块（内容、商品等）引用，仅通过事件暴露给监听方自行处理。
- **不维护 `top_id`**：模型虽有 `top_id` 列与 Sort 控制器写入，但 Action 层的 `establish()` 与 `sort()` 都没有显式设置 `top_id`，`top_id` 多由调用方或数据库触发器维护（待确认）。
- **不存储分类层级深度**：仅靠 `parent_id` 自引用，树深由 `TreeHelper` 现算。
- **不做跨 `type` 的 name/全局唯一**：唯一性约束 scope 限定在 `(type, title)` 与 `(type, name)`。
- **不提供批量导入/导出接口**：仅支持逐条 `establish()`。

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
