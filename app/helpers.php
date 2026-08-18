<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function normalize_path(string $path): string {
    $path = parse_url($path, PHP_URL_PATH) ?: '/';
    $decoded = rawurldecode($path);
    if ($decoded !== '/' && !str_ends_with($decoded, '/')) {
        $decoded .= '/';
    }
    return $decoded;
}

function absolute_url(string $path): string {
    $cfg = site_config();
    if ($path === '/') return $cfg['site_url'] . '/';
    return $cfg['site_url'] . '/' . ltrim($path, '/');
}

function icon_svg(string $name): string {
    $icons = [
        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
        'check' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.8 2.9 8.1 7 10 4.1-1.9 7-5.2 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-5"/></svg>',
        'briefcase' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="12" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/></svg>',
        'scale' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M6 21h12M4 7h16M7 7l-4 7h8L7 7Zm10 0-4 7h8l-4-7Z"/></svg>',
        'document' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h5M9 12h6M9 16h6"/></svg>',
        'mark' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v16H7z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>',
        'building' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 21V5l8-3 8 3v16M8 8h2M14 8h2M8 12h2M14 12h2M8 16h2M14 16h2M10 21v-3h4v3"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3 4 6c0 7.7 6.3 14 14 14l3-3-4-4-2 2c-2.7-.9-5.1-3.3-6-6l2-2-4-4Z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.8A8 8 0 0 1 8.1 18.7L4 20l1.3-4A8 8 0 1 1 20 11.8Z"/><path d="M8.5 8.2c.5 3.2 2.1 5 5.4 6.2l1.5-1.6 2.1 1c-.2 1.2-1.2 2.3-2.4 2.5-4.7-.3-8.1-3.7-8.5-8.4.2-1.1 1.3-2 2.4-2.3l1.1 2.1-1.6.5Z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
    ];
    return $icons[$name] ?? $icons['check'];
}

function page_schema(array $page): array {
    $cfg = site_config();
    $service = [
        '@context' => 'https://schema.org',
        '@type' => 'LegalService',
        'name' => BRAND_NAME_AR,
        'url' => absolute_url($page['slug']),
        'description' => $page['description'],
        'areaServed' => ['@type' => 'Country', 'name' => 'Saudi Arabia'],
        'telephone' => $cfg['phone_e164'],
        'email' => $cfg['email_primary'],
        'provider' => [
            '@type' => 'Organization',
            'name' => BRAND_NAME_AR,
            'url' => $cfg['site_url'],
        ],
    ];
    if (!empty($page['faq'])) {
        $service['subjectOf'] = [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn($item) => [
                '@type' => 'Question',
                'name' => $item[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item[1]],
            ], $page['faq']),
        ];
    }
    return $service;
}

function request_headers_safe(): array {
    return [
        'referrer' => substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 1000),
        'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 800),
    ];
}
