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

if (!is_callable('payment_cleanup_inactive_orders')) {
    fwrite(STDERR, "Rotina segura de limpeza de pedidos ausente.\n");
    exit(1);
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

$rankingExpression = payment_ranking_datetime_sql('purchase');
if (strpos($rankingExpression, "purchase.payment_method = 'VenoPag'") === false
    || strpos($rankingExpression, 'purchase.date_created') === false
    || strpos($rankingExpression, 'purchase.date_updated') === false) {
    fwrite(STDERR, "Regra de data do ranking VenoPag ausente.\n");
    exit(1);
}

$expiration = payment_expiration_datetime(30);
if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}-03:00$/', $expiration)) {
    fwrite(STDERR, "Data de expiração inválida.\n");
    exit(1);
}

$checkoutSource = file_get_contents(dirname(__DIR__) . '/class/Main.php');
$gatewaySelectionPosition = strpos($checkoutSource, '$orderPaymentProvider = payment_provider_for_amount($total_amount)');
$orderInsertPosition = strpos($checkoutSource, 'INSERT INTO `order_list` (`code`, `customer_id`, `product_name`, `quantity`, `status`, `total_amount`, `order_token`, `order_numbers`, `product_id`, `payment_method`');
$lateGatewayPosition = strpos($checkoutSource, 'payment_register_order_gateway($oid, $total_amount)');
if ($gatewaySelectionPosition === false
    || $orderInsertPosition === false
    || $lateGatewayPosition === false
    || $gatewaySelectionPosition >= $orderInsertPosition
    || $orderInsertPosition >= $lateGatewayPosition) {
    fwrite(STDERR, "O gateway não está sendo persistido junto com o pedido.\n");
    exit(1);
}

echo "Núcleo de pagamentos validado para 6 gateways.\n";
