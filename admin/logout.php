<?php
require_once 'auth_check.php';

// Call the admin-specific logout function
admin_logout();

// Admin-specific logout function that preserves user session
function admin_logout() {
    global $conn;

    if (isset($_SESSION['admin_id'])) {
        // Clear admin token from database
        $conn->query("UPDATE admins SET token = NULL, token_expiry = NULL WHERE id = {$_SESSION['admin_id']}");

        // Clear admin session variables only
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_role']);
        
        // Clear admin cookie only
        setcookie('admin_token', '', time() - 3600, '/');
    }

    header('Location: login');
    exit();
}
?>