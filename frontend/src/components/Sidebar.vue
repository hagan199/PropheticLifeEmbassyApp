<template>
  <CSidebar :visible="visible" position="fixed" placement="start" @visible-change="emitVisibleChange">
    <CSidebarHeader>
      <div class="sidebar-brand">
        <div class="brand-logo">
          <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="brand-text">
          <span class="brand-name">PLE</span>
          <span class="brand-tagline">Church CMS</span>
        </div>
      </div>
    </CSidebarHeader>
    <CSidebarNav>
      <!-- Reports - All Roles (Moved to Top) -->
      <CNavGroup :visible="reportsOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <span class="nav-icon-wrapper">
              <i class="bi bi-bar-chart-fill"></i>
            </span>
            <span class="nav-text me-auto">Reports</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/reports/finance" class="nav-link" :class="{ active: isActive('/reports/finance') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-cash-stack"></i>
            </span>
            <span class="nav-text">Finance Monthly Report</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/reports/finance-export" class="nav-link"
            :class="{ active: isActive('/reports/finance-export') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-file-earmark-arrow-down"></i>
            </span>
            <span class="nav-text">Finance Export Report</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/reports/attendance" class="nav-link" :class="{ active: isActive('/reports/attendance') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-calendar-check"></i>
            </span>
            <span class="nav-text">Attendance Report</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/reports/attendance-import" class="nav-link"
            :class="{ active: isActive('/reports/attendance-import') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-file-earmark-arrow-up"></i>
            </span>
            <span class="nav-text">Attendance Import</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/reports/audit-logs" class="nav-link" :class="{ active: isActive('/reports/audit-logs') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-journal-text"></i>
            </span>
            <span class="nav-text">Audit Logs Export</span>
          </RouterLink>
        </CNavItem>
      </CNavGroup>
      <!-- End Reports -->

      <!-- Dashboard - All Roles -->
      <CNavItem>
        <RouterLink to="/dashboard" class="nav-link" :class="{ active: isActive('/dashboard') }">
          <span class="nav-icon-wrapper">
            <i class="bi bi-grid-1x2-fill"></i>
          </span>
          <span class="nav-text">Dashboard</span>
        </RouterLink>
      </CNavItem>

      <!-- Admin Only: User Management -->
      <CNavGroup v-if="hasRole(['admin'])" :visible="adminOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <span class="nav-icon-wrapper">
              <i class="bi bi-shield-lock-fill"></i>
            </span>
            <span class="nav-text me-auto">Administration</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/users" class="nav-link" :class="{ active: isActive('/users') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-people-fill"></i>
            </span>
            <span class="nav-text">Users</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/roles-permissions" class="nav-link" :class="{ active: isActive('/roles-permissions') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-key-fill"></i>
            </span>
            <span class="nav-text">Roles & Permissions</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/attendance-approvals" class="nav-link"
            :class="{ active: isActive('/attendance-approvals') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-clipboard-check-fill"></i>
            </span>
            <span class="nav-text">Attendance Approvals</span>
            <span v-if="pendingApprovals > 0" class="nav-badge warning">{{
              pendingApprovals
              }}</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/broadcasts" class="nav-link" :class="{ active: isActive('/broadcasts') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-broadcast-pin"></i>
            </span>
            <span class="nav-text">Broadcasts</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/audit-logs" class="nav-link" :class="{ active: isActive('/audit-logs') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-journal-text"></i>
            </span>
            <span class="nav-text">Audit Logs</span>
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Usher: My Submissions -->
      <CNavItem v-if="hasRole(['usher'])">
        <RouterLink to="/my-submissions" class="nav-link" :class="{ active: isActive('/my-submissions') }">
          <span class="nav-icon-wrapper">
            <i class="bi bi-send-check-fill"></i>
          </span>
          <span class="nav-text">My Submissions</span>
        </RouterLink>
      </CNavItem>

      <!-- Attendance - Admin, Pastor, Usher -->
      <CNavGroup v-if="hasRole(['admin', 'pastor', 'usher'])" :visible="attendanceOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <span class="nav-icon-wrapper">
              <i class="bi bi-calendar2-check-fill"></i>
            </span>
            <span class="nav-text me-auto">Attendance</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/attendance" class="nav-link" :class="{ active: isActive('/attendance') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-calendar-check-fill"></i>
            </span>
            <span class="nav-text">Records</span>
          </RouterLink>
        </CNavItem>
        <CNavItem v-if="hasRole(['admin', 'pastor'])">
          <RouterLink to="/visitors" class="nav-link" :class="{ active: isActive('/visitors') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-person-plus-fill"></i>
            </span>
            <span class="nav-text">Visitors</span>
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Follow-ups - Admin, Pastor, PR -->
      <CNavGroup v-if="hasRole(['admin', 'pastor', 'pr_follow_up'])" :visible="followUpOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <span class="nav-icon-wrapper">
              <i class="bi bi-chat-heart-fill"></i>
            </span>
            <span class="nav-text me-auto">Follow-ups</span>
            <span v-if="dueFollowUps > 0" class="nav-badge danger">{{ dueFollowUps }}</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/follow-ups" class="nav-link" :class="{ active: isActive('/follow-ups') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-people-fill"></i>
            </span>
            <span class="nav-text">All Visitors</span>
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Finance - Admin, Finance -->
      <CNavGroup v-if="hasRole(['admin', 'finance'])" :visible="financeOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <span class="nav-icon-wrapper">
              <i class="bi bi-cash-coin"></i>
            </span>
            <span class="nav-text me-auto">Finance</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/contributions" class="nav-link" :class="{ active: isActive('/contributions') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-cash-stack"></i>
            </span>
            <span class="nav-text">Contributions</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/expense" class="nav-link" :class="{ active: isActive('/expense') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-receipt"></i>
            </span>
            <span class="nav-text">Expenses</span>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/expense-types" class="nav-link" :class="{ active: isActive('/expense-types') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-tags-fill"></i>
            </span>
            <span class="nav-text">Expense Types</span>
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Departments - Admin, Pastor, Department Leader -->
      <CNavGroup v-if="hasRole(['admin', 'pastor', 'department_leader'])" :visible="departmentOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <span class="nav-icon-wrapper">
              <i class="bi bi-diagram-3-fill"></i>
            </span>
            <span class="nav-text me-auto">Departments</span>
          </span>
        </template>
        <CNavItem v-if="hasRole(['admin', 'pastor'])">
          <RouterLink to="/departments" class="nav-link" :class="{ active: isActive('/departments') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-building"></i>
            </span>
            <span class="nav-text">All Departments</span>
          </RouterLink>
        </CNavItem>
        <CNavItem v-if="hasRole(['department_leader'])">
          <RouterLink to="/my-department" class="nav-link" :class="{ active: isActive('/my-department') }">
            <span class="nav-icon-wrapper small">
              <i class="bi bi-people-fill"></i>
            </span>
            <span class="nav-text">My Department</span>
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Settings -->
      <CNavItem>
        <RouterLink to="/settings" class="nav-link" :class="{ active: isActive('/settings') }">
          <span class="nav-icon-wrapper">
            <i class="bi bi-gear-fill"></i>
          </span>
          <span class="nav-text">Settings</span>
        </RouterLink>
      </CNavItem>
    </CSidebarNav>
    <CSidebarFooter>
      <div class="sidebar-footer-content">
        <div class="version-info">
          <i class="bi bi-lightning-charge me-1"></i>
          PLE CMS v0.1.0
        </div>
      </div>
    </CSidebarFooter>
  </CSidebar>
</template>

<script setup>
import {
  CSidebar,
  CSidebarHeader,
  CSidebarFooter,
  CSidebarNav,
  CNavItem,
  CNavGroup,
} from '@coreui/vue';
import { RouterLink, useRoute } from 'vue-router';
import { computed, ref, onMounted } from 'vue';
import { useAuthStore } from '../store/auth';
import { dashboardApi } from '../api';

defineProps({ visible: { type: Boolean, default: true } });
const emit = defineEmits(['visible-change']);
function emitVisibleChange(v) {
  emit('visible-change', v);
}

const route = useRoute();
function isActive(path) {
  return route.path.startsWith(path);
}

// Nav group open states
const adminOpen = computed(() =>
  ['/users', '/roles-permissions', '/attendance-approvals', '/broadcasts', '/audit-logs'].some(p =>
    route.path.startsWith(p)
  )
);
const attendanceOpen = computed(() =>
  ['/attendance', '/visitors'].some(p => route.path.startsWith(p))
);
const followUpOpen = computed(() => route.path.startsWith('/follow-ups'));
const financeOpen = computed(() =>
  ['/contributions', '/expense'].some(p => route.path.startsWith(p))
);
const departmentOpen = computed(() =>
  ['/departments', '/my-department'].some(p => route.path.startsWith(p))
);
const reportsOpen = computed(() => route.path.startsWith('/reports'));

// Auth & Role
const auth = useAuthStore();
const userRole = computed(() => auth.user?.role || null);

function hasRole(roles) {
  if (!userRole.value) return false;
  return roles.includes(userRole.value);
}

const _name = computed(() => auth.user?.name || 'Admin User');
// role label, avatar and initials handled in Navbar/store; removed unused computed values

// Badges - Fetch from API
const pendingApprovals = ref(0);
const dueFollowUps = ref(0);

onMounted(async () => {
  if (auth.isAuthenticated) {
    try {
      const { data } = await dashboardApi.stats();
      if (data.success && data.data) {
        const quickActions = data.data.quick_actions || [];
        const pendingAction = quickActions.find(a =>
          a.label?.toLowerCase().includes('pending approval')
        );
        const followUpAction = quickActions.find(
          a =>
            a.label?.toLowerCase().includes('follow-up') ||
            a.label?.toLowerCase().includes('visitor')
        );

        pendingApprovals.value = pendingAction?.count || 0;
        dueFollowUps.value = followUpAction?.count || 0;
      }
    } catch (error) {
      console.warn('Failed to load sidebar stats:', error);
    }
  }
});
</script>

<style>
/* IMPORTANT: Using non-scoped style for CoreUI overrides */
</style>

<style scoped>
/* Sidebar Container - Multiple selectors for specificity */
:deep(.sidebar),
:deep(.sidebar.sidebar-fixed),
:deep(.sidebar.show),
:deep(div.sidebar) {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  height: 100vh !important;
  height: 100dvh !important;
  width: 300px !important;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  backdrop-filter: blur(12px) saturate(180%) !important;
  -webkit-backdrop-filter: blur(12px) saturate(180%) !important;
  border-right: 2px solid #764ba2 !important;
  box-shadow: 8px 0 32px rgba(102, 126, 234, 0.12), 0 2px 8px rgba(118, 75, 162, 0.1) !important;
  z-index: 1040 !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  transform: translateX(0) !important;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s !important;
}

:deep(.sidebar:not(.show)) {
  transform: translateX(-100%);
}

@media (max-width: 991.98px) {
  :deep(.sidebar) {
    width: 280px;
    box-shadow: 4px 0 40px rgba(0, 0, 0, 0.5);
  }
}

@media (min-width: 992px) {
  :deep(.sidebar.show) {
    transform: translateX(0);
  }

  :deep(.sidebar:not(.show)) {
    transform: translateX(-100%);
  }
}

/* Sidebar Header */
:deep(.sidebar .sidebar-header),
:deep(.sidebar-header) {
  background: transparent !important;
  padding: 1.25rem !important;
  border-bottom: 1px solid rgba(102, 126, 234, 0.12) !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: stretch !important;
}

/* Brand Section */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.12);
}

.brand-logo {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4), 0 4px 8px rgba(118, 75, 162, 0.3);
  animation: glowPulse 3s ease-in-out infinite;
}

@keyframes glowPulse {

  0%,
  100% {
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4), 0 4px 8px rgba(118, 75, 162, 0.3);
  }

  50% {
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.6), 0 6px 12px rgba(118, 75, 162, 0.5);
  }
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.25rem;
  font-weight: 800;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.03em;
}

.brand-tagline {
  font-size: 0.7rem;
  color: #667eea;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  font-weight: 600;
}

/* Profile Section */
.profile {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 1rem;
  background: rgba(102, 126, 234, 0.06);
  border-radius: 14px;
  transition: all 0.3s ease;
  border: 1px solid rgba(102, 126, 234, 0.12);
}

.profile:hover {
  background: rgba(102, 126, 234, 0.12);
  border-color: rgba(102, 126, 234, 0.25);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.profile-avatar {
  position: relative;
  flex-shrink: 0;
}

.profile-avatar :deep(.avatar) {
  width: 48px !important;
  height: 48px !important;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4), 0 2px 4px rgba(118, 75, 162, 0.3);
  font-size: 1rem;
  font-weight: 700;
  border-radius: 12px !important;
}

.status-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 11px;
  height: 11px;
  background: #10b981;
  border-radius: 50%;
  border: 2.5px solid white;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.5);
  animation: pulse 2s infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}

.profile-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.125rem;
}

.profile-name {
  color: #1e293b !important;
  font-size: 0.95rem;
  font-weight: 700;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.profile-role {
  color: #667eea !important;
  font-size: 0.8rem;
  font-weight: 600;
  line-height: 1.3;
  display: block;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Sidebar Nav */
:deep(.sidebar .sidebar-nav),
:deep(.sidebar-nav) {
  overflow-y: auto !important;
  max-height: calc(100vh - 200px) !important;
  padding: 0.75rem !important;
  scrollbar-width: thin !important;
  scrollbar-color: rgba(102, 126, 234, 0.2) transparent !important;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar,
:deep(.sidebar-nav)::-webkit-scrollbar {
  width: 6px !important;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-thumb,
:deep(.sidebar-nav)::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.2) !important;
  border-radius: 50px !important;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-thumb:hover,
:deep(.sidebar-nav)::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.3) !important;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-track,
:deep(.sidebar-nav)::-webkit-scrollbar-track {
  background: transparent !important;
}

/* Nav Link */
:deep(.sidebar .nav-link) {
  color: #f1f5f9 !important;
  border-radius: 12px !important;
  padding: 0.75rem 1.15rem !important;
  margin: 3px 0;
  font-weight: 600;
  font-size: 1rem;
  letter-spacing: 0.01em;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  border: 1px solid transparent;
}

:deep(.sidebar .nav-link:hover) {
  color: #fff !important;
  background: rgba(102, 126, 234, 0.22) !important;
  border-color: #a5b4fc;
  transform: translateX(6px) scale(1.03);
}

:deep(.sidebar .nav-link.active) {
  background: rgba(255, 255, 255, 0.12) !important;
  color: #fff !important;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.18), 0 4px 8px rgba(118, 75, 162, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.12);
  transform: translateX(0);
  border-color: transparent;
}

:deep(.sidebar .nav-link.active:hover) {
  transform: scale(1.02);
}

/* Nav Icon Wrapper */
.nav-icon-wrapper {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  margin-right: 0.875rem;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.nav-icon-wrapper.small {
  width: 30px;
  height: 30px;
  font-size: 0.85rem;
}

.nav-icon-wrapper i {
  font-size: 1.1rem;
  color: #fff;
}

:deep(.sidebar .nav-link:hover) .nav-icon-wrapper {
  background: rgba(102, 126, 234, 0.15);
  transform: scale(1.1) rotate(5deg);
}

:deep(.sidebar .nav-link.active) .nav-icon-wrapper {
  background: rgba(255, 255, 255, 0.25);
}

:deep(.sidebar .nav-link.active) .nav-icon-wrapper i {
  color: white;
}

.nav-text {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Nav Badge */
.nav-badge {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 50px;
  margin-left: auto;
}

.nav-badge.warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}

.nav-badge.danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

/* Nav Group */
:deep(.sidebar .nav-group-toggle) {
  color: #64748b !important;
  border-radius: 12px !important;
  padding: 0.75rem 1rem !important;
  margin: 3px 0;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

:deep(.sidebar .nav-group-toggle:hover) {
  color: #667eea !important;
  background: rgba(102, 126, 234, 0.08) !important;
  border-color: rgba(102, 126, 234, 0.15);
}

:deep(.sidebar .nav-group-items) {
  background: rgba(102, 126, 234, 0.04) !important;
  border-radius: 12px;
  margin: 6px 0 6px 12px;
  padding: 8px;
  border-left: 3px solid rgba(102, 126, 234, 0.3);
}

:deep(.sidebar .nav-group-items .nav-link) {
  padding: 0.6rem 0.875rem !important;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Sidebar Footer */
:deep(.sidebar .sidebar-footer) {
  background: rgba(0, 0, 0, 0.2) !important;
  border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
  padding: 0.875rem 1rem !important;
}

.sidebar-footer-content {
  display: flex;
  align-items: center;
  justify-content: center;
}

.version-info {
  color: rgba(255, 255, 255, 0.35);
  font-size: 0.75rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.version-info i {
  color: rgba(99, 102, 241, 0.6);
}
</style>

<style>
/* Global unscoped styles to override CoreUI with maximum specificity */
.sidebar,
.sidebar.sidebar-fixed,
div.sidebar {
  background: rgba(255, 255, 255, 0.98) !important;
  backdrop-filter: blur(20px) saturate(180%) !important;
  -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
}

.sidebar .sidebar-header,
.sidebar-header {
  background: transparent !important;
  border-bottom: 1px solid rgba(102, 126, 234, 0.12) !important;
}

.sidebar .nav-link {
  color: #64748b !important;
}

.sidebar .nav-link:hover {
  color: #667eea !important;
  background: rgba(102, 126, 234, 0.08) !important;
}

.sidebar .nav-link.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  color: #fff !important;
}

.sidebar .nav-group-toggle {
  color: #64748b !important;
}

.sidebar .nav-group-toggle:hover {
  color: #667eea !important;
  background: rgba(102, 126, 234, 0.08) !important;
}
</style>
