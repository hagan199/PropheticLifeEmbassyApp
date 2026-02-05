<template>
  <CHeader class="header">
    <div class="d-flex align-items-center w-100">
      <!-- Menu Toggle -->
      <button class="menu-btn" @click="toggleSidebar" aria-label="Toggle sidebar">
        <span class="menu-icon">
          <span></span>
          <span></span>
          <span></span>
        </span>
      </button>

      <!-- Brand -->
      <CHeaderBrand class="d-none d-lg-flex align-items-center ms-3">
        <span class="brand-text">Prophetic Life Embassy</span>
      </CHeaderBrand>

      <!-- Search Trigger -->
      <div class="search-wrapper flex-grow-1 d-none d-md-block px-4">
        <div class="search-box" @click="openSearch">
          <i class="bi bi-search search-icon"></i>
          <span class="search-placeholder">Search pages...</span>
          <kbd class="search-kbd">⌘K</kbd>
        </div>
      </div>

    <!-- Search Modal -->
    <teleport to="body">
      <transition name="search-modal">
        <div v-if="searchOpen" class="search-overlay" @click.self="closeSearch">
          <div class="search-modal">
            <div class="search-header">
              <i class="bi bi-search"></i>
              <input
                ref="searchInputRef"
                type="text"
                v-model="searchQuery"
                placeholder="Search pages, actions..."
                @keydown.esc="closeSearch"
                @keydown.down.prevent="navigateResults(1)"
                @keydown.up.prevent="navigateResults(-1)"
                @keydown.enter.prevent="selectResult"
              />
              <kbd @click="closeSearch">ESC</kbd>
            </div>
            <div class="search-results" v-if="filteredResults.length > 0">
              <div class="results-group" v-for="group in groupedResults" :key="group.category">
                <div class="group-label">{{ group.category }}</div>
                <div
                  v-for="(item, idx) in group.items"
                  :key="item.path"
                  class="result-item"
                  :class="{ active: selectedIndex === getGlobalIndex(group.category, idx) }"
                  @click="goToPage(item)"
                  @mouseenter="selectedIndex = getGlobalIndex(group.category, idx)"
                >
                  <div class="result-icon" :style="{ background: item.color }">
                    <i :class="item.icon"></i>
                  </div>
                  <div class="result-info">
                    <div class="result-name">{{ item.name }}</div>
                    <div class="result-path">{{ item.path }}</div>
                  </div>
                  <i class="bi bi-arrow-return-left result-enter"></i>
                </div>
              </div>
            </div>
            <div class="search-empty" v-else-if="searchQuery">
              <i class="bi bi-search"></i>
              <span>No results for "{{ searchQuery }}"</span>
            </div>
            <div class="search-footer">
              <div class="footer-hint"><kbd>↑↓</kbd> Navigate</div>
              <div class="footer-hint"><kbd>↵</kbd> Select</div>
              <div class="footer-hint"><kbd>ESC</kbd> Close</div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

      <!-- Nav Actions -->
      <CHeaderNav class="ms-auto d-flex align-items-center gap-2">
        <!-- Theme Toggle -->
        <button class="nav-action-btn" @click="theme.toggle()" :title="theme.mode === 'dark' ? 'Light mode' : 'Dark mode'">
          <transition name="theme-icon" mode="out-in">
            <i v-if="theme.mode === 'dark'" key="sun" class="bi bi-sun-fill"></i>
            <i v-else key="moon" class="bi bi-moon-stars-fill"></i>
          </transition>
        </button>

        <!-- Notifications -->
        <CDropdown alignment="end">
          <CDropdownToggle class="nav-action-btn notification-btn" :caret="false">
            <i class="bi bi-bell-fill"></i>
            <span v-if="unreadCount > 0" class="notification-badge">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </CDropdownToggle>
          <CDropdownMenu class="notifications-dropdown">
            <div class="dropdown-header-custom">
              <div class="header-title">
                <i class="bi bi-bell-fill me-2"></i>
                Notifications
              </div>
              <button v-if="unreadCount > 0" class="mark-read-btn" @click="markAllRead">
                Mark all read
              </button>
            </div>
            <div class="notifications-list">
              <div
                v-for="n in notifications"
                :key="n.id"
                class="notification-item"
                :class="{ unread: !n.read }"
                @click="handleNotification(n)"
              >
                <div class="notification-icon" :class="n.type">
                  <i :class="getNotificationIcon(n.type)"></i>
                </div>
                <div class="notification-content">
                  <div class="notification-title">{{ n.title }}</div>
                  <div class="notification-message">{{ n.message }}</div>
                  <div class="notification-time">
                    <i class="bi bi-clock me-1"></i>{{ n.time }}
                  </div>
                </div>
              </div>
              <div v-if="notifications.length === 0" class="empty-notifications">
                <i class="bi bi-bell-slash"></i>
                <span>No notifications</span>
              </div>
            </div>
            <div class="dropdown-footer-custom" @click="router.push('/notifications')">
              <span>View All Notifications</span>
              <i class="bi bi-arrow-right"></i>
            </div>
          </CDropdownMenu>
        </CDropdown>

        <!-- Profile -->
        <CDropdown alignment="end">
          <CDropdownToggle class="profile-btn" :caret="false">
            <div class="profile-avatar">
              <span class="avatar-text">{{ userInitials }}</span>
              <span class="status-indicator"></span>
            </div>
            <div class="profile-info d-none d-sm-block">
              <span class="profile-name">{{ displayName }}</span>
              <span class="profile-role">{{ auth.user?.role || 'User' }}</span>
            </div>
            <i class="bi bi-chevron-down chevron-icon d-none d-sm-block"></i>
          </CDropdownToggle>
          <CDropdownMenu class="profile-dropdown">
            <div class="profile-header">
              <div class="profile-avatar-large">
                <span>{{ userInitials }}</span>
              </div>
              <div class="profile-details">
                <div class="name">{{ auth.user?.name || 'User' }}</div>
                <div class="email">{{ auth.user?.email || '' }}</div>
              </div>
            </div>
            <div class="dropdown-divider-custom"></div>
            <div class="menu-item" @click="goToProfile">
              <i class="bi bi-person"></i>
              <span>My Profile</span>
            </div>
            <div class="menu-item" @click="goToSettings">
              <i class="bi bi-gear"></i>
              <span>Settings</span>
            </div>
            <div class="dropdown-divider-custom"></div>
            <div class="menu-item danger" @click="signOut">
              <i class="bi bi-box-arrow-right"></i>
              <span>Sign Out</span>
            </div>
          </CDropdownMenu>
        </CDropdown>
      </CHeaderNav>
    </div>
  </CHeader>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { CHeader, CHeaderBrand, CHeaderNav, CDropdown, CDropdownToggle, CDropdownMenu } from '@coreui/vue'
