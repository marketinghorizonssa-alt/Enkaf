/* ENKAF entity logos + contextual authority copy — 2026-08-24 */
(function(){
  'use strict';

  var ROOT='/assets/img/entities/';
  var codeSet=new Set([
    'MOJ','NOTARY','RER','LAW','REX','SAIP','SCCA','MOC','MC','SBC','MISA','INV',
    'NAJIZ','COURT','CT','ENF','INFATH','GCC','REGA','EXEC','BOE','GOV'
  ]);

  function pickLogo(context,code){
    var s=(context||'')+' '+(code||'');
    if(/إنفاذ|الإسناد والتصفية|INFATH|ENF/.test(s)) return 'infath.png';
    if(/البورصة العقارية|REX/.test(s)) return 'rex.png';
    if(/السجل العقاري|التسجيل العيني|\bRER\b/.test(s)) return 'rer.png';
    if(/الهيئة العامة للعقار|\bREGA\b/.test(s)) return 'rega.png';
    if(/الهيئة السعودية للملكية الفكرية|SAIP|نظام العلامات التجارية لدول مجلس التعاون|\bGCC\b/.test(s)) return 'saip.png';
    if(/المركز السعودي للتحكيم التجاري|SCCA/.test(s)) return 'scca.png';
    if(/المركز السعودي للأعمال|مركز الأعمال السعودي|\bSBC\b/.test(s)) return 'sbc.png';
    if(/وزارة الاستثمار|نظام الاستثمار|MISA|\bINV\b/.test(s)) return 'misa.png';
    if(/وزارة التجارة|نظام الشركات|\bMOC\b|\bMC\b/.test(s)) return 'mc.png';
    if(/ناجز|NAJIZ/.test(s)) return 'najiz.png';
    if(/وزارة العدل|كتابة العدل|المحكمة التجارية|المحاكم السعودية|نظام المحاكم التجارية|MOJ|NOTARY|COURT|\bCT\b|\bEXEC\b/.test(s)) return 'moj.png';
    if(/\bLAW\b|نظام /.test(s)) return 'law.svg';
    return 'gov.svg';
  }

  function replaceCodes(){
    var els=document.querySelectorAll('span,b,strong,em,i,small');
    els.forEach(function(el){
      if(el.children.length) return;
      var code=(el.textContent||'').trim().toUpperCase();
      if(!codeSet.has(code)) return;
      var host=el.closest('a,li') || el.parentElement;
      var context=host ? (host.textContent||'') : '';
      var file=pickLogo(context,code);
      var img=document.createElement('img');
      img.src=ROOT+file;
      img.alt='';
      img.loading='lazy';
      img.decoding='async';
      if(file==='law.svg'||file==='gov.svg') img.dataset.generic='1';
      el.textContent='';
      el.classList.add('enkaf-entity-logo-slot');
      el.setAttribute('aria-hidden','true');
      el.appendChild(img);
    });
  }

  var copy={
    general:{
      title:'الجهات العدلية المرتبطة بمسار الخدمة',
      sub:'نتابع الإجراء عبر الجهة والمنصة المختصة بحسب نوع الطلب ومرحلته.'
    },
    corporate:{
      title:'جهات تنظيم وتأسيس الأعمال في المملكة',
      sub:'نربط خطوات التأسيس والحوكمة والاستثمار بالجهات والأنظمة المنظمة لكل إجراء.'
    },
    disputes:{
      title:'جهات التقاضي والتحكيم والتنفيذ',
      sub:'نحدد الجهة القضائية أو التحكيمية ومسار التنفيذ بحسب طبيعة النزاع والمستندات.'
    },
    ip:{
      title:'جهات حماية وتسجيل الملكية الفكرية',
      sub:'نتابع التسجيل والحماية والاعتراض وفق الجهة المختصة والنظام المنطبق على الحق.'
    },
    realestate:{
      title:'جهات التوثيق والتسجيل والخدمات العقارية',
      sub:'نتابع الإجراء العقاري عبر الجهة والمنصة المختصة من التوثيق وحتى التسجيل والتصرف.'
    }
  };

  function pageTheme(){
    var cls=document.body.className||'';
    if(/theme-corporate/.test(cls)) return 'corporate';
    if(/theme-disputes/.test(cls)) return 'disputes';
    if(/theme-ip/.test(cls)) return 'ip';
    if(/theme-realestate/.test(cls)) return 'realestate';
    if(/theme-general/.test(cls)) return 'general';
    return '';
  }

  function contextualizeHeading(){
    var theme=pageTheme();
    if(!theme||!copy[theme]) return;
    var candidates=document.querySelectorAll('h2,h3,h4,strong,b,span');
    var heading=null;
    candidates.forEach(function(el){
      if(heading) return;
      var t=(el.textContent||'').trim();
      if(t==='جهات وأنظمة مرتبطة بالخدمة' || t==='جهات وأنظمة ذات صلة' || /جهات وأنظمة.*الخدمة/.test(t)) heading=el;
    });
    if(!heading) return;
    heading.textContent=copy[theme].title;
    var parent=heading.parentElement;
    if(!parent) return;
    var sub=null;
    Array.prototype.slice.call(parent.children).forEach(function(el){
      if(sub||el===heading) return;
      var tag=el.tagName;
      var t=(el.textContent||'').trim();
      if((tag==='P'||tag==='SMALL'||tag==='SPAN') && /بحسب|نوع الطلب|الإجراء|الاختصاص/.test(t)) sub=el;
    });
    if(!sub && heading.nextElementSibling && /^(P|SMALL|SPAN)$/.test(heading.nextElementSibling.tagName)) sub=heading.nextElementSibling;
    if(sub) sub.textContent=copy[theme].sub;
  }

  function run(){
    replaceCodes();
    contextualizeHeading();
  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',run,{once:true});
  else run();
})();
