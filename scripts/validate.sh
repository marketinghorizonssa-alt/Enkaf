#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

find "$ROOT/app" "$ROOT/public" -type f -name '*.php' -print0 | while IFS= read -r -d '' f; do php -l "$f" >/dev/null; done
node --check "$ROOT/public/assets/js/site.js" >/dev/null

if grep -RniE 'arkan|shaghel|easttwist|GTM-P5J6D6ND|AW-18127536852|AW-18303013990' "$ROOT/app" "$ROOT/public" "$ROOT/docs" --exclude='validate.sh'; then
  echo 'ERROR: cross-client token found' >&2; exit 1
fi
if grep -RniE 'dataLayer\.push\(|gtag\(.event.,.conversion' "$ROOT/public/assets/js/site.js"; then
  echo 'ERROR: page JavaScript must not push conversion events directly' >&2; exit 1
fi
for token in "/محامي-واستشارات-قانونية/" "/محامي-شركات-وتأسيس-شركات/" "/قضايا-تجارية-وتحصيل-ديون/" "/تسجيل-علامة-تجارية-والملكية-الفكرية/" "/محامي-عقاري-وتوثيق-عقود/"; do
  grep -Fq "$token" "$ROOT/app/config.php" || { echo "ERROR: missing route $token" >&2; exit 1; }
done

grep -Fq '0559556606' "$ROOT/app/config.php"
grep -Fq 'Info@enkaf.sa' "$ROOT/app/config.php"
grep -Fq 'ENKAF_REVIEW_MODE' "$ROOT/.env.example"
grep -Fq 'lead-success' "$ROOT/public/assets/js/site.js"
grep -Fq 'privacy_consent' "$ROOT/app/views.php"
grep -Fq 'hero-photo-img' "$ROOT/app/views.php"

required_assets=(
  hero-home.webp hero-general.webp hero-corporate.webp hero-disputes.webp hero-ip.webp hero-realestate.webp
  section-strategy.webp section-work.webp
  motif-scale.svg motif-corporate.svg motif-disputes.svg motif-ip.svg motif-realestate.svg
)
for asset in "${required_assets[@]}"; do
  [ -s "$ROOT/public/assets/img/$asset" ] || { echo "ERROR: missing required visual asset $asset" >&2; exit 1; }
done

echo 'ENKAF_LOCAL_VALIDATE_OK'
