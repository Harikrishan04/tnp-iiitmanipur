# Modular Dashboard Structure Guide

## Overview

This guide explains how to use the modular dashboard structure with reusable components. The system is designed to be maintainable, consistent, and easy to extend.

## File Structure

```
tnp@iiitmanipur/
├── includes/
│   ├── sidebar.php      # Reusable sidebar component
│   ├── topbar.php       # Reusable topbar component
│   ├── footer.php       # Reusable footer component
│   ├── styles.php       # Reusable CSS styles
│   └── scripts.php      # Reusable JavaScript functionality
├── Dashboard/
│   ├── template.php     # Template for new pages
│   ├── company_profile.php  # Example page
│   └── [other pages]    # Your dashboard pages
└── assets/
    ├── css/
    ├── js/
    └── img/
```

## Component Structure

### 1. Sidebar (`includes/sidebar.php`)
- **Purpose**: Navigation menu based on user role
- **Features**:
  - Role-based menu items
  - Active page highlighting
  - Mobile responsive
  - User info display
  - Logout functionality

### 2. Topbar (`includes/topbar.php`)
- **Purpose**: Top navigation bar with user actions
- **Features**:
  - Mobile menu toggle
  - Notifications
  - Messages
  - User dropdown menu
  - Page title display

### 3. Footer (`includes/footer.php`)
- **Purpose**: Consistent footer across all pages
- **Features**:
  - Copyright information
  - Help links
  - Privacy/Terms links
  - Contact information

### 4. Styles (`includes/styles.php`)
- **Purpose**: Centralized CSS styles
- **Features**:
  - Progress bar styles
  - Form input styles
  - Button styles
  - Responsive utilities
  - Animation classes

### 5. Scripts (`includes/scripts.php`)
- **Purpose**: Centralized JavaScript functionality
- **Features**:
  - Sidebar toggle
  - Progress bar functionality
  - Form validation
  - Notification system
  - Smooth scrolling

## How to Create a New Dashboard Page

### Step 1: Use the Template
Copy `Dashboard/template.php` and rename it to your new page:

```php
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
    <title>Your Page Title - TNP Portal</title>
    
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
                <!-- Your content here -->
            </main>
            
            <!-- Footer -->
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- Include Dashboard Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
```

### Step 2: Add Your Content
Replace the main content area with your page-specific content:

```php
<!-- Main Content -->
<main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Your Page Title</h1>
        <p class="text-gray-600 mt-2">Page description</p>
    </div>
    
    <!-- Main Content Area -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Your content goes here -->
    </div>
</main>
```

## Available CSS Classes

### Form Classes
- `.form-input` - Styled form inputs
- `.submit-btn` - Styled submit buttons

### Progress Bar Classes
- `.progress-circle` - Progress circle
- `.progress-line` - Progress line
- `.completed` - Completed state
- `.active` - Active state
- `.inactive` - Inactive state

### Status Badge Classes
- `.status-badge` - Base status badge
- `.status-badge.success` - Success state
- `.status-badge.warning` - Warning state
- `.status-badge.error` - Error state
- `.status-badge.info` - Info state

### Utility Classes
- `.card-hover` - Card hover effects
- `.fade-in` - Fade in animation
- `.slide-up` - Slide up animation
- `.custom-scrollbar` - Custom scrollbar styling

## JavaScript Functions

### Form Validation
```javascript
// Validate a single field
validateField(fieldElement);

// Validate entire form
validateForm();

// Submit form
submitForm();
```

### Notifications
```javascript
// Show notification
showNotification('Your message', 'success'); // success, error, warning, info
```

### Progress Bar
```javascript
// Initialize progress bar
initProgressBar();

// Update progress bar
updateProgressBar();
```

## Role-Based Navigation

The sidebar automatically shows different menu items based on user role:

### Admin Menu
- Dashboard
- Manage Coordinators
- Manage Students
- Manage Recruiters
- System Settings

### Recruiter Menu
- Dashboard
- Post Job
- My Jobs
- Applications
- Company Profile
- Analytics

### Coordinator Menu
- Dashboard
- Verify Recruiters
- Verify Jobs
- Manage Students
- Reports

### Student Menu
- Dashboard
- Profile
- Jobs
- Applications
- Documents
- Placement Status

## Customization

### Adding New Menu Items
Edit `includes/sidebar.php` and add items to the appropriate role array:

```php
case 'your_role':
    $menu_items = [
        ['Menu Item', 'fas fa-icon', '/path/to/page.php', 'Description'],
        // Add more items...
    ];
    break;
```

### Custom Styles
Add your custom styles to `includes/styles.php`:

```css
/* Your custom styles */
.your-custom-class {
    /* styles */
}
```

### Custom JavaScript
Add your custom JavaScript to `includes/scripts.php`:

```javascript
// Your custom functions
function yourCustomFunction() {
    // Your code
}
```

## Best Practices

### 1. Consistent Structure
Always follow the template structure:
- Session check
- Head section with includes
- Body with sidebar, topbar, main content, footer
- Scripts include

### 2. Responsive Design
- Use Tailwind's responsive classes
- Test on mobile devices
- Use the provided mobile menu functionality

### 3. Form Handling
- Use the provided form validation
- Use `.form-input` class for consistent styling
- Use `.submit-btn` class for buttons

### 4. Error Handling
- Always check for user authentication
- Use try-catch blocks for database operations
- Show user-friendly error messages

### 5. Performance
- Minimize database queries
- Use efficient CSS selectors
- Optimize images and assets

## Troubleshooting

### Common Issues

1. **Sidebar not showing**
   - Check if user is logged in
   - Verify session variables
   - Check file paths

2. **Styles not loading**
   - Verify `includes/styles.php` path
   - Check for syntax errors
   - Clear browser cache

3. **JavaScript not working**
   - Check browser console for errors
   - Verify `includes/scripts.php` path
   - Ensure jQuery is loaded

4. **Mobile menu not working**
   - Check if all required elements exist
   - Verify CSS classes
   - Test on actual mobile device

### Debug Mode
Add this to your page for debugging:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

## Security Considerations

1. **Session Management**
   - Always start session
   - Check user authentication
   - Validate user permissions

2. **Input Validation**
   - Use the provided form validation
   - Sanitize all user inputs
   - Use prepared statements

3. **File Includes**
   - Use relative paths carefully
   - Validate include paths
   - Don't expose sensitive information

## Examples

### Simple Dashboard Page
```php
<!-- Main Content -->
<main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
            <h3 class="text-lg font-semibold mb-4">Statistics</h3>
            <p class="text-3xl font-bold text-blue-600">1,234</p>
        </div>
        <!-- More cards... -->
    </div>
</main>
```

### Form Page
```php
<!-- Main Content -->
<main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Add New Item</h1>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-600 font-medium mb-2">Name</label>
                    <input type="text" class="form-input" required>
                </div>
                <div>
                    <label class="block text-gray-600 font-medium mb-2">Email</label>
                    <input type="email" class="form-input" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="submit-btn">Save</button>
            </div>
        </form>
    </div>
</main>
```

This modular structure provides a consistent, maintainable, and scalable foundation for your TNP Portal dashboard. 