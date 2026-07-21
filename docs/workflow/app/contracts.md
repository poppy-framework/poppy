# 对外契约

## 管理后台路由（backend.php）

> 路由前缀：`/{prefix}/py-app`（由 `Http/RouteServiceProvider::map()` 设置，分组中间件 `backend-auth`）。
> 命名空间：`Poppy\App\Http\Request\Backend`。

| HTTP方法 | URI                              | 请求类/控制器                  | 中间件                       | 说明                                                     |
|--------|----------------------------------|--------------------------|---------------------------|--------------------------------------------------------|
| GET/POST | `app`                            | `AppController@index`    | `backend-auth`            | 应用列表（`Grid` + `ListSysApp`），同时负责渲染"新建应用"按钮（iframe 弹出 `establish`） |
| GET/POST | `app/establish/{id?}`            | `AppController@establish`| `backend-auth`            | 新建 / 编辑应用（`FormAppEstablish`）；无 `{id}` 表示创建，否则为编辑       |
| GET/POST | `app/status/{id}/{status}`       | `AppController@status`   | `backend-auth`            | 切换应用启用状态；`status=1` 启用，`status=0` 禁用                     |

> 路由命名（必须保留）：`py-app:backend.app.index` / `py-app:backend.app.establish` / `py-app:backend.app.status`。
> 权限点：`AppController::$permission['global'] = 'backend:py-app.app.manage'`（声明在 `configurations/permissions.yaml` 的 `backend:py-app/app/manage`）。

## API 路由

> **本模块不暴露任何对外 API 路由**。仅提供**应用验签中间件**（`py-app.sign` 别名），由其他模块按需挂载到其对外 API 路由组上。

## Web 路由

| HTTP方法 | URI | 请求类/控制器 | 说明 |
|--------|-----|---------|----|
| 无      | —   | —       | —  |

## 其他路由文件

无。

## 发布的事件（本模块对外发布）

| 事件类 | 携带数据 | 触发时机 | 监听方 |
|-----|------|------|-----|
| 无   | —    | —    | —   |

> 当前 `Events/` 目录仅含 `.gitkeep` 占位文件，没有事件定义。

## 监听的事件（本模块消费）

| 监听器类 | 监听的事件 | 业务动作 | 产生的事件/任务 |
|------|-------|------|----------|
| 无    | —     | —    | —        |

## 队列任务

| Job 类 | 队列名 | 延迟 | 触发来源 | 业务动作 |
|------|-----|----|------|------|
| 无    | —   | —  | —    | —    |

## Artisan 命令

| 命令签名 | 说明 | 调度方式 |
|------|----|------|
| 无    | —  | —    |

## 应用验签中间件契约（供其他模块挂载）

### 别名

`py-app.sign` → `Poppy\App\Http\Middlewares\AppSignMiddleware`

注册位置：`Http/MiddlewareServiceProvider::boot(Router $router)`，调用 `$router->aliasMiddleware('py-app.sign', AppSignMiddleware::class)`。

### 算法规约（参考实现 `Poppy\App\Classes\Sign\DefaultAppSign`）

**请求必传参数**：

| 参数         | 必选 | 类型      | 说明                                            |
|------------|-----|---------|-----------------------------------------------|
| `appid`    | 是   | int     | `sys_app.id`，用于定位应用                             |
| `timestamp`| 是   | int     | 秒级 Unix 时间戳；当前实现**仅校验存在，不校验时间窗口**            |
| `sign`     | 是   | string  | 32 位 MD5 签名串                                   |
| `_py_secret` | 否  | string  | **调试旁路**：若存在且等于 `config('poppy.system.secret')` 则跳过签名 |

**签名计算步骤**：

1. 丢弃所有以 `_` 开头的参数（保留其余原始键）。
2. `Arr::except($params, ['sign', 'image', 'file', 'appid'])`。
3. 数组参数原样保留，标量参数 `trim((string)$param)`。
4. `ksort($params)` 按 key 排序。
5. `ArrayHelper::toKvStr($params)` → `k1=v1&k2=v2&...`。
6. `sign = md5(md5(kvStr) . secret)`（`secret` 取自 `sys_app.secret`，长度必须为 32）。

**校验失败响应**：

