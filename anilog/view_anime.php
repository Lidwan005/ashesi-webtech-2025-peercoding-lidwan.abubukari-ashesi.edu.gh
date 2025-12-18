<?php
// View Anime (Read-only details page with community reviews)
 
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
$user = get_logged_in_user();

$anime_id = intval($_GET['id'] ?? 0);

if ($anime_id <= 0) {
    redirect('browse.php');
}

// Fetch anime details
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        GROUP_CONCAT(DISTINCT g.genre_name) as genres,
        GROUP_CONCAT(DISTINCT s.studio_name) as studios,
        (SELECT ROUND(AVG(rating), 1) FROM user_anime WHERE anime_id = a.anime_id AND rating IS NOT NULL) as avg_rating
    FROM anime a
    LEFT JOIN anime_genres ag ON a.anime_id = ag.anime_id
    LEFT JOIN genres g ON ag.genre_id = g.genre_id
    LEFT JOIN anime_studios asStudio ON a.anime_id = asStudio.anime_id
    LEFT JOIN studios s ON asStudio.studio_id = s.studio_id
    WHERE a.anime_id = ?
    GROUP BY a.anime_id
");
$stmt->execute([$anime_id]);
$anime = $stmt->fetch();

if (!$anime) {
    redirect('browse.php');
}

