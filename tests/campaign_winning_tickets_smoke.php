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
session_id('campaign-winning-' . $suffix);
session_start();
$_SESSION['userdata'] = ['id' => 1, 'firstname' => 'Administrador', 'lastname' => 'Teste', 'type' => 1];

$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
require dirname(__DIR__) . '/class/Main.php';
ob_end_clean();
chdir($originalDirectory);

$_POST = [
    'name' => 'Campanha sem premiadas ' . $suffix,
    'description' => 'Validação obrigatória',
    'type_of_draw' => '1',
    'qty_numbers' => '1000',
    'price' => '0,20',
];
$defaultResponse = json_decode($Main->save_product(), true);
$defaultProductId = (int) ($defaultResponse['pid'] ?? 0);
$defaultCampaign = null;
$defaultConnection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($defaultProductId > 0) {
    $defaultCampaign = $defaultConnection->query("SELECT cotas_premiadas, cotas_premiadas_premios FROM product_list WHERE id = {$defaultProductId}")->fetch_assoc();
}
if (($defaultResponse['status'] ?? '') !== 'success' || !$defaultCampaign
    || count(explode(',', (string) $defaultCampaign['cotas_premiadas'])) !== 10
    || count(explode(',', (string) $defaultCampaign['cotas_premiadas_premios'])) !== 10) {
    fwrite(STDERR, "A campanha nova não recebeu as 10 cotas premiadas padrão.\n");
    exit(1);
}
if ($defaultProductId > 0) {
    $defaultConnection->query('DELETE FROM product_list WHERE id = ' . $defaultProductId);
}
$defaultConnection->close();

$_POST = [
    'name' => 'Campanha premiada ' . $suffix,
    'description' => 'Campanha criada no teste automatizado',
    'type_of_draw' => '1',
    'qty_numbers' => '1000',
    'price' => '0,20',
    'status' => '1',
    'cotas_premiadas' => '0001,0042',
    'cotas_premiadas_premios' => '0001:PIX de R$ 10:normal,0042:PIX de R$ 20:normal',
];
$validResponse = json_decode($Main->save_product(), true);
$productId = (int) ($validResponse['pid'] ?? 0);

$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$connection->set_charset('utf8mb4');
$campaign = null;
if ($productId > 0) {
    $campaign = $connection->query(
        "SELECT cotas_premiadas, cotas_premiadas_premios, cotas_premiadas_descricao FROM product_list WHERE id = {$productId}"
    )->fetch_assoc();
}

$valid = ($validResponse['status'] ?? '') === 'success'
    && $campaign
    && $campaign['cotas_premiadas'] === '0001,0042'
    && str_contains((string) $campaign['cotas_premiadas_premios'], '0001:PIX de R$ 10')
    && trim((string) $campaign['cotas_premiadas_descricao']) !== '';

if ($productId > 0) {
    $connection->query('DELETE FROM product_list WHERE id = ' . $productId);
}
$connection->close();

if (!$valid) {
    fwrite(STDERR, 'A campanha válida não foi criada: ' . json_encode($validResponse, JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}

echo "Campanha criada com cotas premiadas obrigatórias.\n";
