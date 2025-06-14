<?php
session_start();
include("connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Homepage</title>
  <link rel="stylesheet" href="Account.css" />
</head>
<body>
  <header>
    <img src="AUTORIDE LOGO1-04.png" alt="Logo" />
    <nav>
      <a href="homepage.php">HOME</a>
      <a href="Aboutpage.php">ABOUT</a>
      <a href="Accountpage.php">
        <img src="profile-user.png" alt="Account Icon" class="account-icon" />
      </a>  
    </nav>
  </header>

<div class="content">
  <?php 
  if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Get user info first using email
    $userStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $userStmt->bind_param("s", $email);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userRow = $userResult->fetch_assoc()) {
      $userId = $userRow['Id']; // note: capital 'I' in Id

      // Now get latest vehicle_registration using user_id
      $stmt = $conn->prepare("
        SELECT status 
        FROM vehicle_registration 
        WHERE user_id = ? 
        ORDER BY registration_date DESC 
        LIMIT 1
      ");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $result = $stmt->get_result();

      $vehicleStatus = "No registration found";
      if ($row = $result->fetch_assoc()) {
        $vehicleStatus = ucfirst(htmlspecialchars($row['status']));
      }

      echo "<h2>ACCOUNT INFORMATION</h2>";

      echo '<table class="account-table" cellspacing="10" cellpadding="8">';
      echo "<tr><th>FIELD</th><th>INFORMATION</th></tr>";
      echo "<tr><td><strong>Account Name</strong></td><td>" . htmlspecialchars($userRow['firstName']) . " " . htmlspecialchars($userRow['lastName']) . "</td></tr>";
      echo "<tr><td><strong>ID Number</strong></td><td>" . htmlspecialchars($userRow['id_number']) . "</td></tr>";
      echo "<tr><td><strong>Mobile Number</strong></td><td>" . htmlspecialchars($userRow['mobile_number']) . "</td></tr>";
      echo "<tr><td><strong>Course/Year/Section</strong></td><td>" . htmlspecialchars($userRow['course_year_section']) . "</td></tr>"; // added
      echo "<tr><td><strong>Status of Request</strong></td><td>" . $vehicleStatus . "</td></tr>";
      echo "<tr><td><strong>Email</strong></td><td>" . htmlspecialchars($userRow['email']) . "</td></tr>";
      echo "<tr><td><strong>Signed Up On</strong></td><td>" . date("F j, Y g:i A", strtotime($userRow['created_at'])) . "</td></tr>";
      echo "</table>";


      // Buttons
      echo '<div class="button-group" style="margin-top: 20px;">';
      echo '<form action="view_qr.php" method="get" style="display: inline-block; margin-right: 10px;">
        <button type="submit" class="btn-view-qr">View My QR</button>
      </form>';

      echo '<a href="logout.php" class="logout-button">LOGOUT</a>';
      echo '</div>';

    } else {
      echo "<p>No account information found.</p>";
    }

    $userStmt->close();
    $stmt->close();

  } else {
    echo "<p>Please log in to view your account details.</p>";  
  }
  ?>
</div>

<footer>
  &copy; <?php echo date("Y"); ?> AutoRide — Keeping your campus secure and smooth.
</footer>

</body>
</html>
