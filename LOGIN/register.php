<?php
session_start();
include 'connect.php';

if (isset($_POST['signUp'])) {
    // Trim and get inputs
    $firstName = trim($_POST['fName']);
    $lastName = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $idNumber = trim($_POST['id_number']);
    $mobileNumber = trim($_POST['mobile_number']);
    $courseYearSection = trim($_POST['course_year_section']);
    $password = $_POST['password'];
    $retypePassword = $_POST['retype_password'];

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($idNumber) || empty($mobileNumber) || empty($courseYearSection) || empty($password) || empty($retypePassword)) {
        $_SESSION['error'] = "All fields are required.";
        $_SESSION['form'] = "signUp";
        header("Location: index.php");
        exit();
    }

    if ($password !== $retypePassword) {
        $_SESSION['error'] = "Passwords do not match!";
        $_SESSION['form'] = "signUp";
        header("Location: index.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        $_SESSION['form'] = "signUp";
        header("Location: index.php");
        exit();
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email Address Already Exists!";
        $_SESSION['form'] = "signUp";
        header("Location: index.php");
        exit();
    } else {
        // Hash password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with course_year_section
        $insertQuery = $conn->prepare("INSERT INTO users (firstName, lastName, id_number, mobile_number, course_year_section, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertQuery->bind_param("sssssss", $firstName, $lastName, $idNumber, $mobileNumber, $courseYearSection, $email, $hashedPassword);

        if ($insertQuery->execute()) {
            $_SESSION['success'] = "Account Created Successfully! You can now log in.";
            $_SESSION['form'] = "signIn";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
            $_SESSION['form'] = "signUp";
            header("Location: index.php");
            exit();
        }
    }
}

if (isset($_POST['signIn'])) {
    $email = trim($_POST['email']);
    $passwordInput = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        $_SESSION['form'] = "signIn";
        header("Location: index.php");
        exit();
    }

    // Fetch user by email
    $sql = $conn->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($passwordInput, $row['password'])) {
            $_SESSION['email'] = $row['email'];

            // Redirect based on role or specific email
            if (strtolower($email) === strtolower("AutoRide@gmail.com")) {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: homepage.php");
                exit();
            }

        } else {
            $_SESSION['error'] = "Incorrect Email or Password.";
            $_SESSION['form'] = "signIn";
            header("Location: index.php");
            exit();
        }

    } else {
        $_SESSION['error'] = "Incorrect Email or Password.";
        $_SESSION['form'] = "signIn";
        header("Location: index.php");
        exit();
    }
}
?>
