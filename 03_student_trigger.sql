-- Part 3: Student Auto-Initialization Trigger
-- Improved TNP Database Schema

USE tnpdb;

-- =============================================
-- STUDENT AUTO-INITIALIZATION TRIGGER
-- =============================================

-- First, let's get the student role ID
-- We'll assume the student role has ID = 4 (based on typical role ordering)
-- If this doesn't match your actual role ID, please adjust accordingly

DELIMITER //

-- Drop the trigger if it exists
DROP TRIGGER IF EXISTS after_user_insert_student_init//

-- Create trigger to automatically initialize student details
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

        -- Insert verification record for the student
        INSERT INTO verifications (
            verified_entity_id,
            verified_entity_type,
            notes,
            created_at,
            updated_at
        ) VALUES (
            NEW.user_id, -- Use the same user_id for student_id
            'student',
            'Complete Your Profile and send for verification',
            NOW(),
            NOW()
        );
        
        -- Log the student initialization (optional)
        -- You can create a log table if needed for tracking
        -- INSERT INTO student_initialization_log (user_id, student_id, created_at) VALUES (NEW.user_id, NEW.user_id, NOW());
        
    END IF;
END//

DELIMITER ;

-- =============================================
-- VERIFICATION QUERIES
-- =============================================

-- Query to check if the trigger was created successfully
SHOW TRIGGERS LIKE 'after_user_insert_student_init';

-- Query to verify the student role ID (adjust the role name if different)
SELECT id, name FROM roles WHERE name = 'student';

-- Query to test the trigger (uncomment and run after inserting a test user)
-- INSERT INTO users (user_email, user_name, role_id) VALUES ('test.student@example.com', 'Test Student', 4);
-- SELECT * FROM students WHERE student_id = (SELECT user_id FROM users WHERE user_email = 'test.student@example.com');

-- =============================================
-- NOTES
-- =============================================

/*
IMPORTANT NOTES:

1. ROLE ID: Make sure the student_role_id variable (currently set to 4) matches 
   the actual student role ID in your roles table. You can check this with:
   SELECT id, name FROM roles WHERE name = 'student';

2. ROLL NUMBER: The trigger creates a default roll number using the user_id. 
   You may want to modify this logic based on your institution's roll number format.

3. PROGRAM/DEPARTMENT: The trigger sets default values for program and department.
   Adjust these based on your institution's offerings.

4. YEAR CALCULATIONS: The trigger sets year_of_admission to current year and 
   year_of_passing to current year + 4. Adjust based on your academic calendar.

5. JSON STRUCTURE: The trigger initializes all JSON fields with empty or default values.
   This ensures the student profile form can work with these fields immediately.

6. TESTING: After creating the trigger, test it by inserting a new user with student role
   and verify that a corresponding student record is created automatically.

7. ERROR HANDLING: The trigger includes basic error handling. Consider adding more
   sophisticated error handling if needed for your use case.
*/

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

CREATE PROCEDURE GetStudentById(
    IN p_student_id CHAR(36)
)
BEGIN
    SELECT 
        student_id,
        roll_no,
        name,
        category,
        date_of_birth,
        gender,
        blood_group,
        phone_number,
        locality,
        city,
        state,
        country,
        pincode,
        program,
        department,
        current_semester,
        cpi,
        year_of_admission,
        year_of_passing,
        placement_interest,
        comments,
        personal_details_json,
        education_details_json,
        experiences_json,
        additional_details_json,
        documents_json,
        created_at,
        updated_at
    FROM students
    WHERE student_id = p_student_id;
END //

CREATE PROCEDURE UpdateStudentById(
    IN p_student_id CHAR(36),
    IN p_roll_no VARCHAR(20),
    IN p_name VARCHAR(255),
    IN p_category ENUM('general', 'obc', 'sc', 'st', 'ews', 'pwd'),
    IN p_date_of_birth DATE,
    IN p_gender ENUM('male', 'female', 'other'),
    IN p_blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    IN p_phone_number VARCHAR(15),
    IN p_locality VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(100),
    IN p_country VARCHAR(100),
    IN p_pincode VARCHAR(10),
    IN p_program VARCHAR(100),
    IN p_department VARCHAR(100),
    IN p_current_semester INT,
    IN p_cpi DECIMAL(4,2),
    IN p_year_of_admission INT,
    IN p_year_of_passing INT,
    IN p_placement_interest TINYINT(1),
    IN p_comments TEXT,
    IN p_personal_details_json JSON,
    IN p_education_details_json JSON,
    IN p_experiences_json JSON,
    IN p_additional_details_json JSON,
    IN p_documents_json JSON
)
BEGIN
    -- Ensure p_student_id is not NULL
    IF p_student_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'student_id cannot be NULL';
    END IF;

    UPDATE students
    SET
        roll_no = COALESCE(p_roll_no, roll_no),
        name = COALESCE(p_name, name),
        category = COALESCE(p_category, category),
        date_of_birth = COALESCE(p_date_of_birth, date_of_birth),
        gender = COALESCE(p_gender, gender),
        blood_group = COALESCE(p_blood_group, blood_group),
        phone_number = COALESCE(p_phone_number, phone_number),
        locality = COALESCE(p_locality, locality),
        city = COALESCE(p_city, city),
        state = COALESCE(p_state, state),
        country = COALESCE(p_country, country),
        pincode = COALESCE(p_pincode, pincode),
        program = COALESCE(p_program, program),
        department = COALESCE(p_department, department),
        current_semester = COALESCE(p_current_semester, current_semester),
        cpi = COALESCE(p_cpi, cpi),
        year_of_admission = COALESCE(p_year_of_admission, year_of_admission),
        year_of_passing = COALESCE(p_year_of_passing, year_of_passing),
        placement_interest = COALESCE(p_placement_interest, placement_interest),
        comments = COALESCE(p_comments, comments),
        personal_details_json = COALESCE(p_personal_details_json, personal_details_json),
        education_details_json = COALESCE(p_education_details_json, education_details_json),
        experiences_json = COALESCE(p_experiences_json, experiences_json),
        additional_details_json = COALESCE(p_additional_details_json, additional_details_json),
        documents_json = COALESCE(p_documents_json, documents_json),
        updated_at = CURRENT_TIMESTAMP
    WHERE student_id = p_student_id;
END //

CREATE PROCEDURE GetRecruiterList()
BEGIN
    SELECT 
        r.recruiter_id AS recruiterId,
        r.company_details_json AS CompanyDetailsJson,
        v.status AS Status,
        r.created_at,
        r.updated_at
    FROM recruiters AS r
    JOIN verifications AS v
        ON r.recruiter_id = v.verified_entity_id
        AND v.verified_entity_type = 'recruiter'
    WHERE v.status != 'draft';
END //

DELIMITER ; 


