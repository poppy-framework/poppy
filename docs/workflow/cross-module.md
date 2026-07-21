# 跨模块关联分析

## 文档目的

本文档汇总 `poppy/*` 与 `modules/demo` 下所有业务模块的跨模块依赖、事件级联、共享模型引用、跨模块 Action 调用，供**改动评估**与**接口设计**时查阅。

如需了解某个模块自身的细节（职责、目录、路由、事件、流程），请进入对应模块的 `overview.md` / `business.md` / `contracts.md` / `flows.md`。

---

## 1. 模块依赖矩阵

> 行 = 消费方，列 = 被消费方。`✓` 表示该消费方在 `use` 语句层面引用了被消费方的命名空间。

| 消费方 ↓ \ 提供方 → | framework | core | system | mgr-page | ad | aliyun-oss | aliyun-push | app | area | category | content | sensitive-word | sms | version |
|--------------------|:---------:|:---:|:------:|:--------:|:--:|:----------:|:-----------:|:--:|:----:|:--------:|:-------:|:--------------:|:--:|:-------:|
| **framework**      | —         | —   | —      | —        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **core**           | ✓         | —   | ✓      | —        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **system**         | ✓         | ✓   | —      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **mgr-page**       | ✓         | —   | ✓      | —        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **ad**             | ✓         | ✓   | ✓      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **aliyun-oss**     | ✓         | ✓   | ✓      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **aliyun-push**    | ✓         | ✓   | —      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **app**            | ✓         | ✓   | ✓      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **area**           | ✓         | —   | ✓      | —        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **category**       | ✓         | ✓   | ✓      | ✓        | —  | —          | —           | ✓  | —    | —        | —       | —              | —  | —       |
| **content**        | ✓         | —   | ✓      | ✓        | —  | —          | —           | —  | —    | ✓        | —       | —              | —  | —       |
| **sensitive-word** | ✓         | —   | ✓      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **sms**            | ✓         | —   | —      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **version**        | ✓         | —   | ✓      | ✓        | —  | —          | —           | —  | —    | —        | —       | —              | —  | —       |
| **demo**           | ✓         | ✓   | ✓      | ✓        | —  | —          | —           | —  | ✓    | —        | —       | —              | —  | —       |

### 1.1 被依赖度（反向引用计数）

| 提供方       | 被 N 个业务模块直接引用 | 备注 |
|--------------|:-----------------------:|------|
| **framework**| 13（全部）             | 基类/Helper/异常/分页/Resp，几乎所有模块都依赖 |
| **system**   | 12                      | PamAccount/SysConfig/FileContract/JwtApiController，后台身份认证 & 文件存储 |
| **mgr-page** | 11                      | Form/Grid/ListBase/BackendController，后台 CRUD 标配 |
| **core**     | 5（ad/aliyun-oss/aliyun-push/app/category/demo） | RBAC contract + ServiceArray/Html + SettingContract |
| **area**     | 1（demo）               | 行政区划树查询 |
| **category** | 1（content）            | 内容分类 |
| **app**      | 1（category）           | AppSignMiddleware 复用（注意：实际是 `ext-app` 的 `JsonAppSignMiddleware`，category 仅 `use Poppy\App\Action\App;`） |

### 1.2 基础模块清单（强耦合，改动需谨慎）

- **framework** — 全局基类，任何 ServiceProvider 都继承 `Poppy\Framework\Support\PoppyServiceProvider`
- **core** — RBAC + Service Hook + 缓存标签 + 模块加载器，是后台权限的源头
- **system** — PAM 账号体系 + 鉴权 + 文件上传抽象 + 系统设置 + 通知
- **mgr-page** — 后台 UI 框架（Form/Grid/Operation/分页），所有后台 Controller 都继承 `BackendController`

---

## 2. 事件级联链路

### 2.1 跨模块事件订阅矩阵

> 行 = 监听方所在模块，列 = 事件发布方所在模块。