import { useAuthStore } from '../store/auth'
import { useThemeStore } from '../store/theme'
import { useRouter } from 'vue-router'
import { dashboardApi } from '../api/dashboard'

const props = defineProps({ toggleSidebar: { type: Function, default: () => { } } })
const auth = useAuthStore()
const theme = useThemeStore()
const router = useRouter()

// Search
const searchOpen = ref(false)
const searchQuery = ref('')
const searchInputRef = ref(null)
const selectedIndex = ref(0)

// All searchable pages
const allPages = [
  { name: 'Dashboard', path: '/dashboard', icon: 'bi bi-grid-1x2-fill', category: 'Pages', color: 'rgba(99, 102, 241, 0.15)', roles: ['admin', 'pastor', 'usher', 'finance', 'pr_follow_up', 'department_leader'] },
  { name: 'Users', path: '/users', icon: 'bi bi-people-fill', category: 'Administration', color: 'rgba(239, 68, 68, 0.15)', roles: ['admin'] },
  { name: 'Roles & Permissions', path: '/roles-permissions', icon: 'bi bi-key-fill', category: 'Administration', color: 'rgba(239, 68, 68, 0.15)', roles: ['admin'] },
  { name: 'Attendance Approvals', path: '/attendance-approvals', icon: 'bi bi-clipboard-check-fill', category: 'Administration', color: 'rgba(239, 68, 68, 0.15)', roles: ['admin'] },
  { name: 'Broadcasts', path: '/broadcasts', icon: 'bi bi-broadcast-pin', category: 'Administration', color: 'rgba(239, 68, 68, 0.15)', roles: ['admin'] },
  { name: 'Audit Logs', path: '/audit-logs', icon: 'bi bi-journal-text', category: 'Administration', color: 'rgba(239, 68, 68, 0.15)', roles: ['admin'] },
  { name: 'Attendance Records', path: '/attendance', icon: 'bi bi-calendar-check-fill', category: 'Attendance', color: 'rgba(16, 185, 129, 0.15)', roles: ['admin', 'pastor', 'usher'] },
  { name: 'Visitors', path: '/visitors', icon: 'bi bi-person-plus-fill', category: 'Attendance', color: 'rgba(16, 185, 129, 0.15)', roles: ['admin', 'pastor'] },
  { name: 'Follow-ups', path: '/follow-ups', icon: 'bi bi-chat-heart-fill', category: 'Follow-ups', color: 'rgba(245, 158, 11, 0.15)', roles: ['admin', 'pastor', 'pr_follow_up'] },
  { name: 'Contributions', path: '/contributions', icon: 'bi bi-cash-stack', category: 'Finance', color: 'rgba(59, 130, 246, 0.15)', roles: ['admin', 'finance'] },
  { name: 'Expenses', path: '/expense', icon: 'bi bi-receipt', category: 'Finance', color: 'rgba(59, 130, 246, 0.15)', roles: ['admin', 'finance'] },
  { name: 'Expense Types', path: '/expense-types', icon: 'bi bi-tags-fill', category: 'Finance', color: 'rgba(59, 130, 246, 0.15)', roles: ['admin', 'finance'] },
  { name: 'Departments', path: '/departments', icon: 'bi bi-diagram-3-fill', category: 'Departments', color: 'rgba(139, 92, 246, 0.15)', roles: ['admin', 'pastor'] },
  { name: 'My Department', path: '/my-department', icon: 'bi bi-people-fill', category: 'Departments', color: 'rgba(139, 92, 246, 0.15)', roles: ['department_leader'] },
  { name: 'My Submissions', path: '/my-submissions', icon: 'bi bi-send-check-fill', category: 'Pages', color: 'rgba(99, 102, 241, 0.15)', roles: ['usher'] },
  { name: 'Settings', path: '/settings', icon: 'bi bi-gear-fill', category: 'Pages', color: 'rgba(100, 116, 139, 0.15)', roles: ['admin', 'pastor', 'usher', 'finance', 'pr_follow_up', 'department_leader'] },
  { name: 'Profile', path: '/profile', icon: 'bi bi-person-fill', category: 'Pages', color: 'rgba(100, 116, 139, 0.15)', roles: ['admin', 'pastor', 'usher', 'finance', 'pr_follow_up', 'department_leader'] },
]

