<?php

/**
 * Payment integration boundary.
 *
 * Secrets are read from system_info (or matching environment variables), never
 * returned to the browser, and provider errors are reduced to safe messages.
 */

function payment_provider_definitions()
{
    return [
        'mercadopago' => ['label' => 'Mercado Pago', 'method' => 'MercadoPago', 'tax' => 'mercadopago_tax'],
        'gerencianet' => ['label' => 'Efí (Gerencianet)', 'method' => 'Gerencianet', 'tax' => 'gerencianet_tax'],
        'paggue' => ['label' => 'Paggue', 'method' => 'Paggue', 'tax' => 'paggue_tax'],
        'openpix' => ['label' => 'OpenPix / Woovi', 'method' => 'OpenPix', 'tax' => 'openpix_tax'],
        'pay2m' => ['label' => 'Pay2M', 'method' => 'Pay2m', 'tax' => 'pay2m_tax'],
        'venopag' => ['label' => 'VenoPag', 'method' => 'VenoPag', 'tax' => 'venopag_tax'],
    ];
}

function payment_setting($field, $default = '')
{
    global $_settings;
    $environment = getenv(strtoupper($field));
    if ($environment !== false && $environment !== '') {
        return $environment;
    }
    if (isset($_settings) && is_object($_settings)) {
        $value = $_settings->info($field);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
        }
    }
    return $default;
}

function payment_active_provider()
{
    $enabled = [];
    foreach (payment_provider_definitions() as $provider => $definition) {
        if ((string) payment_setting($provider, '2') === '1') {
            $enabled[] = $provider;
        }
    }
    if (count($enabled) === 1) {
        return $enabled[0];
    }
    sort($enabled);
    $automaticSplit = strtolower((string) payment_setting('gateway_provider')) === 'split'
        && (string) payment_setting('pay2m_high_value_enabled', '0') === '1';
    return $automaticSplit && $enabled === ['pay2m', 'venopag'] ? 'venopag' : null;
}

function payment_pay2m_high_value_threshold()
{
    $configured = str_replace(',', '.', (string) payment_setting('pay2m_high_value_threshold', '999.00'));
    return max(0, round((float) $configured, 2));
}

function payment_provider_for_amount($amount)
{
    $activeProvider = payment_active_provider();
    if (!$activeProvider) {
        return null;
    }

    $baseAmount = round((float) str_replace(',', '.', (string) $amount), 2);
    $usePay2mForHighValue = (string) payment_setting('pay2m_high_value_enabled', '0') === '1';
    if ($usePay2mForHighValue && $baseAmount > payment_pay2m_high_value_threshold()) {
        return 'pay2m';
    }

    return $activeProvider;
}

function payment_requires_customer_document()
{
    return false;
}

function payment_venopag_default_document()
{
    $configured = getenv('VENOPAG_DEFAULT_DOCUMENT');
    if ($configured === false || trim((string) $configured) === '') {
        $configured = payment_setting('venopag_default_document');
    }
    return preg_replace('/\D+/', '', (string) $configured);
}

function payment_venopag_minimum_amount()
{
    $configured = getenv('VENOPAG_MIN_AMOUNT');
    if ($configured === false || trim((string) $configured) === '') {
        $configured = payment_setting('venopag_min_amount', '1.00');
    }
    return max(1.00, round((float) str_replace(',', '.', (string) $configured), 2));
}

function payment_customer_document_is_valid($value)
{
    $document = preg_replace('/\D+/', '', (string) $value);
    $length = strlen($document);
    if (!in_array($length, [11, 14], true) || preg_match('/^(\d)\1+$/', $document)) {
        return false;
    }

    if ($length === 11) {
        for ($digit = 9; $digit < 11; $digit++) {
            $sum = 0;
            for ($index = 0; $index < $digit; $index++) {
                $sum += (int) $document[$index] * (($digit + 1) - $index);
            }
            $expected = (($sum * 10) % 11) % 10;
            if ($expected !== (int) $document[$digit]) {
                return false;
            }
        }
        return true;
    }

    $weights = [
        [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
    ];
    foreach ($weights as $offset => $sequence) {
        $sum = 0;
        foreach ($sequence as $index => $weight) {
            $sum += (int) $document[$index] * $weight;
        }
        $remainder = $sum % 11;
        $expected = $remainder < 2 ? 0 : 11 - $remainder;
        if ($expected !== (int) $document[12 + $offset]) {
            return false;
        }
    }
    return true;
}

function payment_amount($amount, $provider)
{
    $definitions = payment_provider_definitions();
    $base = round((float) str_replace(',', '.', (string) $amount), 2);
    $tax = isset($definitions[$provider]) ? (float) payment_setting($definitions[$provider]['tax'], 0) : 0;
    return round($base + ($base * max(0, $tax) / 100), 2);
}

function payment_local_datetime()
{
    return (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
}

/**
 * VenoPag confirms asynchronously. Rank its paid orders by the reservation
 * time so a database server running in UTC cannot move the purchase outside
 * the configured ranking window.
 */
function payment_ranking_datetime_sql($alias = 'o')
{
    $alias = preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $alias) ? (string) $alias : 'o';
    $created = "NULLIF({$alias}.date_created, '0000-00-00 00:00:00')";
    $updated = "NULLIF({$alias}.date_updated, '0000-00-00 00:00:00')";

    return "CASE WHEN {$alias}.payment_method = 'VenoPag' "
        . "THEN COALESCE({$created}, {$updated}) "
        . "ELSE COALESCE({$updated}, {$created}) END";
}

function ranking_timer_configuration($productId, $now = null)
{
    global $_settings;

    $productId = (int) $productId;
    $timezone = new DateTimeZone('America/Sao_Paulo');
    $now = $now instanceof DateTimeImmutable ? $now : new DateTimeImmutable('now', $timezone);
    $prefix = 'ranking_timer_' . $productId . '_';
    $read = static function ($field) use ($_settings, $prefix) {
        return trim((string) $_settings->info($prefix . $field));
    };
    $parse = static function ($value) use ($timezone) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', (string) $value, $timezone);
        return $date && $date->format('Y-m-d H:i:s') === $value ? $date : null;
    };

    $start = $parse($read('start'));
    $end = $parse($read('end'));
    $reset = $parse($read('reset'));
    $pausedAt = $parse($read('paused_at'));
    $enabled = $read('enabled') === '1';
    $configured = $start && $end && $end > $start;
    $storedState = strtolower($read('state'));
    $isPaused = $enabled && $configured && $storedState === 'paused' && $pausedAt;

    if (!$enabled || !$configured) {
        $state = 'disabled';
    } elseif ($isPaused) {
        $state = 'paused';
    } elseif ($now < $start) {
        $state = 'scheduled';
    } elseif ($now >= $end) {
        $state = 'ended';
    } else {
        $state = 'running';
    }

    $windowStart = $start;
    $usesReset = false;
    if ($configured && $reset && $reset >= $start) {
        $windowStart = $reset;
        $usesReset = true;
    }
    $windowEnd = $end;
    if ($state === 'paused' && $pausedAt < $end) {
        $windowEnd = $pausedAt;
    }

    $pauseIntervals = [];
    $decoded = json_decode($read('pause_intervals'), true);
    if (is_array($decoded)) {
        foreach ($decoded as $interval) {
            if (!is_array($interval)) {
                continue;
            }
            $pauseStart = $parse($interval['start'] ?? '');
            $pauseEnd = $parse($interval['end'] ?? '');
            if ($pauseStart && $pauseEnd && $pauseEnd > $pauseStart) {
                $pauseIntervals[] = [
                    'start' => $pauseStart->format('Y-m-d H:i:s'),
                    'end' => $pauseEnd->format('Y-m-d H:i:s'),
                ];
            }
        }
    }

    return [
        'product_id' => $productId,
        'enabled' => $enabled,
        'configured' => (bool) $configured,
        'state' => $state,
        'start' => $start ? $start->format('Y-m-d H:i:s') : '',
        'end' => $end ? $end->format('Y-m-d H:i:s') : '',
        'reset' => $reset ? $reset->format('Y-m-d H:i:s') : '',
        'paused_at' => $pausedAt ? $pausedAt->format('Y-m-d H:i:s') : '',
        'window_start' => $windowStart ? $windowStart->format('Y-m-d H:i:s') : '',
        'window_end' => $windowEnd ? $windowEnd->format('Y-m-d H:i:s') : '',
        'uses_reset' => $usesReset,
        'pause_intervals' => $pauseIntervals,
    ];
}

function ranking_timer_sql_conditions($alias, array $timer, $connection)
{
    if (empty($timer['configured'])) {
        return [];
    }

    $alias = preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $alias) ? (string) $alias : 'o';
    $dateSql = payment_ranking_datetime_sql($alias);
    $start = $connection->real_escape_string((string) $timer['window_start']);
    $end = $connection->real_escape_string((string) $timer['window_end']);
    $operator = !empty($timer['uses_reset']) ? '>' : '>=';
    $conditions = ["{$dateSql} {$operator} '{$start}'", "{$dateSql} <= '{$end}'"];

    foreach ($timer['pause_intervals'] ?? [] as $interval) {
        $pauseStart = $connection->real_escape_string((string) $interval['start']);
        $pauseEnd = $connection->real_escape_string((string) $interval['end']);
        $confirmationSql = "COALESCE(NULLIF({$alias}.date_updated, '0000-00-00 00:00:00'), NULLIF({$alias}.date_created, '0000-00-00 00:00:00'))";
        $conditions[] = "NOT (({$dateSql} >= '{$pauseStart}' AND {$dateSql} <= '{$pauseEnd}') "
            . "OR ({$confirmationSql} >= '{$pauseStart}' AND {$confirmationSql} <= '{$pauseEnd}'))";
    }

    return $conditions;
}

