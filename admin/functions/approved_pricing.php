<?php

// update_pricing.php
// Start session and include database connection
session_start();
require '../../db_connection.php'; // Adjust this to your database connection file
require '../../vendor/autoload.php'; // Include PHPMailer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the request is POST and user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_id'])) {
    // Get data from POST request
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $price = isset($_POST['price']) && $_POST['price'] !== '' ? $_POST['price'] : null;
    $subtotal = isset($_POST['subtotal']) ? $_POST['subtotal'] : null;
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $ticket = isset($_POST['ticket']) ? $_POST['ticket'] : null;
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : null;

    // Get pricing-value if sent
    $pricing_value = isset($_POST['pricing']) && $_POST['pricing'] !== '' ? $_POST['pricing'] : null;

    // Determine which price to use
    if (!empty($pricing_value) && is_numeric($pricing_value)) {
        $unit_price = $pricing_value;
    } elseif (!empty($price) && is_numeric($price)) {
        $unit_price = $price;
    } else {
        $unit_price = null;
    }

    // Calculate subtotal
    if (!empty($unit_price) && !empty($quantity) && is_numeric($unit_price) && is_numeric($quantity)) {
        $subtotal = $unit_price * $quantity;
    } else {
        $subtotal = null;
    }

    // Validate required inputs
    if (empty($id) || empty($user_id) || empty($ticket) || empty($unit_price) || empty($subtotal)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data: Missing required fields or price.']);
        exit;
    }

    try {
        // First, get user's email from database
        $user_query = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        
        if ($user_result->num_rows === 0) {
            throw new Exception("User not found");
        }
        
        $user_data = $user_result->fetch_assoc();
        $user_email = $user_data['email'];
        $user_query->close();

        // Build the SQL query dynamically based on provided values
        $query = "UPDATE orders SET is_approved_admin = 'yes', admin_approved_date = NOW(), status = 'approved'";
        $params = [];
        $types = "";

        if (!empty($unit_price) && is_numeric($unit_price)) {
            $query .= ", pricing = ?";
            $params[] = $unit_price;
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
            $content = "admin just approved a quote price of ₱" . number_format($unit_price, 2) . " on ticket #{$ticket}";
            $notify_stmt = $conn->prepare("INSERT INTO notification (user_id, content, notify_field) VALUES (?, ?, 'yes')");
            if ($notify_stmt === false) {
                throw new Exception("Failed to prepare the notification statement: " . $conn->error);
            }

            $notify_stmt->bind_param("is", $user_id, $content);

            if ($notify_stmt->execute()) {
                // Send email notification
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'capstoneproject0101@gmail.com';
                    $mail->Password   = 'sgox knuc kool pftq';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Recipients
                    $mail->setFrom('capstoneproject0101@gmail.com', 'CSH Enterprises');
                    $mail->addAddress($user_email); // User's email

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Quote for Ticket #' . $ticket . ' Has Been Approved';
                    
                    // Build the email body with proper formatting
                    $emailBody = '
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .details { margin: 20px 0; }
                            .details li { margin-bottom: 10px; }
                            .thank-you { margin-top: 20px; font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <p>Dear Valued Customer,</p>
                        
                        <p>We are pleased to inform you that your quote has been approved. Below are the details:</p>
                        
                        <div class="details">
                            <ul>
                                <li><strong>Ticket Number:</strong> #' . htmlspecialchars($ticket) . '</li>
                                <li><strong>Unit Price:</strong> ₱' . htmlspecialchars(number_format($unit_price, 2)) . '</li>
                                <li><strong>Quantity:</strong> ' . htmlspecialchars($quantity) . '</li>
                                <li><strong>Total Amount:</strong> ₱' . htmlspecialchars(number_format($subtotal, 2)) . '</li>
                            </ul>
                        </div>
                        
                        <p class="thank-you">Thank you for choosing our service!</p>
                        
                        <p>Should you have any questions, please don\'t hesitate to contact us.</p>
                        
                        <p>Best regards,<br>
                        CSH Enterprises</p>
                    </body>
                    </html>';

                    $mail->Body = $emailBody;
                    $mail->AltBody = "Dear Valued Customer,\n\n"
                        . "We are pleased to inform you that your quote has been approved. Below are the details:\n\n"
                        . "Ticket Number: #" . $ticket . "\n"
                        . "Unit Price: ₱" . number_format($unit_price, 2) . "\n"
                        . "Quantity: " . $quantity . "\n"
                        . "Total Amount: ₱" . number_format($subtotal, 2) . "\n\n"
                        . "Thank you for choosing our service!\n\n"
                        . "Best regards,\n"
                        . "CSH Enterprises";

                    $mail->send();
                    
                    // All operations successful
                    echo json_encode(['success' => true, 'message' => 'Pricing updated and notifications sent successfully']);
                } catch (Exception $emailException) {
                    // Email failed but database operations succeeded
                    error_log("Email sending failed: " . $emailException->getMessage());
                    echo json_encode(['success' => true, 'message' => 'Pricing updated but email notification failed']);
                }
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