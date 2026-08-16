<?php
if ((int) $_settings->userdata('type') !== 1) {
    echo '<script>location.href="./"</script>';
    exit;
}

$currentId = (int) $_settings->userdata('id');
$primaryRow = $conn->query('SELECT MIN(id) AS id FROM users WHERE type = 1')->fetch_assoc();
$primaryId = (int) ($primaryRow['id'] ?? 0);
$adminsResult = $conn->query('SELECT id, firstname, lastname, username, date_added FROM users WHERE type = 1 ORDER BY id ASC');
$admins = [];
while ($row = $adminsResult->fetch_assoc()) {
    $admins[] = $row;
}
$currentAdmin = null;
foreach ($admins as $admin) {
    if ((int) $admin['id'] === $currentId) {
        $currentAdmin = $admin;
        break;
    }
}

function admin_initials($firstname, $lastname)
{
    $first = mb_substr(trim((string) $firstname), 0, 1);
    $last = mb_substr(trim((string) $lastname), 0, 1);
    return mb_strtoupper($first . $last);
}

function admin_icon($name)
{
    $icons = [
        'settings' => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10M14 4v6M6 14v6"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'shield' => '<path d="M12 3 5 6v5c0 4.6 2.9 8.8 7 10 4.1-1.2 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M15 8l3 3M17 6l2 2"/>',
        'user-plus' => '<path d="M15 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M16 11h6"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
    ];
    $content = $icons[$name] ?? $icons['check'];
    return '<svg class="admin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $content . '</svg>';
}
?>

