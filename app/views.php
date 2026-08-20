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
        . '<meta name="theme-color" content="#172019">'
        . '<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">'
        . '<link rel="preload" href="/assets/css/site.css?v=' . e(BUILD_ID) . '" as="style"><link rel="stylesheet" href="/assets/css/site.css?v=' . e(BUILD_ID) . '">'
        . '<link rel="stylesheet" href="/assets/css/luxury.css?v=' . e(BUILD_ID) . '">'
        . '<link rel="stylesheet" href="/assets/css/photo-hero.css?v=' . e(BUILD_ID) . '">'
        . '<link rel="stylesheet" href="/assets/css/photo-office.css?v=' . e(BUILD_ID) . '">'
        . '<link rel="stylesheet" href="/assets/css/photo-team.css?v=' . e(BUILD_ID) . '">'
        . '<link rel="stylesheet" href="/assets/css/luxury-v2.css?v=' . e(BUILD_ID) . '">'
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
        . '<nav class="desktop-nav" aria-label="التنقل الرئيسي"><a href="/محامي-واستشارات-قانونية/">الاستشارات</a><a href="/محامي-شركات-وتأسيس-شركات/">الشركات</a><a href="/قضايا-تجارية-وتحصيل-ديون/">القضايا التجارية</a><a href="/تسجيل-علامة-تجارية-والملكية-الفكرية/">الملكية الفكرية</a><a href="/محامي-عقاري-وتوثيق-عقود/">العقار</a></nav>'
        . '<div class="header-actions"><span class="header-location">جدة</span><a class="header-phone" href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call">' . icon_svg('phone') . '<span><bdi dir="ltr">' . e($cfg['phone_display']) . '</bdi></span></a></div>'
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
    foreach (['landing_path','landing_url','utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid','ttclid','fbclid','campaignid','adgroupid','creative','keyword','matchtype','device','network','targetid','loc_physical_ms','gad_source','gad_campaignid','referrer','first_landing_url','session_id'] as $field) {
        $hidden .= '<input type="hidden" name="' . $field . '">';
    }
    return '<aside class="lead-card" id="form" aria-labelledby="formTitle"><div class="form-kicker">تواصل مع إنكاف</div><h2 id="formTitle">' . e($page['form_title']) . '</h2><p class="form-intro">اترك بيانات التواصل الأساسية، ويتولى الفريق ترتيب الخطوة التالية معك.</p>'
        . '<form id="leadForm" autocomplete="on" novalidate><input type="hidden" name="landing_page_id" value="' . e($page['id']) . '">' . $hidden
        . '<div class="honeypot" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>'
        . '<div class="field"><label for="full_name">الاسم</label><input id="full_name" name="full_name" autocomplete="name" minlength="2" maxlength="100" required placeholder="الاسم"><small class="field-error" data-error-for="full_name"></small></div>'
        . '<div class="field"><label for="phone">رقم التواصل</label><input id="phone" name="phone" class="ltr-input" type="tel" inputmode="tel" autocomplete="tel" required placeholder="اكتب رقمك بالطريقة المعتادة" dir="ltr"><small class="field-error" data-error-for="phone"></small></div>'
        . '<div class="field"><label for="service">الخدمة</label><select id="service" name="service" required>' . $options . '</select><small class="field-error" data-error-for="service"></small></div>'
        . '<label class="consent"><input type="checkbox" name="privacy_consent" value="1" required><span>أوافق على استخدام بياناتي للتواصل بخصوص الطلب وفق <a href="/سياسة-الخصوصية/" target="_blank" rel="noopener">سياسة الخصوصية</a>.</span></label><small class="field-error consent-error" data-error-for="privacy_consent"></small>'
        . '<button class="btn btn-primary btn-block" type="submit"><span>' . e($page['form_cta']) . '</span>' . icon_svg('arrow') . '</button><div class="form-status" id="formStatus" role="status" aria-live="polite"></div></form></aside>';
}

function trust_strip(): string {
    $items = [['briefcase','فريق قانوني','تخصصات متعددة داخل المكتب'],['building','مكتب جدة','اجتماعات حضورية بخصوصية'],['document','مرونة رقمية','اجتماعات ومتابعة عن بُعد'],['shield','أفراد وشركات','خدمة حسب طبيعة الملف']];
    $html = '<section class="trust-strip"><div class="container trust-grid">';
    foreach ($items as [$icon,$title,$text]) $html .= '<div class="trust-item"><span class="icon-box">' . icon_svg($icon) . '</span><div><strong>' . e($title) . '</strong><small>' . e($text) . '</small></div></div>';
    return $html . '</div></section>';
}

