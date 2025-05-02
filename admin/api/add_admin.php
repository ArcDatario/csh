<?php
require_once '../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$user_name = $conn->real_escape_string($_POST['username'] ?? '');
$fullname = $conn->real_escape_string($_POST['fullname'] ?? '');
$role = $conn->real_escape_string($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($user_name) || empty($fullname) || empty($role) || empty($password)) {
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit;
}

// Check if username exists
$check_query = "SELECT id FROM admins WHERE username = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $user_name);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $response['message'] = 'Username already exists';
    echo json_encode($response);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin
$insert_query = "INSERT INTO admins (username, fullname, role, password) VALUES (?, ?, ?, ?)";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("ssss", $user_name, $fullname, $role, $hashedPassword);

if ($insert_stmt->execute()) {
    $response = [
        'success' => true,
        'message' => 'Admin added successfully'
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>