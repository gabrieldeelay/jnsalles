<?php

/** Display width follows the campaign total, not the length of a stored ticket. */
function jnsalles_ticket_width($campaignTotal)
{
    return strlen((string) max(0, (int) $campaignTotal));
}

/** Presentation only: never use the formatted value to reassign ownership. */
function jnsalles_format_ticket($number, $campaignTotal)
{
    $number = trim((string) $number);
    if (preg_match('/^\d{1,3}(?:\.\d{3})+$/D', $number)) {
        $number = str_replace('.', '', $number);
    }
    if ($number === '' || !ctype_digit($number) || (int) $campaignTotal <= 0) {
        return $number;
    }
    $digits = ltrim($number, '0');
    // Padding must never truncate a ticket, including legacy out-of-range data.
    return str_pad($digits === '' ? '0' : $digits, jnsalles_ticket_width($campaignTotal), '0', STR_PAD_LEFT);
}

function jnsalles_format_ticket_list($numbers, $campaignTotal)
{
    $result = [];
    foreach (is_array($numbers) ? $numbers : explode(',', (string) $numbers) as $number) {
        if (trim((string) $number) !== '') {
            $result[] = jnsalles_format_ticket($number, $campaignTotal);
        }
    }
    return $result;
}
