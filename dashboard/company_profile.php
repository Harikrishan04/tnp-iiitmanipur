<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - TNP Portal</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Include Dashboard Styles -->
    <?php include '../includes/styles.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Topbar -->
            <?php include '../includes/topbar.php'; ?>
            
            <!-- Main Content -->
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <!-- Status Card -->
                <div class="mb-6 w-full">
                    <div class="bg-blue-100 border border-blue-300 text-blue-800 rounded-lg px-6 py-4 shadow flex items-center gap-4">
                        <i class="fas fa-info-circle fa-lg"></i>
                        <div>
                            <div class="font-semibold">Status: <span class="font-bold">Profile Incomplete</span></div>
                            <div class="text-sm">Please complete all sections to proceed.</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 min-h-0 flex flex-col w-full">
                    <div class="bg-white rounded-lg shadow-lg p-0 flex-1 min-h-0 flex flex-col w-full">
                        <!-- Sticky Progress Steps -->
                        <div class="sticky top-0 z-10 bg-white pt-8 pb-4">
                            <div id="progressBar" class="flex items-center justify-center">
                                <!-- Step 1 -->
                                <div class="flex flex-col items-center">
                                    <div class="progress-circle completed" id="step1">1</div>
                                    <span class="mt-2 text-sm font-medium text-gray-600">Company Details</span>
                                </div>
                                <div class="progress-line" id="line1"></div>
                                <!-- Step 2 -->
                                <div class="flex flex-col items-center">
                                    <div class="progress-circle inactive" id="step2">2</div>
                                    <span class="mt-2 text-sm font-medium text-gray-600">Primary Contact</span>
                                </div>
                                <div class="progress-line" id="line2"></div>
                                <!-- Step 3 -->
                                <div class="flex flex-col items-center">
                                    <div class="progress-circle inactive" id="step3">3</div>
                                    <span class="mt-2 text-sm font-medium text-gray-600">Alternative Contact</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Scrollable Form Area -->
                        <div class="flex-1 min-h-0 overflow-y-auto px-8 pb-8 custom-scrollbar">
                            <form class="space-y-12">
                                <!-- Section 1: Company Details -->
                                <div class="border-b pb-8 mb-8" id="companyDetailsSection">
                                    <h3 class="text-xl font-bold text-gray-800 mb-4">Company Details</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Company Name</label>
                                            <input type="text" class="form-input" placeholder="Enter company name" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Company Website</label>
                                            <input type="url" class="form-input" placeholder="https://company.com" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Located At</label>
                                            <input type="text" class="form-input" placeholder="Street address" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">City</label>
                                            <input type="text" class="form-input" placeholder="City" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">State</label>
                                            <input type="text" class="form-input" placeholder="State" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Country</label>
                                            <input type="text" class="form-input" placeholder="Country" required>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-gray-600 font-medium mb-2">About Company</label>
                                            <textarea class="form-input" rows="3" placeholder="Brief about the company" required></textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-gray-600 font-medium mb-2">Company Size</label>
                                            <select class="form-input" required>
                                                <option value="">Select size</option>
                                                <option value="Startup">Startup</option>
                                                <option value="MNC">MNC</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-gray-600 font-medium mb-2">Company LinkedIn Profile</label>
                                            <input type="url" class="form-input" placeholder="https://linkedin.com/company/yourcompany">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Section 2: Primary Contact -->
                                <div class="border-b pb-8 mb-8" id="primaryContactSection">
                                    <h3 class="text-xl font-bold text-gray-800 mb-4">Primary Contact</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Position</label>
                                            <input type="text" class="form-input" placeholder="e.g. HR Manager" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Name</label>
                                            <input type="text" class="form-input" placeholder="Full name" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Email</label>
                                            <input type="email" class="form-input" placeholder="email@company.com" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Phone</label>
                                            <input type="tel" class="form-input" placeholder="+91 XXXXX XXXXX" required pattern="\+91\s\d{5}\s\d{5}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Section 3: Alternative Contact (optional) -->
                                <div id="alternativeContactSection">
                                    <h3 class="text-xl font-bold text-gray-800 mb-4">Alternative Contact <span class="text-gray-400 text-sm">(optional)</span></h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Position</label>
                                            <input type="text" class="form-input" placeholder="e.g. Assistant HR">
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Name</label>
                                            <input type="text" class="form-input" placeholder="Full name">
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Email</label>
                                            <input type="email" class="form-input" placeholder="email@company.com">
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 font-medium mb-2">Phone</label>
                                            <input type="tel" class="form-input" placeholder="+91 XXXXX XXXXX" pattern="\+91\s\d{5}\s\d{5}">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Form Actions -->
                                <div class="pt-8 flex justify-between items-center">
                                    <button
                                        type="button"
                                        class="flex items-center px-6 py-3 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 transition-all duration-300"
                                    >
                                        <i class="fas fa-arrow-left mr-2"></i>Back
                                    </button>
                                    <button
                                        type="submit"
                                        class="submit-btn"
                                    >
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            
            <!-- Footer -->
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- Include Dashboard Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html> 