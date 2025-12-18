<?php
// Handles profile picture upload and username changes

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

try {
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF images are allowed.');
        }
        
        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception('File size must be less than 10MB.');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
        $upload_path = '../uploads/profiles/' . $filename;
        
        // Delete old profile picture if exists
        $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $old_picture = $stmt->fetchColumn();
        if ($old_picture && file_exists('../' . $old_picture)) {
            unlink('../' . $old_picture);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload file.');
        }
        
        // Update database
        $db_path = 'uploads/profiles/' . $filename;
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$db_path, $user_id]);
        
        $_SESSION['profile_picture'] = $db_path;
        $response['success'] = true;
        $response['message'] = 'Profile picture updated successfully!';
        $response['profile_picture'] = $db_path;
    }
    
    // Handle username change
    if (isset($_POST['username']) && !empty($_POST['username'])) {
        $new_username = sanitize_input($_POST['username']);
        
        // Validate username
        $validation_error = validate_username_update($new_username, $user_id, $pdo);
        if ($validation_error) {
            throw new Exception($validation_error);
        }
        
        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->execute([$new_username, $user_id]);
        
        $_SESSION['username'] = $new_username;
        $response['success'] = true;
        $response['message'] = 'Username updated successfully!';
        $response['username'] = $new_username;
    }
    
    if (!$response['success']) {
        $response['message'] = 'No changes were made.';
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
