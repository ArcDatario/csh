<?php 
require_once '../db_connection.php';
require_once '../auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Ensure the user is logged in
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Get the logged-in user's ID
    $userId = $_SESSION['user_id'];

    // Get form data
    $printType = $_POST['printType'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $note = $_POST['note'] ?? '';
    $address = $_POST['address'] ?? ''; // Get the address value from the form

    // Generate a unique 6-digit ticket number
    do {
        $ticket = random_int(100000, 999999); // Generate a random 6-digit number
        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE ticket = ?");
        $stmt->bind_param("i", $ticket);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0); // Repeat until a unique ticket is found

    // Handle file upload
    $designFile = '';
    if (isset($_FILES['designFile']) && $_FILES['designFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['designFile']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['designFile']['tmp_name'], $targetPath)) {
            $designFile = $targetPath;
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO orders (user_id, print_type, quantity, note, design_file, ticket, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isissis", $userId, $printType, $quantity, $note, $designFile, $ticket, $address);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quote submitted successfully', 'ticket' => $ticket]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>