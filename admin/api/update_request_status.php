<?php
require_once '../../db_connection.php';
require_once '../../auth_check.php';

$response = ['success' => false, 'message' => ''];

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !$status) {
    $response['message'] = 'Invalid request';
    echo json_encode($response);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Build the query and types for binding
    $query = "UPDATE stock_requests SET status = ?";
    $types = "s";
    $params = [$status];

    switch ($status) {
        case 'preparing':
            $query .= ", is_prepairing = 'yes', prepairing_date = NOW()";
            break;
        case 'for_delivery':
            $query .= ", is_for_delivery = 'yes', delivery_date = NOW()";
            break;
        case 'completed':
            $query .= ", completed_date = NOW()";
            break;
    }

    $query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('No rows updated. Check the request ID.');
    }

    // Get item details for notification and inventory update
    $stmt2 = $conn->prepare("SELECT item_id, item_name, quantity_requested, field_manager_id FROM stock_requests WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $row = $result->fetch_assoc();
    $stmt2->close();

    // If status is completed, update inventory
    if ($status === 'completed') {
        $updateInventory = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?");
        $updateInventory->bind_param("ii", $row['quantity_requested'], $row['item_id']);
        $updateInventory->execute();
        
        if ($updateInventory->affected_rows === 0) {
            throw new Exception('Failed to update inventory quantity');
        }
        $updateInventory->close();
    }

    // Format status for notification
    $statusMap = [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'for_delivery' => 'For Delivery',
        'completed' => 'Completed'
    ];
    $statusText = $statusMap[$status] ?? ucfirst($status);

    // Notification content
    $content = "Your request for {$row['quantity_requested']} {$row['item_name']} is now {$statusText}";
    $notify_field = "yes";
    $user_id = $row['field_manager_id'];

    $stmt3 = $conn->prepare("INSERT INTO notification (user_id, content, notify_field, created_at) VALUES (?, ?, ?, NOW())");
    $stmt3->bind_param("iss", $user_id, $content, $notify_field);
    $stmt3->execute();
    $stmt3->close();

    // Commit transaction
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Status updated successfully';
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response['message'] = "Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>