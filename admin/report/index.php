<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$validDate = static function ($value, $fallback) {
    $date = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
};

$paymentMethods = [
    'MercadoPago' => 'Mercado Pago',
    'Paggue' => 'Paggue',
    'Gerencianet' => 'Efí / Gerencianet',
    'OpenPix' => 'OpenPix / Woovi',
    'Pay2m' => 'Pay2M',
    'Manual' => 'Manual',
];
$statusLabels = [1 => 'Pendente', 2 => 'Pago', 3 => 'Cancelado'];

$product_id = isset($_GET['product_id']) && ctype_digit((string) $_GET['product_id'])
    ? (int) $_GET['product_id']
    : 0;
$status_id = isset($_GET['status_id']) && in_array((int) $_GET['status_id'], [1, 2, 3], true)
    ? (int) $_GET['status_id']
    : 0;
$payment_method = isset($_GET['payment_method'], $paymentMethods[$_GET['payment_method']])
    ? $_GET['payment_method']
    : '';
$start_date = $validDate($_GET['start_date'] ?? '', date('Y-m-d', strtotime('-6 days')));
$end_date = $validDate($_GET['end_date'] ?? '', date('Y-m-d'));
if ($start_date > $end_date) {
    [$start_date, $end_date] = [$end_date, $start_date];
}

$products = [];
$productsQuery = $conn->query('SELECT id, name FROM product_list ORDER BY id DESC');
while ($product = $productsQuery->fetch_assoc()) {
    $products[] = $product;
}

$conditions = [];
if ($product_id > 0) {
    $conditions[] = 'o.product_id = ' . $product_id;
}
if ($status_id > 0) {
    $conditions[] = 'o.status = ' . $status_id;
}
if ($payment_method !== '') {
    $conditions[] = "o.payment_method = '" . $conn->real_escape_string($payment_method) . "'";
}
$start_datetime = $conn->real_escape_string($start_date . ' 00:00:00');
$end_datetime = $conn->real_escape_string($end_date . ' 23:59:59');
$conditions[] = "o.date_created BETWEEN '{$start_datetime}' AND '{$end_datetime}'";
$whereSql = ' WHERE ' . implode(' AND ', $conditions);

$salesConditions = array_values(array_filter($conditions, static function ($condition) {
    return strpos($condition, 'o.status = ') !== 0;
}));
$salesConditions[] = 'o.status = 2';
$salesWhereSql = ' WHERE ' . implode(' AND ', $salesConditions);

$salesQuery = $conn->query(
    'SELECT COALESCE(SUM(o.quantity), 0) AS quantity, COALESCE(SUM(o.total_amount), 0) AS revenue ' .
    'FROM order_list o' . $salesWhereSql
);
$sales = $salesQuery->fetch_assoc();
$soldQuantity = (int) ($sales['quantity'] ?? 0);
$revenue = (float) ($sales['revenue'] ?? 0);

$ordersCountQuery = $conn->query('SELECT COUNT(*) AS total FROM order_list o' . $whereSql);
$ordersCount = (int) ($ordersCountQuery->fetch_assoc()['total'] ?? 0);

$customerStart = $conn->real_escape_string($start_date . ' 00:00:00');
$customerEnd = $conn->real_escape_string($end_date . ' 23:59:59');
$customersQuery = $conn->query(
    "SELECT COUNT(*) AS total FROM customer_list c WHERE c.date_created BETWEEN '{$customerStart}' AND '{$customerEnd}'"
);
$newCustomers = (int) ($customersQuery->fetch_assoc()['total'] ?? 0);

