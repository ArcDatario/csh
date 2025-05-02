<?php
require_once '../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

$query = "SELECT id, name, quantity FROM inventory";
$result = $conn->query($query);

if ($result) {
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $response = [
        'success' => true,
        'data' => $items
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>