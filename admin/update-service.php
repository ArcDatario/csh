<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (!isset($_POST['id']) || !isset($_POST['service_name'])) {
        echo json_encode(['success' => false, 'message' => 'Service ID and name are required']);
        exit;
    }

    // Sanitize input data
    $id = intval($_POST['id']);
    $serviceName = $conn->real_escape_string(trim($_POST['service_name']));
    $description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
    
    // Get current service data first
    $currentDataQuery = "SELECT image FROM services WHERE id = $id";
    $currentResult = $conn->query($currentDataQuery);
    
    if ($currentResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit;
    }
    
    $currentData = $currentResult->fetch_assoc();
    $currentImage = $currentData['image'];
    $newImagePath = $currentImage; // Default to current image

    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageFile = $_FILES['image'];
        
        // Validate image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($imageFile['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WebP images are allowed']);
            exit;
        }
        
        if ($imageFile['size'] > $maxFileSize) {
            echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB']);
            exit;
        }
        
        // Prepare upload directory
        $uploadDir = 'uploads/services/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid('service_') . '.' . $fileExtension;
        $destination = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($imageFile['tmp_name'], $destination)) {
            $newImagePath = $destination;
            
            // Delete old image if it exists and is different from new one
            if (!empty($currentImage) && $currentImage !== $destination && file_exists($currentImage)) {
                unlink($currentImage);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
    }
    
    // Update service in database
    $stmt = $conn->prepare("UPDATE services SET service_name = ?, description = ?, image = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("sssi", $serviceName, $description, $newImagePath, $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Service updated successfully',
                'image' => $newImagePath // Return new image path for potential immediate display
            ]);
        } else {
            // If database update failed, delete the new image if it was uploaded
            if ($newImagePath !== $currentImage && file_exists($newImagePath)) {
                unlink($newImagePath);
            }
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        // If preparation failed, delete the new image if it was uploaded
        if ($newImagePath !== $currentImage && file_exists($newImagePath)) {
            unlink($newImagePath);
        }
        echo json_encode(['success' => false, 'message' => 'Database preparation error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>