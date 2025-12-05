<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        // Redirect based on actual role
        if ($_SESSION['role'] === 'student') {
            header("Location: student_dashboard.php");
        } else {
            header("Location: faculty_dashboard.php");
        }
        exit();
    }
}
