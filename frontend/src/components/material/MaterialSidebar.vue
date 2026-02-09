<template>
  <div>
    <aside class="md-sidebar" :class="{ 'md-sidebar-visible': visible }">
      <!-- Header -->
      <div class="md-sidebar-header">
        <div class="md-sidebar-brand">
          <div class="md-sidebar-logo">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <div class="md-sidebar-brand-text">
            <span class="md-sidebar-title">PLE</span>
            <span class="md-sidebar-subtitle">Church CMS</span>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="md-sidebar-nav">
        <!-- Dashboard -->
        <RouterLink to="/dashboard" class="md-nav-link" :class="{ active: isActive('/dashboard') }">
          <span class="md-nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
          <span class="md-nav-text">Dashboard</span>
        </RouterLink>

        <!-- Reports -->
        <div class="md-nav-group" :class="{ expanded: reportsOpen }">
          <button class="md-nav-group-toggle" @click="toggleReports">
            <span class="md-nav-icon"><i class="bi bi-bar-chart-fill"></i></span>
            <span class="md-nav-text">Reports</span>
            <i class="bi bi-chevron-down md-nav-arrow"></i>
          </button>
          <div class="md-nav-group-items">
            <RouterLink to="/reports" class="md-nav-link" :class="{ active: isActive('/reports') }">
              <span class="md-nav-icon sm"><i class="bi bi-file-earmark-bar-graph"></i></span>
              <span class="md-nav-text">Overview</span>
            </RouterLink>
          </div>
        </div>

        <!-- Admin Section -->
        <div v-if="hasRole(['admin'])" class="md-nav-group" :class="{ expanded: adminOpen }">
          <button class="md-nav-group-toggle" @click="toggleAdmin">
            <span class="md-nav-icon"><i class="bi bi-shield-lock-fill"></i></span>
            <span class="md-nav-text">Administration</span>
            <i class="bi bi-chevron-down md-nav-arrow"></i>
          </button>
          <div class="md-nav-group-items">
            <RouterLink to="/users" class="md-nav-link" :class="{ active: isActive('/users') }">
              <span class="md-nav-icon sm"><i class="bi bi-people-fill"></i></span>
              <span class="md-nav-text">Users</span>
            </RouterLink>
            <RouterLink to="/roles-permissions" class="md-nav-link" :class="{ active: isActive('/roles-permissions') }">
              <span class="md-nav-icon sm"><i class="bi bi-key-fill"></i></span>
              <span class="md-nav-text">Roles & Permissions</span>
            </RouterLink>
            <RouterLink to="/attendance-approvals" class="md-nav-link"
              :class="{ active: isActive('/attendance-approvals') }">
              <span class="md-nav-icon sm"><i class="bi bi-clipboard-check-fill"></i></span>
              <span class="md-nav-text">Approvals</span>
              <span v-if="pendingApprovals > 0" class="md-nav-badge warning">{{
                pendingApprovals
              }}</span>
            </RouterLink>
            <RouterLink to="/broadcasts" class="md-nav-link" :class="{ active: isActive('/broadcasts') }">
              <span class="md-nav-icon sm"><i class="bi bi-broadcast-pin"></i></span>
              <span class="md-nav-text">Broadcasts</span>
            </RouterLink>
            <RouterLink to="/audit-logs" class="md-nav-link" :class="{ active: isActive('/audit-logs') }">
              <span class="md-nav-icon sm"><i class="bi bi-journal-text"></i></span>
              <span class="md-nav-text">Audit Logs</span>
            </RouterLink>
          </div>
        </div>

        <!-- Usher's My Submissions -->
        <RouterLink v-if="hasRole(['usher'])" to="/my-submissions" class="md-nav-link"
          :class="{ active: isActive('/my-submissions') }">
          <span class="md-nav-icon"><i class="bi bi-send-check-fill"></i></span>
          <span class="md-nav-text">My Submissions</span>
        </RouterLink>

        <!-- Attendance -->
        <div v-if="hasRole(['admin', 'pastor', 'usher'])" class="md-nav-group" :class="{ expanded: attendanceOpen }">
          <button class="md-nav-group-toggle" @click="toggleAttendance">
            <span class="md-nav-icon"><i class="bi bi-calendar2-check-fill"></i></span>
            <span class="md-nav-text">Attendance</span>
            <i class="bi bi-chevron-down md-nav-arrow"></i>
          </button>
          <div class="md-nav-group-items">
            <RouterLink to="/attendance" class="md-nav-link" :class="{ active: isActive('/attendance') }">
              <span class="md-nav-icon sm"><i class="bi bi-calendar-check-fill"></i></span>
              <span class="md-nav-text">Records</span>
            </RouterLink>
            <RouterLink v-if="hasRole(['admin', 'pastor'])" to="/visitors" class="md-nav-link"
              :class="{ active: isActive('/visitors') }">
              <span class="md-nav-icon sm"><i class="bi bi-person-plus-fill"></i></span>
              <span class="md-nav-text">Visitors</span>
            </RouterLink>
          </div>
        </div>

        <!-- Follow-ups -->
        <div v-if="hasRole(['admin', 'pastor', 'pr_follow_up'])" class="md-nav-group"
          :class="{ expanded: followUpOpen }">
          <button class="md-nav-group-toggle" @click="toggleFollowUp">
            <span class="md-nav-icon"><i class="bi bi-chat-heart-fill"></i></span>
            <span class="md-nav-text">Follow-ups</span>
            <span v-if="dueFollowUps > 0" class="md-nav-badge danger">{{ dueFollowUps }}</span>
            <i class="bi bi-chevron-down md-nav-arrow"></i>
          </button>
          <div class="md-nav-group-items">
            <RouterLink to="/follow-ups" class="md-nav-link" :class="{ active: isActive('/follow-ups') }">
              <span class="md-nav-icon sm"><i class="bi bi-people-fill"></i></span>
              <span class="md-nav-text">All Visitors</span>
            </RouterLink>
          </div>
        </div>

        <!-- Finance -->
        <div v-if="hasRole(['admin', 'finance'])" class="md-nav-group" :class="{ expanded: financeOpen }">
          <button class="md-nav-group-toggle" @click="toggleFinance">
            <span class="md-nav-icon"><i class="bi bi-cash-coin"></i></span>
            <span class="md-nav-text">Finance</span>
            <i class="bi bi-chevron-down md-nav-arrow"></i>
          </button>
          <div class="md-nav-group-items">
            <RouterLink to="/contributions" class="md-nav-link" :class="{ active: isActive('/contributions') }">
              <span class="md-nav-icon sm"><i class="bi bi-cash-stack"></i></span>
              <span class="md-nav-text">Contributions</span>
            </RouterLink>
            <RouterLink to="/expense" class="md-nav-link" :class="{ active: isActive('/expense') }">
              <span class="md-nav-icon sm"><i class="bi bi-receipt"></i></span>
              <span class="md-nav-text">Expenses</span>
            </RouterLink>
            <RouterLink to="/expense-types" class="md-nav-link" :class="{ active: isActive('/expense-types') }">
              <span class="md-nav-icon sm"><i class="bi bi-tags-fill"></i></span>
              <span class="md-nav-text">Expense Types</span>
            </RouterLink>
          </div>
        </div>

        <!-- Departments -->
        <div v-if="hasRole(['admin', 'pastor', 'department_leader'])" class="md-nav-group"
          :class="{ expanded: departmentOpen }">
          <button class="md-nav-group-toggle" @click="toggleDepartment">
            <span class="md-nav-icon"><i class="bi bi-diagram-3-fill"></i></span>
            <span class="md-nav-text">Departments</span>
            <i class="bi bi-chevron-down md-nav-arrow"></i>
          </button>
          <div class="md-nav-group-items">
            <RouterLink v-if="hasRole(['admin', 'pastor'])" to="/departments" class="md-nav-link"
              :class="{ active: isActive('/departments') }">
              <span class="md-nav-icon sm"><i class="bi bi-building"></i></span>
              <span class="md-nav-text">All Departments</span>
            </RouterLink>
            <RouterLink v-if="hasRole(['department_leader'])" to="/my-department" class="md-nav-link"
              :class="{ active: isActive('/my-department') }">
              <span class="md-nav-icon sm"><i class="bi bi-people-fill"></i></span>
              <span class="md-nav-text">My Department</span>
            </RouterLink>
          </div>
        </div>

        <!-- Settings (Admin only) -->
        <RouterLink v-if="auth.hasRole(['admin'])" to="/settings" class="md-nav-link"
          :class="{ active: isActive('/settings') }">
          <span class="md-nav-icon"><i class="bi bi-gear-fill"></i></span>
          <span class="md-nav-text">Settings</span>
        </RouterLink>
      </nav>

      <!-- Footer -->
      <div class="md-sidebar-footer">
        <div class="md-sidebar-version">
          <i class="bi bi-lightning-charge"></i>
          <span>PLE CMS v0.1.0</span>
        </div>
      </div>
    </aside>
    <!-- Overlay for mobile -->
    <div v-if="visible" class="md-sidebar-overlay" @click="close"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '../../store/auth';
