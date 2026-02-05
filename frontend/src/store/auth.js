import { defineStore } from "pinia";
import { authApi } from "../api/auth";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: JSON.parse(localStorage.getItem("auth_user")) || null,
    token: localStorage.getItem("auth_token") || null,
    isLoading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userRole: (state) => state.user?.role || null,
    userName: (state) => state.user?.name || "",
    userInitials: (state) => {
      const name = state.user?.name || "U";
      return name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .substring(0, 2)
        .toUpperCase();
    },
    hasRole: (state) => (roles) => {
      if (!state.user?.role) return false;
      if (typeof roles === "string") return state.user.role === roles;
      return roles.includes(state.user.role);
    },
  },

  actions: {
    /**
     * Sign in with phone and password
     * @param {string} phone - Phone number
     * @param {string} password - Password
     * @returns {Object} Result with success or message
     */
    async signIn(phone, password) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await authApi.login(phone, password);
        const data = response.data;

        if (data.success) {
          this.setAuthData(data.data.token, data.data.user);
          return { success: true };
        }

        return { success: false, message: data.message || "Login failed" };
      } catch (error) {
        const message =
          error.response?.data?.message ||
          "Connection error. Please try again.";
        this.error = message;
        return { success: false, message };
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Sign out current user
     */
    async signOut() {
      try {
        if (this.token) {
          await authApi.logout();
        }
      } catch (error) {
        // Ignore logout API errors
        console.warn("Logout API error:", error);
      } finally {
        this.clearAuthData();
      }
    },

    /**
     * Fetch current user data
     * @returns {Object} Result with success or message
     */
    async fetchUser() {
      if (!this.token) {
        return { success: false, message: "Not authenticated" };
      }

      try {
        const response = await authApi.getUser();
        if (response.data.success) {
          this.user = response.data.data.user;
          localStorage.setItem("auth_user", JSON.stringify(this.user));
          return { success: true };
        }
        return { success: false };
      } catch (error) {
        this.clearAuthData();
        return { success: false, message: "Session expired" };
      }
    },

    /**
     * Initialize auth state from localStorage
     */
    initAuth() {
      const token = localStorage.getItem("auth_token");
      const user = localStorage.getItem("auth_user");

      if (token && user) {
        this.token = token;
        this.user = JSON.parse(user);
      }
    },

    /**
     * Set authentication data
     */
    setAuthData(token, user) {
      this.token = token;
      this.user = user;
      localStorage.setItem("auth_token", token);
      localStorage.setItem("auth_user", JSON.stringify(user));
    },

    /**
     * Clear authentication data
     */
    clearAuthData() {
      this.user = null;
      this.token = null;
      this.error = null;
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");
    },
  },
});
