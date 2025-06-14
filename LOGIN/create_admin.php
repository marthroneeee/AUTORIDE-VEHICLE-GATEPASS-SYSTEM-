<?php
// Database connection parameters
$servername = "localhost";
$dbUsername = "root";   // change if your DB username is different
$dbPassword = "";       // change if your DB password is different
$dbName = "autoride_db"; // your database name

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin credentials to insert
$username = "admin";
$password = "admin1216";

// Hash the password securely
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare and bind statement to avoid SQL injection
$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

// Execute and check
if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();
?>
