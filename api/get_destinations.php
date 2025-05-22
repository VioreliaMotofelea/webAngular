<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db_connect.php';

$items_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$country = isset($_GET['country']) ? $_GET['country'] : '';
$min_cost = isset($_GET['min_cost']) ? (float)$_GET['min_cost'] : 0;
$max_cost = (isset($_GET['max_cost']) && $_GET['max_cost'] !== '') ? (float)$_GET['max_cost'] : PHP_FLOAT_MAX;

$query = "SELECT * FROM destinations WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (location LIKE :search OR description LIKE :search OR tourist_targets LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($country) {
    $query .= " AND country = :country";
    $params[':country'] = $country;
}
if ($min_cost > 0) {
    $query .= " AND cost_per_day >= :min_cost";
    $params[':min_cost'] = $min_cost;
}
if ($max_cost < PHP_FLOAT_MAX) {
    $query .= " AND cost_per_day <= :max_cost";
    $params[':max_cost'] = $max_cost;
}

$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_items = $stmt->fetchColumn();
$total_pages = max(1, ceil($total_items / $items_per_page));

$query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'destinations' => $destinations,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_items' => $total_items
]); 