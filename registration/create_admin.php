<?php
include 'connect.php';

// Admin credentials
$adminEmail = "admin1216@gmail.com";
$adminPassword = "admin1216"; // plain password
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Check if admin account already exists
$check = $conn->prepare("SELECT * FROM users WHERE email = ?");
$check->bind_param("s", $adminEmail);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "✅ Admin account already exists.";
} else {
    // Insert admin account
    $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
    $firstName = "Admin";
    $lastName = "User";
    $stmt->bind_param("ssss", $firstName, $lastName, $adminEmail, $hashedPassword);

    if ($stmt->execute()) {
        echo "✅ Admin account created successfully!";
    } else {
        echo "❌ Error creating admin: " . $stmt->error;
    }

    $stmt->close();
}
$check->close();
$conn->close();
?>
