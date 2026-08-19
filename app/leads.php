<?php
declare(strict_types=1);

function private_data_dir(): string {
    $cfg = site_config();
    $dir = rtrim((string)$cfg['data_dir'], DIRECTORY_SEPARATOR);
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to prepare private lead storage.');
    }
    return $dir;
}

function ascii_digits(string $value): string {
    return strtr($value, [
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
    ]);
}

function normalize_contact_phone(string $raw): ?array {
    $input = trim(ascii_digits($raw));
    $digits = preg_replace('/\D+/', '', $input);
    if ($digits === null) return null;
    $len = strlen($digits);
    if ($len < 7 || $len > 15) return null;

    $normalized = $digits;
    $e164 = '';

    if (preg_match('/^00966(5\d{8})$/', $digits, $m)) {
        $normalized = '+966' . $m[1];
        $e164 = $normalized;
    } elseif (preg_match('/^966(5\d{8})$/', $digits, $m)) {
        $normalized = '+966' . $m[1];
        $e164 = $normalized;
    } elseif (preg_match('/^05\d{8}$/', $digits)) {
        $normalized = '+966' . substr($digits, 1);
        $e164 = $normalized;
    } elseif (preg_match('/^5\d{8}$/', $digits)) {
        $normalized = '+966' . $digits;
        $e164 = $normalized;
    } elseif (str_starts_with($input, '+') && $len >= 8) {
        $normalized = '+' . $digits;
        $e164 = $normalized;
    } elseif (str_starts_with($digits, '00') && $len >= 10) {
        $normalized = '+' . substr($digits, 2);
        $e164 = $normalized;
    }

    return [
        'input' => u_substr($input, 40),
        'normalized' => $normalized,
        'e164' => $e164,
    ];
}

function u_substr(string $value, int $max): string {
    if (function_exists('mb_substr')) return mb_substr($value, 0, $max, 'UTF-8');
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($chars)) return substr($value, 0, $max);
    return implode('', array_slice($chars, 0, $max));
}

function u_strlen(string $value): int {
    if (function_exists('mb_strlen')) return mb_strlen($value, 'UTF-8');
    $count = preg_match_all('/./us', $value, $m);
    return $count === false ? strlen($value) : $count;
}

function payload_value(array $payload, string $key, int $max = 500): string {
    $value = trim((string)($payload[$key] ?? ''));
    return u_substr($value, $max);
}

function json_response(array $body, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function rate_limit_ok(): bool {
    $dir = private_data_dir();
    $path = $dir . '/rate-limits.json';
    $fp = fopen($path, 'c+');
    if ($fp === false) return true;
    if (!flock($fp, LOCK_EX)) { fclose($fp); return true; }
    $raw = stream_get_contents($fp);
    $state = $raw ? json_decode($raw, true) : [];
    if (!is_array($state)) $state = [];
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = hash('sha256', $ip . '|' . date('Y-m-d-H'));
    $window = time() - (time() % 600);
    $entry = $state[$key] ?? ['window' => $window, 'count' => 0];
    if (($entry['window'] ?? 0) !== $window) $entry = ['window' => $window, 'count' => 0];
    if (($entry['count'] ?? 0) >= 5) {
        flock($fp, LOCK_UN); fclose($fp); return false;
    }
    $entry['count'] = (int)($entry['count'] ?? 0) + 1;
    $state[$key] = $entry;
    foreach ($state as $k => $v) {
        if ((int)($v['window'] ?? 0) < $window - 3600) unset($state[$k]);
    }
    ftruncate($fp, 0); rewind($fp);
    fwrite($fp, json_encode($state, JSON_UNESCAPED_SLASHES));
    fflush($fp); flock($fp, LOCK_UN); fclose($fp);
    @chmod($path, 0600);
    return true;
}

function read_leads_locked($fp): array {
    rewind($fp);
    $leads = [];
    while (($line = fgets($fp)) !== false) {
        $row = json_decode(trim($line), true);
        if (is_array($row)) $leads[] = $row;
    }
    return $leads;
}

function append_lead(array $lead): array {
    $dir = private_data_dir();
    $path = $dir . '/enkaf-leads.ndjson';
    $fp = fopen($path, 'c+');
    if ($fp === false) throw new RuntimeException('Unable to open lead store.');
    if (!flock($fp, LOCK_EX)) { fclose($fp); throw new RuntimeException('Unable to lock lead store.'); }
    $existing = read_leads_locked($fp);
    $since = time() - 86400;
    $duplicate = false;
    foreach ($existing as $row) {
        $existingPhone = (string)($row['phone_normalized'] ?? $row['phone_e164'] ?? '');
        if ($existingPhone !== '' && $existingPhone === $lead['phone_normalized'] && strtotime((string)($row['created_at'] ?? '1970-01-01')) >= $since) {
            $duplicate = true;
            break;
        }
    }
    $lead['duplicate_flag'] = $duplicate;
    fseek($fp, 0, SEEK_END);
    $encoded = json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false || fwrite($fp, $encoded . "\n") === false) {
        flock($fp, LOCK_UN); fclose($fp); throw new RuntimeException('Unable to write lead store.');
    }
    fflush($fp); flock($fp, LOCK_UN); fclose($fp);
    @chmod($path, 0600);
    return $lead;
}

