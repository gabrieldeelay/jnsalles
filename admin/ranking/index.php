<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$validDate = static function ($value, $fallback) {
    $date = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
};
$validTime = static function ($value, $fallback) {
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $value) ? $value : $fallback;
};

$productId = isset($_GET['raffle']) && ctype_digit((string) $_GET['raffle']) ? (int) $_GET['raffle'] : 0;
$startDate = $validDate($_GET['start_date'] ?? '', date('Y-m-d', strtotime('-6 days')));
$endDate = $validDate($_GET['end_date'] ?? '', date('Y-m-d'));
$startTime = $validTime($_GET['start_time'] ?? '', '00:00');
$endTime = $validTime($_GET['end_time'] ?? '', '23:59');
if ($startDate > $endDate) {
    [$startDate, $endDate] = [$endDate, $startDate];
}

$products = [];
$productsQuery = $conn->query('SELECT id, name FROM product_list WHERE delete_flag = 0 ORDER BY id DESC');
while ($productsQuery && ($product = $productsQuery->fetch_assoc())) {
    $products[] = $product;
}

$timerProductId = isset($_GET['timer_product_id']) && ctype_digit((string) $_GET['timer_product_id'])
    ? (int) $_GET['timer_product_id']
    : $productId;
if ($timerProductId === 0 && count($products) === 1) {
    $timerProductId = (int) $products[0]['id'];
}
$timer = $timerProductId > 0 ? ranking_timer_configuration($timerProductId) : [
    'enabled' => false, 'configured' => false, 'state' => 'disabled', 'start' => '', 'end' => '',
    'reset' => '', 'paused_at' => '', 'window_start' => '', 'window_end' => '', 'uses_reset' => false,
    'pause_intervals' => [],
];
$timerEnabled = $timer['enabled'];
$timerStart = $timer['start'];
$timerEnd = $timer['end'];
$timerConfigured = $timer['configured'];
$timerPreservedSince = $timer['window_start'];
$timerStartValue = $timerStart !== '' && strtotime($timerStart) ? date('Y-m-d\TH:i', strtotime($timerStart)) : '';
$timerEndValue = $timerEnd !== '' && strtotime($timerEnd) ? date('Y-m-d\TH:i', strtotime($timerEnd)) : '';
$timerPreviewRows = [];
$timerPreviewError = '';
if ($timerProductId > 0 && $timerConfigured) {
    $timerConditions = ranking_timer_sql_conditions('o', $timer, $conn);
    $timerPreviewQuery = $conn->query(
        'SELECT c.firstname, c.lastname, SUM(o.quantity) AS total_quantity FROM order_list o ' .
        'INNER JOIN customer_list c ON c.id = o.customer_id ' .
        'WHERE o.product_id = ' . $timerProductId . ' AND o.status = 2 AND ' . implode(' AND ', $timerConditions) . ' ' .
        'GROUP BY c.id, c.firstname, c.lastname ORDER BY total_quantity DESC, c.firstname ASC, c.lastname ASC LIMIT 3'
    );
    if ($timerPreviewQuery) {
        while ($previewRow = $timerPreviewQuery->fetch_assoc()) {
            $timerPreviewRows[] = $previewRow;
        }
    } else {
        $timerPreviewError = $conn->error;
    }
}

$conditions = ['o.status = 2'];
if ($productId > 0) {
    $conditions[] = 'o.product_id = ' . $productId;
}
$startDatetime = $conn->real_escape_string($startDate . ' ' . $startTime . ':00');
$endDatetime = $conn->real_escape_string($endDate . ' ' . $endTime . ':59');
$confirmationDatetime = payment_ranking_datetime_sql('o');
$conditions[] = "{$confirmationDatetime} BETWEEN '{$startDatetime}' AND '{$endDatetime}'";
$whereSql = ' WHERE ' . implode(' AND ', $conditions);

