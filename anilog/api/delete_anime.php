<?php
// Deletes an anime from the database (Admin Only)
 
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication and admin role
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {

    // Get anime_id from POST
    if (!isset($_POST['anime_id']) || empty($_POST['anime_id'])) {
        throw new Exception('Anime ID is required');
    }
    
    $anime_id = intval($_POST['anime_id']);
    
    // Verify anime exists
    $stmt = $pdo->prepare("SELECT title FROM anime WHERE anime_id = ?");
    $stmt->execute([$anime_id]);
    $anime = $stmt->fetch();
    
    if (!$anime) {
        throw new Exception('Anime not found');
    }
    
    // Delete anime 
    $stmt = $pdo->prepare("DELETE FROM anime WHERE anime_id = ?");
    $stmt->execute([$anime_id]);
    
    $response['success'] = true;
    $response['message'] = 'Anime "' . $anime['title'] . '" has been deleted successfully';
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
