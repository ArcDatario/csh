<?php
header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'message' => 'Invalid verification code.'];

// Check if code is set in session
if (!isset($_SESSION['verification_code'])) {
    $response['message'] = 'No verification session found.';
    echo json_encode($response);
    exit;
}

$code = $_POST['code'] ?? '';
$sessionCode = $_SESSION['verification_code'];

// Check if code matches
if ($code === $sessionCode) {
    $response['success'] = true;
    $response['message'] = 'Code verified successfully!';
    
    // Store verification status
    if ($_SESSION['verification_method'] === 'email') {
        $_SESSION['email_verified'] = true;
        $_SESSION['verified_email'] = $_SESSION['verification_email'];
    } else {
        $_SESSION['phone_verified'] = true;
        $_SESSION['verified_phone'] = $_SESSION['verification_phone'];
    }
    
    // Clear verification code
    unset($_SESSION['verification_code']);
} else {
    $response['message'] = 'Invalid verification code.';
}

echo json_encode($response);
?>