<?php
session_start();
include("connect.php"); // Your DB connection

function uploadFile($fileInput, $id_number) {
    $baseDir = "uploads/";
    $targetDir = $baseDir . $id_number . "/";

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES[$fileInput]["name"]);
        $filename = preg_replace("/[^a-zA-Z0-9._-]/", "", $filename);
        $targetFile = $targetDir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES[$fileInput]["tmp_name"], $targetFile)) {
            return $targetFile;
        }
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get user_id from hidden input (passed from form)
    $user_id = intval($_POST['user_id'] ?? 0);
    $id_number = trim($_POST['id_number'] ?? '');
    $vehicle_type = trim($_POST['vehicle_type'] ?? '');

    if ($user_id === 0 || empty($vehicle_type)) {
        die("Missing required data.");
    }

    // Check if this user already has a pending or approved vehicle registration
    $checkSql = "SELECT * FROM vehicle_registration WHERE user_id = ? AND status IN ('Pending', 'Approved')";
    $checkStmt = $conn->prepare($checkSql);
    if ($checkStmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // User already registered, redirect with error
        $checkStmt->close();
        $conn->close();
        header("Location: registerVehicle.php?error=already_registered");
        exit();
    }
    $checkStmt->close();

    // Upload files
    $license_file = uploadFile('license_file', $id_number);
    $or_file = uploadFile('or_file', $id_number);
    $cr_file = uploadFile('cr_file', $id_number);
    $parent_id_file = uploadFile('parent_id_file', $id_number);
    $proof_of_purchase_file = uploadFile('proof_of_purchase_file', $id_number);

    // Insert new vehicle registration
    $sql = "INSERT INTO vehicle_registration (
        user_id, vehicle_type, license_file, or_file, cr_file, parent_id_file, proof_of_purchase_file, registration_date, status, qr_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', 'Inactive')";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "issssss",
        $user_id,
        $vehicle_type,
        $license_file,
        $or_file,
        $cr_file,
        $parent_id_file,
        $proof_of_purchase_file
    );

    if ($stmt->execute()) {
        // Update users request_status to pending
        $updateStatus = "UPDATE users SET request_status = 'pending' WHERE Id = ?";
        $stmtUpdate = $conn->prepare($updateStatus);
        if ($stmtUpdate) {
            $stmtUpdate->bind_param("i", $user_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }

        header("Location: registerVehicle.php?success=1");
        exit();
    } else {
        echo "Error inserting record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
