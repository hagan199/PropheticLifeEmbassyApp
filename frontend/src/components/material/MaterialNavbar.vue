<template>
  <header class="md-appbar" :class="{ 'md-appbar-elevated': isScrolled }">
    <!-- Menu Toggle -->
    <button class="md-icon-btn" aria-label="Toggle sidebar" @click="toggleSidebar">
      <i class="bi bi-list"></i>
    </button>

    <!-- Page Title (Mobile) -->
    <h1 class="md-appbar-title d-lg-none">{{ pageTitle }}</h1>

    <!-- Search (Desktop) -->
    <div class="md-search-container d-none d-lg-flex">
      <div class="md-search" :class="{ focused: searchFocused }">
        <i class="bi bi-search md-search-icon"></i>
        <input v-model="searchQuery" type="text" placeholder="Search..." class="md-search-input"
          @focus="searchFocused = true" @blur="searchFocused = false" />
        <kbd v-if="!searchFocused" class="md-search-kbd">⌘K</kbd>
      </div>
    </div>

    <!-- Spacer -->
    <div class="md-spacer"></div>

    <!-- Theme Toggle -->
    <button class="md-icon-btn" :title="isDark ? 'Light mode' : 'Dark mode'" @click="toggleTheme">
      <i :class="isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill'"></i>
    </button>

    <!-- Notifications -->
    <div ref="notifDropdown" class="md-dropdown">
      <button class="md-icon-btn md-badge-wrap" @click="toggleNotifications">
        <i class="bi bi-bell-fill"></i>
        <span v-if="unreadCount > 0" class="md-icon-badge">{{ unreadCount }}</span>
      </button>

      <transition name="dropdown">
        <div v-if="showNotifications" class="md-dropdown-menu notifications">
          <div class="md-dropdown-header">
            <span class="md-dropdown-title">Notifications</span>
            <button v-if="unreadCount > 0" class="md-text-btn" @click="markAllRead">
              Mark all read
            </button>
          </div>

          <div class="md-dropdown-body">
            <div v-if="notifications.length === 0" class="md-empty-state">
              <i class="bi bi-bell-slash"></i>
              <span>No notifications</span>
            </div>

            <div v-for="notif in notifications" :key="notif.id" class="md-notification-item"
              :class="{ unread: !notif.read }">
              <div class="md-notification-icon" :class="notif.type">
                <i :class="getNotifIcon(notif.type)"></i>
              </div>
              <div class="md-notification-content">
                <p class="md-notification-text">{{ notif.message }}</p>
                <span class="md-notification-time">{{ notif.time }}</span>
              </div>
            </div>
          </div>

          <div class="md-dropdown-footer">
            <RouterLink to="/notifications" class="md-text-btn">View all</RouterLink>
          </div>
        </div>
      </transition>
    </div>

    <!-- Profile -->
    <div ref="profileDropdown" class="md-dropdown">
      <button class="md-profile-btn" @click="toggleProfile">
        <div class="md-avatar">
          <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
          <span v-else>{{ userInitials }}</span>
        </div>
        <div class="md-profile-info d-none d-md-flex">
          <span class="md-profile-name">{{ user?.name || 'User' }}</span>
          <span class="md-profile-role">{{ roleLabel }}</span>
        </div>
        <i class="bi bi-chevron-down md-profile-arrow d-none d-md-block"></i>
      </button>

      <transition name="dropdown">
        <div v-if="showProfile" class="md-dropdown-menu profile">
          <div class="md-dropdown-profile">
            <div class="md-avatar lg">
              <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
              <span v-else>{{ userInitials }}</span>
            </div>
            <div class="md-profile-details">
              <span class="md-profile-name">{{ user?.name }}</span>
              <span class="md-profile-email">{{ user?.email }}</span>
            </div>
          </div>

          <div class="md-divider"></div>

          <RouterLink to="/profile" class="md-dropdown-item">
            <i class="bi bi-person"></i>
            <span>My Profile</span>
          </RouterLink>
          <RouterLink v-if="auth.hasRole(['admin'])" to="/settings" class="md-dropdown-item">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
          </RouterLink>

          <div class="md-divider"></div>

          <button class="md-dropdown-item danger" @click="handleLogout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sign out</span>
          </button>
        </div>
      </transition>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../store/auth';
