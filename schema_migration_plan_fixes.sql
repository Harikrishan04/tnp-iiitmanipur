-- TNP Schema migration: plan fixes (event_status, procedures, FKs, data cleanup, views)
-- Run this against tnpdb after backing up. Execute in order.

USE tnpdb;

-- =============================================================================
-- 1. CRITICAL: event_status enum + procedures
-- =============================================================================

-- 1.1 Add 'draft' to events.event_status
ALTER TABLE `events`
  MODIFY COLUMN `event_status` ENUM('draft','opened','closed') DEFAULT NULL
  COMMENT 'admin can open or close the event for students; draft = not yet opened';

-- 1.2 ManageEventById: allow admin to set opened/closed (draft remains for recruiter flow only)
-- No change needed; procedure already uses ENUM('opened','closed').

-- 1.3 UpsertEvent: add p_event_status and use it
DROP PROCEDURE IF EXISTS `UpsertEvent`;
DELIMITER ;;
CREATE PROCEDURE `UpsertEvent`(
    IN p_event_id CHAR(36),
    IN p_event_organiser_id CHAR(36),
    IN p_event_title VARCHAR(255),
    IN p_event_type VARCHAR(100),
    IN p_event_description TEXT,
    IN p_event_document JSON,
    IN p_event_location VARCHAR(255),
    IN p_event_status VARCHAR(20)
)
BEGIN
    DECLARE v_actual_event_id CHAR(36);
    DECLARE v_event_status VARCHAR(20);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    SET v_event_status = NULLIF(TRIM(p_event_status), '');
    IF v_event_status IS NULL THEN
        SET v_event_status = 'draft';
    END IF;
    IF v_event_status NOT IN ('draft','opened','closed') THEN
        SET v_event_status = 'draft';
    END IF;

    START TRANSACTION;

    SET v_actual_event_id = COALESCE(p_event_id, UUID());

    INSERT INTO events (
        event_id,
        event_organiser_id,
        reference_id,
        event_title,
        event_type,
        event_description,
        event_document,
        event_location,
        event_start_date,
        event_end_date,
        event_status,
        max_participants,
        created_at,
        updated_at
    ) VALUES (
        v_actual_event_id,
        p_event_organiser_id,
        NULL,
        p_event_title,
        p_event_type,
        p_event_description,
        p_event_document,
        p_event_location,
        NULL,
        NULL,
        v_event_status,
        NULL,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
    ON DUPLICATE KEY UPDATE
        event_title = VALUES(event_title),
        event_type = VALUES(event_type),
        event_description = VALUES(event_description),
        event_document = VALUES(event_document),
        event_location = VALUES(event_location),
        event_status = v_event_status,
        updated_at = CURRENT_TIMESTAMP;

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
        v_actual_event_id,
        'event',
        NULL,
        NULL,
        'draft',
        'Successfully saved! Click "Post Verification" to send for verification',
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
    ON DUPLICATE KEY UPDATE
        status = 'draft',
        notes = 'Event data saved/updated. Click "Post Verification" to send for verification',
        updated_at = CURRENT_TIMESTAMP;

    COMMIT;
END ;;
DELIMITER ;

-- InsertEventDraftByRecruiterId already uses 'draft'; no change needed after enum fix.

-- =============================================================================
-- 2. HIGH: ManageParticipantEntry – use p_status
-- =============================================================================

DROP PROCEDURE IF EXISTS `ManageParticipantEntry`;
DELIMITER ;;
CREATE PROCEDURE `ManageParticipantEntry`(
    IN p_participant_id CHAR(36),
    IN p_event_id CHAR(36),
    IN p_status VARCHAR(20),
    IN p_message TEXT,
    IN p_document VARCHAR(255)
)
BEGIN
    DECLARE v_status VARCHAR(20);
    SET v_status = COALESCE(NULLIF(TRIM(p_status), ''), 'registered');
    IF v_status NOT IN ('registered','approved','rejected','shortlisted','selected','attended','blocked','cancelled','waitinglist') THEN
        SET v_status = 'registered';
    END IF;

    INSERT INTO `participants` (
        `participant_id`,
        `event_id`,
        `registration_datetime`,
        `status`,
        `message`,
        `document`
    ) VALUES (
        p_participant_id,
        p_event_id,
        CURRENT_TIMESTAMP,
        v_status,
        p_message,
        p_document
    )
    ON DUPLICATE KEY UPDATE
        `status` = v_status,
        `message` = VALUES(`message`),
        `document` = VALUES(`document`),
        `updated_at` = CURRENT_TIMESTAMP;
END ;;
DELIMITER ;

-- =============================================================================
-- 3. HIGH: Clean bad data (empty recruiter_id / verified_entity_id)
-- =============================================================================

DELETE FROM verifications WHERE verified_entity_id = '' OR verified_entity_id IS NULL;
DELETE FROM recruiters WHERE recruiter_id = '' OR recruiter_id IS NULL;

-- =============================================================================
-- 4. MEDIUM: UpdateStudentById – p_placement_interest VARCHAR, date_of_birth column
-- =============================================================================

-- 4.1 Change students.date_of_birth to DATE (optional: run only if you want strict type)
ALTER TABLE `students`
  MODIFY COLUMN `date_of_birth` DATE DEFAULT NULL;

-- 4.2 UpdateStudentById: p_placement_interest as VARCHAR(255)
DROP PROCEDURE IF EXISTS `UpdateStudentById`;
DELIMITER ;;
CREATE PROCEDURE `UpdateStudentById`(
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
    IN p_placement_interest VARCHAR(255),
    IN p_comments TEXT,
    IN p_personal_details_json JSON,
    IN p_education_details_json JSON,
    IN p_experiences_json JSON,
    IN p_skills_json JSON,
    IN p_documents_json JSON
)
BEGIN
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

-- =============================================================================
-- 5. HIGH: Add missing foreign keys
-- See schema_migration_plan_fixes_fks.sql - run that file after ensuring
-- no orphan rows (events.event_organiser_id in recruiters, etc.).
-- =============================================================================

-- =============================================================================
-- 6. LOW: Fix view RecruiterJobDetailById – AttachedDocumens -> AttachedDocuments
-- =============================================================================

DROP VIEW IF EXISTS `RecruiterJobDetailById`;
CREATE VIEW `RecruiterJobDetailById` AS
SELECT
    e.event_id AS EventID,
    e.event_organiser_id AS OrganiserId,
    e.event_title AS Event,
    e.event_type AS Type,
    e.created_at AS Posted,
    e.event_location AS Location,
    e.event_description AS Description,
    e.event_document AS AttachedDocuments,
    v.status AS Status,
    v.verified_on AS StatusOn,
    v.notes AS Message
FROM events e
JOIN verifications v ON e.event_id = v.verified_entity_id AND v.verified_entity_type = 'event';
