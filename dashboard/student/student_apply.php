<?php
session_start();
// Check if user is logged in and is a student
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
    <title>Apply for Jobs - TNP Portal</title>
    
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
                    <h1 class="text-2xl font-bold text-gray-800">Apply for Jobs</h1>
                    <p class="text-gray-600 mt-2">Browse and apply for available job openings</p>
                </div>
                
                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-600 font-medium mb-2">Search Jobs</label>
                            <input type="text" class="form-input" placeholder="Search by company, position, or skills">
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2">Location</label>
                            <select class="form-input">
                                <option value="">All Locations</option>
                                <option value="remote">Remote</option>
                                <option value="bangalore">Bangalore</option>
                                <option value="mumbai">Mumbai</option>
                                <option value="delhi">Delhi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2">Job Type</label>
                            <select class="form-input">
                                <option value="">All Types</option>
                                <option value="fulltime">Full Time</option>
                                <option value="parttime">Part Time</option>
                                <option value="internship">Internship</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button class="submit-btn w-full">Search</button>
                        </div>
                    </div>
                </div>
                
                <!-- Job Listings -->
                <div class="space-y-4">
                    <!-- Job Card 1 -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <img src="../../assets/img/companyLogo/amazon.png" alt="Amazon" class="w-12 h-12 rounded-lg mr-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800">Software Engineer</h3>
                                        <p class="text-gray-600">Amazon</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>Bangalore, India</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span>Full Time</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        <span>₹12-18 LPA</span>
                                    </div>
                                </div>
                                <p class="text-gray-700 mb-4">
                                    We are looking for a talented Software Engineer to join our team. 
                                    Experience with Java, Python, and cloud technologies preferred.
                                </p>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Java</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Python</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">AWS</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">React</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span class="status-badge success">Active</span>
                                <button class="submit-btn">Apply Now</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Job Card 2 -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <img src="../../assets/img/companyLogo/google.png" alt="Google" class="w-12 h-12 rounded-lg mr-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800">Data Analyst Intern</h3>
                                        <p class="text-gray-600">Google</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>Remote</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span>Internship</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        <span>₹25,000/month</span>
                                    </div>
                                </div>
                                <p class="text-gray-700 mb-4">
                                    Join our data analytics team as an intern. Learn from experts and work on real projects.
                                    Strong analytical skills and knowledge of SQL/Python required.
                                </p>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Python</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">SQL</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Excel</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Tableau</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span class="status-badge success">Active</span>
                                <button class="submit-btn">Apply Now</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Job Card 3 -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <img src="../../assets/img/companyLogo/microsoft.png" alt="Microsoft" class="w-12 h-12 rounded-lg mr-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800">Frontend Developer</h3>
                                        <p class="text-gray-600">Microsoft</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>Hyderabad, India</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span>Full Time</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        <span>₹10-15 LPA</span>
                                    </div>
                                </div>
                                <p class="text-gray-700 mb-4">
                                    We are seeking a Frontend Developer with experience in React, TypeScript, and modern web technologies.
                                    Join our team to build amazing user experiences.
                                </p>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">React</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">TypeScript</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">JavaScript</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">CSS</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span class="status-badge success">Active</span>
                                <button class="submit-btn">Apply Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center mt-8">
                    <nav class="flex items-center space-x-2">
                        <button class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="px-3 py-2 text-white bg-blue-600 border border-blue-600 rounded-lg">1</button>
                        <button class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                        <button class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                        <button class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
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