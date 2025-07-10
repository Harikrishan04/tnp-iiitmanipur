<?php
session_start();
// // Check if user is logged in and is a student
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
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
    <title>Student Dashboard - TNP Portal</title>
    
    <!-- Tailwind CSS -->
    <link href="/tnp@iiitmanipur/assets/css/2.2.19.tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="/tnp@iiitmanipur/assets/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- Include Dashboard Styles -->
    <?php include '../../../includes/styles.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../../../includes/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Topbar -->
            <?php include '../../../includes/topbar.php'; ?>
            
            <!-- Main Content -->
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Student Dashboard</h1>
                    <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?>!</p>
                </div>
                
                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-file-alt text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Applications</h3>
                                <p class="text-2xl font-bold text-blue-600">12</p>
                                <p class="text-sm text-gray-500">Total submitted</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Accepted</h3>
                                <p class="text-2xl font-bold text-green-600">3</p>
                                <p class="text-sm text-gray-500">Applications</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
                                <p class="text-2xl font-bold text-yellow-600">5</p>
                                <p class="text-sm text-gray-500">Under review</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="student_profile.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-edit text-blue-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Update Profile</h3>
                                <p class="text-sm text-gray-500">Edit your information</p>
                            </div>
                        </a>
                        
                        <a href="student_apply.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-paper-plane text-green-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">Apply for Jobs</h3>
                                <p class="text-sm text-gray-500">Browse and apply</p>
                            </div>
                        </a>
                        
                        <a href="student_applications.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-list-alt text-purple-600 text-xl mr-3"></i>
                            <div>
                                <h3 class="font-medium text-gray-800">My Applications</h3>
                                <p class="text-sm text-gray-500">Track your applications</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Applications -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Applications</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TechCorp Inc.</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Software Engineer</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge success">Accepted</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">DataSoft Solutions</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Data Analyst</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-12</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge warning">Pending</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
            
            <!-- Footer -->
            <?php include '../../../includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- Include Dashboard Scripts -->
    <?php include '../../../includes/scripts.php'; ?>
</body>
</html> 