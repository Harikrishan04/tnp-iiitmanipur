/**
 * Frontend Router for TNP Portal
 * Handles client-side navigation and permission-based routing
 */
class FrontendRouter {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
        this.currentRoute = '';
        this.user = null;
        this.routes = {};
        this.navigationContainer = null;
        this.contentContainer = null;
        this.init();
    }
    
    /**
     * Initialize the router
     */
    async init() {
        // Set up containers
        this.navigationContainer = document.getElementById('navigation-container');
        this.contentContainer = document.getElementById('content-container');
        
        // Load user data and routes
        await this.loadUserData();
        await this.loadRoutes();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Handle initial route
        this.handleRoute();
    }
    
    /**
     * Load user data from backend
     */
    async loadUserData() {
        try {
            const response = await fetch(`${this.baseUrl}/dataRouting/api/router_handler.php?action=permissions`);
            const data = await response.json();
            
            if (data.success) {
                this.user = data.user_role;
                this.permissions = data.permissions;
            } else {
                console.error('Failed to load user data:', data.error);
                this.redirectToLogin();
            }
        } catch (error) {
            console.error('Error loading user data:', error);
            this.redirectToLogin();
        }
    }
    
    /**
     * Load accessible routes from backend
     */
    async loadRoutes() {
        try {
            const response = await fetch(`${this.baseUrl}/dataRouting/api/router_handler.php?action=routes`);
            const data = await response.json();
            
            if (data.success) {
                this.routes = data.routes;
                this.renderNavigation();
            } else {
                console.error('Failed to load routes:', data.error);
            }
        } catch (error) {
            console.error('Error loading routes:', error);
        }
    }
    
    /**
     * Set up event listeners for navigation
     */
    setupEventListeners() {
        // Handle browser back/forward buttons
        window.addEventListener('popstate', (event) => {
            this.handleRoute();
        });
        
        // Handle navigation clicks
        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-route]')) {
                event.preventDefault();
                const route = event.target.getAttribute('data-route');
                this.navigateTo(route);
            }
        });
    }
    
    /**
     * Handle current route
     */
    handleRoute() {
        const path = window.location.pathname;
        const routeName = this.getRouteNameFromPath(path);
        
        if (routeName && this.routes[routeName]) {
            this.currentRoute = routeName;
            this.loadPageContent(routeName);
            this.updateNavigation();
        } else {
            // Redirect to dashboard if route not found
            this.navigateTo('dashboard');
        }
    }
    
    /**
     * Get route name from path
     * @param {string} path URL path
     * @returns {string} Route name
     */
    getRouteNameFromPath(path) {
        // Remove base URL from path
        const cleanPath = path.replace(this.baseUrl, '');
        
        // Find matching route
        for (const [name, route] of Object.entries(this.routes)) {
            if (route.path === cleanPath) {
                return name;
            }
        }
        
        return null;
    }
    
    /**
     * Navigate to a specific route
     * @param {string} routeName Route name
     */
    navigateTo(routeName) {
        if (!this.routes[routeName]) {
            console.error('Route not found:', routeName);
            return;
        }
        
        const route = this.routes[routeName];
        this.currentRoute = routeName;
        
        // Update URL
        const newUrl = this.baseUrl + route.path;
        window.history.pushState({ route: routeName }, route.title, newUrl);
        
        // Load content
        this.loadPageContent(routeName);
        this.updateNavigation();
    }
    
    /**
     * Load page content for a route
     * @param {string} routeName Route name
     */
    async loadPageContent(routeName) {
        const route = this.routes[routeName];
        
        if (!route) {
            this.showError('Route not found');
            return;
        }
        
        // Check access permission
        const canAccess = await this.checkRouteAccess(routeName);
        if (!canAccess) {
            this.showError('Access denied');
            return;
        }
        
        // Show loading state
        this.showLoading();
        
        try {
            // Load page content based on route
            const content = await this.getPageContent(routeName);
            this.renderContent(content);
        } catch (error) {
            console.error('Error loading page content:', error);
            this.showError('Failed to load page content');
        }
    }
    
    /**
     * Check if user can access a route
     * @param {string} routeName Route name
     * @returns {boolean} True if accessible
     */
    async checkRouteAccess(routeName) {
        try {
            const response = await fetch(`${this.baseUrl}/dataRouting/api/router_handler.php?action=check_access&route=${routeName}`);
            const data = await response.json();
            return data.success && data.can_access;
        } catch (error) {
            console.error('Error checking route access:', error);
            return false;
        }
    }
    
    /**
     * Get page content for a route
     * @param {string} routeName Route name
     * @returns {string} HTML content
     */
    async getPageContent(routeName) {
        // This would typically load content from your backend
        // For now, return placeholder content
        const route = this.routes[routeName];
        
        return `
            <div class="container mx-auto px-4 py-8">
                <div class="mb-6">
                    ${this.generateBreadcrumb(routeName)}
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4">
                        <i class="${route.icon} mr-2"></i>${route.title}
                    </h1>
                    
                    <div class="text-gray-600">
                        <p>Welcome to the ${route.title} page.</p>
                        <p class="mt-4">This page is accessible to users with ${route.module}.${route.action} permission.</p>
                        
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold mb-2">Route Information:</h3>
                            <ul class="text-sm space-y-1">
                                <li><strong>Path:</strong> ${route.path}</li>
                                <li><strong>Module:</strong> ${route.module}</li>
                                <li><strong>Action:</strong> ${route.action}</li>
                                <li><strong>Allowed Roles:</strong> ${route.allowed_roles.length > 0 ? route.allowed_roles.join(', ') : 'All roles'}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Render navigation menu
     */
    async renderNavigation() {
        if (!this.navigationContainer) return;
        
        try {
            const response = await fetch(`${this.baseUrl}/dataRouting/api/router_handler.php?action=navigation&route=${this.currentRoute}&type=nav`);
            const data = await response.json();
            
            if (data.success) {
                this.navigationContainer.innerHTML = data.html;
            }
        } catch (error) {
            console.error('Error rendering navigation:', error);
        }
    }
    
    /**
     * Update navigation with current route
     */
    updateNavigation() {
        this.renderNavigation();
    }
    
    /**
     * Generate breadcrumb HTML
     * @param {string} routeName Route name
     * @returns {string} Breadcrumb HTML
     */
    generateBreadcrumb(routeName) {
        const route = this.routes[routeName];
        if (!route) return '';
        
        return `
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="#" data-route="dashboard" class="text-gray-700 hover:text-gray-900">
                            <i class="fas fa-home mr-2"></i>Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-gray-500">${route.title}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        `;
    }
    
    /**
     * Render content in the main container
     * @param {string} content HTML content
     */
    renderContent(content) {
        if (this.contentContainer) {
            this.contentContainer.innerHTML = content;
        }
    }
    
    /**
     * Show loading state
     */
    showLoading() {
        if (this.contentContainer) {
            this.contentContainer.innerHTML = `
                <div class="flex items-center justify-center h-64">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600">Loading...</span>
                </div>
            `;
        }
    }
    
    /**
     * Show error message
     * @param {string} message Error message
     */
    showError(message) {
        if (this.contentContainer) {
            this.contentContainer.innerHTML = `
                <div class="container mx-auto px-4 py-8">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong class="font-bold">Error:</strong>
                        <span class="block sm:inline">${message}</span>
                    </div>
                </div>
            `;
        }
    }
    
    /**
     * Redirect to login page
     */
    redirectToLogin() {
        window.location.href = this.baseUrl + '/login';
    }
    
    /**
     * Get current user role
     * @returns {string} User role
     */
    getUserRole() {
        return this.user;
    }
    
    /**
     * Check if user has permission
     * @param {string} permission Permission name
     * @returns {boolean} True if user has permission
     */
    hasPermission(permission) {
        return this.permissions.some(p => p.name === permission);
    }
    
    /**
     * Check if user has module access
     * @param {string} module Module name
     * @returns {boolean} True if user has module access
     */
    hasModuleAccess(module) {
        return this.permissions.some(p => p.module === module);
    }
}

// Initialize router when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Get base URL from current location
    const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
    
    // Initialize the router
    window.tnpRouter = new FrontendRouter(baseUrl);
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FrontendRouter;
} 