# 对外契约

## API 路由（`api_v1.php`）

| HTTP方法       | URI                                | 请求类                                                                 | 中间件       | 说明                                       |
|-------------|------------------------------------|---------------------------------------------------------------------|-----------|------------------------------------------|
| POST/GET/…  | `/api_v1/aliyun-oss/sts/temp_oss` | `Poppy\AliyunOss\Http\Request\ApiV1\Web\StsController::tempOss`<br>（参数 `StsTempOssRequest`） | `api-sign` | 签发 STS 临时授权，返回 `{directory, prefix_url, bucket, endpoint, access_key_id, access_key_secret, security_token, expiration}` |

请求参数（`StsTempOssRequest`）：

| 字段        | 类型      | 必填 | 取值             | 默认值 | 说明                            |
|-----------|---------|----|----------------|-----|-------------------------------|
| `is_temp` | string  | 否  | `Y` / `N`      | `N` | 是否使用 `temp` 子目录前缀                |

> 控制器路由名未显式 `name(...)` 设置，沿用默认（基于 URI 生成）。

## 管理后台路由（`backend.php`）

| HTTP方法       | URI                                  | 请求类/控制器                                       | 中间件                | 说明                                                  |
|-------------|--------------------------------------|-----------------------------------------------|--------------------|-----------------------------------------------------|
| POST/GET/…  | `/{prefix}/aliyun-oss/upload/store`  | `Poppy\AliyunOss\Http\Request\Backend\UploadController::store` | `backend-auth`     | 渲染 OSS 设置表单（`FormSettingAliyunOss`），权限 `backend:py-system.global.manage`。路由名：`py-aliyun-oss:backend.upload.store` |

> `{prefix}` 通常为 `backend`（取决于宿主的 `poppy.system.prefix`）。

## Web 路由（`web.php`）

无。

## 其他路由文件

无。

## Hooks（对外注册）

| Hook 名称                       | 实现类                                       | 携带数据                                                                                                                                                                                | 注册位置                                                  |
|-------------------------------|-------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------|
| `poppy.system.upload_type`    | `Poppy\AliyunOss\Hooks\System\UploadTypeAliyun` | `{key: 'aliyun', title: '阿里云存储(Oss)', provider: OssFileProvider::class, setting: FormSettingAliyunOss::class, path: 'form/py-aliyun-oss:api-backend.home.store', route: 'py-aliyun-oss:backend.upload.store'}` | `configurations/hooks.yaml` 注册到 `poppy.system.upload_type` |

## 发布的事件（本模块对外发布）

无。本模块不触发任何事件。

## 监听的事件（本模块消费）

无。

## 队列任务

无。

## Artisan 命令

无。

## 异常类

| 异常类                       | 父类                                  | 触发场景                              |
|---------------------------|-------------------------------------|-----------------------------------|
| `StsException`            | `Poppy\Framework\Exceptions\BaseException` | 预留给 STS 流程（当前未在代码中显式抛出）       |
| `LoadConfigurationException`（来自 `poppy/framework`） | — | OSS 配置缺失：`return_url_error` / `watermark_not_match` |

## FileProvider 契约实现

| 类                                              | 父类                                                                 | 实现接口                                                | 注册位置                                                                                            |
|-----------------------------------------------|--------------------------------------------------------------------|-----------------------------------------------------|-------------------------------------------------------------------------------------------------|
| `Poppy\AliyunOss\Classes\Provider\OssFileProvider` | `Poppy\System\Classes\File\DefaultFileProvider`（间接实现 `FileContract`） | `Poppy\System\Classes\Contracts\FileContract`（链式继承） | `poppy/system` 的 `ServiceProvider` 中通过 `sys_hook('poppy.system.upload_type')` 按 `py-system::picture.save_type=aliyun` 解析得到 |

### `OssFileProvider` 对外暴露的方法（继承自 `FileContract`）

