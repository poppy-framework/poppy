# 对外契约

> demo 模块是 poppy 框架的**示例/模板**。本文件罗列所有路由、事件、监听、Job、Artisan 命令。
> 所有路径/类名均来自代码事实，命名模式为 `demo:<area>.<controller>.<method>`。

## API 路由

### api.php（Web API 演示 — 前缀 `api/demo`）

> 由 `Http/RouteServiceProvider::mapApiRoutes()` 注入前缀 `api/demo`。
> 分两个组：跨域演示组（`cross`）和 SSO 演示组（`api-sso`）。

| HTTP方法         | URI                            | 控制器                          | 请求类                      | 中间件       | 说明                                       |
|----------------|--------------------------------|------------------------------|--------------------------|-----------|------------------------------------------|
| GET            | `/api/demo/apidoc/how`         | `ApiDocController@how`       | `ApiDocHowRequest`       | `cross`   | ApiDoc 注解演示，回显请求参数                  |
| ANY            | `/api/demo/resp/success`       | `RespController@success`     | `RespSuccessRequest`     | `cross`   | 成功响应演示，支持 `_location` / `_reload` meta |
| GET            | `/api/demo/resp/error`         | `RespController@error`       | —                        | `cross`   | 业务失败响应演示（`code=1`）                  |
| GET            | `/api/demo/resp/validator`     | `RespController@validator`   | —                        | `cross`   | Validator 校验失败响应演示                  |
| GET            | `/api/demo/resp/401`           | `RespController@unAuth`      | —                        | `cross`   | HTTP 401 自定义 JSON 响应演示               |
| GET            | `/api/demo/resp/header`        | `RespController@header`      | —                        | `cross`   | 回显 `x-app-id` / `x-app-os` / `x-app-version` 签名头 |
| POST           | `/api/demo/sso/access`         | `SsoController@access`       | —                        | `api-sso` | 通过 `api-sso` 中间件返回 `$this->pam->id`     |

#### 配套 Schema（`Http/Request/Api/Web/…`）

| Schema 类                         | 用途                |
|----------------------------------|-------------------|
| `DemoApiDocHowRequest`           | `/apidoc/how` 入参   |
| `DemoApiDocHowResponseBody`      | `/apidoc/how` 出参   |
| `DemoRespSuccessRequest`         | `/resp/success` 入参 |
| `DemoRespSuccessResponseBody`    | `/resp/success` 出参 |
| `DemoRespUnAuthResponseBody`     | `/resp/401` 出参     |
| `DemoRespHeaderResponseBody`     | `/resp/header` 出参   |
| `DemoSsoAccessResponseBody`      | `/sso/access` 出参   |

> 全局 `PoppySystemResponseBody`（来自 `poppy/system`）被多个接口作为错误响应 Schema 引用。

### api-app.php（App API 演示 — 前缀 `api/app/demo`，中间件 `py-ext-app.sign-json`）

> `RouteServiceProvider::mapApiRoutes()` 给该组同时注入前缀 `api/app/demo` 与中间件 `py-ext-app.sign-json`（来自 `poppy/ext-app`）。

| HTTP方法 | URI                     | 控制器                       | 请求类                          | 中间件                       | 说明                       |
|------|-------------------------|---------------------------|-----------------------------|---------------------------|--------------------------|
| GET  | `/api/app/demo/demo/index` | `Api\App\DemoController@index` | —                           | `py-ext-app.sign-json` | App 端健康检查，回 `Resp::success('OK')` |

Schema：`DemoAppDemoIndexResponseBody`（位于 `Http/Request/Api/App/Demo/DemoIndexResponseBody.php`）。

### backend.php（后台 — 前缀 `{prefix}/demo`，由 `RouteServiceProvider::mapWebRoutes()` 注入）

> `RouteServiceProvider` 给后台路由加了 `prefix => $this->prefix . '/demo'` 与 `middleware => 'backend-auth'`，其中 `$this->prefix` 通常由 `poppy.mgr-page` 注入为 `backend`。所以运行时前缀即 `backend/demo`。

| HTTP方法 | URI           | 控制器                              | 请求类 | 中间件                                | 说明                                          |
|------|---------------|----------------------------------|-----|------------------------------------|---------------------------------------------|
| GET  | `backend/demo/` | `Backend\DemoController@index`  | —   | `sys-auth:backend`、`sys-rbac`（来自 `poppy.mgr-page`） | 直接 echo `"Demo Backend Request Success"`        |

## Web 路由

### web.php（演示 — 前缀 `demo`）

> 由 `RouteServiceProvider::mapWebRoutes()` 注入前缀 `demo`。
> 第一组原计划挂 `sys-auth:jwt_web`，现已被注释——演示 JWT 在 Web 层是可选项。
> 第二组无中间件，全部按命名路由 `demo:web.*` 暴露。

