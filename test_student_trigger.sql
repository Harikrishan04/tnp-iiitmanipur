-- Test Script for Student Trigger
-- This script will test the trigger by adding a new user with student role

USE tnpdb;

-- =============================================
-- TEST THE STUDENT TRIGGER
-- =============================================

-- First, let's check the current roles to confirm student role ID
SELECT 'Current Roles:' as info;
SELECT id, name, description FROM roles ORDER BY id;

-- Check if there are any existing users
SELECT 'Existing Users:' as info;
SELECT user_id, user_email, user_name, role_id FROM users LIMIT 5;

-- Check if there are any existing students
SELECT 'Existing Students:' as info;
SELECT student_id, name, roll_no FROM students LIMIT 5;

-- =============================================
-- INSERT TEST STUDENT USER
-- =============================================

-- Insert a new user with student role (assuming role_id = 1 for student)
INSERT INTO users (user_email, user_name, role_id) 
VALUES ('test.student@example.com', 'Test Student', 1);

-- Get the user_id of the newly inserted user
SET @new_user_id = (SELECT user_id FROM users WHERE user_email = 'test.student@example.com');

-- =============================================
-- VERIFY THE TRIGGER WORKED
-- =============================================

-- Check if the student record was created
SELECT 'New Student Record:' as info;
SELECT 
    student_id,
    roll_no,
    name,
    category,
    date_of_birth,
    gender,
    program,
    department,
    current_semester,
    cpi,
    year_of_admission,
    year_of_passing,
    blood_group,
    phone_number,
    locality,
    city,
    state,
    country,
    pincode,
    placement_interest,
    comments
FROM students 
WHERE student_id = @new_user_id;

-- Check the JSON fields
SELECT 'Personal Details JSON:' as info;
SELECT personal_details_json FROM students WHERE student_id = @new_user_id;

SELECT 'Education Details JSON:' as info;
SELECT education_details_json FROM students WHERE student_id = @new_user_id;

SELECT 'Experiences JSON:' as info;
SELECT experiences_json FROM students WHERE student_id = @new_user_id;

SELECT 'Additional Details JSON:' as info;
SELECT additional_details_json FROM students WHERE student_id = @new_user_id;

SELECT 'Documents JSON:' as info;
SELECT documents_json FROM students WHERE student_id = @new_user_id;

-- =============================================
-- VERIFY USER-STUDENT RELATIONSHIP
-- =============================================

-- Check that the user_id matches the student_id
SELECT 'User-Student Relationship:' as info;
SELECT 
    u.user_id,
    u.user_email,
    u.user_name,
    u.role_id,
    s.student_id,
    s.name as student_name,
    s.roll_no
FROM users u
LEFT JOIN students s ON u.user_id = s.student_id
WHERE u.user_email = 'test.student@example.com';

-- =============================================
-- CLEANUP (OPTIONAL)
-- =============================================

-- Uncomment the following lines if you want to clean up the test data
-- DELETE FROM students WHERE student_id = @new_user_id;
-- DELETE FROM users WHERE user_id = @new_user_id;
-- SELECT 'Test data cleaned up' as info;

-- =============================================
-- SUMMARY
-- =============================================

SELECT 'Trigger Test Summary:' as info;
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'SUCCESS: Student record created automatically'
        ELSE 'FAILED: No student record found'
    END as trigger_status
FROM students 
WHERE student_id = @new_user_id; 