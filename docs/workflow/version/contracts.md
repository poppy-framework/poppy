# 对外契约

## API 路由（`api_v1.php`，前缀 `api_v1/version`，中间件 `api-sign`）

| HTTP方法                | URI                          | 请求类 / 控制器                                                                                       | 中间件        | 说明                                                       |
|----------------------|------------------------------|--------------------------------------------------------------------------------------------------|------------|----------------------------------------------------------|
| `any` (实际：GET / POST) | `/api_v1/version/app/version` | `Poppy\Version\Http\Request\ApiV1\Web\VersionController::version`<br>请求体 `VersionVersionRequest`<br>响应体 `VersionVersionResponseBody` | `api-sign` | App 版本自检：通过请求头 `x-os` 识别平台（默认 `android`），返回最新版本信息与是否强制升级 |

> 路由命名空间：`Poppy\Version\Http\Request\ApiV1\Web`。
> 该路由未显式设置 `->name(...)`，因此**没有命名路由**（待确认：是否符合预期）。

### 请求体（`VersionVersionRequest`）

| 字段        | 类型      | 必填 | 规则                  | 默认值       | 说明              |
|-----------|---------|----|---------------------|-----------|-----------------|
| `version` | string  | 否  | `nullable`、`string` | `1.0.0`   | 客户端当前版本号        |

### 请求头

| Header  | 必填 | 取值                  | 说明                        |
|---------|----|---------------------|---------------------------|
| `x-os`  | 否  | `android` \| `ios`  | 客户端平台标识；缺省视为 `android`     |
| `sign`  | 是  | 字符串                 | 由 `api-sign` 中间件校验        |

### 响应体（`VersionVersionResponseBody`）

成功（HTTP 200，`Resp::success`）：

```json
{
  "code": 0,
  "msg":  "获取版本成功",
  "data": {
    "download_url": "https://cdn.example.com/static/app/latest.apk",
    "description": "修复若干 bug",
    "version":     "4.6.0",
    "is_upgrade":  "Y"
  }
}
```

错误响应（`Resp::error`，HTTP 200）：

| 触发条件                              | `msg`                  |
|-----------------------------------|------------------------|
| `x-os` 不是 `android` / `ios`         | `不正确的平台信息`             |
| `latestVersion` 为空（缓存与 DB 均无该平台记录） | `当前已是最新版本!`           |
| `current >= latestVersion['title']`  | `您当前的版本是最新版本`         |

> 注：`is_upgrade` 以 `"Y" | "N"` 字符串返回（不是布尔），OpenAPI schema 已声明 enum。

## 管理后台路由（`backend.php`，前缀 `{prefix}/version`，中间件 `backend-auth`）

| HTTP方法                                       | URI                                       | 控制器 / 组件                                                                                                                  | 路由名称                                  | 说明                          |
|---------------------------------------------|-------------------------------------------|-------------------------------------------------------------------------------------------------------------------------|---------------------------------------|-----------------------------|
| `any`                                        | `{prefix}/version`                        | `Backend\VersionController::index` → `Grid(new SysAppVersion())` + `ListSysAppVersion`                                  | `py-version:backend.version.index`    | 版本列表（带平台 scope 过滤）           |
| `any`                                        | `{prefix}/version/establish/{id?}`       | `Backend\VersionController::establish` → `FormVersionEstablish`                                                            | `py-version:backend.version.establish` | 新增 / 编辑版本（`id` 可选）            |
| `any`                                        | `{prefix}/version/setting`                | `Backend\VersionController::setting` → `FormSettingVersion`                                                                | `py-version:backend.version.setting`   | 版本设置（路径、最新包名、上传开关、AppStore） |
| `any`                                        | `{prefix}/version/delete/{id}`            | `Backend\VersionController::delete` → `Action\Version::delete()`                                                            | `py-version:backend.version.delete`    | 删除版本（同时清理缓存、调度删除旧文件任务）     |
| `any`                                        | `{prefix}/version/clear_cache`            | `Backend\VersionController::clearCache`                                                                                    | `py-version:backend.version.clear_cache` | 清除 `py-version` 命名空间下所有缓存    |

> 后台路由命名空间：`Poppy\Version\Http\Request\Backend`。
> 全局权限点：`backend:py-version.main.manage`（在 `VersionController::__construct` 中设置）。

### 校验规则（`Action\Version::establish`）

| 字段              | 规则                                                                                                | 失败提示                |
|-----------------|---------------------------------------------------------------------------------------------------|---------------------|
| `title`         | `required`、`string`、`unique(title, platform)`（编辑时排除自身 ID）<br>正则 `/\d\.\d+\..+/`<br>`version_compare(title, '0.0.1', '>=')` | `版本号`、格式、命名不规范       |
| `download_url`  | `string`、`url`                                                                                   | `下载地址`              |
| `description`   | `required`、`string`                                                                              | `版本更新描述`            |
| 跨字段             | `platform=android` 时 `download_url` 必填                                                                              | `请输入下载地址`           |

