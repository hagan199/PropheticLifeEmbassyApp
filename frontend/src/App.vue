<template>
  <div class="md-app" :class="{ 'theme-dark': isDarkLayout }">
    <!-- Sidebar -->
    <MaterialSidebar v-if="!isAuthLayout" :visible="sidebarVisible" @visible-change="sidebarVisible = $event" />

    <!-- Main Content -->
    <div class="md-main" :class="{
      'no-sidebar': isAuthLayout,
      'sidebar-visible': sidebarVisible && !isMobile,
    }">
      <!-- Navbar -->
      <MaterialNavbar v-if="!isAuthLayout" :toggle-sidebar="toggleSidebar" />

      <!-- Page Content -->
      <main class="md-page">
        <router-view v-slot="{ Component }">
          <transition name="page-fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- Footer -->
      <footer v-if="!isAuthLayout" class="md-footer">
        <span>© {{ new Date().getFullYear() }} Prophetic Life Embassy</span>
        <span class="md-footer-version">v0.1.0</span>
      </footer>

      <!-- Mobile Bottom Navigation -->
      <MobileTabBar v-if="!isAuthLayout && isMobile" />
    </div>

    <!-- Toast Notifications -->
    <Toast />
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { useThemeStore } from './store/theme';
import { useAuthStore } from './store/auth';
import { useRoute } from 'vue-router';
import { useSessionTimeout } from './composables/useSessionTimeout';
import MaterialSidebar from './components/material/MaterialSidebar.vue';
import MaterialNavbar from './components/material/MaterialNavbar.vue';
import MobileTabBar from './components/MobileTabBar.vue';
import Toast from './components/shared/Toast.vue';

const route = useRoute();
const theme = useThemeStore();
useAuthStore();

// Initialize session timeout monitoring (2 hours inactivity)
useSessionTimeout({
  timeoutMinutes: 120, // 2 hours
  warningMinutes: 5, // 5 minutes warning before logout
});

// Layout checks
const isAuthLayout = computed(() => route.meta.layout === 'auth');
const isDarkLayout = computed(() => theme.mode === 'dark');

// Responsive state
const isMobile = ref(false);
const sidebarVisible = ref(true);

function checkMobile() {
  if (typeof window !== 'undefined') {
    isMobile.value = window.innerWidth < 992;
    sidebarVisible.value = !isMobile.value;
  }
}

function toggleSidebar() {
  sidebarVisible.value = !sidebarVisible.value;
}

watch(isAuthLayout, val => {
  if (val) sidebarVisible.value = false;
});

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<style>
/* ========================================
   APP LAYOUT - Material Design 3
   ======================================== */

.md-app {
  min-height: 100vh;
  display: flex;
  background: var(--md-background);
  position: relative;
}

/* Main Content Area */
.md-main {
  flex: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--md-background);
  transition: margin-left var(--md-motion-duration-medium2) var(--md-motion-easing-emphasized);
  width: 100%;
  position: relative;
}

/* When sidebar is visible on desktop */
@media (min-width: 992px) {
  .md-main.sidebar-visible {
    margin-left: 280px;
    width: calc(100% - 280px);
  }
}

/* No sidebar (auth layout) */
.md-main.no-sidebar {
  margin-left: 0;
  width: 100%;
}

/* Page Content */
.md-page {
  flex: 1;
  padding: var(--md-space-6);
  padding-bottom: var(--md-space-8);
  min-height: 0;
}

@media (max-width: 768px) {
  .md-page {
    padding: var(--md-space-4);
    padding-bottom: calc(var(--md-bottom-nav-height) + var(--md-space-4));
  }
}

/* Footer */
.md-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--md-space-4) var(--md-space-6);
  border-top: 1px solid var(--md-outline-variant);
  background: var(--md-surface);
  font: var(--md-body-small);
  color: var(--md-on-surface-muted);
  margin-top: auto;
}

.md-footer-version {
  color: var(--md-on-surface-muted);
}

@media (max-width: 991.98px) {
  .md-footer {
    display: none;
  }
}

/* Page Transitions */
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity var(--md-motion-duration-medium1) var(--md-motion-easing-standard),
    transform var(--md-motion-duration-medium1) var(--md-motion-easing-standard);
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ========================================
   UTILITY CLASSES
   ======================================== */

.d-flex {
  display: flex !important;
}

.d-none {
  display: none !important;
}

.d-block {
  display: block !important;
}

.flex-column {
  flex-direction: column !important;
}

.align-items-center {
  align-items: center !important;
}

.justify-content-between {
  justify-content: space-between !important;
}

.justify-content-center {
  justify-content: center !important;
}

