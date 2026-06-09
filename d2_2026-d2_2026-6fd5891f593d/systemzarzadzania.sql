-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260417.26bcf2d71e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 08, 2026 at 12:37 PM
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
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci,
  `visibility` enum('public','private') CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'public',
  `access_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL COMMENT 'Klucz dostępu – wymagany gdy visibility=private',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `share_role` enum('admin','member','viewer','') CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `owner_id`, `name`, `description`, `visibility`, `access_key`, `created_at`, `updated_at`, `share_role`) VALUES
(1, 1, 'Nauka', '', 'private', '0c45b970b170b2f6ccaabf6f7fa81319', '2026-05-11 12:02:02', '2026-05-11 12:02:02', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `project_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('admin','member','viewer') CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'viewer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`project_id`, `user_id`, `role`) VALUES
(1, 1, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `project_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL COMMENT 'Użytkownik który utworzył zadanie',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci,
  `status` enum('todo','in_progress','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'todo',
  `priority` enum('low','medium','high') CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'medium',
  `deadline` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `backgroundColor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `start_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `created_by`, `title`, `description`, `status`, `priority`, `deadline`, `created_at`, `updated_at`, `backgroundColor`, `start_date`) VALUES
(2, NULL, 1, 'Spotkania z ziomkiem', '', 'todo', 'low', '2026-04-22 23:59:00', '2026-04-22 13:24:20', '2026-04-28 17:11:23', '#33ff0a', '2026-04-21 04:00:00'),
(3, NULL, 1, 'Spotkania z ziomkiem', '', 'todo', 'low', '2026-04-26 04:59:00', '2026-04-22 17:57:34', '2026-04-28 17:08:17', '#0a47ff', '2026-04-25 03:00:00'),
(5, NULL, 1, 'Spotkania ', '', 'todo', 'medium', '2026-04-29 11:00:00', '2026-04-24 16:24:06', '2026-05-01 10:57:09', '#09f162', '2026-04-29 09:00:00'),
(9, 1, 1, 'Spotkania z ziomkiem', '', 'completed', 'high', '2026-04-25 09:00:00', '2026-04-27 18:09:58', '2026-05-11 12:06:00', '#ff6c0a', '2026-04-25 06:00:00'),
(16, NULL, 1, 'próba', '', 'completed', 'high', '2026-05-20 23:59:00', '2026-05-01 10:57:56', '2026-05-04 13:25:09', '#ffdd00', '2026-05-18 00:00:00'),
(19, NULL, 1, 'cos', 'asdfa', 'in_progress', 'high', '2026-05-07 23:59:00', '2026-05-01 11:42:04', '2026-05-04 14:33:19', '#00b336', '2026-05-07 00:00:00'),
(21, NULL, 1, 'lubie placki', '', 'todo', 'low', '2026-05-14 16:14:00', '2026-05-11 12:14:22', '2026-05-18 12:12:15', '#00378f', '2026-05-14 12:12:00'),
(22, NULL, 2, 'Rower', '', 'todo', 'high', '2026-05-11 18:20:00', '2026-05-11 12:40:23', '2026-05-11 12:53:02', '#fbad04', '2026-05-11 16:45:00');

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
  `login` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `first_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `last_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `email`, `first_name`, `last_name`) VALUES
(1, 'PiotrekM', '5817280a2398fc3779e880a3fedd1c589cb54efc1cd4ceffd84da177b377f421', 'losowy@email.com', 'Piotr', 'Misiula'),
(2, 'JakubM', '76e3da50347c7df0056c7ae7b9276598be33a751d0877db453cf57c5ec2208cb', 'mickiewicz@gmail.com', 'Jakub', 'Mickiewicz');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
