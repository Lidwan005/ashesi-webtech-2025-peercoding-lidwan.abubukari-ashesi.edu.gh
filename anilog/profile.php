<?php
// User Profile & Statistics
 
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
if (is_admin()) {
    redirect('dashboard.php');
}
$user = get_logged_in_user();

// Get user statistics (calculated directly to avoid MySQL View permission issues)
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN watch_status = 'watching' THEN 1 END) as watching_count,
        COUNT(CASE WHEN watch_status = 'completed' THEN 1 END) as completed_count,
        COUNT(CASE WHEN watch_status = 'on-hold' THEN 1 END) as on_hold_count,
        COUNT(CASE WHEN watch_status = 'dropped' THEN 1 END) as dropped_count,
        COUNT(CASE WHEN watch_status = 'plan-to-watch' THEN 1 END) as plan_to_watch_count,
        ROUND(AVG(rating), 2) as average_rating,
        SUM(current_episode) as total_episodes_watched
    FROM user_anime
    WHERE user_id = ?
");
$stats_stmt->execute([$user['user_id']]);
$stats = $stats_stmt->fetch();

// Get genre breakdown
$genre_stmt = $pdo->prepare("
    SELECT g.genre_name, COUNT(*) as count
    FROM user_anime ua
    JOIN anime_genres ag ON ua.anime_id = ag.anime_id
    JOIN genres g ON ag.genre_id = g.genre_id
    WHERE ua.user_id = ?
    GROUP BY g.genre_name
    ORDER BY count DESC
    LIMIT 5
");
$genre_stmt->execute([$user['user_id']]);
$top_genres = $genre_stmt->fetchAll();

// Get recent activity
$recent_stmt = $pdo->prepare("
    SELECT 
        ua.*,
        a.title,
        a.poster_image
    FROM user_anime ua
    JOIN anime a ON ua.anime_id = a.anime_id
    WHERE ua.user_id = ?
    ORDER BY ua.updated_at DESC
    LIMIT 5
");
$recent_stmt->execute([$user['user_id']]);
$recent_activity = $recent_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - AniLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">AniLog</a>
            <ul class="navbar-nav">
                <li><a href="dashboard.php" class="nav-link">My Collection</a></li>
                <li><a href="browse.php" class="nav-link">Browse</a></li>
                <li><a href="profile.php" class="nav-link active">Profile</a></li>
                <li><a href="auth/logout.php" class="nav-link">Logout</a></li>
            </ul>
    </nav>
    
    <div class="container">
        <div class="main-content">
            <!-- Profile Header -->
            <div class="card" style="margin-bottom: 2rem; text-align: center;">
                <?php 
                $profile_pic = get_profile_picture($user);
                if ($profile_pic): 
                ?>
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                         alt="Profile Picture"
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; display: block; box-shadow: var(--shadow-lg);">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; color: white;">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p style="color: var(--text-muted); margin: 0;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <!-- Statistics Overview -->
            <h2 style="margin-bottom: 1.5rem;">Your Statistics</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['watching_count'] ?? 0; ?></div>
                    <div class="stat-label">Watching</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['completed_count'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['on_hold_count'] ?? 0; ?></div>
                    <div class="stat-label">On Hold</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['dropped_count'] ?? 0; ?></div>
                    <div class="stat-label">Dropped</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['plan_to_watch_count'] ?? 0; ?></div>
                    <div class="stat-label">Plan to Watch</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['average_rating'] ?? 0, 1); ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['total_episodes_watched'] ?? 0); ?></div>
                    <div class="stat-label">Episodes Watched</div>
                </div>
                <div class="stat-card">
                    <?php 
                    $total = ($stats['watching_count'] ?? 0) + ($stats['completed_count'] ?? 0) + 
                             ($stats['on_hold_count'] ?? 0) + ($stats['dropped_count'] ?? 0) + 
                             ($stats['plan_to_watch_count'] ?? 0);
                    ?>
                    <div class="stat-value"><?php echo $total; ?></div>
                    <div class="stat-label">Total Anime</div>
                </div>
            </div>
            
            <!-- Top Genres -->
            <?php if (!empty($top_genres)): ?>
                <div class="card" style="margin-top: 2rem;">
                    <h3>Top Genres</h3>
                    <div class="grid grid-2">
                        <?php foreach ($top_genres as $genre): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-secondary); border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                                <span><?php echo htmlspecialchars($genre['genre_name']); ?></span>
                                <span style="font-weight: 700; color: var(--primary);"><?php echo $genre['count']; ?> anime</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Recent Activity -->
            <?php if (!empty($recent_activity)): ?>
                <div class="card" style="margin-top: 2rem;">
                    <h3>Recent Activity</h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($recent_activity as $item): ?>
                            <div style="display: flex; gap: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-md);">
                                <img src="<?php echo htmlspecialchars($item['poster_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     style="width: 60px; height: 90px; object-fit: cover; border-radius: var(--radius-sm);"
                                     onerror="this.src='https://via.placeholder.com/60x90/6366F1/FFFFFF?text=?'">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($item['title']); ?></h4>
                                    <div style="display: flex; gap: 1rem; align-items: center;">
                                        <span class="status-badge status-<?php echo $item['watch_status']; ?>">
                                            <?php echo get_status_display($item['watch_status']); ?>
                                        </span>
                                        <span style="color: var(--text-muted); font-size: 0.875rem;">
                                            Updated <?php echo format_date($item['updated_at']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
