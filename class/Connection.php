<?php


class DBConnection
{
	private $host = DB_SERVER;
	private $username = DB_USERNAME;
	private $password = DB_PASSWORD;
	private $database = DB_NAME;
	public $conn = null;

	public function __construct()
	{
		if (!isset($this->conn)) {
			for ($attempt = 1; $attempt <= 3; $attempt++) {
				try {
					$connection = new mysqli($this->host, $this->username, $this->password, $this->database);
					if (!$connection->connect_errno) {
						$this->conn = $connection;
						break;
					}
				} catch (Throwable $error) {
					error_log('[database] application connection attempt=' . $attempt . ' failed');
				}
				if ($attempt < 3) {
					usleep(250000 * $attempt);
				}
			}

			if (!$this->conn) {
				http_response_code(503);
				header('Retry-After: 2');
				exit('O site esta reconectando ao banco de dados. Atualize a pagina em alguns segundos.');
			}
			$this->conn->set_charset('utf8mb4');
		}
	}

	public function __destruct()
	{
		if ($this->conn instanceof mysqli) {
			$this->conn->close();
		}
	}
}

if (!defined('DB_SERVER')) {
	require_once '../initialize.php';
}

?>
