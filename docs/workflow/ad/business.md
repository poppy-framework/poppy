# 业务逻辑（ad 模块）

本文档说明 **ad 模块为什么这样处理**：广告位/广告内容的后台管理规则、删除前置校验、唯一性约束、时段字段语义、跨模块协作规则。完整的对外契约见 [contracts.md](contracts.md)，执行链路见 [flows.md](flows.md)。

---

## 广告位管理（`SysAdPlace`）

### 业务规则

- **广告位是"容器"**：一个 `SysAdPlace` 可以容纳多条 `SysAdContent`。先有位后有内容，`Action\Place::delete` 强制要求"先清空内容再删位"。
- **删除前置校验（防止孤儿）**：`Action\Place::delete` 在删除前会执行 `SysAdContent::where('place_id', $id)->exists()`：若仍存在内容，则通过 `setError('存在广告, 不得删除!')` 终止并返回 `false`。**为什么**：保留 `sys_ad_content.place_id` 外键的业务一致性，避免历史广告失去归属位置。
- **唯一性约束**：`AdPlaceRequest::rules()` 对 `title` 字段用 `Rule::unique($tbName, 'title')->where(...)` 限制同一标题不可重复（编辑时排除自身 `id`）。**为什么**：广告位名称常被运营在跨模块表单中作为展示文案（`FormPlaceSelect` 渲染为 `<select>` 选项），同名会导致运营误选。
- **尺寸字段语义**：`width` / `height` 是 `unsignedSmallInteger`，**仅为运营参考**（在前端投放时建议按此尺寸制作素材）。`Action\Place` 不做内容图与位尺寸的强制校验，**为什么**：本模块不存储"原图实际像素"，`OssFileProvider` 上传时也不限制长宽比（仅 `resizeLongDistrict=30000` 保护）。

### 路由/分发规则

模块没有多路分发逻辑，所有广告位操作走 `py-ad:backend.place.*`：

| 条件              | 处理路径                                                |
|-----------------|-----------------------------------------------------|
| 新建广告位           | `place/establish`（无 `id`）                          |
| 编辑广告位           | `place/establish/{id?}`                              |
| 删除广告位           | `place/delete/{id}`                                  |
| 跳转至该位下的内容列表    | `py-ad:backend.content.index?place_id={id}`（`ListSysAdPlace::columns` 中 `actions->page(...)`） |
| 跳转至该位下的内容新建表单 | `py-ad:backend.content.establish?place_id={id}`（`ListSysAdPlace::columns` 中 `actions->iframe(...)`） |

### 关键算法/计算

- **`SysAdPlace::title()`**：返回 `[id => title]` 映射，供同仓库后台表单构造下拉选项时复用。**为什么**：避免在 MgrPage 列表/详情页再次查询数据库。
- **`SysAdPlace::returnAdContent(int $id)`**：按 `list_order` 升序、仅返回 `is_enable = SysConfig::YES` 的内容数组 `[{id, src, action, value, title}]`。**为什么**：前端投放接口需要的"最小可用集"——不返回 `introduce/list_order/start_at/end_at` 字段，减小 payload；只取启用的，过滤交给字段。

---

## 广告内容管理（`SysAdContent`）

### 业务规则

