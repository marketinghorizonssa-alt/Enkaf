#!/usr/bin/env bash
set -euo pipefail

COMMIT="${1:-}"
if ! printf '%s' "$COMMIT" | grep -Eq '^[0-9a-f]{40}$'; then
  echo "ENKAF_SITE_UPDATE_ERROR invalid_commit"
  exit 2
fi

ROOT="/home/u128565677/domains/enkaf.sa"
PUBLIC="$ROOT/public_html"
APP="$ROOT/app"
BACKUPS="$ROOT/enkaf-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="$ROOT/.enkaf-site-update-$STAMP"
V68_ZIP="$WORK/enkaf-v68-release.zip"
V68_SRC="$WORK/enkaf_v68_release"
BACKUP="$BACKUPS/before-site-update-${COMMIT:0:12}-$STAMP.tar.gz"
V68_URL="https://drive.google.com/uc?export=download&id=1DjG_qq2ieX4CmkjbS4bbO1IjKlbwXbBI"
RAW_BASE="https://raw.githubusercontent.com/marketinghorizonssa-alt/Enkaf/$COMMIT"
FONT_URL="https://raw.githubusercontent.com/marketinghorizonssa-alt/Enkaf/8c6db5379ca0f7bbe12ca0f3f6d9966d1a85bf5e/public/assets/css/brand-fonts-v69.css"

mkdir -p "$WORK" "$BACKUPS"
cleanup(){ rm -rf "$WORK"; }
trap cleanup EXIT

tar --warning=no-file-changed --ignore-failed-read -czf "$BACKUP" -C "$ROOT" app public_html
[ -s "$BACKUP" ] || { echo "ENKAF_SITE_UPDATE_ERROR backup"; exit 3; }

curl -LfsS --retry 3 --connect-timeout 15 "$V68_URL" -o "$V68_ZIP"
unzip -t "$V68_ZIP" >/dev/null
unzip -q "$V68_ZIP" -d "$WORK"

mkdir -p "$WORK/payload" "$V68_SRC/public_html/assets/css"
for n in 00 01 02 03 04 05; do
  curl -fsSL --retry 3 --connect-timeout 15 "$RAW_BASE/scripts/payload-v69/views.part.$n" -o "$WORK/payload/views.part.$n"
done
cat "$WORK"/payload/views.part.* | base64 -d | gzip -d > "$V68_SRC/app/views.php"

curl -fsSL --retry 3 --connect-timeout 15 "$RAW_BASE/scripts/payload-v69/index.b64" -o "$WORK/payload/index.b64"
base64 -d "$WORK/payload/index.b64" | gzip -d > "$V68_SRC/public_html/index.php"

curl -fsSL --retry 3 --connect-timeout 15 "$RAW_BASE/scripts/payload-v69/css.b64" -o "$WORK/payload/css.b64"
base64 -d "$WORK/payload/css.b64" | gzip -d > "$V68_SRC/public_html/assets/css/enkaf-updates-20260824.css"

curl -LfsS --retry 3 --connect-timeout 15 "$FONT_URL" -o "$V68_SRC/public_html/assets/css/brand-fonts-v69.css"

php -l "$V68_SRC/app/views.php" >/dev/null
php -l "$V68_SRC/public_html/index.php" >/dev/null
grep -Fq 'brand-fonts-v69.css' "$V68_SRC/app/views.php"
grep -Fq 'enkaf-updates-20260824.css' "$V68_SRC/app/views.php"
grep -Fq 'href="/من-نحن/">من نحن</a></nav>' "$V68_SRC/app/views.php"
grep -Fq 'الهيئة السعودية للملكية الفكرية (SAIP)' "$V68_SRC/app/views.php"
grep -Fq 'المركز السعودي للتحكيم التجاري (SCCA)' "$V68_SRC/app/views.php"
grep -Fq 'نظام الشركات' "$V68_SRC/app/views.php"
grep -Fq 'نظام التسجيل العيني للعقار' "$V68_SRC/app/views.php"
COUNT="$(find "$V68_SRC/public_html/assets/img/nkf/v68" -type f -name '*.webp' | wc -l | tr -d ' ')"
[ "$COUNT" = "18" ]

cp -a "$V68_SRC/app/views.php" "$APP/views.php"
cp -a "$V68_SRC/public_html/index.php" "$PUBLIC/index.php"
cp -a "$V68_SRC/public_html/assets/." "$PUBLIC/assets/"

php -l "$APP/views.php" >/dev/null
php -l "$PUBLIC/index.php" >/dev/null
COUNT2="$(find "$PUBLIC/assets/img/nkf/v68" -type f -name '*.webp' | wc -l | tr -d ' ')"
[ "$COUNT2" = "18" ]
[ -s "$PUBLIC/assets/css/brand-fonts-v69.css" ]
[ -s "$PUBLIC/assets/css/enkaf-updates-20260824.css" ]
grep -Fq 'left:18px!important;right:auto!important' "$PUBLIC/assets/css/enkaf-updates-20260824.css"
grep -Fq 'function about_page_html' "$APP/views.php"
! grep -Fq 'team-section' "$APP/views.php"

REQUEST_URI='/' REQUEST_METHOD='GET' ENKAF_REVIEW_MODE='false' php "$PUBLIC/index.php" > "$WORK/home.html"
REQUEST_URI='/من-نحن/' REQUEST_METHOD='GET' ENKAF_REVIEW_MODE='false' php "$PUBLIC/index.php" > "$WORK/about.html"
grep -Fq '/assets/img/nkf/v68/home-hero.webp' "$WORK/home.html"
grep -Fq 'brand-fonts-v69.css' "$WORK/home.html"
grep -Fq 'enkaf-updates-20260824.css' "$WORK/home.html"
grep -Fq '>من نحن</a></nav>' "$WORK/home.html"
grep -Fq 'موقع المكتب' "$WORK/home.html"
grep -Fq '<h1>من نحن</h1>' "$WORK/about.html"
grep -Fq 'قيم إنكاف' "$WORK/about.html"
grep -Fq 'خرائط Google' "$WORK/about.html"

printf '%s\n' "$COMMIT" > "$ROOT/.enkaf-site-update"
echo "ENKAF_SITE_UPDATE_OK commit=$COMMIT images=$COUNT2 backup=$BACKUP"
