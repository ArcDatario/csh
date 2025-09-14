<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php'; // Include the log helper
header('Content-Type: application/json');

// Start session to get admin ID
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];

$id = $conn->real_escape_string($_POST['id'] ?? '');
$user_name = $conn->real_escape_string($_POST['username'] ?? '');
$fullname = $conn->real_escape_string($_POST['fullname'] ?? '');
$role = $conn->real_escape_string($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($id) || empty($user_name) || empty($fullname) || empty($role)) {
    $response['message'] = 'Required fields are missing';
    echo json_encode($response);
    exit;
}

// Get current admin data for logging changes
$current_data_query = "SELECT username, fullname, role FROM admins WHERE id = ?";
$current_stmt = $conn->prepare($current_data_query);
$current_stmt->bind_param("i", $id);
$current_stmt->execute();
$current_result = $current_stmt->get_result();
$old_data = $current_result->fetch_assoc();

// Check if username exists (excluding current admin)
$check_query = "SELECT id FROM admins WHERE username = ? AND id != ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("si", $user_name, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $response['message'] = 'Username already exists';
    echo json_encode($response);
    exit;
}

// Update admin
if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $update_query = "UPDATE admins SET username = ?, fullname = ?, role = ?, password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssi", $user_name, $fullname, $role, $hashedPassword, $id);
    $password_changed = true;
} else {
    $update_query = "UPDATE admins SET username = ?, fullname = ?, role = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $user_name, $fullname, $role, $id);
    $password_changed = false;
}

if ($update_stmt->execute()) {
    // Prepare data for logging
    $new_data = [
        'username' => $user_name,
        'fullname' => $fullname,
        'role' => $role,
        'password_changed' => $password_changed
    ];
    
    // Format the changes for the log
    $changes = formatChanges($old_data, $new_data, ['username', 'fullname', 'role']);
    $admin_id = getCurrentAdminId();
    
    // Log the action
    logAction($admin_id, 'update', 'admin', $id, $changes, 'admin_management');
    
    $response = [
        'success' => true,
        'message' => 'Admin updated successfully'
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>