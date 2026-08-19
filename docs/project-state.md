# ENKAF Performance Landing Project - Source of Truth

## Identity
- Client: ENKAF / إنكاف
- Brand: إنكاف للمحاماة والاستشارات القانونية
- Intended production domain: https://enkaf.sa/
- Temporary review URL: https://enkaf.hositee.com/
- Primary email: Info@enkaf.sa
- Work email: Work@enkaf.sa
- Approved phone supplied by client: 0559556606 / +966559556606
- WhatsApp: 966559556606
- Brand palette: Midnight Green #29332B, Parchment #F1EAD8, Umber #683F18, Burnt Umber #74391B, Sandstone Beige #D3BFA6
- Arabic brand font reference: DIN Arabice; production uses a fast system fallback stack unless a licensed webfont is supplied.

## Approved landing architecture
Approved by user from the Keyword Planner structure.
- LP01 /محامي-واستشارات-قانونية/
- LP02 /محامي-شركات-وتأسيس-شركات/
- LP03 /قضايا-تجارية-وتحصيل-ديون/
- LP04 /تسجيل-علامة-تجارية-والملكية-الفكرية/
- LP05 /محامي-عقاري-وتوثيق-عقود/

## Current form / lead schema
Visible fields:
- full_name
- phone
- service
- privacy_consent

UX rules:
- Browser autocomplete is enabled for name and telephone fields.
- Dedicated pages keep the relevant service preselected so the user usually types only name + phone, then confirms privacy consent.
- Phone input no longer enforces a Saudi-only visual format. Server/client validation accepts a reasonable 7–15 digit phone in common local or international formatting.
- Known Saudi forms such as 05xxxxxxxx, 5xxxxxxxx, 966..., +966..., and 00966... normalize to Saudi E.164.
- Non-Saudi/local forms such as 01000448589 are preserved as a normalized local phone value instead of being rejected or having +966 forced onto them.
- Explicit privacy consent remains required and is not prechecked automatically.

Automatic attribution captured when available:
- landing_page_id, landing_path, landing_url
- utm_source, utm_medium, utm_campaign, utm_term, utm_content
- gclid, gbraid, wbraid, ttclid, fbclid
- campaignid, adgroupid, creative, keyword, matchtype, device, network, targetid, loc_physical_ms
- gad_source, gad_campaignid when present in the landing URL/session
- referrer, first_landing_url, session_id
- consent_version, consent_at, server_submit_at

Important limitation:
- A normal ad click does not expose the visitor's private name/phone to the website. The site can auto-capture campaign/click metadata and use browser autofill, but personal contact data still requires user/browser-approved entry or a separately configured ad-platform lead form.

## Lead durability and conversion rule
- Same-origin POST: /api/lead/
- Private durable store: locked NDJSON outside public web root
- Lead reference: ENK-YYMMDD-HHMMSS-XXXX
- Private feed: /api/leads.csv/?token=... only when ENKAF_FEED_TOKEN is configured
- lead_form_success must fire only after durable server acknowledgement.
- Call and WhatsApp remain separate outcomes.
- No personal data is placed in thank-you URLs or tracking parameters.

## Current tracking / Ads state
- ENKAF GTM container: not created yet
- Google Ads account under manager: not present in last verified manager sync
- Google Ads conversions: not created yet
- Search campaign: not created yet
- Launch approval: not given
- Campaigns must remain paused until final live-domain QA and explicit launch approval.

## GitHub source of truth
- Repository: marketinghorizonssa-alt/Enkaf
- Initial site PR: #1
- Reviewed initial website release: 9564d10852efaedaa248f21788fcd75c3dd4e55a
- Deployment reliability PRs: #2 and #3
- Runtime QA PRs: #4, #5, #6
- Temporary hositee preview tooling PR: #7
- Visual / conversion refresh PR: #8
- PR #8 merged to main on 2026-08-19.
- Current visual-refresh merge commit: 73fc50a9e4d22106ae3268e5a46e1d192b6fcfe0
- Preview deployment script: scripts/deploy-hositee-preview.sh
- Production deployment script: scripts/deploy-hostinger.sh

## Visual refresh - 2026-08-19
User requested a less sparse, more premium/catchy appearance without sacrificing speed, SEO alignment, or lead performance.

Implemented:
- Added a lightweight local legal/corporate SVG visual: public/assets/img/enkaf-legal-visual.svg
- Added a local visual override layer: public/assets/css/refresh.css
- No external image CDN, external font, slider, autoplay video, canvas effect, or heavy third-party visual library was added.
- Hero now uses multiple approved ENKAF identity colors instead of a nearly single-color presentation.
- Added intent-sensitive hook lines to the hero for corporate, disputes, IP, real-estate, general and home contexts.
- Added branded visual badges, richer form treatment, colored card accents, section gradients, and improved visual rhythm.
- Form remains prominent above the fold and the floating call/WhatsApp paths remain secondary.
- Mobile treatment keeps the hero image compact and removes unnecessary decorative content at narrow widths.
- System font stack changed toward modern platform UI fonts for cleaner Arabic rendering while keeping zero font-download cost.

