<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';
require_once '../../auth_check.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if request ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request ID'
    ]);
    exit();
}

$requestId = (int)$_GET['id'];
$response = ['success' => false, 'message' => '', 'data' => null];

// Check if user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit();
}

// Get database connection from db_connection.php
global $conn;

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Prepare and execute the query
$sql = "SELECT * FROM stock_requests WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    $response['message'] = 'Prepare failed: ' . $conn->error;
    echo json_encode($response);
    exit();
}

$stmt->bind_param("i", $requestId);
$executed = $stmt->execute();

if (!$executed) {
    http_response_code(500);
    $response['message'] = 'Execute failed: ' . $stmt->error;
    echo json_encode($response);
    exit();
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    $response['message'] = 'Request not found';
} else {
    $request = $result->fetch_assoc();
    $response['success'] = true;
    $response['message'] = 'Request details retrieved successfully';
    $response['data'] = $request;
}

$stmt->close();
echo json_encode($response);
?>