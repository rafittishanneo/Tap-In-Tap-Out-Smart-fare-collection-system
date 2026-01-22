<?php
session_start();
// Admin-only access - moderators can only be created by admins
if (!isset($_SESSION['userid']) || !isset($_SESSION['userrole']) || $_SESSION['userrole'] != 'admin') {
    header("Location: login.php");
    exit();
}

include '../db/db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? '';

    // Basic Validation
    if (strlen($firstname) < 2 || strlen($lastname) < 2) {
        $error = "Name must be at least 2 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirmpassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password and insert as MODERATOR only
            $passwordhash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, passwordhash, role) VALUES (?, ?, 'moderator')");
            $stmt->bind_param("ss", $email, $passwordhash);

            if ($stmt->execute()) {
                $success = "Moderator account created successfully! They can now <a href='../php/login.php'>login</a>.";
            } else {
                $error = "Registration failed. Try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Moderator | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/web-tech/tapintapout/moderator/create-moderator.css">
</head>
<body>
    <div class="auth-wrapper">
        <section class="auth-card">
            <div class="admin-badge">🔐 Admin Only</div>
            <div class="auth-header">
                <h1>Create Moderator Account</h1>
                <p>Register a new moderator for route management.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-text"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-text"><?php echo $success; ?></div>
            <?php else: ?>
            <form method="post" action="create-moderator.php">
                <div class="form-group two-cols">
                    <div>
                        <label>First name</label>
                        <input type="text" name="firstname" class="input-field" placeholder="MD Rafit" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label>Last name</label>
                        <input type="text" name="lastname" class="input-field" placeholder="Tishan Neo" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="input-field" placeholder="mail@address.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input-field" placeholder="Create password (min 6 chars)" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirmpassword" class="input-field" placeholder="Re-type password" minlength="6" required>
                </div>
                <button type="submit" class="btn-primary">Register</button>
            </form>
            <?php endif; ?>
            <p class="meta-text">
                <a href="/web-tech/tapintapout/admin/php/admin-dashboard.php">Back to Admin Dashboard</a>
            </p>
        </section>
    </div>
</body>
</html>
<?php $conn->close(); ?>
