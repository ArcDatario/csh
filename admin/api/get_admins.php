<?php
require_once '../../db_connection.php';
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

// Get current admin's ID and role from session (using your exact session variable names)
$current_admin_id = $_SESSION['admin_id'] ?? null;
$current_admin_role = $_SESSION['admin_role'] ?? null;

if (!$current_admin_id || !$current_admin_role) {
    $response['message'] = 'Unauthorized access - session not set';
    echo json_encode($response);
    exit;
}

// Base query - always exclude current admin
$query = "SELECT id, username, fullname, role FROM admins WHERE id != ?";

// Add role-specific conditions
if ($current_admin_role === 'Owner') {
    // Owner sees all admins except themselves (already handled in base query)
} elseif ($current_admin_role === 'General Manager') {
    // GM sees all except Owner and themselves
    $query .= " AND role != 'Owner'";
} elseif ($current_admin_role === 'Secretary') {
    // Secretary sees all except Owner, General Manager, and themselves
    $query .= " AND role NOT IN ('Owner', 'General Manager')";
} else {
    // Default for other roles: hide Owner and General Manager
    $query .= " AND role NOT IN ('Owner', 'General Manager')";
}

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!$stmt) {
    $response['message'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("i", $current_admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'fullname' => $row['fullname'],
            'role' => $row['role']
        ];
    }
    $response = [
        'success' => true,
        'data' => $admins
    ];
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

$stmt->close();
echo json_encode($response);
?>