// Filter pages based on user role and search query
const filteredResults = computed(() => {
  const userRole = auth.user?.role || ''
  const query = searchQuery.value.toLowerCase().trim()

  return allPages.filter(page => {
    const hasRole = page.roles.includes(userRole)
    const matchesQuery = !query ||
      page.name.toLowerCase().includes(query) ||
      page.category.toLowerCase().includes(query) ||
      page.path.toLowerCase().includes(query)
    return hasRole && matchesQuery
  })
})

// Group results by category
const groupedResults = computed(() => {
  const groups = {}
  filteredResults.value.forEach(item => {
    if (!groups[item.category]) {
      groups[item.category] = []
    }
    groups[item.category].push(item)
  })
  return Object.keys(groups).map(category => ({
    category,
    items: groups[category]
  }))
})

// Get global index for keyboard navigation
function getGlobalIndex(category, localIdx) {
  let globalIdx = 0
  for (const group of groupedResults.value) {
    if (group.category === category) {
      return globalIdx + localIdx
    }
    globalIdx += group.items.length
  }
  return 0
}

function openSearch() {
  searchOpen.value = true
  searchQuery.value = ''
  selectedIndex.value = 0
  nextTick(() => {
    searchInputRef.value?.focus()
  })
}

function closeSearch() {
  searchOpen.value = false
  searchQuery.value = ''
}

