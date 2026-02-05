import api from "./index";

/**
 * Attendance API endpoints
 */
export const attendanceApi = {
  /**
   * Get paginated list of attendance records
   * @param {Object} params - Query parameters (page, per_page, date, service_type, status)
   * @returns {Promise} API response with attendance list
   */
  getAll(params = {}) {
    return api.get("/attendance", { params });
  },

  /**
   * Get a single attendance record by ID
   * @param {number|string} id - Attendance ID
   * @returns {Promise} API response with attendance data
   */
  get(id) {
    return api.get(`/attendance/${id}`);
  },

  /**
   * Create a new attendance record
   * @param {Object} data - Attendance data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/attendance", data);
  },

  /**
   * Create a ministry unit attendance record
   * @param {Object} data - Attendance data (unit, service, date, time, member_id, member_name, present)
   * @returns {Promise} API response
   */
  createUnitAttendance(data) {
    return api.post("/attendance/unit", data);
  },

  /**
   * Update an existing attendance record
   * @param {number|string} id - Attendance ID
   * @param {Object} data - Updated attendance data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/attendance/${id}`, data);
  },

  /**
   * Delete an attendance record
   * @param {number|string} id - Attendance ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/attendance/${id}`);
  },

  /**
   * Get pending attendance approvals (Admin)
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with pending approvals
   */
  getPendingApprovals(params = {}) {
    return api.get("/attendance/approvals/pending", { params });
  },

  /**
   * Bulk approve attendance records
   * @param {Array} ids - Array of attendance IDs to approve
   * @returns {Promise} API response
   */
  bulkApprove(ids) {
    return api.post("/attendance/approvals/bulk-approve", { ids });
  },

  /**
   * Bulk reject attendance records
   * @param {Array} ids - Array of attendance IDs to reject
   * @param {string} reason - Rejection reason
   * @returns {Promise} API response
   */
  bulkReject(ids, reason) {
    return api.post("/attendance/approvals/bulk-reject", { ids, reason });
  },

  /**
   * Approve a single attendance record
   * @param {number|string} id - Attendance ID
   * @returns {Promise} API response
   */
  approve(id) {
    return api.post(`/attendance/${id}/approve`);
  },

  /**
   * Reject a single attendance record
   * @param {number|string} id - Attendance ID
   * @param {string} reason - Rejection reason
   * @returns {Promise} API response
   */
  reject(id, reason) {
    return api.post(`/attendance/${id}/reject`, { reason });
  },

  /**
   * Get current user's attendance submissions (Usher)
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with user's submissions
   */
  getMySubmissions(params = {}) {
    return api.get("/attendance/my-submissions", { params });
  },
};

export default attendanceApi;
