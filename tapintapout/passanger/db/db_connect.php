<?php
$host = "localhost";
$db_user = "root";      // Default for XAMPP
$db_pass = "";          // Default for XAMPP
$db_name = "tap_in_out"; // Your database name

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for special characters
$conn->set_charset("utf8mb4");
?>