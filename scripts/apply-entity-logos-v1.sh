#!/usr/bin/env bash
set -euo pipefail

ROOT="/home/u128565677/domains/enkaf.sa"
PUBLIC="$ROOT/public_html"
INDEX="$PUBLIC/index.php"
BACKUPS="$ROOT/enkaf-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="$ROOT/.entity-logos-$STAMP"
RAW="https://raw.githubusercontent.com/marketinghorizonssa-alt/Enkaf/23cd6131e2912af3d621359e43a9b4df67560c00"

mkdir -p "$WORK" "$BACKUPS" "$PUBLIC/assets/img/entities"
cp -a "$INDEX" "$BACKUPS/index-before-entity-logos-$STAMP.php"

curl -fsSL "$RAW/public/assets/css/entity-logos-v1.css" -o "$PUBLIC/assets/css/entity-logos-v1.css"
curl -fsSL "$RAW/public/assets/js/entity-logos-v1.js" -o "$PUBLIC/assets/js/entity-logos-v1.js"
curl -fsSL "$RAW/public/assets/img/entities/law.svg" -o "$PUBLIC/assets/img/entities/law.svg"
curl -fsSL "$RAW/public/assets/img/entities/gov.svg" -o "$PUBLIC/assets/img/entities/gov.svg"

fetch_icon(){
  local name="$1"
  local domain="$2"
  local out="$PUBLIC/assets/img/entities/$name.png"
  curl -LfsS --retry 2 --connect-timeout 10 --max-time 25 \
    "https://www.google.com/s2/favicons?sz=128&domain_url=https://$domain" -o "$out"
  [ -s "$out" ]
}

fetch_icon moj moj.gov.sa
fetch_icon najiz najiz.sa
fetch_icon mc mc.gov.sa
fetch_icon sbc business.sa
fetch_icon misa misa.gov.sa
fetch_icon scca sadr.org
fetch_icon saip saip.gov.sa
fetch_icon rer rer.sa
fetch_icon rega rega.gov.sa
fetch_icon rex srem.rega.gov.sa
fetch_icon infath infath.gov.sa

cat > "$WORK/patch.php" <<'PHP'
<?php
$f = $argv[1];
$s = file_get_contents($f);
if ($s === false) exit(10);
if (strpos($s, 'ENKAF_ENTITY_LOGOS_V1') === false) {
    $needle = "require_once dirname(__DIR__) . '/app/views.php';";
    $inject = <<<'CODE'
require_once dirname(__DIR__) . '/app/views.php';

// ENKAF_ENTITY_LOGOS_V1: non-destructive visual enhancement for authority badges.
ob_start(function (string $html): string {
    if (stripos($html, '</body>') === false || stripos($html, 'entity-logos-v1.js') !== false) return $html;
    $assets = '<link rel="stylesheet" href="/assets/css/entity-logos-v1.css?v=20260824-1">'
        . '<script src="/assets/js/entity-logos-v1.js?v=20260824-1" defer></script>';
    return str_replace('</body>', $assets . '</body>', $html);
});
CODE;
    if (strpos($s, $needle) === false) exit(11);
    $s = str_replace($needle, $inject, $s, $count);
    if ($count !== 1) exit(12);
    if (file_put_contents($f, $s) === false) exit(13);
}
PHP

php "$WORK/patch.php" "$INDEX"
php -l "$INDEX" >/dev/null

for f in moj najiz mc sbc misa scca saip rer rega rex infath; do
  test -s "$PUBLIC/assets/img/entities/$f.png"
done

test -s "$PUBLIC/assets/js/entity-logos-v1.js"
test -s "$PUBLIC/assets/css/entity-logos-v1.css"
grep -Fq 'ENKAF_ENTITY_LOGOS_V1' "$INDEX"
grep -Fq '.scope-number' "$PUBLIC/assets/css/entity-logos-v1.css"
grep -Fq 'جهات التوثيق والتسجيل والخدمات العقارية' "$PUBLIC/assets/js/entity-logos-v1.js"

REQUEST_URI='/' REQUEST_METHOD='GET' ENKAF_REVIEW_MODE='false' php "$INDEX" > "$WORK/home.html"
grep -Fq 'entity-logos-v1.js?v=20260824-1' "$WORK/home.html"
grep -Fq '/assets/img/nkf/v68/home-hero.webp' "$WORK/home.html"
grep -Fq 'brand-fonts-v69.css' "$WORK/home.html"

rm -rf "$WORK"
echo "ENKAF_ENTITY_LOGOS_OK"
