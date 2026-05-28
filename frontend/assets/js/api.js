/**
 * api.js — Generic HTTP client for communicating with the REST API.
 *
 * Automatically attaches JWT token from localStorage.
 * Handles JSON parsing, error normalization, and token expiry detection.
 *
 * Usage:
 *   const { data } = await Api.get('/students/me');
 *   const { data } = await Api.post('/auth/login', { email, role });
 *   const { data } = await Api.put('/students/me', { name: 'John' });
 */

class Api {
    /**
     * Make an HTTP request to the API.
     *
     * @param {string} endpoint  API path (e.g., '/auth/login')
     * @param {object} options   Fetch options override
     * @returns {Promise<object>} Parsed JSON response
     */
    static async request(endpoint, options = {}) {
        const url = `${APP_CONFIG.API_BASE}${endpoint}`;

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers,
        };

        // Attach JWT token if available
        const token = Auth.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers,
            });

            const json = await response.json();

            // Token expired — redirect to login
            if (response.status === 401) {
                Auth.clearSession();
                window.location.href = APP_CONFIG.LOGIN_PAGE;
                return json;
            }

            // Attach HTTP status to the response object for caller inspection
            json._httpStatus = response.status;

            return json;
        } catch (error) {
            console.error(`API request failed: ${endpoint}`, error);
            return {
                status: 'error',
                message: 'Network error. Please check your connection.',
                _httpStatus: 0,
            };
        }
    }

    /**
     * GET request.
     *
     * @param {string} endpoint API path
     * @param {object} params   Query parameters (optional)
     */
    static async get(endpoint, params = {}) {
        const query = new URLSearchParams(params).toString();
        const url = query ? `${endpoint}?${query}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    /**
     * POST request.
     *
     * @param {string} endpoint API path
     * @param {object} body     Request body
     */
    static async post(endpoint, body = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body),
        });
    }

    /**
     * PUT request.
     *
     * @param {string} endpoint API path
     * @param {object} body     Request body
     */
    static async put(endpoint, body = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body),
        });
    }

    /**
     * DELETE request.
     *
     * @param {string} endpoint API path
     */
    static async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}
