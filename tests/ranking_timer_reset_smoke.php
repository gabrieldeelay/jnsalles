<?php

if (getenv('JNSALLES_TEST_MODE') !== '1') {
    fwrite(STDERR, "Habilite JNSALLES_TEST_MODE para executar este teste.\n");
    exit(2);
}

$databaseName = (string) getenv('TEST_DB_NAME');
if (!preg_match('/(?:^|_)test$/i', $databaseName)) {
    fwrite(STDERR, "O banco deve terminar com _test.\n");
    exit(2);
}

define('DB_HOST', (string) getenv('TEST_DB_HOST'));
define('DB_NAME', $databaseName);
define('DB_USER', (string) getenv('TEST_DB_USER'));
define('DB_PASSWORD', (string) getenv('TEST_DB_PASSWORD'));

$_SERVER['DOCUMENT_ROOT'] = sys_get_temp_dir();
$_SERVER['REQUEST_URI'] = '/class/System.php?action=save_ranking_timer';
$_SERVER['HTTP_HOST'] = '127.0.0.1';

$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$connection->set_charset('utf8mb4');
$suffix = bin2hex(random_bytes(5));
$connection->query("INSERT INTO customer_list (firstname, lastname, phone) VALUES ('Ranking', '{$suffix}', '')");
$customerId = (int) $connection->insert_id;
$connection->query("INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, cotas_premiadas, cotas_premiadas_premios) VALUES ('Ranking {$suffix}', 'Teste', 0.20, 1, 1, 1, 1000, 1, 100, 'ranking-{$suffix}', '0001', '0001:PIX:normal')");
$productId = (int) $connection->insert_id;
$connection->query("INSERT INTO order_list (code, customer_id, quantity, total_amount, status, product_id, payment_method, order_token) VALUES ('OLD{$suffix}', {$customerId}, 3, 0.60, 2, {$productId}, 'Manual', 'OLDTOKEN{$suffix}')");
$oldOrderId = (int) $connection->insert_id;

$timezone = new DateTimeZone('America/Sao_Paulo');
$start = new DateTime('-5 minutes', $timezone);
$end = new DateTime('+2 hours', $timezone);

session_id('ranking-reset-' . $suffix);
session_start();
$_SESSION['userdata'] = ['id' => 1, 'firstname' => 'Administrador', 'lastname' => 'Teste', 'type' => 1];
$_GET['action'] = 'save_ranking_timer';
$_POST = [
    'product_id' => $productId,
    'enabled' => '1',
    'mode' => 'restart',
    'start_at' => $start->format('Y-m-d\TH:i'),
    'end_at' => $end->format('Y-m-d\TH:i'),
];

$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
require dirname(__DIR__) . '/class/System.php';
$rawResponse = trim((string) ob_get_clean());
chdir($originalDirectory);
$response = json_decode($rawResponse, true);

$metaPrefix = 'ranking_timer_' . $productId . '_';
$statement = $connection->prepare('SELECT meta_value FROM system_info WHERE meta_field = ? LIMIT 1');
$resetField = $metaPrefix . 'reset';
$statement->bind_param('s', $resetField);
$statement->execute();
$resetRow = $statement->get_result()->fetch_assoc();
$statement->close();
$resetAt = (string) ($resetRow['meta_value'] ?? '');

$oldCount = $connection->prepare('SELECT COUNT(*) total FROM order_list WHERE product_id = ? AND status = 2 AND COALESCE(date_updated, date_created) > ?');
$oldCount->bind_param('is', $productId, $resetAt);
$oldCount->execute();
$oldVisible = (int) $oldCount->get_result()->fetch_assoc()['total'];
$oldCount->close();

sleep(1);
$connection->query("INSERT INTO order_list (code, customer_id, quantity, total_amount, status, product_id, payment_method, order_token) VALUES ('NEW{$suffix}', {$customerId}, 4, 0.80, 2, {$productId}, 'Manual', 'NEWTOKEN{$suffix}')");
$newOrderId = (int) $connection->insert_id;
$newCount = $connection->prepare('SELECT SUM(quantity) total FROM order_list WHERE product_id = ? AND status = 2 AND COALESCE(date_updated, date_created) > ?');
$newCount->bind_param('is', $productId, $resetAt);
$newCount->execute();
$newVisible = (int) $newCount->get_result()->fetch_assoc()['total'];
$newCount->close();

$extendedEnd = new DateTime('+4 hours', $timezone);
$_POST = [
    'product_id' => $productId,
    'enabled' => '1',
    'mode' => 'extend',
    'start_at' => $start->format('Y-m-d\TH:i'),
    'end_at' => $extendedEnd->format('Y-m-d\TH:i'),
];
$extendResponse = json_decode($sysset->save_ranking_timer(), true);

$statement = $connection->prepare('SELECT meta_field, meta_value FROM system_info WHERE meta_field IN (?, ?)');
$endField = $metaPrefix . 'end';
$statement->bind_param('ss', $resetField, $endField);
$statement->execute();
$extendedMeta = [];
$extendedResult = $statement->get_result();
while ($extendedRow = $extendedResult->fetch_assoc()) {
    $extendedMeta[$extendedRow['meta_field']] = $extendedRow['meta_value'];
}
$statement->close();
$extendedResetAt = (string) ($extendedMeta[$resetField] ?? '');
$savedExtendedEnd = (string) ($extendedMeta[$endField] ?? '');

$valid = ($response['status'] ?? '') === 'success'
    && ($extendResponse['status'] ?? '') === 'success'
    && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $resetAt)
    && $oldVisible === 0
    && $newVisible === 4
    && $extendedResetAt === $resetAt
    && $savedExtendedEnd === $extendedEnd->format('Y-m-d H:i:00');

$connection->query("DELETE FROM system_info WHERE meta_field LIKE 'ranking_timer_{$productId}_%'");
$connection->query("DELETE FROM order_list WHERE id IN ({$oldOrderId}, {$newOrderId})");
$connection->query('DELETE FROM product_list WHERE id = ' . $productId);
$connection->query('DELETE FROM customer_list WHERE id = ' . $customerId);
$connection->close();

if (!$valid) {
    fwrite(STDERR, 'O reinício do ranking falhou: ' . json_encode([
        'response' => $response,
        'reset_at' => $resetAt,
        'old_visible' => $oldVisible,
        'new_visible' => $newVisible,
        'extend_response' => $extendResponse,
        'extended_reset_at' => $extendedResetAt,
        'extended_end' => $savedExtendedEnd,
    ], JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}

echo "Ranking reiniciado corretamente e preservado ao estender o período.\n";
