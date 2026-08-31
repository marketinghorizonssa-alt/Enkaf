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
REQUIRED_ASSETS=(
  hero-home.webp hero-general.webp hero-corporate.webp hero-disputes.webp hero-ip.webp hero-realestate.webp
  section-strategy.webp section-work.webp
  motif-scale.svg motif-corporate.svg motif-disputes.svg motif-ip.svg motif-realestate.svg
)

assets_ok() {
  local root="$1"
  local asset
  for asset in "${REQUIRED_ASSETS[@]}"; do
    [ -s "$root/assets/img/$asset" ] || return 1
  done
  return 0
}

css_sources_ok() {
  local root="$1"
  [ -s "$root/assets/css/site.css" ] \
    && [ -s "$root/assets/css/luxury-v6.css" ] \
    && [ -s "$root/assets/css/enhancements.css" ] \
    && [ -s "$root/assets/css/brand-fonts-v69.css" ]
}

css_bundle_ok() {
  local root="$1"
  local bundle="$root/assets/css/enkaf-bundle.css"
  [ -s "$bundle" ] \
    && grep -Fq 'ENKAF V6' "$bundle" \
    && grep -Fq 'ENKAF content/SEO enhancement layer' "$bundle" \
    && grep -Fq 'ENKAF-V6.9-BRAND-FONTS' "$bundle"
}

app_runtime_ok() {
  local root="$1"
  [ -s "$root/enhancements.php" ] \
    && grep -Fq 'function authority_context' "$root/enhancements.php" \
    && grep -Fq 'وزارة التجارة' "$root/enhancements.php" \
    && grep -Fq 'الهيئة السعودية للملكية الفكرية' "$root/enhancements.php" \
    && grep -Fq 'function about_page_html' "$root/enhancements.php"
}

build_css_bundle() {
  local root="$1"
  local bundle="$root/assets/css/enkaf-bundle.css"
  {
    cat "$root/assets/css/site.css"
    printf '\n'
    cat "$root/assets/css/luxury-v6.css"
    printf '\n'
    cat "$root/assets/css/enhancements.css"
    printf '\n'
    cat "$root/assets/css/brand-fonts-v69.css"
    printf '\n'
  } > "$bundle"
  css_bundle_ok "$root"
}

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
  if assets_ok "$PUBLIC_ROOT" && css_bundle_ok "$PUBLIC_ROOT" && app_runtime_ok "$APP_ROOT"; then
    echo "ENKAF_DEPLOY_ALREADY_OK commit=${COMMIT} assets=ok css_bundle=complete app_runtime=complete"
    exit 0
  fi
  echo "ENKAF_DEPLOY_REPAIR commit=${COMMIT} reason=incomplete_runtime"
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
if ! assets_ok "$SRC/public"; then
  echo "ENKAF_DEPLOY_ERROR source_visual_assets_missing"
  exit 3
fi
if ! css_sources_ok "$SRC/public"; then
  echo "ENKAF_DEPLOY_ERROR source_css_missing"
  exit 3
fi
if ! app_runtime_ok "$SRC/app"; then
  echo "ENKAF_DEPLOY_ERROR source_app_enhancements_missing"
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

if ! assets_ok "$PUBLIC_ROOT"; then
  echo "ENKAF_DEPLOY_ERROR deployed_visual_assets_missing"
  rollback
  exit 4
fi
if ! css_sources_ok "$PUBLIC_ROOT" || ! build_css_bundle "$PUBLIC_ROOT"; then
  echo "ENKAF_DEPLOY_ERROR css_bundle_failed"
  rollback
  exit 4
fi
if ! app_runtime_ok "$APP_ROOT"; then
  echo "ENKAF_DEPLOY_ERROR deployed_app_enhancements_missing"
  rollback
  exit 4
fi

cat >> "$PUBLIC_ROOT/.htaccess" <<EOF

# ENKAF deployment environment (generated from recorded GitHub commit)
SetEnv ENKAF_SITE_URL https://${DOMAIN}
SetEnv ENKAF_REVIEW_MODE ${REVIEW_MODE}
SetEnv ENKAF_DATA_DIR ${PRIVATE_ROOT}
EOF

printf '%s\n' "$COMMIT" > "$DOMAIN_ROOT/.enkaf-release"
printf '%s\n' "$COMMIT" > "$PUBLIC_ROOT/.enkaf-release"
printf '%s\n' "$COMMIT" > "$PUBLIC_ROOT/enkaf-release.txt"
chmod 644 "$PUBLIC_ROOT/.enkaf-release" "$PUBLIC_ROOT/enkaf-release.txt"

HEALTH="$(ENKAF_SITE_URL="https://${DOMAIN}" ENKAF_REVIEW_MODE="$REVIEW_MODE" ENKAF_DATA_DIR="$PRIVATE_ROOT" REQUEST_URI='/healthz/' REQUEST_METHOD='GET' php "$PUBLIC_ROOT/index.php" 2>/dev/null || true)"
RELEASE="$(cat "$DOMAIN_ROOT/.enkaf-release" 2>/dev/null || true)"
if ! printf '%s' "$HEALTH" | grep -Fq '"ok":true' || [ "$(printf '%s' "$RELEASE" | tr -d '\r\n')" != "$COMMIT" ] || ! css_bundle_ok "$PUBLIC_ROOT" || ! app_runtime_ok "$APP_ROOT"; then
  echo "ENKAF_DEPLOY_ERROR local_verification_failed commit=${COMMIT}"
  rollback
  exit 4
fi

echo "ENKAF_DEPLOY_OK commit=${COMMIT} review_mode=${REVIEW_MODE} assets=ok css_bundle=complete app_runtime=complete backup=${BACKUP}"
