<?php

if (isset($_GET['id']) && 0 < $_GET['id']) {
    $qry = $conn->query('SELECT * from `product_list` where id = \'' . $_GET['id'] . '\' ');

    if (0 < $qry->num_rows) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}

// A tela de nova campanha usa os mesmos campos da edição. Inicializar os
// valores opcionais evita avisos e mantém o formulário previsível no PHP 8+.
$campaignFormDefaults = [
    'double_ini' => '',
    'double_fim' => '',
    'qtd_upsell' => '',
    'desconto_upsell' => '',
    'probabilidade' => 0,
    'quantidade_compra_sorte' => 0,
];
foreach ($campaignFormDefaults as $field => $defaultValue) {
    if (!isset($$field)) {
        $$field = $defaultValue;
    }
}
$winner = ['name' => '', 'number' => ''];
$isNewCampaign = !isset($_GET['id']) || (int) $_GET['id'] <= 0;
if ($isNewCampaign && (!isset($cotas_premiadas) || trim((string) $cotas_premiadas) === '')) {
    $defaultWinningNumbers = range(0, 9);
    $cotas_premiadas = implode(',', $defaultWinningNumbers);
    $cotas_premiadas_premios = implode(',', array_map(static function ($number) {
        return $number . ':Prêmio surpresa ' . ($number + 1) . ':premiada';
    }, $defaultWinningNumbers));
    $tipo_auto_cota = $cotas_premiadas;
    $cotas_premiadas_descricao = 'Além do prêmio principal, esta campanha possui 10 cotas premiadas. Consulte abaixo os números e os prêmios disponíveis.';
}

$winningPrizeMap = [];
foreach (explode(',', (string) ($cotas_premiadas_premios ?? '')) as $winningPrizeEntry) {
    $winningPrizeParts = array_map('trim', explode(':', $winningPrizeEntry));
    $winningPrizeNumber = array_shift($winningPrizeParts);
    if (end($winningPrizeParts) === 'premiada') {
        array_pop($winningPrizeParts);
    }
    if ($winningPrizeNumber !== '') {
        $winningPrizeMap[$winningPrizeNumber] = implode(': ', $winningPrizeParts);
    }
}
$winningTicketRows = [];
foreach (explode(',', (string) ($cotas_premiadas ?? '')) as $winningTicketNumber) {
    $winningTicketNumber = trim($winningTicketNumber);
    if ($winningTicketNumber !== '') {
        $winningTicketRows[] = ['number' => $winningTicketNumber, 'prize' => $winningPrizeMap[$winningTicketNumber] ?? ''];
    }
}
?>

