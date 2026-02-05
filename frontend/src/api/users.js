import api from "./index";

/**
 * Users API endpoints
 */
export const usersApi = {
  /**
   * Get paginated list of users
   * @param {Object} params - Query parameters (page, per_page, search, role, status)
   * @returns {Promise} API response with users list
   */
  getAll(params = {}) {
    return api.get("/users", { params });
  },

  /**
   * Get a single user by ID
   * @param {number|string} id - User ID
   * @returns {Promise} API response with user data
   */
  get(id) {
    return api.get(`/users/${id}`);
  },

  /**
   * Create a new user
   * @param {Object} data - User data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/users", data);
  },

  /**
   * Update an existing user
   * @param {number|string} id - User ID
   * @param {Object} data - Updated user data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/users/${id}`, data);
  },

  /**
   * Delete a user
   * @param {number|string} id - User ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/users/${id}`);
  },

  /**
   * Deactivate a user
   * @param {number|string} id - User ID
   * @returns {Promise} API response
   */
  deactivate(id, payload = {}) {
    return api.post(`/users/${id}/deactivate`, payload);
  },

  /**
   * Reactivate a user
   * @param {number|string} id - User ID
   * @returns {Promise} API response
   */
  reactivate(id) {
    return api.post(`/users/${id}/reactivate`);
  },
};

export default usersApi;
