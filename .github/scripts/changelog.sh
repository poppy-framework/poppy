#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────
# 生成指定模块的 changelog markdown
# 用法: changelog.sh <module> <prev-tag> <new-tag> [--dry-run]
#
# 输出到 stdout，可重定向到文件。
# --dry-run 模式只打印前 20 条 commit。
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

if [[ ! -d "$REPO_ROOT/$PREFIX" ]]; then
  echo "✗ Module directory not found: $PREFIX (looked under $REPO_ROOT)" >&2
  exit 1
fi

# ─── Tag & range validation ────────────────────────────────────────────────
# PREV_TAG may legitimately be empty (initial release), but if supplied it
# must resolve. NEW_TAG is mandatory.
prev_ok=0
if [[ -n "$PREV_TAG" ]]; then
  if git -C "$REPO_ROOT" rev-parse --verify --quiet "$PREV_TAG^{commit}" >/dev/null 2>&1; then
    prev_ok=1
  fi
fi

if ! git -C "$REPO_ROOT" rev-parse --verify --quiet "$NEW_TAG^{commit}" >/dev/null 2>&1; then
  echo "✗ new tag not resolvable: $NEW_TAG" >&2
  exit 1
fi

RANGE=""
if [[ "$prev_ok" -eq 1 ]]; then
  # Catch reversed ranges: a swapped prev/new silently produces an empty
  # range and would ship a "(no commits)" changelog to a real release.
  if ! git -C "$REPO_ROOT" merge-base --is-ancestor "$PREV_TAG" "$NEW_TAG" 2>/dev/null; then
    echo "✗ $PREV_TAG is not an ancestor of $NEW_TAG — range would be empty." >&2
    echo "  (verify the prev/new tag ordering before re-running)" >&2
    exit 1
  fi
  RANGE="$PREV_TAG..$NEW_TAG"
fi

DRY_RUN=0
if [[ "${4:-}" == "--dry-run" ]]; then
  DRY_RUN=1
fi

# ─── Single source of truth for the git log filter ─────────────────────────
# Critical: range must come BEFORE `--`, otherwise git treats it as a path
# filter and silently drops the range.
#
# Args: <pretty-format> <limit>
print_commits() {
  local pretty="$1"
  local limit="$2"

  local -a log_args=(--no-merges "--pretty=format:$pretty")
  if [[ -n "$RANGE" ]]; then
    log_args+=("$RANGE")
  fi
  log_args+=("--" "$PREFIX")

  # `head -n` closes the pipe early → git receives SIGPIPE (exit 141) → under
  # `set -o pipefail` the whole pipeline would be marked failed. Capture
  # output, append `|| true`, and surface "(no commits)" on empty result.
  local out
  out=$(git -C "$REPO_ROOT" log "${log_args[@]}" 2>/dev/null | head -n "$limit" || true)
  if [[ -z "$out" ]]; then
    echo "(no commits)"
  else
    printf '%s\n' "$out"
  fi
}

# ─── Render ────────────────────────────────────────────────────────────────
echo "## Changes in $NEW_TAG"
echo

if [[ "$prev_ok" -eq 0 && -n "$PREV_TAG" ]]; then
  # Caller gave a prev tag we couldn't resolve; surface that fact and fall
  # back to the last 20 commits for that module path.
  echo "_(previous tag $PREV_TAG not found — showing last 20 commits)_"
  echo
  print_commits "%h %s" 20
elif [[ -z "$RANGE" ]]; then
  echo "_(initial release)_"
else
  echo "### Commits between $PREV_TAG and $NEW_TAG"
  echo
  if [[ "$DRY_RUN" -eq 1 ]]; then
    print_commits "%h %s" 20
  else
    print_commits "- %s (%h)" 100
  fi
fi
