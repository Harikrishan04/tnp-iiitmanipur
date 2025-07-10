-- Check the users table structure
USE tnpdb;

-- Show the complete table structure
DESCRIBE users;

-- Show the CREATE TABLE statement
SHOW CREATE TABLE users;

-- Check if there are any existing users
SELECT COUNT(*) as total_users FROM users;

-- Check the roles table
SELECT id, name FROM roles ORDER BY id; 