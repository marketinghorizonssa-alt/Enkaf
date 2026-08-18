# ENKAF Performance Landing Project - Source of Truth

## Identity
- Client: ENKAF / إنكاف
- Brand: إنكاف للمحاماة والاستشارات القانونية
- Reference URL: https://enkaf.sa/
- Intended production domain: https://enkaf.sa/
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
- Reviewed website release commit: 9564d10852efaedaa248f21788fcd75c3dd4e55a
- Deployment/rollback script: scripts/deploy-hostinger.sh
- Deployment reliability PRs: #2 backup-race fix; #3 server-local health verification for broken self-DNS.
- Deployment tooling commit used for final run: b0fbbce4962de57c85968749fbe926a8c79ab7fe
- Legacy archive helper commit: f43c2dbf796c0eac56d573db92c2df7222bccd34
- Local site validation before publication: ENKAF_LOCAL_VALIDATE_OK

## Release state
- Source publication to GitHub: complete.
- Hostinger target: username u128565677, domain enkaf.sa, root /home/u128565677/domains/enkaf.sa/public_html.
- Reviewed release deployed on Hostinger: 9564d10852efaedaa248f21788fcd75c3dd4e55a.
- Runtime read-back: health JSON returned ok=true, service=enkaf-landing-site, build=enkaf-2026-08-18-a, review_mode=false, gtm_configured=false.
- Current public root contains only the ENKAF release files: assets, .enkaf-release, enkaf-release.txt, .htaccess, and index.php.
- No wp-admin, wp-content, wp-includes, or wp-config.php remains in the serving root.
- Hostinger server-side cache purge was requested after deployment.
- No direct local-archive bypass was used; deployment downloaded the recorded GitHub release.

## Legacy WordPress disposition
- User explicitly authorized treating the old WordPress site as disposable legacy material and continuing as if the domain were empty.
- Legacy WordPress is archival only and must not be used as production source, design source, or deployment source.
- Full preservation ZIP: /home/u128565677/legacy-archives/enkaf/enkaf-wordpress-legacy-20260818-192401.zip
- Archive size: 688M.
- Archive SHA-256: 1875f740a7fa9c09ddd97202cd83853ecf9ebc0dd6509d6fdd4e3c59a8c3b5a7.
- WordPress database dump is included inside the ZIP.
- ZIP integrity was independently rechecked with unzip -tq and returned ZIP_TEST_OK.
- Seven redundant pre-release tar backups were moved out of the domain backup directory and then deleted after ZIP verification.
- /home/u128565677/domains/enkaf.sa/enkaf-backups now contains zero files, so future backups start from the ENKAF release only.
- No destructive database deletion was performed; the old database is not used by the ENKAF application.

## Current DNS blocker
- Public DNS is not currently suitable for live QA/indexing.
- DNS-over-HTTPS verification on 2026-08-18 returned NOERROR but no A answer for enkaf.sa.
- DNS-over-HTTPS verification returned NOERROR but no AAAA answer for enkaf.sa.
- www.enkaf.sa A lookup returned NXDOMAIN.
- Authority observed in the DNS responses: ns10.dnetns.com / dnet.sa SOA.
- Hostinger domain-ownership verification currently returns is_accessible=false.
- Because the public hostname is not resolving consistently, external live HTTP, robots, sitemap, form, mobile, and Search Console QA remain blocked even though the production files and PHP runtime are deployed on Hostinger.

## Crawl / release rules
- Review mode defaults to true in source but production deployment sets ENKAF_REVIEW_MODE=false.
- Review mode returns X-Robots-Tag noindex,nofollow and robots Disallow: /.
- Do not submit Search Console or create/promote conversion tracking until public DNS resolves and live health, release marker, robots, sitemap, form, and mobile checks pass.