| 监听方 ↓ \ 发布方 → | core | system | category | framework | poppy/system 自身 |
|--------------------|:----:|:------:|:--------:|:---------:|:-----------------:|
| **system**         | ✓ `PermissionInitEvent` | ✓ `LoginSuccessEvent` / `LoginTokenPassedEvent` / `PamLogoutEvent` / `PamPasswordModifiedEvent` / `TokenRenewEvent` | — | — | — |
| **area**           | —   | —      | —        | —         | ✓ `PoppyOptimized` |
| **core**           | —   | —      | —        | —         | ✓ `PoppyOptimized` |
| **sensitive-word** | —   | —      | —        | —         | （目录为空）       |
| **version**        | —   | —      | —        | —         | （目录为空）       |

> 说明：`system → system` 一列指 `system` 自身订阅 `Illuminate\Auth\Events\Login` 等 Laravel 框架事件，以及 `system` 内部事件的多 Listener 处理。

### 2.2 关键事件级联链

#### 链 A：用户登录（system 模块内 + framework 事件）

```
[POST] /api/system/auth/login
    → AuthController::login
        → Action\Pam::login
            → event(new LoginSuccessEvent(...))   ← system 发布
                ├─→ Listeners\LoginSuccess\UpdatePasswordHashListener
                ├─→ Listeners\LoginSuccess\LogListener
                └─→ Listeners\LoginSuccess\UpdateLastLoginListener
            → event(new LoginTokenPassedEvent(...))  ← system 发布
                └─→ Listeners\LoginTokenPassed\SsoListener
            → event(new PamPasswordModifiedEvent(...))  ← 密码变更时
                └─→ Listeners\PamPasswordModified\SsoListener
```

涉及模块：system（自治）；外部触发仅通过 HTTP。

#### 链 B：权限初始化（core → system 跨模块）

```
py-core:permission init  ← core Commands\PermissionCommand
    → event(new PermissionInitEvent(...))   ← core 发布
        └─→ Listeners\PermissionInit\InitToDbListener  ← system 监听
            → pam_permission upsert
            → PamRole::BE_ROOT->syncPermission()
```

涉及模块：**core（发布）→ system（监听执行）**。`core` 模块自身没有 Listener，全靠 `system` 完成落库。

#### 链 C：SSO 续期与退出

```
[POST] /api/system/auth/renew   (api-sso 中间件)
    → AuthController::renew
        → Action\Sso::renewToken
            → event(new TokenRenewEvent(...))
                └─→ Listeners\TokenRenew\TokenRenewListener
            → event(new TokenRenewAfterEvent(...))

[POST] /api/system/auth/logout
    → Action\Pam::logout
        → event(new PamLogoutEvent(...))
            └─→ Listeners\PamLogout\SsoListener
        → event(new BePamLogoutEvent(...))
            └─→ Listeners\AuthLogout\LogoutLogListener  ⚠ 已实现但未在 $listens 绑定
```

涉及模块：system 自治。

#### 链 D：系统优化 / 缓存清理（framework 事件）

```
py-core:optimize  (framework Console\Commands\PoppyOptimizeCommand)
    → event(new PoppyOptimized(...))   ← framework 发布
        ├─→ Listeners\PoppyOptimized\ClearCacheListener (system)
        ├─→ Listeners\PoppyOptimized\ClearCacheListener (core)
        ├─→ Listeners\PoppyOptimized\ClearCacheListener (area)
        └─→ Listeners\PoppyOptimized\SystemInitListener  (system)
```

涉及模块：**framework（发布）→ system/core/area（监听）**。注意 area 模块也注册了 `ClearCacheListener`，是因为 area 用了 `sys_tag('py-area')` 整标签缓存。

### 2.3 未被消费的预留事件

| 事件类                              | 发布方     | 监听方  | 处置建议 |
|-------------------------------------|------------|---------|----------|
| `Poppy\Category\Events\SysCategoryBeforeDeleteEvent` | category  | **无**  | 扩展点/未来 cascade 删除时启用 |

---

## 3. 共享模型直接引用（`use Poppy\X\Models\Y`）

