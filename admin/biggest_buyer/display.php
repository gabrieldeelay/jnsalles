<?php
require_once dirname(__DIR__, 2) . '/settings.php';
if (empty($_settings->userdata('firstname')) || (int) $_settings->userdata('type') !== 1) {
    http_response_code(403);
    exit('Acesso restrito ao administrador.');
}
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ranking — Maior Comprador</title>
  <style>
  :root{color-scheme:dark}*{box-sizing:border-box}body{min-height:100vh;margin:0;background:radial-gradient(circle at top,#1e293b 0,#0b1120 46%,#060a12 100%);color:#f8fafc;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.display-shell{width:min(1060px,100%);margin:0 auto;padding:30px 24px 50px}.display-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:22px}.display-eyebrow{margin:0 0 5px;color:#c4b5fd;font-size:11px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}.display-head h1{margin:0;font-size:34px;letter-spacing:-.04em}.display-head p{margin:7px 0 0;color:#94a3b8;font-size:13px}.display-live{display:flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid #334155;border-radius:999px;background:rgba(15,23,42,.8);color:#cbd5e1;font-size:11px;font-weight:850}.display-live:before{width:9px;height:9px;border-radius:50%;background:#34d399;box-shadow:0 0 0 5px rgba(52,211,153,.12);content:""}.display-card{overflow:hidden;border:1px solid #334155;border-radius:18px;background:rgba(15,23,42,.8);box-shadow:0 24px 70px rgba(0,0,0,.28);backdrop-filter:blur(14px)}table{width:100%;border-collapse:collapse}th{padding:14px 18px;border-bottom:1px solid #334155;background:rgba(15,23,42,.96);color:#94a3b8;font-size:10px;font-weight:900;letter-spacing:.08em;text-align:left;text-transform:uppercase}td{padding:17px 18px;border-bottom:1px solid rgba(51,65,85,.64);color:#cbd5e1;font-size:15px}tbody tr:first-child{background:linear-gradient(90deg,rgba(124,58,237,.28),rgba(15,23,42,.2))}tbody tr.target{background:linear-gradient(90deg,rgba(16,185,129,.2),rgba(15,23,42,.2))}.position{width:80px;color:#c4b5fd;font-size:18px;font-weight:950}.name{color:#fff;font-weight:800}.quantity{text-align:right;font-size:17px;font-weight:900}.empty{padding:60px 20px;color:#94a3b8;text-align:center}.display-footer{display:flex;justify-content:space-between;gap:18px;margin-top:14px;color:#64748b;font-size:11px}.display-error{margin-bottom:14px;padding:12px 14px;border:1px solid #991b1b;border-radius:10px;background:rgba(127,29,29,.34);color:#fecaca;font-size:12px}@media(max-width:650px){.display-shell{padding:20px 12px 40px}.display-head{align-items:flex-start;flex-direction:column}.display-head h1{font-size:28px}th,td{padding:13px 11px}.position{width:55px}.display-footer{flex-direction:column}}
  </style>
</head>
<body>
  <main class="display-shell">
    <header class="display-head"><div><p class="display-eyebrow">Classificação ao vivo</p><h1 id="display-title">Maior Comprador</h1><p>Atualização automática a cada segundo.</p></div><span class="display-live">AO VIVO</span></header>
    <div id="display-error" class="display-error" hidden></div>
    <section class="display-card"><table><thead><tr><th>Posição</th><th>Participante</th><th style="text-align:right">Cotas</th></tr></thead><tbody id="display-ranking"><tr><td colspan="3" class="empty">Carregando ranking...</td></tr></tbody></table></section>
    <footer class="display-footer"><span id="display-updated">Aguardando atualização</span><span>Somente pedidos confirmados entram na classificação</span></footer>
  </main>
  <script>
  (function () {
    var baseUrl = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;
    var productId = <?= (int) $productId ?>;
    var ranking = document.getElementById('display-ranking');
    var title = document.getElementById('display-title');
    var updated = document.getElementById('display-updated');
    var error = document.getElementById('display-error');
    var busy = false;
    function integer(value) { return Number(value || 0).toLocaleString('pt-BR'); }
    function render(state) {
      if (!state || state.status !== 'success' || !state.available) {
        error.hidden = false; error.textContent = 'O contador está desativado ou a classificação está indisponível.'; return;
      }
      error.hidden = true;
      if (state.product) { productId = state.product.id; title.textContent = state.product.name; }
      ranking.innerHTML = '';
      if (!state.ranking || !state.ranking.length) { var empty = document.createElement('tr'); empty.innerHTML = '<td colspan="3" class="empty">Ainda não há participantes neste período.</td>'; ranking.appendChild(empty); }
      else state.ranking.forEach(function (row, index) {
        var tr = document.createElement('tr'); if (state.action && Number(row.customer_id) === Number(state.action.customer_id) && state.action.status === 'active') tr.className = 'target';
        var pos = document.createElement('td'); pos.className = 'position'; pos.textContent = (index + 1) + 'º';
        var name = document.createElement('td'); name.className = 'name'; name.textContent = row.name;
        var quantity = document.createElement('td'); quantity.className = 'quantity'; quantity.textContent = integer(row.quantity);
        tr.append(pos, name, quantity); ranking.appendChild(tr);
      });
      updated.textContent = 'Atualizado às ' + String(state.server_time || '').slice(11,19);
    }
    function poll() {
      if (busy) return; busy = true;
      fetch(baseUrl + 'class/Main.php?action=biggest_buyer_state&product_id=' + encodeURIComponent(productId), {credentials:'same-origin'})
        .then(function (response) { return response.json(); }).then(render)
        .catch(function () { error.hidden = false; error.textContent = 'Falha ao atualizar o ranking.'; })
        .finally(function () { busy = false; });
    }
    poll(); window.setInterval(poll, 1000);
  })();
  </script>
</body>
</html>
