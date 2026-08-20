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

## Performance objective
- Primary objective is paid-search conversion performance and strong Google relevance/quality, not a visual showcase.
- Keep the first-step form in the first viewport on campaign landings.
- Keep pages fast and lightweight: no autoplay video, slider, heavy animation framework, or unnecessary third-party visual library.
- Maintain keyword/ad/page alignment, unique metadata/H1/canonical, crawlable service copy, FAQ schema, robots and sitemap.
- Call and WhatsApp remain separate secondary conversion paths.

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
- Luxury Saudi redesign: PR #11
- Conversion-first V4 visual system: PR #12
- PR #12 merged on 2026-08-20.
- Current reviewed production release: 73f8352e006afc6b5a8599fde0d36cdb9436a124
- Production deployment script: scripts/deploy-hostinger.sh
- Preview deployment script: scripts/deploy-hositee-preview.sh

## V4 design direction
User supplied https://atheer-law.hositee.com/lawyer-jeddah as a visual reference, while explicitly preserving ENKAF performance/conversion requirements.

Reference patterns inspected from the existing hositee server:
- Strong dark legal hero and high-contrast premium accent treatment.
- Clear service hierarchy and repeated conversion paths.
- Fixed/floating contact paths and strong legal-service information architecture.
- The reference uses autoplay/hero video and more decorative effects; ENKAF deliberately does not copy those performance-heavy choices.

V4 implementation:
- Refined the ENKAF interface to a smoother premium Saudi legal presentation rather than a harsh geometric layout.
- Keeps ENKAF green, parchment, sandstone, umber and burnt-umber palette.
- Preserves formal Saudi legal/business Arabic already introduced in V3.
- Keeps form adjacent to hero copy and visible in the first screen on desktop, with mobile one-column conversion flow.
- Uses the real ENKAF office photo layers already stored locally in the repository as embedded optimized WebP CSS assets.
- Explicitly forces hero/office/team photo layers to render as cover backgrounds with visible opacity and safe fallbacks.
- Does not add a video hero, external image CDN, external UI library, or heavy animation bundle.
- Keeps the existing form schema, attribution, durable lead flow, call event and WhatsApp event unchanged.

## Production deployment - V4
- Hostinger username: u128565677
- Production root: /home/u128565677/domains/enkaf.sa/public_html
- Exact deployed release: 73f8352e006afc6b5a8599fde0d36cdb9436a124
- Verified deployment output:
  - ENKAF_SERVER_VALIDATE_OK
  - ENKAF_DEPLOY_OK commit=73f8352e006afc6b5a8599fde0d36cdb9436a124 review_mode=false
- Deployment backup: /home/u128565677/domains/enkaf.sa/enkaf-backups/before-73f8352e006a-20260820-140921.tar.gz
- Production deployment crons were requested for deletion after the verified deploy.

## Photo source
- User-provided real office Drive folder: https://drive.google.com/drive/folders/18Baielx_kbTrgKTNbQwMJH2fQ6OQRIqH
- Folder contains real ENKAF office photography in HEIC/JPG formats.
- Representative images were inspected from the folder and confirm a dark, premium Saudi office environment with legal books, executive desks, Saudi identity cues and meeting areas.
- Current production V4 still uses the optimized real-office WebP photo layers already embedded in the repository; newer Drive JPG/HEIC originals are source material for future curated replacements and are not claimed as separately published binary files.

## DNS / SSL
- DNET authoritative DNS remains in place.
- A record: enkaf.sa -> 145.223.36.55, TTL 300.
- CNAME: www.enkaf.sa -> enkaf.sa, TTL 300.
- Existing Hostinger MX/SPF/DMARC/DKIM records were preserved.
- User provided current Hostinger hPanel screenshot on 2026-08-20 showing Lifetime SSL for enkaf.sa as Active.
- Old subdomains mz.enkaf.sa, profile.enkaf.sa and test.enkaf.sa show failed SSL entries but are not the production root site.
- Final live HTTPS/browser redirect verification is still required before Search Console completion.

## Legacy WordPress archive
- Hostinger still labels the website metadata as website_type=wordpress because the site was originally created as WordPress.
- WordPress is not the serving production source.
- Old WordPress archive: /home/u128565677/legacy-archives/enkaf/enkaf-wordpress-legacy-20260818-192401.zip
- Archive size: 688M
- SHA-256: 1875f740a7fa9c09ddd97202cd83853ecf9ebc0dd6509d6fdd4e3c59a8c3b5a7
- Database dump is included; ZIP integrity previously returned ZIP_TEST_OK.

## Current tracking / Ads state
- ENKAF GTM container: not created yet
- Google Ads account under manager: not present in last verified manager sync
- Google Ads conversions: not created yet
- Search campaign: not created yet
- Launch approval: not given
- Campaigns must remain paused until live-domain HTTPS, browser/mobile QA, forms, tracking and final QA are complete and the user explicitly approves launch.

## Crawl / launch rules
- Do not use enkaf.hositee.com as the final SEO/Search Console hostname.
- Do not claim Search Console completion for enkaf.sa until HTTPS, robots, sitemap, forms and real-browser/mobile QA pass on the live domain.
- Do not create/promote final Google Ads conversion actions or activate campaigns until the approved live domain and end-to-end QA are verified.
- New conversion actions start Secondary and become Primary only after verified testing and explicit approval.
