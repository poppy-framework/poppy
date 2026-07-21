# 业务逻辑

## 账号与登录（PAM）

### 业务规则

- **账号类型两分**：前台用户（`PamAccount::TYPE_USER = 'user'`）与后台管理员（`TYPE_BACKEND = 'backend'`），二者生成的用户名规则、注册流程、SSO 行为都不同——后台账号**不允许**自动注册（`Action\Pam::captchaLogin()` 中遇 `backend` guard 直接拒绝）
- **通行证自动归一化**：`PamAccount::fullFilledPassport()` 去掉空格、转小写、中国手机号自动补 `86-` 前缀，避免同一手机号因格式差池导致账号分裂
- **Passport 类型识别**：账号登录字段按"邮箱 / 手机号 / 用户名"自动判定，优先级在 `PamAccount::passportType()`，登录凭证 keys 也由该方法决定，登录接口只用 1 个字段 `passport` 即可适配三种场景
- **密码哈希策略（自研）**：密码 = `md5(sha1(plain + created_at_datetime) . password_key)`，其中 `password_key` 是用户注册时随机生成的 6 位字符串。这样相同明文 + 不同用户每次存盘不同，且 `JWTGuard` 自定义 claim 中携带 `user.salt = md5(sha1(password_key) . password)`，让旧 Token 在修改密码后立刻失效
- **JWT Token 失效机制**：除签名过期外，`Middleware\JwtAuthenticate` 会用当前账号最新 `password_key + password` 重算 salt 与 claim 比对；`Middleware\AuthenticateSession`（SessionGuard）在每次请求时把 `password_hash` 存入 Session，密码变更后旧 Session 自动 401

### 路由/分发规则

| 条件                                            | 处理路径                                                                                |
|-----------------------------------------------|---------------------------------------------------------------------------------------|
| 用户传 `captcha` 字段                           | 走验证码登录（`Action\Pam::captchaLogin()`），同时校验频率 + 测试账号 + 真实验证码                                   |
| 用户传 `password` 字段                          | 走密码登录（`Action\Pam::loginCheck()`），自动选 `jwt_backend` / `jwt_web` Guard                       |
| 账号不存在 + `poppy.system.captcha_register=true` | 自动 `register()`，并将 `is_register=Y` 透出给前端（仅 `user` 类型允许，**`backend` 永远不允许**）                    |
| `verify_code` + `captcha` 同时传                | `AuthController::resetPassword` / `bindMobile` 路径：二选一，互斥校验                                          |

### 状态机（账号封禁）

```
ENABLED ──(Action\Pam::disable)──> DISABLED
   ↑                              │ (disable_end_at 已过 或 主动 enable)
   └──(Action\Pam::enable/autoEnable)─────────────────────┘
                                        ↑
                                  定时自动解禁：py-system:user auto_enable
```

- `PamAccount::is_enable` 为 `NO` 且 `now < disable_end_at` ⇒ 拦截登录（`Action\Pam::checkIsEnable`）
- 登录时若到期，自动调用 `enable($pam->id, '用户登录, 超过封禁时间, 自动解禁')` 并放行
- `py-system:user auto_enable` 由调度每 15 分钟扫一次：`is_enable=DISABLE && disable_end_at <= now()` 自动解禁

### 关键算法

- **JWT 自定义 claim salt**（详见 `PamAccount::getJWTCustomClaims()`）：保证改密码后旧 Token 在 JwtAuthenticate 中间件立即失效
- **密码强度校验**（`PamAccount::pwdStrength`）：正则检测数字 / 字母 / 大小写 / 特殊字符，与设置 `py-system::pam.{type}_pwd_strength` 取交集
- **PAM register username 自动生成**：非用户名类型时，前缀 = `sys_setting('py-system::pam.prefix', 'PF')`，形如 `PF_20251220153012AbCdEf`

## 单点登录（SSO）

### 业务规则

- **三种 SSO 模式**（由 `py-system::pam.sso_type` 控制）：
    - `Sso::SSO_NONE` —— 不启用，方法 `handle()` / `renew()` / `logout()` 均直接放行（向后兼容默认）
    - `Sso::SSO_DEVICE_NUM` —— 允许同时登录设备最多 N 个（`py-system::pam.sso_device_num`，默认 10），超出按 id 升序淘汰最早 Token
    - `Sso::SSO_GROUP` —— 按设备类型分组，`kicked` 组（Android/iOS/HarmonyNext）同组互踢；`unlimited` 组（H5/WebApp）不限
