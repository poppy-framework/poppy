# demo 模块（`Demo\`）

## 模块职责

`demo` 是 poppy 框架的官方示例 / 脚手架模块，对外不承载真实业务。它的职责是：

1. 把 poppy 模块能"做什么"集中展示一遍——5 种路由形态（api / api-app / backend / web / web-root）、表单组件、列表 / 搜索、Resp / ApiDoc / SSO 模板、PassportVerify 监听、JWT 探活、Artisan 命令、Seeder、Policy、Hook、Progress、Layout 等。
2. 给新模块提供一份可直接复制的目录结构与命名规范（PSR-4 namespace、控制器分包、RouteServiceProvider 模板、Form/List 拆分约定）。
3. 作为联调与健康检查入口（`/api/app/demo/demo/index`、`/api/demo/resp/success` 等）。

业务上没有任何"业务领域"绑定——它就是模板。所有真实业务都应放在 `poppy/<domain>/` 下。

## 目录结构

| 目录              | 职责                                                      | 文件数 |
|-----------------|---------------------------------------------------------|-----|
| Classes         | 常量（`DemoDef`）、示例桩类（`Stub`）、Layout（`Layout/Demo`）、全局 helper | 4   |
| Commands        | Artisan 命令                                               | 1   |
| Events          | 占位（仅 `.gitkeep`，本模块不发布事件）                              | 0   |
| Forms           | 后台 FormBuilder 控件清单（`FormText`、`FormSelect` 等 ~58 个 Widget）     | 58  |
| Hooks           | 框架 Hook 实现示例（ArrayDemo / HtmlDemo / AuthAccessUser 等）        | 6   |
| Http/Exception  | 自定义异常 Handler                                          | 1   |
| Http/Forms      | 业务级表单构造器（`FormDemoAli`、`FormSettingAvatar` + Helpers）         | 5   |
| Http/Lists      | Grid/List 控件的 ListDriver 与 SearchDriver                  | 27  |
| Http/Request    | 控制器（按 Api/App / Api/Web / Backend / Web 子目录分层）             | 18  |
| Http/Routes     | 5 个路由文件（api / api-app / backend / web / web-root）            | 5   |
| Http/Validation | 自定义 Request / 校验                                         | 5   |
| Jobs            | 占位（仅 `.gitkeep`，本模块无队列任务）                                | 0   |
| Listeners       | 事件监听器                                                  | 1   |
| Models          | Eloquent 模型 + Policy                                     | 4 + 1 |
| Progress        | 进度条 / 异步推进示例（`SendSmsProgress`）                          | 1   |
| Seeds           | 数据库 seeder                                             | 3   |
| ServiceProvider | 模块引导（注册 `RouteServiceProvider`、policy、listen）           | 1   |

> `Action/` 目录在本模块中**不存在**。Poppy 框架允许没有 Action 的模块；需要编排业务时再按需新增。

## 技术栈

| 技术       | 版本/说明                                      |
|----------|--------------------------------------------|
| PHP      | ^7.4（继承根项目 `composer.json` 的 `require.php`） |
| Laravel  | `^6.0`（与根项目一致）                             |
| 模块框架     | `poppy/framework`（`*@dev`）                  |
| ORM      | Eloquent                                   |
| 队列       | 占位（`Jobs/` 为空，无实际任务）                      |
| 认证       | JWT（继承 `poppy/system` 的 `JwtApiController`）  |
| 签名验证     | 通过 `py-ext-app.sign-json` 中间件用于 App 接口       |
| SSO      | 通过 `poppy/system` 的 `api-sso` 中间件           |
| 其他关键依赖  | `poppy/mgr-page`（Form/Grid/Content/Layout）  |

## 路由概览

| 路由文件         | 类型            | 前缀                              | 中间件                                  | 路由数 | 说明                          |
|--------------|---------------|---------------------------------|-------------------------------------|-----|-----------------------------|
| api.php      | Web API       | `api/demo`                       | `cross` / `api-sso`（分两组）              | 6   | 跨域示例 + Resp 模板 + SSO 接入示例     |
| api-app.php  | App API       | `api/app/demo`                   | `py-ext-app.sign-json`（由 RouteServiceProvider 添加） | 1   | App 健康检查                     |
| backend.php  | 后台           | `{prefix}/demo`（`prefix` 由 `poppy.mgr-page` 注入，前缀通常是 `backend`） | `sys-auth:backend`、`sys-rbac`        | 1   | 后台 demo 入口                   |
| web.php      | Web 页面        | `demo`                           | （注释掉的 `sys-auth:jwt_web`）              | ~24 | 后台控件示例（表单/表格/Grid/Search/Helper/Mail/Js/Exception/Log） |
| web-root.php | 根级 Web       | 无                                | 无                                   | 3   | `/`、`/demo`、`/demo/output/{info}` |

完整路由表见 [contracts.md](contracts.md)。

## 模型清单

| 模型             | 数据表              | 关键关联                          | 说明                  |
|----------------|------------------|-------------------------------|---------------------|
| `DemoGrid`     | `demo_grid`      | `hasOne(PamAccount, id, account_id)` | 后台 Grid 示例主表（含 JSON 字段 `setting`） |
| `DemoComment`  | `demo_comment`   | —                             | 示例模型，关系与结构用于二级页面演示   |
| `DemoDb`       | `demo_db`        | —                             | 示例模型                |
| `DemoUser`     | `demo_user`      | —                             | 示例模型                |

Policy：`Demo\Models\Policies\DemoGridPolicy::create()` 统一返回 `false`，仅作注册示例。

## 依赖的其他模块

| 模块                | 引用方式                                                                 | 说明                          |
|-------------------|----------------------------------------------------------------------|-----------------------------|
| `poppy/framework` | `use Poppy\Framework\Application\ApiController` 等                    | 基础控制器、Resp、RouteServiceProvider |
| `poppy/system`    | `use Poppy\System\Models\PamAccount` / `Events\PassportVerifyEvent` | PAM 账号 / 跨模块事件             |
| `poppy/mgr-page`  | `use Poppy\MgrPage\Classes\Grid / Form / Content / Layout`           | 后台 Form、Grid、Layout 渲染 |
| `poppy/ext-app`   | `py-ext-app.sign-json` 中间件                                          | App 接口签名校验                 |

## 被其他模块依赖

**无**。`grep -rn "use Demo\\\\"` 扫描显示本项目其余模块（含所有 `poppy/*`）没有任何 `use Demo\…` 引用——本模块是终端示例/模板，不被消费。

## 边界说明（不负责的事项）

- 不承载任何具体业务领域逻辑：账号、回收、议价、商品等业务都应在 `poppy/<domain>/` 下。
- 不应当作为新业务的"试运行"落地——它会被频繁改动以演示新特性，正式业务请另起模块。
- `Events/`、`Jobs/` 留空仅作占位；如果新模块实现事件/队列，先复制 `Listeners/PassportVerify` 与目录骨架再扩展。
- `backend.php` 只有一个 `/` 路由，**不渲染完整页面**：由 `poppy/mgr-page` 接管后台壳层，模块只负责子路由。

## 文档索引

- 业务逻辑（约定与展示规则） → [business.md](business.md)
- 对外契约（路由 / 事件 / Job / 命令） → [contracts.md](contracts.md)
- 执行流程（PassportVerify 事件链 / App 签名链路） → [flows.md](flows.md)
