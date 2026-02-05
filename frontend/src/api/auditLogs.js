import api from "./index";

/**
 * Audit Logs API endpoints
 */
export const auditLogsApi = {
  /**
   * Get paginated list of audit logs
   * @param {Object} params - Query parameters (page, per_page, user_id, action, entity_type, date_from, date_to)
   * @returns {Promise} API response with audit logs list
   */
  getAll(params = {}) {
    return api.get("/audit-logs", { params });
  },

  /**
   * Get a single audit log by ID
   * @param {number|string} id - Audit log ID
   * @returns {Promise} API response with audit log data
   */
  get(id) {
    return api.get(`/audit-logs/${id}`);
  },

  /**
   * Export audit logs
   * @param {Object} params - Query parameters for filtering export
   * @returns {Promise} API response with export data/file
   */
  export(params = {}) {
    return api.get("/audit-logs/export", {
      params,
      responseType: "blob",
    });
  },
};

export default auditLogsApi;
