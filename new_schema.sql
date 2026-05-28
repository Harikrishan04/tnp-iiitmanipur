-- =============================================================================
-- IIIT MANIPUR — TRAINING & PLACEMENT PORTAL
-- new_schema.sql  |  MySQL 8.0  |  utf8mb4  |  InnoDB
-- =============================================================================
-- Tables (23):
--   Auth       : users, roles, otp_requests
--   Lookup     : departments, programs, job_types, round_types
--   Profiles   : students, recruiters, coordinators, admins
--   Jobs       : placement_sessions, jobs, job_drafts, job_rounds,
--                round_schedule_logs
--   Apply      : applications, round_results
--   Placement  : placements
--   Verify     : verifications, verification_logs
--   Comms      : announcements, announcement_targets, notification_log
-- =============================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- =============================================================================
-- DROP ORDER  (children before parents)
-- =============================================================================
DROP TABLE IF EXISTS notification_log;
DROP TABLE IF EXISTS announcement_targets;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS verification_logs;
DROP TABLE IF EXISTS verifications;
DROP TABLE IF EXISTS placements;
DROP TABLE IF EXISTS round_results;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS round_schedule_logs;
DROP TABLE IF EXISTS job_rounds;
DROP TABLE IF EXISTS job_drafts;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS placement_sessions;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS coordinators;
DROP TABLE IF EXISTS recruiters;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS otp_requests;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS round_types;
DROP TABLE IF EXISTS job_types;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS roles;

