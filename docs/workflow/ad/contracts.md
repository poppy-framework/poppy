# 对外契约（ad 模块）

本文件是 ad 模块对外契约的 **唯一来源**：后台路由、菜单/权限、Hooks、模型关系、跨模块引用。
业务规则说明在 [business.md](business.md)，执行链路在 [flows.md](flows.md)。

> URL 前缀：`{prefix}/py-ad`，其中 `prefix = config('poppy.framework.prefix') ?: 'mgr-page'`（来自 `poppy/framework/src/Application/RouteServiceProvider.php` 构造器）。下文统一写为 `/mgr-page/py-ad/...` 形式。

---

## 管理后台路由（`Http/Routes/backend.php`）

全部路由位于 `Route::group(['prefix' => $this->prefix . '/py-ad', 'middleware' => 'backend-auth'])` 之下，请求类命名空间 `Poppy\Ad\Http\Request\Backend`，命名遵循 `py-ad:backend.{resource}.{action}`。

| HTTP 方法                | URI                                | 请求类/控制器                                       | 路由名                              | 说明                          |
|------------------------|------------------------------------|-----------------------------------------------|----------------------------------|-----------------------------|
| `any`                  | `/mgr-page/py-ad/place`            | `AdPlaceController@index`                     | `py-ad:backend.place.index`     | 广告位列表（`Grid + ListSysAdPlace`） |
| `any`                  | `/mgr-page/py-ad/place/establish/{id?}` | `AdPlaceController@establish`                 | `py-ad:backend.place.establish`  | 创建/编辑广告位（`FormPlaceEstablish`）|
| `any`                  | `/mgr-page/py-ad/place/delete/{id}` | `AdPlaceController@delete`                    | `py-ad:backend.place.delete`     | 删除广告位（先校验 `place_id` 仍有内容则拒绝）|
| `any`                  | `/mgr-page/py-ad/content`          | `AdContentController@index`                   | `py-ad:backend.content.index`    | 广告内容列表（`Grid + ListSysAdContent`，可按 `?place_id=` 过滤）|
| `any`                  | `/mgr-page/py-ad/content/establish/{id?}` | `AdContentController@establish`               | `py-ad:backend.content.establish` | 创建/编辑广告内容（`FormContentEstablish`，必传 `place_id`）|
| `any`                  | `/mgr-page/py-ad/content/delete/{id}` | `AdContentController@delete`                  | `py-ad:backend.content.delete`   | 删除广告内容                    |
| `any`                  | `/mgr-page/py-ad/content/toggle/{id}` | `AdContentController@toggle`                  | `py-ad:backend.content.toggle`   | 启/停切换（`is_enable` 取反）        |

> 路由定义均使用 `any()`，对 GET/POST 不做区分；与 MgrPage 后台统一使用表单/JS 提交一致。

## API 路由（`api_v1.php`）

**无**。模块未提供 `api_v1.php`；广告投放的最小可用集通过模型静态方法 `SysAdPlace::returnAdContent(int $id)` 暴露（详见"模型关系"）。

## Web 路由（`web.php`）

**无**。模块未提供 `web.php`，未参与前台页面路由。

## 其他路由文件

**无**。仅 `Http/Routes/backend.php` 一份。

---

## 菜单（`configurations/menus.yaml`）

| injection                    | 标题   | 路由                              | 权限                              |
|------------------------------|------|---------------------------------|---------------------------------|
| `poppy.mgr-page/backend\|\|setting` | 占位管理 | `py-ad:backend.place.index`     | `backend:py-ad.place.manage`    |

> 单菜单挂载点 `setting`：模块未提供独立顶部菜单组，仅在 MgrPage 后台"设置"分组下显示一项。

## 权限（`configurations/permissions.yaml`）

| Slug                       | 标题       | 说明              |
|----------------------------|----------|-----------------|
| `backend:py-ad`            | 广告位管理    | 父 slug         |
| `backend:py-ad.place.manage` | 位置管理 / 位置管理描述 | 唯一叶子权限；管理位置/内容 |

> 当前实现是"单一权限管理"：`AdContentController` 显式使用 `backend:py-ad.place.manage`，`AdPlacePolicy::$permissionMap` 也映射到该权限。

## 服务 / Hook（`configurations/services.yaml` + `configurations/hooks.yaml`）

| 服务 Key                          | 类型   | 渲染器 (`builder`)                       | 用途                                                  |
|----------------------------------|------|---------------------------------------|-----------------------------------------------------|
| `poppy.ad.form_place_select`     | form | `Poppy\Ad\Hooks\FormPlaceSelect`      | 渲染"选择广告位"下拉控件，选项为 `SysAdPlace::pluck('title', 'id')` |

