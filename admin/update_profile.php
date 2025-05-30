<?php
session_start();
include '../db_connection.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit();
}

$admin_id = $_POST['admin_id'];
$username = trim($_POST['username'] ?? '');
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

try {
    // Validate username
    if (empty($username)) {
        throw new Exception('Username cannot be empty');
    }

    // Check if username is already taken by another admin
    $stmt = $conn->prepare("SELECT id, image FROM admins WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $admin_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Username already taken');
    }

    // Handle password change if provided
    $passwordUpdate = '';
    if (!empty($currentPassword)) {
        if (empty($newPassword)) {
            throw new Exception('New password cannot be empty');
        }
        if ($newPassword !== $confirmPassword) {
            throw new Exception('New passwords do not match');
        }

        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if (!password_verify($currentPassword, $admin['password'])) {
            throw new Exception('Current password is incorrect');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordUpdate = ", password = '$hashedPassword'";
    }

    // Handle image upload
    $imageUpdate = '';
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'profile/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Get current image if exists
        $stmt = $conn->prepare("SELECT image FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $currentImage = $admin['image'] ?? '';

        // Delete old image if it exists
        if (!empty($currentImage) && file_exists($uploadDir . $currentImage)) {
            unlink($uploadDir . $currentImage);
        }

        $fileExt = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
        $fileName = 'admin_' . $admin_id . '.' . $fileExt; // Consistent filename
        $uploadPath = $uploadDir . $fileName;

        // Validate image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profileImage']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Only JPG, PNG, and GIF images are allowed');
        }

        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadPath)) {
            $imageUpdate = ", image = '$fileName'";
            $response['newImage'] = $fileName;
        } else {
            throw new Exception('Failed to upload image');
        }
    }

    // Update admin record
    $sql = "UPDATE admins SET username = ? $passwordUpdate $imageUpdate WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $username, $admin_id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_username'] = $username;
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully';
        $response['newUsername'] = $username;
    } else {
        throw new Exception('Failed to update profile');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>