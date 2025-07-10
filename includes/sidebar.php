<?php
// session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Define menu items based on role
$menu_items = [];
switch ($user_role) {
    case 'admin':
        $menu_items = [
            ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/admin/admin_dashboard.php', 'Overview'],
            ['Manage Coordinators', 'fas fa-user-tie', '/tnp/Dashboard/admin/manage_coordinators.php', 'Coordinator Management'],
            ['Manage Students', 'fas fa-user-graduate', '/tnp/Dashboard/admin/manage_students.php', 'Student Management'],
            ['Manage Recruiters', 'fas fa-building', '/tnp/Dashboard/admin/manage_recruiters.php', 'Recruiter Management'],
            ['System Settings', 'fas fa-cog', '/tnp/Dashboard/admin/system_settings.php', 'System Configuration']
        ];
        break;
    case 'recruiter':
        $menu_items = [
            ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/recruiter/recruiter_dashboard.php', 'Overview'],
            ['Post Job', 'fas fa-plus-circle', '/tnp/Dashboard/recruiter/post_job.php', 'Create New Job'],
            ['My Jobs', 'fas fa-briefcase', '/tnp/Dashboard/recruiter/manage_jobs.php', 'Manage Jobs'],
            ['Applications', 'fas fa-users', '/tnp/Dashboard/recruiter/applications.php', 'View Applications'],
            ['Company Profile', 'fas fa-building', '/tnp/Dashboard/recruiter/company_profile.php', 'Company Info'],
            ['Analytics', 'fas fa-chart-line', '/tnp/Dashboard/recruiter/analytics.php', 'Job Analytics']
        ];
        break;
    case 'coordinator':
        $menu_items = [
            ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/coordinator/coordinator_dashboard.php', 'Overview'],
            ['Verify Recruiters', 'fas fa-user-check', '/tnp/Dashboard/coordinator/verify_recruiters.php', 'Recruiter Verification'],
            ['Verify Jobs', 'fas fa-briefcase', '/tnp/Dashboard/coordinator/verify_jobs.php', 'Job Verification'],
            ['Manage Students', 'fas fa-user-graduate', '/tnp/Dashboard/coordinator/manage_students.php', 'Student Management'],
            ['Reports', 'fas fa-chart-bar', '/tnp/Dashboard/coordinator/reports.php', 'Placement Reports']
        ];
        break;
    case 'student':
        $menu_items = [
            ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/student/student_dashboard.php', 'Overview'],
            ['Profile', 'fas fa-user', '/tnp/Dashboard/student/student_profile.php', 'My Profile'],
            ['Jobs', 'fas fa-search', '/tnp/Dashboard/student/jobs.php', 'Browse Jobs'],
            ['Applications', 'fas fa-file-alt', '/tnp/Dashboard/student/applications.php', 'My Applications'],
            ['Documents', 'fas fa-file-upload', '/tnp/Dashboard/student/documents.php', 'Upload Documents'],
            ['Placement Status', 'fas fa-chart-pie', '/tnp/Dashboard/student/placement_status.php', 'Status']
        ];
        break;
    default:
        $menu_items = [
            ['Home', 'fas fa-home', '/tnp/index.php', 'Main Page']
        ];
}
?>


<aside id="sidebar" class="fixed md:static z-30 top-0 left-0 h-full w-64 bg-gradient-to-b from-green-500 to-blue-500 shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col md:flex md:w-64 md:min-h-full">
            <div class="flex flex-col h-full p-6">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-white capitalize"><?php echo htmlspecialchars($user_role); ?> Dashboard</h2>
                    <button id="closeSidebarBtn" class="md:hidden text-white focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="flex-1 space-y-2">
                    <?php foreach ($menu_items as $item): 
                        $is_active = strpos($current_page, basename($item[2])) !== false;
                    ?>
                    <a href="<?php echo htmlspecialchars($item[2]); ?>" class="flex items-center px-4 py-2 rounded-lg text-white hover:bg-white hover:text-blue-700 transition <?php if ($is_active) echo 'bg-white text-blue-700 font-semibold'; ?>" title="<?php echo htmlspecialchars($item[3]); ?>">
                        <i class="<?php echo htmlspecialchars($item[1]); ?> mr-3"></i><span><?php echo htmlspecialchars($item[0]); ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <div class="mt-auto pt-6 border-t border-white border-opacity-20">
                    <span class="text-white text-xs block mb-1"><?php echo htmlspecialchars($user_name); ?></span>
                    <span class="text-white text-xs"><?php echo htmlspecialchars($user_email); ?></span><br>
                    <span class="text-white text-xs">&copy; 2025 Training & Placement Portal</span>
                </div>
            </div>
</aside>

 <!-- Overlay for mobile sidebar -->
 <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
