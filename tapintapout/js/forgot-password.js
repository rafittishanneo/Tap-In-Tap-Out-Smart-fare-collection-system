document.getElementById('forgotForm')?.addEventListener('submit', function(e) {
  const email = document.getElementById('email').value.trim();
  if (!email || !email.includes('@')) {
    alert('Please enter a valid email.');
    e.preventDefault();
  }
});

document.getElementById('resetForm')?.addEventListener('submit', function(e) {
  const p1 = document.getElementById('new_password').value;
  const p2 = document.getElementById('confirm_password').value;

  if (p1.length < 6) {
    alert('Password must be at least 6 characters.');
    e.preventDefault();
    return;
  }
  if (p1 !== p2) {
    alert('Passwords do not match.');
    e.preventDefault();
  }
});
