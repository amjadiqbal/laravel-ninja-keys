#!/usr/bin/env bash
# Rebuilds resources/dist/ninja-keys.js: ninja-keys with its exact dependencies (Lit 2, hotkeys-js,
# @material/mwc-icon) in one ES module, so the "local" script mode makes no third-party requests and
# cannot pick up a mismatched lit-html from a CDN. Needs node/npm. Pin bumps go in NINJA_KEYS_VERSION.
set -euo pipefail
cd "$(dirname "$0")/.."
NINJA_KEYS_VERSION=${NINJA_KEYS_VERSION:-1.2.2}
ESBUILD_VERSION=0.24.2
WORK=$(mktemp -d); trap 'rm -rf "$WORK"' EXIT

(cd "$WORK" && npm init -y >/dev/null && npm install --ignore-scripts --no-audit --no-fund \
  "ninja-keys@$NINJA_KEYS_VERSION" "esbuild@$ESBUILD_VERSION" >/dev/null)
echo "import 'ninja-keys';" > "$WORK/entry.js"
DEPS=$(cd "$WORK" && npm ls --all --parseable --long 2>/dev/null | awk -F: 'NR>1 {print $2}' | grep -v '^esbuild\|^@esbuild' | sort -u | tr '\n' ' ')

{
  printf '/*! ninja-keys %s bundled for amjadiqbal/laravel-ninja-keys by scripts/build-bundle.sh.\n' "$NINJA_KEYS_VERSION"
  printf ' * ninja-keys (MIT) (c) Serhii Slieptsov, https://github.com/ssleptsov/ninja-keys\n'
  printf ' * Includes: %s\n' "$DEPS"
  printf ' * Licences: Lit (BSD-3-Clause), hotkeys-js (MIT), @material/mwc-icon (Apache-2.0). */\n'
  "$WORK/node_modules/.bin/esbuild" "$WORK/entry.js" --bundle --format=esm --minify --legal-comments=inline --log-level=warning
} > resources/dist/ninja-keys.js

node --check resources/dist/ninja-keys.js 2>/dev/null || node --input-type=module --check < resources/dist/ninja-keys.js
echo "resources/dist/ninja-keys.js: $(wc -c < resources/dist/ninja-keys.js) bytes"
