<?php
require_once '../db_connection.php';
header('Content-Type: application/json');

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
} else {
    $update_query = "UPDATE admins SET username = ?, fullname = ?, role = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $user_name, $fullname, $role, $id);
}

if ($update_stmt->execute()) {
    $response = [
        'success' => true,
        'message' => 'Admin updated successfully'
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>