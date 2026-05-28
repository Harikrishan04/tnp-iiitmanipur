<?php
// utils/Router.php
declare(strict_types=1);

class Router {
    
    private $routes = [];
    private $user = null;
    private $userPermissions = [];
    private $basePath = '';
    
    public function __construct(string $basePath = '') {
        $this->basePath = $basePath;
        $this->initializeRoutes();
    }
    
    /**
     * Set current user and load permissions
     * @param array $user User data
     * @param PDO $pdo Database connection
     */
    public function setUser(array $user, PDO $pdo): void {
        $this->user = $user;
        $this->loadUserPermissions($pdo);
    }
    
    /**
     * Load user permissions from database
     * @param PDO $pdo Database connection
     */
    private function loadUserPermissions(PDO $pdo): void {
        if (!$this->user || !isset($this->user['user_id'])) {
            $this->userPermissions = [];
            return;
        }
        
        try {
            $stmt = $pdo->prepare("
                SELECT p.name, p.module, p.action
                FROM users u
                JOIN roles r ON u.role_id = r.id
                JOIN role_permissions rp ON r.id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE u.user_id = ?
            ");
            $stmt->execute([$this->user['user_id']]);
            $this->userPermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error loading user permissions: " . $e->getMessage());
            $this->userPermissions = [];
        }
    }
    
    /**
     * Check if user has specific permission
     * @param string $permission Permission name
     * @return bool True if user has permission
     */
    public function hasPermission(string $permission): bool {
        foreach ($this->userPermissions as $perm) {
            if ($perm['name'] === $permission) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has any permission for a module
     * @param string $module Module name
     * @return bool True if user has any permission for module
     */
    public function hasModuleAccess(string $module): bool {
        foreach ($this->userPermissions as $perm) {
            if ($perm['module'] === $module) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Initialize all application routes
     */
    private function initializeRoutes(): void {
        // Dashboard routes
        $this->addRoute('dashboard', '/dashboard', 'Dashboard', 'dashboard', 'read', 'fas fa-tachometer-alt');
        
        // Profile routes
        $this->addRoute('profile', '/profile', 'My Profile', 'profile', 'read', 'fas fa-user');
        $this->addRoute('profile-edit', '/profile/edit', 'Edit Profile', 'profile', 'update', 'fas fa-edit');
        
        // Student routes
        $this->addRoute('student-dashboard', '/student', 'Student Dashboard', 'profile', 'read', 'fas fa-graduation-cap', ['student']);
        $this->addRoute('student-profile', '/student/profile', 'Student Profile', 'profile', 'read', 'fas fa-user-graduate', ['student']);
        $this->addRoute('student-applications', '/student/applications', 'My Applications', 'application', 'read', 'fas fa-file-alt', ['student']);
        $this->addRoute('student-events', '/student/events', 'Available Events', 'event', 'read', 'fas fa-calendar-alt', ['student']);
        
        // Recruiter routes
        $this->addRoute('recruiter-dashboard', '/recruiter', 'Recruiter Dashboard', 'event', 'read', 'fas fa-building', ['recruiter']);
        $this->addRoute('recruiter-events', '/recruiter/events', 'My Events', 'event', 'read', 'fas fa-calendar', ['recruiter']);
        $this->addRoute('recruiter-create-event', '/recruiter/events/create', 'Create Event', 'event', 'create', 'fas fa-plus', ['recruiter']);
        $this->addRoute('recruiter-applications', '/recruiter/applications', 'Applications', 'application', 'read', 'fas fa-users', ['recruiter']);
        
        // Coordinator routes
        $this->addRoute('coordinator-dashboard', '/coordinator', 'Coordinator Dashboard', 'profile', 'verify', 'fas fa-user-check', ['coordinator']);
        $this->addRoute('coordinator-verifications', '/coordinator/verifications', 'Verifications', 'profile', 'verify', 'fas fa-check-circle', ['coordinator']);
        $this->addRoute('coordinator-students', '/coordinator/students', 'Student Management', 'profile', 'view_all', 'fas fa-users', ['coordinator']);
        $this->addRoute('coordinator-events', '/coordinator/events', 'Event Management', 'event', 'read', 'fas fa-calendar-check', ['coordinator']);
        
        // Admin routes
        $this->addRoute('admin-dashboard', '/admin', 'Admin Dashboard', 'profile', 'view_all', 'fas fa-cogs', ['admin']);
        $this->addRoute('admin-users', '/admin/users', 'User Management', 'profile', 'view_all', 'fas fa-users-cog', ['admin']);
        $this->addRoute('admin-roles', '/admin/roles', 'Role Management', 'coordinator', 'create', 'fas fa-user-shield', ['admin']);
        $this->addRoute('admin-events', '/admin/events', 'All Events', 'event', 'view_all', 'fas fa-calendar-times', ['admin']);
        $this->addRoute('admin-reports', '/admin/reports', 'Reports', 'application', 'view_all', 'fas fa-chart-bar', ['admin']);
        
        // Common routes
        $this->addRoute('settings', '/settings', 'Settings', 'profile', 'update', 'fas fa-cog');
        $this->addRoute('logout', '/logout', 'Logout', 'profile', 'read', 'fas fa-sign-out-alt');
    }
    
    /**
     * Add a route to the router
     * @param string $name Route name
     * @param string $path Route path
     * @param string $title Route title
     * @param string $module Required module
     * @param string $action Required action
     * @param string $icon Icon class
     * @param array $allowedRoles Allowed roles (empty for all)
     */
    private function addRoute(string $name, string $path, string $title, string $module, string $action, string $icon, array $allowedRoles = []): void {
        $this->routes[$name] = [
            'path' => $path,
            'title' => $title,
            'module' => $module,
            'action' => $action,
            'icon' => $icon,
            'allowed_roles' => $allowedRoles
        ];
    }
    
    /**
     * Get all accessible routes for current user
     * @return array Accessible routes
     */
    public function getAccessibleRoutes(): array {
        $accessibleRoutes = [];
        
        foreach ($this->routes as $name => $route) {
            if ($this->isRouteAccessible($route)) {
                $accessibleRoutes[$name] = $route;
            }
        }
        
        return $accessibleRoutes;
    }
    
    /**
     * Check if a route is accessible to current user
     * @param array $route Route data
     * @return bool True if accessible
     */
    private function isRouteAccessible(array $route): bool {
        // Check role restrictions
        if (!empty($route['allowed_roles'])) {
            if (!$this->user || !in_array($this->user['role_name'] ?? '', $route['allowed_roles'])) {
                return false;
            }
        }
        
        // Check permission
        $permission = $route['module'] . '.' . $route['action'];
        return $this->hasPermission($permission);
    }
    
    /**
     * Generate navigation menu HTML
     * @param string $activeRoute Current active route
     * @return string HTML navigation menu
     */
    public function generateNavigationMenu(string $activeRoute = ''): string {
        $accessibleRoutes = $this->getAccessibleRoutes();
        $html = '<nav class="bg-white shadow-lg">';
        $html .= '<div class="max-w-7xl mx-auto px-4">';
        $html .= '<div class="flex justify-between h-16">';
        
        // Logo/Brand
        $html .= '<div class="flex items-center">';
        $html .= '<a href="' . $this->basePath . '/dashboard" class="text-xl font-bold text-gray-800">TNP Portal</a>';
        $html .= '</div>';
        
        // Navigation Links
        $html .= '<div class="hidden md:flex items-center space-x-4">';
        
        foreach ($accessibleRoutes as $name => $route) {
            if ($name === 'logout') continue; // Handle logout separately
            
            $isActive = ($activeRoute === $name) ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-700 hover:text-white';
            $html .= '<a href="' . $this->basePath . $route['path'] . '" ';
            $html .= 'class="px-3 py-2 rounded-md text-sm font-medium ' . $isActive . '">';
            $html .= '<i class="' . $route['icon'] . ' mr-2"></i>' . $route['title'];
            $html .= '</a>';
        }
        
        // User menu
        if ($this->user) {
            $html .= '<div class="ml-3 relative">';
            $html .= '<div class="flex items-center">';
            $html .= '<span class="text-gray-700 mr-2">' . htmlspecialchars($this->user['user_name'] ?? 'User') . '</span>';
            $html .= '<a href="' . $this->basePath . '/logout" class="text-gray-600 hover:text-gray-800">';
            $html .= '<i class="fas fa-sign-out-alt"></i> Logout</a>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Generate sidebar menu HTML
     * @param string $activeRoute Current active route
     * @return string HTML sidebar menu
     */
    public function generateSidebarMenu(string $activeRoute = ''): string {
        $accessibleRoutes = $this->getAccessibleRoutes();
        $html = '<aside class="w-64 bg-gray-800 min-h-screen">';
        $html .= '<div class="p-4">';
        $html .= '<h2 class="text-white text-lg font-semibold mb-4">Navigation</h2>';
        $html .= '<nav class="space-y-2">';
        
        foreach ($accessibleRoutes as $name => $route) {
            if ($name === 'logout') continue; // Handle logout separately
            
            $isActive = ($activeRoute === $name) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
            $html .= '<a href="' . $this->basePath . $route['path'] . '" ';
            $html .= 'class="flex items-center px-4 py-2 rounded-md text-sm font-medium ' . $isActive . '">';
            $html .= '<i class="' . $route['icon'] . ' mr-3 w-5"></i>' . $route['title'];
            $html .= '</a>';
        }
        
        $html .= '</nav>';
        $html .= '</div>';
        $html .= '</aside>';
        
        return $html;
    }
    
    /**
     * Generate breadcrumb HTML
     * @param string $activeRoute Current active route
     * @return string HTML breadcrumb
     */
    public function generateBreadcrumb(string $activeRoute = ''): string {
        if (!isset($this->routes[$activeRoute])) {
            return '';
        }
        
        $route = $this->routes[$activeRoute];
        $html = '<nav class="flex" aria-label="Breadcrumb">';
        $html .= '<ol class="inline-flex items-center space-x-1 md:space-x-3">';
        $html .= '<li class="inline-flex items-center">';
        $html .= '<a href="' . $this->basePath . '/dashboard" class="text-gray-700 hover:text-gray-900">';
        $html .= '<i class="fas fa-home mr-2"></i>Home</a>';
        $html .= '</li>';
        $html .= '<li>';
        $html .= '<div class="flex items-center">';
        $html .= '<i class="fas fa-chevron-right text-gray-400 mx-2"></i>';
        $html .= '<span class="text-gray-500">' . $route['title'] . '</span>';
        $html .= '</div>';
        $html .= '</li>';
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Redirect to route if accessible
     * @param string $routeName Route name
     * @param string $fallbackRoute Fallback route if not accessible
     */
    public function redirectToRoute(string $routeName, string $fallbackRoute = '/dashboard'): void {
        if (isset($this->routes[$routeName]) && $this->isRouteAccessible($this->routes[$routeName])) {
            header('Location: ' . $this->basePath . $this->routes[$routeName]['path']);
        } else {
            header('Location: ' . $this->basePath . $fallbackRoute);
        }
        exit;
    }
    
    /**
     * Get route data by name
     * @param string $routeName Route name
     * @return array|null Route data or null if not found
     */
    public function getRoute(string $routeName): ?array {
        return $this->routes[$routeName] ?? null;
    }
    
    /**
     * Check if current user can access a specific route
     * @param string $routeName Route name
     * @return bool True if accessible
     */
    public function canAccessRoute(string $routeName): bool {
        if (!isset($this->routes[$routeName])) {
            return false;
        }
        
        return $this->isRouteAccessible($this->routes[$routeName]);
    }
    
    /**
     * Get user's role name
     * @return string Role name
     */
    public function getUserRole(): string {
        return $this->user['role_name'] ?? 'guest';
    }
    
    /**
     * Get user's permissions as array
     * @return array User permissions
     */
    public function getUserPermissions(): array {
        return $this->userPermissions;
    }
} 