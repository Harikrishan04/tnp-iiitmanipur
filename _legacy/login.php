<?php
// login.php - Frontend login interface
declare(strict_types=1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cookie configuration
const COOKIE_NAME = 'auth_token';
const COOKIE_SECRET = 'f9b4d1c8a7e24316b5f6d9ea31c7a2ef'; // Replace with a secure 32+ char key
const COOKIE_EXPIRE = 604800; // 7 days in seconds

// Initialize variables
$error_message = "";
$success_message = "";
$current_role = $_GET["role"] ?? "student";
$otp_flow_active = false; // This will be true if OTP has been sent
$submitted_email = $_SESSION['submitted_email'] ?? '';
$submitted_name = $_SESSION['submitted_name'] ?? '';
$submitted_role = $_SESSION['current_role'] ?? $current_role;

// Handle session clear/refresh
if (isset($_GET['action']) && $_GET['action'] === 'clear_session') {
    unset($_SESSION['submitted_email'], $_SESSION['submitted_name'], $_SESSION['current_role']);
    setcookie(COOKIE_NAME, '', time() - 3600, '/', '', true, true); // Clear cookie
    session_regenerate_id(true);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

/**
 * Get the base URL for API requests
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['REQUEST_URI']);
    return $protocol . $host . $path;
}

/**
 * Make API request to authentication service
 */
function makeApiRequest($url, $data) {
    $post_data_json = json_encode($data);
    if ($post_data_json === false) {
        error_log("JSON Encoding Failed: " . json_last_error_msg());
        return ['success' => false, 'error' => 'Error encoding request data.'];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($post_data_json)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        error_log("cURL Error: " . $error . " | URL: " . $url);
        curl_close($ch);
        return ['success' => false, 'error' => 'Could not connect to the authentication service. Error: ' . $error];
    }

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $response_body = substr($response, $header_size);
    $response_headers = substr($response, 0, $header_size);

    curl_close($ch);

    $api_response = json_decode($response_body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("API Response JSON Decode Error (HTTP $http_code): " . json_last_error_msg() . " | Response: " . $response_body);
        return ['success' => false, 'error' => 'Error processing server response.'];
    }

    return [
        'success' => ($http_code == 200 && isset($api_response['status']) && $api_response['status'] === "success"),
        'http_code' => $http_code,
        'data' => $api_response,
        'error' => !($http_code == 200 && isset($api_response['status']) && $api_response['status'] === "success")
            ? ($api_response['message'] ?? 'Request failed') : null
    ];
}

/**
 * Validate email, name and role inputs
 */
function validateInputs($email, $name, $role)
{
    if (empty($email)) {
        return "Please enter your email address.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email address format.";
    }
    // Name is optional for OTP flow, as it might be fetched from DB later
    // if (empty($name) || strlen(trim($name)) < 2) {
    //     return "Please enter a valid name (at least 2 characters).";
    // }
    if (empty($role) || !in_array($role, ['student', 'recruiter', 'coordinator', 'admin'])) {
        return "Please select a valid role.";
    }
    return null;
}

/**
 * Create and set authentication cookie
 */
function setAuthCookie($user_data)
{
    $payload = [
        'user_id' => $user_data['id'],
        'email' => $user_data['email'],
        'role' => $user_data['role'],
        'token' => $user_data['token'],
        'exp' => time() + COOKIE_EXPIRE
    ];

    $payload_json = json_encode($payload);
    if ($payload_json === false) {
        error_log("Failed to encode cookie payload");
        return false;
    }

    // Create HMAC signature
    $signature = hash_hmac('sha256', $payload_json, COOKIE_SECRET);
    $cookie_value = base64_encode($payload_json) . '.' . $signature;

    // Set HTTP-only cookie
    return setcookie(
        COOKIE_NAME,
        $cookie_value,
        [
            'expires' => time() + COOKIE_EXPIRE,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Handle user authentication
 */
function handleUserAuthentication($api_response)
{
    if (!isset($api_response['email'], $api_response['role'], $api_response['token'], $api_response['id'])) {
        error_log("Incomplete API response: " . json_encode($api_response));
        return false;
    }
    // Set session variables for immediate use
    $_SESSION['authenticated'] = true;
    $_SESSION['user_email'] = $api_response['email'];
    $_SESSION['user_role'] = $api_response['role'];
    $_SESSION['user_id'] = $api_response['id'];
    $_SESSION['user_name'] = $api_response['name'] ?? '';
    $_SESSION['logged_in'] = true;
    // Set authentication cookie
    if (!setAuthCookie($api_response)) {
        error_log("Failed to set authentication cookie for email: " . $api_response['email']);
        return false;
    }

    // Clear OTP session variables
    unset($_SESSION['submitted_email'], $_SESSION['submitted_name'], $_SESSION['current_role']);

    return true;
}

// Handle Send OTP (initial send and resend)
if (
    $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['stage']) &&
    ($_POST['stage'] === 'send_otp' || $_POST['stage'] === 'resend_otp')
) {

    $email = '';
    $name = ''; // Name is now optional for send_otp stage

    if ($_POST['stage'] === 'resend_otp') {
        $email = $_SESSION['submitted_email'] ?? '';
        $name = $_SESSION['submitted_name'] ?? '';
        $role = $_SESSION['current_role'] ?? '';

        if (empty($email) || empty($role)) {
            $error_message = "Session expired. Please start over.";
            unset($_SESSION['submitted_email'], $_SESSION['submitted_name'], $_SESSION['current_role']);
            $otp_flow_active = false;
        }
    } else {
        $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
        $name = trim($_POST["name"] ?? ''); // Name is collected here, but not validated as required
        $role = ($email === 'nirb230103039@iiitmanipur.ac.in') ? 'admin' : trim($_POST["role"] ?? '');
    }

    if (empty($error_message)) {
        // Only validate email and role here, name is optional for send_otp
        $validation_error = validateInputs($email, 'dummy_name', $role); // Pass dummy name if not required
        if ($validation_error && $validation_error !== "Please enter a valid name (at least 2 characters).") {
            $error_message = $validation_error;
        } else {
            $api_url = getBaseUrl() . "/dataRouting/api/auth/auth_handler.php";
            $result = makeApiRequest($api_url, [
                'email' => $email,
                'name' => $name, // Pass name to API even if optional for frontend validation
                'role' => $role,
                'action' => 'send_otp'
            ]);
            if ($result['success']) {
                $success_message = $_POST['stage'] === 'resend_otp'
                    ? "OTP has been sent again to your email."
                    : ($result['data']['message'] ?? "OTP sent successfully to your email.");

                $_SESSION['submitted_email'] = $email;
                $_SESSION['submitted_name'] = $name;
                $_SESSION['current_role'] = $role;
                $submitted_email = $email;
                $submitted_role = $role;
                $otp_flow_active = true;
            } else {
                $error_message = $result['error'];
                if (isset($result['http_code'])) {
                    $http_code = $result['http_code'];
                    if ($http_code >= 500) {
                        $error_message = "A server error occurred. Please try again later.";
                    } elseif ($http_code >= 400 && isset($result['data']['message'])) {
                        $error_message = $result['data']['message'];
                    } elseif ($http_code >= 400) {
                        $error_message = "There was a problem with your request (Error: " . $http_code . ").";
                    }
                }
            }
        }
    }
}

// Handle Verify OTP
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['stage']) && $_POST['stage'] === 'verify_otp') {
    $otp = trim($_POST["otp"] ?? '');
    $email = $_SESSION['submitted_email'] ?? '';
    $role = $_SESSION['current_role'] ?? '';
    if ($email === 'nirb230103039@iiitmanipur.ac.in') {
        $role = 'admin';
    }

    $otp_flow_active = true; // Stay in OTP flow
    $submitted_email = $email;
    $submitted_role = $role;

    if (empty($email) || empty($role)) {
        $error_message = "Session expired. Please start over.";
        $otp_flow_active = false;
        unset($_SESSION['submitted_email'], $_SESSION['submitted_name'], $_SESSION['current_role']);
    } elseif (empty($otp)) {
        $error_message = "Please enter the OTP.";
    } elseif (!preg_match('/^\d{6}$/', $otp)) {
        $error_message = "OTP must be a 6-digit number.";
    } else {
        $api_url = getBaseUrl() . "/dataRouting/api/auth/auth_handler.php";
        $result = makeApiRequest($api_url, [
            'email' => $email,
            'otp' => $otp,
            'role' => $role,
            'action' => 'verify_otp'
        ]);

        if ($result['success']) {
            if (handleUserAuthentication($result['data'])) {
                // Redirect based on role or a default dashboard
                $dashboard_path = 'dashboard/index.html'; // Default
                if ($result['data']['role'] === 'student') {
                    $dashboard_path = 'dashboard/student/student_profile.php';
                } elseif ($result['data']['role'] === 'recruiter') {
                    $dashboard_path = 'dashboard/recruiter/recruiter_profile.php';
                } elseif ($result['data']['role'] === 'coordinator') {
                    $dashboard_path = 'dashboard/coordinator/verify_students.php';
                } elseif ($result['data']['role'] === 'admin') {
                    $dashboard_path = 'dashboard/admin/manage_events.php';
                }
                header('Location: ' . $dashboard_path);
                exit;
            } else {
                $error_message = "Authentication error: Unable to process login. Please try again.";
            }
        } else {
            error_log("API Error for OTP verification ($email, HTTP " . ($result['http_code'] ?? 'unknown') . "): " . $result['error']);
            $error_message = $result['error'] ?? "OTP verification failed. Please check the OTP and try again.";
        }
    }
}

// Check if we're in OTP flow from session on initial page load
if (isset($_SESSION['submitted_email']) && !empty($_SESSION['submitted_email'])) {
    $otp_flow_active = true;
    $submitted_email = $_SESSION['submitted_email'];
    $submitted_role = $_SESSION['current_role'] ?? $current_role;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IIIT Manipur Placement Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Container grid layout */
        .container-grid {
            width: 100vw; /* Use full viewport width */
            height: 100vh; /* Use full viewport height */
            display: grid;
            grid-template-columns: 3fr 2fr 1fr; /* Three columns: 1/4, 2/4 (1/2), 1/4 */
            gap: 0; /* Ensure no gap between columns */
            overflow: hidden; /* Prevent overflow issues */
        }

        /* Left panel - Glassmorphism Effect */
        .left-panel {
            padding: 2rem; /* Adjust padding as needed */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* Adjust alpha (0.7) for transparency to let background show through */
            background-color: transparent;
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            background-image: linear-gradient(120deg, rgba(139, 42, 139, 1) 0%, rgba(192, 56, 84, 1) 100%);
            border-radius: 0; /* No specific rounding for a panel in a 3-panel grid */
            color: white;
            
            /* Removed clip-path for a cleaner 3-panel division */
            border: 1px solid rgba(255, 255, 255, 0.3); /* Light, transparent border for a subtle highlight */
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37); /* Soft shadow for depth */
        }
        
        /* Center panel (formerly right-panel) */
        .center-panel {
            padding: 3rem;
            padding-top: 0px; /* Adjust padding as needed */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 1); /* White background for the center panel */
            border-radius: 0; /* Remove individual rounding to match container */
            align-items: center; /* Center content horizontally within center panel */
        }

        /* New Right panel (1/4 width) */
        .right-panel-new {
            background-color: transparent;
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            background-image: linear-gradient(120deg, rgba(139, 42, 139, 1) 0%, rgba(192, 56, 84, 1) 100%);
            clip-path: polygon(63% 0, 100% 0, 100% 100%, 99% 100%);
            border-radius: 0;
            border: 1px solid rgba(255, 255, 255, 0.3); /* Match border with left panel */
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37); /* Match shadow with left panel */
        }

        /* Ensure no extra margins or padding cause gaps */
        .left-panel, .center-panel, .right-panel-new {
            margin: 0;
        }
        
        /* Removed .corner specific styles, as it's no longer a fixed element */
        /* .corner{ ... } */


        /* Base styles for the entire page */
        body {
            font-family: 'Inter', sans-serif;
            /* Example: Add a background image for better glassmorphism visibility */
            background-image: url('https://source.unsplash.com/random/1920x1080/?abstract,technology'); /* Replace with your desired image or gradient */
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            margin: 0;
        }

        /* Logo specific styles */
        .logo-circle {
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.2); /* A slightly transparent white background for the circle */
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: white;
            font-weight: bold;
            text-align: center;
            overflow: hidden; /* Crucial: Hides anything outside the circle */
        }

        .logo-circle img {
            width: 100%; /* Ensures image doesn't exceed circle width */
            height: 100%; /* Ensures image doesn't exceed circle height */
            object-fit: contain; /* Scales the image to fit without cropping, maintaining aspect ratio */
            display: block; /* Removes any extra space below the image */
        }

        /* Original logo text if needed for other places, otherwise remove */
        .logo-text-main {
            font-size: 3.5rem;
            line-height: 1;
        }

        .logo-text-sub {
            font-size: 1.2rem;
            margin-top: 0.25rem;
        }

        /* New wrapper to control overall width of form and tabs */
        .form-and-tabs-wrapper {
            width: 100%; /* Take full width of parent (center-panel) initially */
            max-width: 400px; /* Set a consistent max-width for both tabs and forms */
            margin-left: auto; /* Center the wrapper */
            margin-right: auto; /* Center the wrapper */
        }

        /* Role Tabs styling - NEW TAB BAR */
        .role-tab-bar {
            display: flex;
            justify-content: center;
            background-color: #f0f0f0; /* Light background for the tab bar */
            border-radius: 12px; /* Slightly more rounded corners for the bar */
            padding: 6px; /* Inner padding for the tab items */
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); /* Subtle inner shadow */
            /* Removed max-width here, it's now controlled by .form-and-tabs-wrapper */
        }

        .role-tab-item {
            flex: 1;
            text-align: center;
            padding: 12px 20px;
            color: #555;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 9px; /* Slightly less rounded than the bar */
            user-select: none; /* Prevent text selection */
            white-space: nowrap; /* Prevent wrapping of text */
        }

        .role-tab-item.active {
            background: linear-gradient(135deg, #8B2A8B 0%, #C03854 100%); /* Your existing gradient for active state */
            color: white;
            box-shadow: 0 4px 12px rgba(139, 42, 139, 0.3); /* Stronger shadow for active tab */
            transform: translateY(-2px); /* Slight lift for active tab */
        }

        .role-tab-item:hover:not(.active) {
            background-color: #e6e6e6; /* Light hover background */
            color: #333;
        }

        /* Input field styling */
        .form-input-field {
            width: 100%; /* Changed to 100% to fill its parent (.form-and-tabs-wrapper or inner div) */
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input-field:focus {
            outline: none;
            border-color: #8B2A8B;
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 42, 139, 0.1);
        }

        /* Button styling */
        .btn-action {
            width: 100%; /* Changed to 100% to fill its parent (.form-and-tabs-wrapper or inner div) */
            padding: 15px;
            background: linear-gradient(135deg, #8B2A8B 0%, #C03854 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 42, 139, 0.3);
        }

        .btn-action:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: translateY(0);
            box-shadow: none;
        }

        /* Specific styles for OTP input */
        #otp {
            letter-spacing: 0.5em;
            text-align: center;
        }

        /* Resend timer and link */
        .resend-timer {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .resend-link {
            color: #8B2A8B;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .resend-link:hover {
            text-decoration: underline;
            color: #6A1B9A;
        }

        .resend-disabled {
            color: #adb5bd;
            cursor: not-allowed;
            text-decoration: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-grid {
                grid-template-columns: 1fr; /* Stack panels on small screens */
            }

            .left-panel, .center-panel, .right-panel-new {
                padding: 1.5rem;
            }
             .center-panel {
                justify-content: start; /* Align form to top on mobile */
                padding-top: 3rem;
            }
            /* The new right panel will also stack on mobile */
            .right-panel-new {
                min-height: 100px; /* Give it some height when stacked */
            }


            .logo-circle {
                width: 150px;
                height: 150px;
            }

            .logo-text-main {
                font-size: 2.5rem;
            }

            .logo-text-sub {
                font-size: 1rem;
            }

            .form-and-tabs-wrapper {
                max-width: 100%; /* Allow it to take full width on smaller screens */
            }
            
            .role-tab-bar {
                padding: 4px; /* Adjust padding for smaller screens */
            }

            .role-tab-item {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .signin-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="bg-white">
    <div class="grid container-grid">
        <div class="left-panel text-white space-y-6">
            <div class="logo-circle">
                <img class="object-fit" src="./assets/img/iiitm-logo.png" alt="IIIT Manipur Logo" srcset="">
            </div>
            <h1 class="text-4xl font-bold">IIIT Manipur</h1>
            <h2 class="text-2xl font-light">Placement Portal</h2>
            <p class="text-lg opacity-90">Connecting talent with opportunities</p>
        </div>

        <div class="center-panel">
            <h1 class="text-3xl md:text-4xl font-semibold text-purple-800 mb-8 text-center signin-title">Sign-in to IIIT Manipur Placement Portal</h1>

            <?php if ($otp_flow_active): ?>
                <div class="flex justify-end mb-4">
                    <button onclick="clearSession()" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                        <i class="fas fa-refresh mr-1"></i>Refresh
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="form-and-tabs-wrapper">
                <?php if (!$otp_flow_active): ?>
                    <div class="role-tab-bar mb-8">
                        <div class="role-tab-item <?php echo $current_role === 'student' ? 'active' : ''; ?>" onclick="window.location.href='?role=student'">Student</div>
                        <div class="role-tab-item <?php echo $current_role === 'recruiter' ? 'active' : ''; ?>" onclick="window.location.href='?role=recruiter'">Recruiter</div>
                        <div class="role-tab-item <?php echo $current_role === 'coordinator' ? 'active' : ''; ?>" onclick="window.location.href='?role=coordinator'">Coordinator</div>
                    </div>
                    <form method="POST" action="" class="space-y-6" onsubmit="return validateForm()">
                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($current_role); ?>">
                        <input type="hidden" name="stage" value="send_otp">

                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                <?php
                                if ($current_role === 'student') {
                                    echo 'Full Name as per College Records';
                                } elseif ($current_role === 'recruiter') {
                                    echo 'Full Name (Recruiter)';
                                } elseif ($current_role === 'coordinator') {
                                    echo 'Full Name (Coordinator)';
                                } else {
                                    echo 'Full Name'; // Default or fallback
                                }
                                ?>
                            </label>
                            <input id="name" name="name" type="text"
                                value="<?php echo htmlspecialchars($submitted_name); ?>"
                                class="form-input-field"
                                placeholder="Enter your full name">
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                <?php
                                if ($current_role === 'student') {
                                    echo 'College Email Address (@iiitmanipur.ac.in)';
                                } elseif ($current_role === 'recruiter') {
                                    echo 'Official Company Email';
                                } elseif ($current_role === 'coordinator') {
                                    echo 'Official  Email';
                                } else {
                                    echo 'Email Address'; // Default or fallback
                                }
                                ?>
                            </label>
                            <input id="email" name="email" type="email" required
                                value="<?php echo htmlspecialchars($submitted_email); ?>"
                                class="form-input-field"
                                placeholder="Enter your email">
                        </div>

                        <button type="submit" id="sendOtpBtn" class="btn-action">
                            <span class="btn-text">Send OTP</span>
                            <span class="btn-spinner hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Sending...
                            </span>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="space-y-6"> <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                OTP sent to <strong><?php echo htmlspecialchars($submitted_email); ?></strong> for <strong><?php echo ucfirst(htmlspecialchars($submitted_role)); ?></strong> role.
                            </p>
                        </div>

                        <form method="POST" action="" class="space-y-6" onsubmit="return validateOtpForm()">
                            <input type="hidden" name="stage" value="verify_otp">

                            <div class="space-y-2">
                                <label for="otp" class="block text-sm font-medium text-gray-700">Enter OTP</label>
                                <input id="otp" name="otp" type="text" required maxlength="6"
                                    class="form-input-field"
                                    placeholder="000000"
                                    pattern="[0-9]{6}"
                                    autocomplete="one-time-code">
                            </div>

                            <button type="submit" id="verifyOtpBtn" class="btn-action bg-gradient-to-r from-green-500 to-emerald-600">
                                <span class="btn-text">Verify OTP</span>
                                <span class="btn-spinner hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Verifying...
                                </span>
                            </button>
                        </form>

                        <div class="mt-6 text-center">
                            <div class="mb-3">
                                <span class="text-sm text-gray-600">Didn't receive the OTP?</span>
                            </div>

                            <div id="resendSection">
                                <form method="POST" action="" style="display: inline;" onsubmit="return handleResendOtp()">
                                    <input type="hidden" name="stage" value="resend_otp">
                                    <button type="submit" id="resendBtn" class="resend-link text-sm transition-colors duration-200">
                                        <i class="fas fa-paper-plane mr-1"></i>Send OTP Again
                                    </button>
                                </form>
                            </div>

                            <div id="timerSection" style="display: none;">
                                <span class="resend-timer text-sm">
                                    <i class="fas fa-clock mr-1"></i>
                                    Resend available in <span id="countdown">60</span> seconds
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 text-center border-t pt-4">
                            <a href="?action=clear_session" class="text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-1"></i>Back to email entry
                            </a>
                        </div>
                    </div> <?php endif; ?>
            </div> 
        </div>
        <div class="right-panel-new">
            </div>
    </div>

    <script>
        let resendTimer = null;
        let timeLeft = 0;

        function clearSession() {
            if (confirm('This will clear your current session and you will need to start over. Continue?')) {
                window.location.href = '?action=clear_session';
            }
        }

        function startResendTimer() {
            timeLeft = 60;
            const resendSection = document.getElementById('resendSection');
            const timerSection = document.getElementById('timerSection');
            const countdown = document.getElementById('countdown');

            if (resendSection && timerSection && countdown) {
                resendSection.style.display = 'none';
                timerSection.style.display = 'block';

                resendTimer = setInterval(() => {
                    timeLeft--;
                    countdown.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(resendTimer);
                        resendSection.style.display = 'block';
                        timerSection.style.display = 'none';
                    }
                }, 1000);
            }
        }

        function handleResendOtp() {
            const resendBtn = document.getElementById('resendBtn');
            if (resendBtn) {
                resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Sending...';
                resendBtn.disabled = true;
            }

            // Simulate a short delay before starting the timer to mimic network call
            setTimeout(() => {
                startResendTimer();
            }, 1000);

            return true; // Allow form submission
        }

        function validateForm() {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const sendBtn = document.getElementById('sendOtpBtn');
            const btnText = sendBtn.querySelector('.btn-text');
            const btnSpinner = sendBtn.querySelector('.btn-spinner');

            // Name is now optional for the OTP send stage
            // if (!name.value.trim() || name.value.trim().length < 2) {
            //     alert('Please enter a valid name (at least 2 characters).');
            //     return false;
            // }

            if (!email.value.trim()) {
                alert('Please enter your email address.');
                return false;
            }

            if (!isValidEmail(email.value)) {
                alert('Please enter a valid email address.');
                return false;
            }

            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
            sendBtn.disabled = true;

            return true;
        }

        function validateOtpForm() {
            const otp = document.getElementById('otp');
            const verifyBtn = document.getElementById('verifyOtpBtn');
            const btnText = verifyBtn.querySelector('.btn-text');
            const btnSpinner = verifyBtn.querySelector('.btn-spinner');

            if (!otp.value.trim()) {
                alert('Please enter the OTP.');
                return false;
            }

            if (!/^\d{6}$/.test(otp.value)) {
                alert('OTP must be a 6-digit number.');
                return false;
            }

            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
            verifyBtn.disabled = true;

            return true;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.focus();

                otpInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 6) {
                        this.value = this.value.slice(0, 6);
                    }
                });

                otpInput.addEventListener('input', function(e) {
                    if (this.value.length === 6) {
                        // Optional: auto-submit the form
                    }
                });
            }

            <?php if ($otp_flow_active && !empty($error_message)): ?>
                startResendTimer();
            <?php endif; ?>
        });

        window.addEventListener('beforeunload', function(e) {
            if (resendTimer) {
                clearInterval(resendTimer);
            }
        });
    </script>
</body>
</html>