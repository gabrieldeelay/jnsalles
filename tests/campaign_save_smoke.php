<?php

if (getenv('JNSALLES_TEST_MODE') !== '1') {
    fwrite(STDERR, "Defina JNSALLES_TEST_MODE=1 para executar este teste.\n");
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
    fwrite(STDERR, "O banco de teste deve terminar com _test.\n");
    exit(2);
}

define('DB_HOST', (string) getenv('TEST_DB_HOST'));
define('DB_NAME', $databaseName);
define('DB_USER', (string) getenv('TEST_DB_USER'));
define('DB_PASSWORD', (string) getenv('TEST_DB_PASSWORD'));

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
$_SERVER['REQUEST_URI'] = '/class/Main.php?action=save_product_sys';
$_SERVER['HTTP_HOST'] = '127.0.0.1';

session_id('jnsalles-campaign-smoke-' . bin2hex(random_bytes(4)));
session_start();
$_SESSION['userdata'] = [
    'id' => 1,
    'firstname' => 'Teste',
    'lastname' => 'Automatizado',
    'type' => 1,
];

$campaignName = 'Campanha Smoke ' . bin2hex(random_bytes(5));
$_POST = [
    'name' => $campaignName,
    'description' => 'Teste automatizado de criação de campanha.',
    'type_of_draw' => '1',
    'qty_numbers' => '1000',
    'price' => '0,20',
    'limit_orders' => '',
    'min_purchase' => '1',
    'max_purchase' => '500',
    'status' => '1',
    'date_of_draw' => '',
    'probabilidade' => '',
];
$_GET['action'] = 'save_product_sys';

$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
require dirname(__DIR__) . '/class/Main.php';
$rawResponse = trim((string) ob_get_clean());
chdir($originalDirectory);

$response = json_decode($rawResponse, true);
if (!is_array($response) || ($response['status'] ?? '') !== 'success') {
    fwrite(STDERR, "Falha ao criar campanha: {$rawResponse}\n");
    exit(1);
}

$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$statement = $connection->prepare('SELECT id, name, qty_numbers, price FROM product_list WHERE id = ?');
$productId = (int) $response['pid'];
$statement->bind_param('i', $productId);
$statement->execute();
$campaign = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$campaign || $campaign['name'] !== $campaignName || (int) $campaign['qty_numbers'] !== 1000 || (float) $campaign['price'] !== 0.20) {
    fwrite(STDERR, "A campanha foi gravada com dados divergentes.\n");
    exit(1);
}

$cleanup = $connection->prepare('DELETE FROM product_list WHERE id = ?');
$cleanup->bind_param('i', $productId);
$cleanup->execute();
$cleanup->close();
$connection->close();

echo "Campanha criada, validada e removida com sucesso (ID {$productId}).\n";
