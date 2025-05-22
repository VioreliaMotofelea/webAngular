<?php
require_once '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['destination_id']) || !isset($data['reviewer_name']) || 
    !isset($data['rating']) || !isset($data['comment'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO reviews (destination_id, reviewer_name, rating, comment) 
                           VALUES (:destination_id, :reviewer_name, :rating, :comment)");
    
    $stmt->execute([
        ':destination_id' => $data['destination_id'],
        ':reviewer_name' => $data['reviewer_name'],
        ':rating' => $data['rating'],
        ':comment' => $data['comment']
    ]);

    echo json_encode(['success' => true, 'message' => 'Review added successfully']);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 