# ENKAF production release — 2026-08-20

## Approved scope
User explicitly approved working directly on the primary domain `https://enkaf.sa` for the luxury redesign. This approval applies to website deployment only; Google Ads campaign activation remains unapproved.

## Design direction
- Luxury Saudi legal-firm positioning using ENKAF brand colors.
- Real office photography supplied in the approved Google Drive source.
- Photography used selectively rather than as a gallery.
- Minimal first-step lead form with flexible phone input.
- Existing durable lead storage and automatic campaign/click attribution preserved.
- Copy kept concise and factual; no invented reviews, guarantees, prices, response-time claims, or fabricated statistics.

## GitHub publication
- Design PR: #9 `Redesign ENKAF as a luxury Saudi legal brand`.
- Design merge commit: `4f0fdaad8b1abe0196acd612ee7f31f395b7743e`.
- First production deployment of that merge was automatically rolled back because `public/index.php` still referenced the removed draft file `app/views-v2.php`.
- Rollback backup: `/home/u128565677/domains/enkaf.sa/enkaf-backups/before-4f0fdaad8b1a-20260820-111901.tar.gz`.
- Hotfix PR: #10 `Fix ENKAF luxury production entrypoint`.
- Final production merge commit: `440397bf0f544b74a91d2da6c8300eed7e4b5884`.

## Production deployment
Hostinger deployment returned:
`ENKAF_DEPLOY_OK commit=440397bf0f544b74a91d2da6c8300eed7e4b5884 review_mode=false`

Backup created before the successful replacement:
`/home/u128565677/domains/enkaf.sa/enkaf-backups/before-440397bf0f54-20260820-112101.tar.gz`

The temporary deployment cron was deleted after success and the cron list was read back as empty.

## Verification state
- Server-side source validation passed before deployment: `ENKAF_SERVER_VALIDATE_OK`.
- Server-side deployment health/release verification passed inside the deployment script.
- Public DNS had previously resolved both `enkaf.sa` and `www.enkaf.sa` to `145.223.36.55`.
- Independent external browser QA is still pending because the current execution environment is intermittently failing DNS/HTTP fetches for the newly pointed domain. Do not claim external-browser QA complete until a clean public fetch is observed.
- Search Console, GTM/GA4 conversion publication, Google Ads conversions, and campaign activation remain separate later gates.
