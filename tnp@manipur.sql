-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: tnpdb
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.24.04.1

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
-- Temporary view structure for view `EventDetailsView`
--

DROP TABLE IF EXISTS `EventDetailsView`;
/*!50001 DROP VIEW IF EXISTS `EventDetailsView`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `EventDetailsView` AS SELECT 
 1 AS `EventId`,
 1 AS `EventTitle`,
 1 AS `EventType`,
 1 AS `EventStatus`,
 1 AS `EventLocation`,
 1 AS `EventDescription`,
 1 AS `EventDocument`,
 1 AS `EventStartDate`,
 1 AS `EventEndDate`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `EventsRecruiterDetails`
--

DROP TABLE IF EXISTS `EventsRecruiterDetails`;
/*!50001 DROP VIEW IF EXISTS `EventsRecruiterDetails`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `EventsRecruiterDetails` AS SELECT 
 1 AS `EventId`,
 1 AS `EventTitle`,
 1 AS `EventType`,
 1 AS `EventDescription`,
 1 AS `EventDocument`,
 1 AS `EventLocation`,
 1 AS `EventStartDate`,
 1 AS `EventEndDate`,
 1 AS `VerificationStatus`,
 1 AS `VerifiedOn`,
 1 AS `RecruiterId`,
 1 AS `PrimaryContactName`,
 1 AS `PrimaryContactPosition`,
 1 AS `PrimaryContactEmail`,
 1 AS `PrimaryContactPhone`,
 1 AS `PrimaryContactLinkedinProfile`,
 1 AS `AltContactName`,
 1 AS `AltContactPosition`,
 1 AS `AltContactEmail`,
 1 AS `AltContactPhone`,
 1 AS `AltContactLinkedinProfile`,
 1 AS `CompanyDetailsJson`,
 1 AS `CreatedAt`,
 1 AS `UpdatedAt`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `RecruiterJobDetailById`
--

DROP TABLE IF EXISTS `RecruiterJobDetailById`;
/*!50001 DROP VIEW IF EXISTS `RecruiterJobDetailById`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `RecruiterJobDetailById` AS SELECT 
 1 AS `EventID`,
 1 AS `OrganiserId`,
 1 AS `Event`,
 1 AS `Type`,
 1 AS `Posted`,
 1 AS `Location`,
 1 AS `Description`,
 1 AS `AttachedDocumens`,
 1 AS `Status`,
 1 AS `StatusOn`,
 1 AS `Message`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `RecruiterJobsList`
--

DROP TABLE IF EXISTS `RecruiterJobsList`;
/*!50001 DROP VIEW IF EXISTS `RecruiterJobsList`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `RecruiterJobsList` AS SELECT 
 1 AS `EventID`,
 1 AS `OrganiserId`,
 1 AS `Event`,
 1 AS `Type`,
 1 AS `Posted`,
 1 AS `Location`,
 1 AS `Status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `RecruiterList`
--

DROP TABLE IF EXISTS `RecruiterList`;
/*!50001 DROP VIEW IF EXISTS `RecruiterList`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `RecruiterList` AS SELECT 
 1 AS `recruiterId`,
 1 AS `primaryContactName`,
 1 AS `primaryContactPosition`,
 1 AS `primaryContactEmail`,
 1 AS `primaryContactPhone`,
 1 AS `primaryContactLinkedinProfile`,
 1 AS `altContactName`,
 1 AS `altContactPosition`,
 1 AS `altContactEmail`,
 1 AS `altContactPhone`,
 1 AS `altContactLinkedinProfile`,
 1 AS `remark`,
 1 AS `CompanyDetailsJson`,
 1 AS `Status`,
 1 AS `createdAt`,
 1 AS `updatedAt`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `RecruitersDetailsPreview`
--

DROP TABLE IF EXISTS `RecruitersDetailsPreview`;
/*!50001 DROP VIEW IF EXISTS `RecruitersDetailsPreview`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `RecruitersDetailsPreview` AS SELECT 
 1 AS `RecruiterID`,
 1 AS `PrimaryContactName`,
 1 AS `PrimaryContactPosition`,
 1 AS `PrimaryContactEmail`,
 1 AS `PrimaryContactPhone`,
 1 AS `PrimaryContactLinkedInProfile`,
 1 AS `AlternateContactName`,
 1 AS `AlternateContactPosition`,
 1 AS `AlternateContactEmail`,
 1 AS `AlternateContactPhone`,
 1 AS `AlternateContactLinkedInProfile`,
 1 AS `Remark`,
 1 AS `CompanyDetails`,
 1 AS `CreatedAt`,
 1 AS `LastUpdatedAt`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `StudentDetailsPreview`
--

DROP TABLE IF EXISTS `StudentDetailsPreview`;
/*!50001 DROP VIEW IF EXISTS `StudentDetailsPreview`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `StudentDetailsPreview` AS SELECT 
 1 AS `StudentID`,
 1 AS `RollNumber`,
 1 AS `StudentName`,
 1 AS `StudentCategory`,
 1 AS `DateOfBirth`,
 1 AS `Gender`,
 1 AS `AcademicProgram`,
 1 AS `Department`,
 1 AS `CurrentSemester`,
 1 AS `CurrentCPI`,
 1 AS `AdmissionYear`,
 1 AS `PassingYear`,
 1 AS `BloodGroup`,
 1 AS `PhoneNumber`,
 1 AS `Locality`,
 1 AS `City`,
 1 AS `State`,
 1 AS `Country`,
 1 AS `Pincode`,
 1 AS `PlacementInterest`,
 1 AS `StudentComments`,
 1 AS `PersonalDetails`,
 1 AS `EducationDetails`,
 1 AS `Experiences`,
 1 AS `Skills`,
 1 AS `Documents`,
 1 AS `CreatedAt`,
 1 AS `LastUpdatedAt`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `StudentEventApplicationsView`
--

DROP TABLE IF EXISTS `StudentEventApplicationsView`;
/*!50001 DROP VIEW IF EXISTS `StudentEventApplicationsView`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `StudentEventApplicationsView` AS SELECT 
 1 AS `participantEntryId`,
 1 AS `studentId`,
 1 AS `eventId`,
 1 AS `registrationDatetime`,
 1 AS `participationStatus`,
 1 AS `applicationMessage`,
 1 AS `applicationDocumentLink`,
 1 AS `studentRollNo`,
 1 AS `studentName`,
 1 AS `studentProgram`,
 1 AS `studentDepartment`,
 1 AS `studentCPI`,
 1 AS `studentPhoneNumber`,
 1 AS `eventTitle`,
 1 AS `eventType`,
 1 AS `eventLocation`,
 1 AS `eventStartDate`,
 1 AS `eventEndDate`,
 1 AS `eventStatus`,
 1 AS `participantCreatedAt`,
 1 AS `participantUpdatedAt`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `StudentsList`
--

DROP TABLE IF EXISTS `StudentsList`;
/*!50001 DROP VIEW IF EXISTS `StudentsList`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `StudentsList` AS SELECT 
 1 AS `studentId`,
 1 AS `RollNo`,
 1 AS `Name`,
 1 AS `Category`,
 1 AS `DateOfBirth`,
 1 AS `Gender`,
 1 AS `Program`,
 1 AS `Department`,
 1 AS `CurrentSemester`,
 1 AS `CPI`,
 1 AS `YearOfAdmission`,
 1 AS `YearOfPassing`,
 1 AS `BloodGroup`,
 1 AS `PhoneNumber`,
 1 AS `Locality`,
 1 AS `City`,
 1 AS `State`,
 1 AS `Country`,
 1 AS `Pincode`,
 1 AS `PlacementInterest`,
 1 AS `Comments`,
 1 AS `PersonalDetailsJson`,
 1 AS `EducationDetailsJson`,
 1 AS `ExperiencesJson`,
 1 AS `AdditionalDetailsJson`,
 1 AS `DocumentsJson`,
 1 AS `Status`,
 1 AS `created_at`,
 1 AS `updated_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `VerificationStatus`
--

DROP TABLE IF EXISTS `VerificationStatus`;
/*!50001 DROP VIEW IF EXISTS `VerificationStatus`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `VerificationStatus` AS SELECT 
 1 AS `verified_entity_id`,
 1 AS `verified_on`,
 1 AS `status`,
 1 AS `notes`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `coordinators`
--

DROP TABLE IF EXISTS `coordinators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coordinators` (
  `coordinator_id` char(36) NOT NULL DEFAULT (uuid()),
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `department` varchar(255) NOT NULL COMMENT 'department of coordinator',
  `semester` int NOT NULL,
  `designation` varchar(255) NOT NULL,
  `team` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`coordinator_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_department` (`department`),
  KEY `idx_team` (`team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coordinators`
--

/*!40000 ALTER TABLE `coordinators` DISABLE KEYS */;
INSERT INTO `coordinators` VALUES ('88846247-5cbf-11f0-99ba-cc4740c7c70f','hari ','h@gmail.com','7017375520','cse',5,'chief','NULL','2025-07-09 12:23:34','2025-07-09 12:23:34');
/*!40000 ALTER TABLE `coordinators` ENABLE KEYS */;

