# 对外契约

## 运行时前缀与路由约定

`Poppy\MgrPage\Http\RouteServiceProvider` 使用 `config('poppy.framework.prefix') ?: 'mgr-page'` 作为 URL 前缀。下表用 `{prefix}` 表示该值（默认 `/mgr-page`）；路由名称是稳定契约，尤其不得改动 `py-mgr-page:backend.*`。

## 管理后台入口路由

| HTTP 方法 | URI（默认前缀） | 控制器/方法 | 中间件 | 路由名 |
|---|---|---|---|---|
| ANY | `/{prefix}/` | `Poppy\MgrPage\Http\Request\Backend\HomeController@index` | `backend-auth` | `py-mgr-page:backend.home.index` |
| ANY | `/{prefix}/login` | `HomeController@login` | `web` | `py-mgr-page:backend.home.login` |
| ANY | `/{prefix}/captcha/send` | `CaptchaController@send` | `web` | `py-mgr-page:backend.captcha.send` |

## 管理后台路由（`Routes/backend.php`）

该文件整体位于 `/{prefix}/system`，使用 `backend-auth`，下表 URI 是相对 `/system` 的部分。

| HTTP 方法 | URI（默认前缀） | 控制器/方法 | 路由名 |
|---|---|---|---|
| ANY | `/{prefix}/system/cp` | `HomeController@cp` | `py-mgr-page:backend.home.cp` |
| ANY | `/{prefix}/system/password` | `HomeController@password` | `py-mgr-page:backend.home.password` |
| ANY | `/{prefix}/system/clear_cache` | `HomeController@clearCache` | `py-mgr-page:backend.home.clear_cache` |
| ANY | `/{prefix}/system/logout` | `HomeController@logout` | `py-mgr-page:backend.home.logout` |
| ANY | `/{prefix}/system/setting/{path?}/{index?}` | `HomeController@setting` | `py-mgr-page:backend.home.setting` |
| ANY | `/{prefix}/system/easy-web/{type}` | `HomeController@easyWeb` | `py-mgr-page:backend.home.easy-web` |
| GET | `/{prefix}/system/role` | `RoleController@index` | `py-mgr-page:backend.role.index` |
| ANY | `/{prefix}/system/role/establish/{id?}` | `RoleController@establish` | `py-mgr-page:backend.role.establish` |
| ANY | `/{prefix}/system/role/delete/{id?}` | `RoleController@delete` | `py-mgr-page:backend.role.delete` |
| ANY | `/{prefix}/system/role/menu/{id}` | `RoleController@menu` | `py-mgr-page:backend.role.menu` |
| GET | `/{prefix}/system/pam` | `PamController@index` | `py-mgr-page:backend.pam.index` |
| ANY | `/{prefix}/system/pam/establish/{id?}` | `PamController@establish` | `py-mgr-page:backend.pam.establish` |
| ANY | `/{prefix}/system/pam/password/{id}` | `PamController@password` | `py-mgr-page:backend.pam.password` |
| ANY | `/{prefix}/system/pam/note/{id}` | `PamController@note` | `py-mgr-page:backend.pam.note` |
| ANY | `/{prefix}/system/pam/disable/{id}` | `PamController@disable` | `py-mgr-page:backend.pam.disable` |
| ANY | `/{prefix}/system/pam/enable/{id}` | `PamController@enable` | `py-mgr-page:backend.pam.enable` |
| ANY | `/{prefix}/system/pam/mobile/{id}` | `PamController@mobile` | `py-mgr-page:backend.pam.mobile` |
| ANY | `/{prefix}/system/pam/clearMobile/{id}` | `PamController@clearMobile` | `py-mgr-page:backend.pam.clear_mobile` |
| ANY | `/{prefix}/system/pam/log` | `PamController@log` | `py-mgr-page:backend.pam.log` |
| ANY | `/{prefix}/system/pam/setting_log` | `PamController@settingLog` | `py-mgr-page:backend.pam.setting_log` |
| ANY | `/{prefix}/system/pam/token` | `PamController@token` | `py-mgr-page:backend.pam.token` |
| ANY | `/{prefix}/system/pam/ban/{id}/{type}` | `PamController@ban` | `py-mgr-page:backend.pam.ban` |
| ANY | `/{prefix}/system/pam/delete_token/{id}` | `PamController@deleteToken` | `py-mgr-page:backend.pam.delete_token` |
| ANY | `/{prefix}/system/pam/setting/{id}` | `PamController@setting` | `py-mgr-page:backend.pam.setting` |
| ANY | `/{prefix}/system/ban` | `BanController@index` | `py-mgr-page:backend.ban.index` |
| ANY | `/{prefix}/system/ban/establish/{id?}` | `BanController@establish` | `py-mgr-page:backend.ban.establish` |
| ANY | `/{prefix}/system/ban/setting` | `BanController@setting` | `py-mgr-page:backend.ban.setting` |
| ANY | `/{prefix}/system/ban/status` | `BanController@status` | `py-mgr-page:backend.ban.status` |
| ANY | `/{prefix}/system/ban/type` | `BanController@type` | `py-mgr-page:backend.ban.type` |
| ANY | `/{prefix}/system/ban/delete/{id}` | `BanController@delete` | `py-mgr-page:backend.ban.delete` |
| ANY | `/{prefix}/system/upload/store` | `UploadController@store` | `py-mgr-page:backend.upload.store` |

