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

$menu_items = [
    ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/recruiter/recruiter_dashboard.php', 'Overview'],
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
    <title>Manage Jobs - TNP Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="formstyle.css" rel="stylesheet">
    <style>
        #jobList {
            overflow-y: auto;
            min-height: 200px;
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
                        <div class="flex justify-end items-center mb-6 gap-2">
                            <button id="cancelBtn" type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 font-semibold px-4 py-2 rounded transition-colors duration-200">Cancel</button>
                            <button id="saveDraftBtn" type="submit" class="bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold px-5 py-2 rounded opacity-50 cursor-not-allowed transition-colors duration-300" disabled><i class="fa-solid fa-floppy-disk mr-2"></i> Draft</button>
                            <button id="postVerificationBtn" type="button" class="bg-blue-600 text-white font-semibold px-5 py-2 rounded opacity-50 cursor-not-allowed transition-colors duration-300" disabled><i class="fa-solid fa-paper-plane mr-2"></i> Verification</button>
                        </div>
                        <h2 class="text-xl font-bold mb-6">Create New Opportunity</h2>
                        <form id="createJobForm" class="space-y-6" autocomplete="off">
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
                                <label class="block mb-1 text-sm text-gray-700 font-medium">Detailed Job Description File(s)</label>
                                <div class="flex items-center gap-3">
                                    <label for="jobDocuments" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded cursor-pointer hover:bg-blue-700 transition-colors duration-200">
                                        <i class="fa-solid fa-upload mr-2"></i>
                                        <input id="jobDocuments" name="jobDocuments" type="file" multiple required class="hidden" />
                                    </label>
                                    <span id="fileNames" class="text-sm text-gray-600">No files selected.</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="editJobModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-8 relative">
                        <button id="closeEditModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">×</button>
                        <div class="flex justify-end items-center mb-6 gap-2">
                            <button id="cancelEditBtn" type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 font-semibold px-4 py-2 rounded transition-colors duration-200">Cancel</button>
                            <button id="saveEditBtn" type="submit" class="bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold px-5 py-2 rounded opacity-50 cursor-not-allowed transition-colors duration-300" disabled><i class="fa-solid fa-floppy-disk mr-2"></i> Save Changes</button>
                            <button id="postEditVerificationBtn" type="button" class="bg-blue-600 text-white font-semibold px-5 py-2 rounded opacity-50 cursor-not-allowed transition-colors duration-300" disabled><i class="fa-solid fa-paper-plane mr-2"></i> Verification</button>
                        </div>
                        <h2 class="text-xl font-bold mb-6">Edit Job Opportunity</h2>
                        <form id="editJobForm" class="space-y-6" autocomplete="off">
                            <div class="mb-6">
                                <label for="editJobTitle" class="block mb-1 text-sm text-gray-700 font-medium">Job Title (e.g. Software Engineer, Researcher)</label>
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
                                <label for="editJobLocation" class="block mb-1 text-sm text-gray-700 font-medium">Location (e.g. Remote, Hybrid, Delhi)</label>
                                <input type="text" id="editJobLocation" name="editJobLocation" required class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300" />
                            </div>
                            <div class="mb-6">
                                <label for="editJobDescription" class="block mb-1 text-sm text-gray-700 font-medium">Description</label>
                                <textarea id="editJobDescription" name="editJobDescription" required rows="3" class="block w-full text-sm text-gray-900 bg-white border-0 border-b-2 border-green-500 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors duration-300"></textarea>
                            </div>
                            <div class="mb-6">
                                <label class="block mb-1 text-sm text-gray-700 font-medium">Detailed Job Description File(s)</label>
                                <div class="flex items-center gap-3">
                                    <label for="editJobDocuments" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded cursor-pointer hover:bg-blue-700 transition-colors duration-200">
                                        <i class="fa-solid fa-upload mr-2"></i>
                                        <input id="editJobDocuments" name="editJobDocuments" type="file" multiple class="hidden" />
                                    </label>
                                    <span id="editFileNames" class="text-sm text-gray-600">No files selected.</span>
                                </div>
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
    <script>
    let jobsData = [];
    let filteredJobs = [];
    let selectedJobId = null;
    let currentFullJobDetails = null; // NEW: To store the full job details fetched via fetchJobDetailsAndRender

    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM loaded, fetching jobs');
        fetchJobsAndRender();
    });
    document.getElementById('searchJobs').addEventListener('input', filterAndRenderJobs);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderJobs);

    const recruiterId = "<?php echo $recruiterId; ?>";

    async function fetchJobsAndRender() {
        if (!recruiterId) {
            document.getElementById('jobList').innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 self-center"></i><div>Recruiter ID not found. Cannot fetch jobs.</div>`;
            return;
        }

        const jobListContainer = document.getElementById('jobList');
        jobListContainer.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x mb-2 self-center"></i><div class="self-center text-gray-400">Loading jobs...</div>`;

        try {
            const apiUrl = `../../dataRouting/api/recruiter/getRecruiterJobsList.php?recruiter_id=${recruiterId}`;
            console.log('Fetching jobs from:', apiUrl); // Debug: Log the URL
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            console.log('Response status:', response.status); // Debug: Log status
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status} (${response.statusText})`);
            }
            let data;
            try {
                data = await response.json();
            } catch (jsonError) {
                throw new Error(`Invalid JSON response: ${jsonError.message}`);
            }
            console.log('API Data (getRecruiterJobsList):', data); // Debug: Log response data

            if (data.status === 'success' && Array.isArray(data.jobs)) {
                // Initial mapping for list view (doesn't include description/documents yet)
                jobsData = data.jobs.map(job => ({
                    id: job.EventID || 'N/A',
                    title: job.Event || 'Untitled',
                    type: job.Type || 'N/A', // Assuming Type is available in getRecruiterJobsList.php, if not you'll need to fetch it later
                    status: job.Status || 'N/A', // Assuming Status is available or derived
                    location: job.Location || 'N/A', // Assuming Location is available
                    posted: job.Posted ? new Date(job.Posted).toLocaleDateString('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' }) : 'N/A'
                }));
                console.log('Mapped jobsData:', jobsData); // Debug: Log mapped data
                filterAndRenderJobs();
            } else {
                const errorMessage = data.message || 'Failed to load jobs: Invalid API response';
                jobListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 self-center"></i><div>${errorMessage}</div>`;
            }
        } catch (error) {
            console.error('Error fetching jobs:', error.message, error.stack); // Enhanced error logging
            jobListContainer.innerHTML = `<i class="fas fa-times-circle fa-3x mb-2 self-center"></i><div>Error loading jobs: ${error.message || 'Unknown error'}. Please check the API endpoint or try again later.</div>`;
        }
    }

    function filterAndRenderJobs() {
        const search = document.getElementById('searchJobs').value.trim().toLowerCase();
        const status = document.getElementById('statusFilter').value;
        console.log('Filter - Search:', search, 'Status:', status); // Debug

        filteredJobs = jobsData.filter(j => {
            const matchesSearch = !search || 
                (j.title && j.title.toLowerCase().includes(search)) || 
                (j.location && j.location.toLowerCase().includes(search));
            const matchesStatus = !status || (j.status && j.status.toLowerCase() === status.toLowerCase());
            return matchesSearch && matchesStatus;
        });
        console.log('Filtered Jobs:', filteredJobs); // Debug
        renderJobList(filteredJobs);
    }

    function renderJobList(list) {
        const container = document.getElementById('jobList');
        console.log('Rendering job list:', list); // Debug
        if (!list || !list.length) {
            container.innerHTML = `<i class="fas fa-briefcase fa-3x mb-2 self-center"></i><div class="self-center text-gray-400">No jobs match your criteria.</div>`;
            container.classList.add('items-center', 'justify-center', 'text-gray-400');
            return;
        }
        container.classList.remove('items-center', 'justify-center', 'text-gray-400');
        container.innerHTML = '';
        list.forEach(j => {
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(j.status)}">${j.status}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center ${selectedJobId === j.id ? 'bg-blue-100' : ''}`;
            item.innerHTML = `<div class="flex-1"><div class="font-semibold text-gray-800">${j.title}</div><div class="text-gray-500 text-xs">${j.location} | Posted: ${j.posted}</div></div>${statusBadge}`;
            item.onclick = () => {
                selectedJobId = j.id;
                // window.currentJob = j; // Removed this as currentFullJobDetails will hold the complete data
                renderJobList(filteredJobs); // Re-render list to highlight selected item
                fetchJobDetailsAndRender(j.id); // NEW: Fetch full details when a job is clicked
            };
            container.appendChild(item);
        });
    }

    function getStatusColor(status) {
        switch ((status || '').toLowerCase()) {
            case 'open': return 'bg-green-100 text-green-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'closed': return 'bg-red-100 text-red-800';
            case 'verified': return 'bg-green-100 text-green-800';
            case 'rejected': return 'bg-red-100 text-red-800';
            case 'draft': return 'bg-gray-200 text-gray-600';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    // NEW: Function to fetch complete job details
    async function fetchJobDetailsAndRender(jobId) {
        const jobDetailsContainer = document.getElementById('jobDetails');
        jobDetailsContainer.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x mb-2 self-center"></i><div class="self-center text-gray-400">Loading job details...</div>`;
        jobDetailsContainer.classList.add('items-center', 'justify-center', 'text-gray-400');

        try {
            // This API call is for detailed view and edit form
            const apiUrl = `../../dataRouting/api/recruiter/getJobDetailsById.php?event_id=${jobId}`;
            console.log('Fetching single job details from:', apiUrl); // Debug
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status} (${response.statusText})`);
            }
            let data = await response.json();
            console.log('Single Job API Data (getJobDetailsById):', data);

            // Corrected line: Check for data.job array and its length
            // The API response for getJobDetailsById.php provides the job in a 'job' array, like getRecruiterJobsList.php
            if (data.status === 'success' && data.job && Array.isArray(data.job) && data.job.length > 0) {
                const job = data.job[0]; // Access the first element of the job array
                currentFullJobDetails = { // Store the fully detailed job object
                    id: job.EventID || 'N/A',
                    title: job.Event || 'Untitled',
                    type: job.Type || 'N/A',
                    status: job.Status || 'N/A',
                    location: job.Location || 'N/A',
                    posted: job.Posted ? new Date(job.Posted).toLocaleDateString('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' }) : 'N/A',
                    description: job.Description || 'No description provided.',
                    // *** CRITICAL FIX: Corrected property name from 'AttachedDocuments' to 'AttachedDocumens' ***
                    attachedDocuments: job.AttachedDocumens ? JSON.parse(job.AttachedDocumens) : null
                };
                renderJobDetails(currentFullJobDetails); // Render with full details
            } else {
                const errorMessage = data.message || 'Failed to load job details: Invalid API response or no job found.';
                jobDetailsContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 self-center"></i><div>${errorMessage}</div>`;
            }
        } catch (error) {
            console.error('Error fetching single job details:', error.message, error.stack);
            jobDetailsContainer.innerHTML = `<i class="fas fa-times-circle fa-3x mb-2 self-center"></i><div>Error loading job details: ${error.message || 'Unknown error'}. Please check the API endpoint or try again later.</div>`;
        }
    }


    // MODIFIED: renderJobDetails to correctly display description and attached documents
    function renderJobDetails(j) {
        const detailsDiv = document.getElementById('jobDetails');
        detailsDiv.classList.remove('items-center', 'justify-center', 'text-gray-400');

        // Define the base URL for your documents. Adjust this to your actual document storage path.
        const documentBaseUrl = '/dataRouting/documents/uploads/recruiters/'; // Corrected path for recruiter job documents

        let attachedDocumentsHtml = '';
        // Ensure attachedDocuments is an array, even if it's null or not an array initially
        const documents = Array.isArray(j.attachedDocuments) ? j.attachedDocuments : [];

        if (documents.length > 0) { 
            const documentLinks = documents.map(docLink => {
                const fileName = docLink.substring(docLink.lastIndexOf('/') + 1); // Extract filename from URL
                return `<a href="${docLink}" target="_blank" class="text-blue-600 hover:underline">${fileName}</a>`;
            });
            attachedDocumentsHtml = `<div class="mb-2 text-gray-700"><b>Attached Documents:</b> ${documentLinks.join(', ')}</div>`;
        } else {
            attachedDocumentsHtml = `<div class="mb-2 text-gray-700"><b>Attached Documents:</b> No documents attached.</div>`;
        }

        detailsDiv.innerHTML = `
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-briefcase fa-2x text-blue-600 mr-3"></i>
                    <h2 class="text-2xl font-bold mr-3">${j.title}</h2>
                    <span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(j.status)}">${j.status}</span>
                </div>
                <button id="editJobBtn" class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded flex items-center text-sm font-semibold transition-colors duration-200">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Edit
                </button>
            </div>
            <div class="mb-2 text-gray-700"><b>Job Type:</b> ${j.type}</div>
            <div class="mb-2 text-gray-700"><b>Location:</b> ${j.location}</div>
            <div class="mb-2 text-gray-700"><b>Posted:</b> ${j.posted}</div>
            <div class="mb-2 text-gray-700"><b>Description:</b> ${j.description}</div>
            ${attachedDocumentsHtml}
        `;
    }

    const createOpportunityBtn = document.getElementById('createOpportunityBtn');
    const createJobModal = document.getElementById('createJobModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const createJobForm = document.getElementById('createJobForm');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const jobType = document.getElementById('jobType');
    const typeOtherContainer = document.getElementById('typeOtherContainer');
    const typeOther = document.getElementById('typeOther');
    const cancelBtn = document.getElementById('cancelBtn');
    const postVerificationBtn = document.getElementById('postVerificationBtn');

    createOpportunityBtn.addEventListener('click', () => {
        createJobModal.classList.remove('hidden');
    });
    closeModalBtn.addEventListener('click', () => {
        createJobModal.classList.add('hidden');
        createJobForm.reset();
        typeOtherContainer.style.display = 'none';
        saveDraftBtn.disabled = true;
        saveDraftBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postVerificationBtn.disabled = true;
        postVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('fileNames').textContent = 'No files selected.';
    });

    jobType.addEventListener('change', function() {
        if (this.value === 'Other') {
            typeOtherContainer.style.display = '';
            typeOther.required = true;
        } else {
            typeOtherContainer.style.display = 'none';
            typeOther.required = false;
            typeOther.value = '';
        }
        validateJobForm();
    });

    createJobForm.addEventListener('input', validateJobForm);

    function validateJobForm() {
        const title = document.getElementById('jobTitle').value.trim();
        const type = jobType.value;
        const location = document.getElementById('jobLocation').value.trim();
        const description = document.getElementById('jobDescription').value.trim();
        const typeOtherVal = typeOtherContainer.style.display !== 'none' ? typeOther.value.trim() : 'ok';
        const files = document.getElementById('jobDocuments').files;
        const allFilled = title && type && location && description && typeOtherVal && files && files.length > 0;
        saveDraftBtn.disabled = !allFilled;
        saveDraftBtn.classList.toggle('opacity-50', !allFilled);
        saveDraftBtn.classList.toggle('cursor-not-allowed', !allFilled);
        postVerificationBtn.disabled = !allFilled;
        postVerificationBtn.classList.toggle('opacity-50', !allFilled);
        postVerificationBtn.classList.toggle('cursor-not-allowed', !allFilled);
    }

    document.getElementById('jobDocuments').addEventListener('change', function() {
        const fileNames = Array.from(this.files).map(f => f.name).join(', ') || 'No files selected.';
        document.getElementById('fileNames').textContent = fileNames;
        validateJobForm();
    });

    createJobForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log("Job creation form submitted.");
        createJobModal.classList.add('hidden');
        createJobForm.reset();
        typeOtherContainer.style.display = 'none';
        saveDraftBtn.disabled = true;
        saveDraftBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postVerificationBtn.disabled = true;
        postVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('fileNames').textContent = 'No files selected.';
        fetchJobsAndRender();
    });

    cancelBtn.addEventListener('click', () => {
        createJobModal.classList.add('hidden');
        createJobForm.reset();
        typeOtherContainer.style.display = 'none';
        saveDraftBtn.disabled = true;
        saveDraftBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postVerificationBtn.disabled = true;
        postVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('fileNames').textContent = 'No files selected.';
    });

    const editJobModal = document.getElementById('editJobModal');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveEditBtn = document.getElementById('saveEditBtn');
    const postEditVerificationBtn = document.getElementById('postEditVerificationBtn');
    const editJobForm = document.getElementById('editJobForm');
    const editJobTitle = document.getElementById('editJobTitle');
    const editJobType = document.getElementById('editJobType');
    const editTypeOtherContainer = document.getElementById('editTypeOtherContainer');
    const editTypeOther = document.getElementById('editTypeOther');
    const editJobLocation = document.getElementById('editJobLocation');
    const editJobDescription = document.getElementById('editJobDescription');
    const editJobDocuments = document.getElementById('editJobDocuments');
    const editFileNames = document.getElementById('editFileNames');

    // MODIFIED: openEditModal to use currentFullJobDetails and handle documents
    function openEditModal(job) {
        // Use currentFullJobDetails which contains the complete job data
        const jobToEdit = currentFullJobDetails; 

        if (!jobToEdit) {
            console.error('No full job details available for editing.');
            return;
        }

        editJobTitle.value = jobToEdit.title || '';
        editJobType.value = jobToEdit.type || '';
        editJobLocation.value = jobToEdit.location || '';
        editJobDescription.value = jobToEdit.description || '';
        
        if (jobToEdit.type === 'Other') {
            editTypeOtherContainer.style.display = '';
            editTypeOther.required = true;
            editTypeOther.value = jobToEdit.typeOther || ''; // Populate if typeOther is available
        } else {
            editTypeOtherContainer.style.display = 'none';
            editTypeOther.required = false;
            editTypeOther.value = '';
        }
        
        editJobDocuments.value = ''; // Clear previous file selection in input
        // Ensure attachedDocuments is an array, even if it's null or not an array initially
        const editDocuments = Array.isArray(jobToEdit.attachedDocuments) ? jobToEdit.attachedDocuments : [];

        if (editDocuments.length > 0) {
            // Extract filenames from the URLs for display in the edit modal
            const fileNamesArray = editDocuments.map(docLink => docLink.substring(docLink.lastIndexOf('/') + 1));
            editFileNames.textContent = fileNamesArray.join(', ');
        } else {
            editFileNames.textContent = 'No files selected.';
        }

        saveEditBtn.disabled = true;
        saveEditBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postEditVerificationBtn.disabled = true;
        postEditVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        validateEditJobForm();
        editJobModal.classList.remove('hidden');
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'editJobBtn') {
            // Pass currentFullJobDetails to openEditModal
            if (currentFullJobDetails) {
                openEditModal(currentFullJobDetails); 
            } else {
                console.warn('No full job details available for editing. Please select a job first.');
                // Optionally show a message to the user
            }
        }
    });

    closeEditModalBtn.addEventListener('click', () => {
        editJobModal.classList.add('hidden');
        editJobForm.reset();
        editTypeOtherContainer.style.display = 'none';
        saveEditBtn.disabled = true;
        saveEditBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postEditVerificationBtn.disabled = true;
        postEditVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        editFileNames.textContent = 'No files selected.';
    });

    cancelEditBtn.addEventListener('click', () => {
        editJobModal.classList.add('hidden');
        editJobForm.reset();
        editTypeOtherContainer.style.display = 'none';
        saveEditBtn.disabled = true;
        saveEditBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postEditVerificationBtn.disabled = true;
        postEditVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        editFileNames.textContent = 'No files selected.';
    });

    editJobType.addEventListener('change', function() {
        if (this.value === 'Other') {
            editTypeOtherContainer.style.display = '';
            editTypeOther.required = true;
        } else {
            editTypeOtherContainer.style.display = 'none';
            editTypeOther.required = false;
            editTypeOther.value = '';
        }
        validateEditJobForm();
    });

    editJobForm.addEventListener('input', validateEditJobForm);
    editJobDocuments.addEventListener('change', function() {
        const fileNames = Array.from(this.files).map(f => f.name).join(', ') || 'No files selected.';
        editFileNames.textContent = fileNames;
        validateEditJobForm();
    });

    function validateEditJobForm() {
        const title = editJobTitle.value.trim();
        const type = editJobType.value;
        const location = editJobLocation.value.trim();
        const description = editJobDescription.value.trim();
        const typeOtherVal = editTypeOtherContainer.style.display !== 'none' ? editTypeOther.value.trim() : 'ok';
        // const files = editJobDocuments.files; // File input not required for validation on edit if existing files are there
        const allFilled = title && type && location && description && typeOtherVal;
        saveEditBtn.disabled = !allFilled;
        saveEditBtn.classList.toggle('opacity-50', !allFilled);
        saveEditBtn.classList.toggle('cursor-not-allowed', !allFilled);
        postEditVerificationBtn.disabled = !allFilled;
        postEditVerificationBtn.classList.toggle('opacity-50', !allFilled);
        postEditVerificationBtn.classList.toggle('cursor-not-allowed', !allFilled);
    }

    editJobForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log("Job edit form submitted.");
        editJobModal.classList.add('hidden');
        editJobForm.reset();
        editTypeOtherContainer.style.display = 'none';
        saveEditBtn.disabled = true;
        saveEditBtn.classList.add('opacity-50', 'cursor-not-allowed');
        postEditVerificationBtn.disabled = true;
        postEditVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
        editFileNames.textContent = 'No files selected.';
        fetchJobsAndRender();
    });
    </script>
</body>
</html>