-- =============================================================================
-- LAYER 1 — LOOKUP TABLES (no dependencies)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- roles
-- -----------------------------------------------------------------------------
CREATE TABLE roles (
    role_id     TINYINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(50)         NOT NULL,
    description VARCHAR(255)        NULL,
    PRIMARY KEY (role_id),
    UNIQUE KEY uq_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (name, description) VALUES
    ('student',     'B.Tech student seeking placement'),
    ('recruiter',   'Company representative posting jobs'),
    ('coordinator', 'Institute T&P coordinator — verifies students & recruiters'),
    ('admin',       'T&P admin — manages everything');

-- -----------------------------------------------------------------------------
-- departments
-- -----------------------------------------------------------------------------
CREATE TABLE departments (
    dept_id     CHAR(36)        NOT NULL DEFAULT (UUID()),
    name        VARCHAR(100)    NOT NULL,
    code        VARCHAR(20)     NOT NULL,   -- e.g. CSE, ECE, ME
    is_active   BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (dept_id),
    UNIQUE KEY uq_dept_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO departments (name, code) VALUES
    ('Computer Science and Engineering',        'CSE'),
    ('Electronics and Communication Engineering','ECE'),
    ('Mechanical Engineering',                  'ME'),
    ('Civil Engineering',                       'CE'),
    ('Electrical Engineering',                  'EE'),
    ('Information Technology',                  'IT'),
    ('Chemical Engineering',                    'CHE'),
    ('Biotechnology',                           'BT');

-- -----------------------------------------------------------------------------
-- programs
-- -----------------------------------------------------------------------------
CREATE TABLE programs (
    program_id      CHAR(36)        NOT NULL DEFAULT (UUID()),
    name            VARCHAR(100)    NOT NULL,
    code            VARCHAR(20)     NOT NULL,   -- e.g. BTECH, MTECH
    duration_years  TINYINT         NOT NULL DEFAULT 4,
    is_active       BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (program_id),
    UNIQUE KEY uq_program_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO programs (name, code, duration_years) VALUES
    ('Bachelor of Technology', 'BTECH', 4);

-- -----------------------------------------------------------------------------
-- job_types
-- -----------------------------------------------------------------------------
CREATE TABLE job_types (
    job_type_id CHAR(36)        NOT NULL DEFAULT (UUID()),
    name        VARCHAR(50)     NOT NULL,   -- Full Time, Internship, Contract
    code        VARCHAR(20)     NOT NULL,   -- full_time, internship, contract
    is_active   BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (job_type_id),
    UNIQUE KEY uq_job_type_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO job_types (name, code) VALUES
    ('Full Time',   'full_time'),
    ('Internship',  'internship'),
    ('Contract',    'contract'),
    ('Part Time',   'part_time');

-- -----------------------------------------------------------------------------
-- round_types
-- -----------------------------------------------------------------------------
CREATE TABLE round_types (
    round_type_id   CHAR(36)        NOT NULL DEFAULT (UUID()),
    name            VARCHAR(50)     NOT NULL,   -- Aptitude Test, Technical Interview
    code            VARCHAR(30)     NOT NULL,   -- aptitude, technical, hr
    is_active       BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (round_type_id),
    UNIQUE KEY uq_round_type_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO round_types (name, code) VALUES
    ('Aptitude Test',       'aptitude'),
    ('Technical Interview', 'technical'),
    ('HR Interview',        'hr'),
    ('Coding Test',         'coding'),
    ('Group Discussion',    'group_discussion'),
    ('Psychometric Test',   'psychometric'),
    ('Case Study',          'case_study'),
    ('Other',               'other');

-- =============================================================================
-- LAYER 2 — AUTH
-- =============================================================================

-- -----------------------------------------------------------------------------
-- users  (auth-only — no business columns ever)
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    user_id                 CHAR(36)        NOT NULL DEFAULT (UUID()),
    email                   VARCHAR(255)    NOT NULL,
    phone                   VARCHAR(15)     NULL,
    role_id                 TINYINT UNSIGNED NOT NULL,
    is_active               BOOLEAN         NOT NULL DEFAULT TRUE,
    account_activated       BOOLEAN         NOT NULL DEFAULT FALSE,
    preferred_otp_channel   ENUM('email','sms') NOT NULL DEFAULT 'email',
    first_login_at          DATETIME        NULL,
    last_login_at           DATETIME        NULL,
    -- NULL = self-registered (student/recruiter)
    -- admin_id = institute-created (coordinator/admin)
    created_by              CHAR(36)        NULL,
    deactivated_at          DATETIME        NULL,
    deactivation_reason     VARCHAR(255)    NULL,
    created_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_user_email (email),
    CONSTRAINT chk_phone_format
        CHECK (phone IS NULL OR phone REGEXP '^[0-9]{10,15}$'),
    CONSTRAINT fk_user_role
        FOREIGN KEY (role_id) REFERENCES roles (role_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_user_created_by
        FOREIGN KEY (created_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_user_role       ON users (role_id);
CREATE INDEX idx_user_is_active  ON users (is_active);
CREATE INDEX idx_user_created_by ON users (created_by);

-- -----------------------------------------------------------------------------
-- otp_requests
-- -----------------------------------------------------------------------------
CREATE TABLE otp_requests (
    otp_id          CHAR(36)    NOT NULL DEFAULT (UUID()),
    user_id         CHAR(36)    NOT NULL,
    otp_hash        CHAR(64)    NOT NULL,   -- SHA2(otp, 256) — never store plain
    channel         ENUM('email','sms')             NOT NULL DEFAULT 'email',
    purpose         ENUM('login','verify_profile','sensitive_action')
                                            NOT NULL DEFAULT 'login',
    expires_at      DATETIME    NOT NULL,
    used_at         DATETIME    NULL,       -- NULL = not yet used
    is_invalidated  BOOLEAN     NOT NULL DEFAULT FALSE,
    ip_address      VARCHAR(45) NULL,       -- IPv4 or IPv6
    attempts        TINYINT     NOT NULL DEFAULT 0
                                CHECK (attempts >= 0 AND attempts <= 10),
    created_at      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (otp_id),
    CONSTRAINT fk_otp_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Composite index for rate-limit check: WHERE user_id = ? AND created_at > ?
CREATE INDEX idx_otp_user_created   ON otp_requests (user_id, created_at);
-- Index for validation query
CREATE INDEX idx_otp_user_active    ON otp_requests (user_id, used_at, is_invalidated, expires_at);

-- =============================================================================
-- LAYER 3 — PROFILE TABLES
-- =============================================================================

-- -----------------------------------------------------------------------------
-- students
-- -----------------------------------------------------------------------------
CREATE TABLE students (
    student_id          CHAR(36)        NOT NULL,   -- = user_id
    roll_no             VARCHAR(20)     NULL,
    name                VARCHAR(150)    NOT NULL,
    date_of_birth       DATE            NULL,
    gender              ENUM('male','female','other','prefer_not_to_say') NULL,
    category            ENUM('general','obc','sc','st','ews','pwd')       NULL,
    blood_group         ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-')  NULL,
    -- Academic
    program_id          CHAR(36)        NULL,
    dept_id             CHAR(36)        NULL,
    current_semester    TINYINT         NULL,
    cpi                 DECIMAL(4,2)    NULL,
    year_of_admission   YEAR            NULL,
    year_of_passing     YEAR            NULL,
    placement_status    ENUM('not_placed','placed','opted_out','ppo','off_campus')
                                        NOT NULL DEFAULT 'not_placed',
    -- Address
    locality            VARCHAR(150)    NULL,
    city                VARCHAR(100)    NULL,
    state               VARCHAR(100)    NULL,
    pincode             VARCHAR(10)     NULL,
    -- JSON blobs — display/profile only, not queried
    education_details_json  JSON        NULL,   -- {tenth, twelfth, jee}
    experiences_json        JSON        NULL,   -- [{type, title, org, duration, desc}]
    skills_json             JSON        NULL,   -- ["Python", "MySQL", ...]
    personal_links_json     JSON        NULL,   -- {github, linkedin, portfolio}
    family_info_json        JSON        NULL,   -- {father, mother, guardian}
    documents_json          JSON        NULL,   -- {resume, photo, tenth_marksheet, ...}
    -- Meta
    profile_completed   BOOLEAN         NOT NULL DEFAULT FALSE,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id),
    UNIQUE KEY uq_student_roll (roll_no),
    CONSTRAINT chk_student_cpi
        CHECK (cpi IS NULL OR cpi BETWEEN 0.00 AND 10.00),
    CONSTRAINT chk_student_dob
        CHECK (date_of_birth IS NULL OR date_of_birth < '2050-01-01'),
    CONSTRAINT chk_student_semester
        CHECK (current_semester IS NULL OR current_semester BETWEEN 1 AND 10),
    CONSTRAINT fk_student_user
        FOREIGN KEY (student_id) REFERENCES users (user_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_student_program
        FOREIGN KEY (program_id) REFERENCES programs (program_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_student_dept
        FOREIGN KEY (dept_id) REFERENCES departments (dept_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_student_dept        ON students (dept_id);
CREATE INDEX idx_student_yop         ON students (year_of_passing);
CREATE INDEX idx_student_cpi         ON students (cpi);
CREATE INDEX idx_student_placement   ON students (placement_status);
CREATE INDEX idx_student_program     ON students (program_id);

-- -----------------------------------------------------------------------------
-- recruiters
-- -----------------------------------------------------------------------------
CREATE TABLE recruiters (
    recruiter_id                CHAR(36)        NOT NULL,   -- = user_id
    -- Primary contact (the person who logs in)
    primary_name                VARCHAR(150)    NOT NULL,
    primary_position            VARCHAR(100)    NULL,
    primary_phone               VARCHAR(15)     NULL,
    primary_linkedin            VARCHAR(255)    NULL,
    -- Alternate contact
    alt_name                    VARCHAR(150)    NULL,
    alt_position                VARCHAR(100)    NULL,
    alt_email                   VARCHAR(255)    NULL,
    alt_phone                   VARCHAR(15)     NULL,
    alt_linkedin                VARCHAR(255)    NULL,
    -- Company (display only — not queried)
    company_details_json        JSON            NULL,
    -- {name, website, linkedin, about, address, city, state, country, logo_url}
    -- Coordinator notes
    remark                      TEXT            NULL,
    profile_completed           BOOLEAN         NOT NULL DEFAULT FALSE,
    created_at                  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (recruiter_id),
    CONSTRAINT fk_recruiter_user
        FOREIGN KEY (recruiter_id) REFERENCES users (user_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- coordinators
-- -----------------------------------------------------------------------------
CREATE TABLE coordinators (
    coordinator_id      CHAR(36)        NOT NULL,   -- = user_id
    name                VARCHAR(150)    NOT NULL,
    phone               VARCHAR(15)     NULL,
    dept_id             CHAR(36)        NULL,
    designation         VARCHAR(100)    NULL,
    coordinator_type    ENUM('faculty','student') NOT NULL DEFAULT 'faculty',
    team                VARCHAR(100)    NULL,   -- e.g. "Placement Team A"
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (coordinator_id),
    CONSTRAINT fk_coordinator_user
        FOREIGN KEY (coordinator_id) REFERENCES users (user_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_coordinator_dept
        FOREIGN KEY (dept_id) REFERENCES departments (dept_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_coord_dept ON coordinators (dept_id);

-- -----------------------------------------------------------------------------
-- admins
-- -----------------------------------------------------------------------------
CREATE TABLE admins (
    admin_id        CHAR(36)        NOT NULL,   -- = user_id
    name            VARCHAR(150)    NOT NULL,
    phone           VARCHAR(15)     NULL,
    designation     VARCHAR(100)    NULL,
    access_level    ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (admin_id),
    CONSTRAINT fk_admin_user
        FOREIGN KEY (admin_id) REFERENCES users (user_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- LAYER 4 — PLACEMENT SESSIONS
-- =============================================================================

CREATE TABLE placement_sessions (
    session_id      CHAR(36)        NOT NULL DEFAULT (UUID()),
    label           VARCHAR(20)     NOT NULL,   -- e.g. '2025-26'
    start_date      DATE            NOT NULL,
    end_date        DATE            NOT NULL,
    is_active       BOOLEAN         NOT NULL DEFAULT FALSE,
    active_session_guard TINYINT GENERATED ALWAYS AS (IF(is_active = TRUE, 1, NULL)) VIRTUAL,
    created_by      CHAR(36)        NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (session_id),
    UNIQUE KEY uq_session_label (label),
    UNIQUE KEY uq_only_one_active_session (active_session_guard),
    CONSTRAINT chk_session_dates
        CHECK (end_date > start_date),
    CONSTRAINT fk_session_created_by
        FOREIGN KEY (created_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_session_active ON placement_sessions (is_active);

-- Ensure only ONE active session at a time
-- (enforced via stored procedure / application layer — MySQL doesn't support
--  partial unique indexes natively; trigger below handles it)

-- =============================================================================
-- LAYER 5 — JOBS
-- =============================================================================

-- -----------------------------------------------------------------------------
-- jobs
-- -----------------------------------------------------------------------------
CREATE TABLE jobs (
    job_id                  CHAR(36)        NOT NULL DEFAULT (UUID()),
    session_id              CHAR(36)        NOT NULL,
    recruiter_id            CHAR(36)        NOT NULL,
    job_type_id             CHAR(36)        NOT NULL,
    title                   VARCHAR(200)    NOT NULL,
    description             TEXT            NULL,
    location                VARCHAR(255)    NULL,
    -- Salary
    ctc_lpa                 DECIMAL(7,2)    NULL,   -- advertised CTC
    stipend_pm              DECIMAL(8,2)    NULL,   -- for internship
    salary_type             ENUM('fixed','range','negotiable','not_disclosed')
                                            NOT NULL DEFAULT 'not_disclosed',
    -- Eligibility (columns — used in WHERE clauses)
    min_cpi                 DECIMAL(4,2)    NULL,
    allowed_year_of_passing YEAR            NULL,
    -- Eligibility (arrays — used for display filtering only)
    allowed_branches_json   JSON            NULL,   -- ["CSE","ECE"]
    allowed_programs_json   JSON            NULL,   -- ["BTECH"]
    -- Application window
    apply_start             DATETIME        NULL,
    apply_end               DATETIME        NULL,
    -- Capacity
    max_participants        INT UNSIGNED    NULL,
    applications_count      INT UNSIGNED    NOT NULL DEFAULT 0,
    -- Documents
    documents_json          JSON            NULL,   -- [{name, url}]
    -- Status
    job_status              ENUM('draft','pending','verified','opened','closed','cancelled')
                                            NOT NULL DEFAULT 'draft',
    -- Edit tracking (for re-verification)
    edit_summary            TEXT            NULL,
    last_edited_by          CHAR(36)        NULL,
    last_edited_at          DATETIME        NULL,
    -- Close tracking
    closed_at               DATETIME        NULL,
    closed_by               CHAR(36)        NULL,
    -- Clone tracking (returning recruiter)
    cloned_from_job_id      CHAR(36)        NULL,
    -- Meta
    created_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (job_id),
    CONSTRAINT chk_job_cpi
        CHECK (min_cpi IS NULL OR min_cpi BETWEEN 0.00 AND 10.00),
    CONSTRAINT chk_job_apply_window
        CHECK (apply_end IS NULL OR apply_start IS NULL OR apply_end > apply_start),
    CONSTRAINT chk_job_capacity
        CHECK (max_participants IS NULL OR max_participants > 0),
    CONSTRAINT fk_job_session
        FOREIGN KEY (session_id) REFERENCES placement_sessions (session_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_job_recruiter
        FOREIGN KEY (recruiter_id) REFERENCES recruiters (recruiter_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_job_type
        FOREIGN KEY (job_type_id) REFERENCES job_types (job_type_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_job_last_edited_by
        FOREIGN KEY (last_edited_by) REFERENCES users (user_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_job_closed_by
        FOREIGN KEY (closed_by) REFERENCES users (user_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_job_cloned_from
        FOREIGN KEY (cloned_from_job_id) REFERENCES jobs (job_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_job_session        ON jobs (session_id);
CREATE INDEX idx_job_recruiter      ON jobs (recruiter_id);
CREATE INDEX idx_job_status         ON jobs (job_status);
CREATE INDEX idx_job_type           ON jobs (job_type_id);
CREATE INDEX idx_job_yop            ON jobs (allowed_year_of_passing);
CREATE INDEX idx_job_min_cpi        ON jobs (min_cpi);
CREATE INDEX idx_job_apply_end      ON jobs (apply_end);

-- -----------------------------------------------------------------------------
-- job_drafts  (auto-save recruiter form state)
-- -----------------------------------------------------------------------------
CREATE TABLE job_drafts (
    draft_id        CHAR(36)        NOT NULL DEFAULT (UUID()),
    recruiter_id    CHAR(36)        NOT NULL,
    draft_data_json JSON            NOT NULL,   -- entire form state
    last_saved_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (draft_id),
    -- One active draft per recruiter at a time
    UNIQUE KEY uq_draft_recruiter (recruiter_id),
    CONSTRAINT fk_draft_recruiter
        FOREIGN KEY (recruiter_id) REFERENCES recruiters (recruiter_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- job_rounds
-- -----------------------------------------------------------------------------
CREATE TABLE job_rounds (
    round_id            CHAR(36)        NOT NULL DEFAULT (UUID()),
    job_id              CHAR(36)        NOT NULL,
    round_type_id       CHAR(36)        NOT NULL,
    round_number        TINYINT         NOT NULL,   -- 1, 2, 3 ...
    round_name          VARCHAR(100)    NOT NULL,   -- "Online Aptitude Test"
    instructions        TEXT            NULL,
    scheduled_at        DATETIME        NULL,
    submission_deadline DATETIME        NULL,       -- pre-submission if needed
    location            VARCHAR(255)    NULL,
    duration_mins       SMALLINT        NULL,
    max_score           DECIMAL(6,2)    NULL,
    -- Status
    round_status        ENUM('draft','scheduled','ongoing','completed','cancelled')
                                        NOT NULL DEFAULT 'draft',
    -- Finalization (recruiter suggests, admin finalizes)
    suggested_by        ENUM('recruiter','admin') NOT NULL DEFAULT 'recruiter',
    is_finalized        BOOLEAN         NOT NULL DEFAULT FALSE,
    finalized_by        CHAR(36)        NULL,
    finalized_at        DATETIME        NULL,
    -- Soft delete
    is_cancelled        BOOLEAN         NOT NULL DEFAULT FALSE,
    cancelled_at        DATETIME        NULL,
    cancellation_reason TEXT            NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (round_id),
    -- No two rounds with same number in same job
    UNIQUE KEY uq_round_job_number (job_id, round_number),
    CONSTRAINT chk_round_deadline
        CHECK (submission_deadline IS NULL OR scheduled_at IS NULL
               OR submission_deadline <= scheduled_at),
    CONSTRAINT fk_round_job
        FOREIGN KEY (job_id) REFERENCES jobs (job_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_round_type
        FOREIGN KEY (round_type_id) REFERENCES round_types (round_type_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_round_finalized_by
        FOREIGN KEY (finalized_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_round_job      ON job_rounds (job_id);
CREATE INDEX idx_round_status   ON job_rounds (round_status);
CREATE INDEX idx_round_active   ON job_rounds (job_id, is_cancelled, is_finalized);

-- -----------------------------------------------------------------------------
-- round_schedule_logs  (reschedule audit trail)
-- -----------------------------------------------------------------------------
CREATE TABLE round_schedule_logs (
    log_id          CHAR(36)        NOT NULL DEFAULT (UUID()),
    round_id        CHAR(36)        NOT NULL,
    old_datetime    DATETIME        NULL,
    new_datetime    DATETIME        NULL,
    reason          TEXT            NULL,
    changed_by      CHAR(36)        NULL,
    changed_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    CONSTRAINT fk_rsl_round
        FOREIGN KEY (round_id) REFERENCES job_rounds (round_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_rsl_changed_by
        FOREIGN KEY (changed_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_rsl_round ON round_schedule_logs (round_id);

-- =============================================================================
-- LAYER 6 — APPLICATIONS
-- =============================================================================

CREATE TABLE applications (
    application_id          CHAR(36)        NOT NULL DEFAULT (UUID()),
    student_id              CHAR(36)        NOT NULL,
    job_id                  CHAR(36)        NOT NULL,
    resume_url              VARCHAR(500)    NOT NULL,
    applied_at              DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Eligibility snapshot at time of apply (immutable after insert)
    eligibility_snapshot    JSON            NOT NULL,
    -- {min_cpi, allowed_branches, allowed_year_of_passing, applied_cpi, applied_branch}
    -- Shortlisting (before Round 1 — independent of round results)
    is_shortlisted          BOOLEAN         NOT NULL DEFAULT FALSE,
    shortlisted_at          DATETIME        NULL,
    shortlisted_by          CHAR(36)        NULL,
    -- Privacy — resume visible to recruiter only after job is verified+opened
    resume_visible_to_recruiter BOOLEAN     NOT NULL DEFAULT FALSE,
    -- Status
    status  ENUM(
        'applied',
        'shortlisted',
        'in_process',
        'selected',
        'not_selected',
        'withdrawn',
        'offer_accepted',
        'offer_declined'
    ) NOT NULL DEFAULT 'applied',
    -- Withdrawal
    withdrawn_at            DATETIME        NULL,
    withdrawal_reason       ENUM('got_other_offer','personal','other') NULL,
    -- Offer acceptance
    offer_accepted_at       DATETIME        NULL,
    -- Coordinator/admin note
    coordinator_note        TEXT            NULL,
    created_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (application_id),
    -- One application per student per job
    UNIQUE KEY uq_application (student_id, job_id),
    CONSTRAINT fk_app_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_app_job
        FOREIGN KEY (job_id) REFERENCES jobs (job_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_app_shortlisted_by
        FOREIGN KEY (shortlisted_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_app_student    ON applications (student_id);
CREATE INDEX idx_app_job        ON applications (job_id);
CREATE INDEX idx_app_status     ON applications (status);
CREATE INDEX idx_app_shortlist  ON applications (job_id, is_shortlisted);

-- -----------------------------------------------------------------------------
-- round_results
-- -----------------------------------------------------------------------------
CREATE TABLE round_results (
    result_id       CHAR(36)        NOT NULL DEFAULT (UUID()),
    round_id        CHAR(36)        NOT NULL,
    application_id  CHAR(36)        NOT NULL,
    student_id      CHAR(36)        NOT NULL,   -- denormalized for query speed
    score           DECIMAL(6,2)    NULL,
    result          ENUM('pass','fail','absent','pending')
                                    NOT NULL DEFAULT 'pending',
    feedback        TEXT            NULL,
    -- Result visibility
    result_released BOOLEAN         NOT NULL DEFAULT FALSE,
    released_at     DATETIME        NULL,
    released_by     CHAR(36)        NULL,
    entered_by      CHAR(36)        NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (result_id),
    -- One result per student per round
    UNIQUE KEY uq_result (round_id, application_id),
    CONSTRAINT fk_result_round
        FOREIGN KEY (round_id) REFERENCES job_rounds (round_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_result_application
        FOREIGN KEY (application_id) REFERENCES applications (application_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_result_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_result_released_by
        FOREIGN KEY (released_by) REFERENCES users (user_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_result_entered_by
        FOREIGN KEY (entered_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_result_round       ON round_results (round_id);
CREATE INDEX idx_result_application ON round_results (application_id);
CREATE INDEX idx_result_student     ON round_results (student_id);
CREATE INDEX idx_result_released    ON round_results (round_id, result_released);

-- =============================================================================
-- LAYER 7 — PLACEMENTS
-- =============================================================================

CREATE TABLE placements (
    placement_id        CHAR(36)        NOT NULL DEFAULT (UUID()),
    student_id          CHAR(36)        NOT NULL,
    job_id              CHAR(36)        NULL,           -- NULL for PPO/off-campus
    application_id      CHAR(36)        NULL,           -- NULL for PPO/off-campus
    placement_type      ENUM('campus','ppo','off_campus') NOT NULL DEFAULT 'campus',
    -- For PPO/off-campus where no job_id exists
    company_name_manual VARCHAR(200)    NULL,
    -- Offer details
    actual_ctc_lpa      DECIMAL(7,2)    NULL,           -- actual offer (may differ from job CTC)
    stipend_pm          DECIMAL(8,2)    NULL,
    offer_date          DATE            NULL,
    joining_date        DATE            NULL,
    offer_letter_url    VARCHAR(500)    NULL,
    offer_status        ENUM('offered','accepted','declined','joined','revoked')
                                        NOT NULL DEFAULT 'offered',
    -- Session reference for NIRF reports
    session_id          CHAR(36)        NULL,
    academic_year       VARCHAR(10)     NULL,           -- e.g. '2025-26'
    -- Who recorded this
    recorded_by         CHAR(36)        NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (placement_id),
    -- One final accepted placement per student (DB-level one-offer rule)
    UNIQUE KEY uq_student_placed (student_id),
    CONSTRAINT fk_placement_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_placement_job
        FOREIGN KEY (job_id) REFERENCES jobs (job_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_placement_application
        FOREIGN KEY (application_id) REFERENCES applications (application_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_placement_session
        FOREIGN KEY (session_id) REFERENCES placement_sessions (session_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_placement_recorded_by
        FOREIGN KEY (recorded_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_placement_student  ON placements (student_id);
CREATE INDEX idx_placement_session  ON placements (session_id);
CREATE INDEX idx_placement_type     ON placements (placement_type);
CREATE INDEX idx_placement_status   ON placements (offer_status);

-- =============================================================================
-- LAYER 8 — VERIFICATIONS
-- =============================================================================

-- -----------------------------------------------------------------------------
-- verifications  (current status only — history in verification_logs)
-- -----------------------------------------------------------------------------
CREATE TABLE verifications (
    verification_id         CHAR(36)        NOT NULL DEFAULT (UUID()),
    entity_id               CHAR(36)        NOT NULL,
    -- VARCHAR not ENUM — new entity types need zero schema change
    entity_type             VARCHAR(50)     NOT NULL,   -- 'student','recruiter','job'
    status  ENUM(
        'draft',
        'pending',
        'under_review',
        'verified',
        'resubmit',
        'rejected'
    ) NOT NULL DEFAULT 'draft',
    assigned_coordinator_id CHAR(36)        NULL,
    verified_by             CHAR(36)        NULL,
    verified_at             DATETIME        NULL,
    remark                  TEXT            NULL,
    created_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (verification_id),
    -- One verification record per entity
    UNIQUE KEY uq_verification_entity (entity_id, entity_type),
    CONSTRAINT fk_verif_coordinator
        FOREIGN KEY (assigned_coordinator_id) REFERENCES coordinators (coordinator_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_verif_verified_by
        FOREIGN KEY (verified_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_verif_entity_type  ON verifications (entity_type, status);
CREATE INDEX idx_verif_coordinator  ON verifications (assigned_coordinator_id);
CREATE INDEX idx_verif_status       ON verifications (status);

-- -----------------------------------------------------------------------------
-- verification_logs  (full audit history)
-- -----------------------------------------------------------------------------
CREATE TABLE verification_logs (
    log_id          CHAR(36)        NOT NULL DEFAULT (UUID()),
    verification_id CHAR(36)        NOT NULL,
    entity_id       CHAR(36)        NOT NULL,   -- denormalized for easy query
    entity_type     VARCHAR(50)     NOT NULL,
    from_status     VARCHAR(30)     NULL,
    to_status       VARCHAR(30)     NOT NULL,
    changed_by      CHAR(36)        NULL,
    note            TEXT            NULL,
    changed_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    CONSTRAINT fk_vlog_verification
        FOREIGN KEY (verification_id) REFERENCES verifications (verification_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_vlog_changed_by
        FOREIGN KEY (changed_by) REFERENCES users (user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_vlog_verification  ON verification_logs (verification_id);
CREATE INDEX idx_vlog_entity        ON verification_logs (entity_id, entity_type);
CREATE INDEX idx_vlog_changed_by    ON verification_logs (changed_by);

-- =============================================================================
-- LAYER 9 — COMMUNICATIONS
-- =============================================================================

-- -----------------------------------------------------------------------------
-- announcements
-- -----------------------------------------------------------------------------
CREATE TABLE announcements (
    announcement_id     CHAR(36)        NOT NULL DEFAULT (UUID()),
    posted_by           CHAR(36)        NOT NULL,
    posted_by_role      ENUM('admin','coordinator') NOT NULL,
    title               VARCHAR(255)    NOT NULL,
    body                TEXT            NOT NULL,
    attachments_json    JSON            NULL,       -- [{name, url}]
    priority            ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
    -- Optional job link
    job_id              CHAR(36)        NULL,
    -- Visibility to roles
    visible_to_roles_json JSON          NOT NULL,  -- ["student","recruiter"]
    -- Scheduling
    publish_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at          DATETIME        NULL,
    status              ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (announcement_id),
    CONSTRAINT fk_ann_posted_by
        FOREIGN KEY (posted_by) REFERENCES users (user_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_ann_job
        FOREIGN KEY (job_id) REFERENCES jobs (job_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_ann_posted_by  ON announcements (posted_by);
CREATE INDEX idx_ann_status     ON announcements (status, publish_at);
CREATE INDEX idx_ann_job        ON announcements (job_id);

-- -----------------------------------------------------------------------------
-- announcement_targets  (replaces audience ENUM — fully extensible)
-- -----------------------------------------------------------------------------
CREATE TABLE announcement_targets (
    target_id           CHAR(36)        NOT NULL DEFAULT (UUID()),
    announcement_id     CHAR(36)        NOT NULL,
    -- target_type examples: 'all','branch','year','job','placement_status'
    target_type         VARCHAR(50)     NOT NULL,
    -- target_value examples: 'CSE', '2027', job_id, 'not_placed'
    target_value        VARCHAR(100)    NULL,
    PRIMARY KEY (target_id),
    CONSTRAINT fk_target_announcement
        FOREIGN KEY (announcement_id) REFERENCES announcements (announcement_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_target_announcement ON announcement_targets (announcement_id);
CREATE INDEX idx_target_type_value   ON announcement_targets (target_type, target_value);

-- -----------------------------------------------------------------------------
-- notification_log  (track what was sent, to whom, when)
-- -----------------------------------------------------------------------------
CREATE TABLE notification_log (
    log_id      CHAR(36)        NOT NULL DEFAULT (UUID()),
    user_id     CHAR(36)        NOT NULL,
    type        ENUM(
        'otp',
        'welcome',
        'verification_status',
        'application_status',
        'round_result',
        'round_schedule',
        'announcement',
        'offer',
        'general'
    ) NOT NULL,
    ref_id      CHAR(36)        NULL,   -- announcement_id / application_id / etc.
    ref_type    VARCHAR(50)     NULL,   -- 'announcement','application','round_result'
    channel     ENUM('email','sms','portal') NOT NULL DEFAULT 'email',
    status      ENUM('queued','sent','failed','delivered') NOT NULL DEFAULT 'queued',
    sent_at     DATETIME        NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    CONSTRAINT fk_notif_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_notif_user     ON notification_log (user_id, created_at);
CREATE INDEX idx_notif_ref      ON notification_log (ref_id, ref_type);
CREATE INDEX idx_notif_status   ON notification_log (status);

-- =============================================================================
-- TRIGGERS
-- =============================================================================

DELIMITER $$

-- -----------------------------------------------------------------------------
-- T1: After user insert — auto-create profile row
-- Uses role NAME not integer ID (open/closed principle)
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
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
    -- coordinators and admins are created via stored procedures (full data supplied)
    -- so triggers are not needed for those roles
END$$

-- -----------------------------------------------------------------------------
-- T2: After user is_active flipped to FALSE — invalidate all pending OTPs
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_user_deactivate
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.is_active = TRUE AND NEW.is_active = FALSE THEN
        UPDATE otp_requests
        SET is_invalidated = TRUE
        WHERE user_id = NEW.user_id
          AND used_at IS NULL
          AND is_invalidated = FALSE;
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- T3: After verification status update — write to verification_logs
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_verification_update
AFTER UPDATE ON verifications
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO verification_logs (
            verification_id, entity_id, entity_type,
            from_status, to_status, changed_by
        ) VALUES (
            NEW.verification_id, NEW.entity_id, NEW.entity_type,
            OLD.status, NEW.status, NEW.verified_by
        );
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- T4: After new verification insert — write initial log entry
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_verification_insert
AFTER INSERT ON verifications
FOR EACH ROW
BEGIN
    INSERT INTO verification_logs (
        verification_id, entity_id, entity_type,
        from_status, to_status
    ) VALUES (
        NEW.verification_id, NEW.entity_id, NEW.entity_type,
        NULL, NEW.status
    );
END$$

-- -----------------------------------------------------------------------------
-- T5: Before application insert — enforce business rules
--     a) Student profile must be verified
--     b) Student must not already be placed
--     c) Job must be open
--     d) Application window must be active
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_before_application_insert
BEFORE INSERT ON applications
FOR EACH ROW
BEGIN
    DECLARE v_verif_status  VARCHAR(30);
    DECLARE v_placement     VARCHAR(30);
    DECLARE v_job_status    VARCHAR(30);
    DECLARE v_apply_end     DATETIME;
    DECLARE v_apply_start   DATETIME;
    DECLARE v_max_participants INT UNSIGNED;
    DECLARE v_applications_count INT UNSIGNED;

    -- Check student verification
    SELECT status INTO v_verif_status
    FROM verifications
    WHERE entity_id = NEW.student_id AND entity_type = 'student';

    IF v_verif_status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student profile must be verified before applying.';
    END IF;

    -- Check placement status
    SELECT placement_status INTO v_placement
    FROM students WHERE student_id = NEW.student_id;

    IF v_placement = 'placed' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student is already placed and cannot apply to new jobs.';
    END IF;

    -- Check job status and capacity
    SELECT job_status, apply_start, apply_end, max_participants, applications_count
    INTO v_job_status, v_apply_start, v_apply_end, v_max_participants, v_applications_count
    FROM jobs WHERE job_id = NEW.job_id;

    IF v_job_status <> 'opened' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This job is not currently open for applications.';
    END IF;

    -- Check capacity limit
    IF v_max_participants IS NOT NULL AND v_applications_count >= v_max_participants THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'This job posting has reached its maximum applicant capacity.';
    END IF;

    -- Check application window
    IF v_apply_start IS NOT NULL AND NOW() < v_apply_start THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Application window has not opened yet.';
    END IF;

    IF v_apply_end IS NOT NULL AND NOW() > v_apply_end THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Application deadline has passed.';
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- T6: After application insert — increment jobs.applications_count atomically
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_application_insert
AFTER INSERT ON applications
FOR EACH ROW
BEGIN
    UPDATE jobs
    SET applications_count = applications_count + 1
    WHERE job_id = NEW.job_id;
END$$

-- -----------------------------------------------------------------------------
-- T7: After application withdrawn — decrement jobs.applications_count
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_application_withdraw
AFTER UPDATE ON applications
FOR EACH ROW
BEGIN
    IF OLD.status <> 'withdrawn' AND NEW.status = 'withdrawn' THEN
        UPDATE jobs
        SET applications_count = GREATEST(0, applications_count - 1)
        WHERE job_id = NEW.job_id;
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- T8: After job is closed — auto-resolve all pending applications
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_job_close
AFTER UPDATE ON jobs
FOR EACH ROW
BEGIN
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
END$$

-- -----------------------------------------------------------------------------
-- T9: After job edited when verified — reset to resubmit (never to draft)
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_before_job_edit
BEFORE UPDATE ON jobs
FOR EACH ROW
BEGIN
    -- Only reset if meaningful content changed (not status/count changes)
    IF OLD.job_status = 'verified'
       AND (OLD.title <> NEW.title
            OR OLD.description <> NEW.description
            OR NOT (OLD.min_cpi <=> NEW.min_cpi)
            OR NOT (OLD.allowed_year_of_passing <=> NEW.allowed_year_of_passing))
    THEN
        SET NEW.job_status = 'pending';
    END IF;
END$$


-- -----------------------------------------------------------------------------
-- T11: After round scheduled_at changes — log the reschedule
-- -----------------------------------------------------------------------------
CREATE TRIGGER trg_after_round_reschedule
AFTER UPDATE ON job_rounds
FOR EACH ROW
BEGIN
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
END$$

DELIMITER ;

-- =============================================================================
-- STORED PROCEDURES
-- =============================================================================

DELIMITER $$

-- -----------------------------------------------------------------------------
-- SP1: CreateCoordinatorAccount
-- Admin calls this single procedure to add a coordinator
-- Creates: users row + coordinators row + welcome OTP
-- -----------------------------------------------------------------------------
CREATE PROCEDURE CreateCoordinatorAccount(
    IN p_email          VARCHAR(255),
    IN p_phone          VARCHAR(15),
    IN p_name           VARCHAR(150),
    IN p_dept_code      VARCHAR(20),
    IN p_designation    VARCHAR(100),
    IN p_team           VARCHAR(100),
    IN p_coord_type     ENUM('faculty','student'),
    IN p_created_by     CHAR(36),       -- admin's user_id
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

    -- Get role_id by name (never hardcode integer)
    SELECT role_id INTO v_role_id FROM roles WHERE name = 'coordinator';

    -- Get dept_id
    SELECT dept_id INTO v_dept_id FROM departments WHERE code = p_dept_code;

    -- Check email not already used
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email already exists in the system.';
    END IF;

    SET v_new_user_id = UUID();

    -- Create user row
    INSERT INTO users (
        user_id, email, phone, role_id,
        is_active, account_activated,
        preferred_otp_channel, created_by
    ) VALUES (
        v_new_user_id, p_email, p_phone, v_role_id,
        TRUE, FALSE,
        'email', p_created_by
    );

    -- Create coordinator profile
    INSERT INTO coordinators (
        coordinator_id, name, phone,
        dept_id, designation, coordinator_type, team
    ) VALUES (
        v_new_user_id, p_name, p_phone,
        v_dept_id, p_designation, p_coord_type, p_team
    );

    SET p_new_user_id = v_new_user_id;

    COMMIT;
    -- Application layer sends welcome OTP after this returns
END$$

-- -----------------------------------------------------------------------------
-- SP2: CreateAdminAccount
-- Super-admin calls this to add a new admin
-- -----------------------------------------------------------------------------
CREATE PROCEDURE CreateAdminAccount(
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
END$$

-- -----------------------------------------------------------------------------
-- SP3: ValidateAndUseOTP
-- Atomic OTP validation — checks all conditions and marks used in one shot
-- Returns: 'ok', 'expired', 'used', 'invalid', 'max_attempts', 'not_found'
-- -----------------------------------------------------------------------------
CREATE PROCEDURE ValidateAndUseOTP(
    IN  p_user_id   CHAR(36),
    IN  p_otp_hash  CHAR(64),       -- SHA2(entered_otp, 256)
    IN  p_purpose   VARCHAR(30),
    OUT p_result    VARCHAR(20)
)
BEGIN
    DECLARE v_otp_id        CHAR(36);
    DECLARE v_attempts      TINYINT;
    DECLARE v_expires_at    DATETIME;
    DECLARE v_used_at       DATETIME;
    DECLARE v_invalidated   BOOLEAN;

    -- Lock the row for atomic update
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
        -- Mark as used
        UPDATE otp_requests
        SET used_at = NOW()
        WHERE otp_id = v_otp_id;

        -- Update user login tracking
        UPDATE users
        SET last_login_at    = NOW(),
            account_activated = TRUE,
            first_login_at   = COALESCE(first_login_at, NOW())
        WHERE user_id = p_user_id;

        SET p_result = 'ok';
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- SP4: AcceptOffer  (atomic — prevents double placement)
-- -----------------------------------------------------------------------------
CREATE PROCEDURE AcceptOffer(
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

    -- Lock student row to prevent race condition
    SELECT placement_status INTO @v_placed
    FROM students
    WHERE student_id = p_student_id
    FOR UPDATE;

    IF @v_placed = 'placed' THEN
        SET p_result = 'already_placed';
        ROLLBACK;
    ELSE
        -- Get job and session
        SELECT j.job_id, j.session_id
        INTO v_job_id, v_session_id
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        WHERE a.application_id = p_application_id;

        -- Update application status
        UPDATE applications
        SET status = 'offer_accepted',
            offer_accepted_at = NOW()
        WHERE application_id = p_application_id;

        -- Mark student as placed
        UPDATE students
        SET placement_status = 'placed'
        WHERE student_id = p_student_id;

        -- Record placement
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
END$$

-- -----------------------------------------------------------------------------
-- SP5: DeactivateUser  (soft delete — invalidates OTPs immediately)
-- -----------------------------------------------------------------------------
CREATE PROCEDURE DeactivateUser(
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
    -- Trigger trg_after_user_deactivate auto-invalidates OTPs
END$$

DELIMITER ;

-- =============================================================================
-- EVENT SCHEDULER — Auto-purge expired OTPs
-- =============================================================================

SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS evt_purge_expired_otps
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
    DELETE FROM otp_requests
    WHERE expires_at < NOW() - INTERVAL 24 HOUR;

-- =============================================================================
-- SEED SUPER ADMIN  (first admin — bootstrapped directly)
-- Change email/name before deploying to production
-- =============================================================================

SET @super_admin_id = UUID();

INSERT INTO users (
    user_id, email, phone, role_id,
    is_active, account_activated,
    preferred_otp_channel, created_by
) VALUES (
    @super_admin_id,
    'tpo@iiitmanipur.ac.in',     -- change before deploy
    NULL,
    (SELECT role_id FROM roles WHERE name = 'admin'),
    TRUE, TRUE,
    'email', NULL
);

INSERT INTO admins (admin_id, name, designation, access_level)
VALUES (@super_admin_id, 'TPO Admin', 'Training & Placement Officer', 'super_admin');

-- Seed initial placement session
INSERT INTO placement_sessions (label, start_date, end_date, is_active, created_by)
VALUES ('2025-26', '2025-08-01', '2026-06-30', TRUE, @super_admin_id);

-- =============================================================================
-- RESTORE SETTINGS
-- =============================================================================
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =============================================================================
-- END OF new_schema.sql
-- Total: 23 tables | 11 triggers | 5 stored procedures | 1 event
-- =============================================================================
