// Form Validation - AniLog (Client-side validation using regex)


// Email validation regex
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Rating validation (1-10, allowing decimals like 9.4)
const RATING_REGEX = /^(10(\.0)?|[1-9](\.[0-9])?)$/;

// Episode number validation (positive integers)
const EPISODE_REGEX = /^\d+$/;

// Validate registration form
document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('registerForm');

    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            let isValid = true;

            // Clear previous errors
            clearErrors();

            // Validate username
            const username = document.getElementById('username').value.trim();
            if (username.length < 3) {
                showError('username', 'Username must be at least 3 characters');
                isValid = false;
            }

            // Validate email
            const email = document.getElementById('email').value.trim();
            if (!EMAIL_REGEX.test(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            }

            // Validate password
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                showError('password', 'Password must be at least 6 characters');
                isValid = false;
            }

            // Validate password confirmation
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                showError('confirm', 'Passwords do not match');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Validate rating input

    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    ratingInputs.forEach(input => {
        input.addEventListener('blur', function () {
            const value = this.value.trim();
            if (!value) {
                clearError(this.id);
                return;
            }

            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue < 1 || numValue > 10) {
                showError(this.id, 'Rating must be between 1 and 10');
            } else {
                clearError(this.id);
            }
        });
    });

    // Validate episode input
    const episodeInputs = document.querySelectorAll('input[name="current_episode"]');
    episodeInputs.forEach(input => {
        input.addEventListener('blur', function () {
            const value = this.value.trim();
            const maxEpisodes = parseInt(this.getAttribute('max')) || 999;

            if (value && !EPISODE_REGEX.test(value)) {
                showError(this.id, 'Please enter a valid episode number');
            } else if (parseInt(value) > maxEpisodes) {
                showError(this.id, `Episode cannot exceed ${maxEpisodes}`);
            } else {
                clearError(this.id);
            }
        });
    });
});

// Show error message

function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + '-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#EF4444';
    }
}

// Clear specific error

function clearError(fieldId) {
    const errorElement = document.getElementById(fieldId + '-error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }

    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#334155';
    }
}

// Clear all errors

function clearErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });

    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.style.borderColor = '#334155';
    });
}
