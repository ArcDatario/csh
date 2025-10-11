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
    // Get notifications for this user where notify_user = 'yes'
    $stmt = $conn->prepare("
        SELECT id, content, created_at, is_viewed_user 
        FROM notification 
        WHERE user_id = ? AND notify_user = 'yes' 
        ORDER BY created_at DESC 

    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    $unread_count = 0;
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if ($row['is_viewed_user'] === 'no') {
            $unread_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>