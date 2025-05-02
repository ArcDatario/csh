<?php
header('Content-Type: application/json');
require '../db_connection.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false, 'message' => ''];

try {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    // Check if email exists
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('No account found with that email address');
    }

    $user = $result->fetch_assoc();
    
    // Generate reset token (expires in 1 hour)
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', time() + 3600);
    
    // Store token in database (using correct column names)
    $update_query = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $token, $expiry, $user['id']);
    $update_stmt->execute();
    
    // Create reset link (update with your actual domain)
    $resetLink = "https://yourdomain.com/reset_password.php?token=$token";
    
    // Send email with PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'datarioarc@gmail.com';
        $mail->Password = 'ezlh qkqz lwnm exln';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('datarioarc@gmail.com', 'Your App Name');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        
        $emailBody = "
            <h2>Password Reset</h2>
            <p>We received a request to reset your password.</p>
            <p>Click the link below to reset your password (expires in 1 hour):</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        $mail->Body = $emailBody;
        $mail->AltBody = "Password reset link: $resetLink (expires in 1 hour)";
        
        $mail->send();
        
        $response['success'] = true;
        $response['message'] = 'Password reset link sent to your email';
    } catch (Exception $e) {
        throw new Exception("Failed to send reset email. Please try again later.");
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Forgot Password Error: ' . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo json_encode($response);
?>