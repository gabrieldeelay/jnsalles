<?php

function strcasecmp_utf8($str1, $str2)
{
    return strcasecmp(mb_strtolower($str1, 'UTF-8'), mb_strtolower($str2, 'UTF-8'));
}

$status = isset($_GET['status']) ? $_GET['status'] : '';
$stat_arr = ['Pending Orders', 'Packed Orders', 'Our for Delivery', 'Completed Order'];
$product_id = isset($_GET['product_id']) && ctype_digit((string) $_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$status_id = isset($_GET['status_id']) && in_array((int) $_GET['status_id'], [1, 2, 3], true) ? (int) $_GET['status_id'] : 0;
$order = trim(isset($_GET['order']) ? $_GET['order'] : '');
$order_number = trim(isset($_GET['order_number']) ? $_GET['order_number'] : '');
$customer_phone = trim(isset($_GET['customer_phone']) ? $_GET['customer_phone'] : '');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-6 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$tod = '';
$name = trim(isset($_GET['name']) ? $_GET['name'] : '');
$payment_methods = [
    'MercadoPago' => 'Mercado Pago',
    'Paggue' => 'Paggue',
    'Gerencianet' => 'Efí / Gerencianet',
    'OpenPix' => 'OpenPix / Woovi',
    'Pay2m' => 'Pay2M',
    'Manual' => 'Manual',
];
$payment_method = isset($_GET['payment_method'], $payment_methods[$_GET['payment_method']]) ? $_GET['payment_method'] : '';
$sort_options = [
    'newest' => 'Mais recentes',
    'value_desc' => 'Maior valor primeiro',
    'value_asc' => 'Menor valor primeiro',
];
$sort_order = isset($_GET['sort_order'], $sort_options[$_GET['sort_order']]) ? $_GET['sort_order'] : 'newest';

foreach (['start_date', 'end_date'] as $dateKey) {
    $dateValue = ${$dateKey};
    $dateObject = DateTime::createFromFormat('Y-m-d', (string) $dateValue);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $dateValue) {
        ${$dateKey} = $dateKey === 'start_date' ? date('Y-m-d', strtotime('-6 days')) : date('Y-m-d');
    }
}

if ($start_date > $end_date) {
    [$start_date, $end_date] = [$end_date, $start_date];
}

if ($product_id) {
    $qry = $conn->query('SELECT type_of_draw FROM `product_list` WHERE id = ' . $product_id);

    if (0 < $qry->num_rows) {
        $row = $qry->fetch_assoc();
        $tod = $row['type_of_draw'];
    }
}
?>


