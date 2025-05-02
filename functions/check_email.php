<?php
header('Content-Type: application/json');
require '../db_connection.php';

$response = ['exists' => false];

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if ($email) {
    try {
        $check_query = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $response['exists'] = $result->num_rows > 0;
            $stmt->close();
        }
    } catch (Exception $e) {
        // Log error if needed
    } finally {
        $conn->close();
    }
}

echo json_encode($response);
?>