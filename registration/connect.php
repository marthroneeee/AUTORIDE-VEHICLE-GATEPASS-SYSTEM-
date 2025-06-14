<?php
$servername = "localhost";
$username = "root";       // default sa XAMPP
$password = "";           // default sa XAMPP usually empty
$dbname = "autoride_db1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>