/**
 * auth.js — JWT session management and authentication helpers.
 *
 * Manages token storage in localStorage, session state,
 * and provides login/logout utilities.
 *
 * Usage:
 *   Auth.setSession(token, user);
 *   const user = Auth.getUser();
 *   Auth.logout();
 *   Auth.requireAuth();   // redirects to login if not authenticated
 */

class Auth {
    /**
     * Store JWT token in localStorage.
     */
    static setToken(token) {
        localStorage.setItem(APP_CONFIG.TOKEN_KEY, token);
    }

    /**
     * Get JWT token from localStorage.
     *
     * @returns {string|null}
     */
    static getToken() {
        return localStorage.getItem(APP_CONFIG.TOKEN_KEY);
    }

    /**
     * Store user data in localStorage.
     *
     * @param {object} user { sub, email, role }
     */
    static setUser(user) {
        localStorage.setItem(APP_CONFIG.USER_KEY, JSON.stringify(user));
    }

    /**
     * Get stored user data.
     *
     * @returns {object|null}
     */
    static getUser() {
        const data = localStorage.getItem(APP_CONFIG.USER_KEY);
        if (!data) return null;

        try {
            return JSON.parse(data);
        } catch {
            return null;
        }
    }

    /**
     * Set full session after successful OTP verification.
     *
     * @param {string} token JWT token string
     * @param {object} user  User data from API response
     */
    static setSession(token, user) {
        this.setToken(token);
        this.setUser(user);
    }

    /**
     * Clear all session data.
     */
    static clearSession() {
        localStorage.removeItem(APP_CONFIG.TOKEN_KEY);
        localStorage.removeItem(APP_CONFIG.USER_KEY);
    }

    /**
     * Check if user is authenticated (has a non-expired token).
     *
     * @returns {boolean}
     */
    static isAuthenticated() {
        const token = this.getToken();
        if (!token) return false;

        // Decode JWT payload to check expiry (without verifying signature — server does that)
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const now = Math.floor(Date.now() / 1000);
            return payload.exp > now;
        } catch {
            return false;
        }
    }

    /**
     * Get the role of the current user.
     *
     * @returns {string|null}
     */
    static getRole() {
        const user = this.getUser();
        return user?.role ?? null;
    }

    /**
     * Redirect to the appropriate dashboard based on user role.
     */
    static redirectToDashboard() {
        const role = this.getRole();
        const path = APP_CONFIG.DASHBOARDS[role] || APP_CONFIG.LOGIN_PAGE;
        window.location.href = path;
    }

    /**
     * Require authentication — redirect to login if not authenticated.
     * Call this at the top of every protected page's script.
     */
    static requireAuth() {
        if (!this.isAuthenticated()) {
            this.clearSession();
            window.location.href = APP_CONFIG.LOGIN_PAGE;
            return false;
        }
        return true;
    }

    /**
     * Require a specific role — redirect to login if role doesn't match.
     *
     * @param {string[]} allowedRoles Array of allowed role names
     */
    static requireRole(allowedRoles) {
        if (!this.requireAuth()) return false;

        const role = this.getRole();
        if (!allowedRoles.includes(role)) {
            window.location.href = APP_CONFIG.LOGIN_PAGE;
            return false;
        }
        return true;
    }

    /**
     * Logout — clear session and redirect to login.
     */
    static logout() {
        this.clearSession();
        window.location.href = APP_CONFIG.LOGIN_PAGE;
    }
}
