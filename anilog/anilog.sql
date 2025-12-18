-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 10:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anilog`
--

-- --------------------------------------------------------

--
-- Table structure for table `anime`
--

CREATE TABLE `anime` (
  `anime_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `total_episodes` int(11) NOT NULL,
  `poster_image` varchar(500) DEFAULT 'images/placeholders/default-anime.jpg',
  `release_season` varchar(50) DEFAULT NULL,
  `release_year` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anime`
--

INSERT INTO `anime` (`anime_id`, `title`, `description`, `total_episodes`, `poster_image`, `release_season`, `release_year`, `created_at`) VALUES
(1, 'Attack on Titan', 'Humanity fights for survival against giant humanoid Titans in a world surrounded by massive walls.', 75, 'https://cdn.myanimelist.net/images/anime/10/47347.jpg', 'Spring', 2013, '2025-12-18 17:47:36'),
(2, 'Demon Slayer', 'A young boy seeks revenge against demons who killed his family and turned his sister into one.', 44, 'https://cdn.myanimelist.net/images/anime/1286/99889.jpg', 'Spring', 2019, '2025-12-18 17:47:36'),
(3, 'My Hero Academia', 'In a world where superpowers are common, a powerless boy dreams of becoming the greatest hero.', 138, 'https://cdn.myanimelist.net/images/anime/10/78745.jpg', 'Spring', 2016, '2025-12-18 17:47:36'),
(4, 'One Punch Man', 'A hero who can defeat any opponent with a single punch searches for a worthy challenge.', 24, 'https://cdn.myanimelist.net/images/anime/12/76049.jpg', 'Fall', 2015, '2025-12-18 17:47:36'),
(5, 'Jujutsu Kaisen', 'A high school student joins a secret organization to fight deadly curses and save lives.', 47, 'https://cdn.myanimelist.net/images/anime/1171/109222.jpg', 'Fall', 2020, '2025-12-18 17:47:36'),
(6, 'Spy x Family', 'A spy, assassin, and telepath form an unconventional family for their secret missions.', 25, 'https://cdn.myanimelist.net/images/anime/1441/122795.jpg', 'Spring', 2022, '2025-12-18 17:47:36'),
(7, 'Chainsaw Man', 'A young man merges with his pet devil to become a devil hunter and pay off his debts.', 12, 'https://cdn.myanimelist.net/images/anime/1806/126216.jpg', 'Fall', 2022, '2025-12-18 17:47:36'),
(8, 'Frieren: Beyond Journey\'s End', 'An elf mage reflects on her adventure after the defeat of the demon king.', 28, 'https://cdn.myanimelist.net/images/anime/1015/138006.jpg', 'Fall', 2023, '2025-12-18 17:47:36'),
(9, 'Vinland Saga', 'A young Viking warrior seeks revenge while caught in the cycles of violence and war.', 48, 'https://cdn.myanimelist.net/images/anime/1500/103005.jpg', 'Summer', 2019, '2025-12-18 17:47:36'),
(10, 'Death Note', 'A high school student discovers a notebook that can kill anyone whose name is written in it.', 37, 'https://cdn.myanimelist.net/images/anime/9/9453.jpg', 'Fall', 2006, '2025-12-18 17:47:36'),
(11, 'Fullmetal Alchemist: Brotherhood', 'After a horrific alchemy experiment goes wrong in the Elric household, brothers Edward and Alphonse are left in a catastrophic new reality. Ignoring the alchemical principle banning human transmutation, the boys attempted to bring their recently deceased mother back to life. Instead, they suffered brutal personal loss: Alphonse&#039;s body disintegrated while Edward lost a leg and then sacrificed an arm to keep Alphonse&#039;s soul in the physical realm by binding it to a hulking suit of armor.\r\n\r\nThe brothers are rescued by their neighbor Pinako Rockbell and her granddaughter Winry. Known as a bio-mechanical engineering prodigy, Winry creates prosthetic limbs for Edward by utilizing &quot;automail,&quot; a tough, versatile metal used in robots and combat armor. After years of training, the Elric brothers set off on a quest to restore their bodies by locating the Philosopher&#039;s Stone—a powerful gem that allows an alchemist to defy the traditional laws of Equivalent Exchange.\r\n\r\nAs Edward becomes an infamous alchemist and gains the nickname &quot;Fullmetal,&quot; the boys&#039; journey embroils them in a growing conspiracy that threatens the fate of the world.', 64, 'https://cdn.myanimelist.net/images/anime/1208/94745.jpg', 'Summer', 2009, '2025-12-18 20:12:06'),
(12, 'Steins; Gate', 'Eccentric scientist Rintarou Okabe has a never-ending thirst for scientific exploration. Together with his ditzy but well-meaning friend Mayuri Shiina and his roommate Itaru Hashida, Okabe founds the Future Gadget Laboratory in the hopes of creating technological innovations that baffle the human psyche. Despite claims of grandeur, the only notable &quot;gadget&quot; the trio have created is a microwave that has the mystifying power to turn bananas into green goo.\r\n\r\nHowever, when Okabe attends a conference on time travel, he experiences a series of strange events that lead him to believe that there is more to the &quot;Phone Microwave&quot; gadget than meets the eye. Apparently able to send text messages into the past using the microwave, Okabe dabbles further with the &quot;time machine,&quot; attracting the ire and attention of the mysterious organization SERN.\r\n\r\nDue to the novel discovery, Okabe and his friends find themselves in an ever-present danger. As he works to mitigate the damage his invention has caused to the timeline, Okabe fights a battle to not only save his loved ones but also to preserve his degrading sanity.', 24, 'https://cdn.myanimelist.net/images/anime/1935/127974.jpg', 'Fall', 2009, '2025-12-18 20:15:01'),
(13, 'Naruto: Shippuden', 'It has been two and a half years since Naruto Uzumaki left Konohagakure, the Hidden Leaf Village, for intense training following events which fueled his desire to be stronger. Now Akatsuki, the mysterious organization of elite rogue ninja, is closing in on their grand plan which may threaten the safety of the entire shinobi world.\r\n\r\nAlthough Naruto is older and sinister events loom on the horizon, he has changed little in personality—still rambunctious and childish—though he is now far more confident and possesses an even greater determination to protect his friends and home. Come whatever may, Naruto will carry on with the fight for what is important to him, even at the expense of his own body, in the continuation of the saga about the boy who wishes to become Hokage.', 500, 'https://cdn.myanimelist.net/images/anime/1565/111305.jpg', 'Winter', 2007, '2025-12-18 20:18:04'),
(14, 'Cowboy Bebop', 'Crime is timeless. By the year 2071, humanity has expanded across the galaxy, filling the surface of other planets with settlements like those on Earth. These new societies are plagued by murder, drug use, and theft, and intergalactic outlaws are hunted by a growing number of tough bounty hunters.\r\n\r\nSpike Spiegel and Jet Black pursue criminals throughout space to make a humble living. Beneath his goofy and aloof demeanor, Spike is haunted by the weight of his violent past. Meanwhile, Jet manages his own troubled memories while taking care of Spike and the Bebop, their ship. The duo is joined by the beautiful con artist Faye Valentine, odd child Edward Wong Hau Pepelu Tivrusky IV, and Ein, a bioengineered Welsh corgi.\r\n\r\nWhile developing bonds and working to catch a colorful cast of criminals, the Bebop crew&#039;s lives are disrupted by a menace from Spike&#039;s past. As a rival&#039;s maniacal plot continues to unravel, Spike must choose between life with his newfound family or revenge for his old wounds.', 25, 'https://cdn.myanimelist.net/images/anime/4/19644.jpg', 'Spring', 1998, '2025-12-18 20:20:35'),
(15, 'Bleach', 'Ichigo Kurosaki is an ordinary high schooler—until his family is attacked by a Hollow, a corrupt spirit that seeks to devour human souls. It is then that he meets a Soul Reaper named Rukia Kuchiki, who gets injured while protecting Ichigo&#039;s family from the assailant. To save his family, Ichigo accepts Rukia&#039;s offer of taking her powers and becomes a Soul Reaper as a result.\r\n\r\nHowever, as Rukia is unable to regain her powers, Ichigo is given the daunting task of hunting down the Hollows that plague their town. However, he is not alone in his fight, as he is later joined by his friends—classmates Orihime Inoue, Yasutora Sado, and Uryuu Ishida—who each have their own unique abilities. As Ichigo and his comrades get used to their new duties and support each other on and off the battlefield, the young Soul Reaper soon learns that the Hollows are not the only real threat to the human world.', 366, 'https://cdn.myanimelist.net/images/anime/3/40451.jpg', 'Fall', 2004, '2025-12-18 20:23:14'),
(16, 'Tokyo Ghoul', 'Two years have passed since the CCG&#039;s raid on Anteiku. Although the atmosphere in Tokyo has changed drastically due to the increased influence of the CCG, ghouls continue to pose a problem as they have begun taking caution, especially the terrorist organization Aogiri Tree, who acknowledge the CCG&#039;s growing threat to their existence.\r\n\r\nThe creation of a special team, known as the Quinx Squad, may provide the CCG with the push they need to exterminate Tokyo&#039;s unwanted residents. As humans who have undergone surgery in order to make use of the special abilities of ghouls, they participate in operations to eradicate the dangerous creatures. The leader of this group, Haise Sasaki, is a half-ghoul, half-human who has been trained by famed special class investigator, Kishou Arima. However, there&#039;s more to this young man than meets the eye, as unknown memories claw at his mind, slowly reminding him of the person he used to be.', 48, 'https://cdn.myanimelist.net/images/anime/1063/95086.jpg', 'Spring', 2014, '2025-12-18 20:26:01'),
(17, 'One Piece', 'Barely surviving in a barrel after passing through a terrible whirlpool at sea, carefree Monkey D. Luffy ends up aboard a ship under attack by fearsome pirates. Despite being a naive-looking teenager, he is not to be underestimated. Unmatched in battle, Luffy is a pirate himself who resolutely pursues the coveted One Piece treasure and the King of the Pirates title that comes with it.\r\n\r\nThe late King of the Pirates, Gol D. Roger, stirred up the world before his death by disclosing the whereabouts of his hoard of riches and daring everyone to obtain it. Ever since then, countless powerful pirates have sailed dangerous seas for the prized One Piece only to never return. Although Luffy lacks a crew and a proper ship, he is endowed with a superhuman ability and an unbreakable spirit that make him not only a formidable adversary but also an inspiration to many.\r\n\r\nAs he faces numerous challenges with a big smile on his face, Luffy gathers one-of-a-kind companions to join him in his ambitious endeavor, together embracing perils and wonders on their once-in-a-lifetime adventure.', 1153, 'https://cdn.myanimelist.net/images/anime/1244/138851.jpg', 'Fall', 1999, '2025-12-18 20:28:11'),
(18, 'Kuroku no Basket', 'The &quot;Generation of Miracles&quot; were a legendary group of five basketball prodigies. After winning everything, they grew bored and split up for high school.\r\n\r\nAt Seirin High, newcomer Taiga Kagami teams up with Tetsuya Kuroko, a seemingly unskilled player who was actually the invisible &quot;Phantom Sixth Man&quot; of that legendary team. Together, they form a powerful partnership to take on the national championship, but must face off against Kuroko&#039;s former, now rival, teammates.', 75, 'https://cdn.myanimelist.net/images/anime/11/50453.jpg', 'Summer', 2012, '2025-12-18 20:30:49'),
(19, 'Beserk', 'Guts, a man who will one day be known as the Black Swordsman, is a young traveling mercenary characterized by the large greatsword he carries. He accepts jobs that offer the most money, but he never stays with one group for long—until he encounters the Band of the Falcon. Ambushed after completing a job, Guts crushes many of its members in combat. Griffith, The Band of the Falcon&#039;s leader and founder, takes an interest in Guts and duels him. While the others are no match for Guts, Griffith defeats him in one blow.\r\n\r\nIncapacitated and taken into the Band of the Falcon&#039;s camp to recover, Guts wakes up two days later. He confronts Griffith, and the two duel yet again, only this time with a condition: Guts will join the Band of the Falcon if he loses. Due to his fresh injuries, Guts loses the fight and is inducted by Griffith.\r\n\r\nIn three years&#039; time, Guts has become one of the Band of the Falcon&#039;s commanders. On the battlefield, his combat prowess is second only to Griffith as he takes on large groups of enemies all on his own. With Guts&#039; immense strength and Griffith&#039;s leadership, the Band of the Falcon dominate every battle they partake in. But something menacing lurks in the shadows, threatening to change Guts&#039; life forever.', 26, 'https://cdn.myanimelist.net/images/anime/1384/119988.jpg', 'Spring', 1998, '2025-12-18 20:34:13'),
(20, 'Black Clover', 'Asta and Yuno were abandoned at the same church on the same day. Raised together as children, they came to know of the &quot;Wizard King&quot;—a title given to the strongest mage in the kingdom—and promised that they would compete against each other for the position of the next Wizard King. However, as they grew up, the stark difference between them became evident. While Yuno is able to wield magic with amazing power and control, Asta cannot use magic at all and desperately tries to awaken his powers by training physically.\r\n\r\nWhen they reach the age of 15, Yuno is bestowed a spectacular Grimoire with a four-leaf clover, while Asta receives nothing. However, soon after, Yuno is attacked by a person named Lebuty, whose main purpose is to obtain Yuno&#039;s Grimoire. Asta tries to fight Lebuty, but he is outmatched. Though without hope and on the brink of defeat, he finds the strength to continue when he hears Yuno&#039;s voice. Unleashing his inner emotions in a rage, Asta receives a five-leaf clover Grimoire, a &quot;Black Clover&quot; giving him enough power to defeat Lebuty. A few days later, the two friends head out into the world, both seeking the same goal—to become the Wizard King!', 170, 'https://cdn.myanimelist.net/images/anime/2/88336.jpg', 'Spring', 2017, '2025-12-18 20:38:51');

-- --------------------------------------------------------

--
-- Table structure for table `anime_genres`
--

CREATE TABLE `anime_genres` (
  `anime_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anime_genres`
--

INSERT INTO `anime_genres` (`anime_id`, `genre_id`) VALUES
(1, 1),
(1, 4),
(1, 5),
(2, 1),
(2, 5),
(2, 13),
(3, 1),
(3, 3),
(4, 1),
(4, 3),
(5, 1),
(5, 13),
(6, 1),
(6, 3),
(7, 1),
(7, 6),
(8, 2),
(8, 4),
(8, 5),
(9, 1),
(9, 2),
(9, 4),
(10, 7),
(10, 8),
(10, 14),
(11, 1),
(11, 10),
(11, 13),
(12, 1),
(12, 2),
(12, 10),
(13, 1),
(13, 2),
(13, 9),
(14, 1),
(14, 2),
(14, 10),
(15, 1),
(15, 2),
(15, 13),
(16, 1),
(16, 4),
(16, 6),
(17, 1),
(17, 2),
(17, 3),
(18, 5),
(18, 11),
(18, 12),
(19, 1),
(19, 2),
(19, 5),
(20, 1),
(20, 5),
(20, 10);

-- --------------------------------------------------------

--
-- Table structure for table `anime_studios`
--

CREATE TABLE `anime_studios` (
  `anime_id` int(11) NOT NULL,
  `studio_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anime_studios`
--

INSERT INTO `anime_studios` (`anime_id`, `studio_id`) VALUES
(1, 3),
(2, 2),
(3, 4),
(4, 6),
(5, 1),
(6, 10),
(7, 1),
(8, 6),
(9, 3),
(10, 6);

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `genre_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `genre_name`) VALUES
(1, 'Action'),
(2, 'Adventure'),
(3, 'Comedy'),
(4, 'Drama'),
(5, 'Fantasy'),
(6, 'Horror'),
(7, 'Mystery'),
(8, 'Psychological'),
(9, 'Romance'),
(10, 'Sci-Fi'),
(11, 'Slice of Life'),
(12, 'Sports'),
(13, 'Supernatural'),
(14, 'Thriller');

