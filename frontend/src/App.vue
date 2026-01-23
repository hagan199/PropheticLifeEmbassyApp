<template>
  <Sidebar v-if="!isAuthLayout" :visible="sidebarVisible" @visible-change="sidebarVisible = $event" />
  <div class="main-content"
    :class="{ 'no-side': isAuthLayout, collapsed: !sidebarVisible, 'theme-dark': isDarkLayout }">
    <Navbar v-if="!isAuthLayout" :toggleSidebar="toggleSidebar" />
    <transition name="view-fade" mode="out-in">
      <router-view />
    </transition>
    <footer v-if="!isAuthLayout" class="app-footer d-flex justify-content-between align-items-center text-muted">
      <div>© Prophetic Life Embassy</div>
      <div>v0.1.0</div>
    </footer>
    <MobileTabBar v-if="!isAuthLayout" />
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
const sidebarVisible = ref(typeof window !== 'undefined' ? window.innerWidth >= 992 : true)
watch(isAuthLayout, (val) => { if (val) sidebarVisible.value = false })
function toggleSidebar() { sidebarVisible.value = !sidebarVisible.value }
function handleResize() { if (typeof window !== 'undefined') sidebarVisible.value = window.innerWidth >= 992 }
onMounted(() => { window.addEventListener('resize', handleResize) })
onUnmounted(() => { window.removeEventListener('resize', handleResize) })
</script>

<style>
.main-content {
  margin-left: 250px;
  /* Adjust based on sidebar width */
  padding: 20px;
}

.no-side {
  margin-left: 0;
  padding: 0;
}

.collapsed {
  margin-left: 0;
}

@media (max-width: 992px) {
  .main-content {
    margin-left: 0;
    padding: 12px;
    padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px));
  }
}

.app-footer {
  padding: 12px 20px;
  border-top: 1px solid rgba(0, 0, 0, .06);
  margin-top: 16px;
}

.view-fade-enter-active,
.view-fade-leave-active {
  transition: opacity .25s ease, transform .25s ease;
}

.view-fade-enter-from,
.view-fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
}
</style>
