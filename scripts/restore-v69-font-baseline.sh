#!/usr/bin/env bash
set -euo pipefail

ROOT="/home/u128565677/domains/enkaf.sa"
WORK="$ROOT/.v69-font-restore"
ZIP="$WORK/enkaf-v68-release.zip"
SRC="$WORK/enkaf_v68_release"
V68_URL="https://drive.google.com/uc?export=download&id=1DjG_qq2ieX4CmkjbS4bbO1IjKlbwXbBI"
FONT_URL="https://raw.githubusercontent.com/marketinghorizonssa-alt/Enkaf/8c6db5379ca0f7bbe12ca0f3f6d9966d1a85bf5e/public/assets/css/brand-fonts-v69.css"
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$ROOT/enkaf-backups"
tar -czf "$ROOT/enkaf-backups/before-v69-font-baseline-$STAMP.tar.gz" -C "$ROOT" app public_html

rm -rf "$WORK"
mkdir -p "$WORK"
curl -LfsS "$V68_URL" -o "$ZIP"
unzip -t "$ZIP" >/dev/null
unzip -q "$ZIP" -d "$WORK"

test -s "$SRC/app/views.php"
test -s "$SRC/public_html/assets/js/site.js"
test -s "$SRC/public_html/assets/css/service-visuals-v61.css"
COUNT="$(find "$SRC/public_html/assets/img/nkf/v68" -type f -name '*.webp' | wc -l | tr -d ' ')"
[ "$COUNT" = "18" ]

cp -a "$SRC/app/views.php" "$ROOT/app/views.php"
cp -a "$SRC/public_html/assets/." "$ROOT/public_html/assets/"

curl -LfsS "$FONT_URL" -o "$ROOT/public_html/assets/css/brand-fonts-v69.css"
grep -Fq 'ENKAF-V6.9-BRAND-FONTS' "$ROOT/public_html/assets/css/brand-fonts-v69.css"

if ! grep -Fq 'brand-fonts-v69.css' "$ROOT/app/views.php"; then
  sed -i "/luxury-v6.css/a\\        . '<link rel=\"stylesheet\" href=\"/assets/css/brand-fonts-v69.css?v=' . e(asset_version()) . '\">'" "$ROOT/app/views.php"
fi

grep -Fq 'brand-fonts-v69.css' "$ROOT/app/views.php"
grep -Fq 'Enkaf DIN' "$ROOT/public_html/assets/css/brand-fonts-v69.css"
grep -Fq 'Enkaf Seraphim' "$ROOT/public_html/assets/css/brand-fonts-v69.css"
grep -Fq 'v68/home-hero.webp' "$ROOT/app/views.php"
COUNT2="$(find "$ROOT/public_html/assets/img/nkf/v68" -type f -name '*.webp' | wc -l | tr -d ' ')"
[ "$COUNT2" = "18" ]

php -l "$ROOT/app/views.php" >/dev/null
rm -rf "$WORK"
echo "ENKAF_V69_FONT_BASELINE_OK images=$COUNT2"
