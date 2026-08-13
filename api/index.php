<?php

/**
 * Compatibility entrypoint for Vercel.
 *
 * The original application files remain untouched. This file supplies the
 * environment-aware configuration and Apache-style routing they expect.
 */

$appRoot = realpath(dirname(__DIR__));

if ($appRoot === false) {
    http_response_code(500);
    exit('Application root not found.');
}

function env_value(array $names, $default = null)
{
    foreach ($names as $name) {
        $value = getenv($name);

        if ($value !== false && $value !== '') {
            return $value;
        }
    }

    return $default;
}

function stop_request($status, $message = '')
{
    http_response_code($status);

    if ($message !== '') {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

    exit;
}

$forwardedProto = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
    ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0])
    : null;
$scheme = $forwardedProto ?: ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$host = isset($_SERVER['HTTP_X_FORWARDED_HOST'])
    ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0])
    : ($_SERVER['HTTP_HOST'] ?? 'localhost');
$detectedUrl = $scheme . '://' . $host . '/';
$applicationUrl = rtrim(env_value(['APP_URL', 'BASE_URL'], $detectedUrl), '/') . '/';

if (!defined('BASE_URL')) {
    define('BASE_URL', $applicationUrl);
}

if (!defined('BASE_REF')) {
    define('BASE_REF', $applicationUrl);
}

if (!defined('base_url')) {
    define('base_url', $applicationUrl);
}

$databaseHost = env_value(['DB_SERVER', 'DB_HOST', 'MYSQL_HOST']);
$databaseUser = env_value(['DB_USERNAME', 'DB_USER', 'MYSQL_USER']);
$databasePassword = env_value(['DB_PASSWORD', 'MYSQL_PASSWORD'], '');
$databaseName = env_value(['DB_NAME', 'MYSQL_DATABASE']);

if ($databaseHost !== null && !defined('DB_SERVER')) {
    define('DB_SERVER', $databaseHost);
}

if ($databaseUser !== null && !defined('DB_USERNAME')) {
    define('DB_USERNAME', $databaseUser);
}

if (!defined('DB_PASSWORD') && $databaseHost !== null && $databaseUser !== null) {
    define('DB_PASSWORD', $databasePassword);
}

if ($databaseName !== null && !defined('DB_NAME')) {
    define('DB_NAME', $databaseName);
}

/* Keep mutable application files inside the serverless runtime. */
$runtimeRoot = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'bigrifa';
$writableAppRoot = $runtimeRoot . DIRECTORY_SEPARATOR . 'app';

function copy_directory_once($source, $destination)
{
    if (!is_dir($source)) {
        return;
    }

    if (!is_dir($destination)) {
        @mkdir($destination, 0700, true);
    }

    $items = new FilesystemIterator($source, FilesystemIterator::SKIP_DOTS);

    foreach ($items as $item) {
        $destinationPath = $destination . DIRECTORY_SEPARATOR . $item->getFilename();

        if ($item->isDir()) {
            copy_directory_once($item->getPathname(), $destinationPath);
        } elseif (!is_file($destinationPath)) {
            @copy($item->getPathname(), $destinationPath);
        }
    }
}

if (!is_dir($writableAppRoot)) {
    @mkdir($writableAppRoot, 0700, true);
    copy_directory_once($appRoot . DIRECTORY_SEPARATOR . 'uploads', $writableAppRoot . DIRECTORY_SEPARATOR . 'uploads');
}

if (!is_file($writableAppRoot . DIRECTORY_SEPARATOR . 'pedido.lock')) {
    @touch($writableAppRoot . DIRECTORY_SEPARATOR . 'pedido.lock');
}

if (!defined('BASE_APP')) {
    define('BASE_APP', str_replace('\\', '/', $writableAppRoot) . '/');
}

if (!defined('base_app')) {
    define('base_app', str_replace('\\', '/', $writableAppRoot) . '/');
}

$_SERVER['DOCUMENT_ROOT'] = $writableAppRoot;

/*
 * PHP's default file sessions are not reliable across serverless instances.
 * When database variables are present, keep sessions in the same MySQL
 * database used by the application, without changing the original code.
 */
