# TNP Database Schema - Improved Version

## Overview

This repository contains an improved version of the TNP (Training and Placement) database schema with enhanced data integrity, performance optimizations, and better structure.

## Files Structure

```
├── schema_improvements.md          # Detailed analysis of improvements
├── 01_roles_permissions.sql        # Part 1: Roles, permissions, and users
├── 02_user_profiles.sql           # Part 2: Student, recruiter, coordinator profiles
├── 03_events_participants.sql     # Part 3: Events and participants management
├── 04_verifications_data.sql      # Part 4: Verifications and initial data
└── README.md                      # This file
```

## Key Improvements Made

### 1. **Fixed Critical Issues**
- ✅ Added missing `role_permissions` table
- ✅ Fixed foreign key reference inconsistencies
- ✅ Added proper foreign key constraints
- ✅ Corrected column naming inconsistencies

### 2. **Enhanced Data Integrity**
- ✅ Added `user_id` foreign key to all profile tables
- ✅ Implemented proper CASCADE/SET NULL rules
- ✅ Added unique constraints where needed
- ✅ Enhanced verification system

### 3. **Performance Optimizations**
- ✅ Added comprehensive indexing strategy
- ✅ Functional indexes for JSON columns
- ✅ Composite indexes for common queries
- ✅ Generated columns for complex operations

### 4. **Better Structure**
- ✅ Consistent naming conventions
- ✅ Proper data types and constraints
- ✅ Enhanced ENUM definitions
- ✅ Better default values

## Installation Instructions

### Step 1: Create Database
```sql
CREATE DATABASE tnpdb;
USE tnpdb;
```

### Step 2: Execute Schema Files
Execute the files in order:

```bash
# Execute in sequence
mysql -u your_username -p tnpdb < 01_roles_permissions.sql
mysql -u your_username -p tnpdb < 02_user_profiles.sql
mysql -u your_username -p tnpdb < 03_events_participants.sql
mysql -u your_username -p tnpdb < 04_verifications_data.sql
```

Or execute all at once:
```bash
cat *.sql | mysql -u your_username -p tnpdb
```

## Schema Overview

### Core Tables

#### 1. **Authentication & Authorization**
- `users` - Base user accounts with role assignment
- `roles` - User roles (student, recruiter, admin, coordinator)
- `permissions` - System permissions
- `role_permissions` - Role-permission mappings

#### 2. **User Profiles**
- `students` - Student-specific information
- `recruiters` - Recruiter/company information
- `coordinators` - Coordinator information

#### 3. **Event Management**
- `events` - Event/job postings
- `participants` - Student participation in events
- `event_sub_processes` - Multi-stage event processes
- `event_sub_process_participants` - Individual stage participation

#### 4. **Verification System**
- `verifications` - Comprehensive verification tracking

## Key Features

### 1. **Role-Based Access Control**
- Granular permissions per module and action
- Flexible role-permission mapping
- Support for complex access patterns

### 2. **Multi-Stage Event Processing**
- Support for complex recruitment processes
- Individual tracking per stage
- Flexible status management

### 3. **Comprehensive Verification**
- Multi-entity verification support
- Audit trail for all verifications
- Flexible verification workflows

### 4. **JSON Flexibility**
- Structured data in JSON columns
- Functional indexes for performance
- Flexible schema evolution

## Performance Considerations

### Indexing Strategy
- **Primary Keys**: All tables have proper primary keys
- **Foreign Keys**: All foreign key columns are indexed
- **JSON Columns**: Functional indexes for common queries
- **Composite Indexes**: Multi-column indexes for complex queries

### Query Optimization
- Use generated columns for complex JSON operations
- Leverage functional indexes for JSON path queries
- Consider partitioning for large tables

## Security Features

### 1. **Data Protection**
- Foreign key constraints prevent orphaned records
- Unique constraints prevent duplicates
- Proper CASCADE rules for data cleanup

### 2. **Access Control**
- Role-based permissions
- Granular permission system
- User verification tracking

### 3. **Audit Trail**
- Timestamps on all critical operations
- Verification tracking
- Status change
 history

## Common Queries

### Get User Permissions
```sql
SELECT p.name, p.module, p.action
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE u.user_id = 'user-uuid';
```

### Get Student Profile with Verification
```sql
SELECT s.*, u.is_verified, v.status as verification_status
FROM students s
JOIN users u ON s.user_id = u.user_id
LEFT JOIN verifications v ON s.student_id = v.verified_entity_id 
    AND v.verified_entity_type = 'student'
WHERE s.student_id = 'student-uuid';
```

### Get Event Participants
```sql
SELECT p.*, s.name, s.roll_no, s.department
FROM participants p
JOIN students s ON p.participant_id = s.student_id
WHERE p.event_id = 'event-uuid'
ORDER BY p.registration_datetime;
```

## Migration from Original Schema

If you have existing data, follow these steps:

1. **Backup existing data**
2. **Create new schema**
3. **Migrate data with transformations**
4. **Verify data integrity**
5. **Update application code**

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**
   - Ensure all referenced records exist
   - Check data types match exactly

2. **JSON Index Errors**
   - Verify MySQL version supports functional indexes (8.0.13+)
   - Check JSON path syntax

3. **Performance Issues**
   - Monitor query execution plans
   - Add missing indexes as needed
   - Consider query optimization

## Support

For issues or questions:
1. Check the `schema_improvements.md` file for detailed analysis
2. Review the troubleshooting section
3. Verify your MySQL version compatibility

## Version History

- **v2.0**: Improved schema with better integrity and performance
- **v1.0**: Original schema (reference only) 