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

function normalize_saudi_phone(string $raw): ?string {
    $value = preg_replace('/[^0-9+]/', '', trim($raw));
    if ($value === null) return null;
    if (str_starts_with($value, '00966')) $value = '+' . substr($value, 2);
    if (preg_match('/^05\d{8}$/', $value)) return '+966' . substr($value, 1);
    if (preg_match('/^5\d{8}$/', $value)) return '+966' . $value;
    if (preg_match('/^\+9665\d{8}$/', $value)) return $value;
    if (preg_match('/^9665\d{8}$/', $value)) return '+' . $value;
    return null;
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
        if (($row['phone_e164'] ?? '') === $lead['phone_e164'] && strtotime((string)($row['created_at'] ?? '1970-01-01')) >= $since) {
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
    $phone = normalize_saudi_phone(payload_value($payload, 'phone', 40));
    $serviceKey = payload_value($payload, 'service', 80);
    $services = service_options();
    $consent = in_array((string)($payload['privacy_consent'] ?? ''), ['1','true','on','yes'], true);

    $errors = [];
    if (u_strlen($name) < 2) $errors['full_name'] = 'اكتب الاسم بشكل صحيح.';
    if ($phone === null) $errors['phone'] = 'اكتب رقم جوال سعودي صحيح.';
    if (!isset($services[$serviceKey])) $errors['service'] = 'اختر نوع الخدمة القانونية.';
    if (!$consent) $errors['privacy_consent'] = 'يلزم الموافقة على سياسة الخصوصية قبل الإرسال.';
    if ($errors) json_response(['ok' => false, 'error' => 'validation_error', 'fields' => $errors], 422);

    try {
        if (!rate_limit_ok()) {
            json_response(['ok' => false, 'error' => 'rate_limited', 'message' => 'تم استلام عدة محاولات خلال وقت قصير. حاول مرة أخرى بعد قليل.'], 429);
        }
        $nowIso = gmdate('c');
        $cfg = site_config();
        $landingPath = payload_value($payload, 'landing_path', 300) ?: '/';
        $lead = [
            'lead_id' => generate_lead_id(),
            'created_at' => $nowIso,
            'full_name' => $name,
            'phone_e164' => $phone,
            'phone_raw' => payload_value($payload, 'phone', 40),
            'service_key' => $serviceKey,
            'service_label' => $services[$serviceKey],
            'landing_page_id' => payload_value($payload, 'landing_page_id', 50),
            'landing_path' => $landingPath,
            'landing_url' => payload_value($payload, 'landing_url', 1200),
            'utm_source' => payload_value($payload, 'utm_source', 300),
            'utm_medium' => payload_value($payload, 'utm_medium', 300),
            'utm_campaign' => payload_value($payload, 'utm_campaign', 300),
            'utm_term' => payload_value($payload, 'utm_term', 500),
            'utm_content' => payload_value($payload, 'utm_content', 500),
            'gclid' => payload_value($payload, 'gclid', 500),
            'gbraid' => payload_value($payload, 'gbraid', 500),
            'wbraid' => payload_value($payload, 'wbraid', 500),
            'ttclid' => payload_value($payload, 'ttclid', 500),
            'fbclid' => payload_value($payload, 'fbclid', 500),
            'referrer' => payload_value($payload, 'referrer', 1000),
            'first_landing_url' => payload_value($payload, 'first_landing_url', 1200),
            'session_id' => payload_value($payload, 'session_id', 150),
            'consent' => true,
            'consent_version' => PRIVACY_VERSION,
            'consent_at' => $nowIso,
            'server_submit_at' => $nowIso,
            'source' => 'website',
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 800),
            'ip_hash' => hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? '') . '|' . date('Y-m')),
        ];
        $lead = append_lead($lead);
        $ref = rawurlencode((string)$lead['lead_id']);
        json_response([
            'ok' => true,
            'lead_id' => $lead['lead_id'],
            'duplicate' => (bool)$lead['duplicate_flag'],
            'thank_you_url' => '/شكرا/?ref=' . $ref,
        ]);
    } catch (Throwable $e) {
        error_log('[ENKAF lead] ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'server_error', 'message' => 'تعذر حفظ الطلب الآن. حاول مرة أخرى أو تواصل معنا مباشرة.'], 500);
    }
}

function csv_escape($value): string {
    $stream = fopen('php://temp', 'r+');
    if ($stream === false) return '""';
    fputcsv($stream, [(string)$value]);
    rewind($stream);
    $out = trim((string)stream_get_contents($stream));
    fclose($stream);
    return $out;
}

function handle_lead_feed(): never {
    $cfg = site_config();
    $token = (string)($_GET['token'] ?? '');
    $expected = (string)$cfg['feed_token'];
    if ($expected === '' || !hash_equals($expected, $token)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    $out = fopen('php://output', 'w');
    if ($out === false) exit;
    fwrite($out, "\xEF\xBB\xBF");
    $headers = ['lead_id','created_at','full_name','phone_e164','phone_raw','service_key','service_label','landing_page_id','landing_path','landing_url','source','utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid','ttclid','fbclid','referrer','first_landing_url','session_id','consent','consent_version','consent_at','server_submit_at','duplicate_flag'];
    fputcsv($out, $headers);
    $path = private_data_dir() . '/enkaf-leads.ndjson';
    if (is_file($path) && ($fp = fopen($path, 'r')) !== false) {
        if (flock($fp, LOCK_SH)) {
            while (($line = fgets($fp)) !== false) {
                $r = json_decode(trim($line), true);
                if (!is_array($r)) continue;
                fputcsv($out, [
                    $r['lead_id'] ?? '',$r['created_at'] ?? '',$r['full_name'] ?? '',$r['phone_e164'] ?? '',$r['phone_raw'] ?? '',
                    $r['service_key'] ?? '',$r['service_label'] ?? '',$r['landing_page_id'] ?? '',$r['landing_path'] ?? '',$r['landing_url'] ?? '',
                    $r['source'] ?? '',$r['utm_source'] ?? '',$r['utm_medium'] ?? '',$r['utm_campaign'] ?? '',
                    $r['utm_term'] ?? '',$r['utm_content'] ?? '',$r['gclid'] ?? '',$r['gbraid'] ?? '',$r['wbraid'] ?? '',$r['ttclid'] ?? '',
                    $r['fbclid'] ?? '',$r['referrer'] ?? '',$r['first_landing_url'] ?? '',$r['session_id'] ?? '',!empty($r['consent']) ? 'TRUE' : 'FALSE',
                    $r['consent_version'] ?? '',$r['consent_at'] ?? '',$r['server_submit_at'] ?? '',!empty($r['duplicate_flag']) ? 'TRUE' : 'FALSE'
                ]);
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    fclose($out);
    exit;
}
