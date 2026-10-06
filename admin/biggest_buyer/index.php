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
    $customers[] = [
        'id' => (int) $customer['id'],
        'name' => trim((string) $customer['firstname'] . ' ' . (string) $customer['lastname']),
        'phone' => (string) $customer['phone'],
    ];
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
.buyer-shell{max-width:1380px;padding:32px 26px 60px;color:#e5edf8}.buyer-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:18px}.buyer-eyebrow{margin:0 0 6px;color:#a78bfa;font-size:11px;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.buyer-head h2{margin:0;color:#fff;font-size:32px;font-weight:900;letter-spacing:-.045em}.buyer-head p{max-width:680px;margin:7px 0 0;color:#91a2ba;font-size:13px;line-height:1.55}.buyer-live-badge{display:flex;min-width:150px;align-items:center;justify-content:center;gap:9px;padding:10px 14px;border:1px solid #34425a;border-radius:999px;background:#111a2a;color:#cbd5e1;font-size:11px;font-weight:850}.buyer-live-badge:before{width:8px;height:8px;border-radius:50%;background:#64748b;content:""}.buyer-live-badge.running:before{background:#34d399;box-shadow:0 0 0 5px rgba(52,211,153,.12)}
.buyer-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:16px}.buyer-overview-item{min-width:0;padding:13px 15px;border:1px solid #2d3a50;border-radius:13px;background:rgba(17,26,42,.78)}.buyer-overview-item small{display:block;margin-bottom:4px;color:#71839d;font-size:9px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.buyer-overview-item strong{display:block;overflow:hidden;color:#f8fafc;font-size:13px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.buyer-overview-item strong.accent{color:#c4b5fd}.buyer-alert{margin-bottom:16px;padding:13px 15px;border:1px solid #92400e;border-radius:12px;background:rgba(120,53,15,.25);color:#fde68a;font-size:12px;line-height:1.55}
.buyer-grid{display:grid;grid-template-columns:minmax(430px,1.14fr) minmax(360px,.86fr);gap:16px;align-items:start}.buyer-card{overflow:visible;border:1px solid #2d3a50;border-radius:18px;background:linear-gradient(145deg,rgba(28,39,57,.94),rgba(15,23,42,.98));box-shadow:0 20px 48px rgba(0,0,0,.16)}.buyer-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 20px;border-bottom:1px solid #2b384d}.buyer-card-title{display:flex;align-items:center;gap:12px}.buyer-card-icon{display:grid;width:40px;height:40px;flex:0 0 40px;place-items:center;border-radius:12px;background:linear-gradient(145deg,rgba(124,58,237,.38),rgba(91,33,182,.2));color:#ddd6fe;font-size:17px}.buyer-card-head h3{margin:0;color:#f8fafc;font-size:17px;font-weight:850}.buyer-card-head p{margin:4px 0 0;color:#8da0ba;font-size:11px;line-height:1.4}.buyer-card-body{padding:20px}.buyer-form{display:grid;gap:16px}.buyer-field{min-width:0}.buyer-field>label,.buyer-field-title{display:block;margin:0 0 7px;color:#cbd5e1;font-size:11px;font-weight:800}.buyer-field select,.buyer-field input[type=number],.buyer-customer-input{width:100%;min-width:0;height:46px!important;padding:0 14px!important;border:1px solid #40506a!important;border-radius:10px!important;background:#0c1526!important;color:#f8fafc!important;font-size:12px!important;line-height:46px!important;box-shadow:none!important;outline:0}.buyer-field input[type=number]{appearance:textfield;-moz-appearance:textfield}.buyer-field input[type=number]::-webkit-inner-spin-button,.buyer-field input[type=number]::-webkit-outer-spin-button{margin:0;opacity:.7}.buyer-field select:focus,.buyer-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.buyer-field input:disabled,.buyer-field select:disabled{cursor:not-allowed;opacity:.62}.buyer-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px}
.buyer-customer-picker{position:relative}.buyer-customer-control{position:relative}.buyer-customer-input{padding-right:76px!important}.buyer-customer-input.selected{border-color:#7c3aed!important;background:rgba(76,29,149,.18)!important}.buyer-search-icon{position:absolute;top:50%;right:14px;color:#71839d;transform:translateY(-50%);pointer-events:none}.buyer-customer-clear{position:absolute;top:50%;right:38px;width:25px;height:25px;border:0;border-radius:7px;background:#28364b;color:#cbd5e1;font-size:14px;transform:translateY(-50%)}.buyer-suggestions{position:absolute;z-index:30;top:calc(100% + 7px);right:0;left:0;overflow:auto;max-height:265px;padding:6px;border:1px solid #465570;border-radius:12px;background:#0b1322;box-shadow:0 20px 50px rgba(0,0,0,.48)}.buyer-suggestion{display:flex;width:100%;align-items:center;justify-content:space-between;gap:12px;padding:10px 11px;border:0;border-radius:8px;background:transparent;text-align:left}.buyer-suggestion:hover,.buyer-suggestion.active{background:#1b2940}.buyer-suggestion strong{display:block;color:#f8fafc;font-size:12px}.buyer-suggestion span{display:block;margin-top:3px;color:#71839d;font-size:10px}.buyer-suggestion em{color:#a78bfa;font-size:10px;font-style:normal;font-weight:800}.buyer-selected-person{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:8px;padding:9px 11px;border:1px solid rgba(139,92,246,.38);border-radius:10px;background:rgba(91,33,182,.13)}.buyer-selected-person small{color:#8da0ba;font-size:9px;font-weight:850;text-transform:uppercase}.buyer-selected-person strong{display:block;margin-top:2px;color:#e9e5ff;font-size:11px}
.buyer-duration{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:7px}.buyer-duration label{display:flex;height:43px;align-items:center;justify-content:center;border:1px solid #40506a;border-radius:10px;background:#101a2b;color:#aab8cb;font-size:11px;font-weight:850;cursor:pointer}.buyer-duration label:has(input:checked){border-color:#8b5cf6;background:linear-gradient(145deg,rgba(124,58,237,.38),rgba(91,33,182,.25));color:#fff;box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}.buyer-duration input{position:absolute;opacity:0;pointer-events:none}.buyer-time-preview{display:grid;grid-template-columns:1fr 1fr;gap:9px}.buyer-time-box{padding:11px 13px;border:1px solid #2f3d53;border-radius:11px;background:#10192a}.buyer-time-box small{display:block;color:#71839d;font-size:9px;font-weight:850;text-transform:uppercase}.buyer-time-box strong{display:block;margin-top:4px;color:#f8fafc;font-size:16px;font-weight:900;letter-spacing:.03em}.buyer-time-box:last-child strong{color:#c4b5fd}.buyer-actions{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(105px,.7fr) minmax(92px,.55fr);gap:8px}.buyer-primary,.buyer-pass,.buyer-danger,.buyer-secondary{min-height:44px;padding:0 15px;border-radius:10px;font-size:11px;font-weight:900;transition:transform .16s ease,border-color .16s ease,opacity .16s ease}.buyer-primary{border:0;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff}.buyer-pass{border:1px solid #0ea5e9;background:rgba(3,105,161,.23);color:#bae6fd}.buyer-danger{border:1px solid #ef4444;background:rgba(127,29,29,.25);color:#fecaca}.buyer-secondary{border:1px solid #475569;background:#172033;color:#e2e8f0}.buyer-primary:not(:disabled):hover,.buyer-pass:not(:disabled):hover,.buyer-danger:not(:disabled):hover,.buyer-secondary:hover{transform:translateY(-1px)}.buyer-primary:disabled,.buyer-pass:disabled,.buyer-danger:disabled{cursor:not-allowed;opacity:.42}
.buyer-clock{position:relative;display:grid;min-height:188px;place-items:center;overflow:hidden;margin-top:18px;border:1px solid #33435b;border-radius:16px;background:radial-gradient(circle at 50% 0,rgba(124,58,237,.15),transparent 55%),#0b1424;text-align:center}.buyer-clock:after{position:absolute;right:20%;bottom:-45px;left:20%;height:80px;border-radius:50%;background:rgba(124,58,237,.16);filter:blur(26px);content:""}.buyer-clock.running{border-color:rgba(52,211,153,.42);background:radial-gradient(circle at 50% 0,rgba(16,185,129,.19),transparent 56%),#0b1424}.buyer-clock>div{position:relative;z-index:1;padding:22px}.buyer-clock small{display:block;color:#8da0ba;font-size:10px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.buyer-clock strong{display:block;margin:3px 0 4px;color:#fff;font-size:52px;font-weight:950;letter-spacing:.055em;line-height:1.1;text-shadow:0 6px 28px rgba(0,0,0,.32)}.buyer-clock-person{display:block;color:#dbe5f3;font-size:12px;font-weight:750}.buyer-clock-end{display:block;margin-top:8px;color:#a78bfa!important;font-size:11px!important;font-weight:800}.buyer-feedback{display:none;margin-top:12px;padding:11px 13px;border-radius:10px;font-size:11px;line-height:1.5}.buyer-feedback.success{display:block;border:1px solid rgba(52,211,153,.22);background:rgba(6,78,59,.42);color:#a7f3d0}.buyer-feedback.error{display:block;border:1px solid rgba(248,113,113,.22);background:rgba(127,29,29,.36);color:#fecaca}
.buyer-ranking-toolbar{display:flex;align-items:center;gap:8px}.buyer-ranking-list{display:grid;gap:8px;padding:14px}.buyer-rank{display:grid;grid-template-columns:45px minmax(0,1fr) auto;align-items:center;gap:10px;padding:12px 13px;border:1px solid #2f3d53;border-radius:12px;background:#0d1728;transition:background .2s ease,border-color .2s ease}.buyer-rank:first-child{padding-top:14px;padding-bottom:14px;border-color:rgba(245,158,11,.35);background:linear-gradient(90deg,rgba(120,53,15,.2),#0d1728)}.buyer-rank.target{border-color:rgba(139,92,246,.55);background:linear-gradient(90deg,rgba(91,33,182,.25),#0d1728)}.buyer-rank-position{display:grid;width:38px;height:38px;place-items:center;border-radius:10px;background:#19263a;color:#c4b5fd;font-size:13px;font-weight:950}.buyer-rank:first-child .buyer-rank-position{background:rgba(180,83,9,.26);color:#fde68a}.buyer-rank-name{overflow:hidden;color:#f8fafc;font-size:12px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.buyer-rank-name small{display:block;margin-top:3px;color:#6f829d;font-size:9px;font-weight:700}.buyer-rank-quantity{color:#dbe5f3;font-size:13px;font-weight:900;text-align:right}.buyer-rank-quantity small{display:block;margin-top:2px;color:#6f829d;font-size:8px;text-transform:uppercase}.buyer-empty{padding:34px!important;color:#8394ad!important;text-align:center;font-size:12px}.buyer-orders{grid-column:1/-1}.buyer-order-list{display:grid;gap:8px}.buyer-order{display:grid;grid-template-columns:minmax(180px,1.4fr) 90px 110px 100px 110px;align-items:center;gap:12px;padding:12px 14px;border:1px solid #2f3d53;border-radius:12px;background:#0d1728}.buyer-order-person{min-width:0}.buyer-order-person strong{display:block;overflow:hidden;color:#f8fafc;font-size:12px;text-overflow:ellipsis;white-space:nowrap}.buyer-order-person time{display:block;margin-top:3px;color:#71839d;font-size:10px}.buyer-order-cell small{display:block;margin-bottom:3px;color:#657892;font-size:8px;font-weight:850;text-transform:uppercase}.buyer-order-cell strong{color:#cbd5e1;font-size:11px}.buyer-chip{display:inline-flex;padding:5px 8px;border-radius:999px;background:#273449;color:#cbd5e1;font-size:9px;font-weight:850}.buyer-chip.manual{background:rgba(109,40,217,.27);color:#ddd6fe}.buyer-chip.paid{background:rgba(6,78,59,.55);color:#a7f3d0}.buyer-chip.pending{background:rgba(120,53,15,.5);color:#fde68a}.buyer-chip.cancelled{background:rgba(127,29,29,.45);color:#fecaca}.buyer-unavailable{padding:38px;border:1px solid #334155;border-radius:17px;background:#111827;color:#cbd5e1;text-align:center}.buyer-unavailable h3{margin:0 0 7px;color:#f8fafc;font-size:19px}.buyer-unavailable p{max-width:620px;margin:0 auto;color:#94a3b8;font-size:12px;line-height:1.6}
@media(max-width:1080px){.buyer-grid{grid-template-columns:1fr}.buyer-orders{grid-column:auto}}@media(max-width:760px){.buyer-shell{padding:24px 14px 46px}.buyer-head{align-items:flex-start;flex-direction:column}.buyer-head h2{font-size:27px}.buyer-overview{grid-template-columns:1fr}.buyer-row,.buyer-time-preview{grid-template-columns:1fr}.buyer-duration{grid-template-columns:repeat(5,minmax(48px,1fr))}.buyer-actions{grid-template-columns:1fr 1fr}.buyer-actions .buyer-danger{grid-column:1/-1}.buyer-order{grid-template-columns:1fr 1fr;gap:10px}.buyer-order-person{grid-column:1/-1}.buyer-clock strong{font-size:44px}}@media(max-width:430px){.buyer-card-body,.buyer-card-head{padding-right:14px;padding-left:14px}.buyer-duration{gap:5px}.buyer-duration label{height:41px;font-size:10px}.buyer-actions{grid-template-columns:1fr}.buyer-actions .buyer-danger{grid-column:auto}.buyer-order{grid-template-columns:1fr}.buyer-order-person{grid-column:auto}.buyer-live-badge{min-width:0}.buyer-ranking-toolbar{align-items:stretch;flex-direction:column}}
</style>

<main class="h-full overflow-y-auto">
  <div class="container mx-auto buyer-shell">
    <header class="buyer-head">
      <div><p class="buyer-eyebrow">Central da ação ao vivo</p><h2>Maior Comprador</h2><p>Controle a prioridade, acompanhe o Top 5 e veja as últimas movimentações sem sair desta tela.</p></div>
      <span id="buyer-live-badge" class="buyer-live-badge">Aguardando dados</span>
    </header>

    <?php if (!$products): ?>
      <section class="buyer-unavailable"><h3>Área indisponível</h3><p>Ative “Exibir contador” em uma campanha na aba Ranking. A aba Maior Comprador ficará disponível enquanto o contador estiver ligado.</p></section>
    <?php else: ?>
      <section class="buyer-overview" aria-label="Resumo da ação">
        <div class="buyer-overview-item"><small>Horário de Brasília</small><strong id="buyer-current-time">--:--:--</strong></div>
        <div class="buyer-overview-item"><small>Encerramento previsto</small><strong id="buyer-forecast-time" class="accent">--:--:--</strong></div>
        <div class="buyer-overview-item"><small>Pessoa selecionada</small><strong id="buyer-overview-person">Nenhuma pessoa</strong></div>
      </section>
      <div id="buyer-runtime-alert" class="buyer-alert" hidden></div>
      <div class="buyer-grid">
        <section class="buyer-card">
          <header class="buyer-card-head"><div class="buyer-card-title"><span class="buyer-card-icon"><i class="fa-solid fa-bolt"></i></span><div><h3>Ações</h3><p>Configure a prioridade automática ou faça uma passagem manual.</p></div></div></header>
          <div class="buyer-card-body">
            <form id="buyer-action-form" class="buyer-form">
              <div class="buyer-field"><label for="buyer-product">Campanha com contador ativo</label><select id="buyer-product" name="product_id"><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= $selectedProductId === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select></div>
              <div class="buyer-field buyer-customer-picker">
                <label for="buyer-customer-search">Pessoa mantida em primeiro lugar</label>
                <input id="buyer-customer-id" name="customer_id" type="hidden" value="">
                <div class="buyer-customer-control"><input id="buyer-customer-search" class="buyer-customer-input" type="search" autocomplete="off" placeholder="Digite o nome ou telefone..." aria-autocomplete="list" aria-controls="buyer-customer-suggestions"><button id="buyer-customer-clear" class="buyer-customer-clear" type="button" aria-label="Limpar pessoa" hidden>×</button><i class="fa-solid fa-magnifying-glass buyer-search-icon" aria-hidden="true"></i></div>
                <div id="buyer-customer-suggestions" class="buyer-suggestions" role="listbox" hidden></div>
                <div id="buyer-selected-person" class="buyer-selected-person" hidden><div><small>Pessoa escolhida</small><strong id="buyer-selected-person-name"></strong></div><i class="fa-solid fa-circle-check" style="color:#a78bfa"></i></div>
              </div>
              <div class="buyer-field"><span class="buyer-field-title">Duração da prioridade</span><div class="buyer-duration"><?php foreach ([1, 2, 3, 4, 5] as $minute): ?><label><input type="radio" name="duration_seconds" value="<?= $minute * 60 ?>" <?= $minute === 3 ? 'checked' : '' ?>><?= $minute ?> min</label><?php endforeach; ?></div></div>
              <div class="buyer-time-preview"><div class="buyer-time-box"><small>Agora em Brasília</small><strong id="buyer-form-current-time">--:--:--</strong></div><div class="buyer-time-box"><small>Encerra às</small><strong id="buyer-form-end-time">--:--:--</strong></div></div>
              <div class="buyer-row"><div class="buyer-field"><label for="buyer-margin-min">Margem mínima</label><input id="buyer-margin-min" name="margin_min" type="number" inputmode="numeric" min="1" max="100000" value="2" required></div><div class="buyer-field"><label for="buyer-margin-max">Margem máxima</label><input id="buyer-margin-max" name="margin_max" type="number" inputmode="numeric" min="1" max="100000" value="320" required></div></div>
              <div class="buyer-actions"><button id="buyer-start" class="buyer-primary" type="submit">Iniciar prioridade</button><button id="buyer-pass" class="buyer-pass" type="button">Passar</button><button id="buyer-stop" class="buyer-danger" type="button" disabled>Encerrar</button></div>
            </form>
            <div id="buyer-clock" class="buyer-clock"><div><small id="buyer-clock-label">Automação inativa</small><strong id="buyer-clock-time">00:00</strong><span id="buyer-clock-person" class="buyer-clock-person">Nenhuma pessoa selecionada</span><span id="buyer-clock-end" class="buyer-clock-end">Selecione a duração para ver o encerramento</span></div></div>
            <div id="buyer-feedback" class="buyer-feedback" role="status" aria-live="polite"></div>
          </div>
        </section>

        <section class="buyer-card">
          <header class="buyer-card-head"><div class="buyer-card-title"><span class="buyer-card-icon"><i class="fa-solid fa-ranking-star"></i></span><div><h3>Top 5</h3><p>Classificação atualizada a cada segundo.</p></div></div><div class="buyer-ranking-toolbar"><button id="buyer-open-ranking" class="buyer-secondary" type="button">Abrir Ranking</button></div></header>
          <div id="buyer-ranking-list" class="buyer-ranking-list"><div class="buyer-empty">Carregando ranking...</div></div>
        </section>

        <section class="buyer-card buyer-orders">
          <header class="buyer-card-head"><div class="buyer-card-title"><span class="buyer-card-icon"><i class="fa-solid fa-receipt"></i></span><div><h3>Últimos pedidos</h3><p>As 5 movimentações mais recentes, da mais nova para a mais antiga.</p></div></div></header>
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
  var customers = <?= json_encode($customers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var product = document.getElementById('buyer-product');
  var customerId = document.getElementById('buyer-customer-id');
  var customerSearch = document.getElementById('buyer-customer-search');
  var customerClear = document.getElementById('buyer-customer-clear');
  var suggestions = document.getElementById('buyer-customer-suggestions');
  var selectedPerson = document.getElementById('buyer-selected-person');
  var selectedPersonName = document.getElementById('buyer-selected-person-name');
  var overviewPerson = document.getElementById('buyer-overview-person');
  var form = document.getElementById('buyer-action-form');
  var startButton = document.getElementById('buyer-start');
  var passButton = document.getElementById('buyer-pass');
  var stopButton = document.getElementById('buyer-stop');
  var feedback = document.getElementById('buyer-feedback');
  var liveBadge = document.getElementById('buyer-live-badge');
  var clock = document.getElementById('buyer-clock');
  var clockLabel = document.getElementById('buyer-clock-label');
  var clockTime = document.getElementById('buyer-clock-time');
  var clockPerson = document.getElementById('buyer-clock-person');
  var clockEnd = document.getElementById('buyer-clock-end');
  var currentTime = document.getElementById('buyer-current-time');
  var forecastTime = document.getElementById('buyer-forecast-time');
  var formCurrentTime = document.getElementById('buyer-form-current-time');
  var formEndTime = document.getElementById('buyer-form-end-time');
  var rankingList = document.getElementById('buyer-ranking-list');
  var orderList = document.getElementById('buyer-order-list');
  var runtimeAlert = document.getElementById('buyer-runtime-alert');
  var pollBusy = false;
  var remainingSeconds = 0;
  var lastState = null;
  var serverEpoch = Date.now();
  var serverSyncedAt = Date.now();
  var activeSuggestion = -1;

  function normalize(value) { return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim(); }
  function money(value) { return Number(value || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'}); }
  function integer(value) { return Number(value || 0).toLocaleString('pt-BR'); }
  function formatClock(value) { value = Math.max(0, Number(value || 0)); var m = Math.floor(value / 60); var s = value % 60; return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0'); }
  function formatTime(date) { return new Intl.DateTimeFormat('pt-BR', {timeZone:'America/Sao_Paulo',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(date); }
  function parseBrasilia(value) { return value ? new Date(String(value).replace(' ', 'T') + '-03:00') : null; }
  function selectedDuration() { var checked = form.querySelector('input[name="duration_seconds"]:checked'); return Number(checked ? checked.value : 180); }
  function nowDate() { return new Date(serverEpoch + (Date.now() - serverSyncedAt)); }
  function showFeedback(message, type) { feedback.textContent = message || ''; feedback.className = 'buyer-feedback' + (message ? ' ' + type : ''); }
  function post(action, data) { data = data || new FormData(); data.set('csrf_token', csrfToken); return fetch(endpoint + action, {method:'POST', body:data, credentials:'same-origin'}).then(function (response) { return response.json(); }); }
  function statusLabel(status) { return status === 2 ? 'Pago' : (status === 1 ? 'Aguardando' : 'Cancelado'); }
  function findCustomer(id) { return customers.find(function (item) { return Number(item.id) === Number(id); }); }

  function setCustomer(id, fallbackName) {
    var found = findCustomer(id);
    var name = found ? found.name : (fallbackName || '');
    customerId.value = id ? String(id) : '';
    customerSearch.value = name;
    customerSearch.classList.toggle('selected', Boolean(id));
    customerClear.hidden = !id;
    selectedPerson.hidden = !id;
    selectedPersonName.textContent = name;
    overviewPerson.textContent = name || 'Nenhuma pessoa';
    suggestions.hidden = true;
    updateButtons();
  }
  function renderSuggestions(query) {
    var term = normalize(query);
    var matches = customers.filter(function (item) { return normalize(item.name + ' ' + item.phone).includes(term); }).slice(0, 8);
    suggestions.innerHTML = '';
    activeSuggestion = -1;
    if (!matches.length) { var empty = document.createElement('div'); empty.className = 'buyer-empty'; empty.textContent = 'Nenhuma pessoa encontrada.'; suggestions.appendChild(empty); suggestions.hidden = false; return; }
    matches.forEach(function (item) {
      var button = document.createElement('button'); button.type = 'button'; button.className = 'buyer-suggestion'; button.setAttribute('role', 'option');
      var identity = document.createElement('div'); var name = document.createElement('strong'); name.textContent = item.name; var phone = document.createElement('span'); phone.textContent = item.phone || 'Telefone não informado'; identity.append(name, phone);
      var choose = document.createElement('em'); choose.textContent = Number(customerId.value) === Number(item.id) ? 'Selecionada' : 'Escolher';
      button.append(identity, choose); button.addEventListener('click', function () { setCustomer(item.id, item.name); }); suggestions.appendChild(button);
    });
    suggestions.hidden = false;
  }
  function updateButtons() {
    var active = Boolean(lastState && lastState.action && lastState.action.status === 'active');
    var ready = Boolean(lastState && lastState.can_start && customerId.value);
    startButton.disabled = active || !ready;
    passButton.disabled = active || !ready;
    stopButton.disabled = !active;
  }
  function updateTimes() {
    var now = nowDate();
    var action = lastState && lastState.action ? lastState.action : {};
    var active = action.status === 'active';
    var end = active ? parseBrasilia(action.expires_at) : new Date(now.getTime() + selectedDuration() * 1000);
    var nowText = formatTime(now);
    var endText = end ? formatTime(end) : '--:--:--';
    currentTime.textContent = nowText; formCurrentTime.textContent = nowText; forecastTime.textContent = endText; formEndTime.textContent = endText;
    clockEnd.textContent = active ? 'Encerra às ' + endText + ' — Horário de Brasília' : 'Previsão: ' + endText + ' — Horário de Brasília';
  }
  function renderRanking(rows, targetId) {
    rankingList.innerHTML = '';
    rows = (rows || []).slice(0, 5);
    if (!rows.length) { var empty = document.createElement('div'); empty.className = 'buyer-empty'; empty.textContent = 'Ainda não há participantes neste período.'; rankingList.appendChild(empty); return; }
    rows.forEach(function (row, index) {
      var item = document.createElement('article'); item.className = 'buyer-rank' + (Number(row.customer_id) === Number(targetId) ? ' target' : '');
      var position = document.createElement('span'); position.className = 'buyer-rank-position'; position.textContent = (index + 1) + 'º';
      var name = document.createElement('div'); name.className = 'buyer-rank-name'; name.textContent = row.name; var subtitle = document.createElement('small'); subtitle.textContent = index === 0 ? 'Líder atual' : 'Classificação ao vivo'; name.appendChild(subtitle);
      var quantity = document.createElement('div'); quantity.className = 'buyer-rank-quantity'; quantity.textContent = integer(row.quantity); var unit = document.createElement('small'); unit.textContent = 'cotas'; quantity.appendChild(unit);
      item.append(position, name, quantity); rankingList.appendChild(item);
    });
  }
  function renderOrders(rows) {
    orderList.innerHTML = '';
    rows = (rows || []).slice(0, 5);
    if (!rows.length) { var empty = document.createElement('div'); empty.className = 'buyer-empty'; empty.textContent = 'Nenhum pedido neste período.'; orderList.appendChild(empty); return; }
    rows.forEach(function (row) {
      var item = document.createElement('article'); item.className = 'buyer-order';
      var person = document.createElement('div'); person.className = 'buyer-order-person'; var name = document.createElement('strong'); name.textContent = row.name; var time = document.createElement('time'); time.textContent = String(row.created_at || '').slice(11,19) + ' — horário de criação'; person.append(name, time);
      function cell(label, value) { var box = document.createElement('div'); box.className = 'buyer-order-cell'; var small = document.createElement('small'); small.textContent = label; var strong = document.createElement('strong'); strong.textContent = value; box.append(small, strong); return box; }
      var amount = cell('Valor', money(row.amount)); var quantity = cell('Cotas', integer(row.quantity));
      var origin = document.createElement('div'); origin.className = 'buyer-order-cell'; origin.innerHTML = '<small>Origem</small>'; var originChip = document.createElement('span'); originChip.className = 'buyer-chip' + (row.origin === 'Manual' ? ' manual' : ''); originChip.textContent = row.origin; origin.appendChild(originChip);
      var status = document.createElement('div'); status.className = 'buyer-order-cell'; status.innerHTML = '<small>Status</small>'; var statusChip = document.createElement('span'); statusChip.className = 'buyer-chip ' + (Number(row.status) === 2 ? 'paid' : (Number(row.status) === 1 ? 'pending' : 'cancelled')); statusChip.textContent = statusLabel(Number(row.status)); status.appendChild(statusChip);
      item.append(person, amount, quantity, origin, status); orderList.appendChild(item);
    });
  }
  function render(state) {
    if (!state || state.status !== 'success') { showFeedback((state && state.msg) || 'Não foi possível atualizar a tela.', 'error'); return; }
    lastState = state;
    if (state.server_time) { var parsed = parseBrasilia(state.server_time); if (parsed && !isNaN(parsed.getTime())) { serverEpoch = parsed.getTime(); serverSyncedAt = Date.now(); } }
    var action = state.action || {}; var active = action.status === 'active'; remainingSeconds = Number(action.remaining_seconds || 0);
    liveBadge.textContent = state.timer_state === 'running' ? 'Contador em andamento' : 'Contador ' + String(state.timer_state || 'indisponível'); liveBadge.className = 'buyer-live-badge' + (state.timer_state === 'running' ? ' running' : '');
    if (state.product && !active) product.value = String(state.product.id);
    if (active) { product.value = String(action.product_id); if (Number(customerId.value) !== Number(action.customer_id)) setCustomer(action.customer_id, action.customer_name); }
    product.disabled = active; customerSearch.disabled = active; customerClear.disabled = active;
    form.querySelectorAll('input[type="radio"],input[type="number"]').forEach(function (input) { input.disabled = active; });
    clock.className = 'buyer-clock' + (active ? ' running' : ''); clockLabel.textContent = active ? 'Prioridade automática ativa' : (action.status === 'expired' ? 'Timer encerrado' : 'Automação inativa'); clockTime.textContent = formatClock(remainingSeconds); clockPerson.textContent = active ? (action.customer_name || 'Participante selecionado') : (customerSearch.value || 'Nenhuma pessoa selecionada');
    runtimeAlert.hidden = state.available; runtimeAlert.textContent = state.available ? '' : 'A opção “Exibir contador” foi desativada. Esta área está indisponível.';
    if (action.last_error) showFeedback(action.last_error, 'error');
    renderRanking(state.ranking || [], active ? action.customer_id : customerId.value); renderOrders(state.orders || []); updateButtons(); updateTimes();
  }
  function poll() { if (pollBusy) return; pollBusy = true; var data = new FormData(); data.set('product_id', product.value || '0'); post('biggest_buyer_tick', data).then(render).catch(function () { showFeedback('Falha ao atualizar em tempo real.', 'error'); }).finally(function () { pollBusy = false; }); }

  customerSearch.addEventListener('focus', function () { if (!customerSearch.disabled) renderSuggestions(customerSearch.value); });
  customerSearch.addEventListener('input', function () { customerId.value = ''; customerSearch.classList.remove('selected'); selectedPerson.hidden = true; customerClear.hidden = !customerSearch.value; overviewPerson.textContent = customerSearch.value || 'Nenhuma pessoa'; renderSuggestions(customerSearch.value); updateButtons(); });
  customerSearch.addEventListener('keydown', function (event) { var items = Array.prototype.slice.call(suggestions.querySelectorAll('.buyer-suggestion')); if (!items.length) return; if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); activeSuggestion = event.key === 'ArrowDown' ? Math.min(activeSuggestion + 1, items.length - 1) : Math.max(activeSuggestion - 1, 0); items.forEach(function (item, index) { item.classList.toggle('active', index === activeSuggestion); }); items[activeSuggestion].scrollIntoView({block:'nearest'}); } else if (event.key === 'Enter' && activeSuggestion >= 0) { event.preventDefault(); items[activeSuggestion].click(); } else if (event.key === 'Escape') suggestions.hidden = true; });
  customerClear.addEventListener('click', function () { setCustomer('', ''); customerSearch.focus(); renderSuggestions(''); });
  document.addEventListener('click', function (event) { if (!event.target.closest('.buyer-customer-picker')) suggestions.hidden = true; });
  form.querySelectorAll('input[name="duration_seconds"]').forEach(function (input) { input.addEventListener('change', updateTimes); });
  form.addEventListener('submit', function (event) { event.preventDefault(); if (!customerId.value) { showFeedback('Escolha uma pessoa na lista de sugestões.', 'error'); customerSearch.focus(); return; } var data = new FormData(form); startButton.disabled = true; post('biggest_buyer_start', data).then(function (state) { if (state.status !== 'success') throw new Error(state.msg || 'Não foi possível iniciar.'); render(state); showFeedback('Prioridade automática iniciada.', 'success'); }).catch(function (error) { showFeedback(error.message, 'error'); updateButtons(); }); });
  passButton.addEventListener('click', function () { if (!customerId.value) { showFeedback('Escolha uma pessoa antes de usar Passar.', 'error'); return; } var data = new FormData(form); passButton.disabled = true; post('biggest_buyer_pass', data).then(function (state) { if (state.status !== 'success') throw new Error(state.msg || 'Não foi possível passar a pessoa.'); var info = state.manual_pass || {}; render(state); showFeedback('Passagem concluída: ' + integer(info.quantity) + ' cotas adicionadas (diferença ' + integer(info.difference) + ' + margem ' + integer(info.margin) + '). Pedido Manual #' + info.order_id + '.', 'success'); }).catch(function (error) { showFeedback(error.message, 'error'); updateButtons(); }); });
  stopButton.addEventListener('click', function () { var data = new FormData(); data.set('product_id', product.value || '0'); post('biggest_buyer_stop', data).then(function (state) { render(state); showFeedback('Prioridade automática encerrada.', 'success'); }).catch(function () { showFeedback('Não foi possível encerrar a ação.', 'error'); }); });
  product.addEventListener('change', function () { fetch(endpoint + 'biggest_buyer_state&product_id=' + encodeURIComponent(product.value), {credentials:'same-origin'}).then(function (response) { return response.json(); }).then(render); });
  document.getElementById('buyer-open-ranking').addEventListener('click', function () { window.open('biggest_buyer/display.php?product_id=' + encodeURIComponent(product.value), 'jnsalles-ranking', 'popup=yes,width=980,height=760,resizable=yes,scrollbars=yes'); });
  window.setInterval(function () { if (remainingSeconds > 0) { remainingSeconds -= 1; clockTime.textContent = formatClock(remainingSeconds); } updateTimes(); }, 1000);
  poll(); window.setInterval(poll, 1000);
})();
</script>
<?php endif; ?>
