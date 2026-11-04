<?php

$token = (string) ($_GET['token'] ?? '');
$expected = '836471c079e507513ae0f066cf84322c81a17ba28cad0287c66a362c25558e65';
if ($token === '' || !hash_equals($expected, hash('sha256', $token))) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/settings.php';

$enabled = [];
foreach (payment_provider_definitions() as $provider => $definition) {
    if ((string) payment_setting($provider, '2') === '1') {
        $enabled[] = $provider;
    }
}

$active = payment_active_provider();
$result = $active ? payment_test_gateway($active) : [
    'status' => 'failed',
    'msg' => 'Nenhum gateway unico esta ativo.',
];

$probe = null;
if ($active === 'venopag') {
    $credentials = payment_venopag_credentials();
    if (!empty($credentials['ok'])) {
        $responseHeaders = [];
        $curl = curl_init(payment_venopag_base_url() . '/api/consult-transaction?request_number=codex_connection_test');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 7,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $credentials['headers'],
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
    }
}

$paymentLogs = [];
$errorLog = (string) ini_get('error_log');
if ($errorLog !== '' && is_file($errorLog) && is_readable($errorLog)) {
    $lines = @file($errorLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach (array_slice($lines, -500) as $line) {
        if (strpos($line, '[payments]') !== false) {
            $paymentLogs[] = preg_replace('/(token|secret|authorization|client[_ -]?id)\s*[=:]\s*\S+/i', '$1=[redacted]', $line);
        }
    }
    $paymentLogs = array_slice($paymentLogs, -30);
}

echo json_encode([
    'ok' => ($result['status'] ?? '') === 'success',
    'active_provider' => $active,
    'enabled_providers' => $enabled,
    'gateway_test' => $result,
    'provider_probe' => $probe,
    'runtime' => [
        'php' => PHP_VERSION,
        'curl' => extension_loaded('curl'),
        'openssl' => extension_loaded('openssl'),
    ],
    'payment_logs' => $paymentLogs,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

