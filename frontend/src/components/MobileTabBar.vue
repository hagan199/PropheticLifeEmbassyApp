<template>
  <div class="mobile-tabbar">
    <!-- Dashboard - All Roles -->
    <RouterLink to="/dashboard" class="item" :class="{ active: isActive('/dashboard') }">
      <i class="bi bi-speedometer2"></i>
      <span>Home</span>
    </RouterLink>

    <!-- Admin: Users -->
    <RouterLink v-if="hasRole(['admin'])" to="/users" class="item" :class="{ active: isActive('/users') }">
      <i class="bi bi-people"></i>
      <span>Users</span>
    </RouterLink>

    <!-- Usher: My Submissions -->
    <RouterLink v-if="hasRole(['usher'])" to="/my-submissions" class="item"
      :class="{ active: isActive('/my-submissions') }">
      <i class="bi bi-send-check"></i>
      <span>Submit</span>
    </RouterLink>

    <!-- Admin/Pastor/Usher: Attendance -->
    <RouterLink v-if="hasRole(['admin', 'pastor', 'usher'])" to="/attendance" class="item"
      :class="{ active: isActive('/attendance') }">
      <i class="bi bi-calendar-check"></i>
      <span>Attend</span>
    </RouterLink>

    <!-- PR: Follow-ups -->
    <RouterLink v-if="hasRole(['admin', 'pastor', 'pr_follow_up'])" to="/follow-ups" class="item"
      :class="{ active: isActive('/follow-ups') }">
      <i class="bi bi-chat-heart"></i>
      <span>Follow</span>
    </RouterLink>

    <!-- Finance: Contributions -->
    <RouterLink v-if="hasRole(['admin', 'finance'])" to="/contributions" class="item"
      :class="{ active: isActive('/contributions') }">
      <i class="bi bi-cash-coin"></i>
      <span>Finance</span>
    </RouterLink>

    <!-- Dept Leader: My Department -->
    <RouterLink v-if="hasRole(['department_leader'])" to="/my-department" class="item"
      :class="{ active: isActive('/my-department') }">
      <i class="bi bi-diagram-3"></i>
      <span>Dept</span>
    </RouterLink>

    <!-- Settings - All Roles -->
    <RouterLink to="/settings" class="item" :class="{ active: isActive('/settings') }">
      <i class="bi bi-gear"></i>
      <span>Settings</span>
    </RouterLink>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../store/auth'

const route = useRoute()
function isActive(path) { return route.path.startsWith(path) }

const auth = useAuthStore()
const userRole = computed(() => auth.user?.role || 'admin')

function hasRole(roles) {
  return roles.includes(userRole.value)
}
</script>

<style scoped>
.mobile-tabbar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  display: none;
  z-index: 1040;
  padding-bottom: env(safe-area-inset-bottom, 0);
}

.item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #475569;
  text-decoration: none;
  font-size: 11px;
  max-width: 80px;
}

.item i {
  font-size: 18px;
  margin-bottom: 2px;
}

.item.active {
  color: var(--app-accent, #0d6efd);
}

.item.active i {
  transform: scale(1.1);
}

@media (max-width: 576px) {
  .mobile-tabbar {
    display: flex;
  }
}
</style>
