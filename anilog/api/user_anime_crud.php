<?php
/* Handles adding anime to user's list, updating progress, 
ratings, and reviews (CRUD Operations)*/
 
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check authentication
if (!is_logged_in()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit();
    } else {
        redirect('../auth/login.php');
    }
}

$user = get_logged_in_user();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            // Add anime to user's list
            $anime_id = intval($_POST['anime_id'] ?? 0);
            $watch_status = $_POST['watch_status'] ?? 'plan-to-watch';
            $is_ajax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
            
            if ($anime_id > 0) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO user_anime (user_id, anime_id, watch_status) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$user['user_id'], $anime_id, $watch_status]);
                    $user_anime_id = $pdo->lastInsertId();
                    
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Anime added to your list!',
                            'user_anime_id' => $user_anime_id,
                            'anime_id' => $anime_id
                        ]);
                        exit();
                    }

                    set_flash('success', 'Anime added to your list!');
                    redirect('../dashboard.php');
                } catch (PDOException $e) {
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Failed to add anime']);
                        exit();
                    }

                    set_flash('error', 'Failed to add anime');
                    redirect('../browse.php');
                }
            }
            break;
            
        case 'update':
            // Update anime entry
            $user_anime_id = intval($_POST['user_anime_id'] ?? 0);
            $watch_status = $_POST['watch_status'] ?? '';
            $current_episode = intval($_POST['current_episode'] ?? 0);
            $rating = $_POST['rating'] ?? null;
            $review = $_POST['review'] ?? '';
            
            // Validate rating
            if ($rating !== null && $rating !== '') {
                if (!validate_rating($rating)) {
                    set_flash('error', 'Rating must be between 1 and 10');
                    redirect('../anime_detail.php?id=' . $user_anime_id);
                    exit();
                }
            } else {
                $rating = null;
            }
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE user_anime 
                    SET watch_status = ?,
                        current_episode = ?,
                        rating = ?,
                        review = ?,
                        completed_date = CASE WHEN watch_status = 'completed' AND ? = 'completed' THEN COALESCE(completed_date, CURDATE()) ELSE completed_date END,
                        started_date = CASE WHEN started_date IS NULL AND current_episode > 0 THEN CURDATE() ELSE started_date END
                    WHERE user_anime_id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $watch_status,
                    $current_episode,
                    $rating,
                    $review,
                    $watch_status,
                    $user_anime_id,
                    $user['user_id']
                ]);
                
                set_flash('success', 'Anime updated successfully!');
                redirect('../anime_detail.php?id=' . $user_anime_id);
            } catch (PDOException $e) {
                set_flash('error', 'Failed to update anime');
                redirect('../anime_detail.php?id=' . $user_anime_id);
            }
            break;
            
        case 'update_episode':
            // Quick episode update via AJAX
            $user_anime_id = intval($_POST['user_anime_id'] ?? 0);
            $current_episode = intval($_POST['current_episode'] ?? 0);
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE user_anime 
                    SET current_episode = ?,
                        started_date = CASE WHEN started_date IS NULL AND ? > 0 THEN CURDATE() ELSE started_date END
                    WHERE user_anime_id = ? AND user_id = ?
                ");
                $stmt->execute([$current_episode, $current_episode, $user_anime_id, $user['user_id']]);
                
                echo json_encode(['success' => true]);
                exit();
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit();
            }
            break;
            
        case 'delete':
            // Remove anime from user's list
            $user_anime_id = intval($_POST['user_anime_id'] ?? 0);
            
            try {
                $stmt = $pdo->prepare("
                    DELETE FROM user_anime 
                    WHERE user_anime_id = ? AND user_id = ?
                ");
                $stmt->execute([$user_anime_id, $user['user_id']]);
                
                set_flash('success', 'Anime removed from your list');
                redirect('../dashboard.php');
            } catch (PDOException $e) {
                set_flash('error', 'Failed to remove anime');
                redirect('../dashboard.php');
            }
            break;
    }
}
