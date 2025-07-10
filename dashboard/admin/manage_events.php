<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'admin'; // Changed default to admin
$user_name = $_SESSION['user_name'] ?? 'Admin User';
$user_email = $_SESSION['user_email'] ?? '';
$adminId = $_SESSION['user_id'] ?? 'a1b2c3d4-e5f6-7890-1234-567890abcdef'; // Using a default UUID for admin

// Handle session messages (for redirects)
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
unset($_SESSION['message'], $_SESSION['messageType']);

$menu_items = [
    ['Dashboard', 'fas fa-tachometer-alt', '/tnp/Dashboard/admin/admin_dashboard.php', 'Overview'],
    ['Manage Events', 'fas fa-calendar-alt', '/tnp/Dashboard/admin/manage_events.php', 'Manage All Events'], // This page
    ['Manage Recruiters', 'fas fa-user-tie', '/tnp/Dashboard/admin/manage_recruiters.php', 'Manage Recruiters'],
    ['Manage Students', 'fas fa-user-graduate', '/tnp/Dashboard/admin/manage_students.php', 'Manage Students'],
    ['Analytics', 'fas fa-chart-line', '/tnp/Dashboard/admin/analytics.php', 'Portal Analytics']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - TNP Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../recruiter/formstyle.css" rel="stylesheet"> <!-- Assuming formstyle.css is relative to dashboard/recruiter/ -->
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <div class="flex flex-1 gap-6 min-h-0">
                    <!-- Event List (1/3) -->
                    <div class="w-full md:w-1/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div class="flex items-center mb-4">
                            <h2 class="text-xl font-bold flex-grow">All Events</h2>
                            <input type="text" id="searchEvents" class="form-input ml-4" placeholder="Search events...">
                            <select id="statusFilter" class="form-input ml-2">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                                <option value="draft">Draft</option>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div id="eventList" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                            <div>Loading events...</div>
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
    let eventsData = [];
    let filteredEvents = [];
    let selectedEventId = null;
    const adminId = "<?php echo $adminId; ?>"; // Use adminId for API calls if needed

    document.addEventListener('DOMContentLoaded', fetchEvents);
    document.getElementById('searchEvents').addEventListener('input', filterAndRenderEvents);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderEvents);

    function fetchEvents() {
        const eventListContainer = document.getElementById('eventList');
        eventListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading events...</div>';
        eventListContainer.classList.add('items-center','justify-center','text-gray-400');

        // This API should fetch ALL events, not just those by a specific recruiter.
        // Assuming GetEventRecruiterList.php can fetch all if no recruiter_id is passed,
        // or you have a dedicated API like GetEventList.php
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
            const matchesSearch = !search || 
                                  (e.EventTitle && e.EventTitle.toLowerCase().includes(search)) || 
                                  (e.EventLocation && e.EventLocation.toLowerCase().includes(search)) ||
                                  (e.PrimaryContactName && e.PrimaryContactName.toLowerCase().includes(search));
            const matchesStatus = !status || (e.VerificationStatus && e.VerificationStatus.toLowerCase() === status.toLowerCase());
            return matchesSearch && matchesStatus;
        });
        renderEventList(filteredEvents);
    }

    function renderEventList(list) {
        const container = document.getElementById('eventList');
        if (!list.length) {
            container.innerHTML = `<i class="fas fa-calendar-alt fa-3x mb-2"></i><div>No events match your criteria.</div>`;
            container.classList.add('items-center','justify-center','text-gray-400'); // Ensure centering for empty list
            return;
        }
        container.classList.remove('items-center','justify-center','text-gray-400');
        container.innerHTML = '';
        list.forEach(e => {
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(e.VerificationStatus)}">${e.VerificationStatus||''}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center justify-between ${selectedEventId===e.EventId?'bg-blue-100':''}`;
            item.innerHTML = `<div class="flex-1"><div class="font-semibold text-gray-800">${e.EventTitle || 'N/A'}</div><div class="text-gray-500 text-xs">${e.EventType || 'N/A'} | ${e.EventLocation || 'N/A'}</div><div class="text-gray-500 text-xs">Organiser: ${e.PrimaryContactName || 'N/A'}</div></div><div class="flex items-center">${statusBadge} <button class="ml-2 text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-100" onclick="event.stopPropagation(); openManageEventModalWithData('${e.EventId}');"><i class="fas fa-edit"></i></button></div>`;
            item.onclick = () => {
                selectedEventId = e.EventId;
                renderEventList(filteredEvents);
                fetchEventDetailsAndRender(e.EventId);
            };
            container.appendChild(item);
        });
    }

    function getStatusColor(status) {
        switch((status||'').toLowerCase()) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'verified':
            case 'open': // 'open' status for admin view
            return 'bg-green-100 text-green-800';
            case 'rejected':
            case 'closed': // 'closed' status for admin view
            return 'bg-red-100 text-red-800';
            case 'draft': return 'bg-gray-200 text-gray-600'; // 'draft' status for admin view
            default: return 'bg-gray-100 text-gray-600';
        }
    }

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
                    <h2 class="text-2xl font-bold mr-3" id="eventTitleModal">${e.EventTitle || na}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(e.VerificationStatus)}">${e.VerificationStatus || 'N/A'}</span>
                </div>
                <div class="ml-auto flex space-x-3">
                    <button id="manageEventBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-cog mr-2"></i> Manage Event
                    </button>
                    ${e.VerificationStatus.toLowerCase() !== 'verified' ? `
                    <button id="verifyBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-check-circle mr-2"></i> Verify
                    </button>
                    <button id="modifyBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-edit mr-2"></i> Modify
                    </button>
                    <button id="rejectBtn" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-times-circle mr-2"></i> Reject
                    </button>
                    ` : ''}
                    <button id="archiveBtn" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded shadow">
                        <i class="fas fa-archive mr-2"></i> Archive
                    </button>
                </div>
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
                    <p class="mb-1 text-gray-700"><b>Verification Status:</b> ${e.VerificationStatus || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Verified On:</b> ${e.VerifiedOn || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Event created on:</b> ${e.CreatedAt || na}</p>
                    <p class="mb-1 text-gray-700"><b>Last updated on:</b> ${e.UpdatedAt || na}</p>
                </div>
            </div>
        `;

        // Add event listeners for admin action buttons
        document.getElementById('manageEventBtn').onclick = () => {
            document.getElementById('eventTitleModal').textContent = e.EventTitle || 'N/A';
            document.getElementById('eventStartDate').value = e.EventStartDate || '';
            document.getElementById('eventEndDate').value = e.EventEndDate || '';
            document.getElementById('maxApplications').value = e.MaxApplications || '';
            document.getElementById('manageEventModal').classList.remove('hidden');
            selectedEventId = e.EventId; // Set selectedEventId when modal is opened
        };
        document.getElementById('closeManageEventModal').onclick = () => {
            document.getElementById('manageEventModal').classList.add('hidden');
        };
        document.getElementById('closeEventBtn').onclick = async () => {
            const eventId = selectedEventId;
            if (!eventId) {
                alert('No event selected.');
                return;
            }

            const confirmClose = confirm('Are you sure you want to close this event? This will make it unavailable for new applications.');
            if (!confirmClose) {
                return;
            }

            try {
                const response = await fetch('../../dataRouting/api/admin/ManageEventById.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: eventId,
                        start_datetime: (document.getElementById('eventStartDate').value || '').replace('T', ' ') + ':00',
                        end_datetime: (document.getElementById('eventEndDate').value || '').replace('T', ' ') + ':00',
                        event_status: 'closed',
                        max_applications: document.getElementById('maxApplications').value || null
                    })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    alert('Event successfully closed!');
                    document.getElementById('manageEventModal').classList.add('hidden');
                    fetchEventDetailsAndRender(eventId); // Refresh details
                    fetchEvents(); // Refresh main list
                } else {
                    alert('Failed to close event: ' + (result.message || 'Unknown error.'));
                }
            } catch (error) {
                console.error('Error closing event:', error);
                alert('Network or server error while closing event.');
            }
        };
        document.getElementById('saveManageEventBtn').onclick = () => {
            const eventId = selectedEventId; // Use the currently selected event ID
            const startDate = (document.getElementById('eventStartDate').value || '').replace('T', ' ') + ':00';
            const endDate = (document.getElementById('eventEndDate').value || '').replace('T', ' ') + ':00';
            const maxApplications = document.getElementById('maxApplications').value;

            if (!startDate || !endDate) {
                alert('Please select both Start Date and End Date.');
                return;
            }

            const data = {
                event_id: eventId,
                start_datetime: startDate,
                end_datetime: endDate,
                event_status: 'opened', // Assuming 'opened' when saving changes
                max_applications: maxApplications
            };

            fetch('../../dataRouting/api/admin/ManageEventById.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.json();
            })
            .then(response => {
                if (response.status === 'success') {
                    alert('Event schedule updated successfully!');
                    document.getElementById('manageEventModal').classList.add('hidden');
                    fetchEventDetailsAndRender(eventId); // Refresh details for the specific event
                    fetchEvents(); // Also refresh the main list to update statuses/info
                } else {
                    alert('Failed to update event schedule: ' + (response.message || 'Unknown error.'));
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Network or server error updating event schedule.');
            });
        };
    }

    // Admin's view does not use a message modal for verify/reject/modify
    // These functions can be removed or repurposed if needed for other admin actions
    // For now, I'll keep them as placeholders if they were implicitly part of the copy request
    // and can be filled later if actual "admin modifications" with remarks are needed.
    async function handleVerificationAction(newStatus, eventId) {
        // This function would be for status updates, not direct edit/archive for admin
        // Admin might have a different workflow (e.g., direct status change via API call without remark modal)
        alert(`Admin action: ${newStatus} for Event ID: ${eventId}`);
    }

    function openMessageModal(action, eventId) {
        // This modal is typically for coordinator verification remarks.
        // Admin might use a different modal for direct edits/archive confirmations.
        alert(`Admin wants to ${action} Event ID: ${eventId} with a message.`);
    }

    function closeMessageModal() {
        // No modal to close for admin's direct actions.
    }
    
    // Function to open Manage Event Modal with data
    window.openManageEventModalWithData = async (eventId) => {
        const manageEventModal = document.getElementById('manageEventModal');
        const managedEventTitle = document.getElementById('managedEventTitle');
        const eventStartDateInput = document.getElementById('eventStartDate');
        const eventEndDateInput = document.getElementById('eventEndDate');
        const maxApplicationsInput = document.getElementById('maxApplications');

        managedEventTitle.textContent = 'Loading...';
        eventStartDateInput.value = '';
        eventEndDateInput.value = '';
        maxApplicationsInput.value = '';
        manageEventModal.classList.remove('hidden');
        selectedEventId = eventId; // Set selectedEventId when modal is opened

        try {
            const apiUrl = `../../dataRouting/api/coordinator/GetEventRecruiterDetailsById.php?event_id=${eventId}`;
            const response = await fetch(apiUrl, { credentials: 'include' });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.status === 'success' && data.event) {
                const event = data.event;
                managedEventTitle.textContent = event.EventTitle || 'N/A';
                
                // Format date strings for datetime-local input (YYYY-MM-DDTHH:MM)
                const formattedStartDate = event.EventStartDate ? event.EventStartDate.substring(0, 16).replace(' ', 'T') : '';
                const formattedEndDate = event.EventEndDate ? event.EventEndDate.substring(0, 16).replace(' ', 'T') : '';

                eventStartDateInput.value = formattedStartDate;
                eventEndDateInput.value = formattedEndDate;
                maxApplicationsInput.value = event.MaxApplications || '';
            } else {
                alert('Failed to load event details: ' + (data.message || 'Event not found.'));
                manageEventModal.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error fetching event details:', error);
            alert('Network or server error while loading event details.');
            manageEventModal.classList.add('hidden');
        }
    };
    
    // Initial setup for manage event modal buttons
    document.getElementById('closeManageEventModal').onclick = () => {
        document.getElementById('manageEventModal').classList.add('hidden');
    };
    document.getElementById('closeEventBtn').onclick = async () => {
        const eventId = selectedEventId;
        if (!eventId) {
            alert('No event selected.');
            return;
        }

        const confirmClose = confirm('Are you sure you want to close this event? This will make it unavailable for new applications.');
        if (!confirmClose) {
            return;
        }

        try {
            const response = await fetch('../../dataRouting/api/admin/ManageEventById.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    event_id: eventId,
                    start_datetime: (document.getElementById('eventStartDate').value || '').replace('T', ' ') + ':00',
                    end_datetime: (document.getElementById('eventEndDate').value || '').replace('T', ' ') + ':00',
                    event_status: 'closed',
                    max_applications: document.getElementById('maxApplications').value || null
                })
            });
            const result = await response.json();
            if (result.status === 'success') {
                alert('Event successfully closed!');
                document.getElementById('manageEventModal').classList.add('hidden');
                fetchEventDetailsAndRender(eventId); // Refresh details
                fetchEvents(); // Refresh main list
            } else {
                alert('Failed to close event: ' + (result.message || 'Unknown error.'));
            }
        } catch (error) {
            console.error('Error closing event:', error);
            alert('Network or server error while closing event.');
        }
    };
    document.getElementById('saveManageEventBtn').onclick = () => {
        const eventId = selectedEventId; // Use the currently selected event ID
        const startDate = (document.getElementById('eventStartDate').value || '').replace('T', ' ') + ':00';
        const endDate = (document.getElementById('eventEndDate').value || '').replace('T', ' ') + ':00';
        const maxApplications = document.getElementById('maxApplications').value;

        if (!startDate || !endDate) {
            alert('Please select both Start Date and End Date.');
            return;
        }

        const data = {
            event_id: eventId,
            start_datetime: startDate,
            end_datetime: endDate,
            event_status: 'opened', // Assuming 'opened' when saving changes
            max_applications: maxApplications
        };

        fetch('../../dataRouting/api/admin/ManageEventById.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(r => {
            if (!r.ok) {
                throw new Error(`HTTP error! status: ${r.status}`);
            }
            return r.json();
        })
        .then(response => {
            if (response.status === 'success') {
                alert('Event schedule updated successfully!');
                document.getElementById('manageEventModal').classList.add('hidden');
                fetchEventDetailsAndRender(eventId); // Refresh details for the specific event
                fetchEvents(); // Also refresh the main list to update statuses/info
            } else {
                alert('Failed to update event schedule: ' + (response.message || 'Unknown error.'));
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Network or server error updating event schedule.');
        });
    };
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

<!-- Manage Event Modal -->
<div id="manageEventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 relative">
        <div class="flex items-center justify-between mb-4 border-b pb-3">
            <h2 class="text-xl font-bold">Manage Event: <span id="managedEventTitle" class="text-blue-600"></span></h2>
            <button id="closeManageEventModal" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div class="space-y-4">
            <h3 class="text-lg font-semibold mb-2">Schedule Event</h3>
            <div>
                <label for="eventStartDate" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                <input type="datetime-local" id="eventStartDate" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="eventEndDate" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                <input type="datetime-local" id="eventEndDate" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="maxApplications" class="block text-sm font-medium text-gray-700">Max Applications</label>
                <input type="text" id="maxApplications" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex justify-between items-center pt-4 border-t">
                <button id="closeEventBtn" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded shadow flex items-center">
                    <i class="fas fa-ban mr-2"></i> Close Event
                </button>
                <button id="saveManageEventBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
</html>