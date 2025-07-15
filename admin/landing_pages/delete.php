<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Check for admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . SITE_URL . "/login");
    exit;
}

if (isset($_GET['id'])) {
    $db = db_connect();

    // First, get the folder path to delete it later
    $stmt = $db->prepare("SELECT folder_path FROM landing_pages WHERE page_id = :id");
    $stmt->bindValue(':id', $_GET['id']);
    $stmt->execute();
    $page = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($page) {
        // Delete the database record
        $delete_stmt = $db->prepare("DELETE FROM landing_pages WHERE page_id = :id");
        $delete_stmt->bindValue(':id', $_GET['id']);
        $delete_stmt->execute();

        // Delete the folder and its contents
        if (is_dir($page['folder_path'])) {
            // A simple recursive delete function
            function deleteDir($dirPath) {
                if (! is_dir($dirPath)) {
                    throw new InvalidArgumentException("$dirPath must be a directory");
                }
                if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                    $dirPath .= '/';
                }
                $files = glob($dirPath . '*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir($dirPath);
            }
            deleteDir($page['folder_path']);
        }
    }
}

header("Location: index.php");
exit;
?>
