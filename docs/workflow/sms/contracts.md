# 对外契约

> 默认后台路由前缀：`/mgr-page/py-sms`。`mgr-page` 来自 `config('poppy.framework.prefix')`，可被宿主配置覆盖；路由组统一使用 `backend-auth`。
> 权限：`backend:py-sms.global.manage`，由 `SmsController` 和 `StoreController` 设置。

## API 路由（api_v1.php）

本模块没有 `api_v1.php` 或其他 API 路由。短信发送不是由 SMS 模块暴露的 HTTP API 完成，而是由容器中的 `SmsContract` 供业务代码调用。

## 管理后台路由（backend.php）

源码：`poppy/sms/src/Http/Routes/backend.php`。以下 URI 以默认 `poppy.framework.prefix=mgr-page` 展开；若宿主修改该配置，只替换最前面的前缀。

| HTTP 方法 | URI | 请求类/控制器 | 中间件 | 路由名 | 说明 |
|---|---|---|---|---|---|
| GET | `/mgr-page/py-sms/sms` | `Poppy\Sms\Http\Request\Backend\SmsController::index` | `backend-auth` | `py-sms:backend.sms.index` | 按 `_scope` 查询并展示短信模板，默认 `local` |
| ANY | `/mgr-page/py-sms/sms/establish/{id?}` | `Poppy\Sms\Http\Request\Backend\SmsController::establish` → `FormEstablishSms` | `backend-auth` | `py-sms:backend.sms.establish` | 新增或编辑 `{scope}:{type}` 模板 |
| ANY | `/mgr-page/py-sms/sms/destroy/{id}` | `Poppy\Sms\Http\Request\Backend\SmsController::destroy` | `backend-auth` | `py-sms:backend.sms.destroy` | 删除指定模板设置 |
| ANY | `/mgr-page/py-sms/sms/store` | `Poppy\Sms\Http\Request\Backend\SmsController::store` → `FormSettingSms` | `backend-auth` | `py-sms:backend.sms.store` | 设置签名和各平台分流权重，并链接供应商设置 |
| ANY | `/mgr-page/py-sms/store/aliyun` | `Poppy\Sms\Http\Request\Backend\StoreController::aliyun` → `FormSettingAliyun` | `backend-auth` | `py-sms:backend.store.aliyun` | 阿里云 Key/Secret 设置 |
| ANY | `/mgr-page/py-sms/store/chuanglan` | `Poppy\Sms\Http\Request\Backend\StoreController::chuanglan` → `FormSettingChuanglan` | `backend-auth` | `py-sms:backend.store.chuanglan` | 创蓝国内/国际 Key/Secret 设置 |
| ANY | `/mgr-page/py-sms/store/lianlu` | `Poppy\Sms\Http\Request\Backend\StoreController::lianlu` → `FormSettingLianLu` | `backend-auth` | `py-sms:backend.store.lianlu` | 联麓国内/国际企业 ID、App ID、App Key 设置 |
| ANY | `/mgr-page/py-sms/store/volc` | `Poppy\Sms\Http\Request\Backend\StoreController::volc` → `FormSettingVolc` | `backend-auth` | `py-sms:backend.store.volc` | 火山云 AccessKey、Secret、默认消息组 ID 设置 |

## 发布的事件（本模块对外）

本模块没有 `src/Events`，也没有在 `Action`、Provider 或 Controller 中发布事件。`CaptchaSendEvent` 属于 `Poppy\System`，不是 SMS 模块发布的事件；系统验证码控制器发布它后，是否有宿主 Listener 消费需看下方跨模块契约。

## 监听的事件（本模块消费）

本模块没有 `src/Listeners`，`ServiceProvider` 也没有 `$listens` 注册。对 `poppy/*/src/Listeners/`、尤其是 `poppy/system/src/Listeners/` 的扫描没有找到 SMS 或验证码发送监听器；`poppy/system/src/Jobs/` 也没有短信发送 Job。因此当前代码中不存在“验证码事件 → SMS Provider”的已注册执行契约。

| 监听器类 | 监听的事件 | 业务动作 | 产生的事件/任务 |
|---|---|---|---|
| — | — | 本模块不消费事件 | — |

## 短信驱动契约

### `Poppy\Sms\Classes\Contracts\SmsContract`

