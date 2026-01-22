<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tap_in_tap_out';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
