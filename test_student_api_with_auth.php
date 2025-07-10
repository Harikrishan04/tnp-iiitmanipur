<?php
/**
 * Test script for GetStudentById API endpoint with authentication simulation
 * This script demonstrates how to call the API endpoint with proper authentication
 */

// Configuration
$baseUrl = 'http://localhost/tnp@iiitmanipur/dataRouting/api/student/get_by_id.php';

// Test student ID (replace with an actual student ID from your database)
$testStudentId = '3fb133f3-5aa1-11f0-b6d3-cc4740c7c70f'; // Example UUID

// Test the API endpoint with authentication simulation
function testGetStudentByIdWithAuth($baseUrl, $studentId) {
    $url = $baseUrl . '?student_id=' . urlencode($studentId);
    
    echo "Testing API endpoint: $url\n";
    echo "=====================================\n";
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    // Simulate a logged-in session by setting a cookie
    // Note: This is for testing purposes only
    curl_setopt($ch, CURLOPT_COOKIE, 'session_token=test_session_token');
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        return;
    }
    
    curl_close($ch);
    
    // Display results
    echo "HTTP Status Code: $httpCode\n";
    echo "Response:\n";
    echo $response . "\n";
    echo "=====================================\n\n";
}

// Test with authentication simulation
echo "Test 1: Valid UUID format with auth simulation\n";
testGetStudentByIdWithAuth($baseUrl, $testStudentId);

// Test with invalid UUID format
echo "Test 2: Invalid UUID format with auth simulation\n";
testGetStudentByIdWithAuth($baseUrl, 'invalid-uuid');

// Test without student_id parameter
echo "Test 3: Missing student_id parameter with auth simulation\n";
$url = $baseUrl;
echo "Testing API endpoint: $url\n";
echo "=====================================\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_COOKIE, 'session_token=test_session_token');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
echo "Response:\n";
echo $response . "\n";
echo "=====================================\n\n";

// Check if logs directory exists and show recent logs
$logsDir = __DIR__ . '/dataRouting/logs';
if (is_dir($logsDir)) {
    echo "Recent log files:\n";
    $logFiles = glob($logsDir . '/api_*.log');
    foreach ($logFiles as $logFile) {
        $filename = basename($logFile);
        $size = filesize($logFile);
        $modified = date('Y-m-d H:i:s', filemtime($logFile));
        echo "- $filename (Size: $size bytes, Modified: $modified)\n";
        
        // Show last few log entries
        if ($size > 0) {
            echo "  Last 3 log entries:\n";
            $lines = file($logFile);
            $lastLines = array_slice($lines, -3);
            foreach ($lastLines as $line) {
                $logEntry = json_decode($line, true);
                if ($logEntry) {
                    echo "    " . $logEntry['timestamp'] . " [" . $logEntry['level'] . "] " . $logEntry['operation'] . "\n";
                }
            }
        }
    }
} else {
    echo "Logs directory not found: $logsDir\n";
}

echo "\n";
echo "Manual Testing Instructions:\n";
echo "===========================\n";
echo "1. First, log in to the TNP portal in your browser\n";
echo "2. Open your browser's developer tools (F12)\n";
echo "3. Go to the Network tab\n";
echo "4. Navigate to: $baseUrl?student_id=YOUR_STUDENT_ID\n";
echo "5. Check the response and network tab for details\n";
echo "6. Check the logs at: dataRouting/logs/api_YYYY-MM-DD.log\n";
echo "\n";

echo "Expected Results:\n";
echo "================\n";
echo "- If authenticated and student exists: 200 OK with student data\n";
echo "- If not authenticated: 401 Unauthorized\n";
echo "- If invalid student ID: 400 Bad Request\n";
echo "- If student not found: 404 Not Found\n";
echo "- If no permission: 403 Forbidden\n";
echo "\n";

echo "Note: Replace 'YOUR_STUDENT_ID' with an actual student ID from your database.\n";
echo "You can get a student ID by running: SELECT student_id FROM students LIMIT 1;\n"; 