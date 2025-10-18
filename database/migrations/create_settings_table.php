<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    $db = db_connect();
    $sql = "
    CREATE TABLE `settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(255) NOT NULL,
      `setting_value` text DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('affiliate_commission_rate', '10');
    ";

    try {
        $db->exec($sql);
        echo "Settings table created and default commission rate set successfully.\n";
    } catch (PDOException $e) {
        echo "Error creating settings table: " . $e->getMessage() . "\n";
    }
}

function down() {
    $db = db_connect();
    $sql = "DROP TABLE IF EXISTS `settings`";

    try {
        $db->exec($sql);
        echo "Settings table dropped successfully.\n";
    } catch (PDOException $e) {
        echo "Error dropping settings table: " . $e->getMessage() . "\n";
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