| HTTP方法   | URI                          | 控制器                              | 路由名                          | 中间件                              | 说明                                       |
|--------|------------------------------|----------------------------------|------------------------------|----------------------------------|------------------------------------------|
| ANY    | `demo/token`                  | `Web\TokenController@index`      | `demo:web.token.index`       | （注释掉的 `sys-auth:jwt_web`）        | `JwtApiController::pam()` 演示，鉴权后返回 `Resp::success('ok')` |
| ANY    | `demo/content`                | `Web\ContentController@index`    | `demo:web.content.index`     | 无                                | FormBuilder 标准 Content 页（演示 Form Grid）        |
| ANY    | `demo/content/form`           | `Web\ContentController@form`     | `demo:web.content.form`      | 无                                | `FormEntrance` 单页入口                   |
| ANY    | `demo/form/{type}`            | `Web\FormController@index`       | `demo:web.form.index`        | 无                                | 反射 `\Demo\Forms\Form{Type}` 渲染对应表单         |
| ANY    | `demo/table/easy`             | `Web\TableController@easy`       | `demo:web.table.easy`        | 无                                | 简易 `TableWidget`                           |
| ANY    | `demo/table/manual`           | `Web\TableController@manual`     | `demo:web.table.manual`      | 无                                | `ListTableManualRequest` 校验 + 分页        |
| ANY    | `demo/table/pjax_error`       | `Web\TableController@pjaxError`  | `demo:web.table.pjax_error`  | 无                                | 演示 pjax 请求 sleep 4s 后报错               |
| ANY    | `demo/grid/more/{type?}`      | `Web\GridController@index`       | `demo:web.grid.index`        | 无                                | 反射 `\Demo\Http\Lists\ListGrid{Type}`    |
| ANY    | `demo/grid/no_file`           | `Web\GridController@noFile`      | `demo:web.grid.no_file`      | 无                                | 自定义列 + iframe action 演示                |
| ANY    | `demo/grid/iframe`            | `Web\GridController@iframe`      | `demo:web.grid.iframe`       | 无                                | `dump(input())` 调试入口                      |
| ANY    | `demo/search/{type?}`         | `Web\SearchController@index`    | `demo:web.search.index`      | 无                                | 反射 `\Demo\Http\Lists\ListSearch{Type}`  |
| ANY    | `demo/helper/env`             | `Web\HelperController@env`       | `demo:web.helper.env`        | 无                                | `FormEnvHelper` 演示                      |
| ANY    | `demo/helper/image`           | `Web\HelperController@image`     | `demo:web.helper.image`      | 无                                | `FormImageHelper` 演示                    |
| ANY    | `demo/helper/tree`            | `Web\HelperController@tree`      | `demo:web.helper.tree`       | 无                                | `FormTreeHelper` 演示                     |
| ANY    | `demo/helper/img_str`         | `Web\HelperController@imgStr`    | `demo:web.helper.img_str`    | 无                                | 图像转 base64 演示                            |
| ANY    | `demo/helper/img_bmp`         | `Web\HelperController@imgBmp`    | `demo:web.helper.img_bmp`    | 无                                | bmp 处理演示                                 |
| ANY    | `demo/mail/{slug?}/{page?}`   | `Web\MailController@index`       | `demo:web.mail.index`        | 无                                | 邮件模板预览                                  |
| ANY    | `demo/js`                     | `Web\JsController@index`        | `demo:web.js.index`          | 无                                | 前端脚本 / 组件文档                              |
| ANY    | `demo/js/popup`               | `Web\JsController@popup`         | `demo:web.js.popup`          | 无                                | 弹窗组件示例                                  |
| ANY    | `demo/js/location`            | `Web\JsController@location`      | `demo:web.js.location`       | 无                                | 跳转组件示例                                  |
| ANY    | `demo/exception/validation_when` | `Web\ExceptionController@validationWhen` | —            | 无                                | `$request->scene('edit')` 触发场景校验异常     |
| ANY    | `demo/exception/validation_auto` | `Web\ExceptionController@validationAuto` | —            | 无                                | `ExceptionAutoRequest` 自动校验              |
| ANY    | `demo/exception/validation`       | `Web\ExceptionController@validation`     | —            | 无                                | `ExceptionRequest` 基础校验                 |
| ANY    | `demo/exception/validation_policy` | `Web\ExceptionController@validationPolicy` | —       | 无                                | `ExceptionPolicyRequest` 触发 Policy 异常  |
| ANY    | `demo/exception/{type}`           | `Web\ExceptionController@index`         | `demo:web.exception.index`  | 无                                | 按 `type` 反射到同名方法触发异常（`firstOrFail` / `curl` / `settingKeyNotMatch` / `settingValueOutOfRange` / `tokenMismatch` / `postTooLarge` / `authentication` / `query` / `application`） |
| ANY    | `demo/log`                     | `Web\LogController@index`       | `demo:web.log.index`         | 无                                | 日志查看演示                                  |

