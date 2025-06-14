<?php
session_start();
include("connect.php"); // DB connection

// Fetch user info from session email to pre-fill form fields
$user_email = $_SESSION['email'] ?? null;
$name = $mobile_number = $course_year_section = $id_number = "";

if ($user_email) {
    $userStmt = $conn->prepare("SELECT firstName, lastName, id_number, mobile_number, course_year_section FROM users WHERE email = ?");
    $userStmt->bind_param("s", $user_email);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    if ($userRow = $userResult->fetch_assoc()) {
        $name = htmlspecialchars($userRow['firstName'] . " " . $userRow['lastName']);
        $id_number = htmlspecialchars($userRow['id_number']);
        $mobile_number = htmlspecialchars($userRow['mobile_number']);
        $course_year_section = htmlspecialchars($userRow['course_year_section']);
    }
    $userStmt->close();
}

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
    // Collect POST data from form (readonly user data can be trusted from session)
    $email_post = trim($_POST['email']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $ownership_type = trim($_POST['ownership_type']);

    // Get user_id from users table based on email
    $stmtUserId = $conn->prepare("SELECT Id, id_number FROM users WHERE email = ?");
    $stmtUserId->bind_param("s", $email_post);
    $stmtUserId->execute();
    $resultUserId = $stmtUserId->get_result();
    $user_id = null;
    $id_number_post = null;
    if ($row = $resultUserId->fetch_assoc()) {
        $user_id = $row['Id'];
        $id_number_post = $row['id_number'];
    }
    $stmtUserId->close();

    if (!$user_id) {
        die("User not found.");
    }

    // Check for duplicate registration by user_id only
    $checkSql = "SELECT * FROM vehicle_registration WHERE user_id = ? AND status IN ('Pending', 'Approved')";
    $checkStmt = $conn->prepare($checkSql);
    if ($checkStmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        header("Location: registerVehicle.php?error=already_registered");
        exit();
    }
    $checkStmt->close();

    // Upload all files (using id_number as folder name)
    $license_file = uploadFile('license_file', $id_number_post);
    $or_file = uploadFile('or_file', $id_number_post);
    $cr_file = uploadFile('cr_file', $id_number_post);
    $valid_id_file = uploadFile('valid_id_file', $id_number_post);
    $proof_of_purchase_file = uploadFile('proof_of_purchase_file', $id_number_post);

    // Insert new registration record WITHOUT personal info fields (they are in users table)
    $sql = "INSERT INTO vehicle_registration (
        user_id, vehicle_type, ownership_type,
        license_file, or_file, cr_file, valid_id_file, proof_of_purchase_file,
        registration_date, status, qr_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', 'Inactive')";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssss",
        $user_id,
        $vehicle_type,
        $ownership_type,
        $license_file,
        $or_file,
        $cr_file,
        $valid_id_file,
        $proof_of_purchase_file
    );

    if ($stmt->execute()) {
        // Update request_status in users table
        if (isset($_SESSION['email'])) {
            $sessionEmail = $_SESSION['email'];
            $updateStatus = "UPDATE users SET request_status = 'pending' WHERE email = ?";
            $stmtUpdate = $conn->prepare($updateStatus);
            if ($stmtUpdate) {
                $stmtUpdate->bind_param("s", $sessionEmail);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
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


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register Vehicle</title>

  <style>
    /* Styles from Account.css + your form inline styles combined */

    /* General styles (add your Account.css content here if you want) */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
  }
  
  body {
    height: 100vh;
    background: url('wowbg.jpg') no-repeat center center fixed;
    background-size: cover;
    justify-content: center;
    align-items: center;
    position: relative;
  }

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #33333300;
    padding: 15px 30px;
    color: white;
    gap: 20px;
  }
  
  header img {
    width: 180px;
    height: auto;
  }
  
  nav {
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  nav a {
    margin-left: 20px;
    text-decoration: none;
    color: white;
    font-weight: bold;
  }
  
  nav a:hover {
    color: #FFB000;
  }
  
  .account-icon {
    margin-left: 20px;
    cursor: pointer;
    width: 30px;
    height: 30px;
  }

    .content {
      max-width: 440px;
      margin: 40px auto;
      padding: 20px;
      background-color: #ffffff1c;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .content h2 {
      margin-top: 0;
      margin-bottom: 20px;
      font-weight: 700;
      font-size: 1.8rem;
      color: white;
      text-align: center;
    }

    /* Form styles */
    label {
      font-size: 15px;
      margin-top: 12px;
      display: block;
      color: #FFB000;

    }
    input[type="text"],
    input[type="email"],
    input[type="file"],
    input[type="text"]:read-only {
      font-family: inherit;
      font-size: 1rem;
      margin-top: 5px;
      padding: 8px 10px;
      width: 100%;
      max-width: 400px;
      box-sizing: border-box;
      border-radius: 4px;
      border: 1px solid #ccc;
      background-color: #fff;
      color: #222;
    }
    input[readonly] {
      background-color: #eee;
      color: #555;
      cursor: not-allowed;
    }
    button {
      cursor: pointer;
      background-color: #FFB000;
      border: none;
      color: white;
      margin-top: 15px;
      max-width: 200px;
      padding: 10px 15px;
      border-radius: 4px;
      font-size: 1rem;
      transition: background-color 0.3s ease;
      box-sizing: border-box;
      display: block;
      margin: 0 auto;
      margin-top: 20px;
    }
    button:hover {
      background-color: #d18f00;
    }
    form {
      max-width: 480px;
    }

    /* Message styles */
    .message {
      margin-bottom: 15px;
      font-weight: bold;
    }
    .error {
      color: #cc0000;
    }
    .success {
      color: #008000;
    }

    select#ownership_type {
  margin-top: 5px;
  padding: 8px 12px;
  width: 100%;
  max-width: 400px;
  font-family: inherit;
  font-size: 1rem;
  border-radius: 4px;
  border: 1px solid #ccc;
  background-color: #fff;
  color: #222;
  box-sizing: border-box;
  cursor: pointer;
  transition: border-color 0.3s ease;
}

select#ownership_type:focus {
  border-color: #FFB000;
  outline: none;
  box-shadow: 0 0 3px #007bffaa;
}

    footer {
      text-align: center;
      padding: 20px 0;
      font-size: 0.9rem;
      color: white;
      margin-top: 40px;
      font-size: 17px;
    }
  </style>
