#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────
# Poppy 单模块发布脚本
# 用法: release-module.sh <module-name> <version> <previous-tag>
#
# 步骤:
#   1. git subtree split --prefix=poppy/<module> → split/<module>
#   2. 在临时 git 仓库注入 version 字段
#   3. force-push 到 poppy-framework/<module> 的 main 分支
#   4. 在目标仓库创建 GitHub Release（带 changelog）
#   5. packagist 同步（依赖 GitHub webhook 已配）
# ─────────────────────────────────────────────────────────

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

MODULE="${1:-}"
VERSION="${2:-}"
PREV_TAG="${3:-}"

if [[ -z "$MODULE" || -z "$VERSION" ]]; then
  echo "Usage: $0 <module> <version> [previous-tag]" >&2
  exit 1
fi

# 从 manifest 读取配置（repo_name_template / github_org / default_branch）
MANIFEST="$REPO_ROOT/.github/release-manifest.yml"
GH_ORG="${GH_ORG:-}"
if [[ -z "$GH_ORG" ]]; then
  GH_ORG=$(grep -E '^[[:space:]]*github_org:' "$MANIFEST" | sed -E 's/^[[:space:]]*github_org:[[:space:]]*//' | head -1 | tr -d '[:space:]')
fi
[[ -z "$GH_ORG" ]] && GH_ORG='poppy-framework'

REPO_TEMPLATE=$(grep -E '^[[:space:]]*repo_name_template:' "$MANIFEST" | sed -E 's/^[[:space:]]*repo_name_template:[[:space:]]*//' | head -1 | tr -d '[:space:]')
[[ -z "$REPO_TEMPLATE" ]] && REPO_TEMPLATE='{name}'

# 用 bash 参数展开替换 {name}（避免 sed 把 { 和 } 当元字符）
TARGET_REPO="${TARGET_REPO_OVERRIDE:-${REPO_TEMPLATE//\{name\}/$MODULE}}"

TARGET_BRANCH="${TARGET_BRANCH:-}"
if [[ -z "$TARGET_BRANCH" ]]; then
  TARGET_BRANCH=$(grep -E '^[[:space:]]*default_branch:' "$MANIFEST" | sed -E 's/^[[:space:]]*default_branch:[[:space:]]*//' | head -1 | tr -d '[:space:]')
fi
[[ -z "$TARGET_BRANCH" ]] && TARGET_BRANCH='main'

AUTH_TOKEN="${GH_TOKEN:-${GITHUB_TOKEN:-}}"

if [[ -z "$AUTH_TOKEN" ]]; then
  echo "✗ No GH_TOKEN or GITHUB_TOKEN in environment" >&2
  exit 1
fi

