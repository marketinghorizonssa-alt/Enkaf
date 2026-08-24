#!/usr/bin/env bash
set -euo pipefail

ROOT="/home/u128565677/domains/enkaf.sa"
APP="$ROOT/app"
VIEWS="$APP/views.php"
BACKUPS="$ROOT/enkaf-backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="$ROOT/.authority-logo-v3-$STAMP"
NEW="$WORK/views.new.php"

mkdir -p "$WORK" "$BACKUPS"
cp -a "$VIEWS" "$BACKUPS/views-before-authority-logo-v3-$STAMP.php"

cat > "$WORK/patch.php" <<'PHP'
<?php
$src = $argv[1];
$dst = $argv[2];
$s = file_get_contents($src);
if ($s === false) exit(10);

$newLinks = <<<'CODE'
function authority_links_for_page(array $page): array {
    $theme = $page['theme'] ?? '';
    $map = [
        'general' => [
            ['وزارة العدل', 'moj.png', 'https://www.moj.gov.sa/'],
            ['منصة ناجز', 'najiz.png', 'https://najiz.sa/'],
            ['المحاكم السعودية', 'moj.png', ''],
            ['كتابة العدل', 'moj.png', ''],
        ],
        'corporate' => [
            ['نظام الشركات', 'mc.png', ''],
            ['وزارة التجارة', 'mc.png', 'https://mc.gov.sa/'],
            ['المركز السعودي للأعمال', 'sbc.png', 'https://business.sa/'],
            ['نظام الاستثمار', 'misa.png', ''],
        ],
        'disputes' => [
            ['المحكمة التجارية', 'moj.png', ''],
            ['نظام المحاكم التجارية', 'moj.png', ''],
            ['المركز السعودي للتحكيم التجاري', 'scca.png', 'https://sadr.org/'],
        ],
        'ip' => [
            ['الهيئة السعودية للملكية الفكرية', 'saip.png', 'https://www.saip.gov.sa/'],
            ['نظام العلامات التجارية لدول مجلس التعاون', 'saip.png', ''],
        ],
        'realestate' => [
            ['وزارة العدل', 'moj.png', 'https://www.moj.gov.sa/'],
            ['كتابة العدل', 'moj.png', ''],
            ['الهيئة العامة للعقار', 'rega.png', 'https://rega.gov.sa/'],
            ['السجل العقاري', 'rer.png', 'https://www.rer.sa/'],
            ['نظام التسجيل العيني للعقار', 'rer.png', ''],
            ['البورصة العقارية', 'rex.png', ''],
            ['مركز الإسناد والتصفية (إنفاذ)', 'infath.png', 'https://infath.gov.sa/'],
        ],
    ];
    return $map[$theme] ?? [];
}
CODE;

$newBadges = <<<'CODE'
function authority_badges_inline(array $page): string {
    $items = authority_links_for_page($page);
    if (!$items) return '';
    $theme = $page['theme'] ?? '';
    $copy = [
        'general' => ['الجهات العدلية المرتبطة بمسار الخدمة', 'نتعامل مع الإجراء لدى الجهة والمنصة المختصة بحسب نوع الطلب ومرحلته.'],
        'corporate' => ['جهات تنظيم وتأسيس الأعمال في المملكة', 'نربط خطوات التأسيس والحوكمة والاستثمار بالجهات والأنظمة المنظمة لكل إجراء.'],
        'disputes' => ['جهات التقاضي والتحكيم والتنفيذ', 'نحدد المسار القضائي أو التحكيمي والتنفيذي وفق طبيعة النزاع ومستنداته.'],
        'ip' => ['جهات حماية وتسجيل الملكية الفكرية', 'نتابع التسجيل والحماية والاعتراض وفق الجهة المختصة والنظام المنطبق على الحق.'],
        'realestate' => ['جهات التوثيق والتسجيل والخدمات العقارية', 'نتعامل مع الإجراءات العقارية لدى الجهات والمنصات المختصة من التوثيق وحتى التسجيل والتصرف.'],
    ];
    [$title, $sub] = $copy[$theme] ?? ['جهات مرتبطة بمسار الخدمة', 'بحسب نوع الطلب والاختصاص والإجراء المطلوب.'];

    $html = '<div class="authority-inline" aria-label="' . e($title) . '"><div class="authority-inline-heading"><span class="authority-inline-icon">' . icon_svg('building') . '</span><div><strong>' . e($title) . '</strong><small>' . e($sub) . '</small></div></div><div class="authority-chips">';
    foreach ($items as [$label, $logo, $url]) {
        $logoHtml = '<span class="enkaf-entity-logo-slot" aria-hidden="true"><img src="/assets/img/entities/' . e($logo) . '" alt="" loading="lazy" decoding="async"></span>';
        $inner = $logoHtml . '<span>' . e($label) . '</span>';
        if ($url !== '') {
            $html .= '<a class="authority-chip" href="' . e($url) . '" target="_blank" rel="noopener">' . $inner . '</a>';
        } else {
            $html .= '<span class="authority-chip">' . $inner . '</span>';
        }
    }
    return $html . '</div></div>';
}
CODE;

$patterns = [
    '~function authority_links_for_page\(array \$page\): array \{.*?\n\}\n\nfunction authority_badges_inline~s' => $newLinks . "\n\nfunction authority_badges_inline",
    '~function authority_badges_inline\(array \$page\): string \{.*?\n\}\n\nfunction footer_html~s' => $newBadges . "\n\nfunction footer_html",
];

foreach ($patterns as $pattern => $replacement) {
    $updated = preg_replace($pattern, $replacement, $s, 1, $count);
    if ($updated === null || $count !== 1) exit(20);
    $s = $updated;
}

if (file_put_contents($dst, $s) === false) exit(30);
PHP

php "$WORK/patch.php" "$VIEWS" "$NEW"
php -l "$NEW" >/dev/null

grep -Fq "['وزارة التجارة', 'mc.png'" "$NEW"
grep -Fq "['المركز السعودي للأعمال', 'sbc.png'" "$NEW"
grep -Fq "['نظام الاستثمار', 'misa.png'" "$NEW"
grep -Fq "['مركز الإسناد والتصفية (إنفاذ)', 'infath.png'" "$NEW"
grep -Fq 'enkaf-entity-logo-slot' "$NEW"
! grep -Fq "['وزارة التجارة', 'MC'" "$NEW"
! grep -Fq "['المركز السعودي للأعمال', 'SBC'" "$NEW"
! grep -Fq "['نظام الاستثمار', 'INVEST'" "$NEW"

cat > "$WORK/test.php" <<PHP
<?php
require '$APP/config.php';
require '$APP/helpers.php';
require '$NEW';
echo authority_badges_inline(['theme' => 'corporate']);
echo "\n---\n";
echo authority_badges_inline(['theme' => 'realestate']);
PHP
php "$WORK/test.php" > "$WORK/out.html"

grep -Fq '/assets/img/entities/mc.png' "$WORK/out.html"
grep -Fq '/assets/img/entities/sbc.png' "$WORK/out.html"
grep -Fq '/assets/img/entities/misa.png' "$WORK/out.html"
grep -Fq '/assets/img/entities/infath.png' "$WORK/out.html"
! grep -Eq '>\s*(MC|SBC|INVEST|LAW|RER|REX|MOJ|NOTARY)\s*<' "$WORK/out.html"

cp -a "$NEW" "$VIEWS"
php -l "$VIEWS" >/dev/null

echo "ENKAF_AUTHORITY_LOGOS_V3_OK"
rm -rf "$WORK"
