<?php
$servername = "localhost";
$username = "root"; // change this if needed
$password = ""; // change this if needed
$dbname = "auth_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4 for better security
$conn->set_charset("utf8mb4");
?>
