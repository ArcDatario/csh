<?php
require_once '../../db_connection.php';
session_start();

$role = $_SESSION['admin_role'] ?? '';

$viewedField = '';
switch($role) {
    case 'Owner': $viewedField = 'is_viewed_owner'; break;
    case 'General Manager': $viewedField = 'is_viewed_manager'; break;
    case 'Field Manager': $viewedField = 'is_viewed_field_manager'; break;
    case 'Designer': $viewedField = 'is_viewed_designer'; break;
    case 'Secretary': $viewedField = 'is_viewed_secretary'; break;
}

$success = false;
if ($viewedField) {
    $query = "UPDATE notification SET $viewedField = 'yes' WHERE $viewedField = 'no' OR $viewedField IS NULL OR $viewedField = ''";
    $success = $conn->query($query);
}

header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?>