<?php
require '../../auth_check.php';
require '../../db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isLoggedIn()) {
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];

$query = "SELECT name, email, phone_number, address, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $response['success'] = true;
    $response['user'] = $user;
    
    // Add verification status to response
    $response['verified'] = [
        'email' => isset($_SESSION['email_verified']) && $_SESSION['email_verified'],
        'phone' => isset($_SESSION['phone_verified']) && $_SESSION['phone_verified']
    ];
} else {
    $response['message'] = 'User not found';
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>