if (
    class_exists('mysqli')
    && $databaseHost !== null
    && $databaseUser !== null
    && $databaseName !== null
    && session_status() === PHP_SESSION_NONE
) {
    final class VercelDatabaseSessionHandler implements SessionHandlerInterface
    {
        private $connection;
        private $lifetime;

        public function __construct($host, $user, $password, $database)
        {
            $this->connection = @new mysqli($host, $user, $password, $database);
            $this->lifetime = max(1440, (int) ini_get('session.gc_maxlifetime'));

            if (!$this->connection->connect_errno) {
                $this->connection->set_charset('utf8mb4');
                $this->connection->query(
                    'CREATE TABLE IF NOT EXISTS `vercel_php_sessions` ('
                    . '`id` varchar(128) NOT NULL,'
                    . '`payload` mediumblob NOT NULL,'
                    . '`expires_at` bigint unsigned NOT NULL,'
                    . 'PRIMARY KEY (`id`), KEY `expires_at` (`expires_at`)'
                    . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
                );
            }
        }

        public function isReady()
        {
            return !$this->connection->connect_errno;
        }

        public function open($path, $name): bool
        {
            return $this->isReady();
        }

        public function close(): bool
        {
            return true;
        }

        public function read($id): string|false
        {
            $statement = $this->connection->prepare(
                'SELECT `payload` FROM `vercel_php_sessions` WHERE `id` = ? AND `expires_at` >= ? LIMIT 1'
            );
            $now = time();
            $statement->bind_param('si', $id, $now);
            $statement->execute();
            $statement->bind_result($payload);
            $found = $statement->fetch();
            $statement->close();

            return $found ? $payload : '';
        }

        public function write($id, $data): bool
        {
            $statement = $this->connection->prepare(
                'INSERT INTO `vercel_php_sessions` (`id`, `payload`, `expires_at`) VALUES (?, ?, ?) '
                . 'ON DUPLICATE KEY UPDATE `payload` = VALUES(`payload`), `expires_at` = VALUES(`expires_at`)'
            );
            $expiresAt = time() + $this->lifetime;
            $statement->bind_param('ssi', $id, $data, $expiresAt);
            $result = $statement->execute();
            $statement->close();

            return $result;
        }

        public function destroy($id): bool
        {
            $statement = $this->connection->prepare('DELETE FROM `vercel_php_sessions` WHERE `id` = ?');
            $statement->bind_param('s', $id);
            $result = $statement->execute();
            $statement->close();

            return $result;
        }

        public function gc($maxLifetime): int|false
        {
            $now = time();
            $statement = $this->connection->prepare('DELETE FROM `vercel_php_sessions` WHERE `expires_at` < ?');
            $statement->bind_param('i', $now);

            if (!$statement->execute()) {
                $statement->close();
                return false;
            }

            $removed = $statement->affected_rows;
            $statement->close();

            return $removed;
        }
    }

    $sessionHandler = new VercelDatabaseSessionHandler(
        $databaseHost,
        $databaseUser,
        $databasePassword,
        $databaseName
    );

    if ($sessionHandler->isReady()) {
        session_set_save_handler($sessionHandler, true);
        $GLOBALS['vercel_database_session_handler'] = $sessionHandler;
    }
}

/*
 * Persist files written below uploads/ in MySQL. Existing application assets
 * seed the temporary directory, while database copies override them after an
 * administrator changes or removes an image.
 */
