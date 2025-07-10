# PHP 7.3+ Compatibility Guide

## Overview

This guide explains the changes made to ensure the TNP Portal is compatible with PHP 7.3+ while maintaining functionality and performance.

## Changes Made

### 1. **Composer.json Updates**
- **Before**: `"php": "^7.4 || ^8.0 || ^8.2"`
- **After**: `"php": "^7.3 || ^8.0 || ^8.2"`

### 2. **Auth Utils Compatibility**
- **Removed**: `never` return type (PHP 8.1+ feature)
- **Replaced with**: `void` return type
- **Files affected**: `dataRouting/utils/auth_utils.php`

### 3. **Database Schema Compatibility**
- **Created**: PHP 7.3+ compatible schema files
- **Removed**: Advanced MySQL 8.0+ features from base schema
- **Added**: Conditional feature detection and application

## New Files Created

### Database Schema Files (PHP 7.3+ Compatible)
- `01_roles_permissions_php73.sql`
- `02_user_profiles_php73.sql`
- `03_events_participants_php73.sql`
- `04_verifications_data_php73.sql`

### Utility Classes
- `dataRouting/utils/DatabaseUtils.php` - Handles conditional database features

## Installation Instructions

### Step 1: Install Dependencies
```bash
cd dataRouting
composer install
```

### Step 2: Create Database
```sql
CREATE DATABASE tnpdb;
USE tnpdb;
```

### Step 3: Execute Schema Files
```bash
# Execute in sequence
mysql -u your_username -p tnpdb < 01_roles_permissions_php73.sql
mysql -u your_username -p tnpdb < 02_user_profiles_php73.sql
mysql -u your_username -p tnpdb < 03_events_participants_php73.sql
mysql -u your_username -p tnpdb < 04_verifications_data_php73.sql
```

### Step 4: Initialize Advanced Features
```php
<?php
require_once 'dataRouting/config/db.php';
require_once 'dataRouting/utils/DatabaseUtils.php';

$pdo = require 'dataRouting/config/db.php';
if ($pdo) {
    $results = DatabaseUtils::initializeDatabase($pdo);
    print_r($results);
}
```

## Feature Detection

### Database Features
The system automatically detects and enables features based on your MySQL version:

| Feature | MySQL Version Required | Auto-Enabled |
|---------|----------------------|--------------|
| JSON Functional Indexes | 8.0.13+ | ✅ Yes |
| Generated Columns | 5.7+ | ✅ Yes |
| JSON Data Type | 5.7+ | ✅ Yes |

### PHP Features
All code is compatible with PHP 7.3+ features:

| Feature | PHP Version | Status |
|---------|-------------|--------|
| Type Declarations | 7.0+ | ✅ Supported |
| Return Type Declarations | 7.0+ | ✅ Supported |
| Null Coalescing Operator | 7.0+ | ✅ Supported |
| Array Destructuring | 7.1+ | ✅ Supported |
| Void Return Type | 7.1+ | ✅ Supported |
| Strict Types | 7.0+ | ✅ Supported |

## Usage Examples

### Database Initialization
```php
<?php
require_once 'dataRouting/config/db.php';
require_once 'dataRouting/utils/DatabaseUtils.php';

$pdo = require 'dataRouting/config/db.php';
if ($pdo) {
    // Initialize database with advanced features
    $results = DatabaseUtils::initializeDatabase($pdo);
    
    if ($results['json_indexes_created']) {
        echo "JSON indexes created successfully\n";
    }
    
    if ($results['generated_columns_added']) {
        echo "Generated columns added successfully\n";
    }
    
    echo "Database version: " . $results['database_info']['version'] . "\n";
    echo "PHP version: " . $results['database_info']['php_version'] . "\n";
}
```

### Safe JSON Operations
```php
<?php
require_once 'dataRouting/utils/DatabaseUtils.php';

// Safe JSON decode
$jsonString = '{"name": "John", "age": 30}';
$data = DatabaseUtils::safeJsonDecode($jsonString);
if ($data !== null) {
    echo "Name: " . $data['name'] . "\n";
}

// Safe JSON encode
$data = ['name' => 'John', 'age' => 30];
$jsonString = DatabaseUtils::safeJsonEncode($data);
if ($jsonString !== false) {
    echo "JSON: " . $jsonString . "\n";
}
```

### Authentication (Updated)
```php
<?php
require_once 'dataRouting/utils/auth_utils.php';

// Generate session token
$token = AuthUtils::generateSessionToken('user@example.com', 'student');

// Set session cookie
AuthUtils::setSessionCookie($token);

// Send error response (now returns void instead of never)
AuthUtils::sendErrorResponse('Invalid credentials', 401);

// Send success response (now returns void instead of never)
AuthUtils::sendSuccessResponse(['user_id' => '123']);
```

## Performance Considerations

### JSON Indexes
- **MySQL 8.0.13+**: JSON functional indexes are automatically created
- **Older versions**: Queries will work but may be slower on JSON columns
- **Fallback**: Regular indexes on JSON columns are still available

### Generated Columns
- **MySQL 5.7+**: Generated columns are automatically added
- **Older versions**: Application handles JSON operations in PHP
- **Performance**: Minimal impact as operations are handled efficiently

## Migration from Original Schema

If you have existing data, follow these steps:

1. **Backup existing data**
2. **Create new schema using PHP 7.3+ files**
3. **Migrate data**
4. **Run database initialization**
5. **Verify functionality**

## Troubleshooting

### Common Issues

1. **JSON Index Creation Fails**
   - Check MySQL version: `SELECT VERSION();`
   - Ensure MySQL 8.0.13+ for JSON functional indexes
   - Application will work without them (slower queries)

2. **Generated Columns Not Created**
   - Check MySQL version: `SELECT VERSION();`
   - Ensure MySQL 5.7+ for generated columns
   - Application handles JSON operations in PHP

3. **Composer Install Fails**
   - Ensure PHP 7.3+ is installed
   - Check PHP version: `php -v`
   - Update Composer if needed

### Error Handling
All functions include proper error handling:
- Database operations use try-catch blocks
- JSON operations have safe fallbacks
- Version detection handles unknown versions gracefully

## Testing

### PHP Version Test
```php
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Required: 7.3.0+\n";

if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    echo "✅ PHP version is compatible\n";
} else {
    echo "❌ PHP version is not compatible\n";
}
```

### Database Feature Test
```php
<?php
require_once 'dataRouting/config/db.php';
require_once 'dataRouting/utils/DatabaseUtils.php';

$pdo = require 'dataRouting/config/db.php';
if ($pdo) {
    $info = DatabaseUtils::getDatabaseInfo($pdo);
    print_r($info);
}
```

## Support

For issues or questions:
1. Check PHP version compatibility
2. Verify MySQL version requirements
3. Review error logs for specific issues
4. Test with the provided examples

## Version History

- **v2.1**: PHP 7.3+ compatibility
- **v2.0**: Original improved schema
- **v1.0**: Original schema (reference only) 