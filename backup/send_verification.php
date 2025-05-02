<?php
header('Content-Type: application/json');

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start session first to handle verification codes
session_start();

$response = ['success' => false, 'message' => ''];

// Get the email from POST data
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$isResend = isset($_POST['resend']);

if (!$email) {
    $response['message'] = 'Please provide a valid email address';
    echo json_encode($response);
    exit;
}

// Include database configuration
require '../../db_connection.php';

// First check if email already exists in database (only for new registrations)
if (!$isResend) {
    try {
        $check_query = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $response['message'] = 'Email already registered';
                echo json_encode($response);
                exit;
            }
            $stmt->close();
        } else {
            throw new Exception('Database query error');
        }
    } catch (Exception $e) {
        $response['message'] = 'Error checking email: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Generate a 6-digit verification code
$verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Store the code in session with expiration (15 minutes)
$_SESSION['verification_code'] = $verificationCode;
$_SESSION['verification_email'] = $email;
$_SESSION['verification_time'] = time();
$_SESSION['code_expiry'] = time() + 900; // 15 minutes expiry

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'capstoneproject0101@gmail.com';
    $mail->Password = 'sgox knuc kool pftq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Recipients
    $mail->setFrom('capstoneproject0101@gmail.com', 'CSH Enterprises');
    $mail->addAddress($email);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Verification Code';
    
    $emailBody = "
        <h2>Email Verification</h2>
        <p>Thank you for registering with us!</p>
        <p>Your verification code is: <strong>{$verificationCode}</strong></p>
        <p>This code will expire in 15 minutes.</p>
        <p>If you didn't request this code, please ignore this email.</p>
    ";
    
    $mail->Body = $emailBody;
    $mail->AltBody = "Your verification code is: {$verificationCode}. This code will expire in 15 minutes.";
    
    $mail->send();
    
    $response['success'] = true;
    $response['message'] = $isResend ? 'New verification code sent!' : 'Verification code sent!';
} catch (Exception $e) {
    $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    error_log('Mailer Error: ' . $mail->ErrorInfo);
}

echo json_encode($response);
?>