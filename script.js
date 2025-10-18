// Function to toggle between login and registration forms
function toggleForms(formId) {
  document.getElementById('loginForm').style.display = formId === 'loginForm' ? 'block' : 'none';
  document.getElementById('registerForm').style.display = formId === 'registerForm' ? 'block' : 'none';
}

// Handle Login Submission
document.getElementById('loginForm').addEventListener('submit', function (event) {
  event.preventDefault(); // Prevent page reload

  const email = document.getElementById('loginEmail').value;
  const password = document.getElementById('loginPassword').value;

  // Simple client-side validation (for prototype)
  if (email && password.length >= 8) {
    // Simulate successful login
    alert('Login successful! Redirecting to dashboard...');
    window.location.href = 'dashboard.html'; // redirect to dashboard
  } else {
    alert('Please enter a valid email and password (min 8 characters).');
  }
});

// Handle Registration Form Submission
document.getElementById('registerForm').addEventListener('submit', function (event) {
  event.preventDefault();

  const name = document.getElementById('regName').value;
  const email = document.getElementById('regEmail').value;
  const password = document.getElementById('regPassword').value;
  const role = document.getElementById('regRole').value;

  if (name && email && password.length >= 8 && role) {
    alert('Registration successful! You can now log in.');
    toggleForms('loginForm');
  } else {
    alert('Please fill out all fields correctly.');
  }
});