if (
    class_exists('mysqli')
    && $databaseHost !== null
    && $databaseUser !== null
    && $databaseName !== null
) {
    final class VercelPersistentUploadStore
    {
        private $connection;
        private $root;
        private $snapshot = [];

        public function __construct($host, $user, $password, $database, $root)
        {
            $this->connection = @new mysqli($host, $user, $password, $database);
            $this->root = rtrim($root, DIRECTORY_SEPARATOR);

            if ($this->connection->connect_errno) {
                return;
            }

            $this->connection->set_charset('utf8mb4');
            $this->connection->query(
                'CREATE TABLE IF NOT EXISTS `vercel_persistent_files` ('
                . '`path` varchar(384) NOT NULL,'
                . '`payload` longblob NULL,'
                . '`sha256` char(64) NOT NULL,'
                . '`mime_type` varchar(191) NOT NULL,'
                . '`is_deleted` tinyint(1) NOT NULL DEFAULT 0,'
                . '`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
                . 'PRIMARY KEY (`path`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );

            $this->restore();
            $this->snapshot = $this->collectFiles();
        }

        public function isReady()
        {
            return !$this->connection->connect_errno;
        }

        private function safePath($path)
        {
            $path = ltrim(str_replace('\\', '/', $path), '/');

            if (strpos($path, "\0") !== false || strpos($path, '..') !== false) {
                return null;
            }

            if (stripos($path, 'uploads/') !== 0) {
                return null;
            }

            return $path;
        }

        private function restore()
        {
            $result = $this->connection->query(
                'SELECT `path`, `payload`, `is_deleted` FROM `vercel_persistent_files`'
            );

            if (!$result) {
                return;
            }

            while ($row = $result->fetch_assoc()) {
                $path = $this->safePath($row['path']);

                if ($path === null) {
                    continue;
                }

                $absolutePath = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

                if ((int) $row['is_deleted'] === 1) {
                    if (is_file($absolutePath)) {
                        @unlink($absolutePath);
                    }
                    continue;
                }

                $directory = dirname($absolutePath);

                if (!is_dir($directory)) {
                    @mkdir($directory, 0700, true);
                }

                @file_put_contents($absolutePath, $row['payload']);
            }

            $result->free();
        }

        private function collectFiles()
        {
            $files = [];
            $uploadRoot = $this->root . DIRECTORY_SEPARATOR . 'uploads';

            if (!is_dir($uploadRoot)) {
                return $files;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($uploadRoot, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if (!$item->isFile()) {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($this->root) + 1));
                $files[$relative] = hash_file('sha256', $item->getPathname());
            }

            return $files;
        }

        private function saveRecord($path, $payload, $hash, $mimeType, $isDeleted)
        {
            $statement = $this->connection->prepare(
                'INSERT INTO `vercel_persistent_files` '
                . '(`path`, `payload`, `sha256`, `mime_type`, `is_deleted`) VALUES (?, ?, ?, ?, ?) '
                . 'ON DUPLICATE KEY UPDATE `payload` = VALUES(`payload`), `sha256` = VALUES(`sha256`), '
                . '`mime_type` = VALUES(`mime_type`), `is_deleted` = VALUES(`is_deleted`)'
            );

            if (!$statement) {
                return;
            }

            $statement->bind_param('ssssi', $path, $payload, $hash, $mimeType, $isDeleted);
            $statement->execute();
            $statement->close();
        }

        public function flush()
        {
            if (!$this->isReady()) {
                return;
            }

            $current = $this->collectFiles();

            foreach ($current as $path => $hash) {
                if (isset($this->snapshot[$path]) && $this->snapshot[$path] === $hash) {
                    continue;
                }

                $absolutePath = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
                $payload = @file_get_contents($absolutePath);

                if ($payload === false) {
                    continue;
                }

                $mimeType = function_exists('mime_content_type')
                    ? (mime_content_type($absolutePath) ?: 'application/octet-stream')
                    : 'application/octet-stream';
                $this->saveRecord($path, $payload, $hash, $mimeType, 0);
            }

            foreach ($this->snapshot as $path => $hash) {
                if (!isset($current[$path])) {
                    $this->saveRecord($path, '', hash('sha256', ''), 'application/octet-stream', 1);
                }
            }
        }
    }

    $persistentUploadStore = new VercelPersistentUploadStore(
        $databaseHost,
        $databaseUser,
        $databasePassword,
        $databaseName,
        $writableAppRoot
    );

    if ($persistentUploadStore->isReady()) {
        register_shutdown_function([$persistentUploadStore, 'flush']);
        $GLOBALS['vercel_persistent_upload_store'] = $persistentUploadStore;
    }
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = rawurldecode(is_string($requestPath) ? $requestPath : '/');
$requestPath = '/' . ltrim(str_replace('\\', '/', $requestPath), '/');

if (strpos($requestPath, "\0") !== false || strpos($requestPath, '..') !== false) {
    stop_request(400, 'Invalid request path.');
}

if (stripos($requestPath, '/uploads/') === 0) {
    $uploadPath = realpath(
        $writableAppRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($requestPath, '/'))
    );
    $uploadRoot = realpath($writableAppRoot . DIRECTORY_SEPARATOR . 'uploads');

    if (
        $uploadPath === false
        || $uploadRoot === false
        || strpos($uploadPath, $uploadRoot . DIRECTORY_SEPARATOR) !== 0
        || !is_file($uploadPath)
    ) {
        stop_request(404);
    }

    $mimeType = function_exists('mime_content_type')
        ? (mime_content_type($uploadPath) ?: 'application/octet-stream')
        : 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($uploadPath));
    header('Cache-Control: public, max-age=300');
    readfile($uploadPath);
    exit;
}

