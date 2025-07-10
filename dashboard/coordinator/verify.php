<?php
/**
 * Post Job Page (Recruiter Stage)
 * TNP Portal - IIIT Manipur
 */

session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'recruiter';
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$coordinatorId = $_SESSION['user_id'] ?? '4b0890d5-9ffd-4c4d-901d-966bbdbd7676';

// Only recruiters can access
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'recruiter') {
//     header('Location: /tnp/login.php');
//     exit();
// }



// Handle session messages (for redirects)
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
// Clear session messages after displaying
unset($_SESSION['message'], $_SESSION['messageType']);

$menu_items = [
    ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/recruiter/dashboard.php', 'Overview'],
    ['Post Job', 'fas fa-plus-circle', '/tnp/Dashboard/recruiter/post_job.php', 'Create New Job'],
    ['My Jobs', 'fas fa-briefcase', '/tnp/Dashboard/recruiter/manage_jobs.php', 'Manage Jobs'],
    ['Applications', 'fas fa-users', '/tnp/Dashboard/recruiter/applications.php', 'View Applications'],
    ['Company Profile', 'fas fa-building', '/tnp/Dashboard/recruiter/recruiter_profile.php', 'Company Info'],
    ['Analytics', 'fas fa-chart-line', '/tnp/Dashboard/recruiter/analytics.php', 'Job Analytics']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - TNP Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="formstyle.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
               
             
            </main>
            <footer class="bg-white border-t border-gray-200 py-4 w-full mt-auto flex-shrink-0">
                <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center">
                    <p class="text-gray-600 text-sm">&copy; 2025 Training & Placement Portal. All rights reserved.</p>
                    <div class="flex space-x-4 mt-2 sm:mt-0 text-gray-600 text-sm">
                        <a href="#" class="hover:text-gray-900">Help</a>
                        <a href="#" class="hover:text-gray-900">Privacy</a>
                        <a href="#" class="hover:text-gray-900">Terms</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>


    <script>
    

    // Fetch jobs on page load
    document.addEventListener('DOMContentLoaded', fetchJobs);

    function fetchJobs() {
        fetch('../../dataRouting/api/coordinator/', { credentials: 'include' })
           
    }

    
    </script>
</body>
</html> 