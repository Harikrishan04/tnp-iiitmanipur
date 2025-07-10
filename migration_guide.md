# Migration Guide: Original Schema to Improved Schema

## Pre-Migration Checklist

### 1. **Backup Your Data**
```bash
# Create a full backup
mysqldump -u your_username -p tnpdb > tnpdb_backup_$(date +%Y%m%d_%H%M%S).sql

# Backup specific tables if needed
mysqldump -u your_username -p tnpdb users students recruiters coordinators events participants > critical_tables_backup.sql
```

### 2. **Check MySQL Version**
```sql
SELECT VERSION();
```
Ensure you're running MySQL 8.0.13+ for functional indexes support.

### 3. **Verify Current Schema**
```sql
-- Check existing tables
SHOW TABLES;

-- Check for existing data
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM students;
SELECT COUNT(*) FROM recruiters;
SELECT COUNT(*) FROM coordinators;
SELECT COUNT(*) FROM events;
SELECT COUNT(*) FROM participants;
```

## Migration Strategy

### Option 1: Fresh Installation (Recommended for New Projects)

1. **Drop existing database** (if no important data)
```sql
DROP DATABASE tnpdb;
CREATE DATABASE tnpdb;
USE tnpdb;
```

2. **Execute improved schema**
```bash
cat *.sql | mysql -u your_username -p tnpdb
```

### Option 2: In-Place Migration (For Existing Data)

#### Step 1: Create Missing Tables
```sql
-- Create role_permissions table
CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX idx_role_permission (role_id, permission_id)
);
```

#### Step 2: Add Missing Columns
```sql
-- Add user_id to students table
ALTER TABLE students ADD COLUMN user_id CHAR(36) AFTER student_id;
ALTER TABLE students ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

-- Add user_id to recruiters table
ALTER TABLE recruiters ADD COLUMN user_id CHAR(36) AFTER recruiter_id;
ALTER TABLE recruiters ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

-- Add user_id to coordinators table
ALTER TABLE coordinators ADD COLUMN user_id CHAR(36) AFTER coordinator_id;
ALTER TABLE coordinators ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

-- Add is_verified to users table
ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_active;
```

#### Step 3: Fix Foreign Key References
```sql
-- Fix participants table references
ALTER TABLE participants DROP INDEX idx_student_id;
ALTER TABLE participants ADD INDEX idx_participant_id (participant_id);
ALTER TABLE participants ADD FOREIGN KEY (participant_id) REFERENCES students(student_id) ON DELETE CASCADE;
ALTER TABLE participants ADD FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE;

-- Fix event_sub_process_participants table
ALTER TABLE event_sub_process_participants DROP INDEX idx_actual_start_datetime;
ALTER TABLE event_sub_process_participants ADD FOREIGN KEY (participant_id) REFERENCES participants(participant_entry_id) ON DELETE CASCADE;
ALTER TABLE event_sub_process_participants ADD FOREIGN KEY (sub_process_id) REFERENCES event_sub_processes(sub_process_id) ON DELETE CASCADE;
```

#### Step 4: Add Missing Indexes
```sql
-- Add indexes to users table
ALTER TABLE users ADD INDEX idx_user_email (user_email);
ALTER TABLE users ADD INDEX idx_oauth_provider_id (oauth_provider, oauth_id);
ALTER TABLE users ADD INDEX idx_user_active (is_active);
ALTER TABLE users ADD INDEX idx_user_verified (is_verified);

-- Add functional indexes for JSON columns
ALTER TABLE students ADD INDEX idx_personal_email ((CAST(personal_details_json->>'$.personal_email' AS CHAR(255)) COLLATE utf8mb4_bin));
ALTER TABLE students ADD INDEX idx_jee_mains_rank ((CAST(education_details_json->'$.jee_mains_rank' AS SIGNED)));
ALTER TABLE students ADD INDEX idx_tenth_score ((CAST(education_details_json->'$.tenth_score' AS DECIMAL(5,2))));
ALTER TABLE students ADD INDEX idx_twelfth_score ((CAST(education_details_json->'$.twelfth_score' AS DECIMAL(5,2))));

ALTER TABLE recruiters ADD INDEX idx_company_name ((CAST(company_details_json->>'$.company_name' AS VARCHAR(255)) COLLATE utf8mb4_bin));
ALTER TABLE recruiters ADD INDEX idx_company_website ((CAST(company_details_json->>'$.company_website' AS VARCHAR(255)) COLLATE utf8mb4_bin));
ALTER TABLE recruiters ADD INDEX idx_company_city ((CAST(company_details_json->>'$.address.city' AS VARCHAR(100)) COLLATE utf8mb4_bin));
```

