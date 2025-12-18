<?php
// Updates anime information in the database (Admin Only)

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
    // Get and validate input
    $anime_id = intval($_POST['anime_id'] ?? 0);
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $total_episodes = intval($_POST['total_episodes'] ?? 0);
    $poster_image = sanitize_input($_POST['poster_image'] ?? '');
    $release_season = sanitize_input($_POST['release_season'] ?? '');
    $release_year = intval($_POST['release_year'] ?? 0);
    
    // Validation
    if ($anime_id <= 0) {
        throw new Exception('Invalid anime ID');
    }
    
    if (empty($title)) {
        throw new Exception('Title is required');
    }
    
    if ($total_episodes <= 0) {
        throw new Exception('Total episodes must be greater than 0');
    }
    
    if ($release_year < 1960 || $release_year > 2030) {
        throw new Exception('Release year must be between 1960 and 2030');
    }
    
    // Verify anime exists
    $stmt = $pdo->prepare("SELECT anime_id, poster_image FROM anime WHERE anime_id = ?");
    $stmt->execute([$anime_id]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        throw new Exception('Anime not found');
    }
    
    // Keep existing poster if new one not provided
    if (empty($poster_image)) {
        $poster_image = $existing['poster_image'];
    }
    
    // Update anime
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE anime 
            SET title = ?, 
                description = ?, 
                total_episodes = ?, 
                poster_image = ?, 
                release_season = ?, 
                release_year = ?
            WHERE anime_id = ?
        ");
        
        $stmt->execute([
            $title,
            $description,
            $total_episodes,
            $poster_image,
            $release_season,
            $release_year,
            $anime_id
        ]);

        // Handle genres
        // First delete existing
        $stmt = $pdo->prepare("DELETE FROM anime_genres WHERE anime_id = ?");
        $stmt->execute([$anime_id]);

        // Then insert new ones
        if (isset($_POST['genres']) && is_array($_POST['genres'])) {
            $genre_stmt = $pdo->prepare("INSERT INTO anime_genres (anime_id, genre_id) VALUES (?, ?)");
            foreach ($_POST['genres'] as $genre_id) {
                $genre_stmt->execute([$anime_id, intval($genre_id)]);
            }
        }

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Anime "' . $title . '" has been updated successfully';


    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
    // Response handled inside try block or caught by exception handler
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
