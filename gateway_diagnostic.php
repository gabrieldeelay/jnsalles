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
    'runtime' => [
        'php' => PHP_VERSION,
        'curl' => extension_loaded('curl'),
        'openssl' => extension_loaded('openssl'),
    ],
    'payment_logs' => $paymentLogs,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

