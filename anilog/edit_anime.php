<?php
// Edit Anime (Admin Only)

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
require_admin();
$user = get_logged_in_user();

// Get anime_id from URL
$anime_id = intval($_GET['id'] ?? 0);

// Fetch anime data
$stmt = $pdo->prepare("SELECT * FROM anime WHERE anime_id = ?");
$stmt->execute([$anime_id]);
$anime = $stmt->fetch();

if (!$anime) {
    set_flash('error', 'Anime not found');
    redirect('browse.php');
}

// Get available seasons
$seasons = ['Winter', 'Spring', 'Summer', 'Fall'];
$current_year = date('Y');

// Get all genres
$genres = $pdo->query("SELECT * FROM genres ORDER BY genre_name")->fetchAll();

// Get current genres for this anime
$stmt = $pdo->prepare("SELECT genre_id FROM anime_genres WHERE anime_id = ?");
$stmt->execute([$anime_id]);
$current_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anime - AniLog</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .genre-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding: 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
        }
        
        .genre-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .genre-checkbox input {
            width: auto;
            margin: 0;
        }
    </style>
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
                <li><a href="add_anime.php" class="nav-link">Add Anime</a></li>
                <li><a href="admin_stats.php" class="nav-link">User Stats</a></li>
                <li><a href="auth/logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="main-content">
            <!-- Back Button -->
            <div style="margin-bottom: 2rem;">
                <a href="browse.php" class="btn btn-outline btn-sm">← Back to Browse</a>
            </div>

            <h1>Edit Anime</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">
                Update anime information
            </p>
            
            <div id="message" style="display: none; margin-bottom: 1rem;"></div>
            
            <div class="card">
                <form id="editAnimeForm">
                    <input type="hidden" name="anime_id" value="<?php echo $anime['anime_id']; ?>">
                    
                    <div class="form-group">
                        <label for="title">Anime Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?php echo htmlspecialchars($anime['title']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description"
                        ><?php echo htmlspecialchars($anime['description']); ?></textarea>
                    </div>
                    
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label for="total_episodes">Total Episodes *</label>
                            <input 
                                type="number" 
                                id="total_episodes" 
                                name="total_episodes" 
                                value="<?php echo $anime['total_episodes']; ?>"
                                min="1"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="release_season">Release Season</label>
                            <select id="release_season" name="release_season">
                                <option value="">Select Season</option>
                                <?php foreach ($seasons as $season): ?>
                                    <option value="<?php echo $season; ?>" <?php echo $anime['release_season'] === $season ? 'selected' : ''; ?>>
                                        <?php echo $season; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="release_year">Release Year *</label>
                            <input 
                                type="number" 
                                id="release_year" 
                                name="release_year" 
                                value="<?php echo $anime['release_year']; ?>"
                                min="1960"
                                max="2030"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Genres</label>
                        <div class="genre-grid">
                            <?php foreach ($genres as $genre): ?>
                                <label class="genre-checkbox">
                                    <input 
                                        type="checkbox" 
                                        name="genres[]" 
                                        value="<?php echo $genre['genre_id']; ?>"
                                        <?php echo in_array($genre['genre_id'], $current_genres) ? 'checked' : ''; ?>
                                    >
                                    <?php echo htmlspecialchars($genre['genre_name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="poster_image">Poster Image URL</label>
                        <input 
                            type="url" 
                            id="poster_image" 
                            name="poster_image" 
                            value="<?php echo htmlspecialchars($anime['poster_image']); ?>"
                            placeholder="https://example.com/poster.jpg"
                        >
                        <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                            Current image will be kept if left blank
                        </small>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Update Anime</button>
                        <a href="browse.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('editAnimeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            
            try {
                const response = await fetch('api/update_anime.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'browse.php';
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Update Anime';
                }
            } catch (error) {
                showMessage('Failed to update anime. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Anime';
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = 'alert alert-' + type;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
