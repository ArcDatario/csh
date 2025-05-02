<?php
header('Content-Type: application/json');
require '../db_connection.php';

$response = ['success' => false, 'message' => ''];

try {
    // Get and validate input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $code = $input['code'] ?? '';

    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    if (empty($code) || strlen($code) !== 6 || !ctype_digit($code)) {
        throw new Exception('Please provide a valid 6-digit code');
    }

    // Check database connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Debug: Log the values being checked
    error_log("Verifying code for email: $email, code: $code");

    // Check if code matches and is not expired
    $stmt = $conn->prepare("SELECT id, reset_token, reset_expiry FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error');
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error');
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Email not found in system');
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();

    // Debug: Log what's in the database
    error_log("Database values - Token: {$user['reset_token']}, Expiry: {$user['reset_expiry']}");
    error_log("Current time: " . date('Y-m-d H:i:s'));

    // Verify the code and expiry
    if ($user['reset_token'] !== $code) {
        throw new Exception('Invalid verification code');
    }

    if (strtotime($user['reset_expiry']) < time()) {
        throw new Exception('Verification code has expired');
    }

    $response['success'] = true;
    $response['message'] = 'Code verified successfully';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Verify Reset Code Error: ' . $e->getMessage());
    
    // Add debug info to response (remove in production)
    if (isset($user)) {
        $response['debug'] = [
            'db_token' => $user['reset_token'] ?? null,
            'db_expiry' => $user['reset_expiry'] ?? null,
            'current_time' => date('Y-m-d H:i:s')
        ];
    }
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

echo json_encode($response);
?>