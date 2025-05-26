<?php
require_once '../../db_connection.php';
session_start();

$role = $_SESSION['admin_role'] ?? '';

$notificationField = '';
$viewedField = '';
switch($role) {
    case 'Owner': 
        $notificationField = 'notify_owner';
        $viewedField = 'is_viewed_owner';
        break;
    case 'General Manager': 
        $notificationField = 'notify_manager';
        $viewedField = 'is_viewed_manager';
        break;
    case 'Field Manager': 
        $notificationField = 'notify_field';
        $viewedField = 'is_viewed_field_manager';
        break;
    case 'Designer': 
        $notificationField = 'notify_designer';
        $viewedField = 'is_viewed_designer';
        break;
    case 'Secretary': 
        $notificationField = 'notify_secretary';
        $viewedField = 'is_viewed_secretary';
        break;
}

$count = 0;
if ($notificationField) {
    $query = "SELECT COUNT(*) as count FROM notification 
              WHERE $notificationField = 'yes' 
              AND ($viewedField = '' OR $viewedField IS NULL OR $viewedField = 'no')";
    $result = $conn->query($query);
    $count = $result->fetch_assoc()['count'];
}

header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>