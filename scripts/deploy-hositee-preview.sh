#!/usr/bin/env bash
set -euo pipefail

COMMIT="${1:-}"
REPO="marketinghorizonssa-alt/Enkaf"
PREVIEW_DOMAIN="enkaf.hositee.com"
ACCOUNT_HOME="/home/u878466595"
SITE_ROOT="${ACCOUNT_HOME}/domains/hositee.com/public_html/enkaf-site"
PUBLIC_ROOT="${SITE_ROOT}/public"
APP_ROOT="${SITE_ROOT}/app"
PRIVATE_ROOT="${ACCOUNT_HOME}/enkaf-preview-private"
BACKUP_ROOT="${ACCOUNT_HOME}/enkaf-preview-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="${ACCOUNT_HOME}/.enkaf-preview-deploy-${STAMP}"
ARCHIVE="${WORK}/source.tar.gz"
BACKUP="${BACKUP_ROOT}/before-${COMMIT:0:12}-${STAMP}.tar.gz"
LOCK="${ACCOUNT_HOME}/.enkaf-preview-deploy.lock"

if ! printf '%s' "$COMMIT" | grep -Eq '^[0-9a-f]{40}$'; then
  echo "ENKAF_PREVIEW_DEPLOY_ERROR invalid_commit"
  exit 2
fi

mkdir -p "$WORK" "$BACKUP_ROOT" "$PRIVATE_ROOT"
chmod 700 "$PRIVATE_ROOT" || true

exec 9>"$LOCK"
if command -v flock >/dev/null 2>&1 && ! flock -n 9; then
  echo "ENKAF_PREVIEW_DEPLOY_SKIP locked"
  exit 0
fi

if [ -f "$SITE_ROOT/.enkaf-release" ] && [ "$(tr -d '\r\n' < "$SITE_ROOT/.enkaf-release")" = "$COMMIT" ]; then
  echo "ENKAF_PREVIEW_DEPLOY_ALREADY_OK commit=${COMMIT}"
  exit 0
fi

cleanup() {
  rm -rf "$WORK"
}
trap cleanup EXIT

curl -fLsS --retry 3 --connect-timeout 15 \
  "https://codeload.github.com/${REPO}/tar.gz/${COMMIT}" \
  -o "$ARCHIVE"

tar -xzf "$ARCHIVE" -C "$WORK"
SRC="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -name 'Enkaf-*' | head -n 1)"
if [ -z "$SRC" ] || [ ! -f "$SRC/public/index.php" ] || [ ! -f "$SRC/app/config.php" ]; then
  echo "ENKAF_PREVIEW_DEPLOY_ERROR incomplete_source"
  exit 3
fi

find "$SRC/app" "$SRC/public" -type f -name '*.php' -print0 | while IFS= read -r -d '' f; do
  php -l "$f" >/dev/null
done
if grep -RniE 'arkan|shaghel|easttwist|GTM-P5J6D6ND|AW-18127536852|AW-18303013990' "$SRC/app" "$SRC/public" "$SRC/docs"; then
  echo "ENKAF_PREVIEW_DEPLOY_ERROR cross_client_token"
  exit 3
fi

if [ -d "$SITE_ROOT" ] && [ -n "$(find "$SITE_ROOT" -mindepth 1 -maxdepth 1 -print -quit 2>/dev/null)" ]; then
  tar -czf "$BACKUP" -C "$(dirname "$SITE_ROOT")" "$(basename "$SITE_ROOT")"
  [ -s "$BACKUP" ] || { echo "ENKAF_PREVIEW_DEPLOY_ERROR backup_missing"; exit 3; }
fi

rm -rf "$SITE_ROOT"
mkdir -p "$PUBLIC_ROOT" "$APP_ROOT"
cp -a "$SRC/public/." "$PUBLIC_ROOT/"
cp -a "$SRC/app/." "$APP_ROOT/"

cat > "$SITE_ROOT/.htaccess" <<'EOF'
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
  Deny from all
</IfModule>
EOF

cat >> "$PUBLIC_ROOT/.htaccess" <<EOF

# ENKAF temporary preview environment
SetEnv ENKAF_SITE_URL https://${PREVIEW_DOMAIN}
SetEnv ENKAF_REVIEW_MODE true
SetEnv ENKAF_DATA_DIR ${PRIVATE_ROOT}
EOF

printf '%s\n' "$COMMIT" > "$SITE_ROOT/.enkaf-release"
printf '%s\n' "$COMMIT" > "$PUBLIC_ROOT/.enkaf-release"
printf '%s\n' "$COMMIT" > "$PUBLIC_ROOT/enkaf-release.txt"
chmod 644 "$PUBLIC_ROOT/.enkaf-release" "$PUBLIC_ROOT/enkaf-release.txt"

HEALTH="$(ENKAF_SITE_URL="https://${PREVIEW_DOMAIN}" ENKAF_REVIEW_MODE=true ENKAF_DATA_DIR="$PRIVATE_ROOT" REQUEST_URI='/healthz/' REQUEST_METHOD='GET' php "$PUBLIC_ROOT/index.php" 2>/dev/null || true)"
if ! printf '%s' "$HEALTH" | grep -Fq '"ok":true' || ! printf '%s' "$HEALTH" | grep -Fq '"review_mode":true'; then
  echo "ENKAF_PREVIEW_DEPLOY_ERROR local_verification_failed commit=${COMMIT}"
  exit 4
fi

ROBOTS="$(ENKAF_SITE_URL="https://${PREVIEW_DOMAIN}" ENKAF_REVIEW_MODE=true ENKAF_DATA_DIR="$PRIVATE_ROOT" REQUEST_URI='/robots.txt' REQUEST_METHOD='GET' php "$PUBLIC_ROOT/index.php" 2>/dev/null || true)"
if ! printf '%s' "$ROBOTS" | grep -Fq 'Disallow: /'; then
  echo "ENKAF_PREVIEW_DEPLOY_ERROR robots_not_blocked"
  exit 4
fi

echo "ENKAF_PREVIEW_DEPLOY_OK commit=${COMMIT} domain=${PREVIEW_DOMAIN} review_mode=true backup=${BACKUP}"