function navigateResults(direction) {
  const total = filteredResults.value.length
  if (total === 0) return
  selectedIndex.value = (selectedIndex.value + direction + total) % total
}

function selectResult() {
  if (filteredResults.value.length > 0) {
    goToPage(filteredResults.value[selectedIndex.value])
  }
}

function goToPage(item) {
  router.push(item.path)
  closeSearch()
}

// Keyboard shortcut (Cmd/Ctrl + K)
function handleKeydown(e) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    if (searchOpen.value) {
      closeSearch()
    } else {
      openSearch()
    }
  }
}

// Notifications
const notifications = ref([])
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

async function fetchNotifications() {
  try {
    const response = await dashboardApi.getStats()
    if (response.data.success && response.data.data.quick_actions) {
      notifications.value = response.data.data.quick_actions
        .filter(a => a.count > 0)
        .map((action, idx) => ({
          id: idx + 1,
          type: action.label.includes('Approval') ? 'approval' : action.label.includes('Visitor') ? 'visitor' : 'expense',
          title: action.label,
          message: `${action.count} items require your attention`,
          time: 'Just now',
          read: false,
          link: action.link
        }))
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

function getNotificationIcon(type) {
  const icons = {
    approval: 'bi bi-clipboard-check-fill',
    visitor: 'bi bi-person-plus-fill',
    expense: 'bi bi-receipt',
    default: 'bi bi-bell-fill'
  }
  return icons[type] || icons.default
}

function handleNotification(n) {
  n.read = true
  if (n.link) {
    router.push(n.link)
  }
}

function markAllRead() {
  notifications.value.forEach(n => n.read = true)
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  fetchNotifications()
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

const userInitials = computed(() => auth.userInitials || 'U')
const displayName = computed(() => {
  const name = auth.user?.name || 'User'
  return name.split(' ')[0]
})

async function signOut() {
  await auth.signOut()
  router.push('/login')
}

function goToProfile() {
  router.push('/profile')
}

function goToSettings() {
  router.push('/settings')
}

function toggleSidebar() {
  props.toggleSidebar()
}
</script>

<style scoped>
/* Header */
:deep(.header) {
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.6) !important;
  padding: 0.625rem 1.25rem !important;
  position: sticky;
  top: 0;
  z-index: 1020;
  min-height: 64px;
  transition: all 0.3s ease;
  width: 100%;
}

.theme-dark :deep(.header) {
  background: rgba(15, 23, 42, 0.85) !important;
  border-bottom-color: rgba(51, 65, 85, 0.6) !important;
}

/* Menu Button */
.menu-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.menu-btn:hover {
  background: rgba(99, 102, 241, 0.08);
}

.menu-icon {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 18px;
}

.menu-icon span {
  display: block;
  height: 2px;
  background: #64748b;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.menu-icon span:nth-child(1) { width: 100%; }
.menu-icon span:nth-child(2) { width: 75%; }
.menu-icon span:nth-child(3) { width: 50%; }

.menu-btn:hover .menu-icon span {
  background: #6366f1;
  width: 100%;
}

.theme-dark .menu-icon span {
  background: #94a3b8;
}

/* Brand */
.brand-text {
  font-weight: 700;
  font-size: 1.125rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Search Trigger */
.search-wrapper {
  max-width: 400px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  height: 40px;
  padding: 0 14px;
  border: 1.5px solid transparent;
  border-radius: 12px;
  background: #f1f5f9;
  cursor: pointer;
  transition: all 0.25s ease;
}

.theme-dark .search-box {
  background: rgba(255, 255, 255, 0.06);
}

.search-box:hover {
  background: #e2e8f0;
  border-color: rgba(99, 102, 241, 0.2);
}

.theme-dark .search-box:hover {
  background: rgba(255, 255, 255, 0.1);
}

.search-icon {
  color: #94a3b8;
  font-size: 0.875rem;
}

.search-placeholder {
  flex: 1;
  font-size: 0.875rem;
  color: #94a3b8;
}

.search-kbd {
  padding: 4px 8px;
  font-size: 0.7rem;
  font-family: inherit;
  background: rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 6px;
  color: #94a3b8;
}

.theme-dark .search-kbd {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.1);
}

/* Search Modal */
.search-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 15vh;
}

.search-modal {
  width: 100%;
  max-width: 560px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  animation: modalSlideIn 0.2s ease;
}

.theme-dark .search-modal {
  background: #1e293b;
  border: 1px solid #334155;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.search-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e2e8f0;
}

.theme-dark .search-header {
  border-color: #334155;
}

.search-header i {
  color: #6366f1;
  font-size: 1.125rem;
}

.search-header input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 1rem;
  color: #334155;
  outline: none;
}

.theme-dark .search-header input {
  color: #e2e8f0;
}

.search-header input::placeholder {
  color: #94a3b8;
}

.search-header kbd {
  padding: 4px 8px;
  font-size: 0.7rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  color: #64748b;
  cursor: pointer;
}

.theme-dark .search-header kbd {
  background: #334155;
  border-color: #475569;
  color: #94a3b8;
}

.search-results {
  max-height: 400px;
  overflow-y: auto;
  padding: 0.5rem;
}

.results-group {
  margin-bottom: 0.5rem;
}

.group-label {
  padding: 0.5rem 0.75rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.75rem;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.result-item:hover,
.result-item.active {
  background: rgba(99, 102, 241, 0.08);
}

.result-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #6366f1;
}

.theme-dark .result-icon {
  color: #a5b4fc;
}

.result-info {
  flex: 1;
}

.result-name {
  font-weight: 500;
  font-size: 0.9rem;
  color: #334155;
}

.theme-dark .result-name {
  color: #e2e8f0;
}

.result-path {
  font-size: 0.75rem;
  color: #94a3b8;
}

.result-enter {
  color: #94a3b8;
  font-size: 0.875rem;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.result-item:hover .result-enter,
.result-item.active .result-enter {
  opacity: 1;
}

.search-empty {
  padding: 3rem 1rem;
  text-align: center;
  color: #94a3b8;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.search-empty i {
  font-size: 2rem;
  opacity: 0.5;
}

.search-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.theme-dark .search-footer {
  background: #0f172a;
  border-color: #334155;
}

.footer-hint {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #64748b;
}

.footer-hint kbd {
  padding: 2px 6px;
  font-size: 0.65rem;
  background: #e2e8f0;
  border-radius: 4px;
  color: #475569;
}

.theme-dark .footer-hint kbd {
  background: #334155;
  color: #94a3b8;
}

/* Search Modal Transition */
.search-modal-enter-active,
.search-modal-leave-active {
  transition: opacity 0.2s ease;
}

.search-modal-enter-from,
.search-modal-leave-to {
  opacity: 0;
}

.search-modal-enter-from .search-modal,
.search-modal-leave-to .search-modal {
  transform: scale(0.95) translateY(-10px);
}

/* Nav Action Button */
.nav-action-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  transition: all 0.25s ease;
  font-size: 1.1rem;
}

.theme-dark .nav-action-btn {
  background: rgba(255, 255, 255, 0.06);
  color: #94a3b8;
}

.nav-action-btn:hover {
  background: #e2e8f0;
  color: #6366f1;
  transform: translateY(-2px);
}

.theme-dark .nav-action-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #a5b4fc;
}

/* Theme Icon Transition */
.theme-icon-enter-active,
.theme-icon-leave-active {
  transition: all 0.25s ease;
}

.theme-icon-enter-from {
  opacity: 0;
  transform: rotate(-90deg) scale(0.5);
}

.theme-icon-leave-to {
  opacity: 0;
  transform: rotate(90deg) scale(0.5);
}

/* Notification Badge */
.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  animation: badgePulse 2s infinite;
}

