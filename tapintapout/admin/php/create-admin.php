<?php
require_once '../db/db.php';

$email = 'admin@example.com';
$name = 'Admin User';
$password = password_hash('password', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, passwordhash, role) VALUES (?, ?, ?, 'admin')");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    echo "<h2>✅ Admin created successfully!</h2>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Password:</strong> password</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
} else {
    echo "<h2>❌ Error:</h2>" . $stmt->error;
}

$stmt->close();
$conn->close();
?>
