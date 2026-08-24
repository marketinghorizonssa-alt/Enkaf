#!/usr/bin/env bash
set -euo pipefail

ROOT="/home/u128565677/domains/enkaf.sa"
WORK="$ROOT/.v68-restore"
ZIP="$WORK/enkaf-v68-release.zip"
SRC="$WORK/enkaf_v68_release"
URL="https://drive.google.com/uc?export=download&id=1DjG_qq2ieX4CmkjbS4bbO1IjKlbwXbBI"

rm -rf "$WORK"
mkdir -p "$WORK"
curl -LfsS "$URL" -o "$ZIP"
unzip -t "$ZIP" >/dev/null
unzip -q "$ZIP" -d "$WORK"

test -s "$SRC/app/views.php"
test -s "$SRC/public_html/assets/js/site.js"
test -s "$SRC/public_html/assets/css/service-visuals-v61.css"
test -s "$SRC/public_html/assets/img/nkf/v68/home-hero.webp"
COUNT="$(find "$SRC/public_html/assets/img/nkf/v68" -type f -name '*.webp' | wc -l | tr -d ' ')"
[ "$COUNT" = "18" ]
grep -Fq 'نخبة من المحامين بكفاءة عالية وخبرة سعودية برؤية حديثة' "$SRC/app/views.php"
grep -Fq 'ENKAF V6.8' "$SRC/public_html/assets/css/service-visuals-v61.css"

cp -a "$SRC/app/views.php" "$ROOT/app/views.php"
cp -a "$SRC/public_html/assets/." "$ROOT/public_html/assets/"

COUNT2="$(find "$ROOT/public_html/assets/img/nkf/v68" -type f -name '*.webp' | wc -l | tr -d ' ')"
[ "$COUNT2" = "18" ]
grep -Fq 'نخبة من المحامين بكفاءة عالية وخبرة سعودية برؤية حديثة' "$ROOT/app/views.php"
grep -Fq 'ENKAF V6.8' "$ROOT/public_html/assets/css/service-visuals-v61.css"
test -s "$ROOT/public_html/assets/img/nkf/v68/home-hero.webp"

rm -rf "$WORK"
echo "ENKAF_V68_RESTORE_OK images=$COUNT2"