import { dashboardApi } from '../../api';

defineProps({
  visible: { type: Boolean, default: true },
});

const emit = defineEmits(['visible-change']);

const route = useRoute();
const auth = useAuthStore();

// Navigation state
const reportsOpen = ref(false);
const adminOpen = ref(false);
const attendanceOpen = ref(false);
const followUpOpen = ref(false);
const financeOpen = ref(false);
const departmentOpen = ref(false);

// Check active route
function isActive(path) {
  // Only match exact for /expense and /expense-types
  if (path === '/expense' || path === '/expense-types') {
    return route.path === path;
  }
  return route.path.startsWith(path);
}

// Toggle functions
function toggleReports() {
  reportsOpen.value = !reportsOpen.value;
}
function toggleAdmin() {
  adminOpen.value = !adminOpen.value;
}
function toggleAttendance() {
  attendanceOpen.value = !attendanceOpen.value;
}
function toggleFollowUp() {
  followUpOpen.value = !followUpOpen.value;
}
function toggleFinance() {
  financeOpen.value = !financeOpen.value;
}
function toggleDepartment() {
  departmentOpen.value = !departmentOpen.value;
}

function close() {
  emit('visible-change', false);
}

// Auto-expand based on current route
watch(
  () => route.path,
  path => {
    if (path.startsWith('/reports')) reportsOpen.value = true;
    if (
      ['/users', '/roles-permissions', '/attendance-approvals', '/broadcasts', '/audit-logs'].some(
        p => path.startsWith(p)
      )
    )
      adminOpen.value = true;
    if (['/attendance', '/visitors'].some(p => path.startsWith(p))) attendanceOpen.value = true;
    if (path.startsWith('/follow-ups')) followUpOpen.value = true;
    if (['/contributions', '/expense'].some(p => path.startsWith(p))) financeOpen.value = true;
    if (['/departments', '/my-department'].some(p => path.startsWith(p)))
      departmentOpen.value = true;
  },
  { immediate: true }
);