<style>
    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar {
        width: 10px;
    }


    .theme-dark #closeModal {
        fill: #fff;
    }

    #closeModal {
        fill: #000
    }

    .theme-dark #numbersModal {
        background: none 0% 0% / auto repeat scroll padding-box border-box rgb(17, 19, 21);

    }

    .theme-dark #numbersModal #modal-title {
        color: #fff;
    }

    #numbersModal {
        background: #fff;
        color: #000;
    }

    @media (max-width: 600px) {

        #numbersModal {
            width: 100% !important;

        }
    }

    .theme-dark .view_numbers {
        color: #ffffff;
        box-shadow: 0 0 0 2px #ffffff4a inset;

    }

    .view_numbers {
        color: #000000af;
        box-shadow: 0 0 0 2px #0000004a inset;

    }

    .wd-1 {
        width: 35px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-2 {
        width: 36px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-3 {
        width: 42px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-4 {
        width: 48px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-5 {
        width: 60px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-6 {
        width: 66px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-7 {
        width: 72px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-8 {
        width: 78px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-9 {
        width: 84px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-10 {
        width: 90px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .alert-success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        color: var(--bs-modal-color);
        pointer-events: auto;
        background-color: var(--bs-modal-bg);
        background-clip: padding-box;
        border: var(--bs-modal-border-width) solid var(--bs-modal-border-color);
        border-radius: var(--bs-modal-border-radius);
        outline: 0;
    }

    .modal.show .modal-dialog {
        transform: none;
    }

    .modal.fade .modal-dialog {
        transition: transform .3s ease-out;
        transform: translate(0, -50px);
    }

    @media (min-width: 576px) {
        .modal-dialog {
            max-width: var(--bs-modal-width);
            margin-right: auto;
            margin-left: auto;
        }
    }

    .modal-dialog {
        position: relative;
        width: auto;
        margin: var(--bs-modal-margin);
        pointer-events: none;
    }

    @media (min-width: 576px) {
        .modal {
            --bs-modal-margin: 1.75rem;
            --bs-modal-box-shadow: var(--bs-box-shadow);
        }
    }

    .modal {
        --bs-modal-zindex: 1055;
        --bs-modal-width: 500px;
        --bs-modal-padding: 1rem;
        --bs-modal-margin: 0.5rem;
        --bs-modal-color: ;
        --bs-modal-bg: var(--bs-body-bg);
        --bs-modal-border-color: var(--bs-border-color-translucent);
        --bs-modal-border-width: var(--bs-border-width);
        --bs-modal-border-radius: var(--bs-border-radius-lg);
        --bs-modal-box-shadow: var(--bs-box-shadow-sm);
        --bs-modal-inner-border-radius: calc(var(--bs-border-radius-lg) -(var(--bs-border-width)));
        --bs-modal-header-padding-x: 1rem;
        --bs-modal-header-padding-y: 1rem;
        --bs-modal-header-padding: 1rem 1rem;
        --bs-modal-header-border-color: var(--bs-border-color);
        --bs-modal-header-border-width: var(--bs-border-width);
        --bs-modal-title-line-height: 1.5;
        --bs-modal-footer-gap: 0.5rem;
        --bs-modal-footer-bg: ;
        --bs-modal-footer-border-color: var(--bs-border-color);
        --bs-modal-footer-border-width: var(--bs-border-width);
        position: fixed;
        top: 0;
        left: 0;
        z-index: var(--bs-modal-zindex);
        display: none;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
    }

    .fade {
        transition: opacity .15s linear;
    }

    .style-0 {
        padding: 8px 12px;
        white-space: nowrap;
        border-radius: 8px;
        font-size: 13px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 700;
        text-align: center;
        transition: 0.2s;
        font-family: Inter, sans-serif;
        box-sizing: border-box;
        outline: rgba(252, 252, 252, 0.25) none 0px;
        margin: 0px;
        border: 0px none rgba(252, 252, 252, 0.277);
        cursor: pointer;
    }

    .style-1 {
        box-sizing: border-box;
        outline: rgb(252, 252, 252) none 0px;
        margin: 0px;
        padding: 0px;
        border: 0px none rgb(252, 252, 252);
        vertical-align: baseline;
    }

    .style-2 {
        fill: rgb(252, 252, 252);
        margin-left: 8px;
        transition: 0.2s;
        width: 16px;
        height: 15.9961px;
        vertical-align: middle;
        box-sizing: border-box;
    }

    .style-3 {
        box-sizing: border-box;
    }

    .schedule__product {
        max-width: 170px;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .schedule__link {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2307692308;
        color: #9A9FA5;
    }


    .aprovar {
        --tw-bg-opacity: 1;
        background-color: rgb(0 114 124 / 0.5);
        color: #fff;
    }
    
    
    .pago {
        --tw-bg-opacity: 1;
        background-color: rgb(0 171 85 / 0.5);
        color: #fff;
    }

    .pendente {
        --tw-bg-opacity: 1;
        background-color: rgb(220 38 38 / 0.5);
        color: #fff;
    }

    .cancelado {
        --tw-bg-opacity: 1;
        background-color: rgb(251 191 36 / 0.5);
        color: #000;
    }

    .order_numbers {
        white-space: normal;
    }

    tr.text-gray-700.dark\:text-gray-400 {
        vertical-align: text-bottom;
    }

    .exportar-contatos {
        display: inline-block;
        margin-bottom: 10px;
    }

    .filtro-busca {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .5rem;
    }

    .filtro-busca > * {
        margin-right: 0 !important;
    }

    @media all and (max-width:72em) {
        .filtro-busca {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media all and (max-width:40em) {
        .filtro-busca {
            grid-template-columns: 1fr;
        }
    }

    span#approve-payment {
        background: #2271b1;
        padding: 6px;
        display: inline-block;
        margin-top: 6px;
        border-radius: 4px;
        color: #fff;
        cursor: pointer;
    }

    td.px-4.py-3.text-sm {
        max-width: 240px;
        text-wrap: pretty;
    }

    @media only screen and (max-width:600px) {
        .fb-2 {
            margin-top: 10px;
            width: 100%;
        }
    }

    @media only screen and (max-width:600px) {
        .fb-2 {
            margin-top: 10px;
            width: 100%;
        }
    }

    .badge {
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;

        white-space: nowrap;

    }

    .aprovar {
        margin-top: 4px
    }
</style>
<main class="h-full pb-16 overflow-y-auto">
    <div class="container grid px-6 mx-auto">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Pedidos
            <a href="./?page=orders/create_order" id="create_new">
                <button
                    class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Cadastrar novo
                </button>
            </a>
        </h2>
        <form action="?page=orders&" id="filter-form" style="margin-bottom:10px" method="GET">
            <div class="flex filtro-busca">
                <input type="hidden" name="page" value="orders">
                <select name="product_id" id="product_id"
                    class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                    <option value="">Todos as campanhas</option>
                    <?php
                    $qry = $conn->query('SELECT * FROM `product_list`');
                    while ($row = $qry->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '"' . ($product_id == $row['id'] ? ' selected' : '') . '>' . $row['name'] . '</option>';
                    }
                    ?>
                </select>
                <select name="status_id" id="status_id"
                    class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                    <option value="">Todos os status</option>
                    <option value="2" <?= $status_id == '2' ? 'selected' : '' ?>>Pago</option>
                    <option value="1" <?= $status_id == '1' ? 'selected' : '' ?>>Pendente</option>
                    <option value="3" <?= $status_id == '3' ? 'selected' : '' ?>>Cancelado</option>
                </select>
                <select name="payment_method" id="payment_method"
                    class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                    <option value="">Todos os pagamentos</option>
                    <?php foreach ($payment_methods as $method_value => $method_label): ?>
                        <option value="<?= htmlspecialchars($method_value, ENT_QUOTES, 'UTF-8') ?>" <?= $payment_method === $method_value ? 'selected' : '' ?>><?= htmlspecialchars($method_label, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="sort_order" id="sort_order"
                    class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                    <?php foreach ($sort_options as $sort_value => $sort_label): ?>
                        <option value="<?= htmlspecialchars($sort_value, ENT_QUOTES, 'UTF-8') ?>" <?= $sort_order === $sort_value ? 'selected' : '' ?>><?= htmlspecialchars($sort_label, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <input name="name" id="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                    class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    placeholder="Nome">
                <input name="order" id="order" value="<?= htmlspecialchars($order, ENT_QUOTES, 'UTF-8') ?>"
                    class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    placeholder="Pedido">
                <input name="order_number" id="order_number" value="<?= htmlspecialchars($order_number, ENT_QUOTES, 'UTF-8') ?>"
                    class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    placeholder="Cota">
                <input name="customer_phone" id="customer_phone" value="<?= htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8') ?>"
                    class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    placeholder="Telefone">
                <input name="start_date" id="start_date" type="date"
                    value="<?= $start_date ?: date('Y-m-d', strtotime('-7 days')) ?>"
                    class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                <input name="end_date" id="end_date" type="date" value="<?= $end_date ?: date('Y-m-d') ?>"
                    class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                <button id="set-filter"
                    class="fb-2 px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">
                    Filtrar</button>
            </div>
        </form>
        <?php
        
        if ((!empty($product_id) || !empty($status_id) || !empty($order) || !empty($order_number) || !empty($customer_phone)) && $_settings->userdata('type') == '1') {
            echo "\t\t" . '<button class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple exportar-contatos" onclick="export_raffle_contacts();">Exportar Pedidos</button>' . "\r\n\t";
        }
        
        ?>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">

                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Pedido</th>
                            <th class="px-4 py-3">Reserva</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Números</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Pagamento</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Ação</th>


                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php
        $perPage = 20;
        $page = max(1, (int) ($_GET['pg'] ?? 1));
        $offset = $perPage * ($page - 1);
        $i = 1;
        $where = '';

        if ($product_id) {
            $where .= ' AND o.product_id = \'' . $conn->real_escape_string($product_id) . '\'';
        }

        if ($status_id) {
            $where .= ' AND o.status = \'' . $conn->real_escape_string($status_id) . '\'';
        }

        if ($payment_method) {
            $where .= ' AND o.payment_method = \'' . $conn->real_escape_string($payment_method) . '\'';
        }

        if ($start_date || $end_date) {
            $where .= ' AND o.date_created BETWEEN \'' . $conn->real_escape_string($start_date) . ' 00:00:00\' AND \'' . $conn->real_escape_string($end_date) . ' 23:59:59\'';
        }

        if ($order) {
            $where .= ' AND o.id = \'' . $conn->real_escape_string($order) . '\'';
        }

        if ($name) {
            $escaped_name = $conn->real_escape_string($name);
            $where .= " AND (cl.firstname LIKE '%{$escaped_name}%' OR cl.lastname LIKE '%{$escaped_name}%' OR CONCAT(cl.firstname, ' ', cl.lastname) LIKE '%{$escaped_name}%')";
        }

        if ($order_number) {
            $escaped_order_number = $conn->real_escape_string($order_number);
            $where .= " AND FIND_IN_SET('{$escaped_order_number}', o.order_numbers)";
        }

        if ($customer_phone) {
            $where .= " AND cl.phone LIKE '%" . $conn->real_escape_string($customer_phone) . "%'";
        }

        $orderBy = [
            'newest' => 'o.date_created DESC, o.id DESC',
            'value_desc' => 'o.total_amount DESC, o.date_created DESC, o.id DESC',
            'value_asc' => 'o.total_amount ASC, o.date_created DESC, o.id DESC',
        ][$sort_order];

        $countQuery = $conn->query("SELECT COUNT(*) AS total FROM order_list o INNER JOIN customer_list cl ON o.customer_id = cl.id INNER JOIN product_list p ON o.product_id = p.id WHERE o.id > 0 $where");
        $totalResults = (int) ($countQuery->fetch_assoc()['total'] ?? 0);
        $totalPages = max(1, (int) ceil($totalResults / $perPage));
        $page = min($page, $totalPages);
        $offset = $perPage * ($page - 1);
        $qry = $conn->query("SELECT o.*, cl.firstname, cl.lastname, cl.phone, p.name AS product FROM order_list o INNER JOIN customer_list cl ON o.customer_id = cl.id INNER JOIN product_list p ON o.product_id = p.id WHERE o.id > 0 $where ORDER BY $orderBy LIMIT $offset, $perPage");
        $records = $qry->num_rows;

        $ordersPageUrl = static function ($targetPage) use ($product_id, $status_id, $payment_method, $sort_order, $name, $order, $order_number, $customer_phone, $start_date, $end_date) {
            return './?' . http_build_query([
                'page' => 'orders',
                'product_id' => $product_id ?: '',
                'status_id' => $status_id ?: '',
                'payment_method' => $payment_method,
                'sort_order' => $sort_order,
                'name' => $name,
                'order' => $order,
                'order_number' => $order_number,
                'customer_phone' => $customer_phone,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'pg' => $targetPage,
            ]);
        };
        

        while ($row = $qry->fetch_assoc()) {
            $i++;
        ?>
                        <tr class="text-gray-700 dark:text-gray-400">
                            <td class="px-4 py-3 text-sm">
                                <div class="schedule__details">
                                    <div class="schedule__product">
                                        #<?= $row['id'] ?>
                                    </div>
                                    <div class="schedule__link">
                                        <?= $row['product_name'] ?>
                                    </div>


                                </div>

                            </td>


                            <td class="px-4 py-3 text-sm"><?= date('d/m/Y H:i', strtotime($row['date_created'])) ?></td>




                            <td class="px-4 py-3 text-sm"><?= ucwords($row['firstname'] . ' ' . $row['lastname']) ?>
                            </td>






                            <td class="px-4 py-3 text-sm order_numbers">


                                <button class="style-0 view_numbers" type="button" data-bs-toggle="modal"
                                    data-bs-target="#numbersModal" id="" data-id="<?= $row['id'] ?>">
                                    <span class="style-1">👀 Ver números </span>

                                </button>
                            </td>


                            <td class="px-4 py-3 text-sm">R$<?= number_format($row['total_amount'], 2, ',', '.') ?></td>

                            <td class="px-4 py-3 text-sm"><?= htmlspecialchars($payment_methods[$row['payment_method']] ?? ($row['payment_method'] ?: 'Não informado'), ENT_QUOTES, 'UTF-8') ?></td>

                            <td class="px-4 py-3 text-sm">
                                <?php if ($row['status'] == 1) : ?>
                                <div class="flex items-center  flex-col text-sm">
                                    <span class="badge badge-primary bg-red-500 pendente" data-id="<?= $row['id'] ?>">⏳
                                        aguardando</span>
                                    <span class="badge badge-primary bg-red-500 aprovar"
                                      data-id="<?= $row['id'] ?>">🔓 Aprovar Ped </span>
                                </div>
                                <?php elseif ($row['status'] == 2) : ?>
                                <span class="badge badge-success bg-green-500 pago" data-id="<?= $row['id'] ?>">✔
                                    Confirmado</span>
                                <?php elseif ($row['status'] == 3) : ?>
                                <span class="badge badge-danger bg-yellow-500 cancelado" data-id="<?= $row['id'] ?>">❌
                                    Cancelado</span>
                                <?php endif; ?>
                            </td>


                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2 text-sm">
                                    <?php
                                    
                                    $phone = $row['phone'];
                                    $customer = ucwords($row['firstname'] . ' ' . $row['lastname']);
                                    $raffle = $row['product_name'];
                                    $total = number_format($row['total_amount'], 2, ',', '.');
                                    $status = $row['status'];
                                    $siteUrl = BASE_URL;
                                    $code = $row['order_token'];
                                    $siteName = $_settings->info('name');
                                    
                                    if ($status == 1) {
                                        $message = '⚠️ Olá *' . $customer . '*, evite o cancelamento da sua reserva, o próximo ganhador pode ser você. 😉' . "\r\n\r\n" . '    ------------------------------------' . "\r\n" . '    *CAMPANHA:* ' . $raffle . "\r\n" . '    *NÚMERO(S):* GERADO APÓS PAGAMENTO' . "\r\n" . '    *VALOR TOTAL:* R$ ' . $total . "\r\n" . '    *STATUS*: 🟠 PENDENTE' . "\r\n" . '    ------------------------------------' . "\r\n\r\n" . '    *Para pagar agora, clique no link abaixo* ⤵️' . "\r\n" . '    ' . $siteUrl . 'compra/' .($code) . "\r\n\r\n" . '   _Só ganha quem joga!_' . "\r\n\r\n" . '   *' . $siteName . '*' . "\r\n" . ' ';
                                        $text = urlencode($message);
                                    }
                                    
                                    if ($status == 2) {
                                        $message = 'Oii *' . $customer . '*, seu pagamento foi confirmado com sucesso! ✅' . "\r\n\r\n" . '    ------------------------------------' . "\r\n" . '    *CAMPANHA:* ' . $raffle . "\r\n" . '    *VALOR TOTAL:* R$ ' . $total . "\r\n" . '    *STATUS:* 🟢 PAGO' . "\r\n" . '    ------------------------------------' . "\r\n\r\n" . '    _Boa Sorte!_ 🤞🏽🍀' . "\r\n\r\n" . '    *' . $siteName . '*' . "\r\n" . '    ';
                                        $text = urlencode($message);
                                    }
                                    
                                    if ($status == 3) {
                                        $message = '❌ RESERVA CANCELADA' . "\r\n" . '            ' . "\r\n" . '    Olá *' . $customer . '*, sua reserva *#' . $order . '*, da campanha ' . $raffle . ', *foi cancelada pois não foi paga.*.' . "\r\n\r\n" . '    🚨 Os números da reserva foram novamente disponibilizados automaticamente na campanha. ' . "\r\n\r\n" . '    _Atenciosamente,_' . "\r\n" . ' ' . "\r\n" . '    *' . $siteName . '*' . "\r\n" . '    ';
                                        $text = urlencode($message);
                                    }
                                    
                                    $href = "https://api.whatsapp.com/send?phone=55$phone&text=$text";
                                    ?>

                                    <a href="/compra/<?=$row['order_token']?>" target="_blank">
                                        <button
                                            class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                            aria-label="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="green" class="bi bi-qr-code" viewBox="0 0 16 16">
  <path d="M2 2h2v2H2z"/>
  <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
  <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
  <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
  <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
</svg> </button>
                                    </a>
                                    <a href="<?= $href ?>" target="_blank">
                                        <button
                                            class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                            aria-label="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                fill="green" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path
                                                    d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                            </svg> </button>
                                    </a>


                                    <a href="./?page=orders/view_order&id=<?= $row['id'] ?>">
                                        <button
                                            class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                            aria-label="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </button>
                                    </a>





                                    <a class="delete_pedido" href="javascript:void(0)" @click="openModal"
                                        data-id="<?= $row['id'] ?>">
                                        <button
                                            class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                            aria-label="Delete">
                                            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </a>
                                    <a class="imprimir_pedido" target="_blank"
                                        href="/admin/reportCompra.php?id=<?= $row['id'] ?>">
                                        <button
                                            class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                            aria-label="Delete">
                                            <i class="fa-duotone fa-print w-5 h-5" style="font-size:20px"></i>

                                        </button>
                                    </a>

                                </div>
                            </td>

                        </tr>
                        <?php } ?>

                        <?php if ($records == 0) : ?>
                        <tr>
                            <td class="text-center" colspan="12">Nenhum pedido encontrado</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div
                class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
                <span class="flex items-center col-span-3"></span>
                <span class="col-span-2"></span>

                <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
                    <?php if ($totalResults > 0): ?>
                        <nav aria-label="Paginação dos pedidos">
                            <ul class="inline-flex items-center">
                                <?php if ($page > 1): ?>
                                    <li><a class="px-3 py-1 rounded-md" href="<?= htmlspecialchars($ordersPageUrl($page - 1), ENT_QUOTES, 'UTF-8') ?>">Anterior</a></li>
                                <?php endif; ?>
                                <?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): ?>
                                    <li><a class="px-3 py-1 rounded-md <?= $number === $page ? 'text-white bg-purple-600' : '' ?>" href="<?= htmlspecialchars($ordersPageUrl($number), ENT_QUOTES, 'UTF-8') ?>"><?= $number ?></a></li>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages): ?>
                                    <li><a class="px-3 py-1 rounded-md" href="<?= htmlspecialchars($ordersPageUrl($page + 1), ENT_QUOTES, 'UTF-8') ?>">Próxima</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </span>
            </div>

        </div>
    </div>
    </div>
</main>

<!-- Modal Delete -->
<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
    style="display: none;">
    <!-- Modal -->
    <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0  transform translate-y-1/2" @click.away="closeModal"
        @keydown.escape="closeModal"
        class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
        role="dialog" id="modal" style="display: none;">
        <!-- Remove header if you don't want a close icon. Use modal body to place modal tile. -->
        <header class="flex justify-end">
            <button
                class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700"
                aria-label="close" @click="closeModal">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">
                    <path
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" fill-rule="evenodd"></path>
                </svg>
            </button>
        </header>
        <div class="mt-4 mb-6">
            <p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                Deseja excluir?
            </p>
            <p class="text-sm text-gray-700 dark:text-gray-400">
                Você realmente deseja excluir esse pedido?
            </p>
        </div>
        <footer
            class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
            <button @click="closeModal"
                class="w-full px-5 py-3 text-sm font-medium leading-5 text-white text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">
                Não
            </button>
            <button
                class="delete_data w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Sim
            </button>
        </footer>
    </div>
</div>

<!-- End Modal Delete -->


<!-- Button trigger modal -->

<div class="hidden" id="numbersModal"
    style="transform:matrix(1, 0, 0, 1, 0, 0);position:fixed;top:0px;left:0px;bottom:0px;z-index:30;flex-direction:column;width: 340px;padding:24px 12px;overflow: auto;transition:transform 0.3s, -webkit-transform 0.3s;box-sizing:border-box;outline:rgb(252, 252, 252) none 0px;margin:0px;border:0px none rgb(252, 252, 252);vertical-align:baseline;">
    <div
        style="display:flex;align-items:center;margin-bottom:12px;padding:12px;box-sizing:border-box;outline:rgb(252, 252, 252) none 0px;margin:0px 0px 12px;border:0px none rgb(252, 252, 252);vertical-align:baseline;">

        <div id="modal-title"
            style="font-size:16px;line-height:24px;box-sizing:border-box;outline:rgb(252, 252, 252) none 0px;margin:0px;padding:0px;border:0px none rgb(252, 252, 252);vertical-align:baseline;">
            Números do pedido
        </div>

        <button id="closeModal"
            style="margin-left: auto;font-family:Inter, sans-serif;box-sizing:border-box;outline:rgb(255, 255, 255) none 0px;padding:0px;border:0px none rgb(0, 0, 0);cursor:pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="" class="bi bi-x"
                viewBox="0 0 16 16">
                <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
            </svg>
        </button>
    </div>
    <div id="modal-content"
        style="border-color:rgba(111, 118, 126, 0.2);margin-bottom: auto;padding:24px 0px;border-top:1.25px solid rgba(111, 118, 126, 0.2);box-sizing:border-box;outline:rgb(252, 252, 252) none 0px;margin:0px;vertical-align:baseline;">

    </div>

</div>

<script>
    $(document).ready(function() {
        $('.delete_pedido').click(function() {
            var id = $(this).attr('data-id');
            $('.delete_data').attr('data-id', id);
        });
        $('.delete_data').click(function() {
            var id = $(this).attr('data-id');
            delete_order(id);
        });
        $('.send-whatsapp').click(function() {
            var id = $(this).attr('data-post-id');
            update_whatsapp_status(id);
        });
        $('.corrigir_pedido').click(function() {
            var id = $(this).attr('data-id');
            correct_order(id);
        });
        $('.corrigir_array').click(function() {
            var oid = $(this).attr('data-id');
            var pid = $(this).attr('product-id');
            correct_array(oid, pid);
        });
        $('.corrigir_quantity').click(function() {
            var id = $(this).attr('data-id');
            var qtd = $(this).attr('quantity');
            correct_quantity(id, qtd);
        });

        $('.aprovar').each(function() {
            var id = $(this).attr('data-id');
            $(this).click(function() {
                update_order_status(id, 2);
            });
        });
        $('#closeModal').click(function() {
            $('#numbersModal').hide();
        });

        $('.view_numbers').each(function() {
            $(this).click(function() {
                var id = $(this).attr('data-id');
                openModal(id);



            });
        });
    });

    function openModal(id) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=view_numbers",
            method: "POST",
            data: {
                id: id
            },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert("[AO05] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    var number = resp.order_numbers;
                    var numbers = number.split(',');
                    var html = '';
                    for (var i = 0; i < numbers.length; i++) {

                        var length = numbers[i].length;

                        html +=
                            '<span class="badge badge-primary   alert-success  me-1 wd-' +
                            length +
                            '" style="border-radius: 5px !important;display: inline-block;padding: 5px;border-radius: 2px;margin: 4px;">' +
                            numbers[i] + '</span> ';
                    }


                    $('#modal-title').html('Números do pedido #' + id);
                    $('#modal-content').html(html);
                    $('#numbersModal').show()

                } else {
                    alert("[AO06] - An error occured.");
                }
            }
        });
    }

    function delete_order(id) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=delete_order",
            method: "POST",
            data: {
                id: id
            },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert("[AO01] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert("[AO02] - An error occured.");
                }
            }
        });
    }

    function correct_quantity(id, qtd) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=correct_quantity",
            method: "POST",
            data: {
                id: id,
                qtd: qtd
            },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert("[AO15] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert("[AO16] - An error occured.");
                }
            }
        });
    }

    function correct_order(id) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=correct_order",
            method: "POST",
            data: {
                id: id
            },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert("[AO03] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert("[AO04] - An error occured.");
                }
            }
        });
    }

    function correct_array(oid, pid) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=correct_array",
            method: "POST",
            data: {
                oid: oid,
                pid: pid
            },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert("[AO10] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert("[AO11] - An error occured.");
                }
            }
        });
    }

    function update_whatsapp_status(id) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=update_whatsapp_status",
            method: "POST",
            data: {
                id: id
            },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert("[AO03] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert("[AO04] - An error occured.");
                }
            }
        });
    }

    function export_raffle_contacts() {
        var raffle = $('#product_id').val();
        var status = $('#status_id').val();

        // Montar a URL do download
        var downloadURL = _base_url_ + "class/Main.php?action=export_raffle_contacts2&raffle=" + raffle + "&status=" +
            status;

        // Redirecionar o navegador para a URL de download
        window.location.href = downloadURL;
    }

    function update_order_status($id, $status) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=update_order_status_sys",
            method: "POST",
            data: {
                id: $id,
                status: $status
            },
            dataType: "json",
            error: err => {
                console.log(err)
                alert("[AO13] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    alert('Status atualizado com sucesso!');
                    location.reload();
                } else {
                    alert("[AO14] - An error occured.");
                }
            }
        })
    }
</script>
