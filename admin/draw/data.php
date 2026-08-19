<?php

if (!function_exists('jnsalles_draw_ensure_schema')) {
    function jnsalles_draw_ensure_schema($conn)
    {
        return (bool) $conn->query(
            "CREATE TABLE IF NOT EXISTS `raffle_draws` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `product_id` INT UNSIGNED DEFAULT NULL,
                `product_name_snapshot` TEXT NOT NULL,
                `order_id` INT UNSIGNED DEFAULT NULL,
                `customer_id` INT UNSIGNED DEFAULT NULL,
                `winning_number` VARCHAR(191) NOT NULL,
                `winner_name_snapshot` VARCHAR(500) NOT NULL,
                `phone_masked_snapshot` VARCHAR(40) NOT NULL,
                `eligible_entries` BIGINT UNSIGNED NOT NULL,
                `random_position` BIGINT UNSIGNED NOT NULL,
                `audit_hash` CHAR(64) NOT NULL,
                `drawn_by` INT UNSIGNED DEFAULT NULL,
                `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_raffle_draw_audit_hash` (`audit_hash`),
                UNIQUE KEY `uq_raffle_draw_product_number` (`product_id`, `winning_number`),
                KEY `idx_raffle_draw_product_date` (`product_id`, `date_created`),
                KEY `idx_raffle_draw_order` (`order_id`),
                KEY `idx_raffle_draw_customer` (`customer_id`),
                KEY `idx_raffle_draw_user` (`drawn_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
}

if (!function_exists('jnsalles_draw_mask_phone')) {
    function jnsalles_draw_mask_phone($phone)
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($digits) >= 12 && substr($digits, 0, 2) === '55') {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) < 6) {
            return '(**) ***-****';
        }

        $ddd = substr($digits, 0, 2);
        $local = substr($digits, 2);
        $first = substr($local, 0, 1);
        $visibleMiddle = strlen($local) >= 7 ? substr($local, 3, 4) : substr($local, 2, 3);
        return '(' . $ddd . ') ' . $first . '**-' . str_pad($visibleMiddle, 4, '*') . '**';
    }
}

if (!function_exists('jnsalles_draw_numbers')) {
    function jnsalles_draw_numbers($rawNumbers)
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $rawNumbers)), static function ($number) {
            return $number !== '';
        }));
    }
}

if (!function_exists('jnsalles_draw_history')) {
    function jnsalles_draw_history($conn, $limit = 10)
    {
        $limit = max(1, min(50, (int) $limit));
        return $conn->query(
            'SELECT id, product_name_snapshot, winning_number, winner_name_snapshot, phone_masked_snapshot, '
            . 'eligible_entries, audit_hash, date_created FROM raffle_draws ORDER BY id DESC LIMIT ' . $limit
        );
    }
}