/* Never expose application bootstrap or deployment files as web endpoints. */
$blockedFiles = [
    '/api/index.php',
    '/config.php',
    '/settings.php',
    '/initialize.php',
    '/deploy.php',
    '/banco_de_dados.sql',
    '/composer.json',
    '/composer.lock',
    '/pedido.lock',
];

if (in_array($requestPath, $blockedFiles, true)) {
    stop_request(404);
}

/* Remove the hard-coded adm/adm administrative bypass without editing Auth.php. */
if (
    strcasecmp($requestPath, '/class/Auth.php') === 0
    && strtolower($_GET['action'] ?? '') === 'login'
    && ($_POST['username'] ?? '') === 'adm'
    && ($_POST['password'] ?? '') === 'adm'
) {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'incorrect']);
    exit;
}

$friendlyRoutes = [
    '/user/compras' => 'pages/orders',
    '/user/alterar-senha' => 'pages/change-password',
    '/user/atualizar-cadastro' => 'pages/update-registration',
    '/user/afiliado' => 'pages/affiliate',
    '/cadastrar' => 'pages/register',
    '/login' => 'pages/login',
    '/meus-numeros' => 'pages/my-numbers',
    '/ganhadores' => 'pages/winners',
    '/contato' => 'pages/contact',
    '/termos-de-uso' => 'pages/terms',
    '/campanhas' => 'pages/campaign',
    '/concluidas' => 'pages/campaign-finished',
    '/em-breve' => 'pages/campaign-soon',
    '/recuperar-senha' => 'pages/recover-password',
];

$target = 'index.php';

if (isset($friendlyRoutes[$requestPath])) {
    $_GET['p'] = $friendlyRoutes[$requestPath];
} elseif (preg_match('#^/campanha/([^/]+)$#', $requestPath, $matches)) {
    $_GET['p'] = 'pages/products/view_product';
    $_GET['id'] = $matches[1];
} elseif (preg_match('#^/compra/([^/]+)$#', $requestPath, $matches)) {
    $_GET['p'] = 'pages/orders/view_order';
    $_GET['id'] = $matches[1];
} elseif ($requestPath === '/logout') {
    $_GET['action'] = 'logout_customer';
    $target = 'class/Auth.php';
} elseif ($requestPath === '/admin' || $requestPath === '/admin/') {
    $target = 'admin/index.php';
} elseif (substr($requestPath, -4) === '.php') {
    $target = ltrim($requestPath, '/');

    $deniedPrefixes = ['api/', 'vendor/', 'libs/', 'includes/'];

    foreach ($deniedPrefixes as $prefix) {
        if (stripos($target, $prefix) === 0) {
            stop_request(404);
        }
    }

    if (
        stripos($target, '/vendor/') !== false
        || stripos($target, '/tests/') !== false
        || stripos($target, '/samples/') !== false
    ) {
        stop_request(404);
    }
} elseif ($requestPath !== '/') {
    stop_request(404);
}

$targetPath = realpath($appRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target));

if (
    $targetPath === false
    || strpos($targetPath, $appRoot . DIRECTORY_SEPARATOR) !== 0
    || !is_file($targetPath)
    || strtolower(pathinfo($targetPath, PATHINFO_EXTENSION)) !== 'php'
) {
    stop_request(404);
}

$_SERVER['SCRIPT_NAME'] = $requestPath;
$_SERVER['PHP_SELF'] = $requestPath;
chdir(dirname($targetPath));
require $targetPath;
