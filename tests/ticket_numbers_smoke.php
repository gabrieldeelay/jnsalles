<?php
// Pure formatting/render tests. Never bootstrap the application or connect to a database.
require_once dirname(__DIR__) . '/includes/ticket_numbers.php';

function ticket_assert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// Extract only named pure functions from legacy page files without executing their bootstrap.
function ticket_source_function($path, $name)
{
    $tokens = token_get_all(file_get_contents($path));
    for ($i = 0; $i < count($tokens); $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
            continue;
        }
        $j = $i + 1;
        while (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        if (!is_array($tokens[$j]) || $tokens[$j][1] !== $name) {
            continue;
        }
        $source = '';
        $depth = 0;
        $started = false;
        for (; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            $source .= is_array($token) ? $token[1] : $token;
            if ($token === '{') {
                $started = true;
                $depth++;
            } elseif ($token === '}' && --$depth === 0 && $started) {
                return $source;
            }
        }
    }
    throw new RuntimeException('Function not found: ' . $name);
}

$examples = [1 => '0000001', 25 => '0000025', 1234 => '0001234', 6516 => '0006516', 123456 => '0123456', 2000000 => '2000000', 0 => '0000000'];
foreach ($examples as $number => $expected) {
    ticket_assert(jnsalles_format_ticket($number, 2000000) === $expected, 'Example: ' . $number);
    ticket_assert(jnsalles_format_ticket($expected, 2000000) === $expected, 'Idempotence: ' . $number);
    ticket_assert((int) $expected === $number, 'Numeric identity changed');
}
ticket_assert(jnsalles_format_ticket('1.234', 2000000) === '0001234', 'Grouped number');
ticket_assert(jnsalles_format_ticket('00000006516', 2000000) === '0006516', 'Legacy extra zeroes');
ticket_assert(jnsalles_format_ticket('99999999', 2000000) === '99999999', 'Never truncate');
ticket_assert(jnsalles_format_ticket('perdeu-4', 2000000) === 'perdeu-4', 'Non-numeric label');
ticket_assert(jnsalles_format_ticket('', 2000000) === '', 'Empty must not become zero');
ticket_assert(jnsalles_format_ticket('0001', 0) === '0001', 'Unknown campaign total');

$raw = '1,25,0006516,0,,123456,';
$original = $raw;
$expectedList = ['0000001', '0000025', '0006516', '0000000', '0123456'];
ticket_assert(jnsalles_format_ticket_list($raw, 2000000) === $expectedList, 'CSV and zero ticket');
ticket_assert($raw === $original, 'Stored input was modified');
ticket_assert(jnsalles_format_ticket_list(explode(',', $raw), 2000000) === $expectedList, 'Array input');

foreach ([10, 100, 1000, 1000000, 2000000, 10000000] as $total) {
    for ($i = 0; $i < 500; $i++) {
        // Existing allocation uses 0..total-1. Do not shift that range or stored identities.
        $number = random_int(0, $total - 1);
        $legacyGenerated = str_pad((string) $number, strlen((string) ($total - 1)), '0', STR_PAD_LEFT);
        $display = jnsalles_format_ticket($legacyGenerated, $total);
        ticket_assert(strlen($display) === strlen((string) $total), 'Width: ' . $total);
        ticket_assert((int) $display === $number, 'Generated ticket identity');
    }
}

foreach (['settings.php' => 'TicketSettingsTest', 'config.php' => 'TicketConfigTest'] as $file => $namespace) {
    $functions = ticket_source_function(dirname(__DIR__) . '/' . $file, 'drope_format_luck_numbers');
    $functions .= ticket_source_function(dirname(__DIR__) . '/' . $file, 'drope_format_luck_numbers_dashboard');
    eval('namespace ' . $namespace . ';' . $functions);
    foreach (['drope_format_luck_numbers', 'drope_format_luck_numbers_dashboard'] as $function) {
        $call = $namespace . '\\' . $function;
        foreach ([true, false] as $badges) {
            $rendered = $call($raw, 2000000, 'alert-success', $badges, 1);
            foreach ($expectedList as $expected) {
                ticket_assert(str_contains($rendered, $expected), $file . ': missing ' . $expected);
            }
            ticket_assert(str_contains($call('0', 2000000, '', $badges, 1), '0000000'), 'Standalone zero');
            ticket_assert(str_contains($call('00,01,', 25, '', $badges, 3), 'Avestruz'), 'Animal draw regression');
        }
    }
}

eval(ticket_source_function(dirname(__DIR__) . '/pages/orders/view_order.php', 'leowp_format_luck_numbers'));
foreach ([true, false] as $badges) {
    ob_start();
    leowp_format_luck_numbers(explode(',', $raw), 2000000, 'alert-success', $badges, 1);
    $html = ob_get_clean();
    foreach ($expectedList as $expected) {
        ticket_assert(str_contains($html, $expected), 'Checkout missing ' . $expected);
    }
    if ($badges) {
        ticket_assert(substr_count($html, 'wd-7') === count($expectedList), 'Badge widths');
    }
}

// A display-only endpoint must return formatted codes without writing to storage.
eval('class TicketEndpointTest { public $conn; public ' . ticket_source_function(dirname(__DIR__) . '/class/Main.php', 'view_numbers') . '}');
$endpoint = new TicketEndpointTest();
$endpoint->conn = new class {
    public function query($sql) {
        ticket_assert(str_starts_with($sql, 'SELECT '), 'Unexpected database write');
        ticket_assert(str_contains($sql, 'p.qty_numbers'), 'Campaign total missing from query');
        return new class {
            public function fetch_assoc() { return ['order_numbers' => '1,6516,0,', 'qty_numbers' => 2000000]; }
        };
    }
};
$_POST['id'] = 1;
$response = json_decode($endpoint->view_numbers(), true);
ticket_assert($response['order_numbers'] === '0000001,0006516,0000000', 'View numbers endpoint');

echo "OK: examples, 3000 generated numbers, legacy data, buyer renderers, zero ticket, animal draws and read-only endpoint.\n";
