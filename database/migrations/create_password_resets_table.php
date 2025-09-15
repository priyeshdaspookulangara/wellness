<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    global $db;
    $sql = "
    CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at INT NOT NULL
    )
    ";
    if ($db->query($sql) === TRUE) {
        echo "password_resets table created successfully.\n";
    } else {
        echo "Error creating table: " . $db->error . "\n";
    }
}

function down() {
    global $db;
    $sql = "DROP TABLE IF EXISTS password_resets";
    if ($db->query($sql) === TRUE) {
        echo "password_resets table dropped successfully.\n";
    } else {
        echo "Error dropping table: " . $db->error . "\n";
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
