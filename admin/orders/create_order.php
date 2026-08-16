<?php
$campaigns = [];
$campaignResult = $conn->query('SELECT id, name, price, qty_numbers, pending_numbers, paid_numbers FROM product_list WHERE delete_flag = 0 AND status <> 3 ORDER BY id DESC');
while ($campaignResult && ($campaign = $campaignResult->fetch_assoc())) {
    $campaigns[] = $campaign;
}

$customers = [];
$customerResult = $conn->query('SELECT id, firstname, lastname, phone FROM customer_list ORDER BY firstname ASC, lastname ASC, id DESC');
while ($customerResult && ($customer = $customerResult->fetch_assoc())) {
    $customers[] = $customer;
}
?>

<style>
    .manual-order-shell{max-width:1040px;padding-bottom:48px}.manual-order-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:28px 0 20px}.manual-order-eyebrow{margin:0 0 4px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.manual-order-heading h2{margin:0;color:#f8fafc;font-size:28px;font-weight:780;letter-spacing:-.03em}.manual-order-heading p{margin:6px 0 0;color:#94a3b8;font-size:13px}.manual-order-back{display:inline-flex;min-height:40px;align-items:center;gap:8px;padding:0 14px;border:1px solid #3c4658;border-radius:10px;background:#171c26;color:#e2e8f0;font-size:13px;font-weight:700;transition:.18s}.manual-order-back:hover{border-color:#8b5cf6;color:#fff}.manual-order-card{overflow:hidden;border:1px solid #2d3748;border-radius:17px;background:linear-gradient(145deg,rgba(30,41,59,.72),rgba(17,24,39,.96));box-shadow:0 20px 55px rgba(0,0,0,.2)}.manual-order-section{padding:22px}.manual-order-section+.manual-order-section{border-top:1px solid #2d3748}.manual-order-section-title{display:flex;align-items:center;gap:11px;margin-bottom:17px}.manual-order-step{display:grid;width:34px;height:34px;place-items:center;border:1px solid rgba(139,92,246,.45);border-radius:10px;background:rgba(109,40,217,.22);color:#ddd6fe;font-size:12px;font-weight:800}.manual-order-section-title strong{display:block;color:#f8fafc;font-size:15px}.manual-order-section-title span{display:block;margin-top:2px;color:#94a3b8;font-size:11px}.manual-order-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.manual-order-field.full{grid-column:1/-1}.manual-order-field label{display:block;margin-bottom:7px;color:#cbd5e1;font-size:12px;font-weight:700}.manual-order-field select,.manual-order-field input{width:100%;min-height:46px;padding:0 13px;border:1px solid #3f4d63!important;border-radius:10px!important;background:#111827!important;color:#f8fafc!important;font-size:13px!important;box-shadow:none!important}.manual-order-field select:focus,.manual-order-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.manual-order-hint{display:block;margin-top:6px;color:#7f8da3;font-size:11px}.manual-order-summary{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:18px;padding:16px 18px;border:1px solid rgba(16,185,129,.34);border-radius:12px;background:linear-gradient(110deg,rgba(6,78,59,.38),rgba(17,24,39,.8))}.manual-order-summary span{color:#a7f3d0;font-size:12px}.manual-order-summary strong{display:block;margin-top:2px;color:#ecfdf5;font-size:24px;letter-spacing:-.03em}.manual-order-available{padding:7px 10px;border-radius:999px;background:rgba(16,185,129,.13);color:#6ee7b7;font-size:11px;font-weight:750}.manual-random-box{padding:17px;border:1px dashed #465269;border-radius:13px;background:rgba(15,23,42,.52)}.manual-random-top{display:flex;align-items:center;justify-content:space-between;gap:15px}.manual-random-top strong{color:#f1f5f9;font-size:14px}.manual-random-top p{margin:4px 0 0;color:#94a3b8;font-size:11px}.manual-random-button{min-height:39px;padding:0 13px;border:1px solid #6d5ca5;border-radius:9px;background:rgba(109,40,217,.2);color:#ede9fe;font-size:12px;font-weight:750}.manual-random-button:disabled{cursor:wait;opacity:.6}.manual-number-preview{display:none;gap:7px;flex-wrap:wrap;margin-top:15px}.manual-number-preview.visible{display:flex}.manual-number-chip{padding:5px 8px;border:1px solid #374151;border-radius:7px;background:#0b1220;color:#c4b5fd;font-family:monospace;font-size:11px}.manual-number-more{padding:5px 8px;color:#94a3b8;font-size:11px}.manual-order-message{display:none;margin-top:14px;padding:11px 13px;border-radius:9px;font-size:12px}.manual-order-message.error{display:block;border:1px solid rgba(248,113,113,.38);background:rgba(127,29,29,.42);color:#fee2e2}.manual-order-message.success{display:block;border:1px solid rgba(16,185,129,.38);background:rgba(6,78,59,.46);color:#d1fae5}.manual-order-footer{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 22px;border-top:1px solid #2d3748;background:rgba(15,23,42,.38)}.manual-order-footer p{margin:0;color:#94a3b8;font-size:11px}.manual-order-submit{min-width:190px;min-height:44px;border:0;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;font-size:13px;font-weight:800;box-shadow:0 9px 22px rgba(124,58,237,.22)}.manual-order-submit:disabled{cursor:wait;opacity:.6}@media(max-width:700px){.manual-order-heading{align-items:flex-start;flex-direction:column}.manual-order-grid{grid-template-columns:1fr}.manual-order-field.full{grid-column:auto}.manual-order-summary,.manual-random-top,.manual-order-footer{align-items:flex-start;flex-direction:column}.manual-random-button,.manual-order-submit{width:100%}}
</style>

<main class="h-full overflow-y-auto">
    <div class="container px-6 mx-auto manual-order-shell">
        <header class="manual-order-heading">
            <div>
                <p class="manual-order-eyebrow">Pedidos</p>
                <h2>Novo pedido manual</h2>
                <p>Selecione o cliente e deixe o sistema separar cotas livres automaticamente.</p>
            </div>
            <a class="manual-order-back" href="./?page=orders">&#8592; Voltar aos pedidos</a>
        </header>

        <form id="manage-order" autocomplete="off" novalidate>
            <div class="manual-order-card">
                <section class="manual-order-section">
                    <div class="manual-order-section-title"><div class="manual-order-step">01</div><div><strong>Campanha e cliente</strong><span>Escolha para quem o pedido será criado.</span></div></div>
                    <div class="manual-order-grid">
                        <div class="manual-order-field full">
                            <label for="raffle">Campanha</label>
                            <select name="raffle" id="raffle" required>
                                <option value="">Selecione uma campanha</option>
                                <?php foreach ($campaigns as $campaign):
                                    $available = max(0, (int) $campaign['qty_numbers'] - (int) $campaign['pending_numbers'] - (int) $campaign['paid_numbers']); ?>
                                    <option value="<?php echo (int) $campaign['id']; ?>" data-price="<?php echo htmlspecialchars((string) $campaign['price'], ENT_QUOTES, 'UTF-8'); ?>" data-available="<?php echo $available; ?>">
                                        <?php echo htmlspecialchars($campaign['name'], ENT_QUOTES, 'UTF-8'); ?> — R$ <?php echo number_format((float) $campaign['price'], 2, ',', '.'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="manual-order-field full">
                            <label for="customer_name">Cliente</label>
                            <input name="customer_name" id="customer_name" type="text" list="customer-suggestions" placeholder="Digite o nome completo do cliente" autocomplete="off" required>
                            <datalist id="customer-suggestions">
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo htmlspecialchars(trim($customer['firstname'] . ' ' . $customer['lastname']), ENT_QUOTES, 'UTF-8'); ?>" label="<?php echo htmlspecialchars($customer['phone'], ENT_QUOTES, 'UTF-8'); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <span class="manual-order-hint">Digite o nome completo. As sugestões servem apenas para ajudar e o campo continua livre.</span>
                        </div>
                    </div>
                </section>

                <section class="manual-order-section">
                    <div class="manual-order-section-title"><div class="manual-order-step">02</div><div><strong>Quantidade e situação</strong><span>O valor é calculado pelo preço atual da campanha.</span></div></div>
                    <div class="manual-order-grid">
                        <div class="manual-order-field">
                            <label for="quantidade">Quantidade de cotas</label>
                            <input name="quantidade" id="quantidade" type="number" min="1" max="50000" step="1" value="1" required>
                        </div>
                        <div class="manual-order-field">
                            <label for="status">Status do pedido</label>
                            <select name="status" id="status" required>
                                <option value="2" selected>Aprovado / pago</option>
                                <option value="1">Pendente</option>
                            </select>
                            <small>Pedidos aprovados entram imediatamente nos rankings geral e diário.</small>
                        </div>
                    </div>
                    <div class="manual-order-summary">
                        <div><span>Total calculado</span><strong id="calculated-total">R$ 0,00</strong></div>
                        <div class="manual-order-available" id="available-label">Selecione uma campanha</div>
                    </div>
                </section>

                <section class="manual-order-section">
                    <div class="manual-order-section-title"><div class="manual-order-step">03</div><div><strong>Cotas aleatórias</strong><span>Somente números livres entram no sorteio.</span></div></div>
                    <div class="manual-random-box">
                        <div class="manual-random-top">
                            <div><strong>Prévia das cotas</strong><p>A prévia não reserva números. A confirmação segura acontece ao cadastrar.</p></div>
                            <button class="manual-random-button" id="randomize-numbers" type="button">Aleatorizar cotas livres</button>
                        </div>
                        <div class="manual-number-preview" id="number-preview"></div>
                    </div>
                    <div class="manual-order-message" id="manual-order-message" role="status" aria-live="polite"></div>
                </section>

                <footer class="manual-order-footer">
                    <p>O preço e a disponibilidade serão validados novamente antes da gravação.</p>
                    <button class="manual-order-submit" id="manual-order-submit" type="submit">Cadastrar pedido</button>
                </footer>
            </div>
        </form>
    </div>
</main>

<script>
(function($){
    var money = new Intl.NumberFormat('pt-BR', {style:'currency', currency:'BRL'});
    var raffle = $('#raffle');
    var quantity = $('#quantidade');
    var preview = $('#number-preview');
    var message = $('#manual-order-message');

    function selectedCampaign(){ return raffle.find('option:selected'); }
    function updateSummary(){
        var option = selectedCampaign();
        var unitPrice = parseFloat(option.data('price')) || 0;
        var amount = Math.max(0, parseInt(quantity.val(), 10) || 0);
        var available = option.data('available');
        $('#calculated-total').text(money.format(unitPrice * amount));
        $('#available-label').text(available === undefined ? 'Selecione uma campanha' : Number(available).toLocaleString('pt-BR') + ' cotas disponíveis');
        preview.removeClass('visible').empty();
        message.removeClass('error success').hide().text('');
    }
    raffle.on('change', updateSummary);
    quantity.on('input', updateSummary);
    updateSummary();

    $('#randomize-numbers').on('click', function(){
        var button = $(this);
        if (!raffle.val() || (parseInt(quantity.val(), 10) || 0) < 1) {
            message.attr('class','manual-order-message error').text('Selecione a campanha e informe a quantidade de cotas.');
            return;
        }
        button.prop('disabled', true).text('Aleatorizando...');
        message.removeClass('error success').hide();
        $.ajax({
            url: _base_url_ + 'class/Main.php?action=preview_manual_order_numbers',
            method: 'POST', dataType: 'json',
            data: {raffle: raffle.val(), quantidade: quantity.val()}
        }).done(function(response){
            if (response.status !== 'success') {
                message.attr('class','manual-order-message error').text(response.msg || 'Não foi possível aleatorizar as cotas.');
                return;
            }
            preview.empty();
            response.numbers.slice(0, 30).forEach(function(number){ preview.append($('<span class="manual-number-chip">').text(number)); });
            if (response.numbers.length > 30) preview.append($('<span class="manual-number-more">').text('+' + (response.numbers.length - 30) + ' cotas'));
            preview.addClass('visible');
            message.attr('class','manual-order-message success').text(response.numbers.length + ' cotas livres aleatorizadas. Total: R$ ' + response.total + '.');
        }).fail(function(){
            message.attr('class','manual-order-message error').text('Não foi possível consultar as cotas livres. Tente novamente.');
        }).always(function(){ button.prop('disabled', false).text('Aleatorizar novamente'); });
    });

    $('#manage-order').on('submit', function(event){
        event.preventDefault();
        var button = $('#manual-order-submit');
        var campaignValue = raffle.val();
        var customerValue = $.trim($('#customer_name').val());
        var quantityValue = parseInt(quantity.val(), 10) || 0;
        if (!campaignValue || !customerValue || quantityValue < 1) {
            message.attr('class','manual-order-message error').text(
                !campaignValue ? 'Selecione uma campanha para continuar.' :
                (!customerValue ? 'Informe o nome completo do cliente.' : 'Informe uma quantidade de cotas maior que zero.')
            ).get(0).scrollIntoView({behavior:'smooth', block:'center'});
            (!campaignValue ? raffle : (!customerValue ? $('#customer_name') : quantity)).focus();
            return;
        }
        button.prop('disabled', true).text('Cadastrando...');
        button.attr('aria-busy', 'true');
        message.removeClass('error success').hide();
        $.ajax({
            url: _base_url_ + 'class/Main.php?action=create_order',
            method: 'POST', dataType: 'json',
            data: $(this).serialize(), timeout: 30000
        }).done(function(response){
            if (response.status === 'success') {
                message.attr('class','manual-order-message success').text('Pedido criado com sucesso. ' + response.numbers.length + ' cotas separadas, total de R$ ' + response.total + '.');
                message.get(0).scrollIntoView({behavior:'smooth', block:'center'});
                button.text('Pedido cadastrado');
                window.setTimeout(function(){ window.location.href = './?page=orders'; }, 1600);
            } else {
                message.attr('class','manual-order-message error').text(response.msg || 'Não foi possível criar o pedido.');
                button.prop('disabled', false).text('Cadastrar pedido');
                button.removeAttr('aria-busy');
            }
        }).fail(function(xhr){
            var serverMessage = xhr.responseJSON && xhr.responseJSON.msg ? xhr.responseJSON.msg : '';
            if (!serverMessage && xhr.responseText) {
                try { serverMessage = JSON.parse(xhr.responseText).msg || ''; } catch (ignored) {}
            }
            message.attr('class','manual-order-message error').text(serverMessage || 'O servidor não concluiu o pedido. Nenhuma confirmação foi recebida.');
            button.prop('disabled', false).text('Cadastrar pedido');
            button.removeAttr('aria-busy');
        });
    });
})(jQuery);
</script>
