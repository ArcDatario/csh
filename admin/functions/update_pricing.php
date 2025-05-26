<?php
// update_pricing.php

// Start session and include database connection
session_start();
require '../../db_connection.php'; // Adjust this to your database connection file

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
            // Insert notification into the notification table with order_id
            $content = "Designer just added a quote price of ₱{$price} on ticket #{$ticket}";
            $notify_stmt = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_owner, notify_manager) VALUES (?, ?, ?, 'yes', 'yes')");
            $notify_stmt->bind_param("iis", $user_id, $id, $content);

            if ($notify_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pricing updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update pricing']);
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