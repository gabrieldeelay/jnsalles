<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$validDate = static function ($value, $fallback) {
    $date = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
};

$validTime = static function ($value, $fallback) {
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $value) ? $value : $fallback;
};

$product_id = isset($_GET['raffle']) && ctype_digit((string) $_GET['raffle'])
    ? (int) $_GET['raffle']
    : 0;
$start_date = $validDate($_GET['start_date'] ?? '', date('Y-m-d', strtotime('-6 days')));
$end_date = $validDate($_GET['end_date'] ?? '', date('Y-m-d'));
$start_time = $validTime($_GET['start_time'] ?? '', '00:00');
$end_time = $validTime($_GET['end_time'] ?? '', '23:59');

if ($start_date > $end_date) {
    [$start_date, $end_date] = [$end_date, $start_date];
}

$products = [];
$productsQuery = $conn->query('SELECT id, name FROM product_list ORDER BY id DESC');
while ($product = $productsQuery->fetch_assoc()) {
    $products[] = $product;
}

$conditions = ['o.status = 2'];
if ($product_id > 0) {
    $conditions[] = 'o.product_id = ' . $product_id;
}

$start_datetime = $conn->real_escape_string($start_date . ' ' . $start_time . ':00');
$end_datetime = $conn->real_escape_string($end_date . ' ' . $end_time . ':59');
$conditions[] = "o.date_created BETWEEN '{$start_datetime}' AND '{$end_datetime}'";
$whereSql = ' WHERE ' . implode(' AND ', $conditions);

$perPage = 20;
$page = max(1, (int) ($_GET['pg'] ?? 1));
$countQuery = $conn->query(
    'SELECT COUNT(*) AS total FROM (' .
    'SELECT o.customer_id FROM order_list o ' . $whereSql . ' GROUP BY o.customer_id' .
    ') AS ranked_customers'
);
$totalResults = (int) ($countQuery->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalResults / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$rankingQuery = $conn->query(
    'SELECT c.id, c.firstname, c.lastname, c.phone, ' .
    'SUM(o.quantity) AS total_quantity, SUM(o.total_amount) AS total_amount, ' .
    "GROUP_CONCAT(DISTINCT COALESCE(p.name, o.product_name) ORDER BY COALESCE(p.name, o.product_name) SEPARATOR ', ') AS campaigns " .
    'FROM order_list o ' .
    'INNER JOIN customer_list c ON c.id = o.customer_id ' .
    'LEFT JOIN product_list p ON p.id = o.product_id ' .
    $whereSql . ' ' .
    'GROUP BY c.id, c.firstname, c.lastname, c.phone ' .
    'ORDER BY total_quantity DESC, total_amount DESC, c.firstname ASC ' .
    'LIMIT ' . $perPage . ' OFFSET ' . $offset
);

$paginationUrl = static function ($targetPage) use ($product_id, $start_date, $start_time, $end_date, $end_time) {
    return './?' . http_build_query([
        'page' => 'ranking',
        'raffle' => $product_id ?: '',
        'start_date' => $start_date,
        'start_time' => $start_time,
        'end_date' => $end_date,
        'end_time' => $end_time,
        'pg' => $targetPage,
    ]);
};
?>

<style>
    @media all and (max-width: 64em) {
        .ranking-filters { display: grid !important; grid-template-columns: 1fr 1fr; gap: .5rem; }
        .ranking-filters > * { margin-right: 0 !important; }
    }
    @media all and (max-width: 40em) {
        .ranking-filters { grid-template-columns: 1fr; }
    }
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container grid px-6 mx-auto">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Top compradores</h2>

        <form action="./" class="mb-4" method="GET">
            <input type="hidden" name="page" value="ranking">
            <label for="raffle" class="block text-sm dark:text-gray-300">Selecione a campanha</label>
            <div class="flex ranking-filters">
                <select name="raffle" id="raffle" class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple">
                    <option value="">Todas as campanhas</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int) $product['id'] ?>" <?= $product_id === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input name="start_date" type="date" value="<?= $escape($start_date) ?>" class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 form-input">
                <input name="start_time" type="time" value="<?= $escape($start_time) ?>" class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 form-input">
                <input name="end_date" type="date" value="<?= $escape($end_date) ?>" class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 form-input">
                <input name="end_time" type="time" value="<?= $escape($end_time) ?>" class="mr-2 block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 form-input">
                <button class="mt-1 px-5 py-3 font-medium leading-5 text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">Filtrar</button>
            </div>
        </form>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Qtd. números</th>
                            <th class="px-4 py-3">Total pago</th>
                            <th class="px-4 py-3">Telefone</th>
                            <th class="px-4 py-3">Campanha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php if ($rankingQuery->num_rows === 0): ?>
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">Nenhum pagamento confirmado neste período.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $rankingQuery->fetch_assoc()):
                                $phone = formatPhoneNumber($row['phone']);
                                $maskedPhone = strlen($phone) > 4 ? substr($phone, 0, -4) . '****' : '****';
                            ?>
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3"><?= $escape(trim($row['firstname'] . ' ' . $row['lastname'])) ?></td>
                                    <td class="px-4 py-3"><?= number_format((int) $row['total_quantity'], 0, ',', '.') ?></td>
                                    <td class="px-4 py-3">R$ <?= number_format((float) $row['total_amount'], 2, ',', '.') ?></td>
                                    <td class="px-4 py-3"><?= $escape($maskedPhone) ?></td>
                                    <td class="px-4 py-3"><?= $escape($row['campaigns']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalResults > 0): ?>
                <div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
                    <span class="flex items-center col-span-3"><?= $totalResults ?> comprador<?= $totalResults === 1 ? '' : 'es' ?></span>
                    <span class="col-span-2"></span>
                    <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
                        <nav aria-label="Paginação do ranking"><ul class="inline-flex items-center">
                            <?php if ($page > 1): ?><li><a class="px-3 py-1 rounded-md" href="<?= $escape($paginationUrl($page - 1)) ?>">Anterior</a></li><?php endif; ?>
                            <?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): ?>
                                <li><a class="px-3 py-1 rounded-md <?= $number === $page ? 'text-white bg-purple-600' : '' ?>" href="<?= $escape($paginationUrl($number)) ?>"><?= $number ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?><li><a class="px-3 py-1 rounded-md" href="<?= $escape($paginationUrl($page + 1)) ?>">Próxima</a></li><?php endif; ?>
                        </ul></nav>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
