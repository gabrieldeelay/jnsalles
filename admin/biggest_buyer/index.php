<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

if (empty($_SESSION['biggest_buyer_csrf'])) {
    $_SESSION['biggest_buyer_csrf'] = bin2hex(random_bytes(24));
}
$csrfToken = (string) $_SESSION['biggest_buyer_csrf'];

$products = [];
$productsQuery = $conn->query(
    "SELECT p.id, p.name FROM product_list p "
    . "INNER JOIN system_info s ON s.meta_field = CONCAT('ranking_timer_', p.id, '_enabled') "
    . "AND s.meta_value = '1' WHERE p.delete_flag = 0 ORDER BY p.id DESC"
);
while ($productsQuery && ($product = $productsQuery->fetch_assoc())) {
    $products[] = $product;
}

$customers = [];
$customersQuery = $conn->query(
    "SELECT id, firstname, lastname, phone FROM customer_list "
    . "WHERE TRIM(CONCAT(firstname, ' ', lastname)) <> '' ORDER BY firstname ASC, lastname ASC, id ASC"
);
while ($customersQuery && ($customer = $customersQuery->fetch_assoc())) {
    $customers[] = $customer;
}

$storedAction = json_decode((string) $_settings->info('biggest_buyer_action'), true);
$selectedProductId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
if (is_array($storedAction) && ($storedAction['status'] ?? '') === 'active') {
    $selectedProductId = (int) ($storedAction['product_id'] ?? 0);
}
if ($selectedProductId <= 0 && $products) {
    $selectedProductId = (int) $products[0]['id'];
}
?>

