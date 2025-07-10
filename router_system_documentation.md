# TNP Portal Router System Documentation

## Overview

The TNP Portal Router System provides a comprehensive solution for handling frontend navigation based on user roles and permissions. It consists of both backend and frontend components that work together to ensure secure, permission-based routing.

## Architecture

### Backend Components

1. **Router Class** (`dataRouting/utils/Router.php`)
   - Handles route definitions and permissions
   - Generates navigation menus
   - Manages user access control

2. **Router Handler API** (`dataRouting/api/router_handler.php`)
   - RESTful API endpoints for frontend communication
   - Handles navigation requests
   - Returns permission-based route data

### Frontend Components

1. **Frontend Router** (`assets/js/frontend-router.js`)
   - Client-side routing logic
   - Handles browser navigation
   - Manages page content loading

2. **Dashboard Template** (`dashboard/index.html`)
   - Main application interface
   - Responsive design with sidebar navigation
   - Mobile-friendly layout

## Features

### ✅ Role-Based Access Control
- Routes are restricted based on user roles
- Permission-based navigation
- Dynamic menu generation

### ✅ Responsive Design
- Mobile-first approach
- Collapsible sidebar for mobile devices
- Touch-friendly navigation

### ✅ Security
- Server-side permission validation
- Client-side access checking
- Session-based authentication

### ✅ User Experience
- Loading states
- Error handling
- Breadcrumb navigation
- Active route highlighting

## Installation & Setup

### 1. Database Setup

Ensure your database has the required tables:
- `users` - User accounts
- `roles` - User roles
- `permissions` - Available permissions
- `role_permissions` - Role-permission mappings

### 2. File Structure

```
tnp@iiitmanipur/
├── dataRouting/
│   ├── utils/
│   │   ├── Router.php
│   │   └── auth_utils.php
│   ├── api/
│   │   └── router_handler.php
│   └── config/
│       └── db.php
├── assets/
│   └── js/
│       └── frontend-router.js
└── dashboard/
    └── index.html
```

### 3. Configuration

Update the database configuration in `dataRouting/config/db.php`:

```php
$host = 'your_db_host';
$db   = 'your_db_name';
$user = 'your_db_user';
$pass = 'your_db_password';
```

## Usage

### Backend Usage

#### 1. Initialize Router

```php
require_once 'utils/Router.php';

$router = new Router('/your-base-path');
$router->setUser($userData, $pdo);
```

#### 2. Check Permissions

```php
// Check specific permission
if ($router->hasPermission('profile.read')) {
    // User can read profiles
}

// Check module access
if ($router->hasModuleAccess('event')) {
    // User has some event permissions
}
```

#### 3. Generate Navigation

```php
// Generate top navigation
$navHtml = $router->generateNavigationMenu('dashboard');

// Generate sidebar
$sidebarHtml = $router->generateSidebarMenu('dashboard');

// Generate breadcrumb
$breadcrumbHtml = $router->generateBreadcrumb('dashboard');
```

#### 4. Route Access Control

```php
// Check if user can access route
if ($router->canAccessRoute('admin-dashboard')) {
    // Allow access
} else {
    // Redirect to fallback
    $router->redirectToRoute('dashboard');
}
```

### Frontend Usage

#### 1. Initialize Router

```javascript
// Router is automatically initialized when DOM loads
// Access via window.tnpRouter
```

#### 2. Navigate Programmatically

```javascript
// Navigate to a route
window.tnpRouter.navigateTo('student-dashboard');

// Check route access
const canAccess = await window.tnpRouter.checkRouteAccess('admin-users');
```

#### 3. Permission Checking

```javascript
// Check specific permission
if (window.tnpRouter.hasPermission('event.create')) {
    // Show create event button
}

// Check module access
if (window.tnpRouter.hasModuleAccess('profile')) {
    // Show profile module
}
```

#### 4. Custom Navigation Links

```html
<!-- Use data-route attribute for automatic routing -->
<a href="#" data-route="student-events">My Events</a>
<button data-route="recruiter-create-event">Create Event</button>
```

## API Endpoints

### 1. Get Navigation Menu

```
GET /dataRouting/api/router_handler.php?action=navigation&route=dashboard&type=nav
```

**Response:**
```json
{
    "success": true,
    "html": "<nav>...</nav>",
    "user": {
        "name": "John Doe",
        "role": "student",
        "email": "john@iiitmanipur.ac.in"
    }
}
```

### 2. Get Accessible Routes

```
GET /dataRouting/api/router_handler.php?action=routes
```

**Response:**
```json
{
    "success": true,
    "routes": {
        "dashboard": {
            "path": "/dashboard",
            "title": "Dashboard",
            "module": "dashboard",
            "action": "read",
            "icon": "fas fa-tachometer-alt",
            "allowed_roles": []
        }
    },
    "user_role": "student"
}
```

