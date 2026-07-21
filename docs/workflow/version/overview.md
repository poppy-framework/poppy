# Version（`Poppy\Version\`）

## 模块职责

App 版本管理模块：维护 iOS / Android App 的版本号、更新描述、下载地址与"是否强制升级"标记，向移动端客户端提供版本自检 API，同时为后台管理提供版本 CRUD、设置、清缓存界面。本模块聚焦于"应用市场外分发"或"绕过商店审核"场景下的版本自更新能力，**不是 API 版本号（v1/v2）** 的管理。

## 目录结构

| 目录              | 职责                                              | 文件数 |
|-----------------|-------------------------------------------------|-----|
| Action           | 业务逻辑层（版本建立 / 删除 / 缓存清理）                      | 1   |
| Models           | Eloquent 模型（SysAppVersion）                       | 1   |
| Classes          | 常量 / 缓存键定义（PyVersionDef）                         | 1   |
| Http/MgrPage     | 后台 Form / List 组件（FormWidget + ListBase）          | 3   |
| Http/Routes      | 路由定义（api_v1.php, backend.php）                    | 2   |
| Http/Request     | 控制器 + 请求类 + 响应体（ApiV1/Backend 双端）                | 4   |
| resources/migrations | 建表迁移（sys_app_version）                          | 1   |
| resources/lang   | i18n 文案 / SEO / util 名称                          | 2   |
| resources/views  | 后台模板                                            | -   |
| configurations   | menus.yaml / permissions.yaml                     | 2   |
| tests            | PHPUnit 集成测试（覆盖 Android + iOS 双平台）                | 1   |

## 技术栈

| 技术        | 版本/说明                                                                |
|-----------|----------------------------------------------------------------------|
| PHP       | >= 7.4.0（来自 `composer.json`）                                            |
| Laravel   | 6.x（项目整体框架）                                                            |
| 模块框架      | `poppy/framework`（`Poppy\Framework`），`Poppy\System`，`Poppy\MgrPage` |
| ORM       | Eloquent                                                            |
| 缓存        | 通过 `sys_tag('py-version')`（hash 结构）按平台缓存最新版本与全版本列表                |
| 队列        | `dispatch(new DeleteUploadFileJob(...))` 删除旧安装包文件                    |
| 认证       | 后台 `backend-auth` 中间件；API 走 `api-sign` 签名中间件                      |
| 文件存储     | `Poppy\System\Classes\Contracts\FileContract`（FileManager 上传 / 复制）    |

## 路由概览

| 路由文件        | 类型    | 前缀                  | 路由数 | 说明                                                  |
|-------------|-------|---------------------|-----|-----------------------------------------------------|
| api_v1.php  | API   | `api_v1/version`    | 1   | 移动端 App 调用的版本自检接口（`api-sign` 中间件）                 |
| backend.php | 管理后台  | `{prefix}/version`  | 5   | 版本列表 / 编辑 / 删除 / 设置 / 清缓存（`backend-auth` 中间件）      |
| web.php     | Web页面 | -                   | -   | 无（本模块不直接提供 Web 页面，仅经 MgrPage 提供后台表单）               |

> 命名空间：`Poppy\Version\Http\Request\{ApiV1\Web, Backend}`，根命名空间 `Poppy\Version\Http`。

## 模型清单

| 模型               | 数据表               | 关键关联 | 说明                                                |
|------------------|-------------------|------|---------------------------------------------------|
| `SysAppVersion`  | `sys_app_version` | -    | App 版本主记录，按 `platform`（android/ios）区分；含缓存辅助方法 |

模型字段（迁移 `2021_06_09_233858_create_sys_app_version_table.php`）：

| 字段            | 类型            | 含义                |
|---------------|---------------|-------------------|
| `id`          | increments    | 主键                |
| `title`       | string(50)    | 版本号（如 `4.5.0`）    |
| `description` | text          | 更新描述              |
| `download_url`| string(255)   | 下载地址（apk/ipa 链接）   |
| `is_upgrade`  | tinyInteger   | 是否强制升级当前版本（0/1）    |
| `platform`    | string(50)    | 操作平台 `android`/`ios` |
| `created_at`  | timestamp     | 创建时间              |
| `updated_at`  | timestamp     | 更新时间              |

