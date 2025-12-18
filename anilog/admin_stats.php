<?php
// Admin Stats - View User Anime Statistics

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
require_admin();
$user = get_logged_in_user();

// Fetch stats
$stmt = $pdo->query("
    SELECT 
        a.anime_id,
        a.title,
        a.poster_image,
        COUNT(ua.user_anime_id) as total_users,
        SUM(CASE WHEN ua.watch_status = 'watching' THEN 1 ELSE 0 END) as watching,
        SUM(CASE WHEN ua.watch_status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN ua.watch_status = 'on-hold' THEN 1 ELSE 0 END) as on_hold,
        SUM(CASE WHEN ua.watch_status = 'dropped' THEN 1 ELSE 0 END) as dropped,
        SUM(CASE WHEN ua.watch_status = 'plan-to-watch' THEN 1 ELSE 0 END) as plan_to_watch,
        ROUND(AVG(ua.rating), 1) as avg_rating
    FROM anime a
    LEFT JOIN user_anime ua ON a.anime_id = ua.anime_id
    GROUP BY a.anime_id
    HAVING total_users > 0
    ORDER BY total_users DESC, a.title ASC
");
$stats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statistics - AniLog Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .stats-table th,
        .stats-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .stats-table th {
            background: var(--bg-secondary);
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .stats-table tr:hover {
            background: var(--bg-hover);
        }
        
        .stats-table td {
            color: var(--text-primary);
        }
        
        .thumb {
            width: 40px; 
            height: 60px; 
            border-radius: var(--radius-sm); 
            object-fit: cover;
            vertical-align: middle;
            margin-right: 1rem;
        }

        .stat-number {
            font-weight: 600;
        }
        
        .val-watching { color: var(--success); }
        .val-completed { color: var(--primary-light); }
        .val-hold { color: var(--warning); }
        .val-dropped { color: var(--error); }
        .val-plan { color: var(--text-muted); }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">AniLog</a>
            <ul class="navbar-nav">
                <li><a href="browse.php" class="nav-link">Browse</a></li>
                <li><a href="add_anime.php" class="nav-link">Add Anime</a></li>
                <li><a href="admin_stats.php" class="nav-link active">User Stats</a></li>
                <li><a href="auth/logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1>User Statistics</h1>
                    <p style="color: var(--text-muted);">Overview of user activity for each anime</p>
                </div>
            </div>
            
            <?php if (empty($stats)): ?>
                <div class="card empty-state">
                    <h3>No Data Available</h3>
                    <p>Users haven't added any anime to their lists yet.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Anime</th>
                                <th style="text-align: center;">Total Users</th>
                                <th style="text-align: center;">Avg Rating</th>
                                <th style="text-align: center;">Watching</th>
                                <th style="text-align: center;">Completed</th>
                                <th style="text-align: center;">Plan to Watch</th>
                                <th style="text-align: center;">On Hold</th>
                                <th style="text-align: center;">Dropped</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $row): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            <img src="<?php echo htmlspecialchars($row['poster_image']); ?>" class="thumb" onerror="this.src='https://via.placeholder.com/40x60'">
                                            <a href="view_anime.php?id=<?php echo $row['anime_id']; ?>" style="font-weight: 600; color: var(--text-primary); text-decoration: none;">
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td style="text-align: center;" class="stat-number">
                                        <?php echo $row['total_users']; ?>
                                    </td>
                                    <td style="text-align: center; color: var(--warning); font-weight: 700;">
                                        <?php echo $row['avg_rating'] ? $row['avg_rating'] : '-'; ?>
                                    </td>
                                    <td style="text-align: center;" class="val-watching stat-number">
                                        <?php echo $row['watching']; ?>
                                    </td>
                                    <td style="text-align: center;" class="val-completed stat-number">
                                        <?php echo $row['completed']; ?>
                                    </td>
                                    <td style="text-align: center;" class="val-plan stat-number">
                                        <?php echo $row['plan_to_watch']; ?>
                                    </td>
                                    <td style="text-align: center;" class="val-hold stat-number">
                                        <?php echo $row['on_hold']; ?>
                                    </td>
                                    <td style="text-align: center;" class="val-dropped stat-number">
                                        <?php echo $row['dropped']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
