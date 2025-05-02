<?php
require_once '../../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$id = $conn->real_escape_string($_POST['id'] ?? '');

if (empty($id)) {
    $response['message'] = 'Item ID not provided';
    echo json_encode($response);
    exit;
}

$query = "DELETE FROM inventory WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response = [
            'success' => true,
            'message' => 'Item deleted successfully'
        ];
    } else {
        $response['message'] = 'Item not found';
    }
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>