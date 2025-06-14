<?php
session_start();
include("connect.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About</title>
  <link rel="stylesheet" href="About.css" />
</head>
<body>
    <!-- Header -->
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
    <p>AutoRide is a web-based system designed to help universities, particularly Cebu Technological University, manage student vehicle registrations and Gatepass approvals efficiently. 
      The system uses document validation and QR code technology to ensure that only students with complete and valid vehicle documents are allowed to bring vehicles inside the campus.</p>
    <br>
    <p>It features:</p>
    <br>
    <p> - A user-friendly interface for students to register and upload vehicle documents</p><br>
    <p> - Admin-side validation of OR/CR and driver’s license</p><br>
    <p> - QR code generation for approved Gatepass</p><br>
    <p> - SMS notifications upon approval</p>
    <br>
    <p>AutoRide aims to enhance campus security, reduce vehicle congestion, and prevent unqualified or unauthorized students from bringing vehicles into school grounds.</p>
        <img src="ABOUT AUTORIDE.png" alt="Logo" class="autoride-logo" />
    </div>


  <div class="content">
    <!-- WOW HEHE -->
  </div>
  <div class="content">
    <!-- WOW HEHE -->
  </div>
  <div class="content">
    <!-- WOW HEHE -->
  </div>

    <div class="additional-content">
    <p>OFFICIAL LOGO</p>
    <br>
    <img src="ABOUT LOGO.png" alt="Logo" class="autoride-logo" />
    <br>

    </div>






  <footer>
    &copy; <?php echo date("Y"); ?> AutoRide — Keeping your campus secure and smooth.
  </footer>

</body>
</html>