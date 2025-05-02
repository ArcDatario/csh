<?php
header('Content-Type: application/json');

// Include your existing database connection file
require_once '../db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workName = $conn->real_escape_string($_POST['workName'] ?? '');
    $imageFile = $_FILES['workImage'] ?? null;
    
    // Validate inputs
    if (empty($workName) || !$imageFile || $imageFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
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
    $destination = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit;
    }
    
    // Insert into database
    $sql = "INSERT INTO work (work_name, image) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ss", $workName, $destination);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Work added successfully']);
        } else {
            // Delete the uploaded file if database insertion fails
            if (file_exists($destination)) {
                unlink($destination);
            }
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        // Delete the uploaded file if preparation fails
        if (file_exists($destination)) {
            unlink($destination);
        }
        echo json_encode(['success' => false, 'message' => 'Database preparation error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close connection
$conn->close();
?>