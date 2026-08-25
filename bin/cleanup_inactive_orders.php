<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $root;
require_once $root . '/settings.php';

$result = payment_cleanup_inactive_orders(250, 60);
fwrite(STDOUT, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
exit(!empty($result['ok']) ? 0 : 1);
