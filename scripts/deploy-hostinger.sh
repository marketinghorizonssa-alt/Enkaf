#!/usr/bin/env bash
set -euo pipefail

COMMIT="${1:-}"
REVIEW_MODE="${2:-true}"
REPO="marketinghorizonssa-alt/Enkaf"
DOMAIN="enkaf.sa"
DOMAIN_ROOT="/home/u128565677/domains/${DOMAIN}"
PUBLIC_ROOT="${DOMAIN_ROOT}/public_html"
APP_ROOT="${DOMAIN_ROOT}/app"
PRIVATE_ROOT="${DOMAIN_ROOT}/enkaf-private"
BACKUP_ROOT="${DOMAIN_ROOT}/enkaf-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="${DOMAIN_ROOT}/.enkaf-deploy-${STAMP}"
ARCHIVE="${WORK}/source.tar.gz"

if ! printf '%s' "$COMMIT" | grep -Eq '^[0-9a-f]{40}$'; then
  echo "ENKAF_DEPLOY_ERROR invalid_commit"
  exit 2
fi
if [ "$REVIEW_MODE" != "true" ] && [ "$REVIEW_MODE" != "false" ]; then
  echo "ENKAF_DEPLOY_ERROR invalid_review_mode"
  exit 2
fi

mkdir -p "$WORK" "$BACKUP_ROOT" "$PRIVATE_ROOT"
chmod 700 "$PRIVATE_ROOT" || true

cleanup() {
  rm -rf "$WORK"
}
trap cleanup EXIT

curl -fLsS --retry 3 --connect-timeout 15 \
  "https://codeload.github.com/${REPO}/tar.gz/${COMMIT}" \
  -o "$ARCHIVE"

tar -xzf "$ARCHIVE" -C "$WORK"
SRC="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -name 'Enkaf-*' | head -n 1)"
if [ -z "$SRC" ] || [ ! -f "$SRC/public/index.php" ] || [ ! -f "$SRC/app/config.php" ] || [ ! -f "$SRC/scripts/validate.sh" ]; then
  echo "ENKAF_DEPLOY_ERROR incomplete_source"
  exit 3
fi

bash "$SRC/scripts/validate.sh"

BACKUP="${BACKUP_ROOT}/before-${COMMIT:0:12}-${STAMP}.tar.gz"
if [ -d "$PUBLIC_ROOT" ]; then
  tar -czf "$BACKUP" -C "$DOMAIN_ROOT" public_html $( [ -d "$APP_ROOT" ] && printf '%s' app || true )
fi

mkdir -p "$PUBLIC_ROOT"
find "$PUBLIC_ROOT" -mindepth 1 -maxdepth 1 -exec rm -rf {} +
rm -rf "$APP_ROOT"
mkdir -p "$APP_ROOT"
cp -a "$SRC/public/." "$PUBLIC_ROOT/"
cp -a "$SRC/app/." "$APP_ROOT/"

cat >> "$PUBLIC_ROOT/.htaccess" <<EOF

# ENKAF deployment environment (generated from recorded GitHub commit)
SetEnv ENKAF_SITE_URL https://${DOMAIN}
SetEnv ENKAF_REVIEW_MODE ${REVIEW_MODE}
SetEnv ENKAF_DATA_DIR ${PRIVATE_ROOT}
EOF

printf '%s\n' "$COMMIT" > "$DOMAIN_ROOT/.enkaf-release"
printf '%s\n' "$COMMIT" > "$PUBLIC_ROOT/.enkaf-release"
chmod 644 "$PUBLIC_ROOT/.enkaf-release"

HEALTH="$(curl -fLsS --max-time 20 "https://${DOMAIN}/healthz/" || true)"
if ! printf '%s' "$HEALTH" | grep -Fq '"ok":true' || ! printf '%s' "$HEALTH" | grep -Fq "$COMMIT"; then
  echo "ENKAF_DEPLOY_WARNING health_check_needs_manual_verification commit=${COMMIT}"
  echo "ENKAF_DEPLOY_BACKUP ${BACKUP}"
  exit 4
fi

echo "ENKAF_DEPLOY_OK commit=${COMMIT} review_mode=${REVIEW_MODE} backup=${BACKUP}"
