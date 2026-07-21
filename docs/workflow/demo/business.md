# 业务逻辑

> demo 模块是 poppy 框架的**示范模块**，本身没有"业务领域"。本文档记录的不是"如何收单/如何审核"这类业务规则，而是**模块被设计出来的目的**、**模板性约定**以及**演示用控件/中间件/事件的业务含义**。
>
> 任何"具体的类名/事件名"请回 [contracts.md](contracts.md)；"具体怎么串联"请回 [flows.md](flows.md)。

## 1. demo 模块作为"新模块模板"

### 业务规则

- demo 模块是 poppy 框架的官方脚手架，唯一目的是"展示一个模块能放什么"。任何接入 poppy 的新模块推荐从 `modules/demo/` 复制后再改动，而不是从零写。
- 不承载真实业务领域：账号、回收、议价、商品等独立业务一律放在 `poppy/<domain>/`。demo 模块**不应**作为业务的"试运行"承载位——它的代码会随框架升级被改写示例。
- 命名约定由 demo 模块**统一示范**：
  - 控制器按调用方分目录：`Http/Request/Api/App`、`Http/Request/Api/Web`、`Http/Request/Backend`、`Http/Request/Web`——这是 poppy 4.x 的分层模板。
  - 路由文件与控制器目录一一对应（`api.php`、`api-app.php`、`backend.php`、`web.php`、`web-root.php`），前缀在 `Http/RouteServiceProvider.php` 中按层注入。
  - 路由命名 `<module>:<area>.<controller>.<method>`，例如 `demo:web.token.index`、`demo:web.table.easy`。
  - 后台 Form 控件全部在 `src/Forms/` 下平铺，单文件命名 `Form{Type}`；后台 List/Search 全部在 `src/Http/Lists/` 下，命名 `ListGrid{Type}` 与 `ListSearch{Type}`。
  - Model 放在 `src/Models/`，Policy 放在同级的 `Models/Policies/`，与系统模块保持一致。

### 路由/分发规则

demo 模块展示了 5 套不同的中间件组合，每一套都对应一种"该放什么"：

| 场景                   | 放在哪                | 中间件                       | 触发原因                                  |
|----------------------|--------------------|---------------------------|---------------------------------------|
| 后台管理                | `backend.php`      | `sys-auth:backend`、`sys-rbac` | 必须登录且具有 RBAC 角色才能访问                  |
| 公开 Web 演示           | `web.php`          | （无 / 注释掉的 `sys-auth:jwt_web`） | 控件演示用，登录可选                            |
| 根级 Web              | `web-root.php`     | 无                         | 真正的"模块首页"                             |
| 跨域 Web API 演示       | `api.php`（前半段）     | `cross`                   | 给前端调试 Resp / ApiDoc 注解用                |
| SSO Web API 演示      | `api.php`（后半段）     | `api-sso`                 | 必须携带 SSO 票据才能返回 PAM 账号信息            |
| App API（移动端签名校验）   | `api-app.php`      | `py-ext-app.sign-json`    | App 端需在 header 携带 `x-app-id`、`x-app-os`、`x-app-version` 等签名头 |

### 关键算法/计算

- `DemoGrid::asJson()` 覆写为 `JSON_UNESCAPED_UNICODE`：演示模型层对 JSON 字段的中文不转义处理。
- `DemoGrid::kvStatus()` 静态返回 1→未发布 / 2→草稿 / 5→待审核 / 3→已发布 / 4→已删除 的 kv 映射：演示"模型暴露枚举给后台"的标准模式。
- `Demo\Models\Policies\DemoGridPolicy::create()` **统一返回 false**：纯粹演示 Policy 注册，不实际允许任何人创建行。

## 2. PassportVerify 校验规则（手机号验证）

### 业务规则

- 该规则的"业务背景"是用户中心注册/换绑流程：调用方传入 `passport`（手机号）+ `type`（校验类型），由 demo 模块的监听器告诉调用方这个手机号符不符合规则。
- 仅支持 `type === 'exist'` 一种语义——"校验此手机号是否在 `PamAccount` 中存在"。其它 `type` 一律抛 `ApplicationException('类型错误')`：这是**有意识的**收紧，demo 模块只示范最小可用实现，避免被外部当成"通用校验库"调用。
- 若 `type` 为空（`''`、`null`、`0`）则**直接静默 return**：保留 pop passthrough 接口给其它模块自己处置（例：密码登录场景不需要手机号校验）。

### 路由/分发规则

`passport` 内容处理：

| 条件                          | 处理                                      |
|-----------------------------|-----------------------------------------|
| 是合法的纯手机号（不含 `-`）         | 自动补前缀 `'86-'`（按中国大陆国际区号惯例）           |
| 已含 `-` 区号前缀                | 不做处理                                   |
| 补前缀后 `PamAccount::where('mobile', ...)` 不存在 | 抛 `ApplicationException('系统不存在此手机号!')` |

> 完整的事件级联与监听入口见 [flows.md](flows.md)；事件/监听器全名见 [contracts.md](contracts.md)。

### 状态机

无状态字段——`PassportVerifyListener::handle()` 是无状态函数调用。但它自身有一条"类型 → 行为"的决策分支：

```
$type 空 → silent return
$type === 'exist' → 检查 PamAccount::where('mobile', $passport) 是否存在
$type 其它 → 抛 ApplicationException('类型错误')
```

### 关键算法/计算

- 国际区号前缀补全：`$passport = '86-' . $passport`（前提是 `!Str::contains($passport, '-')`）。
- 存在性查询：`PamAccount::where('mobile', $passport)->exists()`——`exists()` 而非 `first()`，节省一次模型 hydration。

## 3. 后台控件清单（Forms / Lists）

