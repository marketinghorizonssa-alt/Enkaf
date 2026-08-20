# ENKAF Performance Landing Project - Source of Truth

## Identity
- Client: ENKAF / إنكاف
- Brand: إنكاف للمحاماة والاستشارات القانونية
- Production domain: https://enkaf.sa/
- Temporary review URL: https://enkaf.hositee.com/
- Primary email: Info@enkaf.sa
- Work email: Work@enkaf.sa
- Approved phone: 0559556606 / +966559556606
- WhatsApp: 966559556606
- Brand colors: Midnight Green #29332B, Parchment #F1EAD8, Umber #683F18, Burnt Umber #74391B, Sandstone Beige #D3BFA6
- Brand Arabic font reference: DIN Arabice. Production uses a fast system fallback stack unless a licensed webfont is supplied.

## Approved landing architecture
- LP01 /محامي-واستشارات-قانونية/
- LP02 /محامي-شركات-وتأسيس-شركات/
- LP03 /قضايا-تجارية-وتحصيل-ديون/
- LP04 /تسجيل-علامة-تجارية-والملكية-الفكرية/
- LP05 /محامي-عقاري-وتوثيق-عقود/

## Form / lead schema
Visible fields:
- full_name
- phone
- service
- privacy_consent

Rules:
- Browser autocomplete is enabled for name and phone.
- Dedicated pages preselect the relevant legal service.
- Phone input accepts common local or international forms with 7–15 digits instead of forcing +966 in the visible field.
- Saudi forms such as 05xxxxxxxx, 5xxxxxxxx, 966..., +966..., and 00966... normalize to Saudi E.164 where safely derivable.
- Other valid local/international forms are preserved instead of being rejected.
- Privacy consent is explicit and never prechecked.

Automatic attribution when available:
- landing_page_id, landing_path, landing_url
- utm_source, utm_medium, utm_campaign, utm_term, utm_content
- gclid, gbraid, wbraid, ttclid, fbclid
- campaignid, adgroupid, creative, keyword, matchtype, device, network, targetid, loc_physical_ms
- gad_source, gad_campaignid
- referrer, first_landing_url, session_id
- consent_version, consent_at, server_submit_at

Important limitation:
- Standard ad clicks do not expose a visitor's private name or phone number to the website. Campaign/click metadata can be captured automatically and browser autofill can reduce typing, but identity/contact data still requires user/browser-approved entry or a separately configured hosted lead form.

## Lead durability / conversion rule
- Same-origin POST: /api/lead/
- Private durable store: locked NDJSON outside public web root
- Lead reference: ENK-YYMMDD-HHMMSS-XXXX
- Private feed: /api/leads.csv/?token=... only when ENKAF_FEED_TOKEN is configured
- lead_form_success is allowed only after durable server acknowledgement.
- Call and WhatsApp remain separate outcomes.
- No personal data is placed in thank-you URLs or tracking parameters.

## GitHub source of truth
- Repository: marketinghorizonssa-alt/Enkaf
- Initial site PR: #1
- Temporary hositee tooling: PR #7
- Visual/conversion refresh: PR #8
- Luxury office redesign: PR #9
- Production entrypoint fix: PR #10
- Current agency/luxury Saudi redesign: PR #11
- PR #11 merged on 2026-08-20.
- Reviewed production website release commit: 6c1c2e08b75574e00830495b4a02bedc0a8cb079
- Production deployment script: scripts/deploy-hostinger.sh
- Preview deployment script: scripts/deploy-hositee-preview.sh

## Current production design - Luxury Agency V3
User rejected the prior production presentation as too geometric, sparse, visually weak, inconsistent in language, and not sufficiently Saudi/premium.

