-- Part 3: Events and Participants Tables (PHP 7.3+ Compatible)
-- Improved TNP Database Schema

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_organiser_id (event_organiser_id),
    INDEX idx_event_status (event_status),
    INDEX idx_event_type (event_type),
    INDEX idx_event_title (event_title),
    INDEX idx_reference_id (reference_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Note: Generated columns and JSON array indexes are MySQL 8.0+ features
-- For PHP 7.3 compatibility, these will be handled in application layer

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