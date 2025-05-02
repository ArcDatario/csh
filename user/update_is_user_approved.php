<?php
require '../../db_connection.php'; // Include your database connection file

header('Content-Type: application/json');

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['ticketNumber'])) {
    echo json_encode(['success' => false, 'message' => 'Ticket number is required.']);
    exit();
}

$ticketNumber = $data['ticketNumber'];

// Update the is_user_approved column to 'yes'
$sql = "UPDATE orders SET is_user_approved = 'yes', user_approved_date = NOW() WHERE ticket = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ticketNumber);

if ($stmt->execute()) {
    // Get the user_id and id from the updated order
    $selectSql = "SELECT id, user_id FROM orders WHERE ticket = ?";
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->bind_param("s", $ticketNumber);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $orderId = $order['id'];
        $userId = $order['user_id'];

        // Insert into the notification table
        $notificationSql = "INSERT INTO notification (order_id, user_id, content, notify_secretary, notify_owner, notify_manager) VALUES (?, ?, ?, 'yes', 'yes', 'yes')";
        $notificationStmt = $conn->prepare($notificationSql);
        $content = "Quote #$ticketNumber has been agreed to the price";
        $notificationStmt->bind_param("iis", $orderId, $userId, $content);

        if ($notificationStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order updated and notification created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Order updated but failed to create notification.']);
        }

        $notificationStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Order updated but failed to retrieve order details.']);
    }

    $selectStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order.']);
}

$stmt->close();
$conn->close();
?>