function generate_lead_id(): string {
    return 'ENK-' . gmdate('ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

function handle_lead_submission(): never {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
    }
    $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        $payload = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($payload)) $payload = [];
    } else {
        $payload = $_POST;
    }

    if (payload_value($payload, 'website', 200) !== '') {
        json_response(['ok' => true, 'lead_id' => 'accepted'], 200);
    }

    $name = payload_value($payload, 'full_name', 100);
    $phone = normalize_contact_phone(payload_value($payload, 'phone', 40));
    $serviceKey = payload_value($payload, 'service', 80);
    $services = service_options();
    $consent = in_array((string)($payload['privacy_consent'] ?? ''), ['1','true','on','yes'], true);

    $errors = [];
    if (u_strlen($name) < 2) $errors['full_name'] = 'اكتب الاسم بشكل صحيح.';
    if ($phone === null) $errors['phone'] = 'اكتب رقم هاتف صحيح.';
    if (!isset($services[$serviceKey])) $errors['service'] = 'اختر نوع الخدمة القانونية.';
    if (!$consent) $errors['privacy_consent'] = 'يلزم الموافقة على سياسة الخصوصية قبل الإرسال.';
    if ($errors) json_response(['ok' => false, 'error' => 'validation_error', 'fields' => $errors], 422);

    try {
        if (!rate_limit_ok()) {
            json_response(['ok' => false, 'error' => 'rate_limited', 'message' => 'تم استلام عدة محاولات خلال وقت قصير. حاول مرة أخرى بعد قليل.'], 429);
        }
        $nowIso = gmdate('c');
        $landingPath = payload_value($payload, 'landing_path', 300) ?: '/';
        $landingUrl = payload_value($payload, 'landing_url', 1000) ?: absolute_url($landingPath);
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $lead = [
            'lead_id' => generate_lead_id(),
            'created_at' => $nowIso,
            'full_name' => $name,
            'phone_input' => $phone['input'],
            'phone_normalized' => $phone['normalized'],
            'phone_e164' => $phone['e164'],
            'service_key' => $serviceKey,
            'service_label' => $services[$serviceKey],
            'landing_page_id' => payload_value($payload, 'landing_page_id', 40),
            'landing_path' => $landingPath,
            'landing_url' => $landingUrl,
            'utm_source' => payload_value($payload, 'utm_source', 300),
            'utm_medium' => payload_value($payload, 'utm_medium', 300),
            'utm_campaign' => payload_value($payload, 'utm_campaign', 300),
            'utm_term' => payload_value($payload, 'utm_term', 300),
            'utm_content' => payload_value($payload, 'utm_content', 300),
            'gclid' => payload_value($payload, 'gclid', 300),
            'gbraid' => payload_value($payload, 'gbraid', 300),
            'wbraid' => payload_value($payload, 'wbraid', 300),
            'ttclid' => payload_value($payload, 'ttclid', 300),
            'fbclid' => payload_value($payload, 'fbclid', 300),
            'campaignid' => payload_value($payload, 'campaignid', 120),
            'adgroupid' => payload_value($payload, 'adgroupid', 120),
            'creative' => payload_value($payload, 'creative', 120),
            'keyword' => payload_value($payload, 'keyword', 300),
            'matchtype' => payload_value($payload, 'matchtype', 40),
            'device' => payload_value($payload, 'device', 40),
            'network' => payload_value($payload, 'network', 40),
            'targetid' => payload_value($payload, 'targetid', 120),
            'loc_physical_ms' => payload_value($payload, 'loc_physical_ms', 80),
            'referrer' => payload_value($payload, 'referrer', 1000),
            'first_landing_url' => payload_value($payload, 'first_landing_url', 1000),
            'session_id' => payload_value($payload, 'session_id', 120),
            'consent' => true,
            'consent_version' => PRIVACY_VERSION,
            'consent_at' => $nowIso,
            'server_submit_at' => $nowIso,
            'ip_hash' => $ip === '' ? '' : hash('sha256', $ip . '|' . PRIVACY_VERSION),
            'user_agent' => u_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 800),
        ];
        $lead = append_lead($lead);
        json_response([
            'ok' => true,
            'lead_id' => $lead['lead_id'],
            'duplicate_flag' => (bool)$lead['duplicate_flag'],
            'thank_you_url' => '/شكرا/?ref=' . rawurlencode($lead['lead_id']),
        ], 201);
    } catch (Throwable $e) {
        error_log('ENKAF lead storage error: ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'storage_error', 'message' => 'تعذر حفظ الطلب الآن. يمكنك التواصل معنا مباشرة عبر الاتصال أو واتساب.'], 503);
    }
}