function scope_cards(array $cards): string {
    $icons = ['briefcase','document','scale','building'];
    $html = '<div class="scope-grid">';
    foreach ($cards as $i => $card) $html .= '<article class="scope-card"><span class="scope-index">0' . ($i + 1) . '</span><span class="icon-box">' . icon_svg($icons[$i % count($icons)]) . '</span><h3>' . e($card[0]) . '</h3><p>' . e($card[1]) . '</p></article>';
    return $html . '</div>';
}

function process_section(): string {
    $steps = [['01','نبدأ من المطلوب','ترسل بيانات التواصل ونوع الخدمة، بدون تفاصيل مرهقة في البداية.'],['02','الفريق يراجع الملف','نحدد التخصص المناسب وما نحتاجه من معلومات أو مستندات.'],['03','نرتب التواصل','حضوريًا في جدة أو عن بُعد، ثم نوضح نطاق العمل والخطوة التالية.']];
    $html = '<div class="steps-grid">';
    foreach ($steps as [$n,$title,$text]) $html .= '<article class="step-card"><span class="step-number">' . $n . '</span><h3>' . e($title) . '</h3><p>' . e($text) . '</p></article>';
    return $html . '</div>';
}

function faq_html(array $faq): string {
    $html = '<div class="faq-list">';
    foreach ($faq as [$q,$a]) $html .= '<details><summary>' . e($q) . '<span class="faq-plus">+</span></summary><p>' . e($a) . '</p></details>';
    return $html . '</div>';
}

function service_navigation(string $current): string {
    $html = '<div class="service-nav-grid">';
    foreach (internal_service_links() as $path => $label) {
        if ($path === $current) continue;
        $html .= '<a class="service-link-card" href="' . e($path) . '"><span>' . e($label) . '</span>' . icon_svg('arrow') . '</a>';
    }
    return $html . '</div>';
}

function office_experience_section(): string {
    return '<section class="office-story"><div class="container office-story-grid"><figure class="office-photo office-photo-main" role="img" aria-label="مكتب إنكاف في جدة"></figure><div class="office-story-copy"><span class="section-label">من جدة</span><h2>مكتب قانوني بهدوء يليق بالقرارات المهمة.</h2><p>مساحة عمل صُممت للخصوصية والتركيز، واجتماعات تُدار بهدوء واحتراف، مع حضور واضح للهوية السعودية وهوية إنكاف.</p><div class="office-facts"><span>هوية سعودية</span><span>خصوصية في الاجتماعات</span><span>تجربة مهنية</span></div></div></div></section>';
}

function team_section(): string {
    return '<section class="team-section"><div class="container team-grid"><div class="team-copy"><span class="section-label light">فريق إنكاف</span><h2>أكثر من محامٍ، ومسار يناسب طبيعة الملف.</h2><p>يضم المكتب فريقًا قانونيًا يعمل على ملفات الأفراد والشركات، وتوزع الأعمال حسب طبيعة الطلب والتخصص المطلوب.</p><a class="text-link" href="#form">ابدأ بطلبك' . icon_svg('arrow') . '</a></div><figure class="team-photo" role="img" aria-label="محامٍ من فريق إنكاف أثناء العمل"></figure></div></section>';
}

function digital_section(): string {
    return '<section class="digital-section"><div class="container digital-grid digital-grid-clean"><div class="digital-monogram" aria-hidden="true"><span>ENKAF</span><b>LEGAL</b></div><div class="digital-copy"><span class="section-label">مرونة في التواصل</span><h2>خدمة قانونية حديثة، بدون أن تفقد جدية العلاقة.</h2><p>يمكن ترتيب الاجتماعات عن بُعد ومراجعة المستندات ومتابعة التواصل رقميًا عندما يكون ذلك أنسب للعميل وللفريق.</p><div class="digital-list"><div><strong>اجتماعات مرئية</strong><span>للنقاش والمتابعة بدون زيارة في كل مرة.</span></div><div><strong>تبادل المستندات</strong><span>تنظيم الملفات والمراجعات بطريقة أسهل.</span></div><div><strong>متابعة مرنة</strong><span>حضوري أو رقمي حسب طبيعة الطلب.</span></div></div></div></div></section>';
}

