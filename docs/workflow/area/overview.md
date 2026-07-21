# Poppy\Area（poppy/area）

## 模块职责

地区模块，负责中国行政区划（省 / 市 / 区/县）与国别（ISO 国家码）的数据维护与对外查询。
提供：地区树形数据查询、国别 KV 查询、后台地区 CRUD、地区 Form/Grid 扩展字段、数据初始化导入与缓存清理。

## 目录结构

| 目录                | 职责                                                  | 文件数 |
|-------------------|-----------------------------------------------------|-----|
| Action            | 业务逻辑层（地区 CRUD、修复、层级/子集重算）                       | 1   |
| Models            | Eloquent 模型 + Resource                              | 2   |
| Classes           | 工具/配置类（缓存 key、Form/Grid 字段扩展）                    | 4   |
| Listeners         | 事件监听器（监听框架 PoppyOptimized 事件清理本模块缓存）             | 1   |
| Commands          | Artisan 命令（`py-area:init` 初始化数据）                   | 1   |
| Http/Request      | API/Backend 控制器与 Response Body（OpenAPI Schema）   | 3   |
| Http/Routes       | 路由定义（api_v1 / backend）                            | 2   |
| Http/MgrPage      | 后台 MgrPage 组件（列表、表单页）                              | 2   |
| resources/migrations | 数据库迁移（sys_area 表）                              | 1   |
| resources/def     | 静态数据源（country.php + province/city/county.json） | 4   |
| resources/lang/zh | 语言包（错误提示文案）                                       | 3   |
| resources/views   | Blade 模板（Form/Filter 字段视图）                        | 2   |
| configurations    | 菜单/权限注册（menus.yaml / permissions.yaml）         | 2   |

## 技术栈

| 技术       | 版本/说明                                                  |
|----------|--------------------------------------------------------|
| PHP      | >=7.4（composer.json require）                          |
| Laravel  | 6.x（由 poppy 框架基座提供）                                    |
| 模块框架     | poppy/framework（基于 weiran/framework）                    |
| ORM      | Eloquent                                                |
| 缓存       | 通过 `sys_tag('py-area')` 获取的标签缓存（默认 Redis）                |
| 数据初始化    | `py-area:init` 命令读取 resources/def/*.json 灌入数据库 + Redis Hash |
| 跨模块     | 依赖 `poppy/system`（trans、FixTrait、PamTrait）、`poppy/core`（RdsDb、PyCoreDef）、`poppy/framework`（PoppyOptimized 事件、PoppyServiceProvider、UtilHelper、TreeHelper） |

## 路由概览

| 路由文件        | 类型    | 前缀                              | 路由数 | 说明                                       |
|-------------|-------|---------------------------------|-----|------------------------------------------|
| api_v1.php  | API   | `/api_v1/area`（api-sign 中间件）    | 2   | 地区代码树、国别 KV 查询，面向前端 / App                |
| backend.php | 管理后台  | `/backend/area`（backend-auth）  | 4   | 地区列表、添加/编辑、删除、修复（fix）                   |

## 模型清单

| 模型              | 数据表        | 关键字段                                              | 说明                                                  |
|-----------------|------------|---------------------------------------------------|-----------------------------------------------------|
| `SysArea`       | `sys_area` | code(12), title, parent_id, top_parent_id, has_child, level, children(text) | 地区主表，level=1 省 / 2 市 / 4 区/县，自引用树形结构。含静态方法 cityTree / cascader / kvProvince / kvCity / kvArea / country 等。 |
| `AreaContentResource` | —（Resource，非 Model） | id, title, parent_id                              | API 资源转换器，对应 `SysArea` 字段。                     |

## 依赖的其他模块

| 模块            | 引用方式                                                                            | 说明                                  |
|---------------|---------------------------------------------------------------------------------|-------------------------------------|
| poppy/system  | `use Poppy\System\Http\Request\ApiV1\WebApiController;` 等                     | 控制器基类、PAM 鉴权 trait、语言包 key 前缀          |
| poppy/core    | `use Poppy\Core\Redis\RdsDb; use Poppy\Core\Classes\PyCoreDef;`                 | `RdsDb::instance()` 用于初始化时写 Redis Hash；`PyCoreDef::MIN_ONE_MONTH * 60` 作为缓存 TTL 秒数 |
| poppy/framework | `use Poppy\Framework\Events\PoppyOptimized;` 等                                | `PoppyServiceProvider`、`sys_tag`、`TreeHelper`、`UtilHelper`、`Validation\Rule`、`Resp`、`ApplicationException`、`ModuleNotFoundException`、`Events\PoppyOptimized` |
| poppy/mgr-page | `use Poppy\MgrPage\Classes\Form;` `Grid\Filter` 等                             | Form/Grid 字段扩展基类，`ServiceProvider` 通过 `Form::extend('area', …)`、`Filter::extend('area', …)` 注册 |

## 被其他模块依赖

| 模块            | 引用方式                              | 说明     |
|---------------|-----------------------------------|--------|
| 待确认：需扫其他模块 | `Poppy\Area\Models\SysArea::…` 等  | 提供地区/国别 KV、树形查询；其他模块若需在表单做地址选择会引用 cityTree / cascader / kvProvince / kvCity 等 |

## 边界说明（不负责的事项）

- 不存储行政区划变更历史 / 版本管理（仅依赖外部 JSON 初始化并通过 fix 命令批量重算）
- 不提供接口给业务方上传自定义地区；新增/修改只走后台 `establish` 入口
- 国别数据来自 `resources/def/country.php`（静态文件），不与 ISO 官方库实时同步
- 不负责地址解析（地址 → 地区 ID 反向匹配），仅做正向 KV 查询

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)

## 待确认

- `poppy/area` 是否被其他业务模块（如 `poppy/system`、`poppy/user`、`poppy/order` 等）的 MgrPage Grid/Form 或前台 Controller 引用以做地区选择；需要扫 `poppy/*/src/Http/MgrPage/` 与 `poppy/*/src/Http/Request/ApiV1/` 才能补全"被其他模块依赖"表（发现位置：overview.md → "被其他模块依赖" 段落）