<style>
.buyer-shell{max-width:1320px;padding:30px 24px 56px}.buyer-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px}.buyer-eyebrow{margin:0 0 5px;color:#a78bfa;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.buyer-head h2{margin:0;color:#f8fafc;font-size:30px;font-weight:850;letter-spacing:-.035em}.buyer-head p{margin:7px 0 0;color:#94a3b8;font-size:13px}.buyer-live-badge{display:flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid #334155;border-radius:999px;background:#111827;color:#cbd5e1;font-size:11px;font-weight:800}.buyer-live-badge:before{width:8px;height:8px;border-radius:50%;background:#64748b;content:""}.buyer-live-badge.running:before{background:#34d399;box-shadow:0 0 0 5px rgba(52,211,153,.12)}.buyer-alert{margin-bottom:18px;padding:14px 16px;border:1px solid #92400e;border-radius:12px;background:rgba(120,53,15,.28);color:#fde68a;font-size:12px;line-height:1.55}.buyer-grid{display:grid;grid-template-columns:minmax(330px,.78fr) minmax(480px,1.22fr);gap:16px;align-items:start}.buyer-card{overflow:hidden;border:1px solid #2d3748;border-radius:16px;background:linear-gradient(145deg,rgba(30,41,59,.78),rgba(17,24,39,.97));box-shadow:0 18px 44px rgba(0,0,0,.15)}.buyer-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 19px 15px;border-bottom:1px solid #2d3748}.buyer-card-title{display:flex;gap:11px}.buyer-card-icon{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border-radius:11px;background:rgba(124,58,237,.22);color:#ddd6fe;font-size:17px}.buyer-card-head h3{margin:0;color:#f8fafc;font-size:16px;font-weight:800}.buyer-card-head p{margin:4px 0 0;color:#94a3b8;font-size:11px;line-height:1.45}.buyer-card-body{padding:18px 19px}.buyer-form{display:grid;gap:14px}.buyer-field label{display:block;margin-bottom:6px;color:#cbd5e1;font-size:11px;font-weight:750}.buyer-field select,.buyer-field input{width:100%;min-height:44px!important;border:1px solid #3f4d63!important;border-radius:9px!important;background:#0f172a!important;color:#f8fafc!important;font-size:12px!important;box-shadow:none!important}.buyer-field select:focus,.buyer-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.buyer-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}.buyer-duration{display:grid;grid-template-columns:1fr 1fr;gap:8px}.buyer-duration label{display:flex;min-height:44px;align-items:center;justify-content:center;gap:7px;border:1px solid #3f4d63;border-radius:9px;background:#111827;color:#cbd5e1;font-size:12px;font-weight:750;cursor:pointer}.buyer-duration label:has(input:checked){border-color:#8b5cf6;background:rgba(109,40,217,.24);color:#fff}.buyer-duration input{width:auto;min-height:auto!important}.buyer-actions{display:flex;gap:8px}.buyer-primary,.buyer-danger,.buyer-secondary{min-height:43px;padding:0 15px;border-radius:9px;font-size:12px;font-weight:850}.buyer-primary{flex:1;border:0;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff}.buyer-danger{border:1px solid #ef4444;background:rgba(127,29,29,.3);color:#fecaca}.buyer-secondary{border:1px solid #475569;background:#172033;color:#e2e8f0}.buyer-primary:disabled,.buyer-danger:disabled{cursor:not-allowed;opacity:.48}.buyer-clock{display:grid;min-height:150px;place-items:center;margin-top:16px;border:1px solid #334155;border-radius:14px;background:linear-gradient(145deg,#0f172a,#111827);text-align:center}.buyer-clock.running{border-color:rgba(52,211,153,.38);background:linear-gradient(145deg,rgba(6,78,59,.56),#111827)}.buyer-clock small{display:block;color:#94a3b8;font-size:10px;font-weight:850;letter-spacing:.1em;text-transform:uppercase}.buyer-clock strong{display:block;margin:2px 0;color:#f8fafc;font-size:40px;font-weight:900;letter-spacing:.05em}.buyer-clock span{color:#cbd5e1;font-size:11px}.buyer-feedback{display:none;margin-top:12px;padding:10px 12px;border-radius:9px;font-size:11px;line-height:1.45}.buyer-feedback.success{display:block;background:rgba(6,78,59,.5);color:#a7f3d0}.buyer-feedback.error{display:block;background:rgba(127,29,29,.4);color:#fecaca}.buyer-ranking-toolbar{display:flex;align-items:center;gap:8px}.buyer-ranking-wrap{max-height:460px;overflow:auto}.buyer-table{width:100%;border-collapse:collapse}.buyer-table th{position:sticky;top:0;z-index:1;padding:11px 13px;border-bottom:1px solid #2d3748;background:#111827;color:#94a3b8;font-size:10px;font-weight:850;text-align:left;text-transform:uppercase}.buyer-table td{padding:12px 13px;border-bottom:1px solid rgba(51,65,85,.55);color:#cbd5e1;font-size:12px}.buyer-table tr.target{background:rgba(109,40,217,.18)}.buyer-position{width:44px;color:#c4b5fd!important;font-weight:900}.buyer-name{color:#f8fafc!important;font-weight:750}.buyer-quantity{text-align:right;font-weight:850}.buyer-empty{padding:30px!important;color:#94a3b8!important;text-align:center}.buyer-orders{grid-column:1/-1}.buyer-order-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;max-height:520px;overflow:auto}.buyer-order{display:grid;grid-template-columns:1fr auto;gap:7px 14px;padding:13px 14px;border:1px solid #334155;border-radius:11px;background:#0f172a}.buyer-order strong{color:#f8fafc;font-size:12px}.buyer-order time{color:#94a3b8;font-size:10px}.buyer-order-meta{display:flex;flex-wrap:wrap;gap:6px;grid-column:1/-1}.buyer-chip{padding:4px 7px;border-radius:999px;background:#273449;color:#cbd5e1;font-size:10px;font-weight:750}.buyer-chip.manual{background:rgba(109,40,217,.27);color:#ddd6fe}.buyer-chip.paid{background:rgba(6,78,59,.55);color:#a7f3d0}.buyer-chip.pending{background:rgba(120,53,15,.5);color:#fde68a}.buyer-chip.cancelled{background:rgba(127,29,29,.45);color:#fecaca}.buyer-unavailable{padding:34px;border:1px solid #334155;border-radius:16px;background:#111827;color:#cbd5e1;text-align:center}.buyer-unavailable h3{margin:0 0 7px;color:#f8fafc;font-size:19px}.buyer-unavailable p{max-width:620px;margin:0 auto;color:#94a3b8;font-size:12px;line-height:1.6}@media(max-width:960px){.buyer-grid{grid-template-columns:1fr}.buyer-orders{grid-column:auto}}@media(max-width:650px){.buyer-shell{padding:22px 14px 44px}.buyer-head{align-items:flex-start;flex-direction:column}.buyer-head h2{font-size:25px}.buyer-row,.buyer-order-list{grid-template-columns:1fr}.buyer-actions,.buyer-ranking-toolbar{align-items:stretch;flex-direction:column}.buyer-actions>*{width:100%}}
</style>

<main class="h-full overflow-y-auto">
  <div class="container mx-auto buyer-shell">
    <header class="buyer-head">
      <div><p class="buyer-eyebrow">Ação em tempo real</p><h2>Maior Comprador</h2><p>Mantenha um participante no topo, acompanhe a classificação e veja os pedidos no mesmo lugar.</p></div>
      <span id="buyer-live-badge" class="buyer-live-badge">Aguardando dados</span>
    </header>

    <?php if (!$products): ?>
      <section class="buyer-unavailable"><h3>Área indisponível</h3><p>Ative “Exibir contador” em uma campanha na aba Ranking. A aba Maior Comprador ficará disponível enquanto o contador estiver ligado.</p></section>
    <?php else: ?>
      <div id="buyer-runtime-alert" class="buyer-alert" hidden></div>
      <div class="buyer-grid">
        <section class="buyer-card">
          <header class="buyer-card-head"><div class="buyer-card-title"><span class="buyer-card-icon">⚡</span><div><h3>Ações</h3><p>Configuração da prioridade automática.</p></div></div></header>
          <div class="buyer-card-body">
            <form id="buyer-action-form" class="buyer-form">
              <div class="buyer-field"><label for="buyer-product">Campanha com contador ativo</label><select id="buyer-product" name="product_id"><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= $selectedProductId === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select></div>
              <div class="buyer-field"><label for="buyer-customer">Pessoa mantida em primeiro lugar</label><select id="buyer-customer" name="customer_id" required><option value="">Selecione uma pessoa</option><?php foreach ($customers as $customer): ?><option value="<?= (int) $customer['id'] ?>"><?= $escape(trim($customer['firstname'] . ' ' . $customer['lastname'])) ?><?= trim((string) $customer['phone']) !== '' ? ' — ' . $escape($customer['phone']) : '' ?></option><?php endforeach; ?></select></div>
              <div class="buyer-field"><label>Timer da ação</label><div class="buyer-duration"><label><input type="radio" name="duration_seconds" value="180" checked> 3 minutos</label><label><input type="radio" name="duration_seconds" value="300"> 5 minutos</label></div></div>
              <div class="buyer-row"><div class="buyer-field"><label for="buyer-margin-min">Margem mínima</label><input id="buyer-margin-min" name="margin_min" type="number" min="1" max="100000" value="2" required></div><div class="buyer-field"><label for="buyer-margin-max">Margem máxima</label><input id="buyer-margin-max" name="margin_max" type="number" min="1" max="100000" value="320" required></div></div>
              <div class="buyer-actions"><button id="buyer-start" class="buyer-primary" type="submit">Iniciar prioridade</button><button id="buyer-stop" class="buyer-danger" type="button" disabled>Encerrar</button></div>
            </form>
            <div id="buyer-clock" class="buyer-clock"><div><small id="buyer-clock-label">Automação inativa</small><strong id="buyer-clock-time">00:00</strong><span id="buyer-clock-person">Nenhuma pessoa selecionada</span></div></div>
            <div id="buyer-feedback" class="buyer-feedback" role="status" aria-live="polite"></div>
          </div>
        </section>

        <section class="buyer-card">
          <header class="buyer-card-head"><div class="buyer-card-title"><span class="buyer-card-icon">🏆</span><div><h3>Ranking</h3><p>Atualização automática a cada segundo.</p></div></div><div class="buyer-ranking-toolbar"><button id="buyer-open-ranking" class="buyer-secondary" type="button">Abrir Ranking</button></div></header>
          <div class="buyer-ranking-wrap"><table class="buyer-table"><thead><tr><th>Pos.</th><th>Participante</th><th style="text-align:right">Cotas</th></tr></thead><tbody id="buyer-ranking-body"><tr><td colspan="3" class="buyer-empty">Carregando ranking...</td></tr></tbody></table></div>
        </section>

        <section class="buyer-card buyer-orders">
          <header class="buyer-card-head"><div class="buyer-card-title"><span class="buyer-card-icon">🧾</span><div><h3>Pedidos</h3><p>VenoPag e movimentações manuais da ação.</p></div></div></header>
          <div class="buyer-card-body"><div id="buyer-order-list" class="buyer-order-list"><div class="buyer-empty">Carregando pedidos...</div></div></div>
        </section>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php if ($products): ?>
<script>
(function () {
  var endpoint = _base_url_ + 'class/Main.php?action=';
  var csrfToken = <?= json_encode($csrfToken) ?>;
  var product = document.getElementById('buyer-product');
  var customer = document.getElementById('buyer-customer');
  var form = document.getElementById('buyer-action-form');
  var startButton = document.getElementById('buyer-start');
  var stopButton = document.getElementById('buyer-stop');
  var feedback = document.getElementById('buyer-feedback');
  var liveBadge = document.getElementById('buyer-live-badge');
  var clock = document.getElementById('buyer-clock');
  var clockLabel = document.getElementById('buyer-clock-label');
  var clockTime = document.getElementById('buyer-clock-time');
  var clockPerson = document.getElementById('buyer-clock-person');
  var rankingBody = document.getElementById('buyer-ranking-body');
  var orderList = document.getElementById('buyer-order-list');
  var runtimeAlert = document.getElementById('buyer-runtime-alert');
  var pollBusy = false;
  var remainingSeconds = 0;
  var lastState = null;

  function money(value) { return Number(value || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'}); }
  function integer(value) { return Number(value || 0).toLocaleString('pt-BR'); }
  function formatClock(value) { value = Math.max(0, Number(value || 0)); var m = Math.floor(value / 60); var s = value % 60; return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0'); }
  function showFeedback(message, type) { feedback.textContent = message || ''; feedback.className = 'buyer-feedback' + (message ? ' ' + type : ''); }
  function post(action, data) {
    data = data || new FormData();
    data.set('csrf_token', csrfToken);
    return fetch(endpoint + action, {method:'POST', body:data, credentials:'same-origin'}).then(function (response) { return response.json(); });
  }
  function statusLabel(status) { return status === 2 ? 'Pago' : (status === 1 ? 'Aguardando pagamento' : 'Cancelado'); }
  function renderRanking(rows, targetId) {
    rankingBody.innerHTML = '';
    if (!rows || !rows.length) { var empty = document.createElement('tr'); empty.innerHTML = '<td colspan="3" class="buyer-empty">Ainda não há participantes neste período.</td>'; rankingBody.appendChild(empty); return; }
    rows.forEach(function (row, index) {
      var tr = document.createElement('tr');
      if (Number(row.customer_id) === Number(targetId)) tr.className = 'target';
      var pos = document.createElement('td'); pos.className = 'buyer-position'; pos.textContent = (index + 1) + 'º';
      var name = document.createElement('td'); name.className = 'buyer-name'; name.textContent = row.name;
      var quantity = document.createElement('td'); quantity.className = 'buyer-quantity'; quantity.textContent = integer(row.quantity);
      tr.append(pos, name, quantity); rankingBody.appendChild(tr);
    });
  }
  function renderOrders(rows) {
    orderList.innerHTML = '';
    if (!rows || !rows.length) { var empty = document.createElement('div'); empty.className = 'buyer-empty'; empty.textContent = 'Nenhum pedido neste período.'; orderList.appendChild(empty); return; }
    rows.forEach(function (row) {
      var item = document.createElement('article'); item.className = 'buyer-order';
      var name = document.createElement('strong'); name.textContent = row.name;
      var time = document.createElement('time'); time.textContent = String(row.created_at || '').slice(11,19);
      var meta = document.createElement('div'); meta.className = 'buyer-order-meta';
      var amount = document.createElement('span'); amount.className = 'buyer-chip'; amount.textContent = money(row.amount);
      var quantity = document.createElement('span'); quantity.className = 'buyer-chip'; quantity.textContent = integer(row.quantity) + ' cotas';
      var origin = document.createElement('span'); origin.className = 'buyer-chip' + (row.origin === 'Manual' ? ' manual' : ''); origin.textContent = row.origin;
      var status = document.createElement('span'); status.className = 'buyer-chip ' + (row.status === 2 ? 'paid' : (row.status === 1 ? 'pending' : 'cancelled')); status.textContent = statusLabel(Number(row.status));
      meta.append(amount, quantity, origin, status); item.append(name, time, meta); orderList.appendChild(item);
    });
  }
  function render(state) {
    if (!state || state.status !== 'success') { showFeedback((state && state.msg) || 'Não foi possível atualizar a tela.', 'error'); return; }
    lastState = state;
    var action = state.action || {};
    var active = action.status === 'active';
    remainingSeconds = Number(action.remaining_seconds || 0);
    liveBadge.textContent = state.timer_state === 'running' ? 'Contador em andamento' : 'Contador ' + String(state.timer_state || 'indisponível');
    liveBadge.className = 'buyer-live-badge' + (state.timer_state === 'running' ? ' running' : '');
    if (state.product && !active) product.value = String(state.product.id);
    if (active) { product.value = String(action.product_id); customer.value = String(action.customer_id); }
    product.disabled = active; customer.disabled = active;
    form.querySelectorAll('input').forEach(function (input) { input.disabled = active; });
    startButton.disabled = active || !state.can_start;
    stopButton.disabled = !active;
    clock.className = 'buyer-clock' + (active ? ' running' : '');
    clockLabel.textContent = active ? 'Prioridade automática ativa' : (action.status === 'expired' ? 'Timer encerrado' : 'Automação inativa');
    clockTime.textContent = formatClock(remainingSeconds);
    clockPerson.textContent = active ? (action.customer_name || 'Participante selecionado') : 'Nenhuma prioridade automática';
    runtimeAlert.hidden = state.available;
    runtimeAlert.textContent = state.available ? '' : 'A opção “Exibir contador” foi desativada. Esta área está indisponível.';
    if (action.last_error) showFeedback(action.last_error, 'error');
    else if (action.last_order_id) showFeedback('Última movimentação: ' + integer(action.last_added_quantity) + ' cotas, com margem de ' + integer(action.last_margin) + '. Pedido manual #' + action.last_order_id + '.', 'success');
    renderRanking(state.ranking || [], action.customer_id || 0);
    renderOrders(state.orders || []);
  }
  function poll() {
    if (pollBusy) return;
    pollBusy = true;
    var data = new FormData(); data.set('product_id', product.value || '0');
    post('biggest_buyer_tick', data).then(render).catch(function () { showFeedback('Falha ao atualizar em tempo real.', 'error'); }).finally(function () { pollBusy = false; });
  }
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var data = new FormData(form);
    startButton.disabled = true;
    post('biggest_buyer_start', data).then(function (state) { if (state.status !== 'success') throw new Error(state.msg || 'Não foi possível iniciar.'); showFeedback('Prioridade automática iniciada.', 'success'); render(state); }).catch(function (error) { showFeedback(error.message, 'error'); startButton.disabled = false; });
  });
  stopButton.addEventListener('click', function () {
    var data = new FormData(); data.set('product_id', product.value || '0');
    post('biggest_buyer_stop', data).then(function (state) { showFeedback('Prioridade automática encerrada.', 'success'); render(state); }).catch(function () { showFeedback('Não foi possível encerrar a ação.', 'error'); });
  });
  product.addEventListener('change', function () {
    fetch(endpoint + 'biggest_buyer_state&product_id=' + encodeURIComponent(product.value), {credentials:'same-origin'}).then(function (response) { return response.json(); }).then(render);
  });
  document.getElementById('buyer-open-ranking').addEventListener('click', function () {
    window.open('biggest_buyer/display.php?product_id=' + encodeURIComponent(product.value), 'jnsalles-ranking', 'popup=yes,width=980,height=760,resizable=yes,scrollbars=yes');
  });
  window.setInterval(function () { if (remainingSeconds > 0) { remainingSeconds -= 1; clockTime.textContent = formatClock(remainingSeconds); } }, 1000);
  poll(); window.setInterval(poll, 1000);
})();
</script>
<?php endif; ?>
