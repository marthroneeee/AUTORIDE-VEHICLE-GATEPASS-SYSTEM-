<?php
session_start();
include("connect.php"); 

$user_email = $_SESSION['email'] ?? '';
$user_id_number = $_SESSION['id_number'] ?? '';

$has_pending_request = false;

if (!empty($user_email) && !empty($user_id_number)) {
    $stmt = $conn->prepare("SELECT 1 FROM vehicle_requests WHERE (email = ? OR id_number = ?) AND request_status = 'pending' LIMIT 1");
    $stmt->bind_param("ss", $user_email, $user_id_number);
    $stmt->execute();
    $stmt->store_result();
    $has_pending_request = $stmt->num_rows > 0;
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Homepage</title>
  <link rel="stylesheet" href="homepage.css" />
</head>
<body>

<div id="video-preloader">
  <video autoplay muted playsinline id="preload-video">
    <source src="AUTORIDE LOGO ANIMATION.mp4" type="video/mp4" />
    Your browser does not support the video tag.
  </video>
</div>

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
  <img src="AUTORIDE HOME.png" alt="Logo" class="homepage-logo" />

  <?php if ($has_pending_request): ?>
    <p style="color: red; font-weight: bold;">
      You have a pending vehicle registration request. You cannot submit another registration until this is reviewed.
    </p>
    <a href="#" class="register-button" style="pointer-events: none; opacity: 0.5;">REGISTER</a>
  <?php else: ?>
    <a href="../registration/registerVehicle.php" class="register-button">REGISTER</a>
  <?php endif; ?>
</div>

<footer>
  &copy; <?php echo date("Y"); ?> AutoRide — Keeping your campus secure and smooth.
</footer>

<script>
  const video = document.getElementById('preload-video');
  const preloader = document.getElementById('video-preloader');

  function hidePreloader() {
    preloader.classList.add('swipe-up');
    setTimeout(() => {
      preloader.style.display = 'none';
    }, 1000);
  }

  video.addEventListener('ended', hidePreloader);

  setTimeout(hidePreloader, 3000);
</script>

</body>
</html>
