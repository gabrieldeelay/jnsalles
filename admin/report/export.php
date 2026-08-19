<?php

ob_start();
require_once dirname(__DIR__, 2) . '/settings.php';
require_once __DIR__ . '/data.php';
ob_end_clean();

if ((int) $_settings->userdata('type') !== 1) {
    http_response_code(403);
    exit('Acesso administrativo necessário para baixar este relatório.');
}

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(503);
    exit('O gerador de PDF não está disponível no servidor. Envie a pasta vendor do projeto para o Plesk.');
}
require_once $autoload;

$conn = $_settings->conn;
$filters = jnsalles_report_filters($_GET);
$summary = jnsalles_report_summary($conn, $filters);
$orders = jnsalles_report_orders($conn, $filters);
$paymentMethods = jnsalles_report_payment_methods();
$statusLabels = jnsalles_report_status_labels();
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$campaignLabel = 'Todas as campanhas';
if ($filters['product_id'] > 0) {
    $productStatement = $conn->prepare('SELECT name FROM product_list WHERE id = ? LIMIT 1');
    if ($productStatement) {
        $selectedProductId = (int) $filters['product_id'];
        $productStatement->bind_param('i', $selectedProductId);
        $productStatement->execute();
        $product = $productStatement->get_result()->fetch_assoc();
        if ($product) {
            $campaignLabel = $product['name'];
        }
        $productStatement->close();
    }
}
$statusLabel = $filters['status_id'] > 0 ? ($statusLabels[$filters['status_id']] ?? 'Desconhecido') : 'Todos os status';
$paymentLabel = $filters['payment_method'] !== '' ? ($paymentMethods[$filters['payment_method']] ?? $filters['payment_method']) : 'Todos os métodos';
$siteName = trim((string) $_settings->info('name')) ?: 'JNSalles';
$periodLabel = date('d/m/Y H:i', strtotime($filters['start'])) . ' até ' . date('d/m/Y H:i', strtotime($filters['end']));

$rows = '';
if (!$orders || $orders->num_rows === 0) {
    $rows = '<tr><td colspan="7" class="empty">Nenhum pedido corresponde aos filtros aplicados.</td></tr>';
} else {
    while ($order = $orders->fetch_assoc()) {
        $status = (int) $order['status'];
        $rows .= '<tr>'
            . '<td>#' . (int) $order['id'] . '</td>'
            . '<td>' . $escape(date('d/m/Y H:i', strtotime($order['date_created']))) . '</td>'
            . '<td>' . $escape($order['product_name']) . '</td>'
            . '<td>' . $escape($paymentMethods[$order['payment_method']] ?? ($order['payment_method'] ?: 'Não informado')) . '</td>'
            . '<td><span class="status status-' . $status . '">' . $escape($statusLabels[$status] ?? 'Desconhecido') . '</span></td>'
            . '<td class="number">' . number_format((int) $order['quantity'], 0, ',', '.') . '</td>'
            . '<td class="money">R$ ' . number_format((float) $order['total_amount'], 2, ',', '.') . '</td>'
            . '</tr>';
    }
}

