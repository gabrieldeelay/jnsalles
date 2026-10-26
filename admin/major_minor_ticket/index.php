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
?>

<style>
.mm-shell{max-width:1180px;padding:32px 26px 60px;color:#e5edf8}.mm-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:18px}.mm-eyebrow{margin:0 0 6px;color:#a78bfa;font-size:11px;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.mm-head h2{margin:0;color:#fff;font-size:32px;font-weight:900;letter-spacing:-.045em}.mm-head p{max-width:690px;margin:7px 0 0;color:#91a2ba;font-size:13px;line-height:1.55}.mm-status{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #34425a;border-radius:999px;background:#111a2a;color:#cbd5e1;font-size:11px;font-weight:850;white-space:nowrap}.mm-status:before{width:8px;height:8px;border-radius:50%;background:#64748b;content:""}.mm-status.enabled:before{background:#34d399;box-shadow:0 0 0 5px rgba(52,211,153,.12)}
.mm-grid{display:grid;grid-template-columns:minmax(420px,1fr) minmax(360px,.88fr);gap:16px;align-items:start}.mm-card{overflow:hidden;border:1px solid #2d3a50;border-radius:18px;background:linear-gradient(145deg,rgba(28,39,57,.94),rgba(15,23,42,.98));box-shadow:0 20px 48px rgba(0,0,0,.16)}.mm-card-head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #2b384d}.mm-card-icon{display:grid;width:40px;height:40px;flex:0 0 40px;place-items:center;border:1px solid rgba(167,139,250,.16);border-radius:12px;background:linear-gradient(145deg,rgba(124,58,237,.42),rgba(91,33,182,.22));color:#e9e5ff;font-size:17px}.mm-card-head h3{margin:0;color:#f8fafc;font-size:17px;font-weight:850}.mm-card-head p{margin:4px 0 0;color:#8da0ba;font-size:11px;line-height:1.4}.mm-card-body{padding:20px}.mm-form{display:grid;gap:16px}.mm-field label,.mm-field-title{display:block;margin:0 0 7px;color:#cbd5e1;font-size:11px;font-weight:800}.mm-field select,.mm-field input[type=text]{width:100%;height:46px!important;padding:0 14px!important;border:1px solid #40506a!important;border-radius:10px!important;background:#0c1526!important;color:#f8fafc!important;font-size:12px!important;box-shadow:none!important;outline:0}.mm-field select:focus,.mm-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.mm-toggle-row{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 15px;border:1px solid #334155;border-radius:12px;background:#10192a}.mm-toggle-copy strong{display:block;color:#f8fafc;font-size:12px}.mm-toggle-copy span{display:block;margin-top:4px;color:#7f91aa;font-size:10px;line-height:1.45}.mm-switch{position:relative;width:50px;height:28px;flex:0 0 50px}.mm-switch input{position:absolute;opacity:0;pointer-events:none}.mm-switch span{position:absolute;inset:0;border:1px solid #475569;border-radius:999px;background:#1e293b;cursor:pointer;transition:.2s}.mm-switch span:before{position:absolute;top:4px;left:4px;width:18px;height:18px;border-radius:50%;background:#94a3b8;content:"";transition:.2s}.mm-switch input:checked+span{border-color:#8b5cf6;background:#6d28d9}.mm-switch input:checked+span:before{background:#fff;transform:translateX(22px)}.mm-pair{display:grid;grid-template-columns:1fr 1fr;gap:11px}.mm-fieldset{display:grid;gap:12px;padding:14px;border:1px solid #303e54;border-radius:13px;background:rgba(11,20,36,.58)}.mm-fieldset legend{padding:0 6px;color:#c4b5fd;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.mm-help{margin:-4px 0 0;color:#71839d;font-size:10px;line-height:1.5}.mm-save{min-height:46px;border:0;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:11px;font-weight:900;box-shadow:0 9px 24px rgba(109,40,217,.24);transition:transform .16s ease}.mm-save:hover{transform:translateY(-1px)}.mm-feedback{margin-bottom:14px;padding:12px 14px;border-radius:11px;font-size:11px;font-weight:750;line-height:1.5}.mm-feedback.success{border:1px solid rgba(52,211,153,.28);background:rgba(6,78,59,.42);color:#a7f3d0}.mm-feedback.error{border:1px solid rgba(248,113,113,.28);background:rgba(127,29,29,.36);color:#fecaca}
.mm-preview-wrap{padding:20px}.mm-preview-label{margin:0 0 11px;color:#71839d;font-size:9px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.mm-preview{overflow:hidden;border:1px solid rgba(34,197,94,.4);border-radius:17px;background:radial-gradient(circle at 50% -20%,rgba(34,197,94,.14),transparent 45%),#090f19;box-shadow:0 18px 40px rgba(0,0,0,.26)}.mm-preview-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.13)}.mm-preview-title{display:flex;align-items:center;gap:9px}.mm-preview-title i{display:grid;width:29px;height:29px;place-items:center;border-radius:9px;background:rgba(124,58,237,.28);color:#c4b5fd}.mm-preview-title strong{display:block;color:#f8fafc;font-size:13px}.mm-preview-title small{display:block;margin-top:2px;color:#71839d;font-size:9px}.mm-preview-live{color:#6ee7b7;font-size:8px;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.mm-preview-items{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:15px}.mm-preview-item{min-width:0;padding:14px 10px;border:1px solid #29364a;border-radius:13px;background:linear-gradient(145deg,#101a2b,#0b1422);text-align:center}.mm-preview-item:first-child{border-color:rgba(56,189,248,.28)}.mm-preview-item:last-child{border-color:rgba(167,139,250,.3)}.mm-preview-item small{display:block;color:#8797ad;font-size:9px;font-weight:800;text-transform:uppercase}.mm-preview-ticket{display:inline-flex;max-width:100%;margin:10px 0 8px;padding:7px 10px;border:1px solid rgba(52,211,153,.42);border-radius:8px;background:#05090f;color:#fff;font-size:15px;font-weight:950;letter-spacing:.025em;overflow-wrap:anywhere}.mm-preview-name{display:block;overflow:hidden;color:#cbd5e1;font-size:11px;font-weight:750;text-overflow:ellipsis;white-space:nowrap}.mm-preview-off{padding:13px 15px;border-top:1px solid rgba(148,163,184,.11);color:#71839d;font-size:9px;text-align:center}.mm-note{margin-top:13px;padding:12px 13px;border:1px solid #2c394d;border-radius:11px;background:#10192a;color:#8395ad;font-size:10px;line-height:1.55}.mm-note strong{color:#cbd5e1}
@media(max-width:980px){.mm-grid{grid-template-columns:1fr}}@media(max-width:640px){.mm-shell{padding:24px 14px 46px}.mm-head{align-items:flex-start;flex-direction:column}.mm-head h2{font-size:27px}.mm-pair,.mm-preview-items{grid-template-columns:1fr}.mm-card-body,.mm-preview-wrap{padding:15px}}
</style>

<main class="h-full overflow-y-auto">
  <div class="container mx-auto mm-shell">
    <header class="mm-head">
      <div><p class="mm-eyebrow">Destaque visual da campanha</p><h2>Maior/Menor Cota</h2><p>Informe manualmente os nomes e as cotas que serão mostrados abaixo da ação. Os textos são apenas visuais e não alteram pedidos, clientes ou resultados.</p></div>
      <span id="mm-page-status" class="mm-status <?= $isEnabled ? 'enabled' : '' ?>"><?= $isEnabled ? 'Publicado' : 'Desativado' ?></span>
    </header>

    <?php if ($feedback): ?><div class="mm-feedback <?= $escape($feedback['type']) ?>" role="status"><?= $escape($feedback['message']) ?></div><?php endif; ?>

    <?php if (!$products): ?>
      <section class="mm-card"><div class="mm-card-body"><p class="mm-help">Crie uma campanha antes de configurar esta área.</p></div></section>
    <?php else: ?>
      <div class="mm-grid">
        <section class="mm-card">
          <header class="mm-card-head"><span class="mm-card-icon"><i class="fa-duotone fa-ticket"></i></span><div><h3>Configuração</h3><p>Escolha a campanha e defina exatamente o que será exibido.</p></div></header>
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
          <header class="mm-card-head"><span class="mm-card-icon"><i class="fa-duotone fa-eye"></i></span><div><h3>Prévia</h3><p>Veja como o bloco ficará na página da campanha.</p></div></header>
          <div class="mm-preview-wrap">
            <p class="mm-preview-label">Visualização pública</p>
            <div class="mm-preview">
              <div class="mm-preview-head"><div class="mm-preview-title"><i class="fa-duotone fa-ticket"></i><span><strong>Maior/Menor Cota</strong><small>Destaques da ação</small></span></div><span class="mm-preview-live">Em destaque</span></div>
              <div class="mm-preview-items">
                <div class="mm-preview-item"><small>Menor Cota</small><span id="preview-minor-ticket" class="mm-preview-ticket"><?= $escape($minorTicket ?: '0000000') ?></span><strong id="preview-minor-name" class="mm-preview-name"><?= $escape($minorName ?: 'Nome exibido') ?></strong></div>
                <div class="mm-preview-item"><small>Maior Cota</small><span id="preview-major-ticket" class="mm-preview-ticket"><?= $escape($majorTicket ?: '0000000') ?></span><strong id="preview-major-name" class="mm-preview-name"><?= $escape($majorName ?: 'Nome exibido') ?></strong></div>
              </div>
              <div id="mm-preview-state" class="mm-preview-off"><?= $isEnabled ? 'Este bloco está publicado.' : 'Prévia visível somente no painel enquanto a exibição estiver desativada.' ?></div>
            </div>
            <div class="mm-note"><strong>Importante:</strong> esta função é independente dos pedidos. Ela não procura clientes, não calcula cotas e não interfere no sorteio.</div>
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
});
</script>
