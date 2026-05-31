/**
 * config.js — Application-wide configuration constants.
 *
 * Auto-detects environment:
 *   - Local dev (localhost with subfolder): uses /tnp@iiitmanipur prefix
 *   - Production (Railway / any domain root): uses root paths
 */

(function () {
    // Detect if running locally in a subfolder or on a real domain root
    const isLocalDev = window.location.hostname === 'localhost' ||
                       window.location.hostname === '127.0.0.1';

    const base = isLocalDev ? '/tnp@iiitmanipur' : '';

    window.APP_CONFIG = Object.freeze({
        // API base URL — no trailing slash
        API_BASE: base + '/api',

        // Frontend base URL — no trailing slash
        FRONTEND_BASE: base + '/frontend',

        // JWT storage key in localStorage
        TOKEN_KEY: 'tnp_auth_token',

        // User data storage key
        USER_KEY: 'tnp_user',

        // OTP resend cooldown in seconds
        OTP_COOLDOWN: 60,

        // Roles and their dashboard paths
        DASHBOARDS: {
            student:     base + '/frontend/student/profile.html',
            recruiter:   base + '/frontend/recruiter/profile.html',
            coordinator: base + '/frontend/coordinator/verify-students.html',
            admin:       base + '/frontend/admin/dashboard.html',
        },

        // Login page path
        LOGIN_PAGE: base + '/frontend/login.html',

        // App name for display
        APP_NAME: 'IIIT Manipur Placement Portal',
    });
})();
