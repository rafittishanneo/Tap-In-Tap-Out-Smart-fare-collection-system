<?php
// forgot-password.php
session_start();
require_once 'db.php'; // include your DB connection

$email = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists in users table
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            // For security, do NOT say "email not found"
            $success = 'If an account exists with that email, a reset link has been sent.';
        } else {
            // TODO: generate token, save to password_resets table, send email
            // For now we only show a confirmation message.
            $success = 'If an account exists with that email, a reset link has been sent.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tap in Tap Out | Forgot Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Same Poppins font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"><!--[web:54]-->

  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --accent: #facc15;
      --bg-dark: #0f172a;
      --card-bg: #ffffff;
      --border-soft: #e2e8f0;
      --error: #b91c1c;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: radial-gradient(circle at top, #1e293b 0, #020617 55%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      color: #0f172a;
    }
    .auth-wrapper { width: 100%; max-width: 440px; }
    .auth-card {
      background: var(--card-bg);
      padding: 2rem 2.4rem;
      border-radius: 22px;
      box-shadow: 0 24px 60px rgba(15,23,42,0.45);
      border: 1px solid rgba(226,232,240,0.8);
    }
    .auth-header { margin-bottom: 1.3rem; }
    .auth-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    .auth-header p {
      font-size: 0.9rem;
      color: #64748b;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      border-radius: 999px;
      padding: 0.18rem 0.7rem;
      background: #fef9c3;
      color: #854d0e;
      font-size: 0.7rem;
      margin-bottom: 0.75rem;
      font-weight: 500;
    }
    .badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 999px;
      background: var(--accent);
    }
    form { margin-top: 0.4rem; }
    .form-group { margin-bottom: 0.9rem; }
    label {
      display: block;
      font-size: 0.82rem;
      font-weight: 500;
      color: #475569;
      margin-bottom: 0.35rem;
    }
    .input-field {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border-radius: 11px;
      border: 1px solid var(--border-soft);
      font-size: 0.85rem;
      outline: none;
      background-color: #f9fafb;
      transition: border 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
    }
    .input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 1px rgba(37,99,235,0.2);
      background-color: #ffffff;
    }
    .input-field::placeholder { color: #94a3b8; }
    .hint {
      font-size: 0.78rem;
      color: #6b7280;
      margin-top: 0.3rem;
    }
    .error-text {
      color: var(--error);
      font-size: 0.8rem;
      margin-top: 0.2rem;
    }
    .success-text {
      color: #15803d;
      font-size: 0.8rem;
      margin-top: 0.5rem;
    }
    .btn-primary {
      width: 100%;
      border: none;
      outline: none;
      padding: 0.72rem 0.9rem;
      border-radius: 999px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #ffffff;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      margin-top: 0.5rem;
      box-shadow: 0 14px 35px rgba(37,99,235,0.45);
      transition: transform 0.1s ease, box-shadow 0.1s ease, filter 0.1s ease;
    }
    .btn-primary:hover {
      filter: brightness(1.04);
      transform: translateY(-1px);
      box-shadow: 0 20px 45px rgba(30,64,175,0.55);
    }
    .btn-primary span { font-size: 1rem; }
    .meta-text {
      margin-top: 0.9rem;
      font-size: 0.8rem;
      color: #6b7280;
      text-align: center;
    }
    .meta-text a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }
    .meta-text a:hover { text-decoration: underline; }
    @media (max-width: 480px) {
      .auth-card {
        padding: 1.6rem 1.5rem;
        border-radius: 18px;
      }
    }
  </style>
</head>
<body>

  <div class="auth-wrapper">
    <section class="auth-card">
      <div class="auth-header">
        <div class="badge">
          <span class="badge-dot"></span>
          Forgot password
        </div>
        <h1>Reset your password</h1>
        <p>Enter the email linked with your Tap in Tap Out account and we’ll send you a reset link.</p>
      </div>

      <form method="post" action="forgot-password.php">
        <div class="form-group">
          <label for="email">Email address</label>
          <input
            type="email"
            id="email"
            name="email"
            class="input-field"
            placeholder="you@example.com"
            value="<?php echo htmlspecialchars($email); ?>"
            required
          >
          <?php if ($error): ?>
            <div class="error-text"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <p class="hint">We never share your email; it is used only for account security.</p>
        </div>

        <button type="submit" class="btn-primary">
          Send reset link
          <span>↗</span>
        </button>

        <p class="meta-text">
          Remember your password?
          <a href="login.php">Back to login</a>

        </p>

        <?php if ($success): ?>
          <p class="success-text"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
      </form>
    </section>
  </div>

</body>
</html>
