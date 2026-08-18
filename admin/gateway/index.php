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
$splitEnabled = $storedSelection === 'split'
    && in_array('venopag', $enabled, true)
    && in_array('pay2m', $enabled, true)
    && count($enabled) === 2;
if ($splitEnabled) {
    $selected = 'split';
} elseif (isset($definitions[$storedSelection]) && count($enabled) <= 1) {
    $selected = $storedSelection;
}

$required = [
    'mercadopago' => ['mercadopago_access_token'],
    'gerencianet' => ['gerencianet_client_id', 'gerencianet_client_secret', 'gerencianet_pix_key'],
    'paggue' => ['paggue_client_key', 'paggue_client_secret'],
    'openpix' => ['openpix_app_id'],
    'pay2m' => ['pay2m_client_id', 'pay2m_client_secret'],
    'venopag' => ['venopag_client_id', 'venopag_client_secret'],
];
$configured = [];
foreach ($required as $provider => $fields) {
    $configured[$provider] = true;
    foreach ($fields as $field) {
        if (trim((string) payment_setting($field)) === '') {
            $configured[$provider] = false;
        }
    }
}
if (!getenv('EFI_CERTIFICATE_BASE64') && !is_file(BASE_APP . 'pagamentos.pem')) {
    $configured['gerencianet'] = false;
}
$configured['split'] = !empty($configured['venopag']) && !empty($configured['pay2m']);

function gateway_secret_placeholder($configured)
{
    return $configured ? 'Já configurado — deixe vazio para manter' : 'Informe a credencial';
}

function gateway_credential_input($field, $configured = false)
{
    $value = (string) payment_setting($field);
    $environmentValue = getenv(strtoupper($field));
    $isEnvironmentManaged = $environmentValue !== false && $environmentValue !== '';
    $attributes = $isEnvironmentManaged
        ? ' readonly aria-readonly="true" data-environment-managed="true"'
        : ' name="' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . '"';
    $hint = $isEnvironmentManaged ? 'Gerenciada pela Vercel' : gateway_secret_placeholder($configured);

    return '<div class="gateway-secret-control">'
        . '<input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300 gateway-secret-input" type="password"'
        . $attributes
        . ' value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"'
        . ' placeholder="' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '" autocomplete="new-password">'
        . '<button class="gateway-secret-toggle" type="button" aria-pressed="false" aria-label="Mostrar credencial">Mostrar</button>'
        . '</div>'
        . ($isEnvironmentManaged ? '<small class="gateway-environment-note">Esta credencial est&aacute; protegida nas vari&aacute;veis de produ&ccedil;&atilde;o e n&atilde;o ser&aacute; sobrescrita por este formul&aacute;rio.</small>' : '');
}

function gateway_tax_value($field)
{
    global $_settings;
    return htmlspecialchars(number_format((float) $_settings->info($field), 2, '.', ''), ENT_QUOTES, 'UTF-8');
}

$webhookBase = rtrim(BASE_URL, '/') . '/webhook.php?notify=';
$venopagWebhookSecret = trim((string) payment_setting('venopag_webhook_secret'));
$venopagWebhook = $webhookBase . 'venopag' . ($venopagWebhookSecret !== '' ? '&token=' . rawurlencode($venopagWebhookSecret) : '&token=GERADO_AO_SALVAR');
$providerShortNames = [
    'mercadopago' => 'MP', 'gerencianet' => 'EF', 'paggue' => 'PG',
    'openpix' => 'OP', 'pay2m' => 'P2', 'venopag' => 'VP',
];
$activeLabel = $selected === 'split'
    ? 'VenoPag + Pay2M por valor'
    : ($selected !== 'none' && isset($definitions[$selected]) ? $definitions[$selected]['label'] : 'Nenhum gateway ativo');
