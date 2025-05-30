<?php
session_start();
include '../db_connection.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit();
}

try {
    $admin_id = $_SESSION['admin_id'];

    $stmt = $conn->prepare("SELECT username, image FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $response['success'] = true;
        // Always use the username from the database
        $response['username'] = $admin['username'];
        $response['image'] = $admin['image'];
    } else {
        throw new Exception('Admin not found');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>