<?php
require_once __DIR__ . '/data.php';

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$filters = jnsalles_report_filters($_GET);
$paymentMethods = jnsalles_report_payment_methods();
$statusLabels = jnsalles_report_status_labels();
$summary = jnsalles_report_summary($conn, $filters);

$products = [];
$productsQuery = $conn->query('SELECT id, name FROM product_list ORDER BY id DESC');
if ($productsQuery) {
    while ($product = $productsQuery->fetch_assoc()) {
        $products[] = $product;
    }
}

$perPage = 20;
$page = max(1, (int) ($_GET['pg'] ?? 1));
$totalResults = $summary['orders_count'];
$totalPages = max(1, (int) ceil($totalResults / $perPage));
$page = min($page, $totalPages);
$ordersQuery = jnsalles_report_orders($conn, $filters, $perPage, ($page - 1) * $perPage);
$paginationUrl = static function ($targetPage) use ($filters) {
    return './?' . jnsalles_report_query($filters, ['page' => 'report', 'pg' => $targetPage]);
};
$pdfUrl = './report/export.php?' . jnsalles_report_query($filters);
$periodLabel = date('d/m/Y H:i', strtotime($filters['start'])) . ' até ' . date('d/m/Y H:i', strtotime($filters['end']));
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .report-shell{max-width:1260px;padding-bottom:36px}.report-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:28px 0 18px}.report-eyebrow{margin:0 0 4px;color:#a78bfa;font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.report-heading h2{margin:0;color:#f8fafc;font-size:29px;font-weight:780;letter-spacing:-.035em}.report-heading p{margin:7px 0 0;color:#94a3b8;font-size:13px}.report-period-badge{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid #334155;border-radius:999px;background:#111827;color:#cbd5e1;font-size:11px;white-space:nowrap}.report-period-badge:before{content:'';width:7px;height:7px;border-radius:50%;background:#8b5cf6;box-shadow:0 0 0 4px rgba(139,92,246,.12)}
    .report-filter-panel{margin-bottom:20px;padding:18px;border:1px solid #2f3b4f;border-radius:16px;background:linear-gradient(145deg,rgba(30,41,59,.76),rgba(15,23,42,.95));box-shadow:0 18px 44px rgba(0,0,0,.16)}.report-filter-title{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px}.report-filter-title strong{display:block;color:#f1f5f9;font-size:15px}.report-filter-title span{display:block;margin-top:3px;color:#8fa2bb;font-size:11px}.report-filter-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px}.report-field label{display:block;margin-bottom:6px;color:#aebdd0;font-size:11px;font-weight:700}.report-field select,.report-field input{width:100%;height:44px!important;margin:0!important;border:1px solid #3b4a61!important;border-radius:10px!important;background:#101827!important;color:#f1f5f9!important;font-size:12px!important;box-shadow:none!important}.report-field select:focus,.report-field input:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.16)!important}.report-date-range{grid-column:1/-1;display:grid;grid-template-columns:1fr 28px 1fr;align-items:end;gap:11px;padding-top:2px}.report-date-separator{display:flex;height:44px;align-items:center;justify-content:center;color:#64748b}.report-input-wrap{position:relative}.report-input-wrap input{padding-left:40px!important}.report-input-icon{position:absolute;top:50%;left:13px;z-index:2;display:flex;color:#8b9bb0;transform:translateY(-50%);pointer-events:none}.report-filter-actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;margin-top:15px;padding-top:15px;border-top:1px solid #2c3748}.report-action{display:inline-flex;min-height:42px;align-items:center;justify-content:center;gap:8px;padding:0 15px;border-radius:10px;font-size:12px;font-weight:750;transition:.18s}.report-filter-button{border:0;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;box-shadow:0 8px 20px rgba(124,58,237,.22)}.report-pdf-button{border:1px solid rgba(248,113,113,.42);background:rgba(127,29,29,.18);color:#fecaca}.report-pdf-button:hover{border-color:#f87171;background:rgba(127,29,29,.32);color:#fff}.report-reset-button{margin-right:auto;border:1px solid #3b4a61;background:#172033;color:#cbd5e1}
    .report-field select option{background:#101827!important;color:#f1f5f9!important}.report-field input::placeholder{color:#64748b!important}.report-field select:focus-visible,.report-field input:focus-visible{outline:none!important}
    .report-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}.report-metric{display:flex;min-height:104px;align-items:center;gap:13px;padding:16px;border:1px solid #2d394b;border-radius:14px;background:linear-gradient(145deg,#172033,#121925);box-shadow:0 12px 28px rgba(0,0,0,.12)}.report-metric-icon{display:flex;width:43px;height:43px;align-items:center;justify-content:center;flex:0 0 43px;border-radius:12px;font-weight:850}.report-metric:nth-child(1) .report-metric-icon{background:rgba(16,185,129,.16);color:#6ee7b7}.report-metric:nth-child(2) .report-metric-icon{background:rgba(249,115,22,.15);color:#fdba74}.report-metric:nth-child(3) .report-metric-icon{background:rgba(59,130,246,.16);color:#93c5fd}.report-metric:nth-child(4) .report-metric-icon{background:rgba(20,184,166,.16);color:#5eead4}.report-metric small{display:block;margin-bottom:4px;color:#91a1b6;font-size:11px}.report-metric strong{display:block;color:#f8fafc;font-size:19px;letter-spacing:-.02em}
    .report-table-card{overflow:hidden;border:1px solid #2d394b;border-radius:14px;background:#151d2a;box-shadow:0 14px 34px rgba(0,0,0,.13)}.report-table-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #2d394b}.report-table-heading strong{color:#f1f5f9;font-size:14px}.report-table-heading span{color:#8fa2b8;font-size:11px}.report-table-card table{width:100%}.report-table-card thead tr{background:#111722!important}.report-table-card tbody{background:#151d2a!important}.report-table-card tbody tr{border-color:#263245!important}.report-empty{padding:34px 16px!important;color:#8fa2b8!important}.report-status{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:750}.report-status-1{background:rgba(245,158,11,.16);color:#fcd34d}.report-status-2{background:rgba(16,185,129,.16);color:#6ee7b7}.report-status-3{background:rgba(239,68,68,.16);color:#fca5a5}
    .report-shell *::selection{background:#6d28d9!important;color:#fff!important}.report-shell *::-moz-selection{background:#6d28d9!important;color:#fff!important}.report-table-card thead th{border-color:#263245!important;background:#111722!important;color:#aebdd0!important}.report-table-card tbody td{border-color:#29364a!important;background:#151d2a!important;color:#dbe4ef!important}.report-table-card tbody tr:nth-child(even) td{background:#172130!important}.report-table-card tbody tr:hover td{background:#1d293a!important;color:#fff!important}.report-pagination-bar{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 16px;border-top:1px solid #2d394b;background:#111722;color:#8fa2b8}.report-pagination-bar>span{font-size:10px;font-weight:750;letter-spacing:.05em;text-transform:uppercase}.report-pagination{display:flex;align-items:center;gap:5px;margin:0;padding:0;list-style:none}.report-page-link{display:inline-flex;min-width:32px;height:32px;align-items:center;justify-content:center;padding:0 10px;border:1px solid #334155;border-radius:8px;background:#172033!important;color:#cbd5e1!important;font-size:11px;font-weight:750;text-decoration:none!important}.report-page-link:hover,.report-page-link:focus{border-color:#8b5cf6;background:#242f43!important;color:#fff!important;outline:none}.report-page-link.current{border-color:#8b5cf6;background:linear-gradient(135deg,#8b5cf6,#7c3aed)!important;color:#fff!important;box-shadow:0 6px 15px rgba(124,58,237,.25)}
    .flatpickr-calendar{border:1px solid #39475b!important;background:#111827!important;box-shadow:0 22px 50px rgba(0,0,0,.45)!important;color:#e5e7eb!important}.flatpickr-months .flatpickr-month,.flatpickr-current-month .flatpickr-monthDropdown-months,.flatpickr-monthDropdown-month,.flatpickr-weekdays,.flatpickr-weekday{background:#111827!important;color:#dbe3ef!important}.flatpickr-day{color:#cbd5e1!important}.flatpickr-day:hover,.flatpickr-day:focus{border-color:#475569!important;background:#263247!important}.flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange{border-color:#8b5cf6!important;background:#7c3aed!important;color:#fff!important}.flatpickr-day.today{border-color:#8b5cf6!important}.flatpickr-day.prevMonthDay,.flatpickr-day.nextMonthDay{color:#526074!important}.flatpickr-time{border-top-color:#334155!important}.flatpickr-time input,.flatpickr-time .flatpickr-am-pm{color:#e5e7eb!important}.flatpickr-time input:hover,.flatpickr-time input:focus,.flatpickr-time .flatpickr-am-pm:hover{background:#1e293b!important;color:#fff!important}.flatpickr-calendar .numInputWrapper,.flatpickr-calendar .numInputWrapper:hover{background:#111827!important}.flatpickr-calendar .numInputWrapper span{border-color:#334155!important;background:#172033!important}.flatpickr-calendar .numInputWrapper span:hover,.flatpickr-calendar .numInputWrapper span:active{background:#334155!important}.flatpickr-calendar .numInputWrapper span.arrowUp:after{border-bottom-color:#aebdd0!important}.flatpickr-calendar .numInputWrapper span.arrowDown:after{border-top-color:#aebdd0!important}.flatpickr-current-month input.cur-year{color:#e5e7eb!important}
    @media(max-width:900px){.report-filter-grid{grid-template-columns:1fr 1fr}.report-date-range{grid-column:1/-1}.report-metrics{grid-template-columns:1fr 1fr}.report-heading{align-items:flex-start;flex-direction:column}}
    @media(max-width:590px){.report-shell{padding-inline:14px!important}.report-filter-grid,.report-date-range,.report-metrics{grid-template-columns:1fr}.report-date-separator{display:none}.report-filter-actions{align-items:stretch;flex-direction:column}.report-filter-actions .report-action{width:100%}.report-reset-button{margin-right:0}.report-period-badge{white-space:normal}.report-table-heading{align-items:flex-start;flex-direction:column}.report-pagination-bar{align-items:flex-start;flex-direction:column}.report-pagination{flex-wrap:wrap}}
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container grid px-6 mx-auto report-shell">
        <header class="report-heading">
            <div><p class="report-eyebrow">Análise financeira</p><h2>Relatórios</h2><p>Consulte pedidos, clientes, cotas e faturamento dentro de um intervalo exato.</p></div>
            <span class="report-period-badge"><?= $escape($periodLabel) ?></span>
        </header>

        <form action="./" class="report-filter-panel" method="GET" id="report-filter-form">
            <input type="hidden" name="page" value="report">
            <div class="report-filter-title"><div><strong>Filtros do relatório</strong><span>Todos os cartões, pedidos e o PDF respeitam exatamente os filtros abaixo.</span></div></div>
            <div class="report-filter-grid">
                <div class="report-field"><label for="report-product">Campanha</label><select id="report-product" name="product_id"><option value="">Todas as campanhas</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= $filters['product_id'] === (int) $product['id'] ? 'selected' : '' ?>><?= $escape($product['name']) ?></option><?php endforeach; ?></select></div>
                <div class="report-field"><label for="report-status">Status do pedido</label><select id="report-status" name="status_id"><option value="">Todos os status</option><?php foreach ($statusLabels as $value => $label): ?><option value="<?= $value ?>" <?= $filters['status_id'] === $value ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></div>
                <div class="report-field"><label for="report-payment">Forma de pagamento</label><select id="report-payment" name="payment_method"><option value="">Todos os métodos</option><?php foreach ($paymentMethods as $value => $label): ?><option value="<?= $escape($value) ?>" <?= $filters['payment_method'] === $value ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></div>
                <div class="report-date-range">
                    <div class="report-field"><label for="report-start">Início do período</label><div class="report-input-wrap"><span class="report-input-icon">◷</span><input id="report-start" name="start_at" type="text" value="<?= $escape($filters['start_at']) ?>" placeholder="dd/mm/aaaa 00:00" autocomplete="off"></div></div>
                    <span class="report-date-separator">→</span>
                    <div class="report-field"><label for="report-end">Fim do período</label><div class="report-input-wrap"><span class="report-input-icon">◷</span><input id="report-end" name="end_at" type="text" value="<?= $escape($filters['end_at']) ?>" placeholder="dd/mm/aaaa 00:00" autocomplete="off"></div></div>
                </div>
            </div>
            <div class="report-filter-actions">
                <a class="report-action report-reset-button" href="./?page=report">Limpar filtros</a>
                <a class="report-action report-pdf-button" href="<?= $escape($pdfUrl) ?>">↓ Baixar PDF</a>
                <button class="report-action report-filter-button" type="submit">Aplicar filtros</button>
            </div>
        </form>

        <section class="report-metrics" aria-label="Resumo do relatório">
            <article class="report-metric"><span class="report-metric-icon">#</span><div><small>Cotas vendidas e pagas</small><strong><?= number_format($summary['sold_quantity'], 0, ',', '.') ?></strong></div></article>
            <article class="report-metric"><span class="report-metric-icon">+</span><div><small>Novos clientes</small><strong><?= number_format($summary['new_customers'], 0, ',', '.') ?></strong></div></article>
            <article class="report-metric"><span class="report-metric-icon">✓</span><div><small>Pedidos encontrados</small><strong><?= number_format($summary['orders_count'], 0, ',', '.') ?></strong></div></article>
            <article class="report-metric"><span class="report-metric-icon">R$</span><div><small>Faturamento confirmado</small><strong>R$ <?= number_format($summary['revenue'], 2, ',', '.') ?></strong></div></article>
        </section>

        <section class="report-table-card">
            <div class="report-table-heading"><strong>Pedidos do período</strong><span><?= number_format($totalResults, 0, ',', '.') ?> resultado<?= $totalResults === 1 ? '' : 's' ?></span></div>
            <div class="w-full overflow-x-auto"><table class="w-full whitespace-no-wrap"><thead><tr class="text-xs font-semibold tracking-wide text-left text-gray-400 uppercase"><th class="px-4 py-3">ID</th><th class="px-4 py-3">Data e hora</th><th class="px-4 py-3">Campanha</th><th class="px-4 py-3">Gateway</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Cotas</th><th class="px-4 py-3">Total</th></tr></thead><tbody class="divide-y dark:divide-gray-700">
                <?php if (!$ordersQuery || $ordersQuery->num_rows === 0): ?><tr><td colspan="7" class="report-empty text-center">Nenhum pedido corresponde aos filtros aplicados.</td></tr><?php else: ?><?php while ($row = $ordersQuery->fetch_assoc()): ?><tr class="text-gray-300"><td class="px-4 py-3">#<?= (int) $row['id'] ?></td><td class="px-4 py-3"><?= $escape(date('d/m/Y H:i', strtotime($row['date_created']))) ?></td><td class="px-4 py-3"><?= $escape($row['product_name']) ?></td><td class="px-4 py-3"><?= $escape($paymentMethods[$row['payment_method']] ?? ($row['payment_method'] ?: 'Não informado')) ?></td><td class="px-4 py-3"><span class="report-status report-status-<?= (int) $row['status'] ?>"><?= $escape($statusLabels[(int) $row['status']] ?? 'Desconhecido') ?></span></td><td class="px-4 py-3"><?= number_format((int) $row['quantity'], 0, ',', '.') ?></td><td class="px-4 py-3">R$ <?= number_format((float) $row['total_amount'], 2, ',', '.') ?></td></tr><?php endwhile; ?><?php endif; ?>
            </tbody></table></div>
            <?php if ($totalResults > 0): ?><div class="report-pagination-bar"><span>Página <?= $page ?> de <?= $totalPages ?></span><nav aria-label="Paginação do relatório"><ul class="report-pagination"><?php if ($page > 1): ?><li><a class="report-page-link" href="<?= $escape($paginationUrl($page - 1)) ?>">Anterior</a></li><?php endif; ?><?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): ?><li><a class="report-page-link <?= $number === $page ? 'current' : '' ?>" href="<?= $escape($paginationUrl($number)) ?>"><?= $number ?></a></li><?php endfor; ?><?php if ($page < $totalPages): ?><li><a class="report-page-link" href="<?= $escape($paginationUrl($page + 1)) ?>">Próxima</a></li><?php endif; ?></ul></nav></div><?php endif; ?>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
<script>
(function () {
    function activateDateTimePicker(selector) {
        if (typeof flatpickr !== 'function') return;
        flatpickr(selector, {
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 1,
            defaultHour: 0,
            defaultMinute: 0,
            allowInput: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'd/m/Y H:i',
            locale: window.flatpickr && flatpickr.l10ns.pt ? flatpickr.l10ns.pt : 'default',
            disableMobile: true
        });
    }
    activateDateTimePicker('#report-start');
    activateDateTimePicker('#report-end');
})();
</script>
