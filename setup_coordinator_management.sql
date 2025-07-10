-- Setup for Coordinator Management
-- This script creates the necessary stored procedure and trigger

USE tnpdb;

-- Drop existing procedure if it exists
DROP PROCEDURE IF EXISTS UpsertCoordinator;

-- Create stored procedure for upserting coordinator details
DELIMITER //
CREATE PROCEDURE UpsertCoordinator(
    IN p_coordinator_id CHAR(36),
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255), 
    IN p_phone_number VARCHAR(15),
    IN p_department VARCHAR(255),
    IN p_semester INT,
    IN p_designation VARCHAR(255),
    IN p_team VARCHAR(255),
    OUT p_result_id CHAR(36),
    OUT p_operation VARCHAR(10)
)
BEGIN
    DECLARE coordinator_exists INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check if coordinator exists
    IF p_coordinator_id IS NOT NULL AND p_coordinator_id != '' THEN
        SELECT COUNT(*) INTO coordinator_exists 
        FROM coordinators 
        WHERE coordinator_id = p_coordinator_id;
    END IF;
    
    IF coordinator_exists > 0 THEN
        -- Update existing coordinator
        UPDATE coordinators 
        SET 
            name = p_name,
            email = p_email,
            phone_number = p_phone_number,
            department = p_department,
            semester = p_semester,
            designation = p_designation,
            team = p_team,
            updated_at = CURRENT_TIMESTAMP
        WHERE coordinator_id = p_coordinator_id;
        
        SET p_result_id = p_coordinator_id;
        SET p_operation = 'UPDATE';
    ELSE
        -- Insert new coordinator
        SET p_result_id = COALESCE(p_coordinator_id, UUID());
        
        INSERT INTO coordinators (
            coordinator_id, name, email, phone_number, 
            department, semester, designation, team,
            created_at, updated_at
        ) VALUES (
            p_result_id, p_name, p_email, p_phone_number,
            p_department, p_semester, p_designation, p_team,
            CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        );
        
        SET p_operation = 'INSERT';
    END IF;
    
    COMMIT;
END //
DELIMITER ;

-- Drop existing trigger if it exists
DROP TRIGGER IF EXISTS coordinator_user_sync_insert;
DROP TRIGGER IF EXISTS coordinator_user_sync_update;
DROP TRIGGER IF EXISTS coordinator_user_sync_delete;

-- Create trigger for INSERT operations
DELIMITER //
CREATE TRIGGER coordinator_user_sync_insert
    AFTER INSERT ON coordinators
    FOR EACH ROW
BEGIN
    DECLARE role_id_coordinator INT;
    
    -- Get coordinator role ID
    SELECT id INTO role_id_coordinator FROM roles WHERE name = 'coordinator' LIMIT 1;
    
    -- Insert into users table
    INSERT INTO users (
        user_id, user_email, user_name, role_id, 
        is_active, is_verified, created_at, updated_at
    ) VALUES (
        NEW.coordinator_id, NEW.email, NEW.name, role_id_coordinator,
        1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    )
    ON DUPLICATE KEY UPDATE
        user_email = NEW.email,
        user_name = NEW.name,
        updated_at = CURRENT_TIMESTAMP;
END //
DELIMITER ;

-- Create trigger for UPDATE operations
DELIMITER //
CREATE TRIGGER coordinator_user_sync_update
    AFTER UPDATE ON coordinators
    FOR EACH ROW
BEGIN
    -- Update corresponding user record
    UPDATE users 
    SET 
        user_email = NEW.email,
        user_name = NEW.name,
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = NEW.coordinator_id;
END //
DELIMITER ;

-- Create trigger for DELETE operations
DELIMITER //
CREATE TRIGGER coordinator_user_sync_delete
    AFTER DELETE ON coordinators
    FOR EACH ROW
BEGIN
    -- Remove from users table
    DELETE FROM users WHERE user_id = OLD.coordinator_id;
END //
DELIMITER ;

-- Verify the setup
SHOW PROCEDURE STATUS WHERE Name = 'UpsertCoordinator';
SHOW TRIGGERS WHERE `Table` = 'coordinators';
