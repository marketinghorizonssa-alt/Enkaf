<?php
declare(strict_types=1);

/**
 * ENKAF website -> customer service Google Sheet bridge.
 *
 * The website form already stores every submission durably in private/enkaf-leads.ndjson.
 * This worker forwards new leads to the existing ENKAF Apps Script webhook without
 * touching the website rendering/runtime. Google-side de-duplication uses a stable
 * WEB-<website lead id> key.
 *
 * Private runtime files expected next to enkaf-leads.ndjson:
 *   sheet-webhook-url
 *   sheet-webhook-secret
 */

$root = dirname(__DIR__);
$dataDir = $root . '/private';
$leadFile = $dataDir . '/enkaf-leads.ndjson';
$urlFile = $dataDir . '/sheet-webhook-url';
$secretFile = $dataDir . '/sheet-webhook-secret';
$stateFile = $dataDir . '/sheet-sync-state.json';
$lockFile = $dataDir . '/sheet-sync.lock';

if (!is_dir($dataDir) || !is_file($leadFile)) {
    fwrite(STDOUT, "ENKAF_SHEET_SYNC no_leads\n");
    exit(0);
}

$webhookUrl = trim((string) @file_get_contents($urlFile));
$webhookSecret = trim((string) @file_get_contents($secretFile));
if ($webhookUrl === '' || $webhookSecret === '') {
    fwrite(STDERR, "ENKAF_SHEET_SYNC missing_config\n");
    exit(2);
}

$lock = fopen($lockFile, 'c+');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "ENKAF_SHEET_SYNC locked\n");
    exit(0);
}

