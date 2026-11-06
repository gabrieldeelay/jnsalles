<?php
require_once __DIR__ . '/settings.php';

$storedToken = trim((string) $_settings->info('biggest_buyer_overlay_token'));
$providedToken = trim((string) ($_GET['token'] ?? ''));
if ($storedToken === '' || $providedToken === '' || !hash_equals($storedToken, $providedToken)) {
    http_response_code(404);
    exit('Overlay indisponível.');
}

$productId = max(0, (int) ($_GET['product_id'] ?? 0));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Referrer-Policy: no-referrer');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <title>Top Ranking — Ao vivo</title>
  <style>
  :root{color-scheme:dark;font-family:Inter,Montserrat,Arial,sans-serif}*{box-sizing:border-box}html,body{width:100%;min-height:100%;margin:0;background:transparent}body{display:grid;min-height:100vh;place-items:center;padding:22px;color:#fff}.ranking-overlay{position:relative;width:min(820px,100%);overflow:hidden;padding:27px 28px 20px;border:1px solid rgba(196,181,253,.35);border-radius:26px;background:linear-gradient(145deg,rgba(14,20,34,.96),rgba(5,9,17,.94));box-shadow:0 22px 70px rgba(0,0,0,.48),inset 0 1px 0 rgba(255,255,255,.07)}.ranking-overlay:before{position:absolute;top:-150px;left:50%;width:520px;height:260px;border-radius:50%;background:rgba(124,58,237,.23);filter:blur(70px);transform:translateX(-50%);content:""}.ranking-title{position:relative;margin:0 0 22px;color:#fff;font-size:clamp(32px,6vw,52px);font-weight:950;letter-spacing:.06em;line-height:1;text-align:center;text-shadow:0 5px 25px rgba(124,58,237,.32)}.ranking-list{position:relative;display:grid;gap:11px}.ranking-item{display:grid;grid-template-columns:150px minmax(0,1fr) auto;align-items:center;gap:18px;min-height:94px;padding:15px 19px;border:1px solid rgba(148,163,184,.18);border-radius:18px;background:linear-gradient(90deg,rgba(30,41,59,.92),rgba(15,23,42,.72));box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}.ranking-item:first-child{min-height:105px;border-color:rgba(245,158,11,.46);background:linear-gradient(90deg,rgba(120,53,15,.49),rgba(15,23,42,.82));box-shadow:0 10px 30px rgba(245,158,11,.08),inset 0 1px 0 rgba(255,255,255,.07)}.ranking-position{font-size:clamp(17px,2.5vw,23px);font-weight:950;letter-spacing:.045em;white-space:nowrap}.ranking-item:nth-child(1) .ranking-position{color:#fde68a}.ranking-item:nth-child(2) .ranking-position{color:#e2e8f0}.ranking-item:nth-child(3) .ranking-position{color:#fdba74}.ranking-name{overflow:hidden;color:#fff;font-size:clamp(24px,4vw,38px);font-weight:900;letter-spacing:-.025em;text-overflow:ellipsis;white-space:nowrap}.ranking-quantity{min-width:122px;color:#ddd6fe;font-size:clamp(23px,3.6vw,34px);font-weight:950;text-align:right;white-space:nowrap}.ranking-quantity small{display:block;margin-top:2px;color:#7f8da3;font-size:10px;font-weight:850;letter-spacing:.12em;text-transform:uppercase}.ranking-live{position:relative;display:flex;align-items:center;justify-content:center;gap:11px;margin-top:19px;color:#fff;font-size:19px;font-weight:950;letter-spacing:.17em}.ranking-live-dot{width:13px;height:13px;border-radius:50%;background:#ef4444;box-shadow:0 0 0 0 rgba(239,68,68,.7);animation:livePulse 1.35s ease-out infinite}.ranking-offline .ranking-live-dot{animation:none;background:#94a3b8;box-shadow:none}.ranking-offline .ranking-live{color:#cbd5e1}.ranking-offline .ranking-live-label:after{content:" · RECONECTANDO"}.ranking-empty .ranking-name{color:#8d9bb0;font-size:clamp(18px,3vw,27px)}.ranking-empty .ranking-quantity{color:#64748b}@keyframes livePulse{0%{box-shadow:0 0 0 0 rgba(239,68,68,.72)}70%{box-shadow:0 0 0 13px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}@media(max-width:640px){body{padding:12px}.ranking-overlay{padding:22px 16px 17px;border-radius:20px}.ranking-item{grid-template-columns:105px minmax(0,1fr);gap:10px;min-height:86px;padding:13px 14px}.ranking-quantity{grid-column:2;min-width:0;margin-top:-12px;font-size:20px;text-align:left}.ranking-quantity small{display:inline;margin-left:5px}.ranking-title{margin-bottom:17px}.ranking-live{font-size:16px}}
  @media(prefers-reduced-motion:reduce){.ranking-live-dot{animation:none}}
  </style>
</head>
<body>
  <main id="ranking-overlay" class="ranking-overlay">
    <h1 class="ranking-title">TOP RANKING</h1>
    <section class="ranking-list" aria-live="polite">
      <?php foreach (['1º LUGAR', '2º LUGAR', '3º LUGAR'] as $index => $position): ?>
      <article class="ranking-item ranking-empty" data-position="<?= $index ?>">
        <strong class="ranking-position"><?= $position ?></strong>
        <span class="ranking-name">-</span>
        <span class="ranking-quantity">0<small>cotas</small></span>
      </article>
      <?php endforeach; ?>
    </section>
    <footer class="ranking-live"><span class="ranking-live-dot" aria-hidden="true"></span><span class="ranking-live-label">AO VIVO</span></footer>
  </main>
  <script>
  (function () {
    var baseUrl = <?= json_encode(rtrim(BASE_URL, '/') . '/', JSON_UNESCAPED_SLASHES) ?>;
    var token = <?= json_encode($providedToken) ?>;
    var productId = <?= (int) $productId ?>;
    var overlay = document.getElementById('ranking-overlay');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.ranking-item'));
    var busy = false;
    function integer(value) { return Number(value || 0).toLocaleString('pt-BR'); }
    function render(state) {
      if (!state || state.status !== 'success' || !state.available) throw new Error('ranking unavailable');
      overlay.classList.remove('ranking-offline');
      if (state.product && state.product.id) productId = Number(state.product.id);
      var ranking = Array.isArray(state.ranking) ? state.ranking : [];
      rows.forEach(function (row, index) {
        var entry = ranking[index];
        var name = row.querySelector('.ranking-name');
        var quantity = row.querySelector('.ranking-quantity');
        row.classList.toggle('ranking-empty', !entry);
        name.textContent = entry ? String(entry.name || 'PARTICIPANTE').toUpperCase() : '-';
        quantity.firstChild.nodeValue = entry ? integer(entry.quantity) : '0';
      });
    }
    function poll() {
      if (busy) return;
      busy = true;
      var url = baseUrl + 'class/Main.php?action=biggest_buyer_overlay_state&token=' + encodeURIComponent(token) + '&product_id=' + encodeURIComponent(productId) + '&_=' + Date.now();
      fetch(url, {cache:'no-store', credentials:'omit'})
        .then(function (response) { if (!response.ok) throw new Error('request failed'); return response.json(); })
        .then(render)
        .catch(function () { overlay.classList.add('ranking-offline'); })
        .finally(function () { busy = false; });
    }
    poll();
    window.setInterval(poll, 1000);
  })();
  </script>
</body>
</html>
