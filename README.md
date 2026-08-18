# ENKAF Campaign Website & Landing Pages

Keyword-aligned Arabic RTL website and campaign landing pages for ENKAF.

## Landing paths
- `/محامي-واستشارات-قانونية/`
- `/محامي-شركات-وتأسيس-شركات/`
- `/قضايا-تجارية-وتحصيل-ديون/`
- `/تسجيل-علامة-تجارية-والملكية-الفكرية/`
- `/محامي-عقاري-وتوثيق-عقود/`

The reviewed release artifact is stored under `release/enkaf-site.zip.b64`. Decode it to reproduce the exact source archive used for deployment. The deploy script is pinned to a GitHub commit and downloads that exact artifact before installing it on Hostinger.

## Lead flow
The form submits to `/api/lead/`, validates and normalizes the request, writes it to a private durable store, returns a lead reference, then dispatches `enkaf:lead-success`. No Google Ads conversion is fired on submit-button click.

## Release safety
- `ENKAF_REVIEW_MODE=true` is the default until production approval.
- GTM is intentionally not configured until the dedicated ENKAF container and conversion mapping are approved.
- Campaigns remain paused until final QA and explicit launch approval.
- Secrets, feed tokens and customer lead data must never be committed.