.theme-dark .notification-badge {
  border-color: #0f172a;
}

@keyframes badgePulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

/* Notifications Dropdown */
.notifications-dropdown {
  width: 360px !important;
  padding: 0 !important;
  border-radius: 16px !important;
  overflow: hidden;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15) !important;
}

.theme-dark .notifications-dropdown {
  background: #1e293b !important;
  border-color: #334155 !important;
}

.dropdown-header-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.05) 100%);
  border-bottom: 1px solid #e2e8f0;
}

.theme-dark .dropdown-header-custom {
  background: rgba(99, 102, 241, 0.1);
  border-color: #334155;
}

.header-title {
  font-weight: 600;
  font-size: 0.9rem;
  color: #334155;
  display: flex;
  align-items: center;
}

.header-title i {
  color: #6366f1;
}

.theme-dark .header-title {
  color: #e2e8f0;
}

.mark-read-btn {
  background: none;
  border: none;
  color: #6366f1;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mark-read-btn:hover {
  color: #4f46e5;
  text-decoration: underline;
}

.notifications-list {
  max-height: 320px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  padding: 1rem 1.25rem;
  gap: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f1f5f9;
}

.theme-dark .notification-item {
  border-color: #334155;
}

.notification-item:hover {
  background: rgba(99, 102, 241, 0.05);
}