$pay2mHighValueEnabled = (string) payment_setting('pay2m_high_value_enabled', '0') === '1';
$pay2mHighValueThreshold = payment_pay2m_high_value_threshold();
$activeDescription = $selected === 'split'
    ? 'Até R$ 999,99 pela VenoPag; a partir de R$ 1.000,00 pela Pay2M.'
    : ($selected !== 'none' ? 'As novas cobranças PIX usam este provedor.' : 'Nenhuma nova cobrança PIX será gerada até você ativar um provedor.');
?>

<style>
    .gateway-shell{max-width:1120px;padding-bottom:32px}.gateway-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin:28px 0 20px}.gateway-eyebrow{margin:0 0 4px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.gateway-heading h2{margin:0;color:#f8fafc;font-size:28px;font-weight:750;letter-spacing:-.03em}.gateway-heading p{margin:7px 0 0;color:#94a3b8;font-size:14px}.gateway-heading .gateway-eyebrow{margin:0 0 4px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.gateway-security{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #334155;border-radius:999px;background:#111827;color:#cbd5e1;font-size:12px;white-space:nowrap}.gateway-surface{padding:20px;border:1px solid #2d3748;border-radius:16px;background:linear-gradient(145deg,rgba(30,41,59,.72),rgba(17,24,39,.92));box-shadow:0 18px 45px rgba(0,0,0,.18)}.gateway-current{display:flex;align-items:center;gap:14px;padding:16px;border:1px solid #334155;border-radius:13px;background:#111827}.gateway-current.active{border-color:rgba(16,185,129,.42);background:linear-gradient(110deg,rgba(6,78,59,.38),rgba(17,24,39,.92))}.gateway-current__icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex:0 0 42px;border-radius:12px;background:#1e293b;color:#94a3b8}.gateway-current.active .gateway-current__icon{background:#065f46;color:#a7f3d0}.gateway-current__content{min-width:0;flex:1}.gateway-current__label{display:flex;align-items:center;gap:7px;margin-bottom:2px;color:#94a3b8;font-size:11px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.gateway-live-dot{width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 0 4px rgba(52,211,153,.1)}.gateway-current strong{display:block;color:#f8fafc;font-size:17px}.gateway-current p{margin:3px 0 0;color:#94a3b8;font-size:12px}.gateway-disable-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 12px;border:1px solid rgba(248,113,113,.4);border-radius:9px;background:rgba(127,29,29,.16);color:#fecaca;font-size:12px;font-weight:700;transition:.18s}.gateway-disable-button:hover{border-color:#f87171;background:rgba(127,29,29,.3);color:#fff}.gateway-disable-confirm{margin-top:10px;padding:14px 15px;border:1px solid rgba(248,113,113,.36);border-radius:11px;background:rgba(69,10,10,.3);color:#fecaca}.gateway-disable-confirm strong{display:block;color:#fee2e2}.gateway-disable-confirm p{margin:4px 0 12px;color:#fca5a5;font-size:12px}.gateway-confirm-actions{display:flex;gap:8px;flex-wrap:wrap}.gateway-confirm-actions button{padding:8px 11px;border-radius:8px;font-size:12px;font-weight:700}.gateway-confirm-yes{border:1px solid #dc2626;background:#b91c1c;color:#fff}.gateway-confirm-cancel{border:1px solid #475569;background:#1e293b;color:#e2e8f0}.gateway-section-title{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:24px 0 12px}.gateway-section-title h3{margin:0;color:#f1f5f9;font-size:15px;font-weight:700}.gateway-section-title p{margin:3px 0 0;color:#94a3b8;font-size:12px}.gateway-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px}.gateway-choice{position:relative;display:flex;min-height:138px;padding:14px;border:1px solid #3b4659;border-radius:12px;background:rgba(15,23,42,.62);cursor:pointer;flex-direction:column;transition:transform .18s ease,border-color .18s ease,background .18s ease,box-shadow .18s ease}.gateway-choice:hover{transform:translateY(-1px);border-color:#64748b;background:#172033}.gateway-choice input{position:absolute;opacity:0;pointer-events:none}.gateway-choice:has(input:checked){border-color:#8b5cf6;background:linear-gradient(145deg,rgba(109,40,217,.23),rgba(30,27,75,.25));box-shadow:0 0 0 2px rgba(139,92,246,.13),0 9px 24px rgba(0,0,0,.16)}.gateway-choice__top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}.gateway-choice__badge{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border:1px solid #475569;border-radius:10px;background:#1e293b;color:#cbd5e1;font-size:11px;font-weight:800;letter-spacing:.04em}.gateway-choice:has(input:checked) .gateway-choice__badge{border-color:#8b5cf6;background:#6d28d9;color:#fff}.gateway-choice__check{width:20px;height:20px;display:flex;align-items:center;justify-content:center;border:1px solid #475569;border-radius:50%;color:transparent}.gateway-choice:has(input:checked) .gateway-choice__check{border-color:#a78bfa;background:#8b5cf6;color:#fff}.gateway-choice strong{display:block;color:#f1f5f9;font-size:15px}.gateway-choice__description{margin:4px 0 12px;color:#94a3b8;font-size:11px;line-height:1.4}.gateway-status{display:inline-flex;align-items:center;align-self:flex-start;margin-top:auto;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:750;letter-spacing:.02em}.gateway-live{background:rgba(5,150,105,.18);color:#6ee7b7}.gateway-ok{background:rgba(14,116,144,.17);color:#67e8f9}.gateway-missing{background:rgba(153,27,27,.2);color:#fca5a5}.gateway-panel{display:none;margin-top:16px;padding:20px;border:1px solid #334155;border-radius:13px;background:rgba(15,23,42,.62)}.gateway-panel.active{display:block}.gateway-panel h3{margin:0 0 4px;color:#f8fafc;font-size:18px}.gateway-field{margin-top:15px}.gateway-field label{display:block;margin-bottom:6px;color:#cbd5e1;font-size:12px;font-weight:650}.gateway-field label small{color:#94a3b8}.gateway-panel .form-input{min-height:43px!important;border:1px solid #3f4d63!important;border-radius:9px!important;background:#111827!important;color:#f8fafc!important;box-shadow:none!important}.gateway-panel .form-input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.16)!important}.gateway-panel .form-input[readonly]{background:#0b1220!important;color:#94a3b8!important}.gateway-secret-control{position:relative}.gateway-secret-control .form-input{padding-right:82px!important}.gateway-secret-toggle{position:absolute;top:5px;right:5px;min-width:69px;height:33px;border:1px solid #475569;border-radius:7px;background:#1e293b;color:#e2e8f0;font-size:11px;font-weight:750}.gateway-secret-toggle:hover{border-color:#8b5cf6;color:#fff}.gateway-environment-note{display:block;margin-top:6px;color:#94a3b8;font-size:10px;line-height:1.4}.gateway-note{padding:12px 14px;border:1px solid rgba(59,130,246,.42);border-radius:9px;background:rgba(30,64,175,.18);color:#dbeafe;font-size:12px;line-height:1.55}.gateway-note strong{color:#fff}.gateway-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:18px;padding-top:18px;border-top:1px solid #2d3748}.gateway-save{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 16px;border:0;border-radius:9px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-size:13px;font-weight:750;box-shadow:0 7px 18px rgba(124,58,237,.2)}.gateway-save:disabled{cursor:wait;opacity:.65}.gateway-test{min-height:42px;padding:0 15px;border:1px solid #5b4c89;border-radius:9px;background:rgba(76,29,149,.12);color:#ddd6fe;font-size:13px;font-weight:700}.gateway-message{display:none;margin-top:12px;padding:11px 13px;border-radius:9px;font-size:12px}.gateway-message.loading{display:block;border:1px solid #475569;background:#1e293b;color:#cbd5e1}.gateway-message.success{display:block;border:1px solid rgba(16,185,129,.35);background:rgba(6,78,59,.55);color:#d1fae5}.gateway-message.error{display:block;border:1px solid rgba(248,113,113,.35);background:rgba(127,29,29,.5);color:#fee2e2}@media(max-width:820px){.gateway-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.gateway-heading{align-items:flex-start;flex-direction:column}.gateway-security{align-self:flex-start}}@media(max-width:560px){.gateway-shell{padding-inline:14px!important}.gateway-surface{padding:14px}.gateway-grid{grid-template-columns:1fr}.gateway-current{align-items:flex-start;flex-wrap:wrap}.gateway-disable-button{width:100%}.gateway-actions button,.gateway-confirm-actions button{width:100%}}
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container px-6 mx-auto grid gateway-shell">
        <header class="gateway-heading">
            <div>
                <p class="gateway-eyebrow">Financeiro</p>
                <h2>Gateway de pagamento</h2>
                <p>Escolha quem processará os novos pagamentos PIX da sua operação.</p>
            </div>
            <span class="gateway-security">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 2.9 8.8 7 10 4.1-1.2 7-5.4 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9.5 12 1.7 1.7 3.6-3.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Credenciais protegidas
            </span>
        </header>

        <?php if (count($enabled) > 1 && $selected !== 'split'): ?>
            <div class="gateway-message error" style="display:block">Mais de um gateway estava ativo. Selecione apenas um e salve antes de receber novos pedidos.</div>
        <?php endif; ?>

        <div class="gateway-surface mb-8">
            <form id="gateway-form" autocomplete="off">
                <input type="hidden" name="gateway" value="1">
                <input id="gateway-none" type="radio" name="gateway_provider" value="none" <?= $selected === 'none' ? 'checked' : '' ?> hidden>

                <div class="gateway-current <?= $selected !== 'none' ? 'active' : '' ?>">
                    <span class="gateway-current__icon" aria-hidden="true">
                        <?php if ($selected !== 'none'): ?>
                            <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M5 7.5h14M7 4v3.5m10-3.5v3.5M5 7.5v11h14v-11M8.5 12h7M8.5 15.5h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?php else: ?>
                            <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 8h12v10H6zM8.5 8V6.5A2.5 2.5 0 0 1 11 4h2a2.5 2.5 0 0 1 2.5 2.5V8M4 20 20 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?php endif; ?>
                    </span>
                    <div class="gateway-current__content">
                        <span class="gateway-current__label"><?php if ($selected !== 'none'): ?><i class="gateway-live-dot"></i><?php endif; ?> Status atual</span>
                        <strong><?= htmlspecialchars($activeLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                        <p><?= htmlspecialchars($activeDescription, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <?php if ($selected !== 'none'): ?>
                        <button id="disable-gateway" class="gateway-disable-button" type="button">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v9m6.4-5.4a8 8 0 1 1-12.8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Desativar gateway
                        </button>
                    <?php endif; ?>
                </div>

                <div id="gateway-disable-confirm" class="gateway-disable-confirm" hidden>
                    <strong>Desativar <?= htmlspecialchars($activeLabel, ENT_QUOTES, 'UTF-8') ?>?</strong>
                    <p>As credenciais, taxas e configurações continuarão salvas. Apenas novas cobranças deixarão de ser geradas.</p>
                    <div class="gateway-confirm-actions">
                        <button id="confirm-disable-gateway" class="gateway-confirm-yes" type="button">Sim, desativar</button>
                        <button id="cancel-disable-gateway" class="gateway-confirm-cancel" type="button">Cancelar</button>
                    </div>
                </div>

                <div class="gateway-section-title">
                    <div><h3>Escolha como receber</h3><p>Use um provedor sozinho ou divida automaticamente os pagamentos entre VenoPag e Pay2M.</p></div>
                </div>
                <div class="gateway-grid">
                    <label class="gateway-choice">
                        <input type="radio" name="gateway_provider" value="split" data-label="VenoPag + Pay2M" <?= $selected === 'split' ? 'checked' : '' ?>>
                        <span class="gateway-choice__top">
                            <span class="gateway-choice__badge">2X</span>
                            <span class="gateway-choice__check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        </span>
                        <strong>VenoPag + Pay2M</strong>
                        <span class="gateway-choice__description">Até R$ 999,99 na VenoPag e a partir de R$ 1.000,00 na Pay2M.</span>
                        <small class="gateway-status <?= $selected === 'split' ? 'gateway-live' : ($configured['split'] ? 'gateway-ok' : 'gateway-missing') ?>"><?= $selected === 'split' ? 'Divisão ativa' : ($configured['split'] ? 'Credenciais salvas' : 'Informe as duas credenciais') ?></small>
                    </label>
                    <?php foreach ($definitions as $key => $definition): ?>
                        <label class="gateway-choice">
                            <input type="radio" name="gateway_provider" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" data-label="<?= htmlspecialchars($definition['label'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected === $key ? 'checked' : '' ?>>
                            <span class="gateway-choice__top">
                                <span class="gateway-choice__badge"><?= htmlspecialchars($providerShortNames[$key] ?? strtoupper(substr($key, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="gateway-choice__check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            </span>
                            <strong><?= htmlspecialchars($definition['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="gateway-choice__description"><?= $selected === $key ? 'Processando as cobranças atuais.' : ($configured[$key] ? 'Pronto para ser ativado quando quiser.' : 'Preencha as credenciais para utilizar.') ?></span>
                            <small class="gateway-status <?= $selected === $key ? 'gateway-live' : ($configured[$key] ? 'gateway-ok' : 'gateway-missing') ?>"><?= $selected === $key ? 'Ativo agora' : ($configured[$key] ? 'Credenciais salvas' : 'Configuração necessária') ?></small>
                        </label>
                    <?php endforeach; ?>
                </div>

                <section class="gateway-panel" data-provider="none">
                    <div class="gateway-note"><strong>Gateway desativado.</strong> Novas cobranças PIX não serão geradas, mas todas as credenciais, taxas e configurações permanecerão salvas para uma reativação futura.</div>
                </section>

                <section class="gateway-panel" data-provider="mercadopago">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Mercado Pago</h3>
                    <div class="gateway-field"><label>Access Token</label><?= gateway_credential_input('mercadopago_access_token', $configured['mercadopago']) ?></div>
                    <div class="gateway-field"><label>Segredo de assinatura do webhook <small>(recomendado)</small></label><?= gateway_credential_input('mercadopago_webhook_secret', trim((string) payment_setting('mercadopago_webhook_secret')) !== '') ?></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="mercadopago_tax" value="<?= gateway_tax_value('mercadopago_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'mercadopago', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="gerencianet">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Efí (Gerencianet)</h3>
                    <div class="gateway-note">Na Vercel, o certificado deve estar na variável protegida <strong>EFI_CERTIFICATE_BASE64</strong>. Ele não é aceito por upload público.</div>
                    <div class="gateway-field"><label>Client ID</label><?= gateway_credential_input('gerencianet_client_id', trim((string) payment_setting('gerencianet_client_id')) !== '') ?></div>
                    <div class="gateway-field"><label>Client Secret</label><?= gateway_credential_input('gerencianet_client_secret', trim((string) payment_setting('gerencianet_client_secret')) !== '') ?></div>
                    <div class="gateway-field"><label>Chave PIX</label><?= gateway_credential_input('gerencianet_pix_key', trim((string) payment_setting('gerencianet_pix_key')) !== '') ?></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="gerencianet_tax" value="<?= gateway_tax_value('gerencianet_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'gerencianet', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="paggue">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Paggue</h3>
                    <div class="gateway-field"><label>Client Key</label><?= gateway_credential_input('paggue_client_key', trim((string) payment_setting('paggue_client_key')) !== '') ?></div>
                    <div class="gateway-field"><label>Client Secret</label><?= gateway_credential_input('paggue_client_secret', trim((string) payment_setting('paggue_client_secret')) !== '') ?></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="paggue_tax" value="<?= gateway_tax_value('paggue_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'paggue', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="openpix">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">OpenPix / Woovi</h3>
                    <div class="gateway-field"><label>App ID</label><?= gateway_credential_input('openpix_app_id', $configured['openpix']) ?></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="openpix_tax" value="<?= gateway_tax_value('openpix_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'openpix', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="pay2m">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Pay2M</h3>
                    <div class="gateway-note"><strong>Roteamento por valor.</strong> Mesmo com outro gateway ativo, a Pay2M pode processar automaticamente somente as compras que ultrapassarem o limite definido abaixo.</div>
                    <div class="gateway-field"><label>Client ID</label><?= gateway_credential_input('pay2m_client_id', trim((string) payment_setting('pay2m_client_id')) !== '') ?></div>
                    <div class="gateway-field"><label>Client Secret</label><?= gateway_credential_input('pay2m_client_secret', trim((string) payment_setting('pay2m_client_secret')) !== '') ?></div>
                    <div class="gateway-field">
                        <label><input type="checkbox" name="pay2m_high_value_enabled" value="1" <?= $pay2mHighValueEnabled ? 'checked' : '' ?>> Usar Pay2M automaticamente nas compras de maior valor</label>
                    </div>
                    <div class="gateway-field"><label>Usar Pay2M quando o total for maior que (R$)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" step="0.01" name="pay2m_high_value_threshold" value="<?= htmlspecialchars(number_format($pay2mHighValueThreshold, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="pay2m_tax" value="<?= gateway_tax_value('pay2m_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($webhookBase . 'pay2m', ENT_QUOTES, 'UTF-8') ?>"></div>
                </section>

                <section class="gateway-panel" data-provider="venopag">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">VenoPag</h3>
                    <div class="gateway-note">Use as credenciais de uma chave com a permissão <strong>cashin</strong>. O webhook recebe uma URL secreta e toda confirmação é consultada novamente na VenoPag antes de liberar o pedido.</div>
                    <div class="gateway-field"><label>Client ID</label><?= gateway_credential_input('venopag_client_id', trim((string) payment_setting('venopag_client_id')) !== '') ?></div>
                    <div class="gateway-field"><label>Client Secret</label><?= gateway_credential_input('venopag_client_secret', trim((string) payment_setting('venopag_client_secret')) !== '') ?></div>
                    <div class="gateway-field"><label>CPF/CNPJ padrão do pagador <small>(somente números)</small></label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="text" inputmode="numeric" maxlength="14" name="venopag_default_document" value="<?= htmlspecialchars(preg_replace('/\D+/', '', (string) payment_setting('venopag_default_document')), ENT_QUOTES, 'UTF-8') ?>" placeholder="Informe o documento autorizado para as cobranças"></div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Enviado pelo servidor quando o comprador não informa CPF. O documento não aparece no checkout.</p>
                    <div class="gateway-field"><label>Valor mínimo operacional (R$)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="1" step="0.01" name="venopag_min_amount" value="<?= htmlspecialchars(number_format(payment_venopag_minimum_amount(), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="gateway-field"><label>Taxa adicional (%)</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" type="number" min="0" max="100" step="0.01" name="venopag_tax" value="<?= gateway_tax_value('venopag_tax') ?>"></div>
                    <div class="gateway-field"><label>Webhook protegido</label><input class="form-input block w-full dark:bg-gray-700 dark:text-gray-300" readonly value="<?= htmlspecialchars($venopagWebhook, ENT_QUOTES, 'UTF-8') ?>"></div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">A URL é enviada automaticamente em cada nova cobrança; não é necessário cadastrá-la manualmente.</p>
                </section>

                <div class="gateway-actions">
                    <button id="save-gateway" class="gateway-save" type="submit">Salvar configuração</button>
                    <button id="test-gateway" class="gateway-test" type="button">Testar credenciais salvas</button>
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
        var provider = selectedProvider();
        var selectedInput = $('input[name="gateway_provider"]:checked');
        var label = selectedInput.data('label') || '';
        $('.gateway-panel').removeClass('active');
        if (provider === 'split') {
            $('.gateway-panel[data-provider="pay2m"], .gateway-panel[data-provider="venopag"]').addClass('active');
            $('input[name="pay2m_high_value_enabled"]').prop('checked', true);
            $('input[name="pay2m_high_value_threshold"]').val('999.99').prop('readonly', true);
        } else {
            $('.gateway-panel[data-provider="' + provider + '"]').addClass('active');
            $('input[name="pay2m_high_value_threshold"]').prop('readonly', false);
        }
        $('#test-gateway').toggle(provider !== 'none');
        $('#save-gateway').text(provider === 'none' ? 'Salvar gateway desativado' : (provider === 'split' ? 'Ativar divisão automática' : 'Salvar e ativar ' + label));
        $('#gateway-disable-confirm').prop('hidden', true);
    }
    function message(type, text) {
        $('#gateway-message').removeClass('loading success error').addClass(type || 'loading').text(text);
    }
    $('input[name="gateway_provider"]').on('change', showPanel);
    $(document).on('click', '.gateway-secret-toggle', function () {
        var button = $(this);
        var input = button.siblings('.gateway-secret-input');
        var showing = input.attr('type') === 'text';
        input.attr('type', showing ? 'password' : 'text');
        button.attr('aria-pressed', showing ? 'false' : 'true');
        button.attr('aria-label', showing ? 'Mostrar credencial' : 'Ocultar credencial');
        button.text(showing ? 'Mostrar' : 'Ocultar');
    });
    showPanel();

    $('#disable-gateway').on('click', function () {
        $('#gateway-disable-confirm').prop('hidden', false);
    });
    $('#cancel-disable-gateway').on('click', function () {
        $('#gateway-disable-confirm').prop('hidden', true);
    });
    $('#confirm-disable-gateway').on('click', function () {
        $('#gateway-none').prop('checked', true);
        showPanel();
        $('#gateway-form').trigger('submit');
    });

    $('#gateway-form').on('submit', function (event) {
        event.preventDefault();
        var form = this;
        var provider = selectedProvider();
        var saveButton = $('#save-gateway');
        saveButton.prop('disabled', true);
        message('loading', provider === 'none' ? 'Desativando gateway sem apagar as credenciais...' : 'Salvando configuração...');
        $.ajax({
            url: _base_url_ + 'class/System.php?action=update_system', method: 'POST',
            data: new FormData(form), cache: false, contentType: false, processData: false, dataType: 'json'
        }).done(function (response) {
            if (response.status === 'success') {
                message('success', response.msg || 'Configurações salvas.');
                setTimeout(function () { location.reload(); }, 1000);
            } else { message('error', response.msg || 'Não foi possível salvar.'); }
        }).fail(function () { message('error', 'Falha de comunicação ao salvar.'); })
          .always(function () { saveButton.prop('disabled', false); });
    });

    $('#test-gateway').on('click', function () {
        var provider = selectedProvider();
        message('loading', 'Validando credenciais sem criar cobrança...');
        $.ajax({
            url: _base_url_ + 'class/System.php?action=test_gateway', method: 'POST', dataType: 'json', data: {provider: provider}
        }).done(function (response) {
            message(response.status === 'success' ? 'success' : 'error', response.msg || 'Teste concluído.');
        }).fail(function () { message('error', 'Falha de comunicação durante o teste.'); });
    });
})(jQuery);
</script>