### web-root.php（根级 Web — 无前缀）

> 由 `RouteServiceProvider::mapWebRoutes()` 通过空 prefix 组加载，**没有统一中间件、没有 `sys-auth` 守卫**。其中两条路由已经显式命名，最后一条没有命名（演示命名可选性）。

| HTTP方法 | URI                  | 控制器                       | 路由名                       | 说明                                              |
|------|----------------------|---------------------------|--------------------------|-------------------------------------------------|
| ANY  | `/`                   | `Web\HomeController@index` | —                        | 渲染视图 `demo::web.home.index`                        |
| ANY  | `/demo`               | `Web\HomeController@demo`  | —                        | 返回 `\Demo\Classes\Layout\Demo` 实例，标题/描述由调用方决定 |
| ANY  | `/demo/output/{info}` | `Web\HomeController@output` | `demo:web.home.output` | 纯 echo `{info}`（用于调试）                              |

## 发布的事件（本模块对外发布）

**无。** `src/Events/` 当前为空（只有 `.gitkeep`），demo 模块不主动触发任何新事件。

## 监听的事件（本模块消费）

| 监听器类                                       | 监听的事件                           | 业务动作                                                                                                | 产生的事件/任务 |
|--------------------------------------------|---------------------------------|----------------------------------------------------------------------------------------------------|------------|
| `Demo\Listeners\PassportVerify\PassportVerifyListener` | `Poppy\System\Events\PassportVerifyEvent` | 读取 `$event->passport` 与 `$event->type`；当 `$type === 'exist'` 时补 `'86-'` 前缀并校验 `PamAccount::where('mobile', $passport)->exists()`，否则依规则抛 `ApplicationException` | — |

> 事件绑定在 `src/ServiceProvider.php` 的 `$listens` 中声明（`PassportVerifyEvent::class => [PassportVerifyListener::class]`）。

## 队列任务

**无。** `src/Jobs/` 当前为空（只有 `.gitkeep`）；demo 模块不调度任何异步任务。

## Artisan 命令

| 命令签名                                  | 说明                                                              | 调度方式 |
|---------------------------------------|-----------------------------------------------------------------|------|
| `demo:mobile`（`Demo\Commands\UserMobileCommand`） | 把所有 `PamAccount::TYPE_BACKEND` 用户的 `mobile` 回填为 `'33023-' . sprintf("%'.07d", $id)`，仅当 `mobile` 为空时执行；用于演示命令注册 | 手动执行 |

## 跨模块调用（本模块调用其他模块）

| 本模块调用方                                              | 目标模块                | 目标类 / 中间件 / 扩展                                                                                              | 调用方法                                        | 场景                          |
|--------------------------------------------------------|---------------------|------------------------------------------------------------------------------------------------------------|---------------------------------------------|-----------------------------|
| `Demo\Http\Request\Backend\DemoController`             | `poppy/mgr-page`    | `Poppy\MgrPage\Http\Request\Backend\BackendController`                                                       | `extends BackendController`                  | 继承后台基类                    |
| `Demo\Http\Request\Web\HomeController` 等               | `poppy/mgr-page`    | `Poppy\MgrPage\Classes\{Form, Grid, Content, Layout}`、`BackendController` 等                                  | `new Form(new DemoGrid())` 等                 | 表单/表格/内容构造                 |
| `Demo\Http\Request\Web\TokenController`                | `poppy/system`      | `Poppy\System\Http\Request\ApiV1\JwtApiController`                                                            | `$this->pam()`                               | JWT 鉴权演示                   |
| `Demo\Http\Request\Api\Web\SsoController`              | `poppy/system`      | `Poppy\System\Http\Request\ApiV1\WebApiController`                                                            | `$this->pam`                                 | SSO 鉴权后取账号                  |
| `Demo\Http\Request\Api\App\DemoController`             | `poppy/framework`   | `Poppy\Framework\Application\ApiController` + `Poppy\Framework\Classes\Resp`                                  | `Resp::success(...)`                         | App 健康检查回包                  |
| `Demo\Http\RouteServiceProvider`                       | `poppy/framework`   | `Poppy\Framework\Application\RouteServiceProvider`                                                            | `extends RouteServiceProvider`               | 5 个路由文件挂载到全局                  |
| `Demo\Listeners\PassportVerify\PassportVerifyListener` | `poppy/system`      | `Poppy\System\Events\PassportVerifyEvent`、`Poppy\System\Models\PamAccount`                                  | `PamAccount::where('mobile', ...)->exists()` | 手机号存在性校验                  |
| `Demo\Commands\UserMobileCommand`                      | `poppy/system`      | `Poppy\System\Models\PamAccount`                                                                              | `PamAccount::where(..., 'mobile', ...)->update([...])` | 演示命令                        |
| `Demo\Classes\Layout\Demo::render`                     | `poppy/mgr-page`    | `app('poppy.core.module')->menus()->withType('demo', [])`                                                     | `menus()->withType('demo', [])`              | 注入 demo 模块菜单                |
| `Demo\Models\DemoGrid::pam`                            | `poppy/system`      | `Poppy\System\Models\PamAccount`                                                                              | `hasOne(PamAccount, 'id', 'account_id')`     | Grid 关联账号                  |
| `Demo\Http\Routes/api-app.php`                         | `poppy/ext-app`     | `py-ext-app.sign-json`（中间件）                                                                                | `Route::group([..., 'middleware' => ...])`   | App 接口签名                    |
| `Demo\Http\Routes/api.php`                             | `poppy/system`      | `api-sso`（中间件）                                                                                              | `Route::group([..., 'middleware' => ...])`   | SSO 鉴权                       |
| `Demo\Http\Routes/backend.php`                         | `poppy/mgr-page`    | `sys-auth:backend`、`sys-rbac`（中间件）                                                                          | `Route::group([..., 'middleware' => ...])`   | 后台鉴权 + RBAC                 |

