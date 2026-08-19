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
  function injectRefreshStyles() {
    if (qs('link[data-enkaf-refresh]')) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/assets/css/refresh.css?v=20260819c';
    link.dataset.enkafRefresh = '1';
    document.head.appendChild(link);
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
  function pageHook() {
    const cls = document.body.className;
    if (cls.includes('theme-corporate')) return 'أسّس قرارك التجاري على أرض قانونية أوضح.';
    if (cls.includes('theme-disputes')) return 'في النزاع، وضوح المسار من البداية يصنع فرقًا.';
    if (cls.includes('theme-ip')) return 'احمِ اسمك وحقوقك الفكرية بخطوات منظمة.';
    if (cls.includes('theme-realestate')) return 'العقد الواضح اليوم يقلّل مساحة النزاع غدًا.';
    if (cls.includes('theme-general')) return 'قبل أي خطوة قانونية، خلّي موقفك أوضح.';
    return 'ابدأ بفهم قانوني أوضح قبل القرار التالي.';
  }
  function enhanceVisualDesign() {
    const heroCopy = qs('.hero-copy');
    if (heroCopy && !qs('.hero-hookline', heroCopy)) {
      const hook = document.createElement('div');
      hook.className = 'hero-hookline';
      hook.textContent = pageHook();
      const h1 = qs('h1', heroCopy);
      if (h1) heroCopy.insertBefore(hook, h1);
    }
    if (heroCopy && !qs('.hero-showcase', heroCopy)) {
      const showcase = document.createElement('div');
      showcase.className = 'hero-showcase';
      showcase.setAttribute('aria-hidden', 'true');
      showcase.innerHTML = `
        <img src="/assets/img/enkaf-legal-visual.svg" width="820" height="420" alt="" decoding="async">
        <div class="hero-showcase-badges">
          <span class="hero-showcase-badge b1">فهم قانوني واضح</span>
          <span class="hero-showcase-badge b2">للأفراد والشركات</span>
          <span class="hero-showcase-badge b3">خطوة أولى سريعة</span>
        </div>`;
      heroCopy.appendChild(showcase);
    }

    const form = qs('#leadForm');
    if (!form) return;
    ensureHiddenInputs(form);
    form.autocomplete = 'on';

    const phone = qs('[name="phone"]', form);
    if (phone) {
      phone.placeholder = 'اكتب رقمك بأي صيغة';
      phone.setAttribute('autocomplete', 'tel');
      phone.setAttribute('inputmode', 'tel');
      if (!qs('#phoneHelp', form)) {
        const help = document.createElement('small');
        help.id = 'phoneHelp';
        help.className = 'phone-help';
        help.textContent = 'اكتب الرقم بالطريقة المعتادة لديك؛ لا نطلب منك إضافة كود دولة أو صيغة محددة.';
        const field = phone.closest('.field');
        if (field) field.insertAdjacentElement('afterend', help);
        phone.setAttribute('aria-describedby', 'phoneHelp');
      }
    }

    const intro = qs('.form-intro');
    if (intro && !qs('.form-speed-note')) {
      const note = document.createElement('div');
      note.className = 'form-speed-note';
      note.innerHTML = '<span class="form-speed-icon">✓</span><span><strong>بيانات قليلة فقط.</strong> مصدر الإعلان والحملة ومعرّفات النقر تُحفظ تلقائيًا عند توفرها، والخدمة الأساسية محددة مسبقًا ويمكن تغييرها.</span>';
      intro.insertAdjacentElement('afterend', note);
    }
  }

  injectRefreshStyles();
  captureAttribution();
  enhanceVisualDesign();

  const form = qs('#leadForm');
  if (form) {
    const status = qs('#formStatus', form);
    const button = qs('button[type="submit"]', form);
    const service = qs('[name="service"]', form);
    let started = false;

    function dispatch(name, detail = {}) {
      document.dispatchEvent(new CustomEvent(`enkaf:${name}`, { detail }));
    }
    function fillHidden() {
      ensureHiddenInputs(form);
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
      const name = qs('[name="full_name"]', form).value.trim();
      const rawPhone = asciiDigits(qs('[name="phone"]', form).value);
      const phoneDigits = rawPhone.replace(/\D/g, '');
      if (name.length < 2) fields.full_name = 'اكتب الاسم بشكل صحيح.';
      if (phoneDigits.length < 7 || phoneDigits.length > 15) fields.phone = 'اكتب رقم هاتف صحيح.';
      if (!service.value) fields.service = 'اختر نوع الخدمة القانونية.';
      if (!qs('[name="privacy_consent"]', form).checked) fields.privacy_consent = 'يلزم الموافقة على سياسة الخصوصية.';
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
    const service = safeGet(sessionStorage, 'enkaf_last_service_label');
    const parts = ['مرحبًا إنكاف، أرسلت طلب استشارة عبر الموقع.'];
    if (ref) parts.push(`رقم الطلب: ${ref}`);
    if (service) parts.push(`نوع الخدمة: ${service}`);
    thankYouWhatsApp.href = `https://wa.me/${window.ENKAF_WA || '966559556606'}?text=${encodeURIComponent(parts.join('\n'))}`;
    document.dispatchEvent(new CustomEvent('enkaf:thank-you-view', { detail: { lead_id: ref || '' } }));
  }
})();
