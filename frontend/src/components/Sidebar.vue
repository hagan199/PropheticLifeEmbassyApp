<template>
  <CSidebar :visible="visible" position="fixed" placement="start" @visible-change="emitVisibleChange">
    <CSidebarHeader>
      <div class="profile d-flex align-items-center px-3 py-2">
        <CAvatar :src="avatar" color="primary" text-color="white">{{ userInitials }}</CAvatar>
        <div class="ms-2">
          <div class="fw-semibold">{{ name }}</div>
          <div class="text-muted small">{{ roleLabel }}</div>
        </div>
      </div>
    </CSidebarHeader>
    <CSidebarNav>
      <!-- Dashboard - All Roles -->
      <CNavItem>
        <RouterLink to="/dashboard" class="nav-link" :class="{ active: isActive('/dashboard') }">
          <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </RouterLink>
      </CNavItem>

      <!-- Admin Only: User Management -->
      <CNavGroup v-if="hasRole(['admin'])" :visible="adminOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <i class="bi bi-shield-lock me-2"></i>
            <span class="me-auto">Administration</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/users" class="nav-link" :class="{ active: isActive('/users') }">
            <i class="bi bi-people me-2"></i>Users
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/roles-permissions" class="nav-link" :class="{ active: isActive('/roles-permissions') }">
            <i class="bi bi-key me-2"></i>Roles & Permissions
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/attendance-approvals" class="nav-link"
            :class="{ active: isActive('/attendance-approvals') }">
            <i class="bi bi-clipboard-check me-2"></i>Attendance Approvals
            <CBadge v-if="pendingApprovals > 0" color="warning" shape="rounded-pill" class="ms-auto">{{ pendingApprovals
              }}
            </CBadge>
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/broadcasts" class="nav-link" :class="{ active: isActive('/broadcasts') }">
            <i class="bi bi-broadcast me-2"></i>Broadcasts
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/audit-logs" class="nav-link" :class="{ active: isActive('/audit-logs') }">
            <i class="bi bi-journal-text me-2"></i>Audit Logs
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Usher: My Submissions -->
      <CNavItem v-if="hasRole(['usher'])">
        <RouterLink to="/my-submissions" class="nav-link" :class="{ active: isActive('/my-submissions') }">
          <i class="bi bi-send-check me-2"></i>My Submissions
        </RouterLink>
      </CNavItem>

      <!-- Attendance - Admin, Pastor, Usher -->
      <CNavGroup v-if="hasRole(['admin', 'pastor', 'usher'])" :visible="attendanceOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <i class="bi bi-clipboard-data me-2"></i>
            <span class="me-auto">Attendance</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/attendance" class="nav-link" :class="{ active: isActive('/attendance') }">
            <i class="bi bi-calendar-check me-2"></i>Records
          </RouterLink>
        </CNavItem>
        <CNavItem v-if="hasRole(['admin', 'pastor'])">
          <RouterLink to="/visitors" class="nav-link" :class="{ active: isActive('/visitors') }">
            <i class="bi bi-person-plus me-2"></i>Visitors
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Follow-ups - Admin, Pastor, PR -->
      <CNavGroup v-if="hasRole(['admin', 'pastor', 'pr_follow_up'])" :visible="followUpOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <i class="bi bi-chat-heart me-2"></i>
            <span class="me-auto">Follow-ups</span>
            <CBadge v-if="dueFollowUps > 0" color="danger" shape="rounded-pill">{{ dueFollowUps }}</CBadge>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/follow-ups" class="nav-link" :class="{ active: isActive('/follow-ups') }">
            <i class="bi bi-people me-2"></i>All Visitors
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Finance - Admin, Finance -->
      <CNavGroup v-if="hasRole(['admin', 'finance'])" :visible="financeOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <i class="bi bi-cash-coin me-2"></i>
            <span class="me-auto">Finance</span>
          </span>
        </template>
        <CNavItem>
          <RouterLink to="/contributions" class="nav-link" :class="{ active: isActive('/contributions') }">
            <i class="bi bi-cash-stack me-2"></i>Contributions
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/expense" class="nav-link" :class="{ active: isActive('/expense') }">
            <i class="bi bi-receipt me-2"></i>Expenses
          </RouterLink>
        </CNavItem>
        <CNavItem>
          <RouterLink to="/expense-types" class="nav-link" :class="{ active: isActive('/expense-types') }">
            <i class="bi bi-tags me-2"></i>Expense Types
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Departments - Admin, Pastor, Department Leader -->
      <CNavGroup v-if="hasRole(['admin', 'pastor', 'department_leader'])" :visible="departmentOpen">
        <template #togglerContent>
          <span class="d-inline-flex align-items-center w-100">
            <i class="bi bi-diagram-3 me-2"></i>
            <span class="me-auto">Departments</span>
          </span>
        </template>
        <CNavItem v-if="hasRole(['admin', 'pastor'])">
          <RouterLink to="/departments" class="nav-link" :class="{ active: isActive('/departments') }">
            <i class="bi bi-building me-2"></i>All Departments
          </RouterLink>
        </CNavItem>
        <CNavItem v-if="hasRole(['department_leader'])">
          <RouterLink to="/my-department" class="nav-link" :class="{ active: isActive('/my-department') }">
            <i class="bi bi-people me-2"></i>My Department
          </RouterLink>
        </CNavItem>
      </CNavGroup>

      <!-- Settings -->
      <CNavItem>
        <RouterLink to="/settings" class="nav-link" :class="{ active: isActive('/settings') }">
          <i class="bi bi-gear me-2"></i>Settings
        </RouterLink>
      </CNavItem>
    </CSidebarNav>
    <CSidebarFooter>
      <div class="px-3 py-2 small text-muted">© PLE • v0.1.0</div>
    </CSidebarFooter>
  </CSidebar>
</template>

<script setup>
import { CSidebar, CSidebarHeader, CSidebarFooter, CSidebarNav, CNavItem, CNavGroup, CAvatar, CBadge } from '@coreui/vue'
import { RouterLink, useRoute } from 'vue-router'
import { computed, ref } from 'vue'
import { useAuthStore } from '../store/auth'

const props = defineProps({ visible: { type: Boolean, default: true } })
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
const userRole = computed(() => auth.user?.role || 'admin') // Default to admin for demo

function hasRole(roles) {
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

// Badges - Would come from API in real app
const pendingApprovals = ref(3)
const dueFollowUps = ref(5)
</script>

<style scoped>
@media (max-width: 992px) {
  :deep(.sidebar) {
    width: 260px;
  }
}

:deep(.sidebar) {
  height: 100vh;
}

:deep(.sidebar .sidebar-nav) {
  overflow-y: auto;
  max-height: calc(100vh - 112px);
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar {
  width: 8px;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, .15);
  border-radius: 8px;
}

:deep(.sidebar .sidebar-nav)::-webkit-scrollbar-track {
  background: transparent;
}

.profile :deep(.avatar) {
  box-shadow: 0 6px 16px rgba(13, 110, 253, .35);
}

:deep(.sidebar .nav-link.active) {
  background: rgba(13, 110, 253, .12);
  color: #0d6efd;
}
</style>
