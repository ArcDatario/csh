<?php
// confirm_delivery.php
session_start();
require '../../db_connection.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;
    $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : null;
    $ticket = isset($data['ticket']) ? htmlspecialchars($data['ticket']) : null;
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : null;
    $pricing = isset($data['pricing']) ? floatval($data['pricing']) : null;
    $subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : null;
    $address = isset($data['address']) ? htmlspecialchars($data['address']) : null;

    if (empty($id) || empty($user_id) || empty($email) || empty($ticket) || $quantity === null || $pricing === null || $subtotal === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Update order status to completed
        $query = "UPDATE orders SET status = 'completed', completion_date = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update order status");
        }
        $stmt->close();

        // Notify the customer
        $customer_content = "Order with ticket #{$ticket} has been successfully delivered!";
        $customer_notify = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_manager, notify_owner, notify_secretary) VALUES (?, ?, ?, 'yes', 'yes', 'yes')");
        $customer_notify->bind_param("iis", $user_id, $id, $customer_content);
        if (!$customer_notify->execute()) {
            throw new Exception("Failed to insert customer notification");
        }
        $customer_notify->close();

        // Send email notification to customer
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
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Order #' . $ticket . ' Has Been Delivered';
            
            $emailBody = '
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .details { margin: 20px 0; }
                    .thank-you { margin-top: 20px; font-weight: bold; }
                    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                <p>Dear Valued Customer,</p>
                
                <p>We are pleased to inform you that your order has been successfully delivered. Below are the details:</p>
                
                <div class="details">
                    <p><strong>Ticket Number:</strong> #' . htmlspecialchars($ticket) . '</p>
                    
                    <table>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                        <tr>
                            <td>Custom Print</td>
                            <td>' . htmlspecialchars($quantity) . '</td>
                            <td>₱' . number_format($pricing, 2) . '</td>
                            <td>₱' . number_format($subtotal, 2) . '</td>
                        </tr>
                    </table>
                    
                    <p><strong>Status:</strong> Delivered</p>
                    <p><strong>Delivery Address:</strong> ' . htmlspecialchars($address) . '</p>
                </div>
                
                <p class="thank-you">Thank you for choosing our service! We hope you are satisfied with your order.</p>
                
                <p>If you have any questions or concerns, please don\'t hesitate to contact us.</p>
                
                <p>Best regards,<br>
                CSH Enterprises</p>
            </body>
            </html>';

            $mail->Body = $emailBody;
            $mail->AltBody = "Dear Valued Customer,\n\n"
                . "We are pleased to inform you that your order has been successfully delivered. Below are the details:\n\n"
                . "Ticket Number: #" . $ticket . "\n"
                . "Quantity: " . $quantity . "\n"
                . "Unit Price: ₱" . number_format($pricing, 2) . "\n"
                . "Subtotal: ₱" . number_format($subtotal, 2) . "\n"
                . "Status: Delivered\n"
                . "Delivery Address: " . $address . "\n\n"
                . "Thank you for choosing our service! We hope you are satisfied with your order.\n\n"
                . "If you have any questions or concerns, please don't hesitate to contact us.\n\n"
                . "Best regards,\n"
                . "CSH Enterprises";

            $mail->send();
            
            $conn->commit();
            
            echo json_encode(['success' => true, 'message' => 'Order marked as completed and notifications sent successfully']);
        } catch (Exception $emailException) {
            $conn->commit();
            error_log("Email sending failed: " . $emailException->getMessage());
            echo json_encode(['success' => true, 'message' => 'Order marked as completed but email notification failed']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
}

$conn->close();
?>