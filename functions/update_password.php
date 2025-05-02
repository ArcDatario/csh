<?php
header('Content-Type: application/json');
require '../db_connection.php';

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $data['password'] ?? '';

    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if (!$hashedPassword) {
        throw new Exception('Password hashing failed');
    }

    // Update password and clear reset token
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update password');
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No user found with that email');
    }
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'Password updated successfully';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Update Password Error: ' . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
?>