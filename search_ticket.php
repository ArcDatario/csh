<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket = isset($_POST['ticket']) ? trim($_POST['ticket']) : '';
    
    if (empty($ticket)) {
        echo json_encode(['success' => false, 'message' => 'Ticket number is required']);
        exit;
    }
    
    // Prepare SQL query
    $sql = "SELECT design_file, print_type, quantity, status FROM orders WHERE ticket = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ticket);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        echo json_encode(['success' => true, 'orders' => $orders]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No orders found with that ticket number']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    
}
?>