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


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Applications - TNP Portal</title>
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
                            <h2 class="text-xl font-bold flex-grow">Participant Applications</h2>
                            <input type="text" id="searchApplications" class="form-input ml-4 border p-2 rounded" placeholder="Search applications...">
                            <select id="statusFilter" class="form-input ml-2 border p-2 rounded">
                                <option value="">All Status</option>
                                <option value="registered">Registered</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="shortlisted">Shortlisted</option>
                                <option value="selected">Selected</option>
                                <option value="attended">Attended</option>
                                <option value="blocked">Blocked</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="waitlisted">Waitlisted</option>
                            </select>
                        </div>
                        <div id="applicationList" class="flex-1 flex flex-col items-center justify-center text-gray-400 overflow-y-auto">
                            <i class="fas fa-file-alt fa-3x mb-2"></i>
                            <div>Loading applications...</div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="applicationDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400 overflow-y-auto">
                            <i class="fas fa-info-circle fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select an Application</div>
                            <div>Choose an application from the list to view its details</div>
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
    // --- Participant Application Logic ---
    let allApplications = []; // Stores all fetched applications
    let filteredApplications = []; // Stores applications after filtering
    let selectedParticipantEntryId = null; // Stores the ID of the currently selected application

    // Fetch applications on page load
    document.addEventListener('DOMContentLoaded', fetchApplications);

    document.getElementById('searchApplications').addEventListener('input', filterAndRenderApplications);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderApplications);

    async function fetchApplications() {
        const applicationListContainer = document.getElementById('applicationList');
        applicationListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading applications...</div>';
        applicationListContainer.classList.add('items-center','justify-center','text-gray-400');

        try {
            // Updated API endpoint to fetch participant applications
            const response = await fetch('../../dataRouting/api/admin/GetParticipantApplications.php', { credentials: 'include' }); //
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.status === 'success' && data.Participant) { //
                allApplications = data.Participant; //
                filterAndRenderApplications();
            } else {
                console.error('API Error:', data.message);
                renderApplicationList([]); // Show empty list or error message
                applicationListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load applications: ${data.message || 'Unknown error.'}</div>`;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            renderApplicationList([]); // Show empty list or error message
            applicationListContainer.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error. Please try again.</div>`;
        }
    }

    function filterAndRenderApplications() {
        const search = document.getElementById('searchApplications').value.trim().toLowerCase();
        const status = document.getElementById('statusFilter').value;

        filteredApplications = allApplications.filter(app => {
            const studentName = (app.studentName || '').toLowerCase(); //
            const rollNo = (app.studentRollNo || '').toLowerCase(); //
            const eventTitle = (app.eventTitle || '').toLowerCase(); //
            const participationStatus = (app.participationStatus || '').toLowerCase(); //

            const matchesSearch = !search || studentName.includes(search) || rollNo.includes(search) || eventTitle.includes(search);
            const matchesStatus = !status || participationStatus === status.toLowerCase();
            return matchesSearch && matchesStatus;
        });
        renderApplicationList(filteredApplications);
    }

    function renderApplicationList(list) {
        const container = document.getElementById('applicationList');
        container.innerHTML = ''; // Clear previous content

        if (!list.length) {
            container.classList.add('items-center','justify-center','text-gray-400');
            container.innerHTML = `<i class="fas fa-file-alt fa-3x mb-2"></i><div>No applications match your criteria.</div>`;
            return;
        }

        container.classList.remove('items-center','justify-center','text-gray-400');

        list.forEach(app => {
            const name = app.studentName || 'N/A';
            const roll = app.studentRollNo || 'N/A';
            const registrationDate = app.registrationDatetime ? new Date(app.registrationDatetime).toLocaleDateString() : 'N/A'; // Format to date only
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(app.participationStatus)}">${app.participationStatus||'N/A'}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex flex-col ${selectedParticipantEntryId === app.participantEntryId ? 'bg-blue-100 border-blue-300' : 'bg-white'}`;
            item.innerHTML = `
                <div class="flex justify-between items-start w-full">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">${name} (${roll})</div>
                        <div class="text-gray-600 text-sm">Applied: ${registrationDate}</div>
                    </div>
                    ${statusBadge}
                </div>
            `;
            item.onclick = () => {
                selectedParticipantEntryId = app.participantEntryId;
                renderApplicationList(filteredApplications); // Re-render to highlight selection
                const fullApplicationDetails = allApplications.find(a => a.participantEntryId === app.participantEntryId);
                if (fullApplicationDetails) {
                    renderApplicationDetails(fullApplicationDetails);
                } else {
                    document.getElementById('applicationDetails').innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Details not found.</div>`;
                }
            };
            container.appendChild(item);
        });
    }

    function getStatusColor(status) {
        switch((status||'').toLowerCase()) {
            case 'registered': return 'bg-blue-100 text-blue-800';
            case 'approved': return 'bg-green-100 text-green-800';
            case 'rejected': return 'bg-red-100 text-red-800';
            case 'shortlisted': return 'bg-purple-100 text-purple-800';
            case 'selected': return 'bg-indigo-100 text-indigo-800';
            case 'attended': return 'bg-teal-100 text-teal-800';
            case 'blocked': return 'bg-gray-100 text-gray-800';
            case 'cancelled': return 'bg-orange-100 text-orange-800';
            case 'waitlisted': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    // Function to render complete application details
    function renderApplicationDetails(app) { //
        const detailsDiv = document.getElementById('applicationDetails');
        const na = '<span class="text-gray-400">Not provided</span>';
        const createLink = (url, text) => url ? `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${text}</a>` : na;

        detailsDiv.classList.remove('items-center','justify-center','text-gray-400');
        detailsDiv.innerHTML = `
            <div class="mb-4 flex flex-col items-start border-b pb-4 w-full">
                <h2 class="text-2xl font-bold mb-1">${app.eventTitle || na}</h2>
                <p class="text-gray-600 text-lg">Applicant: ${app.studentName || na} (${app.studentRollNo || na})</p>
                <span class="px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(app.participationStatus)}">${app.participationStatus || 'N/A'}</span>
                <p class="text-gray-500 text-sm mt-2">Applied on: ${app.registrationDatetime ? new Date(app.registrationDatetime).toLocaleString() : 'N/A'}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full overflow-y-auto" style="max-height: calc(100vh - 250px);">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Event Details</h3>
                    <p class="mb-1 text-gray-700"><b>Type:</b> ${app.eventType || na}</p>
                    <p class="mb-1 text-gray-700"><b>Location:</b> ${app.eventLocation || na}</p>
                    <p class="mb-1 text-gray-700"><b>Start Date:</b> ${app.eventStartDate ? new Date(app.eventStartDate).toLocaleString() : na}</p>
                    <p class="mb-1 text-gray-700"><b>End Date:</b> ${app.eventEndDate ? new Date(app.eventEndDate).toLocaleString() : na}</p>
                    <p class="mb-1 text-gray-700"><b>Event Status:</b> ${app.eventStatus || na}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Applicant Information</h3>
                    <p class="mb-1 text-gray-700"><b>Program:</b> ${app.studentProgram || na}</p>
                    <p class="mb-1 text-gray-700"><b>Department:</b> ${app.studentDepartment || na}</p>
                    <p class="mb-1 text-gray-700"><b>CPI:</b> ${app.studentCPI || na}</p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> ${app.studentPhoneNumber || na}</p>
                    <p class="mb-1 text-gray-700"><b>Application Message:</b> ${app.applicationMessage || na}</p>
                    <p class="mb-1 text-gray-700"><b>Submitted Document:</b> ${createLink(app.applicationDocumentLink, 'View Document')}</p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-end space-x-3 w-full">
                ${app.participationStatus.toLowerCase() !== 'approved' && app.participationStatus.toLowerCase() !== 'rejected' ? `
                <button id="approveBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow">
                    <i class="fas fa-check-circle mr-2"></i> Approve
                </button>
                <button id="rejectBtn" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded shadow">
                    <i class="fas fa-times-circle mr-2"></i> Reject
                </button>
                <button id="shortlistBtn" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-4 py-2 rounded shadow">
                    <i class="fas fa-star mr-2"></i> Shortlist
                </button>
                ` : ''}
                ${app.participationStatus.toLowerCase() === 'registered' ? `
                <button id="waitlistBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold px-4 py-2 rounded shadow">
                    <i class="fas fa-clock mr-2"></i> Waitlist
                </button>
                ` : ''}
                </div>
        `;
        // Add event listeners for the action buttons
        if (app.participationStatus.toLowerCase() !== 'approved' && app.participationStatus.toLowerCase() !== 'rejected') {
            document.getElementById('approveBtn')?.addEventListener('click', () => handleApplicationAction('approved', app.participantEntryId)); //
            document.getElementById('rejectBtn')?.addEventListener('click', () => openMessageModal('rejected', app.participantEntryId)); //
            document.getElementById('shortlistBtn')?.addEventListener('click', () => handleApplicationAction('shortlisted', app.participantEntryId)); //
        }
        if (app.participationStatus.toLowerCase() === 'registered') {
            document.getElementById('waitlistBtn')?.addEventListener('click', () => handleApplicationAction('waitlisted', app.participantEntryId)); //
        }
    }

    // Function to handle application status updates (Approve, Shortlist, Waitlist)
    async function handleApplicationAction(newStatus, participantEntryId) {
        const confirmAction = window.confirm(`Are you sure you want to change the status to '${newStatus}' for this application?`);
        if (!confirmAction) {
            return;
        }

        try {
            // Assuming this endpoint handles participant application status updates
            // You might need to create/modify `UpdateParticipantStatus.php` on your backend.
            const apiUrl = `../../dataRouting/api/admin/UpdateParticipantStatus.php`; // This endpoint needs to be implemented
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `participant_entry_id=${participantEntryId}&status=${newStatus}&coordinator_id=<?php echo $coordinatorId; ?>`, //
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Application status updated to '${newStatus}' successfully!`);
                fetchApplications(); // Refresh the list
                document.getElementById('applicationDetails').innerHTML = `<i class="fas fa-info-circle fa-3x mb-2"></i><div class="text-lg font-semibold mb-2">Select an Application</div><div>Choose an application from the list to view its details</div>`; // Clear details
            } else {
                alert(`Failed to update application status: ${data.message || 'Unknown error.'}`);
            }
        } catch (error) {
            console.error('API Error:', error);
            alert('Network or server error during status update.');
        }
    }

    // Function to open message modal for Reject action
    function openMessageModal(action, participantEntryId) {
        const modalTitle = document.getElementById('messageModalTitle');
        const messageInput = document.getElementById('messageInput');
        const submitBtn = document.getElementById('submitMessageBtn');
        const cancelBtn = document.getElementById('cancelMessageBtn');

        messageInput.oninput = () => {
            submitBtn.disabled = messageInput.value.trim() === '';
            submitBtn.classList.toggle('opacity-50', submitBtn.disabled);
            submitBtn.classList.toggle('cursor-not-allowed', submitBtn.disabled);
        };

        if (action === 'rejected') {
            modalTitle.textContent = 'Reject Application';
            messageInput.placeholder = 'Enter the reason for rejection...';
            messageInput.value = '';
            submitBtn.textContent = 'Reject';
            submitBtn.onclick = () => handleMessageAction(action, participantEntryId);
            cancelBtn.onclick = () => closeMessageModal();
        }
        messageInput.oninput();
        document.getElementById('messageModal').classList.remove('hidden');
    }

    // Function to close message modal
    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.getElementById('messageInput').value = '';
        const submitBtn = document.getElementById('submitMessageBtn');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    // Function to handle message actions (Reject) - uses the same modal for message input
    async function handleMessageAction(action, participantEntryId) {
        const message = document.getElementById('messageInput').value.trim();
        if (!message) {
            alert('A message/reason is required for this action.');
            return;
        }

        const confirmAction = window.confirm(`Are you sure you want to ${action} this application?`);
        if (!confirmAction) {
            return;
        }

        try {
            // This endpoint would also handle the status update based on the message
            const apiUrl = `../../dataRouting/api/admin/UpdateParticipantStatus.php`; // This endpoint needs to be implemented
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `participant_entry_id=${participantEntryId}&status=${action}&remark=${encodeURIComponent(message)}&coordinator_id=<?php echo $coordinatorId; ?>`, //
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Application ${action} successfully!`);
                fetchApplications(); // Refresh the list
                document.getElementById('applicationDetails').innerHTML = `<i class="fas fa-info-circle fa-3x mb-2"></i><div class="text-lg font-semibold mb-2">Select an Application</div><div>Choose an application from the list to view its details</div>`; // Clear details
            } else {
                alert(`Failed to ${action} application: ${data.message || 'Unknown error.'}`);
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