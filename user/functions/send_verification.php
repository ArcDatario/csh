<?php
ob_start();
header('Content-Type: application/json');

// Error reporting: log errors, don't display to browser
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start session to handle verification codes
session_start();

$response = ['success' => false, 'message' => ''];

// Get method (email or sms)
$method = $_POST['method'] ?? 'email';
$isResend = isset($_POST['resend']);

// Accept both email and phone, depending on method
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = isset($_POST['phone']) ? preg_replace('/[^0-9]/', '', $_POST['phone']) : null;

// Validate input based on method
if ($method === 'email') {
    if (!$email) {
        $response['message'] = 'Please provide a valid email address';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }
} elseif ($method === 'sms') {
    // Accept 09XXXXXXXXX or 639XXXXXXXXX or +639XXXXXXXXX
    if (!$phone || (strlen($phone) < 10 || strlen($phone) > 12)) {
        $response['message'] = 'Please provide a valid phone number (format: 09XXXXXXXXX or 639XXXXXXXXX)';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    // Normalize phone number format for Semaphore (must be 11 digits starting with 63)
    if (strlen($phone) === 10 && strpos($phone, '09') === 0) {
        $phone = '63' . substr($phone, 1);
    } elseif (strlen($phone) === 11 && strpos($phone, '0') === 0) {
        $phone = '63' . substr($phone, 1);
    } elseif (strlen($phone) === 12 && strpos($phone, '63') === 0) {
        // Already in correct format
    } else {
        $response['message'] = 'Invalid phone number format. Must be 09XXXXXXXXX or 639XXXXXXXXX';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }
} else {
    $response['message'] = 'Invalid delivery method.';
    echo json_encode($response);
    ob_end_flush();
    exit;
}

// Generate a 6-digit verification code
$verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Store the code in session with expiration (15 minutes)
$_SESSION['verification_code'] = $verificationCode;
$_SESSION['verification_method'] = $method;
$_SESSION['verification_email'] = $email;
$_SESSION['verification_phone'] = $phone;
$_SESSION['verification_time'] = time();
$_SESSION['code_expiry'] = time() + 900; // 15 minutes expiry

function sendSMS($to, $message, &$error = null) {
    // Replace with your actual Semaphore API key
    $apiKey = '0e1eb241cf70f66127f683cfb8a90e34'; 
    
    // Prepare the data
    $parameters = [
        'apikey' => $apiKey,
        'number' => $to,
        'message' => $message,
        'sendername' => 'CSH'
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = "cURL Error: " . curl_error($ch);
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Debugging - log the response
    error_log("Semaphore API Response: " . $response);
    error_log("HTTP Code: " . $httpCode);

    if ($httpCode !== 200) {
        $error = "API returned HTTP code: " . $httpCode;
        return false;
    }

    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = "Invalid JSON response: " . $response;
        return false;
    }

    // Check for success response - Semaphore returns an array of message objects
    if (is_array($result) && isset($result[0]['status'])) {
        if (strtolower($result[0]['status']) === 'pending') {
            return true;
        } else {
            $error = isset($result[0]['message']) ? $result[0]['message'] : 'SMS sending failed';
            return false;
        }
    }

    $error = isset($result['message']) ? $result['message'] : 'Unknown error occurred';
    return false;
}

if ($method === 'email') {
    // Send via email using PHPMailer
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
        $response['message'] = $isResend ? 'New verification code sent via Email!' : 'Verification code sent via Email!';
    } catch (Exception $e) {
        $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        error_log('Mailer Error: ' . $mail->ErrorInfo);
    }
} elseif ($method === 'sms') {
    // Send via SMS using Semaphore
    $smsMessage = "Your CSH verification code is: {$verificationCode}. This code will expire in 15 minutes.";
    $smsError = null;
    
    if (sendSMS($phone, $smsMessage, $smsError)) {
        $response['success'] = true;
        $response['message'] = $isResend ? 'New verification code sent via SMS!' : 'Verification code sent via SMS!';
    } else {
        $response['message'] = "Failed to send SMS. Error: " . ($smsError ?? 'Unknown error');
        error_log("SMS sending failed: " . $smsError);
    }
}

echo json_encode($response);
ob_end_flush();