try {
    $state = [];
    if (is_file($stateFile)) {
        $decoded = json_decode((string) file_get_contents($stateFile), true);
        if (is_array($decoded)) $state = $decoded;
    }

    $lines = file($leadFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $sent = 0;
    $ok = 0;
    $failed = 0;
    $skipped = 0;
    $maxPerRun = 25;

    foreach ($lines as $line) {
        if ($sent >= $maxPerRun) break;
        $lead = json_decode($line, true);
        if (!is_array($lead)) continue;

        $leadId = clean((string) ($lead['lead_id'] ?? ''));
        if ($leadId === '') continue;

        $entry = isset($state[$leadId]) && is_array($state[$leadId]) ? $state[$leadId] : [];
        $attempts = (int) ($entry['attempts'] ?? 0);
        $successes = (int) ($entry['successes'] ?? 0);
        $done = !empty($entry['done']);

        if ($done) {
            $skipped++;
            continue;
        }

        // Send each new lead successfully up to three times on separate worker runs.
        // The Apps Script de-duplicates by Google Lead ID, while the repeated delivery
        // gives transient Google/Apps Script errors a chance to recover automatically.
        if ($successes >= 3) {
            $entry['done'] = true;
            $entry['done_at'] = gmdate('c');
            $state[$leadId] = $entry;
            $skipped++;
            continue;
        }

        $payload = buildPayload($lead, $webhookSecret);
        [$httpCode, $body, $error] = postJson($webhookUrl, $payload);
        $sent++;
        $attempts++;

        $entry['attempts'] = $attempts;
        $entry['last_attempt_at'] = gmdate('c');
        $entry['last_http_code'] = $httpCode;
        $entry['last_error'] = $error;
        $entry['google_lead_id'] = $payload['lead_id'];

        if ($httpCode >= 200 && $httpCode < 300 && $error === '') {
            $successes++;
            $entry['successes'] = $successes;
            $entry['last_success_at'] = gmdate('c');
            $entry['last_body_sha256'] = hash('sha256', $body);
            if ($successes >= 3) {
                $entry['done'] = true;
                $entry['done_at'] = gmdate('c');
            }
            $ok++;
        } else {
            $failed++;
        }

        $state[$leadId] = $entry;
    }

    $tmp = $stateFile . '.tmp';
    file_put_contents($tmp, json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
    @chmod($tmp, 0600);
    rename($tmp, $stateFile);
    @chmod($stateFile, 0600);

    fwrite(STDOUT, sprintf(
        "ENKAF_SHEET_SYNC sent=%d ok=%d failed=%d skipped=%d total=%d\n",
        $sent,
        $ok,
        $failed,
        $skipped,
        count($lines)
    ));
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
    @chmod($lockFile, 0600);
}

function buildPayload(array $lead, string $secret): array
{
    $websiteLeadId = clean((string) ($lead['lead_id'] ?? ''));
    $serviceKey = clean((string) ($lead['service_key'] ?? ''));
    $serviceLabel = broadService($serviceKey);
    if ($serviceLabel === '') {
        $serviceLabel = clean((string) ($lead['service_label'] ?? 'استشارة قانونية'));
    }

    $campaignId = clean((string) ($lead['campaignid'] ?? ''));
    if ($campaignId === '') $campaignId = clean((string) ($lead['utm_campaign'] ?? ''));

    $landingPageId = clean((string) ($lead['landing_page_id'] ?? ''));
    $formId = 'ENKAF_WEBSITE_FORM_V1' . ($landingPageId !== '' ? ':' . $landingPageId : '');

    return [
        'google_key' => $secret,
        'lead_id' => 'WEB-' . $websiteLeadId,
        'form_id' => $formId,
        'campaign_id' => $campaignId,
        'adgroup_id' => clean((string) ($lead['adgroupid'] ?? '')),
        'creative_id' => clean((string) ($lead['creative'] ?? '')),
        'gcl_id' => clean((string) ($lead['gclid'] ?? '')),
        'gbraid' => clean((string) ($lead['gbraid'] ?? '')),
        'wbraid' => clean((string) ($lead['wbraid'] ?? '')),
        'is_test' => false,
        'user_column_data' => [
            [
                'column_id' => 'FULL_NAME',
                'column_name' => 'الاسم',
                'string_value' => clean((string) ($lead['full_name'] ?? '')),
            ],
            [
                'column_id' => 'PHONE_NUMBER',
                'column_name' => 'رقم الجوال',
                'string_value' => clean((string) (($lead['phone_e164'] ?? '') ?: ($lead['phone_normalized'] ?? ''))),
            ],
            [
                'column_id' => 'SERVICE',
                'column_name' => 'الخدمة المطلوبة',
                'string_value' => $serviceLabel,
            ],
        ],
    ];
}

function broadService(string $key): string
{
    $map = [
        'general_consultation' => 'استشارة قانونية',
        'litigation' => 'استشارة قانونية',
        'corporate' => 'شركات وعقود',
        'company_formation' => 'شركات وعقود',
        'foreign_investment' => 'شركات وعقود',
        'contracts' => 'شركات وعقود',
        'governance' => 'شركات وعقود',
        'commercial_dispute' => 'قضايا تجارية',
        'arbitration' => 'قضايا تجارية',
        'debt_collection' => 'تحصيل ديون وتنفيذ',
        'enforcement' => 'تحصيل ديون وتنفيذ',
        'trademark' => 'ملكية فكرية',
        'ip' => 'ملكية فكرية',
        'real_estate' => 'خدمات عقارية',
        'real_estate_dispute' => 'خدمات عقارية',
        'real_estate_contract' => 'خدمات عقارية',
        'real_estate_documentation' => 'خدمات عقارية',
    ];
    return $map[$key] ?? '';
}

function postJson(string $url, array $payload): array
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return [0, '', 'json_encode_failed'];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'ENKAF-Website-Sheet-Sync/1.0',
        ]);
        $body = curl_exec($ch);
        $error = $body === false ? (string) curl_error($ch) : '';
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return [$code, is_string($body) ? $body : '', $error];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\nUser-Agent: ENKAF-Website-Sheet-Sync/1.0\r\n",
            'content' => $json,
            'timeout' => 15,
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $code = 0;
    foreach (($http_response_header ?? []) as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) $code = (int) $m[1];
    }
    return [$code, is_string($body) ? $body : '', $body === false ? 'http_request_failed' : ''];
}

function clean(string $value, int $max = 1000): string
{
    $value = trim($value);
    if (function_exists('mb_substr')) return mb_substr($value, 0, $max, 'UTF-8');
    return substr($value, 0, $max);
}