V3 changes:
- Rebuilt the visual system around a smoother premium Saudi legal-firm presentation.
- Uses ENKAF green, parchment, sandstone and umber rather than a near-single-color presentation.
- Uses the real ENKAF office photography already stored in the repository as lightweight local photo CSS assets.
- Removes the legacy luxury/refresh stylesheet dependencies from active page rendering.
- Uses formal Saudi legal/business Arabic in customer-facing copy.
- Removes internal implementation-style phrasing and template/AI-sounding copy from the main experience.
- Keeps the first-step form short and above the fold.
- Keeps call and WhatsApp as separate secondary paths.
- Keeps the existing durable lead storage and attribution logic.
- Build ID: enkaf-2026-08-20-luxury-v3
- Runtime design marker: luxury-agency-v3

## Production deployment - 2026-08-20
- Hostinger username: u128565677
- Production root: /home/u128565677/domains/enkaf.sa/public_html
- Exact deployed website release: 6c1c2e08b75574e00830495b4a02bedc0a8cb079
- Hostinger deployment read-back returned: ENKAF_DEPLOY_ALREADY_OK commit=6c1c2e08b75574e00830495b4a02bedc0a8cb079
- The deployment script itself validates the local PHP runtime and release marker before reporting a successful deployment.
- Production cache purge was requested after V3 deployment.
- Cacheless/development mode is enabled temporarily while visual QA continues.
- Temporary deployment/QA cron jobs were deleted; latest verified cron list is empty.

## Production asset read-back
Hostinger filesystem read-back after deployment:
- public/assets/css/site.css: 18,905 bytes
- public/assets/css/photo-hero.css: 20,023 bytes
- public/assets/css/photo-office.css: 19,989 bytes
- public/assets/css/photo-team.css: 20,022 bytes
- Total checked CSS/photo payload: 78,939 bytes

The photo CSS assets contain the optimized local office imagery used by the V3 layout.

## DNS / domain state
The user obtained DNET DNS access and configured:
- A record for enkaf.sa -> 145.223.36.55, TTL 300
- CNAME for www.enkaf.sa -> enkaf.sa, TTL 300
- Existing Hostinger mail MX/SPF/DMARC/DKIM records were preserved.
- DNET nameservers remain authoritative; nameservers were not migrated to Hostinger.
- The user's browser successfully reached the Hostinger site over the production domain after the DNS change.

## HTTPS / SSL incident
- HTTPS is NOT yet cleared as healthy.
- A server-side curl test from the Hostinger account returned TLS error: `curl: (35) ... tlsv1 alert internal error` for https://enkaf.sa/.
- This is separate from the V3 site code and asset deployment.
- Do not force an HTTP->HTTPS redirect until a valid certificate is actually serving without warnings/errors.
- Current application CSP intentionally does not force `upgrade-insecure-requests` while certificate issuance is unresolved, so HTTP rendering does not intentionally break CSS/images during the transition.
- SSL/certificate activation must be verified in Hostinger/hPanel or via a valid external TLS handshake before the production domain is treated as launch-ready.

## Legacy WordPress archive
- Old WordPress is archival only and is not production source.
- Archive: /home/u128565677/legacy-archives/enkaf/enkaf-wordpress-legacy-20260818-192401.zip
- Archive size: 688M
- SHA-256: 1875f740a7fa9c09ddd97202cd83853ecf9ebc0dd6509d6fdd4e3c59a8c3b5a7
- Database dump is included.
- ZIP integrity previously returned ZIP_TEST_OK.

## Current tracking / Ads state
- ENKAF GTM container: not created yet
- Google Ads account under manager: not present in last verified manager sync
- Google Ads conversions: not created yet
- Search campaign: not created yet
- Launch approval: not given
- Campaigns must remain paused until live-domain SSL, browser/mobile QA, forms, tracking and final QA are complete and the user explicitly approves launch.

## Crawl / launch rules
- Do not use enkaf.hositee.com as the final SEO/Search Console hostname.
- Do not claim Search Console completion for enkaf.sa until HTTPS, robots, sitemap, forms and real-browser/mobile QA pass on the live domain.
- Do not create/promote final Google Ads conversion actions or activate campaigns until the approved live domain and end-to-end QA are verified.
- New conversion actions start Secondary and become Primary only after verified testing and explicit approval.