- **设备必传**：`x-id`（device_id） + `x-os`（device_type）必须都存在（特殊：开启 `py-system::pam.sso_os_empty_hold`=Y 时允许为空 os 通过）
- **OS 白名单**：只允许 `android / ios / harmony_next / h5 / webapp`（`Sso->groups` 默认配置，可被 `poppy.system.sso_group` 覆盖）
- **Token 持久化**：`PamToken` 表 + `py-system-persist:sso-valid` Redis 哈希双写；中间件 `sys-sso` 通过哈希比对 `md5(jwt_token)`，使 SSO 踢人实时生效
- **改密码联动**：用户改密码 → `PamPasswordModifiedEvent` → `Sso::banUser($accountId)` 把 SSO Token 清空，前台强制重新登录（后台不触发，这是有意的区别）

### 路由/分发规则

| 入口           | 触发条件                  | 处理                                                                                |
|--------------|-----------------------|-----------------------------------------------------------------------------------|
| 登录成功（JWT 颁发） | `AuthController::login` 末段 `event(LoginTokenPassedEvent)` | `Listeners\LoginTokenPassed\SsoListener` → `Sso::handle()`：写入/更新 Token，按模式剔除旧设备 |
| 退出登录（前台）  | `AuthController::logout` → `Pam::logout()` | 触发 `PamLogoutEvent`，仅 `PamAccount::TYPE_USER` 走 `Sso::logout()`            |
| 改密码         | `Pam::setPassword()`                | 触发 `PamPasswordModifiedEvent`，仅前台走 `Sso::banUser()`                           |
| 定时清理        | `py-system:user clear_expired` 6:00 | `Sso::clearExpired()`：清理 `expired_at<now()` 的 PamToken 与对应 hash               |

> 完整事件级联、middleware 执行链见 flows.md。

## 验证码 / 频率限制

### 业务规则

- **通道支持**：`mail` / `mobile` 两种（`Verification::TYPE_MAIL` / `TYPE_MOBILE`），其余类型 `checkPassport()` 直接拒绝
- **生成参数**：
    - 长度：`py-system::pam.captcha_length`（默认 6）
    - 有效期（分钟）：`py-system::pam.captcha_expired`（默认 5）
- **验证码 Redis 存储**：`py-system-persist:verification-captcha:{type-passport}`，TTL = `expired_min * 60`，再次请求时若仍在 `silence` 窗口（生成后 60 秒）会复用上一次的 code
- **验证规则**：单次有效可配置清除（`forget=true`），同时支持 `py-system::pam.test_account` 多行 `passport:captcha` 用于测试
- **频率限制**：相同 passport + `login-` 前缀 30 秒限 1 次（`Verification::isPassThrottle`），非生产环境下不生效
- **生成 verify_code**：通过 `genOnceVerifyCode()` 生成一次性 `verify_code` 字符串，含序列化隐藏字段用于重置密码 / 换绑手机（默认 10 分钟过期）

### 路由/分发规则

| 接口                   | 条件                                              | 处理                                                                          |
|----------------------|-------------------------------------------------|-----------------------------------------------------------------------------|
| `captcha/send`       | `type=exist` → 必须存在；`type=no-exist` → 必须不存在    | `Verification::genCaptcha` → `event(CaptchaSendEvent)`，由宿主模块（短信 / 邮件服务）真正下发     |
| `captcha/verify_code`| captcha 通过                                       | `genOnceVerifyCode($expire_min, $passport)` 隐藏写入 = passport                            |
| `auth/reset_password`| `verify_code` 与 `passport+captcha` 二选一          | 两种方式最终都到 `Pam::setPassword()`，触发 `PamPasswordModifiedEvent`                      |

## IP / 设备封禁（Ban）

### 业务规则

- **黑白名单切换**：`py-system::ban.type-{type}` = `black/white`（账号类型独立）
- **IP 段格式**：
    - `192.168.1.21-192.168.1.255`（`ip2long` 范围匹配）
    - `192.168.1.*` 或 `CIDR`（`IPLib\Factory::parseRangeString` 解析）
    - 单 IP（`UtilHelper::isIp` 校验）
