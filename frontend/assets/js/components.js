/**
 * components.js — Shared UI components used across all dashboard pages.
 *
 * Provides: toast notifications, sidebar, header, loading overlay.
 * Each component renders itself into a designated container element.
 *
 * Usage:
 *   Toast.success('Profile updated!');
 *   Toast.error('Something went wrong.');
 *   Components.renderSidebar('student', 'profile');
 *   Components.renderHeader();
 */

// ─── Toast Notifications ─────────────────────────────────────────────────────

const Toast = {
    _container: null,

    _getContainer() {
        if (!this._container) {
            this._container = document.createElement('div');
            this._container.id = 'toast-container';
            this._container.className = 'fixed top-6 right-6 z-50 flex flex-col gap-3';
            document.body.appendChild(this._container);
        }
        return this._container;
    },

    _show(message, type = 'info', duration = 4000) {
        const container = this._getContainer();

        const icons = {
            success: '✓',
            error: '✕',
            info: 'ℹ',
            warning: '⚠',
        };

        const colors = {
            success: 'bg-emerald-50 text-emerald-800 border-emerald-200',
            error: 'bg-red-50 text-red-800 border-red-200',
            info: 'bg-blue-50 text-blue-800 border-blue-200',
            warning: 'bg-amber-50 text-amber-800 border-amber-200',
        };

        const toast = document.createElement('div');
        toast.className = `px-5 py-3.5 rounded-xl shadow-lg border flex items-center gap-3 text-sm font-medium max-w-sm
                           transform translate-x-full transition-transform duration-300 ease-out
                           ${colors[type] || colors.info}`;
        toast.innerHTML = `
            <span class="text-lg">${icons[type] || icons.info}</span>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(msg) { this._show(msg, 'success'); },
    error(msg)   { this._show(msg, 'error', 6000); },
    info(msg)    { this._show(msg, 'info'); },
    warning(msg) { this._show(msg, 'warning', 5000); },
};

// ─── Shared UI Components ────────────────────────────────────────────────────

const Components = {
    /**
     * Sidebar navigation items per role.
     */
    _sidebarItems: {
        student: [
            { id: 'profile',      label: 'My Profile',     icon: '👤', href: 'profile.html' },
            { id: 'jobs',          label: 'Job Openings',   icon: '💼', href: 'jobs.html' },
            { id: 'applications',  label: 'My Applications',icon: '📋', href: 'applications.html' },
        ],
        recruiter: [
            { id: 'profile',   label: 'Company Profile', icon: '🏢', href: 'profile.html' },
            { id: 'post-job',  label: 'Post a Job',      icon: '📝', href: 'post-job.html' },
            { id: 'my-jobs',   label: 'My Postings',     icon: '📊', href: 'my-jobs.html' },
        ],
        coordinator: [
            { id: 'verify-students',   label: 'Verify Students',   icon: '🎓', href: 'verify-students.html' },
            { id: 'verify-recruiters', label: 'Verify Recruiters',  icon: '🏢', href: 'verify-recruiters.html' },
            { id: 'verify-jobs',       label: 'Verify Jobs',        icon: '✅', href: 'verify-jobs.html' },
        ],
        admin: [
            { id: 'sessions',       label: 'Sessions',       icon: '📅', href: 'sessions.html' },
            { id: 'users',          label: 'Users',           icon: '👥', href: 'users.html' },
            { id: 'announcements',  label: 'Announcements',   icon: '📢', href: 'announcements.html' },
            { id: 'placements',     label: 'Placements',      icon: '🏆', href: 'placements.html' },
        ],
    },

    /**
     * Render the sidebar into #sidebar-container.
     *
     * @param {string} role       Current user role
     * @param {string} activePage Current page ID (matches sidebar item id)
     */
    renderSidebar(role, activePage) {
        const container = document.getElementById('sidebar-container');
        if (!container) return;

        const items = this._sidebarItems[role] || [];
        const user = Auth.getUser();

        container.innerHTML = `
            <div class="h-full flex flex-col bg-white border-r border-gray-100 w-64">
                <!-- Logo area -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <img src="${APP_CONFIG.FRONTEND_BASE}/assets/img/iiitm-logo.png"
                             alt="IIIT Manipur" class="w-10 h-10 rounded-full">
                        <div>
                            <h2 class="text-sm font-bold text-gray-800">TNP Portal</h2>
                            <p class="text-xs text-gray-500 capitalize">${role} Dashboard</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-1">
                    ${items.map(item => `
                        <a href="${item.href}" id="nav-${item.id}"
                           class="nav-link ${item.id === activePage ? 'active' : ''}">
                            <span class="text-lg">${item.icon}</span>
                            <span>${item.label}</span>
                        </a>
                    `).join('')}
                </nav>

                <!-- User footer -->
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-sm font-bold">
                            ${(user?.email?.[0] || '?').toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${user?.email || ''}</p>
                            <p class="text-xs text-gray-500 capitalize">${role}</p>
                        </div>
                    </div>
                    <button onclick="Auth.logout()" class="w-full text-left nav-link text-red-600 hover:bg-red-50 hover:text-red-700">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        `;
    },

    /**
     * Render the top header bar into #header-container.
     *
     * @param {string} title Page title
     */
    renderHeader(title = '') {
        const container = document.getElementById('header-container');
        if (!container) return;

        container.innerHTML = `
            <header class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800">${title}</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">${new Date().toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' })}</span>
                </div>
            </header>
        `;
    },

    /**
     * Show a full-page loading overlay.
     */
    showLoading() {
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'fixed inset-0 z-40 bg-white/80 flex items-center justify-center';
            overlay.innerHTML = '<div class="spinner w-8 h-8 text-brand-700"></div>';
            document.body.appendChild(overlay);
        }
        overlay.classList.remove('hidden');
    },

    /**
     * Hide the loading overlay.
     */
    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.add('hidden');
    },
};
