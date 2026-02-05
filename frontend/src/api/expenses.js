import api from "./index";

/**
 * Expenses API endpoints
 */
export const expensesApi = {
  /**
   * Get paginated list of expenses
   * @param {Object} params - Query parameters (page, per_page, type_id, status, date_from, date_to)
   * @returns {Promise} API response with expenses list
   */
  getAll(params = {}) {
    return api.get("/expenses", { params });
  },

  /**
   * Get a single expense by ID
   * @param {number|string} id - Expense ID
   * @returns {Promise} API response with expense data
   */
  get(id) {
    return api.get(`/expenses/${id}`);
  },

  /**
   * Create a new expense
   * @param {Object} data - Expense data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/expenses", data);
  },

  /**
   * Update an existing expense
   * @param {number|string} id - Expense ID
   * @param {Object} data - Updated expense data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/expenses/${id}`, data);
  },

  /**
   * Delete an expense
   * @param {number|string} id - Expense ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/expenses/${id}`);
  },

  /**
   * Approve an expense
   * @param {number|string} id - Expense ID
   * @returns {Promise} API response
   */
  approve(id) {
    return api.post(`/expenses/${id}/approve`);
  },

  /**
   * Reject an expense
   * @param {number|string} id - Expense ID
   * @param {string} reason - Rejection reason
   * @returns {Promise} API response
   */
  reject(id, reason) {
    return api.post(`/expenses/${id}/reject`, { reason });
  },

  /**
   * Get all expense types
   * @returns {Promise} API response with expense types
   */
  getTypes() {
    return api.get("/expenses/types/all");
  },

  /**
   * Create a new expense type
   * @param {Object} data - Expense type data
   * @returns {Promise} API response
   */
  createType(data) {
    return api.post("/expenses/types", data);
  },
};

export default expensesApi;