| 方法                                                                          | 入参                              | 返回                | 行为                                          |
|----------------------------------------------------------------------------|---------------------------------|-------------------|---------------------------------------------|
| `saveFile(UploadedFile $file): bool`                                       | 上传文件                            | bool              | 走父类 saveFile（写本地 storage） → `saveAli()` 上传 OSS |
| `saveInput($content): bool`                                                | 内容流/字符串                         | bool              | 同上                                          |
| `copyTo(string $dist): bool`                                               | 目标 OSS 路径                       | bool              | 复制 OSS 内对象                                   |
| `delete(): bool`                                                           | —                               | bool              | 删除 OSS 对象                                    |
| `setReturnUrl/setExtension/setFolder/setType/setResizeDistrict/setQuality/setMimeType/getUrl/getDestination/setDestination/setIsForceSetDestination/enableWatermark` | — | — | 继承自 `DefaultFileProvider`                  |

> `OssFileProvider::__construct()` 不接受 `$config` 参数（与 `DefaultFileProvider::__construct($config = [])` 不一致；调用方 `poppy/system` 的 `ServiceProvider` 仍传入 `$config`，构造时会被忽略）。见 flows.md「待确认」段落。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方               | 目标模块         | 目标类                                                                            | 调用方法                                          | 场景                       |
|----------------------|--------------|--------------------------------------------------------------------------------|-----------------------------------------------|--------------------------|
| `OssFileProvider`    | `poppy/system` | `DefaultFileProvider`（父类）                                                    | `parent::__construct()`、`parent::saveFile()`、`parent::saveInput()` | 复用系统模块的上传基类                |
| `OssFileProvider`    | `poppy/framework` | `LoadConfigurationException`、`Rule`                                          | 构造时校验 `returnUrl`、水印域匹配                       | 配置缺失保护                   |
| `Sts`                | `poppy/framework` | `AppTrait`、`ApplicationException`                                            | 异常包装、辅助函数 `sys_setting()`                      | 异常统一风格                   |
| `UploadController`   | `poppy/mgr-page` | `FormSettingAliyunOss`（继承自 `FormSettingBase`）                              | `render()`                                    | 后台设置表单                   |
| `StsController`      | `poppy/system` | `JwtApiController`（父类）                                                       | 继承 JWT 鉴权                                  | API V1 鉴权                |

## 被其他模块调用（本模块被引用）

| 调用方模块              | 调用方类 / 触发方式                                                                            | 本模块目标类                                                            | 调用方法                                                                                              | 场景                                                                              |
|--------------------|---------------------------------------------------------------------------------------|------------------------------------------------------------------|---------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------|
| `poppy/system`     | `ServiceProvider` 中 `$this->app->bind('poppy.system.file', ...)`                       | `OssFileProvider`（通过 `UploadTypeAliyun` hook 间接解析）                     | `new $uploaderClass($config)`                                                                      | 当 `py-system::picture.save_type=aliyun` 时将 `FileContract` 别名解析到 `OssFileProvider` |
| `poppy/system`     | `Http\Request\ApiV1\UploadController::upload()`                                       | `app(FileContract::class)` → `OssFileProvider`                   | `saveFile()` / `saveInput()` / `getUrl()`                                                          | 后端统一上传 API                                                                     |
| `poppy/system`     | `Jobs\DeleteUploadFileJob`                                                            | `app(FileContract::class)` → `OssFileProvider`                   | `delete()`                                                                                         | 异步删除 OSS 上的文件                                                                  |
| `poppy/system`     | `Http\Request\ApiV1\UploadController` 内部 `$Uploader = app(FileContract::class);`     | `OssFileProvider`                                                | `setFolder`、`setType`、`setExtension`、`saveFile`、`getUrl`                                              | 通用上传/资源管理                                                                      |
| `poppy/mgr-page`   | `FormSettingUpload::form()`、`Http\Routes\backend.php` `UploadController::store`       | `sys_hook('poppy.system.upload_type')` → `UploadTypeAliyun::data()` | 列出 `aliyun` 作为可选 provider                                                                       | 后台上传设置页选择存储类型                                                                  |
| `poppy/version`    | `Action\Version::__construct` 中 `$Upload = app(FileContract::class);`                  | `OssFileProvider`                                                | `setFolder`、`setType`、`setDestination`、`setReturnUrl`、`saveInput`、`getUrl`                                  | 版本模块上传版本安装包/截图                                                                |

> `poppy/version` 和 `poppy/system` 通过 `FileContract` 抽象间接消费本模块，**未直接 `use` `OssFileProvider`**；这种解耦使本模块可以"无缝替换"为其他 FileProvider 实现。
