import api from "./index";

/**
 * Follow-ups API endpoints
 */
export const followUpsApi = {
  /**
   * Get paginated list of follow-ups
   * @param {Object} params - Query parameters (page, per_page, status, assigned_to, visitor_id)
   * @returns {Promise} API response with follow-ups list
   */
  getAll(params = {}) {
    return api.get("/follow-ups", { params });
  },

  /**
   * Get a single follow-up by ID
   * @param {number|string} id - Follow-up ID
   * @returns {Promise} API response with follow-up data
   */
  get(id) {
    return api.get(`/follow-ups/${id}`);
  },

  /**
   * Create a new follow-up
   * @param {Object} data - Follow-up data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/follow-ups", data);
  },

  /**
   * Update an existing follow-up
   * @param {number|string} id - Follow-up ID
   * @param {Object} data - Updated follow-up data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/follow-ups/${id}`, data);
  },

  /**
   * Delete a follow-up
   * @param {number|string} id - Follow-up ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/follow-ups/${id}`);
  },

  /**
   * Get list of due follow-ups
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with due follow-ups
   */
  getDue(params = {}) {
    return api.get("/follow-ups/due", { params });
  },
};

export default followUpsApi;
