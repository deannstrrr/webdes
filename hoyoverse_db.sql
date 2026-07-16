-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 05:46 PM
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
-- Database: `hoyoverse_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_username` varchar(255) NOT NULL,
  `following_username` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_username`, `following_username`, `created_at`) VALUES
(1, 'ophqnwfq', 'Malppe', '2026-07-16 11:40:44'),
(2, 'sample101', 'ophqnwfq', '2026-07-16 15:08:17'),
(3, 'ophqnwfq', 'sample101', '2026-07-16 15:09:31');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender`, `receiver`, `message`, `created_at`) VALUES
(1, 'ophqnwfq', 'mlop', '🙂🙂', '2026-07-16 05:29:38'),
(2, 'ophqnwfq', 'Malppe', ':DD', '2026-07-16 05:30:11'),
(3, 'Malppe', 'ophqnwfq', 'awd', '2026-07-16 05:30:47'),
(4, 'sample101', 'ophqnwfq', 'HALLOO😂😂😂😂', '2026-07-16 15:08:28'),
(5, 'ophqnwfq', 'sample101', 'WASGUDD', '2026-07-16 15:09:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` varchar(255) DEFAULT 'Welcome to my Gearbox profile page.',
  `avatar_path` varchar(255) DEFAULT '',
  `cover_path` varchar(255) DEFAULT '',
  `followers_count` int(11) DEFAULT 0,
  `custom_links` text DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `bio`, `avatar_path`, `cover_path`, `followers_count`, `custom_links`) VALUES
(2, 'ophqnwfq', 'c9deathwish@gmail.com', 'social_linked_facebook', '2026-07-15 15:50:41', 'APWODPAWODAWP', 'uploads/avatar_1784202664_6a58c5a8102a5.png', 'uploads/cover_1784202077_6a58c35d2d8d4.png', 0, '[{\"url\":\"https:\\/\\/www.youtube.com\\/\",\"title\":\"yt\"},{\"url\":\"https:\\/\\/www.riotgames.com\\/en\",\"title\":\"riot\"}]'),
(3, 'Malppe', 'sample@gmail.com', 'people', '2026-07-15 19:20:52', 'Welcome to my Gearbox profile page.', '', '', 3, '[]'),
(4, 'anothersample', 'anothersample@gmail.com', 'social_linked_facebook', '2026-07-16 05:01:06', 'Welcome to my Gearbox profile page.', '', '', 0, '[]'),
(7, 'hehe', 'hehe@gmail.com', '$2y$10$ToHRLkMmkzyGEBpnlGkfr.PeIfSLCvbQdw/xO73tTyNeIRinAb4Oe', '2026-07-16 05:13:09', 'Welcome to my Gearbox profile page.', '', '', 0, '[]'),
(8, 'mlop', 'mlop@gmail.com', '$2y$10$RB3AlGrbOjq0nVcGyKgLSOJTGSxRcvSTDL6/8dl.aDEIoXibgVeAi', '2026-07-16 05:14:34', 'Welcome to my Gearbox profile page.', '', '', 0, '[]'),
(9, 'sample101', 'sample101@gmail.com', '$2y$10$CwvbXjW2yaoawxhw/EX2Dep16yIzE9MbNvoVHTwqccd0OpSDQa72K', '2026-07-16 15:04:44', 'PAKYU', 'uploads/avatar_1784214355_6a58f353484b2.png', 'uploads/cover_1784214390_6a58f3767dc5b.png', 0, '[{\"url\":\"https:\\/\\/gearbox.com\",\"title\":\"gearbox.com\"},{\"url\":\"https:\\/\\/www.youtube.com\\/watch?v=KCwoCJzSnfI\",\"title\":\"eme\"}]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_username`,`following_username`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
