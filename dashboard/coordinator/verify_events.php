<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'coordinator';
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$coordinatorId = $_SESSION['user_id'] ?? '';

// Handle session messages (for redirects)
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
unset($_SESSION['message'], $_SESSION['messageType']);

$menu_items = [
    ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/coordinator/coordinator_dashboard.php', 'Overview'],
    ['Verify Recruiters', 'fas fa-user-check', '/tnp/Dashboard/coordinator/verify_recruiters.php', 'Recruiter Verification'],
    ['Verify Students', 'fas fa-user-graduate', '/tnp/Dashboard/coordinator/verify_students.php', 'Student Verification'],
    ['Verify Events', 'fas fa-calendar-check', '/tnp/Dashboard/coordinator/verify_events.php', 'Event Verification'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Verification - TNP Portal</title>
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
                <div class="flex flex-1 gap-6 min-h-0">
                    <!-- Event Applications List (1/3) -->
                    <div class="w-full md:w-1/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div class="flex items-center mb-4">
                            <h2 class="text-xl font-bold flex-grow">Event Applications</h2>
                            <input type="text" id="searchEvents" class="form-input ml-4" placeholder="Search events...">
                            <select id="statusFilter" class="form-input ml-2">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div id="eventList" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                            <div>No events match your criteria.</div>
                        </div>
                    </div>
                    <!-- Event Details (2/3) -->
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="eventDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-calendar fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select an Event</div>
                            <div>Choose an event from the list to view its detailed info</div>
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
    <script>
    // Placeholder logic for event list/details
    let eventsData = [
        { id: 1, name: 'Amazon Placement Drive', date: '2025-01-15', status: 'pending', location: 'Auditorium', description: 'Amazon campus placement drive for CSE/IT/ECE.' },
        { id: 2, name: 'Resume Workshop', date: '2025-02-10', status: 'verified', location: 'Seminar Hall', description: 'Workshop on resume building and interview skills.' },
        { id: 3, name: 'Tech Talk: AI', date: '2025-03-05', status: 'rejected', location: 'Online', description: 'Guest lecture on Artificial Intelligence.' }
    ];
    let filteredEvents = [];
    let selectedEventId = null;

    document.addEventListener('DOMContentLoaded', fetchEvents);
    document.getElementById('searchEvents').addEventListener('input', filterAndRenderEvents);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderEvents);

    function fetchEvents() {
        const eventListContainer = document.getElementById('eventList');
        eventListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading events...</div>';
        eventListContainer.classList.add('items-center','justify-center','text-gray-400');

        fetch('../../dataRouting/api/coordinator/GetEventRecruiterList.php', { credentials: 'include' })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.json();
            })
            .then(data => {
                console.log('API response data (GetEventRecruiterList):', data); // Debug log
                if (data.status === 'success') {
                    eventsData = data.recruiters || []; // API returns 'recruiters' key for event list
                    console.log('eventsData after assignment:', eventsData); // Debug log
                    filterAndRenderEvents();
                } else {
                    console.error('API Error:', data.message);
                    renderEventList([]);
                    eventListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load events: ${data.message || 'Unknown error.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                renderEventList([]);
                eventListContainer.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error. Please try again.</div>`;
            });
    }

    function filterAndRenderEvents() {
        const search = document.getElementById('searchEvents').value.trim().toLowerCase();
        const status = document.getElementById('statusFilter').value;
        filteredEvents = eventsData.filter(e => {
            const matchesSearch = !search || (e.name && e.name.toLowerCase().includes(search));
            const matchesStatus = !status || (e.status && e.status.toLowerCase() === status.toLowerCase());
            return matchesSearch && matchesStatus;
        });
        renderEventList(filteredEvents);
    }

    function renderEventList(list) {
        const container = document.getElementById('eventList');
        if (!list.length) {
            container.innerHTML = `<i class="fas fa-calendar-alt fa-3x mb-2"></i><div>No events match your criteria.</div>`;
            return;
        }
        container.classList.remove('items-center','justify-center','text-gray-400');
        container.innerHTML = '';
        list.forEach(e => {
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(e.VerificationStatus)}">${e.VerificationStatus||''}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center ${selectedEventId===e.EventId?'bg-blue-100':''}`;
            item.innerHTML = `<div class="flex-1"><div class="font-semibold text-gray-800">${e.EventTitle || 'N/A'}</div><div class="text-gray-500 text-xs">${e.EventType || 'N/A'} | ${e.EventLocation || 'N/A'}</div><div class="text-gray-500 text-xs">Organiser: ${e.PrimaryContactName || 'N/A'}</div></div>${statusBadge}`;
            item.onclick = () => {
                selectedEventId = e.EventId;
                renderEventList(filteredEvents);
                fetchEventDetailsAndRender(e.EventId); // Call new function to fetch and render details
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

    // NEW: Function to fetch complete event details
    async function fetchEventDetailsAndRender(eventId) {
        const detailsDiv = document.getElementById('eventDetails');
        detailsDiv.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading event details...</div>`;
        detailsDiv.classList.add('items-center','justify-center','text-gray-400');

        try {
            const apiUrl = `../../dataRouting/api/coordinator/GetEventRecruiterDetailsById.php?event_id=${eventId}`;
            const response = await fetch(apiUrl, { credentials: 'include' });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.status === 'success' && data.event) {
                renderEventDetails(data.event);
            } else {
                detailsDiv.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load details: ${data.message || 'Event not found.'}</div>`;
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            detailsDiv.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error loading details.</div>`;
        }
    }

    // MODIFIED: renderEventDetails to use full event object and add action buttons
    function renderEventDetails(e) {
        const detailsDiv = document.getElementById('eventDetails');
        const safeJSONParse = (jsonString, defaultValue = {}) => {
            if (!jsonString || typeof jsonString !== 'string') return defaultValue;
            try {
                return JSON.parse(jsonString) || defaultValue;
            } catch (error) {
                console.error("JSON Parse Error:", error);
                return defaultValue;
            }
        };

        const companyDetails = safeJSONParse(e.CompanyDetailsJson);
        const eventDocuments = Array.isArray(safeJSONParse(e.EventDocument)) ? safeJSONParse(e.EventDocument) : [];

        const na = '<span class="text-gray-400">Not provided</span>';
        const createLink = (url, text) => url ? `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${text}</a>` : na;

        let eventDocumentsHtml = '';
        if (eventDocuments.length > 0) {
            const documentLinks = eventDocuments.map(docLink => {
                const fileName = docLink.substring(docLink.lastIndexOf('/') + 1);
                return `<a href="${docLink}" target="_blank" class="text-blue-600 hover:underline">${fileName}</a>`;
            });
            eventDocumentsHtml = `<div><b>Attached Documents:</b> ${documentLinks.join(', ')}</div>`;
        } else {
            eventDocumentsHtml = `<div><b>Attached Documents:</b> No documents attached.</div>`;
        }

        detailsDiv.classList.remove('items-center','justify-center','text-gray-400');
        detailsDiv.innerHTML = `
            <div class="mb-4 flex items-center justify-between border-b pb-4">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt fa-2x text-blue-600 mr-3"></i>
                    <h2 class="text-2xl font-bold mr-3">${e.EventTitle || na}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(e.VerificationStatus)}">${e.VerificationStatus || 'N/A'}</span>
                </div>
                ${e.VerificationStatus.toLowerCase() !== 'verified' ? `
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full overflow-y-auto" style="max-height: calc(100vh - 180px);">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Event Details</h3>
                    <p class="mb-1 text-gray-700"><b>Type:</b> ${e.EventType || na}</p>
                    <p class="mb-1 text-gray-700"><b>Location:</b> ${e.EventLocation || na}</p>
                    <p class="mb-1 text-gray-700"><b>Description:</b> ${e.EventDescription || na}</p>
                    <p class="mb-1 text-gray-700"><b>Start Date:</b> ${e.EventStartDate || na}</p>
                    <p class="mb-1 text-gray-700"><b>End Date:</b> ${e.EventEndDate || na}</p>
                    ${eventDocumentsHtml}
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Organiser Details</h3>
                    <p class="mb-1 text-gray-700"><b>Name:</b> ${e.PrimaryContactName || na}</p>
                    <p class="mb-1 text-gray-700"><b>Email:</b> ${e.PrimaryContactEmail || na}</p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> ${e.PrimaryContactPhone || na}</p>
                    <p class="mb-1 text-gray-700"><b>Position:</b> ${e.PrimaryContactPosition || na}</p>
                    <p class="mb-1 text-gray-700"><b>LinkedIn:</b> ${createLink(e.PrimaryContactLinkedinProfile, 'LinkedIn Profile')}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Alternative Contact Details</h3>
                    <p class="mb-1 text-gray-700"><b>Name:</b> ${e.AltContactName || na}</p>
                    <p class="mb-1 text-gray-700"><b>Position:</b> ${e.AltContactPosition || na}</p>
                    <p class="mb-1 text-gray-700"><b>Email:</b> ${e.AltContactEmail || na}</p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> ${e.AltContactPhone || na}</p>
                    <p class="mb-1 text-gray-700"><b>LinkedIn:</b> ${createLink(e.AltContactLinkedinProfile, 'LinkedIn Profile')}</p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold mb-2">Company Details</h3>
                    <p class="mb-1 text-gray-700"><b>Company Name:</b> ${companyDetails.company_name || na}</p>
                    <p class="mb-1 text-gray-700"><b>About:</b> ${companyDetails.about || na}</p>
                    <p class="mb-1 text-gray-700"><b>Website:</b> ${createLink(companyDetails.company_website, 'Company Website')}</p>
                    <p class="mb-1 text-gray-700"><b>LinkedIn:</b> ${createLink(companyDetails.company_linkedin, 'Company LinkedIn')}</p>
                    <p class="mb-1 text-gray-700"><b>Address:</b> ${companyDetails.address || na}, ${companyDetails.city || na}, ${companyDetails.state || na}, ${companyDetails.country || na}</p>
                </div>

                <div class="col-span-1 md:col-span-2 mt-4 pt-4 border-t">
                    <h3 class="text-lg font-semibold mb-2">Verification Information</h3>
                    <p class="mb-1 text-gray-700"><b>Verification Status:</b> ${e.VerificationStatus || na}</p>
                    <p class="mb-1 text-gray-700"><b>Verified On:</b> ${e.VerifiedOn || na}</p>
                    <p class="mb-1 text-gray-700"><b>Event created on:</b> ${e.CreatedAt || na}</p>
                    <p class="mb-1 text-gray-700"><b>Last updated on:</b> ${e.UpdatedAt || na}</p>
                </div>
            </div>
        `;

        // Add event listeners for action buttons
        if (e.VerificationStatus.toLowerCase() !== 'verified') {
            document.getElementById('verifyBtn').onclick = () => handleVerificationAction('verified', e.EventId);
            document.getElementById('modifyBtn').onclick = () => openMessageModal('modify', e.EventId);
            document.getElementById('rejectBtn').onclick = () => openMessageModal('rejected', e.EventId);
        }
    }

    // NEW: Function to handle verification actions
    async function handleVerificationAction(newStatus, eventId) {
        const message = document.getElementById('messageInput').value; // Assuming a global message input for simplicity
        if (!message) {
            alert('Remark is required for verification.');
            return;
        }

        const confirmAction = window.confirm(`Are you sure you want to ${newStatus} this event?`);
        if (!confirmAction) {
            return;
        }

        try {
            const apiUrl = `../../dataRouting/api/coordinator/UpdateEventStatus.php`; // You will need to implement this endpoint
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `event_id=${eventId}&status=${newStatus}&remark=${encodeURIComponent(message)}&coordinator_id='<?php echo $coordinatorId; ?>'`,
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Event ${newStatus} successfully!`);
                fetchEvents(); // Refresh the list to show updated status
                closeMessageModal(); // Close modal after successful action
            } else {
                alert(`Failed to ${newStatus} event: ${data.message}`);
            }
        } catch (error) {
            console.error('Verification Error:', error);
            alert('Network or server error during verification.');
        }
    }

    // NEW: Function to open message modal
    function openMessageModal(action, eventId) {
        const messageModal = document.getElementById('messageModal');
        const modalTitle = document.getElementById('messageModalTitle');
        const messageInput = document.getElementById('messageInput');
        const submitBtn = document.getElementById('submitMessageBtn');

        messageInput.oninput = () => {
            submitBtn.disabled = messageInput.value.trim() === '';
            submitBtn.classList.toggle('opacity-50', submitBtn.disabled);
            submitBtn.classList.toggle('cursor-not-allowed', submitBtn.disabled);
        };

        if (action === 'modify') {
            modalTitle.textContent = 'Modify Event Details';
            messageInput.placeholder = 'Enter your modifications here...';
            submitBtn.textContent = 'Save Changes';
        } else if (action === 'rejected') {
            modalTitle.textContent = 'Reject Event Application';
            messageInput.placeholder = 'Enter the reason for rejection...';
            submitBtn.textContent = 'Reject';
        }

        messageInput.value = ''; // Clear previous message
        messageInput.oninput(); // Trigger validation immediately
        submitBtn.onclick = () => handleVerificationAction(action, eventId);
        document.getElementById('cancelMessageBtn').onclick = () => closeMessageModal();
        messageModal.classList.remove('hidden');
    }

    // NEW: Function to close message modal
    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.getElementById('messageInput').value = '';
        document.getElementById('submitMessageBtn').disabled = true;
        document.getElementById('submitMessageBtn').classList.add('opacity-50', 'cursor-not-allowed');
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