function payment_uuid_v4()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function payment_http($method, $url, array $headers = [], $body = null, $certificate = null, $timeout = 25)
{
    if (!function_exists('curl_init')) {
        if ($certificate) {
            return ['ok' => false, 'status' => 0, 'json' => [], 'headers' => [], 'error' => 'A extensão cURL é necessária para este provedor.'];
        }

        $payload = $body === null
            ? null
            : (is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $options = [
            'http' => [
                'method' => strtoupper((string) $method),
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => max(3, min(60, (int) $timeout)),
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ];
        if ($payload !== null) {
            $options['http']['content'] = $payload;
        }

        $raw = @file_get_contents($url, false, stream_context_create($options));
        $responseHeaders = [];
        $status = 0;
        foreach (($http_response_header ?? []) as $index => $headerLine) {
            if ($index === 0 && preg_match('/\s(\d{3})(?:\s|$)/', $headerLine, $match)) {
                $status = (int) $match[1];
                continue;
            }
            $parts = explode(':', $headerLine, 2);
            if (count($parts) === 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }
        $lastError = error_get_last();
        $error = $raw === false ? (string) ($lastError['message'] ?? 'Falha de comunicação HTTP.') : '';
        $json = is_string($raw) ? json_decode($raw, true) : null;
        $ok = $error === '' && $status >= 200 && $status < 300;
        return ['ok' => $ok, 'status' => $status, 'json' => is_array($json) ? $json : [], 'headers' => $responseHeaders, 'error' => $error];
    }

    $curl = curl_init($url);
    $responseHeaders = [];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 7,
        CURLOPT_TIMEOUT => max(3, min(60, (int) $timeout)),
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HEADERFUNCTION => function ($curlHandle, $headerLine) use (&$responseHeaders) {
            $length = strlen($headerLine);
            $parts = explode(':', $headerLine, 2);
            if (count($parts) === 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
            return $length;
        },
    ];
    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if ($certificate) {
        $options[CURLOPT_SSLCERT] = $certificate;
        $options[CURLOPT_SSLCERTTYPE] = 'PEM';
        $password = getenv('EFI_CERTIFICATE_PASSWORD');
        if ($password !== false && $password !== '') {
            $options[CURLOPT_SSLCERTPASSWD] = $password;
        }
    }
    curl_setopt_array($curl, $options);
    $raw = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $json = is_string($raw) ? json_decode($raw, true) : null;
    $ok = $error === '' && $status >= 200 && $status < 300;
    return ['ok' => $ok, 'status' => $status, 'json' => is_array($json) ? $json : [], 'headers' => $responseHeaders, 'error' => $error];
}

function payment_safe_provider_error($response, $fallback = 'O gateway recusou a operação.')
{
    $json = $response['json'] ?? [];
    foreach (['message', 'error_description', 'error', 'detail'] as $key) {
        if (isset($json[$key]) && is_string($json[$key]) && strlen($json[$key]) <= 220) {
            return trim(strip_tags($json[$key]));
        }
    }
    return $fallback;
}

function payment_order_supports_txid()
{
    global $conn;
    static $supportsTxid = null;
    if ($supportsTxid !== null) {
        return $supportsTxid;
    }

    try {
        $column = $conn->query("SHOW COLUMNS FROM `order_list` LIKE 'txid'");
        $supportsTxid = $column && $column->num_rows > 0;
        if ($column instanceof mysqli_result) {
            $column->free();
        }
    } catch (Throwable $error) {
        $supportsTxid = false;
        error_log('[payments] txid column detection failed reason=' . $error->getMessage());
    }

    return $supportsTxid;
}

function payment_store_pix($orderId, $method, $pixCode, $reference, $expiration, $txid = '')
{
    global $conn;
    $orderId = (int) $orderId;
    $expiration = (int) $expiration;
    $emptyQr = '';
    $reference = (string) $reference;
    $txid = (string) $txid;

    try {
        if (payment_order_supports_txid()) {
            $statement = $conn->prepare('UPDATE order_list SET payment_method = ?, pix_code = ?, pix_qrcode = ?, id_mp = ?, txid = ?, order_expiration = ? WHERE id = ?');
            if (!$statement) {
                throw new RuntimeException('Não foi possível preparar o salvamento completo do PIX.');
            }
            $statement->bind_param('sssssii', $method, $pixCode, $emptyQr, $reference, $txid, $expiration, $orderId);
        } else {
            // Bancos instalados antes da integração VenoPag não possuem txid.
            // A referência principal continua salva em id_mp e permite consultar
            // e confirmar o pagamento normalmente.
            $statement = $conn->prepare('UPDATE order_list SET payment_method = ?, pix_code = ?, pix_qrcode = ?, id_mp = ?, order_expiration = ? WHERE id = ?');
            if (!$statement) {
                throw new RuntimeException('Não foi possível preparar o salvamento compatível do PIX.');
            }
            $statement->bind_param('ssssii', $method, $pixCode, $emptyQr, $reference, $expiration, $orderId);
        }

        $saved = $statement->execute();
        $statement->close();
        return $saved;
    } catch (Throwable $error) {
        error_log('[payments] pix persistence failed order=' . $orderId . ' method=' . preg_replace('/[^a-z0-9_-]/i', '', (string) $method) . ' reason=' . $error->getMessage());
        return false;
    }
}

function payment_customer_email($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    $host = preg_replace('/[^a-z0-9.-]/i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    return 'no-reply@' . ($host ?: 'localhost');
}

function payment_expiration_datetime($minutes)
{
    $minutes = max(5, min(1440, (int) $minutes));
    $timezone = new DateTimeZone('America/Sao_Paulo');
    return (new DateTimeImmutable('now', $timezone))
        ->modify('+' . $minutes . ' minutes')
        ->format('Y-m-d\\TH:i:s.vP');
}

function payment_create_pix($orderId, $amount, $name, $email, $cpf, $expiration, $phone = '')
{
    $attempt = payment_register_order_gateway($orderId, $amount);
    if (empty($attempt['ok'])) {
        return $attempt;
    }
    $provider = $attempt['provider'];

    $amount = payment_amount($amount, $provider);
    $expiration = max(5, min(1440, (int) $expiration));
    $result = call_user_func('payment_create_' . $provider, (int) $orderId, $amount, $name, $email, $cpf, $expiration, $phone);
    if (empty($result['ok'])) {
        error_log('[payments] charge creation failed provider=' . $provider . ' order=' . (int) $orderId);
    }
    return $result;
}

function payment_register_order_gateway($orderId, $amount)
{
    global $conn;

    $provider = payment_provider_for_amount($amount);
    $definitions = payment_provider_definitions();
    if (!$provider || empty($definitions[$provider]['method'])) {
        return ['ok' => false, 'message' => 'Ative um gateway de pagamento válido no painel administrativo.'];
    }

    $method = (string) $definitions[$provider]['method'];
    $orderId = (int) $orderId;
    try {
        $statement = $conn->prepare(
            "UPDATE order_list SET payment_method = ? WHERE id = ? AND status = 1"
        );
        if (!$statement) {
            throw new RuntimeException('Não foi possível preparar o vínculo do gateway.');
        }
        $statement->bind_param('si', $method, $orderId);
        $saved = $statement->execute();
        $statement->close();
        if (!$saved) {
            throw new RuntimeException('Não foi possível vincular o gateway ao pedido.');
        }
    } catch (Throwable $error) {
        error_log('[payments] gateway attempt persistence failed order=' . $orderId . ' reason=' . $error->getMessage());
        return ['ok' => false, 'message' => 'Não foi possível preparar o pagamento. Tente novamente.'];
    }

    return ['ok' => true, 'provider' => $provider, 'method' => $method];
}

function payment_create_mercadopago($orderId, $amount, $name, $email, $cpf, $expiration, $phone)
{
    $token = (string) payment_setting('mercadopago_access_token');
    if ($token === '') {
        return ['ok' => false, 'message' => 'Access Token do Mercado Pago não configurado.'];
    }
    $payload = [
        'transaction_amount' => $amount,
        'description' => 'Pedido #' . $orderId,
        'payment_method_id' => 'pix',
        'external_reference' => (string) $orderId,
        'notification_url' => rtrim(BASE_URL, '/') . '/webhook.php?notify=mercadopago',
        'date_of_expiration' => payment_expiration_datetime($expiration),
        'payer' => ['email' => payment_customer_email($email), 'first_name' => trim((string) $name)],
    ];
    $document = preg_replace('/\D+/', '', (string) $cpf);
    if (payment_customer_document_is_valid($document)) {
        $payload['payer']['identification'] = [
            'type' => strlen($document) === 14 ? 'CNPJ' : 'CPF',
            'number' => $document,
        ];
    }
    $response = payment_http('POST', 'https://api.mercadopago.com/v1/payments', [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'X-Idempotency-Key: ' . payment_uuid_v4(),
    ], $payload);
    $data = $response['json'];
    $transaction = $data['point_of_interaction']['transaction_data'] ?? [];
    if (!$response['ok'] || empty($data['id']) || empty($transaction['qr_code'])) {
        $message = payment_safe_provider_error($response, 'Não foi possível gerar o PIX no Mercado Pago.');
        error_log(
            '[payments] mercadopago charge rejected order=' . (int) $orderId
            . ' http=' . (int) ($response['status'] ?? 0)
            . ' reason=' . preg_replace('/\\s+/', ' ', $message)
        );
        return ['ok' => false, 'message' => $message];
    }
    if (!payment_store_pix($orderId, 'MercadoPago', $transaction['qr_code'], $data['id'], $expiration)) {
        return ['ok' => false, 'message' => 'O PIX foi criado, mas não pôde ser salvo.'];
    }
    return ['ok' => true, 'provider' => 'mercadopago'];
}

function payment_create_openpix($orderId, $amount, $name, $email, $cpf, $expiration, $phone)
{
    $appId = (string) payment_setting('openpix_app_id');
    if ($appId === '') {
        return ['ok' => false, 'message' => 'App ID da OpenPix não configurado.'];
    }
    $payload = [
        'correlationID' => (string) $orderId,
        'value' => (int) round($amount * 100),
        'type' => 'DYNAMIC',
        'comment' => 'Pedido #' . $orderId,
        'expiresIn' => $expiration * 60,
        'customer' => [
            'name' => trim((string) $name),
            'email' => payment_customer_email($email),
            'phone' => preg_replace('/\D+/', '', (string) $phone),
        ],
    ];
    $response = payment_http('POST', 'https://api.openpix.com.br/api/v1/charge', [
        'Authorization: ' . $appId,
        'Content-Type: application/json',
    ], $payload);
    $charge = $response['json']['charge'] ?? [];
    if (!$response['ok'] || empty($charge['brCode']) || empty($charge['globalID'])) {
        return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Não foi possível gerar o PIX na OpenPix.')];
    }
    if (!payment_store_pix($orderId, 'OpenPix', $charge['brCode'], $charge['globalID'], $expiration)) {
        return ['ok' => false, 'message' => 'O PIX foi criado, mas não pôde ser salvo.'];
    }
    return ['ok' => true, 'provider' => 'openpix'];
}

function payment_pay2m_request_token($clientId, $clientSecret)
{
    $response = payment_http('POST', 'https://portal.pay2m.com.br/api/auth/generate_token', [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/json',
    ], ['grant_type' => 'client_credentials']);
    $data = $response['json'];
    if (!$response['ok'] || empty($data['access_token'])) {
        return ['ok' => false, 'response' => $response];
    }
    return [
        'ok' => true,
        'authorization' => ($data['token_type'] ?? 'Bearer') . ' ' . $data['access_token'],
    ];
}

function payment_pay2m_credential_candidates()
{
    global $_settings;

    $pairs = [];
    $seen = [];
    $addPair = static function ($clientId, $clientSecret) use (&$pairs, &$seen) {
        $clientId = trim((string) $clientId, " \t\n\r\0\x0B\"'");
        $clientSecret = trim((string) $clientSecret, " \t\n\r\0\x0B\"'");
        if ($clientId === '' || $clientSecret === '') {
            return;
        }
        $key = hash('sha256', $clientId . "\0" . $clientSecret);
        if (isset($seen[$key])) {
            return;
        }
        $seen[$key] = true;
        $pairs[] = [$clientId, $clientSecret];
    };

    $configuredId = payment_setting('pay2m_client_id');
    $configuredSecret = payment_setting('pay2m_client_secret');
    $addPair($configuredId, $configuredSecret);
    $addPair($configuredSecret, $configuredId);

    // A legacy environment value can coexist with newer credentials saved in
    // the Plesk admin panel. Keep each source paired and try both field orders.
    if (isset($_settings) && is_object($_settings)) {
        $storedId = $_settings->info('pay2m_client_id');
        $storedSecret = $_settings->info('pay2m_client_secret');
        $addPair($storedId, $storedSecret);
        $addPair($storedSecret, $storedId);
    }

    $environmentId = getenv('PAY2M_CLIENT_ID');
    $environmentSecret = getenv('PAY2M_CLIENT_SECRET');
    $addPair($environmentId !== false ? $environmentId : '', $environmentSecret !== false ? $environmentSecret : '');
    $addPair($environmentSecret !== false ? $environmentSecret : '', $environmentId !== false ? $environmentId : '');

    return $pairs;
}

function payment_pay2m_token()
{
    $candidates = payment_pay2m_credential_candidates();
    if (!$candidates) {
        return ['ok' => false, 'message' => 'Credenciais da Pay2M não configuradas.'];
    }

    $response = [];
    foreach ($candidates as $index => $credentials) {
        $token = payment_pay2m_request_token($credentials[0], $credentials[1]);
        if ($token['ok']) {
            if ($index > 0) {
                error_log('[payments] Pay2M credentials accepted from an alternate configured source/order.');
            }
            return $token;
        }
        $response = $token['response'] ?? $response;
    }

    return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Credenciais da Pay2M recusadas.')];
}

function payment_pay2m_register_webhook($authorization)
{
    $secret = (string) payment_setting('pay2m_webhook_secret');
    if ($secret === '') {
        return ['ok' => false, 'message' => 'Proteção do webhook Pay2M não configurada. Salve novamente o gateway.'];
    }
    $response = payment_http('POST', 'https://portal.pay2m.com.br/api/v1/webhooks', [
        'Authorization: ' . $authorization,
        'Content-Type: application/json',
    ], [
        'url' => rtrim(BASE_URL, '/') . '/webhook.php?notify=pay2m',
        'authorization' => $secret,
    ]);
    if (($response['status'] ?? 0) === 409) {
        $response['ok'] = true;
    }
    return $response;
}

function payment_create_pay2m($orderId, $amount, $name, $email, $cpf, $expiration, $phone)
{
    $token = payment_pay2m_token();
    if (empty($token['ok'])) {
        return $token;
    }
    $payload = [
        'value' => $amount,
        'external_reference' => (string) $orderId,
        'expiration_time' => min(3600, $expiration * 60),
        'payer_message' => 'Pedido #' . $orderId,
    ];
    $document = preg_replace('/\D+/', '', (string) $cpf);
    if (payment_customer_document_is_valid($document)) {
        $payload['generator_name'] = trim((string) $name);
        $payload['generator_document'] = $document;
    }
    $response = payment_http('POST', 'https://portal.pay2m.com.br/api/v1/pix/qrcode', [
        'Authorization: ' . $token['authorization'],
        'Content-Type: application/json',
    ], $payload);
    $data = $response['json'];
    if (!$response['ok'] || empty($data['content']) || empty($data['reference_code'])) {
        return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Não foi possível gerar o PIX na Pay2M.')];
    }
    if (!payment_store_pix($orderId, 'Pay2m', $data['content'], $data['reference_code'], $expiration)) {
        return ['ok' => false, 'message' => 'O PIX foi criado, mas não pôde ser salvo.'];
    }
    return ['ok' => true, 'provider' => 'pay2m'];
}

function payment_efi_certificate()
{
    static $path = null;
    if ($path !== null) {
        return $path;
    }
    $encoded = getenv('EFI_CERTIFICATE_BASE64');
    if ($encoded !== false && $encoded !== '') {
        $contents = base64_decode($encoded, true);
        if ($contents === false || strpos($contents, 'BEGIN') === false) {
            return null;
        }
        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'efi-' . hash('sha256', $encoded) . '.pem';
        if (!is_file($path) && file_put_contents($path, $contents, LOCK_EX) === false) {
            return null;
        }
        @chmod($path, 0600);
        return $path;
    }
    $legacy = defined('BASE_APP') ? BASE_APP . 'pagamentos.pem' : dirname(__DIR__) . '/pagamentos.pem';
    return is_file($legacy) ? $legacy : null;
}

function payment_efi_base_url()
{
    $custom = getenv('EFI_PIX_API_URL');
    return rtrim($custom !== false && $custom !== '' ? $custom : 'https://pix.api.efipay.com.br', '/');
}

function payment_efi_token()
{
    $clientId = (string) payment_setting('gerencianet_client_id');
    $clientSecret = (string) payment_setting('gerencianet_client_secret');
    $certificate = payment_efi_certificate();
    if ($clientId === '' || $clientSecret === '' || !$certificate) {
        return ['ok' => false, 'message' => 'A Efí exige Client ID, Client Secret e o certificado PIX (pagamentos.pem ou EFI_CERTIFICATE_BASE64).'];
    }
    $response = payment_http('POST', payment_efi_base_url() . '/oauth/token', [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/json',
    ], ['grant_type' => 'client_credentials'], $certificate);
    $data = $response['json'];
    if (!$response['ok'] || empty($data['access_token'])) {
        return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Credenciais ou certificado da Efí recusados.')];
    }
    return ['ok' => true, 'authorization' => ($data['token_type'] ?? 'Bearer') . ' ' . $data['access_token'], 'certificate' => $certificate];
}

function payment_create_gerencianet($orderId, $amount, $name, $email, $cpf, $expiration, $phone)
{
    $pixKey = (string) payment_setting('gerencianet_pix_key');
    $token = payment_efi_token();
    if (empty($token['ok'])) {
        return $token;
    }
    if ($pixKey === '') {
        return ['ok' => false, 'message' => 'Chave PIX da Efí não configurada.'];
    }
    $headers = ['Authorization: ' . $token['authorization'], 'Content-Type: application/json'];
    $webhook = payment_http('PUT', payment_efi_base_url() . '/v2/webhook/' . rawurlencode($pixKey), $headers, [
        'webhookUrl' => rtrim(BASE_URL, '/') . '/webhook.php?notify=gerencianet',
    ], $token['certificate']);
    if (!$webhook['ok']) {
        return ['ok' => false, 'message' => payment_safe_provider_error($webhook, 'Não foi possível registrar o webhook da Efí.')];
    }
    $txid = bin2hex(random_bytes(16));
    $payload = [
        'calendario' => ['expiracao' => $expiration * 60],
        'valor' => ['original' => number_format($amount, 2, '.', '')],
        'chave' => $pixKey,
        'solicitacaoPagador' => 'Pedido #' . $orderId,
        'infoAdicionais' => [['nome' => 'pedido', 'valor' => (string) $orderId]],
    ];
    $charge = payment_http('PUT', payment_efi_base_url() . '/v2/cob/' . $txid, $headers, $payload, $token['certificate']);
    $locationId = $charge['json']['loc']['id'] ?? null;
    if (!$charge['ok'] || !$locationId) {
        return ['ok' => false, 'message' => payment_safe_provider_error($charge, 'Não foi possível gerar a cobrança na Efí.')];
    }
    $qr = payment_http('GET', payment_efi_base_url() . '/v2/loc/' . rawurlencode((string) $locationId) . '/qrcode', $headers, null, $token['certificate']);
    $pixCode = $qr['json']['qrcode'] ?? '';
    if (!$qr['ok'] || $pixCode === '') {
        return ['ok' => false, 'message' => payment_safe_provider_error($qr, 'A Efí não retornou o código PIX.')];
    }
    if (!payment_store_pix($orderId, 'Gerencianet', $pixCode, $txid, $expiration, $txid)) {
        return ['ok' => false, 'message' => 'O PIX foi criado, mas não pôde ser salvo.'];
    }
    return ['ok' => true, 'provider' => 'gerencianet'];
}

function payment_paggue_token()
{
    $key = (string) payment_setting('paggue_client_key');
    $secret = (string) payment_setting('paggue_client_secret');
    if ($key === '' || $secret === '') {
        return ['ok' => false, 'message' => 'Credenciais da Paggue não configuradas.'];
    }
    $response = payment_http('POST', 'https://ms.paggue.io/auth/v1/token', ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
        'client_key' => $key,
        'client_secret' => $secret,
    ]));
    $data = $response['json'];
    $company = $data['user']['companies'][0]['id'] ?? null;
    if (!$response['ok'] || empty($data['access_token']) || !$company) {
        return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Credenciais da Paggue recusadas.')];
    }
    return ['ok' => true, 'access_token' => $data['access_token'], 'company_id' => $company];
}

function payment_create_paggue($orderId, $amount, $name, $email, $cpf, $expiration, $phone)
{
    $token = payment_paggue_token();
    if (empty($token['ok'])) {
        return $token;
    }
    $payload = [
        'payer_name' => trim((string) $name),
        'amount' => (int) round($amount * 100),
        'external_id' => (string) $orderId,
        'description' => 'Pedido #' . $orderId,
    ];
    $rawPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $secret = (string) payment_setting('paggue_client_secret');
    $response = payment_http('POST', 'https://ms.paggue.io/cashin/api/billing_order', [
        'Authorization: Bearer ' . $token['access_token'],
        'X-Company-ID: ' . $token['company_id'],
        'Signature: ' . hash_hmac('sha256', $rawPayload, $secret),
        'Content-Type: application/json',
    ], $rawPayload);
    $data = $response['json'];
    if (!$response['ok'] || empty($data['payment']) || empty($data['hash'])) {
        return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Não foi possível gerar o PIX na Paggue.')];
    }
    if (!payment_store_pix($orderId, 'Paggue', $data['payment'], $data['hash'], $expiration)) {
        return ['ok' => false, 'message' => 'O PIX foi criado, mas não pôde ser salvo.'];
    }
    return ['ok' => true, 'provider' => 'paggue'];
}

function payment_venopag_base_url()
{
    $custom = getenv('VENOPAG_API_URL');
    return rtrim($custom !== false && $custom !== '' ? $custom : 'https://venopagments.com', '/');
}

function payment_venopag_credentials()
{
    $environmentClientId = getenv('VENOPAG_CLIENT_ID');
    $environmentClientSecret = getenv('VENOPAG_CLIENT_SECRET');
    $clientId = trim((string) ($environmentClientId !== false && $environmentClientId !== ''
        ? $environmentClientId
        : payment_setting('venopag_client_id')));
    $clientSecret = trim((string) ($environmentClientSecret !== false && $environmentClientSecret !== ''
        ? $environmentClientSecret
        : payment_setting('venopag_client_secret')));
    if ($clientId === '' || $clientSecret === '') {
        return ['ok' => false, 'message' => 'Client ID e Client Secret da VenoPag não configurados.'];
    }
    return [
        'ok' => true,
        'headers' => [
            'X-Client-Id: ' . $clientId,
            'X-Client-Secret: ' . $clientSecret,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
    ];
}

function payment_venopag_webhook_secret()
{
    $environmentSecret = getenv('VENOPAG_WEBHOOK_SECRET');
    return trim((string) ($environmentSecret !== false && $environmentSecret !== ''
        ? $environmentSecret
        : payment_setting('venopag_webhook_secret')));
}

function payment_venopag_webhook_url()
{
    $secret = payment_venopag_webhook_secret();
    if ($secret === '') {
        return '';
    }
    return rtrim(BASE_URL, '/') . '/webhook.php?notify=venopag&token=' . rawurlencode($secret);
}

function payment_venopag_request($method, $path, $body = null, $timeout = 25)
{
    $safeMethod = strtoupper((string) $method);
    $safePath = (string) (parse_url((string) $path, PHP_URL_PATH) ?: '/');
    $credentials = payment_venopag_credentials();
    if (empty($credentials['ok'])) {
        error_log('[payments] venopag request blocked method=' . $safeMethod . ' path=' . $safePath . ' reason=credentials_missing');
        return $credentials + ['status' => 0, 'json' => [], 'headers' => []];
    }
    $response = payment_http($method, payment_venopag_base_url() . $path, $credentials['headers'], $body, null, $timeout);
    if (!array_key_exists('ok', $response['json'] ?? [])) {
        $transportReason = ($response['error'] ?? '') !== '' ? 'transport_error' : 'invalid_json';
        error_log('[payments] venopag invalid response method=' . $safeMethod . ' path=' . $safePath . ' http=' . (int) ($response['status'] ?? 0) . ' reason=' . $transportReason);
        $response['ok'] = false;
        $response['message'] = 'A VenoPag retornou uma resposta inválida.';
        return $response;
    }
    $response['ok'] = !empty($response['json']['ok']);
    if (!$response['ok']) {
        $appCode = (int) ($response['headers']['x-app-error-code'] ?? 0);
        $response['app_error_code'] = $appCode;
        $reason = 'provider_rejected';
        if ($appCode === 401) {
            $reason = 'credentials_rejected';
        } elseif ($appCode === 403) {
            $reason = 'account_or_permission_blocked';
        } elseif ($appCode === 400 || $appCode === 422) {
            $reason = 'request_rejected';
        } elseif ($appCode === 502 || $appCode === 503) {
            $reason = 'provider_unavailable';
        }
        error_log('[payments] venopag request refused method=' . $safeMethod . ' path=' . $safePath . ' http=' . (int) ($response['status'] ?? 0) . ' app_code=' . $appCode . ' reason=' . $reason);
        $response['message'] = payment_safe_provider_error($response, 'A VenoPag recusou a operação.');
    }
    return $response;
}

function payment_venopag_consult($requestNumber, $timeout = 7)
{
    $requestNumber = trim((string) $requestNumber);
    if ($requestNumber === '') {
        return ['ok' => false, 'message' => 'Referência VenoPag ausente.', 'json' => [], 'status' => 0];
    }
    return payment_venopag_request('GET', '/api/consult-transaction?request_number=' . rawurlencode($requestNumber), null, $timeout);
}

function payment_venopag_consult_transaction($transactionId, $timeout = 7)
{
    $transactionId = trim((string) $transactionId);
    if ($transactionId === '') {
        return ['ok' => false, 'message' => 'Referencia VenoPag ausente.', 'json' => [], 'status' => 0];
    }
    return payment_venopag_request('GET', '/api/consult-transaction?transaction_id=' . rawurlencode($transactionId), null, $timeout);
}

function payment_gateway_slot_acquire($provider, $slots = 2)
{
    $slots = max(1, (int) $slots);
    $lockRoot = rtrim((string) sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    $siteKey = hash('sha256', (string) ($_SERVER['DOCUMENT_ROOT'] ?? __DIR__));
    $provider = preg_replace('/[^a-z0-9_-]+/i', '-', (string) $provider);

    for ($slot = 0; $slot < $slots; $slot++) {
        $path = $lockRoot . DIRECTORY_SEPARATOR . 'jnsalles-gateway-' . $siteKey . '-' . $provider . '-' . $slot . '.lock';
        $handle = @fopen($path, 'c');
        if (is_resource($handle) && @flock($handle, LOCK_EX | LOCK_NB)) {
            return $handle;
        }
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    return null;
}

function payment_gateway_slot_release($handle)
{
    if (is_resource($handle)) {
        @flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function payment_create_venopag($orderId, $amount, $name, $email, $cpf, $expiration, $phone)
{
    $chargeAmount = round((float) $amount, 2);
    $minimumAmount = payment_venopag_minimum_amount();
    if ($chargeAmount < $minimumAmount) {
        return [
            'ok' => false,
            'message' => 'O valor mínimo para pagar com VenoPag é R$ ' . number_format($minimumAmount, 2, ',', '.') . '. Adicione mais títulos e tente novamente.',
        ];
    }
    $document = preg_replace('/\D+/', '', (string) $cpf);
    if (!payment_customer_document_is_valid($document)) {
        $document = payment_venopag_default_document();
        if (payment_customer_document_is_valid($document)) {
            error_log('[payments] venopag default document used order=' . (int) $orderId . ' reason=customer_document_missing_or_invalid');
        } else {
            $document = '';
            error_log('[payments] venopag document omitted order=' . (int) $orderId . ' reason=no_valid_document_configured');
        }
    }
    $webhookUrl = payment_venopag_webhook_url();
    if ($webhookUrl === '') {
        return ['ok' => false, 'message' => 'Proteção do webhook VenoPag não configurada. Salve novamente o gateway.'];
    }
    $payload = [
        'amount' => $chargeAmount,
        'name' => trim((string) $name),
        'description' => 'Pedido #' . (int) $orderId,
        'webhook_url' => $webhookUrl,
    ];
    if ($document !== '') {
        $payload['document'] = $document;
    }
    // Uma resposta lenta do provedor não pode ocupar todos os processos PHP e
    // derrubar a campanha. Duas criações simultâneas mantêm o site responsivo.
    $gatewaySlot = payment_gateway_slot_acquire('venopag-cashin', 2);
    if (!is_resource($gatewaySlot)) {
        return [
            'ok' => false,
            'message' => 'Muitos pagamentos estão sendo gerados neste momento. Aguarde alguns segundos e tente novamente.',
        ];
    }

    try {
        // A VenoPag pode levar mais de 30 segundos para devolver o PIX. Aguarde
        // a resposta completa para não abandonar uma cobrança já criada.
        $response = payment_venopag_request('POST', '/api/cashin', $payload, 45);
        if (empty($response['ok']) && (int) ($response['app_error_code'] ?? 0) === 502) {
            // O código 502 informa que a cobrança não foi criada; somente nesse
            // caso é seguro fazer uma nova tentativa curta.
            error_log('[payments] venopag transient failure retrying order=' . (int) $orderId);
            usleep(300000);
            $response = payment_venopag_request('POST', '/api/cashin', $payload, 20);
        }
    } finally {
        payment_gateway_slot_release($gatewaySlot);
    }
    $data = $response['json'] ?? [];
    if (empty($response['ok']) || ($data['status'] ?? '') !== 'pending' || empty($data['copyPaste']) || empty($data['request_number'])) {
        $code = (int) ($response['app_error_code'] ?? 0);
        $message = $response['message'] ?? 'Não foi possível gerar o PIX na VenoPag.';
        if ($message === 'Falha ao gerar PIX') {
            if (in_array($code, [502, 503], true)) {
                $message = 'A VenoPag está temporariamente indisponível. Tente novamente em instantes.';
            } elseif ($code === 403) {
                $message = 'A conta VenoPag não está habilitada para cash-in. Verifique o cadastro, KYC e a permissão cashin.';
            } elseif ($code === 401) {
                $message = 'As credenciais da VenoPag foram recusadas.';
            } else {
                $message = 'A VenoPag recusou os dados do pagamento. Tente novamente.';
            }
        }
        return ['ok' => false, 'message' => $message];
    }
    if (!payment_store_pix($orderId, 'VenoPag', $data['copyPaste'], $data['request_number'], (int) ($data['expire_minutes'] ?? $expiration), (string) ($data['transaction_id'] ?? ''))) {
        return ['ok' => false, 'message' => 'O PIX foi criado, mas não pôde ser salvo.'];
    }
    return ['ok' => true, 'provider' => 'venopag'];
}

function payment_test_gateway($provider)
{
    if ($provider === 'split') {
        foreach (['venopag', 'pay2m'] as $splitProvider) {
            $splitResult = payment_test_gateway($splitProvider);
            if (($splitResult['status'] ?? '') !== 'success') {
                return ['status' => 'failed', 'msg' => ($splitProvider === 'venopag' ? 'VenoPag: ' : 'Pay2M: ') . ($splitResult['msg'] ?? 'credenciais recusadas.')];
            }
        }
        return ['status' => 'success', 'msg' => 'VenoPag e Pay2M validadas. A divisão por valor está pronta.'];
    }
    if (!isset(payment_provider_definitions()[$provider])) {
        return ['status' => 'failed', 'msg' => 'Selecione um gateway válido.'];
    }
    if ($provider === 'mercadopago') {
        $token = (string) payment_setting('mercadopago_access_token');
        $result = $token === '' ? ['ok' => false] : payment_http('GET', 'https://api.mercadopago.com/users/me', ['Authorization: Bearer ' . $token]);
    } elseif ($provider === 'openpix') {
        $appId = (string) payment_setting('openpix_app_id');
        $result = $appId === '' ? ['ok' => false] : payment_http('GET', 'https://api.openpix.com.br/api/v1/charge?limit=1', ['Authorization: ' . $appId]);
    } elseif ($provider === 'pay2m') {
        $result = payment_pay2m_token();
    } elseif ($provider === 'gerencianet') {
        $result = payment_efi_token();
    } elseif ($provider === 'venopag') {
        $credentials = payment_venopag_credentials();
        if (empty($credentials['ok'])) {
            $result = $credentials;
        } else {
            $probe = payment_venopag_request('GET', '/api/consult-transaction?request_number=codex_connection_test');
            $appCode = (int) ($probe['app_error_code'] ?? 0);
            $message = (string) ($probe['message'] ?? '');
            $acceptedProbe = $appCode === 404 || stripos($message, 'não encontrado') !== false;
            $result = $acceptedProbe ? ['ok' => true] : $probe;
        }
    } else {
        $result = payment_paggue_token();
    }
    if (!empty($result['ok'])) {
        return ['status' => 'success', 'msg' => 'Conexão validada. Nenhuma cobrança foi criada.'];
    }
    return ['status' => 'failed', 'msg' => $result['message'] ?? 'Credenciais recusadas ou incompletas.'];
}

function payment_request_headers()
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $normalized = [];
    foreach ($headers as $name => $value) {
        $normalized[strtolower($name)] = trim((string) $value);
    }
    foreach ($_SERVER as $name => $value) {
        if (strpos($name, 'HTTP_') === 0) {
            $normalized[strtolower(str_replace('_', '-', substr($name, 5)))] = trim((string) $value);
        }
    }
    return $normalized;
}

function payment_find_order_id_by_reference($column, $reference)
{
    global $conn;
    if (!in_array($column, ['id_mp', 'txid'], true) || trim((string) $reference) === '') {
        return '';
    }
    $statement = $conn->prepare('SELECT id FROM order_list WHERE ' . $column . ' = ? LIMIT 1');
    $reference = (string) $reference;
    $statement->bind_param('s', $reference);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $statement->close();
    return $row ? (string) $row['id'] : '';
}

function payment_validate_mercadopago_signature($dataId, array $headers)
{
    $secret = (string) payment_setting('mercadopago_webhook_secret');
    if ($secret === '') {
        return true;
    }
    $signature = $headers['x-signature'] ?? '';
    $requestId = $headers['x-request-id'] ?? '';
    if ($signature === '' && $requestId === '') {
        return true;
    }
    $parts = [];
    foreach (explode(',', $signature) as $part) {
        $pair = array_map('trim', explode('=', $part, 2));
        if (count($pair) === 2) {
            $parts[$pair[0]] = $pair[1];
        }
    }
    if (empty($parts['ts']) || empty($parts['v1']) || $requestId === '' || $dataId === '') {
        return false;
    }
    $manifest = 'id:' . strtolower((string) $dataId) . ';request-id:' . $requestId . ';ts:' . $parts['ts'] . ';';
    return hash_equals($parts['v1'], hash_hmac('sha256', $manifest, $secret));
}

function payment_verify_webhook($provider, $raw, array $headers)
{
    $event = json_decode($raw, true);

    if ($provider === 'mercadopago') {
        $event = is_array($event) ? $event : [];
        $paymentId = $event['data']['id']
            ?? $event['id']
            ?? ($_GET['data.id'] ?? ($_GET['data_id'] ?? ($_GET['id'] ?? '')));
        $paymentId = trim((string) $paymentId);
        if ($paymentId === '' || !ctype_digit($paymentId)) {
            return ['ok' => false, 'http' => 400, 'message' => 'Identificador do pagamento ausente.'];
        }
        if (!payment_validate_mercadopago_signature($paymentId, $headers)) {
            return ['ok' => false, 'http' => 401, 'message' => 'Assinatura inválida.'];
        }
        $token = (string) payment_setting('mercadopago_access_token');
        $response = payment_http('GET', 'https://api.mercadopago.com/v1/payments/' . rawurlencode((string) $paymentId), ['Authorization: Bearer ' . $token]);
        $data = $response['json'];
        if (!$response['ok'] || ($data['status'] ?? '') !== 'approved' || ($data['payment_method_id'] ?? '') !== 'pix') {
            return ['ok' => false, 'http' => 202, 'message' => 'Pagamento ainda não aprovado.'];
        }
        $reference = $data['id'] ?? $paymentId;
        $orderId = $data['external_reference'] ?? payment_find_order_id_by_reference('id_mp', $reference);
        return ['ok' => true, 'order_id' => $orderId, 'amount' => $data['transaction_amount'] ?? 0, 'reference' => $reference];
    }

    if (!is_array($event)) {
        return ['ok' => false, 'http' => 400, 'message' => 'JSON inválido.'];
    }

    if ($provider === 'openpix') {
        $correlation = $event['charge']['correlationID'] ?? '';
        $appId = (string) payment_setting('openpix_app_id');
        $response = payment_http('GET', 'https://api.openpix.com.br/api/v1/charge/' . rawurlencode((string) $correlation), ['Authorization: ' . $appId]);
        $charge = $response['json']['charge'] ?? [];
        if (!$response['ok'] || !in_array(strtoupper((string) ($charge['status'] ?? '')), ['COMPLETED', 'PAID'], true)) {
            return ['ok' => false, 'http' => 202, 'message' => 'Pagamento ainda não aprovado.'];
        }
        $reference = $charge['globalID'] ?? '';
        $orderId = $charge['correlationID'] ?? payment_find_order_id_by_reference('id_mp', $reference);
        return ['ok' => true, 'order_id' => $orderId, 'amount' => ((float) ($charge['value'] ?? 0)) / 100, 'reference' => $reference];
    }

    if ($provider === 'venopag') {
        $expectedSecret = payment_venopag_webhook_secret();
        $receivedSecret = trim((string) ($_GET['token'] ?? ''));
        if ($expectedSecret === '' || $receivedSecret === '' || !hash_equals($expectedSecret, $receivedSecret)) {
            return ['ok' => false, 'http' => 401, 'message' => 'Webhook não autorizado.'];
        }
        if (strtolower((string) ($event['type'] ?? '')) !== 'cashin') {
            return ['ok' => false, 'http' => 200, 'message' => 'Evento VenoPag ignorado.'];
        }
        $requestNumber = trim((string) ($event['request_number'] ?? ''));
        if ($requestNumber === '') {
            return ['ok' => false, 'http' => 400, 'message' => 'Referência VenoPag ausente.'];
        }
        $consult = payment_venopag_consult($requestNumber);
        $data = $consult['json'] ?? [];
        if (empty($consult['ok'])) {
            return ['ok' => false, 'http' => 502, 'message' => 'Falha ao confirmar a transação na VenoPag.'];
        }
        $status = strtolower((string) ($data['status'] ?? ''));
        if ($status !== 'confirmed') {
            $validNonPaid = ['pending', 'expired', 'canceled', 'contested', 'chargedback', 'refunded'];
            return [
                'ok' => false,
                'http' => 200,
                'message' => in_array($status, $validNonPaid, true) ? 'Pagamento VenoPag não liberável.' : 'Status VenoPag desconhecido.',
            ];
        }
        $reference = (string) ($data['request_number'] ?? $requestNumber);
        return [
            'ok' => true,
            'order_id' => payment_find_order_id_by_reference('id_mp', $reference),
            'amount' => $data['amount'] ?? 0,
            'reference' => $reference,
        ];
    }

    if ($provider === 'pay2m') {
        $secret = (string) payment_setting('pay2m_webhook_secret');
        $authorization = $headers['authorization'] ?? '';
        if ($secret === '' || !hash_equals($secret, preg_replace('/^Bearer\s+/i', '', $authorization))) {
            // O status e o valor sempre são consultados novamente na Pay2M.
            // Isso mantém a confirmação segura mesmo após trocar o webhook de outro site.
            error_log('[payments] pay2m webhook authorization mismatch; provider verification required');
        }
        $reference = $event['message']['reference_code'] ?? '';
        $token = payment_pay2m_token();
        if (empty($token['ok'])) {
            return ['ok' => false, 'http' => 502, 'message' => 'Falha ao consultar a Pay2M.'];
        }
        $response = payment_http('GET', 'https://portal.pay2m.com.br/api/v1/pix/qrcode/' . rawurlencode((string) $reference), ['Authorization: ' . $token['authorization']]);
        $data = $response['json'];
        if (!$response['ok'] || strtolower((string) ($data['status'] ?? '')) !== 'paid') {
            return ['ok' => false, 'http' => 202, 'message' => 'Pagamento ainda não aprovado.'];
        }
        $reference = $data['reference_code'] ?? $reference;
        $orderId = $data['external_reference'] ?? payment_find_order_id_by_reference('id_mp', $reference);
        return ['ok' => true, 'order_id' => $orderId, 'amount' => $data['value'] ?? 0, 'reference' => $reference];
    }

    if ($provider === 'gerencianet') {
        $pix = $event['pix'][0] ?? [];
        $txid = $pix['txid'] ?? '';
        $token = payment_efi_token();
        if (empty($token['ok']) || $txid === '') {
            return ['ok' => false, 'http' => 502, 'message' => 'Falha ao consultar a Efí.'];
        }
        $response = payment_http('GET', payment_efi_base_url() . '/v2/cob/' . rawurlencode((string) $txid), [
            'Authorization: ' . $token['authorization'], 'Content-Type: application/json',
        ], null, $token['certificate']);
        $data = $response['json'];
        if (!$response['ok'] || strtoupper((string) ($data['status'] ?? '')) !== 'CONCLUIDA') {
            return ['ok' => false, 'http' => 202, 'message' => 'Pagamento ainda não aprovado.'];
        }
        $orderId = '';
        foreach (($data['infoAdicionais'] ?? []) as $info) {
            if (($info['nome'] ?? '') === 'pedido') {
                $orderId = $info['valor'] ?? '';
            }
        }
        if ($orderId === '') {
            $orderId = payment_find_order_id_by_reference('txid', $txid);
        }
        return ['ok' => true, 'order_id' => $orderId, 'amount' => $data['valor']['original'] ?? ($pix['valor'] ?? 0), 'reference' => $txid];
    }

    if ($provider === 'paggue') {
        $secret = (string) payment_setting('paggue_client_secret');
        $signature = $headers['signature'] ?? '';
        if ($secret === '' || $signature === '' || !hash_equals($signature, hash_hmac('sha256', $raw, $secret))) {
            return ['ok' => false, 'http' => 401, 'message' => 'Assinatura inválida.'];
        }
        $status = $event['status'] ?? ($event['payment']['status'] ?? null);
        if (!in_array((string) $status, ['1', 'paid', 'PAID'], true)) {
            return ['ok' => false, 'http' => 202, 'message' => 'Pagamento ainda não aprovado.'];
        }
        $reference = $event['hash'] ?? ($event['payment']['hash'] ?? '');
        $orderId = $event['external_id'] ?? ($event['payment']['external_id'] ?? payment_find_order_id_by_reference('id_mp', $reference));
        return [
            'ok' => true,
            'order_id' => $orderId,
            'amount' => ((float) ($event['amount'] ?? ($event['payment']['amount'] ?? 0))) / 100,
            'reference' => $reference,
        ];
    }

    return ['ok' => false, 'http' => 404, 'message' => 'Gateway desconhecido.'];
}

function payment_mark_order_paid($provider, array $verified)
{
    global $conn, $_settings;
    $definitions = payment_provider_definitions();
    $orderId = filter_var($verified['order_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$orderId || !isset($definitions[$provider])) {
        return ['ok' => false, 'http' => 400, 'message' => 'Pedido inválido.'];
    }

    $conn->begin_transaction();
    try {
        $statement = $conn->prepare('SELECT o.status, o.product_id, o.total_amount, o.quantity, o.payment_method, o.referral_id, o.order_numbers, p.qty_numbers, c.firstname, c.lastname, c.phone FROM order_list o INNER JOIN customer_list c ON c.id = o.customer_id INNER JOIN product_list p ON p.id = o.product_id WHERE o.id = ? FOR UPDATE');
        $statement->bind_param('i', $orderId);
        $statement->execute();
        $result = $statement->get_result();
        $order = $result ? $result->fetch_assoc() : null;
        $statement->close();
        if (!$order) {
            throw new RuntimeException('Pedido não encontrado.', 404);
        }
        if ((string) $order['payment_method'] !== $definitions[$provider]['method']) {
            throw new RuntimeException('O gateway não corresponde ao pedido.', 409);
        }
        $expected = payment_amount($order['total_amount'], $provider);
        if (abs($expected - (float) ($verified['amount'] ?? 0)) > 0.01) {
            throw new RuntimeException('O valor recebido não corresponde ao pedido.', 409);
        }
        if ((int) $order['status'] === 2) {
            if (!empty($verified['reference'])) {
                $reference = (string) $verified['reference'];
                $referenceUpdate = $conn->prepare('UPDATE order_list SET id_mp = ? WHERE id = ? AND (id_mp IS NULL OR id_mp = \'\')');
                $referenceUpdate->bind_param('si', $reference, $orderId);
                $referenceUpdate->execute();
                $referenceUpdate->close();
            }
            $conn->commit();
            return ['ok' => true, 'already_processed' => true, 'message' => 'Pagamento já processado.'];
        }
        $previousStatus = (int) $order['status'];
        $recoveringVenoPag = $provider === 'venopag' && $previousStatus === 3;
        if ($previousStatus !== 1 && !$recoveringVenoPag) {
            throw new RuntimeException('O pedido não está pendente.', 409);
        }

        // A VenoPag pode confirmar o PIX poucos segundos depois do prazo local.
        // Restaure um pedido expirado somente apos a consulta autenticada e sem
        // permitir que duas compras ativas fiquem com a mesma cota.
        $restoredNumbers = (string) ($order['order_numbers'] ?? '');
        if ($recoveringVenoPag) {
            $used = [];
            $usedStatement = $conn->prepare('SELECT order_numbers FROM order_list WHERE product_id = ? AND id <> ? AND status <> 3 FOR UPDATE');
            $productIdForLock = (int) $order['product_id'];
            $usedStatement->bind_param('ii', $productIdForLock, $orderId);
            $usedStatement->execute();
            $usedResult = $usedStatement->get_result();
            while ($usedRow = $usedResult->fetch_assoc()) {
                foreach (explode(',', (string) ($usedRow['order_numbers'] ?? '')) as $usedNumber) {
                    $usedNumber = trim($usedNumber);
                    if ($usedNumber !== '' && ctype_digit($usedNumber)) {
                        $used[(int) $usedNumber] = true;
                    }
                }
            }
            $usedStatement->close();

            $originalNumbers = [];
            $hasConflict = false;
            foreach (explode(',', $restoredNumbers) as $originalNumber) {
                $originalNumber = trim($originalNumber);
                if ($originalNumber === '' || !ctype_digit($originalNumber)) {
                    continue;
                }
                $numericNumber = (int) $originalNumber;
                $originalNumbers[$numericNumber] = true;
                if (isset($used[$numericNumber])) {
                    $hasConflict = true;
                }
            }

            $quantityToRestore = max(0, (int) $order['quantity']);
            if ($hasConflict || count($originalNumbers) !== $quantityToRestore) {
                $totalNumbers = max(0, (int) $order['qty_numbers']);
                if ($totalNumbers <= 0 || ($totalNumbers - count($used)) < $quantityToRestore) {
                    throw new RuntimeException('Pagamento confirmado, mas nao ha cotas livres suficientes para restaurar o pedido.', 409);
                }
                $replacement = [];
                for ($candidate = 0; $candidate < $totalNumbers && count($replacement) < $quantityToRestore; $candidate++) {
                    if (!isset($used[$candidate])) {
                        $replacement[] = $candidate;
                    }
                }
                if (count($replacement) !== $quantityToRestore) {
                    throw new RuntimeException('Pagamento confirmado, mas nao foi possivel reservar novas cotas.', 409);
                }
                $width = max(1, strlen((string) ($totalNumbers - 1)));
                $replacement = array_map(static function ($number) use ($width) {
                    return str_pad((string) $number, $width, '0', STR_PAD_LEFT);
                }, $replacement);
                $restoredNumbers = implode(',', $replacement) . ',';
            }
        }

        $paidAt = payment_local_datetime();
        $reference = trim((string) ($verified['reference'] ?? ''));
        $updated = $conn->prepare("UPDATE order_list SET status = 2, date_updated = ?, whatsapp_status = '', order_numbers = ?, id_mp = CASE WHEN ? <> '' THEN ? ELSE id_mp END WHERE id = ? AND status = ?");
        $updated->bind_param('ssssii', $paidAt, $restoredNumbers, $reference, $reference, $orderId, $previousStatus);
        $updated->execute();
        if ($updated->affected_rows !== 1) {
            throw new RuntimeException('O pedido já foi alterado.', 409);
        }
        $updated->close();

        $quantity = max(0, (int) $order['quantity']);
        $productId = (int) $order['product_id'];
        $product = $conn->prepare('UPDATE product_list SET pending_numbers = GREATEST(0, CAST(pending_numbers AS SIGNED) - ?), paid_numbers = CAST(paid_numbers AS SIGNED) + ? WHERE id = ?');
        $pendingDecrease = $previousStatus === 1 ? $quantity : 0;
        $product->bind_param('iii', $pendingDecrease, $quantity, $productId);
        $product->execute();
        if ($product->affected_rows < 1) {
            throw new RuntimeException('Campanha não encontrada.', 409);
        }
        $product->close();

        $referralCode = (string) ($order['referral_id'] ?? '');
        if ($referralCode !== '' && $referralCode !== '0') {
            $referral = $conn->prepare('SELECT percentage FROM referral WHERE referral_code = ? AND status = 1 LIMIT 1');
            $referral->bind_param('s', $referralCode);
            $referral->execute();
            $referralResult = $referral->get_result();
            $row = $referralResult ? $referralResult->fetch_assoc() : null;
            $referral->close();
            if ($row) {
                $commission = round(((float) $order['total_amount']) * ((float) $row['percentage']) / 100, 2);
                $affiliate = $conn->prepare('UPDATE referral SET amount_pending = amount_pending + ? WHERE referral_code = ? AND status = 1');
                $affiliate->bind_param('ds', $commission, $referralCode);
                $affiliate->execute();
                $affiliate->close();
            }
        }
        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        $http = (int) $error->getCode();
        if ($http < 400 || $http > 599) {
            $http = 500;
        }
        error_log('[payments] approval rejected provider=' . $provider . ' order=' . (int) $orderId);
        return ['ok' => false, 'http' => $http, 'message' => $http === 500 ? 'Não foi possível processar o pagamento.' : $error->getMessage()];
    }

    try {
        $phone = '55' . preg_replace('/\D+/', '', (string) $order['phone']);
        $eventData = [
            'first_name' => $order['firstname'], 'last_name' => $order['lastname'],
            'phone' => $phone, 'id' => $orderId, 'total_amount' => $order['total_amount'],
        ];
        if (function_exists('send_event_pixel')) {
            send_event_pixel('Purchase', $eventData);
        }
        if (function_exists('order_email')) {
            order_email($_settings->info('email_purchase'), '[' . $_settings->info('name') . '] - Pagamento aprovado', $orderId);
        }
    } catch (Throwable $sideEffectError) {
        error_log('[payments] post-approval notification failed order=' . (int) $orderId);
    }

    return ['ok' => true, 'message' => 'Pagamento confirmado.'];
}

function payment_reconcile_venopag_reversal($requestNumber, $status)
{
    global $conn;
    $requestNumber = trim((string) $requestNumber);
    $status = strtolower(trim((string) $status));
    if ($requestNumber === '' || !in_array($status, ['canceled', 'contested', 'chargedback', 'refunded'], true)) {
        return ['ok' => false, 'http' => 400, 'message' => 'Reversão VenoPag inválida.'];
    }

    $conn->begin_transaction();
    try {
        $statement = $conn->prepare("SELECT id, status, product_id, quantity FROM order_list WHERE id_mp = ? AND payment_method = 'VenoPag' LIMIT 1 FOR UPDATE");
        $statement->bind_param('s', $requestNumber);
        $statement->execute();
        $result = $statement->get_result();
        $order = $result ? $result->fetch_assoc() : null;
        $statement->close();
        if (!$order) {
            throw new RuntimeException('Pedido VenoPag não encontrado.', 404);
        }
        if ((int) $order['status'] !== 2) {
            $conn->commit();
            return ['ok' => true, 'message' => 'Reversão já conciliada.'];
        }

        $orderId = (int) $order['id'];
        $quantity = max(0, (int) $order['quantity']);
        $productId = (int) $order['product_id'];
        $update = $conn->prepare('UPDATE order_list SET status = 3, date_updated = NOW() WHERE id = ? AND status = 2');
        $update->bind_param('i', $orderId);
        $update->execute();
        if ($update->affected_rows !== 1) {
            throw new RuntimeException('O pedido já foi alterado.', 409);
        }
        $update->close();

        $product = $conn->prepare('UPDATE product_list SET paid_numbers = GREATEST(0, CAST(paid_numbers AS SIGNED) - ?) WHERE id = ?');
        $product->bind_param('ii', $quantity, $productId);
        $product->execute();
        $product->close();
        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        $http = (int) $error->getCode();
        return ['ok' => false, 'http' => ($http >= 400 && $http <= 599) ? $http : 500, 'message' => $error->getMessage()];
    }

    error_log('[payments] venopag reversal reconciled order=' . $orderId . ' status=' . $status);
    return ['ok' => true, 'message' => 'Reversão VenoPag conciliada.'];
}

function payment_process_webhook($provider, $raw, array $headers)
{
    if (!isset(payment_provider_definitions()[$provider])) {
        return ['ok' => false, 'http' => 404, 'message' => 'Gateway desconhecido.'];
    }
    if ($provider === 'venopag') {
        $event = json_decode($raw, true);
        $expectedSecret = payment_venopag_webhook_secret();
        $receivedSecret = trim((string) ($_GET['token'] ?? ''));
        if (!is_array($event) || $expectedSecret === '' || $receivedSecret === '' || !hash_equals($expectedSecret, $receivedSecret)) {
            return ['ok' => false, 'http' => 401, 'message' => 'Webhook não autorizado.'];
        }
        $requestNumber = trim((string) ($event['request_number'] ?? ''));
        if ($requestNumber === '') {
            return ['ok' => false, 'http' => 400, 'message' => 'Referência VenoPag ausente.'];
        }
        $consult = payment_venopag_consult($requestNumber, 7);
        if (empty($consult['ok'])) {
            return ['ok' => false, 'http' => 502, 'message' => 'Falha ao confirmar a transação na VenoPag.'];
        }
        $confirmedStatus = strtolower((string) ($consult['json']['status'] ?? ''));
        if (in_array($confirmedStatus, ['canceled', 'contested', 'chargedback', 'refunded'], true)) {
            return payment_reconcile_venopag_reversal((string) ($consult['json']['request_number'] ?? $requestNumber), $confirmedStatus);
        }

        if ($confirmedStatus !== 'confirmed') {
            $validNonPaid = ['pending', 'expired'];
            return [
                'ok' => false,
                'http' => 200,
                'message' => in_array($confirmedStatus, $validNonPaid, true)
                    ? 'Pagamento VenoPag ainda não liberável.'
                    : 'Status VenoPag desconhecido.',
            ];
        }

        // A consulta autenticada acima já é a confirmação exigida pela VenoPag.
        // Processar por aqui evita uma segunda consulta e mantém o webhook abaixo
        // do limite de 10 segundos do provedor.
        $confirmed = $consult['json'];
        $reference = (string) ($confirmed['request_number'] ?? $requestNumber);
        return payment_mark_order_paid('venopag', [
            'order_id' => payment_find_order_id_by_reference('id_mp', $reference),
            'amount' => $confirmed['amount'] ?? 0,
            'reference' => $reference,
        ]);
    }

    $verified = payment_verify_webhook($provider, $raw, $headers);
    if (empty($verified['ok'])) {
        return $verified;
    }
    return payment_mark_order_paid($provider, $verified);
}

function payment_check_order($orderId)
{
    global $conn;
    $orderId = (int) $orderId;
    try {
        $txidSelect = payment_order_supports_txid() ? 'txid' : "'' AS txid";
        $statement = $conn->prepare('SELECT status, payment_method, id_mp, ' . $txidSelect . ' FROM order_list WHERE id = ? LIMIT 1');
        if (!$statement) {
            throw new RuntimeException('Não foi possível preparar a consulta do pedido.');
        }
        $statement->bind_param('i', $orderId);
        $statement->execute();
        $result = $statement->get_result();
        $order = $result ? $result->fetch_assoc() : null;
        $statement->close();
    } catch (Throwable $error) {
        error_log('[payments] order status lookup failed order=' . $orderId . ' reason=' . $error->getMessage());
        return ['ok' => false, 'status' => 1, 'message' => 'Não foi possível consultar o pedido.'];
    }
    if (!$order) {
        return ['ok' => false, 'status' => 'failed', 'message' => 'Pedido não encontrado.'];
    }
    if ((int) $order['status'] === 2) {
        return ['ok' => true, 'status' => 2];
    }
    $currentStatus = (int) $order['status'];
    $recoverableVenoPag = $currentStatus === 3 && (string) $order['payment_method'] === 'VenoPag';
    if ($currentStatus !== 1 && !$recoverableVenoPag) {
        return ['ok' => true, 'status' => (int) $order['status']];
    }

    $methodMap = ['MercadoPago' => 'mercadopago', 'Gerencianet' => 'gerencianet', 'Paggue' => 'paggue', 'OpenPix' => 'openpix', 'Pay2m' => 'pay2m', 'VenoPag' => 'venopag'];
    $provider = $methodMap[$order['payment_method']] ?? null;
    if (!$provider) {
        return ['ok' => false, 'status' => 1, 'message' => 'Gateway do pedido inválido.'];
    }

    if ($provider === 'mercadopago') {
        $response = payment_http('GET', 'https://api.mercadopago.com/v1/payments/' . rawurlencode((string) $order['id_mp']), ['Authorization: Bearer ' . payment_setting('mercadopago_access_token')]);
        $data = $response['json'];
        if (!$response['ok']) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar o Mercado Pago.'];
        }
        if (($data['status'] ?? '') !== 'approved') {
            return ['ok' => true, 'status' => 1];
        }
        $verified = ['order_id' => $data['external_reference'] ?? $orderId, 'amount' => $data['transaction_amount'] ?? 0, 'reference' => $data['id'] ?? $order['id_mp']];
    } elseif ($provider === 'openpix') {
        $response = payment_http('GET', 'https://api.openpix.com.br/api/v1/charge/' . rawurlencode((string) $orderId), ['Authorization: ' . payment_setting('openpix_app_id')]);
        $charge = $response['json']['charge'] ?? [];
        if (!$response['ok']) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a OpenPix.'];
        }
        if (!in_array(strtoupper((string) ($charge['status'] ?? '')), ['COMPLETED', 'PAID'], true)) {
            return ['ok' => true, 'status' => 1];
        }
        $verified = ['order_id' => $charge['correlationID'] ?? $orderId, 'amount' => ((float) ($charge['value'] ?? 0)) / 100, 'reference' => $charge['globalID'] ?? $order['id_mp']];
    } elseif ($provider === 'venopag') {
        $response = payment_venopag_consult((string) $order['id_mp']);
        $data = $response['json'] ?? [];
        if (empty($response['ok'])) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a VenoPag.'];
        }
        $venoStatus = strtolower((string) ($data['status'] ?? ''));
        if ($venoStatus !== 'confirmed' && trim((string) ($order['txid'] ?? '')) !== '') {
            $transactionResponse = payment_venopag_consult_transaction((string) $order['txid']);
            $transactionData = $transactionResponse['json'] ?? [];
            if (!empty($transactionResponse['ok']) && strtolower((string) ($transactionData['status'] ?? '')) === 'confirmed') {
                $response = $transactionResponse;
                $data = $transactionData;
                $venoStatus = 'confirmed';
            }
        }
        if ($venoStatus !== 'confirmed') {
            return ['ok' => true, 'status' => $currentStatus, 'provider_status' => $venoStatus];
        }
        $verified = [
            'order_id' => $orderId,
            'amount' => $data['amount'] ?? 0,
            'reference' => $data['request_number'] ?? $order['id_mp'],
        ];
    } elseif ($provider === 'pay2m') {
        $token = payment_pay2m_token();
        if (empty($token['ok'])) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a Pay2M.'];
        }
        $response = payment_http('GET', 'https://portal.pay2m.com.br/api/v1/pix/qrcode/' . rawurlencode((string) $order['id_mp']), ['Authorization: ' . $token['authorization']]);
        $data = $response['json'];
        if (!$response['ok']) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a Pay2M.'];
        }
        if (strtolower((string) ($data['status'] ?? '')) !== 'paid') {
            return ['ok' => true, 'status' => 1];
        }
        $verified = ['order_id' => $data['external_reference'] ?? $orderId, 'amount' => $data['value'] ?? 0, 'reference' => $data['reference_code'] ?? $order['id_mp']];
    } elseif ($provider === 'gerencianet') {
        $token = payment_efi_token();
        if (empty($token['ok'])) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a Efí.'];
        }
        $response = payment_http('GET', payment_efi_base_url() . '/v2/cob/' . rawurlencode((string) $order['txid']), ['Authorization: ' . $token['authorization'], 'Content-Type: application/json'], null, $token['certificate']);
        $data = $response['json'];
        if (!$response['ok']) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a Efi.'];
        }
        if (strtoupper((string) ($data['status'] ?? '')) !== 'CONCLUIDA') {
            return ['ok' => true, 'status' => 1];
        }
        $verified = ['order_id' => $orderId, 'amount' => $data['valor']['original'] ?? 0, 'reference' => $order['txid']];
    } else {
        $token = payment_paggue_token();
        if (empty($token['ok'])) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a Paggue.'];
        }
        $response = payment_http('GET', 'https://ms.paggue.io/cashin/api/billing_order/' . rawurlencode((string) $order['id_mp']), [
            'Authorization: Bearer ' . $token['access_token'], 'X-Company-ID: ' . $token['company_id'],
        ]);
        $data = $response['json'];
        $status = $data['status'] ?? ($data['payment']['status'] ?? null);
        if (!$response['ok']) {
            return ['ok' => false, 'status' => 1, 'message' => 'Falha ao consultar a Paggue.'];
        }
        if (!in_array((string) $status, ['1', 'paid', 'PAID'], true)) {
            return ['ok' => true, 'status' => 1];
        }
        $amount = $data['amount'] ?? ($data['payment']['amount'] ?? 0);
        $verified = ['order_id' => $data['external_id'] ?? $orderId, 'amount' => ((float) $amount) / 100, 'reference' => $order['id_mp']];
    }

    $approved = payment_mark_order_paid($provider, $verified);
    return !empty($approved['ok']) ? ['ok' => true, 'status' => 2] : ['ok' => false, 'status' => $currentStatus, 'message' => $approved['message'] ?? 'Confirmação recusada.'];
}

