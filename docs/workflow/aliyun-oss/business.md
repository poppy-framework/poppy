# 业务逻辑

## STS 临时授权（前端直传 OSS）

### 业务规则

- **AccessSecret 不下发给前端**：阿里云主账号或 RAM 子账号的 `access_secret` 仅服务端持有；前端只能拿到 STS AssumeRole 签发的临时 AccessKey/SecurityToken，到期即失效。
- **TTL = 3600 秒**：`Action\Sts::tempOss()` 固定调用 `AssumeRoleRequest::durationSeconds = 3600`（1 小时）。临时凭证过期后前端需重新调用 `/api_v1/aliyun-oss/sts/temp_oss` 换取新凭证；如改为更长 TTL，权限暴露窗口相应变大，建议保持 ≤1 小时。
- **策略限定可写目录**：每次 AssumeRole 都注入一条 `Effect=Allow, Action=oss:PutObject, Resource=acs:oss:*:*:{bucket}/{dir}*` 的内联 policy。前端拿到 token 后**只能向指定前缀上传**，无法列桶、删对象、覆盖他人文件（除非路径冲突）。
- **STS endpoint 固定为杭州**：`createClient()` 中 `config->endpoint = 'sts.cn-hangzhou.aliyuncs.com'`。Aliyun STS 接口全国统一入口，按 bucket 所在 region 与该入口无关。
- **目录生成策略 = 日期分桶**：默认 `$dir = "uploads/{Ym}/{d}/"`（如 `uploads/202607/21/`）。控制单目录文件数量，避免 OSS 单目录文件过多导致 LIST 性能下降；也方便后续按天清理/迁移。**前端不可跨日期上传**：token 拿到时已经绑死目录。

### 路由/分发规则

| `is_temp` 取值 | 处理路径                                |
|-------------|-------------------------------------|
| `N`（默认）     | `dir = uploads/{date}/{day}/`        |
| `Y`         | 控制器调 `setSubDirectory('temp')`，`dir = temp/{date}/{day}/` |

> 注：API 注解中提到的 `His{rand(8)}` 格式在当前代码中**未实际实现**（仅注释中提及），实际仍是 `temp/{Ym}/{d}/`。

### 状态机

```
CLIENT → /api_v1/aliyun-oss/sts/temp_oss (is_temp=Y/N)
        ↓
SERVER → AssumeRole(roleArn, sessionName='app', duration=3600, policy=dir-scoped)
        ↓
        返回 {directory, prefix_url, bucket, endpoint, access_key_id, access_key_secret, security_token, expiration}
        ↓
CLIENT 用 STS token 直传 OSS（PUT 到 bucket/{directory}/<filename>）
        ↓
        1 小时内到期 → 重新调接口换 token
```

状态说明：

- token 过期后**不会自动续期**；前端需在 401/access-denied 时重新换取。
- 一旦 AssumeRole 失败（RAM 角色配置错误、access_secret 失效），立即抛 `ApplicationException` 返回 500，不会下发任何 token。

### 关键算法/计算

- **目录命名**：`Carbon::now()->format('Ym')` + `format('d')`，按月日两级切分；不带哈希或随机串，**文件名由前端/调用方决定**。
- **policy 模板**：JSON 内联，禁止覆盖为通配符 `*`，否则临时凭证将获得 bucket 全量写权限（含删除/覆盖）。
- **凭证格式归一化**：返回前对 STS 响应 key 做 `Str::snake()`，如 `AccessKeyId → access_key_id`、`SecurityToken → security_token`，便于前端字段对齐。

---

## 后台服务端上传（OssFileProvider）

### 业务规则

- **OSS 单边像素上限 30000**：`$resizeLongDistrict = 30000`。`DefaultFileProvider` 父类的 resize 流程会受此限制，避免超出 OSS 处理能力抛错。
- **上传后默认删除本地临时文件**：`$deleteLocal = true`。`saveAli()` 调 `putObject` 成功后立刻 `storage()->delete($destination)`，防止 Laravel `storage/app/` 临时目录膨胀。
- **URL 前缀必填**：`returnUrl` 必须有值，否则构造时直接抛 `LoadConfigurationException('return_url_error')`——OSS 上传完了前端却拿不到可访问的 URL，等于白传。
- **水印图必须使用同一域名前缀**：`reWatermark()` 检查水印 URL 是否包含 `returnUrl`，否则抛 `watermark_not_match`。因为水印用 `x-oss-process=image/watermark,...` 进行远程合成时，水印图必须能从 OSS 公开访问（不能跨域防盗链）。
- **多 Bucket 不支持**：`OssFileProvider` 单实例只持有一个 `$bucket`，与系统模块设置 `py-aliyun-oss::oss.bucket` 强绑定。如需多 bucket，必须新增上传类型 hook。

