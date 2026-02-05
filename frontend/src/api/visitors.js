import api from "./index";

/**
 * Visitors API endpoints
 */
export const visitorsApi = {
  /**
   * Get paginated list of visitors
   * @param {Object} params - Query parameters (page, per_page, search, date, status)
   * @returns {Promise} API response with visitors list
   */
  getAll(params = {}) {
    return api.get("/visitors", { params });
  },

  /**
   * Get a single visitor by ID
   * @param {number|string} id - Visitor ID
   * @returns {Promise} API response with visitor data
   */
  get(id) {
    return api.get(`/visitors/${id}`);
  },

  /**
   * Create a new visitor
   * @param {Object} data - Visitor data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/visitors", data);
  },

  /**
   * Update an existing visitor
   * @param {number|string} id - Visitor ID
   * @param {Object} data - Updated visitor data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/visitors/${id}`, data);
  },

  /**
   * Delete a visitor
   * @param {number|string} id - Visitor ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/visitors/${id}`);
  },

  /**
   * Get follow-ups for a specific visitor
   * @param {number|string} id - Visitor ID
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with visitor's follow-ups
   */
  getFollowUps(id, params = {}) {
    return api.get(`/visitors/${id}/follow-ups`, { params });
  },
};

export default visitorsApi;
