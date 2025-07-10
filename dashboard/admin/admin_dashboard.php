<?php
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TNP Portal</title>
    
    <!-- Tailwind CSS -->
    <link href="/tnp@iiitmanipur/assets/css/2.2.19.tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="/tnp@iiitmanipur/assets/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- Include Dashboard Styles -->
    <?php include '../../includes/styles.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Topbar -->
            <?php include '../../includes/topbar.php'; ?>
            
            <!-- Main Content -->
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
                    <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?>!</p>
                </div>
                
                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-calendar text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Active Events</h3>
                                <p class="text-2xl font-bold text-blue-600">15</p>
                                <p class="text-sm text-gray-500">Currently running</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-user-graduate text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Total Students</h3>
                                <p class="text-2xl font-bold text-green-600">1,250</p>
                                <p class="text-sm text-gray-500">Registered users</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-building text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Recruiters</h3>
                                <p class="text-2xl font-bold text-yellow-600">85</p>
                                <p class="text-sm text-gray-500">Active companies</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Applications</h3>
                                <p class="text-2xl font-bold text-purple-600">2,450</p>
                                <p class="text-sm text-gray-500">Total submitted</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="admin_manage_events.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-calendar-alt text-blue-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Manage Events</h3>
                                <p class="text-sm text-gray-500">Approve and manage events</p>
                            </div>
                        </a>
                        
                        <a href="admin_manage_students.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-graduate text-green-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Manage Students</h3>
                                <p class="text-sm text-gray-500">View and manage student data</p>
                            </div>
                        </a>
                        
                        <a href="admin_manage_recruiter.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-building text-purple-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Manage Recruiters</h3>
                                <p class="text-sm text-gray-500">Manage company accounts</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- System Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Pending Approvals -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Approvals</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-800">TechCorp Recruitment Drive</h4>
                                    <p class="text-sm text-gray-500">Coordinator approved</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    <button class="text-red-600 hover:text-red-900 text-sm">Reject</button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-800">DataSoft Campus Hiring</h4>
                                    <p class="text-sm text-gray-500">Coordinator approved</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    <button class="text-red-600 hover:text-red-900 text-sm">Reject</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="admin_manage_events.php" class="text-blue-600 hover:text-blue-900 text-sm">View All Pending (8)</a>
                        </div>
                    </div>
                    
                    <!-- System Statistics -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">System Statistics</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Active Events</span>
                                <span class="font-semibold text-blue-600">15</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pending Approvals</span>
                                <span class="font-semibold text-yellow-600">8</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Applications</span>
                                <span class="font-semibold text-green-600">2,450</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Success Rate</span>
                                <span class="font-semibold text-purple-600">78%</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent System Activity</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Event Approved</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">TechCorp Recruitment Drive</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Coordinator</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Student Verified</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">John Doe Profile</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Coordinator</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-14</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Recruiter Verified</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">DataSoft Solutions</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Coordinator</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-13</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge success">Completed</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
            
            <!-- Footer -->
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- Include Dashboard Scripts -->
    <?php include '../../includes/scripts.php'; ?>
</body>
</html> 