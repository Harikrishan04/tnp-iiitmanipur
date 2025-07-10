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
$recruiterId = $_SESSION['user_id'] ?? '4b0890d5-9ffd-4c4d-901d-966bbdbd7676';

// Only recruiters can access
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'recruiter') {
//     header('Location: /tnp/login.php');
//     exit();
// }

require_once __DIR__ . '/../../dataRouting/config/db.php';
$host  = 'localhost';
$dbname = 'tnp';
$username = 'root';
$password = 'Mysql@5805';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = '';
$messageType = '';

function sanitizeFolderName($name) {
    // Only allow alphanumeric, dash, underscore, and space (replace space with underscore)
    return preg_replace('/[^a-zA-Z0-9-_]/', '_', $name);
}

function removeEmptyDirectories($path) {
    // Remove empty directories recursively
    if (is_dir($path)) {
        $files = scandir($path);
        if (count($files) <= 2) { // Only . and .. remain
            if (rmdir($path)) {
                error_log("Removed empty directory: $path");
                // Try to remove parent directory if it becomes empty
                $parent = dirname($path);
                if ($parent !== $path) {
                    removeEmptyDirectories($parent);
                }
            }
        }
    }
}

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
                <div class="mb-6 w-full flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-1 flex items-center">
                            <i class="fas fa-briefcase text-blue-600 mr-3"></i>Job Management
                        </h1>
                        <div class="text-sm text-gray-500">Create and manage your job postings</div>
                    </div>
                    <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i>Create New Job
                    </button>
                </div>
                
                <!-- Debug Information (remove this in production) -->
                <div class="mb-4 p-4 bg-blue-100 border border-blue-300 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">Debug Info:</h4>
                    <p class="text-sm text-blue-700">Session User ID: <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'Not set'); ?></p>
                    <p class="text-sm text-blue-700">Session Role: <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Not set'); ?></p>
                </div>

                <?php if ($message): ?>
                    <div class="mb-4">
                        <div class="rounded-lg px-4 py-3 <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Jobs List will be loaded by JS -->
                <div id="jobsListContainer"></div>
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

    <!-- Create Job Modal -->
    <div id="createJobModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Create New Job</h3>
                    <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="createJobForm" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required class="form-input" placeholder="e.g., Software Engineer, Data Analyst">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Job Location <span class="text-red-500">*</span></label>
                            <input type="text" name="location" required class="form-input" placeholder="e.g., Bangalore, Remote, Hybrid">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Employment Type <span class="text-red-500">*</span></label>
                            <select name="employment_type" required class="form-input">
                                <option value="">Select Type</option>
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Part Time</option>
                                <option value="internship">Internship</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Upload JD Document(s) (PDF/DOC/DOCX) <span class="text-red-500">*</span></label>
                            <input type="file" name="jd_document[]" accept=".pdf,.doc,.docx" multiple required class="form-input">
                            <p class="text-xs text-gray-500 mt-1">Upload one or more JD documents (PDF, DOC, or DOCX). Max 5 files, 10MB total. Server limit: <?php echo ini_get("upload_max_filesize"); ?> per file.</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-medium transition duration-200">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-save mr-2"></i>Create Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View/Edit Job Modal -->
    <div id="viewJobModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div id="viewJobModalContent"></div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Terms and Conditions</h3>
                    <button onclick="closeTermsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="font-medium text-blue-800 mb-2">Important Notice</h4>
                        <p class="text-sm text-blue-700">
                            Please read the following terms and conditions carefully before posting a job. By checking the box below, you agree to comply with all the terms and conditions outlined in this document.
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <h4 class="font-medium text-gray-800 mb-3">Terms and Conditions for Job Posting</h4>
                        
                        <div class="text-sm text-gray-700 space-y-3">
                            <div>
                                <h5 class="font-medium text-gray-800">1. Job Posting Guidelines</h5>
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li>All job postings must be legitimate employment opportunities</li>
                                    <li>Job descriptions must be accurate and complete</li>
                                    <li>Compensation information must be clearly stated</li>
                                    <li>No discriminatory language or requirements are allowed</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h5 class="font-medium text-gray-800">2. Company Verification</h5>
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li>Your company profile must be complete and verified</li>
                                    <li>Contact information must be accurate and up-to-date</li>
                                    <li>Company must be a legitimate business entity</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h5 class="font-medium text-gray-800">3. Data Privacy and Protection</h5>
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li>Student data will be handled according to privacy laws</li>
                                    <li>You agree to use student information only for recruitment purposes</li>
                                    <li>No sharing of student data with third parties without consent</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h5 class="font-medium text-gray-800">4. Application Process</h5>
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li>You must respond to applications within reasonable time</li>
                                    <li>Interview processes must be fair and transparent</li>
                                    <li>No application fees should be charged to students</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h5 class="font-medium text-gray-800">5. Liability and Disclaimers</h5>
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li>TNP Portal is not responsible for the hiring decisions</li>
                                    <li>You are responsible for verifying candidate credentials</li>
                                    <li>Any disputes must be resolved directly with candidates</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h5 class="font-medium text-gray-800">6. Contact Information</h5>
                                <p class="ml-4 mt-1">
                                    For support or questions regarding these terms, please contact:<br>
                                    Email: tnp@iiitmanipur.ac.in<br>
                                    Phone: +91-XXX-XXXXXXX
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mb-4">
                        <a href="../../dataRouting/policy/recruiter/terms_and_conditions.html" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                            <i class="fas fa-external-link-alt mr-1"></i>View Complete Terms and Conditions
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 mt-4 mb-4">
                    <input type="checkbox" id="terms" class="terms-checkbox">
                    <label for="terms" class="text-gray-700 font-medium">
                        I have read and agree to the <a href="../../dataRouting/policy/recruiter/terms_and_conditions.html" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Terms and Conditions</a>
                    </label>
                    <button id="postJobBtn" class="submit-btn bg-gray-300 text-gray-500 px-4 py-2 rounded-lg font-medium transition duration-200" disabled type="button" onclick="postJobFromTerms()">
                        <i class="fas fa-paper-plane mr-2"></i>Post Job
                    </button>
                </div>
                <style>
                .submit-btn {
                    background: #ccc;
                    color: #888;
                    cursor: not-allowed;
                    pointer-events: none;
                    border: none;
                    transition: background 0.3s, color 0.3s;
                }
                .terms-checkbox:checked ~ .submit-btn {
                    background: #22c55e; /* green-600 */
                    color: #fff;
                    cursor: pointer;
                    pointer-events: auto;
                }
                .terms-checkbox:checked ~ .submit-btn:hover {
                    background: #16a34a; /* green-700 */
                }
                </style>
            </div>
        </div>
    </div>

    <script>
    // --- API-DRIVEN JOB MANAGEMENT ---
    let jobs = [];
    let currentJobId = null;

    // Fetch jobs on page load
    document.addEventListener('DOMContentLoaded', fetchJobs);

    function fetchJobs() {
        fetch('../../dataRouting/api/job/list.php', { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    renderJobsList(data.jobs);
                } else {
                    document.getElementById('jobsListContainer').innerHTML = '<div class="text-red-500">Failed to load jobs.</div>';
                }
            })
            .catch(() => {
                document.getElementById('jobsListContainer').innerHTML = '<div class="text-red-500">Error loading jobs.</div>';
            });
    }

    function renderJobsList(jobs) {
        if (!jobs.length) {
            document.getElementById('jobsListContainer').innerHTML = '<div class="text-gray-500">No jobs posted yet.</div>';
            return;
        }
        let html = '<div class="overflow-x-auto"><table class="min-w-full bg-white rounded-lg shadow"><thead><tr>' +
            '<th class="px-4 py-2">Title</th>' +
            '<th class="px-4 py-2">Location</th>' +
            '<th class="px-4 py-2">Type</th>' +
            '<th class="px-4 py-2">Status</th>' +
            '<th class="px-4 py-2">Actions</th>' +
            '</tr></thead><tbody>';
        for (const job of jobs) {
            let actionsHtml = '';
            if (job.status === 'draft') {
                // Escape double quotes in job.title for JS string
                const safeTitle = job.title.replace(/"/g, '\\"');
                actionsHtml = `
                    <a href="#" class="text-blue-600 hover:underline mr-2" onclick="viewJobDetails('${job.id}')">View/Edit</a>
                    <a href="#" class="text-green-600 hover:underline mr-2" onclick="postJob('${job.id}')">Post</a>
                `;
            } else {
                actionsHtml = `<a href="#" class="text-blue-600 hover:underline" onclick="viewJobDetails('${job.id}')">View</a>`;
            }
            html += '<tr>' +
                `<td class="px-4 py-2">${job.title}</td>` +
                `<td class="px-4 py-2">${job.location}</td>` +
                `<td class="px-4 py-2">${job.employment_type.replace('-', ' ')}</td>` +
                `<td class="px-4 py-2">${job.status.replace('_', ' ')}</td>` +
                `<td class="px-4 py-2 space-x-2">` +
                    actionsHtml +
                `</td>` +
            '</tr>';
        }
        html += '</tbody></table></div>';
        document.getElementById('jobsListContainer').innerHTML = html;
    }

    // --- CREATE JOB (API) ---
    function openCreateModal() {
        document.getElementById('createJobModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createJobModal').classList.add('hidden');
        document.getElementById('createJobForm').reset();
    }
    document.getElementById('createJobForm').onsubmit = function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const payload = {
            title: formData.get('title'),
            location: formData.get('location'),
            employment_type: formData.get('employment_type'),
            jd_documents: [] // File upload handled after job creation
        };
        fetch('../../dataRouting/api/job/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'include'
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.job_id) {
                // Now upload JD documents if any
                const fileInput = form.querySelector('input[name="jd_document[]"]');
                const files = fileInput.files;
                if (files.length > 0) {
                    const uploadFormData = new FormData();
                    uploadFormData.append('job_id', data.job_id);
                    for (let i = 0; i < files.length; i++) {
                        uploadFormData.append('new_jd_documents[]', files[i]);
                    }
                    uploadFormData.append('files_to_remove', JSON.stringify([]));
                    fetch('../../dataRouting/api/job/upload_documents.php', {
                        method: 'POST',
                        body: uploadFormData,
                        credentials: 'include'
                    })
                    .then(r => r.json())
                    .then(uploadData => {
                        if (uploadData.status === 'success') {
                            closeCreateModal();
                            fetchJobs();
                            alert('Job and documents created successfully!');
                        } else {
                            alert('Job created, but document upload failed: ' + uploadData.message);
                        }
                    })
                    .catch(() => {
                        alert('Job created, but error uploading documents.');
                    });
                } else {
                    closeCreateModal();
                    fetchJobs();
                    alert('Job created successfully!');
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(() => {
            alert('Error creating job.');
        });
    };

    // --- EDIT JOB (Combined with View) ---
    function closeEditModal() {
        document.getElementById('viewJobModal').classList.add('hidden');
    }

    // --- VIEW/EDIT JOB DETAILS (API) ---
    function viewJobDetails(jobId) {
        fetch(`../../dataRouting/api/job/view.php?job_id=${encodeURIComponent(jobId)}`)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success' && data.job) {
                    const job = data.job;
                    const isEditable = job.status === 'draft';
                    
                    let filesHtml = '';
                    if (job.job_description_path) {
                        const files = job.job_description_path.split(',');
                        filesHtml = files.map((file, index) =>
                            `<div class=\"flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0\">` +
                            `<div class=\"flex items-center\">` +
                            `<i class=\"fas fa-file-pdf text-red-500 mr-2\"></i>` +
                            `<a href=\"${file}\" target=\"_blank\" class=\"text-blue-600 hover:text-blue-800 text-sm\">${file.split('/').pop()}</a>` +
                            `</div>` +
                            (isEditable ? 
                                `<div class=\"flex items-center space-x-2\">` +
                                `<label class=\"flex items-center\">` +
                                `<input type=\"checkbox\" name=\"remove_files[]\" value=\"${index}\" class=\"mr-2\">` +
                                `<span class=\"text-xs text-red-600\">Remove</span>` +
                                `</label>` +
                                `</div>` 
                            : '') +
                            `</div>`
                        ).join('');
                    }
                    
                    let html = `<div class=\"mt-3\">` +
                        `<div class=\"flex justify-between items-center mb-4\">` +
                            `<h3 class=\"text-lg font-medium text-gray-900\">${isEditable ? 'View/Edit Job' : 'Job Details'}</h3>` +
                            `<button onclick=\"closeViewModal()\" class=\"text-gray-400 hover:text-gray-600\">` +
                                `<i class=\"fas fa-times\"></i>` +
                            `</button>` +
                        `</div>` +
                        `<form id=\"jobDetailsForm\">` +
                        `<div class=\"space-y-4\">` +
                            `<div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">` +
                                `<div>` +
                                    `<label class=\"block text-gray-700 font-medium mb-1\">Job Title</label>` +
                                    (isEditable ? 
                                        `<input type=\"text\" name=\"title\" value=\"${job.title}\" required class=\"form-input\" placeholder=\"e.g., Software Engineer, Data Analyst\">` :
                                        `<p class=\"text-gray-900\">${job.title}</p>`
                                    ) +
                                `</div>` +
                                `<div>` +
                                    `<label class=\"block text-gray-700 font-medium mb-1\">Location</label>` +
                                    (isEditable ? 
                                        `<input type=\"text\" name=\"location\" value=\"${job.location}\" required class=\"form-input\" placeholder=\"e.g., Bangalore, Remote, Hybrid\">` :
                                        `<p class=\"text-gray-900\">${job.location}</p>`
                                    ) +
                                `</div>` +
                            `</div>` +
                            `<div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">` +
                                `<div>` +
                                    `<label class=\"block text-gray-700 font-medium mb-1\">Employment Type</label>` +
                                    (isEditable ? 
                                        `<select name=\"employment_type\" required class=\"form-input\">` +
                                        `<option value=\"full-time\"${job.employment_type === 'full-time' ? ' selected' : ''}>Full Time</option>` +
                                        `<option value=\"part-time\"${job.employment_type === 'part-time' ? ' selected' : ''}>Part Time</option>` +
                                        `<option value=\"internship\"${job.employment_type === 'internship' ? ' selected' : ''}>Internship</option>` +
                                        `<option value=\"contract\"${job.employment_type === 'contract' ? ' selected' : ''}>Contract</option>` +
                                        `</select>` :
                                        `<p class=\"text-gray-900\">${job.employment_type.replace('-', ' ')}</p>`
                                    ) +
                                `</div>` +
                                `<div>` +
                                    `<label class=\"block text-gray-700 font-medium mb-1\">Status</label>` +
                                    `<span class=\"px-2 py-1 rounded-full text-xs font-medium\">${job.status.replace('_', ' ')}</span>` +
                                `</div>` +
                            `</div>` +
                            `<div>` +
                                `<label class=\"block text-gray-700 font-medium mb-1\">Posted Date</label>` +
                                `<p class=\"text-gray-900\">${new Date(job.created_at).toLocaleDateString()}</p>` +
                            `</div>` +
                            (isEditable ? 
                                `<!-- Upload Document Section -->` +
                                `<div class=\"mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg\">` +
                                    `<h4 class=\"font-medium text-blue-800 mb-3 flex items-center\">` +
                                        `<i class=\"fas fa-upload mr-2\"></i>Upload New JD Documents` +
                                    `</h4>` +
                                    `<div class=\"space-y-3\">` +
                                        `<div>` +
                                            `<label class=\"block text-gray-700 font-medium mb-2\">Upload JD Document(s) <span class=\"text-blue-600\">(Optional)</span></label>` +
                                            `<input type=\"file\" name=\"new_jd_document[]\" accept=\".pdf,.doc,.docx\" multiple class=\"form-input border-2 border-dashed border-blue-300 hover:border-blue-400 transition-colors\">` +
                                            `<p class=\"text-xs text-gray-600 mt-1\">` +
                                                `<i class=\"fas fa-info-circle mr-1\"></i>` +
                                                `Supported formats: PDF, DOC, DOCX. Max 5 files total, 10MB per upload.` +
                                            `</p>` +
                                        `</div>` +
                                        `<div class=\"text-xs text-blue-700 bg-blue-100 p-2 rounded\">` +
                                            `<i class=\"fas fa-lightbulb mr-1\"></i>` +
                                            `<strong>Tip:</strong> New files will be added to your existing JD documents.` +
                                        `</div>` +
                                    `</div>` +
                                `</div>` 
                            : '') +
                            `<!-- Existing JD Files Section -->` +
                            (filesHtml ? 
                                `<div class=\"mt-4\">` +
                                    `<label class=\"block text-gray-700 font-medium mb-2\">Current JD Files</label>` +
                                    `<div class=\"bg-gray-50 p-3 rounded-lg\">` +
                                        filesHtml +
                                    `</div>` +
                                    (isEditable ? `<p class=\"text-xs text-gray-500 mt-1\">Check "Remove" to delete files. New files will be added to existing ones.</p>` : '') +
                                `</div>` 
                            : '') +
                        `</div>` +
                        `<!-- Action Buttons -->` +
                        `<div class=\"flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-4\">` +
                            `<button type=\"button\" onclick=\"closeViewModal()\" class=\"bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-medium transition duration-200\">Close</button>` +
                            (isEditable ? 
                                `<button type=\"button\" onclick=\"saveJobChanges('${job.id}')\" class=\"bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200\">` +
                                `<i class=\"fas fa-save mr-2\"></i>Save Changes` +
                                `</button>` 
                            : '') +
                        `</div>` +
                        `</form>` +
                    `</div>`;
                    document.getElementById('viewJobModalContent').innerHTML = html;
                    document.getElementById('viewJobModal').classList.remove('hidden');
                } else {
                    alert('Job not found.');
                }
            })
            .catch(() => {
                alert('Error loading job details.');
            });
    }
    function closeViewModal() {
        document.getElementById('viewJobModal').classList.add('hidden');
    }

    // --- SAVE JOB CHANGES (Combined) ---
    function saveJobChanges(jobId) {
        const modal = document.getElementById('viewJobModalContent');
        const form = modal.querySelector('#jobDetailsForm');
        const formData = new FormData(form);
        const fileInput = modal.querySelector('input[name="new_jd_document[]"]');
        const removeCheckboxes = modal.querySelectorAll('input[name="remove_files[]"]:checked');
        
        // Get job field changes
        const payload = {
            job_id: jobId,
            title: formData.get('title'),
            location: formData.get('location'),
            employment_type: formData.get('employment_type')
            // jd_documents will only be added if needed
        };
        
        // Get new files
        const newFiles = fileInput.files;
        const formDataForUpload = new FormData();
        formDataForUpload.append('job_id', jobId);
        
        // Add new files
        for (let i = 0; i < newFiles.length; i++) {
            formDataForUpload.append('new_jd_documents[]', newFiles[i]);
        }
        
        // Get files to remove
        const filesToRemove = Array.from(removeCheckboxes).map(cb => cb.value);
        
        // Show loading state
        const saveBtn = modal.querySelector('button[onclick*="saveJobChanges"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        saveBtn.disabled = true;
        
        // First save job field changes
        fetch('../../dataRouting/api/job/edit.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'include'
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                // Handle file operations sequentially
                let operations = [];
                
                // Add file removal operation if needed
                if (filesToRemove.length > 0) {
                    operations.push(
                        fetch('../../dataRouting/api/job/remove_documents.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                job_id: jobId,
                                files_to_remove: JSON.stringify(filesToRemove)
                            }),
                            credentials: 'include'
                        }).then(r => r.json())
                    );
                }
                
                // Add file upload operation if needed
                if (newFiles.length > 0) {
                    operations.push(
                        fetch('../../dataRouting/api/job/upload_documents.php', {
                            method: 'POST',
                            body: formDataForUpload,
                            credentials: 'include'
                        }).then(r => r.json())
                    );
                }
                
                // Execute operations
                if (operations.length > 0) {
                    Promise.all(operations)
                        .then(results => {
                            let success = true;
                            let messages = [];
                            
                            results.forEach((result, index) => {
                                if (result.status !== 'success') {
                                    success = false;
                                    messages.push(result.message || 'Operation failed');
                                }
                            });
                            
                            if (success) {
                                alert('Job and documents updated successfully!');
                                closeViewModal();
                                fetchJobs(); // Refresh job list
                            } else {
                                alert('Job updated, but some document operations failed: ' + messages.join(', '));
                                saveBtn.innerHTML = originalText;
                                saveBtn.disabled = false;
                            }
                        })
                        .catch(() => {
                            alert('Job updated, but error with document operations.');
                            saveBtn.innerHTML = originalText;
                            saveBtn.disabled = false;
                        });
                } else {
                    // No file operations needed
                    alert('Job updated successfully!');
                    closeViewModal();
                    fetchJobs();
                }
            } else {
                if (data.message && data.message.includes('not editable')) {
                    alert('No changes made.');
                } else {
                    alert('Error: ' + data.message);
                }
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        })
        .catch(() => {
            alert('Error updating job.');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }

    // --- POST JOB (STATUS CHANGE) ---
    function showTermsModal(jobId) {
        currentJobId = jobId;
        document.getElementById('termsModal').classList.remove('hidden');
        document.getElementById('terms').checked = false;
        document.getElementById('postJobBtn').disabled = true;
    }
    function closeTermsModal() {
        document.getElementById('termsModal').classList.add('hidden');
        currentJobId = null;
    }
    document.getElementById('terms').addEventListener('change', function() {
        document.getElementById('postJobBtn').disabled = !this.checked;
    });
    function postJobFromTerms() {
        if (!currentJobId) {
            alert('No job selected for posting.');
            return;
        }
        if (!document.getElementById('terms').checked) {
            alert('Please accept the terms and conditions before posting.');
            return;
        }
        if (confirm('Are you sure you want to post this job? Once posted, it will be sent to the coordinator for verification and you won\'t be able to edit it until it\'s returned.')) {
            fetch('../../dataRouting/api/job/update_status.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ job_id: currentJobId, status: 'pending_verification' }),
                credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Job posted successfully! It is now pending coordinator verification.');
                    closeTermsModal();
                    fetchJobs();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => {
                alert('Error posting job.');
            });
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const createModal = document.getElementById('createJobModal');
        const viewModal = document.getElementById('viewJobModal');
        const termsModal = document.getElementById('termsModal');
        if (event.target === createModal) closeCreateModal();
        if (event.target === viewModal) closeViewModal();
        if (event.target === termsModal) closeTermsModal();
    }

    // Add Font Awesome plus icon to Add Job button
    // Find the Add Job button and update its HTML
    window.addEventListener('DOMContentLoaded', function() {
        const addJobBtn = document.getElementById('addJobBtn');
        if (addJobBtn) {
            addJobBtn.innerHTML = '<i class="fas fa-plus mr-2"></i>Add Job';
        }
    });
    </script>
</body>
</html> 