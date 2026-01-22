// Client-side validation
const form = document.getElementById('createModeratorForm');
const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const errors = {
    nameError: document.getElementById('nameError'),
    emailError: document.getElementById('emailError'),
    passwordError: document.getElementById('passwordError'),
    confirmError: document.getElementById('confirmError')
};

form.addEventListener('submit', function(e) {
    let valid = true;
    Object.values(errors).forEach(el => el.textContent = '');

    if (nameInput.value.trim().length < 2) {
        errors.nameError.textContent = 'Name must be at least 2 characters.';
        valid = false;
    }
    if (!emailInput.value.includes('@') || !emailInput.value.includes('.')) {
        errors.emailError.textContent = 'Please enter a valid email.';
        valid = false;
    }
    if (passwordInput.value.length < 6) {
        errors.passwordError.textContent = 'Password must be at least 6 characters.';
        valid = false;
    }
    if (passwordInput.value !== confirmInput.value) {
        errors.confirmError.textContent = 'Passwords do not match.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