function payment_reconcile_canceled_venopag_orders($limit = 25)
{
    global $conn;
    $limit = max(1, min(100, (int) $limit));
    $result = $conn->query(
        "SELECT id FROM order_list WHERE status = 3 AND payment_method = 'VenoPag' "
        . "AND id_mp IS NOT NULL AND id_mp <> '' ORDER BY id DESC LIMIT " . $limit
    );
    if (!$result) {
        return ['ok' => false, 'checked' => 0, 'recovered' => 0, 'message' => 'Nao foi possivel localizar os pedidos VenoPag.'];
    }

    $orderIds = [];
    while ($row = $result->fetch_assoc()) {
        $orderIds[] = (int) $row['id'];
    }
    $result->free();

    $checked = 0;
    $recovered = 0;
    $errors = [];
    $statuses = [];
    $deadline = microtime(true) + 50;
    foreach ($orderIds as $orderId) {
        if (microtime(true) >= $deadline) {
            break;
        }
        $checked++;
        $check = payment_check_order($orderId);
        if (!empty($check['ok']) && (int) ($check['status'] ?? 0) === 2) {
            $recovered++;
            $statuses[] = ['order_id' => $orderId, 'provider_status' => 'confirmed'];
        } elseif (!empty($check['ok'])) {
            $statuses[] = ['order_id' => $orderId, 'provider_status' => (string) ($check['provider_status'] ?? 'unknown')];
        } elseif (empty($check['ok'])) {
            $errors[] = ['order_id' => $orderId, 'message' => $check['message'] ?? 'Falha na consulta.'];
        }
    }

    return ['ok' => true, 'checked' => $checked, 'recovered' => $recovered, 'statuses' => $statuses, 'errors' => $errors];
}