// Role-based access
const userRole = computed(() => auth.user?.role || null);

function hasRole(roles) {
  return roles.includes(userRole.value);
}

// Badge counts
const pendingApprovals = ref(0);
const dueFollowUps = ref(0);

onMounted(async () => {
  if (auth.isAuthenticated) {
    try {
      const { data } = await dashboardApi.stats();
      if (data.success && data.data) {
        const quickActions = data.data.quick_actions || [];
        pendingApprovals.value =
          quickActions.find(a => a.label?.toLowerCase().includes('pending approval'))?.count || 0;
        dueFollowUps.value =
          quickActions.find(a => a.label?.toLowerCase().includes('follow-up'))?.count || 0;
      }
    } catch (e) {
      console.warn('Failed to load sidebar stats:', e);
    }
  }
});
</script>

<style scoped>
/* ========================================
   MATERIAL SIDEBAR
   ======================================== */

.md-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  height: 100dvh;
  width: 280px;
  background: var(--md-surface);
  border-right: 1px solid var(--md-outline-variant);
  display: flex;
  flex-direction: column;
  z-index: var(--md-z-fixed);
  transform: translateX(-100%);
  transition: transform var(--md-motion-duration-medium2) var(--md-motion-easing-emphasized);
  box-shadow: var(--md-elevation-3);
}

.md-sidebar-visible {
  transform: translateX(0);
}

@media (min-width: 992px) {
  .md-sidebar-visible {
    transform: translateX(0);
  }
}