#### Step 5: Populate Missing Data
```sql
-- Create user records for existing students
INSERT INTO users (user_id, user_email, user_name, role_id, is_active, is_verified)
SELECT 
    UUID() as user_id,
    COALESCE(JSON_UNQUOTE(personal_details_json->'$.personal_email'), CONCAT(roll_no, '@iiitmanipur.ac.in')) as user_email,
    name as user_name,
    1 as role_id, -- student role
    1 as is_active,
    0 as is_verified
FROM students
WHERE user_id IS NULL;

-- Update students with user_id
UPDATE students s
JOIN users u ON u.user_email = COALESCE(JSON_UNQUOTE(s.personal_details_json->'$.personal_email'), CONCAT(s.roll_no, '@iiitmanipur.ac.in'))
SET s.user_id = u.user_id
WHERE s.user_id IS NULL;

-- Similar process for recruiters and coordinators
-- (Adjust email mapping based on your data structure)
```

## Post-Migration Verification

### 1. **Check Data Integrity**
```sql
-- Verify foreign key relationships
SELECT COUNT(*) as orphaned_students 
FROM students s 
LEFT JOIN users u ON s.user_id = u.user_id 
WHERE u.user_id IS NULL;

SELECT COUNT(*) as orphaned_participants 
FROM participants p 
LEFT JOIN students s ON p.participant_id = s.student_id 
WHERE s.student_id IS NULL;

-- Check for duplicate entries
SELECT roll_no, COUNT(*) as count 
FROM students 
GROUP BY roll_no 
HAVING count > 1;
```

### 2. **Test Common Queries**
```sql
-- Test user permissions query
SELECT p.name, p.module, p.action
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE u.user_id = (SELECT user_id FROM users LIMIT 1);

-- Test student profile query
SELECT s.*, u.is_verified
FROM students s
JOIN users u ON s.user_id = u.user_id
LIMIT 5;
```

### 3. **Performance Testing**
```sql
-- Check index usage
EXPLAIN SELECT * FROM students WHERE department = 'CSE';

-- Test JSON queries
EXPLAIN SELECT * FROM students 
WHERE JSON_UNQUOTE(personal_details_json->'$.personal_email') = 'test@example.com';
```

## Rollback Plan

If issues occur, you can rollback:

```bash
# Restore from backup
mysql -u your_username -p tnpdb < tnpdb_backup_YYYYMMDD_HHMMSS.sql
```

## Application Code Updates

### 1. **Update User Creation**
```php
// Old way
$student = new Student();
$student->save();

// New way
$user = new User();
$user->role_id = 1; // student role
$user->save();

$student = new Student();
$student->user_id = $user->user_id;
$student->save();
```

### 2. **Update Queries**
```php
// Old way
$student = Student::find($student_id);

// New way
$student = Student::with('user')->find($student_id);
```

### 3. **Update Permissions**
```php
// New permission checking
$user = User::with(['role.permissions'])->find($user_id);
$hasPermission = $user->role->permissions->contains('name', 'event.create');
```

## Troubleshooting Migration Issues

### Common Problems

1. **Foreign Key Constraint Errors**
   - Check for orphaned records
   - Verify data types match
   - Ensure referenced records exist

2. **JSON Index Errors**
   - Verify MySQL version
   - Check JSON syntax
   - Ensure JSON columns contain valid JSON

3. **Performance Issues**
   - Monitor slow query log
   - Add missing indexes
   - Optimize queries

### Support Commands

```sql
-- Check table structure
DESCRIBE table_name;

-- Check foreign keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'tnpdb';

-- Check indexes
SHOW INDEX FROM table_name;
```

## Final Steps

1. **Update application code** to use new schema
2. **Test thoroughly** in staging environment
3. **Monitor performance** after migration
4. **Update documentation** for team members
5. **Schedule regular backups** with new schema 