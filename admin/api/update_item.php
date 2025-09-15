<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php';
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

try {
    // Get current item data for logging
    $stmt = $conn->prepare("SELECT name, quantity FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $old_data = $stmt->get_result()->fetch_assoc();

    if (!$old_data) {
        $response['message'] = 'Item not found';
        echo json_encode($response);
        exit;
    }

    // Update item
    $update_stmt = $conn->prepare("UPDATE inventory SET name = ?, quantity = ? WHERE id = ?");
    $update_stmt->bind_param("sii", $name, $quantity, $id);

    if ($update_stmt->execute()) {
        // Prepare new data for logging
    $new_data = [
        'name' => $name,
        'quantity' => $quantity
    ];

    // Generate readable changes
    $changes = formatChanges($old_data, $new_data, ['name', 'quantity']);

    // Log the action
    $admin_id = getCurrentAdminId();
    logAction($admin_id, 'update', 'inventory', $id, $changes, 'inventory_management');


        $response = [
            'success' => true,
            'message' => 'Item updated successfully'
        ];
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
