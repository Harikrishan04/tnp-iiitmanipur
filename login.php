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
$email_field_disabled = false;
$otp_flow_active = false;
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
    if (empty($name) || strlen(trim($name)) < 2) {
        return "Please enter a valid name (at least 2 characters).";
    }
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
    $role = '';

    if ($_POST['stage'] === 'resend_otp') {
        $email = $_SESSION['submitted_email'] ?? '';
        $name = $_SESSION['submitted_name'] ?? '';
        $role = $_SESSION['current_role'] ?? '';

        if (empty($email) || empty($role)) {
            $error_message = "Session expired. Please start over.";
            unset($_SESSION['submitted_email'], $_SESSION['submitted_name'], $_SESSION['current_role']);
            $otp_flow_active = false;
            $email_field_disabled = false;
        }
    } else {
        $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
        $name = trim($_POST["name"] ?? '');
        $role = ($email === 'tnp@iiitmanipur.ac.in') ? 'admin' : trim($_POST["role"] ?? '');
    }

    if (empty($error_message)) {
        $validation_error = validateInputs($email, $name, $role);
        if ($validation_error) {
            $error_message = $validation_error;
        } else {
            $api_url = getBaseUrl() . "/dataRouting/api/auth/auth_handler.php";
            $result = makeApiRequest($api_url, [
                'email' => $email,
                'name' => $name,
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
                $email_field_disabled = true;
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
    if ($email === 'tnp@iiitmanipur.ac.in') {
        $role = 'admin';
    }

    $email_field_disabled = true;
    $otp_flow_active = true;
    $submitted_email = $email;
    $submitted_role = $role;

    if (empty($email) || empty($role)) {
        $error_message = "Session expired. Please start over.";
        $otp_flow_active = false;
        $email_field_disabled = false;
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
                header('Location: dashboard/index.html');
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

// Check if we're in OTP flow from session
if (!$otp_flow_active && isset($_SESSION['submitted_email']) && !empty($_SESSION['submitted_email'])) {
    $email_field_disabled = true;
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
    <title>Login - TNP Portal</title>
    <!-- Tailwind CSS -->
    <link href="/tnp@iiitmanipur/assets/css/2.2.19.tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/tnp@iiitmanipur/assets/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .form-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .tab-active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .email-disable {
            pointer-events: none;
            opacity: 0.6;
        }

        .resend-timer {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .resend-link {
            color: #8b5cf6;
            cursor: pointer;
            text-decoration: underline;
        }

        .resend-link:hover {
            color: #7c3aed;
        }

        .resend-disabled {
            color: #9ca3af;
            cursor: not-allowed;
            text-decoration: none;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-6xl w-full grid md:grid-cols-2 gap-8 items-center">
            <!-- Left side - Branding -->
            <div class="text-center text-white space-y-6">
                <div class="bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-3xl p-8 inline-block">
                    <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-graduation-cap text-6xl text-purple-600"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold">IIIT Manipur</h1>
                <h2 class="text-2xl font-light">Placement Portal</h2>
                <p class="text-lg opacity-90">Connecting talent with opportunities</p>
            </div>

            <!-- Right side - Login Form -->
            <div class="form-container rounded-3xl p-8 shadow-2xl">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Welcome !</h2>
                    <?php if ($otp_flow_active): ?>
                        <button onclick="clearSession()" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                            <i class="fas fa-refresh mr-1"></i>Refresh
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Error/Success Messages -->
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

                <!-- Role Tabs (only show when not in OTP flow) -->
                <?php if (!$otp_flow_active): ?>
                    <div class="flex bg-gray-100 rounded-lg p-1 mb-8">
                        <a href="?role=student" class="flex-1 text-center py-3 px-4 rounded-md transition-all duration-200 <?php echo $current_role === 'student' ? 'tab-active' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-user-graduate mr-2"></i>Student
                        </a>
                        <a href="?role=recruiter" class="flex-1 text-center py-3 px-4 rounded-md transition-all duration-200 <?php echo $current_role === 'recruiter' ? 'tab-active' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-building mr-2"></i>Recruiter
                        </a>
                        <a href="?role=coordinator" class="flex-1 text-center py-3 px-4 rounded-md transition-all duration-200 <?php echo $current_role === 'coordinator' ? 'tab-active' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-user-tie mr-2"></i>Coordinator
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!$otp_flow_active): ?>
                    <!-- SEND OTP FORM -->
                    <form method="POST" action="" class="space-y-6" onsubmit="return validateForm()">
                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($current_role); ?>">
                        <input type="hidden" name="stage" value="send_otp">

                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="name" name="name" type="text" required
                                    value="<?php echo htmlspecialchars($submitted_name); ?>"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter your full name">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" required
                                    value="<?php echo htmlspecialchars($submitted_email); ?>"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter your email">
                            </div>
                        </div>

                        <button type="submit" id="sendOtpBtn" class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-lg hover:opacity-90 transition duration-200 font-medium">
                            <span class="btn-text">Send OTP</span>
                            <span class="btn-spinner hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Sending...
                            </span>
                        </button>
                    </form>
                <?php else: ?>
                    <!-- OTP VERIFICATION FORM -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            OTP sent to <strong><?php echo htmlspecialchars($submitted_email); ?></strong> for <strong><?php echo ucfirst(htmlspecialchars($submitted_role)); ?></strong> role.
                        </p>
                    </div>

                    <form method="POST" action="" class="space-y-6" onsubmit="return validateOtpForm()">
                        <input type="hidden" name="stage" value="verify_otp">

                        <div class="space-y-2">
                            <label for="otp" class="block text-sm font-medium text-gray-700">Enter OTP</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="otp" name="otp" type="text" required maxlength="6"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-center text-lg tracking-widest transition-all duration-200"
                                    placeholder="000000"
                                    pattern="[0-9]{6}"
                                    autocomplete="one-time-code">
                            </div>
                        </div>

                        <button type="submit" id="verifyOtpBtn" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-lg hover:opacity-90 transition duration-200 font-medium">
                            <span class="btn-text">Verify OTP</span>
                            <span class="btn-spinner hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Verifying...
                            </span>
                        </button>
                    </form>

                    <!-- Resend OTP Section -->
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

                    <!-- Option to go back -->
                    <div class="mt-6 text-center border-t pt-4">
                        <a href="?action=clear_session" class="text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-1"></i>Back to email entry
                        </a>
                    </div>
                <?php endif; ?>
            </div>
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

            setTimeout(() => {
                startResendTimer();
            }, 1000);

            return true;
        }

        function validateForm() {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const sendBtn = document.getElementById('sendOtpBtn');
            const btnText = sendBtn.querySelector('.btn-text');
            const btnSpinner = sendBtn.querySelector('.btn-spinner');

            if (!name.value.trim() || name.value.trim().length < 2) {
                alert('Please enter a valid name (at least 2 characters).');
                return false;
            }

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