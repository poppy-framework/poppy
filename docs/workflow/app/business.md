# 业务逻辑

## 第三方应用接入（SysApp 管理）

### 业务规则

- **每个接入方必须有唯一 `name` 标识**：`name` 是 5-16 位小写字母开头的 slug（`^[a-z][a-z0-9_]{4,15}$`），同时在 `sys_app` 表上唯一。原因是 `name` 在调用场景中可能被外部系统用于快速检索应用，且要求保持机器可读性。
- **`secret` 必须是 32 位字符串**：创建时由后台手动录入（表单用 `Rule::size(32)`），签名校验时再次硬校验 `strlen($item['secret']) !== 32`，避免历史脏数据绕过签名。32 位长度刚好等于 `md5()` 输出，对客户端来说无需额外编码。
- **`account_id` + `account_type` 双键绑定 PAM 账号**：`account_type` 取自 `PamAccount::kvType()` 的合法值（多账号体系：管理员 / 前台用户 / …）。原因：PAM 账号在 `poppy/system` 模块中按 `type` 区分（同一手机号在不同体系下可能拥有多个账号），所以应用必须明确指明绑定到哪一类账号。
- **`is_enable` 启用开关决定是否能通过验签**：禁用状态下签名直接被拒（"此应用已禁用"）。这是最低成本的"封禁"开关，比删除更可恢复。
- **`permissions` 字段按权限 KEY 列表存储**：用逗号分隔（`text` 列），由 `SysApp::getPermissionsAttribute()` 自动 `explode` 还原成数组。**只放 `type='app'` 的权限点**，由 `FormAppEstablish` 在表单构造时通过 `corePermission()` 过滤得出——避免与应用内 RBAC 权限混淆。

### 路由/分发规则

| 条件                       | 处理路径                                |
|--------------------------|-------------------------------------|
| 后台管理（admin 操作）           | `py-app:backend.*` 路由组（`backend-auth` 中间件） |
| 任意业务模块需要 API 签名         | 在其路由组挂载 `py-app.sign`（`AppSignMiddleware`） |

### 状态机

```
DRAFT (无记录) ──create──▶ ENABLED (is_enable=1)
                              │
                       status(0)
                              │
                              ▼
                          DISABLED (is_enable=0)
                              │
                       status(1)
                              │
                              ▼
                          ENABLED
```

状态说明：

- `ENABLED → DISABLED`：在后台列表点击"禁用"按钮（`py-app:backend.app.status` 路由），对应 `Action\App::status()`；同时**清理 Redis 缓存**（`sys_tag('py-app')->del(ckItem($appid))`），否则已缓存的应用条目还在生效，禁用会延迟生效。
- `DISABLED → ENABLED`：同上，调用 `status($id, SysConfig::YES)`。
- **没有"删除"操作**：模块只暴露 `establish`（创建/更新）和 `status`（启用/禁用），没有 `destroy`。需要废弃应用时直接禁用即可。

### 关键算法/计算

#### 应用签名算法（HMAC-like）

```
sign = md5( md5( kvStr(except(input)) ) + secret )
```

关键步骤：

1. **过滤**：丢弃所有以 `_` 开头的参数（如 `_py_secret`、`_top_reload`），再 `Arr::except` 掉 `sign` / `image` / `file` / `appid`；数组参数原样保留，标量参数做 `trim`。
2. **排序**：按 key 名 `ksort`（保证客户端/服务端一致）。
3. **序列化**：`ArrayHelper::toKvStr($params)` 转成 `k1=v1&k2=v2&...` 字符串。
4. **二次哈希**：先 `md5(kvStr)` 再拼接 `secret` 做 `md5(...)`。**这种"双 md5 + secret 拼接"是 Poppy 框架经典签名方式**，能防止 secret 被反查 rainbow table。
5. **客户端调用约定**：调用方在请求体里额外带 `appid`、`timestamp`（秒级 Unix 时间戳）、`sign`，中间件按同样规则重算后比对。

#### 调试旁路

- 当请求里带 `_py_secret` 且等于 `config('poppy.system.secret')` 时**跳过验签**。原因：测试 / Clockwork 调试场景下不希望被签名阻拦。

## 定时任务 / Artisan 命令

| 命令/任务 | 调度频率 | 业务动作 |
|--------|------|------|
| 无     | —    | —    |

> 应用管理是纯后台手动操作，没有定时清理或同步任务。

## 中间件规则

| 中间件                    | 应用范围                                            | 规则                                                                                |
|------------------------|-------------------------------------------------|-----------------------------------------------------------------------------------|
| `AppSignMiddleware`（别名 `py-app.sign`） | 由其他模块按需挂在对外 API 路由组上（**本模块自己不挂任何 API 路由**） | 1) 带 `_py_secret` 等于系统密钥 → 直接放行；<br/>2) 校验 `timestamp` / `sign` / `appid` 存在；<br/>3) 加载 `SysApp::item($appid)`（带 1 月缓存）；<br/>4) 应用需 `is_enable=1`；<br/>5) `secret` 必须 32 位；<br/>6) 用 `DefaultAppSign::calcSign` 重算并比对 `sign`，失败时 `sys_warning('poppy.app-sign_error')` 上报并返回 `Resp::SIGN_ERROR`。 |

> 应用列表上的 `enabled` / `disabled` 切换按钮通过 `py-app:backend.app.status` 路由调用，路由本身在 `backend-auth` 中间件之后，**不走 `AppSignMiddleware`**——后台管理员操作不需要签名。

---

## 待确认

- **`timestamp` 是否做时间窗口校验**：`DefaultAppSign::check()` 只验证 `timestamp` 存在，没有看到 `abs(now - timestamp) > N` 的窗口判断逻辑（可能由其他中间件/调用方负责）。**发现位置：`Poppy\App\Classes\Sign\DefaultAppSign::check()`**。
- **`secret` 是否在后台以明文回显**：表单 `FormAppEstablish::form()` 直接渲染 `secret` 文本框（`$this->text('secret', '应用密钥')->rules([Rule::size(32)])`），**未发现脱敏处理**。若安全策略要求只写不读，需要改造为仅创建时显示一次。**发现位置：`Poppy\App\Http\MgrPage\FormAppEstablish::form()`**。
- **`name` 字段的实际使用方**：`SysApp` 模型定义了 `name` 列，但 `DefaultAppSign` 没有用到它（只用 `appid` + `secret`）；`App` 模块当前对外暴露的签名流程以 `appid` 寻址。**`name` 字段的存在目的（是否为预留字段？）需要确认**。**发现位置：`SysApp::fillable` / `DefaultAppSign::check()`**。
- **是否允许多个应用绑定同一个 PAM 账号**：模型未对 `account_id` + `account_type` 加唯一约束，从代码看可以重复绑定；如果业务不允许，需要加唯一索引。
- **应用权限（`type='app'`）的实际消费方**：本模块的 `SysApp::check($appid, $permission)` 暴露了权限判定，但代码内**没有调用方**（可能由业务模块按需引入），需后续在调用模块里确认消费模式。
- **`_py_secret` 旁路是否还在生产环境启用**：`config('poppy.system.secret')` 是否会被注入真实 secret（应仅在 staging / dev 环境），需要运维策略确认。**发现位置：`DefaultAppSign::check()` 顶部 debug 分支**。