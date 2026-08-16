<?php

require_once '../settings.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

$adminCountResult = $conn->query('SELECT COUNT(*) AS total FROM users WHERE type = 1');
$adminCount = $adminCountResult ? (int) $adminCountResult->fetch_assoc()['total'] : 0;

if ($adminCount > 0) {
    http_response_code(404);
    exit('Pagina nao encontrada.');
}

$setupToken = defined('ADMIN_SETUP_TOKEN') ? (string) ADMIN_SETUP_TOKEN : '';
if (strlen($setupToken) < 32) {
    http_response_code(503);
    exit('A instalacao administrativa ainda nao foi habilitada no arquivo privado.');
}

if (empty($_SESSION['admin_setup_csrf'])) {
    $_SESSION['admin_setup_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf'] ?? '');
    $providedToken = (string) ($_POST['setup_token'] ?? '');
    $name = trim((string) ($_POST['name'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!hash_equals((string) $_SESSION['admin_setup_csrf'], $csrf)) {
        $error = 'A sessao expirou. Atualize a pagina e tente novamente.';
    } elseif (!hash_equals($setupToken, $providedToken)) {
        $_SESSION['admin_setup_attempts'] = (int) ($_SESSION['admin_setup_attempts'] ?? 0) + 1;
        if ($_SESSION['admin_setup_attempts'] >= 5) {
            http_response_code(429);
            exit('Muitas tentativas. Feche o navegador e tente novamente mais tarde.');
        }
        $error = 'Chave de instalacao incorreta.';
    } elseif (mb_strlen($name) < 2) {
        $error = 'Informe o nome do administrador.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,40}$/', $username)) {
        $error = 'O usuario deve ter de 3 a 40 caracteres e usar apenas letras, numeros, ponto, traco ou sublinhado.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail valido.';
    } elseif (strlen($password) < 12) {
        $error = 'A senha deve ter pelo menos 12 caracteres.';
    } elseif (!hash_equals($password, $confirmation)) {
        $error = 'A confirmacao da senha nao confere.';
    } else {
        $duplicate = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $duplicate->bind_param('s', $username);
        $duplicate->execute();
        $alreadyExists = $duplicate->get_result()->num_rows > 0;
        $duplicate->close();

        if ($alreadyExists) {
            $error = 'Este usuario ja existe.';
        } else {
            $parts = preg_split('/\s+/', $name, 2);
            $firstname = $parts[0];
            $lastname = $parts[1] ?? 'Administrador';
            $email = $email !== '' ? $email : $username . '@admin.local';
            $hash = jnsalles_admin_password_hash($password);

            $statement = $conn->prepare('INSERT INTO users (firstname, lastname, username, password, email, type) VALUES (?, ?, ?, ?, ?, 1)');
            $statement->bind_param('sssss', $firstname, $lastname, $username, $hash, $email);
            $success = $statement->execute();
            $statement->close();

            if ($success) {
                unset($_SESSION['admin_setup_attempts'], $_SESSION['admin_setup_csrf']);
            } else {
                $error = 'Nao foi possivel criar o administrador.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar administrador</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#0f1117;color:#f8fafc;font-family:Inter,Arial,sans-serif}.card{width:100%;max-width:470px;padding:28px;border:1px solid #303747;border-radius:16px;background:#171b24;box-shadow:0 24px 70px rgba(0,0,0,.35)}h1{margin:0 0 8px;font-size:24px}p{color:#aab4c4;line-height:1.5}.field{margin-top:16px}label{display:block;margin-bottom:6px;color:#cbd5e1;font-size:13px;font-weight:700}input{width:100%;height:46px;padding:0 13px;border:1px solid #3b4558;border-radius:9px;outline:none;background:#10141c;color:#fff;font-size:14px}input:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.16)}button,.button{display:flex;width:100%;height:46px;align-items:center;justify-content:center;margin-top:20px;border:0;border-radius:9px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-weight:800;text-decoration:none;cursor:pointer}.message{margin:16px 0 0;padding:12px;border-radius:9px;font-size:13px}.error{border:1px solid #7f1d1d;background:#450a0a;color:#fecaca}.success{border:1px solid #047857;background:#064e3b;color:#d1fae5}.note{margin-top:18px;font-size:12px;color:#94a3b8}
    </style>
</head>
<body>
<main class="card">
    <?php if ($success): ?>
        <h1>Administrador criado</h1>
        <div class="message success">A instalacao foi concluida. Esta pagina agora esta desativada automaticamente.</div>
        <a class="button" href="login.php">Ir para o login</a>
    <?php else: ?>
        <h1>Primeiro administrador</h1>
        <p>Crie a conta principal do painel. Esta pagina deixara de funcionar assim que a conta for criada.</p>
        <?php if ($error !== ''): ?><div class="message error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['admin_setup_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="field"><label>Chave de instalacao</label><input type="password" name="setup_token" required autocomplete="off"></div>
            <div class="field"><label>Nome completo</label><input type="text" name="name" required maxlength="500"></div>
            <div class="field"><label>Usuario</label><input type="text" name="username" required minlength="3" maxlength="40" autocomplete="username"></div>
            <div class="field"><label>E-mail (opcional)</label><input type="email" name="email" maxlength="254"></div>
            <div class="field"><label>Senha</label><input type="password" name="password" required minlength="12" autocomplete="new-password"></div>
            <div class="field"><label>Confirmar senha</label><input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></div>
            <button type="submit">Criar administrador</button>
        </form>
        <div class="note">Por seguranca, sao permitidas no maximo cinco tentativas de chave por sessao.</div>
    <?php endif; ?>
</main>
</body>
</html>
