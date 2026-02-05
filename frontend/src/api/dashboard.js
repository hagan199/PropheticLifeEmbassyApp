import api from "./index";

/**
 * Dashboard API endpoints
 */
export const dashboardApi = {
  /**
   * Get dashboard statistics
   * @param {Object} params - Query parameters (range, start_date, end_date)
   * @returns {Promise} API response with stats data
   */
  getStats(params = {}) {
    return api.get("/dashboard/stats", { params });
  },

  /**
   * Get dashboard analytics
   * @param {Object} params - Query parameters (range, start_date, end_date, type)
   * @returns {Promise} API response with analytics data
   */
  getAnalytics(params = {}) {
    return api.get("/dashboard/analytics", { params });
  },
};

export default dashboardApi;
