<?php

require __DIR__ . '/settings.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$expectedKey = '7f3c8a91d2e64b50a746e0f44c7c2961';
if (!hash_equals($expectedKey, (string) ($_GET['key'] ?? ''))) {
    http_response_code(404);
    exit;
}

$where = "status IN (1, 3) AND COALESCE(payment_method, '') = '' "
    . "AND COALESCE(pix_code, '') = '' AND COALESCE(id_mp, '') = '' "
    . "AND date_created < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
$countResult = $conn->query('SELECT COUNT(*) AS total FROM order_list WHERE ' . $where);
$before = $countResult ? (int) $countResult->fetch_assoc()['total'] : -1;

$mode = (string) ($_GET['mode'] ?? 'count');
if ($mode !== 'delete') {
    echo json_encode(['ok' => $before >= 0, 'eligible' => $before], JSON_UNESCAPED_UNICODE);
    exit;
}

$deleted = 0;
do {
    $cleanup = payment_cleanup_empty_failed_attempts(null, 250);
    if (empty($cleanup['ok'])) {
        echo json_encode(['ok' => false, 'eligible_before' => $before, 'deleted' => $deleted], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $batch = (int) ($cleanup['deleted'] ?? 0);
    $deleted += $batch;
} while ($batch === 250);

$countResult = $conn->query('SELECT COUNT(*) AS total FROM order_list WHERE ' . $where);
$after = $countResult ? (int) $countResult->fetch_assoc()['total'] : -1;
echo json_encode([
    'ok' => $after === 0,
    'eligible_before' => $before,
    'deleted' => $deleted,
    'eligible_after' => $after,
], JSON_UNESCAPED_UNICODE);
