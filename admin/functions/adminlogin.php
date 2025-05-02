<?php
// Start session with strict security settings
session_start([
    'use_strict_mode' => true,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// Clear any existing output buffers
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');
require '../../db_connection.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check if already logged in
    if (isset($_SESSION['admin_id'])) {
        $response['success'] = true;
        $response['message'] = 'Already logged in';
        $response['redirect'] = 'admin/dashboard';
        echo json_encode($response);
        exit();
    }

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get raw POST data
    $postData = file_get_contents('php://input');
    $_POST = json_decode($postData, true) ?? $_POST;

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === '1';

    if (empty($username)) {
        throw new Exception('Please provide your username');
    }

    if (empty($password)) {
        throw new Exception('Please provide your password');
    }

    // Check if admin exists
    $query = "SELECT id, username, password, role, status FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid username or password');
    }

    $admin = $result->fetch_assoc();

    // Check if account is active
    if ($admin['status'] !== 'active') {
        throw new Exception('Your account has been suspended. Please contact support.');
    }

    // Verify password
    if (!password_verify($password, $admin['password'])) {
        throw new Exception('Invalid username or password');
    }

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['last_activity'] = time();

    // Update last login
    $update_login = "UPDATE admins SET last_login = NOW() WHERE id = ?";
    $stmt_update = $conn->prepare($update_login);
    $stmt_update->bind_param("i", $admin['id']);
    $stmt_update->execute();

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 60 * 60 * 24 * 30; // 30 days
        
        // Store token in database
        $update_query = "UPDATE admins SET remember_token = ?, remember_expiry = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $token, date('Y-m-d H:i:s', $expiry), $admin['id']);
        $update_stmt->execute();
        
        setcookie(
            'admin_remember_token', 
            $token, 
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['redirect'] = 'admin/dashboard';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Admin Login Error: ' . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
exit();
?>