<template>
  <div class="app-wrapper" :class="{ 'theme-dark': isDarkLayout }">
    <Sidebar
      v-if="!isAuthLayout"
      :visible="sidebarVisible"
      @visible-change="sidebarVisible = $event"
    />

    <!-- Overlay for mobile when sidebar is open -->
    <div
      v-if="!isAuthLayout && sidebarVisible && isMobile"
      class="sidebar-overlay"
      @click="sidebarVisible = false"
    ></div>

    <div
      class="main-content"
      :class="{
        'no-side': isAuthLayout,
        'sidebar-collapsed': !sidebarVisible,
        'sidebar-open': sidebarVisible
      }"
    >
      <Navbar v-if="!isAuthLayout" :toggleSidebar="toggleSidebar" />

      <main class="page-content">
        <transition name="view-fade" mode="out-in">
          <router-view />
        </transition>
      </main>

      <footer v-if="!isAuthLayout" class="app-footer">
        <div>© {{ new Date().getFullYear() }} Prophetic Life Embassy</div>
        <div>v0.1.0</div>
      </footer>

      <MobileTabBar v-if="!isAuthLayout" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { useThemeStore } from './store/theme'
import { useRoute } from 'vue-router'
import Sidebar from './components/Sidebar.vue'
import Navbar from './components/Navbar.vue'
import MobileTabBar from './components/MobileTabBar.vue'

const route = useRoute()
const isAuthLayout = computed(() => route.meta.layout === 'auth')
const theme = useThemeStore()
const isDarkLayout = computed(() => theme.mode === 'dark')

// Responsive state
const isMobile = ref(false)
const sidebarVisible = ref(true)

function checkMobile() {
  if (typeof window !== 'undefined') {
    isMobile.value = window.innerWidth < 992
    // Auto-collapse sidebar on mobile
    if (isMobile.value) {
      sidebarVisible.value = false
    } else {
      sidebarVisible.value = true
    }
  }
}

watch(isAuthLayout, (val) => {
  if (val) sidebarVisible.value = false
})

function toggleSidebar() {
  sidebarVisible.value = !sidebarVisible.value
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

/* Reset & Base */
*,
*::before,
*::after {
  box-sizing: border-box;
}

html {
  font-size: 16px;
  -webkit-text-size-adjust: 100%;
}

body {
  margin: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background: var(--bg-main, #f8fafc);
  color: var(--text-primary, #0f172a);
  line-height: 1.6;
  overflow-x: hidden;
}

/* App Wrapper */
.app-wrapper {
  min-height: 100vh;
  display: flex;
  background: var(--bg-main, #f8fafc);
}

/* Main Content Area */
.main-content {
  flex: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg-main, #f8fafc);
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  width: 100%;
}

/* Desktop: Sidebar visible */
@media (min-width: 992px) {
  .main-content.sidebar-open {
    margin-left: 260px;
    width: calc(100% - 260px);
  }

  .main-content.sidebar-collapsed {
    margin-left: 0;
    width: 100%;
  }
}

/* Mobile & Tablet: No margin, sidebar overlays */
@media (max-width: 991.98px) {
  .main-content {
    margin-left: 0 !important;
    width: 100% !important;
    padding-bottom: calc(70px + env(safe-area-inset-bottom, 0px));
  }
}

/* Auth layout (no sidebar) */
.main-content.no-side {
  margin-left: 0;
  width: 100%;
}

/* Sidebar Overlay (Mobile) */
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 1019;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Page Content */
.page-content {
  flex: 1;
  padding: 0;
  min-height: 0;
}

/* Footer */
.app-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-color, #e2e8f0);
  background: var(--bg-card, #ffffff);
  font-size: 0.8125rem;
  color: var(--text-muted, #94a3b8);
  margin-top: auto;
}

/* Page Transitions */
.view-fade-enter-active,
.view-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.view-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.view-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Responsive Page Padding */
.page-wrap {
  padding: 1.5rem;
}

@media (max-width: 768px) {
  .page-wrap {
    padding: 1rem;
  }
}

@media (max-width: 576px) {
  .page-wrap {
    padding: 0.875rem;
  }
}

/* Dark Theme Variables Override */
.theme-dark {
  --bg-main: #0f172a;
  --bg-card: rgba(30, 41, 59, 0.8);
  --bg-hover: rgba(51, 65, 85, 0.5);
  --text-primary: #f1f5f9;
  --text-secondary: #94a3b8;
  --text-muted: #64748b;
  --border-color: rgba(71, 85, 105, 0.5);
}

.theme-dark .app-footer {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(51, 65, 85, 0.5);
}

/* Utility Classes */
.d-flex { display: flex !important; }
.flex-column { flex-direction: column !important; }
.align-items-center { align-items: center !important; }
.justify-content-between { justify-content: space-between !important; }
.gap-2 { gap: 0.5rem !important; }
.gap-3 { gap: 1rem !important; }
.gap-4 { gap: 1.5rem !important; }
.w-100 { width: 100% !important; }
.h-100 { height: 100% !important; }
.text-muted { color: var(--text-muted, #94a3b8) !important; }
.fw-semibold { font-weight: 600 !important; }
.fw-bold { font-weight: 700 !important; }
.small { font-size: 0.875rem !important; }
.text-center { text-align: center !important; }
.text-end { text-align: right !important; }
.me-1 { margin-right: 0.25rem !important; }
.me-2 { margin-right: 0.5rem !important; }
.me-3 { margin-right: 1rem !important; }
.ms-2 { margin-left: 0.5rem !important; }
.ms-3 { margin-left: 1rem !important; }
.ms-auto { margin-left: auto !important; }
.mt-1 { margin-top: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }
.p-0 { padding: 0 !important; }
.p-3 { padding: 1rem !important; }
.p-4 { padding: 1.5rem !important; }
.px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }
.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
.py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
.rounded { border-radius: 0.375rem !important; }

/* Responsive display utilities */
.d-none { display: none !important; }
.d-block { display: block !important; }
.d-inline-flex { display: inline-flex !important; }

@media (min-width: 576px) {
  .d-sm-block { display: block !important; }
  .d-sm-none { display: none !important; }
  .d-sm-inline { display: inline !important; }
}

@media (min-width: 768px) {
  .d-md-block { display: block !important; }
  .d-md-none { display: none !important; }
  .d-md-flex { display: flex !important; }
}

@media (min-width: 992px) {
  .d-lg-block { display: block !important; }
  .d-lg-none { display: none !important; }
  .d-lg-flex { display: flex !important; }
}

/* Flex wrap for responsive layouts */
.flex-wrap { flex-wrap: wrap !important; }

/* Form responsive adjustments */
@media (max-width: 576px) {
  .form-control,
  .form-select {
    font-size: 16px !important; /* Prevents zoom on iOS */
  }
}

/* Table responsive */
.table-responsive {
  display: block;
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Card responsive */
@media (max-width: 576px) {
  .card {
    border-radius: 12px !important;
  }

  .card-body {
    padding: 1rem !important;
  }
}

/* Hide scrollbar but allow scrolling */
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
