<?php

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/settings.php';
require_once __DIR__ . '/data.php';

function jnsalles_draw_response($statusCode, array $payload)
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ((int) $_settings->userdata('type') !== 1) {
    jnsalles_draw_response(403, ['status' => 'error', 'message' => 'Acesso administrativo necessário.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jnsalles_draw_response(405, ['status' => 'error', 'message' => 'Método não permitido.']);
}
$csrf = (string) ($_POST['csrf'] ?? '');
if (empty($_SESSION['draw_csrf']) || !hash_equals((string) $_SESSION['draw_csrf'], $csrf)) {
    jnsalles_draw_response(419, ['status' => 'error', 'message' => 'A sessão expirou. Atualize a página e tente novamente.']);
}
$action = (string) ($_POST['action'] ?? 'draw');
$productId = isset($_POST['product_id']) && ctype_digit((string) $_POST['product_id'])
    ? (int) $_POST['product_id']
    : 0;
if ($productId <= 0) {
    jnsalles_draw_response(422, ['status' => 'error', 'message' => 'Selecione uma campanha válida.']);
}

$productStatement = $conn->prepare('SELECT id, name, qty_numbers FROM product_list WHERE id = ? AND delete_flag = 0 LIMIT 1');
$productStatement->bind_param('i', $productId);
$productStatement->execute();
$product = $productStatement->get_result()->fetch_assoc();
$productStatement->close();
if (!$product) {
    jnsalles_draw_response(404, ['status' => 'error', 'message' => 'Campanha não encontrada.']);
}

if ($action === 'simulation_preview') {
    $freeNumber = jnsalles_draw_free_demo_number($conn, $productId, (int) $product['qty_numbers']);
    if ($freeNumber === null) {
        jnsalles_draw_response(422, ['status' => 'error', 'message' => 'Não foi possível localizar uma cota livre para a demonstração.']);
    }
    jnsalles_draw_response(200, [
        'status' => 'success',
        'preview' => [
            'number' => $freeNumber,
            'phone' => jnsalles_draw_demo_phone(),
        ],
    ]);
}
if ($action !== 'draw') {
    jnsalles_draw_response(422, ['status' => 'error', 'message' => 'Ação de sorteio inválida.']);
}
if (!jnsalles_draw_ensure_schema($conn)) {
    jnsalles_draw_response(500, ['status' => 'error', 'message' => 'Não foi possível preparar o histórico de sorteios.']);
}

$lockName = 'jnsalles_draw_' . $productId;
$lockStatement = $conn->prepare('SELECT GET_LOCK(?, 5) AS acquired');
$lockStatement->bind_param('s', $lockName);
$lockStatement->execute();
$lock = $lockStatement->get_result()->fetch_assoc();
$lockStatement->close();
if ((int) ($lock['acquired'] ?? 0) !== 1) {
    jnsalles_draw_response(409, ['status' => 'error', 'message' => 'Outro sorteio desta campanha está em andamento. Aguarde alguns segundos.']);
}

$transactionStarted = false;
$responseStatus = 200;
$responsePayload = [];
try {
    $drawnNumbers = [];
    $drawnStatement = $conn->prepare('SELECT winning_number FROM raffle_draws WHERE product_id = ?');
    $drawnStatement->bind_param('i', $productId);
    $drawnStatement->execute();
    $drawnResult = $drawnStatement->get_result();
    while ($drawn = $drawnResult->fetch_assoc()) {
        $drawnNumbers[(string) $drawn['winning_number']] = true;
    }
    $drawnStatement->close();

    $candidateStatement = $conn->prepare(
        "SELECT o.id AS order_id, o.customer_id, o.order_numbers,
                TRIM(CONCAT(COALESCE(c.firstname, ''), ' ', COALESCE(c.lastname, ''))) AS winner_name,
                c.phone
         FROM order_list o
         INNER JOIN customer_list c ON c.id = o.customer_id
         WHERE o.product_id = ? AND o.status = 2
           AND o.order_numbers IS NOT NULL AND TRIM(o.order_numbers) <> ''
         ORDER BY o.id ASC"
    );
    $candidateStatement->bind_param('i', $productId);
    $candidateStatement->execute();
    $candidates = $candidateStatement->get_result();

    $eligibleEntries = 0;
    while ($candidate = $candidates->fetch_assoc()) {
        foreach (jnsalles_draw_numbers($candidate['order_numbers']) as $number) {
            if (!isset($drawnNumbers[$number])) {
                $eligibleEntries++;
            }
        }
    }
    if ($eligibleEntries < 1) {
        $candidateStatement->close();
        throw new RuntimeException('Não existem cotas pagas disponíveis para um novo sorteio nesta campanha.');
    }

    $randomPosition = random_int(1, $eligibleEntries);
    $position = 0;
    $winner = null;
    $candidates->data_seek(0);
    while ($candidate = $candidates->fetch_assoc()) {
        foreach (jnsalles_draw_numbers($candidate['order_numbers']) as $number) {
            if (isset($drawnNumbers[$number])) {
                continue;
            }
            $position++;
            if ($position === $randomPosition) {
                $winner = $candidate;
                $winner['winning_number'] = $number;
                break 2;
            }
        }
    }
    $candidateStatement->close();
    if (!$winner) {
        throw new RuntimeException('Não foi possível localizar a cota sorteada. Tente novamente.');
    }

    $winnerName = trim((string) $winner['winner_name']);
    if ($winnerName === '') {
        $winnerName = 'Cliente #' . (int) $winner['customer_id'];
    }
    $maskedPhone = jnsalles_draw_mask_phone($winner['phone']);
    $drawnBy = (int) $_settings->userdata('id');
    $auditHash = hash('sha256', implode('|', [
        $productId,
        (int) $winner['order_id'],
        $winner['winning_number'],
        $eligibleEntries,
        $randomPosition,
        microtime(true),
        bin2hex(random_bytes(16)),
    ]));

    $conn->begin_transaction();
    $transactionStarted = true;
    $insert = $conn->prepare(
        'INSERT INTO raffle_draws '
        . '(product_id, product_name_snapshot, order_id, customer_id, winning_number, winner_name_snapshot, '
        . 'phone_masked_snapshot, eligible_entries, random_position, audit_hash, drawn_by) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $productName = (string) $product['name'];
    $orderId = (int) $winner['order_id'];
    $customerId = (int) $winner['customer_id'];
    $winningNumber = (string) $winner['winning_number'];
    $insert->bind_param(
        'isiisssiisi',
        $productId,
        $productName,
        $orderId,
        $customerId,
        $winningNumber,
        $winnerName,
        $maskedPhone,
        $eligibleEntries,
        $randomPosition,
        $auditHash,
        $drawnBy
    );
    $saved = $insert->execute();
    $drawId = (int) $insert->insert_id;
    $insert->close();
    if (!$saved) {
        throw new RuntimeException('Não foi possível registrar o resultado do sorteio.');
    }
    $conn->commit();
    $transactionStarted = false;

    $responsePayload = [
        'status' => 'success',
        'draw' => [
            'id' => $drawId,
            'campaign' => $productName,
            'winner' => $winnerName,
            'phone' => $maskedPhone,
            'number' => $winningNumber,
            'eligible_entries' => $eligibleEntries,
            'audit_hash' => $auditHash,
            'date' => date('d/m/Y H:i:s'),
        ],
    ];
} catch (Throwable $error) {
    if ($transactionStarted) {
        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
        }
    }
    $message = $error instanceof RuntimeException
        ? $error->getMessage()
        : 'O servidor não conseguiu concluir o sorteio. Tente novamente.';
    $responseStatus = $error instanceof RuntimeException ? 422 : 500;
    $responsePayload = ['status' => 'error', 'message' => $message];
} finally {
    $releaseStatement = $conn->prepare('SELECT RELEASE_LOCK(?)');
    if ($releaseStatement) {
        $releaseStatement->bind_param('s', $lockName);
        $releaseStatement->execute();
        $releaseStatement->close();
    }
}
jnsalles_draw_response($responseStatus, $responsePayload);
