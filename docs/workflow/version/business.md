# 业务逻辑

## App 版本自更新

### 业务规则

- **平台识别（OS）**：客户端通过 HTTP 请求头 `x-os`（经 `x_header('os')` 读取）告知服务端当前平台；缺省视为 `android`。仅识别 `android` / `ios` 两个值，其他值（如 `harmonyos`）会被拒绝并返回 `不正确的平台信息`。
- **版本号格式**：
  - 新建版本时强制要求格式 `数字.数字+.任意`，通过正则 `/\d\.\d+\..+/` 校验，失败返回 `版本号格式不正确`。
  - 同时要求 `version_compare($title, '0.0.1', '>=')`，防止创建初始无效版本（失败返回 `版本号命名不规范`）。
  - **同一平台下版本号必须唯一**（`Rule::unique(...)->where('platform', ...)`），不同平台可同名；编辑时排除自身 ID。
- **平台决定下载地址形态**：
  - `android`：**必须**提供 `download_url`（表单字段必填，路径对应 `.apk`）。
  - `ios`：`download_url` 可为空，此时 `platformUrl()` 会回退到后台设置 `py-version::setting.ios_store_url`（即 AppStore 链接，符合 iOS 通常从商店更新的事实）。
- **强制升级语义（`is_upgrade`）**：
  - `is_upgrade=1` 表示"凡版本号低于该记录的所有客户端都必须升级"。
  - 客户端自检接口通过 `SysAppVersion::isUpgrade($platform, $currentVersion)` 判断：遍历缓存中该平台全部版本（按 `version_compare` 升序），若存在某条 `is_upgrade=1` 且 `title > current`，即返回 `Y`。
  - 即使当前已是最新版本（`current >= latest`），若中间存在强制升级历史版本，仍可能命中 `is_upgrade=Y`（`isUpgrade` 与 `latestVersion` 走两套独立的缓存键）。

### 路由/分发规则

| 条件                         | 处理路径                                                 |
|----------------------------|------------------------------------------------------|
| API 请求头 `x-os` 缺失或非 `android`/`ios` | 返回错误 `不正确的平台信息`（不走缓存）                          |
| `$current >= $latestVersion['title']` | 返回 `您当前的版本是最新版本`（不返回数据体）                     |
| `$latestVersion` 缓存 / 数据库均为空         | 返回 `当前已是最新版本!`（不返回数据体）                         |
| 默认分支                        | 返回 `download_url`、`description`、`version`、`is_upgrade` |

### 缓存策略

- **缓存键空间**：`sys_tag('py-version')`（Poppy 的标签化缓存，hash 结构）。
- **`ckMaxVersion()`** = `max-version`：每平台缓存"最新版本"数组（含 `title`/`description`），避免每次 API 调用都查 DB 并 `usort`。
- **`ckVersions()`** = `versions`：每平台缓存"全版本数组"（用于 `isUpgrade` 遍历）。
- **失效时机**：任何 `establish`（新建 / 更新）或 `delete` 后，调用 `Action\Version::clearCache($platform)` 删除该平台两个键。
- **全局失效**：后台 `version/clear_cache` 路由调用 `sys_tag('py-version')->del([...])` 清掉所有平台的缓存键。
- **一致性注意点**：`isUpgrade` 是"是否需要强制升级"，与 `latestVersion` 是"最新版本"是两件事——存在 `4.4.4`、`4.5.0(is_upgrade=1)`、`4.6.0` 三条记录时，`latestVersion` 返回 `4.6.0`，但 `isUpgrade('android','4.5.1')` 会返回 `false`（因为 `4.5.0 > 4.5.1` 不成立），`isUpgrade('android','4.4.5')` 会返回 `true`。

### 状态机

无显式状态机字段；版本记录无启用 / 禁用 / 灰度状态，建表即生效。

### 关键算法

