-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: tnpdb
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uq_role_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'student','B.Tech student seeking placement'),(2,'recruiter','Company representative posting jobs'),(3,'coordinator','Institute T&P coordinator — verifies students & recruiters'),(4,'admin','T&P admin — manages everything');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_types`
--

DROP TABLE IF EXISTS `job_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_types` (
  `job_type_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_type_id`),
  UNIQUE KEY `uq_job_type_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_types`
--

LOCK TABLES `job_types` WRITE;
/*!40000 ALTER TABLE `job_types` DISABLE KEYS */;
INSERT INTO `job_types` VALUES ('364d63ae-57e8-11f1-bf45-cc4740c7c70f','Full Time','full_time',1,'2026-05-25 03:17:08'),('364d6518-57e8-11f1-bf45-cc4740c7c70f','Internship','internship',1,'2026-05-25 03:17:08'),('364d6594-57e8-11f1-bf45-cc4740c7c70f','Contract','contract',1,'2026-05-25 03:17:08'),('364d65cc-57e8-11f1-bf45-cc4740c7c70f','Part Time','part_time',1,'2026-05-25 03:17:08');
/*!40000 ALTER TABLE `job_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `round_types`
--

DROP TABLE IF EXISTS `round_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `round_types` (
  `round_type_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`round_type_id`),
  UNIQUE KEY `uq_round_type_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `round_types`
--

LOCK TABLES `round_types` WRITE;
/*!40000 ALTER TABLE `round_types` DISABLE KEYS */;
INSERT INTO `round_types` VALUES ('364f2138-57e8-11f1-bf45-cc4740c7c70f','Aptitude Test','aptitude',1,'2026-05-25 03:17:08'),('364f2367-57e8-11f1-bf45-cc4740c7c70f','Technical Interview','technical',1,'2026-05-25 03:17:08'),('364f2449-57e8-11f1-bf45-cc4740c7c70f','HR Interview','hr',1,'2026-05-25 03:17:08'),('364f24df-57e8-11f1-bf45-cc4740c7c70f','Coding Test','coding',1,'2026-05-25 03:17:08'),('364f25b0-57e8-11f1-bf45-cc4740c7c70f','Group Discussion','group_discussion',1,'2026-05-25 03:17:08'),('364f2638-57e8-11f1-bf45-cc4740c7c70f','Psychometric Test','psychometric',1,'2026-05-25 03:17:08'),('364f26c3-57e8-11f1-bf45-cc4740c7c70f','Case Study','case_study',1,'2026-05-25 03:17:08'),('364f274d-57e8-11f1-bf45-cc4740c7c70f','Other','other',1,'2026-05-25 03:17:08');
/*!40000 ALTER TABLE `round_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `dept_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dept_id`),
  UNIQUE KEY `uq_dept_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES ('364a327f-57e8-11f1-bf45-cc4740c7c70f','Computer Science and Engineering','CSE',1,'2026-05-25 03:17:08'),('364a349a-57e8-11f1-bf45-cc4740c7c70f','Electronics and Communication Engineering','ECE',1,'2026-05-25 03:17:08'),('364a3562-57e8-11f1-bf45-cc4740c7c70f','Mechanical Engineering','ME',1,'2026-05-25 03:17:08'),('364a35dc-57e8-11f1-bf45-cc4740c7c70f','Civil Engineering','CE',1,'2026-05-25 03:17:08'),('364a364e-57e8-11f1-bf45-cc4740c7c70f','Electrical Engineering','EE',1,'2026-05-25 03:17:08'),('364a36c5-57e8-11f1-bf45-cc4740c7c70f','Information Technology','IT',1,'2026-05-25 03:17:08'),('364a373b-57e8-11f1-bf45-cc4740c7c70f','Chemical Engineering','CHE',1,'2026-05-25 03:17:08'),('364a37b1-57e8-11f1-bf45-cc4740c7c70f','Biotechnology','BT',1,'2026-05-25 03:17:08');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programs` (
  `program_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_years` tinyint NOT NULL DEFAULT '4',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`program_id`),
  UNIQUE KEY `uq_program_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES ('364bd306-57e8-11f1-bf45-cc4740c7c70f','Bachelor of Technology','BTECH',4,1,'2026-05-25 03:17:08');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-31 22:27:34
