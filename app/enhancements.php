<?php
declare(strict_types=1);

function enkaf_maps_url(): string {
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('ENKAF Law Firm Al-Madinah Al-Munawarah Branch Rd Al Faisaliyyah Jeddah 23442 Saudi Arabia');
}

function authority_context(): array {
    return [
        '/محامي-واستشارات-قانونية/' => [
            'title' => 'جهات وإجراءات مرتبطة بالاستشارات والتمثيل القانوني',
            'intro' => 'بحسب طبيعة الملف، قد ترتبط الاستشارة أو إجراءات التمثيل بوزارة العدل، منصة ناجز، المحاكم السعودية، وكتابات العدل. يبدأ دور إنكاف بفهم الوقائع والمستندات وتحديد الجهة والإجراء النظامي المناسب للحالة.',
            'terms' => ['وزارة العدل', 'منصة ناجز', 'المحاكم السعودية', 'كتابة العدل'],
        ],
        '/محامي-شركات-وتأسيس-شركات/' => [
            'title' => 'تأسيس الشركات والأنظمة والجهات ذات الصلة',
            'intro' => 'تتداخل أعمال تأسيس الشركات والهيكلة والحوكمة مع نظام الشركات، وزارة التجارة، المركز السعودي للأعمال، ونظام الاستثمار بحسب نوع الكيان ونشاطه وهيكله وملكية المستثمرين.',
            'terms' => ['نظام الشركات', 'وزارة التجارة', 'المركز السعودي للأعمال', 'مركز الأعمال السعودي', 'نظام الاستثمار'],
        ],
        '/قضايا-تجارية-وتحصيل-ديون/' => [
            'title' => 'النزاعات التجارية والتحصيل أمام الجهات المختصة',
            'intro' => 'تقييم النزاع التجاري أو المطالبة المالية يشمل تحديد الاختصاص والمسار الإجرائي في ضوء نظام المحاكم التجارية، المحكمة التجارية، وإجراءات التنفيذ، مع دراسة اللجوء إلى المركز السعودي للتحكيم التجاري (SCCA) عندما يكون التحكيم مناسبًا ومتاحًا.',
            'terms' => ['المحكمة التجارية', 'نظام المحاكم التجارية', 'المركز السعودي للتحكيم التجاري (SCCA)', 'محكمة التنفيذ'],
        ],
        '/تسجيل-علامة-تجارية-والملكية-الفكرية/' => [
            'title' => 'تسجيل العلامات أمام الهيئة السعودية للملكية الفكرية (SAIP)',
            'intro' => 'تشمل خدمات العلامات التجارية دراسة التسجيل والمتابعة والاعتراض والحماية أمام الهيئة السعودية للملكية الفكرية (SAIP)، مع مراعاة نظام العلامات التجارية لدول مجلس التعاون لدول الخليج العربية والمتطلبات الإجرائية ذات الصلة.',
            'terms' => ['الهيئة السعودية للملكية الفكرية (SAIP)', 'نظام العلامات التجارية لدول مجلس التعاون', 'تسجيل علامة تجارية', 'اعتراضات العلامات التجارية'],
        ],
        '/محامي-عقاري-وتوثيق-عقود/' => [
            'title' => 'العقار والتوثيق والتسجيل العيني',
            'intro' => 'ترتبط المعاملات العقارية والتوثيق بوزارة العدل، كتابة العدل، الصك العقاري، نظام التسجيل العيني للعقار، وخدمات البورصة العقارية بحسب نوع التصرف والإجراء المطلوب.',
            'terms' => ['وزارة العدل', 'كتابة العدل', 'التوثيق', 'الصك العقاري', 'نظام التسجيل العيني للعقار', 'البورصة العقارية'],
        ],
    ];
}

function authority_section_html(string $path): string {
    $contexts = authority_context();
    if (!isset($contexts[$path])) return '';
    $context = $contexts[$path];
    $chips = '';
    foreach ($context['terms'] as $term) {
        $chips .= '<span>' . e($term) . '</span>';
    }
    return '<section class="section authority-section"><div class="container authority-grid">'
        . '<div class="section-heading compact"><span class="section-label">جهات وأنظمة ذات صلة</span><h2>' . e($context['title']) . '</h2><p>' . e($context['intro']) . '</p></div>'
        . '<div class="authority-terms">' . $chips . '</div>'
        . '<p class="authority-note">تُذكر الجهات والأنظمة لشرح السياق الإجرائي المرتبط بالخدمة، ولا يعني ذكرها وجود شراكة أو اعتماد خاص إلا حيث يكون ذلك موثقًا رسميًا.</p>'
        . '</div></section>';
}

