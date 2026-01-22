<!-- app/views/auth/login.php -->
<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="brand">
      <img src="/assets/img/logo.png" class="logo" alt="Tap in Tap Out">
      <h1>Tap in Tap Out</h1>
      <p>Smart ticketing for public transport</p>
    </div>
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); ?></div>
      <?php unset($_SESSION['error']); endif; ?>
    <form id="loginForm" method="post" action="/login">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" id="email" required>
        <small class="error" id="emailError"></small>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="password" required minlength="6">
        <small class="error" id="passwordError"></small>
      </div>
      <div class="form-footer">
        <label class="remember">
          <input type="checkbox" name="remember"> Remember me
        </label>
        <a href="/forgot-password">Forgot password?</a>
      </div>
      <button type="submit" class="btn-primary">Login</button>
      <p class="switch-link">New here? <a href="/register">Create account</a></p>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
