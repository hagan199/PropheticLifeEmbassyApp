import api from "./index";

/**
 * Roles & Permissions API endpoints
 */
export const rolesApi = {
  /**
   * Get all roles
   * @returns {Promise} API response with roles list
   */
  getAll() {
    return api.get("/roles");
  },

  /**
   * Get permissions for a specific role
   * @param {string} role - Role name/slug
   * @returns {Promise} API response with role permissions
   */
  getPermissions(role) {
    return api.get(`/roles/${role}/permissions`);
  },
};

export default rolesApi;
