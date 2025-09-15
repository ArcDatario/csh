<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php'; // Include your logging helper
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$name = $conn->real_escape_string($_POST['name'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);

if (empty($name)) {
    $response['message'] = 'Item name is required';
    echo json_encode($response);
    exit;
}

if ($quantity < 0) {
    $response['message'] = 'Quantity must be a positive number';
    echo json_encode($response);
    exit;
}

// Insert item
$query = "INSERT INTO inventory (name, quantity) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $name, $quantity);

if ($stmt->execute()) {
    // Get current admin ID for logging
    $admin_id = getCurrentAdminId();

    // Prepare log message
    $changes = "Added '$name' with quantity '$quantity'";

    // Log the action
    logAction($admin_id, 'add', 'inventory', $stmt->insert_id, $changes, 'inventory_management');

    $response = [
        'success' => true,
        'message' => 'Item added successfully'
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>
