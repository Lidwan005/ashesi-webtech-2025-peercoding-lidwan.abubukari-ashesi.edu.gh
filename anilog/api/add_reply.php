<?php
// Add Review Reply

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check authentication
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user = get_logged_in_user();
$user_anime_id = intval($_POST['user_anime_id'] ?? 0);
$parent_reply_id = isset($_POST['parent_reply_id']) ? intval($_POST['parent_reply_id']) : null;
$content = trim($_POST['content'] ?? '');

if ($user_anime_id <= 0 || empty($content)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    // Check if the review exists
    $stmt = $pdo->prepare("SELECT user_anime_id FROM user_anime WHERE user_anime_id = ?");
    $stmt->execute([$user_anime_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Review not found']);
        exit();
    }

    // If parent_reply_id is set, check if it exists and belongs to the same review
    if ($parent_reply_id !== null) {
        $stmt = $pdo->prepare("SELECT reply_id FROM review_replies WHERE reply_id = ? AND user_anime_id = ?");
        $stmt->execute([$parent_reply_id, $user_anime_id]);
        if (!$stmt->fetch()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Parent reply not found or mismatch']);
            exit();
        }
    }

    // Insert reply
    $stmt = $pdo->prepare("
        INSERT INTO review_replies (user_anime_id, parent_reply_id, user_id, content) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_anime_id, $parent_reply_id, $user['user_id'], $content]);
    $reply_id = $pdo->lastInsertId();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Reply added successfully',
        'reply' => [
            'reply_id' => $reply_id,
            'username' => $user['username'],
            'profile_picture' => $user['profile_picture'],
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to add reply: ' . $e->getMessage()]);
}
