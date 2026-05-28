<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
// Set role to admin for this page context
$user_role = $_SESSION['user_role'] ?? 'admin';
$user_name = $_SESSION['user_name'] ?? 'Admin User';
$user_email = $_SESSION['user_email'] ?? '';
$coordinatorId = $_SESSION['user_id'] ?? ''; // This might be repurposed for an admin ID if needed

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
    <title>Event Participants - Admin Portal</title>
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
                            <h2 class="text-xl font-bold flex-grow">Events</h2>
                            <input type="text" id="searchEvents" class="form-input ml-4" placeholder="Search events...">
                            <select id="statusFilter" class="form-input ml-2">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div id="eventList" class="flex-1 flex flex-col items-center justify-center text-gray-400 overflow-y-auto overflow-scroll">
                            <i class="fas fa-calendar-alt fa-3x mb-2"></i>
                            <div>No events match your criteria.</div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="participantDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400 overflow-y-auto overflow-scroll">
                            <i class="fas fa-users fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select an Event</div>
                            <div>Choose an event from the list to view participant details.</div>
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
    let currentParticipants = []; // Store the full list of participants for the selected event

    document.addEventListener('DOMContentLoaded', fetchEvents);
    document.getElementById('searchEvents').addEventListener('input', filterAndRenderEvents);
    document.getElementById('statusFilter').addEventListener('change', filterAndRenderEvents);

    function fetchEvents() {
        const eventListContainer = document.getElementById('eventList');
        eventListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading events...</div>';
        eventListContainer.classList.add('items-center','justify-center','text-gray-400');

        fetch('../../dataRouting/api/coordinator/GetEventRecruiterList.php', { credentials: 'include' })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                return r.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    eventsData = data.recruiters || [];
                    filterAndRenderEvents();
                } else {
                    eventListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load events: ${data.message || 'Unknown error.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                eventListContainer.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error. Please try again.</div>`;
            });
    }

    function filterAndRenderEvents() {
        const search = document.getElementById('searchEvents').value.trim().toLowerCase();
        const status = document.getElementById('statusFilter').value;
        
        filteredEvents = eventsData.filter(e => {
            const matchesSearch = !search || (e.EventTitle && e.EventTitle.toLowerCase().includes(search));
            const matchesStatus = !status || (e.VerificationStatus && e.VerificationStatus.toLowerCase() === status.toLowerCase());
            return matchesSearch && matchesStatus;
        });
        renderEventList(filteredEvents);
    }

    function renderEventList(list) {
        const container = document.getElementById('eventList');
        if (!list.length) {
            container.innerHTML = `<i class="fas fa-calendar-alt fa-3x mb-2"></i><div>No events match your criteria.</div>`;
            container.classList.add('items-center','justify-center','text-gray-400');
            return;
        }
        container.classList.remove('items-center','justify-center','text-gray-400');
        container.innerHTML = '';
        list.forEach(e => {
            const statusBadge = `<span class="ml-2 px-2 py-1 rounded text-xs ${getStatusColor(e.VerificationStatus)}">${e.VerificationStatus||'N/A'}</span>`;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center ${selectedEventId === e.EventId ? 'bg-blue-100 border-blue-300' : ''}`;
            item.innerHTML = `<div class="flex-1">
                                <div class="font-semibold text-gray-800">${e.EventTitle || 'N/A'}</div>
                                <div class="text-gray-500 text-xs">${e.EventType || 'N/A'} | ${e.EventLocation || 'N/A'}</div>
                              </div>${statusBadge}`;
            item.onclick = () => {
                selectedEventId = e.EventId;
                renderEventList(filteredEvents);
                fetchAndDisplayParticipants(e.EventId, e.EventTitle);
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

    async function fetchAndDisplayParticipants(eventId, eventTitle) {
        const container = document.getElementById('participantDetails');
        container.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x text-blue-500"></i><div class="mt-2 text-gray-500">Loading participants...</div>`;
        container.classList.add('items-center', 'justify-center');

        try {
            const apiUrl = `../../dataRouting/api/admin/GetEventParticipantsList.php?event_id=${encodeURIComponent(eventId)}`;
            const response = await fetch(apiUrl, { credentials: 'include' });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            if (data.status === 'success' && data.participantsList) {
                currentParticipants = data.participantsList; // Store the full list
                renderParticipantsTable(eventTitle); // Render the table shell and initial rows
            } else {
                currentParticipants = [];
                container.innerHTML = `<i class="fas fa-exclamation-circle fa-3x text-red-500"></i><div class="mt-2">${data.message || 'Could not fetch participant data.'}</div>`;
            }
        } catch (error) {
            console.error('Fetch Participants Error:', error);
            currentParticipants = [];
            container.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x text-red-500"></i><div class="mt-2">A network or server error occurred.</div>`;
        }
    }

    function renderParticipantsTable(eventTitle) {
        const container = document.getElementById('participantDetails');
        container.classList.remove('items-center', 'justify-center');

        container.innerHTML = `
            <div class="flex flex-col sm:flex-row items-center justify-between mb-4 border-b pb-2 gap-4">
                <h2 class="text-2xl font-bold text-center sm:text-left">Participants: <span class="text-blue-600">${eventTitle}</span></h2>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-grow">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-gray-400"></i></span>
                        <input type="text" id="participantSearch" class="form-input w-full pl-10" placeholder="Search name, roll, dept...">
                    </div>
                    <button id="exportBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow text-sm whitespace-nowrap">
                        <i class="fas fa-file-excel mr-2"></i> Export
                    </button>
                </div>
            </div>
            <div class="overflow-y-auto" style="max-height: calc(100vh - 250px);">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roll No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPI</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered On</th>
                        </tr>
                    </thead>
                    <tbody id="participantTableBody" class="divide-y divide-gray-200">
                        </tbody>
                </table>
            </div>
        `;
        
        renderParticipantRows(currentParticipants); // Initial render of all rows

        document.getElementById('exportBtn').addEventListener('click', () => {
            exportToCsv(currentParticipants, `${eventTitle.replace(/ /g,"_")}_participants.csv`);
        });

        document.getElementById('participantSearch').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredParticipants = currentParticipants.filter(p => {
                return (p.student_name?.toLowerCase().includes(searchTerm)) ||
                       (p.roll_no?.toLowerCase().includes(searchTerm)) ||
                       (p.department?.toLowerCase().includes(searchTerm));
            });
            renderParticipantRows(filteredParticipants);
        });
    }

    function renderParticipantRows(participants) {
        const tableBody = document.getElementById('participantTableBody');
        if (!tableBody) return;

        if (participants.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-gray-500 py-10">No participants found.</td></tr>`;
            return;
        }

        tableBody.innerHTML = participants.map(p => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700">${p.roll_no || 'N/A'}</td>
                <td class="px-4 py-2 text-sm text-gray-800 font-medium">${p.student_name || 'N/A'}</td>
                <td class="px-4 py-2 text-sm text-gray-700">${p.department || 'N/A'}</td>
                <td class="px-4 py-2 text-sm text-gray-700">${p.cpi || 'N/A'}</td>
                <td class="px-4 py-2 text-sm text-gray-700">${p.phone_number || 'N/A'}</td>
                <td class="px-4 py-2 text-sm text-gray-700"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${p.participation_status || 'N/A'}</span></td>
                <td class="px-4 py-2 text-sm text-gray-500">${new Date(p.registration_datetime).toLocaleString() || 'N/A'}</td>
            </tr>
        `).join('');
    }

    function exportToCsv(jsonData, fileName) {
        const headers = ["Student ID", "Roll No", "Name", "Category", "Department", "Program", "Semester", "CPI", "Admission Year", "Gender", "Blood Group", "Phone", "City", "State", "Country", "Placement Interest", "Participation Status", "Registered On"];
        
        const csvRows = jsonData.map(row => {
            const values = [
                row.student_id, row.roll_no, row.student_name, row.category, row.department,
                row.program, row.current_semester, row.cpi, row.year_of_admission, row.gender,
                row.blood_group, row.phone_number, row.city, row.state, row.country,
                row.placement_interest, row.participation_status, new Date(row.registration_datetime).toLocaleString()
            ];
            return values.map(val => `"${String(val || '').replace(/"/g, '""')}"`).join(',');
        });

        const csvString = [headers.join(','), ...csvRows].join('\n');
        
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', fileName);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
</script>
</body>
</html>
