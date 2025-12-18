<?php
// Deletes a review reply (Owner or Admin Only)

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    // Get and validate input
    $reply_id = intval($_POST['reply_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $is_admin = is_admin();
    
    if ($reply_id <= 0) {
        throw new Exception('Invalid reply ID');
    }
    
    // Check if reply exists and get owner
    $stmt = $pdo->prepare("SELECT user_id FROM review_replies WHERE reply_id = ?");
    $stmt->execute([$reply_id]);
    $reply = $stmt->fetch();
    
    if (!$reply) {
        throw new Exception('Reply not found');
    }
    
    // Check permissions (Owner or Admin)
    if ($reply['user_id'] != $user_id && !$is_admin) {
        throw new Exception('Access denied. You can only delete your own replies.');
    }
    
    // Delete reply (Cascading will handle child replies due to DB constraint)
    $stmt = $pdo->prepare("DELETE FROM review_replies WHERE reply_id = ?");
    $stmt->execute([$reply_id]);
    
    $response['success'] = true;
    $response['message'] = 'Reply deleted successfully';
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
