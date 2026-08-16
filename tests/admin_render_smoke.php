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

$requestedPage = (string) (getenv('TEST_ADMIN_PAGE') ?: 'home');
$expectedByPage = [
    'home' => 'Central de Comando',
    'products' => 'Sorteios',
    'products/manage_product' => 'winning-ticket-editor',
    'orders' => 'Pedidos',
    'orders/create_order' => 'preview_token',
    'report' => 'Relatórios',
    'ranking' => 'Top compradores',
    'customers' => 'Clientes',
    'affiliates' => 'Afiliados',
    'gateway' => 'Gateway de pagamento',
    'system_info' => 'Configuração',
    'admin_accounts' => 'Administradores',
];
if (!isset($expectedByPage[$requestedPage])) {
    fwrite(STDERR, "Página de teste desconhecida: {$requestedPage}.\n");
    exit(2);
}

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
$_SERVER['REQUEST_URI'] = '/admin/?page=' . rawurlencode($requestedPage);
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_GET['page'] = $requestedPage;
session_id('admin-render-' . preg_replace('/[^a-z0-9]/i', '-', $requestedPage) . '-' . bin2hex(random_bytes(3)));
session_start();
$_SESSION['userdata'] = [
    'id' => 1,
    'firstname' => 'Administrador',
    'lastname' => 'Teste',
    'username' => 'render.test',
    'date_added' => date('Y-m-d H:i:s'),
    'type' => 1,
    'login_type' => 1,
];

$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/admin');
ob_start();
require dirname(__DIR__) . '/admin/index.php';
$html = (string) ob_get_clean();
chdir($originalDirectory);

if ($html === '' || !str_contains($html, $expectedByPage[$requestedPage]) || str_contains($html, 'Fatal error')) {
    fwrite(STDERR, "Falha ao renderizar {$requestedPage}.\n");
    exit(1);
}
if (!str_contains($html, 'admin-context-guide-template')) {
    fwrite(STDERR, "A ajuda contextual não foi carregada em {$requestedPage}.\n");
    exit(1);
}
if ($requestedPage === 'products/manage_product') {
    preg_match('/id="winning-ticket-rows">(.*?)<div class="winning-ticket-actions">/s', $html, $winningRowsMatch);
    if (empty($winningRowsMatch[1]) || substr_count($winningRowsMatch[1], 'class="winning-ticket-row"') !== 10) {
        fwrite(STDERR, "A nova campanha não abriu com 10 cotas premiadas.\n");
        exit(1);
    }
}

echo "Página administrativa renderizada: {$requestedPage}.\n";
