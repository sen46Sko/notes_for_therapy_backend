-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 28, 2022 at 11:25 AM
-- Server version: 5.7.38-0ubuntu0.18.04.1
-- PHP Version: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bless`
--

-- --------------------------------------------------------

--
-- Table structure for table `blesses`
--

CREATE TABLE `blesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2022_07_26_110134_create_blesses_table', 1),
(4, '2014_10_12_100000_create_password_resets_table', 2),
(5, '2019_08_19_000000_create_failed_jobs_table', 2),
(6, '2022_07_27_121333_add_username_to_users_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('a@gmail.com', '$2y$10$r1Mt/HTDPQpgoa/.Cm8I6efo5PttT6Nn6fNAiRtnFvVd8qr8kJApS', '2022-07-27 04:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `username`, `age`, `image`) VALUES
(1, 'admin', 'a@gmail.com', NULL, '$2y$10$Qc1hG68tdRZ3dqyAPzs.s.Txx0BAYle5g6nCBqBf.BNA8LBCh.1yu', NULL, '2022-07-27 00:39:25', '2022-07-27 00:39:25', NULL, NULL, NULL),
(2, 'admin', 'ag@gmail.com', NULL, '$2y$10$fbaoDABscb3YnYrGsJ8hVOmGVnDvTErU1Ihcb0yg0WSEomHdL.Eo6', NULL, '2022-07-27 01:13:20', '2022-07-27 01:13:20', NULL, NULL, NULL),
(3, 'user', 'a12@gmail.com', NULL, '$2y$10$njkVE.EBzi7MX3czNF8hWOZAaMvrnrTmPRrtL/Bw2BCsmUKx2iyBa', NULL, '2022-07-27 04:09:47', '2022-07-27 04:09:47', NULL, NULL, NULL),
(4, 'admin', 'a3@gmail.com', NULL, '$2y$10$1AS8baulA9zQdjSukj1QQuGHJjJKJkOmbS56G0H50DwRy9e/eHrca', NULL, '2022-07-27 04:45:21', '2022-07-27 04:45:21', NULL, NULL, NULL),
(5, 'admin', 'username@gmail.com', NULL, '$2y$10$XVyzKs4TIjfjXdHq6ttIQe2/EQQi2ytiXG5Hwm6Xqm20IeetCqdIy', NULL, '2022-07-27 04:45:43', '2022-07-27 04:45:43', NULL, NULL, NULL),
(6, 'admin', 'username1@gmail.com', NULL, '$2y$10$bCJko8euc52g8i41EiQxnOJXy/sC4aj3nxaj49bsWLbWEsTjV3c1.', NULL, '2022-07-27 04:51:40', '2022-07-27 04:51:40', NULL, NULL, NULL),
(7, 'admin', 'username12@gmail.com', NULL, '$2y$10$Eh9SLogScZGlod/oxQ1XCO2LswrcHaW.L85ABBEEhLEw9XG.tVyN2', NULL, '2022-07-27 04:52:31', '2022-07-27 04:52:31', NULL, NULL, NULL),
(8, 'admin', 'username123@gmail.com', NULL, '$2y$10$ulIJUdXfRrqoxFNGQ3Q6bejwesx8Pp4rSgWP6NF3DCMiglXh5e/Ba', NULL, '2022-07-27 05:12:52', '2022-07-27 05:12:52', NULL, NULL, NULL),
(9, 'admin', 'username12321@gmail.com', NULL, '$2y$10$ifZzWi4h1RF5tQ5.YwtAT.ooacZ1DmiLg1Hnrc4KOMLWhKXTMHKBa', NULL, '2022-07-27 05:28:18', '2022-07-27 05:28:18', NULL, NULL, NULL),
(10, 'admin', 'username154@gmail.com', NULL, '$2y$10$9fa.KkLM1Jb3B1fWhghvtev6.3sGgibId1XdLi5Eh.a5zdox.96BK', NULL, '2022-07-27 05:38:35', '2022-07-27 05:38:35', NULL, NULL, NULL),
(11, 'admin', 'username1454@gmail.com', NULL, '$2y$10$oCroMavQY58RV13308E8y.aizHQ6q.llDwkB8M3p1YuBpax.Di3vu', NULL, '2022-07-27 05:39:56', '2022-07-27 05:39:56', NULL, NULL, NULL),
(12, 'admin', 'adsg@gmail.com', NULL, '$2y$10$6cYZLbCvQepY5lrAMZeKMO4AW0m8d/DxB1y.k8F7sj.50TxgSr7Eu', NULL, '2022-07-27 06:48:38', '2022-07-27 06:48:38', 'fdfdg', '12', NULL),
(13, 'admin', 'adfdgsg@gmail.com', NULL, '$2y$10$yEs7GdWMGIMzU48pU7Ti0e/TGKKpNzlJpKgcySvuBfw2IhEu6xK4.', NULL, '2022-07-27 06:50:03', '2022-07-27 06:50:03', 'fdfdg', '12', '/tmp/phpphBMZZ'),
(14, 'admin', 'username14874@gmail.com', NULL, '$2y$10$kJm1WroEpuGGX466iZ1LJ.BoBhxbIxs91JMdiO5EFzP3jnAU5KqzW', NULL, '2022-07-27 07:01:10', '2022-07-27 07:01:10', 'hiiiii', '22', '/tmp/phpXo54jV'),
(15, 'admin', 'adfdghgsg@gmail.com', NULL, '$2y$10$qBs5Xw5I2abfmCZJaHm.I.E28sl.YXEyksvbjmmTiSaekenTZVQ0y', NULL, '2022-07-27 07:12:07', '2022-07-27 07:12:07', 'fdfdg', '12', NULL),
(16, 'admin', 'username148554474@gmail.com', NULL, '$2y$10$B931ys5FbelSBs0r8OaCrOE4nQ1KpnvRKnv8SbGVE.99e2aTjG13K', NULL, '2022-07-27 07:12:18', '2022-07-27 07:12:18', 'hiiiii', '22', NULL),
(17, 'admin', 'adfdghghjhsg@gmail.com', NULL, '$2y$10$W43rOgvJw7mGwXsFgIUaGO6U855hbdLmM8trenC4NWn7bsBYQZI7q', NULL, '2022-07-27 07:12:26', '2022-07-27 07:12:26', 'fdfdg', '12', NULL),
(18, 'admin', 'username174@gmail.com', NULL, '$2y$10$ZEXypFwo51cX5z/PFinQmOsAGLGfk869cqgJC7mwxd0J7EBWBONAW', NULL, '2022-07-27 23:53:53', '2022-07-27 23:53:53', 'hiiiii', '22', NULL),
(19, 'admin', 'adfdghghjhfgdgfsg@gmail.com', NULL, '$2y$10$w/kzrK31DWEze4kQcsz0ceXx.22N/XLx/xz9vZ6/gnAyzVmIkBz.W', NULL, '2022-07-28 00:11:24', '2022-07-28 00:11:24', 'fdfdg', '12', '/tmp/php4NUPia'),
(20, 'admin', 'adfdghgdgvcvhjhfgdgfsg@gmail.com', NULL, '$2y$10$nBbPrUYjOLngRwWBCWd29e06XyKz0T1wIHtJTshYDEAEuuw35FW/u', NULL, '2022-07-28 00:12:57', '2022-07-28 00:12:57', 'fdfdg', '12', '1658986977_thaiFood.png'),
(21, 'admin', 'username1744@gmail.com', NULL, '$2y$10$gTWS.Iw9gad05QHn/sQl0uYU7Z4x9.dZZ4KqKbUURInBCiDFnqmy2', NULL, '2022-07-28 00:17:10', '2022-07-28 00:17:10', 'hiiiii', '22', '1658987230_Screen Shot 1401-05-06 at 10.56.21.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blesses`
--
ALTER TABLE `blesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blesses_email_unique` (`email`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blesses`
--
ALTER TABLE `blesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