- **设备封禁开关**：`py-system::ban.device_{black|white}_{type}_is_open` = `Y/N`
- **缓存结构**（由 `Ban::initCache()` 在 `PoppyOptimized` 事件中初始化一次）：
    - Hash：`py-system:ban-one-{type}` → `{type}|{value}`
    - Set：`py-system:ban-ip-range-{type}` → `range-{id}|{startIp}-{endIp}`
- **踢人联动**：`Action\Ban::type($tokenId, $type)` 既落库 `PamBan`，又删 `PamToken` + `event(PamTokenBanEvent)` 通知被踢方

### 路由/分发规则

| 中间件              | 触发场景                  | 行为                                                                                  |
|------------------|-----------------------|-------------------------------------------------------------------------------------|
| `sys-ban`        | `api-sign` / `api-sso` 入口 | `Ban::checkIn($type, 'ip', $ip)` + 可选设备黑白名单，黑名单命中 / 白名单未命中即返回错误                       |
| `sys-ban:{type}` | 显式指定账号类型               | 中间件第 3 参数 `string $type`，默认 `user`；`x_header('type')` 覆盖                               |

## 文件上传（FileProvider / Hook 派发）

### 业务规则

- **多 Provider 派发**：通过 `sys_hook('poppy.system.upload_type')` 拿到所有 key，默认 `default` = `Classes\File\DefaultFileProvider`；上传类型 `save_type` 配置 `py-system::picture.save_type` 决定使用哪一个
- **磁盘与目录**：默认 `disk = 'public'`，生成路径形如 `{dev/?}uploads/{Ym/d/H/issssssss}.{ext}`
- **图片自动压缩**：超过 `resizeDistrict`（默认 1920 短边）或 `resizeLongDistrict`（最大长边）时通过 `Intervention\Image` 缩放，质量由 `setQuality()` 控制，默认 70；不处理 GIF / 非图片扩展
- **三类上传入口**：
    - `form`：标准 multipart/form-data，需校验 `allow_extensions` 与 `allow_image_mimes`
    - `base64`：支持 `data:image/jpeg;base64,xxx` 与裸 base64
    - `url`：从 URL 拉取后再走 `Intervention\Image::make()` 入库
- **类型 → 扩展映射**（`FileManager::kvExt`）：`images`（jpg/jpeg/png/gif/bmp/svg），`file`，`video`，`audio`
- **异步删除**：`Jobs\DeleteUploadFileJob` 把 URL 解析成 path，调 `FileContract::setDestination()` + `delete()`

### 路由/分发规则

| 接口                  | 条件                                  | 处理                                                                                  |
|---------------------|-------------------------------------|-------------------------------------------------------------------------------------|
| `upload/image`      | `type=form/base64/url`               | `DefaultFileProvider`（除非 hook 派发了其他实现）；支持水印（`enableWatermark`）                       |
| `upload/file`       | `type=images` 自动按 `district` 短边压缩 | 同上；`type` 用于决定 `setFolder()` 和允许扩展                                              |
| `sys_is_demo()`     | 演示模式                                | 直接返回示例 URL（`https://i.wulicode.com/img/400`）                                       |

## 系统设置（SettingRepository）

### 业务规则

- **存储格式**：`sys_config` 表按 `namespace.group.item` 三段存（如 `py-system::pam.sso_type`）；`value` 字段为 `serialize($value)`，并写 `content` 字段（JSON cast）便于后端展示
- **写入流程**：`SettingRepository::set($key, $value)` —— 落库（`updateOrCreate`）→ `event(SysConfigSavedEvent)` → 写 Redis（`py-system:setting` hash）
- **读取流程**：`sys_setting()` 全局函数 → `SettingRepository::get()` —— Redis 命中直接 `unserialize` 返回，未命中查库；表不存在返回默认值（`existTable` 静态位）防止 migrate 期间抛 PDOException
- **缓存清理**：`Listeners\PoppyOptimized\ClearCacheListener` 在框架优化钩子清空 `py-system` tag 的所有 key

### 路由/分发规则

| 行为   | 触发                | 业务动作                                            |
|------|-------------------|-------------------------------------------------|
| 全清   | `py-system:user ban_init` | `Ban::initCache()` 重新刷缓存                       |
| 全清设置 | `SettingServiceProvider::clear()` | 清 `py-system:setting` 哈希                      |
| 监听   | `Listeners\PoppyOptimized\ClearCacheListener` | 框架优化事件，清空 cache tag |

## 定时任务 / Artisan 命令

