<?php
/**
 * Test file for UpdateStudentById API endpoint
 * Usage: php test_update_student_by_id.php
 */

// Test configuration
$baseUrl = 'http://localhost/tnp@iiitmanipur/dataRouting/api/student/UpdateStudentById.php';

function makeRequest($url, $method, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
    }
    
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
    global $baseUrl;
    
    echo "=== UpdateStudentById API Test Suite ===\n\n";
    
    // Test 1: Valid update
    echo "Test 1: Valid student update...\n";
    $updateData = [
        'student_id' => '3fb133f3-5aa1-11f0-b6d3-cc4740c7c70f',
        'name' => 'John Doe Updated',
        'roll_no' => '2021CS001',
        'phone_number' => '9876543210',
        'city' => 'Bangalore',
        'cpi' => 8.5,
        'current_semester' => 3
    ];
    
    $result = makeRequest($baseUrl, 'PUT', $updateData);
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
    echo ($result['http_code'] == 200) ? "✅ PASS\n" : "❌ FAIL\n";
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    // Test 2: Missing student_id
    echo "Test 2: Missing student_id...\n";
    $updateData = [
        'name' => 'John Doe',
        'roll_no' => '2021CS001'
    ];
    
    $result = makeRequest($baseUrl, 'PUT', $updateData);
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
    echo ($result['http_code'] == 400) ? "✅ PASS\n" : "❌ FAIL\n";
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    // Test 3: Invalid UUID format
    echo "Test 3: Invalid UUID format...\n";
    $updateData = [
        'student_id' => 'invalid-uuid',
        'name' => 'John Doe'
    ];
    
    $result = makeRequest($baseUrl, 'PUT', $updateData);
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
    echo ($result['http_code'] == 400) ? "✅ PASS\n" : "❌ FAIL\n";
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    // Test 4: Non-existent student
    echo "Test 4: Non-existent student...\n";
    $updateData = [
        'student_id' => '3fb133f3-5aa1-11f0-b6d3-cc4740c7c70d',
        'name' => 'Non-existent Student'
    ];
    
    $result = makeRequest($baseUrl, 'PUT', $updateData);
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
    echo ($result['http_code'] == 404) ? "✅ PASS\n" : "❌ FAIL\n";
    echo "\n" . str_repeat('-', 50) . "\n\n";
    
    // Test 5: JSON fields update
    echo "Test 5: JSON fields update...\n";
    $updateData = [
        'student_id' => '3fb133f3-5aa1-11f0-b6d3-cc4740c7c70f',
        'personal_details_json' => [
            'personal_email' => 'john.updated@example.com',
            'linkedin_profile' => 'https://linkedin.com/in/johndoe',
            'github_profile' => 'https://github.com/johndoe',
            'programming_skills' => ['Python', 'Java', 'JavaScript'],
            'area_of_interest' => 'Web Development'
        ],
        'education_details_json' => [
            'jee_year' => '2021',
            'jee_mains_rank' => '50000',
            'tenth_score' => '95.5',
            'twelfth_score' => '92.0'
        ]
    ];
    
    $result = makeRequest($baseUrl, 'PUT', $updateData);
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
    echo ($result['http_code'] == 200) ? "✅ PASS\n" : "❌ FAIL\n";
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Run tests
if (php_sapi_name() === 'cli') {
    testApi();
} else {
    echo "<pre>";
    testApi();
    echo "</pre>";
} 