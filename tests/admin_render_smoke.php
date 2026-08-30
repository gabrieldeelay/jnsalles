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

$renderConnection = null;
$renderProductId = 0;
if ($requestedPage === 'products') {
    $renderConnection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $renderConnection->set_charset('utf8mb4');
    $renderSuffix = bin2hex(random_bytes(4));
    $renderConnection->query("INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, cotas_premiadas, cotas_premiadas_premios) VALUES ('Campanha render {$renderSuffix}', 'Teste', 0.20, 1, 1, 1, 1000, 1, 100, 'campanha-render-{$renderSuffix}', '0001', '0001:PIX:premiada')");
    $renderProductId = (int) $renderConnection->insert_id;
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
if ($renderProductId > 0) {
    $renderConnection->query('DELETE FROM product_list WHERE id = ' . $renderProductId);
    $renderConnection->close();
}

if ($html === '' || !str_contains($html, $expectedByPage[$requestedPage]) || str_contains($html, 'Fatal error')) {
    fwrite(STDERR, "Falha ao renderizar {$requestedPage}.\n");
    exit(1);
}
if (!str_contains($html, 'admin-context-guide-template')) {
    fwrite(STDERR, "A ajuda contextual não foi carregada em {$requestedPage}.\n");
    exit(1);
}
if ($requestedPage === 'products' && (!str_contains($html, 'campaign-delete-button')
    || !str_contains($html, 'confirmCampaignDeletion')
    || !str_contains($html, 'campaign-status-button')
    || !str_contains($html, 'toggleCampaignLifecycle')
    || !str_contains($html, 'update_product_lifecycle'))) {
	fwrite(STDERR, "As ações visuais da campanha não foram renderizadas.\n");
    exit(1);
}
if ($requestedPage === 'products') {
    preg_match('/<div class="campaign-actions text-sm">(.*?)<\/div>/s', $html, $campaignActionsMatch);
    $campaignActions = $campaignActionsMatch[1] ?? '';
    if ($campaignActions === ''
        || str_contains($campaignActions, 'class="stock"')
        || str_contains($campaignActions, 'fa-duotone')
        || !str_contains($campaignActions, 'aria-label="Compartilhar campanha"')
        || !str_contains($campaignActions, 'aria-label="Duplicar campanha"')) {
        fwrite(STDERR, "As ações de campanha ainda contêm botões vazios ou ícones externos.\n");
        exit(1);
    }
}
if ($requestedPage === 'home' && (!str_contains($html, 'dashboard-prize-table-shell') || !str_contains($html, 'Selecione uma campanha para consultar as cotas premiadas.'))) {
    fwrite(STDERR, "O card de cotas premiadas não foi renderizado com o novo acabamento.\n");
    exit(1);
}
if ($requestedPage === 'products/manage_product') {
    preg_match('/id="winning-ticket-rows">(.*?)<div class="winning-ticket-actions">/s', $html, $winningRowsMatch);
    if (empty($winningRowsMatch[1]) || substr_count($winningRowsMatch[1], 'class="winning-ticket-row"') !== 10) {
        fwrite(STDERR, "A nova campanha não abriu com 10 cotas premiadas.\n");
        exit(1);
    }
    if (!str_contains($html, 'id="generate-winning-tickets"') || !str_contains($html, 'id="winning-ticket-generate-count"')
        || !str_contains($html, 'validateWinningEditor')) {
        fwrite(STDERR, "O gerador de cotas premiadas não foi carregado.\n");
        exit(1);
    }
    if (!str_contains($html, 'type="submit" id="save-product-button"')
        || substr_count($html, "typeof $.fn.mask === 'function'") < 2) {
        fwrite(STDERR, "O botão de salvar ainda depende de um recurso externo.\n");
        exit(1);
    }
    if (!str_contains($html, 'data-native-campaign-save')
        || !str_contains($html, "button.addEventListener('click'")
        || !str_contains($html, "form.addEventListener('submit'")
        || !str_contains($html, "localStorage.removeItem('selectedTab_manage_product')")
        || !str_contains($html, 'Validando os dados da campanha...')) {
        fwrite(STDERR, "O salvamento nativo ou a aba Dados padrão não foram carregados.\n");
        exit(1);
    }
}

echo "Página administrativa renderizada: {$requestedPage}.\n";
