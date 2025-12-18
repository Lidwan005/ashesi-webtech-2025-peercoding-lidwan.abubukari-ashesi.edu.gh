-- AniLog Consolidated Database Script
-- This file contains the full schema, sample data, and all recent updates.

-- 1. INITIAL SCHEMA & SAMPLE DATA
-- (From database.sql)

CREATE DATABASE IF NOT EXISTS anilog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE anilog;

-- Drop tables if they exist (for clean reinstall)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS review_replies;
DROP TABLE IF EXISTS anime_studios;
DROP TABLE IF EXISTS anime_genres;
DROP TABLE IF EXISTS user_anime;
DROP TABLE IF EXISTS studios;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS anime;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anime Table
CREATE TABLE anime (
    anime_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    total_episodes INT NOT NULL,
    poster_image VARCHAR(500) DEFAULT 'images/placeholders/default-anime.jpg',
    release_season VARCHAR(50),
    release_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_season_year (release_season, release_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Anime Tracking Table (Junction with tracking data)
CREATE TABLE user_anime (
    user_anime_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anime_id INT NOT NULL,
    watch_status ENUM('watching', 'completed', 'on-hold', 'dropped', 'plan-to-watch') NOT NULL DEFAULT 'plan-to-watch',
    current_episode INT DEFAULT 0,
    rating DECIMAL(3,1) DEFAULT NULL CHECK (rating >= 1 AND rating <= 10),
    review TEXT,
    started_date DATE,
    completed_date DATE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (anime_id) REFERENCES anime(anime_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_anime (user_id, anime_id),
    INDEX idx_user_status (user_id, watch_status),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Genres Table
CREATE TABLE genres (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_genre_name (genre_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anime Genres Junction Table
CREATE TABLE anime_genres (
    anime_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (anime_id, genre_id),
    FOREIGN KEY (anime_id) REFERENCES anime(anime_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Studios Table
CREATE TABLE studios (
    studio_id INT AUTO_INCREMENT PRIMARY KEY,
    studio_name VARCHAR(100) NOT NULL UNIQUE,
    INDEX idx_studio_name (studio_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anime Studios Junction Table
CREATE TABLE anime_studios (
    anime_id INT NOT NULL,
    studio_id INT NOT NULL,
    PRIMARY KEY (anime_id, studio_id),
    FOREIGN KEY (anime_id) REFERENCES anime(anime_id) ON DELETE CASCADE,
    FOREIGN KEY (studio_id) REFERENCES studios(studio_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Genres
INSERT INTO genres (genre_name) VALUES
('Action'), ('Adventure'), ('Comedy'), ('Drama'), ('Fantasy'), 
('Horror'), ('Mystery'), ('Psychological'), ('Romance'), ('Sci-Fi'), 
('Slice of Life'), ('Sports'), ('Supernatural'), ('Thriller');

-- Insert Sample Studios
INSERT INTO studios (studio_name) VALUES
('Mappa'), ('Ufotable'), ('Wit Studio'), ('Bones'), ('A-1 Pictures'), 
('Madhouse'), ('Kyoto Animation'), ('Production I.G'), ('Trigger'), ('CloverWorks');

-- Insert Sample Anime
INSERT INTO anime (title, description, total_episodes, poster_image, release_season, release_year) VALUES
('Attack on Titan', 'Humanity fights for survival against giant humanoid Titans in a world surrounded by massive walls.', 75, 'https://cdn.myanimelist.net/images/anime/10/47347.jpg', 'Spring', 2013),
('Demon Slayer', 'A young boy seeks revenge against demons who killed his family and turned his sister into one.', 44, 'https://cdn.myanimelist.net/images/anime/1286/99889.jpg', 'Spring', 2019),
('My Hero Academia', 'In a world where superpowers are common, a powerless boy dreams of becoming the greatest hero.', 138, 'https://cdn.myanimelist.net/images/anime/10/78745.jpg', 'Spring', 2016),
('One Punch Man', 'A hero who can defeat any opponent with a single punch searches for a worthy challenge.', 24, 'https://cdn.myanimelist.net/images/anime/12/76049.jpg', 'Fall', 2015),
('Jujutsu Kaisen', 'A high school student joins a secret organization to fight deadly curses and save lives.', 47, 'https://cdn.myanimelist.net/images/anime/1171/109222.jpg', 'Fall', 2020),
('Spy x Family', 'A spy, assassin, and telepath form an unconventional family for their secret missions.', 25, 'https://cdn.myanimelist.net/images/anime/1441/122795.jpg', 'Spring', 2022),
('Chainsaw Man', 'A young man merges with his pet devil to become a devil hunter and pay off his debts.', 12, 'https://cdn.myanimelist.net/images/anime/1806/126216.jpg', 'Fall', 2022),
('Frieren: Beyond Journey''s End', 'An elf mage reflects on her adventure after the defeat of the demon king.', 28, 'https://cdn.myanimelist.net/images/anime/1015/138006.jpg', 'Fall', 2023),
('Vinland Saga', 'A young Viking warrior seeks revenge while caught in the cycles of violence and war.', 48, 'https://cdn.myanimelist.net/images/anime/1500/103005.jpg', 'Summer', 2019),
('Death Note', 'A high school student discovers a notebook that can kill anyone whose name is written in it.', 37, 'https://cdn.myanimelist.net/images/anime/9/9453.jpg', 'Fall', 2006);

-- Link anime to genres
INSERT INTO anime_genres (anime_id, genre_id) VALUES
(1, 1), (1, 4), (1, 5), (2, 1), (2, 5), (2, 13), (3, 1), (3, 3), (4, 1), (4, 3),
(5, 1), (5, 13), (6, 1), (6, 3), (7, 1), (7, 6), (8, 2), (8, 4), (8, 5), (9, 1),
(9, 2), (9, 4), (10, 7), (10, 8), (10, 14);

-- Link anime to studios
INSERT INTO anime_studios (anime_id, studio_id) VALUES
(1, 3), (2, 2), (3, 4), (4, 6), (5, 1), (6, 10), (7, 1), (8, 6), (9, 3), (10, 6);

-- Create admin user (password: admin2025)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@anilog.com', '$2y$10$a.iLKL3MFFu9UxyICoE7Pua6j11/TMI3lxxIyetn2wjWlJuNCmKrC', 'admin');

-- Create demo user (password: demo123)
INSERT INTO users (username, email, password_hash, role) VALUES
('demo_user', 'demo@anilog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Add some demo tracking data for the demo user
INSERT INTO user_anime (user_id, anime_id, watch_status, current_episode, rating, review, started_date, completed_date) VALUES
(1, 2, 'watching', 15, NULL, NULL, '2024-12-01', NULL),
(1, 4, 'completed', 24, 9.5, 'Amazing action and comedy!', '2024-11-01', '2024-11-15'),
(1, 6, 'completed', 25, 8.0, 'Heartwarming family dynamics!', '2024-10-01', '2024-10-20');

-- (Note: Views are removed for compatibility with free hosting providers like InfinityFree)

-- 2. REVIEW REPLIES (With Nesting Support)
-- (From add_review_replies.sql & update_review_replies_parent.sql)

CREATE TABLE IF NOT EXISTS review_replies (
    reply_id INT AUTO_INCREMENT PRIMARY KEY,
    user_anime_id INT NOT NULL,
    parent_reply_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_anime_id) REFERENCES user_anime(user_anime_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_reply_id) REFERENCES review_replies(reply_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
