<?php
session_start();

// Check if user is logged in and is a student
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
//     header('Location: /tnp/login.php');
//     exit();
// }

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'student'; // Default to student for testing
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
// Use a default UUID for demonstration if the session is not set
$user_id = $_SESSION['user_id'] ?? 'a1b2c3d4-e5f6-7890-1234-567890abcdef';

// Initialize variables
$profile = [];
$errors = [];
$success_message = '';

// Database connection
require_once '../../dataRouting/config/db.php';

// Define menu items based on role
$menu_items = [];
switch ($user_role) {
    case 'admin':
        $menu_items = [
            ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/admin/admin_dashboard.php', 'Overview'],
            // ... other admin items
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - TNP Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.min.js"></script>
    <style>
      .progress-line { height: 4px; background: #e0e0e0; position: relative; flex: 1; margin: 0 20px; }
      .progress-line.active { background: #4CAF50; }
      .progress-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
      .progress-circle.completed { background: #4CAF50; color: white; }
      .progress-circle.active { background: #2196F3; color: white; }
      .progress-circle.inactive { background: #e0e0e0; color: #666; }
      .form-input { border: none; border-bottom: 2px solid #4CAF50; background: transparent; padding: 12px 0; outline: none; width: 100%; font-size: 16px; transition: border-bottom-color 0.3s ease; }
      .form-input:valid { border-bottom-color: #4CAF50; }
      .form-input:focus { border-bottom-color: #2196F3; }
      .form-input::placeholder { color: #999; }
      .submit-btn { background: #4CAF50; color: white; padding: 12px 40px; border: none; border-radius: 25px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; }
      .submit-btn:hover { background: #45a049; transform: translateY(-2px); }
      .submit-btn[disabled] { opacity: 0.5; cursor: not-allowed; transform: none; }
      .step { display: none; }
      .step.active { display: block; }
      .form-label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }
      .required-star { color: #dc2626; }
      .section-heading { color: #1f2937; margin-bottom: 1.5rem; border-bottom: 2px solid #4CAF50; padding-bottom: 0.5rem; position: sticky; top: 0; z-index: 10; background: white; font-size: 1.875rem; /* text-3xl */ font-weight: bold; }
      .select2-container--default .select2-selection--multiple { border: 2px solid #e1e5e9; border-radius: 10px; padding: 8px; min-height: 50px; background: #f8f9fa; transition: all 0.3s ease; }
      #side-progress-bar { width: 70px; }
      .step-icon { width: 40px; height: 40px; border-radius: 50%; background: #e0e0e0; color: #888; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 2px solid #e0e0e0; transition: all 0.2s; }
      .step-icon.active, .step-icon.completed { background: #4CAF50; color: #fff; border-color: #4CAF50; }
      .progress-connector { width: 4px; height: 32px; background: #e0e0e0; margin: 0 auto; }
      .step-icon.completed ~ .progress-connector { background: #4CAF50; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
       
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>

            <main class="flex-1 min-h-0 flex flex-col p-4 md:p-8 w-full overflow-hidden">
                <div class="mb-6 flex justify-end gap-4">
                    <button type="button" id="viewProfileBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-eye"></i> View Profile
                    </button>
                    <button type="button" id="updateProfileBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-user-edit"></i> Update Profile
                    </button>
                </div>
            </main>
            
            <footer class="bg-white border-t border-gray-200 py-4 w-full mt-auto flex-shrink-0">
                <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
                    <p class="text-gray-600 text-sm">&copy; <?php echo date('Y'); ?> Training & Placement Portal. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <div id="viewProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-bold text-gray-800">Student Profile</h2>
                <button id="closeProfileModal" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" style="max-height: calc(90vh - 60px);">
                <div id="profileContent" class="space-y-6">
                    <div id="profileLoading" class="text-center py-8 flex-shrink-0">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <span class="ml-3 text-gray-600">Loading profile...</span>
                    </div>
                    <div id="profileData" class="hidden flex-1 overflow-y-auto flex flex-col min-h-0">
                        </div>
                </div>
            </div>
        </div>
    </div>

    <div id="updateProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-bold text-gray-800">Update Student Profile</h2>
                <button id="closeUpdateProfileModal" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="flex flex-1 min-h-0">
                <div class="w-[90px] h-full overflow-y-auto bg-gray-50 p-2 flex-shrink-0" id="side-progress-bar">
                    <div class="flex flex-col items-center py-4">
                        </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <form method="POST" id="profile-form" class="flex flex-col">
                        </form>
                </div>
            </div>
        </div>
    </div>

    <script>
$(document).ready(function() {
    // --- Configuration & Global State ---
    const API_BASE_URL = '../../dataRouting/api/student/';
    const STUDENT_ID = "<?php echo $user_id; ?>";
    let currentStep = 1;
    const TOTAL_STEPS = 10;
    const STEPS_CONFIG = [
        { num: 1, title: 'Basic Info', icon: 'fa-user' },
        { num: 2, title: 'Address', icon: 'fa-map-marker-alt' },
        { num: 3, title: 'Placement', icon: 'fa-briefcase' },
        { num: 4, title: 'Personal', icon: 'fa-id-card' },
        { num: 5, title: 'Education', icon: 'fa-graduation-cap' },
        { num: 6, title: 'Internships', icon: 'fa-building' },
        { num: 7, title: 'Certificates', icon: 'fa-certificate' },
        { num: 8, title: 'Projects', icon: 'fa-code' },
        { num: 9, title: 'Documents', icon: 'fa-file-alt' },
        { num: 10, title: 'Preview', icon: 'fa-eye' },
    ];

    let originalStudentData = null; // NEW: To store the fetched student data

    // --- Utility Functions ---
    const showToast = (message, isError = false) => {
        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: isError ? "linear-gradient(to right, #ff5f6d, #ffc371)" : "linear-gradient(to right, #00b09b, #96c93d)",
        }).showToast();
    };

    const safeJSONParse = (jsonString, defaultValue = {}) => {
        if (!jsonString || typeof jsonString !== 'string') return defaultValue;
        try {
            return JSON.parse(jsonString) || defaultValue;
        } catch (e) {
            console.error("JSON Parse Error:", e);
            return defaultValue;
        }
    };

    const fetchStudentData = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}getStudentDetailsById.php?student_id=${STUDENT_ID}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            if (data.status !== 'success' || !data.student) throw new Error(data.message || 'Invalid data structure');
            originalStudentData = data.student; // Store the original data
            return data.student;
        } catch (error) {
            console.error('Fetch Error:', error);
            showToast(`Error: ${error.message}`, true);
            return null;
        }
    };
    
    // --- View Profile Modal ---
    const viewModal = $('#viewProfileModal');
    const profileDataContainer = $('#profileData');
    const profileLoading = $('#profileLoading');

    $('#viewProfileBtn').on('click', async () => {
        viewModal.removeClass('hidden');
        profileLoading.show();
        profileDataContainer.hide().empty();

        const student = await fetchStudentData();
        if (student) {
            populateProfileView(student);
            profileLoading.hide();
            profileDataContainer.show();
        } else {
            profileLoading.html('<p class="text-red-500">Could not load profile.</p>');
        }
    });

    $('#closeProfileModal, #viewProfileModal').on('click', function(e) {
        if (e.target === this) viewModal.addClass('hidden');
    });

    function populateProfileView(s) {
        const personal = safeJSONParse(s.PersonalDetails);
        const education = safeJSONParse(s.EducationDetails);
        const experiences = safeJSONParse(s.Experiences);
        const skills = safeJSONParse(s.Skills);
        const documents = safeJSONParse(s.Documents);

        const na = '<span class="text-gray-400">Not provided</span>';
        const createLink = (url, text) => url ? `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${text}</a>` : na;

        // Helper to get status color (replicated from verify_recruiters.php for consistency)
        function getStatusColor(status) {
            switch((status||'').toLowerCase()) {
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'verified': return 'bg-green-100 text-green-800';
                case 'rejected': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-600';
            }
        }

        let experiencesHtml = '';
        if (experiences.internships?.length) {
            experiencesHtml += '<h4 class="font-semibold text-gray-700 mb-2">Internships</h4>' + experiences.internships.map(i => `
                <div class="border-t pt-2 mt-2">
                    <p><strong>${i.title || 'N/A'}</strong> at ${i.company || 'N/A'} (${i.duration || 'N/A'})</p>
                    <p class="text-sm text-gray-600">${i.description || ''}</p>
                </div>`).join('');
        }
        if (experiences.projects?.length) {
             experiencesHtml += '<h4 class="font-semibold text-gray-700 mt-4 mb-2">Projects</h4>' + experiences.projects.map(p => `
                <div class="border-t pt-2 mt-2">
                    <p><strong>${p.title || 'N/A'}</strong> - ${createLink(p.link, 'View Project')}</p>
                    <p class="text-sm text-gray-600">${p.description || ''}</p>
                </div>`).join('');
        }
        if (experiences.certificates?.length) {
             experiencesHtml += '<h4 class="font-semibold text-gray-700 mt-4 mb-2">Certificates</h4>' + experiences.certificates.map(c => `
                <div class="border-t pt-2 mt-2">
                    <p><strong>${c.name || 'N/A'}</strong> from ${c.platform || 'N/A'} (${c.year || 'N/A'})</p>
                </div>`).join('');
        }

        const viewHtml = `
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 text-center">
                    <img src="${documents.photo_link || 'https://via.placeholder.com/150'}" alt="Photo" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-gray-200 shadow-md">
                    <h2 class="text-2xl font-bold mt-4">${s.StudentName || na}</h2>
                    <p class="text-gray-600">${s.RollNumber || na} <span class="ml-2 px-2 py-1 rounded text-xs font-semibold ${getStatusColor(s.Status)}">${s.Status || 'N/A'}</span></p>
                </div>
                <div class="lg:col-span-2 space-y-4">
                    <div><strong>Email:</strong> ${personal.personal_email || na}</div>
                    <div><strong>Phone:</strong> ${s.PhoneNumber || na}</div>
                    <div><strong>Program:</strong> ${s.AcademicProgram || na}</div>
                    <div><strong>Department:</strong> ${s.Department || na}</div>
                    <div><strong>Profile:</strong> ${createLink(personal.linkedin_profile, 'LinkedIn')} | ${createLink(personal.github_profile, 'GitHub')} | ${createLink(personal.portfolio_link, 'Portfolio')}</div>
                    <div><strong>Address:</strong> ${s.Locality || na}, ${s.City || na}, ${s.State || na}, ${s.Country || na} - ${s.Pincode || na}</div>
                </div>
            </div>
            <div class="mt-6 space-y-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div><strong>DOB:</strong> ${s.DateOfBirth || na}</div>
                        <div><strong>Gender:</strong> ${s.Gender || na}</div>
                        <div><strong>Blood Group:</strong> ${s.BloodGroup || na}</div>
                        <div><strong>Category:</strong> ${s.StudentCategory || na}</div>
                        <div><strong>CPI:</strong> ${s.CurrentCPI || na}</div>
                        <div><strong>Semester:</strong> ${s.CurrentSemester || na}</div>
                    </div>
                </div>
                 <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Skills & Interests</h3>
                     <p><strong>Area of Interest:</strong> ${skills.area_of_interest || na}</p>
                     <p><strong>Skills:</strong> <span class="text-blue-700">${skills.programming_skills?.join(', ') || na}</span></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Experiences</h3>
                    ${experiencesHtml || na}
                </div>
                 <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Documents</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        ${createLink(documents.tenth_marksheet_link, "10th Marksheet")}
                        ${createLink(documents.twelfth_marksheet_link, "12th Marksheet")}
                        ${createLink(documents.jee_main_scorecard_link, "JEE Main Scorecard")}
                        ${createLink(documents.jee_advanced_scorecard_link, "JEE Advanced Scorecard")}
                    </div>
                </div>
            </div>
        `;
        profileDataContainer.html(viewHtml);
    }
    
    // --- Update Profile Modal ---
    const updateModal = $('#updateProfileModal');
    const formContainer = $('#profile-form');
    const sideProgressBar = $('#side-progress-bar > div');
    
    $('#updateProfileBtn').on('click', async () => {
        updateModal.removeClass('hidden');
        const student = await fetchStudentData();
        initializeForm(student);
    });

    $('#closeUpdateProfileModal, #updateProfileModal').on('click', function(e) {
        if (e.target === this) updateModal.addClass('hidden');
    });

    function initializeForm(studentData) {
        currentStep = 1; // Reset to first step
        entryCounters = { internship: 0, certificate: 0, project: 0 }; // Reset entry counters
        // Build form steps and side progress bar
        sideProgressBar.empty();
        formContainer.empty();
        STEPS_CONFIG.forEach((step, index) => {
            sideProgressBar.append(`
                <div class="flex flex-col items-center cursor-pointer" onclick="window.showStep(${step.num})">
                    <div class="step-icon mb-1" id="icon-step-${step.num}"><i class="fas ${step.icon}"></i></div>
                    <div class="step-label mb-2 text-xs text-center">${step.title}</div>
                </div>
                ${index < TOTAL_STEPS - 1 ? '<div class="progress-connector"></div>' : ''}
            `);
            formContainer.append(`<div class="step" data-step="${step.num}">${getStepContent(step.num)}</div>`);
        });

        // Initialize Select2 & other plugins
        $('#programming_skills_multiselect').select2({ placeholder: "Select skills", tags: true });
        
        // Populate form fields
        if (studentData) {
            populateFormFields(studentData);
        }

        // Show first step
        showStep(1);
    }

    function populateFormFields(s) {
        const personal = safeJSONParse(s.PersonalDetails);
        const education = safeJSONParse(s.EducationDetails);
        const experiences = safeJSONParse(s.Experiences);
        const skills = safeJSONParse(s.Skills);
        const documents = safeJSONParse(s.Documents);

        // Simple fields
        $('#roll_no').val(s.RollNumber);
        $('#name').val(s.StudentName);
        $('#date_of_birth').val(s.DateOfBirth);
        $('#gender').val(s.Gender);
        $('#blood_group').val(s.BloodGroup);
        $('#phone_number').val(s.PhoneNumber);
        $('#personal_email').val(personal.personal_email);
        $('#category').val(s.StudentCategory);
        $('#locality').val(s.Locality);
        $('#city').val(s.City);
        $('#state').val(s.State);
        $('#country').val(s.Country);
        $('#pincode').val(s.Pincode);
        $('#placement_interest').val(s.PlacementInterest);
        $('#comments').val(s.StudentComments);
        $('#mother_name').val(personal.family_info?.mother_name);
        $('#father_name').val(personal.family_info?.father_name);
        $('#linkedin_profile').val(personal.linkedin_profile);
        $('#github_profile').val(personal.github_profile);
        $('#portfolio_link').val(personal.portfolio_link);

        // Skills
        if (skills.programming_skills) {
            let programmingSkillsArray = skills.programming_skills;
            if (typeof programmingSkillsArray === 'string') {
                programmingSkillsArray = programmingSkillsArray.split(',').map(skill => skill.trim());
            }
            
            // Ensure each skill is added as an option if it doesn't exist, then select it
            programmingSkillsArray.forEach(function(skill) {
                if (!$('#programming_skills_multiselect option[value="' + skill + '"]').length) {
                    const newOption = new Option(skill, skill, true, true);
                    $('#programming_skills_multiselect').append(newOption);
                }
            });
            $('#programming_skills_multiselect').val(programmingSkillsArray).trigger('change');
        }
        $('#area_of_interest_select').val(skills.area_of_interest);

        // Education
        Object.keys(education).forEach(key => $(`#${key}`).val(education[key]));
        $('#jee_advanced_cleared').prop('checked', !!education.jee_advanced_cleared).trigger('change');
        
        // Documents
        Object.keys(documents).forEach(key => $(`#${key}`).val(documents[key]));

        // Dynamic experiences
        populateDynamicEntries('internship', experiences.internships || []);
        populateDynamicEntries('certificate', experiences.certificates || []);
        populateDynamicEntries('project', experiences.projects || []);
    }
    
    function populateDynamicEntries(type, dataArray) {
        const container = $(`#${type}s-container`);
        container.empty();
        if(dataArray.length === 0) { // Add one empty entry if none exist
             addDynamicEntry(type);
        } else {
            dataArray.forEach(item => {
                const newEntry = addDynamicEntry(type, false);
                Object.keys(item).forEach(key => {
                    let fieldName = key;
                    if(type === 'certificate' && key === 'name') fieldName = 'title';
                    if(type === 'certificate' && key === 'platform') fieldName = 'organisation';
                    if(type === 'certificate' && key === 'year') fieldName = 'completion_date';
                    if(type === 'internship' && key === 'duration') return; // Skip duration
                    
                    newEntry.find(`[name*="[${fieldName}]"]`).val(item[key]);
                });
            });
        }
    }

    // --- Form Step Navigation & Logic ---
    window.showStep = (stepNum) => {
        currentStep = stepNum;
        $('.step').removeClass('active').css({ 'flex': '', 'overflow-y': '' }); // Reset previous styles
        const activeStep = $(`.step[data-step="${currentStep}"]`);
        activeStep.addClass('active').css({ 'flex': '1', 'overflow-y': 'auto' });

        $('.step-icon').removeClass('active completed');
        for (let i = 1; i <= TOTAL_STEPS; i++) {
            const icon = $(`#icon-step-${i}`);
            if (i < currentStep) icon.addClass('completed');
            else if (i === currentStep) icon.addClass('active');
        }
        
        if(stepNum === 10) generatePreview();
    };
    
    $(document).on('click', '.next-btn', () => currentStep < TOTAL_STEPS && showStep(currentStep + 1));
    $(document).on('click', '.prev-btn', () => currentStep > 1 && showStep(currentStep - 1));
    $(document).on('change', '#jee_advanced_cleared', function() {
        $('#jee_advanced_rank_div, #jee_advanced_scorecard_div').toggle(this.checked);
    });

    // --- Dynamic Entry Management (Internships, Projects, etc.) ---
    let entryCounters = { internship: 0, certificate: 0, project: 0 };

    window.addDynamicEntry = (type, animate = true) => {
        const typePlural = type + 's';
        const container = $(`#${typePlural}-container`);
        entryCounters[type]++;
        const index = entryCounters[type] - 1;
        
        const entryHtml = `
            <div class="dynamic-entry border rounded-lg p-4 mb-4" data-type="${type}">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold capitalize">${type} ${entryCounters[type]}</h4>
                    <button type="button" class="text-red-500 hover:text-red-700 remove-entry-btn">Remove</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    ${getDynamicEntryFields(type, index)}
                </div>
            </div>`;
        
        const newEntry = $(entryHtml);
        container.append(newEntry);
        if (animate) newEntry.hide().fadeIn();
        return newEntry;
    };

    $(document).on('click', '.remove-entry-btn', function() {
        $(this).closest('.dynamic-entry').fadeOut(300, function() { $(this).remove(); });
    });

    // --- Form Submission ---
    formContainer.on('submit', async function(e) {
        e.preventDefault();
        const submitBtn = $('#final-submit-btn');
        submitBtn.prop('disabled', true).text('Submitting...');
        
        const payload = buildUpdatePayload();

        try {
            const response = await fetch(`${API_BASE_URL}putStudentDetailsById.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (result.status === 'success') {
                showToast('Profile updated successfully!');
                updateModal.addClass('hidden');
                    } else {
                throw new Error(result.message || 'An unknown error occurred.');
            }
        } catch (error) {
            showToast(`Update failed: ${error.message}`, true);
        } finally {
            submitBtn.prop('disabled', false).text('Submit');
        }
    });

    function buildUpdatePayload() {
        const form = document.getElementById('profile-form');
        const getVal = (id) => form.querySelector(`#${id}`)?.value || null;

        const payload = { p_student_id: STUDENT_ID };
        
        // Basic, Address, Placement
        ['roll_no', 'name', 'category', 'date_of_birth', 'gender', 'blood_group', 'phone_number', 'locality', 'city', 'state', 'country', 'pincode', 'placement_interest', 'comments']
        .forEach(key => payload[`p_${key}`] = getVal(key));
        
        // Preserve non-editable fields from original data
        if (originalStudentData) {
            payload.p_program = originalStudentData.Program || null;
            payload.p_department = originalStudentData.Department || null;
            payload.p_current_semester = originalStudentData.CurrentSemester || null;
            payload.p_cpi = originalStudentData.CurrentCPI || null;
            payload.p_year_of_admission = originalStudentData.YearOfAdmission || null;
            payload.p_year_of_passing = originalStudentData.YearOfPassing || null;
            payload.p_profile_photo_link = originalStudentData.ProfilePhotoLink || null; // Ensure photo link is passed
        }

        // JSON Fields
        payload.p_personal_details_json = JSON.stringify({
            family_info: { father_name: getVal('father_name'), mother_name: getVal('mother_name') },
            github_profile: getVal('github_profile'),
            personal_email: getVal('personal_email'),
            portfolio_link: getVal('portfolio_link'),
            linkedin_profile: getVal('linkedin_profile'),
            area_of_interest_other: getVal('area_of_interest_other')
        });

        payload.p_education_details_json = JSON.stringify({
            jee_year: getVal('jee_year'),
            tenth_board: getVal('tenth_board'),
            tenth_score: getVal('tenth_score'),
            twelfth_board: getVal('twelfth_board'),
            twelfth_score: getVal('twelfth_score'),
            jee_mains_rank: getVal('jee_mains_rank'),
            twelfth_stream: getVal('twelfth_stream'),
            jee_advanced_rank: getVal('jee_advanced_rank'),
            tenth_school_name: getVal('tenth_school_name'),
            twelfth_school_name: getVal('twelfth_school_name'),
            jee_advanced_cleared: form.querySelector('#jee_advanced_cleared').checked,
            tenth_year_of_passing: getVal('tenth_year_of_passing'),
            twelfth_year_of_passing: getVal('twelfth_year_of_passing'),
        });

        const getDynamicData = (type) => {
            const entries = [];
            $(`#${type}s-container .dynamic-entry`).each(function() {
                const entry = {};
                $(this).find('input, textarea, select').each(function() {
                    const name = $(this).attr('name').match(/\[(\w+)\]$/)[1];
                    entry[name] = $(this).val();
                });
                entries.push(entry);
            });
            return entries;
        };

        payload.p_experiences_json = JSON.stringify({
            projects: getDynamicData('project'),
            internships: getDynamicData('internship'),
            certificates: getDynamicData('certificate')
        });

        payload.p_skills_json = JSON.stringify({
            area_of_interest: $('#area_of_interest_select').val(),
            programming_skills: $('#programming_skills_multiselect').val()
        });
        
        payload.p_documents_json = JSON.stringify({
            photo_link: getVal('photo_link'),
            tenth_marksheet_link: getVal('tenth_marksheet_link'),
            twelfth_marksheet_link: getVal('twelfth_marksheet_link'),
            jee_main_scorecard_link: getVal('jee_main_scorecard_link'),
            jee_advanced_scorecard_link: getVal('jee_advanced_scorecard_link'),
            internship_certificate_link: getVal('internship_certificate_link'),
            other_certificate_link: getVal('other_certificate_link'),
        });
        
        // These fields are not in the form, pass as null so the SP doesn't overwrite them with NULL
        ['program', 'department', 'current_semester', 'cpi', 'year_of_admission', 'year_of_passing'].forEach(key => payload[`p_${key}`] = null);

        return payload;
    }
    
    // --- Content Generation for Form Steps & Preview ---
    function getNavButtons(stepNum) {
        let buttons = '';
        if (stepNum > 1) buttons += `<button type="button" class="submit-btn prev-btn">Previous</button>`;
        if (stepNum < TOTAL_STEPS) buttons += `<button type="button" class="submit-btn next-btn">Next</button>`;
        if (stepNum === TOTAL_STEPS) buttons += `<button type="submit" id="final-submit-btn" class="submit-btn">Submit Profile</button>`;
        return `<div class="flex items-center justify-end gap-4 mt-8">${buttons}</div>`;
    }
    
    function generatePreview() {
       const data = buildUpdatePayload();
       const p = (jsonString) => safeJSONParse(jsonString);
       const getVal = (id) => document.getElementById(id)?.value || 'N/A';

       const personal = p(data.p_personal_details_json);
       const education = p(data.p_education_details_json);
       const skills = p(data.p_skills_json);
       const experiences = p(data.p_experiences_json);
       const documents = p(data.p_documents_json);

       const na = '<span class="text-gray-400">N/A</span>';
       const createLink = (url, text) => url && url !== 'N/A' ? `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${text}</a>` : na;

       let experiencesHtml = '';
        if (experiences.internships?.length) {
            experiencesHtml += '<h4 class="font-semibold text-gray-700 mb-2">Internships</h4>' + experiences.internships.map(i => `
                <div class="border-t pt-2 mt-2">
                    <p><strong>Title:</strong> ${i.title || na}</p>
                    <p><strong>Company:</strong> ${i.company || na}</p>
                    <p><strong>Duration:</strong> ${i.duration || na}</p>
                    <p class="text-sm text-gray-600"><strong>Description:</strong> ${i.description || na}</p>
                </div>`).join('');
        }
        if (experiences.projects?.length) {
             experiencesHtml += '<h4 class="font-semibold text-gray-700 mt-4 mb-2">Projects</h4>' + experiences.projects.map(p => `
                <div class="border-t pt-2 mt-2">
                    <p><strong>Title:</strong> ${p.title || na}</p>
                    <p><strong>Link:</strong> ${createLink(p.link, 'View Project')}</p>
                    <p class="text-sm text-gray-600"><strong>Description:</strong> ${p.description || na}</p>
                </div>`).join('');
        }
        if (experiences.certificates?.length) {
             experiencesHtml += '<h4 class="font-semibold text-gray-700 mt-4 mb-2">Certificates</h4>' + experiences.certificates.map(c => `
                <div class="border-t pt-2 mt-2">
                    <p><strong>Name:</strong> ${c.name || na}</p>
                    <p><strong>Platform:</strong> ${c.platform || na}</p>
                    <p><strong>Year:</strong> ${c.year || na}</p>
                </div>`).join('');
        }

       let previewHtml = `
            <div class="space-y-4">
                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Basic & Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <p><strong>Name:</strong> ${data.p_name || na}</p>
                        <p><strong>Roll No:</strong> ${data.p_roll_no || na}</p>
                        <p><strong>Date of Birth:</strong> ${data.p_date_of_birth || na}</p>
                        <p><strong>Gender:</strong> ${data.p_gender || na}</p>
                        <p><strong>Blood Group:</strong> ${data.p_blood_group || na}</p>
                        <p><strong>Phone:</strong> ${data.p_phone_number || na}</p>
                        <p><strong>Personal Email:</strong> ${personal.personal_email || na}</p>
                        <p><strong>Category:</strong> ${data.p_category || na}</p>
                    </div>
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Address</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <p><strong>Locality:</strong> ${data.p_locality || na}</p>
                        <p><strong>City:</strong> ${data.p_city || na}</p>
                        <p><strong>State:</strong> ${data.p_state || na}</p>
                        <p><strong>Country:</strong> ${data.p_country || na}</p>
                        <p><strong>Pincode:</strong> ${data.p_pincode || na}</p>
                    </div>
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Placement Preferences</h4>
                    <p><strong>Placement Interest:</strong> ${data.p_placement_interest === '1' ? 'Interested' : 'Not Interested'}</p>
                    <p><strong>Comments:</strong> ${data.p_comments || na}</p>
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Social Profiles</h4>
                    <p><strong>LinkedIn:</strong> ${createLink(personal.linkedin_profile, 'LinkedIn Profile')}</p>
                    <p><strong>GitHub:</strong> ${createLink(personal.github_profile, 'GitHub Profile')}</p>
                    <p><strong>Portfolio:</strong> ${createLink(personal.portfolio_link, 'Portfolio Link')}</p>
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Education Details</h4>
                    <h5 class="font-semibold text-md mb-1">10th Standard</h5>
                    <p><strong>School:</strong> ${education.tenth_school_name || na}</p>
                    <p><strong>Board:</strong> ${education.tenth_board || na}</p>
                    <p><strong>Score:</strong> ${education.tenth_score || na}%</p>
                    <p><strong>Year:</strong> ${education.tenth_year_of_passing || na}</p>

                    <h5 class="font-semibold text-md mt-4 mb-1">12th Standard</h5>
                    <p><strong>School:</strong> ${education.twelfth_school_name || na}</p>
                    <p><strong>Board:</strong> ${education.twelfth_board || na}</p>
                    <p><strong>Stream:</strong> ${education.twelfth_stream || na}</p>
                    <p><strong>Score:</strong> ${education.twelfth_score || na}%</p>
                    <p><strong>Year:</strong> ${education.twelfth_year_of_passing || na}</p>

                    <h5 class="font-semibold text-md mt-4 mb-1">JEE Details</h5>
                    <p><strong>JEE Year:</strong> ${education.jee_year || na}</p>
                    <p><strong>Mains Rank:</strong> ${education.jee_mains_rank || na}</p>
                    <p><strong>Advanced Cleared:</strong> ${education.jee_advanced_cleared ? 'Yes' : 'No'}</p>
                    ${education.jee_advanced_cleared ? `<p><strong>Advanced Rank:</strong> ${education.jee_advanced_rank || na}</p>` : ''}
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Skills & Interests</h4>
                    <p><strong>Area of Interest:</strong> ${skills.area_of_interest || na}</p>
                    <p><strong>Programming Skills:</strong> ${skills.programming_skills?.join(', ') || na}</p>
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Experiences</h4>
                    ${experiencesHtml || '<p>No experiences added.</p>'}
                </section>

                <section class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Document Links</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <p><strong>Photo:</strong> ${createLink(documents.photo_link, 'View Photo')}</p>
                        <p><strong>10th Marksheet:</strong> ${createLink(documents.tenth_marksheet_link, 'View Marksheet')}</p>
                        <p><strong>12th Marksheet:</strong> ${createLink(documents.twelfth_marksheet_link, 'View Marksheet')}</p>
                        <p><strong>JEE Main Scorecard:</strong> ${createLink(documents.jee_main_scorecard_link, 'View Scorecard')}</p>
                        ${education.jee_advanced_cleared ? `<p><strong>JEE Advanced Scorecard:</strong> ${createLink(documents.jee_advanced_scorecard_link, 'View Scorecard')}</p>` : ''}
                        <p><strong>Internship Certificate:</strong> ${createLink(documents.internship_certificate_link, 'View Certificate')}</p>
                        <p><strong>Other Certificate:</strong> ${createLink(documents.other_certificate_link, 'View Certificate')}</p>
                    </div>
                </section>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center text-blue-800">
                   <i class="fas fa-info-circle text-blue-600"></i>
                   <p>Review your information. Click 'Submit Profile' to save.</p>
               </div>
            </div>
       `;
       $('#preview-content').html(previewHtml);
    }
    
    function getDynamicEntryFields(type, index) {
        const fields = {
            internship: `
                <input type="text" name="internships[${index}][title]" placeholder="Internship Title" class="form-input">
                <input type="text" name="internships[${index}][company]" placeholder="Company" class="form-input">
                <input type="text" name="internships[${index}][duration]" placeholder="e.g., May 2024 - July 2024" class="form-input">
                <textarea name="internships[${index}][description]" placeholder="Description" rows="3" class="form-input md:col-span-2"></textarea>`,
            certificate: `
                <input type="text" name="certificates[${index}][name]" placeholder="Certificate Name" class="form-input">
                <input type="text" name="certificates[${index}][platform]" placeholder="Issuing Organisation" class="form-input">
                <input type="text" name="certificates[${index}][year]" placeholder="Year" class="form-input">`,
            project: `
                <input type="text" name="projects[${index}][title]" placeholder="Project Title" class="form-input">
                <input type="url" name="projects[${index}][link]" placeholder="Project Link" class="form-input">
                <textarea name="projects[${index}][description]" placeholder="Description" rows="3" class="form-input md:col-span-2"></textarea>`
        };
        return fields[type] || '';
    }

    function getStepContent(stepNum) {
        const content = {
            1: `
                <h3 class="section-heading">Basic Information</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div><label for="roll_no" class="form-label">Roll Number</label><input type="text" id="roll_no" class="form-input"></div>
                    <div><label for="name" class="form-label">Full Name</label><input type="text" id="name" class="form-input"></div>
                    <div><label for="date_of_birth" class="form-label">Date of Birth</label><input type="date" id="date_of_birth" class="form-input"></div>
                    <div><label for="gender" class="form-label">Gender</label><select id="gender" class="form-input"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                    <div><label for="blood_group" class="form-label">Blood Group</label><input type="text" id="blood_group" class="form-input"></div>
                    <div><label for="phone_number" class="form-label">Phone</label><input type="tel" id="phone_number" class="form-input"></div>
                    <div><label for="personal_email" class="form-label">Personal Email</label><input type="email" id="personal_email" class="form-input"></div>
                    <div><label for="category" class="form-label">Category</label><select id="category" class="form-input"><option value="general">General</option><option value="obc">OBC</option><option value="sc">SC</option><option value="st">ST</option></select></div>
                </div>
                <h4 class="font-semibold mt-6 mb-2">Skills & Interests</h4>
                <div><label for="programming_skills_multiselect" class="form-label">Programming Skills</label><select id="programming_skills_multiselect" multiple="multiple" class="w-full"></select></div>
                <div class="mt-4"><label for="area_of_interest_select" class="form-label">Area of Interest</label><input type="text" id="area_of_interest_select" class="form-input" placeholder="e.g., Web Development, Machine Learning, Data Science"></div>`,
            2: `
                <h3 class="section-heading">Address</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div><label for="locality" class="form-label">Locality</label><input type="text" id="locality" class="form-input"></div>
                    <div><label for="city" class="form-label">City</label><input type="text" id="city" class="form-input"></div>
                    <div><label for="state" class="form-label">State</label><input type="text" id="state" class="form-input"></div>
                    <div><label for="country" class="form-label">Country</label><input type="text" id="country" class="form-input" value="India"></div>
                    <div><label for="pincode" class="form-label">Pincode</label><input type="text" id="pincode" class="form-input"></div>
                </div>`,
            3: `
                <h3 class="section-heading">Placement Preferences</h3>
                <label for="placement_interest" class="form-label">Placement Interest</label>
                <select id="placement_interest" class="form-input"><option value="1">Interested</option><option value="0">Not Interested</option></select>
                <label for="comments" class="form-label mt-4">Comments</label>
                <textarea id="comments" rows="4" class="form-input"></textarea>`,
            4: `
                <h3 class="section-heading">Personal Details</h3>
                 <div class="grid md:grid-cols-2 gap-6">
                    <div><label for="father_name" class="form-label">Father's Name</label><input type="text" id="father_name" class="form-input"></div>
                    <div><label for="mother_name" class="form-label">Mother's Name</label><input type="text" id="mother_name" class="form-input"></div>
                    <div><label for="linkedin_profile" class="form-label">LinkedIn Profile</label><input type="url" id="linkedin_profile" class="form-input"></div>
                    <div><label for="github_profile" class="form-label">GitHub Profile</label><input type="url" id="github_profile" class="form-input"></div>
                    <div class="md:col-span-2"><label for="portfolio_link" class="form-label">Portfolio Link</label><input type="url" id="portfolio_link" class="form-input"></div>
                 </div>`,
            5: `
                <h3 class="section-heading">Education Details</h3>
                <fieldset class="border p-4 rounded-lg mb-4"><legend class="px-2 font-semibold">10th Standard</legend><div class="grid md:grid-cols-2 gap-4">
                    <input id="tenth_school_name" placeholder="School Name" class="form-input"><input id="tenth_board" placeholder="Board" class="form-input">
                    <input id="tenth_score" placeholder="Score %" class="form-input"><input id="tenth_year_of_passing" placeholder="Year of Passing" class="form-input">
                </div></fieldset>
                <fieldset class="border p-4 rounded-lg mb-4"><legend class="px-2 font-semibold">12th Standard</legend><div class="grid md:grid-cols-2 gap-4">
                    <input id="twelfth_school_name" placeholder="School Name" class="form-input"><input id="twelfth_board" placeholder="Board" class="form-input">
                    <input id="twelfth_stream" placeholder="Stream" class="form-input"><input id="twelfth_score" placeholder="Score %" class="form-input">
                    <input id="twelfth_year_of_passing" placeholder="Year of Passing" class="form-input">
                </div></fieldset>
                <fieldset class="border p-4 rounded-lg"><legend class="px-2 font-semibold">JEE Details</legend><div class="grid md:grid-cols-2 gap-4">
                    <input id="jee_year" placeholder="JEE Year" class="form-input"><input id="jee_mains_rank" placeholder="Mains Rank" class="form-input">
                    <div class="flex items-center gap-2"><input type="checkbox" id="jee_advanced_cleared"><label for="jee_advanced_cleared">JEE Advanced Cleared</label></div>
                    <div id="jee_advanced_rank_div" style="display:none;"><input id="jee_advanced_rank" placeholder="Advanced Rank" class="form-input"></div>
                </div></fieldset>`,
            6: `<h3 class="section-heading">Internships</h3><div id="internships-container"></div><button type="button" class="submit-btn mt-2" onclick="addDynamicEntry('internship')">Add Internship</button>`,
            7: `<h3 class="section-heading">Certificates</h3><div id="certificates-container"></div><button type="button" class="submit-btn mt-2" onclick="addDynamicEntry('certificate')">Add Certificate</button>`,
            8: `<h3 class="section-heading">Projects</h3><div id="projects-container"></div><button type="button" class="submit-btn mt-2" onclick="addDynamicEntry('project')">Add Project</button>`,
            9: `
                <h3 class="section-heading">Document Links</h3>
                <div class="grid md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <label for="photo_link" class="form-label">Photo Link</label>
                        <input type="url" id="photo_link" class="form-input">
                    </div>
                    <div>
                        <label for="tenth_marksheet_link" class="form-label">10th Marksheet</label>
                        <input type="url" id="tenth_marksheet_link" class="form-input">
                    </div>
                    <div>
                        <label for="twelfth_marksheet_link" class="form-label">12th Marksheet</label>
                        <input type="url" id="twelfth_marksheet_link" class="form-input">
                    </div>
                    <div>
                        <label for="jee_main_scorecard_link" class="form-label">JEE Main Scorecard</label>
                        <input type="url" id="jee_main_scorecard_link" class="form-input">
                    </div>
                    <div id="jee_advanced_scorecard_div" style="display:none;">
                        <label for="jee_advanced_scorecard_link" class="form-label">JEE Advanced Scorecard</label>
                        <input type="url" id="jee_advanced_scorecard_link" class="form-input">
                    </div>
                    <div>
                        <label for="internship_certificate_link" class="form-label">Internship Certificate</label>
                        <input type="url" id="internship_certificate_link" class="form-input">
                    </div>
                    <div>
                        <label for="other_certificate_link" class="form-label">Other Certificate</label>
                        <input type="url" id="other_certificate_link" class="form-input">
                    </div>
                </div>`,
            10: `<h3 class="section-heading">Preview</h3><div id="preview-content"></div>`,
        };
        return (content[stepNum] || '') + getNavButtons(stepNum);
    }
});
    </script>

</body>
</html>