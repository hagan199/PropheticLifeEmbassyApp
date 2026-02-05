import api from "./index";

/**
 * Broadcasts API endpoints
 */
export const broadcastsApi = {
  /**
   * Get paginated list of broadcasts
   * @param {Object} params - Query parameters (page, per_page, type, status)
   * @returns {Promise} API response with broadcasts list
   */
  getAll(params = {}) {
    return api.get("/broadcasts", { params });
  },

  /**
   * Get a single broadcast by ID
   * @param {number|string} id - Broadcast ID
   * @returns {Promise} API response with broadcast data
   */
  get(id) {
    return api.get(`/broadcasts/${id}`);
  },

  /**
   * Create a new broadcast
   * @param {Object} data - Broadcast data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/broadcasts", data);
  },

  /**
   * Delete a broadcast
   * @param {number|string} id - Broadcast ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/broadcasts/${id}`);
  },

  /**
   * Get delivery status for a broadcast
   * @param {number|string} id - Broadcast ID
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with delivery details
   */
  getDeliveries(id, params = {}) {
    return api.get(`/broadcasts/${id}/deliveries`, { params });
  },
};

export default broadcastsApi;
