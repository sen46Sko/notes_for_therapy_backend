-- MySQL dump 10.13  Distrib 5.7.38, for Linux (x86_64)
--
-- Host: localhost    Database: first_project
-- ------------------------------------------------------
-- Server version	5.7.38-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bank_details`
--

DROP TABLE IF EXISTS `bank_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ifsc_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_details`
--

LOCK TABLES `bank_details` WRITE;
/*!40000 ALTER TABLE `bank_details` DISABLE KEYS */;
INSERT INTO `bank_details` VALUES (1,1,'daniel','8888888888','KJL251','2022-03-23 00:37:53','2022-03-23 01:06:08');
/*!40000 ALTER TABLE `bank_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drop_us_lines`
--

DROP TABLE IF EXISTS `drop_us_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drop_us_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drop_us_lines`
--

LOCK TABLES `drop_us_lines` WRITE;
/*!40000 ALTER TABLE `drop_us_lines` DISABLE KEYS */;
INSERT INTO `drop_us_lines` VALUES (1,'neeturaj','neetu@gmail.com','7687893','test','2022-06-17 01:34:50','2022-06-17 01:34:50');
/*!40000 ALTER TABLE `drop_us_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2022_03_23_052342_create_bank_details_table',2),(6,'2022_03_23_080930_add_code_expiry_to_users_table',3),(7,'2022_03_23_082054_add_refered_code_to_users_table',3),(8,'2022_05_03_123851_add_columns_to_users_table',4),(9,'2022_05_03_125413_add_paid_to_users_table',5),(12,'2022_06_17_055630_create_drop_us_lines_table',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES ('dev1@yopmail.com','$2y$10$B8C/TCIIUdA1yuSULRseUO8/zbdQvPcHy88nS6ZM.yRWtuFVRRVaW','2022-03-22 08:00:58'),('tehgvvvet@yopmail.com','$2y$10$WxqyhNclbXfyj6PH14NYlevIt4jT4nz0BgZL655sZTj6rClB1kcB6','2022-05-03 23:48:04'),('test@yopmail.com','$2y$10$KUAl8yoIDDy72/eTwbKKA.CvUMbrakUUaYNBL6ps3B9VBMl1OiW7G','2022-05-03 23:49:05');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:42:58','2022-05-08 20:42:58'),(2,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:44:46','2022-05-08 20:44:46'),(3,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:46:44','2022-05-08 20:46:44'),(4,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:48:50','2022-05-08 20:48:50'),(5,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:49:19','2022-05-08 20:49:19'),(6,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:49:43','2022-05-08 20:49:43'),(7,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:50:12','2022-05-08 20:50:12'),(8,14,6,'arun','7766','676778','7','7676767','2022-05-08 20:50:40','2022-05-08 20:50:40'),(9,14,6,'neetu','7766','676778','7','7676767','2022-05-08 20:52:18','2022-05-08 20:52:18'),(10,14,6,'neetu','7766','676778','7','7676767','2022-05-08 20:55:59','2022-05-08 20:55:59'),(11,14,6,'neetu','7766','676778','7','7676767','2022-05-08 21:07:08','2022-05-08 21:07:08'),(12,18,6,'neetu','7766','676778','7','7676767','2022-05-08 22:21:57','2022-05-08 22:21:57'),(13,18,6,'neetu','7766','676778','7','7676767','2022-05-08 22:22:44','2022-05-08 22:22:44'),(14,18,6,'neetu','7766','676778','7','7676767','2022-05-08 22:59:14','2022-05-08 22:59:14'),(15,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:00:47','2022-05-08 23:00:47'),(16,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:13:02','2022-05-08 23:13:02'),(17,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:14:45','2022-05-08 23:14:45'),(18,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:16:58','2022-05-08 23:16:58'),(19,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:18:26','2022-05-08 23:18:26'),(20,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:21:37','2022-05-08 23:21:37'),(21,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:22:19','2022-05-08 23:22:19'),(22,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:25:33','2022-05-08 23:25:33'),(23,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:26:19','2022-05-08 23:26:19'),(24,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:34:55','2022-05-08 23:34:55'),(25,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:35:13','2022-05-08 23:35:13'),(26,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:35:49','2022-05-08 23:35:49'),(27,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:39:35','2022-05-08 23:39:35'),(28,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:47:59','2022-05-08 23:47:59'),(29,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:48:13','2022-05-08 23:48:13'),(30,18,6,'neetu','7766','676778','7','7676767','2022-05-08 23:49:10','2022-05-08 23:49:10'),(31,32,6,'neetu','7766','676778','7','7676767','2022-05-10 00:29:01','2022-05-10 00:29:01'),(32,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:31:54','2022-05-10 20:31:54'),(33,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:33:46','2022-05-10 20:33:46'),(34,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:35:03','2022-05-10 20:35:03'),(35,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:38:19','2022-05-10 20:38:19'),(36,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:40:50','2022-05-10 20:40:50'),(37,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:41:07','2022-05-10 20:41:07'),(38,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:50:11','2022-05-10 20:50:11'),(39,34,6,NULL,'7766','676778','7','7676767','2022-05-10 20:53:03','2022-05-10 20:53:03'),(40,34,6,NULL,'7766','676778','7','7676767','2022-05-10 21:18:00','2022-05-10 21:18:00'),(41,34,6,NULL,'7766','676778','7','7676767','2022-05-10 22:23:53','2022-05-10 22:23:53'),(42,34,6,NULL,'7766','676778','7','7676767','2022-05-10 22:24:19','2022-05-10 22:24:19'),(43,34,6,NULL,'7766','676778','7','7676767','2022-05-10 22:27:18','2022-05-10 22:27:18'),(44,55,6,NULL,'7766','676778','7','7676767','2022-05-12 01:37:21','2022-05-12 01:37:21'),(45,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 01:38:35','2022-05-12 01:38:35'),(46,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 01:39:31','2022-05-12 01:39:31'),(47,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:18:41','2022-05-12 02:18:41'),(48,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:25:49','2022-05-12 02:25:49'),(49,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:26:51','2022-05-12 02:26:51'),(50,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:28:10','2022-05-12 02:28:10'),(51,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:28:29','2022-05-12 02:28:29'),(52,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:28:59','2022-05-12 02:28:59'),(53,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:29:19','2022-05-12 02:29:19'),(54,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:30:34','2022-05-12 02:30:34'),(55,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:31:27','2022-05-12 02:31:27'),(56,55,5,'neetuju','77668','67677','766','76767678','2022-05-12 02:32:07','2022-05-12 02:32:07'),(57,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 02:33:43','2022-05-12 02:33:43'),(58,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 19:55:32','2022-05-12 19:55:32'),(59,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 19:56:01','2022-05-12 19:56:01'),(60,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 19:58:56','2022-05-12 19:58:56'),(61,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 19:59:39','2022-05-12 19:59:39'),(62,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:01:11','2022-05-12 20:01:11'),(63,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:01:49','2022-05-12 20:01:49'),(64,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:04:02','2022-05-12 20:04:02'),(65,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:05:13','2022-05-12 20:05:13'),(66,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:05:39','2022-05-12 20:05:39'),(67,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:06:50','2022-05-12 20:06:50'),(68,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:08:15','2022-05-12 20:08:15'),(69,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:11:13','2022-05-12 20:11:13'),(70,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:12:44','2022-05-12 20:12:44'),(71,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:13:05','2022-05-12 20:13:05'),(72,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:15:17','2022-05-12 20:15:17'),(73,55,4,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:15:40','2022-05-12 20:15:40'),(74,55,22,'neetujhjk','77678','6768','78687','767676778','2022-05-12 20:57:23','2022-05-12 20:57:23');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mobile_number` bigint(20) NOT NULL,
  `code_expiry` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refered_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'dsdsd','dev1@yopmail.com','',NULL,'$2y$10$6mWxdyIWUBrRetJpnYwLN.sP.Tp3lNtZiadx3bn6Jrdd39LTIV67K',NULL,'2022-03-22 02:59:29','2022-05-04 00:30:15',9854245442,NULL,NULL,'10993858458548324234','google',NULL,NULL,NULL),(2,'dev','dev2@yopmail.com','',NULL,'$2y$10$WupxKsRue4CcdUWRBy/wiehKpSN0ToKxcvAwYwuHW..E63BQty.wC',NULL,'2022-03-22 04:15:50','2022-03-22 04:15:50',1234567895,NULL,NULL,'',NULL,NULL,NULL,NULL),(3,'devops','dev3@yopmail.com','',NULL,'$2y$10$hdgi1/kLOWif6.PXkfKDC.qZsAYklUCBJgvfie92GYpAUN6wlPKg.',NULL,'2022-03-23 02:22:32','2022-03-23 05:19:18',1234567895,NULL,NULL,'',NULL,NULL,NULL,NULL),(4,'admin','admin1@yopmail.com','',NULL,'$2y$10$EmLuQBCaCmvbstsGK60H8OM.dzCt3knH7LvmhUZY0snseRD9L65Vm',NULL,'2022-05-03 05:22:30','2022-05-03 05:22:30',74475867766,NULL,NULL,'',NULL,NULL,NULL,NULL),(5,'admin','test@yopmail.com','',NULL,'$2y$10$TA00L6HfoLno0FZMJtMoP.JYsguZsmiGu/gMHnQvPC2VgJ9GvCtSi',NULL,'2022-05-03 05:45:13','2022-05-03 05:45:13',74475867766,NULL,NULL,'',NULL,NULL,NULL,NULL),(6,'Developer','tessdst@yopmail.com','',NULL,'$2y$10$lTQBg3dhdrYzdbh7wOZvIuC4JkY8cpwH8A5arMbmQ6SSxm0LEokWa',NULL,'2022-05-03 08:44:25','2022-05-03 08:54:10',78687987998,NULL,NULL,'109930861523788',NULL,'ios','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUz',NULL),(7,'Developer','tehghgsdst@yopmail.com','',NULL,'$2y$10$JU0qfoTwdnh0Jck4D54lpO8NM28XCYr03w/NGSTDP4uNOWJulG1Zq',NULL,'2022-05-03 08:55:16','2022-05-03 08:57:15',78687987998,NULL,NULL,'10993086152378867',NULL,'ios','eyJ0eXAiOiJKVfhjg1QiLCJhbGciOiJIUz',NULL),(8,'Developer','tehghggjhcfdsevdsdst@yopmail.com','',NULL,'$2y$10$1HsvcE52hHr4LUC3nwCZ4ebiCcmr2q/l.N1b32.wnCqcfkQTD2wzO',NULL,'2022-05-03 08:57:29','2022-05-03 08:58:10',78687987998,NULL,NULL,'10993086152378867564365',NULL,'ios','eyJ0eXAiOiJKVfhjg1QiLCJhbGciOiJIUzjhfdhjh',NULL),(9,'Developer','tehget@yopmail.com','',NULL,'$2y$10$ErnkqjPed3HkvG.RbOeft.JkuPG/pG6lO8eNrRHauJ6h6Hc4DDGpO',NULL,'2022-05-03 08:58:29','2022-05-03 08:58:33',78687987998,NULL,NULL,'1099308615264365',NULL,'ios','eyJ0eXAiOiJKVfhjg1QiLCJhbGciOiJIUzjhfdhjh',NULL),(10,'Developer','tehgvvvet@yopmail.com','',NULL,'$2y$10$XNu3l45xgnIHjwKukXCy8uaiszuSjK3hRJTQJfy8uFPgCoYC4Fy3W',NULL,'2022-05-03 08:58:59','2022-05-03 23:43:33',78687987998,NULL,NULL,'1099308615264365768',NULL,'ios','eyJ0eXAiOiJKVfhjg1QiLCJhbGciOiJIUzjhfdhjh',NULL),(11,'Developer usedr','dev2342@yopmail.com','',NULL,'$2y$10$mJRpSeVFKQBtN3b7OClmQepAfQwIRg8hCO78WtGYLejcCiWVVyXwO',NULL,'2022-05-04 00:14:39','2022-05-04 00:28:43',78687987998,NULL,NULL,'10993858458548324234','google',NULL,NULL,NULL),(12,'Developerfhdhd','dev23423@yopmail.com','',NULL,'$2y$10$itPq6b/2.ccYt.jK0mHpQOIWh/U2hFbmyidY0iDUlWeVqxCuRHiJa',NULL,'2022-05-04 00:34:30','2022-05-04 00:34:51',78687987998,NULL,NULL,'109938584585487687868','google',NULL,NULL,NULL),(13,'admin123','admin12354@yopmail.com','',NULL,'$2y$10$Tf3qTsy0P4wCv8nEbcpIr.y2tFZCytpz/mGgv3hsPYhUuKZOXljjG',NULL,'2022-05-05 01:02:07','2022-05-05 01:02:07',744758677663,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(14,'testuhghuyjhhu','tesjkhjksdt3@yopmail.com','1651751758_about_img.jpg',NULL,'$2y$10$QiJ7zWff3TGSPb/4D.A9Cus/Vyohw1ty6HBUttzqSYCbcj/mAxcqK',NULL,'2022-05-05 01:03:05','2022-05-05 06:25:58',8475985487,NULL,NULL,'1099385845854876','dell','access','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1Ni','nbnvbnffvjjn'),(15,'admin123','admin154@yopmail.com','',NULL,'$2y$10$Ln3V91bUb1I8.gkSkSzyDu5WKIX9i4q2Gz/53Ty61f.DnCHgIv7TW',NULL,'2022-05-05 02:25:10','2022-05-05 02:25:10',744758677663,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-06-17 13:10:14