- **最新版本选取**：`SysAppVersion::versions()` 拉取该平台全量记录，`usort(...version_compare)` 升序，`array_pop` 取末尾。
- **强制升级判定**：见上"强制升级语义"。
- **最新包文件路径（`SysAppVersion::path()`）**：
  - Android → `{setting.path}/{setting.latest_name}.apk`（默认 `/static/app/latest.apk`）
  - iOS    → `{setting.path}/{setting.latest_name}.ipa`（但通常 iOS 走 AppStore 链接，不使用此路径）
- **最新包 URL（`SysAppVersion::platformUrl()`）**：
  - Android → `FileManager::prefix() . path()`（拼接 CDN / 静态域名）
  - iOS    → 直接返回 `py-version::setting.ios_store_url`
- **"覆盖最新文件"选项**：`FormVersionEstablish` 提交时若勾选 `is_cover`，`Action\Version::allowCopy()` 开启；`establish` 成功后调用 `FileContract::copyTo()` 把当前 `download_url` 对应的源文件复制到"最新包路径"。背景：后台可以一次性上传历史版本包，发布时勾选"覆盖"即可让客户端拿到的是固定 URL 的 latest 文件，无需每次发版都更新链接。

## 版本设置（`FormSettingVersion`）

### 业务规则

- **`path`**：apk/ipa 文件存放目录（不含文件名），默认 `/static/app`，仅允许小写字母、数字、下划线、目录分隔符（`/^[a-z_0-9\/]{3,}$/`）。
- **`latest_name`**：最新版文件主名，默认 `latest`，仅允许小写字母、数字、下划线（`/^[a-z_0-9]{3,}$/`）。
- **`is_upload`**：开关，决定后台表单是否显示"上传文件"字段（而非手动粘贴 URL）。**开启后受服务端 `upload_max_filesize` 限制**（在表单帮助中提示）。
- **`ios_store_url`**：iOS 平台在没有专属 `download_url` 时返回的 AppStore 链接。

### 跨模块关联（设置由谁读取）

- `Poppy\System\Models\SysConfig`：使用 `FormSettingBase` 的 `$group = 'py-version::setting'` 存储到 `sys_config` 表。
- 读取通过 `sys_setting('py-version::setting.path')` / `sys_setting('py-version::setting.latest_name')` / `sys_setting('py-version::setting.is_upload')` / `sys_setting('py-version::setting.ios_store_url')`（见 `Models/SysAppVersion` 与 `Http/MgrPage/FormVersionEstablish`）。

## 中间件规则

| 中间件                | 应用范围                  | 规则            |
|--------------------|-----------------------|---------------|
| `backend-auth`     | `backend.php` 全部 5 条  | 后台鉴权         |
| `api-sign`         | `api_v1.php` 全部 1 条  | API 请求签名校验   |

## 权限与菜单

- **后台菜单**（`configurations/menus.yaml`）：注入到 `poppy.mgr-page/backend||setting` 父组下，标题 `App版本`，路由 `py-version:backend.version.index`（默认 scope `android`）。
- **权限点**（`configurations/permissions.yaml`）：`backend:py-version.main.manage`，挂在 `VersionController::__construct` 的 `self::$permission['global']` 上。
- **i18n / SEO 键**（`resources/lang/zh/seo.php`）：`backend_version_index`、`backend_version_establish`、`backend_version_delete`、`backend_version_setting`。
- **util 名称**（`resources/lang/zh/util.php`）：`sys_app_version` → `App 版本`。

## 待确认

- 是否计划支持"灰度发布 / 部分用户强制升级"？当前 `is_upgrade` 是平台级一刀切，缺少用户群 / 比例维度。（发现位置：`Models/SysAppVersion` 字段 `is_upgrade`）
- `ios_store_url` 的填写时机：是每次发版都要求运营填写，还是仅在后台"无 download_url 的 iOS 版本"时回退？当前代码未做强制校验。（发现位置：`Http/MgrPage/FormSettingVersion`）
- 是否需要支持"强制升级最小版本号"（即"低于 X 必须升级，高于 X 可选"）？当前模型只能表达"某个具体版本以上必须升级"。（发现位置：`Action/Version::isUpgrade`）