```php
public function send(
    string $type,
    $mobile,
    array $params = [],
    string $sign = ''
): bool;
```

参数与结果：

| 参数 | 类型/含义 |
|---|---|
| `$type` | 短信业务类型，例如默认配置中的 `captcha`；必须能在模板设置中找到对应类型 |
| `$mobile` | 单个手机号或手机号数组；具体 Provider 对数组/国际号码的支持不同 |
| `$params` | 模板变量或供应商模板参数；本地驱动使用 Laravel 翻译参数，远端驱动按其模板协议转换 |
| `$sign` | 可选的单次发送签名；非空时覆盖系统默认签名 |
| 返回值 | 成功 `true`；前置校验、供应商失败或 SDK 初始化失败通常返回 `false`，错误可从 Provider 的 `getError()` 获取；部分 JSON 异常按实现可能向上抛出 |

### 容器绑定

`Poppy\Sms\ServiceProvider::register()` 注册：

| 解析方式 | 实现 |
|---|---|
| `app('poppy.sms')` | `Poppy\Sms\Classes\SmsProvider` |
| `app(Poppy\Sms\Classes\Contracts\SmsContract::class)` | `Poppy\Sms\Classes\SmsProvider`（`alias`） |

`SmsProvider::send()` 按短信类型选择一个 Provider，再转发同一组 `$type/$mobile/$params/$sign` 参数。内置 Provider 均实现该接口：`LocalSmsProvider`、`AliyunSmsProvider`、`ChuanglanSmsProvider`、`LianLuSmsProvider`、`VolcSmsProvider`。平台可通过 `poppy.sms.send_type` Hook 扩展，但 Hook 必须提供 `provider` 类并满足该契约。

## 模板与设置契约

| 设置键 | 写入方 | 数据契约 |
|---|---|---|
| `py-sms::sms.template` | `Poppy\Sms\Action\Sms::establish()` / `destroy()` | 关联数组，键 `{scope}:{type}`，值包含 `scope`、`type`、`code` |
| `py-sms::sms.sign` | `FormSettingSms` | 全局默认签名，字符串，可为空保存但实际发送必需 |
| `py-sms::sms.send_rate_{scope}` | `FormSettingSms::handle()` | 整数 `0..100`；用于相对权重，不是频率上限 |
| `py-sms::sms.aliyun_access_key` / `_secret` | `FormSettingAliyun` | 阿里云凭据，表单规则为 nullable |
| `py-sms::sms.chuanglan_access_key` / `_secret` / `chuanglan_cty_access_key` / `_secret` | `FormSettingChuanglan` | 创蓝国内/国际凭据，表单规则为 nullable |
| `py-sms::sms.lianlu_mch_id` / `app_id` / `app_key` 及 `lianlu_cty_*` | `FormSettingLianLu` | 联麓国内/国际凭据，表单规则为 nullable |
| `py-sms::sms.volc_access_key` / `_secret` / `volc_default_account` | `FormSettingVolc` | 火山云凭据和默认消息组 ID，表单规则为 nullable |

## 请求验证规则

### `Poppy\Sms\Http\Validation\SmsEstablishRequest`

| 字段 | 规则 | 属性名 |
|---|---|---|
| `type` | `required`；`in(array_keys(Sms::kvType()))` | 短信类型 |
| `scope` | `required`；未在 Request 中验证为已注册平台 | 平台类型 |
| `code` | `required` | 短信模版 |

`FormSettingSms` 另外把所有 `send_rate_*` 输入转成整数，超出 `0..100` 时返回“错误的分流比例”；签名和各 Provider 凭据使用 `Rule::nullable()`。`BaseSms::checkSms()` 在实际发送时再次校验手机号、类型、模板和签名是否存在。

## 事件与跨模块发送契约

### 验证码事件的真实定义

| 事件类 | 数据 | 触发方 | 触发点 |
|---|---|---|---|
| `Poppy\System\Events\CaptchaSendEvent` | 公共属性 `$passport`、`$captcha`（源码未声明属性类型） | `Poppy\System\Http\Request\ApiV1\CaptchaController::send` | 验证码生成成功后 `event(new CaptchaSendEvent($passport, $captcha))` |
| `Poppy\System\Events\CaptchaSendEvent` | 公共属性 `$passport`、`$captcha`（源码未声明属性类型） | `Poppy\MgrPage\Http\Request\Backend\CaptchaController::send` | 后台验证码生成成功后 `event(new CaptchaSendEvent($passport, $captcha))` |

