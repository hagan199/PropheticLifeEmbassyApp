import api from "./index";

/**
 * Authentication API endpoints
 */
export const authApi = {
  /**
   * Login with phone and password
   * @param {string} phone - Phone number with country code (e.g., +233241234567)
   * @param {string} password - User password
   * @returns {Promise} API response
   */
  login(phone, password) {
    return api.post("/auth/login", { phone, password });
  },

  /**
   * Logout current user
   * @returns {Promise} API response
   */
  logout() {
    return api.post("/auth/logout");
  },

  /**
   * Get current authenticated user
   * @returns {Promise} API response with user data
   */
  getUser() {
    return api.get("/auth/user");
  },

  /**
   * Request password reset
   * @param {string} phone - Phone number to send reset code
   * @returns {Promise} API response
   */
  forgotPassword(phone) {
    return api.post("/auth/forgot-password", { phone });
  },

  /**
   * Reset password with code
   * @param {string} resetToken - Token from forgot password response
   * @param {string} code - 6-digit reset code
   * @param {string} password - New password
   * @param {string} passwordConfirmation - Password confirmation
   * @returns {Promise} API response
   */
  resetPassword(resetToken, code, password, passwordConfirmation) {
    return api.post("/auth/reset-password", {
      reset_token: resetToken,
      code,
      password,
      password_confirmation: passwordConfirmation,
    });
  },

  /**
   * Change password for authenticated user
   * @param {string} currentPassword - Current password
   * @param {string} password - New password
   * @param {string} passwordConfirmation - Password confirmation
   * @returns {Promise} API response
   */
  changePassword(currentPassword, password, passwordConfirmation) {
    return api.post("/auth/change-password", {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    });
  },

  /**
   * Update user profile
   * @param {Object} data - Profile data (name, email)
   * @returns {Promise} API response
   */
  updateProfile(data) {
    return api.put("/auth/profile", data);
  },
};

export default authApi;
