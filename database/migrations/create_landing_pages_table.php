<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

function up() {
    $db = db_connect();
    $sql = "
    CREATE TABLE IF NOT EXISTS landing_pages (
        page_id INT AUTO_INCREMENT PRIMARY KEY,
        page_name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        folder_path VARCHAR(255) NOT NULL,
        page_title VARCHAR(255) NOT NULL,
        hero_heading VARCHAR(255) NOT NULL,
        hero_subheading TEXT,
        main_content_html TEXT,
        button_text VARCHAR(255) NOT NULL,
        button_target_url VARCHAR(255) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )
    ";
    $db->exec($sql);
    echo "landing_pages table created successfully.\n";
}

function down() {
    $db = db_connect();
    $sql = "DROP TABLE IF EXISTS landing_pages";
    $db->exec($sql);
    echo "landing_pages table dropped successfully.\n";
}

// This allows running the migration from the command line
if (isset($argv[1])) {
    if ($argv[1] === 'up') {
        up();
    } elseif ($argv[1] === 'down') {
        down();
    }
}
