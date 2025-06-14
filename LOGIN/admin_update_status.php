<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    if (!in_array($status, ['approved', 'rejected'])) {
        die("Invalid status");
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "autoride_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user's email from vehicle_registration
    $stmt = $conn->prepare("SELECT email FROM vehicle_registration WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($user_email);
    $stmt->fetch();
    $stmt->close();

    if (!$user_email) {
        $conn->close();
        die("User not found.");
    }

    if ($status === 'approved') {
        // Update vehicle_registration with approved status, date, and qr_status
        $stmt = $conn->prepare("UPDATE vehicle_registration SET status = 'Approved', approved_at = NOW(), qr_status = 'Ready' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Update users table
        $stmt = $conn->prepare("UPDATE users SET request_status = 'Approved' WHERE email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->close();
    } else {
        // Reject logic: clear approved_at and reset qr_status
        $stmt = $conn->prepare("UPDATE vehicle_registration SET status = 'Rejected', approved_at = NULL, qr_status = 'Pending' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET request_status = 'Rejected' WHERE email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>
