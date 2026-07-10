#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────
# 给 composer.json 注入或更新 version 字段
# 用法: inject-version.sh <composer.json> <version>
#
# 用 jq 优先，sed 兜底
# ─────────────────────────────────────────────────────────

set -euo pipefail

FILE="${1:-}"
VERSION="${2:-}"

if [[ -z "$FILE" || -z "$VERSION" ]]; then
  echo "Usage: $0 <composer.json> <version>" >&2
  exit 1
fi

if [[ ! -f "$FILE" ]]; then
  echo "✗ File not found: $FILE" >&2
  exit 1
fi

if command -v jq >/dev/null 2>&1; then
  TMP=$(mktemp)
  jq --arg v "$VERSION" '.version = $v' "$FILE" > "$TMP"
  mv "$TMP" "$FILE"
else
  # fallback: sed
  if grep -qE '"version"[[:space:]]*:' "$FILE"; then
    sed -i.bak -E "s/\"version\"[[:space:]]*:[[:space:]]*\"[^\"]*\"/\"version\": \"$VERSION\"/" "$FILE"
    rm -f "$FILE.bak"
  else
    # 在 name 字段后面插入 version 字段
    sed -i.bak -E "/\"name\"[[:space:]]*:/a\\
    \"version\": \"$VERSION\"," "$FILE"
    rm -f "$FILE.bak"
  fi
fi

echo "  ✓ version=$VERSION injected into $(basename "$FILE")"
