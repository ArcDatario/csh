<?php
require_once '../db_connection.php';

header('Content-Type: application/json');

$sql = "SELECT id, work_name, image FROM work ORDER BY id DESC";
$result = $conn->query($sql);

$works = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $works[] = $row;
    }
}

echo json_encode($works);
$conn->close();
?>