<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php'; // Include your log helper

header('Content-Type: application/json');

session_start(); // Make sure session is started
$admin_id = $_SESSION['admin_id'] ?? null; // admin performing the action

if (!$admin_id) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

if (!isset($_POST['id']) || !isset($_POST['ticket']) || !isset($_POST['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

$id = $_POST['id'];
$ticket = $_POST['ticket'];
$user_id = $_POST['user_id'];

try {
    // Update the order to mark it as being processed
    $stmt = $conn->prepare("UPDATE orders 
                           SET is_for_processing = 'yes', 
                               processing_date = NOW() 
                           WHERE id = ? AND ticket = ?");
    $stmt->bind_param("is", $id, $ticket);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Insert notification for user with notify_user = 'yes' and is_viewed_user = 'no'
        $user_content = "Your order with ticket #{$ticket} is now being processed.";
        $user_notify_stmt = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_user, is_viewed_user, status) VALUES (?, ?, ?, 'yes', 'no', 'info')");
        
        if ($user_notify_stmt === false) {
            throw new Exception("Failed to prepare the user notification statement: " . $conn->error);
        }

        $user_notify_stmt->bind_param("iis", $user_id, $id, $user_content);

        if ($user_notify_stmt->execute()) {
            // Log the action
            $logContent = "Marked Ticket #{$ticket} as processing";
            logAction(
                $admin_id,      // actor/admin
                'update',       // action type
                'orders',       // entity type
                $id,            // order ID
                $logContent,    // human-readable log
                'Orders'        // module/category
            );

            echo json_encode([
                'success' => true,
                'message' => "Order #$ticket has been marked as processing and user notified"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to insert user notification'
            ]);
        }

        $user_notify_stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made or order not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating order: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>