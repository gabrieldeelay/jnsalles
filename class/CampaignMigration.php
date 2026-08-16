<?php

require_once '../settings.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function migration_reply(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function migration_phone(string $phone): string
{
    return preg_replace('/\D+/', '', $phone) ?: '';
}

function migration_text($value, int $limit): string
{
    $value = trim((string) $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $limit, 'UTF-8') : substr($value, 0, $limit);
}

function migration_date($value): string
{
    $date = DateTime::createFromFormat('Y-m-d H:i:s', (string) $value);
    return $date ? $date->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    migration_reply(405, ['status' => 'failed', 'msg' => 'Método não permitido.']);
}
if (empty($_SESSION['userdata']) || (int) ($_SESSION['userdata']['type'] ?? 0) !== 1) {
    migration_reply(403, ['status' => 'failed', 'msg' => 'Acesso administrativo obrigatório.']);
}
if (!hash_equals('big2-iphone-20260816', (string) ($_SERVER['HTTP_X_JNSALLES_MIGRATION'] ?? ''))) {
    migration_reply(403, ['status' => 'failed', 'msg' => 'Operação de migração não autorizada.']);
}

$rawPayload = file_get_contents('php://input');
if (!is_string($rawPayload) || $rawPayload === '' || strlen($rawPayload) > 12 * 1024 * 1024) {
    migration_reply(400, ['status' => 'failed', 'msg' => 'Pacote de migração ausente ou muito grande.']);
}
$payload = json_decode($rawPayload, true);
if (!is_array($payload)) {
    migration_reply(400, ['status' => 'failed', 'msg' => 'Pacote JSON inválido.']);
}

