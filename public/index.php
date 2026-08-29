<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/app/leads.php';
require_once dirname(__DIR__) . '/app/views.php';
require_once dirname(__DIR__) . '/app/enhancements.php';

function performance_ready_html(string $html, string $path): string {
    $cleaned = preg_replace(
        '#<link[^>]+href="/assets/css/(?:site|enhancements|luxury-v6|enkaf-bundle)\\.css(?:\\?[^\\"]*)?"[^>]*>#',
        '',
        $html
    );
    if (is_string($cleaned)) $html = $cleaned;

    $critical = <<<'CSS'
:root{--green:#29332b;--deep:#151d17;--paper:#f1ead8;--cream:#fbf8f1;--sand:#d3bfa6;--umber:#683f18;--burnt:#74391b;--ink:#1d2821;--line:rgba(41,51,43,.14);--font-body:Tahoma,"Segoe UI","Geeza Pro",Arial,sans-serif;--font-display:"Arabic Typesetting","Traditional Arabic","Noto Naskh Arabic","Geeza Pro",serif;--v6-gold:#c6a36d;--container:min(1180px,calc(100% - 40px));--motif:url('/assets/img/motif-scale.svg')}*{box-sizing:border-box}html{background:var(--cream)}body{margin:0;background:var(--cream);color:var(--ink);font-family:var(--font-body);font-size:16px;line-height:1.75;-webkit-font-smoothing:antialiased}body.theme-home,body.theme-general{--motif:url('/assets/img/motif-scale.svg')}body.theme-corporate{--motif:url('/assets/img/motif-corporate.svg');--v6-gold:#cba56d}body.theme-disputes{--motif:url('/assets/img/motif-disputes.svg');--v6-gold:#bd8a5d}body.theme-ip{--motif:url('/assets/img/motif-ip.svg');--v6-gold:#d2b57f}body.theme-realestate{--motif:url('/assets/img/motif-realestate.svg');--v6-gold:#bf9b67}body,button,input,select{font-family:var(--font-body)}a{color:inherit;text-decoration:none}img{display:block;max-width:100%}.container{width:var(--container);margin-inline:auto}svg{width:1.15em;height:1.15em;fill:none;stroke:currentColor;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;vertical-align:-.15em}.site-header{position:sticky;top:0;z-index:80;background:linear-gradient(180deg,rgba(13,21,16,.985),rgba(18,27,21,.965));border-bottom:1px solid rgba(214,184,137,.14);box-shadow:0 12px 34px rgba(4,8,6,.18)}.site-header::after{content:"";position:absolute;right:0;left:0;bottom:-1px;height:1px;background:linear-gradient(90deg,transparent,rgba(211,191,166,.38),transparent);pointer-events:none}.header-row{height:82px;display:grid;grid-template-columns:auto 1fr auto;gap:28px;align-items:center}.brand img{width:178px;height:auto}.desktop-nav{display:flex;align-items:center;justify-content:center;gap:25px;color:rgba(255,253,248,.78);font-size:13px;white-space:nowrap}.desktop-nav a{padding:9px 0}.header-actions{display:flex;align-items:center;gap:9px}.header-phone,.header-cta{height:42px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:12px}.header-phone{padding:0 14px;color:#fff;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.025)}.header-cta{padding:0 19px;background:linear-gradient(135deg,#dcc29c,#b88955);color:var(--deep);font-weight:800;box-shadow:0 8px 24px rgba(95,60,25,.22)}.hero{position:relative;min-height:735px;display:flex;align-items:center;overflow:hidden;background:#0d1510;isolation:isolate}.hero-photo-img{position:absolute;inset:0;z-index:0;width:100%;height:100%;object-fit:cover;object-position:center 50%;filter:saturate(.80) contrast(1.09) brightness(.79);transform:scale(1.012)}.theme-home .hero-photo-img{object-position:center 47%}.theme-general .hero-photo-img{object-position:center 54%}.theme-corporate .hero-photo-img{object-position:center 48%}.theme-disputes .hero-photo-img{object-position:center 51%}.theme-ip .hero-photo-img{object-position:center 50%}.theme-realestate .hero-photo-img{object-position:center 47%}.hero-overlay{position:absolute;inset:0;z-index:1;background:radial-gradient(circle at 20% 44%,rgba(202,163,104,.08),transparent 26%),linear-gradient(90deg,rgba(7,12,9,.90) 0%,rgba(10,17,12,.58) 45%,rgba(9,15,11,.91) 100%),linear-gradient(180deg,rgba(4,9,6,.06),rgba(4,9,6,.54))}.hero::before{content:"";position:absolute;z-index:2;inset:24px;border:1px solid rgba(215,184,133,.16);border-radius:26px;pointer-events:none}.hero::after{content:"";position:absolute;z-index:2;width:min(34vw,450px);aspect-ratio:1;left:26%;top:50%;translate:-50% -50%;background-image:var(--motif);background-repeat:no-repeat;background-size:contain;background-position:center;opacity:.10;pointer-events:none}.hero-grid{position:relative;z-index:3;display:grid;grid-template-columns:minmax(0,1.1fr) minmax(360px,.76fr);gap:62px;align-items:center;padding-block:66px}.hero-copy{position:relative;max-width:720px;color:#fff;padding-inline-end:8px}.hero-copy::before{content:"";position:absolute;right:-18px;top:2px;width:1px;height:94px;background:linear-gradient(180deg,var(--v6-gold),transparent);opacity:.8}.eyebrow,.form-kicker{display:inline-flex;align-items:center;gap:9px;color:#e0c8a8;font-size:12px;font-weight:800}.eyebrow::before{content:"";width:42px;height:1px;background:linear-gradient(90deg,var(--v6-gold),rgba(198,163,109,.14));opacity:.75}.hero h1,.lead-card h2{font-family:var(--font-display);font-weight:700;letter-spacing:0}.hero h1{font-size:clamp(54px,5.15vw,80px);line-height:1.02;margin:15px 0 20px;color:#fff;max-width:780px;text-wrap:balance;text-shadow:0 7px 34px rgba(0,0,0,.28)}.hero-intro{font-size:17px;line-height:1.95;color:rgba(255,255,255,.88);max-width:710px;margin:0}.hero-details{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px}.hero-details span{padding:7px 12px;border:1px solid rgba(220,194,154,.22);background:rgba(12,21,15,.38);border-radius:999px;color:#f3eadc;font-size:11px}.hero-actions{display:flex;align-items:center;gap:18px;margin-top:28px}.btn{min-height:50px;padding:0 22px;border:0;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;gap:9px;cursor:pointer;font-weight:800;font-size:14px}.btn-primary{background:linear-gradient(135deg,#29332b,#17221a);color:#fff;box-shadow:0 14px 28px rgba(31,41,34,.18)}.btn-secondary{background:linear-gradient(135deg,#e4ccb0,#bc8d59);color:var(--deep);box-shadow:0 14px 34px rgba(0,0,0,.18)}.btn-block{width:100%}.hero-link{color:rgba(255,255,255,.86);font-size:13px;border-bottom:1px solid rgba(255,255,255,.25);padding-bottom:2px}.lead-card{position:relative;background:linear-gradient(145deg,rgba(253,250,243,.985),rgba(242,234,217,.975));border:1px solid rgba(229,211,182,.76);border-radius:28px;padding:27px;box-shadow:0 34px 90px rgba(1,7,3,.38),0 0 0 1px rgba(141,103,62,.08);color:var(--ink)}.lead-card::before{content:"";display:block;width:54px;height:3px;border-radius:99px;background:linear-gradient(90deg,var(--burnt),var(--sand));margin-bottom:12px}.lead-card::after{content:"";position:absolute;inset:10px;border:1px solid rgba(125,87,46,.07);border-radius:20px;pointer-events:none}.form-kicker{color:var(--umber)}.lead-card h2{font-size:34px;line-height:1.08;margin:6px 0 8px;color:#243228}.form-intro{margin:0 0 16px;color:#6c665e;font-size:12.5px;line-height:1.7}.field{margin-bottom:10px}.field label{display:block;margin-bottom:5px;color:#374038;font-size:12px;font-weight:800}.field input,.field select{width:100%;height:47px;border:1px solid rgba(41,51,43,.16);border-radius:12px;background:#fff;color:var(--ink);padding:0 13px;font-size:14px}.ltr-input{text-align:left;direction:ltr}.field-error{display:block;min-height:0;margin-top:4px;color:#a73d2e;font-size:11px}.consent{display:grid;grid-template-columns:auto 1fr;gap:8px;align-items:start;margin:3px 0 12px;color:#625d55;font-size:10.5px;line-height:1.6}.consent input{margin-top:4px;width:15px;height:15px;accent-color:var(--green)}.consent a{text-decoration:underline;text-underline-offset:3px}.form-status{min-height:16px;margin-top:8px;font-size:11px;color:#2e6c48;text-align:center}.honeypot{position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;overflow:hidden!important}.floating-actions{position:fixed;z-index:90;right:16px;bottom:18px;display:flex;flex-direction:column;gap:8px;align-items:flex-end}.float-btn{height:46px;min-width:108px;border-radius:999px;padding:0 15px;display:flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 12px 30px rgba(0,0,0,.18);font-size:11px;font-weight:800}.float-btn.wa{background:linear-gradient(135deg,#1e6a4d,#154b39);color:#fff}.float-btn.call{background:#fffaf2;color:var(--green);border:1px solid rgba(104,63,24,.16)}@supports(content-visibility:auto){.services-section,.office-section,.digital-section,.process-section,.faq-section,.related-section,.final-cta,.site-footer{content-visibility:auto;contain-intrinsic-size:auto 760px}}@media(max-width:1100px){.desktop-nav{display:none}.header-row{grid-template-columns:auto 1fr auto}.hero-grid{grid-template-columns:1fr .82fr;gap:30px}}@media(max-width:860px){:root{--container:min(100% - 28px,760px)}.header-row{height:74px;grid-template-columns:1fr auto}.brand img{width:154px}.header-phone{display:none}.header-cta{height:39px;padding:0 14px}.hero{min-height:auto}.hero-photo-img{object-position:center center}.hero-overlay{background:linear-gradient(180deg,rgba(8,14,10,.68),rgba(9,15,11,.89) 58%,rgba(8,14,10,.97))}.hero::before{inset:12px;border-radius:20px}.hero::after{width:560px;max-width:86vw;left:50%;top:27%;opacity:.10}.hero-grid{grid-template-columns:1fr;gap:28px;padding-block:45px}.hero-copy{padding:0}.hero-copy::before{right:-10px;height:72px}.hero h1{font-size:clamp(50px,12vw,72px);max-width:680px}.lead-card{max-width:620px;width:100%;justify-self:center}}@media(max-width:620px){:root{--container:calc(100% - 24px)}body{font-size:15px}.site-header,.lead-card,.hero-details span{backdrop-filter:none;-webkit-backdrop-filter:none}.header-row{height:68px}.brand img{width:140px}.header-cta{height:38px;padding:0 13px;font-size:11px}.hero-photo-img{filter:none;transform:none}.hero-grid{padding-block:32px;gap:20px}.hero h1{font-size:46px;line-height:1.02;margin:12px 0 15px}.hero-intro{font-size:14px;line-height:1.82}.hero-details{margin-top:17px;gap:6px}.hero-details span{font-size:9.5px;padding:6px 9px}.hero-actions{display:none}.hero::before{inset:8px;border-radius:16px}.hero::after{top:22%;opacity:.09;filter:none;animation:none}.hero-copy::before{right:-6px;height:56px}.lead-card{padding:20px 16px;border-radius:22px}.lead-card h2{font-size:31px}.field input,.field select{height:46px}.floating-actions{right:9px;left:auto;bottom:9px;flex-direction:column;gap:7px;width:auto}.float-btn{flex:none;min-width:106px;height:44px;padding:0 13px}}
CSS;

    $criticalTag = '<style id="enkaf-critical">' . $critical . '</style>';
    $bundle = '<link rel="stylesheet" data-enkaf-v6="1" href="/assets/css/enkaf-bundle.css?v=20260829-5" media="print" onload="this.media=\'all\'">'
        . '<noscript><link rel="stylesheet" href="/assets/css/enkaf-bundle.css?v=20260829-5"></noscript>';
    if (!str_contains($html, 'id="enkaf-critical"')) {
        $html = str_replace('</head>', $criticalTag . $bundle . '</head>', $html);
    }

    $cfg = site_config();
    if ($cfg['gtm_id'] !== '') {
        $gtmPattern = '#<script>\\(function\\(w,d,s,l,i\\).*?googletagmanager\\.com/gtm\\.js\\?id=.*?</script>#s';
        $withoutGtm = preg_replace($gtmPattern, '', $html, 1);
        if (is_string($withoutGtm)) $html = $withoutGtm;
        $gtmId = e($cfg['gtm_id']);
        $optimizedGtm = '<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});</script>'
            . '<script defer fetchpriority="low" src="https://www.googletagmanager.com/gtm.js?id=' . $gtmId . '"></script>';
        $html = str_replace('</head>', $optimizedGtm . '</head>', $html);
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

    // The hero is the mobile LCP candidate. It is tiny and preloaded, so decode it
    // eagerly rather than deferring its decode behind later paint work.
    $html = str_replace(
        'fetchpriority="high" decoding="async"',
        'fetchpriority="high" loading="eager" decoding="sync"',
        $html
    );

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
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' https://www.googletagmanager.com 'unsafe-inline'; connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://www.googleadservices.com; frame-src https://www.googletagmanager.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
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
        'build' => BUILD_ID . '-v5-legal-about-seo-lcp2',
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
echo performance_ready_html(enhance_site_html(not_found_html(), $path), $path);
