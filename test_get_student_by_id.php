<?php
/**
 * Test file for GetStudentById API endpoint
 * Usage: php test_get_student_by_id.php
 */

// Test configuration
$baseUrl = 'http://localhost/tnp@iiitmanipur/dataRouting/api/student/GetStudentById.php';

// Test cases
$testCases = [
    [
        'name' => 'Test 1: Valid student ID',
        'params' => ['student_id' => '3fb133f3-5aa1-11f0-b6d3-cc4740c7c70f'],
        'expected_status' => 200
    ],
    [
        'name' => 'Test 2: Missing student_id parameter',
        'params' => [],
        'expected_status' => 400
    ],
    [
        'name' => 'Test 3: Invalid UUID format',
        'params' => ['student_id' => 'invalid-uuid'],
        'expected_status' => 400
    ],
    [
        'name' => 'Test 4: Non-existent student ID',
        'params' => ['student_id' => '3fb133f3-5aa1-11f0-b6d3-cc4740c7c70d'],
        'expected_status' => 404
    ],
    [
        'name' => 'Test 5: Empty student_id',
        'params' => ['student_id' => ''],
        'expected_status' => 400
    ]
];

function makeRequest($url, $params = []) {
    $fullUrl = $url . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

function testApi() {
    global $baseUrl, $testCases;
    
    echo "=== GetStudentById API Test Suite ===\n\n";
    
    foreach ($testCases as $index => $testCase) {
        echo "Running {$testCase['name']}...\n";
        
        $result = makeRequest($baseUrl, $testCase['params']);
        
        echo "HTTP Status: {$result['http_code']}\n";
        
        if ($result['error']) {
            echo "CURL Error: {$result['error']}\n";
        } else {
            $response = json_decode($result['response'], true);
            if ($response) {
                echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "Raw Response: {$result['response']}\n";
            }
        }
        
        // Check if status matches expected
        if ($result['http_code'] == $testCase['expected_status']) {
            echo "✅ PASS\n";
        } else {
            echo "❌ FAIL - Expected {$testCase['expected_status']}, got {$result['http_code']}\n";
        }
        
        echo "\n" . str_repeat('-', 50) . "\n\n";
    }
}

// Run tests
if (php_sapi_name() === 'cli') {
    testApi();
} else {
    echo "<pre>";
    testApi();
    echo "</pre>";
} 