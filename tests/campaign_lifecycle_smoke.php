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
$connection->query("INSERT INTO product_list (name, description, price, status, status_display, type_of_draw, qty_numbers, min_purchase, max_purchase, slug, cotas_premiadas, cotas_premiadas_premios) VALUES ('Ciclo {$suffix}', 'Teste', 0.20, 1, 1, 1, 1000, 1, 100, 'ciclo-{$suffix}', '0001', '0001:PIX:premiada')");
$productId = (int) $connection->insert_id;

session_id('campaign-lifecycle-' . $suffix);
session_start();
$_SESSION['userdata'] = ['id' => 1, 'firstname' => 'Administrador', 'lastname' => 'Teste', 'type' => 1, 'login_type' => 1];
$originalDirectory = getcwd();
chdir(dirname(__DIR__) . '/class');
ob_start();
require dirname(__DIR__) . '/class/Main.php';
ob_end_clean();
chdir($originalDirectory);

$_POST = ['id' => $productId, 'lifecycle_action' => 'finalize'];
$finalizeResponse = json_decode($Main->update_product_lifecycle(), true);
$finalized = $connection->query('SELECT status, status_display FROM product_list WHERE id = ' . $productId)->fetch_assoc();

$_POST = ['id' => $productId, 'draw_number' => '0042', 'draw_winner' => '11987654321'];
$winnerResponse = json_decode($Main->save_raffle_winner(), true);
$withWinner = $connection->query('SELECT status, status_display, draw_number, draw_winner FROM product_list WHERE id = ' . $productId)->fetch_assoc();

$_POST = ['id' => $productId, 'lifecycle_action' => 'reactivate'];
$reactivateResponse = json_decode($Main->update_product_lifecycle(), true);
$reactivated = $connection->query('SELECT status, status_display, draw_number, draw_winner FROM product_list WHERE id = ' . $productId)->fetch_assoc();

$connection->query('DELETE FROM product_list WHERE id = ' . $productId);
$connection->close();

$valid = ($finalizeResponse['status'] ?? '') === 'success'
    && (int) ($finalized['status'] ?? 0) === 3
    && (int) ($finalized['status_display'] ?? 0) === 4
    && ($winnerResponse['status'] ?? '') === 'success'
    && (int) ($withWinner['status'] ?? 0) === 3
    && (int) ($withWinner['status_display'] ?? 0) === 4
    && json_decode((string) ($withWinner['draw_number'] ?? ''), true) === ['0042']
    && json_decode((string) ($withWinner['draw_winner'] ?? ''), true) === ['11987654321']
    && ($reactivateResponse['status'] ?? '') === 'success'
    && (int) ($reactivated['status'] ?? 0) === 1
    && (int) ($reactivated['status_display'] ?? 0) === 1
    && (string) ($reactivated['draw_number'] ?? '') === (string) ($withWinner['draw_number'] ?? '')
    && (string) ($reactivated['draw_winner'] ?? '') === (string) ($withWinner['draw_winner'] ?? '');

if (!$valid) {
    fwrite(STDERR, 'Ciclo da campanha inválido: ' . json_encode([
        'finalize' => $finalizeResponse,
        'finalized' => $finalized,
        'winner' => $winnerResponse,
        'with_winner' => $withWinner,
        'reactivate' => $reactivateResponse,
        'reactivated' => $reactivated,
    ], JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}

echo "Campanha finalizada, ganhador preservado e reativação concluída.\n";