import { useThemeStore } from '../../store/theme';

defineProps({
  toggleSidebar: { type: Function, required: true },
});

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const themeStore = useThemeStore();

// Scroll state
const isScrolled = ref(false);

function handleScroll() {
  isScrolled.value = window.scrollY > 10;
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Page title
const pageTitle = computed(() => {
  const titles = {
    '/dashboard': 'Dashboard',
    '/users': 'Users',
    '/reports': 'Reports',
    '/attendance': 'Attendance',
    '/contributions': 'Contributions',
    '/settings': 'Settings',
  };
  return Object.keys(titles).find(p => route.path.startsWith(p))
    ? titles[Object.keys(titles).find(p => route.path.startsWith(p))]
    : 'PLE CMS';
});

// Search
const searchQuery = ref('');
const searchFocused = ref(false);

// Theme
const isDark = computed(() => themeStore.mode === 'dark');

function toggleTheme() {
  themeStore.toggleMode();
}

// Notifications
const showNotifications = ref(false);
const notifDropdown = ref(null);
const unreadCount = ref(3);
const notifications = ref([
  {
    id: 1,
    type: 'info',
    message: 'New member registration pending approval',
    time: '5 min ago',
    read: false,
  },
  {
    id: 2,
    type: 'success',
    message: 'Weekly report has been generated',
    time: '1 hour ago',
    read: false,
  },
  {
    id: 3,
    type: 'warning',
    message: 'Follow-up due for 2 visitors',
    time: '2 hours ago',
    read: false,
  },
]);

function toggleNotifications() {
  showNotifications.value = !showNotifications.value;
  showProfile.value = false;
}

function markAllRead() {
  notifications.value.forEach(n => (n.read = true));
  unreadCount.value = 0;
}

function getNotifIcon(type) {
  const icons = {
    info: 'bi bi-info-circle-fill',
    success: 'bi bi-check-circle-fill',
    warning: 'bi bi-exclamation-triangle-fill',
    error: 'bi bi-x-circle-fill',
  };
  return icons[type] || icons.info;
}

// Profile
const showProfile = ref(false);
const profileDropdown = ref(null);

const user = computed(() => auth.user);
const userInitials = computed(() => {
  const name = user.value?.name || 'U';
  return name
    .split(' ')
    .map(w => w[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
});

const roleLabel = computed(() => {
  const labels = {
    admin: 'Administrator',
    pastor: 'Pastor',
    usher: 'Usher',
    finance: 'Finance',
    pr_follow_up: 'PR & Follow-up',
    department_leader: 'Dept. Leader',
  };
  return labels[user.value?.role] || user.value?.role;
});

function toggleProfile() {
  showProfile.value = !showProfile.value;
  showNotifications.value = false;
}

async function handleLogout() {
  await auth.logout();
  router.push('/login');
}

// Click outside to close dropdowns
function handleClickOutside(e) {
  if (notifDropdown.value && !notifDropdown.value.contains(e.target)) {
    showNotifications.value = false;
  }
  if (profileDropdown.value && !profileDropdown.value.contains(e.target)) {
    showProfile.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* ========================================
   MATERIAL APP BAR
   ======================================== */

.md-appbar {
  height: var(--md-navbar-height);
  background: var(--md-surface);
  border-bottom: 1px solid var(--md-outline-variant);
  display: flex;
  align-items: center;
  padding: 0 var(--md-space-4);
  gap: var(--md-space-3);
  position: sticky;
  top: 0;
  z-index: var(--md-z-sticky);
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
}

.md-appbar-elevated {
  box-shadow: var(--md-elevation-2);
  background: var(--md-glass-background);
  backdrop-filter: var(--md-glass-blur);
}

.md-appbar-title {
  font: var(--md-title-large);
  color: var(--md-on-surface);
  margin: 0;
}

.md-spacer {
  flex: 1;
}

/* Icon Button */
.md-icon-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--md-shape-full);
  background: transparent;
  border: none;
  color: var(--md-on-surface-variant);
  font-size: 1.25rem;
  cursor: pointer;
  transition: all var(--md-motion-duration-short3) var(--md-motion-easing-standard);
  position: relative;
}

.md-icon-btn:hover {
  background: var(--md-surface-container);
  color: var(--md-on-surface);
}

.md-badge-wrap {
  position: relative;
}

.md-icon-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  min-width: 18px;
  height: 18px;
  background: var(--md-error);
  color: var(--md-on-error);
  font: var(--md-label-small);
  border-radius: var(--md-shape-full);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}

/* Search */
.md-search-container {
  flex: 1;
  max-width: 400px;
}

.md-search {
  display: flex;
  align-items: center;
  gap: var(--md-space-3);
  background: var(--md-surface-container);
  border-radius: var(--md-shape-full);
  padding: var(--md-space-2) var(--md-space-4);
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
  border: 2px solid transparent;
}

.md-search.focused {
  background: var(--md-surface);
  border-color: var(--md-primary);
  box-shadow: 0 0 0 4px var(--md-primary-container);
}

.md-search-icon {
  color: var(--md-on-surface-muted);
  font-size: 1rem;
}

.md-search-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font: var(--md-body-medium);
  color: var(--md-on-surface);
}

.md-search-input::placeholder {
  color: var(--md-on-surface-muted);
}

.md-search-kbd {
  background: var(--md-surface-container-high);
  color: var(--md-on-surface-muted);
  font: var(--md-label-small);
  padding: var(--md-space-1) var(--md-space-2);
  border-radius: var(--md-shape-xs);
  font-family: var(--md-font-family);
}

/* Dropdown */
.md-dropdown {
  position: relative;
}

.md-dropdown-menu {
  position: absolute;
  top: calc(100% + var(--md-space-2));
  right: 0;
  min-width: 280px;
  background: var(--md-surface);
  border-radius: var(--md-shape-lg);
  box-shadow: var(--md-elevation-4);
  border: 1px solid var(--md-outline-variant);
  overflow: hidden;
  z-index: var(--md-z-dropdown);
}

.md-dropdown-menu.notifications {
  width: 360px;
}

.md-dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--md-space-4) var(--md-space-5);
  border-bottom: 1px solid var(--md-outline-variant);
}

