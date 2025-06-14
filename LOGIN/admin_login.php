<?php
session_start();
$message = $_SESSION['error'] ?? "";
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<style>
  * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

body {
  height: 100vh;
  background: url('wowbg.jpg') no-repeat center center fixed;
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.login-container {
  background: #ffffff1c;
  width: 450px;
  padding: 2rem;
  border-radius: 30px;
  z-index: 1;
  color: white;
}

h1 {
  font-size: 20px;
  text-align: center;
  color: white;
  margin-bottom: 1.5rem;
  margin-top: 20px;
}

form {
  margin: 0 2rem;
}

.input-group {
  padding:1% 0;
  position:relative;
}

.input-group i {
  position: absolute;
  color: black;
  padding-top: 7px;
  padding-left: 10px;
}

.input-group input {
  width: 100%;
  padding: 5px;
  padding-left: 35px;
  border: none;
  border-bottom: 1px solid #757575;
  border-radius: 10px;
  font-size: 15px;
  background: white;
  color: black;
}

.input-group input:focus {
  background-color: transparent;
  outline: none;
  border-bottom: 2px solid #FFB000;
}

.input-group input::placeholder {
  color: transparent;
}

.input-group label {
  color: #000;
  position: relative;
  left: 2em;
  top: -1.4em;
  cursor: auto;
  transition: 0.3s ease all;
}

.input-group input:focus ~ label,
.input-group input:not(:placeholder-shown) ~ label {
  top: -3em;
  font-size: 14px;
  color: #FFB000;
}

.btn {
  font-size: 1.1rem;
  padding: 10px 0;
  border-radius: 5px;
  border: none;
  width: 100%;
  background: #FFB000;
  color: white;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.btn:hover {
  background: #d18f00;
}

.error-message {
  background: #900;
  padding: 10px;
  margin-bottom: 1rem;
  border-radius: 6px;
  text-align: center;
  color: white;
}

.back-link {
  margin-top: 1rem;
  text-align: center;
}

.back-link a {
  color: white;
  text-decoration: none;
  font-weight: bold;
}

.back-link a:hover {
  color: #d18f00;
}

/* Responsive */
@media (max-width: 600px) {
  .login-container {
    width: 90%;
    padding: 1.5rem;
  }

  h1 {
    font-size: 18px;
  }

  .btn {
    font-size: 1rem;
  }
}

.logo {
  display: block;
  margin: -10px auto  -10px;
  width: 180px;
  height: auto;
}

</style>
</head>
<body>

<div class="login-container">
  <img src="AUTORIDE LOGO1-04.png" alt="Logo" class="logo" />
  <h1>ADMIN LOGIN</h1>

  <?php if ($message): ?>
    <div class="error-message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="post" action="admin_auth.php">
    <div class="input-group">
      <i class="fas fa-user"></i>
      <input type="text" name="admin_username" placeholder="Username" required autofocus />
      <label for="admin_username">Username</label>
    </div>

    <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" name="admin_password" placeholder="Password" required />
      <label for="admin_password">Password</label>
    </div>

    <button type="submit" name="adminLogin" class="btn">Login as Admin</button>
  </form>

  <div class="back-link">
    <a href="index.php">BACK TO USER LOGIN</a>
  </div>
</div>  

</body>
</html>
