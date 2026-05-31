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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `admin_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_level` enum('admin','super_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement_reads`
--

DROP TABLE IF EXISTS `announcement_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcement_reads` (
  `read_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `announcement_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`read_id`),
  UNIQUE KEY `uq_ann_user` (`announcement_id`,`user_id`),
  KEY `fk_read_user` (`user_id`),
  CONSTRAINT `fk_read_ann` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`announcement_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_read_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcement_targets`
--

DROP TABLE IF EXISTS `announcement_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcement_targets` (
  `target_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `announcement_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`target_id`),
  KEY `idx_target_announcement` (`announcement_id`),
  KEY `idx_target_type_value` (`target_type`,`target_value`),
  CONSTRAINT `fk_target_announcement` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`announcement_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `announcement_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `posted_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posted_by_role` enum('admin','coordinator') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments_json` json DEFAULT NULL,
  `priority` enum('normal','important','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `job_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visible_to_roles_json` json NOT NULL,
  `publish_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcement_id`),
  KEY `idx_ann_posted_by` (`posted_by`),
  KEY `idx_ann_status` (`status`,`publish_at`),
  KEY `idx_ann_job` (`job_id`),
  CONSTRAINT `fk_ann_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ann_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `application_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `student_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eligibility_snapshot` json NOT NULL,
  `is_shortlisted` tinyint(1) NOT NULL DEFAULT '0',
  `shortlisted_at` datetime DEFAULT NULL,
  `shortlisted_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resume_visible_to_recruiter` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('applied','shortlisted','in_process','selected','not_selected','withdrawn','offer_accepted','offer_declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'applied',
  `withdrawn_at` datetime DEFAULT NULL,
  `withdrawal_reason` enum('got_other_offer','personal','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer_accepted_at` datetime DEFAULT NULL,
  `coordinator_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `uq_application` (`student_id`,`job_id`),
  KEY `fk_app_shortlisted_by` (`shortlisted_by`),
  KEY `idx_app_student` (`student_id`),
  KEY `idx_app_job` (`job_id`),
  KEY `idx_app_status` (`status`),
  KEY `idx_app_shortlist` (`job_id`,`is_shortlisted`),
  CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_app_shortlisted_by` FOREIGN KEY (`shortlisted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_before_application_insert` BEFORE INSERT ON `applications` FOR EACH ROW BEGIN
    DECLARE v_verif_status  VARCHAR(30);
    DECLARE v_placement     VARCHAR(30);
    DECLARE v_job_status    VARCHAR(30);
    DECLARE v_apply_end     DATETIME;
    DECLARE v_apply_start   DATETIME;
    DECLARE v_max_participants INT UNSIGNED;
    DECLARE v_applications_count INT UNSIGNED;

    
    SELECT status INTO v_verif_status
    FROM verifications
    WHERE entity_id = NEW.student_id AND entity_type = 'student';

    IF v_verif_status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student profile must be verified before applying.';
    END IF;

    
    SELECT placement_status INTO v_placement
    FROM students WHERE student_id = NEW.student_id;

    IF v_placement = 'placed' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student is already placed and cannot apply to new jobs.';
    END IF;

    
    SELECT job_status, apply_start, apply_end, max_participants, applications_count
    INTO v_job_status, v_apply_start, v_apply_end, v_max_participants, v_applications_count
    FROM jobs WHERE job_id = NEW.job_id;

    IF v_job_status <> 'opened' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This job is not currently open for applications.';
    END IF;

    
    IF v_max_participants IS NOT NULL AND v_applications_count >= v_max_participants THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This job posting has reached its maximum applicant capacity.';
    END IF;

    
    IF v_apply_start IS NOT NULL AND NOW() < v_apply_start THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Application window has not opened yet.';
    END IF;

    IF v_apply_end IS NOT NULL AND NOW() > v_apply_end THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Application deadline has passed.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_application_insert` AFTER INSERT ON `applications` FOR EACH ROW BEGIN
    UPDATE jobs
    SET applications_count = applications_count + 1
    WHERE job_id = NEW.job_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_application_withdraw` AFTER UPDATE ON `applications` FOR EACH ROW BEGIN
    IF OLD.status <> 'withdrawn' AND NEW.status = 'withdrawn' THEN
        UPDATE jobs
        SET applications_count = GREATEST(0, applications_count - 1)
        WHERE job_id = NEW.job_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `coordinators`
--

DROP TABLE IF EXISTS `coordinators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coordinators` (
  `coordinator_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dept_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coordinator_type` enum('faculty','student') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'faculty',
  `team` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`coordinator_id`),
  KEY `idx_coord_dept` (`dept_id`),
  CONSTRAINT `fk_coordinator_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_coordinator_user` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `job_drafts`
--

DROP TABLE IF EXISTS `job_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_drafts` (
  `draft_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `recruiter_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `draft_data_json` json NOT NULL,
  `last_saved_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`draft_id`),
  UNIQUE KEY `uq_draft_recruiter` (`recruiter_id`),
  CONSTRAINT `fk_draft_recruiter` FOREIGN KEY (`recruiter_id`) REFERENCES `recruiters` (`recruiter_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_rounds`
--

DROP TABLE IF EXISTS `job_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_rounds` (
  `round_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `job_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `round_type_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `round_number` tinyint NOT NULL,
  `round_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` datetime DEFAULT NULL,
  `submission_deadline` datetime DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_mins` smallint DEFAULT NULL,
  `max_score` decimal(6,2) DEFAULT NULL,
  `round_status` enum('draft','scheduled','ongoing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `suggested_by` enum('recruiter','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recruiter',
  `is_finalized` tinyint(1) NOT NULL DEFAULT '0',
  `finalized_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `is_cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`round_id`),
  UNIQUE KEY `uq_round_job_number` (`job_id`,`round_number`),
  KEY `fk_round_type` (`round_type_id`),
  KEY `fk_round_finalized_by` (`finalized_by`),
  KEY `idx_round_job` (`job_id`),
  KEY `idx_round_status` (`round_status`),
  KEY `idx_round_active` (`job_id`,`is_cancelled`,`is_finalized`),
  CONSTRAINT `fk_round_finalized_by` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_round_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_round_type` FOREIGN KEY (`round_type_id`) REFERENCES `round_types` (`round_type_id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_round_deadline` CHECK (((`submission_deadline` is null) or (`scheduled_at` is null) or (`submission_deadline` <= `scheduled_at`)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_round_reschedule` AFTER UPDATE ON `job_rounds` FOR EACH ROW BEGIN
    IF OLD.scheduled_at <> NEW.scheduled_at
       OR (OLD.scheduled_at IS NULL AND NEW.scheduled_at IS NOT NULL)
       OR (OLD.scheduled_at IS NOT NULL AND NEW.scheduled_at IS NULL)
    THEN
        INSERT INTO round_schedule_logs (
            round_id, old_datetime, new_datetime, reason
        ) VALUES (
            NEW.round_id, OLD.scheduled_at, NEW.scheduled_at,
            NEW.cancellation_reason
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `job_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `session_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recruiter_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_type_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctc_lpa` decimal(7,2) DEFAULT NULL,
  `stipend_pm` decimal(8,2) DEFAULT NULL,
  `salary_type` enum('fixed','range','negotiable','not_disclosed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_disclosed',
  `min_cpi` decimal(4,2) DEFAULT NULL,
  `allowed_year_of_passing` year DEFAULT NULL,
  `allowed_branches_json` json DEFAULT NULL,
  `allowed_programs_json` json DEFAULT NULL,
  `apply_start` datetime DEFAULT NULL,
  `apply_end` datetime DEFAULT NULL,
  `max_participants` int unsigned DEFAULT NULL,
  `applications_count` int unsigned NOT NULL DEFAULT '0',
  `documents_json` json DEFAULT NULL,
  `job_status` enum('draft','pending','verified','opened','closed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `edit_summary` text COLLATE utf8mb4_unicode_ci,
  `last_edited_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_edited_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `closed_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cloned_from_job_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_id`),
  KEY `fk_job_last_edited_by` (`last_edited_by`),
  KEY `fk_job_closed_by` (`closed_by`),
  KEY `fk_job_cloned_from` (`cloned_from_job_id`),
  KEY `idx_job_session` (`session_id`),
  KEY `idx_job_recruiter` (`recruiter_id`),
  KEY `idx_job_status` (`job_status`),
  KEY `idx_job_type` (`job_type_id`),
  KEY `idx_job_yop` (`allowed_year_of_passing`),
  KEY `idx_job_min_cpi` (`min_cpi`),
  KEY `idx_job_apply_end` (`apply_end`),
  CONSTRAINT `fk_job_cloned_from` FOREIGN KEY (`cloned_from_job_id`) REFERENCES `jobs` (`job_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_job_closed_by` FOREIGN KEY (`closed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_job_last_edited_by` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_job_recruiter` FOREIGN KEY (`recruiter_id`) REFERENCES `recruiters` (`recruiter_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_job_session` FOREIGN KEY (`session_id`) REFERENCES `placement_sessions` (`session_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_job_type` FOREIGN KEY (`job_type_id`) REFERENCES `job_types` (`job_type_id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_job_apply_window` CHECK (((`apply_end` is null) or (`apply_start` is null) or (`apply_end` > `apply_start`))),
  CONSTRAINT `chk_job_capacity` CHECK (((`max_participants` is null) or (`max_participants` > 0))),
  CONSTRAINT `chk_job_cpi` CHECK (((`min_cpi` is null) or (`min_cpi` between 0.00 and 10.00)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_before_job_edit` BEFORE UPDATE ON `jobs` FOR EACH ROW BEGIN
    IF OLD.job_status = 'verified'
       AND (OLD.title <> NEW.title
            OR OLD.description <> NEW.description
            OR NOT (OLD.min_cpi <=> NEW.min_cpi)
            OR NOT (OLD.allowed_year_of_passing <=> NEW.allowed_year_of_passing))
    THEN
        SET NEW.job_status = 'pending';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_job_close` AFTER UPDATE ON `jobs` FOR EACH ROW BEGIN
    IF OLD.job_status <> 'closed' AND NEW.job_status = 'closed' THEN
        UPDATE applications
        SET status = 'not_selected',
            coordinator_note = CONCAT(
                COALESCE(coordinator_note, ''),
                ' [Auto-closed: job was closed by admin]'
            )
        WHERE job_id = NEW.job_id
          AND status IN ('applied', 'shortlisted', 'in_process');
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notification_log`
--

DROP TABLE IF EXISTS `notification_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_log` (
  `log_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('otp','welcome','verification_status','application_status','round_result','round_schedule','announcement','offer','general') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel` enum('email','sms','portal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `status` enum('queued','sent','failed','delivered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_notif_user` (`user_id`,`created_at`),
  KEY `idx_notif_ref` (`ref_id`,`ref_type`),
  KEY `idx_notif_status` (`status`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `otp_requests`
--

DROP TABLE IF EXISTS `otp_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_requests` (
  `otp_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` enum('email','sms') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `purpose` enum('login','verify_profile','sensitive_action') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `is_invalidated` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempts` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`otp_id`),
  KEY `idx_otp_user_created` (`user_id`,`created_at`),
  KEY `idx_otp_user_active` (`user_id`,`used_at`,`is_invalidated`,`expires_at`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `otp_requests_chk_1` CHECK (((`attempts` >= 0) and (`attempts` <= 10)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `placement_sessions`
--

DROP TABLE IF EXISTS `placement_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `placement_sessions` (
  `session_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `label` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `active_session_guard` tinyint GENERATED ALWAYS AS (if((`is_active` = true),1,NULL)) VIRTUAL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `uq_session_label` (`label`),
  UNIQUE KEY `uq_only_one_active_session` (`active_session_guard`),
  KEY `fk_session_created_by` (`created_by`),
  KEY `idx_session_active` (`is_active`),
  CONSTRAINT `fk_session_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `chk_session_dates` CHECK ((`end_date` > `start_date`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `placements`
--

DROP TABLE IF EXISTS `placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `placements` (
  `placement_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `student_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `placement_type` enum('campus','ppo','off_campus') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'campus',
  `company_name_manual` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_ctc_lpa` decimal(7,2) DEFAULT NULL,
  `stipend_pm` decimal(8,2) DEFAULT NULL,
  `offer_date` date DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `offer_letter_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer_status` enum('offered','accepted','declined','joined','revoked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offered',
  `session_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`placement_id`),
  UNIQUE KEY `uq_student_placed` (`student_id`),
  KEY `fk_placement_job` (`job_id`),
  KEY `fk_placement_application` (`application_id`),
  KEY `fk_placement_recorded_by` (`recorded_by`),
  KEY `idx_placement_student` (`student_id`),
  KEY `idx_placement_session` (`session_id`),
  KEY `idx_placement_type` (`placement_type`),
  KEY `idx_placement_status` (`offer_status`),
  CONSTRAINT `fk_placement_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_placement_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_placement_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_placement_session` FOREIGN KEY (`session_id`) REFERENCES `placement_sessions` (`session_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_placement_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `recruiters`
--

DROP TABLE IF EXISTS `recruiters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recruiters` (
  `recruiter_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary_position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_details_json` json DEFAULT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `profile_completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`recruiter_id`),
  CONSTRAINT `fk_recruiter_user` FOREIGN KEY (`recruiter_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `round_results`
--

DROP TABLE IF EXISTS `round_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `round_results` (
  `result_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `round_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `result` enum('pass','fail','absent','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `result_released` tinyint(1) NOT NULL DEFAULT '0',
  `released_at` datetime DEFAULT NULL,
  `released_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entered_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `uq_result` (`round_id`,`application_id`),
  KEY `fk_result_released_by` (`released_by`),
  KEY `fk_result_entered_by` (`entered_by`),
  KEY `idx_result_round` (`round_id`),
  KEY `idx_result_application` (`application_id`),
  KEY `idx_result_student` (`student_id`),
  KEY `idx_result_released` (`round_id`,`result_released`),
  CONSTRAINT `fk_result_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_result_entered_by` FOREIGN KEY (`entered_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_result_released_by` FOREIGN KEY (`released_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_result_round` FOREIGN KEY (`round_id`) REFERENCES `job_rounds` (`round_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_result_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `round_schedule_logs`
--

DROP TABLE IF EXISTS `round_schedule_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `round_schedule_logs` (
  `log_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `round_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_datetime` datetime DEFAULT NULL,
  `new_datetime` datetime DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `changed_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `fk_rsl_changed_by` (`changed_by`),
  KEY `idx_rsl_round` (`round_id`),
  CONSTRAINT `fk_rsl_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_rsl_round` FOREIGN KEY (`round_id`) REFERENCES `job_rounds` (`round_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `student_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roll_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('general','obc','sc','st','ews','pwd') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `program_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dept_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_semester` tinyint DEFAULT NULL,
  `cpi` decimal(4,2) DEFAULT NULL,
  `year_of_admission` year DEFAULT NULL,
  `year_of_passing` year DEFAULT NULL,
  `placement_status` enum('not_placed','placed','opted_out','ppo','off_campus') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_placed',
  `locality` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_details_json` json DEFAULT NULL,
  `experiences_json` json DEFAULT NULL,
  `skills_json` json DEFAULT NULL,
  `personal_links_json` json DEFAULT NULL,
  `family_info_json` json DEFAULT NULL,
  `documents_json` json DEFAULT NULL,
  `profile_completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `uq_student_roll` (`roll_no`),
  KEY `idx_student_dept` (`dept_id`),
  KEY `idx_student_yop` (`year_of_passing`),
  KEY `idx_student_cpi` (`cpi`),
  KEY `idx_student_placement` (`placement_status`),
  KEY `idx_student_program` (`program_id`),
  CONSTRAINT `fk_student_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_user` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_student_cpi` CHECK (((`cpi` is null) or (`cpi` between 0.00 and 10.00))),
  CONSTRAINT `chk_student_dob` CHECK (((`date_of_birth` is null) or (`date_of_birth` < _utf8mb4'2050-01-01'))),
  CONSTRAINT `chk_student_semester` CHECK (((`current_semester` is null) or (`current_semester` between 1 and 10)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` tinyint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `account_activated` tinyint(1) NOT NULL DEFAULT '0',
  `preferred_otp_channel` enum('email','sms') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `first_login_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deactivated_at` datetime DEFAULT NULL,
  `deactivation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_email` (`email`),
  KEY `idx_user_role` (`role_id`),
  KEY `idx_user_is_active` (`is_active`),
  KEY `idx_user_created_by` (`created_by`),
  CONSTRAINT `fk_user_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_phone_format` CHECK (((`phone` is null) or regexp_like(`phone`,_utf8mb4'^[0-9]{10,15}$')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    DECLARE v_role_name VARCHAR(50);
    SELECT name INTO v_role_name
    FROM roles WHERE role_id = NEW.role_id;

    IF v_role_name = 'student' THEN
        INSERT INTO students (student_id, roll_no, name)
        VALUES (NEW.user_id, NULL, '');

        INSERT INTO verifications (entity_id, entity_type, status)
        VALUES (NEW.user_id, 'student', 'draft');

    ELSEIF v_role_name = 'recruiter' THEN
        INSERT INTO recruiters (recruiter_id, primary_name)
        VALUES (NEW.user_id, '');

        INSERT INTO verifications (entity_id, entity_type, status)
        VALUES (NEW.user_id, 'recruiter', 'draft');
    END IF;
    
    
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_user_deactivate` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.is_active = TRUE AND NEW.is_active = FALSE THEN
        UPDATE otp_requests
        SET is_invalidated = TRUE
        WHERE user_id = NEW.user_id
          AND used_at IS NULL
          AND is_invalidated = FALSE;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `verification_logs`
--

DROP TABLE IF EXISTS `verification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verification_logs` (
  `log_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `verification_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_vlog_verification` (`verification_id`),
  KEY `idx_vlog_entity` (`entity_id`,`entity_type`),
  KEY `idx_vlog_changed_by` (`changed_by`),
  CONSTRAINT `fk_vlog_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vlog_verification` FOREIGN KEY (`verification_id`) REFERENCES `verifications` (`verification_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `verifications`
--

DROP TABLE IF EXISTS `verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verifications` (
  `verification_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `entity_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','pending','under_review','verified','resubmit','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `assigned_coordinator_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`verification_id`),
  UNIQUE KEY `uq_verification_entity` (`entity_id`,`entity_type`),
  KEY `fk_verif_verified_by` (`verified_by`),
  KEY `idx_verif_entity_type` (`entity_type`,`status`),
  KEY `idx_verif_coordinator` (`assigned_coordinator_id`),
  KEY `idx_verif_status` (`status`),
  CONSTRAINT `fk_verif_coordinator` FOREIGN KEY (`assigned_coordinator_id`) REFERENCES `coordinators` (`coordinator_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_verif_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_verification_insert` AFTER INSERT ON `verifications` FOR EACH ROW BEGIN
    INSERT INTO verification_logs (
        verification_id, entity_id, entity_type,
        from_status, to_status
    ) VALUES (
        NEW.verification_id, NEW.entity_id, NEW.entity_type,
        NULL, NEW.status
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_after_verification_update` AFTER UPDATE ON `verifications` FOR EACH ROW BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO verification_logs (
            verification_id, entity_id, entity_type,
            from_status, to_status, changed_by
        ) VALUES (
            NEW.verification_id, NEW.entity_id, NEW.entity_type,
            OLD.status, NEW.status, NEW.verified_by
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping routines for database 'tnpdb'
--
/*!50003 DROP PROCEDURE IF EXISTS `AcceptOffer` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `AcceptOffer`(
    IN  p_application_id    CHAR(36),
    IN  p_student_id        CHAR(36),
    IN  p_actual_ctc        DECIMAL(7,2),
    IN  p_offer_date        DATE,
    IN  p_offer_letter_url  VARCHAR(500),
    OUT p_result            VARCHAR(50)
)
BEGIN
    DECLARE v_job_id        CHAR(36);
    DECLARE v_session_id    CHAR(36);
    DECLARE v_app_status    VARCHAR(30);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    
    SELECT placement_status INTO @v_placed
    FROM students
    WHERE student_id = p_student_id
    FOR UPDATE;

    IF @v_placed = 'placed' THEN
        SET p_result = 'already_placed';
        ROLLBACK;
    ELSE
        
        SELECT j.job_id, j.session_id
        INTO v_job_id, v_session_id
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        WHERE a.application_id = p_application_id;

        
        UPDATE applications
        SET status = 'offer_accepted',
            offer_accepted_at = NOW()
        WHERE application_id = p_application_id;

        
        UPDATE students
        SET placement_status = 'placed'
        WHERE student_id = p_student_id;

        
        INSERT INTO placements (
            student_id, job_id, application_id,
            placement_type, actual_ctc_lpa,
            offer_date, offer_letter_url,
            offer_status, session_id,
            academic_year
        )
        SELECT
            p_student_id, v_job_id, p_application_id,
            'campus', p_actual_ctc,
            p_offer_date, p_offer_letter_url,
            'accepted', v_session_id,
            label
        FROM placement_sessions
        WHERE session_id = v_session_id;

        SET p_result = 'ok';
        COMMIT;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CreateAdminAccount` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateAdminAccount`(
    IN p_email          VARCHAR(255),
    IN p_phone          VARCHAR(15),
    IN p_name           VARCHAR(150),
    IN p_designation    VARCHAR(100),
    IN p_access_level   ENUM('admin','super_admin'),
    IN p_created_by     CHAR(36),
    OUT p_new_user_id   CHAR(36)
)
BEGIN
    DECLARE v_role_id       TINYINT UNSIGNED;
    DECLARE v_new_user_id   CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT role_id INTO v_role_id FROM roles WHERE name = 'admin';

    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email already exists in the system.';
    END IF;

    SET v_new_user_id = UUID();

    INSERT INTO users (
        user_id, email, phone, role_id,
        is_active, account_activated,
        preferred_otp_channel, created_by
    ) VALUES (
        v_new_user_id, p_email, p_phone, v_role_id,
        TRUE, FALSE,
        'email', p_created_by
    );

    INSERT INTO admins (
        admin_id, name, phone, designation, access_level
    ) VALUES (
        v_new_user_id, p_name, p_phone, p_designation, p_access_level
    );

    SET p_new_user_id = v_new_user_id;

    COMMIT;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CreateCoordinatorAccount` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateCoordinatorAccount`(
    IN p_email          VARCHAR(255),
    IN p_phone          VARCHAR(15),
    IN p_name           VARCHAR(150),
    IN p_dept_code      VARCHAR(20),
    IN p_designation    VARCHAR(100),
    IN p_team           VARCHAR(100),
    IN p_coord_type     ENUM('faculty','student'),
    IN p_created_by     CHAR(36),       
    OUT p_new_user_id   CHAR(36)
)
BEGIN
    DECLARE v_role_id       TINYINT UNSIGNED;
    DECLARE v_dept_id       CHAR(36);
    DECLARE v_new_user_id   CHAR(36);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    
    SELECT role_id INTO v_role_id FROM roles WHERE name = 'coordinator';

    
    SELECT dept_id INTO v_dept_id FROM departments WHERE code = p_dept_code;

    
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email already exists in the system.';
    END IF;

    SET v_new_user_id = UUID();

    
    INSERT INTO users (
        user_id, email, phone, role_id,
        is_active, account_activated,
        preferred_otp_channel, created_by
    ) VALUES (
        v_new_user_id, p_email, p_phone, v_role_id,
        TRUE, FALSE,
        'email', p_created_by
    );

    
    INSERT INTO coordinators (
        coordinator_id, name, phone,
        dept_id, designation, coordinator_type, team
    ) VALUES (
        v_new_user_id, p_name, p_phone,
        v_dept_id, p_designation, p_coord_type, p_team
    );

    SET p_new_user_id = v_new_user_id;

    COMMIT;
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DeactivateUser` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DeactivateUser`(
    IN p_user_id    CHAR(36),
    IN p_reason     VARCHAR(255),
    IN p_by         CHAR(36)
)
BEGIN
    UPDATE users
    SET is_active           = FALSE,
        deactivated_at      = NOW(),
        deactivation_reason = p_reason
    WHERE user_id = p_user_id;
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ValidateAndUseOTP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ValidateAndUseOTP`(
    IN  p_user_id   CHAR(36),
    IN  p_otp_hash  CHAR(64),       
    IN  p_purpose   VARCHAR(30),
    OUT p_result    VARCHAR(20)
)
BEGIN
    DECLARE v_otp_id        CHAR(36);
    DECLARE v_attempts      TINYINT;
    DECLARE v_expires_at    DATETIME;
    DECLARE v_used_at       DATETIME;
    DECLARE v_invalidated   BOOLEAN;

    
    SELECT otp_id, attempts, expires_at, used_at, is_invalidated
    INTO v_otp_id, v_attempts, v_expires_at, v_used_at, v_invalidated
    FROM otp_requests
    WHERE user_id   = p_user_id
      AND otp_hash  = p_otp_hash
      AND purpose   = p_purpose
    ORDER BY created_at DESC
    LIMIT 1
    FOR UPDATE;

    IF v_otp_id IS NULL THEN
        SET p_result = 'not_found';
    ELSEIF v_invalidated = TRUE THEN
        SET p_result = 'invalid';
    ELSEIF v_used_at IS NOT NULL THEN
        SET p_result = 'used';
    ELSEIF NOW() > v_expires_at THEN
        SET p_result = 'expired';
    ELSEIF v_attempts >= 3 THEN
        SET p_result = 'max_attempts';
    ELSE
        
        UPDATE otp_requests
        SET used_at = NOW()
        WHERE otp_id = v_otp_id;

        
        UPDATE users
        SET last_login_at    = NOW(),
            account_activated = TRUE,
            first_login_at   = COALESCE(first_login_at, NOW())
        WHERE user_id = p_user_id;

        SET p_result = 'ok';
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-31 22:27:34