| 消费方       | 直接引用的外部 Model                                  | 用途 |
|--------------|-------------------------------------------------------|------|
| 全部业务模块 | `Poppy\System\Models\PamAccount` (92) / `PamRole` (21) / `SysConfig` (29) / `PamToken` (8) / `PamBan` (7) / `PamLog` (4) / `PamPermission` (3) / `PamRoleAccount` (3) | 后台身份/权限/系统配置 |
| content      | `Poppy\Category\Models\SysCategory` (9)               | 内容分类归属 |
| area         | `Poppy\System\Models\PamAccount` (3)                  | 后台权限校验 |
| demo         | `Poppy\Area\Models\SysArea` (1)                       | 行政区划展示 |
| ad           | `Poppy\System\Models\PamAccount/PamRole/SysConfig`    | 后台账号/角色/配置 |
| app          | `Poppy\System\Models\PamAccount/SysConfig`             | 应用绑定账号 |
| version      | `Poppy\System\Models\SysConfig`                       | 系统设置读取 |

> **禁止跨模块引用 Request/Controller/Listener/Middleware**（详见 `architecture.md` 第 2 节）。当前代码扫描中**未发现**任何模块 `use` 其他模块的 `Http\Request\`、`Http\Middlewares\`、`Listeners\` 命名空间 — 符合规范。

---

## 4. 跨模块 Action 调用

> 仅列出 Action 类之间的直接调用（`app(XxxAction::class)->method(...)` 或 `new XxxAction()->method(...)`）。统计基于代码扫描 + 各模块文档中标注的调用方。

| 调用方模块      | 目标 Action                                       | 目标方法（节选）                                | 场景 |
|-----------------|---------------------------------------------------|------------------------------------------------|------|
| ad              | `Poppy\System\Action\Pam` (11)                    | （依赖后台权限）                                 | 后台 Controller 鉴权 |
| category        | `Poppy\App\Action\App` (1)                        | item()                                          | FormCategorySelect 关联 App |
| mgr-page        | `Poppy\System\Action\Pam` / `Action\Role` / `Action\Ban` | CRUD + 设置 + RBAC             | 全部后台 Controller |
| 系统模块自身     | `Poppy\Area\Action\Area` (2) / `Category\Action\Category` (3) | 地区/分类查询                  | 在 system 模块 Action 内引用 |
| 业务 Controller | `Poppy\System\Action\Sso` (8) / `Ban` (6) / `Verification` (5) | SSO/封禁/二次验证                | system 模块内部 Controller 调用 |
| sms             | —                                                 | —                                               | sms 是无状态驱动封装，不调用其他模块的 Action |

---

## 5. 跨模块 Job / Queue 关系

| Job 类                                | 模块           | 触发方（Action/Listener）                       | 是否跨模块消费 |
|---------------------------------------|----------------|------------------------------------------------|----------------|
| `Poppy\System\Jobs\NotifyJob`         | system         | system 内部 Action（注册验证码/通知场景）       | 否             |
| `Poppy\System\Jobs\NotifyProJob`      | system         | system 内部 Action（高阶通知）                  | 否             |
| `Poppy\System\Jobs\DeleteUploadFileJob`| system        | version.Action\Version::delete (跨模块)        | **是**：被 version 模块 dispatch |
| `Poppy\AliyunPush\Jobs\SenderJob`     | aliyun-push    | aliyun-push 内部 `AliPush::send()`              | 否             |

---

## 6. 跨模块 Hook / Service 关系

### 6.1 Service Hook（`ServiceArray` / `ServiceHtml` / `ServiceForm` 契约）

| 注册模块     | Hook key（来自 `configurations/hooks.yaml` 或 `services.yaml`）         | Builder 类                                  | 消费方 |
|--------------|--------------------------------------------------------------------------|---------------------------------------------|--------|
| system       | `poppy.system.upload_type`（含 `default` / `aliyun`）                    | `UploadTypeAliyun` (aliyun-oss) / `DefaultFileProvider` (system) | system ServiceProvider 注册 |
| aliyun-oss   | `poppy.oss.sts`                                                          | `OssFileProvider` / `Action\Sts`            | system 通过 `sys_hook('poppy.system.upload_type')` 选取 |
| sms          | `py-sms:send.volc` / `py-sms:send.chuanglan` / `py-sms:send.lianlu` / `py-sms:send.aliyun` | `AliyunSmsProvider` / `VolcSmsProvider` / `ChuanglanSmsProvider` / `LianLuSmsProvider` | system `CaptchaSendEvent` Listener |
| ad           | `poppy.ad.form_place_select`                                             | `FormPlaceSelect`                            | demo 模块（FormHook.php） |
| category     | `poppy.category.form_select`                                             | `FormCategorySelect`                         | （content 模块 Form/Manager 引用） |
| app          | `poppy.app.form_select`                                                  | `FormAppEstablish`                           | （system 模块内引用）     |

### 6.2 Backend 设置 Hook（`poppy.mgr-page.settings`）

| 模块         | 设置 group key              | Form 类                  |
|--------------|-----------------------------|--------------------------|
| system       | `py-system::upload`         | `FormSettingUpload` (mgr-page) |
| aliyun-oss   | `poppy.aliyun-oss`          | `FormSettingAliyunOss`   |
| aliyun-push  | `poppy.aliyun-push`         | `FormSettingAliyunPush`  |
| sms          | `poppy.sms`                 | `FormSettingSms`         |
| version      | `poppy.version`             | `FormSettingVersion`     |
| mgr-page     | `poppy.mgr-page`            | `FormSettingSite`        |

---

## 7. 鉴权 / 中间件 跨模块链

```
请求进入
    ├─ EnableCrossRequest（framework）
    ├─ backend-auth = web + sys-auth:backend + sys-auth_session + sys-ban:backend + sys-rbac + py-mgr-lifetime
    │      ├─ sys-rbac        ← Poppy\Core\Rbac\Middlewares\RbacPermission
    │      ├─ sys-auth:backend ← Poppy\System\ 自定义
    │      └─ py-mgr-lifetime ← Poppy\MgrPage\ 自定义
    ├─ api-sign                ← Poppy\System\ 自定义
    ├─ sys-app_sign            ← Poppy\System\（来自 app 模块的 AppSignMiddleware 配置，被 system 引用）
    ├─ sys-jwt                 ← Poppy\System\
    └─ api-sso                 ← Poppy\System\ SSO 中间件
