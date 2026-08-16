<?php

if (getenv('JNSALLES_TEST_MODE') !== '1') {
    fwrite(STDERR, "Habilite JNSALLES_TEST_MODE para executar este teste.\n");
    exit(2);
}

$required = ['TEST_DB_HOST', 'TEST_DB_NAME', 'TEST_DB_USER', 'TEST_DB_PASSWORD'];
foreach ($required as $variable) {
    if (getenv($variable) === false) {
        fwrite(STDERR, "Variável ausente: {$variable}\n");
        exit(2);
    }
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
$_SERVER['REQUEST_URI'] = '/class/Main.php';
$_SERVER['HTTP_HOST'] = '127.0.0.1';

$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$connection->set_charset('utf8mb4');
$suffix = bin2hex(random_bytes(5));
$campaignName = 'Pedido manual ' . $suffix;
$slug = 'pedido-manual-' . $suffix;
$product = $connection->prepare("INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, limit_order_remove, cotas_premiadas, cotas_premiadas_premios) VALUES (?, 'Teste do pedido manual', 0.20, 1, 1, 1, 10000, 1, 500, ?, 30, '0001', '0001:Prêmio teste:normal')");
$product->bind_param('ss', $campaignName, $slug);
$product->execute();
$productId = (int) $connection->insert_id;
$product->close();

session_id('manual-order-ranking-' . $suffix);
session_start();
$_SESSION['userdata'] = ['id' => 1, 'firstname' => 'Administrador', 'lastname' => 'Teste', 'type' => 1];
$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
$_GET['action'] = '';
require dirname(__DIR__) . '/class/Main.php';
ob_end_clean();
chdir($originalDirectory);
$previewPost = ['raffle' => $productId, 'quantidade' => 5];
$_POST = $previewPost;
$previewResponse = json_decode($Main->preview_manual_order_numbers(), true);
$previewNumbers = $previewResponse['numbers'] ?? [];
$invalidPreviewPost = [
    'raffle' => $productId,
    'customer_name' => 'Cliente Token Inválido ' . $suffix,
    'quantidade' => 5,
    'status' => 2,
    'preview_token' => ($previewResponse['preview_token'] ?? '') . 'alterado',
];
$_POST = $invalidPreviewPost;
$invalidPreviewResponse = json_decode($Main->create_order(), true);
if (($invalidPreviewResponse['status'] ?? '') !== 'failed') {
    fwrite(STDERR, "Um pedido com prévia adulterada foi aceito.\n");
    exit(1);
}
$_POST = [
    'raffle' => $productId,
    'customer_name' => 'Cliente Manual ' . $suffix,
    'quantidade' => 5,
    'status' => 2,
    'preview_token' => $previewResponse['preview_token'] ?? '',
];
$rawResponse = $Main->create_order();
$response = json_decode($rawResponse, true);

$order = $connection->query(
    "SELECT o.id, o.customer_id, o.quantity, o.total_amount, o.status, o.payment_method, o.order_numbers, " .
    "c.firstname, c.lastname FROM order_list o INNER JOIN customer_list c ON c.id = o.customer_id " .
    "WHERE o.product_id = {$productId} ORDER BY o.id DESC LIMIT 1"
)->fetch_assoc();
$campaign = $connection->query("SELECT paid_numbers, pending_numbers FROM product_list WHERE id = {$productId}")->fetch_assoc();
$generalRanking = $connection->query("SELECT SUM(quantity) total FROM order_list WHERE product_id = {$productId} AND status = 2")->fetch_assoc();
$windowStart = date('Y-m-d H:i:s', time() - 60);
$windowEnd = date('Y-m-d H:i:s', time() + 60);
$daily = $connection->prepare('SELECT SUM(quantity) total FROM order_list WHERE product_id = ? AND status = 2 AND COALESCE(date_updated, date_created) BETWEEN ? AND ?');
$daily->bind_param('iss', $productId, $windowStart, $windowEnd);
$daily->execute();
$dailyRanking = $daily->get_result()->fetch_assoc();
$daily->close();

$numbers = $order ? array_values(array_filter(explode(',', (string) $order['order_numbers']), 'strlen')) : [];
$valid = is_array($response)
    && ($response['status'] ?? '') === 'success'
    && $order
    && (int) $order['quantity'] === 5
    && (int) $order['status'] === 2
    && $order['payment_method'] === 'Manual'
    && abs((float) $order['total_amount'] - 1.00) < 0.001
    && count($numbers) === 5
    && $numbers === $previewNumbers
    && (int) $campaign['paid_numbers'] === 5
    && (int) $campaign['pending_numbers'] === 0
    && (int) $generalRanking['total'] === 5
    && (int) $dailyRanking['total'] === 5;

if ($order) {
    $connection->query('DELETE FROM customer_list WHERE id = ' . (int) $order['customer_id']);
}
$connection->query('DELETE FROM product_list WHERE id = ' . $productId);
$connection->close();

if (!$valid) {
    fwrite(STDERR, "Pedido manual ou ranking inválido. Resposta: {$rawResponse}\n");
    exit(1);
}

echo "Pedido manual criado e contabilizado nos rankings geral e diário.\n";
