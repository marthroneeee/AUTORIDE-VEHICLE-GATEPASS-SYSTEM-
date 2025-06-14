<?php
include("connect.php");

if($conn){
    echo "Connected to database successfully!";
} else {
    echo "Failed to connect.";
}
?>
