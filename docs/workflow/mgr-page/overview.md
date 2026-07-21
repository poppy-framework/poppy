# mgr-page（`Poppy\MgrPage`）

## 模块职责

后台管理页面框架，负责把登录后的管理端请求统一接入，并提供 CRUD 页面所需的 Grid/List、FormWidget、FormBuilder、菜单与设置扩展能力。它提供系统后台的基础账号、角色、封禁、设置和开发工具页面，但不承载这些领域对象的持久化业务；账号、角色、权限和文件服务主要由 `Poppy\System` / `Poppy\Core` 提供。

## 目录结构

统计口径为 `src/` 下的 PHP 文件；`Classes` 中约 170 个类，不逐一枚举。

| 目录 | 职责 | 文件数 |
|---|---|---:|
| `src/Classes` | FormBuilder、Grid/List、字段、过滤器、操作、布局和 Widget | 171 |
| `src/Http` | 路由提供者、中间件、Backend/Develop 控制器及表单/列表页面 | 36 |
| `src/Hooks` | 设置数组 Hook、页面 JS 变量 HTML Hook | 2 |
| `src/Commands` | 资源反向复制命令 | 1 |
| `src/Facade` | `Form` Facade | 1 |
| `src/Support` | 全局辅助函数 | 1 |
| `configurations` | `module.yaml`、`menus.yaml`、`services.yaml`、`hooks.yaml` | 4 |
| `resources` | Backend/Develop Blade 视图、分页视图和 layui/前端资源 | 非 PHP |

## 技术栈

| 技术 | 版本/说明 |
|---|---|
| PHP | `>=7.4` |
| Laravel | 宿主项目 `laravel/framework: 6.*`；mgr-page 通过 Poppy 运行时接入 |
| Poppy 框架 | `poppy/framework`；模块基类使用 `PoppyServiceProvider`、`Poppy\Framework\Application\Controller` |
| ORM | Eloquent；本模块不定义 `Models`，通过 `Poppy\System` 模型驱动页面 |
| 表单 | `laravelcollective/html: ^6.0` 的 `Collective\Html\FormBuilder`，由 `Poppy\MgrPage\Classes\FormBuilder` 扩展 |
| 验证/认证 | FormWidget 字段规则；后台认证由 `Poppy\System` 的 backend guard/JWT/session 能力提供 |
| 文件/签名 | 上传、图片预览和编辑器签名调用 `Poppy\System` 的文件管理与 `ApiSignContract` |
| 验证码 | `mews/captcha: ^3.2` |

## 路由概览

`RouteServiceProvider` 从 `config('poppy.framework.prefix')` 读取前缀，默认值为 `mgr-page`；下表中的 `{prefix}` 在默认配置下就是 `mgr-page`。路由名称必须保持 `py-mgr-page:backend.*` / `py-mgr-page:develop.*` 命名空间。

| 路由文件/来源 | 类型 | 前缀 | 路由数 | 说明 |
|---|---|---|---:|---|
| `RouteServiceProvider` | 管理后台入口 | `/{prefix}` | 3 | 首页、登录、验证码发送 |
| `Routes/backend.php` | 管理后台 | `/{prefix}/system` | 31 | 首页工具、角色、账号、封禁、上传 |
| `Routes/develop.php` | 开发后台 | `/{prefix}/develop` | 5 | 环境信息、日志、优化 |
| 合计 | — | — | 39 | 只有 Backend/Develop 路由，没有 API 路由文件 |

所有受保护的管理页面通过 `backend-auth` 组接入 `web`、backend auth/session、封禁检查、RBAC 和管理端生命周期中间件；登录和验证码入口使用 `web`。

## 页面能力分层

- **表单**：`Classes/FormBuilder` 是 Laravel Collective 的后台扩展；`Classes/Widgets/FormWidget` 负责声明字段、验证、GET 渲染和 POST `handle()`；`Classes/Form/FormSettingBase` 将表单字段映射到系统设置。
- **列表**：`Classes/Grid` 负责模型查询、排序、过滤、分页、导出和渲染；`Classes/Grid/ListBase` 的 `columns()`、`filter()`、`quickButtons()` 描述单个列表页。`Http/MgrPage` 中仅保留 5 个系统列表类作为代表：`ListPamAccount`、`ListPamRole`、`ListPamLog`、`ListPamToken`、`ListPamBan`。
- **控制器**：`BackendController` 为管理端控制器基类；`HomeController`、`PamController`、`RoleController`、`BanController`、`UploadController` 等负责将路由连接到 Grid/Form/SettingView。
- **扩展**：`services.yaml` 声明 HTML/数组服务，`hooks.yaml` 追加实现类；菜单和权限本身来自模块配置，经过 Core Repository 聚合并按用户能力裁剪。

## 依赖的其他模块

| 模块 | 引用方式 | 说明 |
|---|---|---|
| `framework` | `Poppy\Framework\...` | ServiceProvider、Controller、响应、分页、SEO、路由前缀和公共 Trait |
| `core` | `Poppy\Core\...` | Hook ServiceArray/ServiceHtml、模块菜单/服务/权限仓库、RBAC 能力 |
| `system` | `Poppy\System\...` | `PamAccount`/`PamRole` 等模型、Pam/Role/Ban/Sso Action、设置、文件、API 签名和 backend guard |
| `laravelcollective/html` | `Collective\Html\FormBuilder` | 基础 HTML 表单控件 |
| `mews/captcha` | Laravel captcha 集成 | 登录页验证码 |

## 被其他模块依赖

`mgr-page` 是管理端页面基础设施，当前源码中直接引用它的模块包括：

| 模块 | 代表性调用 | 说明 |
|---|---|---|
| `system` | `Action\Pam` 使用 `Http\MgrPage\FormSettingLog` | 系统账号相关设置复用管理端设置表单 |
| `category`、`content`、`area` | Backend Controller、`Grid`、`ListBase`、`FormWidget` | 标准 CRUD 后台页 |
| `ad`、`app`、`version`、`sensitive-word` | Backend Controller、Grid/List/FormWidget | 各自领域的后台管理页 |
| `sms`、`aliyun-oss`、`aliyun-push` | `FormSettingBase`、BackendController；`aliyun-push` 还提供设置 Hook | 将第三方配置挂入后台设置页 |

## 边界说明（不负责的事项）

- 不定义账号、角色、权限、封禁、日志或文件存储模型；这些对象的 Action/Policy/Repository 属于 `Poppy\System` 或 `Poppy\Core`。
- 不拥有业务模块的领域 CRUD；只提供通用 Grid/Form/Operation 页面能力和路由承载。
- 不提供业务 API 路由、队列 Job 或独立的 Events/Listeners 目录；表单上传调用的是 `Poppy\System` 的 API 路由。
- 不把菜单权限判断实现为页面模板逻辑；菜单裁剪由 Core 的菜单仓库和 `sys-rbac` 中间件完成。

## 待确认

- `poppy.framework.prefix`、分页配置和队列/缓存驱动可由宿主应用覆盖；本文按源码默认前缀 `mgr-page`、默认分页 15 说明，部署环境最终值需要运行时确认。
- 需求中提到的 `PyCoreMenuHook`、`PyCorePermissionHook`、`PyCoreSettingHook` 在当前仓库未找到；实际 Hook 契约是 `ServiceArray`/`ServiceHtml`，菜单和权限来自 YAML 配置，是否存在外部兼容层需要确认。

## 文档索引

- 业务规则 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
