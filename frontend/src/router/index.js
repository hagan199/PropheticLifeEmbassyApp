import { createRouter, createWebHistory } from "vue-router";

// Auth
import SignIn from "../views/SignIn.vue";

// Core
import Home from "../views/Home.vue";
import Dashboard from "../views/Dashboard.vue";
import Settings from "../views/Settings.vue";
import Profile from "../views/Profile.vue";

// Admin
import Users from "../views/Users.vue";
import RolePermission from "../views/RolePermission.vue";
import AttendanceApprovals from "../views/AttendanceApprovals.vue";
import Broadcasts from "../views/Broadcasts.vue";
import AuditLogs from "../views/AuditLogs.vue";
import Notifications from "../views/Notifications.vue";

// Attendance & Visitors
import Attendance from "../views/Attendance.vue";
import Visitors from "../views/Visitors.vue";
import FollowUps from "../views/FollowUps.vue";
import MySubmissions from "../views/MySubmissions.vue";

// Finance
import Expense from "../views/Expense.vue";
import ExpenseType from "../views/Expense_type.vue";
import Contributions from "../views/Contributions.vue";

// Departments
import Departments from "../views/Departments.vue";
import MyDepartment from "../views/MyDepartment.vue";

const routes = [
  // Redirects
  { path: "/", redirect: "/dashboard" },
  { path: "/login", redirect: "/signin" },

  // Auth
  {
    path: "/signin",
    component: SignIn,
    meta: { layout: "auth", breadcrumb: "Sign In" },
  },

  // Core
  {
    path: "/home",
    component: Home,
    meta: { requiresAuth: true, breadcrumb: "Home" },
  },
  {
    path: "/dashboard",
    component: Dashboard,
    meta: { requiresAuth: true, breadcrumb: "Dashboard" },
  },
  {
    path: "/settings",
    component: Settings,
    meta: { requiresAuth: true, breadcrumb: "Settings" },
  },
  {
    path: "/profile",
    component: Profile,
    meta: { breadcrumb: "Profile", requiresAuth: true },
  },
  {
    path: "/notifications",
    component: Notifications,
    meta: { requiresAuth: true, breadcrumb: "Notifications" },
  },

  // Admin routes
  {
    path: "/users",
    component: Users,
    meta: { requiresAuth: true, breadcrumb: "Users", roles: ["admin"] },
  },
  {
    path: "/roles-permissions",
    component: RolePermission,
    meta: {
      requiresAuth: true,
      breadcrumb: "Roles & Permissions",
      roles: ["admin"],
    },
  },
  {
    path: "/attendance-approvals",
    component: AttendanceApprovals,
    meta: {
      requiresAuth: true,
      breadcrumb: "Attendance Approvals",
      roles: ["admin"],
    },
  },
  {
    path: "/broadcasts",
    component: Broadcasts,
    meta: { requiresAuth: true, breadcrumb: "Broadcasts", roles: ["admin"] },
  },
  {
    path: "/audit-logs",
    component: AuditLogs,
    meta: { requiresAuth: true, breadcrumb: "Audit Logs", roles: ["admin"] },
  },

  // Attendance & Visitors
  {
    path: "/attendance",
    component: Attendance,
    meta: { requiresAuth: true, breadcrumb: "Attendance" },
  },
  {
    path: "/visitors",
    component: Visitors,
    meta: { requiresAuth: true, breadcrumb: "Visitors" },
  },
  {
    path: "/follow-ups",
    component: FollowUps,
    meta: {
      requiresAuth: true,
      breadcrumb: "Follow-ups",
      roles: ["admin", "pastor", "pr_follow_up"],
    },
  },
  {
    path: "/my-submissions",
    component: MySubmissions,
    meta: {
      requiresAuth: true,
      breadcrumb: "My Submissions",
      roles: ["usher"],
    },
  },

  // Finance
  {
    path: "/contributions",
    component: Contributions,
    meta: {
      requiresAuth: true,
      breadcrumb: "Contributions",
      roles: ["admin", "finance"],
    },
  },
  {
    path: "/expense",
    component: Expense,
    meta: { requiresAuth: true, breadcrumb: "Expenses" },
  },
  {
    path: "/expense-types",
    component: ExpenseType,
    meta: { requiresAuth: true, breadcrumb: "Expense Types" },
  },

  // Departments
  {
    path: "/departments",
    component: Departments,
    meta: { requiresAuth: true, breadcrumb: "Departments" },
  },
  {
    path: "/my-department",
    component: MyDepartment,
    meta: {
      requiresAuth: true,
      breadcrumb: "My Department",
      roles: ["department_leader"],
    },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("auth_token");
  const user = JSON.parse(localStorage.getItem("auth_user") || "null");

  // Check if route requires auth
  if (to.meta.requiresAuth && !token) {
    return next("/signin");
  }

  // Redirect to dashboard if already logged in and trying to access signin
  if (to.path === "/signin" && token) {
    return next("/dashboard");
  }

  // Check role-based access
  if (to.meta.roles && to.meta.roles.length > 0) {
    if (!token || !user) {
      return next("/signin");
    }
    if (!to.meta.roles.includes(user.role)) {
      // User doesn't have required role - redirect to dashboard
      return next("/dashboard");
    }
  }

  next();
});

export default router;
