<?php
// confirm_pickup.php
session_start();
require '../../db_connection.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_id'])) {
    // Get data from POST request
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;
    $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : null;
    $ticket = isset($data['ticket']) ? htmlspecialchars($data['ticket']) : null;
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : null;
    $pricing = isset($data['pricing']) ? floatval($data['pricing']) : null;
    $subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : null;

    // Validate required inputs
    if (empty($id) || empty($user_id) || empty($email) || empty($ticket) || $quantity === null || $pricing === null || $subtotal === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data: Missing required fields.']);
        exit;
    }

    // Get the address from the orders table using the order ID
    $address = '';
    $address_stmt = $conn->prepare("SELECT address FROM orders WHERE id = ?");
    $address_stmt->bind_param("i", $id);
    $address_stmt->execute();
    $address_stmt->bind_result($address);
    $address_stmt->fetch();
    $address_stmt->close();

    if (empty($address)) {
        $address = 'N/A';
    }

    try {
        // Update order status to mark as ready for pickup and increment pickup_attempt
        $query = "UPDATE orders SET is_for_pickup = 'yes', pickup_date = NOW(), pickup_attempt = IFNULL(pickup_attempt,0) + 1 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Insert notification with order_id
            $content = "Your order with ticket #{$ticket} is ready for pickup. Our logistics team will pick up the items at your address: {$address}";
            $notify_stmt = $conn->prepare("INSERT INTO notification (user_id, order_id, content, notify_user, status) VALUES (?, ?, ?, 'yes', 'info')");
            $notify_stmt->bind_param("iis", $user_id, $id, $content);
            
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
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Order #' . $ticket . ' is Ready for Pickup';
                    
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
                        
                        <p>We are pleased to inform you that your order is now ready for pickup. Below are the details:</p>
                        
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
                            
                            <p><strong>Status:</strong> Ready for Pickup</p>
                            <p><strong>Pickup Address:</strong> ' . htmlspecialchars($address) . '</p>
                        </div>
                        
                        <p class="thank-you">Thank you for choosing our service!</p>
                        
                        <p>
                           
                            Please ensure someone is available at the location for the pickup.
                        </p>
                        
                        <p>Best regards,<br>
                        CSH Enterprises</p>
                    </body>
                    </html>';

                    $mail->Body = $emailBody;
                    $mail->AltBody = "Dear Valued Customer,\n\n"
                        . "We are pleased to inform you that your order is now ready for pickup. Below are the details:\n\n"
                        . "Ticket Number: #" . $ticket . "\n"
                        . "Quantity: " . $quantity . "\n"
                        . "Unit Price: ₱" . number_format($pricing, 2) . "\n"
                        . "Subtotal: ₱" . number_format($subtotal, 2) . "\n"
                        . "Status: Ready for Pickup\n"
                        . "Pickup Address: " . $address . "\n\n"
                        . "Our logistics team will pick up the items at your location above. Please ensure someone is available at the location for the pickup.\n\n"
                        . "Thank you for choosing our service!\n\n"
                        . "Best regards,\n"
                        . "CSH Enterprises";

                    $mail->send();
                    
                    echo json_encode(['success' => true, 'message' => 'Pickup confirmed and notifications sent successfully']);
                } catch (Exception $emailException) {
                    error_log("Email sending failed: " . $emailException->getMessage());
                    echo json_encode(['success' => true, 'message' => 'Pickup confirmed but email notification failed']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to insert notification']);
            }
            
            $notify_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
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