<?php
header('Content-Type: application/json');

session_start();

$response = ['success' => false, 'message' => ''];

// Get the submitted data
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);

// Validate inputs
if (!$email || !$code || strlen($code) !== 6 || !ctype_digit($code)) {
    $response['message'] = 'Invalid verification data';
    echo json_encode($response);
    exit;
}

// Check if the session data exists
if (!isset($_SESSION['verification_code']) || 
    !isset($_SESSION['verification_email']) || 
    !isset($_SESSION['verification_time'])) {
    $response['message'] = 'No verification session found';
    echo json_encode($response);
    exit;
}

// Check if email matches
if ($_SESSION['verification_email'] !== $email) {
    $response['message'] = 'Email mismatch';
    echo json_encode($response);
    exit;
}

// Check if code is correct
if ($_SESSION['verification_code'] !== $code) {
    $response['message'] = 'Invalid verification code';
    echo json_encode($response);
    exit;
}

// Check if code is expired (15 minutes)
$expirationTime = 15 * 60; // 15 minutes in seconds
if (time() - $_SESSION['verification_time'] > $expirationTime) {
    $response['message'] = 'Verification code has expired';
    echo json_encode($response);
    exit;
}

// If all checks pass
$response['success'] = true;
$response['message'] = 'Email verified successfully!';

// Clear the verification session
unset($_SESSION['verification_code']);
unset($_SESSION['verification_email']);
unset($_SESSION['verification_time']);

echo json_encode($response);
?>