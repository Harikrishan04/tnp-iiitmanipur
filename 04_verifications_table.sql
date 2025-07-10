-- Part 4: Verifications Table
-- Improved TNP Database Schema

USE tnpdb;

-- =============================================
-- VERIFICATIONS TABLE
-- =============================================

CREATE TABLE `verifications` (
    `verification_id` char(36) NOT NULL DEFAULT(uuid()),
    `verified_entity_id` char(36) NOT NULL,
    `verified_entity_type` enum(
        'user',
        'event',
        'student',
        'recruiter'
    ) NOT NULL,
    `verified_by_user_id` char(36) DEFAULT NULL,
    `verified_on` datetime DEFAULT NULL,
    `status` enum(
        'pending',
        'verified',
        'rejected',
        'reverted'
    ) DEFAULT 'pending',
    `notes` text,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`verification_id`),
    KEY `idx_verified_entity` (
        `verified_entity_type`,
        `verified_entity_id`
    ),
    KEY `idx_verified_by_user_id` (`verified_by_user_id`),
    KEY `idx_verified_on` (`verified_on`),
    KEY `idx_status` (`status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- =============================================
-- VERIFICATION QUERIES
-- =============================================

-- Query to check if the verifications table was created successfully
SHOW TABLES LIKE 'verifications';

-- Query to check the table structure
DESCRIBE verifications;

-- Query to check existing verification records
SELECT * FROM verifications LIMIT 5;

-- =============================================
-- NOTES
-- =============================================

/*
IMPORTANT NOTES:

1. VERIFICATION TYPES: The table supports verification for different entity types:
   - user: General user verification
   - event: Event verification
   - student: Student profile verification
   - recruiter: Recruiter verification

2. STATUS VALUES: The verification status can be:
   - pending: Default status for new verifications
   - verified: Successfully verified
   - rejected: Verification rejected
   - reverted: Verification reverted back to pending

3. AUTOMATIC VERIFICATION: The trigger automatically creates a verification record
   when a new student is inserted with status 'pending'.

4. VERIFICATION PROCESS: The verification process typically involves:
   - Student completes profile
   - Student submits for verification
   - Coordinator reviews and verifies/rejects
   - Status is updated accordingly

5. INDEXES: The table includes indexes for efficient querying:
   - idx_verified_entity: For queries by entity type and ID
   - idx_verified_by_user_id: For queries by verifier
   - idx_verified_on: For queries by verification date
   - idx_status: For queries by status

6. FOREIGN KEYS: Consider adding foreign key constraints if needed:
   - verified_entity_id -> users(user_id) for user verifications
   - verified_by_user_id -> users(user_id) for verifier reference
*/ 