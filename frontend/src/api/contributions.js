import api from "./index";

/**
 * Contributions API endpoints
 */
export const contributionsApi = {
  /**
   * Get paginated list of contributions
   * @param {Object} params - Query parameters (page, per_page, type, partner_id, date_from, date_to)
   * @returns {Promise} API response with contributions list
   */
  getAll(params = {}) {
    return api.get("/contributions", { params });
  },

  /**
   * Get a single contribution by ID
   * @param {number|string} id - Contribution ID
   * @returns {Promise} API response with contribution data
   */
  get(id) {
    return api.get(`/contributions/${id}`);
  },

  /**
   * Create a new contribution
   * @param {Object} data - Contribution data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/contributions", data);
  },

  /**
   * Update an existing contribution
   * @param {number|string} id - Contribution ID
   * @param {Object} data - Updated contribution data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/contributions/${id}`, data);
  },

  /**
   * Delete a contribution
   * @param {number|string} id - Contribution ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/contributions/${id}`);
  },

  /**
   * Get contributions for a specific partner
   * @param {number|string} partnerId - Partner ID
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with partner's contributions
   */
  getByPartner(partnerId, params = {}) {
    return api.get(`/contributions/partner/${partnerId}`, { params });
  },
};

export default contributionsApi;