# ─── 诊断：打印 token 的真实身份和权限 ───
echo "[$MODULE] ── Token diagnostics ──"
TOKEN_PREFIX="${AUTH_TOKEN:0:10}"
echo "  Token prefix: $TOKEN_PREFIX"
# 用 token 查 /user，看是哪个身份
WHOAMI=$(curl -sS -H "Authorization: token $AUTH_TOKEN" -H "Accept: application/vnd.github+json" \
  https://api.github.com/user 2>/dev/null || echo "{}")
LOGIN=$(echo "$WHOAMI" | jq -r '.login // "(invalid token)"' 2>/dev/null)
TYPE=$(echo "$WHOAMI" | jq -r '.type // "(unknown)"' 2>/dev/null)
echo "  Identity: $LOGIN (type: $TYPE)"

# 查 framework 仓库权限（用 /repos/.../installation 看 App 安装的实际授权）
REPO_PERMS=$(curl -sS -o /dev/null -w "%{http_code}" \
  -H "Authorization: token $AUTH_TOKEN" -H "Accept: application/vnd.github+json" \
  "https://api.github.com/repos/$GH_ORG/$TARGET_REPO" 2>/dev/null || echo "000")
echo "  GET /repos/$GH_ORG/$TARGET_REPO → HTTP $REPO_PERMS"

# 列出 token 能访问的所有仓库数（用 /installation/repositories，仅 GitHub App token 有权限）
REPO_LIST=$(curl -sS -H "Authorization: token $AUTH_TOKEN" -H "Accept: application/vnd.github+json" \
  "https://api.github.com/installation/repositories?per_page=100" 2>/dev/null || echo "{}")
REPO_COUNT=$(echo "$REPO_LIST" | jq -r '.total_count // 0' 2>/dev/null)
echo "  Installation repositories count: $REPO_COUNT"
if [[ "$REPO_COUNT" -gt 0 ]] && [[ "$REPO_COUNT" -lt 100 ]]; then
  echo "  Accessible repos (sample):"
  echo "$REPO_LIST" | jq -r '.repositories[]?.name' 2>/dev/null | head -10 | sed 's/^/    - /'
fi
echo "───────────────────────────────────────"

PREFIX="poppy/$MODULE"

if [[ ! -d "$REPO_ROOT/$PREFIX" ]]; then
  echo "✗ Module directory not found: $PREFIX" >&2
  exit 1
fi

SPLIT_BRANCH="split/$MODULE"
TMPDIR=$(mktemp -d -t "poppy-release-$MODULE-XXXXXX")
trap 'rm -rf "$TMPDIR"; git -C "$REPO_ROOT" branch -D "$SPLIT_BRANCH" 2>/dev/null || true' EXIT

echo "[$MODULE] Step 1/5: subtree split"
# 注意：--squash 不能用在 split 上，只对 add 有意义
git -C "$REPO_ROOT" subtree split \
  --prefix="$PREFIX" \
  --branch="$SPLIT_BRANCH" \
  >/dev/null 2>&1 || {
    echo "  ✗ subtree split failed for $MODULE" >&2
    exit 1
  }
SPLIT_SHA=$(git -C "$REPO_ROOT" rev-parse "$SPLIT_BRANCH")
echo "  ✓ split → $SPLIT_SHA"

echo "[$MODULE] Step 2/5: prepare release commit"
git -C "$REPO_ROOT" archive "$SPLIT_BRANCH" | tar -x -C "$TMPDIR"
"$SCRIPT_DIR/inject-version.sh" "$TMPDIR/composer.json" "$VERSION"

# 在临时 git 仓库里做一次 commit（包含 version 注入）
git -C "$TMPDIR" init -q
git -C "$TMPDIR" config user.email "release-bot@poppy-framework.local"
git -C "$TMPDIR" config user.name "poppy-release-bot"
git -C "$TMPDIR" add -A
git -C "$TMPDIR" commit -q -m "chore(release): bump version to $VERSION"
RELEASE_SHA=$(git -C "$TMPDIR" rev-parse HEAD)
echo "  ✓ release commit: $RELEASE_SHA"

echo "[$MODULE] Step 3/5: push to $GH_ORG/$TARGET_REPO:$TARGET_BRANCH"
PUSH_URL="https://x-access-token:${AUTH_TOKEN}@github.com/$GH_ORG/$TARGET_REPO.git"
if git -C "$TMPDIR" push --force "$PUSH_URL" "HEAD:refs/heads/$TARGET_BRANCH" 2>&1 | tail -3; then
  echo "  ✓ pushed"
else
  echo "  ✗ push failed" >&2
  exit 1
fi

echo "[$MODULE] Step 4/5: create GitHub Release v$VERSION"
# Capture changelog errors into a temp file so CI logs show what went wrong
# (instead of silently swallowing them and shipping a junk release body).
ERR=$(mktemp)
if ! CHANGELOG=$("$SCRIPT_DIR/changelog.sh" "$MODULE" "$PREV_TAG" "v$VERSION" 2>"$ERR"); then
  cat "$ERR" >&2
  echo "::warning::changelog generation failed for $MODULE — using placeholder body" >&2
  CHANGELOG="## v$VERSION"
fi
rm -f "$ERR"
RELEASE_NOTES=$(printf '%s\n\n%s\n' "## v$VERSION" "$CHANGELOG")

RELEASE_BODY=$(jq -n \
  --arg tag "v$VERSION" \
  --arg name "v$VERSION" \
  --arg body "$RELEASE_NOTES" \
  --arg target "$TARGET_BRANCH" \
  '{tag_name: $tag, name: $name, body: $body, target_commitish: $target, draft: false, prerelease: false}')

HTTP_CODE=$(curl -sS -o "$TMPDIR/release-resp.json" -w "%{http_code}" \
  -X POST \
  -H "Authorization: token $AUTH_TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "Content-Type: application/json" \
  -d "$RELEASE_BODY" \
  "https://api.github.com/repos/$GH_ORG/$TARGET_REPO/releases")

case "$HTTP_CODE" in
  201)
    echo "  ✓ release created"
    ;;
  422)
    # 已存在（release with this tag already exists）—— 幂等
    echo "  ✓ release already exists (idempotent)"
    ;;
  *)
    echo "  ✗ release create failed: HTTP $HTTP_CODE" >&2
    cat "$TMPDIR/release-resp.json" >&2
    exit 1
    ;;
esac

# Step 5: packagist 同步 — 通过 push 事件触发 GitHub webhook（用户需在 packagist 后台预先配置）
# 这里不直接调 packagist API，避免管理 22 个 token

echo "[$MODULE] Step 5/5: packagist sync via webhook (configured upstream)"
echo "  → push event will trigger packagist webhook if configured at packagist.org"
echo "[$MODULE] ✓ done"
