-- Fix the trigger with correct column count
USE tnpdb;

-- Drop the existing trigger
DROP TRIGGER IF EXISTS after_user_insert_student_init;

-- Create the fixed trigger
DELIMITER //

CREATE DEFINER=`root`@`localhost` TRIGGER `after_user_insert_student_init` 
AFTER INSERT ON `users` 
FOR EACH ROW 
BEGIN
    DECLARE student_role_id INT DEFAULT 1; -- Adjust this to match your student role ID
    
    -- Check if the inserted user is a student
    IF NEW.role_id = student_role_id THEN
        -- Insert default student record using the same user_id
        INSERT INTO students (
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
            comments,
            personal_details_json,
            education_details_json,
            experiences_json,
            additional_details_json,
            documents_json
        ) VALUES (
            NEW.user_id, -- Use the same user_id for student_id
            NULL, 
            NEW.user_name,
            'general',
            NULL,
            'other',
            'B.Tech Computer Science and Engineering',
            'CSE',
            1,
            0.00,
            YEAR(CURRENT_DATE)-3,
            YEAR(CURRENT_DATE) + 1,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            'India',
            NULL,
            'Not Specified',
            NULL,
            JSON_OBJECT(
                'mother_name', 'UNK',
                'father_name', 'UNK',
                'guardian_name', 'UNK',
                'personal_email', 'UNK',
                'linkedin_profile', 'UNK',
                'github_profile', 'UNK',
                'portfolio_link', 'UNK'
            ),
            JSON_OBJECT(
                'jee_year', 'UNK',
                'jee_mains_rank', 'UNK',
                'jee_advanced_cleared', false,
                'jee_advanced_rank', 'UNK',
                'tenth_board', 'UNK',
                'tenth_score', 'UNK',
                'tenth_year_of_passing', 'UNK',
                'tenth_school_name', 'UNK',
                'twelfth_board', 'UNK',
                'twelfth_stream', 'UNK',
                'twelfth_score', 'UNK',
                'twelfth_year_of_passing', 'UNK',
                'twelfth_school_name', 'UNK'
            ),
            JSON_OBJECT(
                'internships', JSON_ARRAY(),
                'certificates', JSON_ARRAY(),
                'projects', JSON_ARRAY()
            ),
            JSON_OBJECT(
                'programming_skills', JSON_ARRAY(),
                'area_of_interest', 'UNK',
                'area_of_interest_other', 'UNK'
            ),
            JSON_OBJECT(
                'photo_link', 'UNK',
                'tenth_marksheet_link', 'UNK',
                'twelfth_marksheet_link', 'UNK',
                'jee_main_scorecard_link', 'UNK',
                'jee_advanced_scorecard_link', 'UNK',
                'internship_certificate_link', 'UNK',
                'other_certificate_link', 'UNK'
            )
        );

        -- Insert verification record for the student (FIXED)
        INSERT INTO verifications (
            verified_entity_id,
            verified_entity_type,
            notes
        ) VALUES (
            NEW.user_id, -- Use the same user_id for student_id
            'student',
            'Complete Your Profile and send for verification'
        );
        
        -- Log the student initialization (optional)
        -- You can create a log table if needed for tracking
        -- INSERT INTO student_initialization_log (user_id, student_id, created_at) VALUES (NEW.user_id, NEW.user_id, NOW());
        
    END IF;
END//

DELIMITER ;

-- Test the trigger with a simple user insert
INSERT INTO users (
    user_email,
    user_name,
    role_id
) VALUES (
    'test.student@iiitmanipur.ac.in',
    'Test Student',
    1  -- Adjust this based on your roles table
);

-- Check results
SELECT 'User Inserted' as action, COUNT(*) as count FROM users WHERE user_email = 'test.student@iiitmanipur.ac.in'
UNION ALL
SELECT 'Student Record Created' as action, COUNT(*) as count FROM students WHERE student_id = (SELECT user_id FROM users WHERE user_email = 'test.student@iiitmanipur.ac.in')
UNION ALL
SELECT 'Verification Record Created' as action, COUNT(*) as count FROM verifications WHERE verified_entity_id = (SELECT user_id FROM users WHERE user_email = 'test.student@iiitmanipur.ac.in'); 