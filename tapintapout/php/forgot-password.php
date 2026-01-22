<?php
session_start();
require_once '../db/db.php';

$email = '';
$error = '';
$success = '';
$step = 'email'; // email | reset

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 1: check email
    if (($_POST['action'] ?? '') === 'check_email') {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user) {
                $_SESSION['reset_email'] = $email;
                $step = 'reset';
            } else {
                $error = 'No account found with this email.';
                $step = 'email';
            }
        }
    }

    // STEP 2: update password
    if (($_POST['action'] ?? '') === 'reset_password') {
        $email = $_SESSION['reset_email'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$email) {
            $error = 'Session expired. Please enter email again.';
            $step = 'email';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
            $step = 'reset';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
            $step = 'reset';
        } else {
            // Store hashed password (bcrypt/argon depending on PHP) [web:323]
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // ✅ correct column name from your table: password_hash [file:357]
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ? LIMIT 1");
            $stmt->bind_param('ss', $hashed, $email);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                unset($_SESSION['reset_email']);
                $success = 'Password updated successfully. You can login now.';
                $step = 'email';
                $email = '';
            } else {
                $error = 'Failed to update password. Try again.';
                $step = 'reset';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tap in Tap Out | Forgot Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="auth-wrapper">
  <section class="auth-card">
    <div class="auth-header">
      <div class="badge"><span class="badge-dot"></span> Forgot password</div>
      <h1>Reset your password</h1>
      <p>Enter email, then set a new password.</p>
    </div>

    <?php if ($error): ?><div class="error-text"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><p class="success-text"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

    <?php if ($step === 'email'): ?>
      <form id="forgotForm" method="post" action="../php/forgot-password.php">
        <input type="hidden" name="action" value="check_email">
        <div class="form-group">
          <label for="email">Email address</label>
          <input class="input-field" type="email" id="email" name="email"
                 placeholder="you@example.com"
                 value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <button type="submit" class="btn-primary">Continue <span>↗</span></button>
        <p class="meta-text">Remember your password? <a href="../php/login.php">Back to login</a></p>
      </form>
    <?php else: ?>
      <form id="resetForm" method="post" action="../php/forgot-password.php">
        <input type="hidden" name="action" value="reset_password">

        <div class="form-group">
          <label>Email</label>
          <input class="input-field" type="email"
                 value="<?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>"
                 disabled>
        </div>

        <div class="form-group">
          <label for="new_password">New password</label>
          <input class="input-field" type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm password</label>
          <input class="input-field" type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn-primary">Update password <span>↗</span></button>
        <p class="meta-text"><a href="../php/login.php">Back to login</a></p>
      </form>
    <?php endif; ?>
  </section>
</div>

<script src="../js/forgot-password.js"></script>
</body>
</html>
