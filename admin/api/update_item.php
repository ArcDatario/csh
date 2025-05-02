<?php
require_once '../../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$id = $conn->real_escape_string($_POST['id'] ?? '');
$name = $conn->real_escape_string($_POST['name'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);

if (empty($id) || empty($name)) {
    $response['message'] = 'Required fields are missing';
    echo json_encode($response);
    exit;
}

if ($quantity < 0) {
    $response['message'] = 'Quantity must be a positive number';
    echo json_encode($response);
    exit;
}

// Update item
$query = "UPDATE inventory SET name = ?, quantity = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $name, $quantity, $id);

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => 'Item updated successfully'
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>