.gap-2 {
  gap: var(--md-space-2) !important;
}

.gap-3 {
  gap: var(--md-space-3) !important;
}

.gap-4 {
  gap: var(--md-space-4) !important;
}

.gap-6 {
  gap: var(--md-space-6) !important;
}

.w-100 {
  width: 100% !important;
}

.h-100 {
  height: 100% !important;
}

.text-muted {
  color: var(--md-on-surface-muted) !important;
}

.text-center {
  text-align: center !important;
}

.fw-semibold {
  font-weight: 600 !important;
}

.fw-bold {
  font-weight: 700 !important;
}

.small {
  font-size: 0.875rem !important;
}

/* Margin Utilities */
.m-0 {
  margin: 0 !important;
}

.mt-2 {
  margin-top: var(--md-space-2) !important;
}

.mt-3 {
  margin-top: var(--md-space-3) !important;
}

.mt-4 {
  margin-top: var(--md-space-4) !important;
}

.mt-6 {
  margin-top: var(--md-space-6) !important;
}

.mb-2 {
  margin-bottom: var(--md-space-2) !important;
}

.mb-3 {
  margin-bottom: var(--md-space-3) !important;
}

.mb-4 {
  margin-bottom: var(--md-space-4) !important;
}

.mb-6 {
  margin-bottom: var(--md-space-6) !important;
}

.me-2 {
  margin-right: var(--md-space-2) !important;
}

.me-3 {
  margin-right: var(--md-space-3) !important;
}

.ms-2 {
  margin-left: var(--md-space-2) !important;
}

.ms-auto {
  margin-left: auto !important;
}

/* Padding Utilities */
.p-0 {
  padding: 0 !important;
}

.p-3 {
  padding: var(--md-space-3) !important;
}

.p-4 {
  padding: var(--md-space-4) !important;
}

.p-6 {
  padding: var(--md-space-6) !important;
}

.px-3 {
  padding-left: var(--md-space-3) !important;
  padding-right: var(--md-space-3) !important;
}

.px-4 {
  padding-left: var(--md-space-4) !important;
  padding-right: var(--md-space-4) !important;
}

.py-2 {
  padding-top: var(--md-space-2) !important;
  padding-bottom: var(--md-space-2) !important;
}

.py-3 {
  padding-top: var(--md-space-3) !important;
  padding-bottom: var(--md-space-3) !important;
}

/* Responsive Display */
@media (min-width: 576px) {
  .d-sm-block {
    display: block !important;
  }

  .d-sm-none {
    display: none !important;
  }

  .d-sm-flex {
    display: flex !important;
  }
}

@media (min-width: 768px) {
  .d-md-block {
    display: block !important;
  }

  .d-md-none {
    display: none !important;
  }

  .d-md-flex {
    display: flex !important;
  }
}

@media (min-width: 992px) {
  .d-lg-block {
    display: block !important;
  }

  .d-lg-none {
    display: none !important;
  }

  .d-lg-flex {
    display: flex !important;
  }
}

/* Form Elements - Material Style */
.form-control,
.form-select {
  width: 100%;
  padding: var(--md-space-4);
  font: var(--md-body-large);
  color: var(--md-on-surface);
  background: var(--md-surface);
  border: 1px solid var(--md-outline);
  border-radius: var(--md-shape-sm);
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
  outline: none;
}

.form-control:hover,
.form-select:hover {
  border-color: var(--md-on-surface);
}

.form-control:focus,
.form-select:focus {
  border-color: var(--md-primary);
  border-width: 2px;
  box-shadow: none;
}

.form-control::placeholder {
  color: var(--md-on-surface-muted);
}

/* Button - Material Style */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--md-space-2);
  font: var(--md-label-large);
  text-decoration: none;
  border: none;
  border-radius: var(--md-shape-full);
  padding: var(--md-space-3) var(--md-space-6);
  cursor: pointer;
  transition: all var(--md-motion-duration-short4) var(--md-motion-easing-standard);
}

.btn-primary {
  background: var(--md-primary);
  color: var(--md-on-primary);
}

.btn-primary:hover {
  background: var(--md-primary-dark);
  box-shadow: var(--md-elevation-2);
}

.btn-secondary {
  background: var(--md-surface-container-high);
  color: var(--md-on-surface);
}

.btn-secondary:hover {
  background: var(--md-surface-container-highest);
}

.btn-danger {
  background: var(--md-error);
  color: var(--md-on-error);
}

.btn-success {
  background: var(--md-success);
  color: var(--md-on-success);
}

/* Table Responsive */
.table-responsive {
  display: block;
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Hide scrollbar utility */
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
