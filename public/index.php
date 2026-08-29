<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/app/leads.php';
require_once dirname(__DIR__) . '/app/views.php';
require_once dirname(__DIR__) . '/app/enhancements.php';

function performance_ready_html(string $html, string $path): string {
    // Keep the complete V6 visual system, but deliver it as one CSS request.
    // This removes the render-blocking chain created by site.css + enhancements.css + luxury-v6.css.
    $cleaned = preg_replace(
        '#<link[^>]+href="/assets/css/(?:site|enhancements|luxury-v6)\.css(?:\?[^\"]*)?"[^>]*>#',
        '',
        $html
    );
    if (is_string($cleaned)) $html = $cleaned;
    $bundle = '<link rel="preload" href="/assets/css/enkaf-bundle.css?v=20260829-3" as="style">'
        . '<link rel="stylesheet" data-enkaf-v6="1" href="/assets/css/enkaf-bundle.css?v=20260829-3">';
    if (!str_contains($html, '/assets/css/enkaf-bundle.css')) {
        $html = str_replace('</head>', $bundle . '</head>', $html);
    }

    $visuals = [
        '/' => ['/assets/img/hero-home.webp', '/assets/img/hero-general.webp'],
        '/محامي-واستشارات-قانونية/' => ['/assets/img/hero-general.webp', '/assets/img/hero-home.webp'],
        '/محامي-شركات-وتأسيس-شركات/' => ['/assets/img/hero-corporate.webp', '/assets/img/hero-general.webp'],
        '/قضايا-تجارية-وتحصيل-ديون/' => ['/assets/img/hero-disputes.webp', '/assets/img/hero-home.webp'],
        '/تسجيل-علامة-تجارية-والملكية-الفكرية/' => ['/assets/img/hero-ip.webp', '/assets/img/hero-corporate.webp'],
        '/محامي-عقاري-وتوثيق-عقود/' => ['/assets/img/hero-realestate.webp', '/assets/img/hero-corporate.webp'],
    ];
    if (isset($visuals[$path])) {
        [$office, $digital] = $visuals[$path];
        $html = str_replace('/assets/img/section-strategy.webp', $office, $html);
        $html = str_replace('/assets/img/section-work.webp', $digital, $html);
    }

    if ($path === '/') {
        $html = strtr($html, [
            'إنكاف للمحاماة والاستشارات القانونية | مكتب محاماة سعودي في جدة' => 'إنكاف للمحاماة والاستشارات القانونية | خبرة سعودية ورؤية حديثة',
            'مكتب محاماة واستشارات قانونية سعودي في جدة يقدم خدمات الشركات والعقود والقضايا التجارية والتحصيل والملكية الفكرية والعقار للأفراد والشركات.' => 'إنكاف مكتب محاماة سعودي في جدة يجمع خبرات قانونية ممتدة في السوق السعودي مع أسلوب حديث وتقني في الاستشارات والتمثيل والخدمات القانونية للأفراد والشركات.',
            'ممارسة قانونية بمعيار يليق بقراراتك وأعمالك' => 'نخبة من المحامين بكفاءة عالية وخبرة سعودية برؤية حديثة',
            'إنكاف مكتب محاماة واستشارات قانونية سعودي في جدة. يعمل فريقنا مع الشركات والأفراد في الشركات والعقود، القضايا التجارية، التحصيل والتنفيذ، الملكية الفكرية، والعقار، باجتماعات حضورية أو عن بُعد ومتابعة رقمية منظمة.' => 'يجمع إنكاف نخبة من المحامين ذوي الكفاءة والخبرات الممتدة في السوق السعودي مع رؤية واضحة وحديثة تعتمد على التقنية والتنظيم والعمل الحضوري وعن بُعد؛ لتقديم استشارات وتمثيل وصياغة قانونية بجودة عالية وتواصل أكثر وضوحًا للأفراد والشركات.',
            'استشارات وتمثيل قانوني' => 'خبرات ممتدة في السوق السعودي',
            'شركات وعقود ونزاعات' => 'فريق قانوني متعدد التخصصات',
            'تحصيل وتنفيذ وحقوق فكرية' => 'تواصل ومتابعة رقمية منظمة',
            '<span class="section-label">نطاق الخدمة</span>' => '<span class="section-label">لماذا إنكاف</span>',
            'قوة الموقف تبدأ من قراءة دقيقة للملف' => 'خبرة قانونية راسخة بمنهج عمل يواكب احتياجك اليوم',
            'نقرأ الملف في سياقه النظامي والتجاري، ثم نحدد نطاق العمل المناسب: استشارة، عقد، مطالبة، تمثيل، أو إجراء نظامي.' => 'الفخامة في إنكاف ليست مظهرًا فقط؛ بل مستوى في القراءة القانونية، جودة الصياغة، وضوح التواصل، وتنظيم المتابعة من أول تواصل وحتى تنفيذ نطاق العمل المتفق عليه.',
            'الشركات والعقود' => 'خبرة في السوق السعودي',
            'تأسيس الشركات، العقود التجارية، الحوكمة والاستشارات القانونية المرتبطة بأعمال المنشأة.' => 'فهم للأنظمة وبيئة الأعمال والسياق المحلي يساعد على قراءة الملف ضمن واقعه العملي.',
            'القضايا والمطالبات' => 'كفاءة وتخصص',
            'إدارة النزاعات التجارية، الترافع، المطالبات المالية، التنفيذ والتحكيم وفق مستندات كل ملف.' => 'توجيه الطلب إلى التخصص القانوني الأنسب مع ربط الرأي بالمستندات والوقائع والهدف.',
            'العلامات والملكية الفكرية' => 'رؤية حديثة وتقنية',
            'تسجيل العلامات التجارية ودعم حماية الحقوق الفكرية والعقود المرتبطة بها.' => 'استخدام أدوات تنظيم ومتابعة رقمية لتسهيل التواصل وتبادل المعلومات ومتابعة الطلب.',
            'العقار والتوثيق' => 'خدمة للأفراد والشركات',
            'مراجعة العقود والصفقات والمنازعات والخدمات القانونية المرتبطة بالتوثيق والتسجيل العقاري.' => 'دعم قانوني من الاستشارة والعقد إلى النزاع والمطالبة والملكية الفكرية والعقار.',
        ]);
    }

    return $html;
}

