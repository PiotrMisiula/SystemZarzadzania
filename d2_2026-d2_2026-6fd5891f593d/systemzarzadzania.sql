-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260409.dbb116703b
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 01, 2026 at 11:10 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `systemzarzadzania`
--

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_polish_ci NOT NULL,
  `description` text COLLATE utf8mb4_polish_ci,
  `visibility` enum('public','private') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'public',
  `access_key` varchar(64) COLLATE utf8mb4_polish_ci DEFAULT NULL COMMENT 'Klucz dostępu – wymagany gdy visibility=private',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `project_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('admin','member','viewer') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'viewer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `project_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL COMMENT 'Użytkownik który utworzył zadanie',
  `title` varchar(200) COLLATE utf8mb4_polish_ci NOT NULL,
  `description` text COLLATE utf8mb4_polish_ci,
  `status` enum('todo','in_progress','completed') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'todo',
  `priority` enum('low','medium','high') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'medium',
  `deadline` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `backgroundColor` varchar(20) COLLATE utf8mb4_polish_ci NOT NULL,
  `start_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `created_by`, `title`, `description`, `status`, `priority`, `deadline`, `created_at`, `updated_at`, `backgroundColor`, `start_date`) VALUES
(2, NULL, 1, 'Spotkania z ziomkiem', '', 'todo', 'low', '2026-04-22 23:59:00', '2026-04-22 13:24:20', '2026-04-28 17:11:23', '#33ff0a', '2026-04-21 04:00:00'),
(3, NULL, 1, 'Spotkania z ziomkiem', '', 'todo', 'low', '2026-04-26 04:59:00', '2026-04-22 17:57:34', '2026-04-28 17:08:17', '#0a47ff', '2026-04-25 03:00:00'),
(5, NULL, 1, 'Spotkania ', '', 'todo', 'medium', '2026-04-29 11:00:00', '2026-04-24 16:24:06', '2026-05-01 10:57:09', '#09f162', '2026-04-29 09:00:00'),
(9, NULL, 1, 'Spotkania z ziomkiem', '', 'completed', 'high', '2026-04-25 09:00:00', '2026-04-27 18:09:58', '2026-04-28 17:09:11', '#ff6c0a', '2026-04-25 06:00:00'),
(16, NULL, 1, 'próba', '', 'completed', 'high', '2026-05-21 23:59:00', '2026-05-01 10:57:56', '2026-05-01 12:50:04', '#ffdd00', '2026-05-19 00:00:00'),
(18, NULL, 1, 'proba2', '', 'todo', 'medium', '2026-05-12 23:59:00', '2026-05-01 11:39:07', '2026-05-01 11:39:07', '#09fb39', '2026-05-12 00:00:00'),
(19, NULL, 1, 'safsadsd', 'asdfsdgsdf', 'todo', 'low', '2026-05-13 23:59:00', '2026-05-01 11:42:04', '2026-05-01 11:42:04', '#3b82f6', '2026-05-13 00:00:00'),
(20, NULL, 1, 'xcbzfg', 'sdfsgdsfg', 'todo', 'high', '2026-05-06 23:59:00', '2026-05-01 11:58:11', '2026-05-01 11:58:11', '#00fbff', '2026-05-06 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `task_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(32) COLLATE utf8mb4_polish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_polish_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_polish_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8mb4_polish_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `email`, `first_name`, `last_name`) VALUES
(1, 'PiotrM', '5817280a2398fc3779e880a3fedd1c589cb54efc1cd4ceffd84da177b377f421', 'losowy@email.com', 'Piotr', 'Misiula');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`),
  ADD KEY `priority` (`priority`),
  ADD KEY `deadline` (`deadline`),
  ADD KEY `fk_tasks_projects` (`project_id`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`task_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_projects` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
