<?php
session_start();
// Check if user is logged in and is a coordinator
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
//     header('Location: ../login.php');
//     exit;
// }

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard - TNP Portal</title>
    
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
                    <h1 class="text-2xl font-bold text-gray-800">Coordinator Dashboard</h1>
                    <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Coordinator'); ?>!</p>
                </div>
                
                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-user-graduate text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Student Profiles</h3>
                                <p class="text-2xl font-bold text-blue-600">25</p>
                                <p class="text-sm text-gray-500">Pending verification</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-building text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Recruiter Profiles</h3>
                                <p class="text-2xl font-bold text-green-600">8</p>
                                <p class="text-sm text-gray-500">Pending verification</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-calendar-check text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Events</h3>
                                <p class="text-2xl font-bold text-yellow-600">12</p>
                                <p class="text-sm text-gray-500">Pending verification</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Verified Today</h3>
                                <p class="text-2xl font-bold text-purple-600">15</p>
                                <p class="text-sm text-gray-500">Total verified</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="coordinator_verify_student.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-check text-blue-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Verify Students</h3>
                                <p class="text-sm text-gray-500">Review student profiles</p>
                            </div>
                        </a>
                        
                        <a href="coordinator_verify_recruiter.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-building text-green-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Verify Recruiters</h3>
                                <p class="text-sm text-gray-500">Review company profiles</p>
                            </div>
                        </a>
                        
                        <a href="coordinator_verify_event.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-calendar-alt text-purple-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Verify Events</h3>
                                <p class="text-sm text-gray-500">Review job postings</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Pending Verifications -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Pending Student Verifications -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Student Verifications</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-800">John Doe</h4>
                                    <p class="text-sm text-gray-500">john.doe@iiitmanipur.ac.in</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    <button class="text-red-600 hover:text-red-900 text-sm">Reject</button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-800">Jane Smith</h4>
                                    <p class="text-sm text-gray-500">jane.smith@iiitmanipur.ac.in</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    <button class="text-red-600 hover:text-red-900 text-sm">Reject</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="coordinator_verify_student.php" class="text-blue-600 hover:text-blue-900 text-sm">View All (25)</a>
                        </div>
                    </div>
                    
                    <!-- Pending Recruiter Verifications -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Recruiter Verifications</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-800">TechCorp Inc.</h4>
                                    <p class="text-sm text-gray-500">hr@techcorp.com</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    <button class="text-red-600 hover:text-red-900 text-sm">Reject</button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-800">DataSoft Solutions</h4>
                                    <p class="text-sm text-gray-500">careers@datasoft.com</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-green-600 hover:text-green-900 text-sm">Approve</button>
                                    <button class="text-red-600 hover:text-red-900 text-sm">Reject</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="coordinator_verify_recruiter.php" class="text-blue-600 hover:text-blue-900 text-sm">View All (8)</a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Verification Activity</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Student</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Alice Johnson</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Profile Verification</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge success">Approved</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Recruiter</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">InnovateTech</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Company Verification</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-14</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge success">Approved</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Event</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Software Engineer Position</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Job Posting Verification</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-13</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge warning">Pending</span>
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