- `Resp::PARAM_ERROR`（"未传递时间戳"/"未进行签名"/"请传入 Appid"/"应用不存在"/"错误的密钥"）
- `Resp::SIGN_ERROR`（"签名错误"）—— 同时通过 `sys_warning('poppy.app-sign_error', [], true)` 上报警告日志。

### 客户端调用封装

`DefaultAppSign::sign(array $params, int $appid, string $secret): array`

自动注入 `appid`、`timestamp`、`sign`，并把签名结果合并回 `$params` 返回。**消费方在外部模块的 SDK / 客户端库里可直接调用**，参考 `tests/Classes/TestSign.php::testCheck()` 的用法。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方                             | 目标模块             | 目标类                       | 调用方法                  | 场景                                                                 |
|------------------------------------|------------------|---------------------------|-----------------------|--------------------------------------------------------------------|
| `Http\MgrPage\FormAppEstablish`    | `poppy/core`     | `Permission`              | `$permission->type()` / `key()` / `description()` | 过滤出 `type='app'` 的权限点，渲染 `permissions` 多选框                          |
| `Http\MgrPage\FormAppEstablish`    | `poppy/core`     | `CoreTrait::corePermission()` | `corePermission()`    | 拉取整个权限树                                                          |
| `Http\MgrPage\FormAppEstablish`    | `poppy/system`   | `PamAccount`              | `kvType()`            | 渲染"账号类型"下拉                                                       |
| `Http\MgrPage\ListSysApp`          | `poppy/system`   | `PamAccount`              | `kvType()`            | 列表列"绑定用户"显示类型 + ID                                              |
| `Http\MgrPage\ListSysApp`          | `poppy/system`   | `SysConfig`               | `YES` / `NO` 常量       | 启用/禁用按钮的 status 参数                                              |
| `Http\Validation\AppEstablishRequest` | `poppy/system`   | `PamAccount`              | `kvType()`            | `account_type` 校验 `in(array_keys(PamAccount::kvType()))`            |
| `Action\App::status`               | `poppy/system`   | `SysConfig`               | `YES` / `NO` 常量       | 状态值                                                              |
| `Models\SysApp::item`              | `poppy/framework` | `Resp`（间接通过 `sys_tag`）     | `sys_tag('py-app')`   | 标签缓存（带 1 月 TTL）                                                  |
| `Http\Request\Backend\AppController` | `poppy/mgr-page` | `Grid` / `ListSysApp` / `FormAppEstablish` | 列表/表单渲染 | 渲染后台管理界面                                                          |

## 被其他模块调用（本模块被引用）

| 调用方模块            | 调用方类          | 本模块目标类                  | 调用方法                  | 场景                              |
|------------------|---------------|--------------------------|-----------------------|---------------------------------|
| 其他业务模块（API 接入方） | 路由定义文件        | `Http\Middlewares\AppSignMiddleware` | `py-app.sign` 中间件挂载   | 对外开放 API 时强制签名校验               |
| 其他业务模块          | 任意业务 Action   | `Models\SysApp`          | `SysApp::item($appid)` / `SysApp::check($appid, $permission)` | 读取应用条目或校验应用权限（**当前代码内无消费方**） |
| 客户端 / SDK       | 应用签名客户端代码     | `Classes\Sign\DefaultAppSign` | `sign($params, $appid, $secret)` | 计算请求签名                          |

---

## 待确认

- **本模块是否被 `poppy/system`、`poppy/content`、`poppy/area` 等模块实际挂载 `py-app.sign` 中间件**：扫描其他模块路由文件应能确认（**当前分析范围未覆盖**），若有挂载，需要补充"被引用"列表。
- **`DefaultAppSign` 是否在客户端包中也有同名实现**：服务端实现位于 `Poppy\App\Classes\Sign\DefaultAppSign`，但对外 API 客户端（PHP SDK / JS SDK）若要对接，需要复用同一算法；具体客户端实现路径未在本模块中找到。
- **`FormAppEstablish` 中"加载权限点"的来源细节**：`CoreTrait::corePermission()` 返回 `Permission` 集合并按 `type() === 'app'` 过滤；`type` 字段在 `poppy/core` 中如何填充（与 `PamPermission` 的 `type` 是否一致）需要确认。