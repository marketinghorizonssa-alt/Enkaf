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
- Brand Arabic font reference: DIN Arabice. Current V5 uses a zero-download premium Arabic system serif display stack plus a fast system body stack; no font files are distributed.

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
- Legal capability / real-image V5: PR #13
- Required visual-asset deployment validation: PR #14
- PR #13 merged on 2026-08-20 with release 225795149d1d216f58421ea365378330416333ab.
- PR #14 merged on 2026-08-20 with release e2d4dd8591310ef6639e57c0e637978ced64d4fb.
- Current reviewed production release: e2d4dd8591310ef6639e57c0e637978ced64d4fb
- Production deployment script: scripts/deploy-hostinger.sh
- Preview deployment script: scripts/deploy-hositee-preview.sh

## V5 design direction
User rejected office/interior-design-led copy and requested that the copy sell legal capability while luxury is communicated through visual treatment, office photography and brand identity.

V5 implementation:
- Replaced office/interior-design messaging with legal-capability messaging focused on consultations, representation, companies, contracts, disputes, debt collection, enforcement, IP, document analysis and legal positioning.
- Retained formal Saudi legal/business Arabic and removed visitor-facing implementation commentary.
- Replaced fragile CSS data-URI photo backgrounds with normal local WebP image files referenced by HTML <img> elements.
- Hero image is preloaded with fetch priority; below-the-fold images are lazy-loaded.
- Added original lightweight legal motifs inspired by the visual principle of the user-supplied Atheer reference without copying its heavy GIF/video asset: scales for home/general, corporate-building motif, dispute/gavel motif, IP/fingerprint motif, real-estate motif.
- Legal motifs are local SVGs with a restrained CSS motion effect and reduced-motion fallback.
- Uses a premium Arabic serif display stack without downloading a webfont; body/forms use a fast system stack.
- Keeps the lead form above the fold, floating call/WhatsApp, campaign attribution, durable success flow, privacy consent and tracking event semantics unchanged.

## V5 production deployment
- Hostinger username: u128565677
- Production root: /home/u128565677/domains/enkaf.sa/public_html
- Exact deployed release: e2d4dd8591310ef6639e57c0e637978ced64d4fb
- Verified deployment output:
  - ENKAF_SERVER_VALIDATE_OK
  - ENKAF_DEPLOY_OK commit=e2d4dd8591310ef6639e57c0e637978ced64d4fb review_mode=false assets=ok
- Deployment backup: /home/u128565677/domains/enkaf.sa/enkaf-backups/before-e2d4dd859131-20260820-160121.tar.gz
- Deployment validation now requires all six hero WebPs, both section WebPs and all five motif SVGs in the GitHub source before replacement and again in the deployed public root after copy.
- If a release marker matches but required visual assets are absent, the deployment script no longer exits as successful; it repairs the same release or rolls back on failure.
- Hostinger server/CDN cache was cleared after deployment; cacheless development mode was turned off and normal website caching was enabled.
- Temporary deployment crons were deleted after verified deployment.

## Photo source
- User-provided real office Drive folder: https://drive.google.com/drive/folders/18Baielx_kbTrgKTNbQwMJH2fQ6OQRIqH
- Folder contains real ENKAF office photography in HEIC/JPG formats.
- Representative images were inspected and confirm a dark premium Saudi office environment with legal books, executive desks, Saudi identity cues and meeting areas.
- Curated office photos were converted into lightweight local WebP files for V5 hero and supporting imagery; they are part of the GitHub release rather than runtime external dependencies.

## DNS / SSL
- DNET authoritative DNS remains in place.
- A record: enkaf.sa -> 145.223.36.55, TTL 300.
- CNAME: www.enkaf.sa -> enkaf.sa, TTL 300.
- Existing Hostinger MX/SPF/DMARC/DKIM records were preserved.
- User provided Hostinger hPanel screenshot on 2026-08-20 showing Lifetime SSL for enkaf.sa as Active.
- Old subdomains mz.enkaf.sa, profile.enkaf.sa and test.enkaf.sa show failed SSL entries but are not the production root site.
- Browser-level visual QA of the new V5 release is still required before Search Console completion.

## Legacy WordPress archive
- Hostinger may still expose historical WordPress metadata because the website was originally created as WordPress; current serving source is the ENKAF PHP landing application.
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
- Campaigns must remain paused until live-domain browser/mobile QA, forms, tracking and final QA are complete and the user explicitly approves launch.

## Crawl / launch rules
- Do not use enkaf.hositee.com as the final SEO/Search Console hostname.
- Do not claim Search Console completion for enkaf.sa until HTTPS, robots, sitemap, forms and real-browser/mobile QA pass on the live domain.
- Do not create/promote final Google Ads conversion actions or activate campaigns until the approved live domain and end-to-end QA are verified.
- New conversion actions start Secondary and become Primary only after verified testing and explicit approval.
