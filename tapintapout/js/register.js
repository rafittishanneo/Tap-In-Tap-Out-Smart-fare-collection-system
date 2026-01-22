// register.js

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirmpassword"]');
    const firstName = document.querySelector('input[name="firstname"]');
    const lastName = document.querySelector('input[name="lastname"]');

    form.addEventListener('submit', function (e) {
        let errorMessage = "";

        // 1. Password Match Validation
        if (password.value !== confirmPassword.value) {
            errorMessage = "Passwords do not match!";
        }

        // 2. Password Length Validation
        else if (password.value.length < 6) {
            errorMessage = "Password must be at least 6 characters long.";
        }

        // 3. Name Validation (at least 2 characters)
        else if (firstName.value.trim().length < 2 || lastName.value.trim().length < 2) {
            errorMessage = "First and Last name must be at least 2 characters.";
        }

        // Jodi kono error thake, tobe form submit bondho koro
        if (errorMessage !== "") {
            e.preventDefault(); // Form submit hote dibe na
            alert(errorMessage);
        }
    });

    // Real-time check: Password type korar somoy red border deya
    confirmPassword.addEventListener('input', function () {
        if (password.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = "#b91c1c"; // Red color
        } else {
            confirmPassword.style.borderColor = "#e2e8f0"; // Default color
        }
    });
});