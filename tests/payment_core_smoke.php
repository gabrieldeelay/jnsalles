<?php

define('BASE_URL', 'https://jnsalles.online/');
require dirname(__DIR__) . '/includes/payment_core.php';

$providers = array_keys(payment_provider_definitions());
$expected = ['mercadopago', 'gerencianet', 'paggue', 'openpix', 'pay2m', 'venopag'];
if ($providers !== $expected) {
    fwrite(STDERR, "Lista de gateways divergente.\n");
    exit(1);
}

foreach ($expected as $provider) {
    if (!is_callable('payment_create_' . $provider)) {
        fwrite(STDERR, "Integração ausente: {$provider}.\n");
        exit(1);
    }
}

if (!payment_customer_document_is_valid('52998224725')) {
    fwrite(STDERR, "Validação de CPF falhou.\n");
    exit(1);
}
if (payment_customer_document_is_valid('11111111111')) {
    fwrite(STDERR, "CPF inválido foi aceito.\n");
    exit(1);
}
if (payment_venopag_minimum_amount() < 1.00) {
    fwrite(STDERR, "Valor mínimo da VenoPag inseguro.\n");
    exit(1);
}

$expiration = payment_expiration_datetime(30);
if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}-03:00$/', $expiration)) {
    fwrite(STDERR, "Data de expiração inválida.\n");
    exit(1);
}

echo "Núcleo de pagamentos validado para 6 gateways.\n";
