USE tnpdb;

-- Drop the existing stored procedure
DROP PROCEDURE IF EXISTS GetStudentById;

-- Recreate the stored procedure without user_id column
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

DELIMITER ; 




DELIMITER //

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
    IN p_skills_json JSON,
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
        additional_details_json = COALESCE(p_skills_json, additional_details_json),
        documents_json = COALESCE(p_documents_json, documents_json),
        updated_at = CURRENT_TIMESTAMP
    WHERE student_id = p_student_id;
END //

DELIMITER ;