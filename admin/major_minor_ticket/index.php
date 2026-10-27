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
$brazilNow = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
$todayStart = $brazilNow->setTime(0, 0)->format('Y-m-d\TH:i');
$todayEnd = $brazilNow->setTime(23, 59)->format('Y-m-d\TH:i');
$defaultConfig = [
    'enabled' => false,
    'sources' => ['minor' => 'manual', 'major' => 'manual'],
    'manual' => [
        'minor' => ['name' => '', 'ticket' => ''],
        'major' => ['name' => '', 'ticket' => ''],
    ],
    'real' => ['start_at' => $todayStart, 'end_at' => $todayEnd],
];
$storedConfig = json_decode((string) $_settings->info($settingKey), true);
$config = is_array($storedConfig) ? array_replace_recursive($defaultConfig, $storedConfig) : $defaultConfig;
if (isset($storedConfig['minor']) || isset($storedConfig['major'])) {
    $config['manual']['minor'] = array_replace($defaultConfig['manual']['minor'], (array) ($storedConfig['minor'] ?? []));
    $config['manual']['major'] = array_replace($defaultConfig['manual']['major'], (array) ($storedConfig['major'] ?? []));
}
if (!isset($storedConfig['sources'])) {
    $legacyMode = (string) ($storedConfig['mode'] ?? 'manual');
    $legacyMode = in_array($legacyMode, ['real', 'manual'], true) ? $legacyMode : 'manual';
    $config['sources'] = ['minor' => $legacyMode, 'major' => $legacyMode];
}
$config['sources']['minor'] = in_array((string) ($config['sources']['minor'] ?? ''), ['real', 'manual'], true) ? (string) $config['sources']['minor'] : 'manual';
$config['sources']['major'] = in_array((string) ($config['sources']['major'] ?? ''), ['real', 'manual'], true) ? (string) $config['sources']['major'] : 'manual';
$feedback = null;

$persistConfig = static function ($conn, $_settings, $settingKey, array $config) {
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
    }
    return $saved;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $postedToken)) {
        $feedback = ['type' => 'error', 'message' => 'A sessão expirou. Atualize a página e tente novamente.'];
    } elseif (!$productExists) {
        $feedback = ['type' => 'error', 'message' => 'Selecione uma campanha válida.'];
    } else {
        $enabled = (string) ($_POST['enabled'] ?? '0') === '1';
        $minorSource = (string) ($_POST['minor_source'] ?? 'manual');
        $majorSource = (string) ($_POST['major_source'] ?? 'manual');
        $minorSource = in_array($minorSource, ['real', 'manual'], true) ? $minorSource : 'manual';
        $majorSource = in_array($majorSource, ['real', 'manual'], true) ? $majorSource : 'manual';
        $minorName = trim((string) ($_POST['minor_name'] ?? ''));
        $minorTicket = trim((string) ($_POST['minor_ticket'] ?? ''));
        $majorName = trim((string) ($_POST['major_name'] ?? ''));
        $majorTicket = trim((string) ($_POST['major_ticket'] ?? ''));
        $realStart = trim((string) ($_POST['real_start_at'] ?? ''));
        $realEnd = trim((string) ($_POST['real_end_at'] ?? ''));

        $shorten = static function ($value, $length) {
            return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
        };
        $minorName = $shorten($minorName, 120);
        $minorTicket = $shorten($minorTicket, 80);
        $majorName = $shorten($majorName, 120);
        $majorTicket = $shorten($majorTicket, 80);

        $realStartDate = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $realStart, new DateTimeZone('America/Sao_Paulo'));
        $realEndDate = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $realEnd, new DateTimeZone('America/Sao_Paulo'));
        $realPeriodValid = $realStartDate && $realEndDate
            && $realStartDate->format('Y-m-d\TH:i') === $realStart
            && $realEndDate->format('Y-m-d\TH:i') === $realEnd
            && $realEndDate >= $realStartDate;

        if ($enabled && $minorSource === 'manual' && ($minorName === '' || $minorTicket === '')) {
            $feedback = ['type' => 'error', 'message' => 'Preencha o nome e a cota manual do destaque Menor Cota.'];
        } elseif ($enabled && $majorSource === 'manual' && ($majorName === '' || $majorTicket === '')) {
            $feedback = ['type' => 'error', 'message' => 'Preencha o nome e a cota manual do destaque Maior Cota.'];
        } elseif ($enabled && ($minorSource === 'real' || $majorSource === 'real') && !$realPeriodValid) {
            $feedback = ['type' => 'error', 'message' => 'Confira as datas e horários do período do modo Real.'];
        } else {
            $config = [
                'enabled' => $enabled,
                'sources' => ['minor' => $minorSource, 'major' => $majorSource],
                'manual' => [
                    'minor' => ['name' => $minorName, 'ticket' => $minorTicket],
                    'major' => ['name' => $majorName, 'ticket' => $majorTicket],
                ],
                'real' => [
                    'start_at' => $realPeriodValid ? $realStart : $todayStart,
                    'end_at' => $realPeriodValid ? $realEnd : $todayEnd,
                ],
                'updated_at' => $brazilNow->format('Y-m-d H:i:s'),
            ];
            $saved = $persistConfig($conn, $_settings, $settingKey, $config);

            if ($saved) {
                $feedback = [
                    'type' => 'success',
                    'message' => $enabled
                        ? 'Exibição ativada. As fontes da menor e da maior cota já serão enviadas aos visitantes sem precisar atualizar a página.'
                        : 'Exibição desativada. Suas configurações continuam salvas para quando você ativar novamente.',
                ];
            } else {
                $feedback = ['type' => 'error', 'message' => 'Não foi possível salvar. Tente novamente.'];
            }
        }
    }
}

