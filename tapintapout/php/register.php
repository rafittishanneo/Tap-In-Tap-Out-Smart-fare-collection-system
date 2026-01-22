<?php
session_start();
require_once '../db/db.php'; // Database connection file

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? '';
    $role = $_POST['role'] ?? 'user';

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
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $passwordhash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, passwordhash, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $passwordhash, $role);

            if ($stmt->execute()) {
                $success = "Account created! You can now <a href='../php/login.php' style='color: var(--primary); font-weight: 600;'>login</a>.";
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
    <title>Register | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="auth-wrapper">
        <section class="auth-card">
            <div class="auth-header">
                <h1>Join Tap in Tap Out</h1>
                <p>Register to start your journey.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-text-box"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-text"><?php echo $success; ?></div>
            <?php else: ?>

            <form method="post" action="../php/register.php">
                <div class="form-group two-cols">
                    <div style="flex: 1;">
                        <label>First name</label>
                        <input type="text" name="firstname" class="input-field" placeholder="Rafit" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Last name</label>
                        <input type="text" name="lastname" class="input-field" placeholder="Tishan Neo" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="input-field" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input-field" placeholder="Create password" minlength="6" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirmpassword" class="input-field" placeholder="Re-type password" minlength="6" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <select name="role" class="input-field" required>
                        <option value="user" selected>Passenger</option>
                        <option value="admin">Admin</option>
                        <!--<option value="moderator">Moderator</option>-->
                    </select>
                </div>

                <button type="submit" class="btn-primary">Create Account</button>
            </form>
            <?php endif; ?>

            <p class="meta-text">
                Already registered? <a href="../php/login.php">Login here</a>
            </p>
        </section>
    </div>
    <script src="../js/register.js"></script>
</body>
</html>
<?php $conn->close(); ?>