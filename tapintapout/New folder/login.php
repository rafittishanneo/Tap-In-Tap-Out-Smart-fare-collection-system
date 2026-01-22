<?php
session_start();
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tap_in_tap_out';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = "Invalid email or password.";
    } else {
        // FIXED: Removed 'name' from SELECT query
        $stmt = $conn->prepare("SELECT id, email, passwordhash, role FROM users WHERE email = ? LIMIT 1");
        
        if (!$stmt) {
            $error = "Database error.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && !empty($user['passwordhash']) && password_verify($password, $user['passwordhash'])) {
                session_regenerate_id(true);
                $_SESSION['userid'] = $user['id'];
                
                // FIXED: Use email prefix as username since name column is gone
                $email_parts = explode('@', $user['email']);
                $_SESSION['username'] = ucfirst($email_parts[0]); 
                
                $_SESSION['useremail'] = $user['email'];
                $_SESSION['userrole'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin-dashboard.php");
                        exit();
                    case 'moderator':
                        header("Location: modarator-dashboard.php"); // check spelling (modarator vs moderator)
                        exit();
                    default:
                        header("Location: user-dashboard.php");
                        exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tap in Tap Out | Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"><!--[web:28]-->

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

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

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

    .auth-wrapper {
      width: 100%;
      max-width: 960px;
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
      gap: 2rem;
      align-items: center;
    }

    .brand-panel {
      color: #e5e7eb;
      padding-right: 1.5rem;
    }

    .logo-circle {
      width: 70px;
      height: 70px;
      border-radius: 24px;
      background: radial-gradient(circle at 20% 20%, var(--accent), var(--primary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
      box-shadow: 0 18px 40px rgba(15,23,42,0.6);
    }

    .logo-icon {
      width: 40px;
      height: 26px;
      border-radius: 10px;
      background: rgba(15,23,42,0.2);
      border: 2px solid rgba(15,23,42,0.5);
      position: relative;
      overflow: hidden;
    }

    .logo-icon::before {
      content: "";
      position: absolute;
      top: 50%;
      right: -4px;
      transform: translateY(-50%);
      width: 10px;
      height: 10px;
      border-radius: 999px;
      border: 2px solid #e5e7eb;
      border-left-color: transparent;
      border-bottom-color: transparent;
    }

    .logo-icon::after {
      content: "";
      position: absolute;
      top: 50%;
      right: -16px;
      transform: translateY(-50%);
      width: 24px;
      height: 24px;
      border-radius: 999px;
      border: 2px solid rgba(226,232,240,0.55);
      border-left-color: transparent;
      border-bottom-color: transparent;
    }

    .brand-title {
      font-size: 1.9rem;
      font-weight: 600;
      letter-spacing: 0.03em;
      margin-bottom: 0.4rem;
    }

    .brand-title span {
      color: var(--accent);
    }

    .brand-subtitle {
      font-size: 0.98rem;
      color: #9ca3af;
      max-width: 320px;
    }

    .stats-strip {
      display: flex;
      gap: 1rem;
      margin-top: 1.8rem;
      font-size: 0.8rem;
    }

    .stat-card {
      padding: 0.7rem 0.9rem;
      border-radius: 999px;
      background: rgba(15,23,42,0.7);
      border: 1px solid rgba(148,163,184,0.4);
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      color: #e5e7eb;
    }

    .stat-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: var(--accent);
    }

    .auth-card {
      background: var(--card-bg);
      padding: 2rem 2.4rem;
      border-radius: 22px;
      box-shadow: 0 24px 60px rgba(15,23,42,0.45);
      max-width: 420px;
      margin-left: auto;
      border: 1px solid rgba(226,232,240,0.8);
    }

    .auth-header {
      margin-bottom: 1.3rem;
    }

    .auth-header h2 {
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 0.15rem;
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
      padding: 0.15rem 0.7rem;
      background: #eff6ff;
      color: #1d4ed8;
      font-size: 0.7rem;
      margin-bottom: 0.75rem;
      font-weight: 500;
    }

    .badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 999px;
      background: #22c55e;
    }

    form {
      margin-top: 0.2rem;
    }

    .form-group {
      margin-bottom: 0.9rem;
    }

    label {
      display: block;
      font-size: 0.82rem;
      font-weight: 500;
      color: #475569;
      margin-bottom: 0.35rem;
    }

    .input-wrapper {
      position: relative;
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

    .input-field::placeholder {
      color: #94a3b8;
    }

    .error-text {
      color: var(--error);
      font-size: 0.74rem;
      margin-top: 0.2rem;
      min-height: 0.9rem;
    }

    .form-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.9rem;
      margin-top: 0.1rem;
    }

    .remember {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      font-size: 0.8rem;
      color: #475569;
    }

    .remember input {
      width: 14px;
      height: 14px;
      accent-color: var(--primary);
    }

    .forgot-link,
    .forgot-link:visited {
      font-size: 0.78rem;
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .forgot-link:hover {
      text-decoration: underline;
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
      margin-top: 0.2rem;
      box-shadow: 0 14px 35px rgba(37,99,235,0.45);
      transition: transform 0.1s ease, box-shadow 0.1s ease, filter 0.1s ease;
    }

    .btn-primary:hover {
      filter: brightness(1.04);
      transform: translateY(-1px);
      box-shadow: 0 20px 45px rgba(30,64,175,0.55);
    }

    .btn-primary span {
      font-size: 1rem;
    }

    .meta-text {
      margin-top: 0.9rem;
      font-size: 0.78rem;
      color: #6b7280;
      display: flex;
      justify-content: space-between;
      gap: 0.6rem;
      flex-wrap: wrap;
      align-items: center;
    }

    .meta-text a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .meta-text a:hover {
      text-decoration: underline;
    }

    @media (max-width: 860px) {
      .auth-wrapper {
        grid-template-columns: minmax(0, 1fr);
        max-width: 440px;
      }

      .brand-panel {
        display: none;
      }
    }

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

    <!-- Left brand / illustration side -->
    <section class="brand-panel">
      <div class="logo-circle">
        <div class="logo-icon"></div>
      </div>
      <h1 class="brand-title">Tap in <span>Tap Out</span></h1>
      <p class="brand-subtitle">
        Seamless smart ticketing for buses and public transport.
        Tap to start your journey and track every ride in one place.
      </p>

      <div class="stats-strip">
        <div class="stat-card">
          <div class="stat-dot"></div>
          Live journeys today
        </div>
        <div class="stat-card">
          <div class="stat-dot"></div>
          Secure session login
        </div>
      </div>
    </section>

    <!-- Right card: Login form -->
    <section class="auth-card">
      <div class="auth-header">
        <div class="badge">
          <span class="badge-dot"></span>
          Active
        </div>
        <h2>Sign in to continue</h2>
        <p>Access your dashboard to tap in, tap out, and view your journey history.</p>
      </div>

      <form id="loginForm" method="post" action="login.php">
        <div class="form-group">
          <label for="email">Email address</label>
          <div class="input-wrapper">
            <input
              type="email"
              id="email"
              name="email"
              class="input-field"
              placeholder="you@example.com"
              required
            >
          </div>
          <div class="error-text" id="emailError"></div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <input
              type="password"
              id="password"
              name="password"
              class="input-field"
              placeholder="Enter your password"
              minlength="6"
              required
            >
          </div>
          <div class="error-text" id="passwordError"></div>
        </div>

        <div class="form-row">
          <label class="remember">
            <input type="checkbox" name="remember" id="remember">
            Remember me
          </label>
          <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary">
          Login
        </button>

        <p class="switch-link" style="margin-top: 1rem; font-size: 0.8rem; color: #64748b; display: flex; flex-direction: column; gap: 0.5rem; align-items: center;">
        <div>
            New here? 
            <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Create account</a>
        </div>
            <a href="index.html" style="color: #64748b; text-decoration: none; font-size: 0.75rem;">
            Back to Home
            </a>
        </p>

      </form>
    </section>
  </div>

  <script>
    // Basic client-side validation (you can extend this)
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    form.addEventListener('submit', function (e) {
      let valid = true;
      emailError.textContent = '';
      passwordError.textContent = '';

      if (!emailInput.value.includes('@')) {
        emailError.textContent = 'Please enter a valid email address.';
        valid = false;
      }

      if (passwordInput.value.trim().length < 6) {
        passwordError.textContent = 'Password must be at least 6 characters long.';
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  </script>

</body>
</html>

<?php $conn->close(); ?> 
