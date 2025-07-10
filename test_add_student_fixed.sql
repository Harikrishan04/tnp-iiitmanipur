-- Fixed test script to add a new student user
USE tnpdb;

-- First, let's see the table structure
DESCRIBE users;

-- Check current roles
SELECT id, name FROM roles ORDER BY id;

-- Insert a new test student user (using only the required fields)
-- Let MySQL handle the defaults for other fields
INSERT INTO users (
    user_email,
    user_name,
    role_id
) VALUES (
    'test.student@iiitmanipur.ac.in',
    'Test Student',
    1  -- Adjust this based on your roles table
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

-- Show summary
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