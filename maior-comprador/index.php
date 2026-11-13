<?php
require_once dirname(__DIR__) . '/settings.php';

if (!headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
}

if (empty($_SESSION['biggest_buyer_portal_login_csrf'])) {
    $_SESSION['biggest_buyer_portal_login_csrf'] = bin2hex(random_bytes(24));
}
$loginCsrf = (string) $_SESSION['biggest_buyer_portal_login_csrf'];
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receivedCsrf = (string) ($_POST['csrf_token'] ?? '');
    if ($receivedCsrf === '' || !hash_equals($loginCsrf, $receivedCsrf)) {
        $loginError = 'A sessão expirou. Atualize a página e tente novamente.';
    } elseif (($_POST['portal_action'] ?? '') === 'logout') {
        unset($_SESSION['biggest_buyer_portal'], $_SESSION['userdata'], $_SESSION['biggest_buyer_csrf']);
        session_regenerate_id(true);
        header('Location: ' . BASE_URL . 'maior-comprador/');
        exit;
    } elseif (($_POST['portal_action'] ?? '') === 'login') {
        $attempts = is_array($_SESSION['biggest_buyer_portal_attempts'] ?? null)
            ? $_SESSION['biggest_buyer_portal_attempts']
            : ['count' => 0, 'started_at' => time()];
        if ((int) ($attempts['started_at'] ?? 0) < time() - 300) {
            $attempts = ['count' => 0, 'started_at' => time()];
        }

        if ((int) ($attempts['count'] ?? 0) >= 8) {
            $loginError = 'Muitas tentativas. Aguarde alguns minutos e tente novamente.';
        } else {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $authenticated = false;
            $restrictedHash = '$2y$10$R4FEs0y6pL/d2/nJpQlOB.WzWRR/wxbId8tqpRmo4grEfaqk3/wIu';

            if (hash_equals('guedes', $username) && password_verify($password, $restrictedHash)) {
                session_regenerate_id(true);
                $_SESSION['biggest_buyer_portal'] = [
                    'authenticated' => true,
                    'username' => 'guedes',
                    'display_name' => 'Guedes',
                    'expires_at' => time() + 43200,
                ];
                $authenticated = true;
            } elseif ($username !== '' && $password !== '') {
                $statement = $conn->prepare('SELECT * FROM users WHERE username = ? AND type = 1 LIMIT 1');
                $statement->bind_param('s', $username);
                $statement->execute();
                $account = $statement->get_result()->fetch_assoc();
                $statement->close();
                if ($account && jnsalles_admin_password_verify($password, $account['password'])) {
                    session_regenerate_id(true);
                    foreach ($account as $field => $value) {
                        if ($field !== 'password') {
                            $_SESSION['userdata'][$field] = $value;
                        }
                    }
                    $_SESSION['userdata']['login_type'] = 1;
                    $authenticated = true;
                    if (password_get_info((string) $account['password'])['algoName'] === 'unknown') {
                        $newHash = jnsalles_admin_password_hash($password);
                        $accountId = (int) $account['id'];
                        $rehash = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                        $rehash->bind_param('si', $newHash, $accountId);
                        $rehash->execute();
                        $rehash->close();
                    }
                }
            }

            if ($authenticated) {
                unset($_SESSION['biggest_buyer_portal_attempts']);
                if (empty($_SESSION['biggest_buyer_csrf'])) {
                    $_SESSION['biggest_buyer_csrf'] = bin2hex(random_bytes(24));
                }
                header('Location: ' . BASE_URL . 'maior-comprador/');
                exit;
            }

            $attempts['count'] = (int) ($attempts['count'] ?? 0) + 1;
            $_SESSION['biggest_buyer_portal_attempts'] = $attempts;
            $loginError = 'Usuário ou senha incorretos.';
        }
    }
}