function payment_expire_pending_orders($productId = null, $limit = 10)
{
    global $conn;
    $limit = max(1, min(50, (int) $limit));
    $productId = $productId === null ? null : (int) $productId;
    $sql = 'SELECT id, product_id, payment_method FROM order_list '
        . 'WHERE status = 1 AND order_expiration > 0 '
        . "AND ((payment_method = 'VenoPag' AND TIMESTAMPADD(MINUTE, order_expiration + 60, date_created) <= NOW()) "
        . "OR ((payment_method IS NULL OR payment_method = '') AND DATE_ADD(date_created, INTERVAL order_expiration MINUTE) <= NOW()) "
        . "OR (payment_method <> 'VenoPag' AND DATE_ADD(date_created, INTERVAL order_expiration MINUTE) <= NOW()))";
    if ($productId !== null && $productId > 0) {
        $sql .= ' AND product_id = ' . $productId;
    }
    $sql .= ' ORDER BY date_created ASC LIMIT ' . $limit;
    $result = $conn->query($sql);
    if (!$result) {
        return ['ok' => false, 'expired' => 0, 'message' => 'Falha ao localizar pedidos vencidos.'];
    }

    $expired = 0;
    $products = [];
    $deadline = microtime(true) + 45;
    $gatewayMethods = ['MercadoPago', 'Gerencianet', 'Paggue', 'OpenPix', 'Pay2m', 'VenoPag'];
    while ($order = $result->fetch_assoc()) {
        if (microtime(true) >= $deadline) {
            break;
        }
        $check = in_array((string) $order['payment_method'], $gatewayMethods, true)
            ? payment_check_order((int) $order['id'])
            : ['ok' => true, 'status' => 1];
        if (empty($check['ok']) || (int) ($check['status'] ?? 1) !== 1) {
            continue;
        }

        // O prazo local nao deve cancelar um PIX que ainda esta pendente na VenoPag.
        // Apenas os estados finais do proprio provedor liberam as cotas.
        if ((string) $order['payment_method'] === 'VenoPag') {
            $providerStatus = strtolower(trim((string) ($check['provider_status'] ?? '')));
            if (!in_array($providerStatus, ['expired', 'canceled'], true)) {
                continue;
            }
        }

        $expiredAt = payment_local_datetime();
        $statement = $conn->prepare(
            'UPDATE order_list SET status = 3, date_updated = ? '
            . 'WHERE id = ? AND status = 1 AND order_expiration > 0 '
            . 'AND DATE_ADD(date_created, INTERVAL order_expiration MINUTE) <= NOW()'
        );
        $orderId = (int) $order['id'];
        $statement->bind_param('si', $expiredAt, $orderId);
        $statement->execute();
        if ($statement->affected_rows === 1) {
            $expired++;
            $products[(int) $order['product_id']] = true;
        }
        $statement->close();
    }
    $result->free();

    foreach (array_keys($products) as $expiredProductId) {
        $statement = $conn->prepare(
            'UPDATE product_list SET pending_numbers = ('
            . 'SELECT COALESCE(SUM(quantity), 0) FROM order_list '
            . 'WHERE product_id = ? AND status = 1'
            . ') WHERE id = ?'
        );
        $statement->bind_param('ii', $expiredProductId, $expiredProductId);
        $statement->execute();
        $statement->close();
    }

    return ['ok' => true, 'expired' => $expired];
}
