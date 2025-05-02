<?php
// Ensure no output before session_start()
if (headers_sent($filename, $linenum)) {
    die("Headers already sent in $filename on line $linenum. Cannot start session.");
}

// Secure session settings
session_start([
    'use_strict_mode' => true,
    'cookie_secure'   => isset($_SERVER['HTTPS']), // Ensure cookies are secure only if HTTPS is used
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// Clear all output buffers
while (ob_get_level()) ob_end_clean();

header('Content-Type: application/json');
require '../db_connection.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check if already logged in (redirect if true)
    if (isset($_SESSION['user_id'])) {
        $response['success'] = true;
        $response['redirect'] = 'user/home';
        echo json_encode($response);
        exit();
    }

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get raw JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $input['password'] ?? '';
    $remember = !empty($input['remember']);

    // Validate input
    if (!$email) throw new Exception('Please provide a valid email address');
    if (empty($password)) throw new Exception('Please provide your password');

    // Database check
    $stmt = $conn->prepare("SELECT id, password, country, full_address FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid email or password');
    }

    $user = $result->fetch_assoc();

    // Password verification
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Invalid email or password');
    }

    // Regenerate session ID before setting data
    session_regenerate_id(true);

    // Set session data
    $_SESSION = [
        'user_id'       => $user['id'],
        'user_email'    => $email,
        'last_activity' => time(),
        'ip_address'    => $_SERVER['REMOTE_ADDR'],
        'user_agent'    => $_SERVER['HTTP_USER_AGENT']
    ];

    // Remember me token
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 2592000; // 30 days
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_expiry = FROM_UNIXTIME(?) WHERE id = ?");
            $stmt->bind_param("sii", $token, $expiry, $user['id']);
            $stmt->execute();
            
            setcookie('remember_token', $token, [
                'expires'  => $expiry,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']), // Ensure cookies are secure only if HTTPS is used
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    // Include address details in the response
    $response['success'] = true;
    $response['redirect'] = 'user/home';
    $response['address'] = [
        'country'      => $user['country'],
        'full_address'    => $user['full_address']
       
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Login Error: ' . $e->getMessage());
} finally {
    if (isset($conn)) $conn->close();
}

echo json_encode($response);
exit();
?>