属性名（中文）：
- `title` → `版本号`
- `download_url` → `下载地址`
- `description` → `版本更新描述`

## Web 路由

无（`web.php` 不存在；本模块的所有"页面"由 `Poppy\MgrPage` 组件 + 后台模板渲染，不直接暴露 Web 路由）。

## 其他路由文件

无。

## 发布的事件（本模块对外发布）

| 事件类 | 携带数据 | 触发时机 | 监听方 |
|------|------|------|------|
| 无    | -    | -    | -    |

> 本模块不发布任何事件（`src/` 下没有 `Events/` 目录）。

## 监听的事件（本模块消费）

| 监听器类 | 监听的事件 | 业务动作 | 产生的事件 / 任务 |
|------|------|------|------------|
| 无    | -    | -    | -          |

## 队列任务（本模块 dispatch）

| Job 类                       | 队列名        | 延迟 | 触发来源                | 业务动作                |
|-----------------------------|------------|----|---------------------|---------------------|
| `Poppy\System\Jobs\DeleteUploadFileJob` | 系统队列（默认）     | -  | `Action\Version::delete` | 异步删除旧 `download_url` 文件 |

> Job 由 `Poppy\System` 提供，本模块仅在删除版本时 dispatch 一次。

## Artisan 命令

无（`src/Commands` 不存在）。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方                                                          | 目标模块          | 目标类 / 文件                                                     | 调用方法 / 用途                                      | 场景                  |
|-----------------------------------------------------------------|---------------|--------------------------------------------------------------|------------------------------------------------|---------------------|
| `Models/SysAppVersion::path`、`platformUrl`                       | `poppy/system` | `Poppy\System\Classes\File\FileManager`                       | `FileManager::prefix()` 拼接下载 URL             | 拼装 Android 包下载地址     |
| `Action/Version::copyTo`                                          | `poppy/system` | `Poppy\System\Classes\Contracts\FileContract`                 | `setIsForceSetDestination` / `setDestination` / `copyTo` | "覆盖最新文件"时把上传包复制到 latest 路径 |
| `Action/Version::delete`                                          | `poppy/system` | `Poppy\System\Jobs\DeleteUploadFileJob`                       | `dispatch(new DeleteUploadFileJob($url))`        | 删除版本时清理旧文件           |
| `Http/Request/ApiV1/Web/VersionController`                        | `poppy/system` | `Poppy\System\Http\Request\ApiV1\WebApiController`            | 继承基类（统一响应格式）                                    | API 通用头/签名           |
| `Http/Request/ApiV1/Web/Version/VersionVersionResponseBody`      | `poppy/system` | `Poppy\System\Http\OpenApi\BaseResponseBody`                 | 继承基类（统一响应体注解）                                   | OpenAPI 文档生成         |
| `Http/Request/Backend/VersionController`                         | `poppy/mgr-page` | `Poppy\MgrPage\Http\Request\Backend\BackendController`       | 继承基类（统一后台权限、入口）                                 | 后台权限 / 入口            |
| `Http/MgrPage/FormVersionEstablish` / `ListSysAppVersion`        | `poppy/mgr-page` | `FormWidget` / `ListBase`                                   | 继承组件基类                                            | 后台 Form / List 渲染    |

## 被其他模块调用（本模块被引用）

| 调用方模块 | 调用方类 | 本模块目标类 | 调用方法 | 场景 |
|------|------|----------|------|----|
| 无    | -    | -        | -    | -   |

> 已对 `poppy/**` 全量扫描，未发现任何外部模块直接 `use Poppy\Version\...`。

## OpenAPI 注解摘要

- **Schema 名**：`PoppyVersionVersionVersionRequest`、`PoppyVersionVersionVersionResponseBody`（命名约定：`Poppy + 模块 + 类名`）。
- **Tag**：`Version`（描述：`App 版本检测 等接口`）。
- **路径**：`/api_v1/version/app/version`，method `GET`（注解），但实际路由用 `Route::any`（待确认：是否要收紧为 GET/POST）。
- **响应 schema**：`#/components/schemas/PoppyVersionVersionVersionResponseBody`。

## 待确认

- `api_v1/version/app/version` 路由未声明 `name(...)`，也未限定 HTTP 方法（用了 `Route::any`）——是否与前端约定一致？（发现位置：`Http/Routes/api_v1.php`）
- OpenAPI 注解把 `version` 接口标注为 `GET`，但路由用 `any`——是否需要统一为 GET？（发现位置：`Http/Request/ApiV1/Web/VersionController` 与 `Http/Routes/api_v1.php`）
- `is_upgrade` 返回 `"Y" | "N"` 字符串而非布尔——是否前端需要布尔？若是，是否需要重构？（发现位置：`ApiV1/Web/VersionController::version`）