--
-- Table structure for table `event_sub_process_participants`
--

DROP TABLE IF EXISTS `event_sub_process_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_sub_process_participants` (
  `sub_process_participant_id` char(36) NOT NULL DEFAULT (uuid()),
  `participant_id` char(36) NOT NULL,
  `sub_process_id` char(36) NOT NULL,
  `status` enum('invited','accepted_invite','declined_invite','in_progress','completed','passed','failed','attended','not_attended','rescheduled','withdrew','blocked') DEFAULT 'invited',
  `feedback` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sub_process_participant_id`),
  UNIQUE KEY `uq_student_sub_process` (`participant_id`,`sub_process_id`),
  KEY `idx_participant_id` (`participant_id`),
  KEY `idx_sub_process_id` (`sub_process_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `event_sub_process_participants_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`participant_entry_id`) ON DELETE CASCADE,
  CONSTRAINT `event_sub_process_participants_ibfk_2` FOREIGN KEY (`sub_process_id`) REFERENCES `event_sub_processes` (`sub_process_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_sub_process_participants`
--

/*!40000 ALTER TABLE `event_sub_process_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_sub_process_participants` ENABLE KEYS */;

--
-- Table structure for table `event_sub_processes`
--

DROP TABLE IF EXISTS `event_sub_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_sub_processes` (
  `sub_process_id` char(36) NOT NULL DEFAULT (uuid()),
  `event_id` char(36) NOT NULL,
  `sub_process_title` varchar(255) NOT NULL,
  `sub_process_description` text,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled','postponed') DEFAULT 'scheduled',
  `location_details` varchar(255) DEFAULT NULL,
  `responsible_coordinator_id` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sub_process_id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_sub_process_title` (`sub_process_title`),
  KEY `idx_status` (`status`),
  KEY `idx_start_datetime` (`start_datetime`),
  KEY `idx_responsible_coordinator_id` (`responsible_coordinator_id`),
  CONSTRAINT `event_sub_processes_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `event_sub_processes_ibfk_2` FOREIGN KEY (`responsible_coordinator_id`) REFERENCES `coordinators` (`coordinator_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_sub_processes`
--

/*!40000 ALTER TABLE `event_sub_processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_sub_processes` ENABLE KEYS */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `event_id` char(36) NOT NULL DEFAULT (uuid()),
  `event_organiser_id` char(36) NOT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_description` text,
  `event_document` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `event_location` varchar(255) DEFAULT NULL,
  `event_start_date` datetime DEFAULT NULL COMMENT 'to be added by admin ',
  `event_end_date` datetime DEFAULT NULL COMMENT 'to be set by admin ',
  `event_status` enum('opened','closed') DEFAULT NULL COMMENT 'admin can open or close the event for students',
  `max_participants` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `reference_id` (`reference_id`),
  KEY `idx_event_organiser_id` (`event_organiser_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_event_title` (`event_title`),
  KEY `idx_reference_id` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES ('c6b1016a-5c9b-11f0-99ba-cc4740c7c70f','123e4567-e89b-12d3-a456-426614174000','JOB-2025-001','Senior Software Engineer','Full time','We are looking for a highly skilled Senior Software Engineer to join our dynamic team. Responsibilities include designing, developing, and maintaining high-quality software solutions, collaborating with cross-functional teams, and mentoring junior engineers. Strong proficiency in Python and AWS is required.','{\"Document1\": \"JobDescription_SSE.pdf\", \"Document2\": \"JobDescription_BBC.pdf\"}','2025-07-09 08:07:37','2025-07-10 09:57:32','Bengaluru, India','2025-08-01 09:00:00','2025-08-31 17:00:00','opened','100'),('dae3c66c-5c83-11f0-99ba-cc4740c7c70f','123e4567-e89b-12d3-a456-426614174000',NULL,'PHP','Internship',NULL,NULL,'2025-07-09 05:16:23','2025-07-09 05:16:23','remote',NULL,NULL,NULL,NULL),('eaaac994-5ca9-11f0-99ba-cc4740c7c70f','123e4567-e89b-12d3-a456-426614174000','INT-DESIGN-002','UI/UX Design Intern','Internship','Looking for a passionate UI/UX Design Intern to assist our design team. You will work on user research, wireframing, prototyping, and creating visual designs for our mobile and web platforms. Basic knowledge of Figma or Adobe XD is a plus.','[\"https://www.w3schools.com/sql/func_mysql_concat.asp\", \"http://localhost/tnp@iiitmanipur/dashboard/student/student_profile.php\", \"https://www.overleaf.com/login?\"]','2025-07-09 09:48:50','2025-07-10 10:42:13','Mumbai, India (Hybrid)','2025-09-01 10:00:00','2025-11-30 18:00:00','closed','100');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;

--
-- Table structure for table `participants`
--

DROP TABLE IF EXISTS `participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participants` (
  `participant_entry_id` char(36) NOT NULL DEFAULT (uuid()),
  `participant_id` char(36) NOT NULL,
  `event_id` char(36) NOT NULL,
  `registration_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('registered','approved','rejected','shortlisted','selected','attended','blocked','cancelled','waitinglist') DEFAULT 'registered',
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `document` varchar(255) DEFAULT NULL COMMENT 'to be a link to be added by student',
  PRIMARY KEY (`participant_entry_id`),
  UNIQUE KEY `uq_student_event` (`participant_id`,`event_id`),
  KEY `idx_participant_id` (`participant_id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_status` (`status`),
  KEY `idx_registration_datetime` (`registration_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participants`
--

/*!40000 ALTER TABLE `participants` DISABLE KEYS */;
INSERT INTO `participants` VALUES ('7cff6ada-5d88-11f0-99ba-cc4740c7c70f','a1b2c3d4-e5f6-7890-1234-567890abcdef','c6b1016a-5c9b-11f0-99ba-cc4740c7c70f','2025-07-10 17:52:04','registered','Student applied for event.','2025-07-10 12:22:04','2025-07-10 12:22:04','http://localhost/tnp@iiitmanipur/dataRouting/student/RegisterStudentForEvent.php');
/*!40000 ALTER TABLE `participants` ENABLE KEYS */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_permission_name` (`name`),
  KEY `idx_module_action` (`module`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;

--
-- Table structure for table `recruiters`
--

DROP TABLE IF EXISTS `recruiters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recruiters` (
  `recruiter_id` char(36) NOT NULL,
  `primary_contact_name` varchar(100) NOT NULL,
  `primary_contact_position` varchar(100) DEFAULT NULL,
  `primary_contact_email` varchar(100) NOT NULL,
  `primary_contact_phone` varchar(20) DEFAULT NULL,
  `primary_contact_linkedin_profile` varchar(255) DEFAULT NULL,
  `alt_contact_name` varchar(100) DEFAULT NULL,
  `alt_contact_position` varchar(100) DEFAULT NULL,
  `alt_contact_email` varchar(100) DEFAULT NULL,
  `alt_contact_phone` varchar(20) DEFAULT NULL,
  `alt_contact_linkedin_profile` varchar(255) DEFAULT NULL,
  `remark` text,
  `company_details_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`recruiter_id`),
  UNIQUE KEY `primary_contact_email` (`primary_contact_email`),
  KEY `idx_primary_contact_email` (`primary_contact_email`),
  KEY `idx_primary_contact_name` (`primary_contact_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recruiters`
--

/*!40000 ALTER TABLE `recruiters` DISABLE KEYS */;
INSERT INTO `recruiters` VALUES ('06053072-5b14-11f0-b6d3-cc4740c7c70f','Test recruiter','HR Manager','test.recruiter@iiitmanipur.ac.in',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Any suggestions or Remarks','{\"city\": \"Bengaluru\", \"about\": \"kjnvkxzxjdvnzkjcx\", \"state\": \"Karnataka\", \"address\": \"Prestige Tech Park, Outer Ring Road\", \"country\": \"India\", \"company_name\": \"AI limited\", \"company_website\": \"https://linkedin.com/in/johndoeupdated\", \"company_linkedin\": \"https://linkedin.com/in/johndoeupdated\"}','2025-07-07 09:23:20','2025-07-10 02:52:33'),('123e4567-e89b-12d3-a456-426614174000','hari',' Director','john.updated@example.com','7017375520','https://linkedin.com/in/johndoeupdated','Jane Smith Updated','Lead Assistant HR','jane.updated@example.com','9998887777','https://linkedin.com/in/janesmithupdated','Updated recruiter info','{\"city\": \"Bengaluru\", \"about\": \"kjnvkxzxjdvnzkjcx\", \"state\": \"Karnataka\", \"address\": \"Prestige Tech Park, Outer Ring Road\", \"country\": \"India\", \"company_name\": \"hk limited\", \"company_website\": \"https://linkedin.com/in/johndoeupdated\", \"company_linkedin\": \"https://linkedin.com/in/johndoeupdated\"}','2025-07-08 07:59:48','2025-07-10 02:44:53');
/*!40000 ALTER TABLE `recruiters` ENABLE KEYS */;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  KEY `idx_role_permission` (`role_id`,`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_role_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'student','Student users who can create profile and apply for jobs after verification','2025-07-06 17:05:25','2025-07-06 17:05:25'),(2,'recruiter','Recruiter users who can create profile and create and manage job postings','2025-07-06 17:05:25','2025-07-06 17:05:25'),(3,'admin','Administrator users with full system access and job approval rights','2025-07-06 17:05:25','2025-07-06 17:05:25'),(4,'coordinator','Coordinator users who verify and approve student, recruiter and job profiles','2025-07-06 17:05:25','2025-07-06 17:05:25');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `student_id` char(36) NOT NULL,
  `roll_no` varchar(20) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `category` enum('general','obc','sc','st','ews','pwd') DEFAULT 'general',
  `date_of_birth` varchar(10) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'other',
  `program` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `current_semester` int DEFAULT '1',
  `cpi` decimal(4,2) DEFAULT '0.00',
  `year_of_admission` int DEFAULT NULL,
  `year_of_passing` int DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `pincode` varchar(10) DEFAULT NULL,
  `placement_interest` varchar(255) DEFAULT 'Not Specified',
  `comments` text,
  `personal_details_json` json DEFAULT NULL,
  `education_details_json` json DEFAULT NULL,
  `experiences_json` json DEFAULT NULL,
  `additional_details_json` json DEFAULT NULL,
  `documents_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `roll_no` (`roll_no`),
  KEY `idx_roll_no` (`roll_no`),
  KEY `idx_name` (`name`),
  KEY `idx_category` (`category`),
  KEY `idx_department` (`department`),
  KEY `idx_program` (`program`),
  KEY `idx_year_of_passing` (`year_of_passing`),
  KEY `idx_placement_interest` (`placement_interest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES ('3fb133f3-5aa1-11f0-b6d3-cc4740c7c70f',NULL,'Test Student','general',NULL,'other','B.Tech Computer Science and Engineering','CSE',1,0.00,2022,2026,NULL,NULL,NULL,NULL,NULL,'India',NULL,'Not Specified',NULL,'{\"github_profile\": \"UNK\", \"personal_email\": \"UNK\", \"portfolio_link\": \"UNK\", \"area_of_interest\": \"UNK\", \"linkedin_profile\": \"UNK\", \"programming_skills\": [], \"area_of_interest_other\": \"UNK\"}','{\"jee_year\": \"UNK\", \"tenth_board\": \"UNK\", \"tenth_score\": \"UNK\", \"twelfth_board\": \"UNK\", \"twelfth_score\": \"UNK\", \"jee_mains_rank\": \"UNK\", \"twelfth_stream\": \"UNK\", \"jee_advanced_rank\": \"UNK\", \"tenth_school_name\": \"UNK\", \"twelfth_school_name\": \"UNK\", \"jee_advanced_cleared\": false, \"tenth_year_of_passing\": \"UNK\", \"twelfth_year_of_passing\": \"UNK\"}','{\"projects\": [], \"internships\": [], \"certificates\": []}','{\"family_info\": {\"father_name\": \"UNK\", \"mother_name\": \"UNK\", \"guardian_name\": \"UNK\"}}','{\"photo_link\": \"UNK\", \"tenth_marksheet_link\": \"UNK\", \"other_certificate_link\": \"UNK\", \"twelfth_marksheet_link\": \"UNK\", \"jee_main_scorecard_link\": \"UNK\", \"internship_certificate_link\": \"UNK\", \"jee_advanced_scorecard_link\": \"UNK\"}','2025-07-06 19:41:45','2025-07-06 19:41:45'),('5327a8a3-5aec-11f0-b6d3-cc4740c7c70f',NULL,'Test Student','general',NULL,'other','B.Tech Computer Science and Engineering','CSE',1,0.00,2022,2026,NULL,NULL,NULL,NULL,NULL,'India',NULL,'Not Specified',NULL,'{\"father_name\": \"UNK\", \"mother_name\": \"UNK\", \"guardian_name\": \"UNK\", \"github_profile\": \"UNK\", \"personal_email\": \"UNK\", \"portfolio_link\": \"UNK\", \"linkedin_profile\": \"UNK\"}','{\"jee_year\": \"UNK\", \"tenth_board\": \"UNK\", \"tenth_score\": \"UNK\", \"twelfth_board\": \"UNK\", \"twelfth_score\": \"UNK\", \"jee_mains_rank\": \"UNK\", \"twelfth_stream\": \"UNK\", \"jee_advanced_rank\": \"UNK\", \"tenth_school_name\": \"UNK\", \"twelfth_school_name\": \"UNK\", \"jee_advanced_cleared\": false, \"tenth_year_of_passing\": \"UNK\", \"twelfth_year_of_passing\": \"UNK\"}','{\"projects\": [], \"internships\": [], \"certificates\": []}','{\"area_of_interest\": \"UNK\", \"programming_skills\": [], \"area_of_interest_other\": \"UNK\"}','{\"photo_link\": \"UNK\", \"tenth_marksheet_link\": \"UNK\", \"other_certificate_link\": \"UNK\", \"twelfth_marksheet_link\": \"UNK\", \"jee_main_scorecard_link\": \"UNK\", \"internship_certificate_link\": \"UNK\", \"jee_advanced_scorecard_link\": \"UNK\"}','2025-07-07 04:39:10','2025-07-07 04:39:10'),('a1b2c3d4-e5f6-7890-1234-567890abcdef','2023CSB001','Arjun Sharma','obc','2002-05-15','male','B.Tech Computer Science and Engineering','CSE',4,8.75,2023,2027,'A+','9876543210','Sector 10','Gurugram','Haryana','India','122001','1','Actively looking for SDE roles, proficient in Python and Java.','{\"family_info\": {\"father_name\": \"Rajesh Sharma\", \"mother_name\": \"Priya Sharma\"}, \"github_profile\": \"https://github.com/arjunsharma_cs\", \"personal_email\": \"arjun.sharma@example.com\", \"portfolio_link\": \"https://arjunsharma.dev\", \"linkedin_profile\": \"https://linkedin.com/in/arjun-sharma-cs\", \"area_of_interest_other\": null}','{\"jee_year\": \"2023\", \"tenth_board\": \"CBSE\", \"tenth_score\": \"92.5\", \"twelfth_board\": \"CBSE\", \"twelfth_score\": \"88.0\", \"jee_mains_rank\": \"5500\", \"twelfth_stream\": \"Science (PCM)\", \"jee_advanced_rank\": \"2800\", \"tenth_school_name\": \"Delhi Public School, Gurugram\", \"twelfth_school_name\": \"Delhi Public School, Gurugram\", \"jee_advanced_cleared\": false, \"tenth_year_of_passing\": \"2019\", \"twelfth_year_of_passing\": \"2021\"}','{\"projects\": [{\"link\": \"https://github.com/arjunsharma_cs/ecommerce\", \"title\": \"E-commerce Platform\", \"description\": \"Developed a full-stack e-commerce site using MERN stack.\"}, {\"link\": \"https://github.com/arjunsharma_cs/image_classifier\", \"title\": \"Image Classifier\", \"description\": \"Built a CNN model for image classification of common objects.\"}], \"internships\": [{\"title\": \"Software Development Intern\", \"company\": \"Innovatech Solutions\", \"duration\": \"\", \"description\": \"Contributed to backend development of a cloud-based CRM system.\"}], \"certificates\": [{\"name\": \"\", \"year\": \"\", \"platform\": \"\"}]}','{\"area_of_interest\": \"php developer\", \"programming_skills\": [\"sdfvs\", \"c++\"]}','{\"photo_link\": \"https://www.w3schools.com/images/w3schools_green.jpg\", \"tenth_marksheet_link\": \"https://example.com/docs/arjun_10th.pdf\", \"other_certificate_link\": \"https://example.com/docs/arjun_internship.pdf\", \"twelfth_marksheet_link\": \"https://example.com/docs/arjun_12th.pdf\", \"jee_main_scorecard_link\": \"https://example.com/docs/arjun_jee_mains.pdf\", \"internship_certificate_link\": \"https://example.com/docs/arjun_internship.pdf\", \"jee_advanced_scorecard_link\": \"https://example.com/docs/arjun_jee_adv.pdf\"}','2025-07-08 05:00:00','2025-07-10 04:48:17');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` char(36) NOT NULL DEFAULT (uuid()),
  `user_email` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `role_id` int NOT NULL,
  `oauth_provider` enum('google','linkedin') DEFAULT NULL,
  `oauth_id` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_verified` tinyint(1) DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  KEY `role_id` (`role_id`),
  KEY `idx_user_email` (`user_email`),
  KEY `idx_oauth_provider_id` (`oauth_provider`,`oauth_id`),
  KEY `idx_user_active` (`is_active`),
  KEY `idx_user_verified` (`is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('06053072-5b14-11f0-b6d3-cc4740c7c70f','test.recruiter@iiitmanipur.ac.in','Test recruiter',2,NULL,NULL,1,0,NULL,'2025-07-07 09:23:20','2025-07-07 09:23:20'),('3fb133f3-5aa1-11f0-b6d3-cc4740c7c70f','test.student@example.com','Test Student',1,NULL,NULL,1,0,NULL,'2025-07-06 19:41:45','2025-07-06 19:41:45'),('5327a8a3-5aec-11f0-b6d3-cc4740c7c70f','test.student@iiitmanipur.ac.in','Test Student',1,NULL,NULL,1,0,NULL,'2025-07-07 04:39:10','2025-07-07 04:39:10'),('8264c6df-5a8b-11f0-b6d3-cc4740c7c70f','recruiter1@example.com','Recruiter One',3,'google','recruiter1_google',1,1,NULL,'2025-07-06 17:06:08','2025-07-06 17:06:08'),('8264c75b-5a8b-11f0-b6d3-cc4740c7c70f','recruiter2@example.com','Recruiter Two',3,'linkedin','recruiter2_linkedin',1,0,NULL,'2025-07-06 17:06:08','2025-07-06 17:06:08'),('8264c7d7-5a8b-11f0-b6d3-cc4740c7c70f','coordinator1@example.com','Coordinator One',4,'google','coordinator1_google',1,1,NULL,'2025-07-06 17:06:08','2025-07-06 17:06:08'),('8264c8b5-5a8b-11f0-b6d3-cc4740c7c70f','coordinator2@example.com','Coordinator Two',4,'linkedin','coordinator2_linkedin',1,0,NULL,'2025-07-06 17:06:08','2025-07-06 17:06:08');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Table structure for table `verifications`
--

DROP TABLE IF EXISTS `verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verifications` (
  `verified_entity_id` char(36) NOT NULL,
  `verified_entity_type` enum('user','event','student','recruiter') NOT NULL,
  `verified_by_user_id` char(36) DEFAULT NULL,
  `verified_on` datetime DEFAULT NULL,
  `status` enum('draft','pending','verified','resubmit','blocked') DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`verified_entity_id`),
  KEY `idx_verified_entity` (`verified_entity_type`,`verified_entity_id`),
  KEY `idx_verified_by_user_id` (`verified_by_user_id`),
  KEY `idx_verified_on` (`verified_on`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verifications`
--

/*!40000 ALTER TABLE `verifications` DISABLE KEYS */;
INSERT INTO `verifications` VALUES ('06053072-5b14-11f0-b6d3-cc4740c7c70f','recruiter','5327d143-5aec-11f0-b6d3-cc4740c7c70v','2025-07-07 00:00:00','verified','Verified by Coordinator','2025-07-07 09:23:20','2025-07-07 12:13:15'),('123e4567-e89b-12d3-a456-426614174000','recruiter',NULL,NULL,'pending','Recruiter data saved/updated. Ready for verification submission.','2025-07-08 16:45:07','2025-07-10 02:50:26'),('5327a8a3-5aec-11f0-b6d3-cc4740c7c70f','student','5327d143-5aec-11f0-b6d3-cc4740c7c70v','2025-07-07 00:00:00','verified','Verified by Coordinator','2025-07-07 04:39:10','2025-07-07 10:02:44'),('a1b2c3d4-e5f6-7890-1234-567890abcdef','student',NULL,NULL,'pending','test','2025-07-10 05:08:39','2025-07-10 05:08:39'),('c6b1016a-5c9b-11f0-99ba-cc4740c7c70f','event','NULL',NULL,'pending','DAta saved click \"Verification\" btn to verify your and proceed futher','2025-07-09 08:12:14','2025-07-09 08:12:14'),('dae3c66c-5c83-11f0-99ba-cc4740c7c70f','event',NULL,NULL,'draft','post it for verification','2025-07-09 05:18:01','2025-07-09 05:18:01'),('eaaac994-5ca9-11f0-99ba-cc4740c7c70f','event','dae3c66c-5c83-11f0-99ba-cc4740c7c70f','2025-07-11 00:00:00','verified','asdjfgdfdasajfnv','2025-07-09 09:50:33','2025-07-09 09:50:33');
/*!40000 ALTER TABLE `verifications` ENABLE KEYS */;

--
-- Dumping routines for database 'tnpdb'
--
/*!50003 DROP PROCEDURE IF EXISTS `GetRecruiterDetailsById` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRecruiterDetailsById`(
    IN p_recruiter_id CHAR(36)
)
BEGIN
    -- Select all columns from the RecruitersDetailsPreview view
    -- where the RecruiterID matches the provided p_recruiter_id.
    SELECT
        RecruiterID,
        PrimaryContactName,
        PrimaryContactPosition,
        PrimaryContactEmail,
        PrimaryContactPhone,
        PrimaryContactLinkedInProfile,
        AlternateContactName,
        AlternateContactPosition,
        AlternateContactEmail,
        AlternateContactPhone,
        AlternateContactLinkedInProfile,
        Remark,
        CompanyDetails,
        CreatedAt,
        LastUpdatedAt
    FROM
        RecruitersDetailsPreview
    WHERE
        RecruiterID = p_recruiter_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetRecruiterList` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRecruiterList`()
BEGIN
 SELECT * FROM RecruiterList;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetStudentDetailsByID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentDetailsByID`(
    IN p_StudentID CHAR(36)
)
BEGIN
    SELECT 
        StudentID,
        RollNumber,
        StudentName,
        StudentCategory,
        DateOfBirth,
        Gender,
        AcademicProgram,
        Department,
        CurrentSemester,
        CurrentCPI,
        AdmissionYear,
        PassingYear,
        BloodGroup,
        PhoneNumber,
        Locality,
        City,
        State,
        Country,
        Pincode,
        PlacementInterest,
        StudentComments,
        PersonalDetails,
        EducationDetails,
        Experiences,
        Skills,
        Documents,
        CreatedAt,
        LastUpdatedAt
    FROM StudentDetailsPreview
    WHERE StudentID = p_StudentID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetStudentList` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentList`()
BEGIN
 SELECT * FROM StudentsList;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetVerificationStatusById` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetVerificationStatusById`(
    IN p_VerificationStatus_id CHAR(36)
)
BEGIN
    SELECT
      status,verified_on,notes
    FROM
        VerificationStatus
    WHERE
        verified_entity_id = p_VerificationStatus_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `InsertEventDraftByRecruiterId` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertEventDraftByRecruiterId`(
    IN p_event_organiser_id CHAR(36),
    IN p_reference_id VARCHAR(255),
    IN p_event_title VARCHAR(255),
    IN p_event_type VARCHAR(100),
    IN p_event_description TEXT,
    IN p_event_document JSON,
    IN p_event_location VARCHAR(255)
)
BEGIN
    INSERT INTO events (
        event_id,
        event_organiser_id,
        reference_id,
        event_title,
        event_type,
        event_description,
        event_status,
        event_document,
        event_location
    ) VALUES (
        UUID(),
        p_event_organiser_id,
        p_reference_id,
        p_event_title,
        p_event_type,
        p_event_description,
        'draft',
        p_event_document,
        p_event_location
    );
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ManageEventById` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ManageEventById`(
    IN p_event_id CHAR(36),
    IN p_event_start_date DATETIME,
    IN p_event_end_date DATETIME,
    IN p_event_status ENUM('opened', 'closed'),
    IN p_max_participants VARCHAR(255)
)
BEGIN
    -- Update the specified event details based on the event_id
    UPDATE `events`
    SET
        `event_start_date` = p_event_start_date,
        `event_end_date` = p_event_end_date,
        `event_status` = p_event_status,
        `max_participants` = p_max_participants,
        `updated_at` = CURRENT_TIMESTAMP -- Automatically update the updated_at timestamp
    WHERE
        `event_id` = p_event_id;

    -- You can add a check here to see if the update was successful
    -- For example, by checking ROW_COUNT()
    IF ROW_COUNT() = 0 THEN
        -- Optionally, raise an error or log a message if no rows were updated
        -- This typically means the p_event_id did not exist
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Event not found or no changes made.';
    END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ManageParticipantEntry` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ManageParticipantEntry`(
    IN p_participant_id CHAR(36),
    IN p_event_id CHAR(36),
    IN p_status ENUM(
        'registered',
        'approved',
        'rejected',
        'shortlisted',
        'selected',
        'attended',
        'blocked',
        'cancelled',
        'waitinglist'
    ),
    IN p_message TEXT,
    IN p_document VARCHAR(255) -- New parameter for the document link
)
BEGIN
    INSERT INTO `participants` (
        `participant_id`,
        `event_id`,
        `registration_datetime`,
        `status`,
        `message`,
        `document` -- Included in INSERT
    ) VALUES (
        p_participant_id,
        p_event_id,
        CURRENT_TIMESTAMP,
        'registered',
        p_message,
        p_document -- Value for document
    )
    ON DUPLICATE KEY UPDATE
        `status` = VALUES(`status`),
        `message` = VALUES(`message`),
        `document` = VALUES(`document`), -- Included in UPDATE
        `updated_at` = CURRENT_TIMESTAMP;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `UpdateStudentById` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateStudentById`(
    IN p_student_id CHAR(36),
    IN p_roll_no VARCHAR(20),
    IN p_name VARCHAR(255),
    IN p_category ENUM('general', 'obc', 'sc', 'st', 'ews', 'pwd'),
    IN p_date_of_birth DATE,
    IN p_gender ENUM('male', 'female', 'other'),
    IN p_blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    IN p_phone_number VARCHAR(15),
    IN p_locality VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(100),
    IN p_country VARCHAR(100),
    IN p_pincode VARCHAR(10),
    IN p_program VARCHAR(100),
    IN p_department VARCHAR(100),
    IN p_current_semester INT,
    IN p_cpi DECIMAL(4,2),
    IN p_year_of_admission INT,
    IN p_year_of_passing INT,
    IN p_placement_interest TINYINT(1),
    IN p_comments TEXT,
    IN p_personal_details_json JSON,
    IN p_education_details_json JSON,
    IN p_experiences_json JSON,
    IN p_skills_json JSON,
    IN p_documents_json JSON
)
BEGIN
    -- Ensure p_student_id is not NULL
    IF p_student_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'student_id cannot be NULL';
    END IF;

    UPDATE students
    SET
        roll_no = COALESCE(p_roll_no, roll_no),
        name = COALESCE(p_name, name),
        category = COALESCE(p_category, category),
        date_of_birth = COALESCE(p_date_of_birth, date_of_birth),
        gender = COALESCE(p_gender, gender),
        blood_group = COALESCE(p_blood_group, blood_group),
        phone_number = COALESCE(p_phone_number, phone_number),
        locality = COALESCE(p_locality, locality),
        city = COALESCE(p_city, city),
        state = COALESCE(p_state, state),
        country = COALESCE(p_country, country),
        pincode = COALESCE(p_pincode, pincode),
        program = COALESCE(p_program, program),
        department = COALESCE(p_department, department),
        current_semester = COALESCE(p_current_semester, current_semester),
        cpi = COALESCE(p_cpi, cpi),
        year_of_admission = COALESCE(p_year_of_admission, year_of_admission),
        year_of_passing = COALESCE(p_year_of_passing, year_of_passing),
        placement_interest = COALESCE(p_placement_interest, placement_interest),
        comments = COALESCE(p_comments, comments),
        personal_details_json = COALESCE(p_personal_details_json, personal_details_json),
        education_details_json = COALESCE(p_education_details_json, education_details_json),
        experiences_json = COALESCE(p_experiences_json, experiences_json),
        additional_details_json = COALESCE(p_skills_json, additional_details_json),
        documents_json = COALESCE(p_documents_json, documents_json),
        updated_at = CURRENT_TIMESTAMP
    WHERE student_id = p_student_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `UpsertRecruiter` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpsertRecruiter`(
    IN p_recruiter_id CHAR(36),
    IN p_primary_contact_name VARCHAR(100),
    IN p_primary_contact_position VARCHAR(100),
    IN p_primary_contact_email VARCHAR(100),
    IN p_primary_contact_phone VARCHAR(20),
    IN p_primary_contact_linkedin_profile VARCHAR(255),
    IN p_alt_contact_name VARCHAR(100),
    IN p_alt_contact_position VARCHAR(100),
    IN p_alt_contact_email VARCHAR(100),
    IN p_alt_contact_phone VARCHAR(20),
    IN p_alt_contact_linkedin_profile VARCHAR(255),
    IN p_remark TEXT,
    IN p_company_details_json JSON
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    -- Start transaction for data consistency
    START TRANSACTION;

    -- Use INSERT ... ON DUPLICATE KEY UPDATE for atomic upsert
    INSERT INTO recruiters (
        recruiter_id,
        primary_contact_name,
        primary_contact_position,
        primary_contact_email,
        primary_contact_phone,
        primary_contact_linkedin_profile,
        alt_contact_name,
        alt_contact_position,
        alt_contact_email,
        alt_contact_phone,
        alt_contact_linkedin_profile,
        remark,
        company_details_json,
        created_at,
        updated_at
    ) VALUES (
        p_recruiter_id,
        p_primary_contact_name,
        p_primary_contact_position,
        p_primary_contact_email,
        p_primary_contact_phone,
        p_primary_contact_linkedin_profile,
        p_alt_contact_name,
        p_alt_contact_position,
        p_alt_contact_email,
        p_alt_contact_phone,
        p_alt_contact_linkedin_profile,
        p_remark,
        p_company_details_json,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
    ON DUPLICATE KEY UPDATE
        primary_contact_name = VALUES(primary_contact_name),
        primary_contact_position = VALUES(primary_contact_position),
        primary_contact_email = VALUES(primary_contact_email),
        primary_contact_phone = VALUES(primary_contact_phone),
        primary_contact_linkedin_profile = VALUES(primary_contact_linkedin_profile),
        alt_contact_name = VALUES(alt_contact_name),
        alt_contact_position = VALUES(alt_contact_position),
        alt_contact_email = VALUES(alt_contact_email),
        alt_contact_phone = VALUES(alt_contact_phone),
        alt_contact_linkedin_profile = VALUES(alt_contact_linkedin_profile),
        remark = VALUES(remark),
        company_details_json = VALUES(company_details_json),
        updated_at = CURRENT_TIMESTAMP;

    -- Handle verifications table with single upsert operation
    -- Use composite key approach for entity_id + entity_type
    INSERT INTO verifications (
        verified_entity_id,
        verified_entity_type,
        verified_by_user_id,
        verified_on,
        status,
        notes,
        created_at,
        updated_at
    ) VALUES (
        p_recruiter_id,
        'recruiter',
        NULL,
        NULL,
        'draft',
        'Successfully saved! Click "Post" to send for verification',
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
    ON DUPLICATE KEY UPDATE
        verified_by_user_id = NULL,
        verified_on = NULL,
        status = 'draft',
        notes = 'Recruiter data saved/updated. Ready for verification submission.',
        updated_at = CURRENT_TIMESTAMP;

    COMMIT;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `EventDetailsView`
--

/*!50001 DROP VIEW IF EXISTS `EventDetailsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `EventDetailsView` AS select `e`.`event_id` AS `EventId`,`e`.`event_title` AS `EventTitle`,`e`.`event_type` AS `EventType`,`e`.`event_status` AS `EventStatus`,`e`.`event_location` AS `EventLocation`,`e`.`event_description` AS `EventDescription`,`e`.`event_document` AS `EventDocument`,`e`.`event_start_date` AS `EventStartDate`,`e`.`event_end_date` AS `EventEndDate` from `events` `e` where (`e`.`event_status` in ('opened','closed')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `EventsRecruiterDetails`
--

/*!50001 DROP VIEW IF EXISTS `EventsRecruiterDetails`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `EventsRecruiterDetails` AS select `e`.`event_id` AS `EventId`,`e`.`event_title` AS `EventTitle`,`e`.`event_type` AS `EventType`,`e`.`event_description` AS `EventDescription`,`e`.`event_document` AS `EventDocument`,`e`.`event_location` AS `EventLocation`,`e`.`event_start_date` AS `EventStartDate`,`e`.`event_end_date` AS `EventEndDate`,`v`.`status` AS `VerificationStatus`,`v`.`verified_on` AS `VerifiedOn`,`r`.`recruiter_id` AS `RecruiterId`,`r`.`primary_contact_name` AS `PrimaryContactName`,`r`.`primary_contact_position` AS `PrimaryContactPosition`,`r`.`primary_contact_email` AS `PrimaryContactEmail`,`r`.`primary_contact_phone` AS `PrimaryContactPhone`,`r`.`primary_contact_linkedin_profile` AS `PrimaryContactLinkedinProfile`,`r`.`alt_contact_name` AS `AltContactName`,`r`.`alt_contact_position` AS `AltContactPosition`,`r`.`alt_contact_email` AS `AltContactEmail`,`r`.`alt_contact_phone` AS `AltContactPhone`,`r`.`alt_contact_linkedin_profile` AS `AltContactLinkedinProfile`,`r`.`company_details_json` AS `CompanyDetailsJson`,`e`.`created_at` AS `CreatedAt`,`e`.`updated_at` AS `UpdatedAt` from ((`events` `e` join `verifications` `v` on(((`e`.`event_id` = `v`.`verified_entity_id`) and (`v`.`verified_entity_type` = 'event') and (`v`.`status` <> 'draft')))) left join `recruiters` `r` on((`e`.`event_organiser_id` = `r`.`recruiter_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `RecruiterJobDetailById`
--

/*!50001 DROP VIEW IF EXISTS `RecruiterJobDetailById`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `RecruiterJobDetailById` AS select `e`.`event_id` AS `EventID`,`e`.`event_organiser_id` AS `OrganiserId`,`e`.`event_title` AS `Event`,`e`.`event_type` AS `Type`,`e`.`created_at` AS `Posted`,`e`.`event_location` AS `Location`,`e`.`event_description` AS `Description`,`e`.`event_document` AS `AttachedDocumens`,`v`.`status` AS `Status`,`v`.`verified_on` AS `StatusOn`,`v`.`notes` AS `Message` from (`events` `e` join `verifications` `v` on((`e`.`event_id` = `v`.`verified_entity_id`))) where (`v`.`verified_entity_type` = 'event') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `RecruiterJobsList`
--

/*!50001 DROP VIEW IF EXISTS `RecruiterJobsList`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `RecruiterJobsList` AS select `e`.`event_id` AS `EventID`,`e`.`event_organiser_id` AS `OrganiserId`,`e`.`event_title` AS `Event`,`e`.`event_type` AS `Type`,`e`.`created_at` AS `Posted`,`e`.`event_location` AS `Location`,`v`.`status` AS `Status` from (`events` `e` join `verifications` `v` on((`e`.`event_id` = `v`.`verified_entity_id`))) where (`v`.`verified_entity_type` = 'event') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `RecruiterList`
--

/*!50001 DROP VIEW IF EXISTS `RecruiterList`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `RecruiterList` AS select `r`.`recruiter_id` AS `recruiterId`,`r`.`primary_contact_name` AS `primaryContactName`,`r`.`primary_contact_position` AS `primaryContactPosition`,`r`.`primary_contact_email` AS `primaryContactEmail`,`r`.`primary_contact_phone` AS `primaryContactPhone`,`r`.`primary_contact_linkedin_profile` AS `primaryContactLinkedinProfile`,`r`.`alt_contact_name` AS `altContactName`,`r`.`alt_contact_position` AS `altContactPosition`,`r`.`alt_contact_email` AS `altContactEmail`,`r`.`alt_contact_phone` AS `altContactPhone`,`r`.`alt_contact_linkedin_profile` AS `altContactLinkedinProfile`,`r`.`remark` AS `remark`,`r`.`company_details_json` AS `CompanyDetailsJson`,`v`.`status` AS `Status`,`r`.`created_at` AS `createdAt`,`r`.`updated_at` AS `updatedAt` from (`recruiters` `r` join `verifications` `v` on(((`r`.`recruiter_id` = `v`.`verified_entity_id`) and (`v`.`verified_entity_type` = 'recruiter')))) where (`v`.`status` <> 'draft') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `RecruitersDetailsPreview`
--

/*!50001 DROP VIEW IF EXISTS `RecruitersDetailsPreview`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `RecruitersDetailsPreview` AS select `recruiters`.`recruiter_id` AS `RecruiterID`,`recruiters`.`primary_contact_name` AS `PrimaryContactName`,`recruiters`.`primary_contact_position` AS `PrimaryContactPosition`,`recruiters`.`primary_contact_email` AS `PrimaryContactEmail`,`recruiters`.`primary_contact_phone` AS `PrimaryContactPhone`,`recruiters`.`primary_contact_linkedin_profile` AS `PrimaryContactLinkedInProfile`,`recruiters`.`alt_contact_name` AS `AlternateContactName`,`recruiters`.`alt_contact_position` AS `AlternateContactPosition`,`recruiters`.`alt_contact_email` AS `AlternateContactEmail`,`recruiters`.`alt_contact_phone` AS `AlternateContactPhone`,`recruiters`.`alt_contact_linkedin_profile` AS `AlternateContactLinkedInProfile`,`recruiters`.`remark` AS `Remark`,`recruiters`.`company_details_json` AS `CompanyDetails`,`recruiters`.`created_at` AS `CreatedAt`,`recruiters`.`updated_at` AS `LastUpdatedAt` from `recruiters` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `StudentDetailsPreview`
--

/*!50001 DROP VIEW IF EXISTS `StudentDetailsPreview`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `StudentDetailsPreview` AS select `students`.`student_id` AS `StudentID`,`students`.`roll_no` AS `RollNumber`,`students`.`name` AS `StudentName`,`students`.`category` AS `StudentCategory`,`students`.`date_of_birth` AS `DateOfBirth`,`students`.`gender` AS `Gender`,`students`.`program` AS `AcademicProgram`,`students`.`department` AS `Department`,`students`.`current_semester` AS `CurrentSemester`,`students`.`cpi` AS `CurrentCPI`,`students`.`year_of_admission` AS `AdmissionYear`,`students`.`year_of_passing` AS `PassingYear`,`students`.`blood_group` AS `BloodGroup`,`students`.`phone_number` AS `PhoneNumber`,`students`.`locality` AS `Locality`,`students`.`city` AS `City`,`students`.`state` AS `State`,`students`.`country` AS `Country`,`students`.`pincode` AS `Pincode`,`students`.`placement_interest` AS `PlacementInterest`,`students`.`comments` AS `StudentComments`,`students`.`personal_details_json` AS `PersonalDetails`,`students`.`education_details_json` AS `EducationDetails`,`students`.`experiences_json` AS `Experiences`,`students`.`additional_details_json` AS `Skills`,`students`.`documents_json` AS `Documents`,`students`.`created_at` AS `CreatedAt`,`students`.`updated_at` AS `LastUpdatedAt` from `students` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `StudentEventApplicationsView`
--

/*!50001 DROP VIEW IF EXISTS `StudentEventApplicationsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `StudentEventApplicationsView` AS select `p`.`participant_entry_id` AS `participantEntryId`,`p`.`participant_id` AS `studentId`,`p`.`event_id` AS `eventId`,`p`.`registration_datetime` AS `registrationDatetime`,`p`.`status` AS `participationStatus`,`p`.`message` AS `applicationMessage`,`p`.`document` AS `applicationDocumentLink`,`s`.`roll_no` AS `studentRollNo`,`s`.`name` AS `studentName`,`s`.`program` AS `studentProgram`,`s`.`department` AS `studentDepartment`,`s`.`cpi` AS `studentCPI`,`s`.`phone_number` AS `studentPhoneNumber`,`e`.`event_title` AS `eventTitle`,`e`.`event_type` AS `eventType`,`e`.`event_location` AS `eventLocation`,`e`.`event_start_date` AS `eventStartDate`,`e`.`event_end_date` AS `eventEndDate`,`e`.`event_status` AS `eventStatus`,`p`.`created_at` AS `participantCreatedAt`,`p`.`updated_at` AS `participantUpdatedAt` from ((`participants` `p` join `students` `s` on((`s`.`student_id` = `p`.`participant_id`))) join `events` `e` on((`p`.`event_id` = `e`.`event_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `StudentsList`
--

/*!50001 DROP VIEW IF EXISTS `StudentsList`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `StudentsList` AS select `s`.`student_id` AS `studentId`,`s`.`roll_no` AS `RollNo`,`s`.`name` AS `Name`,`s`.`category` AS `Category`,`s`.`date_of_birth` AS `DateOfBirth`,`s`.`gender` AS `Gender`,`s`.`program` AS `Program`,`s`.`department` AS `Department`,`s`.`current_semester` AS `CurrentSemester`,`s`.`cpi` AS `CPI`,`s`.`year_of_admission` AS `YearOfAdmission`,`s`.`year_of_passing` AS `YearOfPassing`,`s`.`blood_group` AS `BloodGroup`,`s`.`phone_number` AS `PhoneNumber`,`s`.`locality` AS `Locality`,`s`.`city` AS `City`,`s`.`state` AS `State`,`s`.`country` AS `Country`,`s`.`pincode` AS `Pincode`,`s`.`placement_interest` AS `PlacementInterest`,`s`.`comments` AS `Comments`,`s`.`personal_details_json` AS `PersonalDetailsJson`,`s`.`education_details_json` AS `EducationDetailsJson`,`s`.`experiences_json` AS `ExperiencesJson`,`s`.`additional_details_json` AS `AdditionalDetailsJson`,`s`.`documents_json` AS `DocumentsJson`,`v`.`status` AS `Status`,`s`.`created_at` AS `created_at`,`s`.`updated_at` AS `updated_at` from (`students` `s` join `verifications` `v` on(((`s`.`student_id` = `v`.`verified_entity_id`) and (`v`.`verified_entity_type` = 'student')))) where (`v`.`status` <> 'draft') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `VerificationStatus`
--

/*!50001 DROP VIEW IF EXISTS `VerificationStatus`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `VerificationStatus` AS select `verifications`.`verified_entity_id` AS `verified_entity_id`,`verifications`.`verified_on` AS `verified_on`,`verifications`.`status` AS `status`,`verifications`.`notes` AS `notes` from `verifications` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-10 21:50:04