<?php

if ($_settings->userdata('type') != '1') {
    echo 'Você não tem permissão para acessar essa página.';
    exit;
}

$definitions = payment_provider_definitions();
$enabled = [];
foreach ($definitions as $key => $definition) {
    if ((string) $_settings->info($key) === '1') {
        $enabled[] = $key;
    }
}
$selected = count($enabled) === 1 ? $enabled[0] : 'none';
$storedSelection = strtolower((string) $_settings->info('gateway_provider'));
if (isset($definitions[$storedSelection]) && count($enabled) <= 1) {
    $selected = $storedSelection;
}

$required = [
    'mercadopago' => ['mercadopago_access_token'],
    'gerencianet' => ['gerencianet_client_id', 'gerencianet_client_secret', 'gerencianet_pix_key'],
    'paggue' => ['paggue_client_key', 'paggue_client_secret'],
    'openpix' => ['openpix_app_id'],
    'pay2m' => ['pay2m_client_id', 'pay2m_client_secret'],
];
$configured = [];
foreach ($required as $provider => $fields) {
    $configured[$provider] = true;
    foreach ($fields as $field) {
        if (trim((string) $_settings->info($field)) === '') {
            $configured[$provider] = false;
        }
    }
}
if (!getenv('EFI_CERTIFICATE_BASE64') && !is_file(BASE_APP . 'pagamentos.pem')) {
    $configured['gerencianet'] = false;
}

function gateway_secret_placeholder($configured)
{
    return $configured ? 'Já configurado — deixe vazio para manter' : 'Informe a credencial';
}

function gateway_tax_value($field)
{
    global $_settings;
    return htmlspecialchars(number_format((float) $_settings->info($field), 2, '.', ''), ENT_QUOTES, 'UTF-8');
}

$webhookBase = rtrim(BASE_URL, '/') . '/webhook.php?notify=';
?>