function handle_lead_feed(): never {
    $cfg = site_config();
    $token = (string)($_GET['token'] ?? '');
    if ($cfg['feed_token'] === '' || !hash_equals($cfg['feed_token'], $token)) {
        http_response_code(404);
        exit;
    }
    $path = private_data_dir() . '/enkaf-leads.ndjson';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: inline; filename="enkaf-leads.csv"');
    header('Cache-Control: no-store, private');
    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF");
    $headers = ['Lead ID','Created At','Name','Phone','Phone E164','Service','Landing Page ID','Landing Path','UTM Source','UTM Medium','UTM Campaign','UTM Term','UTM Content','GCLID','GBRAID','WBRAID','TTCLID','FBCLID','Campaign ID','Ad Group ID','Creative','Keyword','Match Type','Device','Network','Target ID','Location ID','Referrer','First Landing URL','Session ID','Consent','Consent Version','Consent At','Server Submit At','Duplicate Flag'];
    fputcsv($out, $headers);
    if (is_file($path)) {
        $rows = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach (array_reverse($rows) as $line) {
            $r = json_decode($line, true);
            if (!is_array($r)) continue;
            fputcsv($out, [
                $r['lead_id'] ?? '',$r['created_at'] ?? '',$r['full_name'] ?? '',$r['phone_normalized'] ?? ($r['phone_e164'] ?? ''),$r['phone_e164'] ?? '',$r['service_label'] ?? '',
                $r['landing_page_id'] ?? '',$r['landing_path'] ?? '',$r['utm_source'] ?? '',$r['utm_medium'] ?? '',$r['utm_campaign'] ?? '',
                $r['utm_term'] ?? '',$r['utm_content'] ?? '',$r['gclid'] ?? '',$r['gbraid'] ?? '',$r['wbraid'] ?? '',$r['ttclid'] ?? '',$r['fbclid'] ?? '',
                $r['campaignid'] ?? '',$r['adgroupid'] ?? '',$r['creative'] ?? '',$r['keyword'] ?? '',$r['matchtype'] ?? '',$r['device'] ?? '',$r['network'] ?? '',$r['targetid'] ?? '',$r['loc_physical_ms'] ?? '',
                $r['referrer'] ?? '',$r['first_landing_url'] ?? '',$r['session_id'] ?? '',!empty($r['consent']) ? 'TRUE' : 'FALSE',
                $r['consent_version'] ?? '',$r['consent_at'] ?? '',$r['server_submit_at'] ?? '',!empty($r['duplicate_flag']) ? 'TRUE' : 'FALSE'
            ]);
        }
    }
    fclose($out);
    exit;
}
