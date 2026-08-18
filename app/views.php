<?php
declare(strict_types=1);

function head_html(array $page, ?string $canonicalPath = null): string {
    $cfg = site_config();
    $canonicalPath ??= $page['slug'] ?? '/';
    $canonical = absolute_url($canonicalPath);
    $robots = $cfg['review_mode'] ? 'noindex,nofollow' : 'index,follow,max-image-preview:large';
    $schema = json_encode(page_schema($page), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $gtmHead = '';
    if ($cfg['gtm_id'] !== '') {
        $id = e($cfg['gtm_id']);
        $gtmHead = "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$id}');</script>";
    }
    return '<head>'
        . '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">'
        . '<title>' . e($page['title'] ?? BRAND_NAME_AR) . '</title>'
        . '<meta name="description" content="' . e($page['description'] ?? '') . '">'
        . '<meta name="robots" content="' . $robots . '"><link rel="canonical" href="' . e($canonical) . '">'
        . '<meta property="og:type" content="website"><meta property="og:locale" content="ar_SA">'
        . '<meta property="og:site_name" content="' . e(BRAND_NAME_AR) . '"><meta property="og:title" content="' . e($page['title'] ?? BRAND_NAME_AR) . '">'
        . '<meta property="og:description" content="' . e($page['description'] ?? '') . '"><meta property="og:url" content="' . e($canonical) . '">'
        . '<meta name="theme-color" content="#29332B">'
        . '<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">'
        . '<link rel="preload" href="/assets/css/site.css?v=' . e(BUILD_ID) . '" as="style"><link rel="stylesheet" href="/assets/css/site.css?v=' . e(BUILD_ID) . '">'
        . $gtmHead
        . '<script type="application/ld+json">' . $schema . '</script>'
        . '</head>';
}

function gtm_body_fallback(): string {
    $cfg = site_config();
    if ($cfg['gtm_id'] === '') return '';
    return '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . e($cfg['gtm_id']) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
}

function header_html(): string {
    $cfg = site_config();
    return '<header class="site-header"><div class="container header-row">'
        . '<a class="brand" href="/" aria-label="العودة إلى الرئيسية"><img src="/assets/img/enkaf-logo-white.png" width="240" height="51" alt="إنكاف للمحاماة والاستشارات القانونية"></a>'
        . '<nav class="desktop-nav" aria-label="التنقل الرئيسي">'
        . '<a href="/محامي-شركات-وتأسيس-شركات/">الشركات</a><a href="/قضايا-تجارية-وتحصيل-ديون/">القضايا التجارية</a><a href="/تسجيل-علامة-تجارية-والملكية-الفكرية/">العلامات</a><a href="/محامي-عقاري-وتوثيق-عقود/">العقار</a>'
        . '</nav>'
        . '<a class="header-phone" href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call">' . icon_svg('phone') . '<span><bdi dir="ltr">' . e($cfg['phone_display']) . '</bdi></span></a>'
        . '</div></header>';
}

function form_html(array $page): string {
    $services = service_options();
    $keys = $page['service_keys'] ?? array_keys($services);
    $options = '<option value="">اختر نوع الخدمة</option>';
    foreach ($keys as $key) {
        if (!isset($services[$key])) continue;
        $selected = ($page['default_service'] ?? '') === $key ? ' selected' : '';
        $options .= '<option value="' . e($key) . '"' . $selected . '>' . e($services[$key]) . '</option>';
    }
    $hidden = '';
    foreach (['landing_path','landing_url','utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid','ttclid','fbclid','referrer','first_landing_url','session_id'] as $field) {
        $hidden .= '<input type="hidden" name="' . $field . '">';
    }
    return '<aside class="lead-card" id="form" aria-labelledby="formTitle">'
        . '<div class="form-kicker">طلب تواصل قانوني</div><h2 id="formTitle">' . e($page['form_title']) . '</h2>'
        . '<p class="form-intro">بيانات أساسية فقط. بعد حفظ الطلب يتواصل معك الفريق لفهم التفاصيل وتحديد الخطوة التالية.</p>'
        . '<form id="leadForm" novalidate>'
        . '<input type="hidden" name="landing_page_id" value="' . e($page['id']) . '">' . $hidden
        . '<div class="honeypot" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>'
        . '<div class="field"><label for="full_name">الاسم</label><input id="full_name" name="full_name" autocomplete="name" minlength="2" maxlength="100" required placeholder="اكتب اسمك"><small class="field-error" data-error-for="full_name"></small></div>'
        . '<div class="field"><label for="phone">رقم الجوال</label><input id="phone" name="phone" class="ltr-input" type="tel" inputmode="tel" autocomplete="tel" required placeholder="05xxxxxxxx" dir="ltr"><small class="field-error" data-error-for="phone"></small></div>'
        . '<div class="field"><label for="service">نوع الخدمة القانونية</label><select id="service" name="service" required>' . $options . '</select><small class="field-error" data-error-for="service"></small></div>'
        . '<label class="consent"><input type="checkbox" name="privacy_consent" value="1" required><span>أوافق على معالجة بياناتي للتواصل بخصوص الطلب وفق <a href="/سياسة-الخصوصية/" target="_blank" rel="noopener">سياسة الخصوصية</a>.</span></label><small class="field-error consent-error" data-error-for="privacy_consent"></small>'
        . '<button class="btn btn-primary btn-block" type="submit"><span>' . e($page['form_cta']) . '</span>' . icon_svg('arrow') . '</button>'
        . '<div class="form-status" id="formStatus" role="status" aria-live="polite"></div>'
        . '</form></aside>';
}

function trust_strip(): string {
    $items = [
        ['shield','النزاهة والوضوح','تعامل مهني وشفاف'],
        ['scale','حلول قانونية عملية','فهم الحالة قبل اختيار المسار'],
        ['document','صياغة دقيقة','لغة واضحة وتفاصيل منظمة'],
        ['briefcase','تركيز على العميل','الخدمة تُبنى حول الهدف القانوني'],
    ];
    $html = '<section class="trust-strip"><div class="container trust-grid">';
    foreach ($items as [$icon,$title,$text]) {
        $html .= '<div class="trust-item"><span class="icon-box">' . icon_svg($icon) . '</span><div><strong>' . e($title) . '</strong><small>' . e($text) . '</small></div></div>';
    }
    return $html . '</div></section>';
}

function service_navigation(string $current): string {
    $html = '<div class="service-nav-grid">';
    $icons = ['scale','briefcase','document','mark','building'];
    $i = 0;
    foreach (internal_service_links() as $path => $label) {
        if ($path === $current) { $i++; continue; }
        $html .= '<a class="service-link-card" href="' . e($path) . '"><span class="icon-box">' . icon_svg($icons[$i % count($icons)]) . '</span><span>' . e($label) . '</span>' . icon_svg('arrow') . '</a>';
        $i++;
    }
    return $html . '</div>';
}

function scope_cards(array $cards): string {
    $icons = ['briefcase','document','scale','building'];
    $html = '<div class="scope-grid">';
    foreach ($cards as $i => $card) {
        $html .= '<article class="scope-card"><span class="icon-box">' . icon_svg($icons[$i % count($icons)]) . '</span><h3>' . e($card[0]) . '</h3><p>' . e($card[1]) . '</p></article>';
    }
    return $html . '</div>';
}

function process_section(): string {
    $steps = [
        ['01','أرسل بياناتك الأساسية','الاسم والجوال ونوع الخدمة؛ بدون تحميل مستندات حساسة في الخطوة الأولى.'],
        ['02','نراجع نوع الطلب','نحدد نطاق الخدمة والمعلومات أو المستندات التي نحتاجها لفهم الحالة.'],
        ['03','نوضح الخطوة التالية','يتواصل معك الفريق لتوضيح المسار القانوني الممكن ونطاق العمل المناسب.'],
    ];
    $html = '<div class="steps-grid">';
    foreach ($steps as [$n,$title,$text]) {
        $html .= '<article class="step-card"><span class="step-number">' . $n . '</span><h3>' . e($title) . '</h3><p>' . e($text) . '</p></article>';
    }
    return $html . '</div>';
}

function faq_html(array $faq): string {
    $html = '<div class="faq-list">';
    foreach ($faq as [$q,$a]) {
        $html .= '<details><summary>' . e($q) . '<span class="faq-plus">+</span></summary><p>' . e($a) . '</p></details>';
    }
    return $html . '</div>';
}

function footer_html(): string {
    $cfg = site_config();
    return '<footer class="site-footer"><div class="container footer-grid">'
        . '<div class="footer-brand"><img src="/assets/img/enkaf-logo-white.png" width="240" height="51" alt="إنكاف"><p>حلول قانونية عملية للأفراد والشركات في المملكة العربية السعودية.</p></div>'
        . '<div><h3>الخدمات</h3><a href="/محامي-واستشارات-قانونية/">الاستشارات القانونية</a><a href="/محامي-شركات-وتأسيس-شركات/">الشركات والعقود</a><a href="/قضايا-تجارية-وتحصيل-ديون/">القضايا التجارية</a><a href="/تسجيل-علامة-تجارية-والملكية-الفكرية/">العلامات والملكية الفكرية</a><a href="/محامي-عقاري-وتوثيق-عقود/">الخدمات العقارية</a></div>'
        . '<div><h3>التواصل</h3><a href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call">' . icon_svg('phone') . '<bdi dir="ltr">' . e($cfg['phone_display']) . '</bdi></a><a href="mailto:' . e($cfg['email_primary']) . '">' . icon_svg('mail') . e($cfg['email_primary']) . '</a><a href="mailto:' . e($cfg['email_work']) . '">' . icon_svg('mail') . e($cfg['email_work']) . '</a><a href="/سياسة-الخصوصية/">سياسة الخصوصية</a></div>'
        . '</div><div class="container footer-bottom"><span>© ' . date('Y') . ' إنكاف. جميع الحقوق محفوظة.</span><span>المعلومات المنشورة عامة ولا تمثل ضمانًا لنتيجة قانونية أو قضائية.</span></div></footer>';
}

function floating_actions(): string {
    $cfg = site_config();
    $wa = 'https://wa.me/' . $cfg['whatsapp'] . '?text=' . rawurlencode('مرحبًا إنكاف، أرغب في استشارة قانونية.');
    return '<div class="floating-actions" aria-label="تواصل سريع"><a class="float-btn wa" href="' . e($wa) . '" target="_blank" rel="noopener" data-event="click_whatsapp" aria-label="تواصل عبر واتساب">' . icon_svg('whatsapp') . '<span>واتساب</span></a><a class="float-btn call" href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call" aria-label="اتصال">' . icon_svg('phone') . '<span>اتصال</span></a></div>';
}

function page_html(array $page): string {
    $chips = '';
    foreach ($page['chips'] as $chip) $chips .= '<span class="hero-chip">' . icon_svg('check') . e($chip) . '</span>';
    $body = '<body class="theme-' . e($page['theme']) . '">' . gtm_body_fallback() . header_html()
        . '<main><section class="hero"><div class="hero-art" aria-hidden="true"><span></span><span></span><span></span></div><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">' . e($page['eyebrow']) . '</span><h1>' . e($page['h1']) . '</h1><p class="hero-intro">' . e($page['intro']) . '</p><div class="hero-chips">' . $chips . '</div><div class="hero-proof">' . icon_svg('shield') . '<span>نراجع كل طلب وفق طبيعته ومستنداته ونوضح نطاق الخدمة قبل بدء العمل.</span></div></div>' . form_html($page) . '</div></section>'
        . trust_strip()
        . '<section class="section section-paper"><div class="container"><div class="section-heading"><span class="eyebrow dark">نطاق الخدمة</span><h2>' . e($page['section_title']) . '</h2><p>' . e($page['section_intro']) . '</p></div>' . scope_cards($page['scope_cards']) . '</div></section>'
        . '<section class="section section-green"><div class="container"><div class="section-heading light"><span class="eyebrow">طريقة العمل</span><h2>من الطلب الأولي إلى الخطوة القانونية التالية</h2><p>نحافظ على الخطوة الأولى بسيطة، ثم نجمع التفاصيل اللازمة عندما يتضح نطاق الحالة.</p></div>' . process_section() . '</div></section>'
        . '<section class="section section-sand"><div class="container split-values"><div><span class="eyebrow dark">نهج إنكاف</span><h2>واضح، مهني، وموجّه للحل</h2><p>تعتمد شخصية إنكاف على الخبرة القانونية مع لغة مفهومة، والالتزام بالنزاهة والجودة والتركيز على احتياج العميل دون مبالغة في الوعود.</p></div><div class="values-list"><div><strong>النزاهة</strong><span>وضوح وشفافية في التعامل.</span></div><div><strong>التميز</strong><span>دقة واهتمام بجودة الحل القانوني.</span></div><div><strong>الابتكار</strong><span>تفكير عملي في التحديات المعقدة.</span></div><div><strong>التركيز على العميل</strong><span>فهم الهدف قبل تحديد المسار.</span></div></div></div></section>'
        . '<section class="section section-paper"><div class="container"><div class="section-heading"><span class="eyebrow dark">الأسئلة الشائعة</span><h2>أسئلة قبل إرسال الطلب</h2></div>' . faq_html($page['faq']) . '</div></section>'
        . '<section class="section section-links"><div class="container"><div class="section-heading compact"><span class="eyebrow dark">خدمات مرتبطة</span><h2>استكشف خدمات إنكاف القانونية</h2></div>' . service_navigation($page['slug']) . '</div></section>'
        . '<section class="final-cta"><div class="container final-cta-grid"><div><span class="eyebrow">ابدأ الآن</span><h2>أرسل طلبك القانوني في أقل من دقيقة</h2><p>ابدأ بالبيانات الأساسية، وسيتم تحديد التفاصيل المطلوبة بعد فهم نوع الخدمة.</p></div><a class="btn btn-paper" href="#form">انتقل إلى النموذج' . icon_svg('arrow') . '</a></div></section></main>'
        . footer_html() . floating_actions() . '<script src="/assets/js/site.js?v=' . e(BUILD_ID) . '" defer></script></body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}

function privacy_html(): string {
    $page = [
        'id' => 'PRIVACY', 'slug' => '/سياسة-الخصوصية/', 'theme' => 'legal',
        'title' => 'سياسة الخصوصية | إنكاف',
        'description' => 'سياسة خصوصية إنكاف وشرح البيانات التي يتم جمعها عبر نماذج التواصل واستخدامها وحمايتها.',
        'faq' => [],
    ];
    $cfg = site_config();
    $body = '<body class="theme-legal">' . gtm_body_fallback() . header_html()
        . '<main class="legal-page"><section class="legal-hero"><div class="container"><span class="eyebrow">الخصوصية والبيانات</span><h1>سياسة الخصوصية</h1><p>الإصدار: ' . e(PRIVACY_VERSION) . '</p></div></section><section class="section section-paper"><div class="container legal-copy">'
        . '<h2>البيانات التي نجمعها</h2><p>عند إرسال نموذج التواصل، نجمع الاسم ورقم الجوال ونوع الخدمة القانونية، إضافة إلى بيانات مصدر الزيارة والحملة ومعرّفات النقر عند توفرها، ومعلومات تقنية محدودة تساعد على تشغيل الخدمة ومنع إساءة الاستخدام.</p>'
        . '<h2>الغرض من الاستخدام</h2><p>نستخدم البيانات لفهم الطلب، التواصل معك، تحديد نوع الخدمة، إدارة ومتابعة الطلب، تحسين جودة تجربة الموقع وقياس مصادر الحملات.</p>'
        . '<h2>الموافقة</h2><p>لا يتم إرسال النموذج قبل تأكيد الموافقة على هذه السياسة. يتم حفظ حالة الموافقة وتوقيتها وإصدار السياسة مع سجل الطلب.</p>'
        . '<h2>المشاركة والوصول</h2><p>يقتصر الوصول إلى بيانات الطلبات على الأشخاص أو مقدمي الخدمات المصرح لهم بالقدر اللازم لتشغيل الخدمة ومتابعة الطلب. لا نعرض بيانات العملاء للعامة.</p>'
        . '<h2>الاحتفاظ والحماية</h2><p>تُحفظ البيانات بالقدر والمدة اللازمين لمتابعة الطلب ومتطلبات العمل، مع تطبيق ضوابط وصول مناسبة. لا نضع الاسم أو رقم الجوال في روابط صفحات الشكر أو معلمات التتبع.</p>'
        . '<h2>ملفات القياس ومعرّفات الحملات</h2><p>قد يحفظ الموقع معرّفات مثل gclid وUTM وبيانات المصدر لفهم أداء الحملات وربط الطلب بمصدره. يتم التعامل مع هذه البيانات ضمن أغراض القياس والتشغيل الموضحة هنا.</p>'
        . '<h2>حقوقك والتواصل</h2><p>للاستفسار عن بياناتك أو طلب تحديثها أو مناقشة طريقة التعامل معها، تواصل معنا عبر <a href="mailto:' . e($cfg['email_primary']) . '">' . e($cfg['email_primary']) . '</a> أو الرقم <a href="tel:' . e($cfg['phone_e164']) . '"><bdi dir="ltr">' . e($cfg['phone_display']) . '</bdi></a>.</p>'
        . '<h2>تحديث السياسة</h2><p>قد يتم تحديث هذه السياسة عند تغير طريقة تشغيل النماذج أو أدوات القياس أو متطلبات الخدمة. يظهر رقم الإصدار الحالي أعلى الصفحة.</p>'
        . '</div></section></main>' . footer_html() . '<script src="/assets/js/site.js?v=' . e(BUILD_ID) . '" defer></script></body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}

function thank_you_html(): string {
    $page = [
        'id' => 'THANKS', 'slug' => '/شكرا/', 'theme' => 'thanks',
        'title' => 'تم استلام طلبك | إنكاف',
        'description' => 'تم استلام طلب التواصل القانوني لدى إنكاف.',
        'faq' => [],
    ];
    $ref = preg_replace('/[^A-Z0-9\-]/', '', strtoupper((string)($_GET['ref'] ?? '')));
    $cfg = site_config();
    $waBase = 'https://wa.me/' . $cfg['whatsapp'];
    $body = '<body class="theme-thanks">' . gtm_body_fallback() . header_html()
        . '<main class="thanks-page"><section class="thanks-card"><span class="success-mark">' . icon_svg('check') . '</span><span class="eyebrow dark">تم حفظ الطلب</span><h1>شكرًا لك، تم استلام طلبك</h1><p>سيتم مراجعة نوع الخدمة والتواصل معك لفهم التفاصيل وتحديد الخطوة التالية.</p>'
        . ($ref ? '<div class="lead-ref">رقم الطلب: <bdi dir="ltr">' . e($ref) . '</bdi></div>' : '')
        . '<div class="thanks-actions"><a class="btn btn-primary" id="thankYouWhatsapp" href="' . e($waBase) . '" target="_blank" rel="noopener" data-event="whatsapp_after_form">متابعة عبر واتساب' . icon_svg('whatsapp') . '</a><a class="btn btn-outline" href="/">العودة للرئيسية</a></div></section></main>'
        . footer_html() . '<script>window.ENKAF_THANK_YOU_REF=' . json_encode($ref, JSON_UNESCAPED_UNICODE) . ';window.ENKAF_WA=' . json_encode($cfg['whatsapp']) . ';</script><script src="/assets/js/site.js?v=' . e(BUILD_ID) . '" defer></script></body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}

function not_found_html(): string {
    $page = [
        'id' => '404', 'slug' => '/', 'theme' => 'legal',
        'title' => 'الصفحة غير موجودة | إنكاف',
        'description' => 'الصفحة المطلوبة غير موجودة.',
        'faq' => [],
    ];
    $body = '<body class="theme-legal">' . header_html() . '<main class="thanks-page"><section class="thanks-card"><span class="eyebrow dark">404</span><h1>الصفحة غير موجودة</h1><p>قد يكون الرابط تغير أو تمت كتابة العنوان بصورة غير صحيحة.</p><div class="thanks-actions"><a class="btn btn-primary" href="/">العودة للرئيسية</a><a class="btn btn-outline" href="/محامي-واستشارات-قانونية/">طلب استشارة قانونية</a></div></section></main>' . footer_html() . '</body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}
