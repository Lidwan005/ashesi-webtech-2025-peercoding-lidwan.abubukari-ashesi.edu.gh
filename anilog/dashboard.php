<?php
// Dashboard
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
if (is_admin()) {
    redirect('browse.php');
}
$user = get_logged_in_user();

// Get user's anime list
$stmt = $pdo->prepare("
    SELECT 
        ua.*,
        a.title,
        a.poster_image,
        a.total_episodes,
        a.release_season,
        a.release_year
    FROM user_anime ua
    JOIN anime a ON ua.anime_id = a.anime_id
    WHERE ua.user_id = ?
    ORDER BY ua.updated_at DESC
");
$stmt->execute([$user['user_id']]);
$user_anime = $stmt->fetchAll();

// Get statistics (calculated directly to avoid MySQL View permission issues on some hosting)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AniLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">AniLog</a>
            <ul class="navbar-nav">
                <li><a href="dashboard.php" class="nav-link active">My Collection</a></li>
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
            <!-- Header -->
            <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem;">
                <?php if (!is_admin()): ?>
                    <div class="profile-avatar-wrapper">
                        <?php 
                        $profile_pic = get_profile_picture($user);
                        if ($profile_pic): 
                        ?>
                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                                 alt="Profile Picture"
                                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; box-shadow: var(--shadow-md);">
                        <?php else: ?>
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: white; box-shadow: var(--shadow-md);">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <button class="profile-edit-icon" onclick="openEditProfileModal()" title="Edit Profile">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 style="margin: 0;">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                    <p style="color: var(--text-muted); margin: 0;">Here's your anime collection</p>
                </div>
            </div>
            
            <!-- Statistics -->
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
                    <div class="stat-value"><?php echo $stats['plan_to_watch_count'] ?? 0; ?></div>
                    <div class="stat-label">Plan to Watch</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['average_rating'] ?? 0, 1); ?></div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
            
            <!-- Search -->
        
            <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
                <button id="toggleSearchBtn" class="btn btn-primary btn-sm" onclick="toggleSearch()" style="border-radius: 20px; padding: 0.5rem 1.5rem;">
                    Search
                </button>
            </div>
            
            <div id="searchContainer" class="card" style="margin-bottom: 2rem; display: none; animation: fadeIn 0.3s ease;">
                <div class="form-group" style="margin: 0;">
                    <label for="dashboardSearch">Search My Collection</label>
                    <input type="text" id="dashboardSearch" placeholder="Type to filter..." autofocus>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" data-tab="all" onclick="setFilterStatus('all')">All</div>
                <div class="tab" data-tab="watching" onclick="setFilterStatus('watching')">Watching</div>
                <div class="tab" data-tab="completed" onclick="setFilterStatus('completed')">Completed</div>
                <div class="tab" data-tab="plan-to-watch" onclick="setFilterStatus('plan-to-watch')">Plan to Watch</div>
                <div class="tab" data-tab="on-hold" onclick="setFilterStatus('on-hold')">On Hold</div>
                <div class="tab" data-tab="dropped" onclick="setFilterStatus('dropped')">Dropped</div>
            </div>
            
            <!-- Anime Grid -->
            <?php if (empty($user_anime)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">No Anime</div>
                    <h3>No anime in your collection yet</h3>
                    <p>Start by browsing anime and adding them to your list!</p>
                    <a href="browse.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Anime</a>
                </div>
            <?php else: ?>
                <div class="grid grid-5" id="animeGrid">
                    <?php foreach ($user_anime as $item): 
                        $progress = calculate_progress($item['current_episode'], $item['total_episodes']);
                    ?>
                        <div class="anime-card" data-status="<?php echo $item['watch_status']; ?>">
                            <img src="<?php echo htmlspecialchars($item['poster_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="anime-poster"
                                 onerror="this.src='https://via.placeholder.com/300x450/6366F1/FFFFFF?text=Anime'">
                            
                            <div class="anime-info">
                                <h3 class="anime-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                
                                <div class="anime-meta">
                                    <span><?php echo $item['release_season'] . ' ' . $item['release_year']; ?></span>
                                </div>
                                
                                <div style="margin: 1rem 0;">
                                    <span class="status-badge status-<?php echo $item['watch_status']; ?>">
                                        <?php echo get_status_display($item['watch_status']); ?>
                                    </span>
                                </div>
                                
                                <!-- Progress -->
                                <?php if ($item['watch_status'] !== 'plan-to-watch'): ?>
                                    <div class="progress-container">
                                        <div class="progress-label">
                                            <span>Progress</span>
                                            <span><?php echo $item['current_episode']; ?> / <?php echo $item['total_episodes']; ?></span>
                                        </div>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar" data-progress="<?php echo $progress; ?>" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Rating -->
                                <?php if ($item['rating']): ?>
                                    <div style="margin-top: 0.5rem;">
                                        <span style="color: var(--warning); font-weight: 700;">
                                            <?php echo number_format($item['rating'], 1); ?>/10
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 1rem;">
                                    <a href="anime_detail.php?id=<?php echo $item['user_anime_id']; ?>" class="btn btn-primary btn-sm btn-block">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <button class="modal-close" onclick="closeEditProfileModal()">&times;</button>
            </div>
            
            <div id="modalMessage" style="display: none; margin-bottom: 1rem;"></div>
            
            <!-- Profile Picture Upload -->
            <form id="modalProfilePictureForm" enctype="multipart/form-data" style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
                <h3 style="margin-bottom: 1rem;">Profile Picture</h3>
                <div class="form-group">
                    <label for="modal_profile_picture">UPLOAD NEW PICTURE</label>
                    <input type="file" id="modal_profile_picture" name="profile_picture" accept="image/*">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Accepted formats: JPG, PNG, GIF. Maximum size: 10MB
                    </small>
                </div>
                <div id="modalImagePreview" style="margin: 1rem 0; display: none;">
                    <img id="modalPreviewImg" src="" alt="Preview" style="max-width: 200px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
                </div>
                <button type="submit" class="btn btn-primary" id="modalUploadBtn">Upload Picture</button>
            </form>
            
            <!-- Username Change -->
            <form id="modalUsernameForm">
                <h3 style="margin-bottom: 1rem;">Change Username</h3>
                <div class="form-group">
                    <label for="modal_new_username">NEW USERNAME</label>
                    <input 
                        type="text" 
                        id="modal_new_username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($user['username']); ?>"
                        minlength="3"
                        maxlength="50"
                        required
                    >
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Minimum 3 characters, maximum 50 characters
                    </small>
                </div>
                <button type="submit" class="btn btn-primary" id="modalUsernameBtn">Update Username</button>
            </form>
        </div>
    </div>
    
    <script src="js/app.js"></script>
    <script>
        // Modal Functions
        function openEditProfileModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditProfileModal() {
            document.getElementById('editProfileModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target === modal) {
                closeEditProfileModal();
            }
        }
        
        // Profile Picture Preview
        document.getElementById('modal_profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('modalPreviewImg').src = e.target.result;
                    document.getElementById('modalImagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Profile Picture Upload
        document.getElementById('modalProfilePictureForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('modal_profile_picture');
            if (!fileInput.files[0]) {
                showModalMessage('Please select an image to upload', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('profile_picture', fileInput.files[0]);
            
            const uploadBtn = document.getElementById('modalUploadBtn');
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            
            try {
                const response = await fetch('api/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showModalMessage(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showModalMessage(data.message, 'error');
                }
            } catch (error) {
                showModalMessage('Upload failed. Please try again.', 'error');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload Picture';
            }
        });
        
        // Username Change
        document.getElementById('modalUsernameForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('modal_new_username').value.trim();
            if (username.length < 3) {
                showModalMessage('Username must be at least 3 characters', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('username', username);
            
            const usernameBtn = document.getElementById('modalUsernameBtn');
            usernameBtn.disabled = true;
            usernameBtn.textContent = 'Updating...';
            
            try {
                const response = await fetch('api/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showModalMessage(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showModalMessage(data.message, 'error');
                }
            } catch (error) {
                showModalMessage('Update failed. Please try again.', 'error');
            } finally {
                usernameBtn.disabled = false;
                usernameBtn.textContent = 'Update Username';
            }
        });
        
        function showModalMessage(message, type) {
            const messageDiv = document.getElementById('modalMessage');
            messageDiv.textContent = message;
            messageDiv.className = 'alert alert-' + type;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
        
        // Unified Filtering (Status + Search)
        let currentStatus = 'all';
        let searchQuery = '';
        
        function toggleSearch() {
            const container = document.getElementById('searchContainer');
            const btn = document.getElementById('toggleSearchBtn');
            const input = document.getElementById('dashboardSearch');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                btn.classList.add('active'); 
                btn.innerHTML = 'Close';
                input.focus();
            } else {
                container.style.display = 'none';
                btn.classList.remove('active');
                btn.innerHTML = 'Search';
                
            }
        }
        
        function setFilterStatus(status) {
            currentStatus = status;
            
            // Update active tab UI
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.toggle('active', tab.getAttribute('data-tab') === status);
            });
            
            applyFilters();
        }
        
        document.getElementById('dashboardSearch').addEventListener('input', function(e) {
            searchQuery = e.target.value.toLowerCase().trim();
            applyFilters();
        });
        
        function applyFilters() {
            const cards = document.querySelectorAll('.anime-card');
            
            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const title = card.querySelector('.anime-title').textContent.toLowerCase();
                
                const matchesStatus = (currentStatus === 'all' || cardStatus === currentStatus);
                const matchesSearch = (title.includes(searchQuery));
                
                if (matchesStatus && matchesSearch) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
