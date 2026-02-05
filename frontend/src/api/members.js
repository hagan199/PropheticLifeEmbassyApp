import api from "./index";

/**
 * Members API endpoints
 */
export const membersApi = {
  /**
   * Get paginated list of members
   * @param {Object} params - Query parameters (page, per_page, search, tier, department_id)
   * @returns {Promise} API response with members list
   */
  getAll(params = {}) {
    return api.get("/members", { params });
  },

  /**
   * Search members
   * @param {Object} params - Search parameters (query, tier, department_id)
   * @returns {Promise} API response with matching members
   */
  search(params = {}) {
    return api.get("/members/search", { params });
  },

  /**
   * Get tier history for a member
   * @param {number|string} id - Member ID
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with tier history
   */
  getTierHistory(id, params = {}) {
    return api.get(`/members/${id}/tier-history`, { params });
  },

  /**
   * Update member's tier
   * @param {number|string} id - Member ID
   * @param {Object} data - Tier update data (tier, reason)
   * @returns {Promise} API response
   */
  updateTier(id, data) {
    return api.post(`/members/${id}/update-tier`, data);
  },
};

export default membersApi;
