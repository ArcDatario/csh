
<?php
header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'message' => 'Invalid verification code.'];

// Check if code and expiry are set in session
if (!isset($_SESSION['verification_code']) || !isset($_SESSION['code_expiry'])) {
    $response['message'] = 'No verification session found.';
    echo json_encode($response);
    exit;
}

$code = $_POST['code'] ?? '';
$sessionCode = $_SESSION['verification_code'];
$expiry = $_SESSION['code_expiry'];

// Check if code matches and isn't expired
if ($code === $sessionCode && time() < $expiry) {
    $response['success'] = true;
    $response['message'] = 'Code verified successfully!';
    $_SESSION['verified'] = true;
} elseif (time() >= $expiry) {
    $response['message'] = 'Verification code has expired.';
}

echo json_encode($response);
