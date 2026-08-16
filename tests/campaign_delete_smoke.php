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
$_SERVER['REQUEST_URI'] = '/class/Main.php';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_GET['action'] = '';

$suffix = bin2hex(random_bytes(5));
$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$connection->set_charset('utf8mb4');
$connection->query("INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, cotas_premiadas, cotas_premiadas_premios) VALUES ('Excluir {$suffix}', 'Teste', 0.20, 1, 1, 1, 1000, 1, 100, 'excluir-{$suffix}', '0001', '0001:PIX:premiada')");
$productId = (int) $connection->insert_id;
$connection->query("INSERT INTO customer_list (firstname, lastname, phone) VALUES ('Cliente', '{$suffix}', '')");
$customerId = (int) $connection->insert_id;
$connection->query("INSERT INTO order_list (code, customer_id, quantity, total_amount, status, product_id, product_name, payment_method, order_token) VALUES ('DEL{$suffix}', {$customerId}, 1, 0.20, 2, {$productId}, 'Excluir {$suffix}', 'Manual', 'DELTOKEN{$suffix}')");
$orderId = (int) $connection->insert_id;

session_id('campaign-delete-' . $suffix);
session_start();
$_SESSION['userdata'] = ['id' => 1, 'firstname' => 'Administrador', 'lastname' => 'Teste', 'type' => 1, 'login_type' => 1];
$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
require dirname(__DIR__) . '/class/Main.php';
ob_end_clean();
chdir($originalDirectory);

$_POST = ['id' => $productId];
$response = json_decode($Main->delete_product(), true);
$productCount = (int) $connection->query('SELECT COUNT(*) AS total FROM product_list WHERE id = ' . $productId)->fetch_assoc()['total'];
$orderCount = (int) $connection->query('SELECT COUNT(*) AS total FROM order_list WHERE id = ' . $orderId)->fetch_assoc()['total'];

$connection->query('DELETE FROM order_list WHERE id = ' . $orderId);
$connection->query('DELETE FROM customer_list WHERE id = ' . $customerId);
$connection->close();

if (($response['status'] ?? '') !== 'success' || $productCount !== 0 || $orderCount !== 1) {
    fwrite(STDERR, 'Exclusão de campanha inválida: ' . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}

echo "Campanha excluída e histórico de pedidos preservado.\n";
