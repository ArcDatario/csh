<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Path to your PHPMailer autoload.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
        exit;
    }

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'capstoneproject0101@gmail.com';
        $mail->Password   = 'sgox knuc kool pftq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Enable debugging if needed (0 = off, 1 = client messages, 2 = client and server messages)
        // $mail->SMTPDebug = 2;

        // Recipients
        $mail->setFrom('capstoneproject0101@gmail.com', 'CSH Contact Form'); // Use a fixed from address
        $mail->addAddress('capstonehosting0101@gmail.com', 'CSH Enterprises');
        $mail->addReplyTo($email, $name); // Set reply-to to the submitter's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'CSH Form Submission from ' . $name;
        
        $emailBody = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Phone:</strong> {$phone}</p>
            <p><strong>Message:</strong></p>
            <p>{$message}</p>
        ";
        
        $mail->Body    = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}