/* Header */
.md-sidebar-header {
  padding: var(--md-space-6);
  border-bottom: 1px solid var(--md-outline-variant);
}

.md-sidebar-brand {
  display: flex;
  align-items: center;
  gap: var(--md-space-4);
}

.md-sidebar-logo {
  width: 48px;
  height: 48px;
  background: var(--md-gradient-primary);
  border-radius: var(--md-shape-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--md-on-primary);
  box-shadow: var(--md-shadow-primary);
}

.md-sidebar-brand-text {
  display: flex;
  flex-direction: column;
}

.md-sidebar-title {
  font: var(--md-headline-medium);
  background: var(--md-gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.md-sidebar-subtitle {
  font: var(--md-label-small);
  color: var(--md-on-surface-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Navigation */
.md-sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: var(--md-space-4);
}

/* Nav Link */
.md-nav-link {
  display: flex;
  align-items: center;
  gap: var(--md-space-4);
  padding: var(--md-space-3) var(--md-space-4);
  border-radius: var(--md-shape-full);
  color: var(--md-on-surface-variant);
  text-decoration: none;
  font: var(--md-label-large);
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
  margin-bottom: var(--md-space-1);
  position: relative;
}

.md-nav-link:hover {
  background: var(--md-surface-container);
  color: var(--md-on-surface);
}

.md-nav-link.active {
  background: var(--md-primary-container);
  color: var(--md-on-primary-container);
}

/* Nav Icon */
.md-nav-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--md-shape-full);
  background: var(--md-surface-container);
  font-size: 1.125rem;
  flex-shrink: 0;
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
}

.md-nav-icon.sm {
  width: 32px;
  height: 32px;
  font-size: 0.875rem;
}

.md-nav-link:hover .md-nav-icon {
  background: var(--md-surface-container-high);
  transform: scale(1.1);
}

.md-nav-link.active .md-nav-icon {
  background: var(--md-primary);
  color: var(--md-on-primary);
}

.md-nav-text {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Nav Badge */
.md-nav-badge {
  font: var(--md-label-small);
  padding: var(--md-space-1) var(--md-space-2);
  border-radius: var(--md-shape-full);
  min-width: 20px;
  text-align: center;
}

.md-nav-badge.warning {
  background: var(--md-warning);
  color: var(--md-on-warning);
}

.md-nav-badge.danger {
  background: var(--md-error);
  color: var(--md-on-error);
}

/* Nav Group */
.md-nav-group {
  margin-bottom: var(--md-space-2);
}

.md-nav-group-toggle {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--md-space-4);
  padding: var(--md-space-3) var(--md-space-4);
  border-radius: var(--md-shape-full);
  color: var(--md-on-surface-variant);
  font: var(--md-label-large);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
}

.md-nav-group-toggle:hover {
  background: var(--md-surface-container);
  color: var(--md-on-surface);
}

.md-nav-arrow {
  margin-left: auto;
  font-size: 0.75rem;
  transition: transform var(--md-motion-duration-short4) var(--md-motion-easing-standard);
}

.md-nav-group.expanded .md-nav-arrow {
  transform: rotate(180deg);
}

.md-nav-group-items {
  display: none;
  padding-left: var(--md-space-6);
  margin-top: var(--md-space-1);
  border-left: 2px solid var(--md-outline-variant);
  margin-left: var(--md-space-6);
}

.md-nav-group.expanded .md-nav-group-items {
  display: block;
  animation: slideDown var(--md-motion-duration-medium1) var(--md-motion-easing-emphasized);
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Footer */
.md-sidebar-footer {
  padding: var(--md-space-4) var(--md-space-6);
  border-top: 1px solid var(--md-outline-variant);
}

.md-sidebar-version {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--md-space-2);
  font: var(--md-body-small);
  color: var(--md-on-surface-muted);
}

.md-sidebar-version i {
  color: var(--md-primary);
}

/* Overlay */
.md-sidebar-overlay {
  display: none;
}

@media (max-width: 991.98px) {
  .md-sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: var(--md-scrim);
    backdrop-filter: blur(4px);
    z-index: calc(var(--md-z-fixed) - 1);
    animation: fadeIn var(--md-motion-duration-short4) var(--md-motion-easing-standard);
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }
}
</style>
