#!/usr/bin/env bash
set -euo pipefail

DOMAIN_ROOT="${HOME}/domains/enkaf.sa"
PUBLIC_ROOT="${DOMAIN_ROOT}/public_html"
TMP="$(mktemp -d "${DOMAIN_ROOT}/.enkaf-qa-XXXXXX")"
PORT="${ENKAF_QA_PORT:-18765}"
PID=""

cleanup() {
  if [ -n "$PID" ]; then kill "$PID" >/dev/null 2>&1 || true; fi
  rm -rf "$TMP"
}
trap cleanup EXIT

fail() { echo "ENKAF_SERVER_QA_ERROR $*"; exit 1; }

[ -f "$PUBLIC_ROOT/index.php" ] || fail missing_index
[ -f "$DOMAIN_ROOT/app/config.php" ] || fail missing_app

export ENKAF_SITE_URL="https://enkaf.sa"
export ENKAF_REVIEW_MODE="false"
export ENKAF_DATA_DIR="$TMP/data"
mkdir -p "$ENKAF_DATA_DIR"

php -S "127.0.0.1:${PORT}" -t "$PUBLIC_ROOT" "$PUBLIC_ROOT/index.php" >"$TMP/php-server.log" 2>&1 &
PID=$!
for _ in $(seq 1 20); do
  if curl -fsS "http://127.0.0.1:${PORT}/healthz/" >"$TMP/health.json" 2>/dev/null; then break; fi
  sleep 0.25
done
grep -Fq '"ok":true' "$TMP/health.json" || fail health
grep -Fq '"review_mode":false' "$TMP/health.json" || fail review_mode

encode_path() {
  php -r '$p=$argv[1];$q="";if(($i=strpos($p,"?"))!==false){$q=substr($p,$i);$p=substr($p,0,$i);}echo implode("/",array_map("rawurlencode",explode("/",$p))).$q;' "$1"
}

request() {
  local path="$1" expected="$2" name="$3"
  local body="$TMP/${name}.body" headers="$TMP/${name}.headers" encoded code
  encoded="$(encode_path "$path")"
  code="$(curl -sS -H 'Host: enkaf.sa' -D "$headers" -o "$body" -w '%{http_code}' "http://127.0.0.1:${PORT}${encoded}")"
  [ "$code" = "$expected" ] || fail "${name}_status_${code}"
}

pages=(
  "/|home"
  "/محامي-واستشارات-قانونية/|lp01"
  "/محامي-شركات-وتأسيس-شركات/|lp02"
  "/قضايا-تجارية-وتحصيل-ديون/|lp03"
  "/تسجيل-علامة-تجارية-والملكية-الفكرية/|lp04"
  "/محامي-عقاري-وتوثيق-عقود/|lp05"
)
: >"$TMP/titles.txt"
for item in "${pages[@]}"; do
  path="${item%%|*}"; name="${item##*|}"
  request "$path" 200 "$name"
  body="$TMP/${name}.body"; headers="$TMP/${name}.headers"
  [ "$(grep -Eo '<h1[^>]*>' "$body" | wc -l | tr -d ' ')" = "1" ] || fail "${name}_h1"
  grep -Fq 'rel="canonical"' "$body" || fail "${name}_canonical"
  grep -Fq 'name="full_name"' "$body" || fail "${name}_name_field"
  grep -Fq 'name="phone"' "$body" || fail "${name}_phone_field"
  grep -Fq 'name="service"' "$body" || fail "${name}_service_field"
  grep -Fq 'name="privacy_consent"' "$body" || fail "${name}_consent_field"
  grep -Fq 'data-event="click_call"' "$body" || fail "${name}_call_cta"
  grep -Fq 'data-event="click_whatsapp"' "$body" || fail "${name}_whatsapp_cta"
  ! grep -qi '^X-Robots-Tag:.*noindex' "$headers" || fail "${name}_noindex"
  php -r '$s=file_get_contents($argv[1]);if(preg_match("~<title>([^<]+)</title>~u",$s,$m)){echo html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,"UTF-8"),PHP_EOL;}' "$body" >>"$TMP/titles.txt"
done
[ "$(wc -l < "$TMP/titles.txt" | tr -d ' ')" = "6" ] || fail title_count
[ "$(sort "$TMP/titles.txt" | uniq | wc -l | tr -d ' ')" = "6" ] || fail duplicate_titles

request "/سياسة-الخصوصية/" 200 privacy
grep -Fq 'سياسة الخصوصية' "$TMP/privacy.body" || fail privacy_content
request "/شكرا/?ref=ENK-QA-TEST" 200 thanks
grep -Fq 'ENK-QA-TEST' "$TMP/thanks.body" || fail thank_you_ref
request "/qa-definitely-missing/" 404 missing
request "/api/leads.csv/" 404 feed_private

request "/robots.txt" 200 robots
grep -Fq 'User-agent: Google-InspectionTool' "$TMP/robots.body" || fail robots_inspection_tool
grep -Fq 'User-agent: Googlebot' "$TMP/robots.body" || fail robots_googlebot
! grep -Fxq 'Disallow: /' "$TMP/robots.body" || fail robots_blocked
grep -Fq 'Sitemap: https://enkaf.sa/sitemap.xml' "$TMP/robots.body" || fail robots_sitemap

request "/sitemap.xml" 200 sitemap
grep -qi '^Content-Type: application/xml' "$TMP/sitemap.headers" || fail sitemap_content_type
[ "$(grep -o '<loc>' "$TMP/sitemap.body" | wc -l | tr -d ' ')" = "7" ] || fail sitemap_url_count
[ "$(grep -o '<loc>https://enkaf.sa' "$TMP/sitemap.body" | wc -l | tr -d ' ')" = "7" ] || fail sitemap_host

invalid_code="$(curl -sS -o "$TMP/invalid.json" -w '%{http_code}' -X POST "http://127.0.0.1:${PORT}/api/lead/" --data 'full_name=A&phone=1&service=x')"
[ "$invalid_code" = "422" ] || fail "invalid_form_${invalid_code}"
grep -Fq 'validation_error' "$TMP/invalid.json" || fail invalid_form_payload

valid_code="$(curl -sS -o "$TMP/valid.json" -w '%{http_code}' -X POST "http://127.0.0.1:${PORT}/api/lead/" \
  --data-urlencode 'full_name=اختبار إنكاف QA' \
  --data-urlencode 'phone=0550000000' \
  --data-urlencode 'service=general_consultation' \
  --data-urlencode 'privacy_consent=1' \
  --data-urlencode 'landing_page_id=QA' \
  --data-urlencode 'landing_path=/' \
  --data-urlencode 'utm_source=server_qa')"
[ "$valid_code" = "201" ] || fail "valid_form_${valid_code}"
grep -Fq '"ok":true' "$TMP/valid.json" || fail valid_form_payload
grep -Eq '"lead_id":"ENK-[0-9]{6}-[0-9]{6}-[A-F0-9]{4}"' "$TMP/valid.json" || fail lead_reference
[ -s "$ENKAF_DATA_DIR/enkaf-leads.ndjson" ] || fail durable_store
[ "$(wc -l < "$ENKAF_DATA_DIR/enkaf-leads.ndjson" | tr -d ' ')" = "1" ] || fail durable_store_count

echo "ENKAF_SERVER_QA_OK pages=6 sitemap_urls=7 form_201=true form_422=true robots_allowed=true private_feed_404=true"
