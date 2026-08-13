<?php

require_once __DIR__ . '/settings.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
    exit;
}

$provider = strtolower(trim((string) ($_GET['notify'] ?? '')));
$raw = file_get_contents('php://input');
$result = payment_process_webhook($provider, is_string($raw) ? $raw : '', payment_request_headers());

http_response_code((int) ($result['http'] ?? (!empty($result['ok']) ? 200 : 400)));
echo json_encode([
    'ok' => !empty($result['ok']),
    'message' => $result['message'] ?? (!empty($result['ok']) ? 'OK' : 'Notificação recusada.'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