function home_about_teaser_html(): string {
    return '<section class="section about-teaser"><div class="container about-teaser-grid">'
        . '<div><span class="section-label">من نحن</span><h2>إنكاف: ممارسة قانونية سعودية تجمع الخبرة بالدقة والتنظيم.</h2><p>تأسست إنكاف لتقديم خدمات قانونية متكاملة للأفراد والشركات، بمنهج يقوم على المهنية والشفافية والسرية، مع فريق قانوني متعدد التخصصات وخبرة في الأنظمة وبيئة الأعمال السعودية.</p><a class="text-link" href="/من-نحن/">تعرف على إنكاف وفريق العمل ' . icon_svg('arrow') . '</a></div>'
        . '<div class="about-teaser-values"><span>الاحترافية</span><span>الشفافية</span><span>الدقة</span><span>النزاهة</span><span>السرية</span></div>'
        . '</div></section>';
}

function about_page_schema(): string {
    $cfg = site_config();
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => ['LegalService','LocalBusiness'],
                '@id' => absolute_url('/من-نحن/') . '#organization',
                'name' => BRAND_NAME_AR,
                'url' => absolute_url('/من-نحن/'),
                'telephone' => $cfg['phone_e164'],
                'email' => $cfg['email_primary'],
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => 'طريق المدينة المنورة الفرعي، حي الفيصلية',
                    'addressLocality' => 'جدة',
                    'postalCode' => '23442',
                    'addressCountry' => 'SA',
                ],
                'employee' => [
                    ['@type' => 'Person', 'name' => 'محمد الزهراني', 'jobTitle' => 'محامٍ ومستشار قانوني ومؤسس إنكاف'],
                    ['@type' => 'Person', 'name' => 'سند الكثيري', 'jobTitle' => 'محامٍ متدرب'],
                ],
            ],
            ['@type' => 'Person', 'name' => 'محمد الزهراني', 'jobTitle' => 'محامٍ ومستشار قانوني ومؤسس إنكاف', 'worksFor' => ['@id' => absolute_url('/من-نحن/') . '#organization']],
            ['@type' => 'Person', 'name' => 'سند الكثيري', 'jobTitle' => 'محامٍ متدرب', 'worksFor' => ['@id' => absolute_url('/من-نحن/') . '#organization']],
        ],
    ];
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

