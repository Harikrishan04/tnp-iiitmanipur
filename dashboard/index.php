<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Redirect based on user role
$user_role = $_SESSION['user_role'];

switch ($user_role) {
    case 'student':
        header('Location: student/student_dashboard.php');
        break;
    case 'recruiter':
        header('Location: recruiter/recruiter_dashboard.php');
        break;
    case 'coordinator':
        header('Location: coordinator/coordinator_dashboard.php');
        break;
    case 'admin':
        header('Location: admin/admin_dashboard.php');
        break;
    default:
        // Invalid role, redirect to login
        session_destroy();
        header('Location: ../login.php');
        break;
}
exit;
?> 