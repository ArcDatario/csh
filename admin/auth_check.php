
<?php
session_start();

// Database configuration
include '../db_connection.php'; // This sets up $conn

// Function to generate random token
function generateToken() {
    return bin2hex(random_bytes(32));
}

// Login function with token generation
function login($username, $password, $remember = false) {
    global $conn;

    // Use prepared statements for security
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Password is correct
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];

            // Generate token for "remember me"
            if ($remember) {
                $token = generateToken();
                $token_hash = password_hash($token, PASSWORD_DEFAULT);
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

                $update_sql = "UPDATE admins SET token = ?, token_expiry = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssi", $token_hash, $expiry, $user['id']);
                $update_stmt->execute();

                setcookie('admin_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }

            // Update last login time
            $conn->query("UPDATE admins SET last_login = NOW() WHERE id = {$user['id']}");

            return true;
        }
    }

    return false;
}

// Check if user is logged in via session or token
function isLoggedIn() {
    global $conn;

    // Check session
    if (isset($_SESSION['admin_id'])) {
        // Make sure role is set in session
        if (!isset($_SESSION['admin_role'])) {
            // If role isn't set, fetch it from database
            $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['admin_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['admin_role'] = $row['role'];
            }
        }
        return true;
    }

    // Check remember me token
    if (isset($_COOKIE['admin_token'])) {
        $token = $_COOKIE['admin_token'];
        $sql = "SELECT id, username, token, token_expiry, role FROM admins WHERE token IS NOT NULL AND token_expiry > NOW()";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            if (password_verify($token, $row['token'])) {
                // Token is valid, log the user in
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['admin_role'] = $row['role']; // Make sure role is set
                return true;
            }
        }
    }

    return false;
}

// Logout function
function logout() {
    global $conn;

    if (isset($_SESSION['admin_id'])) {
        // Clear token from database
        $conn->query("UPDATE admins SET token = NULL, token_expiry = NULL WHERE id = {$_SESSION['admin_id']}");

        // Clear session and cookie
        session_unset();
        session_destroy();
        setcookie('admin_token', '', time() - 3600, '/');
    }

    header('Location: login');
    exit();
}
?>
