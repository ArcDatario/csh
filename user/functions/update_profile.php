<?php
require '../../auth_check.php';
require '../../db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isLoggedIn()) {
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];

// Get current user data
$query = "SELECT email, phone_number, password, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();
$stmt->close();

// Process form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? $currentUser['email'];
$phone = $_POST['phone'] ?? $currentUser['phone_number'];
$address = $_POST['address'] ?? '';
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validate required fields
if (empty($name) || empty($address)) {
    $response['message'] = 'Name and address are required';
    echo json_encode($response);
    exit;
}

// Password change validation
if (!empty($newPassword)) {
    if (empty($currentPassword)) {
        $response['message'] = 'Current password is required to set a new password';
        echo json_encode($response);
        exit;
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $currentUser['password'])) {
        $response['message'] = 'Current password is incorrect';
        echo json_encode($response);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        $response['message'] = 'New passwords do not match';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($newPassword) < 6) {
        $response['message'] = 'New password must be at least 6 characters long';
        echo json_encode($response);
        exit;
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $passwordUpdate = ", password = ?";
}

// Handle image upload
$imageUpdate = '';
$newImageName = $currentUser['image']; // Default to current image

if (!empty($_FILES['profileImage']['name'])) {
    $targetDir = "profile/";
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION));
    $newImageName = uniqid() . '.' . $fileExtension;
    $targetFile = $targetDir . $newImageName;
    
    // Check if image file is an actual image
    $check = getimagesize($_FILES['profileImage']['tmp_name']);
    if ($check === false) {
        $response['message'] = 'File is not an image';
        echo json_encode($response);
        exit;
    }
    
    // Check file size (5MB max)
    if ($_FILES['profileImage']['size'] > 5000000) {
        $response['message'] = 'Image is too large (max 5MB)';
        echo json_encode($response);
        exit;
    }
    
    // Allow certain file formats
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        $response['message'] = 'Only JPG, JPEG, PNG & GIF files are allowed';
        echo json_encode($response);
        exit;
    }
    
    // Try to upload file
    if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFile)) {
        $response['message'] = 'Error uploading image';
        echo json_encode($response);
        exit;
    }
    
    $imageUpdate = ", image = ?";
    
    // Delete old image if it exists and isn't the default
    if (!empty($currentUser['image']) && $currentUser['image'] !== 'icon.png' && file_exists($targetDir . $currentUser['image'])) {
        @unlink($targetDir . $currentUser['image']);
    }
    
    // Update session with new image
    $_SESSION['image'] = $newImageName;
}

// Prepare the update query
$query = "UPDATE users SET name = ?, email = ?, phone_number = ?, address = ?";
$params = [$name, $email, $phone, $address];
$types = "ssss";

if (!empty($passwordUpdate)) {
    $query .= $passwordUpdate;
    $params[] = $hashedPassword;
    $types .= "s";
}

if (!empty($imageUpdate)) {
    $query .= $imageUpdate;
    $params[] = $newImageName;
    $types .= "s";
}

$query .= " WHERE id = ?";
$params[] = $userId;
$types .= "i";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Profile updated successfully';
    
    if (!empty($newImageName) && $newImageName !== $currentUser['image']) {
        $response['newImage'] = $newImageName;
    }
} else {
    $response['message'] = 'Error updating profile: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>