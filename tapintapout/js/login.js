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