</head>
<body>
  <header>
    <img src="AUTORIDE LOGO1-04.png" alt="Logo" />
    <nav>
      <a href="../LOGIN/homepage.php">HOME</a>
      <a href="../LOGIN/Aboutpage.php">ABOUT</a>
      <a href="../LOGIN/Accountpage.php">
        <img src="profile-user.png" alt="Account Icon" class="account-icon" />
      </a>  
    </nav>
  </header>

  <div class="content">
    <h2>Register Vehicle</h2>

    <?php
    if (isset($_GET['error']) && $_GET['error'] === 'already_registered') {
        echo '<p class="message error">You have already registered a vehicle and your request is still pending or approved.</p>';
    }
    if (isset($_GET['success'])) {
        echo '<p class="message success">Vehicle registration successful! Please wait for approval.</p>';
    }
    ?>

    <?php if ($user_email): ?>
    <form action="registerVehicle.php" method="POST" enctype="multipart/form-data" novalidate>
      <label>Name:</label>
      <input type="text" name="name" value="<?= $name ?>" readonly />

      <label>ID Number:</label>
      <input type="text" name="id_number" value="<?= $id_number ?>" readonly />

      <label>Mobile Number:</label>
      <input type="text" name="mobile_number" value="<?= $mobile_number ?>" readonly />

      <label>Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" readonly />

      <label>Course/Year/Section:</label>
      <input type="text" name="course_year_section" value="<?= $course_year_section ?>" readonly />

      <label>Vehicle Type:</label>
      <input type="text" name="vehicle_type" required />

        
      <!-- Ownership Dropdown -->
      <method="POST" enctype="multipart/form-data">
              <label for="ownership_type">Ownership Type:</label>
      <select id="ownership_type" name="ownership_type" onchange="showFileInputs()" required>
          <option value="">-- Select Ownership --</option>
          <option value="First Owner">First Owner</option>
          <option value="Second Owner">Second Owner</option>
      </select>

      <br><br>

      <!-- Group for License, OR, CR -->
      <div id="vehicle_files" style="display: none;">
          <div>
              <label>License File:</label>
              <input type="file" name="license_file" accept=".jpg,.jpeg,.png,.pdf">
          </div>

          <div>
              <label>OR File:</label>
              <input type="file" name="or_file" accept=".jpg,.jpeg,.png,.pdf">
          </div>

          <div>
              <label>CR File:</label>
              <input type="file" name="cr_file" accept=".jpg,.jpeg,.png,.pdf">
          </div>
      </div>

      <!-- Valid ID -->
      <div id="valid_id_group" style="display: none;">
          <label>Valid ID File:</label>
          <input type="file" name="valid_id_file" accept=".jpg,.jpeg,.png,.pdf">
      </div>

      <!-- Proof of Purchase -->
      <div id="proof_group" style="display: none;">
          <label>Authorization (Proof of Purchase):</label>
          <input type="file" name="proof_of_purchase_file" accept=".jpg,.jpeg,.png,.pdf">
      </div>

      <br>
      <button type="submit">Submit</button>
    </form>
    <?php else: ?>
      <p>Please <a href="login.php">login</a> first to register your vehicle.</p>
    <?php endif; ?>
  </div>

<script>
function showFileInputs() {
    const ownership = document.getElementById('ownership_type').value;

    const vehicleFiles = document.getElementById('vehicle_files');
    const validIDGroup = document.getElementById('valid_id_group');
    const proofGroup = document.getElementById('proof_group');

    // Default: hide all
    vehicleFiles.style.display = 'none';
    validIDGroup.style.display = 'none';
    proofGroup.style.display = 'none';

    if (ownership === 'First Owner') {
        vehicleFiles.style.display = 'block';
        validIDGroup.style.display = 'block';
    } else if (ownership === 'Second Owner') {
        vehicleFiles.style.display = 'block';
        validIDGroup.style.display = 'block';
        proofGroup.style.display = 'block';
    }
}

  document.querySelector('form').addEventListener('submit', function(e) {
    const vehicleType = this.vehicle_type.value.trim();
    const ownershipType = this.ownership_type.value;

    if (!vehicleType) {
      alert('Please enter Vehicle Type');
      e.preventDefault();
      return false;
    }

    if (!ownershipType) {
      alert('Please select Ownership Type');
      e.preventDefault();
      return false;
    }

    if (ownershipType === 'First Owner') {
      if (!this.license_file.value || !this.or_file.value || !this.cr_file.value || !this.valid_id_file.value) {
        alert('Please upload all required documents for First Owner');
        e.preventDefault();
        return false;
      }
    } else if (ownershipType === 'Second Owner') {
      if (!this.license_file.value || !this.or_file.value || !this.cr_file.value || !this.valid_id_file.value || !this.proof_of_purchase_file.value) {
        alert('Please upload all required documents for Second Owner');
        e.preventDefault();
        return false;
      }
    }
  });
</script>
</body>
</html>
