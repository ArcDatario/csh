<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php'; // Include your logging helper
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$id = $conn->real_escape_string($_POST['id'] ?? '');

if (empty($id)) {
    $response['message'] = 'Item ID not provided';
    echo json_encode($response);
    exit;
}

// Get item info before deletion for logging
$stmt = $conn->prepare("SELECT name, quantity FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$old_data = $stmt->get_result()->fetch_assoc();

if (!$old_data) {
    $response['message'] = 'Item not found';
    echo json_encode($response);
    exit;
}

// Delete item
$query = "DELETE FROM inventory WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Log the deletion
        $admin_id = getCurrentAdminId();
        $changes = "Deleted '{$old_data['name']}' with quantity '{$old_data['quantity']}'";
        logAction($admin_id, 'delete', 'inventory', $id, $changes, 'inventory_management');

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