### 业务规则

- `src/Forms/` 下平铺了 ~58 个 `Form{Type}` 控件：每个文件就是一个具体的表单 Widget 子类，命名与 `poppy.mgr-page` 的 FormBuilder 渲染管线对接。**demo 模块不"实现"业务**，仅展示 FormBuilder 能渲染什么。
- `src/Http/Lists/` 下平铺了 ~27 个 `ListGrid{Type}` 与 `ListSearch{Type}`：演示后台 Grid 的列表驱动 + 搜索条件驱动如何按 `?type=xxx` 切换。
- `ContentController`、`FormController`、`GridController`、`SearchController`、`TableController` 提供"按 URL 参数反射到对应 List/Form 类"的工厂入口，是**给写新模块的工程师作为复制模板**。

### 路由/分发规则

- `/demo/form/{type}` → `FormController@index` → 通过 `factory($type)` 反射 `\Demo\Forms\Form{$type}`，找不到则抛 `ApplicationException("类 $className 不存在!")`。
- `/demo/grid/more/{type?}`、`/demo/search/{type?}` → 同样思路反射 `\Demo\Http\Lists\ListGrid{Type}` / `ListSearch{Type}`。
- 注意：**反射类名一旦写错，工厂抛出 `ApplicationException` 由统一异常 Handler 接管**，demo 模块刻意不直接 500——目的是让 demo 在控件被删时还能给前端返回一个友好错误。

## 4. Artisan 命令

| 命令/任务                                       | 调度方式  | 业务动作                                                                              |
|----------------------------------------------|-------|-----------------------------------------------------------------------------------|
| `Demo\Commands\UserMobileCommand`（签名 `demo:mobile`） | 手动执行 | 把所有 `PamAccount`（`type = TYPE_BACKEND`）的 `mobile` 字段回填为 `'33023-' . sprintf("%'.07d", $id)`，仅当字段为空时执行 |

### 业务规则

- 这是个**演示用的数据迁移命令**，实际不应当在线上跑：它会无差别把后端账号的手机号覆盖成 `"33023-xxxxxxxx"` 形式的占位符，**用于演示 Artisan 命令的入口规范**，不应承担真实数据清理。

## 5. 中间件规则

| 中间件                       | 演示用路由                       | 规则说明                                                       |
|---------------------------|-----------------------------|-----------------------------------------------------------|
| `cross`                   | `api.php`（前半段，`/api/demo/...`） | 允许跨域——demo 模块纯粹给前端调试 Resp / ApiDoc，没有真实鉴权需求            |
| `api-sso`                 | `api.php`（后半段，`/api/demo/sso/access`） | 由 `poppy/system` 提供；通过 SSO 票据把 `PamAccount` 注入 `$this->pam` |
| `py-ext-app.sign-json`    | `api-app.php`（`/api/app/demo/...`） | 由 `poppy/ext-app` 提供；校验 `x-app-*` 签名头             |
| `sys-auth:backend`、`sys-rbac` | `backend.php`                | 由 `poppy/mgr-page` 提供；后台登录 + RBAC 守卫                |
| `sys-auth:jwt_web`        | `web.php` 中已注释               | **没有强制启用**——演示 JWT 在 Web 路由下的可插拔性，并展示"非必须"时的写法      |

> 完整事件/监听清单与 Job（demo 模块无 Job）见 [contracts.md](contracts.md)。

## 6. 控制器 / OpenAPI 注解规范

### 业务规则

- demo 模块**所有控制器方法都写了 `@OA\…` 注解**：这是给 OpenAPI 自动生成提供演示样本，新模块可整套照抄：
  - `ApiDocController@how`：演示 `@OA\RequestBody` + `@OA\JsonContent(ref="#/components/schemas/...")` 的请求/响应 Schema 模式。
  - `RespController@success/error/validator/401/header`：演示"成功 / 业务失败 / 校验失败 / 未授权 / 头调试"五大返回模板。
  - `SsoController@access`：演示"通过 `api-sso` 中间件拿到 `$this->pam`"的注入模式。
  - `Api/App/DemoController@index`：演示"App 端健康检查"接口的标准结构。
- Schema（`*ResponseBody` / `*Request`）放在 `Http/Request/<area>/<Controller>/` 内，与控制器共目录——避免 `app/Schemas/` 全局污染。

## 7. Hooks / Progress / Seeds 的存在意义

- `Hooks/Demo/ArrayDemo`、`Hooks/Demo/HtmlDemo`：演示如何注册数组型与 HTML 型 Hook。
- `Hooks/MgrPage/HtmlCpA.php`、`Hooks/MgrPage/SettingsKeyA.php`、`Hooks/MgrPage/SettingsKeyB.php`：演示接入 `poppy/mgr-page` 的渲染钩子与系统设置键。
- `Hooks/System/AuthAccessUser.php`：演示拦截授权访问。
- `Progress/SendSmsProgress.php`：演示"分步推进的进度条"——短信发送这种长任务可以分段报告。
- `Seeds/Demo*`：填充 `demo_*` 三张表，给 dev 环境跑后台示例。

## 待确认

- `web-root.php` 中 `$router->any('/', 'HomeController@index')` 是否就是"模块首页"——`HomeController::index()` 仅返回视图 `demo::web.home.index`，实际渲染什么、是否对接 `poppy.mgr-page` 模板，需要查对应 Blade 模板确认（发现位置：`Http/Routes/web-root.php` 与 `Http/Request/Web/HomeController.php`）。
- `Demo\Http\Exception\Handler.php` 与 `poppy.framework` 的全局 Handler 的优先级 / 是否真的会被框架加载，需要进一步确认（发现位置：`Http/Exception/Handler.php`）。