## 开发后台路由（`Routes/develop.php`）

该文件位于 `/{prefix}/develop`，整体使用 `backend-auth`。

| HTTP 方法 | URI（默认前缀） | 控制器/方法 | 路由名 |
|---|---|---|---|
| ANY | `/{prefix}/develop/` | `Poppy\MgrPage\Http\Request\Develop\HomeController@index` | `py-mgr-page:develop.home.cp` |
| ANY | `/{prefix}/develop/optimize` | `HomeController@optimize` | `py-mgr-page:develop.home.optimize` |
| GET | `/{prefix}/develop/env/phpinfo` | `EnvController@phpinfo` | `py-mgr-page:develop.env.phpinfo` |
| GET | `/{prefix}/develop/env/db` | `EnvController@db` | `py-mgr-page:develop.env.db` |
| ANY | `/{prefix}/develop/log` | `LogController@index` | `py-mgr-page:develop.log.index` |

## API/外部回调路由

`mgr-page` 没有 `api*.php` 或回调路由。`FormBuilder` 的编辑器/上传控件调用的是 `py-system:api_v1.upload.image`、`py-system:api_v1.upload.file`，这些接口由 `Poppy\System` 提供，不属于本模块路由契约。

## Hook/服务契约

当前代码使用 Core 的服务契约，而非名为 `PyCoreMenuHook`/`PyCorePermissionHook` 的 PHP 类型。

| 服务 ID | 类型 | 本模块 Provider | 契约与数据 |
|---|---|---|---|
| `poppy.mgr-page.settings` | `array` | `Poppy\MgrPage\Hooks\MgrPage\SettingSystem` | 实现 `Poppy\Core\Services\Contracts\ServiceArray`；`key(): string` 返回 `poppy.mgr-page`，`data(): array` 返回标题“系统”和 `FormSettingSite`、`FormSettingPam` |
| `poppy.mgr-page.html_js_vars` | `html` | `Poppy\MgrPage\Hooks\MgrPage\HtmlJsVar` | 实现 `ServiceHtml`；`output(): string` 输出 `window.POPPY.MGRPAGE.picturePreviewRule` |
| `poppy.mgr-page.html_cp` | `html` | 当前模块无 Provider | 可由其他模块通过 `ServiceHtml::output()` 追加主页卡片 |
| `poppy.mgr-page.html_top_nav` | `html` | 当前模块无 Provider | 可由其他模块追加顶部导航 HTML |
| `poppy.mgr-page.html_login` | `html` | 当前模块无 Provider | 可由其他模块追加登录页 HTML |

