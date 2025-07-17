<?php
// Database connection logic
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For development, you might want to see the error.
    // For production, log this error and show a generic message.
    error_log("Database Connection Error: " . $e->getMessage());
    // In a real application, you'd want to handle this more gracefully
    // than just killing the script.
    die("Error: Could not connect to the database. Please try again later.");
}
?>
