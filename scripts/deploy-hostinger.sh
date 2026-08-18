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
BACKUP="${BACKUP_ROOT}/before-${COMMIT:0:12}-${STAMP}.tar.gz"
LOCK="${DOMAIN_ROOT}/.enkaf-deploy.lock"
HAD_PUBLIC=0
HAD_APP=0

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

exec 9>"$LOCK"
if command -v flock >/dev/null 2>&1 && ! flock -n 9; then
  echo "ENKAF_DEPLOY_SKIP locked"
  exit 0
fi

if [ -f "$DOMAIN_ROOT/.enkaf-release" ] && [ "$(tr -d '\r\n' < "$DOMAIN_ROOT/.enkaf-release")" = "$COMMIT" ]; then
  echo "ENKAF_DEPLOY_ALREADY_OK commit=${COMMIT}"
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
if [ -z "$SRC" ] || [ ! -f "$SRC/public/index.php" ] || [ ! -f "$SRC/app/config.php" ] || [ ! -f "$SRC/scripts/validate.sh" ]; then
  echo "ENKAF_DEPLOY_ERROR incomplete_source"
  exit 3
fi

if command -v node >/dev/null 2>&1; then
  bash "$SRC/scripts/validate.sh"
else
  find "$SRC/app" "$SRC/public" -type f -name '*.php' -print0 | while IFS= read -r -d '' f; do php -l "$f" >/dev/null; done
  if grep -RniE 'arkan|shaghel|easttwist|GTM-P5J6D6ND|AW-18127536852|AW-18303013990' "$SRC/app" "$SRC/public" "$SRC/docs"; then
    echo "ENKAF_DEPLOY_ERROR cross_client_token"
    exit 3
  fi
  echo "ENKAF_SERVER_VALIDATE_OK"
fi

if [ -d "$PUBLIC_ROOT" ]; then HAD_PUBLIC=1; fi
if [ -d "$APP_ROOT" ]; then HAD_APP=1; fi
if [ "$HAD_PUBLIC" -eq 1 ] || [ "$HAD_APP" -eq 1 ]; then
  ITEMS=""
  [ "$HAD_PUBLIC" -eq 1 ] && ITEMS="public_html"
  [ "$HAD_APP" -eq 1 ] && ITEMS="${ITEMS} app"
  # The legacy site can mutate cache/upload files while the archive is being created.
  # Keep the backup usable without treating benign file-changed warnings as a deploy failure.
  # shellcheck disable=SC2086
  tar --warning=no-file-changed --ignore-failed-read -czf "$BACKUP" -C "$DOMAIN_ROOT" $ITEMS
  if [ ! -s "$BACKUP" ]; then
    echo "ENKAF_DEPLOY_ERROR backup_missing"
    exit 3
  fi
fi

rollback() {
  echo "ENKAF_DEPLOY_ROLLBACK starting"
  rm -rf "$PUBLIC_ROOT" "$APP_ROOT"
  if [ -f "$BACKUP" ]; then
    tar -xzf "$BACKUP" -C "$DOMAIN_ROOT"
  else
    [ "$HAD_PUBLIC" -eq 1 ] && mkdir -p "$PUBLIC_ROOT"
    [ "$HAD_APP" -eq 1 ] && mkdir -p "$APP_ROOT"
  fi
  rm -f "$DOMAIN_ROOT/.enkaf-release"
  echo "ENKAF_DEPLOY_ROLLBACK done backup=${BACKUP}"
}

mkdir -p "$PUBLIC_ROOT" "$APP_ROOT"
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

sleep 2
HEALTH="$(curl -fLsS --max-time 20 "https://${DOMAIN}/healthz/" || true)"
RELEASE="$(curl -fLsS --max-time 20 "https://${DOMAIN}/.enkaf-release" || true)"
if ! printf '%s' "$HEALTH" | grep -Fq '"ok":true' || [ "$(printf '%s' "$RELEASE" | tr -d '\r\n')" != "$COMMIT" ]; then
  echo "ENKAF_DEPLOY_ERROR live_verification_failed commit=${COMMIT}"
  rollback
  exit 4
fi

echo "ENKAF_DEPLOY_OK commit=${COMMIT} review_mode=${REVIEW_MODE} backup=${BACKUP}"
