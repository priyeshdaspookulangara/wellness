<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    $db = db_connect();
    $sql = "
    CREATE TABLE IF NOT EXISTS search_terms_analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        query_text VARCHAR(255) NOT NULL,
        total_searches INT DEFAULT 1,
        last_searched_at DATETIME NOT NULL,
        conversion_count INT DEFAULT 0,
        UNIQUE KEY (query_text)
    )
    ";
    $db->exec($sql);
    echo "search_terms_analytics table created successfully.\n";
}

function down() {
    $db = db_connect();
    $sql = "DROP TABLE IF EXISTS search_terms_analytics";
    $db->exec($sql);
    echo "search_terms_analytics table dropped successfully.\n";
}

// This allows running the migration from the command line
if (isset($argv[1])) {
    if ($argv[1] === 'up') {
        up();
    } elseif ($argv[1] === 'down') {
        down();
    }
}
