<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    $db = get_db_connection();
    $sql = "
    CREATE TABLE IF NOT EXISTS user_activity_log (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        session_id VARCHAR(255) NOT NULL,
        event_timestamp DATETIME NOT NULL,
        event_type ENUM('page_view', 'product_view', 'search', 'click', 'form_submit', 'other_interaction') NOT NULL,
        page_path VARCHAR(255),
        page_title VARCHAR(255),
        product_id VARCHAR(255),
        search_query VARCHAR(255),
        ip_address VARCHAR(45),
        country_code CHAR(2),
        region VARCHAR(255),
        city VARCHAR(255),
        user_agent TEXT,
        referrer_url TEXT,
        browser_language VARCHAR(255),
        device_type ENUM('desktop', 'mobile', 'tablet', 'unknown'),
        custom_data JSON,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )
    ";
    $db->exec($sql);
    echo "user_activity_log table created successfully.\n";
}

function down() {
    $db = get_db_connection();
    $sql = "DROP TABLE IF EXISTS user_activity_log";
    $db->exec($sql);
    echo "user_activity_log table dropped successfully.\n";
}

// This allows running the migration from the command line
if (isset($argv[1])) {
    if ($argv[1] === 'up') {
        up();
    } elseif ($argv[1] === 'down') {
        down();
    }
}
