<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'admin';
$user_name = $_SESSION['user_name'] ?? 'Admin User';
$user_email = $_SESSION['user_email'] ?? '';
$adminId = $_SESSION['user_id'] ?? 'a1b2c3d4-e5f6-7890-1234-567890abcdef';

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
unset($_SESSION['message'], $_SESSION['messageType']);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Coordinators - TNP Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../recruiter/formstyle.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php require_once '../../includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col min-h-0">
            <?php require_once '../../includes/topbar.php'; ?>
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <div class="flex flex-1 gap-6 min-h-0">
                    <!-- Coordinator List (1/3) -->
                    <div class="w-full md:w-1/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div class="flex items-center mb-4">
                            <h2 class="text-xl font-bold flex-grow">All Coordinators</h2>
                            <!-- Add Coordinator Button -->
                            <button id="addCoordinatorBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow flex items-center ml-4">
                                <i class="fas fa-plus-circle mr-2"></i> Add Coordinator
                            </button>
                            <input type="text" id="searchCoordinators" class="form-input ml-4" placeholder="Search coordinators...">
                            <select id="departmentFilter" class="form-input ml-2">
                                <option value="">All Departments</option>
                                <option value="cse">CSE</option>
                                <option value="ece">ECE</option>
                                <option value="me">ME</option>
                            </select>
                        </div>
                        <div id="coordinatorList" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-users fa-3x mb-2"></i>
                            <div>Loading coordinators...</div>
                        </div>
                    </div>
                    <!-- Coordinator Details (2/3) -->
                    <div class="w-full md:w-2/3 bg-white rounded-lg shadow-lg flex flex-col p-6 min-h-0">
                        <div id="coordinatorDetails" class="flex flex-1 flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-user-alt fa-3x mb-2"></i>
                            <div class="text-lg font-semibold mb-2">Select a Coordinator</div>
                            <div>Choose a coordinator from the list to view details</div>
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

    <!-- Add/Edit Coordinator Modal -->
    <div id="manageCoordinatorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 relative">
            <div class="flex items-center justify-between mb-4 border-b pb-3">
                <h2 class="text-xl font-bold"><span id="modalTitle">Manage Coordinator</span></h2>
                <button id="closeManageCoordinatorModal" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <form id="coordinatorForm" class="space-y-4">
                <input type="hidden" id="coordinatorIdInput"> <!-- Hidden input for coordinator_id -->

                <div>
                    <label for="nameInput" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="nameInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="emailInput" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="emailInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="phoneNumberInput" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" id="phoneNumberInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="departmentInput" class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" id="departmentInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="semesterInput" class="block text-sm font-medium text-gray-700">Semester</label>
                    <input type="number" id="semesterInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required min="1">
                </div>
                <div>
                    <label for="designationInput" class="block text-sm font-medium text-gray-700">Designation</label>
                    <input type="text" id="designationInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="teamInput" class="block text-sm font-medium text-gray-700">Team</label>
                    <input type="text" id="teamInput" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" id="cancelCoordinatorBtn" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 font-semibold px-4 py-2 rounded transition-colors duration-200">Cancel</button>
                    <button type="submit" id="saveCoordinatorBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let coordinatorsData = [];
    let filteredCoordinators = [];
    let selectedCoordinatorId = null; // This will hold the ID of the selected coordinator for details/edit
    const adminId = "<?php echo $adminId; ?>";

    // Get references to modal elements
    const manageCoordinatorModal = document.getElementById('manageCoordinatorModal');
    const modalTitle = document.getElementById('modalTitle');
    const closeManageCoordinatorModalBtn = document.getElementById('closeManageCoordinatorModal');
    const coordinatorForm = document.getElementById('coordinatorForm');
    const coordinatorIdInput = document.getElementById('coordinatorIdInput');
    const nameInput = document.getElementById('nameInput');
    const emailInput = document.getElementById('emailInput');
    const phoneNumberInput = document.getElementById('phoneNumberInput');
    const departmentInput = document.getElementById('departmentInput'); // Updated reference
    const semesterInput = document.getElementById('semesterInput');
    const designationInput = document.getElementById('designationInput');
    const teamInput = document.getElementById('teamInput');
    const cancelCoordinatorBtn = document.getElementById('cancelCoordinatorBtn');
    const saveCoordinatorBtn = document.getElementById('saveCoordinatorBtn');
    const addCoordinatorBtn = document.getElementById('addCoordinatorBtn');


    document.addEventListener('DOMContentLoaded', fetchCoordinators);
    document.getElementById('searchCoordinators').addEventListener('input', filterAndRenderCoordinators);
    document.getElementById('departmentFilter').addEventListener('change', filterAndRenderCoordinators);

    // Event Listeners for the modal
    addCoordinatorBtn.addEventListener('click', openAddCoordinatorModal);
    closeManageCoordinatorModalBtn.addEventListener('click', closeCoordinatorModal);
    cancelCoordinatorBtn.addEventListener('click', closeCoordinatorModal);
    coordinatorForm.addEventListener('submit', handleCoordinatorFormSubmit);

    function openAddCoordinatorModal() {
        modalTitle.textContent = 'Add New Coordinator';
        coordinatorIdInput.value = ''; // Clear ID for new entry
        coordinatorForm.reset(); // Clear all form fields
        manageCoordinatorModal.classList.remove('hidden');
    }

    function openManageCoordinatorModalWithData(coordinatorId) {
        modalTitle.textContent = 'Edit Coordinator';
        // Find the coordinator data from the already fetched list
        const coordinatorToEdit = coordinatorsData.find(c => c.coordinator_id === coordinatorId);

        if (coordinatorToEdit) {
            coordinatorIdInput.value = coordinatorToEdit.coordinator_id;
            nameInput.value = coordinatorToEdit.name || '';
            emailInput.value = coordinatorToEdit.email || '';
            phoneNumberInput.value = coordinatorToEdit.phone_number || '';
            departmentInput.value = coordinatorToEdit.department || ''; // Updated
            semesterInput.value = coordinatorToEdit.semester || '';
            designationInput.value = coordinatorToEdit.designation || '';
            teamInput.value = coordinatorToEdit.team || '';
            manageCoordinatorModal.classList.remove('hidden');
        } else {
            alert('Coordinator data not found for editing.');
        }
    }

    function closeCoordinatorModal() {
        manageCoordinatorModal.classList.add('hidden');
        coordinatorForm.reset(); // Reset form fields when closing
        coordinatorIdInput.value = ''; // Clear hidden ID
    }

    async function handleCoordinatorFormSubmit(event) {
        event.preventDefault(); // Prevent default form submission

        const coordinatorId = coordinatorIdInput.value || null; // Will be null for new coordinators
        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        const phone_number = phoneNumberInput.value.trim();
        const department = departmentInput.value.trim(); // Trim for text input
        const semester = parseInt(semesterInput.value);
        const designation = designationInput.value.trim();
        const team = teamInput.value.trim();

        // Basic client-side validation
        if (!name || !email || !department || !semester || !designation) {
            alert('Please fill in all required fields (Name, Email, Department, Semester, Designation).');
            return;
        }
        if (isNaN(semester) || semester <= 0) {
            alert('Semester must be a positive number.');
            return;
        }

        const payload = {
            p_coordinator_id: coordinatorId,
            p_name: name,
            p_email: email,
            p_phone_number: phone_number || null, // Send null if empty
            p_department: department,
            p_semester: semester,
            p_designation: designation,
            p_team: team || null // Send null if empty
        };

        try {
            saveCoordinatorBtn.disabled = true; // Disable button during submission
            saveCoordinatorBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

            const response = await fetch('../../dataRouting/api/admin/manage_coordinator.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(`Coordinator ${coordinatorId ? 'updated' : 'added'} successfully!`);
                closeCoordinatorModal();
                fetchCoordinators(); // Refresh the list to show changes
            } else {
                alert(`Failed to ${coordinatorId ? 'update' : 'add'} coordinator: ${data.message || 'Unknown error.'}`);
                console.error('API Error:', data.details || data.message);
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('Network or server error during coordinator save. Please try again.');
        } finally {
            saveCoordinatorBtn.disabled = false; // Re-enable button
            saveCoordinatorBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Changes';
        }
    }

    async function deleteCoordinator(coordinatorId) {
        if (!confirm('Are you sure you want to remove this coordinator? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('../../dataRouting/api/admin/manage_coordinator.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ coordinator_id: coordinatorId }),
                credentials: 'include',
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert('Coordinator removed successfully!');
                fetchCoordinators(); // Refresh the list
                // Clear details pane after deletion
                document.getElementById('coordinatorDetails').innerHTML = `
                    <i class="fas fa-user-alt fa-3x mb-2"></i>
                    <div class="text-lg font-semibold mb-2">Select a Coordinator</div>
                    <div>Choose a coordinator from the list to view details</div>
                `;
                document.getElementById('coordinatorDetails').classList.add('items-center','justify-center','text-gray-400');
                selectedCoordinatorId = null; // Clear selected ID
            } else {
                alert(`Failed to remove coordinator: ${data.message || 'Unknown error.'}`);
                console.error('API Error:', data.details || data.message);
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('Network or server error during coordinator removal. Please try again.');
        }
    }


    function fetchCoordinators() {
        const coordinatorListContainer = document.getElementById('coordinatorList');
        coordinatorListContainer.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading coordinators...</div>';
        coordinatorListContainer.classList.add('items-center','justify-center','text-gray-400');

        fetch('../../dataRouting/api/admin/manage_coordinator.php', { credentials: 'include' })
            .then(r => {
                if (!r.ok) {
                    return r.json().then(errorData => {
                        throw new Error(`HTTP error! Status: ${r.status}, Message: ${errorData.message || 'Unknown API error'}`);
                    }).catch(() => {
                        throw new Error(`HTTP error! Status: ${r.status}, Could not parse error message.`);
                    });
                }
                return r.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    coordinatorsData = data.coordinators || [];
                    filterAndRenderCoordinators();
                } else {
                    console.error('API Error:', data.message);
                    renderCoordinatorList([]);
                    coordinatorListContainer.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load coordinators: ${data.message || 'Unknown error.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                renderCoordinatorList([]);
                coordinatorListContainer.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error. Please try again. Error: ${error.message}</div>`;
            });
    }

    function filterAndRenderCoordinators() {
        const search = document.getElementById('searchCoordinators').value.trim().toLowerCase();
        const department = document.getElementById('departmentFilter').value;
        filteredCoordinators = coordinatorsData.filter(c => {
            const matchesSearch = !search ||
                                  (c.name && c.name.toLowerCase().includes(search)) ||
                                  (c.department && c.department.toLowerCase().includes(search));
            const matchesDepartment = !department || (c.department && c.department.toLowerCase() === department.toLowerCase());
            return matchesSearch && matchesDepartment;
        });
        renderCoordinatorList(filteredCoordinators);
    }

    function renderCoordinatorList(list) {
        const container = document.getElementById('coordinatorList');
        if (!list.length) {
            container.innerHTML = `<i class="fas fa-users fa-3x mb-2"></i><div>No coordinators match your criteria.</div>`;
            container.classList.add('items-center','justify-center','text-gray-400');
            return;
        }
        container.classList.remove('items-center','justify-center','text-gray-400');
        container.innerHTML = '';
        list.forEach(c => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = `w-full text-left px-4 py-3 mb-2 rounded hover:bg-blue-50 border border-gray-100 flex items-center justify-between ${selectedCoordinatorId===c.coordinator_id?'bg-blue-100':''}`;
            item.innerHTML = `<div class="flex-1"><div class="font-semibold text-gray-800">${c.name || 'N/A'}</div><div class="text-gray-500 text-xs">${c.department || 'N/A'}</div><div class="text-gray-500 text-xs">${c.email || 'N/A'}</div></div><div class="flex items-center"><button class="ml-2 text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-100" onclick="event.stopPropagation(); openManageCoordinatorModalWithData('${c.coordinator_id}');"><i class="fas fa-edit"></i></button></div>`;
            item.onclick = () => {
                selectedCoordinatorId = c.coordinator_id;
                renderCoordinatorList(filteredCoordinators);
                fetchCoordinatorDetailsAndRender(c.coordinator_id);
            };
            container.appendChild(item);
        });
    }

    function fetchCoordinatorDetailsAndRender(coordinatorId) {
        const detailsDiv = document.getElementById('coordinatorDetails');
        detailsDiv.innerHTML = `<i class="fas fa-spinner fa-spin fa-3x mb-2 text-blue-500"></i><div>Loading coordinator details...</div>`;
        detailsDiv.classList.add('items-center','justify-center','text-gray-400');

        fetch(`../../dataRouting/api/admin/manage_coordinator.php?id=${coordinatorId}`, { credentials: 'include' })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(`HTTP error! Status: ${response.status}, Message: ${errorData.message || 'Unknown API error'}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' && data.coordinator) {
                    renderCoordinatorDetails(data.coordinator);
                } else {
                    detailsDiv.innerHTML = `<i class="fas fa-exclamation-circle fa-3x mb-2 text-red-500"></i><div>Failed to load details: ${data.message || 'Coordinator not found.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                detailsDiv.innerHTML = `<i class="fas fa-exclamation-triangle fa-3x mb-2 text-red-500"></i><div>Network or server error loading details. Error: ${error.message}</div>`;
            });
    }

    function renderCoordinatorDetails(c) {
        const detailsDiv = document.getElementById('coordinatorDetails');
        detailsDiv.classList.remove('items-center','justify-center','text-gray-400');
        detailsDiv.innerHTML = `
            <div class="mb-4 flex items-center justify-between border-b pb-4">
                <div class="flex items-center">
                    <i class="fas fa-user-tie fa-2x text-blue-600 mr-3"></i>
                    <h2 class="text-2xl font-bold mr-3">${c.name || 'N/A'}</h2>
                </div>
                <div class="flex space-x-2">
                    <button id="editCoordinatorDetailsBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-1 rounded shadow flex items-center text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button id="removeCoordinatorBtn" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1 rounded shadow flex items-center text-sm">
                        <i class="fas fa-trash-alt mr-1"></i> Remove
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full overflow-y-auto" style="max-height: calc(100vh - 180px);">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Coordinator Details</h3>
                    <p class="mb-1 text-gray-700"><b>Email:</b> ${c.email || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Phone:</b> ${c.phone_number || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Department:</b> ${c.department || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Semester:</b> ${c.semester || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Designation:</b> ${c.designation || 'N/A'}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-2">Team Information</h3>
                    <p class="mb-1 text-gray-700"><b>Team:</b> ${c.team || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Joined On:</b> ${c.created_at || 'N/A'}</p>
                    <p class="mb-1 text-gray-700"><b>Last Updated:</b> ${c.updated_at || 'N/A'}</p>
                </div>
            </div>
        `;
        // Add event listeners for the new buttons
        document.getElementById('editCoordinatorDetailsBtn').addEventListener('click', () => openManageCoordinatorModalWithData(c.coordinator_id));
        document.getElementById('removeCoordinatorBtn').addEventListener('click', () => deleteCoordinator(c.coordinator_id));
    }
    </script>
</body>
</html>
