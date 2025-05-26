<?php
require_once '../../db_connection.php';
session_start();

$role = $_SESSION['admin_role'] ?? '';
$notificationId = $_POST['id'] ?? 0;

$viewedField = '';
switch($role) {
    case 'Owner': $viewedField = 'is_viewed_owner'; break;
    case 'General Manager': $viewedField = 'is_viewed_manager'; break;
    case 'Field Manager': $viewedField = 'is_viewed_field_manager'; break;
    case 'Designer': $viewedField = 'is_viewed_designer'; break;
    case 'Secretary': $viewedField = 'is_viewed_secretary'; break;
}

$success = false;
if ($viewedField && $notificationId) {
    $query = "UPDATE notification SET $viewedField = 'yes' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $notificationId);
    $success = $stmt->execute();
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?>