两处控制器只发布事件，不直接引用 `Poppy\Sms` 或 `SmsContract`。系统 API 入口还执行请求限流和 `Verification::isPassThrottle('send-' . $passport)`；后台入口执行后台账号存在性检查和验证码节流。

### 谁实际 dispatch SMS

当前仓库的事实结果如下：

1. 上述两个验证码控制器 dispatch 的是验证码事件，不是 SMS 请求。
2. `poppy/system/src/Listeners/` 中没有消费该事件并调用 `SmsContract::send()` 的 Listener。
3. `poppy/system/src/Jobs/` 中没有短信发送 Job。
4. 除短信自身测试外，没有扫描到其他模块直接 `use Poppy\Sms\...`、解析 `SmsContract` 或调用 `app('poppy.sms')`。

因此，当前仓库只提供“供宿主 Listener 调用”的 SMS API，并没有完成实际的事件到短信适配。若部署项目在仓库外注册 Listener，应由该 Listener 将事件中的 `passport` 映射到 `$mobile`，将 `captcha` 映射到模板参数（通常是 `['code' => $captcha]`），再调用 `SmsContract::send()`；该调用约定不应当视为当前源码已实现的事实。

## 队列任务

本模块没有 Job，也没有队列名、延迟或重试配置。`SmsContract::send()` 是同步接口。

## Artisan 命令

本模块没有 Artisan 命令。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方 | 目标模块 | 目标类/能力 | 调用方法/方式 | 场景 |
|---|---|---|---|---|
| `Poppy\Sms\ServiceProvider` | `poppy/framework` | `PoppyServiceProvider`、`RouteServiceProvider` | `parent::boot('poppy.sms')`、注册路由 | 模块启动和后台路由 |
| `Poppy\Sms\Action\Sms`、`BaseSms`、各 Provider | `poppy/system` | 系统设置、翻译、`SystemTrait`、设置异常 | `sys_setting()`、`sys_trans()`、`sysSetting()->set()` | 读取凭据/模板/签名，保存模板，渲染本地短信 |
| `Poppy\Sms\Hooks\Sms\SendType*` | `poppy/core` | `ServiceArray` / Hook 注册机制 | `key()`、`data()` | 声明动态平台、Provider 和设置路由 |
| `SmsController`、`StoreController`、MgrPage 表单 | `poppy/mgr-page` | `BackendController`、`FormWidget`、`FormSettingBase` | 继承/`render()` | 管理后台认证、权限和设置页 |

## 被其他模块调用（本模块被引用）

| 调用方模块 | 调用方类 | 本模块目标类 | 调用方法 | 场景 |
|---|---|---|---|---|
| 当前仓库未发现直接调用方 | — | `Poppy\Sms\Classes\SmsProvider` / `SmsContract` | `app('poppy.sms')` / `app(SmsContract::class)` | 短信测试证明了该容器契约，但业务调用方可能位于仓库外 |
| 宿主扩展（待确认） | 验证码事件 Listener（当前未找到） | `Poppy\Sms\Classes\Contracts\SmsContract` | `send('captcha', $passport, ['code' => $captcha])`（预期适配方式，非当前代码） | 将系统验证码事件转换为短信发送 |

## 待确认

- `CaptchaSendEvent` 的实际 Listener 是否由仓库外宿主注册；当前 `poppy/system/src/ServiceProvider::$listens` 没有该事件绑定。
- 验证码模板参数的正式键名是否固定为 `code`；系统事件只携带裸 `captcha` 字符串，SMS 模板默认描述使用 `code`，两者之间没有仓库内适配代码。
- `SmsContract::send()` 的调用方是否依赖 Provider 的 `getError()`；该方法来自 `AppTrait`，不在 SMS 契约接口中声明。
- 创蓝、联麓、火山云的运行时依赖是否由部署项目额外安装；短信模块自己的 `composer.json` 只声明 PHP 版本。

> 业务原因与分流规则见 [business.md](business.md)；完整执行链路见 [flows.md](flows.md)。
