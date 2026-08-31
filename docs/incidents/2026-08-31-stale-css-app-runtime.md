# ENKAF production regression — stale CSS bundle and app runtime

Date: 2026-08-31
Domain: https://enkaf.sa

## Symptom

Production showed an older visual/content state even though the release marker pointed at a recent commit. The visible regressions included the previous Arabic typography, old floating-action presentation, and missing authority/SEO enhancement content.

## Root cause

The deployer's same-commit fast path treated the existence of `assets/css/enkaf-bundle.css` as sufficient proof of a healthy runtime. A restored/stale generated bundle could therefore survive while the release marker still looked current. The fast path also did not validate the private app runtime under `/domains/enkaf.sa/app`, so a stale `app/enhancements.php` was not detected by the release marker alone.

## Fix

- Require `site.css`, `luxury-v6.css`, `enhancements.css`, and `brand-fonts-v69.css` as bundle sources.
- Build the production bundle in stable cascade order: site -> luxury V6 -> enhancements -> brand fonts.
- Validate bundle markers for V6, SEO enhancements, and V6.9 brand typography before accepting a deploy or same-commit early exit.
- Validate app runtime markers for `authority_context`, Ministry of Commerce content, SAIP content, and About page support before accepting a deploy or same-commit early exit.
- Allow the external brand font host in CSP `font-src`.
- Bump the production bundle query version to `20260831-1` to prevent stale browser/CDN reuse.
- Keep `review_mode=false` and preserve direct Google Ads tagging.

## Verified production deployment

Production code commit: `6785b6a3d4916c630e2123ba574fd7af97e917b5`

Deployment validation returned:

`ENKAF_DEPLOY_OK ... assets=ok css_bundle=complete app_runtime=complete`

A backup was created before the deploy. Hostinger cache was purged after deployment.

## Reusable rule

Never trust an ENKAF release marker by itself. A valid production readback requires the release marker plus semantic validation of generated runtime assets and private app files.