## 被其他模块调用（本模块被引用）

**无。** 通过 `grep -rn "use Demo\\\\"` 扫描 `poppy/…` 与 `modules/…`（排除自身），本项目**没有任何模块**引用 `Demo\*`，这与 overview.md 中的"被依赖"段落保持一致。

## 其余契约性资源

- **Forms（~58 个）**：`src/Forms/Form*` 平铺命名，每个子类对应 `poppy.mgr-page` FormBuilder 中的一种控件。`FormController@index($type)` 通过反射 `\Demo\Forms\Form{$type}` 选择。
- **Lists（~27 个）**：`src/Http/Lists/ListGrid*`（`ListGridDefault`、`ListGridDemo`、`ListGridEditable`、`ListGridIndex`、`ListGridOperation`、`ListGridUser`）与 `ListSearch*`（`ListSearchBetween`、`ListSearchBetweenDate`、`ListSearchBetweenDateTime`、`ListSearchDate`、`ListSearchEqual`、`ListSearchGt`、`ListSearchHidden`、`ListSearchIn`、`ListSearchLike`、`ListSearchLt`、`ListSearchMonth`、`ListSearchQuery`、`ListSearchWhere`、`ListSearchWith`、`ListSearchYear`）——分别由 `GridController` 与 `SearchController` 按 `type` 反射装配。
- **Validation**：`src/Http/Validation/` 下 `ExceptionRequest`、`ExceptionPolicyRequest`、`ExceptionWhenRequest`、`ExceptionAutoRequest`、`ListTableManualRequest`。
- **Http Forms（业务级）**：`src/Http/Forms/FormDemoAli`、`FormSettingAvatar` + `Helpers/{FormEnvHelper, FormImageHelper, FormTreeHelper}`。
- **Policy 注册**：`Demo\Models\Policies\DemoGridPolicy::create()` 永远返回 `false`，仅作 Policy 注册示例（绑定在 `ServiceProvider::$policies`）。
- **Hooks**：`Hooks/Demo/ArrayDemo`、`Hooks/Demo/HtmlDemo`、`Hooks/MgrPage/HtmlCpA.php`、`Hooks/MgrPage/SettingsKeyA.php`、`Hooks/MgrPage/SettingsKeyB.php`、`Hooks/System/AuthAccessUser.php`——由 `configurations/hooks.yaml` 注册。
- **Menus / Permissions / Services / Module config**：`configurations/{menus,permissions,services,module,hooks}.yaml` —— 后台菜单、权限码、服务定义。
- **Seeds**：`Seeds/DemoDatabaseSeeder`、`Seeds/DemoDbDatabaseSeeder`、`Seeds/DemoGridDatabaseSeeder` —— 通过 `php artisan poppy:migrate module.demo` 触发。

## 待确认

- `Demo\Http\Exception\Handler.php` 是否真的被框架装载、是否有更具体的异常映射——需要进一步追溯到 `poppy/framework` 的全局 Handler 加载逻辑（发现位置：`Http/Exception/Handler.php`）。
- `web-root.php` 与 `web.php` 中**没有命名**的路由（`HomeController@index`、`HomeController@demo`、`ExceptionController@validationWhen/validationAuto/validation/validationPolicy`）是否在 framework 内部有任何隐式处理——目前直接用 `Route::any(...)` 注册，缺少 `->name(...)`（发现位置：`Http/Routes/web.php` 与 `web-root.php`）。
