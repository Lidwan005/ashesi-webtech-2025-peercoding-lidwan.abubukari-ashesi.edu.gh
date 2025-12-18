<?php
// AniLog Homepage

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('browse.php');
    } else {
        redirect('dashboard.php');
    }
}

// Fetch some anime for the showcase
$stmt = $pdo->query("
    SELECT * FROM anime 
    ORDER BY total_episodes DESC 
    LIMIT 6
");
$showcase_anime = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniLog - Track Your Anime Journey</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        /* Landing Page Specific Overrides */
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            overflow-x: hidden;
        }

        /* Hero Section */
        .lp-hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: white;
            overflow: hidden;
        }

        .lp-hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.3), #0f172a), url('images/background.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(2px) brightness(0.7);
            z-index: 1;
        }

        .lp-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        @media (max-width: 968px) {
            .lp-content {
                grid-template-columns: 1fr;
                text-align: center;
                padding-top: 4rem;
            }
        }

        .lp-text h1 {
            font-size: 4rem;
            line-height: 1.1;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .lp-text p {
            font-size: 1.25rem;
            color: #cbd5e1;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .lp-buttons {
            display: flex;
            gap: 1rem;
        }

        @media (max-width: 968px) {
            .lp-buttons {
                justify-content: center;
            }
        }

        .btn-glow {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
            transition: all 0.3s ease;
        }

        .btn-glow:hover {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.7);
            transform: translateY(-2px);
        }

        /* Floating Cards Animation */
        .lp-visual {
            position: relative;
            height: 600px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        @media (max-width: 968px) {
            .lp-visual {
                height: 400px;
                display: none; /* Hides on mobile to save space */
            }
        }

        .anime-card-float {
            position: absolute;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            transition: all 0.5s ease;
            width: 200px;
        }
        
        .anime-card-float img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }

        .c1 { transform: translate(-120px, -50px) rotate(-10deg); z-index: 2; animation: float 6s ease-in-out infinite; }
        .c2 { transform: translate(120px, 40px) rotate(10deg); z-index: 2; animation: float 7s ease-in-out infinite 1s; }
        .c3 { transform: translate(0, 0) scale(1.1); z-index: 3; box-shadow: 0 30px 60px rgba(99, 102, 241, 0.3); }

        @keyframes float {
            0%, 100% { transform: translate(-120px, -50px) rotate(-10deg) translateY(0); }
            50% { transform: translate(-120px, -50px) rotate(-10deg) translateY(-20px); }
        }
        
        /* Features Section */
        .section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-box {
            background: rgba(30, 41, 59, 0.5);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-10px);
            background: rgba(30, 41, 59, 0.8);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        /* NavBar */
        .lp-nav {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 2rem;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lp-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .lp-auth-links a {
            color: #cbd5e1;
            text-decoration: none;
            margin-left: 2rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .lp-auth-links a:hover {
            color: white;
        }
        
        .lp-cta-sm {
            padding: 0.5rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .lp-cta-sm:hover {
            background: white;
            color: var(--bg-primary) !important;
        }

    </style>
</head>
<body>

    <!-- Nav -->
    <nav class="lp-nav">
        <a href="#" class="lp-logo">AniLog.</a>
        <div class="lp-auth-links">
            <a href="auth/login.php">Log In</a>
            <a href="auth/register.php" class="lp-cta-sm">Sign Up</a>
        </div>
    </nav>

    <!-- Hero -->
    <div class="lp-hero">
        <div class="lp-hero-bg"></div>
        
        <div class="lp-content">
            <div class="lp-text">
                <h1>Track Your Anime.<br>Discover New Worlds.</h1>
                <p>
                    Join the community of anime enthusiasts. Keep track of every episode, 
                    rate your favorites, and organize your collection in one beautiful place.
                </p>
                <div class="lp-buttons">
                    <a href="auth/register.php" class="btn btn-primary btn-lg btn-glow">Start Tracking - It's Free</a>
                    <a href="auth/login.php" class="btn btn-outline btn-lg" style="background: rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.2);">Log In</a>
                </div>
                
                <div style="margin-top: 3rem; display: flex; gap: 2rem;">
                    <div>
                        <h3 style="font-size: 2rem; margin: 0; color: white;">10k+</h3>
                        <span style="color: #94a3b8; font-size: 0.9rem;">Anime Listed</span>
                    </div>
                    <div>
                        <h3 style="font-size: 2rem; margin: 0; color: white;">Free</h3>
                        <span style="color: #94a3b8; font-size: 0.9rem;">Forever</span>
                    </div>
                </div>
            </div>
            
            <div class="lp-visual">
                <?php if (count($showcase_anime) >= 3): ?>
                    <div class="anime-card-float c1">
                        <img src="<?php echo htmlspecialchars($showcase_anime[1]['poster_image']); ?>" alt="Anime">
                    </div>
                    <div class="anime-card-float c2">
                        <img src="<?php echo htmlspecialchars($showcase_anime[2]['poster_image']); ?>" alt="Anime">
                    </div>
                    <div class="anime-card-float c3">
                        <img src="<?php echo htmlspecialchars($showcase_anime[0]['poster_image']); ?>" alt="Anime">
                    </div>
                <?php else: ?>
                    <!-- Fallback if database is empty -->
                    <div class="anime-card-float c3">
                        <img src="https://via.placeholder.com/200x300?text=AniLog" alt="Anime">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="section">
        <div class="section-title">
            <h2>Everything you need</h2>
            <p style="color: var(--text-muted);">Built by fans, for fans.</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon" style="background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); width: 60px; height: 60px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <h3>Track Progress</h3>
                <p style="color: var(--text-muted);">Never forget which episode you're on. Visual progress bars keep you updated.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon" style="background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); width: 60px; height: 60px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
                <h3>Rate & Review</h3>
                <p style="color: var(--text-muted);">Share your thoughts. Rate anime on a 10-point scale and write detailed reviews.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon" style="background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); width: 60px; height: 60px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h3>Collections</h3>
                <p style="color: var(--text-muted);">Organize into Watching, Completed, On Hold, and Dropped lists effortlessly.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon" style="background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); width: 60px; height: 60px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </div>
                <h3>Mobile Ready</h3>
                <p style="color: var(--text-muted);">Access your list anywhere, anytime. Optimized for every device screen.</p>
            </div>
        </div>
    </div>

</body>
</html>
