<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Check for admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . SITE_URL . "/login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db_connect();

    $page_id = $_POST['page_id'];
    $page_name = $_POST['page_name'];
    $slug = $_POST['slug'];
    $page_title = $_POST['page_title'];
    $hero_heading = $_POST['hero_heading'];
    $hero_subheading = $_POST['hero_subheading'];
    $main_content_html = $_POST['main_content_html'];
    $button_text = $_POST['button_text'];
    $button_target_url = $_POST['button_target_url'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];

    // Generate folder path
    $folder_path = __DIR__ . '/../../landing_pages/' . $slug;

    // Create or update logic
    if (empty($page_id)) {
        // Create
        $sql = "INSERT INTO landing_pages (page_name, slug, folder_path, page_title, hero_heading, hero_subheading, main_content_html, button_text, button_target_url, is_active, created_by, created_at, updated_at) VALUES (:page_name, :slug, :folder_path, :page_title, :hero_heading, :hero_subheading, :main_content_html, :button_text, :button_target_url, :is_active, :created_by, NOW(), NOW())";
        $stmt = $db->prepare($sql);
    } else {
        // Update
        $sql = "UPDATE landing_pages SET page_name = :page_name, slug = :slug, folder_path = :folder_path, page_title = :page_title, hero_heading = :hero_heading, hero_subheading = :hero_subheading, main_content_html = :main_content_html, button_text = :button_text, button_target_url = :button_target_url, is_active = :is_active, updated_at = NOW() WHERE page_id = :page_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':page_id', $page_id);
    }

    $stmt->bindValue(':page_name', $page_name);
    $stmt->bindValue(':slug', $slug);
    $stmt->bindValue(':folder_path', $folder_path);
    $stmt->bindValue(':page_title', $page_title);
    $stmt->bindValue(':hero_heading', $hero_heading);
    $stmt->bindValue(':hero_subheading', $hero_subheading);
    $stmt->bindValue(':main_content_html', $main_content_html);
    $stmt->bindValue(':button_text', $button_text);
    $stmt->bindValue(':button_target_url', $button_target_url);
    $stmt->bindValue(':is_active', $is_active);
    if (empty($page_id)) {
        $stmt->bindValue(':created_by', $created_by);
    }
    $stmt->execute();

    // Create the directory and index.php file
    if (!is_dir($folder_path)) {
        mkdir($folder_path, 0755, true);
    }

    // Get the template
    $template = file_get_contents(__DIR__ . '/../../templates/landing_page_template.php');

    // Replace placeholders
    $template = str_replace('[PAGE_TITLE_PLACEHOLDER]', $page_title, $template);
    $template = str_replace('[HERO_HEADING_PLACEHOLDER]', $hero_heading, $template);
    $template = str_replace('[HERO_SUBHEADING_PLACEHOLDER]', $hero_subheading, $template);
    $template = str_replace('[MAIN_CONTENT_HTML_PLACEHOLDER]', $main_content_html, $template);
    $template = str_replace('[BUTTON_TEXT_PLACEHOLDER]', $button_text, $template);
    $template = str_replace('[BUTTON_TARGET_URL_PLACEHOLDER]', $button_target_url, $template);
    $template = str_replace('[SLUG_PLACEHOLDER]', $slug, $template);

    file_put_contents($folder_path . '/index.php', $template);

    header("Location: index.php");
    exit;
}
?>
