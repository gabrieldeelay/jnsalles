<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

if (empty($_SESSION['major_minor_ticket_csrf'])) {
    $_SESSION['major_minor_ticket_csrf'] = bin2hex(random_bytes(24));
}
$csrfToken = (string) $_SESSION['major_minor_ticket_csrf'];

$products = [];
$productsQuery = $conn->query(
    "SELECT id, name, status FROM product_list WHERE delete_flag = 0 ORDER BY CASE status WHEN 1 THEN 0 WHEN 2 THEN 1 ELSE 2 END, id DESC"
);
while ($productsQuery && ($product = $productsQuery->fetch_assoc())) {
    $products[] = $product;
}

$selectedProductId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : (int) ($_GET['product_id'] ?? 0);
if ($selectedProductId <= 0 && $products) {
    $selectedProductId = (int) $products[0]['id'];
}

$productExists = false;
foreach ($products as $product) {
    if ((int) $product['id'] === $selectedProductId) {
        $productExists = true;
        break;
    }
}
if (!$productExists && $products) {
    $selectedProductId = (int) $products[0]['id'];
}

$settingKey = 'major_minor_ticket_' . $selectedProductId;
$defaultConfig = [
    'enabled' => false,
    'minor' => ['name' => '', 'ticket' => ''],
    'major' => ['name' => '', 'ticket' => ''],
];
$storedConfig = json_decode((string) $_settings->info($settingKey), true);
$config = is_array($storedConfig) ? array_replace_recursive($defaultConfig, $storedConfig) : $defaultConfig;
$feedback = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $postedToken)) {
        $feedback = ['type' => 'error', 'message' => 'A sessão expirou. Atualize a página e tente novamente.'];
    } elseif (!$productExists) {
        $feedback = ['type' => 'error', 'message' => 'Selecione uma campanha válida.'];
    } else {
        $enabled = isset($_POST['enabled']);
        $minorName = trim((string) ($_POST['minor_name'] ?? ''));
        $minorTicket = trim((string) ($_POST['minor_ticket'] ?? ''));
        $majorName = trim((string) ($_POST['major_name'] ?? ''));
        $majorTicket = trim((string) ($_POST['major_ticket'] ?? ''));

        $shorten = static function ($value, $length) {
            return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
        };
        $minorName = $shorten($minorName, 120);
        $minorTicket = $shorten($minorTicket, 80);
        $majorName = $shorten($majorName, 120);
        $majorTicket = $shorten($majorTicket, 80);

        if ($enabled && ($minorName === '' || $minorTicket === '' || $majorName === '' || $majorTicket === '')) {
            $feedback = ['type' => 'error', 'message' => 'Para habilitar, preencha nome e cota nos dois destaques.'];
        } else {
            $config = [
                'enabled' => $enabled,
                'minor' => ['name' => $minorName, 'ticket' => $minorTicket],
                'major' => ['name' => $majorName, 'ticket' => $majorTicket],
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $encodedConfig = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $existingSettingId = 0;
            $lookup = $conn->prepare('SELECT id FROM system_info WHERE meta_field = ? ORDER BY id DESC LIMIT 1');
            if ($lookup) {
                $lookup->bind_param('s', $settingKey);
                $lookup->execute();
                $lookupResult = $lookup->get_result();
                $lookupRow = $lookupResult ? $lookupResult->fetch_assoc() : null;
                $existingSettingId = (int) ($lookupRow['id'] ?? 0);
                $lookup->close();
            }

            if ($existingSettingId > 0) {
                $save = $conn->prepare('UPDATE system_info SET meta_value = ? WHERE id = ?');
                if ($save) {
                    $save->bind_param('si', $encodedConfig, $existingSettingId);
                }
            } else {
                $save = $conn->prepare('INSERT INTO system_info (meta_field, meta_value) VALUES (?, ?)');
                if ($save) {
                    $save->bind_param('ss', $settingKey, $encodedConfig);
                }
            }

            $saved = $save ? $save->execute() : false;
            if ($save) {
                $save->close();
            }

            if ($saved) {
                $_settings->set_info($settingKey, $encodedConfig);
                $feedback = [
                    'type' => 'success',
                    'message' => $enabled
                        ? 'Destaques publicados abaixo da ação desta campanha.'
                        : 'Exibição desativada para esta campanha.',
                ];
            } else {
                $feedback = ['type' => 'error', 'message' => 'Não foi possível salvar. Tente novamente.'];
            }
        }
    }
}

