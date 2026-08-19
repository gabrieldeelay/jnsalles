-- Estrutura limpa para MySQL 8.0+ e MariaDB 10.4+
-- Nao contem clientes, pedidos, campanhas, credenciais ou administradores.

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(250) NOT NULL,
  `middlename` VARCHAR(250) DEFAULT NULL,
  `lastname` VARCHAR(250) NOT NULL,
  `username` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` TEXT DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` VARCHAR(254) DEFAULT NULL,
  `site` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(250) NOT NULL,
  `lastname` VARCHAR(250) NOT NULL,
  `phone` VARCHAR(32) NOT NULL,
  `email` VARCHAR(254) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `avatar` TEXT DEFAULT NULL,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cpf` VARCHAR(20) DEFAULT NULL,
  `zipcode` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `number` VARCHAR(30) DEFAULT NULL,
  `neighborhood` VARCHAR(150) DEFAULT NULL,
  `complement` VARCHAR(255) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `city` VARCHAR(150) DEFAULT NULL,
  `reference_point` VARCHAR(255) DEFAULT NULL,
  `is_affiliate` TINYINT(1) NOT NULL DEFAULT 0,
  `birth` DATE DEFAULT NULL,
  `instagram` VARCHAR(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customer_phone` (`phone`),
  KEY `idx_customer_cpf` (`cpf`),
  KEY `idx_customer_created` (`date_created`),
  KEY `idx_customer_affiliate` (`is_affiliate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `category_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `delete_flag` TINYINT(1) NOT NULL DEFAULT 0,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_status` (`status`, `delete_flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `brand` VARCHAR(191) DEFAULT NULL,
  `name` TEXT NOT NULL,
  `description` LONGTEXT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `image_path` TEXT DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `delete_flag` TINYINT(1) NOT NULL DEFAULT 0,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type_of_draw` TINYINT(1) NOT NULL DEFAULT 1,
  `qty_numbers` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `min_purchase` INT UNSIGNED NOT NULL DEFAULT 1,
  `max_purchase` INT UNSIGNED NOT NULL DEFAULT 0,
  `slug` VARCHAR(191) NOT NULL,
  `pending_numbers` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `paid_numbers` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `ranking_qty` INT UNSIGNED NOT NULL DEFAULT 0,
  `enable_ranking` TINYINT(1) NOT NULL DEFAULT 0,
  `image_gallery` LONGTEXT DEFAULT NULL,
  `enable_progress_bar` TINYINT(1) NOT NULL DEFAULT 0,
  `enable_progress_bar_fake` TINYINT(1) DEFAULT 0,
  `enable_progress_bar_fake_value` DECIMAL(10,2) DEFAULT 0.00,
  `draw_number` VARCHAR(100) DEFAULT NULL,
  `status_display` VARCHAR(30) NOT NULL DEFAULT '1',
  `subtitle` TEXT DEFAULT NULL,
  `date_of_draw` VARCHAR(255) DEFAULT NULL,
  `limit_order_remove` INT UNSIGNED DEFAULT 0,
  `discount_qty` LONGTEXT DEFAULT NULL,
  `discount_amount` LONGTEXT DEFAULT NULL,
  `discount_roleta` LONGTEXT DEFAULT NULL,
  `roleta_qty` LONGTEXT DEFAULT NULL,
  `roleta_amount` LONGTEXT DEFAULT NULL,
  `enable_discount` TINYINT(1) DEFAULT 0,
  `enable_double` VARCHAR(255) NOT NULL DEFAULT '0',
  `double_ini` VARCHAR(250) DEFAULT NULL,
  `double_fim` VARCHAR(250) DEFAULT NULL,
  `enable_cumulative_discount` TINYINT(1) DEFAULT 0,
  `enable_sale` TINYINT(1) DEFAULT 0,
  `sale_qty` INT UNSIGNED DEFAULT 0,
  `sale_price` DECIMAL(12,2) DEFAULT 0.00,
  `ranking_message` TEXT DEFAULT NULL,
  `enable_ranking_show` TINYINT(1) DEFAULT 0,
  `draw_winner` TEXT DEFAULT NULL,
  `private_draw` TINYINT(1) DEFAULT 0,
  `featured_draw` TINYINT(1) DEFAULT 0,
  `cotas_premiadas` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_premios` MEDIUMTEXT DEFAULT NULL,
  `cotas_premiadas_descricao` TEXT DEFAULT NULL,
  `limit_orders` INT UNSIGNED DEFAULT 0,
  `ranking_type` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `qty_select_1` INT UNSIGNED NOT NULL DEFAULT 10,
  `qty_select_2` INT UNSIGNED NOT NULL DEFAULT 20,
  `qty_select_3` INT UNSIGNED NOT NULL DEFAULT 50,
  `qty_select_4` INT UNSIGNED NOT NULL DEFAULT 100,
  `qty_select_5` INT UNSIGNED NOT NULL DEFAULT 200,
  `qty_select_6` INT UNSIGNED NOT NULL DEFAULT 300,
  `status_auto_cota` TINYINT(1) NOT NULL DEFAULT 0,
  `valor_base_auto` INT UNSIGNED NOT NULL DEFAULT 50,
  `quantidade_numeros` INT UNSIGNED NOT NULL DEFAULT 2,
  `tipo_auto_cota` LONGTEXT DEFAULT NULL,
  `up` TINYINT(1) NOT NULL DEFAULT 0,
  `quantidade_auto_cota` INT UNSIGNED NOT NULL DEFAULT 0,
  `quantidade_auto_cota_diario` TINYINT(1) DEFAULT 0,
  `roleta` TINYINT(1) NOT NULL DEFAULT 0,
  `box` TINYINT(1) NOT NULL DEFAULT 0,
  `enable_upsell` TINYINT(1) NOT NULL DEFAULT 0,
  `qtd_upsell` VARCHAR(255) DEFAULT NULL,
  `desconto_upsell` VARCHAR(255) DEFAULT NULL,
  `status_auto_cota_roleta` TINYINT(1) DEFAULT 0,
  `tipo_auto_cota_roleta` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_roleta` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_premios_roleta` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_descricao_roleta` LONGTEXT DEFAULT NULL,
  `discount_box` LONGTEXT DEFAULT NULL,
  `box_qty` LONGTEXT DEFAULT NULL,
  `box_amount` LONGTEXT DEFAULT NULL,
  `status_auto_cota_box` TINYINT(1) DEFAULT 0,
  `tipo_auto_cota_box` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_box` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_premios_box` LONGTEXT DEFAULT NULL,
  `cotas_premiadas_descricao_box` LONGTEXT DEFAULT NULL,
  `enable_ranking_definido` TINYINT(1) DEFAULT 0,
  `ranking_ini` VARCHAR(255) DEFAULT NULL,
  `ranking_fim` VARCHAR(255) DEFAULT NULL,
  `cota_diaria_ini` VARCHAR(255) DEFAULT NULL,
  `cota_diaria_fim` VARCHAR(255) DEFAULT NULL,
  `probabilidade` INT UNSIGNED DEFAULT NULL,
  `habilitar_cota_sorte` TINYINT(1) DEFAULT 0,
  `cota_sorte_ini` VARCHAR(255) DEFAULT NULL,
  `cota_sorte_fim` VARCHAR(255) DEFAULT NULL,
  `cota_sorte` VARCHAR(255) DEFAULT NULL,
  `quantidade_compra_sorte` VARCHAR(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_slug` (`slug`),
  KEY `idx_product_status` (`status`, `delete_flag`),
  KEY `idx_product_draw_date` (`date_of_draw`),
  KEY `idx_product_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category_list` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` BIGINT NOT NULL DEFAULT 0,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stock_product` (`product_id`),
  CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cart_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_customer_product` (`customer_id`, `product_id`),
  KEY `idx_cart_product` (`product_id`),
  CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) DEFAULT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `quantity` BIGINT UNSIGNED DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=pending, 2=paid, 3=cancelled',
  `roleta` INT NOT NULL DEFAULT 0,
  `box` INT NOT NULL DEFAULT 0,
  `roleta_aberta` INT NOT NULL DEFAULT 0,
  `box_aberta` INT NOT NULL DEFAULT 0,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `product_name` TEXT DEFAULT NULL,
  `order_token` VARCHAR(100) DEFAULT NULL,
  `order_numbers` LONGTEXT DEFAULT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `payment_method` VARCHAR(100) DEFAULT NULL,
  `order_expiration` VARCHAR(100) DEFAULT NULL,
  `pix_code` LONGTEXT DEFAULT NULL,
  `pix_qrcode` LONGTEXT DEFAULT NULL,
  `txid` VARCHAR(191) DEFAULT NULL,
  `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
  `whatsapp_status` VARCHAR(100) DEFAULT NULL,
  `dwapi_status` VARCHAR(100) DEFAULT NULL,
  `id_mp` VARCHAR(191) DEFAULT NULL,
  `referral_id` INT UNSIGNED DEFAULT NULL,
  `pixel_sell` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_code` (`code`),
  UNIQUE KEY `uq_order_token` (`order_token`),
  KEY `idx_order_customer` (`customer_id`),
  KEY `idx_order_product_status_date` (`product_id`, `status`, `date_created`),
  KEY `idx_order_status_expiration` (`status`, `order_expiration`),
  KEY `idx_order_payment_method` (`payment_method`),
  KEY `idx_order_provider_id` (`id_mp`),
  KEY `idx_order_txid` (`txid`),
  KEY `idx_order_referral` (`referral_id`),
  CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `raffle_draws` (
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
  KEY `idx_raffle_draw_user` (`drawn_by`),
  CONSTRAINT `fk_raffle_draw_product` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_raffle_draw_order` FOREIGN KEY (`order_id`) REFERENCES `order_list` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_raffle_draw_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_raffle_draw_user` FOREIGN KEY (`drawn_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`order_id`, `product_id`),
  KEY `idx_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `order_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `referral` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `referral_code` VARCHAR(100) DEFAULT NULL,
  `percentage` DECIMAL(7,2) NOT NULL DEFAULT 0.00,
  `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `amount_pending` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `customer_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_referral_code` (`referral_code`),
  UNIQUE KEY `uq_referral_customer` (`customer_id`),
  CONSTRAINT `fk_referral_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `referral_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `referral_id` INT UNSIGNED NOT NULL,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referral_transaction_referral` (`referral_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_info` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meta_field` VARCHAR(191) NOT NULL,
  `meta_value` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_system_info_field` (`meta_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `origin` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_date` (`date`),
  KEY `idx_logs_origin` (`origin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_view` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `page` VARCHAR(255) NOT NULL,
  `origin` TINYINT(1) NOT NULL COMMENT '1=normal, 2=pixel',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_page_view_product` (`product_id`),
  KEY `idx_page_view_customer` (`customer_id`),
  KEY `idx_page_view_date` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `config` VARCHAR(2000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_config_user` (`user_id`),
  CONSTRAINT `fk_config_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_migrations_name` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `request_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) DEFAULT NULL,
  `fullname` VARCHAR(255) NOT NULL,
  `contact` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `location` TEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `videos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `youtube_id` VARCHAR(64) NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT DEFAULT NULL,
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Somente configuracoes indispensaveis. Gateways iniciam desativados e sem credenciais.
INSERT INTO `system_info` (`meta_field`, `meta_value`) VALUES
  ('name', 'JNSalles'),
  ('site_description', 'Participe de campanhas com cotas numeradas, acompanhe seus pedidos e confira todas as informações da premiação com clareza e segurança.'),
  ('terms', ''),
  ('theme', '1'),
  ('gateway_provider', 'none'),
  ('gateway', '1'),
  ('mercadopago', '2'),
  ('gerencianet', '2'),
  ('paggue', '2'),
  ('openpix', '2'),
  ('pay2m', '2'),
  ('venopag', '2'),
  ('venopag_default_document', ''),
  ('venopag_min_amount', '1.00'),
  ('enable_cpf', '2'),
  ('enable_email', '2'),
  ('enable_address', '2'),
  ('enable_birth', '2'),
  ('enable_instagram', '2'),
  ('enable_password', '2'),
  ('enable_two_phone', '2'),
  ('enable_multiple_order', '2'),
  ('enable_share', '2'),
  ('enable_groups', '2'),
  ('enable_footer', '1'),
  ('enable_pixel', '2'),
  ('enable_ga4', '2'),
  ('enable_gtm', '2'),
  ('enable_dwapi', '2'),
  ('enable_hide_numbers', '2')
ON DUPLICATE KEY UPDATE `meta_value` = VALUES(`meta_value`);

SET FOREIGN_KEY_CHECKS = 1;
