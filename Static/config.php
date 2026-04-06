<?php

// ─── Main settings ───────────────────────────────────────────────────
// ──────────────────────────────────────────────────────
$defaults = [
    'System_name'    => 'Billing System',
    'company_name'    => 'Shop 101',
    'company_tagline' => '',
    'logo_path'       => '',
    'logo_initials'   => 'AR',
    'invoice_prefix'  => 'INV',
    'vat_rate'        => 5,
    'currency'        => 'AED',
    'email'           => '',
    'phone'           => '',
    'address_line1'   => '',
    'address_line2'   => '',
    'city'            => 'Dubai',
    'country'         => 'United Arab Emirates',
    'Lang'         => 'en',
    'Direction'         => 'left',
];
$settings = file_exists('Static/settings.json')
    ? array_merge($defaults, json_decode(file_get_contents('Static/settings.json'), true))
    : $defaults;
// ──────────────────────────────────────────────────────
// ──────────────────────────────────────────────────────







// ─── Main translated ───────────────────────────────────────────────────
// ──────────────────────────────────────────────────────
if (isset($_GET['Lang'])) {
    $_SESSION['Lang'] = $_GET['Lang'];
    $_SESSION['Direction'] = $_GET['Direction']; 
    } else if(isset($_SESSION['Lang']) AND ($_SESSION['Lang'] != '')) {
    
    
    } else if(isset($settings['Lang']) AND ($settings['Lang'] != '')) {
$_SESSION['Lang'] = $settings['Lang'];
$_SESSION['Direction'] = $settings['Direction'];
} else {
    $_SESSION['Lang'] = 'en';
    $_SESSION['Direction'] = 'left';
}




$lang = $_SESSION['Lang'];
$Direction = $_SESSION['Direction'];

// ─── Cache helpers ─────────────────────────────────────────────────────────────

function cache_file(string $lang22): string {
    return __DIR__ . "/translated/{$lang22}.json";
}

function load_cache(string $lang22): array {
    $file = cache_file($lang22);
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_cache(string $lang22, array $cache): void {
    file_put_contents(
        cache_file($lang22),
        json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

// ─── Main translate function ───────────────────────────────────────────────────

function translate(string $text, string $lang22): array {
    $key   = mb_strtolower(trim($text));
    $cache = load_cache($lang22);

    // 1. Found in cache — return instantly, no API call
    if (isset($cache[$key])) {
        return [
            'translated' => $cache[$key],
            'source'     => 'cache'
        ];
    }

    // 2. Not cached — call the Python API
    $url      = 'http://localhost:9000/translate?text=' . urlencode($text) . '&lang=' . urlencode($lang22);
    $response = @file_get_contents($url);

    if ($response === false) {
        return ['error' => 'API unavailable. Is server.py running?'];
    }

    $result = json_decode($response, true);

    if (!isset($result['translated'])) {
        return ['error' => $result['error'] ?? 'Unknown API error'];
    }

    // 3. Save new word to cache file (e.g. ar.json, fr.json …)
    $cache[$key] = $result['translated'];
    save_cache($lang22, $cache);

    return [
        'translated' => $result['translated'],
        'source'     => 'api'
    ];
}
// ──────────────────────────────────────────────────────
// ──────────────────────────────────────────────────────