> 该 Hook 已被 `modules/demo/src/Forms/FormHook.php` 调用作为示例（`->service('poppy.ad.form_place_select')`）。其他业务模块如需在表单中选择广告位，可复用同一服务名。

## 发布的事件（本模块对外发布）

**无**。模块无 `Events/` 目录下的业务类（仅 `.gitkeep`）。

## 监听的事件（本模块消费）

**无**。模块无 `Listeners/` 目录下的业务类。

## 队列任务

**无**。模块无 `Jobs/` 目录下的业务类。

## Artisan 命令

**无**。模块未在 `ServiceProvider::register()` 中注册任何 Command。

---

## 跨模块调用（本模块调用其他模块）

| 本模块调用方                                | 目标模块           | 目标类 / 服务                                | 调用方式                                          | 场景                       |
|----------------------------------------|----------------|----------------------------------------|-----------------------------------------------|--------------------------|
| `Models\SysAdContent` / `SysAdPlace` | `poppy/system` | `Poppy\System\Classes\Traits\FilterTrait` | `use Trait;`                                 | 注入 `whereLike/paginateFilter` 等查询作用域 |
| `Models\SysAdPlace::returnAdContent`   | `poppy/system` | `Poppy\System\Models\SysConfig`         | `SysConfig::YES`（类常量）                       | "启用"枚举值                  |
| `Models\Policies\AdPlacePolicy`        | `poppy/system` | `Poppy\System\Classes\Traits\PolicyTrait` + `PamAccount/PamRole` | `use Trait;` + 类型声明                 | 后台账号角色判断                |
| `Http\Validation\AdContentRequest`     | `poppy/system` | `Poppy\System\Models\SysConfig`         | `SysConfig::kvYn()`（`is_enable` 取值范围）         | "是否启用"枚举值                |
| `Http\MgrPage\ListSysAdContent`、`ListSysAdPlace`、`FormPlaceEstablish`、`FormContentEstablish` | `poppy/mgr-page` | `ListBase`、`FormWidget`、`Grid`、`Form\Field\File` | 继承 / `new Grid(...)` / 字段类型 `image()` | 后台 UI 渲染；`image('src')` 字段触发 `py-system:api_v1.upload.image` |
| `Hooks\FormPlaceSelect`                | `poppy/core`   | `Poppy\Core\Services\Contracts\ServiceForm` | `implements`                                | 框架扩展点契约                  |
| `Hooks\FormPlaceSelect`                | `poppy/mgr-page` | `poppy.mgr-page.form` 服务               | `app('poppy.mgr-page.form')->select(...)`     | 渲染下拉控件                   |
| `FormContentEstablish` / `FormPlaceEstablish`（间接） | `poppy/aliyun-oss` | `OssFileProvider`（通过 `py-system::picture.save_type = aliyun` 解析） | `poppy/system` 内部契约 `poppy.system.file`     | 广告图/缩略图上传                |

> **关键事实**：本模块**没有任何 `use Poppy\AliyunOss\*`** 的代码。OSS 依赖通过 `poppy/system` 的 `ServiceProvider::registerContracts()`（`app('poppy.system.file')`）的"按 `save_type` 选择 Provider"机制实现。

## 被其他模块调用（本模块被引用）

| 调用方模块            | 调用方类 / 文件                                                              | 本模块目标类                                                                                       | 调用方式                                                     | 场景                          |
|------------------|------------------------------------------------------------------------|------------------------------------------------------------------------------------------------|----------------------------------------------------------|-----------------------------|
| `modules/demo`   | `Poppy\Demo\Forms\FormHook` (推测路径：`modules/demo/src/Forms/FormHook.php`) | `Poppy\Ad\Hooks\FormPlaceSelect`（通过服务名）                                                       | `->service('poppy.ad.form_place_select')`                | 表单字段"选择占位"                 |
| `poppy/core`     | `Poppy\Core\Tests\Module\ModuleMenuTest`                              | `py-ad:backend.place.index`（仅作为菜单生成测试的样例 URL）                                                  | 字符串硬编码测试断言                                                 | 验证 MgrPage 菜单 URL 解析        |

---

## 模型关系

### 字段表

`sys_ad_place`（来自 `resources/migrations/2023_03_24_145138_create_sys_ad_place_table.php`）：

