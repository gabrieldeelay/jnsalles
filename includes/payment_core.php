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
    return count($enabled) === 1 ? $enabled[0] : null;
}

function payment_amount($amount, $provider)
{
    $definitions = payment_provider_definitions();
    $base = round((float) str_replace(',', '.', (string) $amount), 2);
    $tax = isset($definitions[$provider]) ? (float) payment_setting($definitions[$provider]['tax'], 0) : 0;
    return round($base + ($base * max(0, $tax) / 100), 2);
}

function payment_uuid_v4()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function payment_http($method, $url, array $headers = [], $body = null, $certificate = null)
{
    $curl = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 7,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
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
    return ['ok' => $ok, 'status' => $status, 'json' => is_array($json) ? $json : [], 'error' => $error];
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

function payment_store_pix($orderId, $method, $pixCode, $reference, $expiration, $txid = '')
{
    global $conn;
    $orderId = (int) $orderId;
    $expiration = (int) $expiration;
    $statement = $conn->prepare('UPDATE order_list SET payment_method = ?, pix_code = ?, pix_qrcode = ?, id_mp = ?, txid = ?, order_expiration = ? WHERE id = ?');
    $emptyQr = '';
    $reference = (string) $reference;
    $txid = (string) $txid;
    $statement->bind_param('sssssii', $method, $pixCode, $emptyQr, $reference, $txid, $expiration, $orderId);
    $saved = $statement->execute();
    $statement->close();
    return $saved;
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
    $provider = payment_active_provider();
    if (!$provider) {
        return ['ok' => false, 'message' => 'Ative exatamente um gateway de pagamento no painel administrativo.'];
    }

    $amount = payment_amount($amount, $provider);
    $expiration = max(5, min(1440, (int) $expiration));
    $result = call_user_func('payment_create_' . $provider, (int) $orderId, $amount, $name, $email, $cpf, $expiration, $phone);
    if (empty($result['ok'])) {
        error_log('[payments] charge creation failed provider=' . $provider . ' order=' . (int) $orderId);
    }
    return $result;
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

function payment_pay2m_token()
{
    $clientId = (string) payment_setting('pay2m_client_id');
    $clientSecret = (string) payment_setting('pay2m_client_secret');
    if ($clientId === '' || $clientSecret === '') {
        return ['ok' => false, 'message' => 'Credenciais da Pay2M não configuradas.'];
    }
    $response = payment_http('POST', 'https://portal.pay2m.com.br/api/auth/generate_token', [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/json',
    ], ['grant_type' => 'client_credentials']);
    $data = $response['json'];
    if (!$response['ok'] || empty($data['access_token'])) {
        return ['ok' => false, 'message' => payment_safe_provider_error($response, 'Credenciais da Pay2M recusadas.')];
    }
    return ['ok' => true, 'authorization' => ($data['token_type'] ?? 'Bearer') . ' ' . $data['access_token']];
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
    $webhook = payment_pay2m_register_webhook($token['authorization']);
    if (empty($webhook['ok'])) {
        return ['ok' => false, 'message' => payment_safe_provider_error($webhook, 'Não foi possível proteger o webhook da Pay2M.')];
    }
    $payload = [
        'value' => $amount,
        'generator_name' => trim((string) $name),
        'external_reference' => (string) $orderId,
        'expiration_time' => min(3600, $expiration * 60),
        'payer_message' => 'Pedido #' . $orderId,
    ];
    $document = preg_replace('/\D+/', '', (string) $cpf);
    if (strlen($document) === 11 && $document !== '00000000000') {
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
        return ['ok' => false, 'message' => 'A Efí exige Client ID, Client Secret e EFI_CERTIFICATE_BASE64 na Vercel.'];
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

function payment_test_gateway($provider)
{
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

    if ($provider === 'pay2m') {
        $secret = (string) payment_setting('pay2m_webhook_secret');
        $authorization = $headers['authorization'] ?? '';
        if ($secret === '' || !hash_equals($secret, preg_replace('/^Bearer\s+/i', '', $authorization))) {
            return ['ok' => false, 'http' => 401, 'message' => 'Assinatura inválida.'];
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
        $statement = $conn->prepare('SELECT o.status, o.product_id, o.total_amount, o.quantity, o.payment_method, o.referral_id, c.firstname, c.lastname, c.phone FROM order_list o INNER JOIN customer_list c ON c.id = o.customer_id WHERE o.id = ? FOR UPDATE');
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
        if ((int) $order['status'] !== 1) {
            throw new RuntimeException('O pedido não está pendente.', 409);
        }

        $updated = $conn->prepare("UPDATE order_list SET status = 2, date_updated = NOW(), whatsapp_status = '' WHERE id = ? AND status = 1");
        $updated->bind_param('i', $orderId);
        $updated->execute();
        if ($updated->affected_rows !== 1) {
            throw new RuntimeException('O pedido já foi alterado.', 409);
        }
        $updated->close();

        $quantity = max(0, (int) $order['quantity']);
        $productId = (int) $order['product_id'];
        $product = $conn->prepare('UPDATE product_list SET pending_numbers = GREATEST(0, CAST(pending_numbers AS SIGNED) - ?), paid_numbers = CAST(paid_numbers AS SIGNED) + ? WHERE id = ?');
        $product->bind_param('iii', $quantity, $quantity, $productId);
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

function payment_process_webhook($provider, $raw, array $headers)
{
    if (!isset(payment_provider_definitions()[$provider])) {
        return ['ok' => false, 'http' => 404, 'message' => 'Gateway desconhecido.'];
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
    $statement = $conn->prepare('SELECT status, payment_method, id_mp, txid FROM order_list WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $orderId);
    $statement->execute();
    $result = $statement->get_result();
    $order = $result ? $result->fetch_assoc() : null;
    $statement->close();
    if (!$order) {
        return ['ok' => false, 'status' => 'failed', 'message' => 'Pedido não encontrado.'];
    }
    if ((int) $order['status'] === 2) {
        return ['ok' => true, 'status' => 2];
    }
    if ((int) $order['status'] !== 1) {
        return ['ok' => true, 'status' => (int) $order['status']];
    }

    $methodMap = ['MercadoPago' => 'mercadopago', 'Gerencianet' => 'gerencianet', 'Paggue' => 'paggue', 'OpenPix' => 'openpix', 'Pay2m' => 'pay2m'];
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
    return !empty($approved['ok']) ? ['ok' => true, 'status' => 2] : ['ok' => false, 'status' => 1, 'message' => $approved['message'] ?? 'Confirmação recusada.'];
}

function payment_expire_pending_orders($productId = null, $limit = 10)
{
    global $conn;
    $limit = max(1, min(50, (int) $limit));
    $productId = $productId === null ? null : (int) $productId;
    $sql = 'SELECT id, product_id, payment_method FROM order_list '
        . 'WHERE status = 1 AND order_expiration > 0 '
        . 'AND DATE_ADD(date_created, INTERVAL order_expiration MINUTE) <= NOW()';
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
    $gatewayMethods = ['MercadoPago', 'Gerencianet', 'Paggue', 'OpenPix', 'Pay2m'];
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

        $statement = $conn->prepare(
            'UPDATE order_list SET status = 3, date_updated = NOW() '
            . 'WHERE id = ? AND status = 1 AND order_expiration > 0 '
            . 'AND DATE_ADD(date_created, INTERVAL order_expiration MINUTE) <= NOW()'
        );
        $orderId = (int) $order['id'];
        $statement->bind_param('i', $orderId);
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
