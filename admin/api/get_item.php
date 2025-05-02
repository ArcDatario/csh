<?php
require_once '../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    $query = "SELECT id, name, quantity FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $response = [
            'success' => true,
            'id' => $item['id'],
            'name' => $item['name'],
            'quantity' => $item['quantity']
        ];
    } else {
        $response['message'] = 'Item not found';
    }
    $stmt->close();
} else {
    $response['message'] = 'Item ID not provided';
}

echo json_encode($response);
?>