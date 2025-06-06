<?php
require_once '../../db_connection.php';
require_once '../../auth_check.php';

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    $stmt = $conn->prepare("
        SELECT sr.*, a.username as field_manager_name 
        FROM stock_requests sr
        LEFT JOIN admins a ON sr.field_manager_id = a.id
        ORDER BY sr.request_date DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    $response['success'] = true;
    $response['data'] = $requests;
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>