| 字段           | 类型                         | 默认    | 说明       |
|--------------|----------------------------|-------|----------|
| `id`         | `increments`                | —     | 主键       |
| `title`      | `string(100)`               | `''`  | 广告位名称   |
| `thumb`      | `string(200)`               | `''`  | 广告位示意图  |
| `introduce`  | `string(255)`               | `''`  | 广告位介绍   |
| `width`      | `unsignedSmallInteger`      | `0`   | 宽度       |
| `height`     | `unsignedSmallInteger`      | `0`   | 高度       |
| `created_at` / `updated_at` | `timestamps`     | —     | Laravel 默认 |

`sys_ad_content`（来自 `resources/migrations/2023_03_24_145151_create_sys_ad_content_table.php`）：

| 字段           | 类型                  | 默认    | 说明                            |
|--------------|---------------------|-------|-------------------------------|
| `id`         | `increments`         | —     | 主键                            |
| `title`      | `string(100)`        | `''`  | 标题                            |
| `place_id`   | `unsignedInteger`    | `0`   | 所属广告位（逻辑外键 → `sys_ad_place.id`） |
| `src`        | `string(255)`        | `''`  | 广告图 URL（已上传后的远程地址）            |
| `introduce`  | `string(255)`        | `''`  | 介绍                            |
| `start_at`   | `dateTime` nullable  | `NULL` | 显示开始时间                        |
| `end_at`     | `dateTime` nullable  | `NULL` | 显示结束时间                        |
| `action`     | `string(50)`         | `''`  | 动作类型（`route`/`url`/`none`）      |
| `value`      | `string(255)`        | `''`  | 动作值（路由名或 URL）                 |
| `list_order` | `unsignedSmallInteger` | `0`  | 排序                            |
| `is_enable`  | `unsignedTinyInteger` | `0`  | 是否启用（`0`/`1`）                 |
| `created_at` / `updated_at` | `timestamps` | — | Laravel 默认                       |

### 关联

- **逻辑关系**：`sys_ad_place` (1) — (N) `sys_ad_content`（无外键约束，依赖 `place_id` 字符串）。
- **未声明 Eloquent 关联方法**：`SysAdContent` 与 `SysAdPlace` 均未声明 `hasMany` / `belongsTo`。
- **实际查询路径**：
  - `SysAdPlace::returnAdContent(int $id)` → `SysAdContent::where('place_id', $id)->orderBy('list_order')->where('is_enable', SysConfig::YES)->select(['id', 'src', 'action', 'value', 'title'])->get()`：从位查内容。
  - `Action\Place::delete` → `SysAdContent::where('place_id', $id)->exists()`：删除位前的反向校验。
  - `Action\Ad::establish` 直接持久化 `place_id` 字段，未做"位是否存在"校验（依赖外层 `FormContentEstablish::__construct` 的 `SysAdPlace::findOrFail($placeId)` 兜底）。

### 模型助手 / 枚举

| 模型/方法                                | 用途                                                                                       |
|---------------------------------------|------------------------------------------------------------------------------------------|
| `SysAdPlace::title()`                | `static`，返回 `[id => title]` 映射；供 MgrPage 后台表单构造下拉选项。                                        |
| `SysAdPlace::returnAdContent(int $id)` | `static`，按 `list_order` 升序、过滤 `is_enable=YES`，返回内容数组（`[id, src, action, value, title]`）。           |
| `SysAdContent::kvAction($key = null)` | `static`，返回 `route`/`url`/`none` → 中文标签的映射；`FormContentEstablish` 中 `radio('action', ...)` 渲染。 |
| `SysAdContent::ACTION_ROUTE` / `ACTION_URL` / `ACTION_NONE` | `const` 字符串枚举值                                       |
| `SysAdContent::filter(...)` / `SysAdPlace::filter(...)`   | `FilterTrait` 注入；由 `AdContentFilter` / `AdPlaceFilter` 实现 `id`、`place`、`title` 等过滤           |

### Filter（`Models\Filters/`）

| Filter 类               | 公开方法                                                                       | 行为                          |
|------------------------|----------------------------------------------------------------------------|-----------------------------|
| `AdContentFilter`      | `id($id)` / `place($place_id)` / `title($title)`                          | `where(id, ...)` / `where(place_id, ...)` / `whereLike(title, %$title%)` |
| `AdPlaceFilter`        | `id($id)` / `title($title)`                                               | `where(id, ...)` / `whereLike(title, %$title%)`            |

### Policy（`Models/Policies/`）

| Policy 类            | 权限映射                                                                                                | 方法返回值                  |
|--------------------|-----------------------------------------------------------------------------------------------------|-------------------------|
| `AdPlacePolicy`    | `create => backend:py-ad.place.manage`<br>`edit => backend:py-ad.place.manage` | `create(PamAccount): true`<br>`edit(PamAccount, PamRole): true` |

