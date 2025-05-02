<?php
require_once '../db_connection.php';

header('Content-Type: application/json');

// Get search term if provided
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT name, email, completed_orders FROM users";

// Add search condition if search term exists
if (!empty($searchTerm)) {
    $sql .= " WHERE name LIKE ? OR email LIKE ?";
    $searchTerm = "%$searchTerm%";
}

$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);

if (!empty($searchTerm)) {
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);
$conn->close();
?>