$isEnabled = !empty($config['enabled']);
$minorName = (string) ($config['minor']['name'] ?? '');
$minorTicket = (string) ($config['minor']['ticket'] ?? '');
$majorName = (string) ($config['major']['name'] ?? '');
$majorTicket = (string) ($config['major']['ticket'] ?? '');
$consultNow = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
$consultStartValue = $consultNow->setTime(0, 0)->format('Y-m-d\TH:i');
$consultEndValue = $consultNow->setTime(23, 59)->format('Y-m-d\TH:i');
?>

<style>
.mm-shell{max-width:1180px;padding:32px 26px 60px;color:#e5edf8}.mm-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:18px}.mm-eyebrow{margin:0 0 6px;color:#a78bfa;font-size:11px;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.mm-head h2{margin:0;color:#fff;font-size:32px;font-weight:900;letter-spacing:-.045em}.mm-head p{max-width:690px;margin:7px 0 0;color:#91a2ba;font-size:13px;line-height:1.55}.mm-status{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #34425a;border-radius:999px;background:#111a2a;color:#cbd5e1;font-size:11px;font-weight:850;white-space:nowrap}.mm-status:before{width:8px;height:8px;border-radius:50%;background:#64748b;content:""}.mm-status.enabled:before{background:#34d399;box-shadow:0 0 0 5px rgba(52,211,153,.12)}
.mm-grid{display:grid;grid-template-columns:minmax(420px,1fr) minmax(360px,.88fr);gap:16px;align-items:start}.mm-card{overflow:hidden;border:1px solid #2d3a50;border-radius:18px;background:linear-gradient(145deg,rgba(28,39,57,.94),rgba(15,23,42,.98));box-shadow:0 20px 48px rgba(0,0,0,.16)}.mm-card-head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #2b384d}.mm-card-icon{display:grid;width:40px;height:40px;flex:0 0 40px;place-items:center;border:1px solid rgba(167,139,250,.16);border-radius:12px;background:linear-gradient(145deg,rgba(124,58,237,.42),rgba(91,33,182,.22));color:#e9e5ff;font-size:17px}.mm-card-icon svg{width:20px;height:20px}.mm-card-head h3{margin:0;color:#f8fafc;font-size:17px;font-weight:850}.mm-card-head p{margin:4px 0 0;color:#8da0ba;font-size:11px;line-height:1.4}.mm-card-body{padding:20px}.mm-form{display:grid;gap:16px}.mm-field label,.mm-field-title{display:block;margin:0 0 7px;color:#cbd5e1;font-size:11px;font-weight:800}.mm-field select,.mm-field input[type=text]{width:100%;height:46px!important;padding:0 14px!important;border:1px solid #40506a!important;border-radius:10px!important;background:#0c1526!important;color:#f8fafc!important;font-size:12px!important;box-shadow:none!important;outline:0}.mm-field select:focus,.mm-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.mm-toggle-row{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 15px;border:1px solid #334155;border-radius:12px;background:#10192a}.mm-toggle-copy strong{display:block;color:#f8fafc;font-size:12px}.mm-toggle-copy span{display:block;margin-top:4px;color:#7f91aa;font-size:10px;line-height:1.45}.mm-switch{position:relative;width:50px;height:28px;flex:0 0 50px}.mm-switch input{position:absolute;opacity:0;pointer-events:none}.mm-switch span{position:absolute;inset:0;border:1px solid #475569;border-radius:999px;background:#1e293b;cursor:pointer;transition:.2s}.mm-switch span:before{position:absolute;top:4px;left:4px;width:18px;height:18px;border-radius:50%;background:#94a3b8;content:"";transition:.2s}.mm-switch input:checked+span{border-color:#8b5cf6;background:#6d28d9}.mm-switch input:checked+span:before{background:#fff;transform:translateX(22px)}.mm-pair{display:grid;grid-template-columns:1fr 1fr;gap:11px}.mm-fieldset{display:grid;gap:12px;padding:14px;border:1px solid #303e54;border-radius:13px;background:rgba(11,20,36,.58)}.mm-fieldset legend{padding:0 6px;color:#c4b5fd;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.mm-help{margin:-4px 0 0;color:#71839d;font-size:10px;line-height:1.5}.mm-save{min-height:46px;border:0;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:11px;font-weight:900;box-shadow:0 9px 24px rgba(109,40,217,.24);transition:transform .16s ease}.mm-save:hover{transform:translateY(-1px)}.mm-feedback{margin-bottom:14px;padding:12px 14px;border-radius:11px;font-size:11px;font-weight:750;line-height:1.5}.mm-feedback.success{border:1px solid rgba(52,211,153,.28);background:rgba(6,78,59,.42);color:#a7f3d0}.mm-feedback.error{border:1px solid rgba(248,113,113,.28);background:rgba(127,29,29,.36);color:#fecaca}
.mm-preview-wrap{padding:20px}.mm-preview-label{margin:0 0 11px;color:#71839d;font-size:9px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.mm-preview{overflow:hidden;border:1px solid rgba(34,197,94,.4);border-radius:17px;background:radial-gradient(circle at 50% -20%,rgba(34,197,94,.14),transparent 45%),#090f19;box-shadow:0 18px 40px rgba(0,0,0,.26)}.mm-preview-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.13)}.mm-preview-title{display:flex;align-items:center;gap:9px}.mm-preview-symbol{display:grid;width:29px;height:29px;flex:0 0 29px;place-items:center;border-radius:9px;background:rgba(124,58,237,.28);color:#c4b5fd}.mm-preview-symbol svg{width:15px;height:15px}.mm-preview-title strong{display:block;color:#f8fafc;font-size:13px}.mm-preview-title small{display:block;margin-top:2px;color:#71839d;font-size:9px}.mm-preview-live{color:#6ee7b7;font-size:8px;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.mm-preview-items{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:15px}.mm-preview-item{min-width:0;padding:14px 10px;border:1px solid #29364a;border-radius:13px;background:linear-gradient(145deg,#101a2b,#0b1422);text-align:center}.mm-preview-item:first-child{border-color:rgba(56,189,248,.28)}.mm-preview-item:last-child{border-color:rgba(167,139,250,.3)}.mm-preview-item small{display:block;color:#8797ad;font-size:9px;font-weight:800;text-transform:uppercase}.mm-preview-ticket{display:inline-flex;max-width:100%;margin:10px 0 8px;padding:7px 10px;border:1px solid rgba(52,211,153,.42);border-radius:8px;background:#05090f;color:#fff;font-size:15px;font-weight:950;letter-spacing:.025em;overflow-wrap:anywhere}.mm-preview-name{display:block;overflow:hidden;color:#cbd5e1;font-size:11px;font-weight:750;text-overflow:ellipsis;white-space:nowrap}.mm-preview-off{padding:13px 15px;border-top:1px solid rgba(148,163,184,.11);color:#71839d;font-size:9px;text-align:center}.mm-note{margin-top:13px;padding:12px 13px;border:1px solid #2c394d;border-radius:11px;background:#10192a;color:#8395ad;font-size:10px;line-height:1.55}.mm-note strong{color:#cbd5e1}
.mm-live-card{margin-bottom:16px}.mm-live-card .mm-card-head{justify-content:flex-start}.mm-live-state{display:inline-flex;align-items:center;gap:7px;margin-left:auto;padding:8px 11px;border:1px solid rgba(52,211,153,.22);border-radius:999px;background:rgba(6,78,59,.25);color:#6ee7b7;font-size:9px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.mm-live-state:before{width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.1);content:""}.mm-live-state.loading:before{animation:mmPulse 1s infinite}.mm-live-state.error{border-color:rgba(248,113,113,.25);background:rgba(127,29,29,.22);color:#fca5a5}.mm-live-state.error:before{background:#f87171;box-shadow:0 0 0 4px rgba(248,113,113,.1)}.mm-live-body{padding:18px 20px 20px}.mm-live-controls{display:grid;grid-template-columns:1fr 1fr auto;gap:11px;align-items:end}.mm-live-field label{display:block;margin:0 0 7px;color:#cbd5e1;font-size:10px;font-weight:800}.mm-live-field input{width:100%;height:43px!important;padding:0 12px!important;border:1px solid #40506a!important;border-radius:10px!important;background:#0c1526!important;color:#f8fafc!important;color-scheme:dark;font-size:11px!important;outline:0}.mm-live-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.14)!important}.mm-live-actions{display:flex;gap:8px}.mm-live-button{display:inline-flex;min-height:43px;align-items:center;justify-content:center;gap:7px;padding:0 14px;border:1px solid #40506a;border-radius:10px;background:#142034;color:#dbeafe;font-size:10px;font-weight:900;white-space:nowrap;transition:.16s}.mm-live-button svg{width:15px;height:15px}.mm-live-button.primary{border-color:#7c3aed;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff}.mm-live-button:hover{transform:translateY(-1px)}.mm-live-button:disabled{cursor:wait;opacity:.62;transform:none}.mm-live-results{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:15px}.mm-live-result{display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:13px;align-items:center;padding:15px;border:1px solid #303e54;border-radius:14px;background:linear-gradient(145deg,#101a2b,#0b1422)}.mm-live-result.minor{border-color:rgba(56,189,248,.25)}.mm-live-result.major{border-color:rgba(167,139,250,.3)}.mm-live-result-icon{display:grid;width:44px;height:44px;place-items:center;border-radius:12px;background:rgba(14,165,233,.12);color:#7dd3fc}.mm-live-result.major .mm-live-result-icon{background:rgba(124,58,237,.16);color:#c4b5fd}.mm-live-result-icon svg{width:20px;height:20px}.mm-live-result-copy small{display:block;color:#71839d;font-size:8px;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.mm-live-result-copy strong{display:block;overflow:hidden;margin-top:4px;color:#f8fafc;font-size:13px;text-overflow:ellipsis;white-space:nowrap}.mm-live-result-copy span{display:block;margin-top:3px;color:#8193ac;font-size:9px}.mm-live-result-ticket{max-width:170px;padding:8px 11px;border:1px solid rgba(52,211,153,.36);border-radius:9px;background:#05090f;color:#fff;font-size:14px;font-weight:950;overflow-wrap:anywhere;text-align:center}.mm-live-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;padding:10px 12px;border-radius:10px;background:#10192a;color:#71839d;font-size:9px}.mm-live-message{color:#fca5a5}.mm-live-help{margin:10px 0 0;color:#71839d;font-size:9px;line-height:1.5}@keyframes mmPulse{50%{opacity:.35;transform:scale(.75)}}
.mm-lookup{display:grid;grid-template-columns:minmax(230px,.72fr) 1fr;gap:14px;align-items:end;margin-top:14px;padding-top:14px;border-top:1px solid #2b384d}.mm-lookup-label{display:block;margin:0 0 7px;color:#cbd5e1;font-size:10px;font-weight:800}.mm-lookup-control{display:flex;gap:8px}.mm-lookup-control input{width:100%;height:40px!important;padding:0 12px!important;border:1px solid #40506a!important;border-radius:9px!important;background:#0c1526!important;color:#f8fafc!important;font-size:11px!important;outline:0}.mm-lookup-control input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.14)!important}.mm-lookup-control button{display:grid;width:42px;height:40px;flex:0 0 42px;place-items:center;border:1px solid #7c3aed;border-radius:9px;background:#6d28d9;color:#fff}.mm-lookup-control button svg{width:16px;height:16px}.mm-lookup-control button:disabled{cursor:wait;opacity:.6}.mm-lookup-result{display:grid;grid-template-columns:1.1fr .65fr 1fr;gap:8px}.mm-lookup-result[hidden]{display:none}.mm-lookup-item{min-width:0;padding:9px 11px;border:1px solid #303e54;border-radius:9px;background:#10192a}.mm-lookup-item small{display:block;color:#71839d;font-size:7px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.mm-lookup-item strong{display:block;overflow:hidden;margin-top:3px;color:#e2e8f0;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.mm-lookup-message{align-self:center;color:#fca5a5;font-size:9px}
@media(max-width:980px){.mm-grid{grid-template-columns:1fr}.mm-live-controls{grid-template-columns:1fr 1fr}.mm-live-actions{grid-column:1/-1}.mm-live-button{flex:1}.mm-lookup{grid-template-columns:1fr}}@media(max-width:640px){.mm-shell{padding:24px 14px 46px}.mm-head{align-items:flex-start;flex-direction:column}.mm-head h2{font-size:27px}.mm-pair,.mm-preview-items,.mm-live-controls,.mm-live-results,.mm-lookup-result{grid-template-columns:1fr}.mm-live-actions{grid-column:auto}.mm-card-body,.mm-preview-wrap,.mm-live-body{padding:15px}.mm-live-state{display:none}.mm-live-result{grid-template-columns:40px minmax(0,1fr)}.mm-live-result-ticket{grid-column:1/-1;width:100%;max-width:none}.mm-live-meta{align-items:flex-start;flex-direction:column}}
</style>

<main class="h-full overflow-y-auto">
  <div class="container mx-auto mm-shell">
    <header class="mm-head">
      <div><p class="mm-eyebrow">Consulta e destaque visual</p><h2>Maior/Menor Cota</h2><p>Acompanhe as cotas reais dos pedidos pagos em tempo real e configure, separadamente, os nomes e códigos que serão mostrados abaixo da ação.</p></div>
      <span id="mm-page-status" class="mm-status <?= $isEnabled ? 'enabled' : '' ?>"><?= $isEnabled ? 'Publicado' : 'Desativado' ?></span>
    </header>

    <?php if ($feedback): ?><div class="mm-feedback <?= $escape($feedback['type']) ?>" role="status"><?= $escape($feedback['message']) ?></div><?php endif; ?>

    <?php if (!$products): ?>
      <section class="mm-card"><div class="mm-card-body"><p class="mm-help">Crie uma campanha antes de configurar esta área.</p></div></section>
    <?php else: ?>
      <section class="mm-card mm-live-card" aria-labelledby="mm-live-title">
        <header class="mm-card-head">
          <span class="mm-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2.3-6 4.2 12 2.2-6H21"/><path d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/></svg></span>
          <div><h3 id="mm-live-title">Consulta ao vivo</h3><p>Menor e maior cota entre os pedidos pagos da campanha e do período selecionados.</p></div>
          <span id="mm-live-state" class="mm-live-state loading">Atualizando</span>
        </header>
        <div class="mm-live-body">
          <div class="mm-live-controls">
            <div class="mm-live-field"><label for="mm-consult-start">Início do período</label><input id="mm-consult-start" type="datetime-local" value="<?= $escape($consultStartValue) ?>"></div>
            <div class="mm-live-field"><label for="mm-consult-end">Fim do período</label><input id="mm-consult-end" type="datetime-local" value="<?= $escape($consultEndValue) ?>"></div>
            <div class="mm-live-actions">
              <button id="mm-today" class="mm-live-button" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2v3M18 2v3M3.5 9h17"/><rect x="3.5" y="4" width="17" height="17" rx="2"/><path d="M8 13h3v3H8z"/></svg>Hoje</button>
              <button id="mm-refresh" class="mm-live-button primary" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5"/><path d="M4 17v-5h5"/><path d="M6.1 8a7 7 0 0 1 11.4-2.2L20 8M4 16l2.5 2.2A7 7 0 0 0 17.9 16"/></svg>Consultar</button>
            </div>
          </div>
          <div class="mm-live-results" aria-live="polite">
            <article class="mm-live-result minor">
              <span class="mm-live-result-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16M6 14l6 6 6-6"/></svg></span>
              <div class="mm-live-result-copy"><small>Menor cota real</small><strong id="mm-live-minor-name">Buscando...</strong><span id="mm-live-minor-date">Aguarde a atualização</span></div>
              <span id="mm-live-minor-ticket" class="mm-live-result-ticket">—</span>
            </article>
            <article class="mm-live-result major">
              <span class="mm-live-result-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V4M6 10l6-6 6 6"/></svg></span>
              <div class="mm-live-result-copy"><small>Maior cota real</small><strong id="mm-live-major-name">Buscando...</strong><span id="mm-live-major-date">Aguarde a atualização</span></div>
              <span id="mm-live-major-ticket" class="mm-live-result-ticket">—</span>
            </article>
          </div>
          <div class="mm-live-meta"><span id="mm-live-summary">Consultando pedidos pagos...</span><span id="mm-live-message"></span></div>
          <div class="mm-lookup">
            <div>
              <label class="mm-lookup-label" for="mm-lookup-ticket">Consultar uma cota</label>
              <div class="mm-lookup-control"><input id="mm-lookup-ticket" type="text" inputmode="numeric" maxlength="30" placeholder="Digite o número da cota"><button id="mm-lookup-button" type="button" aria-label="Consultar cota" title="Consultar cota"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></button></div>
            </div>
            <div id="mm-lookup-result" class="mm-lookup-result" hidden>
              <div class="mm-lookup-item"><small>Nome</small><strong id="mm-lookup-name">—</strong></div>
              <div class="mm-lookup-item"><small>Cota</small><strong id="mm-lookup-number">—</strong></div>
              <div class="mm-lookup-item"><small>Comprada em</small><strong id="mm-lookup-date">—</strong></div>
            </div>
            <span id="mm-lookup-message" class="mm-lookup-message"></span>
          </div>
          <p class="mm-live-help">Esta consulta é somente visual e se atualiza automaticamente. Ela não altera pedidos e não preenche os campos publicados abaixo.</p>
        </div>
      </section>
      <div class="mm-grid">
        <section class="mm-card">
          <header class="mm-card-head"><span class="mm-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h10M18 7h2M4 17h2M10 17h10M14 4v6M10 14v6"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg></span><div><h3>Configuração</h3><p>Escolha a campanha e defina exatamente o que será exibido.</p></div></header>
          <div class="mm-card-body">
            <form class="mm-form" method="post" action="./?page=major_minor_ticket&product_id=<?= (int) $selectedProductId ?>">
              <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
              <div class="mm-field"><label for="mm-product">Campanha</label><select id="mm-product" name="product_id"><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (int) $product['id'] === $selectedProductId ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select></div>
              <div class="mm-toggle-row"><div class="mm-toggle-copy"><strong>Exibir abaixo da ação</strong><span>Quando ativado, os dois destaques aparecem na página pública da campanha.</span></div><label class="mm-switch"><input id="mm-enabled" type="checkbox" name="enabled" value="1" <?= $isEnabled ? 'checked' : '' ?>><span></span></label></div>
              <div class="mm-pair">
                <fieldset class="mm-fieldset"><legend>Menor Cota</legend><div class="mm-field"><label for="mm-minor-name">Nome exibido</label><input id="mm-minor-name" type="text" name="minor_name" maxlength="120" value="<?= $escape($minorName) ?>" placeholder="Ex.: Maicon"></div><div class="mm-field"><label for="mm-minor-ticket">Cota exibida</label><input id="mm-minor-ticket" type="text" name="minor_ticket" maxlength="80" value="<?= $escape($minorTicket) ?>" placeholder="Ex.: 0000195"></div></fieldset>
                <fieldset class="mm-fieldset"><legend>Maior Cota</legend><div class="mm-field"><label for="mm-major-name">Nome exibido</label><input id="mm-major-name" type="text" name="major_name" maxlength="120" value="<?= $escape($majorName) ?>" placeholder="Ex.: Lucas"></div><div class="mm-field"><label for="mm-major-ticket">Cota exibida</label><input id="mm-major-ticket" type="text" name="major_ticket" maxlength="80" value="<?= $escape($majorTicket) ?>" placeholder="Ex.: 1999842"></div></fieldset>
              </div>
              <p class="mm-help">Os campos aceitam texto livre. Você pode usar qualquer nome e qualquer código de cota.</p>
              <button class="mm-save" type="submit">Salvar configuração</button>
            </form>
          </div>
        </section>

        <aside class="mm-card">
          <header class="mm-card-head"><span class="mm-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></span><div><h3>Prévia</h3><p>Veja como o bloco ficará na página da campanha.</p></div></header>
          <div class="mm-preview-wrap">
            <p class="mm-preview-label">Visualização pública</p>
            <div class="mm-preview">
              <div class="mm-preview-head"><div class="mm-preview-title"><span class="mm-preview-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 0 0 2-2h12a2 2 0 0 0 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 0-2 2H6a2 2 0 0 0-2-2v-3a2 2 0 0 0 0-4V7Z"/><path d="M12 7v10"/></svg></span><span><strong>Maior/Menor Cota</strong><small>Destaques da ação</small></span></div><span class="mm-preview-live">Em destaque</span></div>
              <div class="mm-preview-items">
                <div class="mm-preview-item"><small>Menor Cota</small><span id="preview-minor-ticket" class="mm-preview-ticket"><?= $escape($minorTicket ?: '0000000') ?></span><strong id="preview-minor-name" class="mm-preview-name"><?= $escape($minorName ?: 'Nome exibido') ?></strong></div>
                <div class="mm-preview-item"><small>Maior Cota</small><span id="preview-major-ticket" class="mm-preview-ticket"><?= $escape($majorTicket ?: '0000000') ?></span><strong id="preview-major-name" class="mm-preview-name"><?= $escape($majorName ?: 'Nome exibido') ?></strong></div>
              </div>
              <div id="mm-preview-state" class="mm-preview-off"><?= $isEnabled ? 'Este bloco está publicado.' : 'Prévia visível somente no painel enquanto a exibição estiver desativada.' ?></div>
            </div>
            <div class="mm-note"><strong>Importante:</strong> os campos publicados são independentes da consulta ao vivo. Nenhum resultado real é copiado automaticamente e nada interfere no sorteio.</div>
          </div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var product = document.getElementById('mm-product');
  if (product) product.addEventListener('change', function () {
    window.location.href = './?page=major_minor_ticket&product_id=' + encodeURIComponent(product.value);
  });

  var bindings = [
    ['mm-minor-name', 'preview-minor-name', 'Nome exibido'],
    ['mm-minor-ticket', 'preview-minor-ticket', '0000000'],
    ['mm-major-name', 'preview-major-name', 'Nome exibido'],
    ['mm-major-ticket', 'preview-major-ticket', '0000000']
  ];
  bindings.forEach(function (binding) {
    var input = document.getElementById(binding[0]);
    var output = document.getElementById(binding[1]);
    if (!input || !output) return;
    input.addEventListener('input', function () { output.textContent = input.value.trim() || binding[2]; });
  });

  var enabled = document.getElementById('mm-enabled');
  var state = document.getElementById('mm-preview-state');
  var pageStatus = document.getElementById('mm-page-status');
  if (enabled) enabled.addEventListener('change', function () {
    state.textContent = enabled.checked ? 'O bloco será publicado depois que você salvar.' : 'A exibição será desativada depois que você salvar.';
    pageStatus.textContent = enabled.checked ? 'Pronto para publicar' : 'Será desativado';
    pageStatus.classList.toggle('enabled', enabled.checked);
  });

  var liveEndpoint = <?= json_encode(BASE_URL . 'class/Main.php?action=major_minor_ticket_consult', JSON_UNESCAPED_SLASHES) ?>;
  var lookupEndpoint = <?= json_encode(BASE_URL . 'class/Main.php?action=major_minor_ticket_lookup', JSON_UNESCAPED_SLASHES) ?>;
  var liveProductId = <?= (int) $selectedProductId ?>;
  var liveToken = <?= json_encode($csrfToken) ?>;
  var liveStart = document.getElementById('mm-consult-start');
  var liveEnd = document.getElementById('mm-consult-end');
  var liveToday = document.getElementById('mm-today');
  var liveRefresh = document.getElementById('mm-refresh');
  var liveState = document.getElementById('mm-live-state');
  var liveSummary = document.getElementById('mm-live-summary');
  var liveMessage = document.getElementById('mm-live-message');
  var liveLoading = false;
  var liveChangeTimer = null;
  var lookupInput = document.getElementById('mm-lookup-ticket');
  var lookupButton = document.getElementById('mm-lookup-button');
  var lookupResult = document.getElementById('mm-lookup-result');
  var lookupMessage = document.getElementById('mm-lookup-message');

  function localDateTimeValue(date) {
    function two(value) { return String(value).padStart(2, '0'); }
    return date.getFullYear() + '-' + two(date.getMonth() + 1) + '-' + two(date.getDate()) + 'T' + two(date.getHours()) + ':' + two(date.getMinutes());
  }

  function renderLiveEntry(prefix, entry) {
    var name = document.getElementById('mm-live-' + prefix + '-name');
    var date = document.getElementById('mm-live-' + prefix + '-date');
    var ticket = document.getElementById('mm-live-' + prefix + '-ticket');
    if (!entry) {
      name.textContent = 'Nenhuma cota encontrada';
      date.textContent = 'Sem pedido pago no período';
      ticket.textContent = '—';
      return;
    }
    name.textContent = entry.name;
    date.textContent = entry.date;
    ticket.textContent = entry.cota;
  }

  function setLiveState(label, mode) {
    liveState.textContent = label;
    liveState.classList.toggle('loading', mode === 'loading');
    liveState.classList.toggle('error', mode === 'error');
  }

  function loadLiveConsultation() {
    if (liveLoading || !liveStart.value || !liveEnd.value) return;
    liveLoading = true;
    liveRefresh.disabled = true;
    setLiveState('Atualizando', 'loading');
    liveMessage.textContent = '';
    var body = new URLSearchParams({
      csrf_token: liveToken,
      product_id: String(liveProductId),
      start_at: liveStart.value,
      end_at: liveEnd.value
    });
    fetch(liveEndpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
      body: body.toString()
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      if (!data || data.status !== 'success') throw new Error(data && (data.msg || data.error) ? (data.msg || data.error) : 'Não foi possível consultar agora.');
      renderLiveEntry('minor', data.minor);
      renderLiveEntry('major', data.major);
      liveSummary.textContent = data.summary.orders + ' pedidos pagos • ' + data.summary.numbers + ' cotas analisadas • atualizado em ' + data.summary.updated_at;
      liveMessage.textContent = data.msg || '';
      setLiveState('Ao vivo', 'ready');
    }).catch(function (error) {
      liveMessage.textContent = error.message || 'Não foi possível consultar agora.';
      setLiveState('Erro', 'error');
    }).finally(function () {
      liveLoading = false;
      liveRefresh.disabled = false;
    });
  }

  liveToday.addEventListener('click', function () {
    var beginning = new Date();
    var ending = new Date();
    beginning.setHours(0, 0, 0, 0);
    ending.setHours(23, 59, 0, 0);
    liveStart.value = localDateTimeValue(beginning);
    liveEnd.value = localDateTimeValue(ending);
    loadLiveConsultation();
  });
  liveRefresh.addEventListener('click', loadLiveConsultation);
  [liveStart, liveEnd].forEach(function (input) {
    input.addEventListener('change', function () {
      window.clearTimeout(liveChangeTimer);
      liveChangeTimer = window.setTimeout(loadLiveConsultation, 250);
    });
  });

  function lookupTicket() {
    var ticket = lookupInput.value.trim();
    lookupResult.hidden = true;
    lookupMessage.textContent = '';
    if (!ticket) {
      lookupMessage.textContent = 'Digite uma cota para consultar.';
      return;
    }
    lookupButton.disabled = true;
    var body = new URLSearchParams({csrf_token: liveToken, product_id: String(liveProductId), ticket: ticket});
    fetch(lookupEndpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
      body: body.toString()
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      if (!data || data.status !== 'success') throw new Error(data && (data.msg || data.error) ? (data.msg || data.error) : 'Não foi possível consultar a cota.');
      if (!data.found) {
        lookupMessage.textContent = data.msg || 'Cota não encontrada.';
        return;
      }
      document.getElementById('mm-lookup-name').textContent = data.entry.name;
      document.getElementById('mm-lookup-number').textContent = data.entry.cota;
      document.getElementById('mm-lookup-date').textContent = data.entry.date;
      lookupResult.hidden = false;
    }).catch(function (error) {
      lookupMessage.textContent = error.message || 'Não foi possível consultar a cota.';
    }).finally(function () {
      lookupButton.disabled = false;
    });
  }

  lookupInput.addEventListener('input', function () {
    lookupInput.value = lookupInput.value.replace(/\D+/g, '');
  });
  lookupInput.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      lookupTicket();
    }
  });
  lookupButton.addEventListener('click', lookupTicket);
  loadLiveConsultation();
  window.setInterval(function () {
    if (!document.hidden) loadLiveConsultation();
  }, 5000);
});
</script>