```

`sys-app_sign` 中间件由 `poppy/app` 模块注册到 `MiddlewareServiceProvider`，但被 `poppy/system` 的 `api_v1_web.php` 路由组使用 — **这是当前项目中典型的中间件跨模块消费场景**（app 提供中间件 → system 挂载）。

---

## 8. 典型改动的影响域参考

| 改动点                                   | 受影响模块（直接 + 间接）                          |
|------------------------------------------|---------------------------------------------------|
| 修改 `PamAccount` 字段                   | system、mgr-page、所有依赖 PamAccount 的业务模块 |
| 修改 `SysConfig` / `SettingContract`     | system、core、所有设置相关模块                    |
| 修改 `FileContract` / `OssFileProvider` | system（Upload 链路）、version（拷贝）、所有用上传的业务模块（content/ad）|
| 修改 `FormBuilder` / `Grid` 行为        | mgr-page、所有后台 Controller                     |
| 修改 `PermissionInitEvent` 载荷          | core（发布）、system（InitToDbListener）         |
| 修改 `CaptchaSendEvent` 载荷             | system（Listener）、sms（实际 dispatch 入口）    |
| 修改 `LoginSuccessEvent` 载荷            | system（3 个 Listener）、潜在外部订阅者           |
| 修改 `SysCategoryBeforeDeleteEvent` 载荷 | 当前无订阅者（未来扩展点）                       |

---

## 9. 跨模块改动自检清单

改动涉及 2 个以上模块时，**逐项确认**：

- [ ] 已在 `docs/workflow/{module}/business.md` 中说明业务规则变更
- [ ] 已在 `docs/workflow/{module}/contracts.md` 中标注新事件/路由/Job 的类名
- [ ] 已在 `docs/workflow/{module}/flows.md` 中给出完整调用序列
- [ ] 检查本文第 8 节"影响域"，确认所有引用方已同步更新
- [ ] 跨模块的 `use` 语句经过 grep 确认（`grep -rn "use Poppy\\\\X\\\\" poppy/*/src/`）
- [ ] 修改 Event 构造函数时，已同步更新所有 Listener（特别是 system 模块的 `$listens` 数组）
- [ ] 修改 Action 方法签名时，已同步更新所有调用方
- [ ] 修改 Action 后，已 grep `new ActXxx(` / `app(ActXxx::class)` 确认影响范围
- [ ] php -l 通过
- [ ] php artisan route:list 通过（如有路由变更）

---

## 10. 待确认

以下事项本轮扫描未能 100% 确认，需要人工 review：

- `poppy/mgr-page` 与 `poppy/system` 之间的中间件 / 基类引用计数，部分通过 `use ... as Alias` 语句省略了原名 — 真实数量可能略高于本表
- 各模块文档中标注的 `## 待确认` 段落（每个模块约 4-8 项）汇总在各自模块目录中
- `ext-app` 扩展插件（`JsonAppSignMiddleware` / `DefaultAppSign` / `AppClient`）当前在 `category` 模块被引用，但 `ext-app` 本身未在本轮分析范围内 — 改动 `ext-app` 时请同步评估 `category` 模块的引用方
- `poppy/faker` 与 `poppy/ext-*` 目录下的扩展模块未在本轮分析中 — 它们是按需启用，独立于主流程

---

## 11. 文档索引

| 模块               | overview                              | business | contracts | flows |
|--------------------|---------------------------------------|----------|-----------|-------|
| framework          | （未生成，框架基类）                  | —        | —         | —     |
| core               | [core/overview.md](core/overview.md) | [business.md](core/business.md) | [contracts.md](core/contracts.md) | [flows.md](core/flows.md) |
| system             | [system/overview.md](system/overview.md) | [business.md](system/business.md) | [contracts.md](system/contracts.md) | [flows.md](system/flows.md) |
| mgr-page           | [mgr-page/overview.md](mgr-page/overview.md) | [business.md](mgr-page/business.md) | [contracts.md](mgr-page/contracts.md) | [flows.md](mgr-page/flows.md) |
| ad                 | [ad/overview.md](ad/overview.md)       | [business.md](ad/business.md) | [contracts.md](ad/contracts.md) | [flows.md](ad/flows.md) |
| aliyun-oss         | [aliyun-oss/overview.md](aliyun-oss/overview.md) | [business.md](aliyun-oss/business.md) | [contracts.md](aliyun-oss/contracts.md) | [flows.md](aliyun-oss/flows.md) |
| aliyun-push        | [aliyun-push/overview.md](aliyun-push/overview.md) | [business.md](aliyun-push/business.md) | [contracts.md](aliyun-push/contracts.md) | [flows.md](aliyun-push/flows.md) |
| app                | [app/overview.md](app/overview.md)     | [business.md](app/business.md) | [contracts.md](app/contracts.md) | [flows.md](app/flows.md) |
| area               | [area/overview.md](area/overview.md)   | [business.md](area/business.md) | [contracts.md](area/contracts.md) | [flows.md](area/flows.md) |
| category           | [category/overview.md](category/overview.md) | [business.md](category/business.md) | [contracts.md](category/contracts.md) | [flows.md](category/flows.md) |
| content            | [content/overview.md](content/overview.md) | [business.md](content/business.md) | [contracts.md](content/contracts.md) | [flows.md](content/flows.md) |
| sensitive-word     | [sensitive-word/overview.md](sensitive-word/overview.md) | [business.md](sensitive-word/business.md) | [contracts.md](sensitive-word/contracts.md) | [flows.md](sensitive-word/flows.md) |
| sms                | [sms/overview.md](sms/overview.md)     | [business.md](sms/business.md) | [contracts.md](sms/contracts.md) | [flows.md](sms/flows.md) |
| version            | [version/overview.md](version/overview.md) | [business.md](version/business.md) | [contracts.md](version/contracts.md) | [flows.md](version/flows.md) |
| demo               | [demo/overview.md](demo/overview.md)   | [business.md](demo/business.md) | [contracts.md](demo/contracts.md) | [flows.md](demo/flows.md) |