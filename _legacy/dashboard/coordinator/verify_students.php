<?php
/**
 * Student Applications Page (Coordinator Stage)
 * TNP Portal - IIIT Manipur
 */

session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'coordinator'; // Changed default to coordinator
$user_name = $_SESSION['user_name'] ?? 'Coordinator User';
$user_email = $_SESSION['user_email'] ?? '';
$coordinatorId = $_SESSION['user_id'] ?? '123e4567-e89b-12d3-a456-426614174000'; // Use a consistent default UUID

// Only coordinators can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
    header('Location: /tnp/login.php'); // Redirect to login
    exit();
}

// Handle session messages (for redirects)
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
// Clear session messages after displaying
unset($_SESSION['message'], $_SESSION['messageType']);

$menu_items = [
    // Adjusted menu for a 'coordinator' perspective managing students
    ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/coordinator/dashboard.php', 'Overview'],
    ['Student Applications', 'fas fa-user-graduate', '/tnp/Dashboard/coordinator/verify_students.php', 'Manage Student Registrations'], // This page
    ['Recruiter Applications', 'fas fa-user-tie', '/tnp/Dashboard/coordinator/verify_recruiters.php', 'Manage Recruiter Registrations'],
    ['Job Postings', 'fas fa-briefcase', '/tnp/Dashboard/coordinator/manage_jobs.php', 'Oversee Job Postings'],
    ['Analytics', 'fas fa-chart-line', '/tnp/Dashboard/coordinator/analytics.php', 'Portal Analytics']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - TNP Portal</title>
    <link href="../../assets/css/2.2.19.tailwind.min.css" rel="stylesheet">
    <link href="../../assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="formstyle.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <div class="flex flex-1 gap-6 min-h-0">
                    <!-- Student Applications List (1/3) -->
                    <div class="w-full md:w-1/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div class="flex items-center mb-4">
                            <h2 class="text-xl font-bold flex-grow">Student Applications</h2>
                            <input type="text" id="searchStudents" class="form-input ml-4 border p-2 rounded" placeholder="Search students...">
                            <select id="statusFilter" class="form-input ml-2 border p-2 rounded">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div id="studentList" class="flex-1 flex flex-col items-center justify-center text-gray-400 overflow-y-auto overflow-scroll">
                            <i class="fas fa-user-graduate fa-3x mb-2"></i>
                            <div>Loading students...</div>
                        </div>
                    </div>
                    <!-- Student Details (2/3) -->
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="studentDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400 overflow-y-auto overflow-scroll">
                            <i class="fas fa-user fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select a Student</div>
                            <div>Choose a student from the list to view their detailed profile</div>
                        </div>
                    </div>
                </div>
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

    <style>
        .form-input {
            border: 1px solid #d1d5db; /* Tailwind gray-300 */
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem; /* Tailwind rounded-md */
            width: auto; /* Adjust as needed */
        }
    </style>

    <script>
    // --- Student Verification Logic ---
    let studentsData = [];
    let filteredStudents = [];
    let selectedStudentId = null;

    // Fetch students on page load
    document.addEventListener('DOMContentLoaded', fetchStudents);

    document.getElementById('searchStudents').addEventListener('input', filterAndRenderStudents);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderStudents);

    function fetchStudents() {
        const studentListContainer = document.getElementById('studentList');
        studentListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading students...</div>';
        studentListContainer.classList.add('items-center','justify-center','text-gray-400'); // Ensure centering for loading state

        fetch('../../dataRouting/api/coordinator/GetStudentList.php', { credentials: 'include' })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.json();
            })
            .then(data => {
                console.log('API response data:', data); // Debug log
                if (data.status === 'success') {
                    studentsData = data.students || [];
                    console.log('studentsData after assignment:', studentsData); // Debug log
                    filterAndRenderStudents();
                } else {
                    console.error('API Error:', data.message);
                    renderStudentList([]); // Show empty list or error message
                    studentListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load students: ${data.message || 'Unknown error.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                renderStudentList([]); // Show empty list or error message
                studentListContainer.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error. Please try again.</div>`;
            });
    }

    function filterAndRenderStudents() {
        const search = document.getElementById('searchStudents').value.trim().toLowerCase();
        const status = document.getElementById('statusFilter').value;
        filteredStudents = studentsData.filter(s => {
            const name = (s.Name || '').toLowerCase();
            const roll = (s.RollNo || '').toLowerCase();
            const city = (s.City || '').toLowerCase();
            const matchesSearch = !search || name.includes(search) || roll.includes(search) || city.includes(search);
            const matchesStatus = !status || (s.Status && s.Status.toLowerCase() === status.toLowerCase());
            return matchesSearch && matchesStatus;
        });
        console.log('filteredStudents after filter:', filteredStudents); // Debug log
        renderStudentList(filteredStudents);
    }

    function renderStudentList(list) {
        const container = document.getElementById('studentList');
        container.innerHTML = ''; // Clear previous content

        if (!list.length) {
            container.classList.add('items-center','justify-center','text-gray-400');
            container.innerHTML = `<i class="fas fa-user-graduate fa-3x mb-2"></i><div>No students match your criteria.</div>`;
            return;
        }

        // Remove centering if there are items to display
        container.classList.remove('items-center','justify-center','text-gray-400');
        container.innerHTML = ''; // Clear previous content

        list.forEach(s => {
            const name = s.Name || '';
            const roll = s.RollNo || '';
            const city = s.City || '';
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(s.Status)}">${s.Status||''}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center ${selectedStudentId===s.studentId?'bg-blue-100 border-blue-300':'bg-white'}`;
            item.innerHTML = `
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">${name}</div>
                    <div class="text-gray-500 text-xs">${roll}</div>
                    <div class="text-gray-500 text-sm">${city}</div>
                </div>
                ${statusBadge}
            `;
            item.onclick = () => {
                selectedStudentId = s.studentId;
                renderStudentList(filteredStudents); // Re-render to highlight selection
                fetchStudentDetailsAndRender(s.studentId); // NEW: Fetch full details on click
            };
            container.appendChild(item);
        });
    }

    function getStatusColor(status) {
        switch((status||'').toLowerCase()) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'verified': return 'bg-green-100 text-green-800';
            case 'rejected': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    // NEW: Function to fetch complete student details
    async function fetchStudentDetailsAndRender(studentId) {
        const detailsDiv = document.getElementById('studentDetails');
        detailsDiv.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading student details...</div>`;
        detailsDiv.classList.add('items-center','justify-center','text-gray-400');

        try {
            const apiUrl = `../../dataRouting/api/coordinator/GetStudentDetailsById.php?student_id=${studentId}`;
            const response = await fetch(apiUrl, { credentials: 'include' });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.status === 'success' && data.student) {
                renderStudentDetails(data.student);
            } else {
                detailsDiv.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load details: ${data.message || 'Student not found.'}</div>`;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            detailsDiv.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error loading details.</div>`;
        }
    }

    // MODIFIED: renderStudentDetails to use full student object and add action buttons
    function renderStudentDetails(s) {
        const detailsDiv = document.getElementById('studentDetails');
        const safeJSONParse = (jsonString, defaultValue = {}) => {
            if (!jsonString || typeof jsonString !== 'string') return defaultValue;
            try {
                return JSON.parse(jsonString) || defaultValue;
            } catch (e) {
                console.error("JSON Parse Error:", e);
                return defaultValue;
            }
        };

        const personal = safeJSONParse(s.PersonalDetailsJson);
        const education = safeJSONParse(s.EducationDetailsJson);
        const experiences = safeJSONParse(s.ExperiencesJson);
        const skills = safeJSONParse(s.AdditionalDetailsJson); // Assuming additional_details_json holds skills
        const documents = safeJSONParse(s.DocumentsJson);

        const na = '<span class="text-gray-400">Not provided</span>';
        const createLink = (url, text) => url ? `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${text}</a>` : na;

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

        detailsDiv.classList.remove('items-center','justify-center','text-gray-400');
        detailsDiv.innerHTML = `
            <div class="mb-4 flex items-center border-b pb-4">
                <img src="${documents.photo_link || 'https://via.placeholder.com/150'}" alt="Photo" class="w-24 h-24 rounded-full mr-4 object-cover border-4 border-gray-200 shadow-md">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold">${s.Name || na}</h2>
                    <p class="text-gray-600">${s.RollNo || na}</p>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(s.Status)}">${s.Status || 'N/A'}</span>
                </div>
                ${s.Status.toLowerCase() !== 'verified' ? `
                <div class="ml-auto flex space-x-3">
                    <button id="verifyBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-check-circle mr-2"></i> Verify
                    </button>
                    <button id="modifyBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-edit mr-2"></i> Modify
                    </button>
                    <button id="rejectBtn" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-times-circle mr-2"></i> Reject
                    </button>
                </div>
                ` : ''}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full overflow-y-auto" style="max-height: calc(100vh - 250px);">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Personal Information</h3>
                    <p class="mb-1 text-gray-700"><b>Email:</b> ${personal.personal_email || na}</p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> ${s.PhoneNumber || na}</p>
                    <p class="mb-1 text-gray-700"><b>DOB:</b> ${s.DateOfBirth || na}</p>
                    <p class="mb-1 text-gray-700"><b>Gender:</b> ${s.Gender || na}</p>
                    <p class="mb-1 text-gray-700"><b>Blood Group:</b> ${s.BloodGroup || na}</p>
                    <p class="mb-1 text-gray-700"><b>Category:</b> ${s.Category || na}</p>
                    <p class="mb-1 text-gray-700"><b>Father's Name:</b> ${personal.family_info?.father_name || na}</p>
                    <p class="mb-1 text-gray-700"><b>Mother's Name:</b> ${personal.family_info?.mother_name || na}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Academic Information</h3>
                    <p class="mb-1 text-gray-700"><b>Program:</b> ${s.Program || na}</p>
                    <p class="mb-1 text-gray-700"><b>Department:</b> ${s.Department || na}</p>
                    <p class="mb-1 text-gray-700"><b>Current Semester:</b> ${s.CurrentSemester || na}</p>
                    <p class="mb-1 text-gray-700"><b>CPI:</b> ${s.CPI || na}</p>
                    <p class="mb-1 text-gray-700"><b>Year of Admission:</b> ${s.YearOfAdmission || na}</p>
                    <p class="mb-1 text-gray-700"><b>Year of Passing:</b> ${s.YearOfPassing || na}</p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Address</h3>
                    <p class="mb-1 text-gray-700"><b>Locality:</b> ${s.Locality || na}</p>
                    <p class="mb-1 text-gray-700"><b>City:</b> ${s.City || na}</p>
                    <p class="mb-1 text-gray-700"><b>State:</b> ${s.State || na}</p>
                    <p class="mb-1 text-gray-700"><b>Country:</b> ${s.Country || na}</p>
                    <p class="mb-1 text-gray-700"><b>Pincode:</b> ${s.Pincode || na}</p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Placement & Social</h3>
                    <p class="mb-1 text-gray-700"><b>Placement Interest:</b> ${s.PlacementInterest === '1' ? 'Interested' : 'Not Interested'}</p>
                    <p class="mb-1 text-gray-700"><b>Comments:</b> ${s.Comments || na}</p>
                    <p class="mb-1 text-gray-700"><b>LinkedIn:</b> ${createLink(personal.linkedin_profile, 'LinkedIn Profile')}</p>
                    <p class="mb-1 text-gray-700"><b>GitHub:</b> ${createLink(personal.github_profile, 'GitHub Profile')}</p>
                    <p class="mb-1 text-gray-700"><b>Portfolio:</b> ${createLink(personal.portfolio_link, 'Portfolio Link')}</p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Skills & Interests</h3>
                    <p class="mb-1 text-gray-700"><b>Area of Interest:</b> ${skills.area_of_interest || na}</p>
                    <p class="mb-1 text-gray-700"><b>Programming Skills:</b> ${skills.programming_skills?.join(', ') || na}</p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Education Documents</h3>
                    <p class="mb-1 text-gray-700"><b>10th Board:</b> ${education.tenth_board || na}, <b>Score:</b> ${education.tenth_score || na}%, <b>Year:</b> ${education.tenth_year_of_passing || na}</p>
                    <p class="mb-1 text-gray-700"><b>12th Board:</b> ${education.twelfth_board || na}, <b>Stream:</b> ${education.twelfth_stream || na}, <b>Score:</b> ${education.twelfth_score || na}%, <b>Year:</b> ${education.twelfth_year_of_passing || na}</p>
                    <p class="mb-1 text-gray-700"><b>JEE Year:</b> ${education.jee_year || na}, <b>Mains Rank:</b> ${education.jee_mains_rank || na}</p>
                    ${education.jee_advanced_cleared ? `<p class="mb-1 text-gray-700"><b>JEE Advanced Cleared:</b> Yes, <b>Rank:</b> ${education.jee_advanced_rank || na}</p>` : '<p class="mb-1 text-gray-700"><b>JEE Advanced Cleared:</b> No</p>'}
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Experiences</h3>
                    ${experiencesHtml || '<p>No experiences added.</p>'}
                </div>

                <div class="col-span-1 md:col-span-2 mt-4 pt-4 border-t">
                    <h3 class="text-lg font-semibold mb-2">Documents Links</h3>
                    <p class="mb-1 text-gray-700"><b>Photo:</b> ${createLink(documents.photo_link, 'View Photo')}</p>
                    <p class="mb-1 text-gray-700"><b>10th Marksheet:</b> ${createLink(documents.tenth_marksheet_link, 'View Marksheet')}</p>
                    <p class="mb-1 text-gray-700"><b>12th Marksheet:</b> ${createLink(documents.twelfth_marksheet_link, 'View Marksheet')}</p>
                    <p class="mb-1 text-gray-700"><b>JEE Main Scorecard:</b> ${createLink(documents.jee_main_scorecard_link, 'View Scorecard')}</p>
                    ${education.jee_advanced_cleared ? `<p class="mb-1 text-gray-700"><b>JEE Advanced Scorecard:</b> ${createLink(documents.jee_advanced_scorecard_link, 'View Scorecard')}</p>` : ''}
                    <p class="mb-1 text-gray-700"><b>Internship Certificate:</b> ${createLink(documents.internship_certificate_link, 'View Certificate')}</p>
                    <p class="mb-1 text-gray-700"><b>Other Certificate:</b> ${createLink(documents.other_certificate_link, 'View Certificate')}</p>
                </div>
            </div>

            <div class="col-span-1 md:col-span-2 mt-4 pt-4 border-t">
                <h3 class="text-lg font-semibold mb-2">Profile Information</h3>
                <p class="mb-1 text-gray-700"><b>Profile created on:</b> ${s.created_at || na}</p>
                <p class="mb-1 text-gray-700"><b>Last update upon:</b> ${s.updated_at || na}</p>
            </div>
        `;
        // Add event listeners after rendering the buttons
        if (s.Status.toLowerCase() !== 'verified') {
            document.getElementById('verifyBtn').onclick = () => handleVerificationAction('verified', s.studentId);
            document.getElementById('modifyBtn').onclick = () => openMessageModal('modify', s.studentId);
            document.getElementById('rejectBtn').onclick = () => openMessageModal('rejected', s.studentId);
        }
    }

    // NEW: Function to handle verification actions
    async function handleVerificationAction(newStatus, studentId) {
        // For verify action, we need to open a modal to get the message
        if (newStatus === 'verified') {
            openMessageModal('verified', studentId);
            return;
        }
        
        const message = document.getElementById('messageInput').value;
        if (!message) {
            alert('Remark is required for verification.');
            return;
        }

        const confirm = window.confirm(`Are you sure you want to ${newStatus} this student?`);
        if (!confirm) {
            return;
        }

        try {
            const apiUrl = `../../dataRouting/api/coordinator/UpdateStudentStatus.php`; // You will need to implement this endpoint
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `student_id=${studentId}&status=${newStatus}&remark=${message}&coordinator_id='<?php echo $coordinatorId; ?>'`,
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Student ${newStatus} successfully!`);
                // Refresh the list to show updated status
                fetchStudents();
            } else {
                alert(`Failed to ${newStatus} student: ${data.message}`);
            }
        } catch (error) {
            console.error('Verification Error:', error);
            alert('Network or server error during verification.');
        }
    }

    // NEW: Function to open message modal
    function openMessageModal(action, studentId) {
        const modalTitle = document.getElementById('messageModalTitle');
        const messageInput = document.getElementById('messageInput');
        const submitBtn = document.getElementById('submitMessageBtn');
        const cancelBtn = document.getElementById('cancelMessageBtn');

        // Add event listener for input change to validate
        messageInput.oninput = () => {
            submitBtn.disabled = messageInput.value.trim() === '';
            submitBtn.classList.toggle('opacity-50', submitBtn.disabled);
            submitBtn.classList.toggle('cursor-not-allowed', submitBtn.disabled);
        };

        if (action === 'verified') {
            modalTitle.textContent = 'Verify Student Application';
            messageInput.placeholder = 'Enter verification notes...';
            messageInput.value = ''; // Clear previous message
            submitBtn.textContent = 'Verify';
            submitBtn.onclick = () => handleMessageAction(action, studentId);
            cancelBtn.onclick = () => closeMessageModal();
        } else if (action === 'modify') {
            modalTitle.textContent = 'Modify Student Details';
            messageInput.placeholder = 'Enter your modifications here...';
            messageInput.value = ''; // Clear previous message
            submitBtn.textContent = 'Save Changes';
            submitBtn.onclick = () => handleMessageAction(action, studentId);
            cancelBtn.onclick = () => closeMessageModal();
        } else if (action === 'rejected') {
            modalTitle.textContent = 'Reject Student Application';
            messageInput.placeholder = 'Enter the reason for rejection...';
            messageInput.value = ''; // Clear previous message
            submitBtn.textContent = 'Reject';
            submitBtn.onclick = () => handleMessageAction(action, studentId);
            cancelBtn.onclick = () => closeMessageModal();
        }
        // Initial validation check
        messageInput.oninput(); // Trigger immediately to set initial button state
        document.getElementById('messageModal').classList.remove('hidden');
    }

    // NEW: Function to close message modal
    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        // Clean up message input and button state on close
        document.getElementById('messageInput').value = '';
        const submitBtn = document.getElementById('submitMessageBtn');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    // NEW: Function to handle message actions (Modify/Reject)
    async function handleMessageAction(action, studentId) {
        const message = document.getElementById('messageInput').value.trim();
        if (!message) {
            alert('Message is required for this action.');
            return;
        }

        const confirm = window.confirm(`Are you sure you want to ${action} this student?`);
        if (!confirm) {
            return;
        }

        try {
            const apiUrl = `../../dataRouting/api/coordinator/UpdateStudentStatus.php`; // You'll need to implement this endpoint
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                // Pass current coordinator ID and remark
                body: `student_id=${studentId}&status=${action === 'modify' ? 'resubmit' : action}&remark=${encodeURIComponent(message)}&coordinator_id=<?php echo $coordinatorId; ?>`,
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Student ${action} successfully!`);
                fetchStudents(); // Refresh the list
            } else {
                alert(`Failed to ${action} student: ${data.message || 'Unknown error.'}`);
            }
        } catch (error) {
            console.error('API Error:', error);
            alert('Network or server error during action.');
        } finally {
            closeMessageModal();
        }
    }
    </script>
</body>
<div id="messageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
        <h2 id="messageModalTitle" class="text-xl font-bold mb-4"></h2>
        <textarea id="messageInput" class="w-full p-3 border border-gray-300 rounded-md mb-4 resize-y" rows="5" placeholder="Enter your message here..." required></textarea>
        <div class="flex justify-end space-x-3">
            <button id="cancelMessageBtn" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 font-semibold px-4 py-2 rounded transition-colors duration-200">Cancel</button>
            <button id="submitMessageBtn" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded opacity-50 cursor-not-allowed" disabled>Submit</button>
        </div>
    </div>
</div>
</html> 