### 3. Check Route Access

```
GET /dataRouting/api/router_handler.php?action=check_access&route=admin-dashboard
```

**Response:**
```json
{
    "success": true,
    "can_access": false,
    "route": "admin-dashboard"
}
```

### 4. Get User Permissions

```
GET /dataRouting/api/router_handler.php?action=permissions
```

**Response:**
```json
{
    "success": true,
    "permissions": [
        {
            "name": "profile.read",
            "module": "profile",
            "action": "read"
        }
    ],
    "user_role": "student"
}
```

## Route Definitions

### Student Routes
- `student-dashboard` - Student dashboard
- `student-profile` - Student profile
- `student-applications` - My applications
- `student-events` - Available events

### Recruiter Routes
- `recruiter-dashboard` - Recruiter dashboard
- `recruiter-events` - My events
- `recruiter-create-event` - Create event
- `recruiter-applications` - Applications

### Coordinator Routes
- `coordinator-dashboard` - Coordinator dashboard
- `coordinator-verifications` - Verifications
- `coordinator-students` - Student management
- `coordinator-events` - Event management

### Admin Routes
- `admin-dashboard` - Admin dashboard
- `admin-users` - User management
- `admin-roles` - Role management
- `admin-events` - All events
- `admin-reports` - Reports

### Common Routes
- `dashboard` - Main dashboard
- `profile` - User profile
- `settings` - Settings
- `logout` - Logout

## Customization

### Adding New Routes

1. **Backend Route Definition**

```php
// In Router.php initializeRoutes() method
$this->addRoute(
    'custom-route',           // Route name
    '/custom-path',           // URL path
    'Custom Page',            // Display title
    'custom_module',          // Required module
    'read',                   // Required action
    'fas fa-custom-icon',     // Icon class
    ['admin', 'coordinator']  // Allowed roles (optional)
);
```

2. **Frontend Integration**

```javascript
// The route will automatically appear in navigation
// if user has the required permissions
```

### Custom Navigation Styles

Modify the HTML generation methods in `Router.php`:

```php
public function generateCustomNavigation(string $activeRoute = ''): string {
    // Custom navigation HTML generation
    $html = '<div class="custom-nav">';
    // ... custom logic
    $html .= '</div>';
    return $html;
}
```

### Custom Permission Logic

Extend the permission checking methods:

```php
public function hasCustomPermission(string $customPermission): bool {
    // Custom permission logic
    return $this->hasPermission($customPermission) && 
           $this->user['custom_field'] === 'required_value';
}
```

## Security Considerations

### 1. Server-Side Validation
- All route access is validated on the server
- Client-side checks are for UX only
- Never trust client-side permission data

### 2. Session Management
- Use secure session tokens
- Implement proper session expiration
- Validate session on every request

### 3. Input Validation
- Sanitize all user inputs
- Validate route names and parameters
- Use prepared statements for database queries

### 4. Error Handling
- Don't expose sensitive information in errors
- Log security-related events
- Implement proper error responses

## Troubleshooting

### Common Issues

1. **Routes Not Loading**
   - Check database connection
   - Verify user permissions exist
   - Check browser console for errors

2. **Navigation Not Updating**
   - Clear browser cache
   - Check JavaScript errors
   - Verify API endpoints are accessible

3. **Permission Denied Errors**
   - Check user role assignments
   - Verify permission mappings
   - Check route definitions

4. **Mobile Menu Issues**
   - Check CSS classes
   - Verify JavaScript event listeners
   - Test on different screen sizes

### Debug Mode

Enable debug logging in the router:

```php
// Add to Router.php constructor
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Testing

1. **Test Different User Roles**
   - Create test users with different roles
   - Verify navigation changes appropriately
   - Test permission restrictions

2. **Test Mobile Responsiveness**
   - Test on various screen sizes
   - Verify sidebar functionality
   - Check touch interactions

3. **Test Browser Navigation**
   - Test back/forward buttons
   - Verify URL updates correctly
   - Check bookmark functionality

## Performance Optimization

### 1. Caching
- Cache user permissions
- Cache route definitions
- Use browser caching for static assets

### 2. Lazy Loading
- Load route content on demand
- Implement progressive enhancement
- Use async/await for API calls

### 3. Database Optimization
- Index permission tables
- Use efficient queries
- Implement connection pooling

## Future Enhancements

### Planned Features
- [ ] Route caching
- [ ] Advanced permission rules
- [ ] Multi-language support
- [ ] Analytics integration
- [ ] A/B testing support

### Extension Points
- Custom permission providers
- Plugin system for routes
- Custom navigation renderers
- Advanced routing patterns

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review error logs
3. Test with different user roles
4. Verify database permissions

## License

This router system is part of the TNP Portal project and follows the same licensing terms. 