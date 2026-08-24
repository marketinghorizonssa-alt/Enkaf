#!/usr/bin/env bash
set -euo pipefail

COMMIT="${1:-}"
DOMAIN_ROOT="/home/u128565677/domains/enkaf.sa"
PUBLIC_ROOT="${DOMAIN_ROOT}/public_html"
APP_ROOT="${DOMAIN_ROOT}/app"
BACKUP_ROOT="${DOMAIN_ROOT}/enkaf-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="${DOMAIN_ROOT}/.enkaf-v6-repair-${STAMP}"
ARCHIVE="${WORK}/source.tar.gz"
BACKUP="${BACKUP_ROOT}/repair-before-${COMMIT:0:12}-${STAMP}.tar.gz"

if ! printf '%s' "$COMMIT" | grep -Eq '^[0-9a-f]{40}$'; then
  echo "ENKAF_V6_REPAIR_ERROR invalid_commit"
  exit 2
fi

mkdir -p "$WORK" "$BACKUP_ROOT"
cleanup(){ rm -rf "$WORK"; }
trap cleanup EXIT

curl -fLsS --retry 3 --connect-timeout 15 \
  "https://codeload.github.com/marketinghorizonssa-alt/Enkaf/tar.gz/${COMMIT}" \
  -o "$ARCHIVE"
tar -xzf "$ARCHIVE" -C "$WORK"
SRC="$(find "$WORK" -mindepth 1 -maxdepth 1 -type d -name 'Enkaf-*' | head -n 1)"

[ -n "$SRC" ] || { echo "ENKAF_V6_REPAIR_ERROR source_missing"; exit 3; }
[ -f "$SRC/public/index.php" ] || { echo "ENKAF_V6_REPAIR_ERROR index_missing"; exit 3; }
[ -f "$SRC/app/enhancements.php" ] || { echo "ENKAF_V6_REPAIR_ERROR enhancements_missing"; exit 3; }
[ -f "$SRC/public/assets/js/site.js" ] || { echo "ENKAF_V6_REPAIR_ERROR site_js_missing"; exit 3; }
[ -s "$SRC/public/assets/css/luxury-v6.css" ] || { echo "ENKAF_V6_REPAIR_ERROR luxury_v6_missing"; exit 3; }
[ -s "$SRC/public/assets/css/service-visuals-v61.css" ] || { echo "ENKAF_V6_REPAIR_ERROR service_visuals_missing"; exit 3; }

grep -q 'applyV6Presentation' "$SRC/public/assets/js/site.js" || { echo "ENKAF_V6_REPAIR_ERROR source_not_v6"; exit 3; }
grep -q 'نخبة من المحامين بكفاءة عالية وخبرة سعودية برؤية حديثة' "$SRC/public/assets/js/site.js" || { echo "ENKAF_V6_REPAIR_ERROR modern_home_copy_missing"; exit 3; }
grep -q 'google.com/maps/search' "$SRC/app/enhancements.php" || { echo "ENKAF_V6_REPAIR_ERROR map_missing"; exit 3; }
grep -q 'about_page_html' "$SRC/public/index.php" || { echo "ENKAF_V6_REPAIR_ERROR about_route_missing"; exit 3; }

find "$SRC/app" -type f -name '*.php' -print0 | while IFS= read -r -d '' f; do php -l "$f" >/dev/null; done
php -l "$SRC/public/index.php" >/dev/null

mkdir -p "$PUBLIC_ROOT" "$APP_ROOT"
tar -czf "$BACKUP" -C "$DOMAIN_ROOT" public_html app

rm -rf "$PUBLIC_ROOT/assets"
cp -a "$SRC/public/assets" "$PUBLIC_ROOT/"
rm -rf "$APP_ROOT"
cp -a "$SRC/app" "$APP_ROOT"
cp -f "$SRC/public/index.php" "$PUBLIC_ROOT/index.php"

grep -q 'applyV6Presentation' "$PUBLIC_ROOT/assets/js/site.js" || { echo "ENKAF_V6_REPAIR_ERROR installed_not_v6"; exit 4; }
grep -q 'google.com/maps/search' "$APP_ROOT/enhancements.php" || { echo "ENKAF_V6_REPAIR_ERROR installed_map_missing"; exit 4; }
grep -q 'about_page_html' "$PUBLIC_ROOT/index.php" || { echo "ENKAF_V6_REPAIR_ERROR installed_about_missing"; exit 4; }
[ -s "$PUBLIC_ROOT/assets/css/luxury-v6.css" ] || { echo "ENKAF_V6_REPAIR_ERROR installed_luxury_v6_missing"; exit 4; }
[ -s "$PUBLIC_ROOT/assets/css/service-visuals-v61.css" ] || { echo "ENKAF_V6_REPAIR_ERROR installed_visuals_missing"; exit 4; }

printf '%s\n' "$COMMIT" > "$PUBLIC_ROOT/.enkaf-v6-repair"
echo "ENKAF_V6_REPAIR_OK commit=${COMMIT} backup=${BACKUP}"
