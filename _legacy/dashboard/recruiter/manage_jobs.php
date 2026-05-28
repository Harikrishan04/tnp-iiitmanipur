<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'recruiter';
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$recruiterId = $_SESSION['user_id'] ?? '123e4567-e89b-12d3-a456-426614174000';

// Handle session messages (for redirects)
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
unset($_SESSION['message'], $_SESSION['messageType']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - TNP Portal</title>
    <link href="../../assets/css/2.2.19.tailwind.min.css" rel="stylesheet">
    <link href="../../assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="formstyle.css" rel="stylesheet">
    <style>
        #jobList {
            overflow-y: auto;
            min-height: 200px;
        }
        /* Styles for the custom message box */
        .message-box {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
            transform: translateY(-20px);
            opacity: 0;
        }
        .message-box.show {
            transform: translateY(0);
            opacity: 1;
        }
        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message-box .close-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: inherit;
            margin-left: 10px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <div class="flex flex-row justify-between items-center mb-4">
                    <h1 class="text-2xl font-bold">Manage Jobs</h1>
                    <button id="createOpportunityBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded shadow flex items-center">
                        <i class="fas fa-plus mr-2"></i> Create Opportunity
                    </button>
                </div>
                <div class="flex flex-1 gap-6 min-h-0">
                    <div class="w-full md:w-1/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div class="flex items-center mb-4">
                            <h2 class="text-xl font-bold flex-grow">My Jobs</h2>
                            <input type="text" id="searchJobs" class="form-input ml-4" placeholder="Search jobs...">
                            <select id="statusFilter" class="form-input ml-2">
                                <option value="" selected>All Status</option>
                                <option value="draft">Draft</option>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div id="jobList" class="flex-1 flex flex-col overflow-y-auto">
                            <i class="fas fa-spinner fa-spin fa-3x mb-2 self-center"></i>
                            <div class="self-center text-gray-400">Loading jobs...</div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="jobDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-briefcase fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select a Job</div>
                            <div>Choose a job from the list to view its detailed info</div>
                        </div>
                    </div>
                </div>
                <div id="createJobModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-8 relative">
                        <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">×</button>
                        <form id="createJobForm" class="space-y-6" autocomplete="off" novalidate>
                            <h2 class="text-xl font-bold mb-6">Create New Opportunity</h2>
                            <div class="mb-6">
                                <label for="jobTitle" class="block mb-1 text-sm text-gray-700 font-medium">Job Title (e.g. Software Engineer, Researcher)</label>
                                <input type="text" id="jobTitle" name="jobTitle" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="jobType" class="block mb-1 text-sm text-gray-700 font-medium">Job Type</label>
                                <select id="jobType" name="jobType" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300">
                                    <option value="" disabled selected hidden></option>
                                    <option value="Full time">Full time</option>
                                    <option value="Part time">Part time</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Volunteer">Volunteer</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-6" id="typeOtherContainer" style="display:none;">
                                <label for="typeOther" class="block mb-1 text-sm text-gray-700 font-medium">Other Type</label>
                                <input type="text" id="typeOther" name="typeOther" class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="jobLocation" class="block mb-1 text-sm text-gray-700 font-medium">Location (e.g. Remote, Hybrid, Delhi)</label>
                                <input type="text" id="jobLocation" name="jobLocation" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="jobDescription" class="block mb-1 text-sm text-gray-700 font-medium">Description</label>
                                <textarea id="jobDescription" name="jobDescription" required rows="3" class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300"></textarea>
                            </div>
                            <div class="mb-6">
                                <label class="block mb-1 text-sm text-gray-700 font-medium">Detailed Job Description Link(s)</label>
                                <div id="jobDocumentsContainer">
                                    </div>
                                <button type="button" id="addJobDocumentLinkBtn" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm"><i class="fas fa-plus mr-1"></i> Add More Link</button>
                            </div>
                             <div class="flex justify-end items-center mb-6 gap-2">
                                <button id="cancelBtn" type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 font-semibold px-4 py-2 rounded transition-colors duration-200">Cancel</button>
                                <button id="saveDraftBtn" type="submit" class="bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold px-5 py-2 rounded opacity-50 cursor-not-allowed transition-colors duration-300" disabled><i class="fa-solid fa-floppy-disk mr-2"></i> Draft</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="editJobModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-8 relative">
                        <button id="closeEditModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">×</button>
                         <form id="editJobForm" class="space-y-6" autocomplete="off" novalidate>
                            <h2 class="text-xl font-bold mb-6">Edit Job Opportunity</h2>
                            <div class="mb-6">
                                <label for="editJobTitle" class="block mb-1 text-sm text-gray-700 font-medium">Job Title</label>
                                <input type="text" id="editJobTitle" name="editJobTitle" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="editJobType" class="block mb-1 text-sm text-gray-700 font-medium">Job Type</label>
                                <select id="editJobType" name="editJobType" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300">
                                    <option value="" disabled selected hidden></option>
                                    <option value="Full time">Full time</option>
                                    <option value="Part time">Part time</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Volunteer">Volunteer</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-6" id="editTypeOtherContainer" style="display:none;">
                                <label for="editTypeOther" class="block mb-1 text-sm text-gray-700 font-medium">Other Type</label>
                                <input type="text" id="editTypeOther" name="editTypeOther" class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="editJobLocation" class="block mb-1 text-sm text-gray-700 font-medium">Location</label>
                                <input type="text" id="editJobLocation" name="editJobLocation" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="editJobDescription" class="block mb-1 text-sm text-gray-700 font-medium">Description</label>
                                <textarea id="editJobDescription" name="editJobDescription" required rows="3" class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300"></textarea>
                            </div>
                            <div class="mb-6">
                                <label class="block mb-1 text-sm text-gray-700 font-medium">Detailed Job Description Link(s)</label>
                                <div id="editJobDocumentsContainer">
                                    </div>
                                <button type="button" id="addEditJobDocumentLinkBtn" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm"><i class="fas fa-plus mr-1"></i> Add More Link</button>
                            </div>
                             <div class="flex justify-end items-center mb-6 gap-2">
                                <button id="cancelEditBtn" type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 font-semibold px-4 py-2 rounded transition-colors duration-200">Cancel</button>
                                <button id="saveEditBtn" type="submit" class="bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold px-5 py-2 rounded opacity-50 cursor-not-allowed transition-colors duration-300" disabled><i class="fa-solid fa-floppy-disk mr-2"></i> Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
            <footer class="bg-white border-t border-gray-200 py-4 w-full mt-auto flex-shrink-0">
                <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center">
                    <p class="text-gray-600 text-sm">© 2025 Training & Placement Portal. All rights reserved.</p>
                    <div class="flex space-x-4 mt-2 sm:mt-0 text-gray-600 text-sm">
                        <a href="#" class="hover:text-gray-900">Help</a>
                        <a href="#" class="hover:text-gray-900">Privacy</a>
                        <a href="#" class="hover:text-gray-900">Terms</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div id="messageBox" class="message-box hidden" role="alert">
        <span id="messageBoxIcon"></span>
        <span id="messageBoxText"></span>
        <button type="button" class="close-btn" aria-label="Close message">&times;</button>
    </div>

    <script>
    let jobsData = [];
    let filteredJobs = [];
    let selectedJobId = null;
    let currentFullJobDetails = null;

    const recruiterId = "<?php echo $recruiterId; ?>";

    // --- Message Box Functions ---
    const messageBox = document.getElementById('messageBox');
    const messageBoxText = document.getElementById('messageBoxText');
    const messageBoxIcon = document.getElementById('messageBoxIcon');
    const messageBoxCloseBtn = messageBox.querySelector('.close-btn');

    messageBoxCloseBtn.addEventListener('click', () => {
        messageBox.classList.remove('show');
        setTimeout(() => messageBox.classList.add('hidden'), 300);
    });

    function showMessageBox(message, type = 'success', duration = 3000) {
        messageBoxText.textContent = message;
        messageBox.className = `message-box ${type}`;
        messageBox.classList.remove('hidden');
        messageBoxIcon.innerHTML = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
        
        void messageBox.offsetWidth; // Trigger reflow for transition
        messageBox.classList.add('show');

        if (duration > 0) {
            setTimeout(() => {
                messageBox.classList.remove('show');
                setTimeout(() => messageBox.classList.add('hidden'), 300);
            }, duration);
        }
    }
    
    // --- Initial Load ---
    document.addEventListener('DOMContentLoaded', () => {
        fetchJobsAndRender();
        document.getElementById('searchJobs').addEventListener('input', filterAndRenderJobs);
        document.getElementById('statusFilter').addEventListener('change', filterAndRenderJobs);
    });

    // --- Data Fetching ---
    async function fetchJobsAndRender() {
        const jobListContainer = document.getElementById('jobList');
        jobListContainer.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x self-center"></i><div class="self-center text-gray-400">Loading jobs...</div>`;
        
        try {
            const response = await fetch(`../../dataRouting/api/recruiter/getRecruiterJobsList.php?recruiter_id=${recruiterId}`);
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            const data = await response.json();

            if (data.status === 'success' && Array.isArray(data.jobs)) {
                jobsData = data.jobs.map(job => ({
                    id: job.EventID,
                    title: job.Event,
                    status: job.Status,
                    location: job.Location,
                    posted: new Date(job.Posted).toLocaleDateString('en-CA')
                }));
                filterAndRenderJobs();
                 // If a job was selected before reload, re-select and re-render its details
                if (selectedJobId) {
                    const selectedJobExists = jobsData.some(j => j.id === selectedJobId);
                    if (selectedJobExists) {
                        fetchJobDetailsAndRender(selectedJobId);
                    } else {
                        // The selected job no longer exists (e.g., deleted), so clear the details pane
                        selectedJobId = null;
                        document.getElementById('jobDetails').innerHTML = `<i class="fas fa-briefcase fa-3x mb-2"></i><div class="text-lg font-semibold mb-2">Select a Job</div><div>Choose a job from the list to view its detailed info</div>`;
                        document.getElementById('jobDetails').classList.add('items-center', 'justify-center', 'text-gray-400');
                    }
                }
            } else {
                jobListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x self-center"></i><div>${data.message || 'Failed to load jobs.'}</div>`;
            }
        } catch (error) {
            console.error('Error fetching jobs:', error);
            jobListContainer.innerHTML = `<i class="fas fa-times-circle fa-3x self-center"></i><div>Error loading jobs. Please try again.</div>`;
        }
    }

    async function fetchJobDetailsAndRender(jobId) {
        const jobDetailsContainer = document.getElementById('jobDetails');
        jobDetailsContainer.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x self-center"></i><div class="self-center text-gray-400">Loading details...</div>`;
        jobDetailsContainer.classList.add('items-center', 'justify-center', 'text-gray-400');

        try {
            const response = await fetch(`../../dataRouting/api/recruiter/getJobDetailsById.php?event_id=${jobId}`);
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            const data = await response.json();

            if (data.status === 'success' && data.job && data.job.length > 0) {
                const job = data.job[0];
                currentFullJobDetails = {
                    id: job.EventID,
                    title: job.Event,
                    type: job.Type,
                    status: job.Status,
                    location: job.Location,
                    posted: new Date(job.Posted).toLocaleDateString('en-CA'),
                    description: job.Description,
                    attachedDocuments: job.AttachedDocumens ? JSON.parse(job.AttachedDocumens) : []
                };
                renderJobDetails(currentFullJobDetails);
            } else {
                jobDetailsContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x self-center"></i><div>${data.message || 'Job details not found.'}</div>`;
            }
        } catch (error) {
            console.error('Error fetching job details:', error);
            jobDetailsContainer.innerHTML = `<i class="fas fa-times-circle fa-3x self-center"></i><div>Error loading details.</div>`;
        }
    }

    // --- UI Rendering ---
    function filterAndRenderJobs() {
        const search = document.getElementById('searchJobs').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        filteredJobs = jobsData.filter(j => 
            (!search || j.title.toLowerCase().includes(search) || j.location.toLowerCase().includes(search)) &&
            (!status || j.status.toLowerCase() === status)
        );
        renderJobList(filteredJobs);
    }

    function renderJobList(list) {
        const container = document.getElementById('jobList');
        if (list.length === 0) {
            container.innerHTML = `<div class="text-center text-gray-400"><i class="fas fa-briefcase fa-2x mb-2"></i><p>No jobs found.</p></div>`;
            return;
        }
        container.innerHTML = list.map(j => `
            <button type="button" data-job-id="${j.id}" class="w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border flex items-center ${selectedJobId === j.id ? 'bg-blue-100 border-blue-400' : 'border-gray-100'}">
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">${j.title}</div>
                    <div class="text-gray-500 text-xs">${j.location} | Posted: ${j.posted}</div>
                </div>
                <span class="px-2 py-1 rounded text-xs ${getStatusColor(j.status)}">${j.status}</span>
            </button>
        `).join('');
        
        container.querySelectorAll('button[data-job-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedJobId = btn.dataset.jobId;
                renderJobList(filteredJobs); // Re-render to update selection style
                fetchJobDetailsAndRender(selectedJobId);
            });
        });
    }

    function renderJobDetails(j) {
        const detailsDiv = document.getElementById('jobDetails');
        detailsDiv.classList.remove('items-center', 'justify-center', 'text-gray-400');
        const documents = j.attachedDocuments.map(link => `<a href="${link}" target="_blank" class="text-blue-600 hover:underline break-all">${link}</a>`).join(', ') || 'None';
        const canSubmit = j.status && (j.status.toLowerCase() === 'draft' || j.status.toLowerCase() === 'rejected');

        detailsDiv.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">${j.title} <span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(j.status)}">${j.status}</span></h2>
                <div class="flex items-center gap-2">
                    <button id="editJobBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-semibold"><i class="fa-solid fa-pen-to-square mr-2"></i> Edit</button>
                    <button id="submitForVerificationBtn" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm font-semibold ${!canSubmit ? 'opacity-50 cursor-not-allowed' : ''}" ${!canSubmit ? 'disabled' : ''}>
                        <i class="fa-solid fa-paper-plane mr-2"></i> Submit for Verification
                    </button>
                </div>
            </div>
            <div class="text-gray-700 space-y-2">
                <p><b>Job Type:</b> ${j.type}</p>
                <p><b>Location:</b> ${j.location}</p>
                <p><b>Posted:</b> ${j.posted}</p>
                <p><b>Description:</b> ${j.description}</p>
                <p><b>Documents:</b> ${documents}</p>
            </div>`;

        document.getElementById('editJobBtn').addEventListener('click', () => openEditModal(j));
        document.getElementById('submitForVerificationBtn').addEventListener('click', () => submitForVerification(j));
    }

    function getStatusColor(status) {
        const s = (status || '').toLowerCase();
        if (s === 'open' || s === 'verified') return 'bg-green-100 text-green-800';
        if (s === 'pending') return 'bg-yellow-100 text-yellow-800';
        if (s === 'closed' || s === 'rejected') return 'bg-red-100 text-red-800';
        return 'bg-gray-200 text-gray-600';
    }

    // --- Modal and Form Logic ---
    // Common elements
    const createJobModal = document.getElementById('createJobModal');
    const editJobModal = document.getElementById('editJobModal');

    // Create Modal
    const createOpportunityBtn = document.getElementById('createOpportunityBtn');
    const createJobForm = document.getElementById('createJobForm');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const jobDocumentsContainer = document.getElementById('jobDocumentsContainer');

    function addDocumentLinkInput(container, value = '', isRequired = false) {
        const div = document.createElement('div');
        div.className = 'flex items-center mb-2';
        div.innerHTML = `
            <input type="url" name="jobDocuments[]" class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none" placeholder="https://example.com/doc" value="${value}" ${isRequired ? 'required' : ''} />
            <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-link-btn ${isRequired ? 'hidden' : ''}"><i class="fas fa-times-circle"></i></button>`;
        container.appendChild(div);
        div.querySelector('.remove-link-btn').addEventListener('click', () => {
             div.remove();
             validateForm(createJobForm, saveDraftBtn);
        });
        div.querySelector('input').addEventListener('input', () => validateForm(createJobForm, saveDraftBtn));
    }

    createOpportunityBtn.addEventListener('click', () => {
        createJobForm.reset();
        jobDocumentsContainer.innerHTML = '';
        addDocumentLinkInput(jobDocumentsContainer, '', true);
        document.getElementById('typeOtherContainer').style.display = 'none';
        document.getElementById('typeOther').required = false; // Ensure it's not required by default
        validateForm(createJobForm, saveDraftBtn);
        createJobModal.classList.remove('hidden');
    });

    createJobForm.addEventListener('input', () => validateForm(createJobForm, saveDraftBtn));
    document.getElementById('jobType').addEventListener('change', (e) => {
        const isOther = e.target.value === 'Other';
        document.getElementById('typeOtherContainer').style.display = isOther ? 'block' : 'none';
        document.getElementById('typeOther').required = isOther;
        validateForm(createJobForm, saveDraftBtn);
    });

    document.getElementById('addJobDocumentLinkBtn').addEventListener('click', () => addDocumentLinkInput(jobDocumentsContainer));
    document.getElementById('closeModalBtn').addEventListener('click', () => createJobModal.classList.add('hidden'));
    document.getElementById('cancelBtn').addEventListener('click', () => createJobModal.classList.add('hidden'));
    
    // Edit Modal
    const editJobForm = document.getElementById('editJobForm');
    const saveEditBtn = document.getElementById('saveEditBtn');
    const editJobDocumentsContainer = document.getElementById('editJobDocumentsContainer');

    function openEditModal(job) {
        editJobForm.reset();
        document.getElementById('editJobTitle').value = job.title;
        document.getElementById('editJobLocation').value = job.location;
        document.getElementById('editJobDescription').value = job.description;

        const isOther = !['Full time', 'Part time', 'Internship', 'Volunteer'].includes(job.type);
        document.getElementById('editJobType').value = isOther ? 'Other' : job.type;
        document.getElementById('editTypeOtherContainer').style.display = isOther ? 'block' : 'none';
        document.getElementById('editTypeOther').required = isOther;
        if (isOther) document.getElementById('editTypeOther').value = job.type;

        editJobDocumentsContainer.innerHTML = '';
        if (job.attachedDocuments.length > 0) {
            job.attachedDocuments.forEach((link, i) => addDocumentLinkInput(editJobDocumentsContainer, link, i === 0));
        } else {
            addDocumentLinkInput(editJobDocumentsContainer, '', true);
        }
        
        validateForm(editJobForm, saveEditBtn);
        editJobModal.classList.remove('hidden');
    }
    
    editJobForm.addEventListener('input', () => validateForm(editJobForm, saveEditBtn));
    document.getElementById('editJobType').addEventListener('change', (e) => {
        const isOther = e.target.value === 'Other';
        document.getElementById('editTypeOtherContainer').style.display = isOther ? 'block' : 'none';
        document.getElementById('editTypeOther').required = isOther;
        validateForm(editJobForm, saveEditBtn);
    });

    document.getElementById('addEditJobDocumentLinkBtn').addEventListener('click', () => addDocumentLinkInput(editJobDocumentsContainer));
    document.getElementById('closeEditModalBtn').addEventListener('click', () => editJobModal.classList.add('hidden'));
    document.getElementById('cancelEditBtn').addEventListener('click', () => editJobModal.classList.add('hidden'));

    function validateForm(form, button) {
        const isValid = form.checkValidity();
        button.disabled = !isValid;
        button.classList.toggle('opacity-50', !isValid);
        button.classList.toggle('cursor-not-allowed', !isValid);
    }

    // --- API Actions ---
    async function handleFormSubmit(form, url, payload, button, successMsg) {
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (result.status === 'success') {
                showMessageBox(successMsg, 'success');
                (form.id === 'createJobForm' ? createJobModal : editJobModal).classList.add('hidden');
                form.reset();
                fetchJobsAndRender();
            } else {
                showMessageBox(result.message || 'An error occurred.', 'error');
            }
        } catch (error) {
            console.error('Form submission error:', error);
            showMessageBox('A network error occurred. Please try again.', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = originalButtonText;
            validateForm(form, button);
        }
    }

    createJobForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const documentLinks = Array.from(this.querySelectorAll('input[name="jobDocuments[]"]')).map(i => i.value.trim()).filter(Boolean);
        const jobType = formData.get('jobType');
        
        const payload = {
            p_event_id: null,
            p_event_organiser_id: recruiterId,
            p_event_title: formData.get('jobTitle'),
            p_event_type: jobType === 'Other' ? formData.get('typeOther') : jobType,
            p_event_description: formData.get('jobDescription'),
            p_event_document: JSON.stringify(documentLinks),
            p_event_location: formData.get('jobLocation'),
            p_event_status: 'draft' // Always create as draft
        };
        handleFormSubmit(this, '../../dataRouting/api/event/putJob.php', payload, saveDraftBtn, 'Job drafted successfully!');
    });

    editJobForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const documentLinks = Array.from(this.querySelectorAll('input[name="jobDocuments[]"]')).map(i => i.value.trim()).filter(Boolean);
        const jobType = formData.get('editJobType');

        const payload = {
            p_event_id: currentFullJobDetails.id,
            p_event_organiser_id: recruiterId,
            p_event_title: formData.get('editJobTitle'),
            p_event_type: jobType === 'Other' ? formData.get('editTypeOther') : jobType,
            p_event_description: formData.get('editJobDescription'),
            p_event_document: JSON.stringify(documentLinks),
            p_event_location: formData.get('editJobLocation'),
            p_event_status: currentFullJobDetails.status // Preserve original status on edit
        };
        handleFormSubmit(this, '../../dataRouting/api/event/putJob.php', payload, saveEditBtn, 'Job saved successfully!');
    });
    
    async function submitForVerification(job) {
        if (!confirm('Are you sure you want to submit this job for verification?')) return;
        
        const button = document.getElementById('submitForVerificationBtn');
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
        
        // Prepare FormData for the updateVerificationStatus.php API
        const formData = new FormData();
        formData.append('event_id', job.id);

        try {
            const response = await fetch('../../dataRouting/api/event/updateVerificationStatus.php', {
                method: 'POST',
                body: formData // Use FormData for content-type: application/x-www-form-urlencoded
            });
            const result = await response.json();

            if (result.status === 'success') {
                showMessageBox('Job submitted for verification!', 'success');
                fetchJobsAndRender();
            } else {
                showMessageBox(result.message || 'Submission failed.', 'error');
            }
        } catch (error) {
             console.error('Submission error:', error);
            showMessageBox('A network error occurred during submission.', 'error');
        } finally {
            // The details pane will be re-rendered on fetchJobsAndRender,
            // so no need to manually restore the button state here.
        }
    }
    </script>
</body>
</html>