.md-dropdown-title {
  font: var(--md-title-medium);
  color: var(--md-on-surface);
}

.md-dropdown-body {
  max-height: 320px;
  overflow-y: auto;
}

.md-dropdown-footer {
  padding: var(--md-space-3) var(--md-space-5);
  border-top: 1px solid var(--md-outline-variant);
  text-align: center;
}

/* Text Button */
.md-text-btn {
  background: transparent;
  border: none;
  color: var(--md-primary);
  font: var(--md-label-large);
  cursor: pointer;
  text-decoration: none;
}

.md-text-btn:hover {
  text-decoration: underline;
}

/* Notification Item */
.md-notification-item {
  display: flex;
  align-items: flex-start;
  gap: var(--md-space-4);
  padding: var(--md-space-4) var(--md-space-5);
  transition: background var(--md-motion-duration-short3) var(--md-motion-easing-standard);
}

.md-notification-item:hover {
  background: var(--md-surface-container-low);
}

.md-notification-item.unread {
  background: var(--md-primary-container);
}

.md-notification-icon {
  width: 36px;
  height: 36px;
  border-radius: var(--md-shape-full);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.md-notification-icon.info {
  background: var(--md-info-container);
  color: var(--md-info);
}

.md-notification-icon.success {
  background: var(--md-success-container);
  color: var(--md-success);
}

.md-notification-icon.warning {
  background: var(--md-warning-container);
  color: var(--md-warning);
}

.md-notification-icon.error {
  background: var(--md-error-container);
  color: var(--md-error);
}

.md-notification-content {
  flex: 1;
  min-width: 0;
}

.md-notification-text {
  font: var(--md-body-medium);
  color: var(--md-on-surface);
  margin: 0;
}

.md-notification-time {
  font: var(--md-body-small);
  color: var(--md-on-surface-muted);
}

/* Empty State */
.md-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--md-space-10);
  color: var(--md-on-surface-muted);
  gap: var(--md-space-2);
}

