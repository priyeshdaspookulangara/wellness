<?php
require_once __DIR__ . '/../config.php';

// Create MySQLi connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    // In a real application, you might want to log this error or display a more user-friendly message
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8mb4 (recommended for modern applications)
mysqli_set_charset($conn, "utf8mb4");

// Function to safely escape strings for SQL queries
function escape_string($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}
?>