$cfg = site_config();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self' https://www.googletagmanager.com 'unsafe-inline'; connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://www.googleadservices.com; frame-src https://www.googletagmanager.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
if ($cfg['review_mode']) header('X-Robots-Tag: noindex, nofollow');

$rawPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$path = normalize_path($rawPath);

if ($path === '/api/lead/') handle_lead_submission();
if ($path === '/api/leads.csv/') handle_lead_feed();

if ($path === '/healthz/') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode([
        'ok' => true,
        'service' => 'enkaf-landing-site',
        'build' => BUILD_ID . '-v5-legal-about-seo-perf-bundle',
        'design' => 'legal-conversion-v6',
        'review_mode' => $cfg['review_mode'],
        'gtm_configured' => $cfg['gtm_id'] !== '',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($path === '/robots.txt/') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache, max-age=0');
    if ($cfg['review_mode']) {
        echo "User-agent: *\nDisallow: /\n";
    } else {
        $sitemap = $cfg['site_url'] . '/sitemap.xml';
        echo "User-agent: Google-InspectionTool\nDisallow:\n\n";
        echo "User-agent: Googlebot\nDisallow:\n\n";
        echo "User-agent: Googlebot-Image\nDisallow:\n\n";
        echo "User-agent: *\nDisallow:\n\n";
        echo "Sitemap: {$sitemap}\n";
    }
    exit;
}

if ($path === '/sitemap.xml/') {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: no-cache, max-age=0');
    $urls = array_keys(page_catalog());
    $urls[] = '/من-نحن/';
    $urls[] = '/سياسة-الخصوصية/';
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        echo '  <url><loc>' . htmlspecialchars(absolute_url($u), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc><lastmod>2026-08-29</lastmod></url>' . "\n";
    }
    echo '</urlset>';
    exit;
}

if ($path === '/سياسة-الخصوصية/') {
    echo performance_ready_html(enhance_site_html(privacy_html(), $path), $path);
    exit;
}
if ($path === '/شكرا/') {
    echo performance_ready_html(enhance_site_html(thank_you_html(), $path), $path);
    exit;
}
if ($path === '/من-نحن/') {
    echo performance_ready_html(enhance_site_html(about_page_html(), $path), $path);
    exit;
}

$catalog = page_catalog();
if (isset($catalog[$path])) {
    echo performance_ready_html(enhance_site_html(page_html($catalog[$path]), $path), $path);
    exit;
}

http_response_code(404);
echo performance_ready_html(enhance_site_html(not_found_html(), $path);