### 状态机

```
调用方 saveFile($uploadedFile) / saveInput($content)
   ↓
父类 DefaultFileProvider::saveFile → 写入 Laravel 本地 storage（$this->destination）
   ↓
saveAli($deleteLocal=true)
   ├─ $client->putObject(bucket, destination, storage->get(destination))
   ├─ reWatermark()（若启用）
   └─ storage->delete(destination)
   ↓
返回 true / setError($msg)
```

### 关键算法/计算

- **水印图远程合成**：
  - 取水印图路径 `$wmPath = $watermark - returnUrl`
  - 缩放水印图：`$wmDef = "$wmPath?x-oss-process=image/resize,P_80"`（缩到原图 80%）
  - Base64 URL-safe 编码后拼接到 `image/watermark,image_{base64},g_center`
  - 服务端 `file_get_contents()` 拉取合成后内容并覆盖 `putObject`
- **copyTo / delete** 都先调 `doesObjectExist` 再执行，避免对不存在的 key 抛异常。

---

## 后台设置（OSS 接入配置）

### 业务规则

- **配置分组**：`FormSettingAliyunOss::$group = 'py-aliyun-oss::oss'`，所有字段落到 `sys_config` 表的 `py-aliyun-oss` 命名空间、`oss` 分组。
- **配置键清单**（必填/可选）：
  - `access_key` / `access_secret`：阿里云主账号或 RAM 子账号的 AccessKey
  - `endpoint`：OSS 地域节点（如 `oss-cn-beijing.aliyuncs.com`）
  - `bucket`：OSS 存储桶名
  - `url_prefix`：对外可访问的 URL 前缀（可绑定 CDN）
  - `role_arn` / `temp_app_key` / `temp_app_secret`：STS RAM 角色描述符与子账号密钥
  - `watermark`：水印图 URL（必须与 `url_prefix` 同域）
- **权限**：`UploadController::store()` 强制要求 `backend:py-system.global.manage` 权限。
- **校验**：除 `url_prefix`（要求 URL 格式）和 `watermark`（要求 URL 格式）外，其余字段均 `Rule::nullable()`——后端不做强校验，把责任交给阿里云（access_key 错误会在首次 OSS 调用时抛 `OssException`）。

---

## 中间件规则

| 中间件           | 应用范围                              | 规则                          |
|---------------|-----------------------------------|-----------------------------|
| `backend-auth`| 后台 `upload/store` 路由                | 必须登录且具 `backend:py-system.global.manage` 权限 |
| `api-sign`    | API V1 `sts/temp_oss` 路由           | 需要 API 签名校验（白名单/时间戳/签名三段）    |

---

## 待确认

- `Action\Sts::tempOss()` 注释/文档提及 `is_temp=Y` 时子目录采用 `His{rand(8)}` 格式，但实际代码仅设置 `subDirectory='temp'`，**未生成 8 位随机串**（发现位置：`src/Action/Sts.php:118-120`、`src/Http/Request/ApiV1/Web/StsController.php:53`）
- `resources/config/aliyun-oss.php` 中存在 `url` 配置键（默认空），但代码中读取的是 `url_prefix`——`url` 键似乎是历史残留或废弃字段（发现位置：`resources/config/aliyun-oss.php:44`）
- `FormSettingAliyunOss` 暴露了 `temp_app_key` / `temp_app_secret` 两个表单字段，但 `Action\Sts::__construct()` 与 `OssFileProvider::__construct()` 都只读取 `access_key` / `access_secret`，未消费 `temp_app_*`——疑似遗留字段（发现位置：`src/Http/MgrPage/FormSettingAliyunOss.php:44-48`）
