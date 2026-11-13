<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$adminSession = $_SESSION['userdata'] ?? null;
$adminAuthorized = is_array($adminSession)
    && (int) ($adminSession['login_type'] ?? 0) === 1
    && (int) ($adminSession['type'] ?? 0) === 1
    && trim((string) ($adminSession['firstname'] ?? '')) !== '';

if (!$adminAuthorized) {
    $target = BASE_URL . 'admin/login.php';
    if (!headers_sent()) {
        header('Location: ' . $target, true, 302);
    } else {
        echo '<script>location.replace(' . json_encode($target) . ');</script>';
    }
    exit;
}

$module = ['', 'admin', ''];

?>
