# ENKAF production stale-release incident — 2026-08-21

## Symptom
The production browser continued to show the previous ENKAF layout after V5 had been reported as deployed. The visible result matched the old design and the new office images did not appear.

## Verified findings
- Initial server-side diagnostic after the user report returned an empty release marker, zero matches for the V5 `hero-photo-img` markup, and a missing `hero-home.webp` asset.
- The V5 production deployment was rerun from GitHub release `e2d4dd8591310ef6639e57c0e637978ced64d4fb` using the recorded Hostinger deployment script.
- Deployment output returned `ENKAF_SERVER_VALIDATE_OK` and `ENKAF_DEPLOY_OK ... assets=ok`.
- A delayed post-deployment read-back then returned `e2d4dd8591310ef6639e57c0e637978ced64d4fb:1:Y`, confirming the exact release marker, V5 hero markup and required hero asset were present on the production filesystem after deployment.
- Hostinger server/CDN cache was purged after the successful read-back.
- Cacheless development mode was enabled temporarily for visual verification.
- PHP OPcache was disabled and read back as disabled, then re-enabled and read back as enabled to flush stale compiled PHP state while preserving production performance.
- All temporary diagnostic and deployment cron jobs were deleted; final cron list was empty.

## Prevention
- Production deployment now requires all V5 visual assets before source replacement and again after copy.
- Same-commit deployments repair missing visual assets instead of returning a false already-deployed success.
- Do not report visual production completion from the deployment success marker alone. Require a post-deployment release/markup/asset read-back and browser-level confirmation.

## Current state
- Intended production release: `e2d4dd8591310ef6639e57c0e637978ced64d4fb`.
- Server-side post-deployment verification: release marker correct, V5 markup present, hero asset present.
- Browser visual confirmation is still pending; cacheless mode remains temporary until the user confirms the refreshed production output.