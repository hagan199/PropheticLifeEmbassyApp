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
            <span v-if="pendingApprovals > 0" class="nav-badge warning">{{ pendingApprovals }}</span>
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
import { CSidebar, CSidebarHeader, CSidebarFooter, CSidebarNav, CNavItem, CNavGroup, CAvatar } from '@coreui/vue'
import { RouterLink, useRoute } from 'vue-router'
import { computed, ref, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import { dashboardApi } from '../api'

defineProps({ visible: { type: Boolean, default: true } })
const emit = defineEmits(['visible-change'])
function emitVisibleChange(v) { emit('visible-change', v) }

const route = useRoute()
function isActive(path) { return route.path.startsWith(path) }

// Nav group open states
const adminOpen = computed(() => ['/users', '/roles-permissions', '/attendance-approvals', '/broadcasts', '/audit-logs'].some(p => route.path.startsWith(p)))
const attendanceOpen = computed(() => ['/attendance', '/visitors'].some(p => route.path.startsWith(p)))
const followUpOpen = computed(() => route.path.startsWith('/follow-ups'))
const financeOpen = computed(() => ['/contributions', '/expense'].some(p => route.path.startsWith(p)))
const departmentOpen = computed(() => ['/departments', '/my-department'].some(p => route.path.startsWith(p)))

// Auth & Role
const auth = useAuthStore()
const userRole = computed(() => auth.user?.role || null)

function hasRole(roles) {
  if (!userRole.value) return false
  return roles.includes(userRole.value)
}

const name = computed(() => auth.user?.name || 'Admin User')
const roleLabel = computed(() => {
  const labels = {
    admin: 'Administrator',
    pastor: 'Pastor',
    usher: 'Usher',
    finance: 'Finance Officer',
    pr_follow_up: 'PR / Follow-up',
    department_leader: 'Dept. Leader'
  }
  return labels[userRole.value] || userRole.value
})
const avatar = computed(() => auth.user?.avatar)
const userInitials = computed(() => {
  const n = name.value
  return n.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
})

// Badges - Fetch from API
const pendingApprovals = ref(0)
const dueFollowUps = ref(0)

onMounted(async () => {
  if (auth.isAuthenticated) {
    try {
      const { data } = await dashboardApi.getStats()
      if (data.success && data.data) {
        const quickActions = data.data.quick_actions || []
        const pendingAction = quickActions.find(a => a.label?.toLowerCase().includes('pending approval'))
        const followUpAction = quickActions.find(a => a.label?.toLowerCase().includes('follow-up') || a.label?.toLowerCase().includes('visitor'))

        pendingApprovals.value = pendingAction?.count || 0
        dueFollowUps.value = followUpAction?.count || 0
      }
    } catch (error) {
      console.warn('Failed to load sidebar stats:', error)
    }
  }
})
</script>

<style scoped>
/* Sidebar Container */
:deep(.sidebar) {
  position: fixed !important;
  top: 0;
  left: 0;
  height: 100vh;
  height: 100dvh;
  width: 260px;
  background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%) !important;
  border-right: 1px solid rgba(99, 102, 241, 0.1) !important;
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.3);
  z-index: 1040 !important;
  overflow-y: auto;
  overflow-x: hidden;
  transform: translateX(0);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s;
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
:deep(.sidebar .sidebar-header) {
  background: transparent !important;
  padding: 1.25rem !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex !important;
  flex-direction: column !important;
  align-items: stretch !important;
}

/* Brand Section */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.brand-logo {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.5);
  animation: glowPulse 3s ease-in-out infinite;
}

@keyframes glowPulse {
  0%, 100% { box-shadow: 0 4px 14px rgba(99, 102, 241, 0.5); }
  50% { box-shadow: 0 4px 24px rgba(99, 102, 241, 0.7); }
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.025em;
}

.brand-tagline {
  font-size: 0.7rem;
  color: #94a3b8;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

/* Profile Section */
.profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.profile:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(99, 102, 241, 0.3);
}

.profile-avatar {
  position: relative;
  flex-shrink: 0;
}

.profile-avatar :deep(.avatar) {
  width: 44px !important;
  height: 44px !important;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
  font-size: 0.9rem;
  font-weight: 600;
}

.status-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 10px;
  height: 10px;
  background: #10b981;
  border-radius: 50%;
  border: 2px solid #0f172a;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
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
  color: #ffffff !important;
  font-size: 0.925rem;
  font-weight: 600;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  display: block;
}

.profile-role {
  color: #c4b5fd !important;
  font-size: 0.8rem;
  font-weight: 500;
  line-height: 1.3;
  display: block;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* Sidebar Nav */
:deep(.sidebar .sidebar-nav) {
  overflow-y: auto;
  max-height: calc(100vh - 200px);
  padding: 0.75rem;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar {
  width: 5px;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50px;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-track {
  background: transparent;
}

/* Nav Link */
:deep(.sidebar .nav-link) {
  color: rgba(255, 255, 255, 0.6) !important;
  border-radius: 10px !important;
  padding: 0.65rem 0.875rem !important;
  margin: 2px 0;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
}

:deep(.sidebar .nav-link:hover) {
  color: #fff !important;
  background: rgba(99, 102, 241, 0.15) !important;
  transform: translateX(4px);
}

:deep(.sidebar .nav-link.active) {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
  color: #fff !important;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
  transform: translateX(0);
}

:deep(.sidebar .nav-link.active:hover) {
  transform: translateX(0);
}

/* Nav Icon Wrapper */
.nav-icon-wrapper {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 8px;
  margin-right: 0.75rem;
  transition: all 0.25s ease;
  flex-shrink: 0;
}

.nav-icon-wrapper.small {
  width: 26px;
  height: 26px;
  font-size: 0.8rem;
}

.nav-icon-wrapper i {
  font-size: 1rem;
  opacity: 0.9;
}

:deep(.sidebar .nav-link:hover) .nav-icon-wrapper {
  background: rgba(99, 102, 241, 0.2);
  transform: scale(1.05);
}

:deep(.sidebar .nav-link.active) .nav-icon-wrapper {
  background: rgba(255, 255, 255, 0.2);
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
  color: rgba(255, 255, 255, 0.6) !important;
  border-radius: 10px !important;
  padding: 0.65rem 0.875rem !important;
  margin: 2px 0;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.25s ease;
}

:deep(.sidebar .nav-group-toggle:hover) {
  color: #fff !important;
  background: rgba(99, 102, 241, 0.1) !important;
}

:deep(.sidebar .nav-group-items) {
  background: rgba(0, 0, 0, 0.25) !important;
  border-radius: 10px;
  margin: 4px 0 4px 8px;
  padding: 6px;
  border-left: 2px solid rgba(99, 102, 241, 0.3);
}

:deep(.sidebar .nav-group-items .nav-link) {
  padding: 0.5rem 0.75rem !important;
  font-size: 0.8125rem;
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
