<?php
header('Content-Type: application/json');
require '../db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$response = ['success' => false, 'message' => ''];

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    // Get and validate input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    // Check database connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Database error');
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Email not found in our system');
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();

    // Generate 6-digit code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Store code in database
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param("sss", $code, $expiry, $email);
    if (!$stmt->execute()) {
        throw new Exception('Database error');
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Failed to update reset token');
    }
    
    $stmt->close();

    // Configure PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;  // Disable debug output
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'capstoneproject0101@gmail.com';
        $mail->Password   = 'sgox knuc kool pftq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 30; // Increased timeout
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom('capstoneproject0101@gmail.com', 'CSH Enterprises');
        $mail->addAddress($email, $user['name'] ?? 'User');
        $mail->addReplyTo('capstoneproject0101@gmail.com', 'CSH Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset Code';
        $mail->Body    = sprintf(
            '<h2>Password Reset Request</h2>
            <p>Hello %s,</p>
            <p>Here is your verification code:</p>
            <h3 style="font-size: 24px; color: #007bff;">%s</h3>
            <p>This code will expire in 15 minutes.</p>
            <p>If you didn\'t request this, please ignore this email.</p>',
            htmlspecialchars($user['name'] ?? 'User'),
            $code
        );
        
        $mail->AltBody = sprintf(
            "Password Reset Request\n\nHello,\n\nYour verification code is: %s\n\nThis code will expire in 15 minutes.",
            $code
        );

        if (!$mail->send()) {
            throw new Exception('Failed to send email');
        }
        
        $response['success'] = true;
        $response['message'] = 'Verification code sent to your email';
        
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        throw new Exception('Failed to send verification email. Please try again later.');
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Password Reset Error: ' . $e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

echo json_encode($response);
?>