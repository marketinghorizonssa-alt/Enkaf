# ENKAF production regression — 2026-08-24

## Incident
A deployment was prepared from `main` after adding About/team/location/authority SEO content. `main` did not contain the newer V6 presentation layer that had been released from `fix/luxury-content-v6-release`.

## User-visible symptom
Production visually regressed to the older presentation. The V6 JavaScript presentation layer (`applyV6Presentation`) and its related V6 styling were absent from the deployed source.

## Root cause
The source branch used for the deployment was stale relative to the actual latest ENKAF presentation release. The About/location change itself touched only three files, but it was merged onto stale `main`, so deploying that commit replaced the newer V6 files with older ones.

## Recovery
1. Confirmed the current production asset was missing `applyV6Presentation`.
2. Identified `fix/luxury-content-v6-release` as the correct V6 source; its `site.js` contains the approved modern home presentation including the headline "نخبة من المحامين بكفاءة عالية وخبرة سعودية برؤية حديثة", flexible phone handling, and prechecked consent behavior.
3. Created `recovery/v6-about-location-20260824` from the V6 release branch.
4. Applied the About/team/location/authority SEO changes on top via PR #16 without modifying the V6 presentation files.
5. Deployed commit `d9aebec142c429cc4040d65c4d9d6819ed6b3086` to `enkaf.sa` with production review mode disabled.

## Prevention
Before every production deployment, compare the intended deployment branch/commit against the currently approved visual release, and explicitly verify required presentation-layer files (including V6 CSS/JS) in the target commit. Do not assume `main` is the latest approved live presentation when production has been released from another branch.