$perPage = 20;
$page = max(1, (int) ($_GET['pg'] ?? 1));
$countQuery = $conn->query('SELECT COUNT(*) AS total FROM (SELECT o.customer_id FROM order_list o ' . $whereSql . ' GROUP BY o.customer_id) ranked_customers');
$totalResults = $countQuery ? (int) ($countQuery->fetch_assoc()['total'] ?? 0) : 0;
$totalPages = max(1, (int) ceil($totalResults / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$rankingQuery = $conn->query(
    'SELECT c.id, c.firstname, c.lastname, c.phone, SUM(o.quantity) total_quantity, SUM(o.total_amount) total_amount, ' .
    "GROUP_CONCAT(DISTINCT COALESCE(p.name, o.product_name) ORDER BY COALESCE(p.name, o.product_name) SEPARATOR ', ') campaigns " .
    'FROM order_list o INNER JOIN customer_list c ON c.id = o.customer_id LEFT JOIN product_list p ON p.id = o.product_id ' .
    $whereSql . ' GROUP BY c.id, c.firstname, c.lastname, c.phone ORDER BY total_quantity DESC, total_amount DESC, c.firstname ASC ' .
    'LIMIT ' . $perPage . ' OFFSET ' . $offset
);
$rankingError = $rankingQuery ? '' : $conn->error;
$paginationUrl = static function ($targetPage) use ($productId, $startDate, $startTime, $endDate, $endTime) {
    return './?' . http_build_query([
        'page' => 'ranking', 'raffle' => $productId ?: '', 'start_date' => $startDate,
        'start_time' => $startTime, 'end_date' => $endDate, 'end_time' => $endTime, 'pg' => $targetPage,
    ]);
};
?>

<style>
.ranking-shell{max-width:1240px;padding:30px 24px 52px}.ranking-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:22px}.ranking-eyebrow{margin:0 0 5px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.ranking-head h2{margin:0;color:#f8fafc;font-size:30px;font-weight:800;letter-spacing:-.035em}.ranking-head p{margin:7px 0 0;color:#94a3b8;font-size:13px}.ranking-period-badge{padding:8px 11px;border:1px solid #334155;border-radius:999px;background:#111827;color:#cbd5e1;font-size:11px;white-space:nowrap}.ranking-card{border:1px solid #2d3748;border-radius:16px;background:linear-gradient(145deg,rgba(30,41,59,.76),rgba(17,24,39,.96));box-shadow:0 18px 45px rgba(0,0,0,.16)}.ranking-timer-card{padding:20px;margin-bottom:18px}.ranking-card-head{display:flex;align-items:flex-start;gap:12px;margin-bottom:18px}.ranking-card-icon{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border-radius:11px;background:rgba(124,58,237,.22);color:#ddd6fe;font-size:18px}.ranking-card-head h3{margin:0;color:#f8fafc;font-size:16px;font-weight:750}.ranking-card-head p{margin:4px 0 0;color:#94a3b8;font-size:11px;line-height:1.5}.ranking-timer-grid{display:grid;grid-template-columns:1.25fr 1fr 1fr minmax(180px,.7fr);gap:12px;align-items:end}.ranking-field{min-width:0}.ranking-field label,.ranking-filter-label{display:block;margin-bottom:6px;color:#cbd5e1;font-size:11px;font-weight:700}.ranking-field input,.ranking-field select,.ranking-filters input,.ranking-filters select{width:100%;min-height:44px!important;border:1px solid #3f4d63!important;border-radius:9px!important;background:#0f172a!important;color:#f8fafc!important;font-size:12px!important}.ranking-field input:focus,.ranking-field select:focus,.ranking-filters input:focus,.ranking-filters select:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.ranking-timer-actions{display:grid;gap:7px}.ranking-timer-switch{display:flex;min-height:25px;align-items:center;gap:7px;color:#cbd5e1;font-size:11px}.ranking-primary{min-height:44px;padding:0 16px;border:0;border-radius:9px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-size:12px;font-weight:800}.ranking-timer-help{margin:12px 0 0;color:#94a3b8;font-size:11px}.ranking-timer-feedback{display:none;margin-top:12px;padding:11px 13px;border-radius:9px;font-size:12px}.ranking-timer-feedback.success{display:block;background:#064e3b;color:#d1fae5}.ranking-timer-feedback.error{display:block;background:#7f1d1d;color:#fee2e2}.ranking-filter-card{padding:18px;margin-bottom:18px}.ranking-filters{display:grid;grid-template-columns:1.2fr .85fr .65fr .85fr .65fr auto;gap:10px;align-items:end}.ranking-table-card{overflow:hidden}.ranking-table-scroll{overflow-x:auto}.ranking-table{width:100%;min-width:760px;border-collapse:collapse}.ranking-table th{padding:13px 15px;border-bottom:1px solid #2d3748;background:rgba(15,23,42,.62);color:#94a3b8;font-size:10px;font-weight:800;letter-spacing:.06em;text-align:left;text-transform:uppercase}.ranking-table td{padding:14px 15px;border-bottom:1px solid rgba(51,65,85,.58);color:#cbd5e1;font-size:12px}.ranking-table tbody tr:hover{background:rgba(51,65,85,.2)}.ranking-name{color:#f8fafc!important;font-weight:700}.ranking-empty{padding:34px!important;text-align:center;color:#94a3b8!important}.ranking-error{margin-bottom:16px;padding:12px 14px;border:1px solid rgba(248,113,113,.35);border-radius:10px;background:rgba(127,29,29,.32);color:#fecaca;font-size:12px}.ranking-pagination{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 15px;background:rgba(15,23,42,.42);color:#94a3b8;font-size:11px}.ranking-pages{display:flex;gap:5px}.ranking-pages a{padding:6px 9px;border-radius:7px;color:#cbd5e1}.ranking-pages a.active{background:#7c3aed;color:#fff}@media(max-width:1000px){.ranking-timer-grid{grid-template-columns:1fr 1fr}.ranking-filters{grid-template-columns:1fr 1fr 1fr}.ranking-filters .ranking-primary{width:100%}}@media(max-width:640px){.ranking-shell{padding:22px 14px 42px}.ranking-head{align-items:flex-start;flex-direction:column}.ranking-head h2{font-size:25px}.ranking-period-badge{white-space:normal}.ranking-timer-grid,.ranking-filters{grid-template-columns:1fr}.ranking-pagination{align-items:flex-start;flex-direction:column}.ranking-pages{flex-wrap:wrap}}
.ranking-secondary{min-height:40px;padding:0 13px;border:1px solid #475569;border-radius:9px;background:#172033;color:#cbd5e1;font-size:11px;font-weight:750}.ranking-secondary:hover{border-color:#8b5cf6;color:#fff}.ranking-timer-current{margin:12px 0 0;padding:10px 12px;border:1px solid rgba(52,211,153,.25);border-radius:9px;background:rgba(6,78,59,.2);color:#a7f3d0;font-size:11px}
.ranking-control-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}.ranking-control{min-height:40px;padding:0 15px;border:1px solid #475569;border-radius:9px;background:#111827;color:#f8fafc;font-size:11px;font-weight:800}.ranking-control.pause{border-color:#f59e0b;color:#fcd34d}.ranking-control.resume{border-color:#34d399;color:#a7f3d0}.ranking-live{display:grid;grid-template-columns:minmax(250px,.8fr) 1.2fr;gap:14px;margin-top:16px;padding:14px;border:1px solid #334155;border-radius:13px;background:#0c1320}.ranking-live-display{display:flex;min-height:132px;align-items:center;justify-content:center;flex-direction:column;border-radius:11px;background:linear-gradient(145deg,#146c43,#198754);color:#fff;text-align:center}.ranking-live-display[data-state="paused"]{background:linear-gradient(145deg,#92400e,#b45309)}.ranking-live-display[data-state="ended"],.ranking-live-display[data-state="disabled"]{background:linear-gradient(145deg,#475569,#64748b)}.ranking-live-display[data-state="scheduled"]{background:linear-gradient(145deg,#1e3a8a,#2563eb)}.ranking-live-kicker{font-size:10px;font-weight:850;letter-spacing:.1em;text-transform:uppercase}.ranking-live-time{margin-top:3px;font-size:28px;font-weight:900;letter-spacing:.04em}.ranking-live-period{margin-top:7px;color:rgba(255,255,255,.82);font-size:10px}.ranking-live-list{padding:4px 6px}.ranking-live-list h4{margin:0 0 8px;color:#f8fafc;font-size:12px}.ranking-live-buyer{display:grid;grid-template-columns:32px 1fr auto;gap:9px;align-items:center;padding:9px 0;border-bottom:1px solid #273449;color:#e2e8f0;font-size:12px}.ranking-live-buyer:last-child{border-bottom:0}.ranking-live-position{color:#c4b5fd;font-weight:850}.ranking-live-quantity{padding:4px 8px;border-radius:999px;background:rgba(16,185,129,.13);color:#a7f3d0;font-size:10px;font-weight:800}.ranking-live-empty{display:grid;min-height:84px;place-items:center;color:#94a3b8;font-size:11px;text-align:center}.ranking-live-note{grid-column:1/-1;margin:0;color:#94a3b8;font-size:10px}.ranking-busy{pointer-events:none;opacity:.55}@media(max-width:760px){.ranking-live{grid-template-columns:1fr}.ranking-control-row>*{flex:1 1 145px}}
</style>

<main class="h-full overflow-y-auto">
  <div class="container mx-auto ranking-shell">
    <header class="ranking-head">
      <div><p class="ranking-eyebrow">Desempenho</p><h2>Top compradores</h2><p>Acompanhe somente pagamentos confirmados e configure o contador exibido na campanha.</p></div>
      <span class="ranking-period-badge"><?= $escape(date('d/m/Y', strtotime($startDate))) ?> até <?= $escape(date('d/m/Y', strtotime($endDate))) ?></span>
    </header>

    <section class="ranking-card ranking-timer-card">
      <header class="ranking-card-head"><span class="ranking-card-icon">⏱</span><div><h3>Período do Top Compradores Diário</h3><p>A prévia abaixo mostra exatamente o estado publicado. Pause sem perder dados, retome de onde parou ou estenda mantendo o ranking.</p></div></header>
      <form id="ranking-timer-form">
        <div class="ranking-timer-grid">
          <div class="ranking-field"><label for="timer_product_id">Campanha</label><select name="product_id" id="timer_product_id" required><option value="">Selecione</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= $timerProductId === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select></div>
          <div class="ranking-field"><label for="timer_start_at">Início</label><input name="start_at" id="timer_start_at" type="datetime-local" value="<?= $escape($timerStartValue) ?>"></div>
          <div class="ranking-field"><label for="timer_end_at">Encerramento</label><input name="end_at" id="timer_end_at" type="datetime-local" value="<?= $escape($timerEndValue) ?>"></div>
          <div class="ranking-timer-actions"><label class="ranking-timer-switch"><input type="checkbox" name="enabled" id="timer_enabled" value="1" <?= $timerEnabled ? 'checked' : '' ?>> Exibir contador</label><?php if ($timerConfigured): ?><button type="submit" class="ranking-primary" data-mode="extend">Estender mantendo ranking</button><?php endif; ?><button type="submit" class="ranking-secondary" data-mode="restart"><?= $timerConfigured ? 'Iniciar novo período' : 'Iniciar período' ?></button></div>
        </div>
        <?php if ($timerConfigured): ?><p class="ranking-timer-current"><strong>Ranking preservado desde:</strong> <?= $escape(date('d/m/Y \à\s H:i', strtotime($timerPreservedSince))) ?>. Para estender, altere somente o encerramento e use “Estender mantendo ranking”.</p><?php endif; ?>
        <p class="ranking-timer-help">Horário de Brasília. Ao encerrar, o tempo permanece em 00:00:00 e o ranking fica congelado. Somente “Iniciar novo período” zera a classificação.</p>
        <div id="ranking-timer-feedback" class="ranking-timer-feedback" role="status"></div>
      </form>
      <?php if ($timerConfigured): ?>
        <div class="ranking-control-row">
          <?php if ($timer['state'] === 'paused'): ?>
            <button type="button" class="ranking-control resume" data-control="resume">&#9654; Retomar per&iacute;odo</button>
          <?php elseif (!in_array($timer['state'], ['ended', 'disabled'], true)): ?>
            <button type="button" class="ranking-control pause" data-control="pause">&#10074;&#10074; Pausar per&iacute;odo</button>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="ranking-live" aria-live="polite">
        <div id="ranking-live-display" class="ranking-live-display" data-state="<?= $escape($timer['state']) ?>">
          <span id="ranking-live-label" class="ranking-live-kicker">Visualiza&ccedil;&atilde;o no site</span>
          <strong id="ranking-live-time" class="ranking-live-time">--:--:--</strong>
          <span class="ranking-live-period"><?= $timerConfigured ? $escape(date('d/m/Y H:i', strtotime($timerStart)) . ' - ' . date('d/m/Y H:i', strtotime($timerEnd))) : 'Nenhum periodo configurado' ?></span>
        </div>
        <div class="ranking-live-list"><h4>Pr&eacute;via exata dos 3 primeiros colocados</h4>
          <?php if ($timerPreviewError !== ''): ?><div class="ranking-live-empty">N&atilde;o foi poss&iacute;vel carregar a pr&eacute;via.</div>
          <?php elseif (!$timerConfigured): ?><div class="ranking-live-empty">Selecione a campanha e inicie um per&iacute;odo.</div>
          <?php elseif (count($timerPreviewRows) === 0): ?><div class="ranking-live-empty">Ainda n&atilde;o h&aacute; pagamentos confirmados neste per&iacute;odo.</div>
          <?php else: foreach ($timerPreviewRows as $index => $previewRow): $parts = preg_split('/\s+/u', trim((string) $previewRow['firstname']), -1, PREG_SPLIT_NO_EMPTY); ?>
            <div class="ranking-live-buyer"><span class="ranking-live-position"><?= $index + 1 ?>&ordm;</span><strong><?= $escape($parts[0] ?? 'Cliente') ?></strong><span class="ranking-live-quantity"><?= number_format((int) $previewRow['total_quantity'], 0, ',', '.') ?> cotas</span></div>
          <?php endforeach; endif; ?>
        </div>
        <p class="ranking-live-note">Esta pr&eacute;via usa o mesmo per&iacute;odo, as mesmas pausas e os mesmos pagamentos confirmados exibidos na campanha. Quando o tempo acaba, ela permanece congelada.</p>
      </div>
    </section>

    <section class="ranking-card ranking-filter-card">
      <form action="./" method="GET"><input type="hidden" name="page" value="ranking"><label class="ranking-filter-label" for="raffle">Filtrar compradores</label><div class="ranking-filters">
        <select name="raffle" id="raffle"><option value="">Todas as campanhas</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= $productId === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select>
        <input aria-label="Data inicial" name="start_date" type="date" value="<?= $escape($startDate) ?>"><input aria-label="Hora inicial" name="start_time" type="time" value="<?= $escape($startTime) ?>">
        <input aria-label="Data final" name="end_date" type="date" value="<?= $escape($endDate) ?>"><input aria-label="Hora final" name="end_time" type="time" value="<?= $escape($endTime) ?>"><button class="ranking-primary">Filtrar</button>
      </div></form>
    </section>

    <?php if ($rankingError !== ''): ?><div class="ranking-error">Não foi possível carregar o ranking. Detalhe: <?= $escape($rankingError) ?></div><?php endif; ?>
    <section class="ranking-card ranking-table-card"><div class="ranking-table-scroll"><table class="ranking-table"><thead><tr><th>Cliente</th><th>Qtd. números</th><th>Total pago</th><th>Telefone</th><th>Campanha</th></tr></thead><tbody>
      <?php if (!$rankingQuery || $rankingQuery->num_rows === 0): ?><tr><td colspan="5" class="ranking-empty">Nenhum pagamento confirmado neste período.</td></tr>
      <?php else: while ($row = $rankingQuery->fetch_assoc()): $phone = formatPhoneNumber($row['phone']); $maskedPhone = strlen($phone) > 4 ? substr($phone, 0, -4) . '****' : '****'; ?>
        <tr><td class="ranking-name"><?= $escape(trim($row['firstname'] . ' ' . $row['lastname'])) ?></td><td><?= number_format((int) $row['total_quantity'], 0, ',', '.') ?></td><td>R$ <?= number_format((float) $row['total_amount'], 2, ',', '.') ?></td><td><?= $escape($maskedPhone) ?></td><td><?= $escape($row['campaigns']) ?></td></tr>
      <?php endwhile; endif; ?></tbody></table></div>
      <?php if ($totalResults > 0): ?><footer class="ranking-pagination"><span><?= $totalResults ?> comprador<?= $totalResults === 1 ? '' : 'es' ?></span><nav class="ranking-pages" aria-label="Paginação do ranking"><?php if ($page > 1): ?><a href="<?= $escape($paginationUrl($page - 1)) ?>">Anterior</a><?php endif; ?><?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): ?><a class="<?= $number === $page ? 'active' : '' ?>" href="<?= $escape($paginationUrl($number)) ?>"><?= $number ?></a><?php endfor; ?><?php if ($page < $totalPages): ?><a href="<?= $escape($paginationUrl($page + 1)) ?>">Próxima</a><?php endif; ?></nav></footer><?php endif; ?>
    </section>
  </div>
</main>

<script>
(function () {
  var productSelect = document.getElementById('timer_product_id');
  var form = document.getElementById('ranking-timer-form');
  var feedback = document.getElementById('ranking-timer-feedback');
  if (productSelect) productSelect.addEventListener('change', function () { var params = new URLSearchParams(window.location.search); params.set('page', 'ranking'); params.set('timer_product_id', productSelect.value); window.location.href = './?' + params.toString(); });
  if (form) form.addEventListener('submit', function (event) {
    event.preventDefault(); feedback.className = 'ranking-timer-feedback'; feedback.textContent = '';
    var mode = event.submitter && event.submitter.dataset.mode ? event.submitter.dataset.mode : 'restart';
    if (mode === 'restart' && <?= $timerConfigured ? 'true' : 'false' ?> && !window.confirm('Iniciar um novo período vai zerar o ranking atual. Deseja continuar?')) return;
    var data = new FormData(form); data.set('mode', mode); if (!document.getElementById('timer_enabled').checked) data.set('enabled', '0');
    fetch(_base_url_ + 'class/System.php?action=save_ranking_timer', {method:'POST', body:data, credentials:'same-origin'})
      .then(function (response) { return response.json(); })
      .then(function (result) { feedback.textContent = result.msg || (result.status === 'success' ? 'Contador salvo.' : 'Não foi possível salvar.'); feedback.classList.add(result.status === 'success' ? 'success' : 'error'); })
      .catch(function () { feedback.textContent = 'Não foi possível salvar o contador. Tente novamente.'; feedback.classList.add('error'); });
  });
  document.querySelectorAll('[data-control]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!productSelect || !productSelect.value) return;
      button.classList.add('ranking-busy');
      feedback.className = 'ranking-timer-feedback';
      feedback.textContent = '';
      var data = new FormData();
      data.set('product_id', productSelect.value);
      data.set('control', button.dataset.control);
      fetch(_base_url_ + 'class/System.php?action=control_ranking_timer', {method:'POST', body:data, credentials:'same-origin'})
        .then(function (response) { return response.json(); })
        .then(function (result) {
          feedback.textContent = result.msg || 'Estado do contador atualizado.';
          feedback.classList.add(result.status === 'success' ? 'success' : 'error');
          if (result.status === 'success') window.setTimeout(function(){ window.location.reload(); }, 700);
          else button.classList.remove('ranking-busy');
        })
        .catch(function () { feedback.textContent = 'Nao foi possivel controlar o contador.'; feedback.classList.add('error'); button.classList.remove('ranking-busy'); });
    });
  });

  var liveDisplay = document.getElementById('ranking-live-display');
  var liveLabel = document.getElementById('ranking-live-label');
  var liveTime = document.getElementById('ranking-live-time');
  var timerState = <?= json_encode($timer['state']) ?>;
  var startAt = <?= $timerStart !== '' ? "new Date('" . date('Y-m-d\\TH:i:s', strtotime($timerStart)) . "-03:00').getTime()" : 'null' ?>;
  var endAt = <?= $timerEnd !== '' ? "new Date('" . date('Y-m-d\\TH:i:s', strtotime($timerEnd)) . "-03:00').getTime()" : 'null' ?>;
  var pausedAt = <?= $timer['paused_at'] !== '' ? "new Date('" . date('Y-m-d\\TH:i:s', strtotime($timer['paused_at'])) . "-03:00').getTime()" : 'null' ?>;
  function formatRemaining(milliseconds) {
    var total = Math.max(0, Math.floor(milliseconds / 1000));
    var days = Math.floor(total / 86400);
    var hours = Math.floor((total % 86400) / 3600);
    var minutes = Math.floor((total % 3600) / 60);
    var seconds = total % 60;
    var pad = function (value) { return String(value).padStart(2, '0'); };
    return (days > 0 ? days + 'd ' : '') + pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
  }
  function updatePreviewTimer() {
    if (!liveDisplay || !liveLabel || !liveTime || startAt === null || endAt === null) {
      if (liveLabel) liveLabel.textContent = 'Desativado';
      if (liveTime) liveTime.textContent = '--:--:--';
      return;
    }
    var now = Date.now();
    var state = timerState;
    var label = '';
    var value = '';
    if (state === 'paused' && pausedAt !== null) { label = 'Pausado'; value = formatRemaining(endAt - pausedAt); }
    else if (now < startAt) { state = 'scheduled'; label = 'Comeca em'; value = formatRemaining(startAt - now); }
    else if (now < endAt) { state = 'running'; label = 'Termina em'; value = formatRemaining(endAt - now); }
    else { state = 'ended'; label = 'Encerrado'; value = '00:00:00'; }
    liveDisplay.dataset.state = state;
    liveLabel.textContent = label;
    liveTime.textContent = value;
  }
  updatePreviewTimer();
  window.setInterval(updatePreviewTimer, 1000);
  if (feedback && window.MutationObserver) {
    new MutationObserver(function () {
      if (feedback.classList.contains('success') && !feedback.dataset.reloadScheduled) {
        feedback.dataset.reloadScheduled = '1';
        window.setTimeout(function () { window.location.reload(); }, 900);
      }
    }).observe(feedback, {attributes:true, childList:true});
  }
})();
</script>
