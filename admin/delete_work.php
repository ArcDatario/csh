<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workId = $_POST['id'] ?? null;
    
    if (!$workId || !is_numeric($workId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid work ID']);
        exit;
    }
    
    // First get the image path to delete the file later
    $sql = "SELECT image FROM work WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $workId);
    $stmt->execute();
    $result = $stmt->get_result();
    $work = $result->fetch_assoc();
    $stmt->close();
    
    if (!$work) {
        echo json_encode(['success' => false, 'message' => 'Work not found']);
        exit;
    }
    
    // Delete the work from database
    $sql = "DELETE FROM work WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $workId);
    
    if ($stmt->execute()) {
        // Delete the image file
        if (file_exists($work['image'])) {
            unlink($work['image']);
        }
        echo json_encode(['success' => true, 'message' => 'Work deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete work']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>