## 关键类与文件

| 类 / 文件                                       | 角色                                                       |
|---------------------------------------------|----------------------------------------------------------|
| `Poppy\Version\Action\Version`              | 业务核心：版本建立（含校验、复制、缓存清理）、删除                       |
| `Poppy\Version\Models\SysAppVersion`        | 数据模型 + 静态查询方法（`latestVersion`/`isUpgrade`/`path`/`platformUrl`） |
| `Poppy\Version\Classes\PyVersionDef`        | 缓存键常量（`ckMaxVersion()`、`ckVersions()`）                 |
| `Poppy\Version\Http\MgrPage\FormVersionEstablish` | 后台"新增/编辑版本"表单组件                                  |
| `Poppy\Version\Http\MgrPage\FormSettingVersion`  | 后台"版本设置"表单组件（路径、最新包名、是否启用上传、AppStore 链接）        |
| `Poppy\Version\Http\MgrPage\ListSysAppVersion`   | 后台版本列表组件（含平台过滤、快速按钮）                              |
| `Poppy\Version\Http\Request\ApiV1\Web\VersionController`     | API 控制器（`version`）                          |
| `Poppy\Version\Http\Request\ApiV1\Web\Version\VersionVersionRequest` | API 请求体（含 OpenAPI 注解）                   |
| `Poppy\Version\Http\Request\ApiV1\Web\Version\VersionVersionResponseBody` | API 响应体（含 OpenAPI 注解）            |
| `Poppy\Version\Http\Request\Backend\VersionController`        | 后台控制器（index/establish/setting/delete/clearCache） |
| `Poppy\Version\Http\RouteServiceProvider`  | 注册路由：API 组前缀 `api_v1/version`，后台组前缀 `{prefix}/version` |

## 依赖的其他模块

| 模块                | 引用方式                                                                  | 说明                                       |
|-------------------|---------------------------------------------------------------------|------------------------------------------|
| `poppy/system`    | `use Poppy\System\Models\SysConfig;`<br>`use Poppy\System\Classes\Contracts\FileContract;`<br>`use Poppy\System\Jobs\DeleteUploadFileJob;` | 读取设置、上传 / 复制文件、删除旧安装包                  |
| `poppy/mgr-page`  | `use Poppy\MgrPage\Classes\Widgets\FormWidget;`<br>`use Poppy\MgrPage\Classes\Grid\ListBase;` | 后台 Form / List 组件基类                     |
| `poppy/framework` | `use Poppy\Framework\Application\RouteServiceProvider;`<br>`use Poppy\Framework\Classes\Resp;` 等 | 路由注册、响应封装、Trait、Rule、Exception |

> 模块仅依赖 `poppy/framework`、`poppy/system`、`poppy/mgr-page` 三个核心包，未见业务侧跨模块调用。

## 被其他模块依赖

无（已对 `poppy/**` 全量 grep，未发现其他模块直接引用 `Poppy\Version\` 命名空间下的类）。

## 边界说明（不负责的事项）

- **不负责 API 版本（v1 / v2 / v3）路由前缀管理**：本模块名称虽为 "Version"，但仅服务于移动端 App 自更新；`api_v1` 仅是路由组前缀。
- **不负责 iOS App Store 上架审核与签名**：iOS 平台若 `download_url` 为空则直接返回后台设置的 `py-version::setting.ios_store_url`（指向 AppStore）。
- **不负责客户端下载与安装**：API 只返回下载链接与升级标记，下载行为由 App 端处理。
- **不负责用户升级提醒通知**：本模块无事件、无监听器；清理缓存需后台手动触发。
- **不负责后端服务自身的版本号**：本模块管理的是"客户端 App 版本"，与后端 API 版本解耦。

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)