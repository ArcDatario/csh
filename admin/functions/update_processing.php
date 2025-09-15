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

if (!isset($_POST['id']) || !isset($_POST['ticket'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

$id = $_POST['id'];
$ticket = $_POST['ticket'];

try {
    // Update the order to mark it as being processed
    $stmt = $conn->prepare("UPDATE orders 
                           SET is_for_processing = 'yes', 
                               processing_date = NOW() 
                           WHERE id = ? AND ticket = ?");
    $stmt->bind_param("is", $id, $ticket);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
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
            'message' => "Order #$ticket has been marked as processing"
        ]);
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
