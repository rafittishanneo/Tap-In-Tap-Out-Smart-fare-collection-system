<?php
session_start();
// Database connection
require_once '../db/db.php'; 

// If user are already logged in, then go to dashboard
if (isset($_SESSION['userid'])) {
    if ($_SESSION['userrole'] === 'admin') {
        header("Location: ../admin/php/admin-dashboard.php");
    } elseif ($_SESSION['userrole'] === 'moderator') {
        header("Location: ../moderator/moderator-dashboardphp.php");
    } else {
        header("Location: ../passanger/php/user-dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = "Invalid email or password format.";
    } else {
        // Prepare statement (db.php file-e $conn variable thakte hobe)
        $stmt = $conn->prepare("SELECT id, email, passwordhash, role FROM users WHERE email = ? LIMIT 1");
        
        if (!$stmt) {
            $error = "Database error.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            // Password check (passwordhash column matching)
            if ($user && !empty($user['passwordhash']) && password_verify($password, $user['passwordhash'])) {
                session_regenerate_id(true);
                
                $_SESSION['userid'] = $user['id'];
                $email_parts = explode('@', $user['email']);
                $_SESSION['username'] = ucfirst($email_parts[0]); 
                $_SESSION['useremail'] = $user['email'];
                $_SESSION['userrole'] = $user['role'];

                // Role wise redirection
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/php/admin-dashboard.php");
                } elseif ($user['role'] === 'moderator') {
                    header("Location: ../moderator/moderator-dashboardphp.php");
                } else {
                    header("Location: ../passanger/php/user-dashboard.php");
                }
                exit();
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <div class="auth-wrapper">
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
                <div class="stat-card"><div class="stat-dot"></div> Live journeys</div>
                <div class="stat-card"><div class="stat-dot"></div> Secure login</div>
            </div>
        </section>

        <section class="auth-card">
            <div class="auth-header">
                <div class="badge"><span class="badge-dot"></span> Active</div>
                <h2>Sign in to continue</h2>
                <p>Access your dashboard to tap in and out.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #fecaca; font-size: 0.85rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="post" action="../php/login.php">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" class="input-field" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="input-field" placeholder="Enter password" minlength="6" required>
                </div>

                <div class="form-row">
                    <label class="remember">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="../php/forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-primary">Login</button>

                <div class="switch-link">
                    <p style="margin-top: 20px; text-align: center; font-size: 0.85rem; color: #64748b;">
                        New here? <a href="../php/register.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">Create account</a><br><br>
                        <a href="../php/index.php" style="color: #64748b; text-decoration: none; font-size: 0.8rem;">Back to Home</a>
                    </p>
                </div>
            </form>
        </section>
    </div>

</body>
</html>