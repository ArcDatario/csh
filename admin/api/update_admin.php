<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

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

try {
    // Get current admin data
    $stmt = $conn->prepare("SELECT username, fullname, role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $old_data = $stmt->get_result()->fetch_assoc();

    if (!$old_data) {
        $response['message'] = 'Admin not found';
        echo json_encode($response);
        exit;
    }

    // Check if username exists (excluding current admin)
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $user_name, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $response['message'] = 'Username already exists';
        echo json_encode($response);
        exit;
    }

    // Check if anything changed
    $password_changed = !empty($password);
    $isChanged = $password_changed || $user_name !== $old_data['username'] || $fullname !== $old_data['fullname'] || $role !== $old_data['role'];

    if (!$isChanged) {
        $response['success'] = false;
        $response['message'] = 'No changes detected';
        $response['newUsername'] = $user_name;
        echo json_encode($response);
        exit;
    }

    // Update admin
    if ($password_changed) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET username = ?, fullname = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $user_name, $fullname, $role, $hashedPassword, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET username = ?, fullname = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $user_name, $fullname, $role, $id);
    }

    if ($stmt->execute()) {
        // New data for logging
        $new_data = [
            'username' => $user_name,
            'fullname' => $fullname,
            'role' => $role,
            'password_changed' => $password_changed
        ];

        // Generate concise human-readable changes
        $changes = formatChanges($old_data, $new_data, ['username', 'fullname', 'role'], $user_name); 

        // Log action with admin ID (no actor info)
        $admin_id = getCurrentAdminId();
        logAction($admin_id, 'update', 'admin', 0, $changes, 'admin_management');

        $response = [
            'success' => true,
            'message' => 'Admin updated successfully',
            'newUsername' => $user_name
        ];
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
