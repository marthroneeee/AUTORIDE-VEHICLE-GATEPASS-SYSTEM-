<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adminLogin'])) {
    $username = trim($_POST['admin_username']);
    $password = trim($_POST['admin_password']);

    // Connect to your MySQL database
    $conn = new mysqli('localhost', 'root', '', 'autoride_db1'); // Adjust user/pass/db if needed

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Prepare statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $stmt->close();
            $conn->close();

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password.";
        }
    } else {
        $_SESSION['error'] = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();

    header("Location: admin_login.php");
    exit();
} else {
    header("Location: admin_login.php");
    exit();
}