<style>
  .admin-icon{display:block;width:18px;height:18px;flex:0 0 18px}.admins-stat__icon .admin-icon,.admins-card__icon .admin-icon{width:20px;height:20px}.admins-show-password .admin-icon{width:17px;height:17px;margin:auto}.admins-shell{max-width:1180px;padding:28px 24px 48px}.admins-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}.admins-eyebrow{margin:0 0 5px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.admins-head h2{margin:0;color:#f8fafc;font-size:30px;font-weight:800;letter-spacing:-.035em}.admins-head p{margin:7px 0 0;color:#94a3b8;font-size:14px}.admins-site-link{display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border:1px solid #3b4659;border-radius:10px;background:#172033;color:#e2e8f0;font-size:12px;font-weight:700;transition:.18s}.admins-site-link:hover{border-color:#8b5cf6;color:#fff}.admins-feedback{position:sticky;top:14px;z-index:30;margin-bottom:16px;padding:12px 14px;border-radius:11px;font-size:13px;font-weight:700}.admins-feedback.success{border:1px solid rgba(52,211,153,.35);background:#064e3b;color:#d1fae5}.admins-feedback.error{border:1px solid rgba(248,113,113,.35);background:#7f1d1d;color:#fee2e2}.admins-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}.admins-stat{display:flex;align-items:center;gap:12px;padding:16px;border:1px solid #2d3748;border-radius:14px;background:linear-gradient(145deg,rgba(30,41,59,.78),rgba(17,24,39,.95))}.admins-stat__icon{display:flex;width:40px;height:40px;align-items:center;justify-content:center;border-radius:11px;background:rgba(124,58,237,.2);color:#c4b5fd}.admins-stat small{display:block;color:#94a3b8;font-size:10px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.admins-stat strong{display:block;margin-top:2px;color:#f8fafc;font-size:16px}.admins-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;margin-bottom:20px}.admins-card{border:1px solid #2d3748;border-radius:16px;background:linear-gradient(145deg,rgba(30,41,59,.72),rgba(17,24,39,.94));box-shadow:0 18px 45px rgba(0,0,0,.14)}.admins-card__head{display:flex;align-items:flex-start;gap:12px;padding:19px 20px;border-bottom:1px solid #2d3748}.admins-card__icon{display:flex;width:38px;height:38px;flex:0 0 38px;align-items:center;justify-content:center;border-radius:10px;background:#312e81;color:#c4b5fd}.admins-card__head h3{margin:0;color:#f8fafc;font-size:16px;font-weight:750}.admins-card__head p{margin:4px 0 0;color:#94a3b8;font-size:11px;line-height:1.5}.admins-card__body{padding:20px}.admins-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.admins-field{min-width:0}.admins-field.full{grid-column:1/-1}.admins-field label{display:block;margin-bottom:6px;color:#cbd5e1;font-size:11px;font-weight:700}.admins-input-wrap{position:relative}.admins-input{width:100%;min-height:43px;padding:0 12px;border:1px solid #3f4d63;border-radius:9px;background:#0f172a;color:#f8fafc;font-size:13px;outline:none;transition:.18s}.admins-input:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.15)}.admins-input-wrap .admins-input{padding-right:48px}.admins-show-password{position:absolute;top:5px;right:5px;display:flex;width:34px;height:33px;align-items:center;justify-content:center;border:0;border-radius:7px;background:#1e293b;color:#94a3b8}.admins-show-password:hover{color:#fff}.admins-submit{display:inline-flex;min-height:42px;align-items:center;justify-content:center;gap:8px;padding:0 16px;border:0;border-radius:9px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-size:12px;font-weight:800;box-shadow:0 8px 20px rgba(124,58,237,.2)}.admins-submit:disabled{cursor:wait;opacity:.6}.admins-list-card{overflow:hidden}.admins-list-head{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:20px;border-bottom:1px solid #2d3748}.admins-list-head h3{margin:0;color:#f8fafc;font-size:17px;font-weight:750}.admins-list-head p{margin:4px 0 0;color:#94a3b8;font-size:11px}.admins-count{padding:5px 9px;border-radius:999px;background:rgba(124,58,237,.2);color:#c4b5fd;font-size:10px;font-weight:800}.admins-list{display:grid;gap:10px;padding:14px}.admin-row{border:1px solid #334155;border-radius:13px;background:rgba(15,23,42,.68);overflow:hidden}.admin-row__summary{display:grid;grid-template-columns:minmax(230px,1.2fr) minmax(150px,.7fr) minmax(130px,.55fr) auto;gap:15px;align-items:center;padding:14px}.admin-identity{display:flex;align-items:center;min-width:0;gap:11px}.admin-avatar{display:flex;width:42px;height:42px;flex:0 0 42px;align-items:center;justify-content:center;border:1px solid rgba(167,139,250,.25);border-radius:12px;background:linear-gradient(135deg,#4c1d95,#312e81);color:#ede9fe;font-size:13px;font-weight:850}.admin-identity strong{display:block;overflow:hidden;color:#f1f5f9;font-size:13px;text-overflow:ellipsis;white-space:nowrap}.admin-identity small,.admin-meta small{display:block;margin-top:3px;color:#94a3b8;font-size:10px}.admin-meta span{display:block;color:#cbd5e1;font-size:12px}.admin-badges{display:flex;gap:5px;flex-wrap:wrap}.admin-badge{padding:4px 7px;border-radius:999px;font-size:9px;font-weight:800;letter-spacing:.03em;text-transform:uppercase}.admin-badge.primary{background:rgba(245,158,11,.16);color:#fcd34d}.admin-badge.session{background:rgba(16,185,129,.16);color:#6ee7b7}.admin-actions{display:flex;justify-content:flex-end;gap:7px}.admin-action{display:inline-flex;min-height:35px;align-items:center;justify-content:center;gap:6px;padding:0 10px;border-radius:8px;font-size:10px;font-weight:800}.admin-edit{border:1px solid #475569;background:#1e293b;color:#e2e8f0}.admin-delete{border:1px solid rgba(248,113,113,.35);background:rgba(127,29,29,.18);color:#fca5a5}.admin-delete.confirming{border-color:#ef4444;background:#b91c1c;color:#fff}.admin-delete:disabled{cursor:not-allowed;opacity:.38}.admin-edit-form{display:none;padding:16px;border-top:1px solid #334155;background:rgba(2,6,23,.38)}.admin-edit-form.open{display:block}.admin-edit-actions{display:flex;align-items:center;gap:8px;grid-column:1/-1}.admin-cancel{min-height:42px;padding:0 13px;border:1px solid #475569;border-radius:9px;background:#1e293b;color:#cbd5e1;font-size:11px;font-weight:750}@media(max-width:900px){.admins-stats{grid-template-columns:1fr}.admins-grid{grid-template-columns:1fr}.admin-row__summary{grid-template-columns:1fr auto}.admin-meta,.admin-badges{grid-column:1}.admin-actions{grid-column:2;grid-row:1/4;flex-direction:column}}@media(max-width:600px){.admins-shell{padding:20px 14px 42px}.admins-head{align-items:flex-start;flex-direction:column}.admins-head h2{font-size:25px}.admins-site-link{width:100%;justify-content:center}.admins-form-grid{grid-template-columns:1fr}.admins-field.full{grid-column:auto}.admin-row__summary{grid-template-columns:1fr}.admin-actions{grid-column:1;grid-row:auto;flex-direction:row;justify-content:flex-start}.admin-action{flex:1}.admin-edit-actions{grid-column:auto;flex-direction:column}.admin-edit-actions button{width:100%}}
</style>

<main class="h-full pb-16 overflow-y-auto">
  <div class="container mx-auto admins-shell">
    <header class="admins-head">
      <div>
        <p class="admins-eyebrow">Acesso e segurança</p>
        <h2>Administradores</h2>
        <p>Gerencie quem pode acessar o painel e proteja sua própria conta.</p>
      </div>
      <a class="admins-site-link" href="./?page=system_info"><?= admin_icon('settings') ?> Configurações do site</a>
    </header>

    <div id="admin-feedback" class="admins-feedback" hidden></div>

    <section class="admins-stats">
      <div class="admins-stat"><span class="admins-stat__icon"><?= admin_icon('users') ?></span><div><small>Contas ativas</small><strong><?= count($admins) ?> administradores</strong></div></div>
      <div class="admins-stat"><span class="admins-stat__icon"><?= admin_icon('user') ?></span><div><small>Sessão atual</small><strong><?= htmlspecialchars(trim(($currentAdmin['firstname'] ?? '') . ' ' . ($currentAdmin['lastname'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong></div></div>
      <div class="admins-stat"><span class="admins-stat__icon"><?= admin_icon('shield') ?></span><div><small>Conta principal</small><strong>Protegida contra exclusão</strong></div></div>
    </section>

    <div class="admins-grid">
      <section class="admins-card">
        <header class="admins-card__head"><span class="admins-card__icon"><?= admin_icon('key') ?></span><div><h3>Alterar minha senha</h3><p>A senha atual é obrigatória. A alteração afeta somente a conta conectada.</p></div></header>
        <div class="admins-card__body">
          <form id="current-password-form" class="admins-form-grid" autocomplete="off">
            <div class="admins-field full"><label>Senha atual</label><div class="admins-input-wrap"><input class="admins-input" name="current_password" type="password" required autocomplete="current-password"><button class="admins-show-password" type="button" aria-label="Mostrar senha"><?= admin_icon('eye') ?></button></div></div>
            <div class="admins-field"><label>Nova senha</label><div class="admins-input-wrap"><input class="admins-input" name="new_password" type="password" required autocomplete="new-password"><button class="admins-show-password" type="button" aria-label="Mostrar senha"><?= admin_icon('eye') ?></button></div></div>
            <div class="admins-field"><label>Confirmar nova senha</label><div class="admins-input-wrap"><input class="admins-input" name="password_confirmation" type="password" required autocomplete="new-password"><button class="admins-show-password" type="button" aria-label="Mostrar senha"><?= admin_icon('eye') ?></button></div></div>
            <button class="admins-submit full" type="submit"><?= admin_icon('lock') ?> Atualizar minha senha</button>
          </form>
        </div>
      </section>

      <section class="admins-card">
        <header class="admins-card__head"><span class="admins-card__icon"><?= admin_icon('user-plus') ?></span><div><h3>Novo administrador</h3><p>Crie um acesso individual com o usuário e a senha desejados.</p></div></header>
        <div class="admins-card__body">
          <form class="admin-account-form admins-form-grid" data-id="0" autocomplete="off">
            <div class="admins-field full"><label>Nome completo</label><input class="admins-input" name="name" required minlength="2" placeholder="Nome do administrador"></div>
            <div class="admins-field"><label>Usuário</label><input class="admins-input" name="username" required minlength="3" maxlength="40" placeholder="usuario.admin"></div>
            <div class="admins-field"><label>Senha</label><div class="admins-input-wrap"><input class="admins-input" name="password" required type="password" autocomplete="new-password"><button class="admins-show-password" type="button" aria-label="Mostrar senha"><?= admin_icon('eye') ?></button></div></div>
            <button class="admins-submit full" type="submit"><?= admin_icon('plus') ?> Criar administrador</button>
          </form>
        </div>
      </section>
    </div>

    <section class="admins-card admins-list-card">
      <header class="admins-list-head"><div><h3>Contas cadastradas</h3><p>Edite dados, redefina senhas ou remova acessos que não são mais necessários.</p></div><span class="admins-count"><?= count($admins) ?> contas</span></header>
      <div class="admins-list">
        <?php foreach ($admins as $admin):
          $adminId = (int) $admin['id'];
          $isPrimary = $adminId === $primaryId;
          $isCurrent = $adminId === $currentId;
          $fullName = trim($admin['firstname'] . ' ' . $admin['lastname']);
          $created = !empty($admin['date_added']) ? date('d/m/Y', strtotime($admin['date_added'])) : 'Data não informada';
        ?>
          <article class="admin-row" data-admin-id="<?= $adminId ?>">
            <div class="admin-row__summary">
              <div class="admin-identity"><span class="admin-avatar"><?= htmlspecialchars(admin_initials($admin['firstname'], $admin['lastname']), ENT_QUOTES, 'UTF-8') ?></span><div><strong><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></strong><small>@<?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?></small></div></div>
              <div class="admin-meta"><small>Criada em</small><span><?= htmlspecialchars($created, ENT_QUOTES, 'UTF-8') ?></span></div>
              <div class="admin-badges"><?php if ($isPrimary): ?><span class="admin-badge primary">Principal</span><?php endif; ?><?php if ($isCurrent): ?><span class="admin-badge session">Sua sessão</span><?php endif; ?></div>
              <div class="admin-actions"><button type="button" class="admin-action admin-edit"><?= admin_icon('edit') ?> Editar</button><button type="button" class="admin-action admin-delete" <?= ($isPrimary || $isCurrent) ? 'disabled title="Esta conta é protegida contra exclusão"' : '' ?>><?= admin_icon('trash') ?> Excluir</button></div>
            </div>
            <form class="admin-account-form admin-edit-form admins-form-grid" data-id="<?= $adminId ?>" autocomplete="off">
              <div class="admins-field"><label>Nome completo</label><input class="admins-input" name="name" required minlength="2" value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"></div>
              <div class="admins-field"><label>Usuário</label><input class="admins-input" name="username" required minlength="3" maxlength="40" value="<?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?>"></div>
              <div class="admins-field full"><label>Nova senha <small>(deixe vazio para manter)</small></label><div class="admins-input-wrap"><input class="admins-input" name="password" type="password" autocomplete="new-password"><button class="admins-show-password" type="button" aria-label="Mostrar senha"><?= admin_icon('eye') ?></button></div></div>
              <div class="admin-edit-actions"><button class="admins-submit" type="submit"><?= admin_icon('check') ?> Salvar alterações</button><button class="admin-cancel" type="button">Cancelar</button></div>
            </form>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>

<script>
(function ($) {
  function feedback(message, ok) {
    $('#admin-feedback').prop('hidden', false).removeClass('success error').addClass(ok ? 'success' : 'error').text(message);
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  function submitForm(form, action) {
    var button = form.find('button[type="submit"]');
    var data = form.serializeArray();
    if (action === 'save') data.push({name: 'id', value: form.data('id')});
    button.prop('disabled', true);
    $.post(_base_url_ + 'class/AdminUsers.php?action=' + action, data, function (response) {
      var ok = response.status === 'success';
      feedback(response.message || (ok ? 'Alteração salva.' : 'Não foi possível concluir.'), ok);
      if (ok) {
        form[0].reset();
        setTimeout(function () { location.reload(); }, 900);
      }
    }, 'json').fail(function () { feedback('Não foi possível comunicar com o servidor.', false); }).always(function () { button.prop('disabled', false); });
  }

  $('.admin-account-form').on('submit', function (event) { event.preventDefault(); submitForm($(this), 'save'); });
  $('#current-password-form').on('submit', function (event) {
    event.preventDefault();
    var form = $(this);
    if (form.find('[name="new_password"]').val() !== form.find('[name="password_confirmation"]').val()) {
      feedback('A confirmação da nova senha não confere.', false);
      return;
    }
    submitForm(form, 'password');
  });
  $('.admin-edit').on('click', function () { $(this).closest('.admin-row').find('.admin-edit-form').toggleClass('open'); });
  $('.admin-cancel').on('click', function () { $(this).closest('.admin-edit-form').removeClass('open'); });
  $('.admins-show-password').on('click', function () {
    var button = $(this), input = button.siblings('input'), showing = input.attr('type') === 'text';
    input.attr('type', showing ? 'password' : 'text');
    button.attr('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha').toggleClass('showing', !showing);
  });
  $('.admin-delete:not(:disabled)').on('click', function () {
    var button = $(this), row = button.closest('.admin-row'), id = row.data('admin-id');
    if (!button.hasClass('confirming')) {
      var originalContent = button.html();
      button.addClass('confirming').text('Confirmar exclusão');
      setTimeout(function () { button.removeClass('confirming').html(originalContent); }, 3500);
      return;
    }
    button.prop('disabled', true);
    $.post(_base_url_ + 'class/AdminUsers.php?action=delete', {id: id}, function (response) {
      var ok = response.status === 'success';
      feedback(response.message || (ok ? 'Administrador excluído.' : 'Não foi possível excluir.'), ok);
      if (ok) row.slideUp(220, function () { location.reload(); });
      else button.prop('disabled', false);
    }, 'json').fail(function () { feedback('Não foi possível comunicar com o servidor.', false); button.prop('disabled', false); });
  });
})(jQuery);
</script>
