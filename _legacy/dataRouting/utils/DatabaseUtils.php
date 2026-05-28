<?php
// utils/DatabaseUtils.php
declare(strict_types=1);

class DatabaseUtils {
    
    /**
     * Check if MySQL version supports JSON functional indexes
     * @param PDO $pdo Database connection
     * @return bool True if JSON functional indexes are supported
     */
    public static function supportsJsonFunctionalIndexes(PDO $pdo): bool {
        try {
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $version = $result['version'];
            
            // MySQL 8.0.13+ supports JSON functional indexes
            return version_compare($version, '8.0.13', '>=');
        } catch (Exception $e) {
            error_log("Error checking MySQL version: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create JSON functional indexes if supported
     * @param PDO $pdo Database connection
     * @return bool True if indexes were created successfully
     */
    public static function createJsonIndexes(PDO $pdo): bool {
        if (!self::supportsJsonFunctionalIndexes($pdo)) {
            error_log("MySQL version does not support JSON functional indexes");
            return false;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Create JSON indexes for students table
            $pdo->exec("
                ALTER TABLE students 
                ADD INDEX idx_personal_email ((CAST(personal_details_json->>'$.personal_email' AS CHAR(255)) COLLATE utf8mb4_bin))
            ");
            
            $pdo->exec("
                ALTER TABLE students 
                ADD INDEX idx_jee_mains_rank ((CAST(education_details_json->'$.jee_mains_rank' AS SIGNED)))
            ");
            
            $pdo->exec("
                ALTER TABLE students 
                ADD INDEX idx_tenth_score ((CAST(education_details_json->'$.tenth_score' AS DECIMAL(5,2))))
            ");
            
            $pdo->exec("
                ALTER TABLE students 
                ADD INDEX idx_twelfth_score ((CAST(education_details_json->'$.twelfth_score' AS DECIMAL(5,2))))
            ");
            
            // Create JSON indexes for recruiters table
            $pdo->exec("
                ALTER TABLE recruiters 
                ADD INDEX idx_company_name ((CAST(company_details_json->>'$.company_name' AS VARCHAR(255)) COLLATE utf8mb4_bin))
            ");
            
            $pdo->exec("
                ALTER TABLE recruiters 
                ADD INDEX idx_company_website ((CAST(company_details_json->>'$.company_website' AS VARCHAR(255)) COLLATE utf8mb4_bin))
            ");
            
            $pdo->exec("
                ALTER TABLE recruiters 
                ADD INDEX idx_company_city ((CAST(company_details_json->>'$.address.city' AS VARCHAR(100)) COLLATE utf8mb4_bin))
            ");
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error creating JSON indexes: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if MySQL version supports generated columns
     * @param PDO $pdo Database connection
     * @return bool True if generated columns are supported
     */
    public static function supportsGeneratedColumns(PDO $pdo): bool {
        try {
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $version = $result['version'];
            
            // MySQL 5.7+ supports generated columns
            return version_compare($version, '5.7.0', '>=');
        } catch (Exception $e) {
            error_log("Error checking MySQL version: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add generated columns to events table if supported
     * @param PDO $pdo Database connection
     * @return bool True if columns were added successfully
     */
    public static function addGeneratedColumns(PDO $pdo): bool {
        if (!self::supportsGeneratedColumns($pdo)) {
            error_log("MySQL version does not support generated columns");
            return false;
        }
        
        try {
            // Check if column already exists
            $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE '_event_all_links_array'");
            if ($stmt->rowCount() > 0) {
                return true; // Column already exists
            }
            
            $pdo->exec("
                ALTER TABLE events 
                ADD COLUMN _event_all_links_array JSON GENERATED ALWAYS AS (JSON_VALUES(event_document, '$.*')) VIRTUAL
            ");
            
            $pdo->exec("
                ALTER TABLE events 
                ADD INDEX idx_event_all_links ((CAST(`_event_all_links_array` AS CHAR(255) ARRAY)) COLLATE utf8mb4_bin)
            ");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error adding generated columns: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get database version information
     * @param PDO $pdo Database connection
     * @return array Version information
     */
    public static function getDatabaseInfo(PDO $pdo): array {
        try {
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $version = $result['version'];
            
            return [
                'version' => $version,
                'supports_json_indexes' => self::supportsJsonFunctionalIndexes($pdo),
                'supports_generated_columns' => self::supportsGeneratedColumns($pdo),
                'php_version' => PHP_VERSION
            ];
        } catch (Exception $e) {
            error_log("Error getting database info: " . $e->getMessage());
            return [
                'version' => 'unknown',
                'supports_json_indexes' => false,
                'supports_generated_columns' => false,
                'php_version' => PHP_VERSION
            ];
        }
    }
    
    /**
     * Initialize database with advanced features if supported
     * @param PDO $pdo Database connection
     * @return array Results of initialization
     */
    public static function initializeDatabase(PDO $pdo): array {
        $results = [
            'json_indexes_created' => false,
            'generated_columns_added' => false,
            'database_info' => self::getDatabaseInfo($pdo)
        ];
        
        // Add generated columns if supported
        if ($results['database_info']['supports_generated_columns']) {
            $results['generated_columns_added'] = self::addGeneratedColumns($pdo);
        }
        
        // Create JSON indexes if supported
        if ($results['database_info']['supports_json_indexes']) {
            $results['json_indexes_created'] = self::createJsonIndexes($pdo);
        }
        
        return $results;
    }
    
    /**
     * Safe JSON decode with error handling for PHP 7.3+
     * @param string $json JSON string to decode
     * @param bool $assoc Return as associative array
     * @return mixed Decoded JSON or null on error
     */
    public static function safeJsonDecode(string $json, bool $assoc = true) {
        $decoded = json_decode($json, $assoc);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Safe JSON encode with error handling for PHP 7.3+
     * @param mixed $data Data to encode
     * @param int $options JSON encode options
     * @return string|false JSON string or false on error
     */
    public static function safeJsonEncode($data, int $options = 0) {
        $encoded = json_encode($data, $options);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON encode error: " . json_last_error_msg());
            return false;
        }
        
        return $encoded;
    }
} 