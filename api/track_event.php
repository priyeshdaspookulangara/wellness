<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Basic security checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType !== "application/json") {
    http_response_code(400);
    exit;
}

$content = trim(file_get_contents("php://input"));
$decoded = json_decode($content, true);

if (!is_array($decoded)) {
    http_response_code(400);
    exit;
}

// For now, we will directly insert into the database.
// In a high-traffic environment, this should be queued.
$db = db_connect();

// IP to Country lookup (simplified, using a free API)
$ip_address = $_SERVER['REMOTE_ADDR'];
$country_code = 'XX'; // Default
$region = '';
$city = '';

// Use a free GeoIP service
$geo_url = "http://ip-api.com/json/{$ip_address}?fields=countryCode,regionName,city";
$geo_data = @json_decode(@file_get_contents($geo_url), true);

if ($geo_data && $geo_data['status'] === 'success') {
    $country_code = $geo_data['countryCode'];
    $region = $geo_data['regionName'];
    $city = $geo_data['city'];
}

$stmt = $db->prepare("
    INSERT INTO user_activity_log (
        user_id, session_id, event_timestamp, event_type, page_path, page_title,
        product_id, search_query, ip_address, country_code, region, city,
        user_agent, referrer_url, browser_language, device_type, custom_data
    ) VALUES (
        :user_id, :session_id, NOW(), :event_type, :page_path, :page_title,
        :product_id, :search_query, :ip_address, :country_code, :region, :city,
        :user_agent, :referrer_url, :browser_language, :device_type, :custom_data
    )
");

$stmt->bindValue(':user_id', $decoded['user_id'] ?? null, PDO::PARAM_INT);
$stmt->bindValue(':session_id', $decoded['session_id'] ?? null);
$stmt->bindValue(':event_type', $decoded['event_type'] ?? null);
$stmt->bindValue(':page_path', $decoded['page_path'] ?? null);
$stmt->bindValue(':page_title', $decoded['page_title'] ?? null);
$stmt->bindValue(':product_id', $decoded['custom_data']['product_id'] ?? null);
$stmt->bindValue(':search_query', $decoded['custom_data']['search_query'] ?? null);
$stmt->bindValue(':ip_address', $ip_address);
$stmt->bindValue(':country_code', $country_code);
$stmt->bindValue(':region', $region);
$stmt->bindValue(':city', $city);
$stmt->bindValue(':user_agent', $decoded['user_agent'] ?? null);
$stmt->bindValue(':referrer_url', $decoded['referrer_url'] ?? null);
$stmt->bindValue(':browser_language', $decoded['browser_language'] ?? null);
$stmt->bindValue(':device_type', $decoded['device_type'] ?? null);
$stmt->bindValue(':custom_data', json_encode($decoded['custom_data'] ?? []));

$stmt->execute();

if ($decoded['event_type'] === 'search' && !empty($decoded['custom_data']['search_query'])) {
    $search_stmt = $db->prepare("
        INSERT INTO search_terms_analytics (query_text, last_searched_at)
        VALUES (:query_text, NOW())
        ON DUPLICATE KEY UPDATE total_searches = total_searches + 1, last_searched_at = NOW()
    ");
    $search_stmt->bindValue(':query_text', $decoded['custom_data']['search_query']);
    $search_stmt->execute();
}

http_response_code(204); // No Content
