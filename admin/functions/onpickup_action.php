<?php
session_start();
require '../../db_connection.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $action = $data['action'] ?? null;
    $id = $data['id'] ?? null;
    $userId = $data['user_id'] ?? null;
    $email = $data['email'] ?? null;
    $ticket = $data['ticket'] ?? null;
    $attempt = $data['attempt'] ?? null;

    if (!$action || !$id || !$userId || !$email || !$ticket) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    try {
        $conn->begin_transaction();
        
        // First, get order details including print_type and quantity
        $orderQuery = $conn->prepare("SELECT print_type, quantity FROM orders WHERE id = ?");
        $orderQuery->bind_param("i", $id);
        $orderQuery->execute();
        $orderResult = $orderQuery->get_result();
        
        if ($orderResult->num_rows === 0) {
            throw new Exception("Order not found");
        }
        
        $orderData = $orderResult->fetch_assoc();
        $printType = $orderData['print_type'];
        $quantity = $orderData['quantity'];
        $orderQuery->close();
        
        // Common notification and email data
        $notificationContent = '';
        $emailSubject = '';
        $emailBody = '';
        $statusUpdate = '';
        
        switch ($action) {
            case 'reattempt':
                $statusUpdate = "UPDATE orders SET pickup_attempt = pickup_attempt + 1, pickup_date = NOW() WHERE id = ?";
                $notificationContent = "Your order #$ticket pickup is being reattempted. Our logistics team will try again to pick up your items.";
                $emailSubject = "Order #$ticket Pickup Reattempt";
                $emailBody = "Dear Customer,<br><br>We are reattempting to pick up your order #$ticket today. Please ensure someone is available at the pickup location.<br><br>Thank you for your patience.";
                break;
                
            case 'failed':
                $statusUpdate = "UPDATE orders SET pickup_date = NOW() WHERE id = ?";
                $notificationContent = "The pickup attempt for your order #$ticket has failed. We will try again soon.";
                $emailSubject = "Order #$ticket Pickup Attempt Failed";
                $emailBody = "Dear Customer,<br><br>The pickup attempt for your order #$ticket has failed. We will try again soon. Please ensure someone is available at the pickup location during business hours.<br><br>Thank you for your understanding.";
                break;
                
            case 'reject':
                $statusUpdate = "UPDATE orders SET status = 'rejected', pickup_date = NOW() WHERE id = ?";
                $notificationContent = "Your order #$ticket has been rejected due to multiple failed pickup attempts.";
                $emailSubject = "Order #$ticket Rejected";
                $emailBody = "Dear Customer,<br><br>We regret to inform you that your order #$ticket has been rejected due to multiple failed pickup attempts.<br><br>Thank you for your understanding.";
                break;
                
            case 'pickedup':
                $statusUpdate = "UPDATE orders SET status = 'processing', pickup_date = NOW(), is_for_processing = 'no' WHERE id = ?";
                $notificationContent = "Order #$ticket has been picked up and will be processed. Please prepare the materials needed for this order.";
                
                // FIELD NOTIFICATION: Include print type and quantity for field team
                $fieldNotificationContent = "Order #$ticket ($printType, Qty: $quantity) has been picked up. Please prepare materials needed for production.";
                
                $emailSubject = "Order #$ticket Picked Up and Processing";
                $emailBody = "Dear Customer,<br><br>Order #$ticket has been picked up and will now be processed. Please ensure all required materials for this order are ready.<br><br>Thank you for your cooperation.";
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit;
        }
        
        // Update order status
        $stmt = $conn->prepare($statusUpdate);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Insert notification for user
        if (!empty($notificationContent)) {
            $notifyStmt = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_user) VALUES (?, ?, ?, 'yes')");
            $notifyStmt->bind_param("iis", $userId, $id, $notificationContent);
            $notifyStmt->execute();
            $notifyStmt->close();
        }
        
        // For picked up orders, insert additional notification for field team
        if ($action === 'pickedup' && !empty($fieldNotificationContent)) {
            // Insert field notification - set notify_field to 'yes' and user_id to 0 (for all field staff)
            $fieldNotifyStmt = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_field, status) VALUES (0, ?, ?, 'yes', 'field_notification')");
            $fieldNotifyStmt->bind_param("is", $id, $fieldNotificationContent);
            $fieldNotifyStmt->execute();
            $fieldNotifyStmt->close();
        }
        
        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'capstoneproject0101@gmail.com';
            $mail->Password   = 'sgox knuc kool pftq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('capstoneproject0101@gmail.com', 'CSH Enterprises');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                        .content { padding: 20px; }
                        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 0.9em; color: #6c757d; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>CSH Enterprises</h2>
                        </div>
                        <div class='content'>
                            $emailBody
                        </div>
                        <div class='footer'>
                            <p>Thank you for choosing our services</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mail->AltBody = strip_tags($emailBody);
            $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Action completed successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
}

$conn->close();
?>