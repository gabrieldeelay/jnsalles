<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/initialize.php';
require dirname(__DIR__) . '/classes/DBConnection.php';

function read_required($label)
{
    do {
        $value = trim((string) readline($label));
    } while ($value === '');
    return $value;
}

function read_password_hidden($label)
{
    fwrite(STDOUT, $label);
    $canHide = DIRECTORY_SEPARATOR === '/' && function_exists('shell_exec');
    if ($canHide) {
        shell_exec('stty -echo');
    }
    $password = trim((string) fgets(STDIN));
    if ($canHide) {
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }
    return $password;
}

$name = read_required('Nome completo: ');
$username = read_required('Usuario: ');

if (!preg_match('/^[A-Za-z0-9._-]{3,40}$/', $username)) {
    fwrite(STDERR, "O usuario deve ter entre 3 e 40 caracteres e usar apenas letras, numeros, ponto, traco ou sublinhado.\n");
    exit(1);
}

$password = read_password_hidden('Senha (minimo de 12 caracteres): ');
$confirmation = read_password_hidden('Confirme a senha: ');
if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "A senha deve ter ao menos 12 caracteres e a confirmacao deve ser igual.\n");
    exit(1);
}

$parts = preg_split('/\s+/', $name, 2);
$firstname = $parts[0];
$lastname = $parts[1] ?? 'Administrador';
$email = $username . '@admin.local';
$hash = jnsalles_admin_password_hash($password);

$db = new DBConnection();
$duplicate = $db->conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$duplicate->bind_param('s', $username);
$duplicate->execute();
if ($duplicate->get_result()->num_rows > 0) {
    fwrite(STDERR, "Este usuario ja existe.\n");
    exit(1);
}

$statement = $db->conn->prepare('INSERT INTO users (firstname, lastname, username, password, email, type) VALUES (?, ?, ?, ?, ?, 1)');
$statement->bind_param('sssss', $firstname, $lastname, $username, $hash, $email);
if (!$statement->execute()) {
    fwrite(STDERR, "Nao foi possivel criar o administrador.\n");
    exit(1);
}

fwrite(STDOUT, "Administrador criado com sucesso.\n");
