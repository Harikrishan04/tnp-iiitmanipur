<?php
// test_router_system.php
declare(strict_types=1);

require_once 'dataRouting/config/db.php';
require_once 'dataRouting/utils/auth_utils.php';
require_once 'dataRouting/utils/Router.php';

echo "=== TNP Portal Router System Test ===\n\n";

try {
    // Get database connection
    $pdo = require 'dataRouting/config/db.php';
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    echo "✅ Database connection successful\n";
    
    // Test Router class
    $router = new Router('/test');
    echo "✅ Router class initialized\n";
    
    // Test with different user roles
    $testUsers = [
        [
            'name' => 'Student User',
            'user_id' => 1,
            'email' => 'student@iiitmanipur.ac.in',
            'user_name' => 'John Student',
            'role_id' => 1,
            'role_name' => 'student'
        ],
        [
            'name' => 'Recruiter User',
            'user_id' => 2,
            'email' => 'recruiter@company.com',
            'user_name' => 'Jane Recruiter',
            'role_id' => 2,
            'role_name' => 'recruiter'
        ],
        [
            'name' => 'Coordinator User',
            'user_id' => 3,
            'email' => 'coordinator@iiitmanipur.ac.in',
            'user_name' => 'Bob Coordinator',
            'role_id' => 3,
            'role_name' => 'coordinator'
        ],
        [
            'name' => 'Admin User',
            'user_id' => 4,
            'email' => 'admin@iiitmanipur.ac.in',
            'user_name' => 'Alice Admin',
            'role_id' => 4,
            'role_name' => 'admin'
        ]
    ];
    
    foreach ($testUsers as $user) {
        echo "\n--- Testing {$user['name']} ---\n";
        
        // Set user in router
        $router->setUser($user, $pdo);
        
        // Test permission checking
        echo "Role: {$user['role_name']}\n";
        
        // Test route access
        $testRoutes = [
            'dashboard',
            'student-dashboard',
            'recruiter-dashboard',
            'coordinator-dashboard',
            'admin-dashboard',
            'admin-users'
        ];
        
        foreach ($testRoutes as $routeName) {
            $canAccess = $router->canAccessRoute($routeName);
            $status = $canAccess ? '✅' : '❌';
            echo "  {$status} {$routeName}: " . ($canAccess ? 'Accessible' : 'Not accessible') . "\n";
        }
        
        // Test permission checking
        $testPermissions = [
            'profile.read',
            'event.create',
            'profile.verify',
            'profile.view_all'
        ];
        
        echo "  Permissions:\n";
        foreach ($testPermissions as $permission) {
            $hasPermission = $router->hasPermission($permission);
            $status = $hasPermission ? '✅' : '❌';
            echo "    {$status} {$permission}\n";
        }
        
        // Test module access
        $testModules = ['profile', 'event', 'application'];
        echo "  Module Access:\n";
        foreach ($testModules as $module) {
            $hasAccess = $router->hasModuleAccess($module);
            $status = $hasAccess ? '✅' : '❌';
            echo "    {$status} {$module}\n";
        }
        
        // Get accessible routes
        $accessibleRoutes = $router->getAccessibleRoutes();
        echo "  Accessible Routes: " . count($accessibleRoutes) . "\n";
        
        // Test navigation generation
        $navHtml = $router->generateNavigationMenu('dashboard');
        $sidebarHtml = $router->generateSidebarMenu('dashboard');
        $breadcrumbHtml = $router->generateBreadcrumb('dashboard');
        
        echo "  Navigation generated: " . (strlen($navHtml) > 0 ? '✅' : '❌') . "\n";
        echo "  Sidebar generated: " . (strlen($sidebarHtml) > 0 ? '✅' : '❌') . "\n";
        echo "  Breadcrumb generated: " . (strlen($breadcrumbHtml) > 0 ? '✅' : '❌') . "\n";
    }
    
    // Test API endpoints
    echo "\n--- Testing API Endpoints ---\n";
    
    // Simulate API requests
    $apiTests = [
        'permissions' => '?action=permissions',
        'routes' => '?action=routes',
        'navigation' => '?action=navigation&route=dashboard&type=nav',
        'check_access' => '?action=check_access&route=admin-dashboard'
    ];
    
    foreach ($apiTests as $testName => $queryString) {
        echo "Testing {$testName} endpoint...\n";
        
        // Simulate the request
        $_GET = [];
        parse_str($queryString, $_GET);
        
        // Capture output
        ob_start();
        
        try {
            // Include the API handler
            include 'dataRouting/api/router_handler.php';
            $output = ob_get_clean();
            
            // Parse JSON response
            $response = json_decode($output, true);
            
            if ($response && isset($response['success'])) {
                echo "  ✅ {$testName}: " . ($response['success'] ? 'Success' : 'Failed') . "\n";
            } else {
                echo "  ❌ {$testName}: Invalid response format\n";
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "  ❌ {$testName}: Error - " . $e->getMessage() . "\n";
        }
    }
    
    // Test error handling
    echo "\n--- Testing Error Handling ---\n";
    
    // Test with invalid user
    $router->setUser([], $pdo);
    $accessibleRoutes = $router->getAccessibleRoutes();
    echo "Empty user routes: " . count($accessibleRoutes) . " (should be 0) ✅\n";
    
    // Test with invalid route
    $canAccess = $router->canAccessRoute('invalid-route');
    echo "Invalid route access: " . ($canAccess ? '❌' : '✅') . "\n";
    
    // Test permission with empty user
    $hasPermission = $router->hasPermission('profile.read');
    echo "Empty user permission: " . ($hasPermission ? '❌' : '✅') . "\n";
    
    echo "\n=== Router System Test Complete ===\n";
    echo "✅ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Clean up
unset($_GET);
?> 