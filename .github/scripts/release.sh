#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────
# Poppy Release Orchestrator
# 主调度脚本：在 GitHub Actions runner 上执行
# 用法: release.sh <version> [previous-tag] [tag-body] [--dry-run]
#
# 环境变量:
#   GH_TOKEN      - GitHub token for API calls（来自 workflow secrets）
#   DRY_RUN=1     - 等价 --dry-run
# ─────────────────────────────────────────────────────────

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

VERSION="${1:-}"
PREV_TAG="${2:-}"
TAG_BODY="${3:-}"
DRY_RUN="${DRY_RUN:-0}"
[[ "${4:-}" == "--dry-run" ]] && DRY_RUN=1

if [[ -z "$VERSION" ]]; then
  echo "Usage: $0 <version> [previous-tag] [tag-body] [--dry-run]" >&2
  exit 1
fi

MANIFEST="$REPO_ROOT/.github/release-manifest.yml"
if [[ ! -f "$MANIFEST" ]]; then
  echo "✗ Manifest not found: $MANIFEST" >&2
  exit 1
fi

# ─── 解析 tag message body 中的 skip / only 控制 ───
SKIP_MODULES=""
ONLY_MODULES=""
if [[ -n "$TAG_BODY" ]]; then
  if echo "$TAG_BODY" | grep -qiE '^[[:space:]]*skip[[:space:]]*:'; then
    SKIP_MODULES=$(echo "$TAG_BODY" \
      | grep -iE '^[[:space:]]*skip[[:space:]]*:' \
      | head -1 \
      | sed -E 's/^[[:space:]]*skip[[:space:]]*:[[:space:]]*//' \
      | tr ',' ' ' \
      | xargs)
  fi
  if echo "$TAG_BODY" | grep -qiE '^[[:space:]]*only[[:space:]]*:'; then
    ONLY_MODULES=$(echo "$TAG_BODY" \
      | grep -iE '^[[:space:]]*only[[:space:]]*:' \
      | head -1 \
      | sed -E 's/^[[:space:]]*only[[:space:]]*:[[:space:]]*//' \
      | tr ',' ' ' \
      | xargs)
  fi
fi

echo "═══════════════════════════════════════════════════"
echo "  Poppy Release Orchestrator"
echo "  Version:      $VERSION"
echo "  Previous tag: ${PREV_TAG:-(none — first release)}"
echo "  Skip modules: ${SKIP_MODULES:-(none)}"
echo "  Only modules: ${ONLY_MODULES:-(all)}"
echo "  Dry run:      $DRY_RUN"
echo "═══════════════════════════════════════════════════"
echo

# ─── 解析 manifest 里的所有模块名 ───
mapfile -t ALL_MODULES < <(grep -E '^[[:space:]]*-[[:space:]]+name:' "$MANIFEST" \
  | sed -E 's/^[[:space:]]*-[[:space:]]+name:[[:space:]]*//' \
  | awk '{print $1}' \
  | sed 's/"//g')