.notification-item.unread {
  background: rgba(99, 102, 241, 0.04);
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: #6366f1;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.notification-icon.approval {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.notification-icon.visitor {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.notification-icon.expense {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  font-size: 0.8125rem;
  color: #334155;
  margin-bottom: 2px;
}

.theme-dark .notification-title {
  color: #e2e8f0;
}

.notification-message {
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 4px;
}

.notification-time {
  font-size: 0.7rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
}

.empty-notifications {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.empty-notifications i {
  font-size: 2rem;
  opacity: 0.5;
}

.dropdown-footer-custom {
  padding: 0.875rem 1.25rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #6366f1;
  cursor: pointer;
  border-top: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.theme-dark .dropdown-footer-custom {
  border-color: #334155;
}

.dropdown-footer-custom:hover {
  background: rgba(99, 102, 241, 0.08);
}

/* Profile Button */
.profile-btn {
  display: flex !important;
  align-items: center !important;
  gap: 0.625rem;
  padding: 0.375rem 0.875rem 0.375rem 0.375rem !important;
  border-radius: 12px !important;
  background: #f1f5f9 !important;
  border: none !important;
  cursor: pointer;
  transition: all 0.25s ease;
}

.theme-dark .profile-btn {
  background: rgba(255, 255, 255, 0.06) !important;
}

.profile-btn:hover {
  background: #e2e8f0 !important;
  transform: translateY(-1px);
}

.theme-dark .profile-btn:hover {
  background: rgba(255, 255, 255, 0.12) !important;
}

.profile-avatar {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.avatar-text {
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-indicator {
  position: absolute;
  bottom: -1px;
  right: -1px;
  width: 10px;
  height: 10px;
  background: #10b981;
  border-radius: 50%;
  border: 2px solid #f1f5f9;
}

.theme-dark .status-indicator {
  border-color: #1e293b;
}

.profile-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.profile-name {
  font-weight: 600;
  font-size: 0.8125rem;
  color: #334155;
  line-height: 1.2;
}

.theme-dark .profile-name {
  color: #e2e8f0;
}

.profile-role {
  font-size: 0.7rem;
  color: #94a3b8;
  text-transform: capitalize;
}

.chevron-icon {
  font-size: 0.75rem;
  color: #94a3b8;
  transition: transform 0.2s ease;
}

.profile-btn:hover .chevron-icon {
  transform: translateY(2px);
}

/* Profile Dropdown */
.profile-dropdown {
  width: 240px !important;
  padding: 0 !important;
  border-radius: 16px !important;
  overflow: hidden;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15) !important;
}

.theme-dark .profile-dropdown {
  background: #1e293b !important;
  border-color: #334155 !important;
}

.profile-header {
  padding: 1.25rem;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.05) 100%);
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.theme-dark .profile-header {
  background: rgba(99, 102, 241, 0.1);
}

.profile-avatar-large {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.profile-details .name {
  font-weight: 600;
  font-size: 0.9rem;
  color: #334155;
}

.theme-dark .profile-details .name {
  color: #e2e8f0;
}

.profile-details .email {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 2px;
}

.dropdown-divider-custom {
  height: 1px;
  background: #e2e8f0;
  margin: 0.5rem 0;
}

.theme-dark .dropdown-divider-custom {
  background: #334155;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  font-size: 0.875rem;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-dark .menu-item {
  color: #cbd5e1;
}

.menu-item i {
  font-size: 1rem;
  width: 20px;
  color: #64748b;
}

.menu-item:hover {
  background: rgba(99, 102, 241, 0.08);
  color: #6366f1;
}

.menu-item:hover i {
  color: #6366f1;
}

.menu-item.danger {
  color: #ef4444;
}

.menu-item.danger i {
  color: #ef4444;
}

.menu-item.danger:hover {
  background: rgba(239, 68, 68, 0.08);
}

/* Hide dropdown caret */
:deep(.dropdown-toggle)::after {
  display: none;
}

/* Responsive */
@media (max-width: 991.98px) {
  :deep(.header) {
    padding: 0.5rem 1rem !important;
    min-height: 56px;
  }

  .brand-text {
    font-size: 1rem;
  }

  .search-wrapper {
    display: none !important;
  }
}

@media (max-width: 768px) {
  .nav-action-btn,
  .menu-btn {
    width: 36px;
    height: 36px;
  }

  .notifications-dropdown {
    width: calc(100vw - 2rem) !important;
    max-width: 360px;
    right: -1rem !important;
    left: auto !important;
  }

  .profile-dropdown {
    width: calc(100vw - 2rem) !important;
    max-width: 280px;
    right: -0.5rem !important;
    left: auto !important;
  }
}

@media (max-width: 576px) {
  :deep(.header) {
    padding: 0.5rem 0.75rem !important;
  }

  .nav-action-btn,
  .menu-btn {
    width: 34px;
    height: 34px;
    font-size: 1rem;
  }

  .profile-btn {
    padding: 0.25rem 0.5rem 0.25rem 0.25rem !important;
  }

  .profile-avatar {
    width: 30px;
    height: 30px;
  }

  .avatar-text {
    font-size: 0.7rem;
  }

  .gap-2 {
    gap: 0.375rem !important;
  }

  .notifications-dropdown,
  .profile-dropdown {
    position: fixed !important;
    left: 1rem !important;
    right: 1rem !important;
    width: auto !important;
    max-width: none !important;
    margin-top: 0.5rem !important;
  }
}
</style>
