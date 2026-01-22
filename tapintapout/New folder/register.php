<?php
session_start();
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tap_in_tap_out'; // Ensure this matches your DB name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

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
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password and insert
            // NOTE: We only insert email, passwordhash, and role because 'name' column was dropped
            $passwordhash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (email, passwordhash, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $passwordhash, $role);

            if ($stmt->execute()) {
                $success = "Account created! You can now <a href='login.php'>login</a>.";
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
    <style>
        /* Copy the exact same CSS from your register.html style block */
        :root { --primary:#2563eb;--primary-dark:#1d4ed8;--accent:#facc15;--bg-dark:#0f172a;--card-bg:#ffffff;--border-soft:#e2e8f0;--error:#b91c1c; }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:radial-gradient(circle at top,#1e293b 0,#020617 55%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;color:#0f172a}
        .auth-wrapper{width:100%;max-width:480px}
        .auth-card{background:var(--card-bg);padding:2rem 2.4rem;border-radius:22px;box-shadow:0 24px 60px rgba(15,23,42,.45);border:1px solid rgba(226,232,240,.8)}
        .success-text{color:#15803d;font-size:.9rem;margin-top:.5rem;text-align:center}
        .error-text{color:var(--error);font-size:.8rem;margin-top:.2rem}
        /* Add all other CSS classes from register.html */
        label{display:block;font-size:.82rem;font-weight:500;color:#475569;margin-bottom:.35rem}
        .input-field{width:100%;padding:.65rem .9rem;border-radius:11px;border:1px solid var(--border-soft);font-size:.85rem;outline:none;background-color:#f9fafb;transition:border .16s ease,box-shadow .16s ease}
        .input-field:focus{border-color:var(--primary);box-shadow:0 0 0 1px rgba(37,99,235,.2);background-color:#ffffff}
        .two-cols{display:flex;gap:.75rem}
        .form-group{margin-bottom:1rem}
        .btn-primary{width:100%;border:none;padding:.72rem .9rem;border-radius:999px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#ffffff;font-size:.9rem;font-weight:600;cursor:pointer;margin-top:.4rem}
        .meta-text{margin-top:.9rem;font-size:.8rem;color:#6b7280;text-align:center}
        .meta-text a{color:var(--primary);text-decoration:none;font-weight:500}
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <section class="auth-card">
            <div class="auth-header">
                <h1>Join Tap in Tap Out</h1>
                <p>Register to start your journey.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-text"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-text"><?php echo $success; ?></div>
            <?php else: ?>

            <form method="post" action="register.php">
                <div class="form-group two-cols">
                    <div>
                        <label>First name</label>
                        <input type="text" name="firstname" class="input-field" placeholder="John" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label>Last name</label>
                        <input type="text" name="lastname" class="input-field" placeholder="Doe" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="input-field" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input-field" placeholder="Create password (min 6 chars)" minlength="6" required>
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
                        <option value="modarator">Moderator</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Create Account</button>
            </form>
            <?php endif; ?>

            <p class="meta-text">
                Already registered? <a href="login.php">Login here</a>
            </p>
        </section>
    </div>
</body>
</html>
<?php $conn->close(); ?>
