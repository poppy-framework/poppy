#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────
# 生成指定模块的 changelog markdown
# 用法: changelog.sh <module> <prev-tag> <new-tag> [--dry-run]
#
# 输出到 stdout，可重定向到文件。
# --dry-run 模式只打印前 20 条 commit，dry-run 用。
# ─────────────────────────────────────────────────────────

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

MODULE="${1:-}"
PREV_TAG="${2:-}"
NEW_TAG="${3:-}"

if [[ -z "$MODULE" || -z "$NEW_TAG" ]]; then
  echo "Usage: $0 <module> <prev-tag> <new-tag> [--dry-run]" >&2
  exit 1
fi

PREFIX="poppy/$MODULE"
RANGE=""
if [[ -n "$PREV_TAG" ]]; then
  RANGE="$PREV_TAG..$NEW_TAG"
fi

if [[ "${4:-}" == "--dry-run" ]]; then
  echo "## Changes in $NEW_TAG"
  echo
  if [[ -n "$RANGE" ]]; then
    if git -C "$REPO_ROOT" rev-parse "$PREV_TAG" >/dev/null 2>&1; then
      git -C "$REPO_ROOT" log --oneline --no-merges -- "$PREFIX" "$RANGE" 2>/dev/null | head -20 || echo "(no commits)"
    else
      echo "(previous tag $PREV_TAG not found — showing last 20 commits)"
      git -C "$REPO_ROOT" log --oneline --no-merges -- "$PREFIX" 2>/dev/null | head -20
    fi
  else
    echo "(initial release)"
  fi
  exit 0
fi

# Normal output
{
  echo "## Changes in $NEW_TAG"
  echo
  if [[ -n "$RANGE" ]] && git -C "$REPO_ROOT" rev-parse "$PREV_TAG" >/dev/null 2>&1; then
    echo "### Commits between $PREV_TAG and $NEW_TAG"
    echo
    if git -C "$REPO_ROOT" rev-parse "$RANGE" >/dev/null 2>&1; then
      git -C "$REPO_ROOT" log --pretty=format:"- %s (%h)" --no-merges -- "$PREFIX" "$RANGE" 2>/dev/null \
        | head -100
      echo
    else
      echo "(no commits in range)"
    fi
  else
    if [[ -n "$PREV_TAG" ]]; then
      echo "_(previous tag $PREV_TAG not found — showing last 50 commits)_"
      echo
      git -C "$REPO_ROOT" log --pretty=format:"- %s (%h)" --no-merges -- "$PREFIX" 2>/dev/null \
        | head -50
      echo
    else
      echo "_(initial release)_"
    fi
  fi
}
