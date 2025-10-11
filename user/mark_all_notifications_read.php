<?php
session_start();
require '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        UPDATE notification 
        SET is_viewed_user = 'yes' 
        WHERE user_id = ? AND notify_user = 'yes' AND is_viewed_user = 'no'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>