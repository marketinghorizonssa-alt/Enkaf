<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/app/leads.php';
require_once dirname(__DIR__) . '/app/views.php';

$cfg = site_config();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
$csp = "default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self' https://www.googletagmanager.com 'unsafe-inline'; connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://www.googleadservices.com; frame-src https://www.googletagmanager.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443)
    || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
if ($isHttps) $csp .= '; upgrade-insecure-requests';
header('Content-Security-Policy: ' . $csp);
if ($cfg['review_mode']) header('X-Robots-Tag: noindex, nofollow');

$rawPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$path = normalize_path($rawPath);

if ($path === '/api/lead/') handle_lead_submission();
if ($path === '/api/leads.csv/') handle_lead_feed();

if ($path === '/healthz/') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode([
        'ok' => true,
        'service' => 'enkaf-landing-site',
        'build' => BUILD_ID,
        'design' => 'luxury-office-v2',
        'review_mode' => $cfg['review_mode'],
        'gtm_configured' => $cfg['gtm_id'] !== '',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($path === '/robots.txt/') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache, max-age=0');
    if ($cfg['review_mode']) {
        echo "User-agent: *\nDisallow: /\n";
    } else {
        $sitemap = $cfg['site_url'] . '/sitemap.xml';
        echo "User-agent: Google-InspectionTool\nDisallow:\n\n";
        echo "User-agent: Googlebot\nDisallow:\n\n";
        echo "User-agent: Googlebot-Image\nDisallow:\n\n";
        echo "User-agent: *\nDisallow:\n\n";
        echo "Sitemap: {$sitemap}\n";
    }
    exit;
}

if ($path === '/sitemap.xml/') {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: no-cache, max-age=0');
    $urls = array_keys(page_catalog());
    $urls[] = '/سياسة-الخصوصية/';
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        echo '  <url><loc>' . htmlspecialchars(absolute_url($u), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc><lastmod>2026-08-20</lastmod></url>' . "\n";
    }
    echo '</urlset>';
    exit;
}

if ($path === '/سياسة-الخصوصية/') {
    echo privacy_html();
    exit;
}
if ($path === '/شكرا/') {
    echo thank_you_html();
    exit;
}

$catalog = page_catalog();
if (isset($catalog[$path])) {
    echo page_html($catalog[$path]);
    exit;
}

http_response_code(404);
echo not_found_html();
