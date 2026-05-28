<?php
/**
 * Recruiter Applications Page (Coordinator Stage)
 * TNP Portal - IIIT Manipur
 */

session_start();
$current_page = basename($_SERVER['PHP_SELF']);
// These should ideally come from session after successful login
$user_role = $_SESSION['user_role'] ?? 'coordinator'; // Changed default to coordinator
$user_name = $_SESSION['user_name'] ?? 'Coordinator User';
$user_email = $_SESSION['user_email'] ?? '';
$coordinatorId = $_SESSION['user_id'] ?? '123e4567-e89b-12d3-a456-426614174000';

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
    // Adjusted menu for a 'coordinator' perspective managing recruiters
    ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/coordinator/dashboard.php', 'Overview'],
    ['Recruiter Applications', 'fas fa-user-tie', '/tnp/Dashboard/recruiter/post_job.php', 'Manage Recruiter Registrations'], // This page
    ['Student Applications', 'fas fa-user-graduate', '/tnp/Dashboard/coordinator/student_applications.php', 'Manage Student Registrations'],
    ['Job Postings', 'fas fa-briefcase', '/tnp/Dashboard/coordinator/manage_jobs.php', 'Oversee Job Postings'],
    ['Analytics', 'fas fa-chart-line', '/tnp/Dashboard/coordinator/analytics.php', 'Portal Analytics']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruiter Management - TNP Portal</title>
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
                    <div class="w-full md:w-1/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div class="flex items-center mb-4">
                            <h2 class="text-xl font-bold flex-grow">Recruiter Applications</h2>
                            <input type="text" id="searchRecruiters" class="form-input ml-4 border p-2 rounded" placeholder="Search recruiters...">
                            <select id="statusFilter" class="form-input ml-2 border p-2 rounded">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div id="recruiterList" class="flex-1 flex flex-col items-center justify-center text-gray-400 overflow-y-auto overflow-scroll">
                            <i class="fas fa-user-tie fa-3x mb-2"></i>
                            <div>Loading recruiters...</div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="recruiterDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400 overflow-y-auto overflow-scroll">
                            <i class="fas fa-user fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select a Recruiter</div>
                            <div>Choose a recruiter from the list to view their detailed profile</div>
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
    // --- Recruiter Verification Logic ---
    let recruitersData = [];
    let filteredRecruiters = [];
    let selectedRecruiterId = null;

    // Fetch recruiters on page load
    document.addEventListener('DOMContentLoaded', fetchRecruiters);

    document.getElementById('searchRecruiters').addEventListener('input', filterAndRenderRecruiters);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderRecruiters);

    function fetchRecruiters() {
        const recruiterListContainer = document.getElementById('recruiterList');
        recruiterListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading recruiters...</div>';
        recruiterListContainer.classList.add('items-center','justify-center','text-gray-400'); // Ensure centering for loading state

        fetch('../../dataRouting/api/coordinator/GetRecruiterList.php', { credentials: 'include' })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.json();
            })
            .then(data => {
                console.log('API response data:', data); // Debug log
                if (data.status === 'success') {
                    recruitersData = data.recruiters || [];
                    console.log('recruitersData after assignment:', recruitersData); // Debug log
                    filterAndRenderRecruiters();
                } else {
                    console.error('API Error:', data.message);
                    renderRecruiterList([]); // Show empty list or error message
                    recruiterListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load recruiters: ${data.message || 'Unknown error.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                renderRecruiterList([]); // Show empty list or error message
                recruiterListContainer.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error. Please try again.</div>`;
            });
    }

    function filterAndRenderRecruiters() {
        const search = document.getElementById('searchRecruiters').value.trim().toLowerCase();
        const status = document.getElementById('statusFilter').value;
        filteredRecruiters = recruitersData.filter(r => {
            let companyName = '';
            try {
                // Safely parse JSON and access company_name
                const details = JSON.parse(r.CompanyDetailsJson);
                companyName = details.company_name || '';
            } catch (e) {
                console.warn('Failed to parse CompanyDetailsJson for recruiter:', r.recruiterId, e);
            }
            const matchesSearch = !search || companyName.toLowerCase().includes(search);
            const matchesStatus = !status || (r.Status && r.Status.toLowerCase() === status.toLowerCase());
            return matchesSearch && matchesStatus;
        });
        console.log('filteredRecruiters after filter:', filteredRecruiters); // Debug log
        renderRecruiterList(filteredRecruiters);
    }

    function renderRecruiterList(list) {
        const container = document.getElementById('recruiterList');
        container.innerHTML = ''; // Clear previous content

        if (!list.length) {
            container.classList.add('items-center','justify-center','text-gray-400');
            container.innerHTML = `<i class="fas fa-user-tie fa-3x mb-2"></i><div>No recruiters match your criteria.</div>`;
            return;
        }

        // Remove centering if there are items to display
        container.classList.remove('items-center','justify-center','text-gray-400');
        container.innerHTML = ''; // Clear previous content

        list.forEach(r => {
            let companyName = '';
            let city = '';
            let website = '';
            // let primaryContact = ''; // Removed from list part as per previous instruction
            try {
                const details = JSON.parse(r.CompanyDetailsJson);
                companyName = details.company_name || '';
                city = details.city || details.address || '';
                website = details.company_website || '';
                // primaryContact = details.primary_contact_name || 'N/A';
            } catch (e) {
                console.warn('Failed to parse CompanyDetailsJson for rendering list item:', r.recruiterId, e);
                companyName = 'N/A (Invalid Details)';
            }
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(r.Status)}">${r.Status}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center ${selectedRecruiterId===r.recruiterId?'bg-blue-100 border-blue-300':'bg-white'}`;
            item.innerHTML = `
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">${companyName}</div>
                    <div class="text-gray-500 text-sm">${city} ${website?`| <a href='${website}' class='text-blue-600 underline' target='_blank' onclick="event.stopPropagation();">Website</a>`:''}</div>
                </div>
                ${statusBadge}
            `;
            item.onclick = () => {
                selectedRecruiterId = r.recruiterId;
                renderRecruiterList(filteredRecruiters); // Re-render to highlight selection
                fetchRecruiterDetailsAndRender(r.recruiterId); // NEW: Fetch full details on click
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

    // NEW: Function to fetch complete recruiter details
    async function fetchRecruiterDetailsAndRender(recruiterId) {
        const detailsDiv = document.getElementById('recruiterDetails');
        detailsDiv.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading recruiter details...</div>`;
        detailsDiv.classList.add('items-center','justify-center','text-gray-400');

        try {
            const apiUrl = `../../dataRouting/api/coordinator/GetRecruiterDetailsById.php?recruiter_id=${recruiterId}`;
            const response = await fetch(apiUrl, { credentials: 'include' });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.status === 'success' && data.recruiter) {
                renderRecruiterDetails(data.recruiter);
            } else {
                detailsDiv.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load details: ${data.message || 'Recruiter not found.'}</div>`;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            detailsDiv.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error loading details.</div>`;
        }
    }

    // MODIFIED: renderRecruiterDetails to use full recruiter object and add action buttons
    function renderRecruiterDetails(r) {
        const detailsDiv = document.getElementById('recruiterDetails');
        let company = {};
        try {
            company = JSON.parse(r.CompanyDetailsJson) || {};
        } catch(e){
            console.error('Error parsing CompanyDetailsJson for details view:', e);
            company = {
                company_name: 'Invalid Company Data',
                about: 'Company details could not be loaded due to a data error.',
                address: 'N/A',
            };
        }

        detailsDiv.classList.remove('items-center','justify-center','text-gray-400');
        detailsDiv.innerHTML = `
            <div class="mb-4 flex items-center border-b pb-4">
                <i class="fas fa-building fa-2x text-blue-600 mr-3"></i>
                <h2 class="text-2xl font-bold">${company.company_name || 'Company Name Not Provided'}</h2>
                <span class="ml-4 px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(r.Status)}">${r.Status}</span>
                ${r.Status.toLowerCase() !== 'verified' ? `
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Company Information</h3>
                    <p class="mb-1 text-gray-700"><b>Location:</b> ${company.address || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Website:</b> <a href="${company.company_website || '#'}" class="text-blue-600 hover:underline" target="_blank">${company.company_website || 'N/A'}</a></p>
                    <p class="text-gray-700"><b>About:</b> ${company.about || 'No description provided.'}</p>
                    <p class="mb-1 text-gray-700 mt-4"><b>Remark:</b> ${r.remark || 'N/A'}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Primary Contact</h3>
                    <p class="mb-1 text-gray-700"><b>Name:</b> ${r.primaryContactName || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Position:</b> ${r.primaryContactPosition || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Email:</b> <a href="mailto:${r.primaryContactEmail || '#'}" class="text-blue-600 hover:underline">${r.primaryContactEmail || 'N/A'}</a></p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> <a href="tel:${r.primaryContactPhone || '#'}" class="text-blue-600 hover:underline">${r.primaryContactPhone || 'N/A'}</a></p>
                    <p class="mb-1 text-gray-700"><b>LinkedIn:</b> <a href="${r.primaryContactLinkedinProfile || '#'}" class="text-blue-600 hover:underline" target="_blank">${r.primaryContactLinkedinProfile || 'N/A'}</a></p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Alternate Contact</h3>
                    <p class="mb-1 text-gray-700"><b>Name:</b> ${r.altContactName || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Position:</b> ${r.altContactPosition || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Email:</b> <a href="mailto:${r.altContactEmail || '#'}" class="text-blue-600 hover:underline">${r.altContactEmail || 'N/A'}</a></p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> <a href="tel:${r.altContactPhone || '#'}" class="class="text-blue-600 hover:underline">${r.altContactPhone || 'N/A'}</a></p>
                    <p class="mb-1 text-gray-700"><b>LinkedIn:</b> <a href="${r.altContactLinkedinProfile || '#'}" class="text-blue-600 hover:underline" target="_blank">${r.altContactLinkedinProfile || 'N/A'}</a></p>
                </div>

                <div class="col-span-1 md:col-span-2 mt-4 pt-4 border-t">
                    <h3 class="text-lg font-semibold mb-2">Profile Information</h3>
                    <p class="mb-1 text-gray-700"><b>Profile created on:</b> ${r.createdAt || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Last update upon:</b> ${r.updatedAt || 'N/A'}</p>
                </div>
            </div>
        `;
        // Add event listeners after rendering the buttons
        if (r.Status.toLowerCase() !== 'verified') {
            document.getElementById('verifyBtn').onclick = () => handleVerificationAction('verified', r.recruiterId);
            document.getElementById('modifyBtn').onclick = () => openMessageModal('modify', r.recruiterId);
            document.getElementById('rejectBtn').onclick = () => openMessageModal('rejected', r.recruiterId);
        }
    }

    // NEW: Function to handle verification actions
    async function handleVerificationAction(newStatus, recruiterId) {
        // For verify action, we need to open a modal to get the message
        if (newStatus === 'verified') {
            openMessageModal('verified', recruiterId);
            return;
        }
        
        const message = document.getElementById('messageInput').value;
        if (!message) {
            alert('Remark is required for verification.');
            return;
        }

        const confirm = window.confirm(`Are you sure you want to ${newStatus} this recruiter?`);
        if (!confirm) {
            return;
        }

        try {
            const apiUrl = `../../dataRouting/api/coordinator/UpdateRecruiterStatus.php`;
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recruiter_id=${recruiterId}&status=${newStatus}&remark=${message}&coordinator_id='<?php echo $coordinatorId; ?>'`,
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Recruiter ${newStatus} successfully!`);
                // Refresh the list to show updated status
                fetchRecruiters();
            } else {
                alert(`Failed to ${newStatus} recruiter: ${data.message}`);
            }
        } catch (error) {
            console.error('Verification Error:', error);
            alert('Network or server error during verification.');
        }
    }

    // NEW: Function to open message modal
    function openMessageModal(action, recruiterId) {
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
            modalTitle.textContent = 'Verify Recruiter Application';
            messageInput.placeholder = 'Enter verification notes...';
            messageInput.value = ''; // Clear previous message
            submitBtn.textContent = 'Verify';
            submitBtn.onclick = () => handleMessageAction(action, recruiterId);
            cancelBtn.onclick = () => closeMessageModal();
        } else if (action === 'modify') {
            modalTitle.textContent = 'Modify Recruiter Details';
            messageInput.placeholder = 'Enter your modifications here...';
            messageInput.value = ''; // Clear previous message
            submitBtn.textContent = 'Save Changes';
            submitBtn.onclick = () => handleMessageAction(action, recruiterId);
            cancelBtn.onclick = () => closeMessageModal();
        } else if (action === 'rejected') {
            modalTitle.textContent = 'Reject Recruiter Application';
            messageInput.placeholder = 'Enter the reason for rejection...';
            messageInput.value = ''; // Clear previous message
            submitBtn.textContent = 'Reject';
            submitBtn.onclick = () => handleMessageAction(action, recruiterId);
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
    async function handleMessageAction(action, recruiterId) {
        const message = document.getElementById('messageInput').value.trim();
        if (!message) {
            alert('Message is required for this action.');
            return;
        }

        const confirm = window.confirm(`Are you sure you want to ${action} this recruiter?`);
        if (!confirm) {
            return;
        }

        try {
            // The API endpoint for updating recruiter status (e.g., to 'verified', 'modified', 'rejected')
            // You need to create this API endpoint (e.g., UpdateRecruiterStatus.php) that handles POST requests.
            // It should accept recruiter_id, status (e.g., 'verified', 'modified', 'rejected'), and a remark.
            const apiUrl = `../../dataRouting/api/coordinator/UpdateRecruiterStatus.php`; // You'll need to implement this endpoint
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                // Pass current coordinator ID and remark
                body: `recruiter_id=${recruiterId}&status=${action === 'modify' ? 'resubmit' : action}&remark=${encodeURIComponent(message)}&coordinator_id=<?php echo $coordinatorId; ?>`,
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Recruiter ${action} successfully!`);
                fetchRecruiters(); // Refresh the list
            } else {
                alert(`Failed to ${action} recruiter: ${data.message || 'Unknown error.'}`);
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