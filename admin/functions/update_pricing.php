<?php
// update_pricing.php

session_start();
require '../../db_connection.php'; // Adjust this to your database connection file
require_once '../../log_helper.php'; // Include log helper

// Check if the request is POST and user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_id'])) {
    // Get data from POST request
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $subtotal = isset($_POST['subtotal']) ? $_POST['subtotal'] : null;
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $ticket = isset($_POST['ticket']) ? $_POST['ticket'] : null;

    // Validate inputs
    if (!$id || !$price || !is_numeric($price) || !$user_id || !$ticket) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    try {
        // Prepare SQL statement to update pricing
        if ($subtotal && is_numeric($subtotal)) {
            $stmt = $conn->prepare("UPDATE orders SET pricing = ?, subtotal = ?, is_approved_designer = 'yes', designer_approved_date = NOW() WHERE id = ?");
            $stmt->bind_param("ddi", $price, $subtotal, $id);
        } else {
            $stmt = $conn->prepare("UPDATE orders SET pricing = ?, is_approved_designer = 'yes', designer_approved_date = NOW() WHERE id = ?");
            $stmt->bind_param("di", $price, $id);
        }

        // Execute the query
        if ($stmt->execute()) {
            // Log the action
            // Log the action
            $admin_id = $_SESSION['admin_id']; // designer/admin performing the action
            $logContent = "Approved a quote of ₱" . number_format($price, 2);

            if ($subtotal && is_numeric($subtotal)) {
                $logContent .= " (Subtotal: ₱" . number_format($subtotal, 2) . ")";
            }

            $logContent .= " for Ticket #{$ticket}";

            logAction(
                $admin_id,        // actor/admin
                'update',         // action type
                'orders',         // entity type
                $id,              // order ID
                $logContent,      // human-readable log
                'Orders'          // module/category
            );

            // Insert notification into the notification table
            $content = "Designer just added a quote price of ₱{$price} on ticket #{$ticket}";
            $notify_stmt = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_owner, notify_manager, status) VALUES (?, ?, ?, 'yes', 'yes', 'approved')");
            $notify_stmt->bind_param("iis", $user_id, $id, $content);

            if ($notify_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pricing updated and logged successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to insert notification']);
            }

            $notify_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update pricing']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
}

$conn->close();
?>