$authorized = biggest_buyer_access_is_authorized();
$siteName = trim((string) $_settings->info('name')) ?: 'JNSalles';
$operatorName = biggest_buyer_operator_name();
?>
<!doctype html>
<html lang="pt-BR" class="theme-dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maior Comprador — <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>admin/assets/css/tailwind.output.css">
  <script>var _base_url_ = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;</script>
  <style>
  :root{color-scheme:dark}*{box-sizing:border-box}body{min-height:100vh;margin:0;background:#0d0f14;color:#e5edf8;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.portal-login{display:grid;min-height:100vh;place-items:center;padding:24px;background:radial-gradient(circle at 50% 0,rgba(124,58,237,.18),transparent 36%),#0d0f14}.portal-login-card{width:min(430px,100%);padding:30px;border:1px solid #2d3a50;border-radius:22px;background:linear-gradient(145deg,rgba(28,39,57,.97),rgba(15,23,42,.99));box-shadow:0 30px 90px rgba(0,0,0,.4)}.portal-brand{display:flex;align-items:center;gap:12px;margin-bottom:26px}.portal-brand-mark{display:grid;width:44px;height:44px;place-items:center;border-radius:13px;background:linear-gradient(145deg,#7c3aed,#4c1d95);color:#fff;font-size:20px;font-weight:900}.portal-brand small{display:block;color:#8798b0;font-size:10px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.portal-brand strong{display:block;margin-top:2px;color:#fff;font-size:17px}.portal-login h1{margin:0;color:#fff;font-size:27px;letter-spacing:-.04em}.portal-login-intro{margin:8px 0 22px;color:#91a2ba;font-size:13px;line-height:1.6}.portal-field{display:block;margin-top:14px}.portal-field span{display:block;margin-bottom:7px;color:#cbd5e1;font-size:11px;font-weight:800}.portal-field input{width:100%;height:47px;padding:0 14px;border:1px solid #40506a;border-radius:11px;background:#0c1526;color:#f8fafc;font-size:13px;outline:0}.portal-field input:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.15)}.portal-submit{width:100%;height:47px;margin-top:20px;border:0;border-radius:11px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:12px;font-weight:900;cursor:pointer}.portal-error{margin:0 0 16px;padding:11px 13px;border:1px solid rgba(248,113,113,.35);border-radius:10px;background:rgba(127,29,29,.35);color:#fecaca;font-size:12px;line-height:1.45}.portal-note{margin:18px 0 0;color:#64748b;font-size:10px;line-height:1.55;text-align:center}.portal-topbar{position:sticky;z-index:80;top:0;display:flex;min-height:62px;align-items:center;justify-content:space-between;gap:18px;padding:10px max(20px,calc((100vw - 1380px)/2 + 26px));border-bottom:1px solid #242d3c;background:rgba(13,15,20,.94);backdrop-filter:blur(14px)}.portal-topbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:13px;font-weight:850}.portal-topbar-brand span:first-child{display:grid;width:34px;height:34px;place-items:center;border-radius:10px;background:#6d28d9}.portal-topbar-actions{display:flex;align-items:center;gap:10px}.portal-identity{color:#8798b0;font-size:11px}.portal-identity strong{color:#dbe5f3}.portal-exit{height:36px;padding:0 12px;border:1px solid #3b4659;border-radius:9px;background:#172033;color:#e2e8f0;font-size:10px;font-weight:850;cursor:pointer}.portal-content main{min-height:calc(100vh - 62px)}@media(max-width:620px){.portal-topbar{align-items:flex-start;padding:10px 14px}.portal-identity{display:none}.portal-login-card{padding:24px 20px}}
  </style>
</head>
<body>
<?php if (!$authorized): ?>
  <main class="portal-login">
    <section class="portal-login-card">
      <div class="portal-brand"><span class="portal-brand-mark">M</span><div><small><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></small><strong>Acesso Maior Comprador</strong></div></div>
      <h1>Entre para continuar</h1>
      <p class="portal-login-intro">Esta área contém somente as ferramentas da ação Maior Comprador.</p>
      <?php if ($loginError !== ''): ?><div class="portal-error" role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <form method="post" autocomplete="on">
        <input type="hidden" name="portal_action" value="login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($loginCsrf, ENT_QUOTES, 'UTF-8') ?>">
        <label class="portal-field"><span>Usuário</span><input name="username" type="text" autocomplete="username" required autofocus></label>
        <label class="portal-field"><span>Senha</span><input name="password" type="password" autocomplete="current-password" required></label>
        <button class="portal-submit" type="submit">Acessar Maior Comprador</button>
      </form>
      <p class="portal-note">Contas administrativas existentes também podem acessar esta área.</p>
    </section>
  </main>
<?php else: ?>
  <header class="portal-topbar">
    <div class="portal-topbar-brand"><span>M</span><span>Maior Comprador</span></div>
    <div class="portal-topbar-actions"><span class="portal-identity">Conectado como <strong><?= htmlspecialchars($operatorName, ENT_QUOTES, 'UTF-8') ?></strong></span><form method="post"><input type="hidden" name="portal_action" value="logout"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($loginCsrf, ENT_QUOTES, 'UTF-8') ?>"><button class="portal-exit" type="submit">Sair</button></form></div>
  </header>
  <div class="portal-content"><?php require dirname(__DIR__) . '/admin/biggest_buyer/index.php'; ?></div>
<?php endif; ?>
</body>
</html>