| 命令/任务                                                  | 调度频率                       | 业务动作                                                                                                          |
|--------------------------------------------------------|----------------------------|---------------------------------------------------------------------------------------------------------------|
| `py-system:user auto_enable`                           | 每 15 分钟                    | `Pam::autoEnable()`：对到期账号自动解禁并触发 `PamEnableEvent`                                                                  |
| `py-system:user clear_log`                             | 每日 04:00                   | `Pam::clearLog()`：清空 `created_at` 在 N 天前（设置 `py-system::log.days`，默认 180 天，`DAYS_FOREVER` 时跳过）                          |
| `py-system:user clear_expired`                         | 每日 06:00                   | `Sso::clearExpired()`：清 `expired_at < now()` 的 `PamToken`，并清掉 `sso-valid` 哈希中键                                       |
| `py-system:user ban_init`                              | 手动                          | `Ban::initCache()`：按账号类型刷 hash + set                                                                        |
| `py-system:user init_role`                             | 手动                          | 保证存在 `PamRole::FE_USER` + `PamRole::BE_ROOT`                                                                   |
| `py-system:user reset_pwd` / `create_user` / `auto_fill` / `assign` / `user` / `check_perm` | 手动                          | 维护账号 / 角色 / 权限                                                                                                  |
| `py-system:install` / `py-system:ban` / `py-system:op` / `py-system:sys_config_convert` | 手动                          | 安装、封禁、运维、配置迁移                                                                                                  |

> 调度通过 `ServiceProvider::registerSchedule()` 在 `console.schedule` 事件中注册到 `Illuminate\Console\Scheduling\Schedule`，配套 `everyFifteenMinutes()` / `dailyAt('04:00')` 等。

## 中间件规则

| 中间件                        | 应用范围                  | 规则                                                                                |
|----------------------------|-----------------------|-----------------------------------------------------------------------------------|
| `api-sign`                | `auth/login` 等公开接口    | `sys-ban` → `sys-app_sign` → `sys-site_open`                                          |
| `api-sso`                 | `auth/access` / `auth/renew` / `auth/logout` | `sys-app_sign` → `sys-site_open` → `sys-ban:user` → `sys-sso` → `sys-auth:jwt_web` |
| `sys-jwt`                 | `upload/*`            | JwtAuthenticate 校验 token 存在 + claim `user.salt` 与最新密码匹配                                    |
| `sys-auth[:guard]`        | 管理员后台                 | Authenticate：失败 401 + JSON 响应或重定向到 `poppy.framework.prefix/login`                       |
| `sys-auth_session`        | 后台 w-auth             | SessionGuard 下用 `password_hash` 校验改密后旧 Session 立即 401                                |
| `sys-ban[:type]`          | 黑白名单                   | 见上"IP / 设备封禁"                                                                    |
| `sys-sso`                 | `api-sso` 末段          | JWT + md5 比对 `py-system-persist:sso-valid`                                          |
| `sys-site_open`           | 公开入口                  | `py-system::site.is_open = N` 且账号类型为 `user` 时阻断，否则放过                                       |
| `sys-mgr-rbac`           | 后台独立路由                | 检查当前账号对目标路由的 RBAC 权限                                                              |
| `sys-disabled_pam`        | -                     | 直接拒绝禁用账号，仅在 `sys-auth` 失败后兜底（已合并进 `Authenticate`）                                          |
| `sys-html_purifier`       | 富文本提交                 | 清洗 XSS                                                                            |
| `sys-app_sign`            | 全部 api                | `ApiSignContract::check()`（默认 `DefaultApiSignProvider`：`md5(md5(kvStr).token)` 拼接四位）       |

## 待确认

- `py-system:user init_role` 等子命令在 `UserCommand.php` 中实现但**未注册到 `ServiceProvider::registerSchedule()`**，需要手动触发；调度项是否需要补全待确认（发现位置：`src/ServiceProvider.php::registerSchedule()` vs `src/Commands/UserCommand.php::handle()`）
- `Listeners\AuthLogout\LogoutLogListener` 已写但未在 `$listens` 中绑定，需确认是否仍有意保留（发现位置：`src/Listeners/AuthLogout/LogoutLogListener.php` + `src/ServiceProvider.php::$listens`）
- `Middleware\DisabledPam`、`Middleware\MgrRbacPermission`、`Middleware\RequestIdMiddleware` 已写但本次未在常用路径出现，待确认其触发路由（发现位置：`src/Http/Middlewares/`）