function footer_html(): string {
    $cfg = site_config();
    return '<footer class="site-footer"><div class="container footer-grid"><div class="footer-brand"><img src="/assets/img/enkaf-logo-white.png" width="240" height="51" alt="إنكاف"><p>إنكاف للمحاماة والاستشارات القانونية. خدمات قانونية للأفراد والشركات في المملكة العربية السعودية.</p></div><div><h3>الخدمات</h3><a href="/محامي-واستشارات-قانونية/">الاستشارات القانونية</a><a href="/محامي-شركات-وتأسيس-شركات/">الشركات والعقود</a><a href="/قضايا-تجارية-وتحصيل-ديون/">القضايا التجارية</a><a href="/تسجيل-علامة-تجارية-والملكية-الفكرية/">الملكية الفكرية</a><a href="/محامي-عقاري-وتوثيق-عقود/">العقار</a></div><div><h3>التواصل</h3><a href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call">' . icon_svg('phone') . '<bdi dir="ltr">' . e($cfg['phone_display']) . '</bdi></a><a href="mailto:' . e($cfg['email_primary']) . '">' . icon_svg('mail') . e($cfg['email_primary']) . '</a><a href="/سياسة-الخصوصية/">سياسة الخصوصية</a></div></div><div class="container footer-bottom"><span>© ' . date('Y') . ' إنكاف. جميع الحقوق محفوظة.</span><span>المعلومات المنشورة عامة ولا تمثل ضمانًا لنتيجة قانونية أو قضائية.</span></div></footer>';
}

function floating_actions(): string {
    $cfg = site_config();
    $wa = 'https://wa.me/' . $cfg['whatsapp'] . '?text=' . rawurlencode('مرحبًا إنكاف، أرغب في استشارة قانونية.');
    return '<div class="floating-actions" aria-label="تواصل سريع"><a class="float-btn wa" href="' . e($wa) . '" target="_blank" rel="noopener" data-event="click_whatsapp" aria-label="واتساب">' . icon_svg('whatsapp') . '<span>واتساب</span></a><a class="float-btn call" href="tel:' . e($cfg['phone_e164']) . '" data-event="click_call" aria-label="اتصال">' . icon_svg('phone') . '<span>اتصال</span></a></div>';
}