## Visual/form refresh QA
Verified before/after GitHub merge:
- Branch deployed to https://enkaf.hositee.com/ in review mode.
- Hostinger deployment result for reviewed branch: ENKAF_PREVIEW_DEPLOY_OK.
- Current merged GitHub commit 73fc50a9e4d22106ae3268e5a46e1d192b6fcfe0 was then deployed to the preview and returned ENKAF_PREVIEW_DEPLOY_OK.
- Preview health returned ok=true, service=enkaf-landing-site, review_mode=true.
- Visual asset files refresh.css and enkaf-legal-visual.svg were verified present on the Hostinger preview filesystem.
- Current site.js passed node --check in the available container environment.
- PHP phone-normalization logic was linted/executed against representative values including 01000448589, Saudi local 05..., +966..., 00966..., Arabic digits, and a 5xxxxxxxx Saudi form.
- Live preview POST using phone 01000448589 returned HTTP/API success with a generated ENK-* lead reference.
- The stored QA row preserved phone_normalized=01000448589 and stored campaignid=QA123, proving flexible-phone acceptance plus automatic ad-context persistence.
- The QA lead was then removed from the preview private lead store.
- Hostinger cache was purged after the preview refresh.
- All ENKAF temporary deployment/QA cron jobs were removed after verification; unrelated pre-existing cron jobs for other projects on the hositee account were intentionally left untouched.

Remaining review item:
- Final visual/browser inspection on the refreshed preview should be checked on real desktop and mobile viewport screenshots before calling the visual gate fully complete.

## Production Hostinger state
- Hostinger production target username: u128565677
- Domain root: /home/u128565677/domains/enkaf.sa/public_html
- Old WordPress is no longer the serving source.
- Current public root is the ENKAF application, not WordPress.
- Hostinger origin previously verified for enkaf.sa: 145.223.36.55
- Production PHP expose_php was switched Off and read back as Off.
- Production server-runtime QA previously returned ENKAF_SERVER_QA_OK pages=6 sitemap_urls=7 form_201=true form_422=true robots_allowed=true private_feed_404=true.

## Temporary hositee preview
- Hostname: https://enkaf.hositee.com/
- Hostinger username: u878466595
- Document root: /home/u878466595/domains/hositee.com/public_html/enkaf-site/public
- Application root: /home/u878466595/domains/hositee.com/public_html/enkaf-site
- Private lead data: /home/u878466595/enkaf-preview-private
- Current deployed GitHub commit: 73fc50a9e4d22106ae3268e5a46e1d192b6fcfe0
- Preview stays review-only: noindex/nofollow + robots Disallow: /
- Do not use the preview as a Search Console property or final Google Ads SEO hostname.

## Legacy WordPress archive
- User approved treating old WordPress as disposable legacy content and continuing as if the domain were empty.
- Archive: /home/u128565677/legacy-archives/enkaf/enkaf-wordpress-legacy-20260818-192401.zip
- Archive size: 688M
- SHA-256: 1875f740a7fa9c09ddd97202cd83853ecf9ebc0dd6509d6fdd4e3c59a8c3b5a7
- Database dump is included in the archive.
- unzip -tq returned ZIP_TEST_OK.
- Old WordPress files are not used as production source/design/deployment source.

## enkaf.sa DNS blocker
- Public DNS for enkaf.sa is still not suitable for final external live QA/indexing.
- Nameservers observed: ns10.dnetns.com / ns11.dnetns.com.
- DNS management is therefore outside the current Hostinger hosting access.
- Verified intended Hostinger A target: 145.223.36.55.
- Intended root record: A @ -> 145.223.36.55.
- Intended www record: CNAME www -> enkaf.sa where supported.
- Preserve existing NS, MX and unrelated TXT records unless the domain owner deliberately migrates DNS management.
- The hositee preview is the approved temporary review target while DNET/domain access is unresolved.

## Crawl / launch rules
- Do not submit enkaf.hositee.com to Search Console.
- Do not submit/claim final Search Console completion for enkaf.sa until public DNS, HTTPS, robots, sitemap, forms and mobile/browser QA pass on the real domain.
- Do not create/promote final Google Ads conversion actions or activate campaigns until the approved live domain and end-to-end QA are verified.
- New conversion actions start Secondary and only become Primary after verified testing and explicit approval.
