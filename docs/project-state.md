# ENKAF Performance Landing Project - Source of Truth

## Identity
- Client: ENKAF / إنكاف
- Brand: إنكاف للمحاماة والاستشارات القانونية
- Reference URL: https://enkaf.sa/
- Intended production domain: https://enkaf.sa/ (production deployment not executed yet)
- Primary email: Info@enkaf.sa
- Work email: Work@enkaf.sa
- Approved phone supplied by client: 0559556606 / +966559556606
- WhatsApp configuration: 966559556606 (same supplied contact number in current build)
- Brand palette: Midnight Green #29332B, Parchment #F1EAD8, Umber #683F18, Burnt Umber #74391B, Sandstone Beige #D3BFA6
- Brand Arabic typeface reference: DIN Arabice; production build uses a system fallback stack unless a licensed webfont is supplied.

## Keyword / landing approval
Approved by user on 2026-08-18 to proceed with implementation from the post-Planner structure.

Landing paths:
- LP01 /محامي-واستشارات-قانونية/
- LP02 /محامي-شركات-وتأسيس-شركات/
- LP03 /قضايا-تجارية-وتحصيل-ديون/
- LP04 /تسجيل-علامة-تجارية-والملكية-الفكرية/
- LP05 /محامي-عقاري-وتوثيق-عقود/

## Form schema
Visible first step:
- full_name
- phone
- service
- privacy_consent

Automatic attribution:
- landing_page_id, landing_path, landing_url
- utm_source, utm_medium, utm_campaign, utm_term, utm_content
- gclid, gbraid, wbraid, ttclid, fbclid
- referrer, first_landing_url, session_id
- consent_version, consent_at, server_submit_at

## Lead durability
- Same-origin POST: /api/lead/
- Private durable store: locked NDJSON outside public web root
- Lead reference: ENK-YYMMDD-HHMMSS-XXXX
- Private feed: /api/leads.csv/?token=... only when ENKAF_FEED_TOKEN is configured
- Canonical form success domain event: enkaf:lead-success after durable storage acknowledgement only
- GTM will later translate the domain event to lead_form_success; page JavaScript does not push conversion events directly to dataLayer.

## Current tracking state
- ENKAF GTM container: not created yet
- Google Ads account under manager: not present in last verified account sync
- Google Ads conversions: not created yet
- Campaign: not created yet
- Launch state: not approved; all future campaign objects must remain paused until explicit final approval.

## GitHub publication
- Dedicated repository: marketinghorizonssa-alt/Enkaf
- Review branch: review/enkaf-launch-v1
- Reviewed branch head before merge: f0d4b702454093c8e520ed61cd6fba3a66d753f8
- Pull request: #1 ENKAF launch v1 — website and Google Ads landing pages
- PR merged to main on 2026-08-18.
- Merge commit: 9564d10852efaedaa248f21788fcd75c3dd4e55a
- Deployment/rollback script: scripts/deploy-hostinger.sh
- Local site validation before publication: ENKAF_LOCAL_VALIDATE_OK

## Release state
- Source publication to GitHub: complete.
- Production Hostinger deployment: blocked because the Hostinger connector returned `Not connected` on the latest verified read after GitHub merge.
- No direct archive bypass was used.
- No Cloudflare account-management deploy connector is currently available; the connected Cloudflare tool is documentation-only.
- Review mode defaults to true.
- Review mode returns X-Robots-Tag noindex,nofollow and robots Disallow: /.
- Production release must set ENKAF_REVIEW_MODE=false and pass live health, release-marker, robots, sitemap, form, and mobile checks before Search Console indexing actions.
