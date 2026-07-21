# ad 模块（Poppy\Ad）

> 模块路径：`poppy/ad/`
> 命名空间：`Poppy\Ad\`
> 模块类型：Laravel 6 + Poppy 4.3 伪多模块单体中的"业务模块"

## 模块职责

广告位（`SysAdPlace`）与广告内容（`SysAdContent`）的"后台增删改查 + 上架"管理。
模块仅提供 **管理后台 (`backend`)** 入口与 **一个跨模块表单 Hook**（`poppy.ad.form_place_select`），不直接对外（`api_v1`）暴露数据。广告图本身由系统层 `poppy/system` 的 `py-system:api_v1.upload.image` 接口处理上传，本模块只保存 `src` URL 字符串。

## 目录结构

| 目录                         | 职责                                                                                | 文件数 |
|----------------------------|-----------------------------------------------------------------------------------|-----|
| `Action/`                  | 业务逻辑层（`Action\Ad` 处理内容 CRUD/toggle；`Action\Place` 处理广告位 CRUD + 删除前置校验）                  | 2   |
| `Models/`                  | Eloquent 模型 + Filter + Policy（`SysAdContent`、`SysAdPlace`、`AdContentFilter`、`AdPlaceFilter`、`AdPlacePolicy`） | 5   |
| `Hooks/`                   | 框架扩展点（`FormPlaceSelect` 注册成全局表单控件 `poppy.ad.form_place_select`）                        | 1   |
| `Http/Request/Backend/`    | 后台 Controller（`AdPlaceController`、`AdContentController`）                                | 2   |
| `Http/Validation/`         | FormRequest 验证器（`AdPlaceRequest`、`AdContentRequest`，均关闭自动校验，靠调用方手动 `validated()`）          | 2   |
| `Http/MgrPage/`            | MgrPage 列表/表单（`ListSysAdPlace`、`ListSysAdContent`、`FormPlaceEstablish`、`FormContentEstablish`） | 4   |
| `Http/RouteServiceProvider`| 路由注册（`backend-auth` 中间件 + `py-ad` 前缀）                                                | 1   |
| `ServiceProvider`          | 模块入口（仅注册 `RouteServiceProvider`，并在 `$policies` 中声明 `SysAdPlace → AdPlacePolicy`）          | 1   |
| `resources/migrations/`     | 建表迁移（`sys_ad_place` / `sys_ad_content`，日期 2023-03-24）                                  | 2   |
| `resources/lang/zh/`        | `seo.php` + `util.php` 翻译键（`sys_ad_content`/`sys_ad_place` 中文表名）                       | 2   |
| `configurations/`          | `module.yaml` + `menus.yaml` + `permissions.yaml` + `services.yaml` + `hooks.yaml`     | 5   |
| `Events/`, `Listeners/`, `Jobs/`, `Commands/` | 目录存在但 `.gitkeep`，无业务实现                                            | 0   |

> 注：模型数量 5 是指 `src/Models/` 目录下的 `*.php` 文件总数（含 Filter、Policy），Eloquent 模型本身只有 2 个。

## 技术栈

| 技术       | 版本/说明                                              |
|----------|----------------------------------------------------|
| PHP      | `>= 7.4`（来自 `poppy/ad/composer.json` `require.php`）   |
| Laravel  | `6.*`（根 `composer.json` `require.laravel/framework`）   |
| 模块框架    | `poppy/framework`（`*@dev`，应用基类 `PoppyServiceProvider`）  |
| ORM      | Eloquent（`FilterTrait` 由 `poppy/system` 提供，注入 `whereLike/whereBeginsWith/whereEndsWith/paginateFilter` 等查询作用域）|
| 队列      | 无（模块不 dispatch 任何 Job）                               |
| 缓存      | 无（模块不直接读写缓存）                                        |
| 认证      | 后台 `PamAccount` + 中间件 `backend-auth`（由 `poppy/mgr-page` 注入） |
| 其他关键依赖  | `poppy/system`（`PamAccount`、`SysConfig`、`DefaultFileProvider` 通过 `poppy.system.file` 契约解析） |

## 路由概览

| 路由文件                       | 类型    | 前缀                       | 路由数 | 说明                            |
|----------------------------|-------|--------------------------|-----|-------------------------------|
| `Http/Routes/backend.php` | 管理后台  | `{prefix}/py-ad`（`prefix` 来自 `poppy.framework.prefix`，默认 `mgr-page`） | 7   | 全模块对外入口：广告位 3 + 广告内容 4 |

完整 URL 形如：`{prefix}/py-ad/{place\|content}/{action}/{id?}`，其中 `prefix` 缺省为 `mgr-page`。
模块未提供 `api_v1.php` / `web.php`，未在前端路由表注册。

## 模型清单

| 模型                  | 数据表                | 关键关联                                                                                       | 说明                                                                                          |
|---------------------|--------------------|--------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------|
| `SysAdContent`      | `sys_ad_content`   | 外键 `place_id → sys_ad_place.id`（无显式 `belongsTo/hasMany` 方法定义，靠 `Action\Place::delete` 中 `where('place_id', $id)->exists()` 自查）  | 单条广告记录，含 `src/title/action/value/list_order/is_enable/start_at/end_at`，业务动作枚举常量 `ACTION_ROUTE`/`ACTION_URL`/`ACTION_NONE` |
| `SysAdPlace`        | `sys_ad_place`     | 反向：被 `SysAdContent::place_id` 引用；查询助手 `title()` 返回 `id => title` 映射；`returnAdContent(int $id)` 返回 `is_enable=YES` 且 `orderBy list_order` 的内容数组 | 广告位（位置），含 `title/thumb/width/height/introduce`，宽高供前端按尺寸投放参考                                       |

## 依赖的其他模块

| 模块                | 引用方式                                                                                | 说明                                                                                    |
|-------------------|-------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------|
| `poppy/system`    | `use Poppy\System\Classes\Traits\FilterTrait;` (模型)<br>`use Poppy\System\Models\SysConfig;` (`SysAdPlace::returnAdContent` 读取 `SysConfig::YES`)<br>`use Poppy\System\Models\PamAccount;` (`AdPlacePolicy` 与 `AdContentRequest`) | 模型混入 `FilterTrait` 获得 `whereLike/paginateFilter`；`SysConfig::YES` 用作"启用"判断；`PamAccount/PamRole` 用于后台 Policy |
| `poppy/mgr-page`  | `use Poppy\MgrPage\Classes\Widgets\FormWidget;` (Form) <br>`use Poppy\MgrPage\Classes\Grid\ListBase;` (List) | MgrPage 列表/表单基类，是后台 UI 的渲染底座。`AdContentController` 显式 `new Grid(new SysAdContent())` 后绑定 `ListSysAdContent::class` |
| `poppy/core`      | `use Poppy\Core\Services\Contracts\ServiceForm;` (`FormPlaceSelect`)                  | 服务表单 Hook 契约，`FormPlaceSelect` 通过它将自身注册为 `poppy.ad.form_place_select`                       |
| `poppy/aliyun-oss` | **间接依赖**：`FormContentEstablish` 与 `FormPlaceEstablish` 的 `image('src'/'thumb')` 字段由 `poppy/mgr-page` 的 `FormBuilder` 渲染，底层调用 `poppy/system` 的 `py-system:api_v1.upload.image` → 通过 `poppy.system.file` 契约解析到 `OssFileProvider`（当 `py-system::picture.save_type = aliyun`） | 广告图/缩略图实际上传走阿里云 OSS；本模块不直接 `use` 任何 `Poppy\AliyunOss\*` 类 |

## 被其他模块依赖

| 模块         | 引用方式                                                                 | 说明                                                                                                  |
|------------|----------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------|
| `modules/demo` | `poppy/demo/src/Forms/FormHook.php`: `$this->hook('place_id', '选择占位')->service('poppy.ad.form_place_select');` | 示例模块演示如何通过服务名调用本模块暴露的 Hook；该 Hook 在本模块 `configurations/hooks.yaml` + `services.yaml` 中声明 |
| `poppy/core` | `poppy/core/tests/Module/ModuleMenuTest.php` 中包含 `'py-ad:backend.place.index/...'` 测试样本            | 仅作为路由名常量参与核心模块的菜单测试，不形成业务依赖                                                       |

> **结论**：本模块在业务上**几乎不被其他模块依赖**（除 demo 示例），是后台运营的"自包含"子系统。

## 边界说明（不负责的事项）

- **不负责图片存储**：仅保存 `src` / `thumb` 字符串（`url` 形式，验证规则 `Rule::url()`）。上传由 `py-system:api_v1.upload.image` 走 `OssFileProvider` / `DefaultFileProvider`。
- **不提供前台投放接口**：`returnAdContent(int $id)` 是 `public static` 助手，仅供同仓库其他业务层按需调用；不提供 `api_v1` 路由。
- **不处理点击/曝光统计**：`action` 字段是 `route` / `url` / `none` 三选一的"动作类型"，仅做点击跳转目标描述，**没有任何点击计数/曝光记录逻辑**（无 `click_count` 字段、无 Job、无 Listener）。
- **不做广告投放时段过滤**：`start_at` / `end_at` 仅作为字段持久化（`is_enable=1` 即可被 `returnAdContent` 返回），前端/调用方需自行比较当前时间。
- **不做广告位分组/多租户隔离**：单表 `sys_ad_place` / `sys_ad_content`，无 `site_id` / `tenant_id` 列。
- **未注册 Console 命令、未注册事件/监听器、未发布事件**。

## 文档索引

- 业务规则 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
