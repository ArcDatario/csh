<?php
// Secure session start
session_start([
    'use_strict_mode' => true,
    'cookie_secure'   => isset($_SERVER['HTTPS']), // Ensure cookies are secure only if HTTPS is used
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// Unset all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        isset($_SERVER['HTTPS']), // Ensure cookies are secure only if HTTPS is used
        true // HttpOnly
    );
}

// Destroy remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Destroy session
session_destroy();

// Redirect to login
header("Location: ../login");
exit();
?>