$targetProductId = (int) ($payload['target_product_id'] ?? 0);
$customers = $payload['customers'] ?? [];
$orders = $payload['orders'] ?? [];
$dryRun = !empty($payload['dry_run']);
if ($targetProductId <= 0 || !is_array($customers) || !is_array($orders) || count($customers) > 1000 || count($orders) > 1000) {
    migration_reply(422, ['status' => 'failed', 'msg' => 'Estrutura da migração inválida.']);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->begin_transaction();

try {
    $productStatement = $conn->prepare('SELECT id, name, price, qty_numbers FROM product_list WHERE id = ? FOR UPDATE');
    $productStatement->bind_param('i', $targetProductId);
    $productStatement->execute();
    $product = $productStatement->get_result()->fetch_assoc();
    $productStatement->close();
    if (!$product || stripos((string) $product['name'], 'iPhone 17 Pro Max') === false) {
        throw new RuntimeException('A campanha de destino não é a campanha do iPhone 17 Pro Max.');
    }

    $quantityLimit = (int) $product['qty_numbers'];
    $numberWidth = max(1, strlen((string) max(0, $quantityLimit - 1)));
    $existingCustomers = [];
    $customerResult = $conn->query('SELECT id, phone FROM customer_list ORDER BY id');
    while ($existingCustomer = $customerResult->fetch_assoc()) {
        $normalizedPhone = migration_phone((string) $existingCustomer['phone']);
        if ($normalizedPhone !== '' && !isset($existingCustomers[$normalizedPhone])) {
            $existingCustomers[$normalizedPhone] = (int) $existingCustomer['id'];
        }
    }

    $insertCustomer = $conn->prepare(
        'INSERT INTO customer_list (firstname, lastname, phone, email, date_created, date_updated, zipcode, address, number, neighborhood, complement, state, city, reference_point, birth, instagram) VALUES (?, ?, ?, NULLIF(?, \'\'), ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'), NULLIF(?, \'\'))'
    );
    $customerMap = [];
    $customersCreated = 0;
    $customersMatched = 0;
    foreach ($customers as $customer) {
        if (!is_array($customer)) {
            throw new RuntimeException('Cadastro de cliente inválido.');
        }
        $oldId = (int) ($customer['old_id'] ?? 0);
        $phone = migration_phone((string) ($customer['phone'] ?? ''));
        if ($oldId <= 0 || strlen($phone) < 8 || strlen($phone) > 15) {
            throw new RuntimeException('Cliente sem identificador ou telefone válido.');
        }
        if (isset($existingCustomers[$phone])) {
            $customerMap[$oldId] = $existingCustomers[$phone];
            $customersMatched++;
            continue;
        }

        $firstname = migration_text($customer['firstname'] ?? '', 250);
        $lastname = migration_text($customer['lastname'] ?? '', 250);
        if ($firstname === '') {
            $firstname = 'Cliente';
        }
        $email = migration_text($customer['email'] ?? '', 254);
        $createdAt = migration_date($customer['date_created'] ?? '');
        $updatedAt = migration_date($customer['date_updated'] ?? $createdAt);
        $zipcode = migration_text($customer['zipcode'] ?? '', 20);
        $address = migration_text($customer['address'] ?? '', 255);
        $addressNumber = migration_text($customer['number'] ?? '', 30);
        $neighborhood = migration_text($customer['neighborhood'] ?? '', 150);
        $complement = migration_text($customer['complement'] ?? '', 255);
        $state = migration_text($customer['state'] ?? '', 100);
        $city = migration_text($customer['city'] ?? '', 150);
        $referencePoint = migration_text($customer['reference_point'] ?? '', 255);
        $birthValue = (string) ($customer['birth'] ?? '');
        $birth = '';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $birthValue, $birthParts)
            && checkdate((int) $birthParts[2], (int) $birthParts[3], (int) $birthParts[1])) {
            $birth = $birthValue;
        }
        $instagram = migration_text($customer['instagram'] ?? '', 191);
        $insertCustomer->bind_param('ssssssssssssssss', $firstname, $lastname, $phone, $email, $createdAt, $updatedAt, $zipcode, $address, $addressNumber, $neighborhood, $complement, $state, $city, $referencePoint, $birth, $instagram);
        $insertCustomer->execute();
        $newCustomerId = (int) $conn->insert_id;
        $existingCustomers[$phone] = $newCustomerId;
        $customerMap[$oldId] = $newCustomerId;
        $customersCreated++;
    }
    $insertCustomer->close();

    $reservedNumbers = [];
    $reservedResult = $conn->prepare('SELECT order_numbers FROM order_list WHERE product_id = ? AND status IN (1, 2)');
    $reservedResult->bind_param('i', $targetProductId);
    $reservedResult->execute();
    $numberRows = $reservedResult->get_result();
    while ($numberRow = $numberRows->fetch_assoc()) {
        foreach (explode(',', (string) $numberRow['order_numbers']) as $number) {
            $number = trim($number);
            if ($number !== '' && ctype_digit($number)) {
                $reservedNumbers[(string) ((int) $number)] = true;
            }
        }
    }
    $reservedResult->close();

    $findOrder = $conn->prepare('SELECT id FROM order_list WHERE order_token = ? LIMIT 1');
    $insertOrder = $conn->prepare(
        'INSERT INTO order_list (code, customer_id, quantity, total_amount, status, date_created, date_updated, product_name, order_token, order_numbers, product_id, payment_method, discount_amount) VALUES (?, ?, ?, ?, 2, ?, ?, ?, ?, ?, ?, ?, 0)'
    );
    $insertItem = $conn->prepare('INSERT IGNORE INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    $ordersCreated = 0;
    $ordersSkipped = 0;
    $numbersRemapped = 0;
    $allocationCursor = 0;

    foreach ($orders as $order) {
        if (!is_array($order)) {
            throw new RuntimeException('Pedido de origem inválido.');
        }
        $oldOrderId = (int) ($order['old_id'] ?? 0);
        $oldCustomerId = (int) ($order['customer_old_id'] ?? 0);
        $quantity = (int) ($order['quantity'] ?? 0);
        if ($oldOrderId <= 0 || $quantity <= 0 || empty($customerMap[$oldCustomerId])) {
            throw new RuntimeException('Pedido sem cliente, identificador ou quantidade válida.');
        }
        $orderToken = 'MIGBIG2-' . $oldOrderId;
        $findOrder->bind_param('s', $orderToken);
        $findOrder->execute();
        if ($findOrder->get_result()->fetch_assoc()) {
            $ordersSkipped++;
            continue;
        }

        $sourceNumbers = array_values(array_filter(array_map('trim', explode(',', (string) ($order['order_numbers'] ?? ''))), static function ($number) {
            return $number !== '';
        }));
        if (count($sourceNumbers) !== $quantity) {
            throw new RuntimeException('A quantidade do pedido ' . $oldOrderId . ' não corresponde às cotas informadas.');
        }
        $finalNumbers = [];
        foreach ($sourceNumbers as $sourceNumber) {
            if (!ctype_digit($sourceNumber) || (int) $sourceNumber >= $quantityLimit) {
                throw new RuntimeException('O pedido ' . $oldOrderId . ' contém uma cota inválida.');
            }
            $canonical = (string) ((int) $sourceNumber);
            if (isset($reservedNumbers[$canonical])) {
                while ($allocationCursor < $quantityLimit && isset($reservedNumbers[(string) $allocationCursor])) {
                    $allocationCursor++;
                }
                if ($allocationCursor >= $quantityLimit) {
                    throw new RuntimeException('Não existem cotas livres suficientes para resolver conflitos.');
                }
                $canonical = (string) $allocationCursor;
                $allocationCursor++;
                $numbersRemapped++;
            }
            $reservedNumbers[$canonical] = true;
            $finalNumbers[] = str_pad($canonical, $numberWidth, '0', STR_PAD_LEFT);
        }

        $code = 'M2I' . str_pad((string) $oldOrderId, 8, '0', STR_PAD_LEFT);
        $customerId = $customerMap[$oldCustomerId];
        $totalAmount = round((float) ($order['total_amount'] ?? 0), 2);
        $createdAt = migration_date($order['date_created'] ?? '');
        $updatedAt = migration_date($order['date_updated'] ?? $createdAt);
        $productName = (string) $product['name'];
        $numberList = implode(',', $finalNumbers);
        $paymentMethod = migration_text($order['payment_method'] ?? 'Migrado', 100);
        if ($paymentMethod === '') {
            $paymentMethod = 'Migrado';
        }
        $insertOrder->bind_param('siidsssssis', $code, $customerId, $quantity, $totalAmount, $createdAt, $updatedAt, $productName, $orderToken, $numberList, $targetProductId, $paymentMethod);
        $insertOrder->execute();
        $newOrderId = (int) $conn->insert_id;
        $unitPrice = (float) $product['price'];
        $insertItem->bind_param('iiid', $newOrderId, $targetProductId, $quantity, $unitPrice);
        $insertItem->execute();
        $ordersCreated++;
    }
    $findOrder->close();
    $insertOrder->close();
    $insertItem->close();

    $paidUpdate = $conn->prepare('UPDATE product_list SET paid_numbers = (SELECT COALESCE(SUM(quantity), 0) FROM order_list WHERE product_id = ? AND status = 2), pending_numbers = (SELECT COALESCE(SUM(quantity), 0) FROM order_list WHERE product_id = ? AND status = 1) WHERE id = ?');
    $paidUpdate->bind_param('iii', $targetProductId, $targetProductId, $targetProductId);
    $paidUpdate->execute();
    $paidUpdate->close();

    $summary = [
        'status' => 'success',
        'dry_run' => $dryRun,
        'target_product_id' => $targetProductId,
        'target_product_name' => $product['name'],
        'customers_created' => $customersCreated,
        'customers_matched' => $customersMatched,
        'orders_created' => $ordersCreated,
        'orders_skipped' => $ordersSkipped,
        'numbers_remapped' => $numbersRemapped,
    ];
    if ($dryRun) {
        $conn->rollback();
    } else {
        $conn->commit();
    }
    migration_reply(200, $summary);
} catch (Throwable $error) {
    $conn->rollback();
    error_log('[campaign-migration] ' . $error->getMessage());
    migration_reply(422, ['status' => 'failed', 'msg' => $error->getMessage()]);
}