if [[ ${#ALL_MODULES[@]} -eq 0 ]]; then
  echo "✗ No modules found in manifest" >&2
  exit 1
fi

echo "Manifest modules (${#ALL_MODULES[@]}): ${ALL_MODULES[*]}"

# 校验 manifest 与实际 poppy/ 目录一致性
MISSING=()
for mod in "${ALL_MODULES[@]}"; do
  if [[ ! -d "$REPO_ROOT/poppy/$mod" ]]; then
    MISSING+=("$mod")
  fi
done
if [[ ${#MISSING[@]} -gt 0 ]]; then
  echo "✗ Manifest references missing poppy/ dirs: ${MISSING[*]}" >&2
  exit 1
fi

# 反向：实际 poppy/ 目录但不在 manifest 的（warn 但不阻断）
ORPHAN=()
for d in "$REPO_ROOT/poppy"/*/; do
  name=$(basename "$d")
  if ! printf '%s\n' "${ALL_MODULES[@]}" | grep -qx "$name"; then
    ORPHAN+=("$name")
  fi
done
if [[ ${#ORPHAN[@]} -gt 0 ]]; then
  echo "⚠ Orphan poppy/ dirs not in manifest (will be skipped): ${ORPHAN[*]}"
fi
echo

# ─── 计算最终要发布的模块列表 ───
declare -a TO_RELEASE=()
declare -a SKIPPED=()
for mod in "${ALL_MODULES[@]}"; do
  skip=0
  reason=""
  if [[ -n "$ONLY_MODULES" ]]; then
    if ! echo " $ONLY_MODULES " | grep -q " $mod "; then
      skip=1
      reason="not in only list"
    fi
  fi
  if [[ -n "$SKIP_MODULES" ]] && echo " $SKIP_MODULES " | grep -q " $mod "; then
    skip=1
    reason="in skip list"
  fi
  if [[ "$skip" == "0" ]]; then
    TO_RELEASE+=("$mod")
  else
    SKIPPED+=("$mod ($reason)")
  fi
done

echo "Release plan:"
echo "  Will release (${#TO_RELEASE[@]}): ${TO_RELEASE[*]:-}"
echo "  Will skip    (${#SKIPPED[@]}): ${SKIPPED[*]:-}"
echo

# ─── Dry-run 模式 ───
if [[ "$DRY_RUN" == "1" ]]; then
  echo "── DRY-RUN MODE — no git push / no API calls ──"
  for mod in "${TO_RELEASE[@]}"; do
    echo
    # 从 manifest 读取模板
    REPO_TPL=$(grep -E '^[[:space:]]*repo_name_template:' "$MANIFEST" | sed -E 's/^[[:space:]]*repo_name_template:[[:space:]]*//' | head -1 | tr -d '[:space:]')
    [[ -z "$REPO_TPL" ]] && REPO_TPL='{name}'
    # 用 bash 参数展开替换 {name}（避免 sed 把 { 和 } 当元字符）
    TARGET_REPO_DISPLAY="${REPO_TPL//\{name\}/$mod}"
    GH_ORG_DISPLAY=$(grep -E '^[[:space:]]*github_org:' "$MANIFEST" | sed -E 's/^[[:space:]]*github_org:[[:space:]]*//' | head -1 | tr -d '[:space:]')
    [[ -z "$GH_ORG_DISPLAY" ]] && GH_ORG_DISPLAY='poppy-framework'
    echo ">>> [DRY-RUN] Would release: $mod → $GH_ORG_DISPLAY/$TARGET_REPO_DISPLAY"
    echo "    changelog preview:"
    "$SCRIPT_DIR/changelog.sh" "$mod" "$PREV_TAG" "v$VERSION" --dry-run 2>/dev/null \
      | head -15 | sed 's/^/      /'
  done
  echo
  echo "── DRY-RUN COMPLETE ──"
  exit 0
fi

# ─── 真发：顺序执行（避免 GitHub API rate limit；split 是 O(history)） ───
FAILED=()
SUCCEEDED=()
for mod in "${TO_RELEASE[@]}"; do
  echo
  echo "▶▶▶ Releasing: $mod"
  if "$SCRIPT_DIR/release-module.sh" "$mod" "$VERSION" "$PREV_TAG"; then
    echo "✓ $mod released successfully"
    SUCCEEDED+=("$mod")
  else
    echo "✗ $module FAILED"
    FAILED+=("$mod")
  fi
done

echo
echo "═══════════════════════════════════════════════════"
echo "  Release Summary"
echo "    Total:     ${#TO_RELEASE[@]}"
echo "    Succeeded: ${#SUCCEEDED[@]} ${SUCCEEDED[*]:-}"
echo "    Failed:    ${#FAILED[@]} ${FAILED[*]:-}"
echo "═══════════════════════════════════════════════════"

if [[ ${#FAILED[@]} -gt 0 ]]; then
  exit 1
fi
