<?php

if (getenv('JNSALLES_TEST_MODE') !== '1' || getenv('VENOPAG_LIVE_TEST') !== '1') {
    fwrite(STDERR, "Habilite JNSALLES_TEST_MODE e VENOPAG_LIVE_TEST para executar este teste.\n");
    exit(2);
}

$required = ['TEST_DB_HOST', 'TEST_DB_NAME', 'TEST_DB_USER', 'TEST_DB_PASSWORD', 'VENOPAG_CLIENT_ID', 'VENOPAG_CLIENT_SECRET'];
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
$_SERVER['REQUEST_URI'] = '/class/Main.php?action=place_order_process';
$_SERVER['HTTP_HOST'] = '127.0.0.1';

$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$connection->set_charset('utf8mb4');
$connection->query("UPDATE system_info SET meta_value = '2' WHERE meta_field IN ('mercadopago','gerencianet','paggue','openpix','pay2m')");
$connection->query("UPDATE system_info SET meta_value = '1' WHERE meta_field = 'venopag'");
$connection->query("UPDATE system_info SET meta_value = 'venopag' WHERE meta_field = 'gateway_provider'");
$connection->query("UPDATE system_info SET meta_value = '1.00' WHERE meta_field = 'venopag_min_amount'");
$webhookSecret = bin2hex(random_bytes(32));
$webhook = $connection->prepare("INSERT INTO system_info (meta_field, meta_value) VALUES ('venopag_webhook_secret', ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)");
$webhook->bind_param('s', $webhookSecret);
$webhook->execute();
$webhook->close();

$suffix = bin2hex(random_bytes(5));
$phone = '119' . random_int(10000000, 99999999);
$customer = $connection->prepare("INSERT INTO customer_list (firstname, lastname, phone, email, cpf) VALUES ('Teste', 'Checkout', ?, ?, '')");
$email = 'checkout-' . $suffix . '@example.test';
$customer->bind_param('ss', $phone, $email);
$customer->execute();
$customerId = (int) $connection->insert_id;
$customer->close();

$campaignName = 'Checkout VenoPag ' . $suffix;
$slug = 'checkout-venopag-' . $suffix;
$product = $connection->prepare("INSERT INTO product_list (name, description, price, status, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, limit_order_remove) VALUES (?, 'Teste integral do checkout', 0.20, 1, 1, 10000, 1, 500, ?, 30)");
$product->bind_param('ss', $campaignName, $slug);
$product->execute();
$productId = (int) $connection->insert_id;
$product->close();

$cart = $connection->prepare('INSERT INTO cart_list (customer_id, product_id, quantity) VALUES (?, ?, 5)');
$cart->bind_param('ii', $customerId, $productId);
$cart->execute();
$cart->close();

session_id('jnsalles-venopag-checkout-' . $suffix);
session_start();
$_SESSION['userdata'] = [
    'id' => $customerId,
    'firstname' => 'Teste',
    'lastname' => 'Checkout',
    'phone' => $phone,
    'email' => $email,
    'cpf' => '',
    'type' => 2,
];
$_SESSION['ads'] = false;

$_POST = [
    'product_id' => $productId,
    'valorUpsell' => 0,
    'qtdUpsell' => 0,
    'numbers' => [],
    'ref' => '',
];
$_GET['action'] = 'place_order_process';

$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
require dirname(__DIR__) . '/class/Main.php';
$rawResponse = trim((string) ob_get_clean());
chdir($originalDirectory);

$response = json_decode($rawResponse, true);
if (!is_array($response) || ($response['status'] ?? '') !== 'success' || ($response['gateway'] ?? '') !== 'venopag') {
    fwrite(STDERR, "Checkout recusado: {$rawResponse}\n");
    $connection->query('DELETE FROM customer_list WHERE id = ' . $customerId);
    $connection->query('DELETE FROM product_list WHERE id = ' . $productId);
    exit(1);
}

$statement = $connection->prepare("SELECT order_token, payment_method, pix_code, id_mp, status, total_amount FROM order_list WHERE customer_id = ? AND product_id = ? ORDER BY id DESC LIMIT 1");
$statement->bind_param('ii', $customerId, $productId);
$statement->execute();
$order = $statement->get_result()->fetch_assoc();
$statement->close();

$source = file_get_contents(dirname(__DIR__) . '/pages/orders/view_order.php');
$paymentUiReady = strpos($source, 'id="pixCopiaCola"') !== false
    && strpos($source, 'id="payment-confirmed-button"') !== false;
$validOrder = $order
    && $order['payment_method'] === 'VenoPag'
    && (int) $order['status'] === 1
    && abs((float) $order['total_amount'] - 1.00) < 0.001
    && strlen((string) $order['pix_code']) >= 50
    && trim((string) $order['id_mp']) !== ''
    && trim((string) $order['order_token']) !== '';

$connection->query('DELETE FROM customer_list WHERE id = ' . $customerId);
$connection->query('DELETE FROM product_list WHERE id = ' . $productId);
$connection->close();

if (!$validOrder || !$paymentUiReady) {
    fwrite(STDERR, "O pedido ou a área de pagamento ficou incompleto.\n");
    exit(1);
}

echo "Checkout completo: pedido pendente, PIX salvo e área de pagamento disponível.\n";
