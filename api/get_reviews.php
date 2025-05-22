<?php
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['destination_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Destination ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE destination_id = :destination_id ORDER BY created_at DESC");
    $stmt->execute([':destination_id' => $_GET['destination_id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'reviews' => $reviews]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 