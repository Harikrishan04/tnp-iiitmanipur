<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$recruiter_id = $_SESSION['user_id'] ?? null;
var_dump($_SESSION['recruiter_id']);
// Define menu items based on role
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
       
        <!-- Main Content Area (Header + Form + Footer) -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Header -->
            <?php require_once '../../includes/topbar.php'; ?>

            <!-- Main/Form Content (with progress bar and scroll) -->
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <div id="verificationStatusBanner" class="mb-6 w-full">
                    <!-- Status will be injected here by JS -->
                </div>
                <div class="flex-1 min-h-0 flex flex-col w-full">
                    <div class="bg-white rounded-lg shadow-lg p-0 flex-1 min-h-0 flex flex-col w-full">
                        
                        <!-- Scrollable Form Area -->
                        <div class="flex-1 min-h-0 overflow-y-auto px-8 pb-8">
                            <!-- Sticky top-right action buttons -->
                            <div class="flex justify-end items-center sticky top-0 z-29 bg-white py-4" style="background: white;">
                                <button id="previewBtn"
                                        type="button"
                                    class="flex items-center px-6 py-3 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 transition-all duration-300 mr-2"
                                    >
                                    <i class="fas fa-eye mr-2"></i>Preview
                                    </button>
                                <button id="updateBtn"
                                    type="button"
                                    class="submit-btn flex items-center px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all duration-300"
                                >
                                    <i class="fas fa-edit mr-2"></i>Update
                                    </button>
                                </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <!-- Footer -->
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

    <!-- Update Profile Modal -->
    <div id="updateProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" tabindex="-1" aria-modal="true" role="dialog">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 flex-shrink-0">
                <h2 class="text-xl font-bold text-gray-800">Update Recruiter Profile</h2>
                <button id="closeUpdateProfileModal" class="text-gray-400 hover:text-gray-700 text-2xl focus:outline-none" aria-label="Close modal">&times;</button>
            </div>
            
            <!-- Modal Content (scrollable) -->
            <form id="updateProfileForm" style="max-height: calc(75vh - 45px);"  class="flex-1 overflow-y-auto p-6 space-y-8">
                <!-- Company Details -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Company Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-building mr-1"></i>Company Name <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_company_name" name="company_name" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-globe mr-1"></i>Company Website <span class="text-red-600">*</span></label>
                            <input type="url" id="modal_company_website" name="company_website" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-map-marker-alt mr-1"></i>Located At <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_company_address" name="company_address" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-city mr-1"></i>City <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_company_city" name="company_city" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-flag mr-1"></i>State <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_company_state" name="company_state" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2">🇮🇳 Country <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_company_country" name="company_country" class="form-input" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-align-left mr-1"></i>About Company <span class="text-red-600">*</span></label>
                            <textarea id="modal_company_about" name="company_about" class="form-input" rows="3" required></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-600 font-medium mb-2"><i class="fab fa-linkedin mr-1"></i>Company LinkedIn Profile</label>
                            <input type="url" id="modal_company_linkedin" name="company_linkedin" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Primary Contact -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Primary Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-briefcase mr-1"></i>Position <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_primary_position" name="primary_position" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-user mr-1"></i>Name <span class="text-red-600">*</span></label>
                            <input type="text" id="modal_primary_name" name="primary_name" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-envelope mr-1"></i>Email <span class="text-red-600">*</span></label>
                            <input type="email" id="modal_primary_email" name="primary_email" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-phone mr-1"></i>Phone <span class="text-red-600">*</span></label>
                            <input type="tel" id="modal_primary_phone" name="primary_phone" class="form-input" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-600 font-medium mb-2"><i class="fab fa-linkedin mr-1"></i>LinkedIn Profile</label>
                            <input type="url" id="modal_primary_linkedin" name="primary_linkedin" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Alternative Contact -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Alternative Contact <span class="text-gray-400 text-sm">(optional)</span></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-briefcase mr-1"></i>Position</label>
                            <input type="text" id="modal_alt_position" name="alt_position" class="form-input">
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-user mr-1"></i>Name</label>
                            <input type="text" id="modal_alt_name" name="alt_name" class="form-input">
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-envelope mr-1"></i>Email</label>
                            <input type="email" id="modal_alt_email" name="alt_email" class="form-input">
                        </div>
                        <div>
                            <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-phone mr-1"></i>Phone</label>
                            <input type="tel" id="modal_alt_phone" name="alt_phone" class="form-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-600 font-medium mb-2"><i class="fab fa-linkedin mr-1"></i>LinkedIn Profile</label>
                            <input type="url" id="modal_alt_linkedin" name="alt_linkedin" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Remark -->
                <div>
                    <label class="block text-gray-600 font-medium mb-2"><i class="fas fa-sticky-note mr-1"></i>Remark</label>
                    <textarea id="modal_remark" name="remark" class="form-input" rows="2"></textarea>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-2 p-4 border-t border-gray-200 flex-shrink-0">
                <button id="cancelUpdateProfile" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-800">Cancel</button>
                <button id="saveUpdateProfile"
                    type="submit"
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                >
                    <span id="saveButtonText">Update Profile</span>
                    <i id="saveButtonSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Recruiter Modal -->
    <div id="previewRecruiterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" tabindex="-1" aria-modal="true" role="dialog">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 flex-shrink-0">
                <h2 class="text-xl font-bold text-gray-800">Recruiter Profile Preview</h2>
                <button id="closePreviewRecruiterModal" class="text-gray-400 hover:text-gray-700 text-2xl focus:outline-none" aria-label="Close modal">&times;</button>
            </div>
            
            <!-- Modal Content (scrollable) -->
            <div  style="max-height: calc(75vh - 45px);"class="flex-1 overflow-y-auto p-6">
                <div id="previewRecruiterContent" class="space-y-6">
                    <div id="previewRecruiterLoading" class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-3 text-gray-600">Loading profile...</span>
                    </div>
                    <div id="previewRecruiterData" class="hidden">
                        <!-- Data will be rendered here by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="fixed top-4 right-4 z-50 hidden">
        <div id="messageBox" class="px-6 py-4 rounded-lg shadow-lg max-w-sm">
            <div id="messageContent" class="flex items-center">
                <i id="messageIcon" class="text-lg mr-3"></i>
                <span id="messageText"></span>
            </div>
        </div>
    </div>
