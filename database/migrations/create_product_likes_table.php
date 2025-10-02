<?php
// Migration to create the product_likes table
require_once __DIR__ . '/../../config.php';

function up() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "
    CREATE TABLE IF NOT EXISTS `product_likes` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `product_id` INT(11) NOT NULL,
      `user_id` INT(11) NULL,
      `session_id` VARCHAR(255) NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      INDEX `product_id_idx` (`product_id`),
      UNIQUE KEY `user_product_like` (`user_id`, `product_id`),
      UNIQUE KEY `session_product_like` (`session_id`, `product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'product_likes' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
    $conn->close();
}

// Example usage from command line: php database/migrations/create_product_likes_table.php
up();
?>