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
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="post" action="">
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
