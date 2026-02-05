import api from "./index";

/**
 * Finance Reports API endpoints
 */
export const financeApi = {
  /**
   * Get monthly finance report
   * @param {Object} params - Query parameters (year, month)
   * @returns {Promise} API response with monthly report data
   */
  getMonthlyReport(params = {}) {
    return api.get("/finance/reports/monthly", { params });
  },

  /**
   * Export finance report
   * @param {Object} params - Query parameters (format, date_from, date_to, type)
   * @returns {Promise} API response with export data/file
   */
  exportReport(params = {}) {
    return api.get("/finance/reports/export", { 
      params,
      responseType: "blob"
    });
  },
};

export default financeApi;
