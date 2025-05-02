<?php
require_once '../db_connection.php';

header('Content-Type: application/json');

$sql = "SELECT id, service_name, description, image FROM services ORDER BY id DESC";
$result = $conn->query($sql);

$services = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

echo json_encode($services);
$conn->close();
?>