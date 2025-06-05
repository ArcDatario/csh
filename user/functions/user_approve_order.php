<?php

require '../../db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['ticketNumber']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$ticketNumber = $data['ticketNumber'];
$action = $data['action'];

if ($action === 'agree') {
    $isUserApproved = 'yes';
    $status = 'to-pick-up'; // New status for agreed orders
    $isForPickup = 'no';    // Always set to 'no' on agree
    $content = "Quote #$ticketNumber has been agreed to the price";
    $successMessage = "Successful, the items will be picked up at your location";
    $notificationStatus = 'approved'; // Set notification status to 'approved'
} else if ($action === 'reject') {
    $isUserApproved = 'no';
    $status = 'rejected'; // New status for rejected orders
    $isForPickup = 'no';  // Always set to 'no' on reject as well
    $content = "Quote #$ticketNumber has been rejected by the user";
    $successMessage = "Successfully rejected the quote";
    $notificationStatus = 'reject'; // Set notification status to 'reject'
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit();
}

// Update is_user_approved, status, user_approved_date, and is_for_pickup fields
$sql = "UPDATE orders SET is_user_approved = ?, user_approved_date = NOW(), status = ?, is_for_pickup = ? WHERE ticket = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $isUserApproved, $status, $isForPickup, $ticketNumber);

if ($stmt->execute()) {
    $selectSql = "SELECT id, user_id FROM orders WHERE ticket = ?";
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->bind_param("s", $ticketNumber);
    $selectStmt->execute();
    $result = $selectStmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $orderId = $order['id'];
        $userId = $order['user_id'];

        $notificationSql = "INSERT INTO notification (order_id, user_id, content, notify_secretary, notify_owner, notify_manager, status) VALUES (?, ?, ?, 'yes', 'yes', 'yes', ?)";
        $notificationStmt = $conn->prepare($notificationSql);
        $notificationStmt->bind_param("iiss", $orderId, $userId, $content, $notificationStatus);

        if ($notificationStmt->execute()) {
            echo json_encode(['success' => true, 'message' => $successMessage]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Order updated but failed to create notification.'
            ]);
        }
        $notificationStmt->close();
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Order updated but failed to retrieve order details.'
        ]);
    }
    $selectStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order.']);
}

$stmt->close();
$conn->close();
?>