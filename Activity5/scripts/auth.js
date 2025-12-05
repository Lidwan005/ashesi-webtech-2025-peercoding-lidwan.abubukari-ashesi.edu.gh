// Show error message
function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + '-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// Clear error message
function clearError(fieldId) {
    const errorElement = document.getElementById(fieldId + '-error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
}

// Show success message
function showSuccess(elementId, message) {
    const successElement = document.getElementById(elementId);
    if (successElement) {
        successElement.textContent = message;
        successElement.style.display = 'block';
        setTimeout(() => {
            successElement.style.display = 'none';
        }, 3000);
    }
}

// Switch forms
function showLoginForm() {
    document.getElementById('register-section').classList.remove('active');
    document.getElementById('login-section').classList.add('active');
    document.title = "Attendance Management System - Login";
}

function showRegisterForm() {
    document.getElementById('login-section').classList.remove('active');
    document.getElementById('register-section').classList.add('active');
    document.title = "Attendance Management System - Register";
}

// Handle Registration
function handleRegister(event) {
    event.preventDefault();

    // Clear all previous errors
    clearError('register-name');
    clearError('register-email');
    clearError('register-password');
    clearError('register-confirm-password');
    clearError('register-role');

    const form = document.getElementById('register-form');
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm-password').value;

    // Check if passwords match
    if (password !== confirmPassword) {
        showError('register-confirm-password', 'Passwords do not match');
        return;
    }

    const formData = new FormData(form);

    // Send the request
    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                showSuccess('register-success', data.message);
                form.reset();
                setTimeout(() => {
                    showLoginForm();
                }, 2000);
            } else {
                showError('register-email', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('register-email', 'Connection error. Please check if Apache and MySQL are running.');
        });
}

// Handle Login
function handleLogin(event) {
    event.preventDefault();

    clearError('login-email');
    clearError('login-password');

    const form = document.getElementById('login-form');
    const formData = new FormData(form);

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                showSuccess('login-success', 'Login successful! Redirecting...');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                showError('login-password', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('login-password', 'Connection error. Please try again.');
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');

    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const showLoginLink = document.getElementById('show-login-link');
    const showRegisterLink = document.getElementById('show-register-link');

    if (showLoginLink) {
        showLoginLink.addEventListener('click', function (e) {
            e.preventDefault();
            showLoginForm();
        });
    }

    if (showRegisterLink) {
        showRegisterLink.addEventListener('click', function (e) {
            e.preventDefault();
            showRegisterForm();
        });
    }
});
