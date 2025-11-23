// Simple JavaScript for Attendance Management System

// Test users data (in real app, this would come from database)
const testUsers = [
    { email: "student@ashesi.edu.gh", password: "password123", name: "Test Student", role: "student" },
    { email: "faculty@ashesi.edu.gh", password: "password123", name: "Test Faculty", role: "faculty" },
    { email: "intern@ashesi.edu.gh", password: "password123", name: "Test Intern", role: "intern" }
];

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
        
        // Hide after 5 seconds
        setTimeout(() => {
            successElement.style.display = 'none';
        }, 5000);
    }
}

// Switch between login and register forms
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

// Validate email format
function isValidEmail(email) {
    return email.includes('@') && email.includes('.');
}

// Validate password length
function isValidPassword(password) {
    return password.length >= 8;
}

// Check if passwords match
function passwordsMatch(password, confirmPassword) {
    return password === confirmPassword;
}

// Handle registration form
function handleRegister(event) {
    event.preventDefault();
    
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm-password').value;
    const role = document.getElementById('register-role').value;
    
    // Clear previous errors
    clearError('register-name');
    clearError('register-email');
    clearError('register-password');
    clearError('register-confirm-password');
    clearError('register-role');
    
    let hasError = false;
    
    // Validate name
    if (name.trim() === '') {
        showError('register-name', 'Please enter your full name');
        hasError = true;
    }
    
    // Validate email
    if (!isValidEmail(email)) {
        showError('register-email', 'Please enter a valid email address');
        hasError = true;
    }
    
    // Validate password
    if (!isValidPassword(password)) {
        showError('register-password', 'Password must be at least 8 characters');
        hasError = true;
    }
    
    // Validate password match
    if (!passwordsMatch(password, confirmPassword)) {
        showError('register-confirm-password', 'Passwords do not match');
        hasError = true;
    }
    
    // Validate role
    if (role === '') {
        showError('register-role', 'Please select your role');
        hasError = true;
    }
    
    // If no errors, register user
    if (!hasError) {
        // Check if email already exists
        const existingUser = testUsers.find(u => u.email === email);
        
        if (existingUser) {
            showError('register-email', 'This email is already registered');
        } else {
            // Registration successful - show success message
            showSuccess('register-success', `Welcome ${name}! Registration successful.`);
            
            // Add user to test users (in real app, this would save to database)
            testUsers.push({ email, password, name, role });
            console.log('New user registered:', { email, name, role });
            
            // Clear the form
            document.getElementById('register-form').reset();
            
            // Switch to login form after 2 seconds
            setTimeout(() => {
                showLoginForm();
                // Pre-fill the email in login form
                document.getElementById('login-email').value = email;
                showSuccess('login-success', `Registration successful! Please login with your new account.`);
            }, 2000);
        }
    }
}

// Handle login form
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    // Clear previous errors
    clearError('login-email');
    clearError('login-password');
    
    let hasError = false;
    
    // Validate email
    if (!isValidEmail(email)) {
        showError('login-email', 'Please enter a valid email address');
        hasError = true;
    }
    
    // Validate password
    if (!isValidPassword(password)) {
        showError('login-password', 'Password must be at least 8 characters');
        hasError = true;
    }
    
    // If no errors, check login
    if (!hasError) {
        // Check if user exists
        const user = testUsers.find(u => u.email === email && u.password === password);
        
        if (user) {
            // Login successful
            showSuccess('login-success', `Welcome back, ${user.name}!`);
            document.getElementById('login-form').reset();
            
            // In real application, you would redirect to dashboard here
            console.log('Login successful for:', user.name);
        } else {
            // Login failed
            showError('login-email', 'Invalid email or password');
            showError('login-password', 'Invalid email or password');
        }
    }
}

// Setup real-time validation for registration form
function setupRealTimeValidation() {
    // Email validation on blur
    document.getElementById('register-email').addEventListener('blur', function() {
        const email = this.value;
        if (email && !isValidEmail(email)) {
            showError('register-email', 'Please enter a valid email');
        } else {
            clearError('register-email');
        }
    });
    
    // Password validation on blur
    document.getElementById('register-password').addEventListener('blur', function() {
        const password = this.value;
        if (password && !isValidPassword(password)) {
            showError('register-password', 'Password must be at least 8 characters');
        } else {
            clearError('register-password');
        }
    });
    
    // Confirm password validation on blur
    document.getElementById('register-confirm-password').addEventListener('blur', function() {
        const confirmPassword = this.value;
        const password = document.getElementById('register-password').value;
        if (confirmPassword && !passwordsMatch(password, confirmPassword)) {
            showError('register-confirm-password', 'Passwords do not match');
        } else {
            clearError('register-confirm-password');
        }
    });
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Show register form by default
    showRegisterForm();
    
    // Add form submit handlers
    document.getElementById('register-form').addEventListener('submit', handleRegister);
    document.getElementById('login-form').addEventListener('submit', handleLogin);
    
    // Setup navigation links
    document.getElementById('show-login-link').addEventListener('click', function(e) {
        e.preventDefault();
        showLoginForm();
    });
    
    document.getElementById('show-register-link').addEventListener('click', function(e) {
        e.preventDefault();
        showRegisterForm();
    });
    
    // Setup real-time validation
    setupRealTimeValidation();
    
    console.log('Attendance System Ready!');
    console.log('Test accounts available:');
    console.log('- student@ashesi.edu.gh / password123');
    console.log('- faculty@ashesi.edu.gh / password123');
    console.log('- intern@ashesi.edu.gh / password123');
});