<?php
//  Session Refresh Script
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

// Fetch fresh user data from database
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Update session
if ($user && $user['profile_picture']) {
    $_SESSION['profile_picture'] = $user['profile_picture'];
    echo "Session updated! Profile picture: " . $user['profile_picture'];
} else {
    echo "No profile picture found in database.";
}

echo "<br><br><a href='../dashboard.php'>Go to Dashboard</a>";
?>
