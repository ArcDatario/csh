<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workId = $_POST['id'] ?? null;
    $workName = $conn->real_escape_string($_POST['work_name'] ?? '');
    $imageFile = $_FILES['image'] ?? null;
    
    // Validate inputs
    if (empty($workId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid work ID']);
        exit;
    }
    
    if (empty($workName)) {
        echo json_encode(['success' => false, 'message' => 'Work name is required']);
        exit;
    }
    
    // Get current work data
    $sql = "SELECT image FROM work WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $workId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentWork = $result->fetch_assoc();
    $stmt->close();
    
    if (!$currentWork) {
        echo json_encode(['success' => false, 'message' => 'Work not found']);
        exit;
    }
    
    $imagePath = $currentWork['image'];
    
    // Handle image upload if a new image was provided
    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        // Validate image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($imageFile['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and GIF images are allowed']);
            exit;
        }
        
        // Set upload directory
        $uploadDir = 'uploads/works/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid('work_') . '.' . $fileExtension;
        $newImagePath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($imageFile['tmp_name'], $newImagePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
        
        // Delete old image if it exists
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        $imagePath = $newImagePath;
    }
    
    // Update work in database
    $sql = "UPDATE work SET work_name = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssi", $workName, $imagePath, $workId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Work updated successfully']);
        } else {
            // Delete the new uploaded file if database update fails
            if (isset($newImagePath) && file_exists($newImagePath)) {
                unlink($newImagePath);
            }
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        // Delete the new uploaded file if preparation fails
        if (isset($newImagePath) && file_exists($newImagePath)) {
            unlink($newImagePath);
        }
        echo json_encode(['success' => false, 'message' => 'Database preparation error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>