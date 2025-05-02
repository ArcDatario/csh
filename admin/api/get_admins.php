<?php
require_once '../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

$query = "SELECT id, username, fullname, role FROM admins";
$result = $conn->query($query);

if ($result) {
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
    $response = [
        'success' => true,
        'data' => $admins
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>