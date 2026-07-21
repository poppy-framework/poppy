# 短信模块（`Poppy\Sms`）

## 模块职责

短信发送适配模块，负责短信模板与平台配置的后台维护，并通过统一的 `SmsContract` 将短信类型、手机号、模板参数和签名路由到本地、阿里云、创蓝、联麓或火山云驱动。模块本身是无状态的配置/SDK 包装层：模板和设置写入 `poppy.system` 的系统设置，不维护短信模型或发送记录表。

## 目录结构

| 目录 | 职责 | PHP 文件数 |
|---|---|---:|
| `Action` | 模板读取、创建/编辑/删除，以及按权重选择发送平台 | 1 |
| `Models` | Eloquent 模型 | 0（目录不存在） |
| `Events` | 领域事件定义 | 0（目录不存在） |
| `Listeners` | 事件监听器 | 0（目录不存在） |
| `Jobs` | 队列任务 | 0（目录不存在） |
| `Http/Request/Backend` | 管理后台控制器 | 2 |
| `Http/Routes` | 管理后台路由 | 1 |
| `Http/MgrPage` | 管理后台表单及平台设置页 | 6 |
| `Http/Validation` | 短信模板请求验证 | 1 |
| `Commands` | Artisan 命令 | 0（目录不存在） |
| `Classes` | 统一契约、驱动选择、供应商实现及第三方 API 封装 | 13 |
| `Hooks` | 注册可用短信平台及对应供应商/设置页 | 5 |
| `Exceptions` | 短信 SDK 缺失等异常 | 1 |

核心文件树：

```text
poppy/sms/
├── configurations/
│   ├── hooks.yaml                 # poppy.sms.send_type
│   ├── menus.yaml                 # 后台短信菜单
│   ├── permissions.yaml           # backend:py-sms.global.manage
│   └── services.yaml              # 注册 5 个发送类型 Hook
├── resources/
│   ├── config/sms.php             # 短信类型、默认签名、基础配置
│   ├── lang/zh/seo.php
│   └── views/backend/sms/index.blade.php
└── src/
    ├── Action/Sms.php
    ├── Classes/
    │   ├── Contracts/SmsContract.php
    │   ├── BaseSms.php
    │   ├── SmsProvider.php         # 容器绑定的统一入口
    │   ├── Factory.php             # 按发送类型选择单例驱动
    │   ├── LocalSmsProvider.php
    │   ├── AliyunSmsProvider.php
    │   ├── ChuanglanSmsProvider.php
    │   ├── LianLuSmsProvider.php
    │   ├── VolcSmsProvider.php
    │   ├── Chuanglan/SmsApi.php
    │   ├── LianLu/SmsApi.php
    │   ├── PySmsDef.php
    │   └── PySmsHelper.php
    ├── Http/
    │   ├── RouteServiceProvider.php
    │   ├── Routes/backend.php
    │   ├── Request/Backend/{SmsController,StoreController}.php
    │   ├── Validation/SmsEstablishRequest.php
    │   └── MgrPage/{FormEstablishSms,FormSettingSms,FormSettingAliyun,
    │       FormSettingChuanglan,FormSettingLianLu,FormSettingVolc}.php
    ├── Hooks/Sms/{SendTypeLocal,SendTypeAliyun,SendTypeChuanglan,
    │   SendTypeLianLu,SendTypeVolc}.php
    ├── Exceptions/SmsException.php
    └── ServiceProvider.php
```

## 技术栈

| 技术 | 版本/说明 |
|---|---|
| PHP | `>=7.4`（根项目及模块 `composer.json`） |
| Laravel | `6.*`（根项目 `composer.json`） |
| 模块框架 | `poppy/framework`，通过 `PoppyServiceProvider` 启动模块与路由 |
| ORM | 无模型、无 Eloquent 查询；配置由 `poppy/system` 的 `sys_setting()`/设置仓储保存 |
| 队列 | 本模块没有 Job 或异步发送；`send()` 同步调用供应商 |
| 缓存 | 未定义短信专用缓存；模板从系统设置读取，`PySmsDef::ckTemplate()` 返回 `py-sms::sms.template` |
| 认证 | 后台路由使用 `backend-auth`，控制器权限为 `backend:py-sms.global.manage` |
| 统一容器入口 | `ServiceProvider` 绑定 `poppy.sms`，并将其别名到 `Poppy\Sms\Classes\Contracts\SmsContract` |
| 阿里云依赖 | 根项目声明 `alibabacloud/dysmsapi-20170525:^4.6`；`AliyunSmsProvider` 调用 `Dysmsapi::sendSms()` |
| 其他供应商 | 创蓝、联麓使用模块内 cURL API 包装；火山云使用 `Volc\Service\Sms` SDK（代码运行时检查组件） |