> `AdContent` **未声明** Policy；权限统一由 `AdContentController::$permission` 控制。

### 翻译键（`resources/lang/zh/`）

| Key                  | 值        |
|----------------------|----------|
| `util.sys_ad_content` | 广告内容   |
| `util.sys_ad_place`   | 广告位    |

---

## 验证规则（`Http/Validation/`）

两个 FormRequest 都将 `protected bool $isValidate = false;`，意味着**不参与 Laravel 自动校验**，由 MgrPage 表单 `handle()` 显式 `app(Request, [$request])` 后 `validated()` 触发。

### `AdPlaceRequest::rules()`

| 字段          | 规则                                                                                                |
|-------------|---------------------------------------------------------------------------------------------------|
| `title`     | `required` + `string` + `unique(sys_ad_place, title)`（编辑时排除自身 `id`）                                |
| `width`     | `required` + `integer` + `min(1)`                                                                |
| `height`    | `required` + `integer` + `min(1)`                                                                |
| `thumb`     | `string` + `url`                                                                                 |
| `introduce` | `required` + `string`                                                                            |

### `AdContentRequest::rules()`

| 字段           | 规则                                                                                                       |
|--------------|----------------------------------------------------------------------------------------------------------|
| `place_id`   | `required` + `integer`                                                                                  |
| `title`      | `required` + `string` + `unique(sys_ad_content, title)`（编辑时排除自身 `id`）                                  |
| `introduce`  | `required` + `string`                                                                                   |
| `start_at`   | `required` + `string`                                                                                   |
| `end_at`     | `required` + `string`                                                                                   |
| `src`        | `url`                                                                                                   |
| `action`     | `required` + `string`                                                                                   |
| `value`      | `string`                                                                                                |
| `is_enable`  | `integer` + `in(array_keys(SysConfig::kvYn()))`（即 `0`/`1`）                                                   |
| `list_order` | `required` + `integer` + `min(1)`                                                                       |

> `start_at` / `end_at` 字段是 `required` 字符串，但 `Action\Ad::establish` 实际只接受前端 `dateTimeRange('at', '显示时段')` 组合的 `start_at - end_at` 字符串再 `explode(' - ', $at)` 拆分；`start_at`/`end_at` 作为独立键**当前**不会被 `establish()` 接收（依赖 `at` 字段存在），详见 [business.md](business.md) "待确认"。

---

## 上传依赖（间接契约）

| 依赖项                                                                              | 用途                                                | 来源文件（不在本仓库）                                  |
|----------------------------------------------------------------------------------|---------------------------------------------------|-----------------------------------------------|
| `py-system:api_v1.upload.image`（`POST /api_v1/system/upload/image`）                | 接收 `Form\Field\File::image()` 控件提交，返回 `{ url: [...] }` | `poppy/system/src/Http/Routes/api_v1_web.php`  |
| `py-system:api_v1.upload.file`（`POST /api_v1/system/upload/file`）                  | 通用文件上传（备用，本模块未使用）                                  | 同上                                            |
| `poppy.system.file` 契约（`FileContract`）                                          | 选择 `DefaultFileProvider` / `OssFileProvider`       | `poppy/system/src/ServiceProvider::registerContracts()` |
| `py-aliyun-oss:backend.upload.store`（`py-aliyun-oss:backend.upload.store`）         | 阿里云 OSS 设置页（仅供 `FormSettingUpload` 入口跳转，本模块不调用） | `poppy/aliyun-oss/src/Http/Routes/backend.php`        |

---

## 待确认

- `Action\Ad::establish` 显式读取 `at` 字段而非 `start_at`/`end_at`，但 `AdContentRequest` 仍将 `start_at` / `end_at` 标为 `required string`——这两套契约存在不一致（详见 [business.md](business.md) "待确认"）。
- `AdContentRequest` 中 `title` 唯一约束是表级唯一，**未限定 `place_id`**；外部调用方据此实现"全局唯一"或"同位唯一"需要在调用侧处理。
- `OssFileProvider` 路径下 `OssFileProvider::__construct()` 会从 `sys_setting('py-aliyun-oss::oss.*')` 读取 OSS 凭证，**当凭证缺失时抛出 `LoadConfigurationException`**——本模块无显式捕获，上传失败会以 MgrPage 表单错误呈现。（发现位置：`poppy/aliyun-oss/src/Classes/Provider/OssFileProvider.php`）
- `AdPlacePolicy::edit` 接受 `PamRole $role` 参数但**未使用**该参数，仅依赖 `$permissionMap`。
