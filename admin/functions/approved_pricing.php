<?php
// update_pricing.php

// Start session and include database connection
session_start();
require '../../db_connection.php'; // Adjust this to your database connection file

// Check if the request is POST and user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Get data from POST request
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $subtotal = isset($_POST['subtotal']) ? $_POST['subtotal'] : null;
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $ticket = isset($_POST['ticket']) ? $_POST['ticket'] : null;

    // Validate required inputs
    if (empty($id) || empty($user_id) || empty($ticket)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data: Missing required fields']);
        exit;
    }

    try {
        // Build the SQL query dynamically based on provided values
        $query = "UPDATE orders SET is_approved_admin = 'yes', admin_approved_date = NOW(), status = 'approved'";
        $params = [];
        $types = "";

        if (!empty($price) && is_numeric($price)) {
            $query .= ", pricing = ?";
            $params[] = $price;
            $types .= "d";
        }

        if (!empty($subtotal) && is_numeric($subtotal)) {
            $query .= ", subtotal = ?";
            $params[] = $subtotal;
            $types .= "d";
        }

        $query .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        // Prepare and bind parameters
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Failed to prepare the statement: " . $conn->error);
        }

        $stmt->bind_param($types, ...$params);

        // Execute the query
        if ($stmt->execute()) {
            // Insert notification into the notification table
            $content = "admin just approved a quote price of ₱" . ($price !== null ? $price : "N/A") . " on ticket #{$ticket}";
            $notify_stmt = $conn->prepare("INSERT INTO notification (user_id, content, notify_field) VALUES (?, ?, 'yes')");
            if ($notify_stmt === false) {
                throw new Exception("Failed to prepare the notification statement: " . $conn->error);
            }

            $notify_stmt->bind_param("is", $user_id, $content);

            if ($notify_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pricing updated successfully']);
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