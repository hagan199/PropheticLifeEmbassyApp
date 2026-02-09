import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store/auth';

// 1. Critical routes
const Dashboard = () => import('../views/Dashboard.vue');
const Home = () => import('../views/Home.vue');
const SignIn = () => import('../views/SignIn.vue');

// 2. Admin Chunks
const Users = () => import('../views/Users.vue');
const RolePermission = () => import('../views/RolePermission.vue');
const AttendanceApprovals = () => import('../views/AttendanceApprovals.vue');
const Broadcasts = () => import('../views/Broadcasts.vue');
const AuditLogs = () => import('../views/AuditLogs.vue');

// 3. Finance Chunks
const Expense = () => import('../views/Expense.vue');
const ExpenseType = () => import('../views/Expense_type.vue');
const Contributions = () => import('../views/Contributions.vue');

// 4. Attendance/Visitors Chunks
const Attendance = () => import('../views/Attendance.vue');
const Visitors = () => import('../views/Visitors.vue');
const FollowUps = () => import('../views/FollowUps.vue');
const MySubmissions = () => import('../views/MySubmissions.vue');

// 5. Departments Chunks
const Departments = () => import('../views/Departments.vue');
const MyDepartment = () => import('../views/MyDepartment.vue');

// 6. Other
const Settings = () => import('../views/Settings.vue');
const Profile = () => import('../views/Profile.vue');
const Notifications = () => import('../views/Notifications.vue');

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', redirect: '/signin' },
  {
    path: '/signin',
    component: SignIn,
    meta: { layout: 'auth', breadcrumb: 'Sign In', guestOnly: true },
  },
  {
    path: '/home',
    component: Home,
    meta: { requiresAuth: true, breadcrumb: 'Home' },
  },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, breadcrumb: 'Dashboard' },
  },
  {
    path: '/settings',
    component: Settings,
    meta: { requiresAuth: true, breadcrumb: 'Settings' },
  },
  {
    path: '/profile',
    component: Profile,
    meta: { breadcrumb: 'Profile', requiresAuth: true },
  },
  {
    path: '/notifications',
    component: Notifications,
    meta: { requiresAuth: true, breadcrumb: 'Notifications' },
  },
  {
    path: '/users',
    component: Users,
    meta: { requiresAuth: true, breadcrumb: 'Users', roles: ['admin'] },
  },
  {
    path: '/roles-permissions',
    component: RolePermission,
    meta: { requiresAuth: true, breadcrumb: 'Roles & Permissions', roles: ['admin'] },
  },
  {
    path: '/attendance-approvals',
    component: AttendanceApprovals,
    meta: { requiresAuth: true, breadcrumb: 'Attendance Approvals', roles: ['admin'] },
  },
  {
    path: '/broadcasts',
    component: Broadcasts,
    meta: { requiresAuth: true, breadcrumb: 'Broadcasts', roles: ['admin'] },
  },
  {
    path: '/audit-logs',
    component: AuditLogs,
    meta: { requiresAuth: true, breadcrumb: 'Audit Logs', roles: ['admin'] },
  },
  {
    path: '/attendance',
    component: Attendance,
    meta: { requiresAuth: true, breadcrumb: 'Attendance' },
  },
  {
    path: '/visitors',
    component: Visitors,
    meta: { requiresAuth: true, breadcrumb: 'Visitors' },
  },
  {
    path: '/follow-ups',
    component: FollowUps,
    meta: {
      requiresAuth: true,
      breadcrumb: 'Follow-ups',
      roles: ['admin', 'pastor', 'pr_follow_up'],
    },
  },
  {
    path: '/my-submissions',
    component: MySubmissions,
    meta: { requiresAuth: true, breadcrumb: 'My Submissions', roles: ['usher'] },
  },
  {
    path: '/contributions',
    component: Contributions,
    meta: { requiresAuth: true, breadcrumb: 'Contributions', roles: ['admin', 'finance'] },
  },
  {
    path: '/expense',
    component: Expense,
    meta: { requiresAuth: true, breadcrumb: 'Expenses' },
  },
  {
    path: '/expense-types',
    component: ExpenseType,
    meta: { requiresAuth: true, breadcrumb: 'Expense Types' },
  },
  {
    path: '/departments',
    component: Departments,
    meta: { requiresAuth: true, breadcrumb: 'Departments' },
  },
  {
    path: '/my-department',
    component: MyDepartment,
    meta: { requiresAuth: true, breadcrumb: 'My Department', roles: ['department_leader'] },
  },
  {
    path: '/reports',
    component: () => import('../views/Reports.vue'),
    meta: { requiresAuth: true, breadcrumb: 'Reports' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: (to, from, savedPosition) => savedPosition || { top: 0 },
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();
  const token = auth.token;
  const user = auth.user;

  // 1. Guest-only check
  if (to.meta.guestOnly && token) {
    return next('/dashboard');
  }

  // 2. Public routes
  if (!to.meta.requiresAuth && to.path !== '/signin') {
    return next();
  }

  // 3. Auth required check
  if (to.meta.requiresAuth && !token) {
    return next('/signin');
  }

  // 4. Role authorization check
  if (to.meta.roles && (!user || !to.meta.roles.includes(user.role))) {
    return next('/dashboard');
  }

  next();
});

export default router;