.md-empty-state i {
  font-size: 2rem;
}

/* Profile Button */
.md-profile-btn {
  display: flex;
  align-items: center;
  gap: var(--md-space-3);
  padding: var(--md-space-2);
  padding-right: var(--md-space-4);
  background: var(--md-surface-container);
  border-radius: var(--md-shape-full);
  border: none;
  cursor: pointer;
  transition: all var(--md-motion-duration-short3) var(--md-motion-easing-standard);
}

.md-profile-btn:hover {
  background: var(--md-surface-container-high);
}

/* Avatar */
.md-avatar {
  width: 36px;
  height: 36px;
  border-radius: var(--md-shape-full);
  background: var(--md-gradient-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font: var(--md-label-large);
  color: var(--md-on-primary);
  overflow: hidden;
  flex-shrink: 0;
}

.md-avatar.lg {
  width: 56px;
  height: 56px;
  font: var(--md-title-large);
}

.md-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.md-profile-info {
  flex-direction: column;
  align-items: flex-start;
}

.md-profile-name {
  font: var(--md-label-large);
  color: var(--md-on-surface);
}

.md-profile-role,
.md-profile-email {
  font: var(--md-body-small);
  color: var(--md-on-surface-muted);
}

.md-profile-arrow {
  font-size: 0.75rem;
  color: var(--md-on-surface-muted);
}

/* Profile Dropdown */
.md-dropdown-profile {
  display: flex;
  align-items: center;
  gap: var(--md-space-4);
  padding: var(--md-space-5);
}

.md-profile-details {
  display: flex;
  flex-direction: column;
}

/* Divider */
.md-divider {
  height: 1px;
  background: var(--md-outline-variant);
  margin: var(--md-space-2) 0;
}

/* Dropdown Item */
.md-dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--md-space-4);
  padding: var(--md-space-3) var(--md-space-5);
  color: var(--md-on-surface-variant);
  text-decoration: none;
  font: var(--md-label-large);
  background: transparent;
  border: none;
  width: 100%;
  cursor: pointer;
  transition: all var(--md-motion-duration-short3) var(--md-motion-easing-standard);
}

.md-dropdown-item:hover {
  background: var(--md-surface-container);
  color: var(--md-on-surface);
}

.md-dropdown-item.danger {
  color: var(--md-error);
}

.md-dropdown-item.danger:hover {
  background: var(--md-error-container);
  color: var(--md-error);
}

/* Dropdown Transitions */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-emphasized);
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}

/* Responsive utilities */
.d-none {
  display: none !important;
}

.d-lg-none {
  display: block !important;
}

.d-lg-flex {
  display: none !important;
}

.d-md-flex {
  display: none !important;
}

.d-md-block {
  display: none !important;
}

@media (min-width: 768px) {
  .d-md-flex {
    display: flex !important;
  }

  .d-md-block {
    display: block !important;
  }
}

@media (min-width: 992px) {
  .d-lg-none {
    display: none !important;
  }

  .d-lg-flex {
    display: flex !important;
  }
}
</style>