</body>
</html>
    <script>
        // Global variables
        const recruiterId = "<?php echo $recruiter_id ?? ''; ?>";
        
        
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileSidebarBtn = document.getElementById('mobileSidebarBtn');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }
        
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }
        
        if (mobileSidebarBtn) {
            mobileSidebarBtn.addEventListener('click', openSidebar);
        }
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        // Message display function
        function showMessage(type, message) {
            const messageContainer = document.getElementById('messageContainer');
            const messageBox = document.getElementById('messageBox');
            const messageIcon = document.getElementById('messageIcon');
            const messageText = document.getElementById('messageText');
            
            // Configure message based on type
            if (type === 'success') {
                messageBox.className = 'px-6 py-4 rounded-lg shadow-lg max-w-sm bg-green-100 border border-green-300 text-green-800';
                messageIcon.className = 'fas fa-check-circle text-lg mr-3';
            } else if (type === 'error') {
                messageBox.className = 'px-6 py-4 rounded-lg shadow-lg max-w-sm bg-red-100 border border-red-300 text-red-800';
                messageIcon.className = 'fas fa-exclamation-circle text-lg mr-3';
            } else if (type === 'warning') {
                messageBox.className = 'px-6 py-4 rounded-lg shadow-lg max-w-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                messageIcon.className = 'fas fa-exclamation-triangle text-lg mr-3';
            }
            
            messageText.textContent = message;
            messageContainer.classList.remove('hidden');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.classList.add('hidden');
            }, 5000);
        }

        // Modal handlers
        const updateBtn = document.getElementById('updateBtn');
        const updateProfileModal = document.getElementById('updateProfileModal');
        const closeUpdateProfileModal = document.getElementById('closeUpdateProfileModal');
        const cancelUpdateProfile = document.getElementById('cancelUpdateProfile');
        const saveUpdateProfile = document.getElementById('saveUpdateProfile');
        const saveButtonText = document.getElementById('saveButtonText');
        const saveButtonSpinner = document.getElementById('saveButtonSpinner');

        // Open update modal and populate with current data
        updateBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            updateProfileModal.classList.remove('hidden');
            
            // Load current data to populate the form
            try {
                const response = await fetch(`../../dataRouting/api/recruiter/getRecruiterdetailsById.php?recruiter_id=${recruiterId}`);
                const data = await response.json();
                
                if (data.status === 'success' && data.recruiter) {
                    populateUpdateForm(data.recruiter);
                } else {
                    showMessage('warning', 'Could not load current profile data. You can still update.');
                }
            } catch (error) {
                showMessage('warning', 'Could not load current profile data. You can still update.');
            }
        });

        // Close modal handlers
        closeUpdateProfileModal.addEventListener('click', function() {
            updateProfileModal.classList.add('hidden');
        });

        cancelUpdateProfile.addEventListener('click', function() {
            updateProfileModal.classList.add('hidden');
        });

        // Backdrop click to close
        updateProfileModal.addEventListener('mousedown', function(e) {
            if (e.target === updateProfileModal) {
                updateProfileModal.classList.add('hidden');
            }
        });

        // Escape key to close
        window.addEventListener('keydown', function(e) {
            if (!updateProfileModal.classList.contains('hidden') && e.key === 'Escape') {
                updateProfileModal.classList.add('hidden');
            }
        });

        // Populate update form with current data
        function populateUpdateForm(recruiter) {
            let company = {};
            try {
                if (recruiter.CompanyDetailsJson) {
                    company = JSON.parse(recruiter.CompanyDetailsJson);
                } else if (recruiter.CompanyDetails) {
                    company = JSON.parse(recruiter.CompanyDetails);
                }
            } catch (e) {
                company = {};
            }

            // Company details
            document.getElementById('modal_company_name').value = company.company_name || '';
            document.getElementById('modal_company_website').value = company.company_website || '';
            document.getElementById('modal_company_address').value = (company.address && company.address.address) || company.address || '';
            document.getElementById('modal_company_city').value = (company.address && company.address.city) || company.city || '';
            document.getElementById('modal_company_state').value = (company.address && company.address.state) || company.state || '';
            document.getElementById('modal_company_country').value = (company.address && company.address.country) || company.country || '';
            document.getElementById('modal_company_about').value = company.about || '';
            document.getElementById('modal_company_linkedin').value = company.company_linkedin || '';

            // Primary contact
            document.getElementById('modal_primary_position').value = recruiter.PrimaryContactPosition || '';
            document.getElementById('modal_primary_name').value = recruiter.PrimaryContactName || '';
            document.getElementById('modal_primary_email').value = recruiter.PrimaryContactEmail || '';
            document.getElementById('modal_primary_phone').value = recruiter.PrimaryContactPhone || '';
            document.getElementById('modal_primary_linkedin').value = recruiter.PrimaryContactLinkedInProfile || '';

            // Alternative contact
            document.getElementById('modal_alt_position').value = recruiter.AlternateContactPosition || '';
            document.getElementById('modal_alt_name').value = recruiter.AlternateContactName || '';
            document.getElementById('modal_alt_email').value = recruiter.AlternateContactEmail || '';
            document.getElementById('modal_alt_phone').value = recruiter.AlternateContactPhone || '';
            document.getElementById('modal_alt_linkedin').value = recruiter.AlternateContactLinkedInProfile || '';

            // Remark
            document.getElementById('modal_remark').value = recruiter.Remark || '';

            setTimeout(checkRequiredFields, 100);
        }

        // Add after modal open logic and after populateUpdateForm
        function checkRequiredFields() {
            const requiredIds = [
                'modal_company_name',
                'modal_company_website',
                'modal_company_address',
                'modal_company_city',
                'modal_company_state',
                'modal_company_country',
                'modal_company_about',
                'modal_primary_position',
                'modal_primary_name',
                'modal_primary_email',
                'modal_primary_phone'
            ];
            let allFilled = true;
            for (const id of requiredIds) {
                const el = document.getElementById(id);
                if (!el || !el.value.trim()) {
                    allFilled = false;
                    break;
                }
            }
            const btn = document.getElementById('saveUpdateProfile');
            if (allFilled) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            } else {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
            }
        }

        // Attach input listeners to required fields
        ['modal_company_name','modal_company_website','modal_company_address','modal_company_city','modal_company_state','modal_company_country','modal_company_about','modal_primary_position','modal_primary_name','modal_primary_email','modal_primary_phone'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', checkRequiredFields);
            }
        });

        // Also check on modal open and after populate
        updateBtn.addEventListener('click', function() {
            setTimeout(checkRequiredFields, 100); // after populate
        });

        // Save changes handler
        saveUpdateProfile.addEventListener('click', async function(e) {
            checkRequiredFields();
            if (saveUpdateProfile.disabled) {
                e.preventDefault();
                showMessage('warning', 'Please fill all required fields before submitting.');
                return;
            }
            
            // Show loading state
            saveButtonText.textContent = 'Saving...';
            saveButtonSpinner.classList.remove('hidden');
            saveUpdateProfile.disabled = true;
            
            try {
                // Collect form data
                const formData = {
                    p_recruiter_id: recruiterId,
                    p_primary_contact_name: document.getElementById('modal_primary_name').value,
                    p_primary_contact_position: document.getElementById('modal_primary_position').value,
                    p_primary_contact_email: document.getElementById('modal_primary_email').value,
                    p_primary_contact_phone: document.getElementById('modal_primary_phone').value,
                    p_primary_contact_linkedin_profile: document.getElementById('modal_primary_linkedin').value,
                    p_alt_contact_name: document.getElementById('modal_alt_name').value,
                    p_alt_contact_position: document.getElementById('modal_alt_position').value,
                    p_alt_contact_email: document.getElementById('modal_alt_email').value,
                    p_alt_contact_phone: document.getElementById('modal_alt_phone').value,
                    p_alt_contact_linkedin_profile: document.getElementById('modal_alt_linkedin').value,
                    p_remark: document.getElementById('modal_remark').value,
                    p_company_details_json: JSON.stringify({
                        company_name: document.getElementById('modal_company_name').value,
                        company_website: document.getElementById('modal_company_website').value,
                        address: document.getElementById('modal_company_address').value,
                        city: document.getElementById('modal_company_city').value,
                        state: document.getElementById('modal_company_state').value,
                        country: document.getElementById('modal_company_country').value,
                        about: document.getElementById('modal_company_about').value,
                        company_linkedin: document.getElementById('modal_company_linkedin').value
                    })
                };

                // Send PUT request
                const response = await fetch('../../dataRouting/api/recruiter/putRecruiterDetailsById.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    showMessage('success', 'Profile updated successfully!');
                    updateProfileModal.classList.add('hidden');
                    // Optionally refresh verification status
                    loadVerificationStatus();
                } else {
                    showMessage('error', result.message || 'Failed to update profile');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showMessage('error', 'Failed to update profile. Please try again.');
            } finally {
                // Reset button state
                saveButtonText.textContent = 'Save changes';
                saveButtonSpinner.classList.add('hidden');
                saveUpdateProfile.disabled = false;
            }
        });

        // Preview modal logic
        const previewBtn = document.getElementById('previewBtn');
        const previewRecruiterModal = document.getElementById('previewRecruiterModal');
        const closePreviewRecruiterModal = document.getElementById('closePreviewRecruiterModal');
        const previewRecruiterLoading = document.getElementById('previewRecruiterLoading');
        const previewRecruiterData = document.getElementById('previewRecruiterData');

        previewBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            previewRecruiterModal.classList.remove('hidden');
            previewRecruiterLoading.classList.remove('hidden');
            previewRecruiterData.classList.add('hidden');
            
            try {
                const response = await fetch(`../../dataRouting/api/recruiter/getRecruiterdetailsById.php?recruiter_id=${recruiterId}`);
                const data = await response.json();
                
                if (data.status === 'success' && data.recruiter) {
                    renderPreviewRecruiter(data.recruiter);
                } else {
                    throw new Error(data.message || 'Failed to fetch recruiter data');
                }
            } catch (error) {
                previewRecruiterLoading.innerHTML = `<div class='text-center py-8'><i class='fas fa-exclamation-triangle text-red-500 text-3xl mb-4'></i><p class='text-red-600'>Error loading recruiter data</p><p class='text-gray-500 text-sm mt-2'>${error.message}</p></div>`;
            }
        });
        closePreviewRecruiterModal.addEventListener('click', function() {
            previewRecruiterModal.classList.add('hidden');
        });
        previewRecruiterModal.addEventListener('mousedown', function(e) {
            if (e.target === previewRecruiterModal) previewRecruiterModal.classList.add('hidden');
        });
        window.addEventListener('keydown', function(e) {
            if (!previewRecruiterModal.classList.contains('hidden') && e.key === 'Escape') previewRecruiterModal.classList.add('hidden');
        });
        function renderPreviewRecruiter(r) {
            previewRecruiterLoading.classList.add('hidden');
            previewRecruiterData.classList.remove('hidden');
            let company = {};
            try {
                if (r.CompanyDetailsJson) {
                    company = JSON.parse(r.CompanyDetailsJson);
                } else if (r.CompanyDetails) {
                    company = JSON.parse(r.CompanyDetails);
                }
            } catch (e) { company = {}; }
           previewRecruiterData.innerHTML = `
<div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
  <!-- Company Details Section -->
  <div class="mb-8">
    <div class="flex items-center mb-4">
      <i class="fas fa-building text-blue-600 text-xl mr-3"></i>
      <h3 class="text-xl font-bold text-gray-800">Company Details</h3>
    </div>
    <div class="bg-gray-50 rounded-lg p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex items-center">
          <i class="fas fa-building text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Company Name:</strong></span>
          <span class="ml-2 text-gray-900">${company.company_name || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-globe text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Website:</strong></span>
          <span class="ml-2">
            ${company.company_website ? 
              `<a href="${company.company_website}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200" target="_blank">
                ${company.company_website}
                <i class="fas fa-external-link-alt text-xs ml-1"></i>
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
        <div class="flex items-start">
          <i class="fas fa-map-marker-alt text-gray-600 text-sm mr-2 mt-1"></i>
          <span class="text-gray-700"><strong>Address:</strong></span>
          <span class="ml-2 text-gray-900">${(company.address && company.address.address) || company.address || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-city text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>City:</strong></span>
          <span class="ml-2 text-gray-900">${(company.address && company.address.city) || company.city || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-flag text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>State:</strong></span>
          <span class="ml-2 text-gray-900">${(company.address && company.address.state) || company.state || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-globe-americas text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Country:</strong></span>
          <span class="ml-2 text-gray-900">${(company.address && company.address.country) || company.country || 'N/A'}</span>
        </div>
        <div class="md:col-span-2">
          <div class="flex items-start">
            <i class="fas fa-info-circle text-gray-600 text-sm mr-2 mt-1"></i>
            <span class="text-gray-700"><strong>About:</strong></span>
          </div>
          <div class="mt-2 text-gray-900 text-sm leading-relaxed bg-white p-3 rounded border-l-4 border-blue-500">
            ${company.about || 'No information available'}
          </div>
        </div>
        <div class="md:col-span-2 flex items-center">
          <i class="fab fa-linkedin text-blue-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>LinkedIn:</strong></span>
          <span class="ml-2">
            ${company.company_linkedin ? 
              `<a href="${company.company_linkedin}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200" target="_blank">
                ${company.company_linkedin}
                <i class="fas fa-external-link-alt text-xs ml-1"></i>
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Primary Contact Section -->
  <div class="mb-8">
    <div class="flex items-center mb-4">
      <i class="fas fa-user-tie text-green-600 text-xl mr-3"></i>
      <h3 class="text-xl font-bold text-gray-800">Primary Contact</h3>
      <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Main</span>
    </div>
    <div class="bg-green-50 rounded-lg p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex items-center">
          <i class="fas fa-user text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Name:</strong></span>
          <span class="ml-2 text-gray-900">${r.PrimaryContactName || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-briefcase text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Position:</strong></span>
          <span class="ml-2 text-gray-900">${r.PrimaryContactPosition || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-envelope text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Email:</strong></span>
          <span class="ml-2">
            ${r.PrimaryContactEmail ? 
              `<a href="mailto:${r.PrimaryContactEmail}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200">
                ${r.PrimaryContactEmail}
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-phone text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Phone:</strong></span>
          <span class="ml-2">
            ${r.PrimaryContactPhone ? 
              `<a href="tel:${r.PrimaryContactPhone}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200">
                ${r.PrimaryContactPhone}
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
        <div class="md:col-span-2 flex items-center">
          <i class="fab fa-linkedin text-blue-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>LinkedIn:</strong></span>
          <span class="ml-2">
            ${r.PrimaryContactLinkedInProfile ? 
              `<a href="${r.PrimaryContactLinkedInProfile}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200" target="_blank">
                ${r.PrimaryContactLinkedInProfile}
                <i class="fas fa-external-link-alt text-xs ml-1"></i>
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Alternative Contact Section -->
  <div class="mb-8">
    <div class="flex items-center mb-4">
      <i class="fas fa-user-friends text-orange-600 text-xl mr-3"></i>
      <h3 class="text-xl font-bold text-gray-800">Alternative Contact</h3>
      <span class="ml-2 bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">Backup</span>
    </div>
    <div class="bg-orange-50 rounded-lg p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex items-center">
          <i class="fas fa-user text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Name:</strong></span>
          <span class="ml-2 text-gray-900">${r.AlternateContactName || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-briefcase text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Position:</strong></span>
          <span class="ml-2 text-gray-900">${r.AlternateContactPosition || 'N/A'}</span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-envelope text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Email:</strong></span>
          <span class="ml-2">
            ${r.AlternateContactEmail ? 
              `<a href="mailto:${r.AlternateContactEmail}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200">
                ${r.AlternateContactEmail}
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
        <div class="flex items-center">
          <i class="fas fa-phone text-gray-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>Phone:</strong></span>
          <span class="ml-2">
            ${r.AlternateContactPhone ? 
              `<a href="tel:${r.AlternateContactPhone}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200">
                ${r.AlternateContactPhone}
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
        <div class="md:col-span-2 flex items-center">
          <i class="fab fa-linkedin text-blue-600 text-sm mr-2"></i>
          <span class="text-gray-700"><strong>LinkedIn:</strong></span>
          <span class="ml-2">
            ${r.AlternateContactLinkedInProfile ? 
              `<a href="${r.AlternateContactLinkedInProfile}" class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200" target="_blank">
                ${r.AlternateContactLinkedInProfile}
                <i class="fas fa-external-link-alt text-xs ml-1"></i>
              </a>` : 
              '<span class="text-gray-500">N/A</span>'
            }
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Remark Section -->
  <div class="mb-6">
    <div class="flex items-center mb-4">
      <i class="fas fa-sticky-note text-purple-600 text-xl mr-3"></i>
      <h3 class="text-xl font-bold text-gray-800">Remark</h3>
    </div>
    <div class="bg-purple-50 rounded-lg p-4">
      <div class="text-gray-900 text-sm leading-relaxed bg-white p-4 rounded border-l-4 border-purple-500">
        ${r.Remark || 'No remarks available'}
      </div>
    </div>
  </div>
</div>
`;
        }

        // Verification status banner
       async function loadVerificationStatus() {
    const banner = document.getElementById('verificationStatusBanner');
    // Ensure recruiterId is defined in the scope where this function is called.
    // For example: const recruiterId = 'YOUR_RECRUITER_UUID';

    banner.innerHTML = `<div class="bg-blue-50 border border-blue-200 text-blue-600 rounded-lg px-6 py-4 shadow flex items-center gap-4">
        <i class='fas fa-spinner fa-spin fa-lg'></i>
        <div><div class='font-semibold'>Loading status...</div></div>
    </div>`;

    try {
        // Assuming recruiterId is available in this scope
        // Make sure the API endpoint path is correct relative to your HTML file
        const res = await fetch(`../../dataRouting/api/getVerificationStatusById.php?verification_id=${recruiterId}`); // Changed event_id to verification_id to match PHP's expected parameter

        // Check for HTTP errors first (e.g., 404, 500)
        if (!res.ok) {
            const errorText = await res.text();
            let errorMessage = `HTTP error! Status: ${res.status}`;
            try {
                const errorData = JSON.parse(errorText);
                errorMessage = errorData.message || errorMessage;
            } catch (e) {
                // If response is not JSON, use the raw text
                errorMessage = errorText || errorMessage;
            }
            throw new Error(errorMessage);
        }

        const data = await res.json(); // Directly parse as JSON

        if (data.status === 'success' && data.data) { // Check for success and if 'data' property exists
            const verificationData = data.data; // Access the nested 'data' object

            let statusText = verificationData.status || 'Unknown';
            let verifiedOn = verificationData.verified_on ? `<div class='text-sm'>Verified On: <span class='font-medium'>${verificationData.verified_on}</span></div>` : '';
            let notes = verificationData.notes ? `<div class='text-sm'>Notes: <span class='font-medium'>${verificationData.notes}</span></div>` : '';

            let color = 'blue'; // Default color
            if (statusText && statusText.toLowerCase() === 'verified') {
                color = 'green';
            } else if (statusText && statusText.toLowerCase() === 'rejected') {
                color = 'red';
            } else if (statusText && statusText.toLowerCase() === 'pending') {
                color = 'yellow';
            } else if (statusText && statusText.toLowerCase() === 'resubmit') { // Added 'resubmit' status
                color = 'orange'; // A suitable color for resubmit
            }

            banner.innerHTML = `
                <div class="bg-${color}-100 border border-${color}-300 text-${color}-800 rounded-lg px-6 py-4 shadow flex items-center gap-4">
                    <i class="fas fa-info-circle fa-lg"></i>
                    <div>
                        <div class="font-semibold">Status: <span class="font-bold">${statusText.charAt(0).toUpperCase() + statusText.slice(1)}</span></div>
                        ${verifiedOn}
                        ${notes}
                    </div>
                </div>
            `;
        } else {
            // Handle cases where status is not 'success' or 'data' property is missing
            banner.innerHTML = `
                <div class="bg-red-100 border border-red-300 text-red-800 rounded-lg px-6 py-4 shadow flex items-center gap-4">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <div>
                        <div class="font-semibold">Status: <span class="font-bold">Unavailable</span></div>
                        <div class="text-sm">${data.message || 'Could not fetch verification status due to an unexpected server response.'}</div>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        // General error handling for network issues or unparseable responses
        let msg = error.message || 'Could not fetch verification status due to a network error.';
        
        banner.innerHTML = `
            <div class="bg-red-100 border border-red-300 text-red-800 rounded-lg px-6 py-4 shadow flex items-center gap-4">
                <i class="fas fa-exclamation-circle fa-lg"></i>
                <div>
                    <div class="font-semibold">Status: <span class="font-bold">Unavailable</span></div>
                    <div class="text-sm">${msg}</div>
                </div>
            </div>
        `;
    }
}
document.addEventListener('DOMContentLoaded', loadVerificationStatus);
    </script>
</body>
</html>