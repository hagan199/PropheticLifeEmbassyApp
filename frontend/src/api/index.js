import axios from "axios";

// Create axios instance with base configuration
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  withCredentials: true, // Important for Sanctum cookies
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// Response interceptor to handle errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle 401 Unauthorized - token expired or invalid
    if (error.response?.status === 401) {
      // Clear stored auth data
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");

      // Redirect to login if not already there
      if (window.location.pathname !== "/login") {
        window.location.href = "/login";
      }
    }

    return Promise.reject(error);
  },
);

export default api;

// Export all API modules for convenient imports
export { authApi } from "./auth";
export { usersApi } from "./users";
export { dashboardApi } from "./dashboard";
export { attendanceApi } from "./attendance";
export { visitorsApi } from "./visitors";
export { followUpsApi } from "./followUps";
export { contributionsApi } from "./contributions";
export { expensesApi } from "./expenses";
export { departmentsApi } from "./departments";
export { broadcastsApi } from "./broadcasts";
export { auditLogsApi } from "./auditLogs";
export { membersApi } from "./members";
export { rolesApi } from "./roles";
export { financeApi } from "./finance";
