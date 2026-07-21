# aliyun-oss（Poppy\AliyunOss）

## 模块职责

`poppy/aliyun-oss` 是 Poppy 框架的 **阿里云 OSS（Object Storage Service）存储扩展模块**，提供：
1. **前端直传** 所需的 STS 临时授权 token 签发（Action\Sts + ApiV1/StsController），让浏览器/移动端在不暴露 AccessSecret 的前提下直传 OSS；
2. **后台上传** 走 `system` 模块 `FileContract` 抽象的阿里云 OSS Provider（OssFileProvider），由系统模块上传 API 反向调用；
3. **后台配置** OSS 接入参数（AccessKey、Endpoint、Bucket、URL 前缀、RAM 角色、水印等）。

模块本身**不引入数据库表**，所有配置通过 `sys_setting('py-aliyun-oss::oss.*')` 写入 `system.sys_config` 表。

## 目录结构

| 目录            | 职责                                             | 文件数 |
|---------------|------------------------------------------------|-----|
| Action        | 业务逻辑层：STS 临时授权                                  | 1   |
| Classes/Provider | 工具类：OssFileProvider（OSS 存储实现）                | 1   |
| Exceptions    | `StsException`                                | 1   |
| Http/Request  | API V1（StsController）/ 后台（UploadController） / Request & Response Body | 5   |
| Http/Routes   | 路由定义（`api_v1.php`、`backend.php`）                | 2   |
| Http/MgrPage  | 后台设置表单：`FormSettingAliyunOss`                  | 1   |
| Hooks/System  | 注册上传类型 hook：`UploadTypeAliyun`                  | 1   |
| resources/config | 默认配置 `aliyun-oss.php`（含空 AccessKey/Endpoint/Bucket 等） | 1   |
| resources/lang | 中文翻译键                                          | 2   |

> 无 Models 子目录，无 Events/Listeners/Jobs/Commands。

## 技术栈

| 技术      | 版本/说明                                                              |
|---------|--------------------------------------------------------------------|
| PHP     | `>=7.4`（来自 `composer.json` `require.php`）                           |
| Laravel | 基于 `poppy/core: 4.3.*`，向下兼容 Laravel 6                                |
| 模块框架    | `poppy/core 4.3.*`（由 `framework` 间接升级）                              |
| OSS SDK | `aliyuncs/oss-sdk-php: 2.*`（`OssClient` / `OssException`）            |
| STS SDK | `alibabacloud/sts-20150401: ^1.1` + `alibabacloud/darabonba: ^1.0`     |
| 依赖模块    | `poppy/mgr-page: 4.3.*`（`FormSettingBase` 后台设置表单基类）                |
| 其它依赖    | `guzzlehttp/guzzle: ^6.3\|^7.3`（仅 dev）                            |

## 路由概览

| 路由文件           | 类型     | 前缀                            | 路由数 | 说明                                          |
|----------------|--------|-------------------------------|-----|---------------------------------------------|
| `api_v1.php`   | API    | `/api_v1/aliyun-oss`          | 1   | 前端/App 调用，获取 STS 临时授权                       |
| `backend.php`  | 管理后台   | `{prefix}/aliyun-oss`（含 `/backend` 前缀） | 1   | 后台保存 OSS 设置表单                              |

> 前缀由 `Http\RouteServiceProvider::mapApiRoutes()` / `mapBackendRoutes()` 设定，中间件分别为 `api-sign` 与 `backend-auth`。详细路由见 `contracts.md`。

## 模型清单

无 Eloquent 模型。所有持久化数据通过 `sys_setting('py-aliyun-oss::oss.*')` 写入 `poppy/system` 的 `sys_config` 表，命名空间 `py-aliyun-oss`、分组 `oss`。

## 依赖的其他模块

| 模块                  | 引用方式                                              | 说明                                                                 |
|---------------------|---------------------------------------------------|--------------------------------------------------------------------|
| `poppy/framework`   | `PoppyServiceProvider`、`Rule`、`Resp`、`LoadConfigurationException` | 基础服务提供者、验证规则、响应助手、配置缺失异常 |
| `poppy/core`        | `ServiceArray`（hook 契约）                            | 实现上传类型 hook 接口 `data(): array`                                       |
| `poppy/system`      | `FileContract`、`DefaultFileProvider`、`JwtApiController`、`BackendController` | FileContract 实现（继承自 DefaultFileProvider）；Jwt/Backend 基类继承 |
| `poppy/mgr-page`    | `FormSettingBase`                                 | 后台设置表单基类，`FormSettingAliyunOss` 继承                                       |

## 被其他模块依赖

| 模块              | 引用方式                                                                  | 使用场景                                                           |
|-----------------|-----------------------------------------------------------------------|------------------------------------------------------------------|
| `poppy/system`  | `sys_hook('poppy.system.upload_type')` → `UploadTypeAliyun::data()` → `OssFileProvider::class` | `system/ServiceProvider` 根据 `py-system::picture.save_type` 决定实例化哪个 FileProvider |
| `poppy/mgr-page`| `sys_hook('poppy.system.upload_type')` → `FormSettingUpload` 列出所有上传 provider（含 `aliyun`） | 后台"上传设置"页中作为可选 Provider 列出                                |
| `poppy/version` | `app(FileContract::class)`（间接消费 OssFileProvider）                  | 版本模块的上传/缩略图逻辑，反向走统一的 FileContract 抽象                       |

> 其他业务模块通过 `app(FileContract::class)` 间接消费本模块 — 任何切换了 `py-system::picture.save_type=aliyun` 的系统都自动走 `OssFileProvider`。

## 边界说明（不负责的事项）

- **不负责**：通用账号/权限（由 `poppy/system` 提供）；上传控件的 UI 与表单（由 `poppy/mgr-page` 提供）
- **不负责**：图片处理（水印、压缩等）—— 仅在 `OssFileProvider::reWatermark()` 中**触发**水印调用，依赖父类 `DefaultFileProvider` 的图像处理能力
- **不负责**：阿里云 RAM 控制台的角色/子账号创建（仅消费用户预先配置好的 `role_arn`）
- **不负责**：CDN 域名绑定（用户自行将 `url_prefix` 配置为 CDN 域名即可生效）

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)
