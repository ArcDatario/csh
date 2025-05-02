<?php
session_start();

function isLoggedIn() {
    // Check if session exists
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    
    // Check for remember me token
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        require '../../db_connection.php';
        
        $token = $_COOKIE['remember_token'];
        
        // Use a prepared statement to prevent SQL injection
        $query = "SELECT id FROM users WHERE remember_token = ? AND remember_expiry > NOW()";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Database error: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
    }
    
    return false;
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: ../login");
        exit();
    }
}

function redirectToUserHomeIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: user/home");
        exit();
    }
}
?>