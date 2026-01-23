import { createRouter, createWebHistory } from "vue-router";

// Auth
import SignIn from "../views/SignIn.vue";

// Core
import Home from "../views/Home.vue";
import Dashboard from "../views/Dashboard.vue";
import Settings from "../views/Settings.vue";

// Admin
import Users from "../views/Users.vue";
import RolePermission from "../views/RolePermission.vue";
import AttendanceApprovals from "../views/AttendanceApprovals.vue";
import Broadcasts from "../views/Broadcasts.vue";
import AuditLogs from "../views/AuditLogs.vue";

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
    meta: { breadcrumb: "Dashboard" },
  },
  { path: "/settings", component: Settings, meta: { breadcrumb: "Settings" } },

  // Admin routes
  {
    path: "/users",
    component: Users,
    meta: { breadcrumb: "Users", roles: ["admin"] },
  },
  {
    path: "/roles-permissions",
    component: RolePermission,
    meta: { breadcrumb: "Roles & Permissions", roles: ["admin"] },
  },
  {
    path: "/attendance-approvals",
    component: AttendanceApprovals,
    meta: { breadcrumb: "Attendance Approvals", roles: ["admin"] },
  },
  {
    path: "/broadcasts",
    component: Broadcasts,
    meta: { breadcrumb: "Broadcasts", roles: ["admin"] },
  },
  {
    path: "/audit-logs",
    component: AuditLogs,
    meta: { breadcrumb: "Audit Logs", roles: ["admin"] },
  },

  // Attendance & Visitors
  {
    path: "/attendance",
    component: Attendance,
    meta: { breadcrumb: "Attendance" },
  },
  { path: "/visitors", component: Visitors, meta: { breadcrumb: "Visitors" } },
  {
    path: "/follow-ups",
    component: FollowUps,
    meta: {
      breadcrumb: "Follow-ups",
      roles: ["admin", "pastor", "pr_follow_up"],
    },
  },
  {
    path: "/my-submissions",
    component: MySubmissions,
    meta: { breadcrumb: "My Submissions", roles: ["usher"] },
  },

  // Finance
  {
    path: "/contributions",
    component: Contributions,
    meta: { breadcrumb: "Contributions", roles: ["admin", "finance"] },
  },
  { path: "/expense", component: Expense, meta: { breadcrumb: "Expenses" } },
  {
    path: "/expense-types",
    component: ExpenseType,
    meta: { breadcrumb: "Expense Types" },
  },

  // Departments
  {
    path: "/departments",
    component: Departments,
    meta: { breadcrumb: "Departments" },
  },
  {
    path: "/my-department",
    component: MyDepartment,
    meta: { breadcrumb: "My Department", roles: ["department_leader"] },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");
  if (to.meta.requiresAuth && !token) {
    next("/signin");
  } else if (to.path === "/signin" && token) {
    next("/dashboard");
  } else {
    next();
  }
});

export default router;