<style>
    .can-toggle label .can-toggle__switch {
        height: 41.46px !important;
        margin-top: 4px !important;
    }

    .can-toggle label .can-toggle__switch:before {
        height: 92%
    }

    .can-toggle label .can-toggle__switch:after {
        height: 92%
    }

    .tag.premiada {
        background-color: green !important;
    }

    .tag.ouro {
        background-color: #f2d007 !important;
    }

    .tag.coringa {
        background-color: black !important;
    }

    .tag.maior {
        background-color: #f20707 !important;
    }

    .tag.menor {
        background-color: #07f2f2 !important;
    }

    .tag {
        background-color: #7e3af2 !important;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        display: inline-block;
        margin: 0.25rem;
        color: #fff;
    }

    .tag .remove-tag {
        margin-left: 0.5rem;
        cursor: pointer;
    }

    .tag .remove-premio {
        margin-left: 0.5rem;
        cursor: pointer;
    }

    .tag .remove-tipo {
        margin-left: 0.5rem;
        cursor: pointer;
    }

    .tag .remove-tipo_roleta {
        margin-left: 0.5rem;
        cursor: pointer;
    }

    .tag .remove-tipo_box {
        margin-left: 0.5rem;
        cursor: pointer;
    }

    .campaign-tab-guide{margin:0 0 18px;padding:14px 16px;border:1px solid rgba(96,165,250,.24);border-radius:12px;background:rgba(30,58,138,.14);color:#bfdbfe;font-size:12px;line-height:1.55}.campaign-tab-guide strong{display:block;margin-bottom:3px;color:#eff6ff;font-size:13px}.campaign-field-help{display:block;margin-top:6px;color:#8fa2bc;font-size:11px;font-weight:400;line-height:1.45}.winning-editor{margin-top:16px}.winning-editor-header{display:grid;grid-template-columns:minmax(150px,.8fr) minmax(260px,2fr) 42px;gap:10px;padding:0 10px 7px;color:#94a3b8;font-size:11px;font-weight:750;text-transform:uppercase}.winning-ticket-row{display:grid;grid-template-columns:minmax(150px,.8fr) minmax(260px,2fr) 42px;gap:10px;align-items:center;margin-bottom:9px;padding:10px;border:1px solid #344158;border-radius:11px;background:rgba(15,23,42,.62)}.winning-ticket-row input{margin:0!important}.winning-ticket-remove{display:grid;width:38px;height:38px;place-items:center;border:1px solid rgba(248,113,113,.35);border-radius:9px;background:rgba(127,29,29,.25);color:#fecaca;font-size:20px}.winning-ticket-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px}.winning-ticket-add{min-height:40px;padding:0 14px;border:1px solid #6d5ca5;border-radius:9px;background:rgba(109,40,217,.2);color:#ede9fe;font-size:12px;font-weight:750}.winning-ticket-count{color:#a7f3d0;font-size:12px;font-weight:700}@media(max-width:650px){.winning-editor-header{display:none}.winning-ticket-row{grid-template-columns:1fr 42px}.winning-ticket-row .winning-prize-input{grid-column:1/-1;grid-row:2}.winning-ticket-remove{grid-column:2;grid-row:1}.winning-ticket-actions{align-items:stretch;flex-direction:column}.winning-ticket-add{width:100%}}
</style>
<style>
    .add_field,
    .add_field_roleta,
    .add_field_box,
    .add_field_,
    .can-toggle {
        margin-bottom: 20px
    }

    .active-tab {
        border-bottom: none !important
    }

    .desconto,
    .roleta,
    .box,
    .ganhador {
        border: 1px solid #e2e8f0;
        padding: 10px;
        margin-bottom: 20px
    }

    div#descontos,
    div#boxs,
    div#roletas {
        display: flex;
        flex-wrap: wrap
    }

    .grupo-desconto,
    .grupo-roleta,
    .grupo-box,
    .grupo-ganhador {
        margin-right: 20px
    }

    span.discount-number {
        display: inline-block;
        border: 1px solid #e2e8f0;
        border-radius: 100%;
        width: 25px;
        height: 25px;
        text-align: center
    }

    .can-toggle {
        position: relative
    }

    .can-toggle *,
    .can-toggle :after,
    .can-toggle :before {
        box-sizing: border-box
    }

    .can-toggle input[type=checkbox] {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0
    }

    .can-toggle input[type=checkbox]:checked~label .can-toggle__switch:before {
        content: attr(data-unchecked);
        left: 0
    }

    .can-toggle label {
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        position: relative;
        display: flex;
        align-items: center;
        font-size: 14px
    }

    .can-toggle label .can-toggle__switch {
        position: relative;
        transition: background-color .3s cubic-bezier(0, 1, .5, 1);
        background: #848484;
        height: 36px;
        flex: 0 0 134px;
        border-radius: 4px
    }

    .can-toggle label .can-toggle__switch:before {
        content: attr(data-checked);
        position: absolute;
        top: 0;
        text-transform: uppercase;
        text-align: center;
        color: rgba(255, 255, 255, .5);
        left: 67px;
        font-size: 12px;
        line-height: 36px;
        width: 67px;
        padding: 0 12px
    }

    .can-toggle label .can-toggle__switch:after {
        content: attr(data-unchecked);
        position: absolute;
        z-index: 5;
        text-transform: uppercase;
        text-align: center;
        background: #fff;
        transform: translate3d(0, 0, 0);
        transition: transform .3s cubic-bezier(0, 1, .5, 1);
        color: #777;
        top: 2px;
        left: 2px;
        border-radius: 2px;
        width: 65px;
        line-height: 32px;
        font-size: 12px
    }

    .can-toggle input[type=checkbox]:focus~label .can-toggle__switch,
    .can-toggle input[type=checkbox]:hover~label .can-toggle__switch {
        background-color: #777
    }

    .can-toggle input[type=checkbox]:focus~label .can-toggle__switch:after,
    .can-toggle input[type=checkbox]:hover~label .can-toggle__switch:after {
        color: #5e5e5e;
        box-shadow: 0 3px 3px rgba(0, 0, 0, .4)
    }

    .can-toggle input[type=checkbox]:hover~label {
        color: #6a6a6a
    }

    .can-toggle input[type=checkbox]:checked~label:hover {
        color: #55bc49
    }

    .can-toggle input[type=checkbox]:checked~label .can-toggle__switch {
        background-color: #70c767
    }

    .can-toggle input[type=checkbox]:checked~label .can-toggle__switch:after {
        content: attr(data-checked);
        color: #4fb743;
        transform: translate3d(65px, 0, 0)
    }

    .can-toggle input[type=checkbox]:checked:focus~label .can-toggle__switch,
    .can-toggle input[type=checkbox]:checked:hover~label .can-toggle__switch {
        background-color: #5fc054
    }

    .can-toggle input[type=checkbox]:checked:focus~label .can-toggle__switch:after,
    .can-toggle input[type=checkbox]:checked:hover~label .can-toggle__switch:after {
        color: #47a43d;
        box-shadow: 0 3px 3px rgba(0, 0, 0, .4)
    }

    .can-toggle label .can-toggle__switch:hover:after {
        box-shadow: 0 3px 3px rgba(0, 0, 0, .4)
    }

    svg#view-email,
    svg#view-phone {
        display: inline-block;
        margin-left: 10px;
        cursor: pointer
    }

    img#loadlogo {
        max-width: 96%;
        max-height: 12em;
        object-fit: scale-down;
        object-position: center center;
        border-radius: 6%
    }

    .imagens-campanha>label {
        margin: 24px 0;
        line-height: initial !important
    }

    .imagens-campanha input[type=file] {
        display: block
    }

    .imagens-campanha .imageThumb {
        max-height: 75px;
        border: 2px solid #9027b0;
        padding: 1px;
        cursor: pointer
    }

    .imagens-campanha .pip {
        display: inline-block;
        margin: 10px 10px 0 0
    }

    .imagens-campanha .remove {
        display: block;
        text-align: center;
        cursor: pointer;
        margin-top: -20px
    }

    .imagens-campanha .remove svg {
        display: inline-block !important
    }

    span.add_image img {
        width: 150px;
        cursor: pointer
    }

    span.remove-logo {
        display: block;
        cursor: pointer;
        width: 35px;
        margin-top: 10px
    }

    .image-container__box {
        background-color: #fff;
        border: 1px dashed #9027b0;
        border-radius: 2px;
        cursor: pointer;
        height: 122px;
        margin-bottom: 6px;
        margin-right: 12px;
        margin-top: 6px;
        text-align: center;
        width: 160px
    }

    .box__icon {
        float: left;
        height: 30px;
        margin-top: 24px;
        width: 100%
    }

    .box__main-text {
        color: #9027b0;
        float: left;
        font-size: 16px;
        margin-top: 5px;
        width: 100%
    }

    .box__info-text {
        color: #9027b0;
        font-size: 11px;
        line-height: 2;
        margin-top: 1px;
        width: 100%
    }

    @media all and (max-width:40em) {
        #tabs {
            flex-wrap: wrap
        }

        #tabs .mr-1 {
            margin-bottom: 15px
        }

        #descontos,
        #roleta,
        #ganhadores {
            display: block !important
        }

        .grupo-desconto,
        .grupo-roleta,
        .grupo-ganhador {
            margin-right: 0
        }
    }

    #campaign-save-feedback {
        position: fixed;
        top: 22px;
        right: 22px;
        z-index: 9999;
        display: none;
        width: calc(100% - 44px);
        max-width: 440px;
        padding: 14px 18px;
        border-radius: 10px;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .28);
    }

    #campaign-save-feedback.success { background: #047857; }
    #campaign-save-feedback.error { background: #b91c1c; }
    #campaign-save-feedback.info { background: #6d28d9; }
    .campaign-editor-shell{max-width:1180px;padding-bottom:52px}.campaign-editor-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:28px 0 20px}.campaign-editor-heading h2{margin:0;color:#f8fafc;font-size:28px;font-weight:780;letter-spacing:-.03em}.campaign-editor-eyebrow{margin:0 0 4px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.campaign-editor-subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}.campaign-editor-new{display:inline-flex;min-height:40px;align-items:center;padding:0 14px;border:1px solid #6d5ca5;border-radius:10px;background:rgba(109,40,217,.2);color:#ede9fe;font-size:13px;font-weight:750}.campaign-editor-card{overflow:hidden;padding:0!important;border:1px solid #2d3748;border-radius:17px!important;background:linear-gradient(145deg,rgba(30,41,59,.72),rgba(17,24,39,.96))!important;box-shadow:0 20px 55px rgba(0,0,0,.2)!important}.campaign-tabs-wrap{overflow-x:auto;padding:14px 16px 0;border-bottom:1px solid #2d3748;background:rgba(15,23,42,.42)}#tabs{gap:7px;min-width:max-content}#tabs li{margin:0!important}#tabs a{border:1px solid transparent!important;border-radius:9px 9px 0 0!important;background:transparent!important;color:#94a3b8!important;font-size:12px!important;transition:.18s}#tabs a:hover{background:#202838!important;color:#e2e8f0!important}#tabs a.active-tab{border-color:#3f4d63!important;border-bottom-color:#171d28!important;background:#171d28!important;color:#fff!important}.campaign-editor-card form{padding:20px 22px 22px}.campaign-editor-card .tabcontent{padding:3px 2px 8px}.campaign-editor-card input.form-input,.campaign-editor-card select.form-select,.campaign-editor-card textarea.form-textarea{min-height:44px!important;border:1px solid #3f4d63!important;border-radius:9px!important;background:#111827!important;color:#f8fafc!important;box-shadow:none!important}.campaign-editor-card input.form-input:focus,.campaign-editor-card select.form-select:focus,.campaign-editor-card textarea.form-textarea:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}.campaign-editor-card label>span{font-size:12px!important;font-weight:650}.campaign-editor-card .shadow-xs{border:1px solid #303a4b!important;border-radius:11px!important;background:rgba(17,24,39,.7)!important;box-shadow:none!important}.campaign-quantity-note{display:block;margin-top:6px;color:#6ee7b7;font-size:11px}.campaign-save-row{display:flex;align-items:center;justify-content:flex-end;margin-top:22px;padding-top:18px;border-top:1px solid #2d3748}.campaign-save-row #save-product-button{min-width:170px;border-radius:10px!important;background:linear-gradient(135deg,#8b5cf6,#7c3aed)!important;box-shadow:0 9px 22px rgba(124,58,237,.22)}@media(max-width:700px){.campaign-editor-heading{align-items:flex-start;flex-direction:column}.campaign-editor-new{width:100%;justify-content:center}.campaign-editor-card form{padding:16px}.campaign-save-row #save-product-button{width:100%}}
</style>
<style>
.campaign-editor-card [aria-invalid="true"]{border-color:#ef4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.16)!important}#campaign-save-feedback{overflow-wrap:anywhere}@media(max-width:760px){#campaign-save-feedback{top:10px;right:10px;width:calc(100% - 20px)}.campaign-tabs-wrap{-webkit-overflow-scrolling:touch}.campaign-editor-card .grid{grid-template-columns:1fr!important}.campaign-editor-card .col-span-2{grid-column:auto!important}.campaign-editor-card input,.campaign-editor-card select,.campaign-editor-card textarea{max-width:100%}.imagens-campanha{display:grid!important;grid-template-columns:1fr!important}.image-container__box{max-width:100%}}
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container px-6 mx-auto campaign-editor-shell">
        <header class="campaign-editor-heading">
            <div><p class="campaign-editor-eyebrow">Campanhas</p><h2><?= isset($id) ? 'Editar campanha' : 'Nova campanha' ?></h2><p class="campaign-editor-subtitle">Organize os dados, imagens e regras da campanha em um só lugar.</p></div>
            <?php if (isset($id)): ?><a href="./?page=products/manage_product" id="create_new" class="campaign-editor-new">+ Criar nova campanha</a><?php endif; ?>
        </header>
        <div id="campaign-save-feedback" role="status" aria-live="polite"></div>
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800 campaign-editor-card">
            <div class="flex campaign-tabs-wrap">
                <ul class="flex" id="tabs">
                    <li class="mr-1">
                        <a href="#tab1" class="bg-white dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700 active-tab">Dados</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab2" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Imagens</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab3" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Descontos</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab4" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Ranking</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab5" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Barra</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab6" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Ganhador</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab7" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Premiadas</a>
                    </li>
                    <li class="mr-1">
                        <a href="#tab11" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Maior/Menor</a>
                    </li>

                    <li class="mr-1">
                        <a href="#tab8" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Upsell</a>
                    </li>
                </ul>
            </div>
            <form action="" id="product-form">
                <input type="hidden" name="id" value="<?= isset($id) ? $id : '' ?>">
                <div class="mt-4">
                    <div id="tab1" class="tabcontent text-gray-700 dark:text-gray-400">
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-2 mt-4">
                            <label class="block text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Titulo</span>
                                <input name="name" id="name" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Nome da campanha" value="<?= isset($name) ? $name : '' ?>" />
                            </label>
                            <label class="block text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Subtitulo</span>
                                <input name="subtitle" id="subtitle" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="ex: CAMPANHA 21 HORAS" value="<?= isset($subtitle) ? $subtitle : '' ?>" />
                            </label>
                        </div>
                       <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Descrição</span>
                            <p style="font-size:13px;color: orange;font-style:italic;">Você pode utilizar tags html na descrição para uma melhor formatação</p>
                        </label><textarea name="description" id="description" class="summernote block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="6" placeholder="Descrição da campanha">
<?= isset($description) ? $description : '' ?>
</textarea>
                        <label class="block mt-4 text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Escolha Tipo de Campanha</span>
                            <select name="type_of_draw" id="type_of_draw" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                <option value="1" <?= isset($type_of_draw) && $type_of_draw == '1' ? 'selected' : '' ?>>Campanha Aleatórios</option>
                                <option value="2" <?= isset($type_of_draw) && $type_of_draw == '2' ? 'selected' : '' ?>>Campanha Números</option>
                            </select>
                        </label>
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-6 mt-4 qtd-select">
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                                <div>
                                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pacote Titulos 1</p>
                                    <input name="qty_select_1" id="qty_select_1" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= isset($qty_select_1) ? $qty_select_1 : 10 ?>" />
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                                <div>
                                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pacote Titulos 2</p>
                                    <input name="qty_select_2" id="qty_select_2" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="20" value="<?= isset($qty_select_2) ? $qty_select_2 : 20 ?>" />
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                                <div>
                                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pacote Titulos 3*</p><input name="qty_select_3" id="qty_select_3" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="50" value="<?= isset($qty_select_3) ? $qty_select_3 : 50 ?>" />
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                                <div>
                                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pacote Titulos 4</p>
                                    <input name="qty_select_4" id="qty_select_4" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="100" value="<?= isset($qty_select_4) ? $qty_select_4 : 100 ?>" />
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                                <div>
                                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pacote Titulos 5</p>
                                    <input name="qty_select_5" id="qty_select_5" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="200" value="<?= isset($qty_select_5) ? $qty_select_5 : 200 ?>" />
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                                <div>
                                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pacote Titulos 6</p>
                                    <input name="qty_select_6" id="qty_select_6" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="300" value="<?= isset($qty_select_6) ? $qty_select_6 : 300 ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6 mt-4">
                            <label class="block mt-4 text-sm col-span-2">
                                <span class="text-gray-700 dark:text-gray-400">Data e hora da campanha</span>
                                <input style="width:100%" type="datetime-local" name="date_of_draw" id="date_of_draw" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($date_of_draw) ? $date_of_draw : '' ?>" />
                            </label><label class="block mt-4 text-sm ml-4"><span class="text-gray-700 dark:text-gray-400">Campanha privada?</span>
                                <div class="can-toggle"><input type="checkbox" name="private_draw" id="private_draw" <?= isset($private_draw) && $private_draw == 1 ? ' checked' : '' ?>><label for="private_draw">
                                        <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                                    </label></div>
                            </label><label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Destaque a Campanha?</span>
                                <div class="can-toggle"><input type="checkbox" name="featured_draw" id="featured_draw" <?= isset($featured_draw) && $featured_draw == 1 ? 'checked' : '' ?>><label for="featured_draw">
                                        <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                                    </label></div>
                            </label>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 mt-4"><label class="block mt-4 text-sm qtd-numeros"><span class="text-gray-700 dark:text-gray-400">Quantidade total de números</span><input name="qty_numbers" id="qty_numbers" type="number" min="10" max="10000000" step="1" required class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Ex.: 1000000" value="<?= isset($qty_numbers) ? htmlspecialchars($qty_numbers, ENT_QUOTES, 'UTF-8') : '1000000' ?>">
                                    <small class="campaign-quantity-note" id="campaign-quantity-note">Defina manualmente quantas cotas existirão na campanha.</small>
                            </label>
                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Valor por cada titulos</span><input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10,00" value="<?= isset($price) ? $price : '' ?>" />
                            </label>
                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Tempo para pagamento </span><select name="limit_order_remove" id="limit_order_remove" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                    <option value="">Selecione o Tempo</option>
                                    <option value="0" <?= isset($limit_order_remove) && $limit_order_remove == 0 ? ' selected' : '' ?>>Sem limite</option>
                                    <option value="5" <?= isset($limit_order_remove) && $limit_order_remove == 5 ? 'selected' : ''; ?>>Tempo 5 minutos</option>
                                    <option value="10" <?= isset($limit_order_remove) && $limit_order_remove == 10 ? 'selected' : ''; ?>>Tempo 10 minutos</option>
                                    <option value="15" <?= isset($limit_order_remove) && $limit_order_remove == 15 ? 'selected' : ''; ?>>Tempo 15 minutos</option>
                                    <option value="30" <?= isset($limit_order_remove) && $limit_order_remove == 30 ? 'selected' : ''; ?>>Tempo 30 minutos</option>
                                </select>
                            </label>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            <label class="block mt-4 text-sm qtd-minima">
                                <span class="text-gray-700 dark:text-gray-400">Quantidade limite de compras por usuário</span>
                                <input name="limit_orders" id="limit_orders" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="1" value="<?= isset($limit_orders) ? $limit_orders : '0' ?>" />
                            </label>
                            <label class="block mt-4 text-sm qtd-minima"><span class="text-gray-700 dark:text-gray-400">Quantidade mínima de números comprados por vez</span><input name="min_purchase" id="min_purchase" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="1" value="<?= isset($min_purchase) ? $min_purchase : '1' ?>" /></label><label class="block mt-4 text-sm qtd-maxima"><span class="text-gray-700 dark:text-gray-400">Quantidade máxima de números comprados por vez</span><input name="max_purchase" id="max_purchase" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Max 20000" type="number" max="200000" value="<?= isset($max_purchase) ? $max_purchase : '500' ?>"></label>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"><label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Status de exibição</span><select name="status_display" id="status_display" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                    <option value="1" <?= isset($status_display) && $status_display == 1 ? 'selected' : '' ?>>Adquira já!</option>
                                    <option value="2" <?= isset($status_display) && $status_display == 2 ? 'selected' : '' ?>>Corre que está acabando!</option>
                                    <option value="3" <?= isset($status_display) && $status_display == 3 ? 'selected' : '' ?>>Aguarde a campanha!</option>
                                    <option value="4" <?= isset($status_display) && $status_display == 4 ? 'selected' : '' ?>>Concluído</option>
                                    <option value="5" <?= isset($status_display) && $status_display == 5 ? 'selected' : '' ?>>Em breve!</option>
                                    <option value="6" <?= isset($status_display) && $status_display == 6 ? 'selected' : '' ?>>Aguarde o sorteio!</option>
                                </select>
                            </label>
                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Status da campanha</span><select name="status" id="status" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                    <option value="1" <?= isset($status) && $status == 1 ? 'selected' : '' ?>>Ativo</option>
                                    <option value="2" <?= isset($status) && $status == 2 ? 'selected' : '' ?>>Pausado</option>
                                    <option value="3" <?= isset($status) && $status == 3 ? 'selected' : '' ?>>Finalizado</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div id="tab2" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                        <div class="imagens-campanha">
                            <!-- Imagem principal -->
                            <label class="pure-material-textfield-outlined"><label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Imagem principal</span>
                                </label>
                                <div class="image-container__box dark:bg-gray-800 add-logo"><svg width="35" height="30" viewBox="0 0 35 30" xmlns="http://www.w3.org/2000/svg" class="box__icon">
                                        <path d="M3.502 3.4h5.11L12.02.09h10.222l3.407 3.31h5.111c1.882 0 3.408 1.481 3.408 3.309v19.856c0 1.828-1.526 3.31-3.408 3.31H3.502c-1.882 0-3.408-1.482-3.408-3.31V6.709c0-1.828 1.526-3.31 3.408-3.31zM17.13 8.364c-4.705 0-8.518 3.704-8.518 8.273 0 4.57 3.813 8.273 8.518 8.273 4.704 0 8.518-3.704 8.518-8.273 0-4.57-3.814-8.273-8.518-8.273zm0 3.309c2.823 0 5.11 2.222 5.11 4.964 0 2.741-2.287 4.964-5.11 4.964-2.823 0-5.111-2.223-5.111-4.964 0-2.742 2.288-4.964 5.11-4.964z" fill="#9027B0" fill-rule="evenodd"></path>
                                    </svg><span class="box__main-text">Adicionar Imagem</span><span class="box__info-text"> JPG, PNG, GIF ou WebP </span></div><input id="customFile1" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" type="file" name="img" style="display:none;">
                                <div class="show_logo" style="display:inline-block;"><img id="loadlogo" src="<?= validate_image(isset($image_path) ? $image_path : '') ?>" width="150" alt="Logo" /><span class="remove-logo"><svg width='25' height='25' viewBox='0 0 25 25' xmlns='http://www.w3.org/2000/svg' class='s'>
                                            <g transform='translate(.317)' fill='none' fill-rule='evenodd'>
                                                <rect fill='#323232' opacity='.99' width='24.503' height='24.33' rx='12.165'></rect>
                                                <path d='M12.266 11.134L7.992 6.86c-.301-.3-.783-.3-1.054 0-.3.299-.3.777 0 1.046l4.275 4.274-4.305 4.244c-.3.3-.3.778 0 1.047.301.298.783.298 1.054 0l4.304-4.245 4.275 4.245c.3.298.782.298 1.053 0 .271-.3.301-.778 0-1.047L13.32 12.18l4.274-4.244c.301-.3.301-.777 0-1.046-.27-.3-.752-.3-1.053-.03l-4.275 4.274z' fill='#FFF'></path>
                                            </g>
                                        </svg>
                                    </span>
                                </div>
                            </label>
                            <!-- Fim imagem principal -->
                            <!-- galeria -->
                            <div class="galeria-imagens">
                                <label class="block mt-4 text-sm">
                                    <span class="text-gray-700 dark:text-gray-400">Galeria de imagens</span></label>
                                <label class="pure-material-textfield-outlined" style="margin-top:5px;display:inline-block;">
                                    <span class="add_image">
                                        <div class="image-container__box dark:bg-gray-800">
                                            <svg width="35" height="30" viewBox="0 0 35 30" xmlns="http://www.w3.org/2000/svg" class="box__icon">
                                                '<path d="M3.502 3.4h5.11L12.02.09h10.222l3.407 3.31h5.111c1.882 0 3.408 1.481 3.408 3.309v19.856c0 1.828-1.526 3.31-3.408 3.31H3.502c-1.882 0-3.408-1.482-3.408-3.31V6.709c0-1.828 1.526-3.31 3.408-3.31zM17.13 8.364c-4.705 0-8.518 3.704-8.518 8.273 0 4.57 3.813 8.273 8.518 8.273 4.704 0 8.518-3.704 8.518-8.273 0-4.57-3.814-8.273-8.518-8.273zm0 3.309c2.823 0 5.11 2.222 5.11 4.964 0 2.741-2.287 4.964-5.11 4.964-2.823 0-5.111-2.223-5.111-4.964 0-2.742 2.288-4.964 5.11-4.964z" fill="#9027B0" fill-rule="evenodd"></path></svg>
                                            <span class="box__main-text">Adicionar fotos</span>
                                            <span class="box__info-text"> JPG, PNG, GIF ou WebP </span>
                                        </div>
                                        <input style="display:none;" type="file" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" id="image_gallery" name="image_gallery[]" multiple />
                                    </span>
                                    <div class="drope-files">
                                        <?php
                                        $image_gallery = isset($image_gallery) ? $image_gallery : '';

                                        if ($image_gallery) {
                                            $image_gallery = json_decode($image_gallery, true);

                                            foreach ($image_gallery as $image) { ?>
                                                <span class="pip">
                                                    <img class="imageThumb" src="<?= validate_image($image) ?>" title="" />
                                                    <input type="hidden" name="on-gallery[]" value="<?= $image ?>">
                                                    <br />
                                                    <span class="remove">
                                                        <svg width='25' height='25' viewBox='0 0 25 25' xmlns='http://www.w3.org/2000/svg' class='s'>
                                                            <g transform='translate(.317)' fill='none' fill-rule='evenodd'>
                                                                <rect fill='#323232' opacity='.99' width='24.503' height='24.33' rx='12.165'></rect>
                                                                <path d='M12.266 11.134L7.992 6.86c-.301-.3-.783-.3-1.054 0-.3.299-.3.777 0 1.046l4.275 4.274-4.305 4.244c-.3.3-.3.778 0 1.047.301.298.783.298 1.054 0l4.304-4.245 4.275 4.245c.3.298.782.298 1.053 0 .271-.3.301-.778 0-1.047L13.32 12.18l4.274-4.244c.301-.3.301-.777 0-1.046-.27-.3-.752-.3-1.053-.03l-4.275 4.274z' fill='#FFF'></path>
                                                            </g>
                                                        </svg>
                                                    </span>
                                                </span>
                                        <?php
                                            }
                                        }

                                        ?>
                                    </div>
                                </label>
                            </div>
                            <!-- end galeria -->
                        </div>
                    </div>
                    <div id="tab3" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Utilizar descontos nesse campanha?</span>
                        </label>
                        <div class="can-toggle">
                            <input type="checkbox" name="enable_discount" id="enable_discount" <?= isset($enable_discount) && $enable_discount == 1 ? ' checked' : '' ?>>
                            <label for="enable_discount">
                                <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                            </label>
                        </div>
                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Utilizar o Titulo em dobro?</span>
                        </label>
                        <div class="can-toggle">
                            <input type="checkbox" name="enable_double" id="enable_double" <?= isset($enable_double) && $enable_double == 1 ? ' checked' : '' ?>>
                            <label for="enable_double">
                                <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                            </label>
                        </div>
                        <div class="flex gap-3">
                            <label class="block my-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Inicio em dobro:</span>
                                <input type="datetime-local" name="double_ini" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $double_ini ?>">
                            </label>
                            <label class="block my-4 mx-2 text-sm"><span class="text-gray-700 dark:text-gray-400">Final em dobro:</span>
                                <input type="datetime-local" name="double_fim" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $double_fim ?>">
                            </label>
                        </div>
                        <label class="add_field block mt-4 text-sm" style="display:inline-block;"><span class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Adicionar desconto</span></label>
                        <!-- Descontos -->
                        <div id="descontos" class="descontos">
                            <?php
                            $discount_qty = isset($discount_qty) ? $discount_qty : '';
                            $discount_amount = isset($discount_amount) ? $discount_amount : '';
                            if ($discount_qty && $discount_amount) {
                                $discount_qty = json_decode($discount_qty, true);
                                $discount_amount = json_decode($discount_amount, true);
                                $discounts = [];

                                foreach ($discount_qty as $qty_index => $qty) {
                                    foreach ($discount_amount as $amount_index => $amount) {
                                        // Quando os índices de quantidade e valor coincidirem, vamos adicionar o desconto
                                        if ($qty_index === $amount_index) {
                                            // Adiciona os valores de quantidade, valor e roleta ao array $discounts
                                            $discounts[$qty_index] = [
                                                'qty' => $qty,
                                                'amount' => $amount,
                                            ];
                                        }
                                    }
                                }

                                $count = 0;

                                foreach ($discounts as $discount) {
                                    ++$count;
                            ?>
                                    <div class="grupo-desconto">
                                        <div class="desconto dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number"><?= $count ?></span> Desconto
                                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400"> :</span>
                                                <input type="text" name="discount_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $discount['qty'] ?>">
                                            </label>
                                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Valor do desconto:</span><input type="text" name="discount_amount[]" class="discount_price block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="1.00" value="<?= $discount['amount'] ?>">
                                            </label>
                                        </div>
                                        <?php

                                        if (1 < $count) { ?>
                                            <label class="remove_field block mt-4 text-sm" style="margin-block:20px;">
                                                <span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover desconto</button></label>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                <?php
                                }
                            } else { ?>
                                <div class="grupo-desconto">
                                    <div class="desconto dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number">1</span> Desconto<label class="block mt-4 text-sm">
                                            <span class="text-gray-700 dark:text-gray-400"> :</span>
                                            <input type="text" name="discount_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10"></label>
                                        <label class="block mt-4 text-sm">
                                            <span class="text-gray-700 dark:text-gray-400">Valor do desconto:</span>
                                            <input type="text" name="discount_amount[]" class="discount_price block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="1.00"></label>
                                    </div>
                                </div>
                            <?php
                            }

                            ?>

                        </div>
                    </div>
                    <div id="tab4" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Habilitar Top Compradores?</span>
                        </label>
                        <div class="can-toggle"><input type="checkbox" name="enable_ranking" id="enable_ranking" <?= isset($enable_ranking) && $enable_ranking == 1 ? 'checked' : '' ?>>
                            <label for="enable_ranking">
                                <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                            </label>
                        </div>
                        <div class="ranking_qty">
                            <input type="hidden" name="enable_ranking_show" id="enable_ranking_show" value="1">
                            <div class="campaign-tab-guide" style="margin-top:16px"><strong>Quantidade sempre visível</strong>O ranking mostra o nome e a quantidade de cotas confirmadas de cada comprador.</div>
                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Escolha Tipo Top Compradores Diario ou Total</span>
                                <select name="ranking_type" id="ranking_type" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                    <option value="1" <?= isset($ranking_type) && $ranking_type == '1' ? 'selected' : '' ?>>Top Compradores Total</option>
                                    <option value="2" <?= isset($ranking_type) && $ranking_type == '2' ? 'selected' : '' ?>>Top Compradores Diário</option>
                                </select>
                            </label>
                             <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Definir data/hora ranking ?</span>
                            </label>
                            <div class="can-toggle"><input type="checkbox" name="enable_ranking_definido" id="enable_ranking_definido" <?= isset($enable_ranking_definido) && $enable_ranking_definido == 1 ? 'checked' : '' ?>>
                                <label for="enable_ranking_definido">
                                    <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                                </label>
                            </div>
                            <div class="ranking_definido">
                                <label class="block mt-4 text-sm col-span-2">
                                    <span class="text-gray-700 dark:text-gray-400">Data e hora inicial ranking</span>
                                    <input style="width:100%" type="datetime-local" name="ranking_ini" id="ranking_ini" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($ranking_ini) ? $ranking_ini : '' ?>">
                                </label>
                                <label class="block mt-4 text-sm col-span-2">
                                    <span class="text-gray-700 dark:text-gray-400">Data e hora final ranking</span>
                                    <input style="width:100%" type="datetime-local" name="ranking_fim" id="ranking_fim" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($ranking_fim) ? $ranking_fim : '' ?>">
                                </label>
                            </div>
                        
                            <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Deseja mostrar quantos compradores?</span><input name="ranking_qty" id="ranking_qty" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="1" value="<?= isset($ranking_qty) ? $ranking_qty : '' ?>" /></label>
                            
                        </div>
                    </div>
                    <div id="tab5" class="tabcontent text-gray-700 dark:text-gray-400 hidden"><label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Exibir barra de progresso?</span>
                        </label>
                        <div class="can-toggle"><input type="checkbox" name="enable_progress_bar" id="enable_progress_bar" <?= isset($enable_progress_bar) && $enable_progress_bar == 1 ? 'checked' : '' ?>>
                            <label for="enable_progress_bar">
                                <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                            </label>
                        </div>
                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Habilitar Barra Fake?</span>
                        </label>
                        <div class="can-toggle"><input type="checkbox" name="enable_progress_bar_fake" id="enable_progress_bar_fake" <?= isset($enable_progress_bar_fake) && $enable_progress_bar_fake == 1 ? 'checked' : '' ?>>
                            <label for="enable_progress_bar_fake">
                                <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                            </label>
                        </div>
                        <div class="value_progrerss_fake">
                            <label class="block mt-4 text-sm">
                                <span class="text-gray-700 dark:text-gray-400">Percentual</span>
                                <input name="enable_progress_bar_fake_value" id="enable_progress_bar_fake_value" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10,00" value="<?= isset($enable_progress_bar_fake_value) ? $enable_progress_bar_fake_value : '' ?>" />
                            </label>
                        </div>
                        
                    </div>
                    <div id="tab6" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                        <label class="add_field_ block mt-4 text-sm" style="display:inline-block;"><span class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Adicionar ganhador</span></label>
                        <!-- Descontos -->
                        <div id="ganhadores" class="ganhadores">
                            <?php
                            $winners_qty = 5;
                            $draw_number = isset($draw_number) ? $draw_number : '';
                            if ($winners_qty && $draw_number) {
                                $draw_winner = json_decode($draw_winner, true);
                                $draw_number = json_decode($draw_number, true);
                                $winners = [];

                                foreach ($draw_winner as $qty_index => $name) {
                                    foreach ($draw_number as $amount_index => $number) {
                                        if ($qty_index === $amount_index) {
                                            $winners[$qty_index] = ['name' => $name, 'number' => $number];
                                        }
                                    }
                                }
                                $count = 0;

                                foreach ($winners as $winner) {
                                    ++$count;
                            ?>
                                    <div class="grupo-ganhador">
                                        <div class="ganhador dark:border-gray-600 text-gray-700 dark:text-gray-400">
                                            <label class="block mt-4 text-sm">
                                                <span class="text-gray-700 dark:text-gray-400"> Telefone ganhador - <?= $count ?>º prêmio</span>
                                                <input type="number" name="draw_name[]" class="draw_number block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Telefone do ganhador" value="<?= $winner['name'] ?>">
                                            </label>
                                            <label class="block mt-4 text-sm">
                                                <span class="text-gray-700 dark:text-gray-400">Número/grupo sorteado - <?= $count ?> º prêmio</span>
                                                <input type="text" name="draw_number[]" class="draw_number block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Número ou grupo sorteado" value="<?= $winner['number'] ?>"></label>
                                        </div>
                                        <?php

                                        if (1 < $count) { ?>
                                            <label class="remove_field_ block mt-4 text-sm" style="margin-block:20px;"><span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover ganhador</button></label>
                                        <?php

                                        }
                                        ?>
                                    </div>
                                <?php
                                }
                            } else { ?>
                                <div class="grupo-ganhador">
                                    <div class="ganhador dark:border-gray-600 text-gray-700 dark:text-gray-400">
                                        <label class="block mt-4 text-sm">
                                            <span class="text-gray-700 dark:text-gray-400"> Telefone ganhador - 1º prêmio</span>
                                            <input type="number" name="draw_name[]" class="draw_number block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Telefone do ganhador" value="<?= $winner['name'] ?>">
                                        </label>
                                        <label class="block mt-4 text-sm">
                                            <span class="text-gray-700 dark:text-gray-400">Número/grupo sorteado - 1º prêmio:</span>
                                            <input type="text" name="draw_number[]" class="draw_number block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Número ou grupo sorteado">
                                        </label>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div id="tab7" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                    <div class="campaign-tab-guide"><strong>Cotas premiadas</strong>Cada linha liga uma cota específica a um prêmio. Novas campanhas já começam com 10 linhas; revise os números e troque “Prêmio surpresa” pelo prêmio real antes de publicar.</div>
                    <div class="winning-editor" id="winning-ticket-editor">
                        <div class="winning-editor-header"><span>Número da cota</span><span>Prêmio entregue</span><span></span></div>
                        <div id="winning-ticket-rows">
                            <?php foreach ($winningTicketRows as $winningTicket): ?>
                                <div class="winning-ticket-row">
                                    <input type="number" min="0" step="1" class="winning-number-input block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input" aria-label="Número da cota premiada" value="<?= htmlspecialchars($winningTicket['number'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="text" class="winning-prize-input block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input" aria-label="Prêmio desta cota" placeholder="Ex.: PIX de R$ 100" value="<?= htmlspecialchars($winningTicket['prize'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="button" class="winning-ticket-remove" title="Remover esta cota" aria-label="Remover esta cota">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="winning-ticket-actions">
                            <button type="button" class="winning-ticket-add" id="add-winning-ticket">+ Adicionar outra cota premiada</button>
                            <span class="winning-ticket-count" id="winning-ticket-count"></span>
                        </div>
                    </div>
                    <input type="hidden" name="cotas_premiadas" id="cotas_premiadas" value="<?= htmlspecialchars((string) ($cotas_premiadas ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="cotas_premiadas_premios" id="cotas_premiadas_premios" value="<?= htmlspecialchars((string) ($cotas_premiadas_premios ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <label class="block mt-4 text-sm qtd-minima">
                        <span class="text-gray-700 dark:text-gray-400">Descrição</span>
                        <input name="cotas_premiadas_descricao" id="cotas_premiadas__descricao" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo isset($cotas_premiadas_descricao) ? $cotas_premiadas_descricao : ''; ?>" placeholder="Além do prêmio principal, temos titulos premiadas esperando por você. " />
                        <small class="campaign-field-help">Este texto aparece na página pública, acima da lista de cotas premiadas.</small>
                    </label>

                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400 ">Habilitar bloqueio de Titulos?</span>
                    </label>
                    <div class="can-toggle" style="margin-top:4px">
                        <input
                            <?php echo isset($status_auto_cota) && $status_auto_cota == 1 ? 'checked' : '' ?>

                            type="checkbox" name="status_auto_cota" id="status_auto_cota" value="<?php echo isset($status_auto_cota) ? $status_auto_cota : '' ?> ">
                        <label for="status_auto_cota">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>

                    </div>

                    <div class="block glass mt-4 text-sm status_cotas" style="">
                        <span class="text-gray-700 dark:text-gray-400 ">
                            Titulos bloqueados
                        </span>
                        <p id="mensagem_porcentagem" style="color:orange" class="italic  text-xs"> Apague o titulo que deseja liberar para
                            venda
                        </p>

                        <div id="tipo-container" class="mt-2">
                            <?php
                            if (isset($tipo_auto_cota)) {
                                $tipo_auto_cota_array = explode(',', $tipo_auto_cota);
                                foreach ($tipo_auto_cota_array as $cota) {
                                    $id = explode(':', $cota);

                                    $id = $id[0];
                                    if ($cota != '') {
                                        echo "<div id='t$id' class='tag'>$cota <span class='remove-tipo'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>



                        <input type="hidden" name="tipo_auto_cota" id="tipo_auto_cota"
                            value="<?php echo isset($tipo_auto_cota) ? $tipo_auto_cota : '' ?> "
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                            placeholder="ex: 12345,83474,78347" style="margin-top:4px">
                    </div>
                     <hr>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400 ">Habilitar Bilhete da sorte?</span>
                    </label>
                    <div class="can-toggle" style="margin-top:4px">
                        <input
                            <?php echo isset($habilitar_cota_sorte) && $habilitar_cota_sorte == 1 ? 'checked' : '' ?>

                            type="checkbox" name="habilitar_cota_sorte" id="habilitar_cota_sorte" value="<?php echo isset($habilitar_cota_sorte) ? $habilitar_cota_sorte : '' ?> ">
                        <label for="habilitar_cota_sorte">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>
                    </div>
                    <div class="periodocotasorte">
                        <?php if ($quantidade_compra_sorte < 0) { ?>
                            <span style="color:green" class="italic  text-lg">O Bilhete informada foi contemplada!!!</span>
                        <?php } ?>
                        <label class="block mt-4 text-sm col-span-2">
                            <span class="text-gray-700 dark:text-gray-400">Periodo Inicial</span>
                            <input style="width:100%" type="datetime-local" name="cota_sorte_ini" id="cota_sorte_ini" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($cota_sorte_ini) ? $cota_sorte_ini : '' ?>">
                        </label>
                        <label class="block mt-4 text-sm col-span-2">
                            <span class="text-gray-700 dark:text-gray-400">Periodo Final</span>
                            <input style="width:100%" type="datetime-local" name="cota_sorte_fim" id="cota_sorte_fim" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($cota_sorte_fim) ? $cota_sorte_fim : '' ?>">
                        </label>

                        <label class="block mt-4 text-sm qtd-minima">
                            <span class="text-gray-700 dark:text-gray-400">Bilhete da sorte.</span>
                            <p style="color:orange" class="italic  text-xs">Insira o numero do bilhete que desejar sair nesse periodo.</p>
                            <input name="cota_sorte" id="cota_sorte" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo isset($cota_sorte) ? $cota_sorte : ''; ?>" placeholder="Coloque aqui bilhete Premiados" />
                        </label>

                        <label class="block mt-4 text-sm qtd-minima">
                            <span class="text-gray-700 dark:text-gray-400">Quantidade de compra.</span>
                            <p style="color:orange" class="italic  text-xs">Insira a quantidade de compras dentro desse periodo para sair a cota.</p>
                            <input type="number" name="quantidade_compra_sorte" id="quantidade_compra_sorte" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo isset($quantidade_compra_sorte) ? $quantidade_compra_sorte : ''; ?>" />
                        </label>

                    </div>
                </div>
                <div id="tab8" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Habilitar Upsell ?</span>
                    </label>
                    <div style="margin-top:4px" class="can-toggle">
                        <input type="checkbox" name="enable_upsell" id="enable_upsell"
                            <?php echo isset($enable_upsell) && $enable_upsell == 1 ? 'checked' : '' ?>
                            value="<?php echo isset($enable_upsell) ? $enable_upsell : '' ?> ">
                        <label for="enable_upsell">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>
                    </div>
                    <div class="flex">
                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Quantidades de Titulos</span>
                            <input type="text" name="qtd_upsell" class="qtd_upsell lock w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $qtd_upsell ?>">
                        </label>

                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">% Desconto de cada Titulos</span>
                            <input type="text" name="desconto_upsell" class="desconto_upsell lock w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $desconto_upsell ?>">
                        </label>
                    </div>
                </div>
                <div id="tab9" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Habilitar Roleta Premiada ?</span>
                    </label>
                    <div style="margin-top:4px" class="can-toggle">
                        <input type="checkbox" name="roleta" id="roleta"
                            <?php echo isset($roleta) && $roleta == 1 ? 'checked' : '' ?>
                            value="<?php echo isset($roleta) ? $roleta : '' ?> ">
                        <label for="roleta">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>
                    </div>

                    <hr>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">
                            Titulos Premiados da Roleta
                            <p style="font-size: 13px; color: orange;">
                                Digite o número do titulo e pressione enter para adicionar
                            </p>
                        </span>
                        <input type="text" id="tags-input_roleta" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="Pressione Enter para adicionar um titulo">
                        <div id="tags-container_roleta" class="mt-2">
                            <?php
                            if (isset($cotas_premiadas_roleta)) {
                                $cotas_premiadas_array_roleta = explode(',', $cotas_premiadas_roleta);
                                $premios_roleta = explode(',', $cotas_premiadas_premios_roleta);

                                foreach ($cotas_premiadas_array_roleta as $cota_roleta) {
                                    $premio_roleta = array_shift($premios_roleta);
                                    $premio_roleta = explode(':', $premio_roleta);
                                    $tipo_roleta = trim($premio_roleta[2]);

                                    $premio_roleta = trim($premio_roleta[1]);

                                    if ($cota_roleta != '') {
                                        echo "<div id='$cota_roleta' data-premio='$premio_roleta' data-tipo='$tipo_roleta'  class='tag $tipo_roleta'>" . trim($cota_roleta) . " <span class='remove-tag_roleta'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                        <input type="hidden" name="cotas_premiadas_roleta" id="cotas_premiadas_roleta" value="<?= isset($cotas_premiadas_roleta) ? $cotas_premiadas_roleta : '' ?>">
                    </label>

                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">
                            Premiações
                        </span>

                        <input type="hidden" name="cotas_premiadas_premios_roleta" id="cotas_premiadas_premios_roleta" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" value="<?= isset($cotas_premiadas_premios_roleta) ? $cotas_premiadas_premios_roleta : '' ?>" placeholder="012345:Pix no valor R$1000" />
                        <div id="premios-container_roleta" class="mt-2">
                            <?php
                            if (isset($cotas_premiadas_premios_roleta)) {
                                $cotas_premiadas_premios_array_roleta = explode(',', $cotas_premiadas_premios_roleta);
                                foreach ($cotas_premiadas_premios_array_roleta as $cota_roleta) {
                                    $id_roleta = explode(':', $cota_roleta);
                                    $tipo_roleta = trim($id_roleta[2]);

                                    $cota_roleta = trim($id_roleta[0]) . ':' . trim($id_roleta[1]);
                                    $id_roleta = trim($id_roleta[0]);
                                    if ($cota_roleta != '' && $cota_roleta != ':') {
                                        echo "<div id='p$id_roleta' data-tipo='$tipo_roleta' data-premio='$cota_roleta'  class='tag  $tipo_roleta'>$cota_roleta <span class='remove-premio_roleta'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                    </label>
                    <label class="block mt-4 text-sm qtd-minima">
                        <span class="text-gray-700 dark:text-gray-400">Descrição</span>
                        <input name="cotas_premiadas_descricao_roleta" id="cotas_premiadas__descricao" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo isset($cotas_premiadas_descricao_roleta) ? $cotas_premiadas_descricao_roleta : ''; ?>" placeholder="Além do prêmio principal, temos titulos premiadas esperando por você. " />
                    </label>

                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400 ">Habilitar Bloqueio de Titulos da Roleta?</span>
                    </label>
                    <div class="can-toggle" style="margin-top:4px">
                        <input
                            <?php echo isset($status_auto_cota_roleta) && $status_auto_cota_roleta == 1 ? 'checked' : '' ?>

                            type="checkbox" name="status_auto_cota_roleta" id="status_auto_cota_roleta" value="<?php echo isset($status_auto_cota_roleta) ? $status_auto_cota_roleta : '' ?> ">
                        <label for="status_auto_cota_roleta">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>

                    </div>

                    <div class="block glass mt-4 text-sm status_cotas_roleta">
                        <span class="text-gray-700 dark:text-gray-400 ">
                            Titulos bloqueados da roleta
                        </span>
                        <p id="mensagem_porcentagem_roleta" style="color:orange" class="italic  text-xs"> Apague o titulo que deseja liberar
                        </p>

                        <div id="tipo-container_roleta" class="mt-2">
                            <?php
                            if (isset($tipo_auto_cota_roleta)) {
                                $tipo_auto_cota_array_roleta = explode(',', $tipo_auto_cota_roleta);
                                foreach ($tipo_auto_cota_array_roleta as $cota_roleta) {
                                    $id_roleta = explode(':', $cota_roleta);

                                    $id_roleta = $id_roleta[0];
                                    if ($cota_roleta != '') {
                                        echo "<div id='t$id_roleta' class='tag'>$cota_roleta <span class='remove-tipo_roleta'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>



                        <input type="hidden" name="tipo_auto_cota_roleta" id="tipo_auto_cota_roleta"
                            value="<?php echo isset($tipo_auto_cota_roleta) ? $tipo_auto_cota_roleta : '' ?> "
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                            placeholder="ex: 12345,83474,78347" style="margin-top:4px">
                    </div>
                    <hr>
                    <label class="add_field_roleta block mt-4 text-sm" style="display:inline-block;"><span class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Adicionar</span></label>
                    <div id="roletas" class="roletas ">
                        <?php


                        $roleta_qty = isset($roleta_qty) ? $roleta_qty : '';
                        $roleta_amount = isset($roleta_amount) ? $roleta_amount : '';
                        if ($roleta_qty && $roleta_amount) {
                            $roleta_qty = json_decode($roleta_qty, true);
                            $roleta_amount = json_decode($roleta_amount, true);
                            $roletas = [];

                            foreach ($roleta_qty as $qty_index => $qty) {
                                foreach ($roleta_amount as $amount_index => $amount) {
                                    if ($qty_index === $amount_index) {
                                        $roletas[$qty_index] = [
                                            'qty' => $qty,
                                            'amount' => $amount
                                        ];
                                    }
                                }
                            }
                            $count = 0;

                            foreach ($roletas as $roleta) {
                                ++$count;
                        ?>
                                <div class="grupo-roleta">
                                    <div class="roleta dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number"><?= $count ?></span> Roletas
                                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Titulos:</span>
                                            <input type="text" name="roleta_amount[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $roleta['amount'] ?>">
                                        </label>
                                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Quantidade:</span>
                                            <input type="text" name="roleta_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $roleta['qty'] ?>">
                                        </label>


                                    </div>
                                    <?php

                                    if (1 < $count) { ?>
                                        <label class="remove_field_roleta block mt-4 text-sm" style="margin-block:20px;">
                                            <span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover</button></label>
                                    <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            }
                        } else { ?>
                            <div class="grupo-roleta">
                                <div class="roleta dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number">1</span> Roletas
                                    <label class="block mt-4 text-sm">
                                        <span class="text-gray-700 dark:text-gray-400">Titulos:</span>
                                        <input type="text" name="roleta_amount[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10">
                                    </label>
                                    <label class="block mt-4 text-sm">
                                        <span class="text-gray-700 dark:text-gray-400">Quantidade:</span>
                                        <input type="text" name="roleta_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10">
                                    </label>

                                </div>
                            </div>
                        <?php
                        }

                        ?>

                    </div>
                </div>
                <div id="tab10" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Habilitar Caixinha Premiada ?</span>
                    </label>
                    <div style="margin-top:4px" class="can-toggle">
                        <input type="checkbox" name="box" id="box"
                            <?php echo isset($box) && $box == 1 ? 'checked' : '' ?>
                            value="<?php echo isset($box) ? $box : '' ?> ">
                        <label for="box">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>
                    </div>
                    <hr>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">
                            Titulos Premiados das Caixinhas
                            <p style="font-size: 13px; color: orange;">
                                Digite o número do titulo e pressione enter para adicionar
                            </p>
                        </span>
                        <input type="text" id="tags-input_box" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="Pressione Enter para adicionar um titulo">
                        <div id="tags-container_box" class="mt-2">
                            <?php
                            if (isset($cotas_premiadas_box)) {
                                $cotas_premiadas_array_box = explode(',', $cotas_premiadas_box);
                                $premios_box = explode(',', $cotas_premiadas_premios_box);

                                foreach ($cotas_premiadas_array_box as $cota_box) {
                                    $premio_box = array_shift($premios_box);
                                    $premio_box = explode(':', $premio_box);
                                    $tipo_box = trim($premio_box[2]);

                                    $premio_box = trim($premio_box[1]);

                                    if ($cota_box != '') {
                                        echo "<div id='$cota_box' data-premio='$premio_box' data-tipo='$tipo_box'  class='tag $tipo_box'>" . trim($cota_box) . " <span class='remove-tag_box'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                        <input type="hidden" name="cotas_premiadas_box" id="cotas_premiadas_box" value="<?= isset($cotas_premiadas_box) ? $cotas_premiadas_box : '' ?>">
                    </label>

                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">
                            Premiações
                        </span>

                        <input type="hidden" name="cotas_premiadas_premios_box" id="cotas_premiadas_premios_box" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" value="<?= isset($cotas_premiadas_premios_box) ? $cotas_premiadas_premios_box : '' ?>" placeholder="012345:Pix no valor R$1000" />
                        <div id="premios-container_box" class="mt-2">
                            <?php
                            if (isset($cotas_premiadas_premios_box)) {
                                $cotas_premiadas_premios_array_box = explode(',', $cotas_premiadas_premios_box);
                                foreach ($cotas_premiadas_premios_array_box as $cota_box) {
                                    $id_box = explode(':', $cota_box);
                                    $tipo_box = trim($id_box[2]);

                                    $cota_box = trim($id_box[0]) . ':' . trim($id_box[1]);
                                    $id_box = trim($id_box[0]);
                                    if ($cota_box != '' && $cota_box != ':') {
                                        echo "<div id='p$id_box' data-tipo='$tipo_box' data-premio='$cota_box'  class='tag  $tipo_box'>$cota_box <span class='remove-premio_box'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                    </label>
                    <label class="block mt-4 text-sm qtd-minima">
                        <span class="text-gray-700 dark:text-gray-400">Descrição</span>
                        <input name="cotas_premiadas_descricao_box" id="cotas_premiadas__descricao" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo isset($cotas_premiadas_descricao_box) ? $cotas_premiadas_descricao_box : ''; ?>" placeholder="Além do prêmio principal, temos titulos premiadas esperando por você. " />
                    </label>

                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400 ">Habilitar Bloqueio de Titulos das Caixinhas?</span>
                    </label>
                    <div class="can-toggle" style="margin-top:4px">
                        <input
                            <?php echo isset($status_auto_cota_box) && $status_auto_cota_box == 1 ? 'checked' : '' ?>

                            type="checkbox" name="status_auto_cota_box" id="status_auto_cota_box" value="<?php echo isset($status_auto_cota_box) ? $status_auto_cota_box : '' ?> ">
                        <label for="status_auto_cota_box">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>

                    </div>

                    <div class="block glass mt-4 text-sm status_cotas_box">
                        <span class="text-gray-700 dark:text-gray-400 ">
                            Titulos bloqueados das caixinhas
                        </span>
                        <p id="mensagem_porcentagem_box" style="color:orange" class="italic  text-xs"> Apague o titulo que deseja liberar para
                            venda
                        </p>

                        <div id="tipo-container_box" class="mt-2">
                            <?php
                            if (isset($tipo_auto_cota_box)) {
                                $tipo_auto_cota_array_box = explode(',', $tipo_auto_cota_box);
                                foreach ($tipo_auto_cota_array_box as $cota_box) {
                                    $id_box = explode(':', $cota_box);

                                    $id_box = $id_box[0];
                                    if ($cota_box != '') {
                                        echo "<div id='t$id_box' class='tag'>$cota_box <span class='remove-tipo_box'>x</span></div>";
                                    }
                                }
                            }
                            ?>
                        </div>



                        <input type="hidden" name="tipo_auto_cota_box" id="tipo_auto_cota_box"
                            value="<?php echo isset($tipo_auto_cota_box) ? $tipo_auto_cota_box : '' ?> "
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                            placeholder="ex: 12345,83474,78347" style="margin-top:4px">
                    </div>
                    <hr>
                    <label class="add_field_box block mt-4 text-sm" style="display:inline-block;">
                        <span class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Adicionar</span>
                    </label>
                    <div id="boxs" class="boxs ">
                        <?php


                        $box_qty = isset($box_qty) ? $box_qty : '';
                        $box_amount = isset($box_amount) ? $box_amount : '';
                        if ($box_qty) {
                            $box_qty = json_decode($box_qty, true);
                            $box_amount = json_decode($box_amount, true);
                            $boxs = [];

                            foreach ($box_qty as $qty_index => $qty) {
                                foreach ($box_amount as $amount_index => $amount) {
                                    if ($qty_index === $amount_index) {
                                        $boxs[$qty_index] = [
                                            'qty' => $qty,
                                            'amount' => $amount,
                                        ];
                                    }
                                }
                            }
                            $count = 0;

                            foreach ($boxs as $box) {
                                ++$count;
                        ?>
                                <div class="grupo-box">
                                    <div class="box dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number"><?= $count ?></span> Box
                                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Titulos:</span>
                                            <input type="text" name="box_amount[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $box['amount'] ?>">
                                        </label>
                                        <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Quantidade:</span>
                                            <input type="text" name="box_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= $box['qty'] ?>">
                                        </label>


                                    </div>
                                    <?php

                                    if (1 < $count) { ?>
                                        <label class="remove_field_box block mt-4 text-sm" style="margin-block:20px;">
                                            <span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover</button></label>
                                    <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            }
                        } else { ?>
                            <div class="grupo-box">
                                <div class="box dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number">1</span> Box
                                    <label class="block mt-4 text-sm">
                                        <span class="text-gray-700 dark:text-gray-400">Titulo:</span>
                                        <input type="text" name="box_amount[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10">
                                    </label>
                                    <label class="block mt-4 text-sm">
                                        <span class="text-gray-700 dark:text-gray-400">Quantidade:</span>
                                        <input type="text" name="box_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10">
                                    </label>

                                </div>
                            </div>
                        <?php
                        }

                        ?>

                    </div>
                </div>
                <div id="tab11" class="tabcontent text-gray-700 dark:text-gray-400 hidden">
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Habilitar Maior/Menor Titulo?</span>
                    </label>
                    <div style="margin-top:4px" class="can-toggle">
                        <input type="checkbox" name="quantidade_auto_cota" id="quantidade_auto_cota"
                            <?php echo isset($quantidade_auto_cota) && $quantidade_auto_cota == 1 ? 'checked' : '' ?>
                            value="<?php echo isset($quantidade_auto_cota) ? $quantidade_auto_cota : '' ?> ">
                        <label for="quantidade_auto_cota">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>
                    </div>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Maior/Menor Titulo Diária?</span>
                    </label>
                    <div style="margin-top:4px" class="can-toggle">
                        <input type="checkbox" name="quantidade_auto_cota_diario" id="quantidade_auto_cota_diario"
                            <?php echo isset($quantidade_auto_cota_diario) && $quantidade_auto_cota_diario == 1 ? 'checked' : '' ?>
                            value="<?php echo isset($quantidade_auto_cota_diario) ? $quantidade_auto_cota_diario : '' ?> ">
                        <label for="quantidade_auto_cota_diario">
                            <div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>
                        </label>
                    </div>
                     <div class="maiormenorcota">
                        <label class="block mt-4 text-sm col-span-2">
                            <span class="text-gray-700 dark:text-gray-400">Data e hora inicial bilhete diária</span>
                            <input style="width:100%" type="datetime-local" name="cota_diaria_ini" id="cota_diaria_ini" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($cota_diaria_ini) ? $cota_diaria_ini : '' ?>">
                        </label>
                        <label class="block mt-4 text-sm col-span-2">
                            <span class="text-gray-700 dark:text-gray-400">Data e hora final bilhete diária</span>
                            <input style="width:100%" type="datetime-local" name="cota_diaria_fim" id="cota_diaria_fim" class="block mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?= isset($cota_diaria_fim) ? $cota_diaria_fim : '' ?>">
                        </label>
                        <label class="block mt-4 text-sm col-span-2">
                            <div class="text-gray-700 dark:text-gray-400">Probabilidade</div>
                            <input type="range" class="win10-thumb" name="probabilidade" value="<?= $probabilidade ?>" id="probabilidadeRange" />
                            <div id="probabilidadeValue"><?= $probabilidade ?></div> <!-- Aqui o valor será mostrado -->
                        </label>

                        <script>
                            // Seleciona o input range e o elemento que irá exibir o valor
                            const rangeInput = document.getElementById('probabilidadeRange');
                            const valueDisplay = document.getElementById('probabilidadeValue');

                            // Função para atualizar o valor mostrado
                            rangeInput.addEventListener('input', function() {
                                valueDisplay.textContent = rangeInput.value;
                            });
                        </script>

                    </div>
                </div>

                <div class="campaign-save-row">
                    <button id="save-product-button" form="product-form" class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
<span id="openModal" href="javascript:void(0)" @click="openModal"></span>
<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center" style="display: none;">
    <!-- Modal -->
    <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0  transform translate-y-1/2" @click.away="closeModal" @keydown.escape="closeModal" class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl" role="dialog" id="modal" style="display: none;">
        <!-- Remove header if you don't want a close icon. Use modal body to place modal tile. -->
        <header class="flex justify-end">
            <button class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700 closeM" aria-label="close" @click="closeModal">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">
                    <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
                </svg>
            </button>
        </header>
        <div class="mt-4 mb-6">
            <p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                Parabéns!
            </p>
            <p class="text-sm text-gray-700 dark:text-gray-400">
                Alterações salvas com sucesso!
            </p>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
<script>
     
    var pageToken = 'manage_product';
    $("#tabs a").click(function() {
        var selectedTab = $(this).attr("href");
        $("#tabs a").removeClass("active-tab");
        $(this).addClass("active-tab");
        $(".tabcontent").hide();
        $(selectedTab).show();
        localStorage.setItem('selectedTab_' + pageToken, pageToken + '_' + selectedTab);
        return false;
    });
    $(document).on('input', '.discount_price', function() {
        $(this).mask("#.##0,00", {
            reverse: true
        });
    });
    $(document).on('input', '.discount_qty', function() {
        $('.discount_qty').keypress(function(event) {
            var charCode = (event.which) ? event.which : event.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
        });
    });
    $(document).ready(function() {
        var campaignTabGuides = {
            tab1: ['Dados principais', 'Defina o nome, o preço, o total de cotas, os limites de compra e como a campanha será exibida.'],
            tab2: ['Imagens', 'Envie a imagem principal, que aparece na vitrine, e imagens extras para a galeria da campanha.'],
            tab3: ['Descontos', 'Crie ofertas por quantidade. Exemplo: ao comprar 100 cotas, o comprador paga o valor promocional configurado.'],
            tab4: ['Ranking', 'Escolha se o Top Compradores aparece na campanha, quantas pessoas serão mostradas e qual período será contabilizado.'],
            tab5: ['Barra de progresso', 'Mostre o avanço real das vendas ou, se desejar, uma porcentagem visual configurada manualmente.'],
            tab6: ['Ganhador', 'Depois do sorteio, informe o telefone e o número sorteado para publicar o resultado na campanha.'],
            tab7: ['Cotas premiadas', 'Cadastre cada número premiado e descreva exatamente o prêmio correspondente.'],
            tab11: ['Maior e menor cota', 'Configure premiações extras ligadas à menor ou à maior cota comprada no período.'],
            tab8: ['Oferta adicional', 'Ofereça cotas extras com desconto depois que o comprador já escolheu a quantidade inicial.']
        };
        Object.keys(campaignTabGuides).forEach(function(tabId) {
            var tab = document.getElementById(tabId);
            if (!tab || tab.querySelector('.campaign-tab-guide')) return;
            var guide = document.createElement('div');
            guide.className = 'campaign-tab-guide';
            var title = document.createElement('strong');
            title.textContent = campaignTabGuides[tabId][0];
            guide.appendChild(title);
            guide.appendChild(document.createTextNode(campaignTabGuides[tabId][1]));
            tab.insertBefore(guide, tab.firstChild);
        });

        var campaignFieldHelp = {
            name: 'Nome que o comprador verá na vitrine e na página da campanha.',
            subtitle: 'Frase curta de apoio exibida junto ao título.',
            description: 'Explique o prêmio, a data do sorteio e as regras específicas desta campanha.',
            type_of_draw: 'Em “Aleatórios”, o sistema escolhe cotas livres. Em “Números”, a campanha trabalha com a numeração definida.',
            date_of_draw: 'Data prevista para o sorteio; ela serve como informação pública.',
            private_draw: 'Se ativado, a campanha deixa de aparecer na vitrine pública e só abre pelo link direto.',
            featured_draw: 'A campanha ganha um cartão maior e mais chamativo na página inicial.',
            qty_numbers: 'Quantidade total de cotas existentes, numeradas de zero até o total menos um.',
            price: 'Preço unitário de uma cota, antes de descontos por quantidade.',
            limit_order_remove: 'Tempo em que uma reserva PIX pendente mantém as cotas separadas.',
            limit_orders: 'Use zero para não limitar a quantidade de pedidos por comprador.',
            min_purchase: 'Menor quantidade permitida em uma única compra.',
            max_purchase: 'Maior quantidade permitida em uma única compra.',
            status_display: 'Texto de situação que aparece no cartão da campanha.',
            status: 'Controla se a campanha aceita novas compras, fica pausada ou é concluída.',
            img: 'Imagem principal da campanha. Prefira uma imagem horizontal e nítida.',
            'image_gallery[]': 'Imagens complementares exibidas ao comprador dentro da campanha.',
            enable_discount: 'Ative para aplicar descontos automáticos conforme a quantidade comprada.',
            enable_ranking: 'Exibe o acesso ao Top Compradores na página pública.',
            ranking_qty: 'Quantidade máxima de compradores mostrados no ranking.',
            ranking_type: '“Diário” considera o período diário; “Total” considera todas as compras confirmadas.',
            enable_ranking_definido: 'Permite restringir o ranking a um início e encerramento específicos.',
            enable_progress_bar: 'Mostra ao comprador o percentual de cotas já vendidas.',
            enable_progress_bar_fake: 'Usa um percentual apenas visual, sem alterar pedidos nem cotas.',
            draw_name: 'Telefone ou identificação do ganhador que será divulgado.',
            draw_number: 'Número ou resultado oficial usado para localizar o ganhador.',
            status_auto_cota: 'Bloqueia as cotas premiadas para impedir a venda enquanto estiver ativado.',
            habilitar_cota_sorte: 'Programa uma cota específica para um intervalo e quantidade de compras.',
            enable_upsell: 'Mostra uma oferta de cotas extras antes da finalização do pedido.',
            qtd_upsell: 'Quantidade de cotas adicionais oferecidas.',
            desconto_upsell: 'Percentual de desconto aplicado somente à oferta adicional.'
        };
        Object.keys(campaignFieldHelp).forEach(function(fieldName) {
            var fields = document.getElementsByName(fieldName);
            Array.prototype.forEach.call(fields, function(field) {
                if (!field) return;
                var reference = field.type === 'checkbox' && field.closest('.can-toggle') ? field.closest('.can-toggle') : field;
                if (reference.nextElementSibling && reference.nextElementSibling.classList.contains('campaign-field-help')) return;
                var help = document.createElement('small');
                help.className = 'campaign-field-help';
                help.textContent = campaignFieldHelp[fieldName];
                reference.insertAdjacentElement('afterend', help);
            });
        });

        if ($('#type_of_draw').val() > 1) {
            if ($('#type_of_draw').val() == 2) {
                $('.qtd-numeros').show();
            } else {
                $('.qtd-numeros').hide();
            }
            $('.qtd-minima').hide();
            $('.qtd-maxima').hide();
            $('.qtd-select').hide();
        } else {
            $('.qtd-numeros').show();
            $('.qtd-minima').show();
            $('.qtd-maxima').show();
            $('.qtd-select').show();
        }
        $('#qty_numbers, #min_purchase, #max_purchase, .discount_qty, #sale_qty, #ranking_qty, #draw_number').keypress(function(event) {
            var charCode = (event.which) ? event.which : event.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
        });
        function updateCampaignQuantityNote() {
            var total = parseInt($('#qty_numbers').val(), 10);
            if (!Number.isInteger(total) || total < 10) {
                $('#campaign-quantity-note').text('Defina manualmente entre 10 e 10.000.000 de cotas.');
                return;
            }
            var lastNumber = total - 1;
            var width = String(lastNumber).length;
            $('#campaign-quantity-note').text(
                'Serão criadas ' + total.toLocaleString('pt-BR') + ' cotas, numeradas de ' +
                String(0).padStart(width, '0') + ' a ' + String(lastNumber).padStart(width, '0') + '.'
            );
        }
        $('#qty_numbers').on('input change', updateCampaignQuantityNote);
        updateCampaignQuantityNote();
         jQuery("#price, #enable_progress_bar_fake_value, .discount_price, #sale_price, #desconto_upsell").mask("#.##0,00", {
            reverse: true
        });
        $('.view-email').each(function() {
            var originalText = $(this).text();
            $(this).data('original-text', originalText);
            $(this).text('**********');
        });
        $('.view-phone').each(function() {
            var originalText = $(this).text();
            $(this).data('original-text', originalText);
            $(this).text('**********');
        });
        $('#view-email').click(function() {
            $('.view-email').each(function() {
                var originalText = $(this).data('original-text');
                if ($(this).text() === '**********') {
                    $(this).text(originalText);
                } else {
                    $(this).text('**********');
                }
            });
        });
        $('#view-phone').click(function() {
            $('.view-phone').each(function() {
                var originalText = $(this).data('original-text');
                if ($(this).text() === '**********') {
                    $(this).text(originalText);
                } else {
                    $(this).text('**********');
                }
            });
        });
        var storedTab = localStorage.getItem('selectedTab_' + pageToken);
        if (storedTab) {
            var selectedTab = storedTab.substring(pageToken.length + 1);
            $("#tabs a").removeClass("active-tab");
            $(selectedTab).addClass("active-tab");
            $(".tabcontent").hide();
            $(selectedTab).show();
        }
        //End tabs
        // Descontos
        var max_fields = 4; // Maximum allowed input pairs
        var max_fields_roleta = 10; // Maximum allowed input pairs
        var max_fields_box = 10; // Maximum allowed input pairs
        var wrapper = $("#descontos"); // Container for input pairs
        var wrapper_roleta = $("#roletas"); // Container for input pairs
        var wrapper_box = $("#boxs"); // Container for input pairs
        var add_button = $(".add_field"); // Add button ID
        var add_button_roleta = $(".add_field_roleta"); // Add button ID
        var add_button_box = $(".add_field_box"); // Add button ID
        var discounts = $('.grupo-desconto').length;
        var roletas = $('.grupo-roleta').length;
        var boxs = $('.grupo-box').length;
        if (discounts > 3) {
            $(".add_field").hide();
        }
        var x = discounts; // Initial counter for input pairs
        var y = roletas; // Initial counter for input pairs
        var w = boxs; // Initial counter for input pairs
        // Add input pairs on click
        $(add_button).click(function(e) {
            e.preventDefault();
            if (x < max_fields) {
                x++;
                $(wrapper).append('<div class="grupo-desconto"><div class="desconto dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number">' + x + '</span> Desconto <label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">:</span> <input type="text" name="discount_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="' + x + '0"> </label> <label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Valor do desconto:</span> <input type="text" name="discount_amount[]" class="discount_price block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="' + x + '.00"> </label><label class="remove_field block mt-4 text-sm"><span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover desconto</span></label><br></div></div>');
            }
            if (x == max_fields) {
                $('.add_field').hide();
            }
        });
        $(add_button_roleta).click(function(e) {
            e.preventDefault();
            if (y < max_fields_roleta) {
                y++;
                $(wrapper_roleta).append('<div class="grupo-roleta"><div class="roleta dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number">' + y + '</span> Roletas <label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Titulos:</span> <input type="text" name="roleta_amount[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="' + y + '0"> </label><label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Quantidade:</span> <input type="text" name="roleta_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="' + y + '0"> </label> <label class="remove_field_roleta block mt-4 text-sm"><span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover</span></label><br></div></div>');
            }
            if (y == max_fields_roleta) {
                $('.add_field_roleta').hide();
            }
        });
        $(add_button_box).click(function(e) {
            e.preventDefault();
            if (y < max_fields_box) {
                w++;
                $(wrapper_box).append('<div class="grupo-roleta"><div class="roleta dark:border-gray-600 text-gray-700 dark:text-gray-400"><span class="discount-number">' + w + '</span> Boxs <label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Titulo:</span> <input type="text" name="box_amount[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="' + w + '0"> </label> <label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Quantidade:</span> <input type="text" name="box_qty[]" class="discount_qty block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="' + w + '0"> </label><label class="remove_field_box block mt-4 text-sm"><span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover</span></label><br></div></div>');
            }
            if (w == max_fields_box) {
                $('.add_field_box').hide();
            }
        });
        // Remove input pair on click
        $(wrapper).on("click", ".remove_field", function(e) {
            $('.add_field').show();
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        });
        $(wrapper_roleta).on("click", ".remove_field_roleta", function(e) {
            $('.add_field_roleta').show();
            e.preventDefault();
            $(this).parent('div').remove();
            y--;
        });
        $(wrapper_box).on("click", ".remove_field_box", function(e) {
            $('.add_field_box').show();
            e.preventDefault();
            $(this).parent('div').remove();
            w--;
        });
        if ($('#enable_discount').is(":checked")) {
            $('.descontos, .add_field').show();
            $('.enable_cumulative_discount').show();
        } else {
            $('.descontos, .add_field').hide();
            $('.enable_cumulative_discount').hide();
        }
        $('#enable_discount').change(function() {
            if ($('#enable_discount').is(":checked")) {
                $('.descontos, .add_field').show();
                $('.enable_cumulative_discount').show();
            } else {
                $('.descontos, .add_field').hide();
                $('.enable_cumulative_discount').hide();
            }
        });
        // Fim descontos
        //Ganhadores
        var max_fields_ = 5; // Maximum allowed input pairs
        var wrapper_ = $("#ganhadores"); // Container for input pairs
        var add_button_ = $(".add_field_"); // Add button ID
        var winners = $('.grupo-ganhador').length;
        if (winners > 3) {
            $(".add_field_").hide();
        }
        var x = winners; // Initial counter for input pairs
        // Add input pairs on click
        $(add_button_).click(function(e) {
            e.preventDefault();
            if (x < max_fields_) {
                x++;
                (wrapper_).append('<div class="grupo-ganhador"><div class="ganhador dark:border-gray-600 text-gray-700 dark:text-gray-400"> <label class="block mt-4 text-sm"><span class="text-gray-700 dark:text-gray-400">Telefone ganhador - ' + x + 'º prêmio:</span><input type="text" name="draw_name[]" class="draw_name block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Telefone do ganhador"></label> <label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Número/grupo sorteado - ' + x + 'º prêmio:</span> <input type="text" name="draw_number[]"class="draw_number block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Número ou grupo sorteado"> </label><label class="remove_field_ block mt-4 text-sm"><span class="bg-red-500 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Remover ganhador</span></label><br></div></div>');
            }
            if (x == max_fields_) {
                $('.add_field_').hide();
            }
        });
        // Remove input pair on click
        $(wrapper_).on("click", ".remove_field_", function(e) {
            $('.add_field_').show();
            e.preventDefault();
            $(this).parent('div').remove();
            x--;
        });
        //Ganhadores
        if ($('#enable_ranking').is(":checked")) {
            $('.ranking_qty').show();
        } else {
            $('.ranking_qty').hide();
        }
        if ($('#enable_ranking_definido').is(":checked")) {
            $('.ranking_definido').show();
        } else {
            $('.ranking_definido').hide();
        }
        $('#enable_ranking_definido').change(function() {
            if ($('#enable_ranking_definido').is(":checked")) {
                $('.ranking_definido').show();
            } else {
                $('.ranking_definido').hide();
            }
        });
        $('#enable_ranking').change(function() {
            if ($('#enable_ranking').is(":checked")) {
                $('.ranking_qty').show();
            } else {
                $('.ranking_qty').hide();
            }
        });
        if ($('#enable_sale').is(":checked")) {
            $('.sale_qty').show();
        } else {
            $('.sale_qty').hide();
        }
        $('#enable_sale').change(function() {
            if ($('#enable_sale').is(":checked")) {
                $('.sale_qty').show();
            } else {
                $('.sale_qty').hide();

            }
        });
        // Fim ranking

        $('#type_of_draw').change(function() {
            if ($('#type_of_draw').val() > 1) {
                if ($('#type_of_draw').val() == 2) {
                    $('.qtd-numeros').show();
                } else {
                    $('.qtd-numeros').hide();
                }
                $('.qtd-minima').hide();
                $('.qtd-maxima').hide();
                $('.qtd-select').hide();
            } else {
                $('.qtd-numeros').show();
                $('.qtd-minima').show();
                $('.qtd-maxima').show();
                $('.qtd-select').show();
            }
        });
        if ($('#private_draw').is(":checked")) {
            $('#featured_draw').prop('checked', false);
        }
        $('#private_draw').change(function() {
            if ($('#private_draw').is(":checked")) {
                $('#featured_draw').prop('checked', false);
            }
        });
        if ($('#featured_draw').is(":checked")) {
            $('#private_draw').prop('checked', false);
        }
        $('#featured_draw').change(function() {
            if ($('#featured_draw').is(":checked")) {
                $('#private_draw').prop('checked', false);
            }
        });

        //Imagem e galeria
        $('.show_logo').hide();
        <?php
        if (!empty($image_path)) { ?>
            $('.show_logo').show();
            $('.add-logo').hide();
        <?php
        }
        ?>
        customFile1.onchange = evt => {
            const [file] = customFile1.files
            if (file) {
                loadlogo.src = URL.createObjectURL(file);
                $('.show_logo').show();
                $('.add-logo').hide();
            }
        }
        $(".remove-logo").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            $('.show_logo').hide();
            $('.add-logo').show();
            $('#customFile1').val('');
        });
        $(".remove").click(function(e) {
            $(this).parent(".pip").remove();
            $(".add_image").show();
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        });
        if ($(".pip").length > 5) {
            $(".add_image").hide();
        }

        if (window.File && window.FileList && window.FileReader) {
            $("#image_gallery").on("change", function(e) {
                let files = e.target.files;
                filesLength = files.length;
                var maxImages = 6;
                var pipLength = $(".pip").length;
                var remainingImages = maxImages - pipLength;
                var maxFiles = Math.min(remainingImages, filesLength);
                var filesInput = document.getElementById("image_gallery");
                var filesList = filesInput.files;
                var newFilesList = new DataTransfer();
                for (var i = 0; i < maxFiles; i++) {
                    newFilesList.items.add(filesList[i]);
                }
                filesInput.files = newFilesList.files;
                /*
                if (totalFiles > maxFiles) {
                alert('Você pode enviar apenas ' + maxFiles + ' imagens.');
                $(this).val('');
                } */
                for (var i = 0; i < maxFiles; i++) {
                    var f = files[i]
                    var fileReader = new FileReader();
                    fileReader.onload = (function(e) {
                        var file = e.target;
                        $('.drope-files').append("<span class=\"pip\">" +
                            "<img class=\"imageThumb\" src=\"" + e.target.result + "\" title=\"" + file.name + "\"/>" +
                            "<br/><span class=\"remove\"><svg width='25' height='25' viewBox='0 0 25 25' xmlns='http://www.w3.org/2000/svg' class='s'><g transform='translate(.317)' fill='none' fill-rule='evenodd'><rect fill='#323232' opacity='.99' width='24.503' height='24.33' rx='12.165'></rect><path d='M12.266 11.134L7.992 6.86c-.301-.3-.783-.3-1.054 0-.3.299-.3.777 0 1.046l4.275 4.274-4.305 4.244c-.3.3-.3.778 0 1.047.301.298.783.298 1.054 0l4.304-4.245 4.275 4.245c.3.298.782.298 1.053 0 .271-.3.301-.778 0-1.047L13.32 12.18l4.274-4.244c.301-.3.301-.777 0-1.046-.27-.3-.752-.3-1.053-.03l-4.275 4.274z' fill='#FFF'></path></g></svg></span>" +
                            "</span>");
                        if ($(".pip").length == 6) {
                            $(".add_image").hide();
                            //$('#image_gallery').prop('disabled', true);
                        }
                        $(".remove").click(function(e) {
                            $(this).parent(".pip").remove();
                            $(".add_image").show();
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                        });
                    });
                    fileReader.readAsDataURL(f);
                }
            });
        } else {
            alert("Your browser doesn't support to File API")
        }

        //Fim imagem e galeria
        //Save products
        function showCampaignFeedback(type, message) {
            var feedback = $('#campaign-save-feedback');
            feedback.stop(true, true).removeClass('success error info').addClass(type).text(message).fadeIn(150);
            if (type !== 'info') {
                setTimeout(function() { feedback.fadeOut(250); }, 6500);
            }
        }

        var persistedFeedback = sessionStorage.getItem('campaignSaveFeedback');
        if (persistedFeedback) {
            sessionStorage.removeItem('campaignSaveFeedback');
            showCampaignFeedback('success', persistedFeedback);
        }

        function decodeCampaignImage(file) {
            return new Promise(function(resolve, reject) {
                var url = URL.createObjectURL(file);
                var image = new Image();
                image.onload = function() {
                    resolve({
                        source: image,
                        width: image.naturalWidth,
                        height: image.naturalHeight,
                        cleanup: function() { URL.revokeObjectURL(url); }
                    });
                };
                image.onerror = function() {
                    URL.revokeObjectURL(url);
                    reject(new Error('A imagem "' + file.name + '" está corrompida ou usa um formato incompatível.'));
                };
                image.src = url;
            });
        }

        function canvasToJpeg(canvas, quality) {
            return new Promise(function(resolve, reject) {
                canvas.toBlob(function(blob) {
                    if (blob) resolve(blob);
                    else reject(new Error('Não foi possível otimizar uma das imagens.'));
                }, 'image/jpeg', quality);
            });
        }

        async function compressCampaignImage(file) {
            var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            var extensionIsValid = /\.(jpe?g|png|gif|webp)$/i.test(file.name || '');
            if (file.type && validTypes.indexOf(file.type) === -1 && !extensionIsValid) {
                throw new Error('Formato inválido em "' + file.name + '". Use JPG, PNG, GIF ou WebP.');
            }

            var decoded = await decodeCampaignImage(file);
            if (!decoded.width || !decoded.height || decoded.width * decoded.height > 50000000) {
                decoded.cleanup();
                throw new Error('A resolução de "' + file.name + '" é muito alta.');
            }

            var maxDimension = 1200;
            var ratio = Math.min(1, maxDimension / Math.max(decoded.width, decoded.height));
            var width = Math.max(1, Math.round(decoded.width * ratio));
            var height = Math.max(1, Math.round(decoded.height * ratio));
            var canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            var context = canvas.getContext('2d', { alpha: false });
            if (!context) {
                decoded.cleanup();
                throw new Error('Seu navegador não conseguiu preparar a imagem.');
            }
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
            context.drawImage(decoded.source, 0, 0, width, height);
            decoded.cleanup();

            var quality = .84;
            var blob = await canvasToJpeg(canvas, quality);
            while (blob.size > 440 * 1024 && quality > .60) {
                quality -= .08;
                blob = await canvasToJpeg(canvas, quality);
            }

            var safeName = (file.name || 'campanha').replace(/\.[^.]+$/, '').replace(/[^a-z0-9_-]+/gi, '-');
            return new File([blob], (safeName || 'campanha') + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
        }

        async function buildCampaignFormData(form) {
            var data = new FormData(form);
            var mainInput = document.getElementById('customFile1');
            var galleryInput = document.getElementById('image_gallery');
            var files = [];

            if (mainInput && mainInput.files.length) {
                data.delete('img');
                var mainImage = await compressCampaignImage(mainInput.files[0]);
                data.append('img', mainImage, mainImage.name);
                files.push(mainImage);
            }

            if (galleryInput && galleryInput.files.length) {
                data.delete('image_gallery[]');
                for (var index = 0; index < galleryInput.files.length; index++) {
                    var galleryImage = await compressCampaignImage(galleryInput.files[index]);
                    data.append('image_gallery[]', galleryImage, galleryImage.name);
                    files.push(galleryImage);
                }
            }

            var totalSize = files.reduce(function(total, file) { return total + file.size; }, 0);
            if (totalSize > 3.5 * 1024 * 1024) {
                throw new Error('As imagens selecionadas ainda ultrapassam o limite. Envie menos imagens por vez.');
            }
            return data;
        }

        function showCampaignFieldError(fieldId, message, tabId) {
            var target = $('#' + fieldId);
            var tab = tabId || (target.closest('.tabcontent').attr('id')) || 'tab1';
            $('#tabs a').removeClass('active-tab');
            $('#tabs a[href="#' + tab + '"]').addClass('active-tab');
            $('.tabcontent').hide();
            $('#' + tab).show();
            showCampaignFeedback('error', message);
            if (target.length) {
                target.attr('aria-invalid', 'true').focus();
                target.one('input change', function () { $(this).removeAttr('aria-invalid'); });
            }
        }

        function validateCampaignForm() {
            syncWinningEditor();
            var title = $.trim($('#name').val());
            if (!title) {
                showCampaignFieldError('name', 'Informe o título da campanha.', 'tab1');
                return false;
            }
            var totalNumbers = parseInt($('#qty_numbers').val(), 10);
            if (!Number.isInteger(totalNumbers) || totalNumbers < 10 || totalNumbers > 10000000) {
                showCampaignFieldError('qty_numbers', 'Informe uma quantidade total entre 10 e 10.000.000 de cotas.', 'tab1');
                return false;
            }
            var priceValue = String($('#price').val() || '').replace(/\./g, '').replace(',', '.');
            if (!(parseFloat(priceValue) > 0)) {
                showCampaignFieldError('price', 'Informe um valor por cota maior que zero.', 'tab1');
                return false;
            }
            if (!$.trim($('#cotas_premiadas').val())) {
                showCampaignFieldError('cotas_premiadas', 'Adicione pelo menos uma cota premiada e informe o prêmio correspondente.', 'tab7');
                return false;
            }
            if (!$.trim($('#cotas_premiadas_premios').val())) {
                showCampaignFieldError('cotas_premiadas_premios', 'Informe o prêmio de cada cota premiada.', 'tab7');
                return false;
            }
            return true;
        }

        $('#product-form').submit(async function(e) {
            e.preventDefault();
            $('.err-msg').remove();
            if (!validateCampaignForm()) {
                return;
            }
            var form = this;
            var button = $('#save-product-button');
            button.prop('disabled', true).text('Salvando...');
            showCampaignFeedback('info', 'Otimizando imagens e salvando a campanha...');

            try {
                var formData = await buildCampaignFormData(form);
                $.ajax({
                    url: _base_url_ + "class/Main.php?action=save_product_sys",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    method: 'POST',
                    type: 'POST',
                    dataType: 'json',
                    timeout: 60000,
                    error: function(err) {
                        var message = 'Não foi possível salvar a campanha. Tente novamente.';
                        var parsedResponse = null;
                        if (err.responseText) {
                            try {
                                parsedResponse = JSON.parse(err.responseText);
                            } catch (parseError) {
                                var jsonStart = err.responseText.lastIndexOf('{');
                                if (jsonStart >= 0) {
                                    try { parsedResponse = JSON.parse(err.responseText.slice(jsonStart)); } catch (ignored) {}
                                }
                            }
                        }
                        if (err.status === 413) {
                            message = 'As imagens ultrapassaram o limite de envio. Elas serão reduzidas automaticamente; tente salvar novamente.';
                        } else if (err.status === 403) {
                            message = 'Sua sessão administrativa expirou. Entre novamente no painel.';
                        } else if (parsedResponse && parsedResponse.msg) {
                            message = parsedResponse.msg;
                        } else if (err.responseJSON && err.responseJSON.msg) {
                            message = err.responseJSON.msg;
                        } else if (err.status === 0) {
                            message = 'A conexão foi interrompida durante o salvamento. Verifique a internet e tente novamente.';
                        } else if (err.status === 500) {
                            message = 'O servidor encontrou um erro interno ao salvar. O detalhe técnico foi registrado no log do servidor.';
                        }
                        console.error('[campaign-save]', { status: err.status, response: err.responseText });
                        showCampaignFeedback('error', message);
                    },
                    success: function(resp) {
                        if (typeof resp === 'object' && resp.status === 'success') {
                            var successMessage = $('input[name="id"]').val()
                                ? 'Campanha atualizada com sucesso!'
                                : 'Campanha criada com sucesso!';
                            sessionStorage.setItem('campaignSaveFeedback', successMessage);
                            showCampaignFeedback('success', successMessage);
                            setTimeout(function() {
                                location.replace('./?page=products/manage_product&id=' + resp.pid);
                            }, 900);
                        } else {
                            var responseMessage = (resp && resp.msg) ? resp.msg : 'O servidor não confirmou o salvamento da campanha.';
                            if (resp && resp.field) {
                                showCampaignFieldError(resp.field, responseMessage, resp.tab);
                            } else {
                                showCampaignFeedback('error', responseMessage);
                            }
                        }
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Salvar');
                    }
                });
            } catch (error) {
                button.prop('disabled', false).text('Salvar');
                showCampaignFeedback('error', error.message || 'Não foi possível preparar as imagens.');
            }
        })
        //End save products
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        function removeSpaces() {
            let val = $('#tipo_auto_cota').val().replace(/\s+/g, ''); // Remove todos os espaços
            $('#tipo_auto_cota').val(val);
            let val_roleta = $('#tipo_auto_cota_roleta').val().replace(/\s+/g, ''); // Remove todos os espaços
            $('#tipo_auto_cota_roleta').val(val_roleta);
            let val_box = $('#tipo_auto_cota_box').val().replace(/\s+/g, ''); // Remove todos os espaços
            $('#tipo_auto_cota_box').val(val_box);
        }

        // Remover espaços quando o valor do input for alterado
        $('#tipo_auto_cota').on('input change', removeSpaces);
        $('#tipo_auto_cota_roleta').on('input change', removeSpaces);
        $('#tipo_auto_cota_box').on('input change', removeSpaces);

        // Remover espaços no carregamento da página
        removeSpaces();



        $('#quantidade_auto_cota').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });

        $('#quantidade_auto_cota_roleta').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });
        $('#quantidade_auto_cota_box').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });

        var status_auto_cota = document.getElementById('status_auto_cota');
        if (status_auto_cota.checked) {
            $('.status_cotas').show();
        } else {
            $('.status_cotas').hide();
        }
        var habilitar_cota_sorte = document.getElementById('habilitar_cota_sorte');
        if (habilitar_cota_sorte.checked) {
            $('.periodocotasorte').show();
        } else {
            $('.periodocotasorte').hide();
        }
        var status_auto_cota_roleta = document.getElementById('status_auto_cota_roleta');
        if (status_auto_cota_roleta.checked) {
            $('.status_cotas_roleta').show();
        } else {
            $('.status_cotas_roleta').hide();
        }
        var status_auto_cota_box = document.getElementById('status_auto_cota_box');
        if (status_auto_cota_box.checked) {
            $('.status_cotas_box').show();
        } else {
            $('.status_cotas_box').hide();
        }

        document.getElementById('status_auto_cota').addEventListener('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
                $('.status_cotas').show();
            } else {
                $(this).val(0);
                $('.status_cotas').hide();
            }
            console.log($(this).val());
        });
        document.getElementById('habilitar_cota_sorte').addEventListener('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
                $('.periodocotasorte').show();
            } else {
                $(this).val(0);
                $('.periodocotasorte').hide();
            }

            console.log($(this).val());
        });
        document.getElementById('status_auto_cota_roleta').addEventListener('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
                $('.status_cotas_roleta').show();
            } else {
                $(this).val(0);
                $('.status_cotas_roleta').hide();

            }

            console.log($(this).val());
        });
        document.getElementById('status_auto_cota_box').addEventListener('change', function() {
            if ($(this).is(':checked')) {
                $(this).val(1);
                $('.status_cotas_box').show();
            } else {
                $(this).val(0);
                $('.status_cotas_box').hide();

            }

            console.log($(this).val());
        });

        document.getElementById('cotas_premiadas').addEventListener('change', function(e) {
            var new_value = $(this).val();
            $('#tipo_auto_cota').val(new_value);
            console.log(new_value);
        });
        document.getElementById('cotas_premiadas_roleta').addEventListener('change', function(e) {
            var new_value = $(this).val();
            $('#tipo_auto_cota_roleta').val(new_value);
            console.log(new_value);
        });
        document.getElementById('cotas_premiadas_box').addEventListener('change', function(e) {
            var new_value = $(this).val();
            $('#tipo_auto_cota_box').val(new_value);
            console.log(new_value);
        });



        function updateHiddenInput() {
            var tags = [];
            var premios = [];
            $('#tags-container .tag').each(function() {

                var tagText = $(this).text().slice(0, -1);

                tagText = tagText.trim();
                tags.push(tagText);
                premios.push(tagText + ':' + $(this).attr('data-premio') + ':' + $(this).attr('data-tipo'))
            });
            $('#cotas_premiadas').val(tags.join(','));
            $('#tipo_auto_cota').val(tags.join(','));
            $('#cotas_premiadas_premios').val(premios.join(','));
            console.log($('#cotas_premiadas').val());
            console.log($('#cotas_premiadas_premios').val());
        }

        function syncWinningEditor() {
            var numbers = [];
            var prizes = [];
            var seen = {};
            $('#winning-ticket-rows .winning-ticket-row').each(function() {
                var number = $.trim($(this).find('.winning-number-input').val());
                var prize = $.trim($(this).find('.winning-prize-input').val()).replace(/[,:]+/g, ' - ');
                if (number === '' || seen[number]) return;
                seen[number] = true;
                numbers.push(number);
                prizes.push(number + ':' + prize + ':premiada');
            });
            $('#cotas_premiadas').val(numbers.join(',')).trigger('change');
            $('#cotas_premiadas_premios').val(prizes.join(','));
            $('#tipo_auto_cota').val(numbers.join(','));
            $('#winning-ticket-count').text(numbers.length + (numbers.length === 1 ? ' cota configurada' : ' cotas configuradas'));
        }

        function addWinningTicketRow(number, prize) {
            if ($('#winning-ticket-rows .winning-ticket-row').length >= 30) {
                showCampaignFeedback('error', 'O limite é de 30 cotas premiadas por campanha.');
                return;
            }
            var row = $('<div class="winning-ticket-row">');
            row.append($('<input type="number" min="0" step="1" class="winning-number-input block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input" aria-label="Número da cota premiada">').val(number || ''));
            row.append($('<input type="text" class="winning-prize-input block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input" aria-label="Prêmio desta cota" placeholder="Ex.: PIX de R$ 100">').val(prize || ''));
            row.append('<button type="button" class="winning-ticket-remove" title="Remover esta cota" aria-label="Remover esta cota">&times;</button>');
            $('#winning-ticket-rows').append(row);
            row.find('.winning-number-input').focus();
            syncWinningEditor();
        }

        $('#add-winning-ticket').on('click', function() { addWinningTicketRow('', ''); });
        $('#winning-ticket-rows').on('input change', 'input', syncWinningEditor);
        $('#winning-ticket-rows').on('click', '.winning-ticket-remove', function() {
            $(this).closest('.winning-ticket-row').remove();
            syncWinningEditor();
        });
        syncWinningEditor();

        function updateHiddenInput_roleta() {
            var tags = [];
            var premios = [];
            $('#tags-container_roleta .tag').each(function() {

                var tagText = $(this).text().slice(0, -1);

                tagText = tagText.trim();
                tags.push(tagText);
                premios.push(tagText + ':' + $(this).attr('data-premio') + ':' + $(this).attr('data-tipo'))
            });
            $('#cotas_premiadas_roleta').val(tags.join(','));
            $('#tipo_auto_cota_roleta').val(tags.join(','));
            $('#cotas_premiadas_premios_roleta').val(premios.join(','));
            console.log($('#cotas_premiadas_roleta').val());
            console.log($('#cotas_premiadas_premios_roleta').val());
        }

        function updateHiddenInput_box() {
            var tags = [];
            var premios = [];
            $('#tags-container_box .tag').each(function() {

                var tagText = $(this).text().slice(0, -1);

                tagText = tagText.trim();
                tags.push(tagText);
                premios.push(tagText + ':' + $(this).attr('data-premio') + ':' + $(this).attr('data-tipo'))
            });
            $('#cotas_premiadas_box').val(tags.join(','));
            $('#tipo_auto_cota_box').val(tags.join(','));
            $('#cotas_premiadas_premios_box').val(premios.join(','));
            console.log($('#cotas_premiadas_box').val());
            console.log($('#cotas_premiadas_premios_box').val());
        }

        $('#tags-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key pressed
                e.preventDefault();
                var items = $('#tags-container .tag').map(function() {
                    // Para cada item, extrai o texto e faz o slice
                    return $(this).text().slice(0, -1);
                }).get(); // O .get() retorna o array com todos os resultados

                // Agora, podemos contar quantos itens temos
                var count = items.length;

                if (count >= 30) {
                    alert('Você atingiu o limite de titulos premiados, o máximo é 30')
                    return false;
                }
                var tagText = $(this).val().trim();
                var tipoText = 'premiada';
                var premioText = prompt('Digite o prêmio para o titulo "' + tagText + '":');
                if (premioText === null || premioText === '') {
                    return
                }
                var isDuplicate = false;

                // Check for duplicate tags
                $('#tags-container .tag').each(function() {
                    if ($(this).text().slice(0, -1) === tagText) {
                        isDuplicate = true;
                        return false; // Exit loop if duplicate is found
                    }
                });

                if (tagText !== '' && !isDuplicate) {
                    $('#tags-container').append('<span id="' + tagText.trim() + '" class="tag ' + tipoText.trim() + '" data-tipo="' + tipoText.trim() + '" data-premio="' +
                        premioText + '">' + tagText.trim() + '<span class="remove-tag">x</span></span>');
                    $('#premios-container').append('<span id="p' + tagText.trim() + '" class="tag ' + tipoText.trim() + '">' +
                        tagText.trim() + ':' + premioText.trim() + '<span class="remove-premio">x</span></span>');

                    $('#tipo-container').append('<span id="t' + tagText + '" class="tag">' + tagText + '<span class="remove-tipo">x</span></span>');
                    $(this).val('');
                    updateHiddenInput();
                } else if (isDuplicate) {
                    alert('Tag duplicada não pode ser adicionada.');
                }
            }
        });
        $('#tags-input_roleta').on('keypress', function(e) {
            if (e.which === 13) { // Enter key pressed
                e.preventDefault();
                var items = $('#tags-container_roleta .tag').map(function() {
                    // Para cada item, extrai o texto e faz o slice
                    return $(this).text().slice(0, -1);
                }).get(); // O .get() retorna o array com todos os resultados

                // Agora, podemos contar quantos itens temos
                var count = items.length;

                if (count >= 30) {
                    alert('Você atingiu o limite de titulos premiados, o máximo é 30')
                    return false;
                }
                var tagText = $(this).val().trim();
                var tipoText = 'premiada';
                var premioText = prompt('Digite o prêmio para o titulo "' + tagText + '":');
                if (premioText === null || premioText === '') {
                    return
                }
                var isDuplicate = false;

                // Check for duplicate tags
                $('#tags-container_roleta .tag').each(function() {
                    if ($(this).text().slice(0, -1) === tagText) {
                        isDuplicate = true;
                        return false; // Exit loop if duplicate is found
                    }
                });

                if (tagText !== '' && !isDuplicate) {
                    $('#tags-container_roleta').append('<span id="' + tagText.trim() + '" class="tag ' + tipoText.trim() + '" data-tipo="' + tipoText.trim() + '" data-premio="' +
                        premioText + '">' + tagText.trim() + '<span class="remove-tag_roleta">x</span></span>');
                    $('#premios-container_roleta').append('<span id="p' + tagText.trim() + '" class="tag ' + tipoText.trim() + '">' +
                        tagText.trim() + ':' + premioText.trim() + '<span class="remove-premio_roleta">x</span></span>');

                    $('#tipo-container_roleta').append('<span id="t' + tagText + '" class="tag">' + tagText + '<span class="remove-tipo_roleta">x</span></span>');
                    $(this).val('');
                    updateHiddenInput_roleta();
                } else if (isDuplicate) {
                    alert('Tag duplicada não pode ser adicionada.');
                }
            }
        });
        $('#tags-input_box').on('keypress', function(e) {
            if (e.which === 13) { // Enter key pressed
                e.preventDefault();
                var items = $('#tags-container_box .tag').map(function() {
                    // Para cada item, extrai o texto e faz o slice
                    return $(this).text().slice(0, -1);
                }).get(); // O .get() retorna o array com todos os resultados

                // Agora, podemos contar quantos itens temos
                var count = items.length;

                if (count >= 30) {
                    alert('Você atingiu o limite de titulos premiados, o máximo é 30')
                    return false;
                }
                var tagText = $(this).val().trim();
                var tipoText = 'premiada';
                var premioText = prompt('Digite o prêmio para o titulo "' + tagText + '":');
                if (premioText === null || premioText === '') {
                    return
                }
                var isDuplicate = false;

                // Check for duplicate tags
                $('#tags-container_box .tag').each(function() {
                    if ($(this).text().slice(0, -1) === tagText) {
                        isDuplicate = true;
                        return false; // Exit loop if duplicate is found
                    }
                });

                if (tagText !== '' && !isDuplicate) {
                    $('#tags-container_box').append('<span id="' + tagText.trim() + '" class="tag ' + tipoText.trim() + '" data-tipo="' + tipoText.trim() + '" data-premio="' +
                        premioText + '">' + tagText.trim() + '<span class="remove-tag_box">x</span></span>');
                    $('#premios-container_box').append('<span id="p' + tagText.trim() + '" class="tag ' + tipoText.trim() + '">' +
                        tagText.trim() + ':' + premioText.trim() + '<span class="remove-premio_box">x</span></span>');

                    $('#tipo-container_box').append('<span id="t' + tagText + '" class="tag">' + tagText + '<span class="remove-tipo_box">x</span></span>');
                    $(this).val('');
                    updateHiddenInput_box();
                } else if (isDuplicate) {
                    alert('Tag duplicada não pode ser adicionada.');
                }
            }
        });

        $(document).on('click', '.remove-tag, .remove-premio', function() {
            var tagText = $(this).parent().text().slice(0, -1).trim();
            if (tagText.includes(':')) {
                tagText = tagText.split(':')[0].trim()
            }
            $('#' + tagText).remove(); // Remove corresponding tag from tags-container
            $(this).parent().remove();

            $('#p' + tagText).remove(); // Remove corresponding tag from tags-container

            // Remove corresponding values from hidden inputs
            var tags = [];
            var premios = [];
            $('#tags-container .tag').each(function() {
                var tagText = $(this).text().slice(0, -1).trim()
                tags.push(tagText);

                premios.push(tagText + ':' + $(this).attr('data-premio').trim() + ':' + $(this).attr('data-tipo').trim())
            })
            $('#cotas_premiadas').val(tags.join(','));
            $('#tipo_auto_cota').val(tags.join(','));
            $('#cotas_premiadas_premios').val(premios.join(','));
            console.log($('#cotas_premiadas').val());
            console.log($('#cotas_premiadas_premios').val());


        })
        $(document).on('click', '.remove-tag_roleta, .remove-premio_roleta', function() {
            var tagText = $(this).parent().text().slice(0, -1).trim();
            if (tagText.includes(':')) {
                tagText = tagText.split(':')[0].trim()
            }
            $('#' + tagText).remove(); // Remove corresponding tag from tags-container
            $(this).parent().remove();

            $('#p' + tagText).remove(); // Remove corresponding tag from tags-container

            // Remove corresponding values from hidden inputs
            var tags = [];
            var premios = [];
            $('#tags-container_roleta .tag').each(function() {
                var tagText = $(this).text().slice(0, -1).trim()
                tags.push(tagText);

                premios.push(tagText + ':' + $(this).attr('data-premio').trim() + ':' + $(this).attr('data-tipo').trim())
            })
            $('#cotas_premiadas_roleta').val(tags.join(','));
            $('#tipo_auto_cota_roleta').val(tags.join(','));
            $('#cotas_premiadas_premios_roleta').val(premios.join(','));
            console.log($('#cotas_premiadas_roleta').val());
            console.log($('#cotas_premiadas_premios_roleta').val());


        })
        $(document).on('click', '.remove-tag_box, .remove-premio_box', function() {
            var tagText = $(this).parent().text().slice(0, -1).trim();
            if (tagText.includes(':')) {
                tagText = tagText.split(':')[0].trim()
            }
            $('#' + tagText).remove(); // Remove corresponding tag from tags-container
            $(this).parent().remove();

            $('#p' + tagText).remove(); // Remove corresponding tag from tags-container

            // Remove corresponding values from hidden inputs
            var tags = [];
            var premios = [];
            $('#tags-container_box .tag').each(function() {
                var tagText = $(this).text().slice(0, -1).trim()
                tags.push(tagText);

                premios.push(tagText + ':' + $(this).attr('data-premio').trim() + ':' + $(this).attr('data-tipo').trim())
            })
            $('#cotas_premiadas_box').val(tags.join(','));
            $('#tipo_auto_cota_box').val(tags.join(','));
            $('#cotas_premiadas_premios_box').val(premios.join(','));
            console.log($('#cotas_premiadas_box').val());
            console.log($('#cotas_premiadas_premios_box').val());


        })

        $('#tipo-container').on('click', '.remove-tipo', function() {
            var tipos = [];
            $('#tipo-container .tag').each(function() {
                var tipoText = $(this).text().slice(0, -1).trim()
                tipos.push(tipoText);
            });
            var tagText = $(this).parent().text().slice(0, -1).trim()
            $(this).parent().remove();

            $('#t' + tagText).remove(); // Remove corresponding tag from tags-container
            tipos = tipos.filter(function(item) {
                return item !== tagText;
            });

            $('#tipo_auto_cota').val(tipos.join(','));

            if ($('#tipo_auto_cota').val().includes(' :') || $('#tipo_auto_cota').val().includes(': ')) {
                $('#tipo_auto_cota').val($('#tipo_auto_cota').val().replace(' :', ':').replace(': ', ':'));
            }

            // Remove corresponding values from hidden inputs	

        })
        $('#tipo-container_roleta').on('click', '.remove-tipo_roleta', function() {
            var tipos = [];
            $('#tipo-container_roleta .tag').each(function() {
                var tipoText = $(this).text().slice(0, -1).trim()
                tipos.push(tipoText);
            });
            var tagText = $(this).parent().text().slice(0, -1).trim()
            $(this).parent().remove();

            $('#t' + tagText).remove(); // Remove corresponding tag from tags-container
            tipos = tipos.filter(function(item) {
                return item !== tagText;
            });

            $('#tipo_auto_cota_roleta').val(tipos.join(','));

            if ($('#tipo_auto_cota_roleta').val().includes(' :') || $('#tipo_auto_cota_roleta').val().includes(': ')) {
                $('#tipo_auto_cota_roleta').val($('#tipo_auto_cota_roleta').val().replace(' :', ':').replace(': ', ':'));
            }

            // Remove corresponding values from hidden inputs	

        })
        $('#tipo-container_box').on('click', '.remove-tipo_box', function() {
            var tipos = [];
            $('#tipo-container_box .tag').each(function() {
                var tipoText = $(this).text().slice(0, -1).trim()
                tipos.push(tipoText);
            });
            var tagText = $(this).parent().text().slice(0, -1).trim()
            $(this).parent().remove();

            $('#t' + tagText).remove(); // Remove corresponding tag from tags-container
            tipos = tipos.filter(function(item) {
                return item !== tagText;
            });

            $('#tipo_auto_cota_box').val(tipos.join(','));

            if ($('#tipo_auto_cota_box').val().includes(' :') || $('#tipo_auto_cota_box').val().includes(': ')) {
                $('#tipo_auto_cota_box').val($('#tipo_auto_cota_box').val().replace(' :', ':').replace(': ', ':'));
            }

            // Remove corresponding values from hidden inputs	

        })
    })
</script>
