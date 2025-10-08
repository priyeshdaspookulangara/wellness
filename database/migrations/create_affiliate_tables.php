<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    $db = db_connect();
    $sql = "
    CREATE TABLE `affiliates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `referral_code` varchar(50) NOT NULL,
      `status` enum('active','inactive') NOT NULL DEFAULT 'active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `referral_code` (`referral_code`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `affiliates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE `referrals` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `affiliate_id` int(11) NOT NULL,
      `referred_user_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `affiliate_id` (`affiliate_id`),
      KEY `referred_user_id` (`referred_user_id`),
      CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
      CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE `commissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `affiliate_id` int(11) NOT NULL,
      `order_id` int(11) NOT NULL,
      `commission_amount` decimal(10,2) NOT NULL,
      `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `affiliate_id` (`affiliate_id`),
      KEY `order_id` (`order_id`),
      CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE,
      CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $db->exec($sql);
    echo "Affiliate tables created successfully.\n";
}

function down() {
    $db = db_connect();
    $sql = "
    DROP TABLE IF EXISTS `commissions`;
    DROP TABLE IF EXISTS `referrals`;
    DROP TABLE IF EXISTS `affiliates`;
    ";
    $db->exec($sql);
    echo "Affiliate tables dropped successfully.\n";
}

// This allows running the migration from the command line
if (isset($argv[1])) {
    if ($argv[1] === 'up') {
        up();
    } elseif ($argv[1] === 'down') {
        down();
    }
}