-- Part 2: User Profile Tables
-- Improved TNP Database Schema

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