-- Test script to add a new student user and verify the trigger
USE tnpdb;

-- First, let's check the current roles to make sure we use the correct role_id
SELECT id, name FROM roles ORDER BY id;

-- Check the users table structure
DESCRIBE users;

-- Check if there are any existing students
SELECT COUNT(*) as existing_students FROM students;

-- Check if there are any existing verifications
SELECT COUNT(*) as existing_verifications FROM verifications;

-- Insert a new test student user with all required fields
INSERT INTO users (
    user_id,
    user_email,
    user_name,
    role_id,
    oauth_provider,
    oauth_id,
    is_active,
    is_verified,
    last_login,
    created_at,
    updated_at
) VALUES (
    UUID(), -- Generate a new UUID
    'test.student@iiitmanipur.ac.in',
    'Test Student',
    1,  -- Assuming student role_id is 1, adjust if different
    NULL, -- oauth_provider
    NULL, -- oauth_id
    1,    -- is_active
    0,    -- is_verified
    NULL, -- last_login
    NOW(), -- created_at
    NOW()  -- updated_at
);

-- Get the user_id of the newly inserted user
SET @new_user_id = (SELECT user_id FROM users WHERE user_email = 'test.student@iiitmanipur.ac.in');

-- Display the inserted user
SELECT 
    user_id,
    user_email,
    user_name,
    role_id,
    created_at
FROM users 
WHERE user_email = 'test.student@iiitmanipur.ac.in';

-- Check if the student record was created by the trigger
SELECT 
    student_id,
    name,
    roll_no,
    program,
    department,
    created_at
FROM students 
WHERE student_id = @new_user_id;

-- Check if the verification record was created by the trigger
SELECT 
    verification_id,
    verified_entity_id,
    verified_entity_type,
    status,
    notes,
    created_at
FROM verifications 
WHERE verified_entity_id = @new_user_id;

-- Display the JSON fields to verify the structure
SELECT 
    student_id,
    name,
    personal_details_json,
    education_details_json,
    experiences_json,
    additional_details_json,
    documents_json
FROM students 
WHERE student_id = @new_user_id;

-- Summary
SELECT 
    'User Inserted' as action,
    COUNT(*) as count
FROM users 
WHERE user_email = 'test.student@iiitmanipur.ac.in'

UNION ALL

SELECT 
    'Student Record Created' as action,
    COUNT(*) as count
FROM students 
WHERE student_id = @new_user_id

UNION ALL

SELECT 
    'Verification Record Created' as action,
    COUNT(*) as count
FROM verifications 
WHERE verified_entity_id = @new_user_id; 