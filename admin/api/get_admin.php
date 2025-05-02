<?php
require_once '../../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    $query = "SELECT id, username, fullname, role FROM admins WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $response = [
            'success' => true,
            'id' => $admin['id'],
            'username' => $admin['username'],
            'fullname' => $admin['fullname'],
            'role' => $admin['role']
        ];
    } else {
        $response['message'] = 'Admin not found';
    }
    $stmt->close();
} else {
    $response['message'] = 'Admin ID not provided';
}

echo json_encode($response);
?>