<?php

if (getenv('JNSALLES_TEST_MODE') !== '1') {
    fwrite(STDERR, "Habilite JNSALLES_TEST_MODE para executar este teste.\n");
    exit(2);
}

class DBConnection
{
    public function __construct() {}
    public function __destruct() {}
}

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jnsalles-image-' . bin2hex(random_bytes(5)) . DIRECTORY_SEPARATOR;
if (!mkdir($testRoot, 0755, true) && !is_dir($testRoot)) {
    fwrite(STDERR, "Não foi possível criar o diretório temporário.\n");
    exit(1);
}

define('BASE_APP', $testRoot);
require dirname(__DIR__) . '/classes/Master.php';

$source = $testRoot . 'source.png';
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAABCAYAAAD0In+KAAAAFElEQVR42mP8z8Dwn4GBgYGJAQoAHgQCAZ9Z/1QAAAAASUVORK5CYII=');
file_put_contents($source, $png);

$reflection = new ReflectionClass(Master::class);
$master = $reflection->newInstanceWithoutConstructor();
$method = $reflection->getMethod('store_campaign_image');
$method->setAccessible(true);
$relativePath = $method->invoke($master, $source, 'Arte Original.png', 'uploads/campanhas');
$savedPath = $relativePath ? BASE_APP . $relativePath : '';

$valid = $relativePath
    && is_file($savedPath)
    && pathinfo($savedPath, PATHINFO_EXTENSION) === 'png'
    && hash_file('sha256', $source) === hash_file('sha256', $savedPath)
    && getimagesize($savedPath)[0] === 2
    && getimagesize($savedPath)[1] === 1;

if (is_file($savedPath)) {
    unlink($savedPath);
}
if (is_file($source)) {
    unlink($source);
}
@rmdir($testRoot . 'uploads' . DIRECTORY_SEPARATOR . 'campanhas');
@rmdir($testRoot . 'uploads');
@rmdir($testRoot);

if (!$valid) {
    fwrite(STDERR, "A imagem não foi preservada integralmente.\n");
    exit(1);
}

echo "Imagem de campanha preservada sem recorte ou recompressão.\n";
