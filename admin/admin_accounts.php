<?php
if ((int) $_settings->userdata('type') !== 1) {
    echo '<script>location.href="./"</script>';
    exit;
}
$admins = $conn->query("SELECT id, firstname, lastname, username, date_added FROM users WHERE type = 1 ORDER BY id ASC");
$currentId = (int) $_settings->userdata('id');
?>
<main class="h-full pb-16 overflow-y-auto">
  <div class="container px-6 mx-auto grid">
    <div class="flex items-center justify-between my-6">
      <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Configuração</h2>
      <a href="./?page=system_info" class="px-4 py-2 text-sm font-medium text-purple-700 bg-purple-100 rounded-lg">Configurações do site</a>
    </div>
    <div id="admin-feedback" class="hidden px-4 py-3 mb-4 rounded-lg"></div>
    <section class="p-5 mb-6 bg-white rounded-xl shadow-md dark:bg-gray-800">
      <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">Novo administrador</h3>
      <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Crie acessos individuais para quem administra o site.</p>
      <form class="admin-form grid gap-4 md:grid-cols-3" data-id="0">
        <input name="name" required minlength="2" placeholder="Nome" class="form-input block w-full text-sm rounded-md dark:bg-gray-700 dark:text-gray-200">
        <input name="username" required minlength="3" autocomplete="off" placeholder="Usuário" class="form-input block w-full text-sm rounded-md dark:bg-gray-700 dark:text-gray-200">
        <input name="password" required minlength="8" type="password" autocomplete="new-password" placeholder="Senha (mínimo 8 caracteres)" class="form-input block w-full text-sm rounded-md dark:bg-gray-700 dark:text-gray-200">
        <button class="md:col-span-3 px-5 py-3 font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">Criar administrador</button>
      </form>
    </section>
    <section class="p-5 bg-white rounded-xl shadow-md dark:bg-gray-800">
      <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Administradores cadastrados</h3>
      <div class="space-y-4">
      <?php while ($admin = $admins->fetch_assoc()): $fullName = trim($admin['firstname'].' '.$admin['lastname']); ?>
        <form class="admin-form grid gap-3 p-4 border rounded-lg md:grid-cols-12 dark:border-gray-700" data-id="<?= (int) $admin['id'] ?>">
          <input name="name" required value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>" class="md:col-span-3 form-input block w-full text-sm rounded-md dark:bg-gray-700 dark:text-gray-200">
          <input name="username" required value="<?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?>" class="md:col-span-3 form-input block w-full text-sm rounded-md dark:bg-gray-700 dark:text-gray-200">
          <input name="password" minlength="8" type="password" autocomplete="new-password" placeholder="Nova senha (opcional)" class="md:col-span-3 form-input block w-full text-sm rounded-md dark:bg-gray-700 dark:text-gray-200">
          <button class="md:col-span-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg">Salvar</button>
          <button type="button" class="delete-admin md:col-span-1 px-3 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-lg disabled:opacity-40" <?= (int)$admin['id'] === $currentId ? 'disabled title="Sessão atual"' : '' ?>>Excluir</button>
          <?php if ((int)$admin['id'] === $currentId): ?><span class="md:col-span-12 text-xs text-green-600">Sessão atual</span><?php endif; ?>
        </form>
      <?php endwhile; ?>
      </div>
    </section>
  </div>
</main>
<script>
(function () {
  function feedback(message, ok) {
    $('#admin-feedback').removeClass('hidden bg-green-100 text-green-800 bg-red-100 text-red-800')
      .addClass(ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800').text(message);
  }
  $('.admin-form').on('submit', function (event) {
    event.preventDefault();
    var form = $(this), data = form.serializeArray();
    data.push({name: 'id', value: form.data('id')});
    $.post(_base_url_ + 'class/AdminUsers.php?action=save', data, function (response) {
      feedback(response.message, response.status === 'success');
      if (response.status === 'success') setTimeout(function () { location.reload(); }, 700);
    }, 'json').fail(function () { feedback('Não foi possível salvar o administrador.', false); });
  });
  $('.delete-admin').on('click', function () {
    if (!confirm('Excluir este administrador?')) return;
    var id = $(this).closest('form').data('id');
    $.post(_base_url_ + 'class/AdminUsers.php?action=delete', {id: id}, function (response) {
      feedback(response.message, response.status === 'success');
      if (response.status === 'success') setTimeout(function () { location.reload(); }, 700);
    }, 'json').fail(function () { feedback('Não foi possível excluir o administrador.', false); });
  });
})();
</script>
