<?php

$token = (string) ($_GET['token'] ?? '');
if ($token === '' || !hash_equals('836471c079e507513ae0f066cf84322c81a17ba28cad0287c66a362c25558e65', hash('sha256', $token))) {
    http_response_code(404);
    exit;
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/settings.php';

$tests = [];
foreach (['venopag', 'pay2m'] as $provider) {
    $tests[$provider] = payment_test_gateway($provider);
}

$credentials = payment_venopag_credentials();
$responseHeaders = [];
$curl = curl_init(payment_venopag_base_url() . '/api/consult-transaction?request_number=codex_connection_test');
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 7,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => !empty($credentials['ok']) ? $credentials['headers'] : [],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HEADERFUNCTION => function ($handle, $line) use (&$responseHeaders) {
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
        }
        return strlen($line);
    },
]);
$raw = curl_exec($curl);
$probe = [
    'status' => (int) curl_getinfo($curl, CURLINFO_HTTP_CODE),
    'content_type' => (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
    'error' => curl_error($curl),
    'app_error_code' => (int) ($responseHeaders['x-app-error-code'] ?? 0),
    'body_prefix' => is_string($raw) ? mb_substr(trim(strip_tags($raw)), 0, 300) : '',
];
curl_close($curl);

echo json_encode([
    'active_provider' => payment_active_provider(),
    'tests' => $tests,
    'venopag_probe' => $probe,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

