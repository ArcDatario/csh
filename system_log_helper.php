<?php
// system_log_helper.php
require_once 'db_connection.php';
function log_system_action($conn, $admin_id, $action_content) {
    // First, get the admin role
    $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return false; // Admin not found
    }
    $admin = $result->fetch_assoc();
    $role = $admin['role'];

    // Insert into system_logs
    $created_at = date('Y-m-d H:i:s');
    $insert = $conn->prepare("INSERT INTO system_logs (account_id, content, is_from, created_at) VALUES (?, ?, ?, ?)");
    $insert->bind_param("isss", $admin_id, $action_content, $role, $created_at);
    return $insert->execute(); // returns true on success, false on failure
}
?>
