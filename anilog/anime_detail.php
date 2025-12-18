<?php
/**
 * Anime Detail Page - Update progress, rating, and review
 */
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
$user = get_logged_in_user();
$flash = get_flash();

// Get user_anime_id from URL
$user_anime_id = intval($_GET['id'] ?? 0);

// Fetch anime details
$stmt = $pdo->prepare("
    SELECT 
        ua.*,
        a.title,
        a.description,
        a.poster_image,
        a.total_episodes,
        a.release_season,
        a.release_year,
        GROUP_CONCAT(DISTINCT g.genre_name) as genres,
        GROUP_CONCAT(DISTINCT s.studio_name) as studios
    FROM user_anime ua
    JOIN anime a ON ua.anime_id = a.anime_id
    LEFT JOIN anime_genres ag ON a.anime_id = ag.anime_id
    LEFT JOIN genres g ON ag.genre_id = g.genre_id
    LEFT JOIN anime_studios asStudio ON a.anime_id = asStudio.anime_id
    LEFT JOIN studios s ON asStudio.studio_id = s.studio_id
    WHERE ua.user_anime_id = ? AND ua.user_id = ?
    GROUP BY ua.user_anime_id
");
$stmt->execute([$user_anime_id, $user['user_id']]);
$anime = $stmt->fetch();

if (!$anime) {
    redirect('dashboard.php');
}

$progress = calculate_progress($anime['current_episode'], $anime['total_episodes']);

// Fetch replies for this specific review
$stmt = $pdo->prepare("
    SELECT 
        rr.*,
        u.username,
        u.profile_picture,
        pu.username as parent_username
    FROM review_replies rr
    JOIN users u ON rr.user_id = u.user_id
    LEFT JOIN review_replies pr ON rr.parent_reply_id = pr.reply_id
    LEFT JOIN users pu ON pr.user_id = pu.user_id
    WHERE rr.user_anime_id = ?
    ORDER BY rr.created_at ASC
");
$stmt->execute([$user_anime_id]);
$all_replies = $stmt->fetchAll();

// Group replies by parent ID
$replies_by_parent = [];
foreach ($all_replies as $reply) {
    $parent_id = $reply['parent_reply_id'] ?? 0;
    $replies_by_parent[$parent_id][] = $reply;
}

// Recursive function to render replies

function renderDetailReplies($replies_by_parent, $parent_id = 0, $level = 0) {
    if (!isset($replies_by_parent[$parent_id])) return;

    foreach ($replies_by_parent[$parent_id] as $reply) {
        $reply_id = $reply['reply_id'];
        ?>
        <div class="reply-item" style="margin-top: 1rem; padding-left: <?php echo $level > 0 ? '1.5rem' : '0'; ?>; border-left: <?php echo $level > 0 ? '1px solid var(--border)' : 'none'; ?>; font-size: 0.9rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--bg-secondary); display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 0.7rem;">
                    <?php if ($reply['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($reply['profile_picture']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-weight: bold; color: var(--primary);"><?php echo strtoupper(substr($reply['username'], 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <span style="font-weight: 600;"><?php echo htmlspecialchars($reply['username']); ?></span>
                <?php if ($reply['parent_username']): ?>
                    <span style="color: var(--text-muted); font-size: 0.8rem;">→ <?php echo htmlspecialchars($reply['parent_username']); ?></span>
                <?php endif; ?>
                <span style="font-size: 0.7rem; color: var(--text-muted);"><?php echo date('M j, Y', strtotime($reply['created_at'])); ?></span>
            </div>
            <p style="color: var(--text-secondary); margin: 0;"><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
            
            <div style="margin-top: 0.3rem; display: flex; gap: 0.75rem; align-items: center;">
                <button class="btn btn-link btn-xs" onclick="toggleReplyForm(0, <?php echo $reply_id; ?>, '<?php echo addslashes($reply['username']); ?>')" style="padding: 0; font-size: 0.75rem; color: var(--primary);">Reply</button>
                
                <?php if ($reply['user_id'] == $_SESSION['user_id'] || is_admin()): ?>
                    <button class="btn btn-link btn-xs" onclick="deleteReply(<?php echo $reply_id; ?>)" style="padding: 0; font-size: 0.75rem; color: var(--error);">Delete</button>
                <?php endif; ?>
            </div>
            
            <!-- Recursive call for nested replies -->
            <?php renderDetailReplies($replies_by_parent, $reply_id, $level + 1); ?>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($anime['title']); ?> - AniLog</title>
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
                <li><a href="browse.php" class="nav-link">Browse</a></li>
                <?php if (!is_admin()): ?>
                    <li><a href="profile.php" class="nav-link">Profile</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li><a href="add_anime.php" class="nav-link">Add Anime</a></li>
                <?php endif; ?>
                <li><a href="auth/logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="main-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <p><?php echo htmlspecialchars($flash['message']); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Back Button -->
            <div style="margin-bottom: 2rem;">
                <a href="dashboard.php" class="btn btn-outline btn-sm">← Back to My Collection</a>
            </div>

            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem; margin-bottom: 2rem;">
                <!-- Poster -->
                <div>
                    <img src="<?php echo htmlspecialchars($anime['poster_image']); ?>" 
                         alt="<?php echo htmlspecialchars($anime['title']); ?>"
                         style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);"
                         onerror="this.src='https://via.placeholder.com/300x450/6366F1/FFFFFF?text=Anime'">
                </div>
                
                <!-- Info -->
                <div>
                    <h1><?php echo htmlspecialchars($anime['title']); ?></h1>
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                        <span class="status-badge status-<?php echo $anime['watch_status']; ?>">
                            <?php echo get_status_display($anime['watch_status']); ?>
                        </span>
                        <?php if ($anime['rating']): ?>
                            <span style="color: var(--warning); font-weight: 700; font-size: 1.125rem;">
                                <?php echo number_format($anime['rating'], 1); ?>/10
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <p style="color: var(--text-secondary); line-height: 1.8;">
                            <?php echo htmlspecialchars($anime['description']); ?>
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="card">
                            <strong style="color: var(--text-muted); font-size: 0.875rem;">Episodes</strong>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                <?php echo $anime['total_episodes']; ?>
                            </div>
                        </div>
                        <div class="card">
                            <strong style="color: var(--text-muted); font-size: 0.875rem;">Season</strong>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                <?php echo $anime['release_season'] . ' ' . $anime['release_year']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($anime['genres']): ?>
                        <div style="margin-bottom: 1rem;">
                            <strong>Genres:</strong> 
                            <span style="color: var(--text-secondary);"><?php echo htmlspecialchars($anime['genres']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($anime['studios']): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <strong>Studio:</strong> 
                            <span style="color: var(--text-secondary);"><?php echo htmlspecialchars($anime['studios']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Update Form -->
            <div class="card">
                <h2>Update Your Progress</h2>
                
                <form method="POST" action="api/user_anime_crud.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_anime_id" value="<?php echo $user_anime_id; ?>">
                    
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="watch_status">Watch Status</label>
                            <select name="watch_status" id="watch_status" required>
                                <option value="watching" <?php echo $anime['watch_status'] === 'watching' ? 'selected' : ''; ?>>Watching</option>
                                <option value="completed" <?php echo $anime['watch_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="on-hold" <?php echo $anime['watch_status'] === 'on-hold' ? 'selected' : ''; ?>>On Hold</option>
                                <option value="dropped" <?php echo $anime['watch_status'] === 'dropped' ? 'selected' : ''; ?>>Dropped</option>
                                <option value="plan-to-watch" <?php echo $anime['watch_status'] === 'plan-to-watch' ? 'selected' : ''; ?>>Plan to Watch</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_episode">Current Episode (0-<?php echo $anime['total_episodes']; ?>)</label>
                            <input 
                                type="number" 
                                name="current_episode" 
                                id="current_episode" 
                                value="<?php echo $anime['current_episode']; ?>"
                                min="0" 
                                max="<?php echo $anime['total_episodes']; ?>"
                                required
                            >
                            <span class="error-message" id="current_episode-error"></span>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Overall Progress</span>
                            <span><?php echo $anime['current_episode']; ?> / <?php echo $anime['total_episodes']; ?> (<?php echo $progress; ?>%)</span>
                        </div>
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar" data-progress="<?php echo $progress; ?>" style="width: 0%;"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rating">Rating (1-10)</label>
                        <input 
                            type="number" 
                            name="rating" 
                            id="rating" 
                            value="<?php echo $anime['rating'] ?? ''; ?>"
                            min="1" 
                            max="10"
                            step="0.1"
                            placeholder="Leave blank if not rated"
                        >
                        <span class="error-message" id="rating-error"></span>
                    </div>
                    
                        <textarea 
                            name="review" 
                            id="review" 
                            placeholder="Write your thoughts about this anime..."
                        ><?php echo htmlspecialchars($anime['review'] ?? ''); ?></textarea>
                    </div>

                    <!-- Replies to your review -->
                    <?php if (!empty($all_replies)): ?>
                        <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-md);">
                            <h3 style="margin-bottom: 1rem; font-size: 1rem;">Replies to your review</h3>
                            <div class="replies-list">
                                <?php renderDetailReplies($replies_by_parent); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
                
                <!-- Delete Button -->
                <form method="POST" action="api/user_anime_crud.php" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_anime_id" value="<?php echo $user_anime_id; ?>">
                    <button type="submit" class="btn btn-sm" style="background: var(--error);" onclick="return confirm('Remove this anime from your list?')">
                        Remove from List
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