- **动作类型三选一**：`SysAdContent::ACTION_ROUTE`（内链）/`ACTION_URL`（外链）/`ACTION_NONE`（无操作），由 `kvAction()` 静态方法渲染为表单 radio。`value` 字段根据 `action` 填写不同语义：路由名或 URL。**为什么**：与 `value` 配合，前端可按 `action` 类型决定跳转方式（`poppy.mgr-page/backend` 路由解析 vs `window.location`）。
- **唯一性约束**：`AdContentRequest::rules()` 对 `title` 字段同样使用 `Rule::unique($tbName, 'title')`，编辑时排除自身。**为什么**：内容标题会被拼装到展示列表文案中，需在同一 `place_id` 内不冲突（注：当前 `unique` 约束是表级，**未限定 `place_id`**——见"待确认"）。
- **时段字段语义**：`start_at` / `end_at` 为 `datetime` 类型，但 `Action\Ad::establish` 只把它们当作字符串透传，**没有"当前时间 vs 时段"的过滤逻辑**。**为什么**：模块定位为"内容管理"，不负责前端投放策略；由调用方在读取时（如 `returnAdContent`）按需补强。
- **排序与启用分离**：`list_order`（`unsignedSmallInteger`，`Rule::min(1)`）用于前端排序；`is_enable`（`tinyInteger`，`0/1`）仅控制"是否被 `returnAdContent` 返回"。**为什么**：允许运营"先排序、暂不启用"，避免保存即生效导致误投。
- **启停切换**：`Action\Ad::toggle(int $id)` 直接将 `is_enable` 取反（`(int) !$this->item->is_enable`），无中间态、无二次校验。**为什么**：开关是高频运营操作，去掉中间态可减少误操作。
- **删除不联动**：`Action\Ad::delete` 直接删除 `SysAdContent` 记录；删除广告位时反过来校验"有内容则不能删"。**为什么**：删除顺序由运营决定，单条广告内容被删是常态，广告位是稀缺资源需要保护。

### 路由/分发规则

| 条件                | 处理路径                            |
|-------------------|---------------------------------|
| 列表（可按 `place_id` 过滤） | `content`                       |
| 新建（必须传 `place_id`）  | `content/establish`（无 `id`，依赖 `Route::input` 与 `input('place_id')` 联合解析） |
| 编辑                | `content/establish/{id?}`       |
| 删除                | `content/delete/{id}`           |
| 启停切换              | `content/toggle/{id}`           |

> **为什么**：`ListSysAdContent` 的"操作列"根据 `is_enable` 当前值，分别渲染"禁用/启用"链接，URL 相同 (`py-ad:backend.content.toggle`)，由 `Action\Ad::toggle` 完成反转。

### 关键算法/计算

- **`dateTimeRange('at', '显示时段')`**：表单侧把 `start_at` 与 `end_at` 合成一个 `YYYY-MM-DD HH:mm:ss - YYYY-MM-DD HH:mm:ss` 字符串，提交后 `Action\Ad::establish` 用 `explode(' - ', $at)` 拆开回 `start_at/end_at`。**为什么**：运营一次操作即配置完整时段，避免"开始时间已设，结束时间被遗漏"。

---

## 跨模块协作规则

### `poppy.system.upload_type` 与图片存储

- `configurations/hooks.yaml` 中本模块**未**注册 `poppy.system.upload_type` Hook；上传走的是 `poppy/system` 自己的 Hook 聚合点（`UploadTypeDefault` + `Poppy\AliyunOss\Hooks\System\UploadTypeAliyun`）。
- 后台 `image('src'/'thumb')` 字段的上传由 `poppy/mgr-page` 的 `Form/Field/File` 渲染成前端上传控件 → `POST /api_v1/system/upload/image` (`py-system:api_v1.upload.image`) → `poppy/system` 的 `UploadController::image`。
- `poppy/system` 的 `ServiceProvider::registerContracts()` 通过 `app('poppy.system.file')` 绑定，按 `sys_setting('py-system::picture.save_type')` 选择 `DefaultFileProvider`（默认 `uploads/`）或 `OssFileProvider`（阿里云 OSS）。
- **本模块的契约**：仅消费 `src` 字段的 `Rule::url()` 校验，**完全不感知**后端是 OSS 还是本地。**为什么**：图片存储是平台级能力，不应与广告业务耦合。

### `poppy.ad.form_place_select` Hook