// Fetch reviews
$stmt = $pdo->prepare("
    SELECT 
        ua.user_anime_id,
        ua.rating,
        ua.review,
        ua.updated_at,
        u.user_id,
        u.username,
        u.profile_picture
    FROM user_anime ua
    JOIN users u ON ua.user_id = u.user_id
    WHERE ua.anime_id = ? 
    AND ua.review IS NOT NULL 
    AND ua.review != ''
    ORDER BY ua.updated_at DESC
");
$stmt->execute([$anime_id]);
$reviews = $stmt->fetchAll();

// Fetch replies for these reviews
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
    JOIN user_anime ua ON rr.user_anime_id = ua.user_anime_id
    WHERE ua.anime_id = ?
    ORDER BY rr.created_at ASC
");
$stmt->execute([$anime_id]);
$all_replies = $stmt->fetchAll();

// Group replies by review ID and parent reply ID
$replies_by_parent = [];
foreach ($all_replies as $reply) {
    $parent_id = $reply['parent_reply_id'] ?? 0;
    $replies_by_parent[$reply['user_anime_id']][$parent_id][] = $reply;
}

// Recursive function to render replies

function renderReplies($replies_by_parent, $review_id, $parent_id = 0, $level = 0) {
    if (!isset($replies_by_parent[$review_id][$parent_id])) return;

    foreach ($replies_by_parent[$review_id][$parent_id] as $reply) {
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
            <p style="color: var(--text-muted); margin: 0;"><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
            
            <div style="margin-top: 0.3rem; display: flex; gap: 0.75rem; align-items: center;">
                <button class="btn btn-link btn-xs" onclick="toggleReplyForm(<?php echo $review_id; ?>, <?php echo $reply_id; ?>, '<?php echo addslashes($reply['username']); ?>')" style="padding: 0; font-size: 0.75rem; color: var(--primary);">Reply</button>
                
                <?php if ($reply['user_id'] == $_SESSION['user_id'] || is_admin()): ?>
                    <button class="btn btn-link btn-xs" onclick="deleteReply(<?php echo $reply_id; ?>)" style="padding: 0; font-size: 0.75rem; color: var(--error);">Delete</button>
                <?php endif; ?>
            </div>

            <!-- Nested replies -->
            <?php renderReplies($replies_by_parent, $review_id, $reply_id, $level + 1); ?>
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
            <!-- Back Button -->
            <div style="margin-bottom: 2rem;">
                <a href="javascript:history.back()" class="btn btn-outline btn-sm">← Back</a>
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
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: center;">
                        <?php if ($anime['avg_rating']): ?>
                            <span style="color: var(--warning); font-weight: 700; font-size: 1.125rem;">
                                ★ <?php echo number_format($anime['avg_rating'], 1); ?>/10
                            </span>
                            <span style="color: var(--text-muted);">From users</span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-secondary);">Synopsis</h3>
                        <p style="color: var(--text-secondary); line-height: 1.8;">
                            <?php echo htmlspecialchars($anime['description']); ?>
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="card">
                            <strong style="color: var(--text-muted); font-size: 0.875rem;">Duration</strong>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                <?php echo $anime['total_episodes']; ?> eps
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
            
            <!-- Reviews Section -->
            <div style="margin-top: 4rem;">
                <h2 style="margin-bottom: 2rem;">Community Reviews</h2>
                
                <?php if (empty($reviews)): ?>
                    <div class="card empty-state">
                        <div class="empty-state-icon">📝</div>
                        <h3>No reviews yet</h3>
                        <p>Be the first to share your thoughts!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-2" id="reviewsGrid">
                        <?php foreach ($reviews as $review): 
                            $review_id = $review['user_anime_id'];
                            $replies = $replies_by_review[$review_id] ?? [];
                        ?>
                            <div class="card review-card" id="review-<?php echo $review_id; ?>">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-secondary); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                            <?php if ($review['profile_picture']): ?>
                                                <img src="<?php echo htmlspecialchars($review['profile_picture']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <span style="font-weight: bold; color: var(--primary);"><?php echo strtoupper(substr($review['username'], 0, 1)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;">
                                                <?php echo htmlspecialchars($review['username']); ?>
                                                <?php if ($review['user_id'] == $user['user_id']): ?>
                                                    <span style="font-size: 0.7rem; background: var(--primary); padding: 0.1rem 0.4rem; border-radius: 4px; vertical-align: middle; margin-left: 0.5rem;">YOU</span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('M j, Y', strtotime($review['updated_at'])); ?></div>
                                        </div>
                                    </div>
                                    <?php if ($review['rating']): ?>
                                        <div style="color: var(--warning); font-weight: 700;">
                                            ★ <?php echo $review['rating']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                                    <?php echo nl2br(htmlspecialchars($review['review'])); ?>
                                </p>

                                <!-- Replies List -->
                                <div class="replies-container" id="replies-<?php echo $review_id; ?>" style="margin-top: 1rem; padding-left: 1.5rem; border-left: 2px solid var(--border);">
                                    <?php renderReplies($replies_by_parent, $review_id); ?>
                                </div>

                                <!-- Reply Action -->
                                <div style="margin-top: 1rem;">
                                    <button class="btn btn-secondary btn-sm" onclick="toggleReplyForm(<?php echo $review_id; ?>)">Reply to Review</button>
                                </div>

                                <!-- Reply Form  -->
                                <div id="reply-form-<?php echo $review_id; ?>" style="display: none; margin-top: 1rem; background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius-md);">
                                    <form onsubmit="submitReply(event, <?php echo $review_id; ?>)">
                                        <input type="hidden" name="parent_reply_id" value="">
                                        <div id="replying-to-<?php echo $review_id; ?>" style="display: none; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                                            <span>Replying to <strong class="username"></strong></span>
                                            <button type="button" class="btn btn-link btn-xs" onclick="resetReplyForm(<?php echo $review_id; ?>)" style="padding: 0; font-size: 0.8rem; color: var(--error);">Cancel</button>
                                        </div>
                                        <textarea class="form-control" name="reply_content" rows="2" placeholder="Write a reply..." required style="margin-bottom: 0.5rem; font-size: 0.9rem;"></textarea>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleReplyForm(<?php echo $review_id; ?>)">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleReplyForm(reviewId, parentReplyId = null, username = null) {
            const formContainer = document.getElementById(`reply-form-${reviewId}`);
            const parentInput = formContainer.querySelector('input[name="parent_reply_id"]');
            const targetInfo = document.getElementById(`replying-to-${reviewId}`);
            
            // If it's the same parent, just toggle visibility
            if (parentInput.value == (parentReplyId || '')) {
                formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
            } else {
                // Otherwise, show the form and update the target
                formContainer.style.display = 'block';
            }
            
            if (parentReplyId) {
                parentInput.value = parentReplyId;
                targetInfo.style.display = 'flex';
                targetInfo.querySelector('.username').textContent = username;
            } else {
                parentInput.value = '';
                targetInfo.style.display = 'none';
            }
            
            if (formContainer.style.display === 'block') {
                formContainer.querySelector('textarea').focus();
            }
        }

        function resetReplyForm(reviewId) {
            const formContainer = document.getElementById(`reply-form-${reviewId}`);
            formContainer.querySelector('input[name="parent_reply_id"]').value = '';
            document.getElementById(`replying-to-${reviewId}`).style.display = 'none';
        }

        async function submitReply(event, reviewId) {
            event.preventDefault();
            const form = event.target;
            const content = form.reply_content.value.trim();
            const parentReplyId = form.parent_reply_id.value;
            const submitBtn = form.querySelector('button[type="submit"]');

            if (!content) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Posting...';

            const formData = new FormData();
            formData.append('user_anime_id', reviewId);
            formData.append('content', content);
            if (parentReplyId) {
                formData.append('parent_reply_id', parentReplyId);
            }

            try {
                const response = await fetch('api/add_reply.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error posting reply');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Post Reply';
                }
            } catch (error) {
                alert('Request failed. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post Reply';
            }
        }
    </script>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
