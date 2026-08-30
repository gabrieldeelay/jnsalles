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

$target = (string) (getenv('TEST_PUBLIC_PAGE') ?: 'home');
$suffix = bin2hex(random_bytes(5));
$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$connection->set_charset('utf8mb4');
$campaignName = 'Campanha pública ' . $suffix;
$slug = 'campanha-publica-' . $suffix;
$statement = $connection->prepare(
    'INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, cotas_premiadas, cotas_premiadas_premios, enable_ranking, enable_ranking_show, ranking_qty, ranking_type) VALUES (?, ?, 0.20, 1, 1, 1, 1000, 1, 100, ?, ?, ?, 1, 0, 5, 2)'
);
$description = 'Descrição da campanha de teste';
$winningNumbers = '0001,0002';
$winningPrizes = '0001:PIX de R$ 10:premiada,0002:PIX de R$ 20:premiada';
$statement->bind_param('sssss', $campaignName, $description, $slug, $winningNumbers, $winningPrizes);
$statement->execute();
$productId = (int) $connection->insert_id;
$statement->close();

$winnerPhone = '119' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
$connection->query("INSERT INTO customer_list (firstname, lastname, phone) VALUES ('Comprador', '{$suffix}', '{$winnerPhone}')");
$customerId = (int) $connection->insert_id;
$connection->query("INSERT INTO order_list (code, customer_id, quantity, total_amount, status, product_id, payment_method, order_token, order_numbers) VALUES ('PUB{$suffix}', {$customerId}, 7, 1.40, 2, {$productId}, 'Manual', 'PUBTOKEN{$suffix}', '0001,0003,0004,0005,0006,0007,0008')");
$finalCampaignName = 'Campanha finalizada ' . $suffix;
$finalSlug = 'campanha-finalizada-' . $suffix;
$drawNumberJson = $connection->real_escape_string(json_encode(['0042']));
$drawWinnerJson = $connection->real_escape_string(json_encode([$winnerPhone]));
$connection->query("INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, featured_draw, private_draw, draw_number, draw_winner, date_of_draw, cotas_premiadas, cotas_premiadas_premios) VALUES ('{$finalCampaignName}', 'Finalizada', 0.20, 3, 4, 1, 1000, 1, 100, '{$finalSlug}', 1, 0, '{$drawNumberJson}', '{$drawWinnerJson}', '" . date('Y-m-d H:i:s') . "', '0001', '0001:PIX:premiada')");
$finalProductId = (int) $connection->insert_id;

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = $target === 'product' ? '/campanha/' . $slug : '/';
$_GET = $target === 'product' ? ['p' => 'pages/products/view_product', 'id' => $slug] : [];

$originalDirectory = getcwd();
chdir(dirname(__DIR__));
ob_start();
require dirname(__DIR__) . '/index.php';
$html = (string) ob_get_clean();
chdir($originalDirectory);

$valid = $html !== '' && !str_contains($html, 'Fatal error');
if ($target === 'home') {
	$valid = $valid
		&& str_contains($html, 'home-campaign-card')
		&& str_contains($html, $campaignName)
		&& str_contains($html, $finalCampaignName)
		&& str_contains($html, 'Finalizada')
		&& str_contains($html, 'Comprador ' . $suffix)
		&& str_contains($html, 'margin-top:auto')
        && str_contains($html, 'Desenvolvido por')
        && !str_contains($html, '/contato.php?site=');
} elseif ($target === 'product') {
    $purchasePosition = strpos($html, 'Quero participar');
    $winningTicketsPosition = strpos($html, 'id="cotas-container"');
    $valid = $valid
        && str_contains($html, 'ranking-buyer-total')
        && str_contains($html, '7 cotas')
        && str_contains($html, 'Cotas premiadas')
        && substr_count($html, 'id="cotas-container"') === 1
        && $purchasePosition !== false
        && $winningTicketsPosition !== false
        && $winningTicketsPosition > $purchasePosition;
} else {
    $valid = false;
}

$connection->query('DELETE FROM order_list WHERE product_id = ' . $productId);
$connection->query('DELETE FROM customer_list WHERE id = ' . $customerId);
$connection->query('DELETE FROM product_list WHERE id = ' . $finalProductId);
$connection->query('DELETE FROM product_list WHERE id = ' . $productId);
$connection->close();

if (!$valid) {
    fwrite(STDERR, "Falha ao renderizar a página pública {$target}.\n");
    exit(1);
}

echo "Página pública renderizada: {$target}.\n";
