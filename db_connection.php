<?php
// Database configuration
$host = 'localhost'; // Database host
$username = 'u182822994_csh'; // Database username
$password = 'Xk1J>ZFmwcc:'; // Database password (leave empty for XAMPP default)
$database = 'u182822994_csh'; // Replace with your database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
// echo "Connected successfully";
?>