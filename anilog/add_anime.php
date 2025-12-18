<?php
// Add New Anime (Admin Only)
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

check_authentication();
require_admin();
$user = get_logged_in_user();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $total_episodes = intval($_POST['total_episodes'] ?? 0);
    $poster_image = sanitize_input($_POST['poster_image'] ?? '');
    $release_season = sanitize_input($_POST['release_season'] ?? '');
    $release_year = intval($_POST['release_year'] ?? 0);
    

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if ($total_episodes <= 0) {
        $errors[] = "Total episodes must be greater than 0";
    }
    
    if ($release_year < 1960 || $release_year > 2030) {
        $errors[] = "Release year must be between 1960 and 2030";
    }
    
    // Insert anime
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO anime (title, description, total_episodes, poster_image, release_season, release_year)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $description,
                $total_episodes,
                $poster_image ?: 'https://via.placeholder.com/300x450/6366F1/FFFFFF?text=Anime',
                $release_season,
                $release_year
            ]);
            
            $anime_id = $pdo->lastInsertId();

            // Handle genres
            if (isset($_POST['genres']) && is_array($_POST['genres'])) {
                $genre_stmt = $pdo->prepare("INSERT INTO anime_genres (anime_id, genre_id) VALUES (?, ?)");
                foreach ($_POST['genres'] as $genre_id) {
                    $genre_stmt->execute([$anime_id, intval($genre_id)]);
                }
            }

            $pdo->commit();
            
            set_flash('success', 'Anime added successfully!');
            redirect('browse.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Failed to add anime: " . $e->getMessage();
        }
    }
}

// Get available seasons
$seasons = ['Winter', 'Spring', 'Summer', 'Fall'];
$current_year = date('Y');

// Get all genres
$genres = $pdo->query("SELECT * FROM genres ORDER BY genre_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Anime - AniLog</title>
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
                <li><a href="add_anime.php" class="nav-link active">Add Anime</a></li>
                <li><a href="admin_stats.php" class="nav-link">User Stats</a></li>
                <li><a href="auth/logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="main-content">
            <h1>Add New Anime</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">
                Manually add an anime to the database
            </p>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Anime Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?php echo htmlspecialchars($title ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description"
                        ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label for="total_episodes">Total Episodes *</label>
                            <input 
                                type="number" 
                                id="total_episodes" 
                                name="total_episodes" 
                                value="<?php echo htmlspecialchars($total_episodes ?? ''); ?>"
                                min="1"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="release_season">Release Season</label>
                            <select id="release_season" name="release_season">
                                <option value="">Select Season</option>
                                <?php foreach ($seasons as $season): ?>
                                    <option value="<?php echo $season; ?>" <?php echo ($release_season ?? '') === $season ? 'selected' : ''; ?>>
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
                                value="<?php echo htmlspecialchars($release_year ?? $current_year); ?>"
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
                                        <?php echo (isset($_POST['genres']) && in_array($genre['genre_id'], $_POST['genres'])) ? 'checked' : ''; ?>
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
                            value="<?php echo htmlspecialchars($poster_image ?? ''); ?>"
                            placeholder="https://example.com/poster.jpg (leave blank for placeholder)"
                        >

                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Add Anime</button>
                        <a href="browse.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