$html = '<!doctype html><html lang="pt-br"><head><meta charset="UTF-8"><style>
@page{margin:22px 24px 28px;size:A4 landscape}*{box-sizing:border-box}body{margin:0;color:#1e293b;font-family:"DejaVu Sans",sans-serif;font-size:9px}.header{padding:18px 20px;border-radius:10px;background:#111827;color:#fff}.eyebrow{margin:0 0 4px;color:#c4b5fd;font-size:8px;font-weight:bold;letter-spacing:1.4px;text-transform:uppercase}.header h1{margin:0;font-size:23px}.header p{margin:5px 0 0;color:#cbd5e1;font-size:9px}.period{float:right;margin-top:-34px;padding:7px 10px;border:1px solid #475569;border-radius:14px;color:#e2e8f0;font-size:8px}.filters{width:100%;margin:12px 0;border-collapse:separate;border-spacing:6px 0}.filters td{width:25%;padding:9px 10px;border:1px solid #dbe2ea;border-radius:7px;background:#f8fafc}.filters small{display:block;margin-bottom:3px;color:#64748b;font-size:7px;text-transform:uppercase}.filters strong{font-size:8px}.metrics{width:100%;margin-bottom:13px;border-collapse:separate;border-spacing:6px 0}.metrics td{width:25%;padding:12px;border-radius:8px;background:#172033;color:#fff}.metrics small{display:block;margin-bottom:4px;color:#aebdd0;font-size:7px}.metrics strong{font-size:15px}.table-title{margin:0;padding:10px 12px;border:1px solid #dbe2ea;border-bottom:0;border-radius:8px 8px 0 0;background:#f8fafc;font-size:10px}table.orders{width:100%;border-collapse:collapse;table-layout:fixed}.orders th{padding:8px 7px;background:#172033;color:#cbd5e1;font-size:7px;text-align:left;text-transform:uppercase}.orders td{padding:7px;border-bottom:1px solid #e2e8f0;vertical-align:top;word-wrap:break-word}.orders tr:nth-child(even) td{background:#f8fafc}.orders th:nth-child(1),.orders td:nth-child(1){width:6%}.orders th:nth-child(2),.orders td:nth-child(2){width:13%}.orders th:nth-child(3),.orders td:nth-child(3){width:28%}.orders th:nth-child(4),.orders td:nth-child(4){width:14%}.orders th:nth-child(5),.orders td:nth-child(5){width:11%}.orders th:nth-child(6),.orders td:nth-child(6){width:10%}.orders th:nth-child(7),.orders td:nth-child(7){width:18%}.number,.money{white-space:nowrap}.money{font-weight:bold}.status{display:inline-block;padding:3px 6px;border-radius:8px;font-size:7px;font-weight:bold}.status-1{background:#fef3c7;color:#92400e}.status-2{background:#d1fae5;color:#065f46}.status-3{background:#fee2e2;color:#991b1b}.empty{padding:25px!important;color:#64748b;text-align:center}.footer{position:fixed;right:0;bottom:-18px;left:0;color:#64748b;font-size:7px;text-align:center}.footer .page:after{content:counter(page)}
</style></head><body>'
    . '<div class="header"><p class="eyebrow">Relatório administrativo</p><h1>' . $escape($siteName) . '</h1><p>Resumo financeiro e operacional conforme os filtros aplicados.</p><div class="period">' . $escape($periodLabel) . '</div></div>'
    . '<table class="filters"><tr><td><small>Campanha</small><strong>' . $escape($campaignLabel) . '</strong></td><td><small>Status</small><strong>' . $escape($statusLabel) . '</strong></td><td><small>Pagamento</small><strong>' . $escape($paymentLabel) . '</strong></td><td><small>Gerado em</small><strong>' . date('d/m/Y H:i') . '</strong></td></tr></table>'
    . '<table class="metrics"><tr><td><small>Cotas vendidas e pagas</small><strong>' . number_format($summary['sold_quantity'], 0, ',', '.') . '</strong></td><td><small>Novos clientes</small><strong>' . number_format($summary['new_customers'], 0, ',', '.') . '</strong></td><td><small>Pedidos encontrados</small><strong>' . number_format($summary['orders_count'], 0, ',', '.') . '</strong></td><td><small>Faturamento confirmado</small><strong>R$ ' . number_format($summary['revenue'], 2, ',', '.') . '</strong></td></tr></table>'
    . '<h2 class="table-title">Pedidos do período</h2><table class="orders"><thead><tr><th>ID</th><th>Data e hora</th><th>Campanha</th><th>Gateway</th><th>Status</th><th>Cotas</th><th>Total</th></tr></thead><tbody>' . $rows . '</tbody></table>'
    . '<div class="footer">' . $escape($siteName) . ' - relatório gerado pelo painel administrativo - página <span class="page"></span></div>'
    . '</body></html>';

$options = new Dompdf\Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf\Dompdf($options);
$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->render();
$filename = 'relatorio-' . date('Ymd-Hi', strtotime($filters['start'])) . '-a-' . date('Ymd-Hi', strtotime($filters['end'])) . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
