<?php

// Sanitize user input

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email format using regex

function validate_email($email) {
    $pattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    return preg_match($pattern, $email);
}

// Validate rating (must be between 1 and 10)

function validate_rating($rating) {
    return is_numeric($rating) && $rating >= 1 && $rating <= 10;
}

// Validate episode number

function validate_episode($episode, $total_episodes) {
    return is_numeric($episode) && $episode >= 0 && $episode <= $total_episodes;
}

// Check if user is authenticated

function check_authentication() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth/login.php");
        exit();
    }
}

// Check if user is logged in (returns boolean)

function is_logged_in() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

// Get current user data
function get_logged_in_user() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'profile_picture' => $_SESSION['profile_picture'] ?? null
    ];
}

// Format date for display

function format_date($date) {
    if (!$date) return 'N/A';
    return date('M j, Y', strtotime($date));
}

// Calculate progress percentage

function calculate_progress($current, $total) {
    if ($total == 0) return 0;
    return round(($current / $total) * 100, 1);
}

// Generate random anime poster placeholder
function get_placeholder_image() {
    $colors = ['6366F1', 'EC4899', '14B8A6', 'F59E0B', '8B5CF6'];
    $color = $colors[array_rand($colors)];
    return "https://via.placeholder.com/300x450/" . $color . "/FFFFFF?text=Anime";
}

// Get watch status badge class

function get_status_class($status) {
    $classes = [
        'watching' => 'status-watching',
        'completed' => 'status-completed',
        'on-hold' => 'status-on-hold',
        'dropped' => 'status-dropped',
        'plan-to-watch' => 'status-plan'
    ];
    return $classes[$status] ?? 'status-default';
}

// Get watch status display name

function get_status_display($status) {
    $displays = [
        'watching' => 'Watching',
        'completed' => 'Completed',
        'on-hold' => 'On Hold',
        'dropped' => 'Dropped',
        'plan-to-watch' => 'Plan to Watch'
    ];
    return $displays[$status] ?? $status;
}

// Redirect helper

function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Set flash message
 
function set_flash($type, $message) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
 
function get_flash() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Check if current user is an admin

function is_admin() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require admin access - redirect if not admin

function require_admin() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth/login.php");
        exit();
    }
    
    if (!is_admin()) {
        set_flash('error', 'Access denied. Admin privileges required.');
        header("Location: dashboard.php");
        exit();
    }
}

// Get profile picture URL or return null for initials avatar
function get_profile_picture($user_data) {
    
    if (!empty($user_data['profile_picture'])) {
        return $user_data['profile_picture'];
    }
    return null; 
}

// Validate username for profile update
 
function validate_username_update($username, $current_user_id, $pdo) {
    // Check length
    if (strlen($username) < 3) {
        return "Username must be at least 3 characters";
    }
    
    if (strlen($username) > 50) {
        return "Username must not exceed 50 characters";
    }
    
    // Check if username is already taken by another user
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $current_user_id]);
    if ($stmt->fetch()) {
        return "Username is already taken";
    }
    
    return null; 
}