## 路由概览

`Http\RouteServiceProvider` 将路由挂在 `poppy.framework.prefix` 下，默认前缀为 `mgr-page`，再追加 `/py-sms`；路由组统一使用 `backend-auth`。因此默认完整 URI 前缀是 `/mgr-page/py-sms`，具体契约及路由名见 [contracts.md](contracts.md)。

| 路由文件 | 类型 | 默认前缀 | 路由数 | 说明 |
|---|---|---|---:|---|
| `backend.php` | 管理后台 | `/mgr-page/py-sms` | 8 | 模板列表/建立/删除、总设置和四个平台设置页 |
| `api_*.php` | API | — | 0 | 模块未提供 API 路由 |
| `web.php` | Web 页面 | — | 0 | 模块未提供独立 Web 路由 |

## 模型清单

本模块没有 `Models` 目录，也没有迁移或独立数据表。模板保存为系统设置 `py-sms::sms.template`，每个键为 `{scope}:{type}`，值至少包含 `scope`、`type`、`code`；签名、分流权重和供应商凭据也通过 `poppy.system` 设置仓储保存。

## 依赖的其他模块

| 模块 | 引用方式 | 说明 |
|---|---|---|
| `poppy/system` | `Poppy\System\Classes\Traits\SystemTrait`、系统设置异常、`sys_setting()`/`sys_trans()` | 读写模板、签名和供应商凭据；本地驱动用系统翻译键渲染短信内容 |
| `poppy/framework` | `Poppy\Framework\Support\PoppyServiceProvider`、`AppTrait`、`backend-auth` 基础设施 | 启动模块、错误保存、路由和应用能力 |
| `poppy/core` | `Poppy\Core\Services\Contracts\ServiceArray` | 通过 `poppy.sms.send_type` Hook 注册驱动及其后台设置页 |
| `poppy/mgr-page` | `BackendController`、`FormSettingBase`、`FormWidget` | 提供后台控制器、表单渲染和权限入口 |

## 被其他模块依赖

| 调用方 | 引用方式 | 说明 |
|---|---|---|
| 宿主应用/业务模块 | `app('poppy.sms')` 或 `app(Poppy\Sms\Classes\Contracts\SmsContract::class)` | 可调用统一 `send()` API；当前仓库除短信测试外未发现直接调用方 |
| `poppy/system` | `Poppy\System\Http\Request\ApiV1\CaptchaController::send` 发布 `Poppy\System\Events\CaptchaSendEvent` | API 验证码生成后发布事件；当前仓库没有 SMS Listener 或直接 `SmsContract` 调用，实际消费方待确认，详见 [contracts.md](contracts.md) |
| `poppy/mgr-page` | `Poppy\MgrPage\Http\Request\Backend\CaptchaController::send` 发布 `Poppy\System\Events\CaptchaSendEvent` | 后台验证码生成后发布事件；当前仓库没有 SMS Listener 或直接 `SmsContract` 调用，实际消费方待确认，详见 [contracts.md](contracts.md) |

## 边界说明（不负责的事项）

- 不生成验证码、不校验验证码，也不负责验证码请求的账号存在性判断；这些逻辑属于 `poppy/system`/`poppy/mgr-page`。
- 不实现模块内的事件监听器：`poppy/sms/src/Listeners/` 不存在，短信发送不会由本模块自动响应验证码事件。
- 不提供模块级频率上限、手机号黑名单、幂等键或重试队列；系统验证码流程的节流不等同于短信模块的发送限流。
- 不维护发送审计表、短信状态回执或统一发送日志；本地驱动写系统日志，火山云驱动写请求/响应日志，其他驱动仅返回错误。
- 不保证供应商失败后的自动切换；当前选择器只在发送前选择一个驱动，发送失败后没有第二驱动重试。

## 文档索引

- 业务规则 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)

## 待确认

- `CaptchaSendEvent` 的实际宿主 Listener 尚未在 `poppy/*/src/Listeners/` 或 `poppy/system/src/Jobs/` 中找到，需确认部署项目是否在仓库外注册了 SMS/邮件消费方。
- `Volc\Service\Sms` 的 Composer 包未在当前根项目 `composer.json` 的显式依赖中看到，需确认火山云驱动的部署依赖管理方式。
