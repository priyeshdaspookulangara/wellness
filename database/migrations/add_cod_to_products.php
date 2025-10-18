<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    $db = db_connect();
    $sql = "ALTER TABLE `products` ADD `is_cod_available` BOOLEAN NOT NULL DEFAULT TRUE AFTER `is_on_sale`";

    try {
        $db->exec($sql);
        echo "Column `is_cod_available` added to `products` table successfully.\n";
    } catch (PDOException $e) {
        echo "Error adding column to products table: " . $e->getMessage() . "\n";
    }
}

function down() {
    $db = db_connect();
    $sql = "ALTER TABLE `products` DROP COLUMN `is_cod_available`";

    try {
        $db->exec($sql);
        echo "Column `is_cod_available` dropped from `products` table successfully.\n";
    } catch (PDOException $e) {
        echo "Error dropping column from products table: " . $e->getMessage() . "\n";
    }
}

// This allows running the migration from the command line
if (isset($argv[1])) {
    if ($argv[1] === 'up') {
        up();
    } elseif ($argv[1] === 'down') {
        down();
    }
}
?>