`sys_hook($id)` 经 `Poppy\Core\Services\Factory\ServiceFactory::parse()` 解析：数组服务实例化每个 `ServiceArray` 并按 `key()` 聚合；HTML 服务拼接每个 `ServiceHtml::output()`。外部模块的 `hooks.yaml` 可以追加同一服务 ID；已扫描到的 MgrPage 设置消费者为 `Poppy\AliyunPush\Hooks\MgrPage\SettingsAliyunPush`，其 key 为 `poppy.aliyun-push`，表单为 `FormSettingAliyunPush`。

## 菜单与权限契约

- `configurations/menus.yaml` 声明 Backend/Develop 菜单树，使用 `route`、`param`、`permission`；例如角色、账号、登录日志和风险拦截项保留 `py-mgr-page:backend.*` 路由名。
- 外部模块可以在自己的 `menus.yaml` 使用 `injection: poppy.mgr-page/backend||setting` 注入系统设置分组；Core `ModulesMenu` 负责聚合、路由 URL 化和按 `PamAccount::capable()` 过滤。
- `mgr-page` 本身没有 `permissions.yaml`；其菜单引用 `backend:py-system.global.manage`、`backend:py-system.pam.*`、`backend:py-system.role.*`，定义在 `Poppy\System` 的权限配置中。
- `Poppy\Core\Rbac\Middlewares\RbacPermission` 读取控制器公开静态 `$permission`，以当前 action 名匹配方法权限，随后回退 `global` 权限；未声明或权限不存在时放行到控制器。

## FormBuilder 公共方法（主要扩展）

`FormBuilder` 还继承 Laravel Collective 的基础方法；以下只列本模块定义、且被后台页面复用的主要方法。

| 方法 | 用途 |
|---|---|
| `tree()`、`radios()`、`checkboxes()` | 树选择、单选组、多选组 |
| `code()`、`captcha()`、`editor()` | 代码、验证码和 WangEditor 控件 |
| `order()`、`scopes()` | 列表排序链接、Scope 标签 |
| `thumb()`、`upload()`、`multiThumb()`、`parseMultiThumb()`、`showThumb()` | 单图/文件/多图上传、拖拽粘贴和预览 |
| `timePicker()`、`timeRangePicker()`、`datetimePicker()`、`datetimeRangePicker()` | 时间/日期时间选择器 |
| `datePicker()`、`dateRangePicker()`、`yearPicker()`、`monthPicker()`、`colorPicker()` | 日期、范围、年份、月份和颜色选择器 |
| `tags()`、`multiSelect()`、`selectJump()` | 标签、xm-select 多选和跳转选择（`selectJump()` 目前仅构造父类 select） |
| `keyword()`、`question()`、`tip()` | 可拖拽关键词、答题组件、提示 HTML |

## BackendController 契约

| 成员 | 可见性/签名 | 约定 |
|---|---|---|
| `$permission` | 继承 `Poppy\Framework\Application\Controller` 的 `public static array` | 控制器以 `global` 和 action 名声明 RBAC 权限；`RoleController`、`PamController` 等在构造器中赋值 |
| `$pam` | `protected ?PamAccount` | 构造器中间件写入当前请求用户，并向视图分享 `_pam` |
| `__construct()` | `public` | 调用父类初始化路由/分页/IP/时间，设置 backend execution context，注册用户共享中间件并调用 `withViews()` |
| `pam()` | `public function pam(): ?PamAccount` | 从 `PamAccount::GUARD_BACKEND` 读取当前用户；源码注释说明该兼容入口逐步废弃 |
| `seo(...$args)` | `protected` | 使用系统站点设置生成 title/description；供 `withViews()` 调用 |

## Facade

