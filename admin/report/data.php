<?php

if (!function_exists('jnsalles_report_payment_methods')) {
    function jnsalles_report_payment_methods()
    {
        return [
            'MercadoPago' => 'Mercado Pago',
            'Paggue' => 'Paggue',
            'Gerencianet' => 'Efí / Gerencianet',
            'OpenPix' => 'OpenPix / Woovi',
            'Pay2m' => 'Pay2M',
            'VenoPag' => 'VenoPag',
            'Manual' => 'Manual',
        ];
    }
}

if (!function_exists('jnsalles_report_status_labels')) {
    function jnsalles_report_status_labels()
    {
        return [1 => 'Pendente', 2 => 'Pago', 3 => 'Cancelado'];
    }
}

if (!function_exists('jnsalles_report_parse_datetime')) {
    function jnsalles_report_parse_datetime($value, $fallback)
    {
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $value = trim((string) $value);
        $formats = ['!Y-m-d H:i', '!Y-m-d\TH:i', '!Y-m-d'];
        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value, $timezone);
            $errors = DateTimeImmutable::getLastErrors();
            $isValid = $date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
            if ($isValid) {
                return $date->format('Y-m-d H:i:00');
            }
        }
        return $fallback;
    }
}

if (!function_exists('jnsalles_report_filters')) {
    function jnsalles_report_filters(array $source)
    {
        $paymentMethods = jnsalles_report_payment_methods();
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $today = new DateTimeImmutable('today', $timezone);
        $defaultStart = $today->modify('-6 days')->format('Y-m-d 00:00:00');
        $defaultEnd = $today->format('Y-m-d 00:00:00');

        $legacyStart = trim((string) ($source['start_date'] ?? ''));
        $legacyEnd = trim((string) ($source['end_date'] ?? ''));
        $startValue = trim((string) ($source['start_at'] ?? ''));
        $endValue = trim((string) ($source['end_at'] ?? ''));
        if ($startValue === '' && $legacyStart !== '') {
            $startValue = $legacyStart . ' ' . trim((string) ($source['start_time'] ?? '00:00'));
        }
        if ($endValue === '' && $legacyEnd !== '') {
            $endValue = $legacyEnd . ' ' . trim((string) ($source['end_time'] ?? '00:00'));
        }

        $start = jnsalles_report_parse_datetime($startValue, $defaultStart);
        $end = jnsalles_report_parse_datetime($endValue, $defaultEnd);
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        // O seletor trabalha com precisao de minuto. Inclua o minuto final
        // completo para que, por exemplo, 19:00 tambem considere 19:00:59.
        $end = substr($end, 0, 16) . ':59';

        $productId = isset($source['product_id']) && ctype_digit((string) $source['product_id'])
            ? (int) $source['product_id']
            : 0;
        $statusId = isset($source['status_id']) && in_array((int) $source['status_id'], [1, 2, 3], true)
            ? (int) $source['status_id']
            : 0;
        $paymentMethod = isset($source['payment_method'], $paymentMethods[$source['payment_method']])
            ? (string) $source['payment_method']
            : '';

        return [
            'product_id' => $productId,
            'status_id' => $statusId,
            'payment_method' => $paymentMethod,
            'start' => $start,
            'end' => $end,
            'start_at' => substr($start, 0, 16),
            'end_at' => substr($end, 0, 16),
        ];
    }
}

if (!function_exists('jnsalles_report_conditions')) {
    function jnsalles_report_conditions($conn, array $filters, $paidOnly = false)
    {
        $conditions = [];
        if ($filters['product_id'] > 0) {
            $conditions[] = 'o.product_id = ' . (int) $filters['product_id'];
        }
        if ($paidOnly) {
            $conditions[] = $filters['status_id'] > 0 && $filters['status_id'] !== 2
                ? '1 = 0'
                : 'o.status = 2';
        } elseif ($filters['status_id'] > 0) {
            $conditions[] = 'o.status = ' . (int) $filters['status_id'];
        }
        if ($filters['payment_method'] !== '') {
            $conditions[] = "o.payment_method = '" . $conn->real_escape_string($filters['payment_method']) . "'";
        }
        $start = $conn->real_escape_string($filters['start']);
        $end = $conn->real_escape_string($filters['end']);
        $conditions[] = "o.date_created BETWEEN '{$start}' AND '{$end}'";
        return ' WHERE ' . implode(' AND ', $conditions);
    }
}

if (!function_exists('jnsalles_report_summary')) {
    function jnsalles_report_summary($conn, array $filters)
    {
        $where = jnsalles_report_conditions($conn, $filters, false);
        $paidWhere = jnsalles_report_conditions($conn, $filters, true);

        $salesQuery = $conn->query(
            'SELECT COALESCE(SUM(o.quantity), 0) AS quantity, COALESCE(SUM(o.total_amount), 0) AS revenue '
            . 'FROM order_list o' . $paidWhere
        );
        $sales = $salesQuery ? $salesQuery->fetch_assoc() : [];

        $ordersQuery = $conn->query('SELECT COUNT(*) AS total FROM order_list o' . $where);
        $orders = $ordersQuery ? $ordersQuery->fetch_assoc() : [];

        $customerStart = $conn->real_escape_string($filters['start']);
        $customerEnd = $conn->real_escape_string($filters['end']);
        $customersQuery = $conn->query(
            "SELECT COUNT(*) AS total FROM customer_list c WHERE c.date_created BETWEEN '{$customerStart}' AND '{$customerEnd}'"
        );
        $customers = $customersQuery ? $customersQuery->fetch_assoc() : [];

        return [
            'sold_quantity' => (int) ($sales['quantity'] ?? 0),
            'revenue' => (float) ($sales['revenue'] ?? 0),
            'orders_count' => (int) ($orders['total'] ?? 0),
            'new_customers' => (int) ($customers['total'] ?? 0),
        ];
    }
}

if (!function_exists('jnsalles_report_orders')) {
    function jnsalles_report_orders($conn, array $filters, $limit = null, $offset = 0)
    {
        $sql = 'SELECT o.id, o.date_created, o.product_name, o.payment_method, o.quantity, o.total_amount, o.status '
            . 'FROM order_list o' . jnsalles_report_conditions($conn, $filters, false)
            . ' ORDER BY o.date_created DESC, o.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, (int) $limit) . ' OFFSET ' . max(0, (int) $offset);
        }
        return $conn->query($sql);
    }
}

if (!function_exists('jnsalles_report_query')) {
    function jnsalles_report_query(array $filters, array $extra = [])
    {
        return http_build_query(array_merge([
            'product_id' => $filters['product_id'] ?: '',
            'status_id' => $filters['status_id'] ?: '',
            'payment_method' => $filters['payment_method'],
            'start_at' => $filters['start_at'],
            'end_at' => $filters['end_at'],
        ], $extra));
    }
}
