import api from "./index";

/**
 * Departments API endpoints
 */
export const departmentsApi = {
  /**
   * Get paginated list of departments
   * @param {Object} params - Query parameters (page, per_page, search)
   * @returns {Promise} API response with departments list
   */
  getAll(params = {}) {
    return api.get("/departments", { params });
  },

  /**
   * Get a single department by ID
   * @param {number|string} id - Department ID
   * @returns {Promise} API response with department data
   */
  get(id) {
    return api.get(`/departments/${id}`);
  },

  /**
   * Create a new department
   * @param {Object} data - Department data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/departments", data);
  },

  /**
   * Update an existing department
   * @param {number|string} id - Department ID
   * @param {Object} data - Updated department data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/departments/${id}`, data);
  },

  /**
   * Delete a department
   * @param {number|string} id - Department ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/departments/${id}`);
  },

  /**
   * Get members of a department
   * @param {number|string} id - Department ID
   * @param {Object} params - Query parameters
   * @returns {Promise} API response with department members
   */
  getMembers(id, params = {}) {
    return api.get(`/departments/${id}/members`, { params });
  },

  /**
   * Add a member to a department
   * @param {number|string} id - Department ID
   * @param {Object} data - Member data (user_id, role)
   * @returns {Promise} API response
   */
  addMember(id, data) {
    return api.post(`/departments/${id}/members`, data);
  },

  /**
   * Remove a member from a department
   * @param {number|string} id - Department ID
   * @param {number|string} memberId - Member ID
   * @returns {Promise} API response
   */
  removeMember(id, memberId) {
    return api.delete(`/departments/${id}/members/${memberId}`);
  },

  /**
   * Get current user's department info (Department Leader)
   * @returns {Promise} API response with department data
   */
  getMyDepartment() {
    return api.get("/departments/my/info");
  },
};

export default departmentsApi;
