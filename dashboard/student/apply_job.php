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
    <title>Apply for Job - TNP Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.min.js"></script>
    <style>
      /* Any specific styles for this page can go here */
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
       
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>

            <main class="flex-1 min-h-0 flex flex-col p-4 md:p-8 w-full overflow-hidden">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Available Jobs / Events</h1>
                    <div class="flex items-center space-x-4">
                        <input type="text" id="searchEvents" class="form-input border p-2 rounded" placeholder="Search jobs...">
                        <select id="typeFilter" class="form-input border p-2 rounded">
                            <option value="">All Types</option>
                            <option value="Full time">Full-time</option>
                            <option value="Internship">Internship</option>
                            <option value="Part time">Part-time</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Webinar">Webinar</option>
                        </select>
                    </div>
                </div>

                <div class="flex-1 bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                    <div id="eventList" class="flex-1 flex flex-col items-center justify-center text-gray-400 p-6 overflow-y-auto">
                        <i class="fas fa-briefcase fa-3x mb-2"></i>
                        <div>Loading jobs...</div>
                    </div>
                </div>
            </main>
            
            <footer class="bg-white border-t border-gray-200 py-4 w-full mt-auto flex-shrink-0">
                <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
                    <p class="text-gray-600 text-sm">&copy; <?php echo date('Y'); ?> Training & Placement Portal. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <div id="jobDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-bold text-gray-800" id="jobModalTitle">Job Details</h2>
                <button id="closeJobModal" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div id="jobModalContent" class="space-y-4">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <span class="ml-3 text-gray-600">Loading job details...</span>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t flex justify-end items-center space-x-4">
                    <div>
                        <label for="resumeUpload" class="block text-sm font-medium text-gray-700">Resume Link (URL)</label>
                        <input type="url" id="resumeUpload" placeholder="https://example.com/your-resume.pdf" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <button id="applyJobBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Apply Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let allEvents = [];
            let filteredEvents = [];
            let selectedEventId = null; // Variable to hold the currently selected event's ID

            // Utility function for toasts
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

            // --- Event List Fetching and Rendering ---
            const fetchEvents = async () => {
                const eventListContainer = $('#eventList');
                eventListContainer.html('<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading jobs...</div>');
                eventListContainer.addClass('items-center justify-center text-gray-400');

                try {
                    const response = await fetch('../../dataRouting/api/student/GetEventslist.php', { credentials: 'include' });
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();

                    if (data.status === 'success' && data.events) {
                        allEvents = data.events;
                        filterAndRenderEvents();
                    } else {
                        showToast(data.message || 'Failed to load events.', true);
                        renderEventList([]);
                    }
                } catch (error) {
                    console.error('Fetch Events Error:', error);
                    showToast('Network or server error loading events.', true);
                    renderEventList([]);
                }
            };

            const filterAndRenderEvents = () => {
                const search = $('#searchEvents').val().trim().toLowerCase();
                const type = $('#typeFilter').val();

                filteredEvents = allEvents.filter(e => {
                    const matchesSearch = !search || (e.EventTitle && e.EventTitle.toLowerCase().includes(search)) || (e.EventLocation && e.EventLocation.toLowerCase().includes(search));
                    const matchesType = !type || (e.EventType && e.EventType.toLowerCase() === type.toLowerCase());
                    return matchesSearch && matchesType;
                });
                renderEventList(filteredEvents);
            };

            const renderEventList = (list) => {
                const container = $('#eventList');
                container.empty();

                if (!list.length) {
                    container.addClass('items-center justify-center text-gray-400');
                    container.html('<i class="fas fa-briefcase fa-3x mb-2"></i><div>No jobs match your criteria.</div>');
                    return;
                }

                container.removeClass('items-center justify-center text-gray-400');
                list.forEach(event => {
                    const item = $(`<button type="button" class="w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 bg-white flex items-center justify-between">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 text-lg">${event.EventTitle || 'N/A'}</div>
                            <div class="text-gray-600 text-sm">${event.EventType || 'N/A'} | ${event.EventLocation || 'N/A'}</div>
                            <div class="text-gray-500 text-xs">Starts: ${event.EventStartDate ? new Date(event.EventStartDate).toLocaleString() : 'N/A'}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold ${getEventStatusColor(event.EventStatus)}">${event.EventStatus || 'N/A'}</span>
                    </button>`);
                    item.on('click', () => openJobDetailsModal(event));
                    container.append(item);
                });
            };

            const getEventStatusColor = (status) => {
                switch((status || '').toLowerCase()) {
                    case 'opened': return 'bg-green-100 text-green-800';
                    case 'closed': return 'bg-red-100 text-red-800';
                    default: return 'bg-gray-100 text-gray-600';
                }
            };

            // --- Job Details Modal Logic ---
            const jobDetailsModal = $('#jobDetailsModal');
            const jobModalContent = $('#jobModalContent');
            const applyJobBtn = $('#applyJobBtn');
            const resumeUploadInput = $('#resumeUpload');

            const openJobDetailsModal = (event) => {
                jobDetailsModal.removeClass('hidden');
                jobModalContent.html('<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><span class="ml-3 text-gray-600">Loading job details...</span></div>');
                
                selectedEventId = event.EventId; // Store the EventId
                renderJobDetails(event);
            };

            const renderJobDetails = (event) => {
                const na = '<span class="text-gray-400">Not provided</span>';
                const createLink = (url, text) => url ? `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${text}</a>` : na;
                
                let eventDocumentsHtml = '';
                if (event.EventDocument) {
                    try {
                        const documents = JSON.parse(event.EventDocument);
                        if (Array.isArray(documents) && documents.length > 0) {
                            const documentLinks = documents.map(docLink => {
                                const fileName = docLink.substring(docLink.lastIndexOf('/') + 1);
                                return `<p>${createLink(docLink, fileName)}</p>`;
                            });
                            eventDocumentsHtml = `<div class="mt-4"><strong>Documents:</strong><br/>${documentLinks.join('')}</div>`;
                        } else if (typeof documents === 'string' && documents.trim() !== '') {
                            // Handle cases where it's a single URL string in the JSON
                             const fileName = documents.substring(documents.lastIndexOf('/') + 1);
                             eventDocumentsHtml = `<div class="mt-4"><strong>Document:</strong><br/>${createLink(documents, fileName)}</div>`;
                        }
                    } catch (e) {
                        console.error("Error parsing EventDocument JSON:", e);
                        eventDocumentsHtml = `<div class="mt-4"><strong>Documents:</strong> Error loading documents.</div>`;
                    }
                } else {
                    eventDocumentsHtml = `<div class="mt-4"><strong>Documents:</strong> No documents attached.</div>`;
                }

                const detailsHtml = `
                    <h3 class="text-2xl font-bold text-gray-800">${event.EventTitle || na}</h3>
                    <p class="text-gray-600">${event.EventType || na} | ${event.Location || na}</p>
                    <div class="mt-4">
                        <p><strong>Description:</strong> ${event.EventDescription || na}</p>
                        <p><strong>Start Date:</strong> ${event.EventStartDate ? new Date(event.EventStartDate).toLocaleString() : na}</p>
                        <p><strong>End Date:</strong> ${event.EventEndDate ? new Date(event.EventEndDate).toLocaleString() : na}</p>
                        <p><strong>Status:</strong> <span class="px-3 py-1 rounded-full text-sm font-semibold ${getEventStatusColor(event.EventStatus)}">${event.EventStatus || 'N/A'}</span></p>
                        <p><strong>Max Applications:</strong> ${event.MaxApplications || 'Unlimited'}</p>
                        ${eventDocumentsHtml}
                    </div>
                `;
                jobModalContent.html(detailsHtml);

                // Conditional disabling of apply button
                if (event.EventStatus && event.EventStatus.toLowerCase() === 'closed') {
                    applyJobBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                    resumeUploadInput.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                } else {
                    applyJobBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    resumeUploadInput.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            };

            $('#closeJobModal, #jobDetailsModal').on('click', function(e) {
                if (e.target === this) jobDetailsModal.addClass('hidden');
            });


            $('#applyJobBtn').on('click', async function(e) { // Made async for fetch
                e.preventDefault();

                if (!selectedEventId) { // Check if an event is selected
                    showToast('Error: No job selected for application.', true);
                    return;
                }

                const resumeLink = $('#resumeUpload').val().trim(); // Get resume link
                if (!resumeLink) { // Validate resume link
                    showToast('Please enter your resume link.', true);
                    return;
                }
                try {
                    new URL(resumeLink); // Basic URL validation
                } catch (e) {
                    showToast('Please enter a valid URL for your resume.', true);
                    return;
                }

                // Disable button to prevent multiple clicks
                const originalBtnText = $(this).html();
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Applying...');

                try {
                    const response = await fetch('../../dataRouting/api/student/RegisterStudentForEvent.php', { // AJAX call to API
                        method: 'POST', // Use POST method
                        headers: {
                            'Content-Type': 'application/json' // Set content type to JSON
                        },
                        body: JSON.stringify({ // Send data as JSON
                            event_id: selectedEventId,
                            status: 'registered', // Initial status for application
                            message: 'Student applied for event.', // Default message
                            document: resumeLink
                        }),
                        credentials: 'include' // Send cookies (for session)
                    });

                    const data = await response.json(); // Parse response as JSON

                    if (data.status === 'success') { // Handle success response
                        showToast(data.message || 'Application submitted successfully!');
                        jobDetailsModal.addClass('hidden');
                        // Optionally, re-fetch events or update UI to reflect application status
                    } else { // Handle error response
                        showToast(data.message || 'Failed to submit application.', true);
                    }
                } catch (error) { // Catch network or server errors
                    console.error('Application submission error:', error);
                    showToast('Network or server error during application.', true);
                } finally {
                    // Re-enable button
                    $(this).prop('disabled', false).html(originalBtnText);
                }
            });

            // Initial fetch of events when the page loads
            fetchEvents();

            // Event listeners for search and filter
            $('#searchEvents').on('input', filterAndRenderEvents);
            $('#typeFilter').on('change', filterAndRenderEvents);

        });
    </script>

</body>
</html>