<style>
    .gateway-shell{max-width:1050px}.gateway-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:12px}.gateway-choice{position:relative;display:block;padding:16px;border:1px solid #4b5563;border-radius:10px;cursor:pointer;transition:.18s;background:rgba(17,24,39,.35)}.gateway-choice input{position:absolute;opacity:0}.gateway-choice:has(input:checked){border-color:#8b5cf6;box-shadow:0 0 0 2px rgba(139,92,246,.25);background:rgba(124,58,237,.12)}.gateway-choice strong{display:block;color:#e5e7eb}.gateway-choice small{color:#9ca3af}.gateway-panel{display:none;margin-top:22px;padding:20px;border:1px solid #374151;border-radius:10px}.gateway-panel.active{display:block}.gateway-field{margin-top:15px}.gateway-field label{display:block;margin-bottom:6px;font-weight:600}.gateway-status{display:inline-flex;padding:3px 9px;border-radius:999px;font-size:12px}.gateway-ok{background:#064e3b;color:#a7f3d0}.gateway-missing{background:#7f1d1d;color:#fecaca}.gateway-note{padding:12px 14px;border-radius:8px;background:rgba(30,64,175,.16);border:1px solid #1d4ed8}.gateway-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}.gateway-message{display:none;margin-top:14px;padding:12px;border-radius:8px}.gateway-message.success{display:block;background:#064e3b;color:#d1fae5}.gateway-message.error{display:block;background:#7f1d1d;color:#fee2e2}@media(max-width:640px){.gateway-actions button{width:100%}}
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container px-6 mx-auto grid gateway-shell">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Gateway de pagamento</h2>

        <?php if (count($enabled) > 1): ?>
            <div class="gateway-message error" style="display:block">Mais de um gateway estava ativo. Selecione apenas um e salve antes de receber novos pedidos.</div>
        <?php endif; ?>

        <div class="px-4 py-5 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <p class="mb-4 text-sm text-gray-700 dark:text-gray-300">Escolha um único provedor. As credenciais ficam protegidas e nunca são mostradas novamente nesta página.</p>

            <form id="gateway-form" autocomplete="off">
                <input type="hidden" name="gateway" value="1">
                <div class="gateway-grid">
                    <label class="gateway-choice">
                        <input type="radio" name="gateway_provider" value="none" <?= $selected === 'none' ? 'checked' : '' ?>>
                        <strong>Desativado</strong><small>Não gerar cobranças</small>
                    </label>
                    <?php foreach ($definitions as $key => $definition): ?>
                        <label class="gateway-choice">
                            <input type="radio" name="gateway_provider" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $selected === $key ? 'checked' : '' ?>>
                            <strong><?= htmlspecialchars($definition['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small class="gateway-status <?= $configured[$key] ? 'gateway-ok' : 'gateway-missing' ?>"><?= $configured[$key] ? 'Credenciais salvas' : 'Configuração pendente' ?></small>
                        </label>
                    <?php endforeach; ?>
                </div>

                <section class="gateway-panel" data-provider="none">
                    <div class="gateway-note">Novos pedidos pagos não poderão ser criados enquanto o gateway estiver desativado.</div>
                </section>

                <section class="gateway-panel" data-provider="mercadopago">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Mercado Pago</h3>
                    <div class="gateway-field"><label>Access Token</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="mercadopago_access_token" placeholder="<?= gateway_secret_placeholder($configured['mercadopago']) ?>" autocomplete="new-password"></div>
                    <div class="gateway-field"><label>Segredo de assinatura do webhook <small>(recomendado)</small></label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="mercadopago_webhook_secret" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('mercadopago_webhook_secret')) !== '') ?>" autocomplete="new-password"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="mercadopago_tax" value="<?= gateway_tax_value('mercadopago_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'mercadopago', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="gerencianet">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Efí (Gerencianet)</h3>
                    <div class="gateway-note">Na Vercel, o certificado deve estar na variável protegida <strong>EFI_CERTIFICATE_BASE64</strong>. Ele não é aceito por upload público.</div>
                    <div class="gateway-field"><label>Client ID</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="gerencianet_client_id" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('gerencianet_client_id')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Client Secret</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="gerencianet_client_secret" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('gerencianet_client_secret')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Chave PIX</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="gerencianet_pix_key" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('gerencianet_pix_key')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="gerencianet_tax" value="<?= gateway_tax_value('gerencianet_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'gerencianet', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="paggue">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Paggue</h3>
                    <div class="gateway-field"><label>Client Key</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="paggue_client_key" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('paggue_client_key')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Client Secret</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="paggue_client_secret" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('paggue_client_secret')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="paggue_tax" value="<?= gateway_tax_value('paggue_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'paggue', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="openpix">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">OpenPix / Woovi</h3>
                    <div class="gateway-field"><label>App ID</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="openpix_app_id" placeholder="<?= gateway_secret_placeholder($configured['openpix']) ?>"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="openpix_tax" value="<?= gateway_tax_value('openpix_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'openpix', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="pay2m">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Pay2M</h3>
                    <div class="gateway-field"><label>Client ID</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="pay2m_client_id" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('pay2m_client_id')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Client Secret</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="password" name="pay2m_client_secret" placeholder="<?= gateway_secret_placeholder(trim((string) $_settings->info('pay2m_client_secret')) !== '') ?>"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="pay2m_tax" value="<?= gateway_tax_value('pay2m_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'pay2m', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <div class="gateway-actions">
                    <button class="px-5 py-3 font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700" type="submit">Salvar configuração</button>
                    <button id="test-gateway" class="px-5 py-3 font-medium text-purple-100 border border-purple-500 rounded-lg" type="button">Testar conexão salva</button>
                </div>
                <div id="gateway-message" class="gateway-message"></div>
            </form>
        </div>
    </div>
</main>

<script>
(function ($) {
    function selectedProvider() { return $('input[name="gateway_provider"]:checked').val() || 'none'; }
    function showPanel() {
        $('.gateway-panel').removeClass('active');
        $('.gateway-panel[data-provider="' + selectedProvider() + '"]').addClass('active');
        $('#test-gateway').toggle(selectedProvider() !== 'none');
    }
    function message(type, text) {
        $('#gateway-message').removeClass('success error').addClass(type).text(text);
    }
    $('input[name="gateway_provider"]').on('change', showPanel);
    showPanel();

    $('#gateway-form').on('submit', function (event) {
        event.preventDefault();
        message('', 'Salvando...');
        $.ajax({
            url: _base_url_ + 'class/System.php?action=update_system', method: 'POST',
            data: new FormData(this), cache: false, contentType: false, processData: false, dataType: 'json'
        }).done(function (response) {
            if (response.status === 'success') {
                message('success', response.msg || 'Configurações salvas.');
                setTimeout(function () { location.reload(); }, 800);
            } else { message('error', response.msg || 'Não foi possível salvar.'); }
        }).fail(function () { message('error', 'Falha de comunicação ao salvar.'); });
    });

    $('#test-gateway').on('click', function () {
        var provider = selectedProvider();
        message('', 'Validando credenciais sem criar cobrança...');
        $.ajax({
            url: _base_url_ + 'class/System.php?action=test_gateway', method: 'POST', dataType: 'json', data: {provider: provider}
        }).done(function (response) {
            message(response.status === 'success' ? 'success' : 'error', response.msg || 'Teste concluído.');
        }).fail(function () { message('error', 'Falha de comunicação durante o teste.'); });
    });
})(jQuery);
</script>
