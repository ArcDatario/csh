<?php
session_start();
require '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$notification_id = $_POST['notification_id'];

try {
    $stmt = $conn->prepare("
        UPDATE notification 
        SET is_viewed_user = 'yes' 
        WHERE id = ? AND user_id = ? AND notify_user = 'yes'
    ");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>