<?php
require_once '../../db_connection.php';
require_once '../../log_helper.php'; // Include your logging helper
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$id = $conn->real_escape_string($_POST['id'] ?? '');

if (empty($id)) {
    $response['message'] = 'Admin ID not provided';
    echo json_encode($response);
    exit;
}

// Get admin info before deletion for logging
$stmt = $conn->prepare("SELECT username, role FROM admins WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$old_data = $stmt->get_result()->fetch_assoc();

if (!$old_data) {
    $response['message'] = 'Admin not found';
    echo json_encode($response);
    exit;
}

// Delete admin
$query = "DELETE FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Log the deletion
        $admin_id = getCurrentAdminId();
        $changes = "Deleted '{$old_data['username']}' with role '{$old_data['role']}'";
        logAction($admin_id, 'delete', 'admin', $id, $changes, 'admin_management');

        $response = [
            'success' => true,
            'message' => 'Admin deleted successfully'
        ];
    } else {
        $response['message'] = 'Admin not found';
    }
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

echo json_encode($response);
?>