- `configurations/services.yaml` + `configurations/hooks.yaml` 联合声明：`poppy.ad.form_place_select` 是 `form` 类型，渲染器是 `Poppy\Ad\Hooks\FormPlaceSelect`。
- `FormPlaceSelect::builder()` 读取 `SysAdPlace::pluck('title', 'id')`，调用 `poppy.mgr-page.form` 服务的 `select()` 渲染。
- **使用方**：`modules/demo/.../FormHook.php`（示例模块）。**为什么**：跨模块表单控件通过服务名解耦，避免 Form 类相互 `use`。

---

## 权限

- 本模块后台路由全部走 `backend-auth` 中间件（`Http/RouteServiceProvider::map()`），不单独挂 `rbac` 中间件。
- `AdContentController` 显式声明 `self::$permission = ['global' => 'backend:py-ad.place.manage']`；`AdPlaceController` 继承自 `BackendController`，未覆盖权限（默认使用 MgrPage 后台权限模型）。
- `AdPlacePolicy` 将 `create` / `edit` 映射到 `backend:py-ad.place.manage`（`$permissionMap`），`create()` / `edit()` 方法直接返回 `true`，**仅做权限占位，无业务逻辑**。
- **为什么**：`backend:py-ad.place.manage` 同时管理"位"和"内容"两套操作——是当前的简化策略；典型"位与内容权限分离"未实现（见"待确认"）。

---

## 定时任务 / Artisan 命令

| 命令/任务 | 调度频率 | 业务动作 |
|---------|--------|--------|
| （无）      | —      | —      |

> 本模块未注册任何 Console 命令，未注册任何 Job，未发布/订阅任何事件。

---

## 中间件规则

| 中间件                              | 应用范围                       | 规则                                                    |
|----------------------------------|----------------------------|-------------------------------------------------------|
| `backend-auth`（来自 `poppy/mgr-page`） | `Http/Routes/backend.php` 整组 | 必须登录后台 `PamAccount`；具体鉴权策略由 MgrPage 后台中间件提供         |
| 表单层 FormRequest（`AdPlaceRequest`、`AdContentRequest`） | 调用方手动 `validated()`        | 两者都设置 `protected bool $isValidate = false;`——不通过 Laravel 自动中间件校验；由 `FormPlaceEstablish::handle()` / `FormContentEstablish::handle()` 显式 `app(Request, [$request])` 后调用 `validated()` |

---

## 待确认

- `AdContentRequest::rules()` 中 `title` 唯一性约束**未限定 `place_id`**，跨位不允许同名；如果未来按"同 `place_id` 下唯一"做精细化，需在 `Rule::unique` 中追加 `->where('place_id', ...)`。（发现位置：`AdContentRequest::rules`）
- 模块**未提供"按时段过滤返回内容"**的公开方法；`returnAdContent` 仅按 `is_enable` 过滤，运营时段 (`start_at`/`end_at`) 是否参与过滤完全依赖调用方实现。（发现位置：`SysAdPlace::returnAdContent`）
- `Action\Ad::establish` 中 `at` 字段的拆分依赖固定分隔符 `' - '`；如果前端 `dateTimeRange` 控件因本地化/格式变化输出其他分隔符，**会因 `explode` 只取前两个片段而静默丢值**。（发现位置：`Action\Ad::establish`）
- `AdContentController` 的 `self::$permission = ['global' => 'backend:py-ad.place.manage']` 复用了"广告位管理"权限；如未来要"内容"与"位置"权限拆分，需新增权限码并改此处与 `AdPlacePolicy`。（发现位置：`AdContentController::__construct`）
- 模块**未提供"图片尺寸与 `width`/`height` 校验"**：上传时 `OssFileProvider` 仅限制最长边 30000 像素，不会阻止运营上传与广告位尺寸不匹配的素材。（发现位置：`FormContentEstablish` / `OssFileProvider`）
- `Poppy\Ad\Models\SysAdContent` 未定义显式 `belongsTo(SysAdPlace)` 关联，跨模型访问靠 `where('place_id', $id)`（见 `SysAdPlace::returnAdContent` 与 `Action\Place::delete`）。（发现位置：`SysAdContent`、`SysAdPlace`）
