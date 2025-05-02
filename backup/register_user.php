<?php
header('Content-Type: application/json');
require '../../db_connection.php';

$response = ['success' => false, 'message' => ''];

try {
    // Get and validate POST data
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $username = trim($_POST['username'] ?? ''); // Get the username
    $userPassword = $_POST['password'] ?? '';

    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    if (strlen($username) < 3) {
        throw new Exception('Username must be at least 3 characters');
    }

    if (strlen($userPassword) < 8) {
        throw new Exception('Password must be at least 8 characters');
    }

    // Check if email exists
    $check_query = "SELECT id FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Email already registered');
        }
        $stmt->close();
    } else {
        throw new Exception('Database query error');
    }

    // Hash password
    $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);
    if (!$hashedPassword) {
        throw new Exception('Password hashing failed');
    }

    // Determine which fields to include in the insert
    $insert_query = "INSERT INTO users (email, name, password, created_at";
    $values = "VALUES (?, ?, ?, NOW()";
    $params = ["sss"]; // types for email, name, and password
    
    // Check if token fields exist in the table
    $table_fields = [];
    $result = $conn->query("DESCRIBE users");
    while ($row = $result->fetch_assoc()) {
        $table_fields[] = $row['Field'];
    }
    
    // Add token fields if they exist in the table
    $token_fields = ['remember_token', 'remember_expiry', 'reset_token', 'reset_expiry'];
    foreach ($token_fields as $field) {
        if (in_array($field, $table_fields)) {
            $insert_query .= ", $field";
            $values .= ", NULL";
        }
    }
    
    $insert_query .= ") " . $values . ")";
    
    // Insert new user
    if ($stmt = $conn->prepare($insert_query)) {
        $stmt->bind_param(implode("", $params), $email, $username, $hashedPassword);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Registration successful';
            
            // Start session and store user data
            session_start();
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $username; // Store username in session
        } else {
            throw new Exception('Registration failed: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        throw new Exception('Database query error: ' . $conn->error);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Registration Error: ' . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
?>