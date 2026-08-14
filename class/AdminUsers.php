<?php
require_once('../settings.php');

header('Content-Type: application/json; charset=utf-8');

function admin_users_reply($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ((int) $_settings->userdata('login_type') !== 1 || (int) $_settings->userdata('type') !== 1) {
    http_response_code(403);
    admin_users_reply('failed', 'Acesso administrativo necessário.');
}

$action = $_GET['action'] ?? '';
$currentId = (int) $_settings->userdata('id');

if ($action === 'save') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (mb_strlen($name) < 2 || !preg_match('/^[A-Za-z0-9._-]{3,40}$/', $username)) {
        admin_users_reply('failed', 'Informe um nome e um usuário com pelo menos 3 caracteres.');
    }
    if (($id === 0 || $password !== '') && strlen($password) < 8) {
        admin_users_reply('failed', 'A senha deve ter pelo menos 8 caracteres.');
    }

    $duplicate = $conn->prepare('SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
    $duplicate->bind_param('si', $username, $id);
    $duplicate->execute();
    if ($duplicate->get_result()->num_rows) {
        admin_users_reply('failed', 'Este usuário já está cadastrado.');
    }

    $parts = preg_split('/\s+/', $name, 2);
    $firstname = $parts[0];
    $lastname = $parts[1] ?? 'Administrador';
    if ($id > 0) {
        if ($password !== '') {
            $hash = md5($password);
            $stmt = $conn->prepare('UPDATE users SET firstname = ?, lastname = ?, username = ?, password = ?, type = 1 WHERE id = ?');
            $stmt->bind_param('ssssi', $firstname, $lastname, $username, $hash, $id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET firstname = ?, lastname = ?, username = ?, type = 1 WHERE id = ?');
            $stmt->bind_param('sssi', $firstname, $lastname, $username, $id);
        }
        if (!$stmt->execute() || !$stmt->affected_rows && $conn->errno) {
            admin_users_reply('failed', 'Não foi possível atualizar o administrador.');
        }
        if ($id === $currentId) {
            $_settings->set_userdata('firstname', $firstname);
            $_settings->set_userdata('lastname', $lastname);
            $_settings->set_userdata('username', $username);
        }
        admin_users_reply('success', 'Administrador atualizado com sucesso.');
    }

    $email = $username . '@admin.local';
    $hash = md5($password);
    $stmt = $conn->prepare('INSERT INTO users (firstname, lastname, username, password, email, type, date_added) VALUES (?, ?, ?, ?, ?, 1, NOW())');
    $stmt->bind_param('sssss', $firstname, $lastname, $username, $hash, $email);
    if (!$stmt->execute()) {
        admin_users_reply('failed', 'Não foi possível criar o administrador.');
    }
    admin_users_reply('success', 'Novo administrador criado com sucesso.');
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0 || $id === $currentId) {
        admin_users_reply('failed', 'Você não pode excluir o administrador usado nesta sessão.');
    }
    $count = $conn->query('SELECT COUNT(*) AS total FROM users WHERE type = 1')->fetch_assoc();
    if ((int) $count['total'] <= 1) {
        admin_users_reply('failed', 'É necessário manter pelo menos um administrador.');
    }
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND type = 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->affected_rows !== 1) {
        admin_users_reply('failed', 'Administrador não encontrado.');
    }
    admin_users_reply('success', 'Administrador excluído com sucesso.');
}

admin_users_reply('failed', 'Ação inválida.');

