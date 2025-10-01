<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/");
    exit;
}

function sluggify($string) {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    $string = strtolower($string);
    if (empty($string)) {
        return 'n-a';
    }
    return $string;
}

function recursive_copy($src, $dst) {
    if (is_dir($src)) {
        if (!is_dir($dst)) {
            mkdir($dst);
        }
        $files = scandir($src);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                recursive_copy("$src/$file", "$dst/$file");
            }
        }
    } else if (file_exists($src)) {
        copy($src, $dst);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;

    if (!$product_id) {
        $_SESSION['error_message'] = "Please select a product.";
        header("Location: index.php");
        exit;
    }

    // Fetch product name to create a slug
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        $_SESSION['error_message'] = "Product not found.";
        header("Location: index.php");
        exit;
    }

    $product_slug = sluggify($product['name']);
    $new_dir = __DIR__ . '/../../' . $product_slug;

    if (file_exists($new_dir)) {
        $_SESSION['error_message'] = "A landing page for this product already exists: " . htmlspecialchars($product_slug);
        header("Location: index.php");
        exit;
    }

    $template_dir = __DIR__ . '/../../landing-page-template';

    if (!is_dir($template_dir)) {
        $_SESSION['error_message'] = "Landing page template directory not found.";
        header("Location: index.php");
        exit;
    }

    // Create the new directory and copy template files
    mkdir($new_dir, 0755, true);
    recursive_copy($template_dir, $new_dir);

    // Customize the new landing page
    $landing_page_index = $new_dir . '/index.php';
    if (file_exists($landing_page_index)) {
        $content = file_get_contents($landing_page_index);
        // Replace the placeholder product ID with the actual product ID
        $content = preg_replace('/\$product_id\s*=\s*\d+;/', '$product_id = ' . $product_id . ';', $content);
        file_put_contents($landing_page_index, $content);
    }

    $_SESSION['success_message'] = "Landing page created successfully at: <a href='" . SITE_URL . $product_slug . "' target='_blank'>" . $product_slug . "</a>";
    header("Location: index.php");
    exit;
} else {
    // Redirect if accessed directly
    header("Location: index.php");
    exit;
}
?>