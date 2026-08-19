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
?>

<style>
    .draw-shell{max-width:1180px;padding:30px 24px 90px}.draw-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px}.draw-eyebrow{margin:0 0 5px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.draw-head h2{margin:0;color:#f8fafc;font-size:30px;font-weight:800;letter-spacing:-.035em}.draw-head p{max-width:720px;margin:7px 0 0;color:#94a3b8;font-size:13px;line-height:1.6}.draw-integrity{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(52,211,153,.32);border-radius:999px;background:rgba(6,78,59,.22);color:#a7f3d0;font-size:10px;font-weight:750;white-space:nowrap}.draw-integrity:before{content:'';width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.12)}
    .draw-card{overflow:hidden;border:1px solid #303d51;border-radius:18px;background:linear-gradient(150deg,#182235,#111827);box-shadow:0 24px 60px rgba(0,0,0,.22)}.draw-controls{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;padding:18px;border-bottom:1px solid #2d394b;background:rgba(15,23,42,.46)}.draw-field label{display:block;margin-bottom:7px;color:#aebdd0;font-size:11px;font-weight:750}.draw-select{width:100%;height:48px!important;margin:0!important;border:1px solid #405069!important;border-radius:11px!important;background:#0f172a!important;color:#f8fafc!important;font-size:13px!important;box-shadow:none!important}.draw-select option{background:#0f172a;color:#f8fafc}.draw-select:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.17)!important;outline:0}.draw-button{align-self:end;display:inline-flex;min-width:170px;height:48px;align-items:center;justify-content:center;gap:9px;border:0;border-radius:11px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:13px;font-weight:850;box-shadow:0 10px 25px rgba(109,40,217,.28);transition:.18s}.draw-button:hover{transform:translateY(-1px);filter:brightness(1.08)}.draw-button:disabled{cursor:wait;opacity:.58;transform:none}
    .draw-stage{position:relative;display:flex;min-height:430px;align-items:center;justify-content:center;padding:44px 22px 84px;overflow:hidden;text-align:center}.draw-stage:before,.draw-stage:after{content:'';position:absolute;width:300px;height:300px;border-radius:50%;filter:blur(4px);opacity:.18}.draw-stage:before{top:-170px;left:-80px;background:#7c3aed}.draw-stage:after{right:-110px;bottom:-190px;background:#0ea5e9}.draw-idle,.draw-running,.draw-result{position:relative;z-index:2;width:100%;max-width:760px}.draw-symbol{display:grid;width:92px;height:92px;margin:0 auto 20px;place-items:center;border:1px solid rgba(167,139,250,.35);border-radius:27px;background:linear-gradient(145deg,rgba(124,58,237,.3),rgba(30,41,59,.85));color:#ddd6fe;font-size:39px;box-shadow:0 18px 45px rgba(0,0,0,.2)}.draw-idle h3,.draw-running h3{margin:0;color:#f8fafc;font-size:25px;font-weight:800}.draw-idle p,.draw-running p{margin:9px auto 0;color:#91a1b6;font-size:13px;line-height:1.6}.draw-running,.draw-result{display:none}.draw-running.active,.draw-result.active{display:block}.draw-idle.hidden{display:none}.draw-orbit{position:relative;width:120px;height:120px;margin:0 auto 20px}.draw-orbit:before,.draw-orbit:after{content:'';position:absolute;border-radius:50%}.draw-orbit:before{inset:0;border:3px solid rgba(139,92,246,.18);border-top-color:#a78bfa;animation:draw-spin 1s linear infinite}.draw-orbit:after{inset:28px;background:radial-gradient(circle at 35% 30%,#c4b5fd,#7c3aed 58%,#312e81);box-shadow:0 0 40px rgba(124,58,237,.45);animation:draw-pulse .8s ease-in-out infinite alternate}.draw-rolling{display:inline-flex;min-height:34px;align-items:center;margin-top:18px;padding:7px 13px;border:1px solid #334155;border-radius:999px;background:#0f172a;color:#cbd5e1;font-size:11px;font-variant-numeric:tabular-nums}.draw-countdown{position:absolute;right:0;bottom:0;left:0;z-index:3;display:flex;height:62px;align-items:center;justify-content:center;gap:9px;border-top:1px solid #2d394b;background:rgba(9,14,24,.92);color:#94a3b8;font-size:11px;font-weight:750;text-transform:uppercase;letter-spacing:.08em}.draw-countdown strong{color:#fff;font-size:25px;font-variant-numeric:tabular-nums;letter-spacing:0}.draw-countdown strong.urgent{color:#f87171}.draw-winner-label{margin:0 0 10px;color:#a78bfa;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.draw-result.simulation .draw-winner-label{color:#94a3b8}.draw-result.simulation h3{color:#fff}.draw-result h3{margin:0;color:#fff;font-size:clamp(32px,5vw,56px);font-weight:850;line-height:1.08;letter-spacing:-.045em;text-shadow:0 10px 35px rgba(0,0,0,.28)}.draw-result-phone{margin:14px 0 0;color:#cbd5e1;font-size:19px;font-weight:650;letter-spacing:.04em}.draw-result-meta{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:20px}.draw-result-meta span{padding:7px 10px;border:1px solid #354258;border-radius:999px;background:#111827;color:#aebdd0;font-size:10px}.draw-result-meta strong{color:#f8fafc}.draw-audit{margin:14px auto 0;color:#64748b;font-family:monospace;font-size:9px;word-break:break-all}.draw-result.simulation .draw-audit{color:#64748b;font-family:inherit;font-size:9px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
    .draw-feedback{display:none;margin:14px 0 0;padding:12px 14px;border-radius:11px;font-size:12px;font-weight:700}.draw-feedback.show{display:block}.draw-feedback.error{border:1px solid rgba(248,113,113,.35);background:#7f1d1d;color:#fee2e2}.draw-feedback.success{border:1px solid rgba(52,211,153,.3);background:#064e3b;color:#d1fae5}.draw-settings-button{position:fixed;right:24px;bottom:24px;z-index:35;display:grid;width:45px;height:45px;place-items:center;border:1px solid #46536a;border-radius:14px;background:#172033;color:#cbd5e1;font-size:17px;box-shadow:0 12px 30px rgba(0,0,0,.32)}.draw-settings-button:hover{border-color:#8b5cf6;color:#fff}.draw-queue-badge{position:absolute;top:-7px;right:-7px;display:none;min-width:20px;height:20px;padding:0 5px;align-items:center;justify-content:center;border:2px solid #111827;border-radius:999px;background:#8b5cf6;color:#fff;font-size:10px;font-weight:900}.draw-queue-badge.active{display:flex}.draw-modal-backdrop{position:fixed;inset:0;z-index:60;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(2,6,23,.76);backdrop-filter:blur(6px)}.draw-modal-backdrop.open{display:flex}.draw-modal{width:min(560px,100%);overflow:hidden;border:1px solid #3a475c;border-radius:17px;background:#151d2a;box-shadow:0 28px 75px rgba(0,0,0,.5)}.draw-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;padding:18px;border-bottom:1px solid #2d394b}.draw-modal-head h3{margin:0;color:#f8fafc;font-size:17px}.draw-modal-head p{margin:4px 0 0;color:#8fa2b8;font-size:11px;line-height:1.5}.draw-modal-close{display:grid;width:34px;height:34px;place-items:center;border:1px solid #3a475c;border-radius:9px;background:#111827;color:#cbd5e1;font-size:18px}.draw-modal-body{display:grid;gap:13px;padding:18px}.draw-sim-warning{padding:11px 12px;border:1px solid #334155;border-radius:11px;background:#111827;color:#94a3b8;font-size:10px;font-weight:650;line-height:1.55}.draw-sim-field label{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;color:#dbe5f2;font-size:11px;font-weight:800}.draw-sim-field label span{color:#77869c;font-size:9px;font-weight:650}.draw-sim-input{width:100%;height:44px!important;margin:0!important;padding:0 12px!important;border:1px solid #405069!important;border-radius:10px!important;background:#0f172a!important;color:#f8fafc!important;font-size:12px!important;box-shadow:none!important}.draw-sim-input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.16)!important;outline:0}.draw-sim-status{min-height:18px;color:#94a3b8;font-size:10px;line-height:1.5}.draw-modal-actions{display:flex;justify-content:flex-end;gap:9px;padding-top:3px}.draw-modal-save{height:42px;padding:0 17px;border:0;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:11px;font-weight:850}.draw-denied{margin:30px 0;padding:14px;border-radius:10px;background:#7f1d1d;color:#fee2e2}@keyframes draw-spin{to{transform:rotate(360deg)}}@keyframes draw-pulse{to{transform:scale(1.08)}}
    @media(max-width:720px){.draw-shell{padding:22px 14px 80px}.draw-head{align-items:flex-start;flex-direction:column}.draw-controls{grid-template-columns:1fr}.draw-button{width:100%}.draw-stage{min-height:400px;padding-inline:16px}.draw-settings-button{right:15px;bottom:15px}}
</style>

<main class="h-full overflow-y-auto">
    <div class="container mx-auto draw-shell">
        <header class="draw-head">
            <div><p class="draw-eyebrow">Apuração e simulação</p><h2>Sortear</h2><p>Selecione uma campanha para executar um sorteio oficial entre pagamentos confirmados ou uma simulação educacional claramente identificada.</p></div>
            <span class="draw-integrity">Sorteio oficial auditável</span>
        </header>

        <?php if (!$schemaReady): ?><div class="draw-feedback error show">Não foi possível preparar o sorteio oficial. Verifique a permissão do banco.</div><?php endif; ?>
        <section class="draw-card">
            <form class="draw-controls" id="draw-form">
                <input type="hidden" name="csrf" value="<?= $escape($_SESSION['draw_csrf']) ?>">
                <div class="draw-field">
                    <label for="draw-product">Campanha que será sorteada</label>
                    <select class="draw-select" id="draw-product" name="product_id" required>
                        <option value="">Selecione uma campanha</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id'] ?>" data-eligible="<?= (int) ($eligibleByProduct[(int) $product['id']] ?? 0) ?>"><?= $escape($product['name']) ?><?= (int) $product['status'] === 1 ? '' : ' — inativa' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="draw-button" type="submit"><span>✦</span> Sortear</button>
            </form>

            <div class="draw-stage">
                <div class="draw-idle" id="draw-idle"><div class="draw-symbol">♛</div><h3>Pronto para o sorteio</h3><p>Sem nomes na fila de testes, o resultado oficial considera exclusivamente cotas vinculadas a pagamentos confirmados.</p></div>
                <div class="draw-running" id="draw-running"><div class="draw-orbit"></div><h3>Sorteio em andamento</h3><p id="draw-running-copy">Selecionando uma cota entre todas as participações elegíveis.</p><span class="draw-rolling" id="draw-rolling">Preparando apuração segura…</span></div>
                <div class="draw-result" id="draw-result"><p class="draw-winner-label" id="draw-winner-label">Ganhador oficial</p><h3 id="draw-winner-name"></h3><p class="draw-result-phone" id="draw-winner-phone"></p><div class="draw-result-meta"><span>Cota: <strong id="draw-winning-number"></strong></span><span>Campanha: <strong id="draw-campaign-name"></strong></span><span><span id="draw-eligible-label">Participações:</span> <strong id="draw-eligible-count"></strong></span></div><p class="draw-audit" id="draw-audit-hash"></p></div>
                <div class="draw-countdown"><span id="draw-countdown-label">Aguardando início</span><strong id="draw-countdown">—</strong></div>
            </div>
        </section>
        <div class="draw-feedback" id="draw-feedback" role="status" aria-live="polite"></div>
    </div>
</main>

<button class="draw-settings-button" id="draw-settings-open" type="button" aria-label="Configurações da simulação" title="Configurações da simulação">⚙<span class="draw-queue-badge" id="draw-queue-badge">0</span></button>
<div class="draw-modal-backdrop" id="draw-settings-modal" aria-hidden="true">
    <section class="draw-modal" role="dialog" aria-modal="true" aria-labelledby="draw-settings-title">
        <header class="draw-modal-head"><div><h3 id="draw-settings-title">Fila de simulação</h3><p>Cadastre até três nomes, usados uma única vez e exatamente nesta ordem.</p></div><button class="draw-modal-close" id="draw-settings-close" type="button" aria-label="Fechar">×</button></header>
        <div class="draw-modal-body">
            <div class="draw-sim-warning">Demonstração educacional: os nomes e os dados de exemplo ficam restritos a esta apresentação.</div>
            <div class="draw-sim-field"><label for="draw-sim-name-1">1º próximo nome <span>primeiro sorteio</span></label><input class="draw-sim-input" id="draw-sim-name-1" type="text" maxlength="80" autocomplete="off" placeholder="Ex.: João"></div>
            <div class="draw-sim-field"><label for="draw-sim-name-2">2º próximo nome <span>segundo sorteio</span></label><input class="draw-sim-input" id="draw-sim-name-2" type="text" maxlength="80" autocomplete="off" placeholder="Ex.: Carlos"></div>
            <div class="draw-sim-field"><label for="draw-sim-name-3">3º próximo nome <span>terceiro sorteio</span></label><input class="draw-sim-input" id="draw-sim-name-3" type="text" maxlength="80" autocomplete="off" placeholder="Ex.: Pedro"></div>
            <div class="draw-sim-status" id="draw-sim-status"></div>
            <div class="draw-modal-actions"><button class="draw-modal-save" id="draw-modal-save" type="button">Salvar fila de testes</button></div>
        </div>
    </section>
</div>

<script>
(function () {
    var form = document.getElementById('draw-form');
    if (!form) return;
    var button = form.querySelector('.draw-button');
    var product = document.getElementById('draw-product');
    var idle = document.getElementById('draw-idle');
    var running = document.getElementById('draw-running');
    var runningCopy = document.getElementById('draw-running-copy');
    var result = document.getElementById('draw-result');
    var winnerLabel = document.getElementById('draw-winner-label');
    var countdown = document.getElementById('draw-countdown');
    var countdownLabel = document.getElementById('draw-countdown-label');
    var rolling = document.getElementById('draw-rolling');
    var feedback = document.getElementById('draw-feedback');
    var queueBadge = document.getElementById('draw-queue-badge');
    var simStatus = document.getElementById('draw-sim-status');
    var simInputs = [document.getElementById('draw-sim-name-1'), document.getElementById('draw-sim-name-2'), document.getElementById('draw-sim-name-3')];
    var simulationMemory = [];
    var simulationKey = 'jnsalles_draw_simulation_queue_v1';
    var activeAnimation = null;

    function setFeedback(type, message) {
        feedback.className = 'draw-feedback show ' + type;
        feedback.textContent = message;
    }
    function clearFeedback() {
        feedback.className = 'draw-feedback';
        feedback.textContent = '';
    }
    function normaliseQueue(value) {
        if (!Array.isArray(value)) return [];
        return value.map(function (name) { return String(name).trim(); }).filter(Boolean).slice(0, 3);
    }
    function loadSimulationQueue() {
        try {
            simulationMemory = normaliseQueue(JSON.parse(window.localStorage.getItem(simulationKey) || '[]'));
        } catch (error) {
            simulationMemory = normaliseQueue(simulationMemory);
        }
        return simulationMemory.slice();
    }
    function saveSimulationQueue(queue) {
        simulationMemory = normaliseQueue(queue);
        try {
            window.localStorage.setItem(simulationKey, JSON.stringify(simulationMemory));
        } catch (error) {}
        renderSimulationQueue();
    }
    function renderSimulationQueue() {
        var queue = loadSimulationQueue();
        simInputs.forEach(function (input, index) { input.value = queue[index] || ''; });
        queueBadge.textContent = String(queue.length);
        queueBadge.classList.toggle('active', queue.length > 0);
        simStatus.textContent = queue.length ? queue.length + ' nome(s) aguardando. O próximo clique consumirá “' + queue[0] + '”.' : 'Nenhum nome configurado. O botão realizará o sorteio oficial.';
    }
    function startAnimation(simulation) {
        idle.classList.add('hidden');
        result.classList.remove('active', 'simulation');
        running.classList.add('active');
        runningCopy.textContent = simulation ? 'Executando uma demonstração sem vínculo com compradores reais.' : 'Selecionando uma cota entre todas as participações elegíveis.';
        countdownLabel.textContent = 'Resultado em';
        countdown.textContent = '10';
        countdown.classList.remove('urgent');
        var startedAt = performance.now();
        var rollingTimer = window.setInterval(function () {
            rolling.textContent = (simulation ? 'Simulando número ' : 'Verificando cota ') + String(Math.floor(Math.random() * 999999)).padStart(6, '0') + '…';
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
    function revealBase() {
        running.classList.remove('active');
        result.classList.add('active');
        countdownLabel.textContent = 'Sorteio concluído';
        countdown.textContent = '0';
    }
    function showOfficialResult(draw) {
        revealBase();
        result.classList.remove('simulation');
        winnerLabel.textContent = 'Ganhador oficial';
        document.getElementById('draw-winner-name').textContent = draw.winner;
        document.getElementById('draw-winner-phone').textContent = draw.phone;
        document.getElementById('draw-winning-number').textContent = draw.number;
        document.getElementById('draw-campaign-name').textContent = draw.campaign;
        document.getElementById('draw-eligible-label').textContent = 'Participações:';
        document.getElementById('draw-eligible-count').textContent = Number(draw.eligible_entries).toLocaleString('pt-BR');
        document.getElementById('draw-audit-hash').textContent = 'Auditoria: ' + draw.audit_hash;
    }
    function showSimulationResult(name, preview) {
        revealBase();
        result.classList.add('simulation');
        winnerLabel.textContent = 'Demonstração';
        document.getElementById('draw-winner-name').textContent = name;
        document.getElementById('draw-winner-phone').textContent = preview.phone;
        document.getElementById('draw-winning-number').textContent = preview.number;
        document.getElementById('draw-campaign-name').textContent = product.options[product.selectedIndex].text;
        document.getElementById('draw-eligible-label').textContent = 'Estado:';
        document.getElementById('draw-eligible-count').textContent = 'Cota disponível';
        document.getElementById('draw-audit-hash').textContent = 'Demonstração educacional';
    }
    function resetAfterError() {
        if (activeAnimation) activeAnimation.cancel();
        running.classList.remove('active');
        result.classList.remove('active', 'simulation');
        idle.classList.remove('hidden');
        countdownLabel.textContent = 'Aguardando início';
        countdown.textContent = '—';
        countdown.classList.remove('urgent');
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (!product.value || button.disabled) {
            setFeedback('error', 'Selecione uma campanha antes de iniciar o sorteio.');
            return;
        }
        clearFeedback();
        button.disabled = true;
        var queue = loadSimulationQueue();
        var simulatedName = queue.length ? queue[0] : '';

        if (simulatedName) {
            try {
                var previewData = new FormData(form);
                previewData.set('action', 'simulation_preview');
                var previewResponse = await fetch('./draw/api.php', {method: 'POST', body: previewData, credentials: 'same-origin'});
                var previewPayload = await previewResponse.json().catch(function () { return {}; });
                if (!previewResponse.ok || previewPayload.status !== 'success' || !previewPayload.preview) {
                    throw new Error(previewPayload.message || 'Não foi possível preparar os dados da demonstração.');
                }
                queue.shift();
                saveSimulationQueue(queue);
                activeAnimation = startAnimation(true);
                await activeAnimation.promise;
                showSimulationResult(simulatedName, previewPayload.preview);
            } catch (error) {
                resetAfterError();
                setFeedback('error', 'Não foi possível concluir a simulação.');
            } finally {
                button.disabled = false;
                activeAnimation = null;
            }
            return;
        }

        if (!<?= $schemaReady ? 'true' : 'false' ?>) {
            button.disabled = false;
            setFeedback('error', 'O sorteio oficial não está disponível porque o banco não pôde ser preparado.');
            return;
        }

        activeAnimation = startAnimation(false);
        try {
            var response = await fetch('./draw/api.php', {method: 'POST', body: new FormData(form), credentials: 'same-origin'});
            var payload = await response.json().catch(function () { return {}; });
            if (!response.ok || payload.status !== 'success') throw new Error(payload.message || 'Não foi possível concluir o sorteio.');
            await activeAnimation.promise;
            showOfficialResult(payload.draw);
            setFeedback('success', 'Sorteio oficial concluído e registrado com sucesso.');
        } catch (error) {
            resetAfterError();
            setFeedback('error', error.message || 'Não foi possível concluir o sorteio.');
        } finally {
            button.disabled = false;
            activeAnimation = null;
        }
    });

    var modal = document.getElementById('draw-settings-modal');
    function setModal(open) {
        if (open) renderSimulationQueue();
        modal.classList.toggle('open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }
    document.getElementById('draw-settings-open').addEventListener('click', function () { setModal(true); });
    document.getElementById('draw-settings-close').addEventListener('click', function () { setModal(false); });
    document.getElementById('draw-modal-save').addEventListener('click', function () {
        var queue = simInputs.map(function (input) { return input.value.trim(); }).filter(Boolean).slice(0, 3);
        saveSimulationQueue(queue);
        setModal(false);
    });
    modal.addEventListener('click', function (event) { if (event.target === modal) setModal(false); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') setModal(false); });
    renderSimulationQueue();
})();
</script>
