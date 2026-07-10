## 本项目用来开发 Poppy 扩展

### 开发流程

将扩展放置到 poppy 目录下进行开发。每个子目录是一个独立的 Composer 包（`vendor: poppy/<name>`）。

## 自动发布到独立 GitHub 仓库

本仓库通过 **GitHub Actions + 单一 tag** 自动把 `poppy/<name>/` 子模块发布到 `poppy-framework/poppy-<name>` 独立仓库，同时创建 GitHub Release 并通过 webhook 同步到 packagist.org。

### 发布流程

```bash
# 1. 本地打 tag（注意 tag 名格式必须是 v*.*.*）
git tag v4.2.1

# 2. 推送 tag 到 origin，触发 GitHub Actions
git push origin v4.2.1

# 3. GitHub Actions 自动：
#    - 解析 tag message 里的 skip / only 控制
#    - 对每个要发布的子模块执行：
#      git subtree split → 注入 version → push 到 poppy-framework/poppy-<name>
#      → 创建 GitHub Release → 触发 packagist webhook
```

### 精细控制（在 tag message body 中写）

```bash
# 跳过指定模块（保留其上次版本号，不发）
git tag v4.2.1 -m "skip: ad, im-rpc, ext-phpstan"

# 只发指定模块（其余不发）
git tag v4.2.1 -m "only: framework, core"
```

### 添加新模块

1. 在 `poppy/<name>/` 准备子目录（含 `composer.json`）
2. 在 `.github/release-manifest.yml` 里加一条：
   ```yaml
   - name: <name>
     description: <your description>
     dependencies: []
   ```
3. 推 tag 即可触发发布

### 本地 dry-run 验证

```bash
composer release:dry-run
# 或直接：
./.github/scripts/release.sh 4.2.1-rc1 "" "" --dry-run
```

### GitHub 端需要配置

- 在 `poppy-framework` 组织下创建 22 个目标仓库：`poppy-framework`、`poppy-framework-core` … `poppy-framework-app`
- 在本仓库 Settings → Secrets → Actions 配置：
  - `POPPY_RELEASE_TOKEN`：一个 PAT，对所有 22 个目标仓库有 `contents: write`（推荐用 GitHub App 替代）
- （可选）在每个目标仓库的 Settings → Webhooks → Add webhook，指向 packagist.org 的 service URL，让 push 事件自动触发 packagist 刷新

详见 `.github/workflows/release.yml` 和 `.github/release-manifest.yml`。

## 校验清单

1. 验证当前的 jwt 和 backend 的 guard 是否正确

