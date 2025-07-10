<?php
/**
 * PHP 7.3+ Compatibility Test Script
 * Run this script to verify your environment is compatible
 */

echo "=== PHP 7.3+ Compatibility Test ===\n\n";

// Test 1: PHP Version
echo "1. PHP Version Check:\n";
echo "   Current PHP Version: " . PHP_VERSION . "\n";
echo "   Required Version: 7.3.0+\n";

if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    echo "   ✅ PHP version is compatible\n";
} else {
    echo "   ❌ PHP version is NOT compatible\n";
    exit(1);
}

// Test 2: Required Extensions
echo "\n2. Required Extensions Check:\n";
$required_extensions = [
    'pdo',
    'pdo_mysql',
    'json',
    'openssl',
    'mbstring'
];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext extension is loaded\n";
    } else {
        echo "   ❌ $ext extension is NOT loaded\n";
    }
}

// Test 3: PHP Features
echo "\n3. PHP Features Check:\n";

// Test type declarations
try {
    function testTypeDeclaration(string $param): string {
        return $param;
    }
    $result = testTypeDeclaration("test");
    echo "   ✅ Type declarations work\n";
} catch (Error $e) {
    echo "   ❌ Type declarations don't work: " . $e->getMessage() . "\n";
}

// Test null coalescing operator
try {
    $test = null ?? "default";
    echo "   ✅ Null coalescing operator works\n";
} catch (ParseError $e) {
    echo "   ❌ Null coalescing operator doesn't work: " . $e->getMessage() . "\n";
}

// Test array destructuring
try {
    $array = ['a', 'b'];
    [$first, $second] = $array;
    echo "   ✅ Array destructuring works\n";
} catch (ParseError $e) {
    echo "   ❌ Array destructuring doesn't work: " . $e->getMessage() . "\n";
}

// Test 4: Database Connection (if config exists)
echo "\n4. Database Connection Test:\n";
if (file_exists('dataRouting/config/db.php')) {
    try {
        $pdo = require 'dataRouting/config/db.php';
        if ($pdo instanceof PDO) {
            echo "   ✅ Database connection successful\n";
            
            // Test database version
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   MySQL Version: " . $result['version'] . "\n";
            
            // Test DatabaseUtils if available
            if (file_exists('dataRouting/utils/DatabaseUtils.php')) {
                require_once 'dataRouting/utils/DatabaseUtils.php';
                
                $info = DatabaseUtils::getDatabaseInfo($pdo);
                echo "   JSON Functional Indexes Supported: " . ($info['supports_json_indexes'] ? 'Yes' : 'No') . "\n";
                echo "   Generated Columns Supported: " . ($info['supports_generated_columns'] ? 'Yes' : 'No') . "\n";
                
                // Test safe JSON operations
                $testData = ['name' => 'Test', 'value' => 123];
                $jsonString = DatabaseUtils::safeJsonEncode($testData);
                $decodedData = DatabaseUtils::safeJsonDecode($jsonString);
                
                if ($jsonString !== false && $decodedData !== null) {
                    echo "   ✅ Safe JSON operations work\n";
                } else {
                    echo "   ❌ Safe JSON operations failed\n";
                }
            } else {
                echo "   ⚠️  DatabaseUtils.php not found\n";
            }
        } else {
            echo "   ❌ Database connection failed\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Database connection error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Database config file not found\n";
}

// Test 5: Auth Utils (if available)
echo "\n5. Auth Utils Test:\n";
if (file_exists('dataRouting/utils/auth_utils.php')) {
    try {
        require_once 'dataRouting/utils/auth_utils.php';
        
        // Test session token generation
        $token = AuthUtils::generateSessionToken('test@example.com', 'student');
        if (!empty($token)) {
            echo "   ✅ Session token generation works\n";
        } else {
            echo "   ❌ Session token generation failed\n";
        }
        
        // Test token parsing
        $parsed = AuthUtils::parseSessionToken($token);
        if ($parsed && isset($parsed['email'])) {
            echo "   ✅ Session token parsing works\n";
        } else {
            echo "   ❌ Session token parsing failed\n";
        }
        
        // Test email validation
        $validEmail = AuthUtils::validateEmail('test@example.com');
        $invalidEmail = AuthUtils::validateEmail('invalid-email');
        
        if ($validEmail && !$invalidEmail) {
            echo "   ✅ Email validation works\n";
        } else {
            echo "   ❌ Email validation failed\n";
        }
        
        // Test role validation
        $validRole = AuthUtils::isValidRole('student');
        $invalidRole = AuthUtils::isValidRole('invalid_role');
        
        if ($validRole && !$invalidRole) {
            echo "   ✅ Role validation works\n";
        } else {
            echo "   ❌ Role validation failed\n";
        }
        
    } catch (Error $e) {
        echo "   ❌ Auth Utils error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Auth Utils file not found\n";
}

// Test 6: Composer Autoloader
echo "\n6. Composer Autoloader Test:\n";
if (file_exists('dataRouting/vendor/autoload.php')) {
    try {
        require_once 'dataRouting/vendor/autoload.php';
        echo "   ✅ Composer autoloader works\n";
    } catch (Exception $e) {
        echo "   ❌ Composer autoloader error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Composer autoloader not found (run 'composer install')\n";
}

echo "\n=== Test Complete ===\n";

// Summary
echo "\nSummary:\n";
echo "If you see ✅ marks for all tests, your environment is compatible with PHP 7.3+\n";
echo "If you see ❌ marks, please address those issues before proceeding\n";
echo "If you see ⚠️ marks, those are optional features that can be set up later\n"; 