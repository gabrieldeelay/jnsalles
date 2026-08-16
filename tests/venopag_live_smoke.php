<?php

if (getenv('VENOPAG_LIVE_TEST') !== '1') {
    fwrite(STDERR, "Defina VENOPAG_LIVE_TEST=1 para executar este teste.\n");
    exit(2);
}
if (getenv('VENOPAG_CLIENT_ID') === false || getenv('VENOPAG_CLIENT_SECRET') === false) {
    fwrite(STDERR, "Credenciais VenoPag ausentes no ambiente.\n");
    exit(2);
}

define('BASE_URL', 'https://jnsalles.online/');
require dirname(__DIR__) . '/includes/payment_core.php';

$probe = payment_venopag_consult('jnsalles_connection_probe');
$probeCode = (int) ($probe['app_error_code'] ?? 0);
$probeMessage = mb_strtolower((string) ($probe['message'] ?? ''), 'UTF-8');
if ($probeCode === 401 || $probeCode === 403) {
    fwrite(STDERR, "Credenciais ou conta VenoPag recusadas.\n");
    exit(1);
}
if ($probeCode !== 404 && strpos($probeMessage, 'não encontrado') === false) {
    fwrite(STDERR, "A consulta de autenticação retornou um estado inesperado.\n");
    exit(1);
}

if (getenv('VENOPAG_LIVE_CHARGE') !== '1') {
    echo "Credenciais e permissão de consulta VenoPag validadas.\n";
    exit(0);
}

$payload = [
    'amount' => 1.00,
    'name' => 'Teste JNSalles',
    'description' => 'Teste técnico de integração JNSalles',
    'webhook_url' => BASE_URL . 'webhook.php?notify=venopag&token=diagnostico-nao-pago',
];
$document = payment_venopag_default_document();
if (payment_customer_document_is_valid($document)) {
    $payload['document'] = $document;
}

$charge = payment_venopag_request('POST', '/api/cashin', $payload);
$data = $charge['json'] ?? [];
if (empty($charge['ok']) || ($data['status'] ?? '') !== 'pending'
    || strlen((string) ($data['copyPaste'] ?? '')) < 50
    || trim((string) ($data['request_number'] ?? '')) === '') {
    fwrite(STDERR, "A VenoPag não gerou um PIX válido: " . ($charge['message'] ?? 'erro desconhecido') . "\n");
    exit(1);
}

$consult = payment_venopag_consult((string) $data['request_number']);
if (empty($consult['ok']) || strtolower((string) ($consult['json']['status'] ?? '')) !== 'pending') {
    fwrite(STDERR, "A cobrança foi criada, mas não pôde ser consultada.\n");
    exit(1);
}

echo "PIX VenoPag criado e consultado com sucesso: {$data['request_number']}\n";
