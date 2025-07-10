USE tnpdb;

-- Drop the procedure if it exists
DROP PROCEDURE IF EXISTS UpdateVerficationStatusToVerifiedById;

DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateVerficationStatusToVerifiedById`(
    IN entity_id CHAR(36),
    IN coordinator_id CHAR(36)
)
BEGIN
    -- Ensure entity_id is not NULL
    IF entity_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'entity_id cannot be NULL';
    END IF;

    UPDATE verifications
    SET
        verified_by_user_id = COALESCE(coordinator_id, verified_by_user_id),
        verified_on =  CURRENT_DATE,
        status = 'verified',
        notes ='Verified by Coordinator',
        updated_at = CURRENT_TIMESTAMP
    WHERE verified_entity_id = entity_id;
END//
DELIMITER ;

-- Test the procedure
CALL UpdateVerficationStatusToVerifiedById('5327d143-5aec-11f0-b6d3-cc4740c7c70f','5327d143-5aec-11f0-b6d3-cc4740c7c70v');

-- Check the updated record
SELECT * FROM verifications WHERE verified_entity_id = '5327d143-5aec-11f0-b6d3-cc4740c7c70f'; 