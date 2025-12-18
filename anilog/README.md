# AniLog - Modern Anime Tracker 🎌

AniLog is a high-performance, full-stack anime collection tracker designed for enthusiasts who want to organize their library, monitor episode-by-episode progress, and engage in threaded community discussions.

##  Key Features

###  User Experience
- **Smart Dashboard**: At-a-glance analytics showing your watching habits, average ratings, and total consumption.
- **Custom Profiles**: Personalized profiles with **Profile Picture Uploads** and detailed genre distribution stats.
- **Admin Controls**: Dedicated administrative role for managing the global anime library and viewing system-wide user stats.

###  Community & Interaction
- **Nested Threaded Replies**: A sophisticated conversation system allowing users to reply to reviews and specific replies, creating deep conversation trees.
- **Rating & Review System**: Detailed feedback mechanics with 1-10 star ratings and written reviews.

###  Tracking Engine
- **Live Progress Bars**: Real-time visual tracking of your watch progress per anime.
- **Advanced Filtering**: Categorize your collection by: *Watching, Completed, On-Hold, Dropped,* or *Plan to Watch*.
- **Collapsible Search**: A modern, sleek search interface for quickly finding titles in your dashboard.

---

##  Technical Stack

- **Frontend**: Semantic HTML5, CSS3 (Modern Glassmorphism & Vanilla CSS), Vanilla JavaScript (ES6+).
- **Backend**: PHP 7.4+ (RESTful API architecture for CRUD operations).
- **Database**: MySQL 8.0+ (Optimized with Prepared Statements and Foreign Keys).
- **Validation**: Multi-layered validation using **Regular Expressions (Regex)** on both client and server sides.

---

## Installation & Local Setup

### 1. Project Deployment
Place the project folder in your local server directory (e.g., `C:\xampp\htdocs\anilog\`).

### 2. Database Initialization
1.  Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2.  Create a new database named `anilog`.
3.  Click the **Import** tab.
4.  Choose the file: `database/anilog_db.sql`.
5.  Click **Go**.

### 3. Connection Config
Ensure your credentials in `config/database.php` match your local setup:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'anilog');
define('DB_USER', 'root');
define('DB_PASS', ''); 
```

---

##  Deployment Guide (InfinityFree)

This project is optimized for deployment on free hosting services like **InfinityFree**.

### 1. Project Deployment
Upload all files directly from your `anilog` folder into the **`htdocs`** directory of your InfinityFree account via FTP or the Online File Manager.

### 2. Database Initialization
1.  Open your **InfinityFree Control Panel** and create a MySQL database.
2.  Open **phpMyAdmin** for that database.
3.  Click the **Import** tab and choose `database/anilog_db.sql`.
4.  Click **Go** to build the tables and sample data.

### 3. Connection Config
Update your `config/database.php` with your unique InfinityFree hosting credentials:
```php
define('DB_HOST', 'sql112.infinityfree.com');
define('DB_NAME', 'if0_40714664_anilog');
define('DB_USER', 'if0_40714664');
define('DB_PASS', 'wannie1551'); 
```
---

##  Project Architecture

```
anilog/
├── api/                # API Endpoints (CRUD, Profile, Replies)
├── auth/               # Login, Registration, Logout logic
├── config/             # DB Connection (PDO)
├── css/                # Main Stylesheet (Purple/Pink Dark Theme)
├── database/           # Master SQL Schema (anilog_db.sql)
├── images/             # Static UI assets
├── includes/           # Centralized Helper Functions
├── js/                 # Interaction (app.js) & Validation (validation.js)
└── uploads/            # User-uploaded Profile Pictures
```

## � Credentials
*   **Admin**: `admin` / `admin2025`
*   **User already registered**: `Sammy` / `Lidwan1551` (you can create your own account if you want)


---

**Web Technologies Final Project**  
*Built with ❤️ for the anime community.*
