#!/usr/bin/env bash
set -euo pipefail

ROOT="/home/u128565677/domains/enkaf.sa"
PUBLIC="$ROOT/public_html"
INDEX="$PUBLIC/index.php"
BACKUPS="$ROOT/enkaf-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="$ROOT/.entity-logos-v2-$STAMP"
NEW_INDEX="$WORK/index.new.php"

mkdir -p "$WORK" "$BACKUPS"
cp -a "$INDEX" "$BACKUPS/index-before-entity-logos-v2-$STAMP.php"

cat > "$WORK/patch.php" <<'PHP'
<?php
$src = $argv[1];
$dst = $argv[2];
$s = file_get_contents($src);
if ($s === false) exit(10);
if (strpos($s, 'ENKAF_ENTITY_LOGOS_V2') === false) {
    $needle = '$cfg = site_config();';
    $inject = <<<'CODE'
// ENKAF_ENTITY_LOGOS_V2: render entity logos in the HTML itself so abbreviations never flash or remain visible.
ob_start(function (string $html): string {
    $logoMap = [
        'MOJ' => 'moj.png', 'NOTARY' => 'moj.png', 'COURT' => 'moj.png', 'CT' => 'moj.png', 'EXEC' => 'moj.png',
        'NAJIZ' => 'najiz.png',
        'MC' => 'mc.png', 'MOC' => 'mc.png',
        'SBC' => 'sbc.png',
        'MISA' => 'misa.png', 'INV' => 'misa.png', 'INVEST' => 'misa.png',
        'SCCA' => 'scca.png',
        'SAIP' => 'saip.png', 'GCC' => 'saip.png',
        'RER' => 'rer.png', 'REGA' => 'rega.png', 'REX' => 'rex.png',
        'ENF' => 'infath.png', 'INFATH' => 'infath.png',
        'LAW' => 'law.svg', 'GOV' => 'gov.svg',
    ];
    $codes = implode('|', array_map(static fn($v) => preg_quote($v, '~'), array_keys($logoMap)));
    $pattern = '~<(span|b|strong|em|i|small)([^>]*)>\s*(' . $codes . ')\s*</\\1>~iu';
    $html = preg_replace_callback($pattern, static function(array $m) use ($logoMap): string {
        $code = strtoupper(trim($m[3]));
        if (!isset($logoMap[$code])) return $m[0];
        $file = $logoMap[$code];
        $generic = ($file === 'law.svg' || $file === 'gov.svg') ? ' data-generic="1"' : '';
        return '<span class="enkaf-entity-logo-slot" aria-hidden="true"><img src="/assets/img/entities/'
            . htmlspecialchars($file, ENT_QUOTES, 'UTF-8')
            . '" alt="" loading="lazy" decoding="async"' . $generic . '></span>';
    }, $html) ?? $html;

    if (stripos($html, 'entity-logos-v1.css') === false && stripos($html, '</head>') !== false) {
        $html = str_replace('</head>', '<link rel="stylesheet" href="/assets/css/entity-logos-v1.css?v=20260824-2"></head>', $html);
    }
    if (stripos($html, 'entity-logos-v1.js') === false && stripos($html, '</body>') !== false) {
        $html = str_replace('</body>', '<script src="/assets/js/entity-logos-v1.js?v=20260824-2" defer></script></body>', $html);
    }
    return $html;
});

$cfg = site_config();
CODE;
    if (strpos($s, $needle) === false) exit(11);
    $s = str_replace($needle, $inject, $s, $count);
    if ($count !== 1) exit(12);
}
if (file_put_contents($dst, $s) === false) exit(13);
PHP

php "$WORK/patch.php" "$INDEX" "$NEW_INDEX"
php -l "$NEW_INDEX" >/dev/null
grep -Fq 'ENKAF_ENTITY_LOGOS_V2' "$NEW_INDEX"

REQUEST_URI='/محامي-شركات-وتأسيس-شركات/' REQUEST_METHOD='GET' ENKAF_REVIEW_MODE='false' php "$NEW_INDEX" > "$WORK/corporate.html"
REQUEST_URI='/محامي-عقاري-وتوثيق-عقود/' REQUEST_METHOD='GET' ENKAF_REVIEW_MODE='false' php "$NEW_INDEX" > "$WORK/realestate.html"

grep -Fq '/assets/img/entities/mc.png' "$WORK/corporate.html"
grep -Fq '/assets/img/entities/sbc.png' "$WORK/corporate.html"
grep -Fq '/assets/img/entities/misa.png' "$WORK/corporate.html"
grep -Fq '/assets/img/entities/rex.png' "$WORK/realestate.html"
grep -Fq 'enkaf-entity-logo-slot' "$WORK/corporate.html"
! grep -Eq '>\s*(MC|SBC|INVEST|MISA|LAW)\s*<' "$WORK/corporate.html"
! grep -Eq '>\s*(RER|REX|MOJ|NOTARY|LAW)\s*<' "$WORK/realestate.html"

cp -a "$NEW_INDEX" "$INDEX"
php -l "$INDEX" >/dev/null

echo "ENKAF_ENTITY_LOGOS_V2_OK"
rm -rf "$WORK"