$perPage = 20;
$page = max(1, (int) ($_GET['pg'] ?? 1));
$totalResults = $ordersCount;
$totalPages = max(1, (int) ceil($totalResults / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$ordersQuery = $conn->query(
    'SELECT o.id, o.date_created, o.product_name, o.payment_method, o.quantity, o.total_amount, o.status ' .
    'FROM order_list o' . $whereSql .
    ' ORDER BY o.date_created DESC, o.id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset
);

$paginationUrl = static function ($targetPage) use ($product_id, $status_id, $payment_method, $start_date, $end_date) {
    return './?' . http_build_query([
        'page' => 'report',
        'product_id' => $product_id ?: '',
        'status_id' => $status_id ?: '',
        'payment_method' => $payment_method,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'pg' => $targetPage,
    ]);
};
?>

<style>
    @media all and (max-width: 72em) {
        .report-filters { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; }
        .report-filters > * { margin-right: 0 !important; }
    }
    @media all and (max-width: 40em) {
        .report-filters { grid-template-columns: 1fr; }
    }
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container grid px-6 mx-auto">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Relatórios</h2>

        <form action="./" class="mb-4" method="GET">
            <input type="hidden" name="page" value="report">
            <div class="flex report-filters">
                <select name="product_id" class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select">
                    <option value="">Todas as campanhas</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int) $product['id'] ?>" <?= $product_id === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status_id" class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select">
                    <option value="">Todos os status</option>
                    <?php foreach ($statusLabels as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $status_id === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="payment_method" class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select">
                    <option value="">Todos os métodos</option>
                    <?php foreach ($paymentMethods as $value => $label): ?>
                        <option value="<?= $escape($value) ?>" <?= $payment_method === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <input name="start_date" type="date" value="<?= $escape($start_date) ?>" class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 form-input">
                <input name="end_date" type="date" value="<?= $escape($end_date) ?>" class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 form-input">
                <button class="mt-1 px-5 py-3 font-medium leading-5 text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Filtrar</button>
            </div>
        </form>

        <div class="grid gap-6 mb-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">#</div>
                <div><p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Cotas vendidas</p><p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= number_format($soldQuantity, 0, ',', '.') ?></p></div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">+</div>
                <div><p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Novos clientes</p><p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= number_format($newCustomers, 0, ',', '.') ?></p></div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">✓</div>
                <div><p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Pedidos efetuados</p><p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= number_format($ordersCount, 0, ',', '.') ?></p></div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500">R$</div>
                <div><p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Faturamento confirmado</p><p class="text-lg font-semibold text-gray-700 dark:text-gray-200">R$ <?= number_format($revenue, 2, ',', '.') ?></p></div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead><tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-4 py-3">ID</th><th class="px-4 py-3">Data</th><th class="px-4 py-3">Campanha</th><th class="px-4 py-3">Gateway</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Qtd. números</th><th class="px-4 py-3">Total</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php if ($ordersQuery->num_rows === 0): ?>
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">Nenhum pedido encontrado neste período.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $ordersQuery->fetch_assoc()): ?>
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3">#<?= (int) $row['id'] ?></td>
                                    <td class="px-4 py-3"><?= $escape(date('d/m/Y H:i', strtotime($row['date_created']))) ?></td>
                                    <td class="px-4 py-3"><?= $escape($row['product_name']) ?></td>
                                    <td class="px-4 py-3"><?= $escape($paymentMethods[$row['payment_method']] ?? $row['payment_method']) ?></td>
                                    <td class="px-4 py-3"><?= $escape($statusLabels[(int) $row['status']] ?? 'Desconhecido') ?></td>
                                    <td class="px-4 py-3"><?= number_format((int) $row['quantity'], 0, ',', '.') ?></td>
                                    <td class="px-4 py-3">R$ <?= number_format((float) $row['total_amount'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalResults > 0): ?>
                <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
                    <span class="flex items-center col-span-3"><?= $totalResults ?> pedido<?= $totalResults === 1 ? '' : 's' ?></span><span class="col-span-2"></span>
                    <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end"><nav aria-label="Paginação do relatório"><ul class="inline-flex items-center">
                        <?php if ($page > 1): ?><li><a class="px-3 py-1 rounded-md" href="<?= $escape($paginationUrl($page - 1)) ?>">Anterior</a></li><?php endif; ?>
                        <?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): ?>
                            <li><a class="px-3 py-1 rounded-md <?= $number === $page ? 'text-white bg-purple-600' : '' ?>" href="<?= $escape($paginationUrl($number)) ?>"><?= $number ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?><li><a class="px-3 py-1 rounded-md" href="<?= $escape($paginationUrl($page + 1)) ?>">Próxima</a></li><?php endif; ?>
                    </ul></nav></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
