<?php

if ((int) $_settings->userdata('type') !== 1) {
    echo '<main class="h-full overflow-y-auto"><div class="container px-6 mx-auto"><p class="draw-denied">Acesso administrativo necessário.</p></div></main>';
    return;
}

require_once __DIR__ . '/data.php';
if (empty($_SESSION['draw_csrf'])) {
    $_SESSION['draw_csrf'] = bin2hex(random_bytes(32));
}
$schemaReady = jnsalles_draw_ensure_schema($conn);
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$products = [];
$productQuery = $conn->query('SELECT id, name, status FROM product_list WHERE delete_flag = 0 ORDER BY id DESC');
if ($productQuery) {
    while ($product = $productQuery->fetch_assoc()) {
        $products[] = $product;
    }
}

$eligibleByProduct = [];
$eligibleQuery = $conn->query('SELECT product_id, COALESCE(SUM(quantity), 0) AS total FROM order_list WHERE status = 2 GROUP BY product_id');
if ($eligibleQuery) {
    while ($eligible = $eligibleQuery->fetch_assoc()) {
        $eligibleByProduct[(int) $eligible['product_id']] = (int) $eligible['total'];
    }
}
$history = $schemaReady ? jnsalles_draw_history($conn, 12) : false;
?>

<style>
    .draw-shell{max-width:1180px;padding:30px 24px 70px}.draw-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px}.draw-eyebrow{margin:0 0 5px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.draw-head h2{margin:0;color:#f8fafc;font-size:30px;font-weight:800;letter-spacing:-.035em}.draw-head p{max-width:720px;margin:7px 0 0;color:#94a3b8;font-size:13px;line-height:1.6}.draw-integrity{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(52,211,153,.32);border-radius:999px;background:rgba(6,78,59,.22);color:#a7f3d0;font-size:10px;font-weight:750;white-space:nowrap}.draw-integrity:before{content:'';width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.12)}
    .draw-card{overflow:hidden;border:1px solid #303d51;border-radius:18px;background:linear-gradient(150deg,#182235,#111827);box-shadow:0 24px 60px rgba(0,0,0,.22)}.draw-controls{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;padding:18px;border-bottom:1px solid #2d394b;background:rgba(15,23,42,.46)}.draw-field label{display:block;margin-bottom:7px;color:#aebdd0;font-size:11px;font-weight:750}.draw-select{width:100%;height:48px!important;margin:0!important;border:1px solid #405069!important;border-radius:11px!important;background:#0f172a!important;color:#f8fafc!important;font-size:13px!important;box-shadow:none!important}.draw-select option{background:#0f172a;color:#f8fafc}.draw-select:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.17)!important;outline:0}.draw-button{align-self:end;display:inline-flex;min-width:170px;height:48px;align-items:center;justify-content:center;gap:9px;border:0;border-radius:11px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:13px;font-weight:850;box-shadow:0 10px 25px rgba(109,40,217,.28);transition:.18s}.draw-button:hover{transform:translateY(-1px);filter:brightness(1.08)}.draw-button:disabled{cursor:wait;opacity:.58;transform:none}
    .draw-stage{position:relative;display:flex;min-height:430px;align-items:center;justify-content:center;padding:44px 22px 84px;overflow:hidden;text-align:center}.draw-stage:before,.draw-stage:after{content:'';position:absolute;width:300px;height:300px;border-radius:50%;filter:blur(4px);opacity:.18}.draw-stage:before{top:-170px;left:-80px;background:#7c3aed}.draw-stage:after{right:-110px;bottom:-190px;background:#0ea5e9}.draw-idle,.draw-running,.draw-result{position:relative;z-index:2;width:100%;max-width:760px}.draw-symbol{display:grid;width:92px;height:92px;margin:0 auto 20px;place-items:center;border:1px solid rgba(167,139,250,.35);border-radius:27px;background:linear-gradient(145deg,rgba(124,58,237,.3),rgba(30,41,59,.85));color:#ddd6fe;font-size:39px;box-shadow:0 18px 45px rgba(0,0,0,.2)}.draw-idle h3,.draw-running h3{margin:0;color:#f8fafc;font-size:25px;font-weight:800}.draw-idle p,.draw-running p{margin:9px auto 0;color:#91a1b6;font-size:13px;line-height:1.6}.draw-running,.draw-result{display:none}.draw-running.active,.draw-result.active{display:block}.draw-idle.hidden{display:none}.draw-orbit{position:relative;width:120px;height:120px;margin:0 auto 20px}.draw-orbit:before,.draw-orbit:after{content:'';position:absolute;border-radius:50%}.draw-orbit:before{inset:0;border:3px solid rgba(139,92,246,.18);border-top-color:#a78bfa;animation:draw-spin 1s linear infinite}.draw-orbit:after{inset:28px;background:radial-gradient(circle at 35% 30%,#c4b5fd,#7c3aed 58%,#312e81);box-shadow:0 0 40px rgba(124,58,237,.45);animation:draw-pulse .8s ease-in-out infinite alternate}.draw-rolling{display:inline-flex;min-height:34px;align-items:center;margin-top:18px;padding:7px 13px;border:1px solid #334155;border-radius:999px;background:#0f172a;color:#cbd5e1;font-size:11px;font-variant-numeric:tabular-nums}.draw-countdown{position:absolute;right:0;bottom:0;left:0;z-index:3;display:flex;height:62px;align-items:center;justify-content:center;gap:9px;border-top:1px solid #2d394b;background:rgba(9,14,24,.92);color:#94a3b8;font-size:11px;font-weight:750;text-transform:uppercase;letter-spacing:.08em}.draw-countdown strong{color:#fff;font-size:25px;font-variant-numeric:tabular-nums;letter-spacing:0}.draw-countdown strong.urgent{color:#f87171}.draw-winner-label{margin:0 0 10px;color:#a78bfa;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.draw-result h3{margin:0;color:#fff;font-size:clamp(32px,5vw,56px);font-weight:850;line-height:1.08;letter-spacing:-.045em;text-shadow:0 10px 35px rgba(0,0,0,.28)}.draw-result-phone{margin:14px 0 0;color:#cbd5e1;font-size:19px;font-weight:650;letter-spacing:.04em}.draw-result-meta{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:20px}.draw-result-meta span{padding:7px 10px;border:1px solid #354258;border-radius:999px;background:#111827;color:#aebdd0;font-size:10px}.draw-result-meta strong{color:#f8fafc}.draw-audit{margin:14px auto 0;color:#64748b;font-family:monospace;font-size:9px;word-break:break-all}
    .draw-feedback{display:none;margin:14px 0 0;padding:12px 14px;border-radius:11px;font-size:12px;font-weight:700}.draw-feedback.show{display:block}.draw-feedback.error{border:1px solid rgba(248,113,113,.35);background:#7f1d1d;color:#fee2e2}.draw-feedback.success{border:1px solid rgba(52,211,153,.3);background:#064e3b;color:#d1fae5}.draw-history{margin-top:20px;border:1px solid #303d51;border-radius:16px;background:#151d2a;overflow:hidden}.draw-history-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid #2d394b}.draw-history-head h3{margin:0;color:#f8fafc;font-size:15px}.draw-history-head span{color:#8393aa;font-size:10px}.draw-history-list{display:grid}.draw-history-row{display:grid;grid-template-columns:minmax(210px,1.4fr) minmax(150px,1fr) minmax(100px,.55fr) minmax(130px,.7fr);gap:14px;align-items:center;padding:13px 18px;border-top:1px solid #283448;color:#cbd5e1}.draw-history-row:first-child{border-top:0}.draw-history-row strong{display:block;color:#f1f5f9;font-size:12px}.draw-history-row small{display:block;margin-top:3px;color:#7f8da3;font-size:9px}.draw-history-number{color:#c4b5fd!important;font-family:monospace}.draw-empty{padding:28px 18px;color:#7f8da3;font-size:12px;text-align:center}
    .draw-settings-button{position:fixed;right:24px;bottom:24px;z-index:35;display:grid;width:45px;height:45px;place-items:center;border:1px solid #46536a;border-radius:14px;background:#172033;color:#cbd5e1;font-size:17px;box-shadow:0 12px 30px rgba(0,0,0,.32)}.draw-settings-button:hover{border-color:#8b5cf6;color:#fff}.draw-modal-backdrop{position:fixed;inset:0;z-index:60;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(2,6,23,.76);backdrop-filter:blur(6px)}.draw-modal-backdrop.open{display:flex}.draw-modal{width:min(520px,100%);overflow:hidden;border:1px solid #3a475c;border-radius:17px;background:#151d2a;box-shadow:0 28px 75px rgba(0,0,0,.5)}.draw-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;padding:18px;border-bottom:1px solid #2d394b}.draw-modal-head h3{margin:0;color:#f8fafc;font-size:17px}.draw-modal-head p{margin:4px 0 0;color:#8fa2b8;font-size:11px}.draw-modal-close{display:grid;width:34px;height:34px;place-items:center;border:1px solid #3a475c;border-radius:9px;background:#111827;color:#cbd5e1;font-size:18px}.draw-modal-body{display:grid;gap:10px;padding:18px}.draw-rule{display:flex;align-items:flex-start;gap:11px;padding:12px;border:1px solid #303d51;border-radius:12px;background:#111827}.draw-rule-icon{display:grid;width:32px;height:32px;flex:0 0 32px;place-items:center;border-radius:9px;background:rgba(124,58,237,.2);color:#c4b5fd}.draw-rule strong{display:block;color:#f1f5f9;font-size:12px}.draw-rule p{margin:3px 0 0;color:#8fa2b8;font-size:10px;line-height:1.5}.draw-denied{margin:30px 0;padding:14px;border-radius:10px;background:#7f1d1d;color:#fee2e2}@keyframes draw-spin{to{transform:rotate(360deg)}}@keyframes draw-pulse{to{transform:scale(1.08)}}
    @media(max-width:720px){.draw-shell{padding:22px 14px 74px}.draw-head{align-items:flex-start;flex-direction:column}.draw-controls{grid-template-columns:1fr}.draw-button{width:100%}.draw-stage{min-height:400px;padding-inline:16px}.draw-history-row{grid-template-columns:1fr 1fr}.draw-history-row>div:first-child{grid-column:1/-1}.draw-settings-button{right:15px;bottom:15px}}
</style>

<main class="h-full overflow-y-auto">
    <div class="container mx-auto draw-shell">
        <header class="draw-head">
            <div><p class="draw-eyebrow">Apuração oficial</p><h2>Sortear</h2><p>Selecione uma campanha. Cada cota de pedido pago participa uma vez, e todo resultado fica registrado para conferência.</p></div>
            <span class="draw-integrity">Seleção aleatória auditável</span>
        </header>

        <?php if (!$schemaReady): ?><div class="draw-feedback error show">Não foi possível preparar o histórico de sorteios. Verifique a permissão do banco.</div><?php endif; ?>
        <section class="draw-card">
            <form class="draw-controls" id="draw-form">
                <input type="hidden" name="csrf" value="<?= $escape($_SESSION['draw_csrf']) ?>">
                <div class="draw-field">
                    <label for="draw-product">Campanha que será sorteada</label>
                    <select class="draw-select" id="draw-product" name="product_id" required <?= !$schemaReady ? 'disabled' : '' ?>>
                        <option value="">Selecione uma campanha</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id'] ?>" data-eligible="<?= (int) ($eligibleByProduct[(int) $product['id']] ?? 0) ?>"><?= $escape($product['name']) ?><?= (int) $product['status'] === 1 ? '' : ' — inativa' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="draw-button" type="submit" <?= !$schemaReady ? 'disabled' : '' ?>><span>✦</span> Sortear</button>
            </form>

            <div class="draw-stage">
                <div class="draw-idle" id="draw-idle"><div class="draw-symbol">♛</div><h3>Pronto para o sorteio</h3><p>Escolha a campanha acima. O resultado considera exclusivamente cotas vinculadas a pagamentos confirmados.</p></div>
                <div class="draw-running" id="draw-running"><div class="draw-orbit"></div><h3>Sorteio em andamento</h3><p>Selecionando uma cota entre todas as participações elegíveis.</p><span class="draw-rolling" id="draw-rolling">Preparando apuração segura…</span></div>
                <div class="draw-result" id="draw-result"><p class="draw-winner-label">Ganhador</p><h3 id="draw-winner-name"></h3><p class="draw-result-phone" id="draw-winner-phone"></p><div class="draw-result-meta"><span>Cota: <strong id="draw-winning-number"></strong></span><span>Campanha: <strong id="draw-campaign-name"></strong></span><span>Participações: <strong id="draw-eligible-count"></strong></span></div><p class="draw-audit" id="draw-audit-hash"></p></div>
                <div class="draw-countdown"><span id="draw-countdown-label">Aguardando início</span><strong id="draw-countdown">—</strong></div>
            </div>
        </section>
        <div class="draw-feedback" id="draw-feedback" role="status" aria-live="polite"></div>

        <section class="draw-history">
            <div class="draw-history-head"><h3>Histórico de resultados</h3><span>Últimos 12 sorteios oficiais</span></div>
            <div class="draw-history-list" id="draw-history-list">
                <?php if (!$history || $history->num_rows === 0): ?><div class="draw-empty" id="draw-history-empty">Nenhum sorteio realizado até agora.</div><?php else: ?>
                    <?php while ($draw = $history->fetch_assoc()): ?><article class="draw-history-row"><div><strong><?= $escape($draw['winner_name_snapshot']) ?></strong><small><?= $escape($draw['phone_masked_snapshot']) ?></small></div><div><strong><?= $escape($draw['product_name_snapshot']) ?></strong><small><?= $escape(date('d/m/Y H:i', strtotime($draw['date_created']))) ?></small></div><div><strong class="draw-history-number">#<?= $escape($draw['winning_number']) ?></strong><small><?= number_format((int) $draw['eligible_entries'], 0, ',', '.') ?> cotas</small></div><div><strong>Auditado</strong><small><?= $escape(substr($draw['audit_hash'], 0, 16)) ?>…</small></div></article><?php endwhile; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<button class="draw-settings-button" id="draw-settings-open" type="button" aria-label="Configurações do sorteio" title="Configurações do sorteio">⚙</button>
<div class="draw-modal-backdrop" id="draw-settings-modal" aria-hidden="true"><section class="draw-modal" role="dialog" aria-modal="true" aria-labelledby="draw-settings-title"><header class="draw-modal-head"><div><h3 id="draw-settings-title">Configurações do sorteio</h3><p>Regras fixas que protegem a integridade da apuração.</p></div><button class="draw-modal-close" id="draw-settings-close" type="button" aria-label="Fechar">×</button></header><div class="draw-modal-body"><article class="draw-rule"><span class="draw-rule-icon">10</span><div><strong>Animação de 10 segundos</strong><p>A contagem vai de 10 até 0 antes de revelar o resultado já registrado.</p></div></article><article class="draw-rule"><span class="draw-rule-icon">✓</span><div><strong>Somente pagamentos confirmados</strong><p>Pedidos pendentes ou cancelados nunca entram na seleção.</p></div></article><article class="draw-rule"><span class="draw-rule-icon">#</span><div><strong>Cotas sem repetição</strong><p>Uma cota já vencedora nesta campanha não pode ser sorteada novamente.</p></div></article><article class="draw-rule"><span class="draw-rule-icon">⌁</span><div><strong>Privacidade e auditoria</strong><p>O telefone aparece censurado, enquanto o resultado completo continua ligado ao pedido real no banco.</p></div></article></div></section></div>

<script>
(function () {
    var form = document.getElementById('draw-form');
    if (!form) return;
    var button = form.querySelector('.draw-button');
    var product = document.getElementById('draw-product');
    var idle = document.getElementById('draw-idle');
    var running = document.getElementById('draw-running');
    var result = document.getElementById('draw-result');
    var countdown = document.getElementById('draw-countdown');
    var countdownLabel = document.getElementById('draw-countdown-label');
    var rolling = document.getElementById('draw-rolling');
    var feedback = document.getElementById('draw-feedback');
    var activeAnimation = null;

    function setFeedback(type, message) {
        feedback.className = 'draw-feedback show ' + type;
        feedback.textContent = message;
    }
    function clearFeedback() {
        feedback.className = 'draw-feedback';
        feedback.textContent = '';
    }
    function startAnimation() {
        idle.classList.add('hidden');
        result.classList.remove('active');
        running.classList.add('active');
        countdownLabel.textContent = 'Resultado em';
        countdown.textContent = '10';
        countdown.classList.remove('urgent');
        var startedAt = performance.now();
        var rollingTimer = window.setInterval(function () {
            rolling.textContent = 'Verificando cota ' + String(Math.floor(Math.random() * 999999)).padStart(6, '0') + '…';
        }, 120);
        var timer = null;
        var cancelled = false;
        var promise = new Promise(function (resolve) {
            timer = window.setInterval(function () {
                var remainingMs = Math.max(0, 10000 - (performance.now() - startedAt));
                var seconds = Math.ceil(remainingMs / 1000);
                countdown.textContent = String(seconds);
                countdown.classList.toggle('urgent', seconds <= 3);
                if (remainingMs <= 0) {
                    window.clearInterval(timer);
                    window.clearInterval(rollingTimer);
                    countdown.textContent = '0';
                    resolve();
                }
            }, 80);
        });
        return {promise: promise, cancel: function () { if (cancelled) return; cancelled = true; window.clearInterval(timer); window.clearInterval(rollingTimer); }};
    }
    function showResult(draw) {
        running.classList.remove('active');
        result.classList.add('active');
        countdownLabel.textContent = 'Sorteio concluído';
        countdown.textContent = '0';
        document.getElementById('draw-winner-name').textContent = draw.winner;
        document.getElementById('draw-winner-phone').textContent = draw.phone;
        document.getElementById('draw-winning-number').textContent = draw.number;
        document.getElementById('draw-campaign-name').textContent = draw.campaign;
        document.getElementById('draw-eligible-count').textContent = Number(draw.eligible_entries).toLocaleString('pt-BR');
        document.getElementById('draw-audit-hash').textContent = 'Auditoria: ' + draw.audit_hash;
        prependHistory(draw);
    }
    function prependHistory(draw) {
        var list = document.getElementById('draw-history-list');
        var empty = document.getElementById('draw-history-empty');
        if (empty) empty.remove();
        var row = document.createElement('article');
        row.className = 'draw-history-row';
        var values = [
            [draw.winner, draw.phone],
            [draw.campaign, draw.date],
            ['#' + draw.number, Number(draw.eligible_entries).toLocaleString('pt-BR') + ' cotas'],
            ['Auditado', draw.audit_hash.slice(0, 16) + '…']
        ];
        values.forEach(function (value, index) {
            var cell = document.createElement('div');
            var strong = document.createElement('strong');
            var small = document.createElement('small');
            if (index === 2) strong.className = 'draw-history-number';
            strong.textContent = value[0];
            small.textContent = value[1];
            cell.appendChild(strong); cell.appendChild(small); row.appendChild(cell);
        });
        list.prepend(row);
        while (list.children.length > 12) list.lastElementChild.remove();
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (!product.value || button.disabled) {
            setFeedback('error', 'Selecione uma campanha antes de iniciar o sorteio.');
            return;
        }
        clearFeedback();
        button.disabled = true;
        activeAnimation = startAnimation();
        try {
            var response = await fetch('./draw/api.php', {method: 'POST', body: new FormData(form), credentials: 'same-origin'});
            var payload = await response.json().catch(function () { return {}; });
            if (!response.ok || payload.status !== 'success') throw new Error(payload.message || 'Não foi possível concluir o sorteio.');
            await activeAnimation.promise;
            showResult(payload.draw);
            setFeedback('success', 'Sorteio concluído e registrado com sucesso.');
        } catch (error) {
            activeAnimation.cancel();
            running.classList.remove('active');
            result.classList.remove('active');
            idle.classList.remove('hidden');
            countdownLabel.textContent = 'Aguardando início';
            countdown.textContent = '—';
            countdown.classList.remove('urgent');
            setFeedback('error', error.message || 'Não foi possível concluir o sorteio.');
        } finally {
            button.disabled = false;
            activeAnimation = null;
        }
    });

    var modal = document.getElementById('draw-settings-modal');
    function setModal(open) { modal.classList.toggle('open', open); modal.setAttribute('aria-hidden', open ? 'false' : 'true'); }
    document.getElementById('draw-settings-open').addEventListener('click', function () { setModal(true); });
    document.getElementById('draw-settings-close').addEventListener('click', function () { setModal(false); });
    modal.addEventListener('click', function (event) { if (event.target === modal) setModal(false); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') setModal(false); });
})();
</script>
