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

if (!function_exists('jnsalles_draw_demo_phone')) {
    function jnsalles_draw_demo_phone()
    {
        $areaCodes = [
            11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 24, 27, 28,
            31, 32, 33, 34, 35, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48, 49,
            51, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69, 71, 73, 74,
            75, 77, 79, 81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94,
            95, 96, 97, 98, 99,
        ];
        $ddd = $areaCodes[random_int(0, count($areaCodes) - 1)];
        $lastFour = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        return sprintf('(%02d) 9****-%s', $ddd, $lastFour);
    }
}

if (!function_exists('jnsalles_draw_free_demo_number')) {
    function jnsalles_draw_free_demo_number($conn, $productId, $quantity)
    {
        $quantity = max(0, (int) $quantity);
        if ($quantity < 1) {
            return null;
        }

        $maxNumber = $quantity - 1;
        $digits = max(1, strlen((string) $maxNumber));
        $candidate = '';
        $statement = $conn->prepare(
            "SELECT 1 FROM order_list
             WHERE product_id = ? AND status <> 3
               AND FIND_IN_SET(?, REPLACE(COALESCE(order_numbers, ''), ' ', '')) > 0
             LIMIT 1"
        );
        if (!$statement) {
            return null;
        }
        $statement->bind_param('is', $productId, $candidate);

        for ($attempt = 0; $attempt < 200; $attempt++) {
            $candidate = str_pad((string) random_int(0, $maxNumber), $digits, '0', STR_PAD_LEFT);
            $statement->execute();
            $occupied = $statement->get_result()->num_rows > 0;
            if (!$occupied) {
                $statement->close();
                return $candidate;
            }
        }
        $statement->close();
        return null;
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