| Facade | accessor | 注册方式 |
|---|---|---|
| `Poppy\MgrPage\Facade\FormFacade`（别名 `Form`） | `poppy.mgr-page.form` | `ServiceProvider::registerForm()` 以 singleton 注册 `FormBuilder`，`composer.json` 的 Laravel alias 暴露 `Form` |

## Artisan 命令

| 命令签名 | 所属/说明 | 调度方式 |
|---|---|---|
| `py-mgr:mix` | `Poppy\MgrPage\Commands\MixCommand`，反向复制两个前端资源文件 | 手动 |
| `py-core:permission {do}` | Core 命令，支持 `list`、`init`、`menus`；用于权限初始化/菜单权限检查，非 mgr-page 所有 | 手动或被 `py-system:install` 调用 |
| `py-system:install` | System 安装命令，初始化角色后调用 `py-core:permission init`，非 mgr-page 所有 | 手动安装 |

## 发布的事件（本模块控制器触发）

| 事件类 | 携带数据 | 触发位置 | 监听方 |
|---|---|---|---|
| `Poppy\System\Events\BePamLogoutEvent` | backend account ID（整数） | `HomeController::logout()` | 当前 mgr-page/System ServiceProvider 中未发现同名绑定 |
| `Poppy\System\Events\PamTokenBanEvent` | `PamToken`、类型 `token` | `PamController::deleteToken()` | System 的事件体系；具体监听关系需确认 |

## 监听的事件（本模块消费）

本模块没有 `src/Events`、`src/Listeners`，ServiceProvider 也没有 `$listens` 事件映射；直接消费的领域事件为“无”。

## 队列任务

本模块没有 `src/Jobs`，也没有 Job dispatch 契约。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方 | 目标模块 | 目标类/服务 | 调用方法/场景 |
|---|---|---|---|
| `FormBuilder` | `system` | `ApiSignContract`、`FileManager`、`PamAccount` | 编辑器/图片/文件控件签名、上传和 backend 用户 token |
| `HomeController` | `core` / `system` | `CoreTrait` 菜单仓库、`Setting`、`Pam` | 计算用户菜单、站点配置、登录/登出 |
| `RoleController` | `system` | `Role`、`PamRole`、`PamAccount` | 角色列表、角色表单、权限保存 |
| `PamController` | `system` | `Pam`、`Ban`、`Sso`、`PamAccount`/`PamLog`/`PamToken` | 账号、封禁、登录凭证和相关操作 |
| `FormSettingBase` | `system` | `SettingContract`、`SettingRepository` | 按分组读写设置 |

## 被其他模块调用（本模块被引用）

| 调用方模块 | 代表类 | 本模块目标 | 场景 |
|---|---|---|---|
| `category`、`content`、`area` | Backend Controller、`ListSys*`、`Form*` | `BackendController`、`Grid`、`ListBase`、`FormWidget`、字段/过滤器 | 标准管理 CRUD 页面 |
| `ad`、`app`、`version`、`sensitive-word` | 各模块 Backend Controller、List/Form | 同上 | 广告、应用、版本和敏感词后台 |
| `sms`、`aliyun-oss` | Store/Upload Controller、设置表单 | `BackendController`、`FormSettingBase`、Grid/FormWidget | 短信与文件服务配置/管理 |
| `aliyun-push` | `Hooks\MgrPage\SettingsAliyunPush`、`FormSettingAliyunPush` | `ServiceArray`、`FormSettingBase` | 将推送设置挂到 `poppy.mgr-page.settings` |
| `system` | `Action\Pam` | `FormSettingLog` | 复用登录日志设置表单 |

## 待确认

- `{prefix}`、最终分页上限以及事件监听方取决于宿主配置/其他模块是否在运行时追加；本文只记录当前源码可确认的默认值和绑定。
- 当前仓库没有 `PyCoreMenuHook`、`PyCorePermissionHook`、`PyCoreSettingHook` 这些类名；若外部文档要求这些旧命名，需要确认是否存在未纳入本分支的兼容层。
