<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user'); // Replace with your database username
define('DB_PASS', 'your_db_password'); // Replace with your database password
define('DB_NAME', 'wellness_ecommerce'); // Replace with your database name

// Site Configuration
define('SITE_URL', 'http://localhost/wellness-platform/'); // Ensure trailing slash
define('SITE_NAME', 'Wellness Wonders');
define('ADMIN_EMAIL', 'admin@example.com');

// Error Reporting (Switch based on environment or comment out for production)

// Development Settings:
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

// Production Settings (Uncomment and configure error_log path for production):
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
// Ensure this path is writable by the web server in a real production environment
// For this project, it will log errors to a file named 'php_errors.log' in the project root
ini_set('error_log', __DIR__ . '/php_errors.log');


// Base path for includes (useful for file system includes)
define('BASE_PATH', __DIR__ . '/');
?>
