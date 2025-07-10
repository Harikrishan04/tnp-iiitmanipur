-- Improved TNP Database Schema
-- Enhanced with better constraints, indexing, and data integrity

USE tnpdb;

-- =============================================
-- ROLES AND PERMISSIONS
-- =============================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_name (name)
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_permission_name (name),
    INDEX idx_module_action (module, action)
);

-- Junction table for role-permission relationships
CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX idx_role_permission (role_id, permission_id)
);

-- =============================================
-- USERS TABLE
-- =============================================

CREATE TABLE users (
    user_id CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL UNIQUE,
    user_name VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    oauth_provider ENUM('google', 'linkedin') DEFAULT NULL,
    oauth_id VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_user_email (user_email),
    INDEX idx_oauth_provider_id (oauth_provider, oauth_id),
    INDEX idx_user_active (is_active),
    INDEX idx_user_verified (is_verified)
);

-- =============================================
-- STUDENTS TABLE
-- =============================================

CREATE TABLE students (
    student_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    roll_no VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category ENUM('general', 'obc', 'sc', 'st', 'ews', 'pwd') DEFAULT 'general',
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other') DEFAULT 'other',
    program VARCHAR(100),
    department VARCHAR(100),
    current_semester INT DEFAULT 1,
    cpi DECIMAL(4, 2) DEFAULT 0.00,
    year_of_admission INT,
    year_of_passing INT,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    phone_number VARCHAR(15),
    locality VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'India',
    pincode VARCHAR(10),
    placement_interest VARCHAR(255) DEFAULT 'Not Specified',
    comments TEXT,
    personal_details_json JSON,
    education_details_json JSON,
    experiences_json JSON,
    additional_details_json JSON,
    documents_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_roll_no (roll_no),
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_department (department),
    INDEX idx_program (program),
    INDEX idx_year_of_passing (year_of_passing),
    INDEX idx_placement_interest (placement_interest),
    INDEX idx_personal_email ((CAST(personal_details_json->>'$.personal_email' AS CHAR(255)) COLLATE utf8mb4_bin)),
    INDEX idx_jee_mains_rank ((CAST(education_details_json->'$.jee_mains_rank' AS SIGNED))),
    INDEX idx_tenth_score ((CAST(education_details_json->'$.tenth_score' AS DECIMAL(5,2)))),
    INDEX idx_twelfth_score ((CAST(education_details_json->'$.twelfth_score' AS DECIMAL(5,2))))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- RECRUITERS TABLE
-- =============================================

CREATE TABLE recruiters (
    recruiter_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    primary_contact_name VARCHAR(100) NOT NULL,
    primary_contact_position VARCHAR(100),
    primary_contact_email VARCHAR(100) NOT NULL UNIQUE,
    primary_contact_phone VARCHAR(20),
    primary_contact_linkedin_profile VARCHAR(255) DEFAULT NULL,
    alt_contact_name VARCHAR(100) DEFAULT NULL,
    alt_contact_position VARCHAR(100) DEFAULT NULL,
    alt_contact_email VARCHAR(100) DEFAULT NULL,
    alt_contact_phone VARCHAR(20) DEFAULT NULL,
    alt_contact_linkedin_profile VARCHAR(255) DEFAULT NULL,
    recruiter_status ENUM(
        'incomplete',
        'complete',
        'pending_verification',
        'verified',
        'resubmit',
        'blocked'
    ) DEFAULT 'incomplete',
    remark TEXT,
    company_details_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_primary_contact_email (primary_contact_email),
    INDEX idx_recruiter_status (recruiter_status),
    INDEX idx_primary_contact_name (primary_contact_name),
    INDEX idx_company_name ((CAST(company_details_json->>'$.company_name' AS VARCHAR(255)) COLLATE utf8mb4_bin)),
    INDEX idx_company_website ((CAST(company_details_json->>'$.company_website' AS VARCHAR(255)) COLLATE utf8mb4_bin)),
    INDEX idx_company_city ((CAST(company_details_json->>'$.address.city' AS VARCHAR(100)) COLLATE utf8mb4_bin))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- COORDINATORS TABLE
-- =============================================

CREATE TABLE coordinators (
    coordinator_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(15) DEFAULT NULL,
    department ENUM('CSE', 'ECE', 'CSE-AI', 'ECE-VLSI') NOT NULL,
    semester INT NOT NULL,
    designation VARCHAR(255) NOT NULL,
    team VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_email (email),
    INDEX idx_department (department),
    INDEX idx_team (team)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- EVENTS TABLE
-- =============================================

CREATE TABLE events (
    event_id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    event_organiser_id CHAR(36) NOT NULL,
    reference_id VARCHAR(255) UNIQUE DEFAULT NULL,
    event_title VARCHAR(255) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_description TEXT,
    event_status ENUM(
        'draft',
        'pending_submission',
        'submitted',
        'under_coordinator_review',
        'coordinator_approved',
        'coordinator_rejected',
        'returned_for_revision',
        'pending_admin_approval',
        'under_admin_review',
        'admin_approved',
        'admin_rejected',
        'published',
        'accepting_applications',
        'application_review',
        'applications_closed',
        'selection_in_progress',
        'completed',
        'archived',
        'temporarily_paused',
        'suspended',
        'cancelled',
        'expired'
    ) DEFAULT 'draft',
    event_document JSON,
    _event_all_links_array JSON GENERATED ALWAYS AS (JSON_VALUES(event_document, '$.*')) VIRTUAL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_organiser_id (event_organiser_id),
    INDEX idx_event_status (event_status),
    INDEX idx_event_type (event_type),
    INDEX idx_event_title (event_title),
    INDEX idx_reference_id (reference_id),
    INDEX idx_event_all_links ((CAST(`_event_all_links_array` AS CHAR(255) ARRAY)) COLLATE utf8mb4_bin)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- PARTICIPANTS TABLE
-- =============================================

CREATE TABLE participants (
    participant_entry_id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    participant_id CHAR(36) NOT NULL,
    event_id CHAR(36) NOT NULL,
    registration_datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM(
        'registered',
        'approved',
        'rejected',
        'shortlisted',
        'selected',
        'attended',
        'blocked',
        'cancelled',
        'waitlisted'
    ) DEFAULT 'registered',
    message TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_event (participant_id, event_id),
    FOREIGN KEY (participant_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    INDEX idx_participant_id (participant_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status),
    INDEX idx_registration_datetime (registration_datetime)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- EVENT SUB PROCESSES TABLE
-- =============================================

CREATE TABLE event_sub_processes (
    sub_process_id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    event_id CHAR(36) NOT NULL,
    sub_process_title VARCHAR(255) NOT NULL,
    sub_process_description TEXT,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM(
        'scheduled',
        'in_progress',
        'completed',
        'cancelled',
        'postponed'
    ) DEFAULT 'scheduled',
    location_details VARCHAR(255) DEFAULT NULL,
    responsible_coordinator_id CHAR(36) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (responsible_coordinator_id) REFERENCES coordinators(coordinator_id) ON DELETE SET NULL,
    INDEX idx_event_id (event_id),
    INDEX idx_sub_process_title (sub_process_title),
    INDEX idx_status (status),
    INDEX idx_start_datetime (start_datetime),
    INDEX idx_responsible_coordinator_id (responsible_coordinator_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- EVENT SUB PROCESS PARTICIPANTS TABLE
-- =============================================

CREATE TABLE event_sub_process_participants (
    sub_process_participant_id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    participant_id CHAR(36) NOT NULL,
    sub_process_id CHAR(36) NOT NULL,
    status ENUM(
        'invited',
        'accepted_invite',
        'declined_invite',
        'in_progress',
        'completed',
        'passed',
        'failed',
        'attended',
        'not_attended',
        'rescheduled',
        'withdrew',
        'blocked'
    ) DEFAULT 'invited',
    feedback TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_sub_process (participant_id, sub_process_id),
    FOREIGN KEY (participant_id) REFERENCES participants(participant_entry_id) ON DELETE CASCADE,
    FOREIGN KEY (sub_process_id) REFERENCES event_sub_processes(sub_process_id) ON DELETE CASCADE,
    INDEX idx_participant_id (participant_id),
    INDEX idx_sub_process_id (sub_process_id),
    INDEX idx_status (status)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- VERIFICATIONS TABLE
-- =============================================

CREATE TABLE verifications (
    verification_id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    verified_entity_id CHAR(36) NOT NULL,
    verified_entity_type ENUM('user', 'event', 'student', 'recruiter') NOT NULL,
    verified_by_user_id CHAR(36) NOT NULL,
    verified_on DATETIME NOT NULL,
    status ENUM(
        'pending',
        'verified',
        'rejected',
        'reverted'
    ) DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (verified_by_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_verified_entity (verified_entity_type, verified_entity_id),
    INDEX idx_verified_by_user_id (verified_by_user_id),
    INDEX idx_verified_on (verified_on),
    INDEX idx_status (status)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- INSERT INITIAL DATA
-- =============================================

-- Insert roles
INSERT INTO roles (id, name, description) VALUES 
(1, 'student', 'Student users who can create profile and apply for jobs after verification'),
(2, 'recruiter', 'Recruiter users who can create profile and create and manage job postings'),
(3, 'admin', 'Administrator users with full system access and job approval rights'),
(4, 'coordinator', 'Coordinator users who verify and approve student, recruiter and job profiles');

-- Insert permissions
INSERT INTO permissions (id, name, description, module, action) VALUES 
-- Profile Management Permissions
(1, 'profile.create', 'Create and setup user profile', 'profile', 'create'),
(2, 'profile.read', 'View user profiles', 'profile', 'read'),
(3, 'profile.update', 'Update own profile information', 'profile', 'update'),
(4, 'profile.delete', 'Delete user profiles', 'profile', 'delete'),
(5, 'profile.verify', 'Verify and approve student profiles', 'profile', 'verify'),
(6, 'profile.view_all', 'View all user profiles in system', 'profile', 'view_all'),
-- Event Management Permissions
(7, 'event.create', 'Create new event postings', 'event', 'create'),
(8, 'event.read', 'View event postings', 'event', 'read'),
(9, 'event.update', 'Update event postings', 'event', 'update'),
(10, 'event.delete', 'Delete event postings', 'event', 'delete'),
(11, 'event.approve', 'Approve event postings for publication', 'event', 'approve'),
(12, 'event.apply', 'Apply for event positions', 'event', 'apply'),
(13, 'event.view_all', 'View all events including drafts', 'event', 'view_all'),
(14, 'event.verify', 'Verify event postings', 'event', 'verify'),
(15, 'event.publish', 'Publish event postings', 'event', 'publish'),
-- Application Management Permissions
(16, 'application.create', 'Submit event applications', 'application', 'create'),
(17, 'application.read', 'View job applications', 'application', 'read'),
(18, 'application.view_all', 'View all applications system-wide', 'application', 'view_all'),
-- Coordinator Management Permissions
(19, 'coordinator.create', 'Create new coordinator accounts', 'coordinator', 'create'),
(20, 'coordinator.read', 'View coordinator information', 'coordinator', 'read'),
(21, 'coordinator.update', 'Update coordinator accounts', 'coordinator', 'update'),
(22, 'coordinator.delete', 'Delete coordinator accounts', 'coordinator', 'delete');

-- Insert role permissions
-- Student permissions
INSERT INTO role_permissions (role_id, permission_id) VALUES 
(1, 1), (1, 2), (1, 3), (1, 8), (1, 12), (1, 16), (1, 17);

-- Recruiter permissions
INSERT INTO role_permissions (role_id, permission_id) VALUES 
(2, 1), (2, 2), (2, 3), (2, 7), (2, 8), (2, 9), (2, 10), (2, 17), (2, 18), (2, 20);

-- Admin permissions
INSERT INTO role_permissions (role_id, permission_id) VALUES 
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8), (3, 9), (3, 10),
(3, 11), (3, 13), (3, 17), (3, 18), (3, 19), (3, 20), (3, 21), (3, 22);

-- Coordinator permissions
INSERT INTO role_permissions (role_id, permission_id) VALUES 
(4, 2), (4, 5), (4, 6), (4, 8), (4, 17), (4, 18), (4, 20); 