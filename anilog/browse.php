<?php

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
$user = get_logged_in_user();

// Get all anime
$stmt = $pdo->query("
    SELECT 
        a.*,
        GROUP_CONCAT(DISTINCT g.genre_name) as genres,
        GROUP_CONCAT(DISTINCT s.studio_name) as studios,
        (SELECT ROUND(AVG(rating), 1) FROM user_anime WHERE anime_id = a.anime_id AND rating IS NOT NULL) as avg_rating,
        ua.user_anime_id,
        ua.watch_status
    FROM anime a
    LEFT JOIN anime_genres ag ON a.anime_id = ag.anime_id
    LEFT JOIN genres g ON ag.genre_id = g.genre_id
    LEFT JOIN anime_studios asStudio ON a.anime_id = asStudio.anime_id
    LEFT JOIN studios s ON asStudio.studio_id = s.studio_id
    LEFT JOIN user_anime ua ON a.anime_id = ua.anime_id AND ua.user_id = {$user['user_id']}
    GROUP BY a.anime_id
    ORDER BY a.release_year DESC, a.title ASC
");
$anime_list = $stmt->fetchAll();

// Get all genres for filter
$genres = $pdo->query("SELECT * FROM genres ORDER BY genre_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Anime - AniLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">AniLog</a>
            <ul class="navbar-nav">
                <?php if (!is_admin()): ?>
                    <li><a href="dashboard.php" class="nav-link">My Collection</a></li>
                <?php endif; ?>
                <li><a href="browse.php" class="nav-link active">Browse</a></li>
                <?php if (!is_admin()): ?>
                    <li><a href="profile.php" class="nav-link">Profile</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li><a href="add_anime.php" class="nav-link">Add Anime</a></li>
                    <li><a href="admin_stats.php" class="nav-link">User Stats</a></li>
                <?php endif; ?>
                <li><a href="auth/logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="main-content">
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <h1>Browse Anime</h1>
                <p style="color: var(--text-muted);">Discover your next favorite series</p>
            </div>
            
            <!-- Search & Filters -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="form-group" style="margin: 0;">
                    <label for="searchInput">Search Anime</label>
                    <input type="text" id="searchInput" placeholder="Type to search...">
                </div>
            </div>
            
            <!-- Anime Grid -->
            <div class="grid grid-5">
                <?php foreach ($anime_list as $anime): ?>
                    <div class="anime-card">
                        <img src="<?php echo htmlspecialchars($anime['poster_image']); ?>" 
                             alt="<?php echo htmlspecialchars($anime['title']); ?>" 
                             class="anime-poster"
                             onerror="this.src='https://via.placeholder.com/300x450/6366F1/FFFFFF?text=Anime'">
                        
                        <div class="anime-info">
                            <h3 class="anime-title"><?php echo htmlspecialchars($anime['title']); ?></h3>
                            
                            <div class="anime-meta">
                                <span><?php echo $anime['total_episodes']; ?> eps</span>
                                <span>•</span>
                                <span><?php echo $anime['release_year']; ?></span>
                                <?php if ($anime['avg_rating']): ?>
                                    <span>•</span>
                                    <span style="color: #F59E0B; font-weight: bold;">★ <?php echo $anime['avg_rating']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($anime['genres']): ?>
                                <div style="margin: 0.5rem 0; font-size: 0.875rem; color: var(--text-muted);">
                                    <?php 
                                    $genres_arr = explode(',', $anime['genres']);
                                    echo implode(', ', array_slice($genres_arr, 0, 3)); 
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($anime['user_anime_id']): ?>
                                <div style="margin: 1rem 0;">
                                    <span class="status-badge status-<?php echo $anime['watch_status']; ?>">
                                        <?php echo get_status_display($anime['watch_status']); ?>
                                    </span>
                                </div>
                                <a href="view_anime.php?id=<?php echo $anime['anime_id']; ?>" class="btn btn-primary btn-sm btn-block">
                                    View Details
                                </a>
                            <?php elseif (!is_admin()): ?>
                                <form method="POST" action="api/user_anime_crud.php" style="margin-top: 1rem;" onsubmit="addToMyList(event, this)">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm btn-block">
                                        + Add to My List
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if (is_admin()): ?>
                                <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                    <a 
                                        href="edit_anime.php?id=<?php echo $anime['anime_id']; ?>" 
                                        class="btn btn-primary btn-sm"
                                        style="flex: 1;">
                                        Edit
                                    </a>
                                    <button 
                                        onclick="deleteAnime(<?php echo $anime['anime_id']; ?>, '<?php echo htmlspecialchars($anime['title'], ENT_QUOTES); ?>')" 
                                        class="btn btn-danger btn-sm"
                                        style="flex: 1;">
                                        Delete
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        async function deleteAnime(animeId, animeTitle) {
            if (!confirm(`Are you sure you want to delete "${animeTitle}"?\n\nThis will remove it from all users' collections and cannot be undone.`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('anime_id', animeId);
                
                const response = await fetch('api/delete_anime.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Failed to delete anime. Please try again.');
                console.error('Delete error:', error);
            }
        }
    </script>
</body>
</html>