function page_html(array $page): string {
    $chips = '';
    foreach ($page['chips'] as $chip) $chips .= '<span class="hero-chip">' . icon_svg('check') . e($chip) . '</span>';
    $body = '<body class="theme-' . e($page['theme']) . '">' . gtm_body_fallback() . header_html()
        . '<main><section class="hero"><div class="hero-media" role="img" aria-label="مكتب إنكاف في جدة"></div><div class="hero-shade"></div><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">' . e($page['eyebrow']) . '</span><h1>' . e($page['h1']) . '</h1><p class="hero-intro">' . e($page['intro']) . '</p><div class="hero-chips">' . $chips . '</div><div class="hero-meta"><span>جدة</span><span>أفراد وشركات</span><span>اجتماعات حضورية وعن بُعد</span></div></div>' . form_html($page) . '</div></section>'
        . trust_strip()
        . '<section class="section section-paper"><div class="container"><div class="section-heading"><span class="section-label">نطاق الخدمة</span><h2>' . e($page['section_title']) . '</h2><p>' . e($page['section_intro']) . '</p></div>' . scope_cards($page['scope_cards']) . '</div></section>'
        . office_experience_section() . team_section() . digital_section()
        . '<section class="section section-green process-wrap"><div class="container"><div class="section-heading light"><span class="section-label light">طريقة العمل</span><h2>من التواصل الأول إلى الخطوة القانونية التالية</h2><p>بداية مختصرة، ثم مراجعة قانونية منظمة، ثم تواصل بالطريقة الأنسب للملف.</p></div>' . process_section() . '</div></section>'
        . '<section class="section section-paper"><div class="container"><div class="section-heading"><span class="section-label">الأسئلة الشائعة</span><h2>أسئلة قبل إرسال الطلب</h2></div>' . faq_html($page['faq']) . '</div></section>'
        . '<section class="section section-links"><div class="container"><div class="section-heading compact"><span class="section-label">خدمات مرتبطة</span><h2>خدمات إنكاف القانونية</h2></div>' . service_navigation($page['slug']) . '</div></section>'
        . '<section class="final-cta"><div class="container final-cta-grid"><div><span class="eyebrow">ابدأ الآن</span><h2>ابدأ بتواصل قانوني واضح.</h2><p>اترك بيانات التواصل الأساسية وسيتولى الفريق ترتيب الخطوة التالية.</p></div><a class="btn btn-paper" href="#form">إرسال طلب' . icon_svg('arrow') . '</a></div></section></main>'
        . footer_html() . floating_actions() . '<script src="/assets/js/site.js?v=' . e(BUILD_ID) . '" defer></script></body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}

function privacy_html(): string {
    $page = ['id'=>'PRIVACY','slug'=>'/سياسة-الخصوصية/','theme'=>'legal','title'=>'سياسة الخصوصية | إنكاف','description'=>'سياسة خصوصية إنكاف وشرح البيانات التي يتم جمعها عبر نماذج التواصل واستخدامها وحمايتها.','faq'=>[]];
    $body = '<body class="theme-legal">' . gtm_body_fallback() . header_html() . '<main class="legal-page"><section class="legal-hero"><div class="container"><span class="eyebrow">الخصوصية والبيانات</span><h1>سياسة الخصوصية</h1><p>الإصدار: ' . e(PRIVACY_VERSION) . '</p></div></section><section class="section section-paper"><div class="container legal-copy"><h2>البيانات التي نجمعها</h2><p>عند إرسال نموذج التواصل، نجمع الاسم ورقم التواصل ونوع الخدمة القانونية، إضافة إلى بيانات مصدر الزيارة والحملة ومعرّفات النقر عند توفرها.</p><h2>الغرض من الاستخدام</h2><p>نستخدم البيانات لفهم الطلب والتواصل معك وتحديد نوع الخدمة وإدارة ومتابعة الطلب وقياس مصادر الحملات.</p><h2>الموافقة</h2><p>لا يتم إرسال النموذج قبل تأكيد الموافقة على هذه السياسة، ويتم حفظ حالة الموافقة وتوقيتها وإصدار السياسة مع سجل الطلب.</p><h2>المشاركة والحماية</h2><p>يقتصر الوصول إلى بيانات الطلبات على الأشخاص أو مقدمي الخدمات المصرح لهم بالقدر اللازم لتشغيل الخدمة ومتابعة الطلب. لا نضع الاسم أو رقم التواصل في روابط صفحات الشكر.</p></div></section></main>' . footer_html() . '<script src="/assets/js/site.js?v=' . e(BUILD_ID) . '" defer></script></body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}

function thank_you_html(): string {
    $page = ['id'=>'THANKS','slug'=>'/شكرا/','theme'=>'thanks','title'=>'تم استلام طلبك | إنكاف','description'=>'تم استلام طلب التواصل القانوني لدى إنكاف.','faq'=>[]];
    $ref = preg_replace('/[^A-Z0-9\-]/', '', strtoupper((string)($_GET['ref'] ?? '')));
    $cfg = site_config();
    $waBase = 'https://wa.me/' . $cfg['whatsapp'];
    $body = '<body class="theme-thanks">' . gtm_body_fallback() . header_html() . '<main class="thanks-page"><section class="thanks-card"><span class="success-mark">' . icon_svg('check') . '</span><span class="section-label">تم حفظ الطلب</span><h1>تم استلام طلبك</h1><p>سيتم مراجعة نوع الخدمة والتواصل معك لتحديد الخطوة التالية.</p>' . ($ref ? '<div class="lead-ref">رقم الطلب: <bdi dir="ltr">' . e($ref) . '</bdi></div>' : '') . '<div class="thanks-actions"><a class="btn btn-primary" id="thankYouWhatsapp" href="' . e($waBase) . '" target="_blank" rel="noopener" data-event="whatsapp_after_form">متابعة عبر واتساب' . icon_svg('whatsapp') . '</a><a class="btn btn-outline" href="/">الرئيسية</a></div></section></main>' . footer_html() . '<script>window.ENKAF_THANK_YOU_REF=' . json_encode($ref) . ';window.ENKAF_WA=' . json_encode($cfg['whatsapp']) . ';</script><script src="/assets/js/site.js?v=' . e(BUILD_ID) . '" defer></script></body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}

function not_found_html(): string {
    $page = ['id'=>'404','slug'=>'/','theme'=>'legal','title'=>'الصفحة غير موجودة | إنكاف','description'=>'الصفحة المطلوبة غير موجودة.','faq'=>[]];
    $body = '<body class="theme-legal">' . header_html() . '<main class="thanks-page"><section class="thanks-card"><span class="section-label">404</span><h1>الصفحة غير موجودة</h1><p>قد يكون الرابط تغير أو تمت كتابة العنوان بصورة غير صحيحة.</p><div class="thanks-actions"><a class="btn btn-primary" href="/">العودة للرئيسية</a></div></section></main>' . footer_html() . '</body>';
    return '<!doctype html><html lang="ar" dir="rtl">' . head_html($page) . $body . '</html>';
}