$isEnabled = !empty($config['enabled']);
$minorSource = (string) ($config['sources']['minor'] ?? 'manual');
$majorSource = (string) ($config['sources']['major'] ?? 'manual');
$minorName = (string) ($config['manual']['minor']['name'] ?? '');
$minorTicket = (string) ($config['manual']['minor']['ticket'] ?? '');
$majorName = (string) ($config['manual']['major']['name'] ?? '');
$majorTicket = (string) ($config['manual']['major']['ticket'] ?? '');
$consultNow = $brazilNow;
$consultStartValue = (string) ($config['real']['start_at'] ?? $todayStart);
$consultEndValue = (string) ($config['real']['end_at'] ?? $todayEnd);
$usesRealSource = $minorSource === 'real' || $majorSource === 'real';
$usesManualSource = $minorSource === 'manual' || $majorSource === 'manual';
?>

<style>
.mm-shell{max-width:1180px;padding:32px 26px 60px;color:#e5edf8}.mm-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:18px}.mm-eyebrow{margin:0 0 6px;color:#a78bfa;font-size:11px;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.mm-head h2{margin:0;color:#fff;font-size:32px;font-weight:900;letter-spacing:-.045em}.mm-head p{max-width:690px;margin:7px 0 0;color:#91a2ba;font-size:13px;line-height:1.55}.mm-status{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #34425a;border-radius:999px;background:#111a2a;color:#cbd5e1;font-size:11px;font-weight:850;white-space:nowrap}.mm-status:before{width:8px;height:8px;border-radius:50%;background:#64748b;content:""}.mm-status.enabled:before{background:#34d399;box-shadow:0 0 0 5px rgba(52,211,153,.12)}
.mm-grid{display:grid;grid-template-columns:minmax(420px,1fr) minmax(360px,.88fr);gap:16px;align-items:start}.mm-card{overflow:hidden;border:1px solid #2d3a50;border-radius:18px;background:linear-gradient(145deg,rgba(28,39,57,.94),rgba(15,23,42,.98));box-shadow:0 20px 48px rgba(0,0,0,.16)}.mm-card-head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #2b384d}.mm-card-icon{display:grid;width:40px;height:40px;flex:0 0 40px;place-items:center;border:1px solid rgba(167,139,250,.16);border-radius:12px;background:linear-gradient(145deg,rgba(124,58,237,.42),rgba(91,33,182,.22));color:#e9e5ff;font-size:17px}.mm-card-icon svg{width:20px;height:20px}.mm-card-head h3{margin:0;color:#f8fafc;font-size:17px;font-weight:850}.mm-card-head p{margin:4px 0 0;color:#8da0ba;font-size:11px;line-height:1.4}.mm-card-body{padding:20px}.mm-form{display:grid;gap:16px}.mm-field label,.mm-field-title{display:block;margin:0 0 7px;color:#cbd5e1;font-size:11px;font-weight:800}.mm-field select,.mm-field input[type=text]{width:100%;height:46px!important;padding:0 14px!important;border:1px solid #40506a!important;border-radius:10px!important;background:#0c1526!important;color:#f8fafc!important;font-size:12px!important;box-shadow:none!important;outline:0}.mm-field select:focus,.mm-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.mm-toggle-row{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 15px;border:1px solid #334155;border-radius:12px;background:#10192a}.mm-toggle-copy strong{display:block;color:#f8fafc;font-size:12px}.mm-toggle-copy span{display:block;margin-top:4px;color:#7f91aa;font-size:10px;line-height:1.45}.mm-switch{position:relative;width:50px;height:28px;flex:0 0 50px}.mm-switch input{position:absolute;opacity:0;pointer-events:none}.mm-switch span{position:absolute;inset:0;border:1px solid #475569;border-radius:999px;background:#1e293b;cursor:pointer;transition:.2s}.mm-switch span:before{position:absolute;top:4px;left:4px;width:18px;height:18px;border-radius:50%;background:#94a3b8;content:"";transition:.2s}.mm-switch input:checked+span{border-color:#8b5cf6;background:#6d28d9}.mm-switch input:checked+span:before{background:#fff;transform:translateX(22px)}.mm-pair{display:grid;grid-template-columns:1fr 1fr;gap:11px}.mm-fieldset{display:grid;gap:12px;padding:14px;border:1px solid #303e54;border-radius:13px;background:rgba(11,20,36,.58)}.mm-fieldset legend{padding:0 6px;color:#c4b5fd;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.mm-help{margin:-4px 0 0;color:#71839d;font-size:10px;line-height:1.5}.mm-save{min-height:46px;border:0;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:11px;font-weight:900;box-shadow:0 9px 24px rgba(109,40,217,.24);transition:transform .16s ease}.mm-save:hover{transform:translateY(-1px)}.mm-feedback{margin-bottom:14px;padding:12px 14px;border-radius:11px;font-size:11px;font-weight:750;line-height:1.5}.mm-feedback.success{border:1px solid rgba(52,211,153,.28);background:rgba(6,78,59,.42);color:#a7f3d0}.mm-feedback.error{border:1px solid rgba(248,113,113,.28);background:rgba(127,29,29,.36);color:#fecaca}
.mm-preview-wrap{padding:20px}.mm-preview-label{margin:0 0 11px;color:#71839d;font-size:9px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.mm-preview{overflow:hidden;border:1px solid rgba(34,197,94,.4);border-radius:17px;background:radial-gradient(circle at 50% -20%,rgba(34,197,94,.14),transparent 45%),#090f19;box-shadow:0 18px 40px rgba(0,0,0,.26)}.mm-preview-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.13)}.mm-preview-title{display:flex;align-items:center;gap:9px}.mm-preview-symbol{display:grid;width:29px;height:29px;flex:0 0 29px;place-items:center;border-radius:9px;background:rgba(124,58,237,.28);color:#c4b5fd}.mm-preview-symbol svg{width:15px;height:15px}.mm-preview-title strong{display:block;color:#f8fafc;font-size:13px}.mm-preview-title small{display:block;margin-top:2px;color:#71839d;font-size:9px}.mm-preview-live{color:#6ee7b7;font-size:8px;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.mm-preview-items{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:15px}.mm-preview-item{min-width:0;padding:14px 10px;border:1px solid #29364a;border-radius:13px;background:linear-gradient(145deg,#101a2b,#0b1422);text-align:center}.mm-preview-item:first-child{border-color:rgba(56,189,248,.28)}.mm-preview-item:last-child{border-color:rgba(167,139,250,.3)}.mm-preview-item small{display:block;color:#8797ad;font-size:9px;font-weight:800;text-transform:uppercase}.mm-preview-ticket{display:inline-flex;max-width:100%;margin:10px 0 8px;padding:7px 10px;border:1px solid rgba(52,211,153,.42);border-radius:8px;background:#05090f;color:#fff;font-size:15px;font-weight:950;letter-spacing:.025em;overflow-wrap:anywhere}.mm-preview-name{display:block;overflow:hidden;color:#cbd5e1;font-size:11px;font-weight:750;text-overflow:ellipsis;white-space:nowrap}.mm-preview-off{padding:13px 15px;border-top:1px solid rgba(148,163,184,.11);color:#71839d;font-size:9px;text-align:center}.mm-note{margin-top:13px;padding:12px 13px;border:1px solid #2c394d;border-radius:11px;background:#10192a;color:#8395ad;font-size:10px;line-height:1.55}.mm-note strong{color:#cbd5e1}
.mm-preview-live.is-preview{color:#94a3b8}
.mm-live-card{margin-bottom:16px}.mm-live-card .mm-card-head{justify-content:flex-start}.mm-live-state{display:inline-flex;align-items:center;gap:7px;margin-left:auto;padding:8px 11px;border:1px solid rgba(52,211,153,.22);border-radius:999px;background:rgba(6,78,59,.25);color:#6ee7b7;font-size:9px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.mm-live-state:before{width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.1);content:""}.mm-live-state.loading:before{animation:mmPulse 1s infinite}.mm-live-state.error{border-color:rgba(248,113,113,.25);background:rgba(127,29,29,.22);color:#fca5a5}.mm-live-state.error:before{background:#f87171;box-shadow:0 0 0 4px rgba(248,113,113,.1)}.mm-live-body{padding:18px 20px 20px}.mm-live-controls{display:grid;grid-template-columns:1fr 1fr auto;gap:11px;align-items:end}.mm-live-field label{display:block;margin:0 0 7px;color:#cbd5e1;font-size:10px;font-weight:800}.mm-live-field input{width:100%;height:43px!important;padding:0 12px!important;border:1px solid #40506a!important;border-radius:10px!important;background:#0c1526!important;color:#f8fafc!important;color-scheme:dark;font-size:11px!important;outline:0}.mm-live-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.14)!important}.mm-live-actions{display:flex;gap:8px}.mm-live-button{display:inline-flex;min-height:43px;align-items:center;justify-content:center;gap:7px;padding:0 14px;border:1px solid #40506a;border-radius:10px;background:#142034;color:#dbeafe;font-size:10px;font-weight:900;white-space:nowrap;transition:.16s}.mm-live-button svg{width:15px;height:15px}.mm-live-button.primary{border-color:#7c3aed;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff}.mm-live-button:hover{transform:translateY(-1px)}.mm-live-button:disabled{cursor:wait;opacity:.62;transform:none}.mm-live-results{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:15px}.mm-live-result{display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:13px;align-items:center;padding:15px;border:1px solid #303e54;border-radius:14px;background:linear-gradient(145deg,#101a2b,#0b1422)}.mm-live-result.minor{border-color:rgba(56,189,248,.25)}.mm-live-result.major{border-color:rgba(167,139,250,.3)}.mm-live-result-icon{display:grid;width:44px;height:44px;place-items:center;border-radius:12px;background:rgba(14,165,233,.12);color:#7dd3fc}.mm-live-result.major .mm-live-result-icon{background:rgba(124,58,237,.16);color:#c4b5fd}.mm-live-result-icon svg{width:20px;height:20px}.mm-live-result-copy small{display:block;color:#71839d;font-size:8px;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.mm-live-result-copy strong{display:block;overflow:hidden;margin-top:4px;color:#f8fafc;font-size:13px;text-overflow:ellipsis;white-space:nowrap}.mm-live-result-copy span{display:block;margin-top:3px;color:#8193ac;font-size:9px}.mm-live-result-ticket{max-width:170px;padding:8px 11px;border:1px solid rgba(52,211,153,.36);border-radius:9px;background:#05090f;color:#fff;font-size:14px;font-weight:950;overflow-wrap:anywhere;text-align:center}.mm-live-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;padding:10px 12px;border-radius:10px;background:#10192a;color:#71839d;font-size:9px}.mm-live-message{color:#fca5a5}.mm-live-help{margin:10px 0 0;color:#71839d;font-size:9px;line-height:1.5}@keyframes mmPulse{50%{opacity:.35;transform:scale(.75)}}
.mm-lookup{display:grid;grid-template-columns:minmax(230px,.72fr) 1fr;gap:14px;align-items:end;margin-top:14px;padding-top:14px;border-top:1px solid #2b384d}.mm-lookup-label{display:block;margin:0 0 7px;color:#cbd5e1;font-size:10px;font-weight:800}.mm-lookup-control{display:flex;gap:8px}.mm-lookup-control input{width:100%;height:40px!important;padding:0 12px!important;border:1px solid #40506a!important;border-radius:9px!important;background:#0c1526!important;color:#f8fafc!important;font-size:11px!important;outline:0}.mm-lookup-control input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.14)!important}.mm-lookup-control button{display:grid;width:42px;height:40px;flex:0 0 42px;place-items:center;border:1px solid #7c3aed;border-radius:9px;background:#6d28d9;color:#fff}.mm-lookup-control button svg{width:16px;height:16px}.mm-lookup-control button:disabled{cursor:wait;opacity:.6}.mm-lookup-result{display:grid;grid-template-columns:1.1fr .65fr 1fr;gap:8px}.mm-lookup-result[hidden]{display:none}.mm-lookup-item{min-width:0;padding:9px 11px;border:1px solid #303e54;border-radius:9px;background:#10192a}.mm-lookup-item small{display:block;color:#71839d;font-size:7px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.mm-lookup-item strong{display:block;overflow:hidden;margin-top:3px;color:#e2e8f0;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.mm-lookup-message{align-self:center;color:#fca5a5;font-size:9px}
@media(max-width:980px){.mm-grid{grid-template-columns:1fr}.mm-live-controls{grid-template-columns:1fr 1fr}.mm-live-actions{grid-column:1/-1}.mm-live-button{flex:1}.mm-lookup{grid-template-columns:1fr}}@media(max-width:640px){.mm-shell{padding:24px 14px 46px}.mm-head{align-items:flex-start;flex-direction:column}.mm-head h2{font-size:27px}.mm-pair,.mm-preview-items,.mm-live-controls,.mm-live-results,.mm-lookup-result{grid-template-columns:1fr}.mm-live-actions{grid-column:auto}.mm-card-body,.mm-preview-wrap,.mm-live-body{padding:15px}.mm-live-state{display:none}.mm-live-result{grid-template-columns:40px minmax(0,1fr)}.mm-live-result-ticket{grid-column:1/-1;width:100%;max-width:none}.mm-live-meta{align-items:flex-start;flex-direction:column}}
.mm-onboarding{display:grid;gap:12px;margin:0 0 16px}.mm-reset-alert{display:grid;grid-template-columns:46px minmax(0,1fr) auto;gap:13px;align-items:center;padding:14px 16px;border:1px solid rgba(251,191,36,.3);border-radius:15px;background:linear-gradient(135deg,rgba(120,53,15,.28),rgba(51,32,12,.52));box-shadow:0 14px 35px rgba(0,0,0,.12)}.mm-reset-alert__icon{display:grid;width:46px;height:46px;place-items:center;border-radius:13px;background:rgba(251,191,36,.14);color:#fcd34d}.mm-reset-alert__icon svg{width:23px;height:23px}.mm-reset-alert strong{display:block;color:#fef3c7;font-size:12px}.mm-reset-alert p{margin:4px 0 0;color:#d6c7a4;font-size:10px;line-height:1.5}.mm-reset-alert__time{padding:8px 11px;border:1px solid rgba(251,191,36,.26);border-radius:999px;background:rgba(15,23,42,.45);color:#fde68a;font-size:9px;font-weight:900;letter-spacing:.08em;white-space:nowrap}.mm-reset-done{padding:10px 13px;border:1px solid rgba(56,189,248,.24);border-radius:11px;background:rgba(12,74,110,.24);color:#bae6fd;font-size:10px;font-weight:750}.mm-step-flow{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.mm-step{display:flex;align-items:center;gap:9px;padding:10px 12px;border:1px solid #2d3a50;border-radius:11px;background:#10192a;color:#8fa2bb;font-size:9px;font-weight:750}.mm-step b{display:grid;width:23px;height:23px;flex:0 0 23px;place-items:center;border-radius:7px;background:rgba(124,58,237,.24);color:#ddd6fe;font-size:9px}.mm-campaign-picker{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,.65fr);gap:18px;align-items:center;margin-bottom:16px;padding:16px 18px;border:1px solid #34425a;border-radius:15px;background:linear-gradient(145deg,#172236,#111a2a)}.mm-campaign-picker__copy{display:flex;align-items:center;gap:12px}.mm-step-number{display:grid;width:35px;height:35px;flex:0 0 35px;place-items:center;border-radius:11px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:12px;font-weight:950;box-shadow:0 8px 20px rgba(109,40,217,.22)}.mm-campaign-picker strong{display:block;color:#f8fafc;font-size:12px}.mm-campaign-picker p{margin:3px 0 0;color:#8395ad;font-size:9px}.mm-campaign-picker select{width:100%;height:44px!important;padding:0 13px!important;border:1px solid #4a5b76!important;border-radius:10px!important;background:#0c1526!important;color:#fff!important;font-size:11px!important;outline:0}.mm-card-head__step{display:block;margin-bottom:3px;color:#a78bfa;font-size:8px;font-weight:950;letter-spacing:.12em;text-transform:uppercase}.mm-form-guide{display:flex;align-items:center;gap:10px;padding:11px 12px;border:1px solid rgba(167,139,250,.22);border-radius:11px;background:rgba(76,29,149,.12);color:#b9a9cf;font-size:9px;line-height:1.45}.mm-form-guide svg{width:17px;height:17px;flex:0 0 17px;color:#c4b5fd}.mm-expiry-pill{display:inline-flex;align-items:center;gap:6px;margin-top:6px;color:#fcd34d;font-size:8px;font-weight:850}.mm-expiry-pill svg{width:12px;height:12px}.mm-save{display:flex;align-items:center;justify-content:center;gap:8px;font-size:12px}.mm-save svg{width:16px;height:16px}.mm-fieldset{position:relative}.mm-fieldset:before{position:absolute;top:-8px;right:10px;padding:3px 7px;border-radius:999px;background:#18243a;color:#7f91aa;font-size:7px;font-weight:850;letter-spacing:.08em;text-transform:uppercase;content:'Manual'}
.mm-mode-card{margin-bottom:16px;padding:18px;border:1px solid #34425a;border-radius:16px;background:linear-gradient(145deg,#172236,#101827)}.mm-mode-card__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px}.mm-mode-card__head h3{margin:0;color:#f8fafc;font-size:15px;font-weight:900}.mm-mode-card__head p{margin:4px 0 0;color:#8395ad;font-size:10px;line-height:1.5}.mm-mode-options{display:grid;grid-template-columns:1fr 1fr;gap:10px}.mm-mode-option{position:relative;display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:12px;align-items:center;padding:14px;border:1px solid #35445c;border-radius:13px;background:#0d1727;cursor:pointer;transition:.18s}.mm-mode-option:hover{border-color:#5b6d89;transform:translateY(-1px)}.mm-mode-option:has(input:checked){border-color:#8b5cf6;background:linear-gradient(135deg,rgba(109,40,217,.25),rgba(15,23,42,.88));box-shadow:0 0 0 3px rgba(139,92,246,.08)}.mm-mode-option input{position:absolute;opacity:0}.mm-mode-icon{display:grid;width:42px;height:42px;place-items:center;border-radius:12px;background:rgba(56,189,248,.12);color:#7dd3fc}.mm-mode-option:last-child .mm-mode-icon{background:rgba(167,139,250,.14);color:#c4b5fd}.mm-mode-icon svg{width:20px;height:20px}.mm-mode-option strong{display:block;color:#f8fafc;font-size:12px}.mm-mode-option small{display:block;margin-top:4px;color:#7f91aa;font-size:9px;line-height:1.4}.mm-mode-check{display:grid;width:21px;height:21px;place-items:center;border:1px solid #52617a;border-radius:50%;color:transparent}.mm-mode-option:has(input:checked) .mm-mode-check{border-color:#8b5cf6;background:#7c3aed;color:#fff}.mm-mode-check svg{width:12px;height:12px}.mm-publish-row{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:12px;padding:13px 14px;border:1px solid #334155;border-radius:12px;background:#10192a}.mm-publish-row strong{display:block;color:#f8fafc;font-size:11px}.mm-publish-row span{display:block;margin-top:3px;color:#7f91aa;font-size:9px}.mm-mode-save{width:100%;margin-top:14px}.mm-mode-inactive{opacity:.7}.mm-mode-inactive .mm-card-head__step:after{content:' • configuração preservada';color:#64748b}.mm-real-save[hidden],.mm-manual-save[hidden]{display:none!important}
.mm-activation{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:center;padding:14px;border:1px solid #334155;border-radius:13px;background:#0d1727}.mm-activation strong{display:block;color:#f8fafc;font-size:12px}.mm-activation p{margin:4px 0 0;color:#7f91aa;font-size:9px}.mm-activation-buttons{display:flex;gap:8px}.mm-activation-button{min-width:108px;height:39px;border:1px solid #40506a;border-radius:9px;background:#172236;color:#a8b6c9;font-size:10px;font-weight:900;transition:.16s}.mm-activation-button:hover{transform:translateY(-1px)}.mm-activation-button.is-active[data-value="1"]{border-color:#10b981;background:#047857;color:#fff;box-shadow:0 7px 18px rgba(4,120,87,.2)}.mm-activation-button.is-active[data-value="0"]{border-color:#ef4444;background:#991b1b;color:#fff}.mm-source-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px}.mm-source-card{padding:14px;border:1px solid #35445c;border-radius:13px;background:#0d1727}.mm-source-card__head{display:flex;align-items:center;gap:10px;margin-bottom:11px}.mm-source-card__icon{display:grid;width:34px;height:34px;place-items:center;border-radius:10px;background:rgba(56,189,248,.12);color:#7dd3fc}.mm-source-card:last-child .mm-source-card__icon{background:rgba(167,139,250,.14);color:#c4b5fd}.mm-source-card__icon svg{width:17px;height:17px}.mm-source-card strong{display:block;color:#f8fafc;font-size:11px}.mm-source-card small{display:block;margin-top:2px;color:#7f91aa;font-size:8px}.mm-source-options{display:grid;grid-template-columns:1fr 1fr;gap:7px}.mm-source-option{position:relative;padding:10px;border:1px solid #3b4a61;border-radius:9px;background:#111c2d;color:#8fa2bb;font-size:9px;font-weight:850;text-align:center;cursor:pointer}.mm-source-option input{position:absolute;opacity:0}.mm-source-option:has(input:checked){border-color:#8b5cf6;background:rgba(109,40,217,.3);color:#fff;box-shadow:0 0 0 2px rgba(139,92,246,.08)}.mm-save-bar{display:grid;grid-template-columns:minmax(0,1fr) minmax(240px,330px);gap:18px;align-items:center;padding:16px 18px;border:1px solid rgba(139,92,246,.32);border-radius:15px;background:linear-gradient(135deg,rgba(76,29,149,.25),#111a2a)}.mm-save-bar strong{display:block;color:#fff;font-size:12px}.mm-save-bar p{margin:4px 0 0;color:#8fa2bb;font-size:9px}.mm-save-bar .mm-save{width:100%}
@media(max-width:760px){.mm-reset-alert{grid-template-columns:42px 1fr}.mm-reset-alert__time{grid-column:1/-1;text-align:center}.mm-step-flow{grid-template-columns:1fr}.mm-campaign-picker{grid-template-columns:1fr}.mm-campaign-picker__copy{align-items:flex-start}}
@media(max-width:760px){.mm-mode-options{grid-template-columns:1fr}.mm-mode-card__head,.mm-publish-row{align-items:flex-start;flex-direction:column}.mm-publish-row .mm-switch{align-self:flex-end}}
@media(max-width:760px){.mm-activation,.mm-save-bar{grid-template-columns:1fr}.mm-source-grid{grid-template-columns:1fr}.mm-activation-buttons{width:100%}.mm-activation-button{flex:1}}
</style>

<main class="h-full overflow-y-auto">
  <div class="container mx-auto mm-shell">
    <header class="mm-head">
      <div><p class="mm-eyebrow">Consulta e destaque visual</p><h2>Maior/Menor Cota</h2><p>Escolha entre dados reais calculados automaticamente ou nomes e cotas definidos manualmente. O visitante sempre verá o mesmo design.</p></div>
      <span id="mm-page-status" class="mm-status <?= $isEnabled ? 'enabled' : '' ?>"><?= $isEnabled ? 'Exibição ativa' : 'Exibição desativada' ?></span>
    </header>

    <?php if ($feedback): ?><div class="mm-feedback <?= $escape($feedback['type']) ?>" role="status"><?= $escape($feedback['message']) ?></div><?php endif; ?>
    <div class="mm-onboarding">
      <div class="mm-step-flow" aria-label="Como usar esta página">
        <span class="mm-step"><b>1</b> Escolha a campanha</span>
        <span class="mm-step"><b>2</b> Escolha a fonte de cada cota</span>
        <span class="mm-step"><b>3</b> Ative e salve</span>
      </div>
    </div>

    <?php if (!$products): ?>
      <section class="mm-card"><div class="mm-card-body"><p class="mm-help">Crie uma campanha antes de configurar esta área.</p></div></section>
    <?php else: ?>
      <section class="mm-campaign-picker" aria-labelledby="mm-campaign-title">
        <div class="mm-campaign-picker__copy"><span class="mm-step-number">1</span><div><strong id="mm-campaign-title">Escolha a campanha</strong><p>A consulta e a publicação abaixo sempre usam a campanha selecionada aqui.</p></div></div>
        <select id="mm-product" aria-label="Campanha selecionada"><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (int) $product['id'] === $selectedProductId ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select>
      </section>
      <form id="mm-config-form" class="mm-form" method="post" action="./?page=major_minor_ticket&product_id=<?= (int) $selectedProductId ?>">
        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
        <input type="hidden" name="product_id" value="<?= (int) $selectedProductId ?>">
      <section class="mm-mode-card" aria-labelledby="mm-mode-title">
        <div class="mm-mode-card__head"><div><h3 id="mm-mode-title">Controle da exibição</h3><p>Ative ou desative com um clique. Depois escolha separadamente a fonte da menor e da maior cota.</p></div><span class="mm-step-number">2</span></div>
        <input id="mm-enabled" type="hidden" name="enabled" value="<?= $isEnabled ? '1' : '0' ?>">
        <div class="mm-activation"><div><strong id="mm-publish-title"><?= $isEnabled ? 'O bloco está aparecendo no site' : 'O bloco está oculto no site' ?></strong><p>Desativar não apaga nenhuma configuração. Ao ativar novamente, tudo volta como estava.</p></div><div class="mm-activation-buttons"><button class="mm-activation-button <?= $isEnabled ? 'is-active' : '' ?>" type="button" data-value="1">Ativar</button><button class="mm-activation-button <?= !$isEnabled ? 'is-active' : '' ?>" type="button" data-value="0">Desativar</button></div></div>
        <div class="mm-source-grid">
          <section class="mm-source-card"><div class="mm-source-card__head"><span class="mm-source-card__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16M6 14l6 6 6-6"/></svg></span><span><strong>Menor Cota</strong><small>Escolha a origem deste lado</small></span></div><div class="mm-source-options"><label class="mm-source-option"><input type="radio" name="minor_source" value="real" <?= $minorSource === 'real' ? 'checked' : '' ?>>Real</label><label class="mm-source-option"><input type="radio" name="minor_source" value="manual" <?= $minorSource === 'manual' ? 'checked' : '' ?>>Manual</label></div></section>
          <section class="mm-source-card"><div class="mm-source-card__head"><span class="mm-source-card__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V4M6 10l6-6 6 6"/></svg></span><span><strong>Maior Cota</strong><small>Escolha a origem deste lado</small></span></div><div class="mm-source-options"><label class="mm-source-option"><input type="radio" name="major_source" value="real" <?= $majorSource === 'real' ? 'checked' : '' ?>>Real</label><label class="mm-source-option"><input type="radio" name="major_source" value="manual" <?= $majorSource === 'manual' ? 'checked' : '' ?>>Manual</label></div></section>
        </div>
      </section>
      <section id="mm-real-card" class="mm-card mm-live-card" aria-labelledby="mm-live-title">
        <header class="mm-card-head">
          <span class="mm-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2.3-6 4.2 12 2.2-6H21"/><path d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/></svg></span>
          <div><span class="mm-card-head__step">Passo 2 • somente consulta</span><h3 id="mm-live-title">Veja as cotas reais</h3><p>Menor e maior cota entre os pedidos pagos da campanha e do período selecionados.</p></div>
          <span id="mm-live-state" class="mm-live-state loading">Atualizando</span>
        </header>
        <div class="mm-live-body">
          <div class="mm-live-controls">
            <div class="mm-live-field"><label for="mm-consult-start">Início do período</label><input id="mm-consult-start" type="datetime-local" name="real_start_at" value="<?= $escape($consultStartValue) ?>"></div>
            <div class="mm-live-field"><label for="mm-consult-end">Fim do período</label><input id="mm-consult-end" type="datetime-local" name="real_end_at" value="<?= $escape($consultEndValue) ?>"></div>
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
          <p id="mm-live-help" class="mm-live-help"><?= $usesRealSource ? 'Este período alimenta automaticamente todo destaque marcado como Real.' : 'A consulta fica disponível como referência. Marque Real na menor ou na maior cota para usar estes resultados.' ?></p>
        </div>
      </section>
      <div class="mm-grid">
        <section id="mm-manual-card" class="mm-card">
          <header class="mm-card-head"><span class="mm-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h10M18 7h2M4 17h2M10 17h10M14 4v6M10 14v6"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg></span><div><span class="mm-card-head__step">Passo 3 • publicação manual</span><h3>Configure o destaque de hoje</h3><p>Digite exatamente o nome e a cota que devem aparecer no site.</p></div></header>
          <div class="mm-card-body">
              <div class="mm-form-guide"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8h.01M11 12h1v4h1"/><circle cx="12" cy="12" r="9"/></svg><span>Estes dados ficam guardados ao trocar para o modo Real. Quando voltar ao Manual, estarão prontos para uso.</span></div>
              <div class="mm-pair">
                <fieldset class="mm-fieldset"><legend>Menor Cota</legend><div class="mm-field"><label for="mm-minor-name">Nome exibido</label><input id="mm-minor-name" type="text" name="minor_name" maxlength="120" value="<?= $escape($minorName) ?>" placeholder="Ex.: Maicon"></div><div class="mm-field"><label for="mm-minor-ticket">Cota exibida</label><input id="mm-minor-ticket" type="text" name="minor_ticket" maxlength="80" value="<?= $escape($minorTicket) ?>" placeholder="Ex.: 0000195"></div></fieldset>
                <fieldset class="mm-fieldset"><legend>Maior Cota</legend><div class="mm-field"><label for="mm-major-name">Nome exibido</label><input id="mm-major-name" type="text" name="major_name" maxlength="120" value="<?= $escape($majorName) ?>" placeholder="Ex.: Lucas"></div><div class="mm-field"><label for="mm-major-ticket">Cota exibida</label><input id="mm-major-ticket" type="text" name="major_ticket" maxlength="80" value="<?= $escape($majorTicket) ?>" placeholder="Ex.: 1999842"></div></fieldset>
              </div>
              <p class="mm-help">Os campos aceitam texto livre. Confira a prévia ao lado antes de salvar.</p>
          </div>
        </section>

        <aside class="mm-card">
          <header class="mm-card-head"><span class="mm-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></span><div><span class="mm-card-head__step">Prévia antes de salvar</span><h3>Confira como ficará</h3><p id="mm-preview-copy">Cada lado da prévia acompanha a fonte escolhida acima.</p></div></header>
          <div class="mm-preview-wrap">
            <p class="mm-preview-label">Visualização pública</p>
            <div class="mm-preview">
              <div class="mm-preview-head"><div class="mm-preview-title"><span class="mm-preview-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 0 0 2-2h12a2 2 0 0 0 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 0-2 2H6a2 2 0 0 0-2-2v-3a2 2 0 0 0 0-4V7Z"/><path d="M12 7v10"/></svg></span><span><strong>Maior/Menor Cota</strong><small>Destaques da ação</small></span></div><span id="mm-preview-badge" class="mm-preview-live <?= $isEnabled ? '' : 'is-preview' ?>"><?= $isEnabled ? 'Publicado' : 'Somente prévia' ?></span></div>
              <div class="mm-preview-items">
                <div class="mm-preview-item"><small>Menor Cota</small><span id="preview-minor-ticket" class="mm-preview-ticket"><?= $escape($minorTicket ?: '0000000') ?></span><strong id="preview-minor-name" class="mm-preview-name"><?= $escape($minorName ?: 'Nome exibido') ?></strong></div>
                <div class="mm-preview-item"><small>Maior Cota</small><span id="preview-major-ticket" class="mm-preview-ticket"><?= $escape($majorTicket ?: '0000000') ?></span><strong id="preview-major-name" class="mm-preview-name"><?= $escape($majorName ?: 'Nome exibido') ?></strong></div>
              </div>
              <div id="mm-preview-state" class="mm-preview-off"><?= $isEnabled ? 'Este bloco está publicado.' : 'Prévia visível somente no painel enquanto a exibição estiver desativada.' ?></div>
            </div>
            <div id="mm-preview-note" class="mm-note"><strong>Fontes atuais:</strong> menor cota <?= $minorSource === 'real' ? 'Real' : 'Manual' ?> e maior cota <?= $majorSource === 'real' ? 'Real' : 'Manual' ?>. Nada interfere no sorteio.</div>
          </div>
        </aside>
      </div>
      <div class="mm-save-bar"><div><strong>Pronto para aplicar?</strong><p>Salve para atualizar o site. Os visitantes verão a mudança automaticamente em poucos segundos.</p></div><button id="mm-save-button" class="mm-save" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4L19 6"/></svg><span id="mm-save-label">Salvar configuração</span></button></div>
      </form>
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
  var enabled = document.getElementById('mm-enabled');
  var state = document.getElementById('mm-preview-state');
  var pageStatus = document.getElementById('mm-page-status');
  var saveLabel = document.getElementById('mm-save-label');
  var previewBadge = document.getElementById('mm-preview-badge');
  var publishTitle = document.getElementById('mm-publish-title');
  var previewNote = document.getElementById('mm-preview-note');
  var liveHelp = document.getElementById('mm-live-help');
  var realCard = document.getElementById('mm-real-card');
  var manualCard = document.getElementById('mm-manual-card');
  var activationButtons = Array.prototype.slice.call(document.querySelectorAll('.mm-activation-button'));
  var sourceRadios = Array.prototype.slice.call(document.querySelectorAll('input[name="minor_source"], input[name="major_source"]'));
  var liveEntries = {minor: null, major: null};

  function sourceFor(kind) {
    var selected = document.querySelector('input[name="' + kind + '_source"]:checked');
    return selected ? selected.value : 'manual';
  }

  function isEnabled() {
    return enabled && enabled.value === '1';
  }

  function previewValue(id, value, fallback) {
    var target = document.getElementById(id);
    if (target) target.textContent = value || fallback;
  }

  function manualValue(kind, field, fallback) {
    var input = document.getElementById('mm-' + kind + '-' + field);
    return input && input.value.trim() ? input.value.trim() : fallback;
  }

  function renderPreviewSlot(kind) {
    if (sourceFor(kind) === 'real') {
      var entry = liveEntries[kind];
      previewValue('preview-' + kind + '-name', entry && entry.name, 'Aguardando pedido no período');
      previewValue('preview-' + kind + '-ticket', entry && entry.cota, '—');
      return;
    }
    previewValue('preview-' + kind + '-name', manualValue(kind, 'name', 'Nome exibido'), 'Nome exibido');
    previewValue('preview-' + kind + '-ticket', manualValue(kind, 'ticket', '0000000'), '0000000');
  }

  function syncUi() {
    var minorSource = sourceFor('minor');
    var majorSource = sourceFor('major');
    var usesReal = minorSource === 'real' || majorSource === 'real';
    var usesManual = minorSource === 'manual' || majorSource === 'manual';
    realCard.classList.toggle('mm-mode-inactive', !usesReal);
    manualCard.classList.toggle('mm-mode-inactive', !usesManual);
    liveHelp.textContent = usesReal
      ? 'Este período alimenta automaticamente todo destaque marcado como Real.'
      : 'A consulta fica disponível como referência. Marque Real na menor ou na maior cota para usar estes resultados.';
    previewNote.innerHTML = '<strong>Fontes atuais:</strong> menor cota ' + (minorSource === 'real' ? 'Real' : 'Manual') + ' e maior cota ' + (majorSource === 'real' ? 'Real' : 'Manual') + '. Nada interfere no sorteio.';
    renderPreviewSlot('minor');
    renderPreviewSlot('major');
    publishTitle.textContent = isEnabled() ? 'O bloco será mantido ativo ao salvar' : 'O bloco será desativado ao salvar';
    state.textContent = isEnabled() ? 'Esta configuração aparecerá no site depois que você salvar.' : 'Somente prévia. O bloco ficará oculto no site.';
    pageStatus.textContent = isEnabled() ? 'Pronto para ativar' : 'Pronto para desativar';
    previewBadge.textContent = isEnabled() ? 'Pronto para publicar' : 'Somente prévia';
    previewBadge.classList.toggle('is-preview', !isEnabled());
    pageStatus.classList.toggle('enabled', isEnabled());
    if (saveLabel) saveLabel.textContent = isEnabled() ? 'Salvar e ativar exibição' : 'Salvar e desativar exibição';
    activationButtons.forEach(function (button) {
      button.classList.toggle('is-active', button.getAttribute('data-value') === enabled.value);
    });
  }

  bindings.forEach(function (binding) {
    var input = document.getElementById(binding[0]);
    if (!input) return;
    input.addEventListener('input', function () {
      var kind = binding[0].indexOf('minor') !== -1 ? 'minor' : 'major';
      if (sourceFor(kind) === 'manual') renderPreviewSlot(kind);
    });
  });
  sourceRadios.forEach(function (radio) { radio.addEventListener('change', syncUi); });
  activationButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      enabled.value = button.getAttribute('data-value');
      syncUi();
    });
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
  if (!liveStart || !liveEnd || !liveToday || !liveRefresh || !lookupInput || !lookupButton) return;

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
      liveEntries.minor = data.minor || null;
      liveEntries.major = data.major || null;
      if (sourceFor('minor') === 'real') renderPreviewSlot('minor');
      if (sourceFor('major') === 'real') renderPreviewSlot('major');
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
  syncUi();
  loadLiveConsultation();
  window.setInterval(function () {
    if (!document.hidden) loadLiveConsultation();
  }, 5000);
});
</script>
