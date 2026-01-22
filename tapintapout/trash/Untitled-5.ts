// public/assets/js/app.js
document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      let valid = true;
      const email = document.getElementById('email');
      const password = document.getElementById('password');
      const emailError = document.getElementById('emailError');
      const passwordError = document.getElementById('passwordError');
      emailError.textContent = '';
      passwordError.textContent = '';

      if (!email.value.includes('@')) {
        emailError.textContent = 'Enter a valid email.';
        valid = false;
      }
      if (password.value.length < 6) {
        passwordError.textContent = 'Password must be at least 6 characters.';
        valid = false;
      }
      if (!valid) e.preventDefault();
    });
  }
});
