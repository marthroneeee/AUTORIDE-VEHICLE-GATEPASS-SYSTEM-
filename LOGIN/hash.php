<?php
$password = "admin1216"; // imong plain password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Plain Password: $password<br>";
echo "Hashed Password: $hashedPassword";
?>