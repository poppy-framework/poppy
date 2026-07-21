# 业务逻辑

## FormBuilder 扩展策略

`FormBuilder` 继承 `Collective\Html\FormBuilder`，而不是重新实现基础表单 API。这样既保留 Laravel Collective 的 `text`、`select`、`radio`、`open`、`close` 等约定，又把管理后台特有的交互封装成可复用方法。

- 普通选择控件直接复用父类输出；`tree()` 先用 `TreeHelper` 将扁平树整理成选择项，`radios()`、`checkboxes()` 统一处理后台值的字符串/数组形态。
- `editor()`、`thumb()`、`upload()` 和多图上传方法负责生成 layui/WangEditor 所需的 HTML/JavaScript，并从当前 backend 用户取得 JWT；没有用户时使用空 token，签名和上传地址交由 `Poppy\System` 的 `ApiSignContract`/文件服务处理。
- 日期、时间、颜色、标签、多选、关键词和排序等控件统一输出管理端的 layui 结构，避免各业务模块各自拼接前端协议。
- `FormBuilder` 由 `ServiceProvider::registerForm()` 作为 `poppy.mgr-page.form` 单例注册，Facade 只暴露这个容器入口；因此扩展方法和 Session token 上下文在所有后台视图中一致。

## FormWidget 与设置表单

后台表单类通过 `form()` 声明字段，通过可选的 `data()` 提供编辑初始值，通过 `handle()` 执行提交后的领域操作。这样表单外壳、字段验证和业务保存分离，业务模块只需要编写字段和动作。

- GET 请求：`FormWidget::render()` 调用 `form()` 建立字段，使用 `data()` 填充字段，最后渲染管理端表单视图。
- POST 请求：先按字段规则验证；验证失败时 Ajax 表单返回错误响应，非 Ajax 表单回退并带回输入和错误；验证成功才调用 `handle()`。
- `ajax`、`inbox`、`withContent` 控制响应包装方式，不改变字段和业务动作的职责。
- `FormSettingBase` 将每个可保存字段写入 `SettingContract` 的 `group.key`；未提交的 checkbox 归一化为空数组，其他缺失值归一化为空字符串，使“清空设置”也能被持久化。

完整的表单提交链路见 [flows.md](flows.md)。

## 菜单注册规则

菜单的事实来源是各模块的 `configurations/menus.yaml`，不是页面控制器里硬编码的导航。这样一个模块可以声明自己的标题、分组、路由和所需权限，并通过 `injection: poppy.mgr-page/backend||setting` 将功能插入系统后台的既有分组。

Core 的菜单仓库会把启用模块的菜单定义聚合、规范化并缓存；有权限的菜单项才对普通后台用户显示，超级管理员跳过能力过滤。菜单与权限键保持同源，避免页面可见但请求永远被拒绝。

## 权限注册规则

权限定义由模块的 `permissions.yaml` 提供；`mgr-page` 的菜单引用 `backend:py-system.*`，对应的定义归 `Poppy\System` 所有。权限不是通过一个名为 `PyCorePermissionHook` 的 PHP 类注册，而是由 Core 的 `PermissionManager` 汇总 YAML 后初始化到 RBAC 存储。

- 首次安装或显式初始化时，系统命令调用 `py-core:permission init`，将当前模块集合中的权限刷新到权限表并同步根角色。
- 后台控制器通过继承控制器上的静态 `$permission` 声明全局权限和方法级权限；`sys-rbac` 根据当前控制器与 action 名称进行检查。
- 列表中的按钮还会使用用户的 `can()` 判断隐藏不允许的操作；这只是显示层优化，真正的请求保护仍由中间件和领域 Policy/Action 完成。

## 设置注册规则

设置扩展使用 Core 的服务/Hook 机制。`poppy.mgr-page.settings` 在 `services.yaml` 中声明为数组服务，Hook 类实现 `ServiceArray::key()` 和 `ServiceArray::data()`：

- `SettingSystem` 注册“系统”设置组以及 `FormSettingSite`、`FormSettingPam`。
- 其他模块可以在自己的 `hooks.yaml` 追加同一服务 ID，例如 `SettingsAliyunPush` 注册 `FormSettingAliyunPush`，无需修改 `mgr-page`。
- `SettingView` 按 Hook key 找到同组表单；提交时将当前表单交回 `FormSettingBase`，由设置仓库校验和保存。

HTML Hook 使用 `ServiceHtml::output()` 拼接页面片段。`HtmlJsVar` 读取系统图片预览规则并输出 `window.POPPY.MGRPAGE`，所以前端全局变量由服务扩展点集中生成。

## BackendController 基础行为

所有管理端控制器继承 `BackendController`，构造时建立 `backend` 执行上下文，并通过控制器中间件把请求用户写入 `$pam`、共享给视图的 `_pam`。随后 `withViews()` 共享 `_ip`、`_now`、`_pagesize`、`_route`，并依据当前路由初始化 SEO。

`pam()` 保留为读取 backend guard 当前用户的公共方法；`seo()` 使用系统站点名称和描述，统一生成页面标题与描述。领域控制器再通过静态 `$permission` 追加自己的全局/方法权限。

## 分页规则

框架配置默认 `poppy.framework.page_size` 为 **15**，`poppy.framework.page_max` 为 **3000**；`PageInfo::pagesize()` 实际读取宿主配置，因此运行时可覆盖，若宿主完全移除该配置键则不会由 `PageInfo` 提供额外回退值。`Grid::$perPage` 初始为 15，列表可提供 `[15, 30, 50, 100, 200]` 选项；请求传入的 `pagesize` 会被限制在配置的最大值内。Grid 的查询分支使用 `PageInfo::pagesize()`，因此默认值、请求覆盖和上限由框架统一控制。

## 定时任务 / Artisan 命令

| 命令/任务 | 调度方式 | 业务动作 |
|---|---|---|
| `Poppy\MgrPage\Commands\MixCommand` (`py-mgr:mix`) | 手动 | 将 `public/assets/libs/boot/style.css`、`app.min.js` 反向复制到模块 `resources/libs/boot`，用于资源回收/定制 |
| `py-core:permission init` | 由 `py-system:install` 调用或手动 | 不是 mgr-page 自有命令；初始化模块权限数据，详见 [contracts.md](contracts.md) |

`mgr-page::ServiceProvider::registerSchedule()` 注册了 `console.schedule` 监听，但当前没有在其中追加任务。

## 中间件规则

| 中间件/组 | 应用范围 | 规则 |
|---|---|---|
| `backend-auth` | 后台受保护页面与 Develop 页面 | 依次组合 `web`、`sys-auth:backend`、`sys-auth_session`、`sys-ban:backend`、`sys-rbac`、`py-mgr-lifetime` |
| `sys-rbac` | `backend-auth` 内 | 读取当前控制器静态 `$permission`，优先检查 action 对应的权限，未定义或不存在时回退全局权限 |
| `web` | 登录、验证码及后台页面基础层 | 提供 Session/CSRF 等 Web 状态 |

## 待确认

- `poppy.framework.prefix` 和 `poppy.framework.page_size` 可被宿主应用覆盖；本文按源码默认的 `mgr-page`、15 描述，部署环境的最终值需要运行时配置确认。
- 需求中提到的 `PyCoreMenuHook`、`PyCorePermissionHook`、`PyCoreSettingHook` 在当前仓库未找到；实际实现是 `menus.yaml`/`permissions.yaml` 加 `ServiceArray`/`ServiceHtml` Hook，是否存在未同步的兼容分支需要确认。
