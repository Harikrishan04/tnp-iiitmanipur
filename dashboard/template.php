<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /tnp/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - TNP Portal</title>
    
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
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Page Title</h1>
                    <p class="text-gray-600 mt-2">Page description or subtitle</p>
                </div>
                
                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <!-- Your page content goes here -->
                    <div class="space-y-6">
                        <!-- Example Content -->
                        <div class="border-b pb-4">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Section Title</h2>
                            <p class="text-gray-600">This is where your main content will go. You can add forms, tables, cards, or any other content here.</p>
                        </div>
                        
                        <!-- Example Form -->
                        <div class="border-b pb-4">
                            <h3 class="text-lg font-medium text-gray-800 mb-3">Example Form</h3>
                            <form class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-600 font-medium mb-2">Field Label</label>
                                        <input type="text" class="form-input" placeholder="Enter value">
                                    </div>
                                    <div>
                                        <label class="block text-gray-600 font-medium mb-2">Another Field</label>
                                        <input type="email" class="form-input" placeholder="email@example.com">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="submit-btn">Submit</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Example Table -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-3">Example Table</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">John Doe</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">john@example.com</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="status-badge success">Active</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                                <button class="text-red-600 hover:text-red-900">Delete</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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