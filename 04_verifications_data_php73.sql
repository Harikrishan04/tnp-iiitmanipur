-- Part 4: Verifications Table and Initial Data (PHP 7.3+ Compatible)
-- Improved TNP Database Schema

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