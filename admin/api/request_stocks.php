<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$item_ids = $_POST['item_ids'];
$quantities = $_POST['quantities'];

try {
    $conn->begin_transaction();
    
    $requested_items = [];
    
    // Insert each requested item
    for ($i = 0; $i < count($item_ids); $i++) {
        $item_id = $item_ids[$i];
        $quantity = $quantities[$i];
        
        // Get item name
        $stmt = $conn->prepare("SELECT name FROM inventory WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        // Insert request
        $stmt = $conn->prepare("INSERT INTO stock_requests 
                               (field_manager_id, item_id, item_name, quantity_requested, status, is_prepairing, is_for_delivery) 
                               VALUES (?, ?, ?, ?, 'pending', 'no', 'no')");
        $stmt->bind_param("iisi", $_SESSION['admin_id'], $item_id, $item['name'], $quantity);
        $stmt->execute();
        
        // Store item info for logging
        $requested_items[] = "{$item['name']} (Qty: {$quantity})";
    }
    
    // Insert notification
    $content = "Field manager has requested new stocks";
    $notify_secretary = "yes";
    $stmt = $conn->prepare("INSERT INTO notification 
                           (user_id, content, notify_secretary, created_at) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $_SESSION['admin_id'], $content, $notify_secretary);
    $stmt->execute();
    
    $conn->commit();
    
    // Log the stock request action - FOLLOWING THE EXACT FORMAT
    $changes = "Requested stocks: " . implode(", ", $requested_items);

    $admin_id = $_SESSION['admin_id'];
    logAction(
        $admin_id,           // actor/admin
        'request',           // action type
        'stock_request',     // entity type
        $conn->insert_id,    // entity ID (using the last insert ID)
        $changes,            // human-readable log
        'stock_management'   // module/category
    );
    
    echo json_encode(['success' => true, 'message' => 'Stock request submitted successfully']);
} catch (Exception $e) {
    $conn->rollback();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Failed to submit request: ' . $e->getMessage()]);
}
?>