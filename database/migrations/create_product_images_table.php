<?php
// Migration to create the product_images table
require_once __DIR__ . '/../../config.php';

function up() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "
    CREATE TABLE IF NOT EXISTS `product_images` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `product_id` INT(11) NOT NULL,
      `image_url` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `product_id_idx` (`product_id`),
      CONSTRAINT `fk_product_images_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'product_images' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
    $conn->close();
}

// You can run this from the command line: php database/migrations/create_product_images_table.php
// Note: This is a simplified runner. A real application would have a more robust migration system.
if (php_sapi_name() === 'cli') {
    up();
}
?>