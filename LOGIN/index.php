<?php
session_start();
$message = "";
$formToShow = "signIn";

if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $formToShow = $_SESSION['form'] ?? "signIn";
    unset($_SESSION['error'], $_SESSION['form']);
} elseif (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $formToShow = $_SESSION['form'] ?? "signIn";
    unset($_SESSION['success'], $_SESSION['form']);
}
?>

<!DOCTYPE html>
<html lang="en" data-message="<?= htmlspecialchars($message) ?>" data-form="<?= $formToShow ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register & Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />

</head>
<body>

  <div class="container" id="signup" style="display:none;">
    <img src="AUTORIDE LOGO1-04.png" alt="Logo" class="logo" />
    <h1 class="form-title">SIGN UP</h1>
    <form method="post" action="register.php">
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="fName" id="fName" placeholder="First Name" required />
        <label for="fName">First Name</label>
      </div>
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="lName" id="lName" placeholder="Last Name" required />
        <label for="lName">Last Name</label>
      </div>
      <div class="input-group">
      <i class="fas fa-book"></i>
      <input type="text" name="course_year_section" id="course_year_section" placeholder="Course Year & Section" required />
      <label for="course_year_section">Course Year & Section</label>
      </div>
      <div class="input-group">
        <i class="fas fa-id-card"></i>
        <input type="text" name="id_number" id="id_number" placeholder="ID Number" required />
        <label for="id_number">ID Number</label>
      </div>
      <div class="input-group">
        <i class="fas fa-phone"></i>
        <input type="tel" name="mobile_number" id="mobile_number" placeholder="Mobile Number" required />
        <label for="mobile_number">Mobile Number</label>
      </div>
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" id="signup_email" placeholder="Email" required />
        <label for="signup_email">Email</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="signup_password" placeholder="Password" required />
        <label for="signup_password">Password</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="retype_password" id="retype_password" placeholder="Retype Password" required />
        <label for="retype_password">Retype Password</label>
      </div>
      <input type="submit" class="btn" value="Sign Up" name="signUp" />
    </form>
    <div class="links">
      <p>Already Have Account ?</p>
      <button id="signInButton">SIGN IN</button>
    </div>
  </div>

  <div class="container" id="signIn">
    <img src="AUTORIDE LOGO1-04.png" alt="Logo" class="logo" />
    <h1 class="form-title">SIGN IN</h1>
<form method="post" action="register.php">
  <div class="input-group">
    <i class="fas fa-envelope"></i>
    <input type="email" name="email" id="signin_email" placeholder="Email" required />
    <label for="signin_email">Email</label>
  </div>
  <div class="input-group">
    <i class="fas fa-lock"></i>
    <input type="password" name="password" id="signin_password" placeholder="Password" required />
    <label for="signin_password">Password</label>
  </div>
  <input type="submit" class="btn" value="Sign In" name="signIn" />
</form>

    </form>
    <div class="links">
      <p>Don't have account yet?</p>
      <button id="signUpButton">SIGN UP</button>
    </div>


  </div>

  <!-- Modal for messages -->
  <div id="messageModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <p id="modalMessage"></p>
    </div>
  </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const message = document.documentElement.getAttribute("data-message");
  const formToShow = document.documentElement.getAttribute("data-form");

  const signupForm = document.getElementById("signup");
  const signInForm = document.getElementById("signIn");
  const modal = document.getElementById("messageModal");
  const modalMsg = document.getElementById("modalMessage");
  const closeBtn = modal.querySelector(".close");

  // Show correct form
  if (formToShow === "signUp") {
    signupForm.style.display = "block";
    signInForm.style.display = "none";
  } else {
    signupForm.style.display = "none";
    signInForm.style.display = "block";
  }

  // Show modal if message exists
  if (message && message.trim() !== "") {
    modalMsg.textContent = message;
    modal.style.display = "block";
    setTimeout(() => modal.style.opacity = 1, 10);

    function fadeOutModal() {
      modal.style.opacity = 0;
      setTimeout(() => modal.style.display = "none", 500);
    }

    closeBtn.onclick = fadeOutModal;
    window.onclick = function(event) {
      if (event.target === modal) fadeOutModal();
    };
  }

  // Toggle forms buttons
  document.getElementById("signUpButton").addEventListener("click", function () {
    signupForm.style.display = "block";
    signInForm.style.display = "none";
  });

  document.getElementById("signInButton").addEventListener("click", function () {
    signupForm.style.display = "none";
    signInForm.style.display = "block";
  });
});
</script>

</body>
</html>
