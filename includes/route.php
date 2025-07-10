<?php
/**
 * Redirects authenticated users to the appropriate dashboard or profile page based on their role and form action.
 */
function routeUser() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit;
    }

    // Validate required session variables
    if (!isset($_SESSION['user_role']) || !isset($_SESSION['form_action'])) {
        error_log("Missing session variables: user_role or form_action for user ID " . ($_SESSION['user_id'] ?? 'unknown'));
        header("Location: login.php?error=session_invalid");
        exit;
    }

    $role = $_SESSION['user_role'];
    $form_action = $_SESSION['form_action'];
    $valid_roles = ['student', 'recruiter', 'admin', 'coordinator'];

    // Validate role
    if (!in_array($role, $valid_roles)) {
        error_log("Invalid role detected: $role for user ID " . ($_SESSION['user_id'] ?? 'unknown'));
        session_unset();
        session_destroy();
        header("Location: login.php?error=invalid_role");
        exit;
    }

    // Define base path (adjust based on your server structure)
    $base_path = '/tnp(staging)/dashboard/';

    // Redirect based on role and form action
    if (in_array($role, ['student', 'recruiter', 'coordinator'])) {
        if ($form_action === 'incomplete') {
            header("Location: {$base_path}{$role}/{$role}_profile.php");
            exit;
        } 
        elseif ($form_action === 'completed') {
            header("Location: {$base_path}{$role}/lock.php");
            exit;
        } 
        elseif ($form_action === 'verified') {
            header("Location: {$base_path}{$role}/verify_jobs.php");
            exit;
        } else {
            error_log("Invalid form_action '$form_action' for role '$role' and user ID " . ($_SESSION['user_id'] ?? 'unknown'));
            header("Location: {$base_path}{$role}/verify_jobs.php");
            exit;
        }
    } elseif (in_array($role, ['admin'])) {
        header("Location: {$base_path}{$role}/{$role}_dashboard.php");
        exit;
    }

    // Fallback for unexpected cases
    error_log("Unexpected routing case for role '$role' and user ID " . ($_SESSION['user_id'] ?? 'unknown'));
    header("Location: login.php?error=unknown");
    exit;
}
?>