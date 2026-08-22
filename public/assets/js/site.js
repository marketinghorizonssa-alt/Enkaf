(() => {
  'use strict';

  const qs = (s, root = document) => root.querySelector(s);
  const qsa = (s, root = document) => Array.from(root.querySelectorAll(s));
  const attribKeys = [
    'utm_source','utm_medium','utm_campaign','utm_term','utm_content',
    'gclid','gbraid','wbraid','ttclid','fbclid',
    'campaignid','adgroupid','creative','keyword','matchtype','device','network','targetid','loc_physical_ms','gad_source','gad_campaignid'
  ];

  function safeGet(storage, key) {
    try { return storage.getItem(key) || ''; } catch (_) { return ''; }
  }

  function safeSet(storage, key, value) {
    try { storage.setItem(key, value); } catch (_) {}
  }

  function sessionId() {
    let id = safeGet(sessionStorage, 'enkaf_session_id');
    if (!id) {
      id = (self.crypto && crypto.randomUUID) ? crypto.randomUUID() : `s-${Date.now()}-${Math.random().toString(16).slice(2)}`;
      safeSet(sessionStorage, 'enkaf_session_id', id);
    }
    return id;
  }

  function captureAttribution() {
    const params = new URLSearchParams(location.search);
    attribKeys.forEach((key) => {
      const incoming = params.get(key);
      if (incoming) safeSet(sessionStorage, `enkaf_${key}`, incoming);
    });
    if (!safeGet(sessionStorage, 'enkaf_first_landing_url')) safeSet(sessionStorage, 'enkaf_first_landing_url', location.href);
    if (!safeGet(sessionStorage, 'enkaf_referrer') && document.referrer) safeSet(sessionStorage, 'enkaf_referrer', document.referrer);
  }

  function asciiDigits(value) {
    const map = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'};
    return String(value || '').replace(/[٠-٩۰-۹]/g, d => map[d] || d);
  }

  function ensureHiddenInputs(form) {
    attribKeys.forEach((key) => {
      if (qs(`[name="${key}"]`, form)) return;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      form.appendChild(input);
    });
  }

  captureAttribution();

  function applyV6Presentation() {
    if (!document.querySelector('link[data-enkaf-v6]')) {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = '/assets/css/luxury-v6.css?v=20260822';
      link.dataset.enkafV6 = '1';
      document.head.appendChild(link);
    }

    const theme = Array.from(document.body.classList).find(c => c.startsWith('theme-'))?.replace('theme-', '') || '';
    const visualMap = {
      home:       { a:'/assets/img/hero-home.webp',       b:'/assets/img/hero-general.webp' },
      general:    { a:'/assets/img/hero-general.webp',    b:'/assets/img/hero-home.webp' },
      corporate:  { a:'/assets/img/hero-corporate.webp',  b:'/assets/img/hero-general.webp' },
      disputes:   { a:'/assets/img/hero-disputes.webp',   b:'/assets/img/hero-home.webp' },
      ip:         { a:'/assets/img/hero-ip.webp',         b:'/assets/img/hero-corporate.webp' },
      realestate: { a:'/assets/img/hero-realestate.webp', b:'/assets/img/hero-corporate.webp' },
    };
    const visuals = visualMap[theme];
    if (visuals) {
      const officeImg = qs('.office-visual img');
      const digitalImg = qs('.digital-visual img');
      if (officeImg) officeImg.src = visuals.a;
      if (digitalImg) digitalImg.src = visuals.b;
    }

    if (theme === 'home') {
      const h1 = qs('.hero-copy h1');
      const intro = qs('.hero-copy .hero-intro');
      if (h1) h1.textContent = 'نخبة من المحامين بكفاءة عالية وخبرة سعودية برؤية حديثة';
      if (intro) intro.textContent = 'يجمع إنكاف نخبة من المحامين ذوي الكفاءة والخبرات الممتدة في السوق السعودي مع رؤية واضحة وحديثة تعتمد على التقنية والتنظيم والعمل الحضوري وعن بُعد؛ لتقديم استشارات وتمثيل وصياغة قانونية بجودة عالية وتواصل أكثر وضوحًا للأفراد والشركات.';
      const chips = qsa('.hero-details span');
      ['خبرات ممتدة في السوق السعودي','فريق قانوني متعدد التخصصات','تواصل ومتابعة رقمية منظمة'].forEach((text, i) => { if (chips[i]) chips[i].textContent = text; });
      const heading = qs('.services-section .section-heading h2');
      const headingP = qs('.services-section .section-heading p');
      const headingLabel = qs('.services-section .section-label');
      if (headingLabel) headingLabel.textContent = 'لماذا إنكاف';
      if (heading) heading.textContent = 'خبرة قانونية راسخة بمنهج عمل يواكب احتياجك اليوم';
      if (headingP) headingP.textContent = 'الفخامة في إنكاف ليست مظهرًا فقط؛ بل مستوى في القراءة القانونية، جودة الصياغة، وضوح التواصل، وتنظيم المتابعة من أول تواصل وحتى تنفيذ نطاق العمل المتفق عليه.';
      const cards = qsa('.services-section .scope-card');
      const homeCards = [
        ['خبرة في السوق السعودي','فهم للأنظمة وبيئة الأعمال والسياق المحلي يساعد على قراءة الملف ضمن واقعه العملي.'],
        ['كفاءة وتخصص','توجيه الطلب إلى التخصص القانوني الأنسب مع ربط الرأي بالمستندات والوقائع والهدف.'],
        ['رؤية حديثة وتقنية','استخدام أدوات تنظيم ومتابعة رقمية لتسهيل التواصل وتبادل المعلومات ومتابعة الطلب.'],
        ['خدمة للأفراد والشركات','دعم قانوني من الاستشارة والعقد إلى النزاع والمطالبة والملكية الفكرية والعقار.'],
      ];
      cards.forEach((card, i) => {
        const data = homeCards[i];
        if (!data) return;
        const title = qs('h3', card); const copy = qs('p', card);
        if (title) title.textContent = data[0];
        if (copy) copy.textContent = data[1];
      });
      document.title = 'إنكاف للمحاماة والاستشارات القانونية | خبرة سعودية ورؤية حديثة';
      const desc = qs('meta[name="description"]');
      if (desc) desc.content = 'إنكاف مكتب محاماة سعودي في جدة يجمع خبرات قانونية ممتدة في السوق السعودي مع أسلوب حديث وتقني في الاستشارات والتمثيل والخدمات القانونية للأفراد والشركات.';
    }
  }

  applyV6Presentation();

  const form = qs('#leadForm');
  if (form) {
    ensureHiddenInputs(form);
    const status = qs('#formStatus', form);
    const button = qs('button[type="submit"]', form);
    const service = qs('[name="service"]', form);
    const nameInput = qs('[name="full_name"]', form);
    const phoneInput = qs('[name="phone"]', form);
    const consentInput = qs('[name="privacy_consent"]', form);
    let started = false;

    if (nameInput && !nameInput.value) nameInput.value = safeGet(sessionStorage, 'enkaf_form_name');
    if (phoneInput && !phoneInput.value) phoneInput.value = safeGet(sessionStorage, 'enkaf_form_phone');
    if (consentInput) consentInput.checked = true;

    [nameInput, phoneInput].forEach((input) => {
      if (!input) return;
      input.addEventListener('input', () => {
        safeSet(sessionStorage, input === nameInput ? 'enkaf_form_name' : 'enkaf_form_phone', input.value.trim());
      });
    });

    function dispatch(name, detail = {}) {
      document.dispatchEvent(new CustomEvent(`enkaf:${name}`, { detail }));
    }

    function fillHidden() {
      const map = {
        landing_path: location.pathname,
        landing_url: location.href.split('#')[0],
        referrer: safeGet(sessionStorage, 'enkaf_referrer') || document.referrer,
        first_landing_url: safeGet(sessionStorage, 'enkaf_first_landing_url') || location.href,
        session_id: sessionId(),
      };
      attribKeys.forEach((key) => map[key] = safeGet(sessionStorage, `enkaf_${key}`));
      Object.entries(map).forEach(([key, value]) => {
        const input = qs(`[name="${key}"]`, form);
        if (input) input.value = value || '';
      });
    }

    function clearErrors() {
      qsa('.field-error', form).forEach(el => el.textContent = '');
      qsa('[aria-invalid="true"]', form).forEach(el => el.removeAttribute('aria-invalid'));
      status.textContent = '';
      status.classList.remove('error');
    }

    function showErrors(fields = {}) {
      let firstInvalid = null;
      Object.entries(fields).forEach(([key, message]) => {
        const target = qs(`[data-error-for="${key}"]`, form);
        const input = qs(`[name="${key}"]`, form);
        if (target) target.textContent = message;
        if (input) {
          input.setAttribute('aria-invalid', 'true');
          if (!firstInvalid) firstInvalid = input;
        }
      });
      if (firstInvalid && typeof firstInvalid.focus === 'function') firstInvalid.focus({ preventScroll: true });
    }

    function clientValidate() {
      const fields = {};
      const name = (nameInput?.value || '').trim();
      const rawPhone = asciiDigits(phoneInput?.value || '');
      const phoneDigits = rawPhone.replace(/\D/g, '');
      if (name.length < 2) fields.full_name = 'اكتب الاسم بشكل صحيح.';
      if (phoneDigits.length < 7 || phoneDigits.length > 15) fields.phone = 'اكتب رقم تواصل صحيح بأي صيغة مناسبة.';
      if (!service.value) fields.service = 'اختر نوع الخدمة القانونية.';
      if (!consentInput.checked) fields.privacy_consent = 'يلزم الموافقة على سياسة الخصوصية.';
      return fields;
    }

    form.addEventListener('focusin', () => {
      if (!started) {
        started = true;
        dispatch('form-start', { landing_page_id: qs('[name="landing_page_id"]', form).value, landing_path: location.pathname });
      }
    }, { once: true });

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      clearErrors();
      fillHidden();
      const localErrors = clientValidate();
      if (Object.keys(localErrors).length) {
        showErrors(localErrors);
        status.textContent = 'راجع البيانات المطلوبة ثم حاول مرة أخرى.';
        status.classList.add('error');
        return;
      }

      const originalText = qs('span', button)?.textContent || 'إرسال الطلب';
      button.disabled = true;
      if (qs('span', button)) qs('span', button).textContent = 'جارٍ حفظ الطلب...';
      status.textContent = '';
      const formData = new FormData(form);

      try {
        const response = await fetch('/api/lead/', { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.ok) {
          if (data.fields) showErrors(data.fields);
          throw new Error(data.message || 'تعذر إرسال الطلب الآن.');
        }
        const selectedText = service.options[service.selectedIndex]?.text || '';
        safeSet(sessionStorage, 'enkaf_last_name', (nameInput?.value || '').trim());
        safeSet(sessionStorage, 'enkaf_last_phone', (phoneInput?.value || '').trim());
        safeSet(sessionStorage, 'enkaf_last_service_label', selectedText);
        safeSet(sessionStorage, 'enkaf_last_lead_ref', data.lead_id || '');
        status.textContent = 'تم حفظ طلبك بنجاح. يتم تحويلك الآن...';
        dispatch('lead-success', {
          lead_id: data.lead_id,
          landing_page_id: qs('[name="landing_page_id"]', form).value,
          landing_path: location.pathname,
          service_key: service.value,
        });
        window.setTimeout(() => { location.assign(data.thank_you_url || '/شكرا/'); }, 650);
      } catch (error) {
        status.textContent = error.message || 'تعذر إرسال الطلب الآن. حاول مرة أخرى أو تواصل معنا مباشرة.';
        status.classList.add('error');
        dispatch('form-submit-error', { landing_path: location.pathname, message: status.textContent });
        button.disabled = false;
        if (qs('span', button)) qs('span', button).textContent = originalText;
      }
    });
  }

  const thankYouWhatsApp = qs('#thankYouWhatsapp');
  if (thankYouWhatsApp) {
    const ref = window.ENKAF_THANK_YOU_REF || safeGet(sessionStorage, 'enkaf_last_lead_ref');
    const name = safeGet(sessionStorage, 'enkaf_last_name');
    const phone = safeGet(sessionStorage, 'enkaf_last_phone');
    const service = safeGet(sessionStorage, 'enkaf_last_service_label');
    const parts = ['السلام عليكم إنكاف، أرسلت طلب استشارة عبر الموقع وأرغب في استكماله عبر واتساب.'];
    if (name) parts.push(`الاسم: ${name}`);
    if (phone) parts.push(`رقم التواصل: ${phone}`);
    if (service) parts.push(`الخدمة: ${service}`);
    if (ref) parts.push(`رقم الطلب: ${ref}`);
    thankYouWhatsApp.href = `https://wa.me/${window.ENKAF_WA || '966559556606'}?text=${encodeURIComponent(parts.join('\n'))}`;
    document.dispatchEvent(new CustomEvent('enkaf:thank-you-view', { detail: { lead_id: ref || '' } }));
  }
})();