-- --------------------------------------------------------

--
-- Table structure for table `review_replies`
--

CREATE TABLE `review_replies` (
  `reply_id` int(11) NOT NULL,
  `user_anime_id` int(11) NOT NULL,
  `parent_reply_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `review_replies`
--

INSERT INTO `review_replies` (`reply_id`, `user_anime_id`, `parent_reply_id`, `user_id`, `content`, `created_at`) VALUES
(3, 22, NULL, 5, 'Too cold!', '2025-12-18 21:09:46'),
(4, 22, 3, 3, 'You know best!!!', '2025-12-18 21:10:23');

-- --------------------------------------------------------

--
-- Table structure for table `studios`
--

CREATE TABLE `studios` (
  `studio_id` int(11) NOT NULL,
  `studio_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `studios`
--

INSERT INTO `studios` (`studio_id`, `studio_name`) VALUES
(5, 'A-1 Pictures'),
(4, 'Bones'),
(10, 'CloverWorks'),
(7, 'Kyoto Animation'),
(6, 'Madhouse'),
(1, 'Mappa'),
(8, 'Production I.G'),
(9, 'Trigger'),
(2, 'Ufotable'),
(3, 'Wit Studio');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `profile_picture`, `created_at`) VALUES
(1, 'admin', 'admin@anilog.com', '$2y$10$a.iLKL3MFFu9UxyICoE7Pua6j11/TMI3lxxIyetn2wjWlJuNCmKrC', 'admin', NULL, '2025-12-18 17:47:36'),
(3, 'Lid boy', 'lidwanabubakari11@gmail.com', '$2y$10$grU4q3DgssHX/l5gBD32Z.6CDI0qWfV4Ijw1NBI1mhbT6/TVwoMj2', 'user', 'uploads/profiles/profile_3_1766091162.jpg', '2025-12-18 18:05:58'),
(4, 'yemzy', 'yemzy@gmail.com', '$2y$10$m9xG4K/Dr7j4VnjhK3kSnOsULOyhO1Awb4VqL60F3qhuzZIleizvi', 'user', NULL, '2025-12-18 18:08:35'),
(5, 'Sammy', 'sam@gmail.com', '$2y$10$oshgzi4qH8Rxo4C.bLEoYO0yB1xKyCsXtQgX.I01/wRp6JFdWPs.O', 'user', 'uploads/profiles/profile_5_1766091056.jpeg', '2025-12-18 20:39:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_anime`
--

CREATE TABLE `user_anime` (
  `user_anime_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL,
  `watch_status` enum('watching','completed','on-hold','dropped','plan-to-watch') NOT NULL DEFAULT 'plan-to-watch',
  `current_episode` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 10),
  `review` text DEFAULT NULL,
  `started_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_anime`
--

INSERT INTO `user_anime` (`user_anime_id`, `user_id`, `anime_id`, `watch_status`, `current_episode`, `rating`, `review`, `started_date`, `completed_date`, `updated_at`) VALUES
(4, 3, 8, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 18:07:47'),
(5, 3, 7, 'dropped', 3, NULL, '', '2025-12-18', NULL, '2025-12-18 20:56:33'),
(6, 3, 6, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 18:07:55'),
(7, 3, 3, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 18:07:59'),
(8, 3, 4, 'on-hold', 4, NULL, '', '2025-12-18', NULL, '2025-12-18 20:55:53'),
(9, 4, 5, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 18:08:52'),
(10, 4, 2, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 18:08:53'),
(11, 4, 9, 'dropped', 17, NULL, '', '2025-12-18', NULL, '2025-12-18 20:59:27'),
(12, 4, 1, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 18:08:56'),
(13, 4, 10, 'on-hold', 12, NULL, '', '2025-12-18', NULL, '2025-12-18 20:59:09'),
(14, 5, 7, 'dropped', 6, NULL, '', '2025-12-18', NULL, '2025-12-18 20:45:53'),
(15, 5, 6, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 20:41:32'),
(16, 5, 5, 'on-hold', 20, NULL, '', '2025-12-18', NULL, '2025-12-18 20:46:29'),
(17, 5, 20, 'on-hold', 7, NULL, '', '2025-12-18', NULL, '2025-12-18 20:46:16'),
(18, 5, 3, 'watching', 6, NULL, '', '2025-12-18', NULL, '2025-12-18 20:45:32'),
(19, 5, 18, 'completed', 75, 9.5, 'Hardest sports anime ever!', '2025-12-18', '2025-12-18', '2025-12-18 20:45:04'),
(20, 5, 11, 'completed', 64, 8.8, 'Cool anime, might watch again next time.', '2025-12-18', '2025-12-18', '2025-12-18 20:43:51'),
(21, 3, 11, 'completed', 64, 8.8, 'The Elric brothers were too good! Loved this show.', '2025-12-18', '2025-12-18', '2025-12-18 20:55:17'),
(22, 3, 18, 'completed', 75, 8.5, 'COLD!!!', '2025-12-18', '2025-12-18', '2025-12-18 20:54:10'),
(23, 3, 15, 'watching', 50, NULL, '', '2025-12-18', NULL, '2025-12-18 20:55:35'),
(24, 4, 17, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 20:57:37'),
(25, 4, 19, 'watching', 6, NULL, '', '2025-12-18', NULL, '2025-12-18 20:58:48'),
(26, 4, 14, 'plan-to-watch', 0, NULL, NULL, NULL, NULL, '2025-12-18 20:57:39'),
(27, 4, 12, 'completed', 24, 8.0, 'This show was peak!', '2025-12-18', '2025-12-18', '2025-12-18 20:58:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anime`
--
ALTER TABLE `anime`
  ADD PRIMARY KEY (`anime_id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_season_year` (`release_season`,`release_year`);

--
-- Indexes for table `anime_genres`
--
ALTER TABLE `anime_genres`
  ADD PRIMARY KEY (`anime_id`,`genre_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Indexes for table `anime_studios`
--
ALTER TABLE `anime_studios`
  ADD PRIMARY KEY (`anime_id`,`studio_id`),
  ADD KEY `studio_id` (`studio_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `genre_name` (`genre_name`),
  ADD KEY `idx_genre_name` (`genre_name`);

--
-- Indexes for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `user_anime_id` (`user_anime_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_reply_id` (`parent_reply_id`);

--
-- Indexes for table `studios`
--
ALTER TABLE `studios`
  ADD PRIMARY KEY (`studio_id`),
  ADD UNIQUE KEY `studio_name` (`studio_name`),
  ADD KEY `idx_studio_name` (`studio_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_anime`
--
ALTER TABLE `user_anime`
  ADD PRIMARY KEY (`user_anime_id`),
  ADD UNIQUE KEY `unique_user_anime` (`user_id`,`anime_id`),
  ADD KEY `anime_id` (`anime_id`),
  ADD KEY `idx_user_status` (`user_id`,`watch_status`),
  ADD KEY `idx_rating` (`rating`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anime`
--
ALTER TABLE `anime`
  MODIFY `anime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `review_replies`
--
ALTER TABLE `review_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `studios`
--
ALTER TABLE `studios`
  MODIFY `studio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_anime`
--
ALTER TABLE `user_anime`
  MODIFY `user_anime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anime_genres`
--
ALTER TABLE `anime_genres`
  ADD CONSTRAINT `anime_genres_ibfk_1` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`anime_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anime_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE;

--
-- Constraints for table `anime_studios`
--
ALTER TABLE `anime_studios`
  ADD CONSTRAINT `anime_studios_ibfk_1` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`anime_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anime_studios_ibfk_2` FOREIGN KEY (`studio_id`) REFERENCES `studios` (`studio_id`) ON DELETE CASCADE;

--
-- Constraints for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD CONSTRAINT `review_replies_ibfk_1` FOREIGN KEY (`user_anime_id`) REFERENCES `user_anime` (`user_anime_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_replies_ibfk_3` FOREIGN KEY (`parent_reply_id`) REFERENCES `review_replies` (`reply_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_anime`
--
ALTER TABLE `user_anime`
  ADD CONSTRAINT `user_anime_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_anime_ibfk_2` FOREIGN KEY (`anime_id`) REFERENCES `anime` (`anime_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
