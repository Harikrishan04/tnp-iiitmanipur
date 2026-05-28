/**
 * config.js — Application-wide configuration constants.
 *
 * Single source of truth for API URLs and app settings.
 * Change API_BASE when deploying to production.
 */

const APP_CONFIG = Object.freeze({
    // API base URL — no trailing slash
    API_BASE: '/tnp@iiitmanipur/api',

    // Frontend base URL — no trailing slash
    FRONTEND_BASE: '/tnp@iiitmanipur/frontend',

    // JWT storage key in localStorage
    TOKEN_KEY: 'tnp_auth_token',

    // User data storage key
    USER_KEY: 'tnp_user',

    // OTP resend cooldown in seconds
    OTP_COOLDOWN: 60,

    // Roles and their dashboard paths
    DASHBOARDS: {
        student:     '/tnp@iiitmanipur/frontend/student/profile.html',
        recruiter:   '/tnp@iiitmanipur/frontend/recruiter/profile.html',
        coordinator: '/tnp@iiitmanipur/frontend/coordinator/verify-students.html',
        admin:       '/tnp@iiitmanipur/frontend/admin/dashboard.html',
    },

    // Login page path
    LOGIN_PAGE: '/tnp@iiitmanipur/frontend/login.html',

    // App name for display
    APP_NAME: 'IIIT Manipur Placement Portal',
});
