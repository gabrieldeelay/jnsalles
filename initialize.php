<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
if (!defined('BASE_URL'))
    define('BASE_URL', 'https://seusite.com/');
if (!defined('BASE_REF'))
    define('BASE_REF', 'https://seusite.com/');
if (!defined('base_url'))
    define('base_url', 'https://seusite.com/');

if (!defined('base_app'))
    define('base_app', str_replace('\\', '/', __DIR__) . '/');

if (!defined('BASE_APP'))
    define('BASE_APP', str_replace('\\', '/', __DIR__) . '/');
if (!defined('DB_SERVER'))
    define('DB_SERVER', 'localhost');
if (!defined('DB_USERNAME'))
    define('DB_USERNAME', 'USUARIODB'); //colocar seu usuario do banco  de dados aqui
if (!defined('DB_PASSWORD'))
    define('DB_PASSWORD', 'SENHA'); //colocar sua senha do banco de dados aqui
if (!defined('DB_NAME'))
    define('DB_NAME', 'BANCODEDADOS'); // colocar o seu banco de dados aqui
?>