function about_page_html(): string {
    $cfg = site_config();
    $page = [
        'id' => 'ABOUT',
        'slug' => '/من-نحن/',
        'theme' => 'about',
        'title' => 'من نحن وفريق العمل | إنكاف للمحاماة والاستشارات القانونية',
        'description' => 'تعرف على إنكاف للمحاماة والاستشارات القانونية وفريق العمل، ومنهم المحامي محمد الزهراني، وعلى قيم المكتب وموقعه في جدة.',
    ];
    $map = enkaf_maps_url();
    $body = '<body class="theme-about">' . gtm_body_fallback() . header_html()
        . '<main><section class="about-hero"><img src="/assets/img/hero-home.webp" width="1600" height="1000" fetchpriority="high" decoding="async" alt="مكتب إنكاف للمحاماة والاستشارات القانونية"><div class="about-hero-overlay"></div><div class="container about-hero-copy"><span class="eyebrow">إنكاف للمحاماة والاستشارات القانونية</span><h1>من نحن وفريق العمل</h1><p>شركة مهنية سعودية متخصصة في تقديم الاستشارات والخدمات القانونية للأفراد والشركات، بمنهج يجمع بين الفهم العميق للأنظمة والدقة في الصياغة والوضوح في التواصل.</p></div></section>'
        . '<section class="section about-intro"><div class="container about-intro-grid"><div><span class="section-label">عن إنكاف</span><h2>حقوقكم وأهدافكم جزء من التزامنا المهني.</h2><p>تعمل إنكاف على تحويل التحديات القانونية إلى مسارات أكثر وضوحًا من خلال التوجيه القانوني، حماية الحقوق، وصياغة الحلول التي تراعي واقع الأعمال والأنظمة في المملكة. ويقوم منهجنا على المهنية والدقة والالتزام وبناء علاقات طويلة المدى مع العملاء.</p><p>يجمع فريق إنكاف بين الخبرة القانونية والتخصص في مجالات التقاضي، الشركات والعقود، الحوكمة، التحكيم، الملكية الفكرية، والاستشارات المرتبطة بالأعمال والاستثمار.</p></div><div class="about-quote"><strong>الرؤية</strong><p>أن تكون إنكاف من الجهات القانونية الموثوقة في المملكة عبر حلول دقيقة ومبتكرة تحمي الحقوق وتدعم القرار.</p><strong>المهمة</strong><p>تقديم خدمة قانونية فعالة وموثوقة تستند إلى فهم عميق للأنظمة وتساعد العملاء على اتخاذ قرارات أكثر وضوحًا.</p></div></div></section>'
        . '<section class="section values-section"><div class="container"><div class="section-heading compact"><span class="section-label">قيم إنكاف</span><h2>قيم ثابتة تحكم طريقة العمل.</h2></div><div class="values-grid"><span>الاحترافية</span><span>الشفافية</span><span>الالتزام</span><span>الدقة</span><span>الجودة</span><span>النزاهة</span><span>السرية</span></div></div></section>'
        . '<section class="section team-section"><div class="container"><div class="section-heading"><span class="section-label">فريق العمل</span><h2>تعرف على فريق إنكاف القانوني.</h2><p>إتاحة أسماء أعضاء الفريق ومهامهم تساعد العميل على التعرف على الخبرة القانونية التي يتواصل معها، وتدعم ظهور أسماء المحامين في نتائج البحث المرتبطة بالمكتب.</p></div><div class="team-grid">'
        . '<article class="team-card"><div class="team-initial">م ز</div><div><h3>المحامي محمد الزهراني</h3><p class="team-role">محامٍ ومستشار قانوني ومؤسس إنكاف</p><p>يعمل ضمن قيادة إنكاف في تقديم الخدمات القانونية والاستشارات المرتبطة بالأعمال والأنظمة السعودية.</p></div></article>'
        . '<article class="team-card"><div class="team-initial">س ك</div><div><h3>سند الكثيري</h3><p class="team-role">محامٍ متدرب</p><p>ضمن فريق إنكاف القانوني في جدة، ويشارك في دعم أعمال الملفات والمتابعة القانونية.</p></div></article>'
        . '</div></div></section>'
        . '<section class="section location-section"><div class="container location-grid"><div><span class="section-label">موقع المكتب</span><h2>إنكاف في جدة</h2><p>طريق المدينة المنورة الفرعي، حي الفيصلية، جدة 23442، المملكة العربية السعودية.</p><div class="location-actions"><a class="btn btn-primary" href="' . e($map) . '" target="_blank" rel="noopener">فتح الموقع في خرائط Google ' . icon_svg('arrow') . '</a><a class="text-link" href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call">اتصال <bdi dir="ltr">' . e($cfg['phone_display']) . '</bdi></a></div></div><div class="location-card"><strong>زيارة المكتب</strong><span>جدة — حي الفيصلية</span><span>طريق المدينة المنورة الفرعي</span><a href="' . e($map) . '" target="_blank" rel="noopener">الاتجاهات على الخريطة</a></div></div></section>'
        . '<section class="final-cta"><div class="container final-cta-grid"><div><span class="section-label light">تواصل مع إنكاف</span><h2>ابدأ بعرض احتياجك القانوني على الفريق.</h2><p>أرسل بيانات التواصل ونوع الخدمة ليتم توجيه الطلب إلى التخصص المناسب.</p></div><a class="btn btn-light" href="/محامي-واستشارات-قانونية/#form">طلب استشارة ' . icon_svg('arrow') . '</a></div></section></main>'
        . footer_html() . floating_actions() . '<script src="/assets/js/site.js?v=' . e(asset_version()) . '" defer></script></body>';
    $html = '<!doctype html><html lang="ar" dir="rtl">' . head_html($page, '/من-نحن/') . $body . '</html>';
    return str_replace('</head>', about_page_schema() . '</head>', $html);
}

function enhance_site_html(string $html, string $path): string {
    $css = '<link rel="stylesheet" href="/assets/css/enhancements.css?v=20260824-1">';
    if (!str_contains($html, '/assets/css/enhancements.css')) {
        $html = str_replace('</head>', $css . '</head>', $html);
    }

    if (!str_contains($html, 'href="/من-نحن/"')) {
        $html = str_replace('<nav class="desktop-nav" aria-label="التنقل الرئيسي">', '<nav class="desktop-nav" aria-label="التنقل الرئيسي"><a href="/من-نحن/">من نحن</a>', $html);
    }

    $footerNeedle = '<a href="/سياسة-الخصوصية/">سياسة الخصوصية</a>';
    if (str_contains($html, $footerNeedle) && !str_contains($html, 'موقع المكتب:')) {
        $addressLink = '<a href="/من-نحن/">من نحن وفريق العمل</a><a href="' . e(enkaf_maps_url()) . '" target="_blank" rel="noopener">موقع المكتب: جدة، حي الفيصلية</a>';
        $html = str_replace($footerNeedle, $addressLink . $footerNeedle, $html);
    }

    if ($path === '/') {
        $marker = '<section class="section process-section">';
        if (str_contains($html, $marker)) $html = str_replace($marker, home_about_teaser_html() . $marker, $html);
    }

    $authority = authority_section_html($path);
    if ($authority !== '') {
        $marker = '<section class="section related-section">';
        if (str_contains($html, $marker)) $html = str_replace($marker, $authority . $marker, $html);
    }

    return $html;
}
