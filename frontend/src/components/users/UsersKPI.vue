<template>
  <div class="kpi-grid mb-4">
    <div class="kpi-card" style="--delay: 0s">
      <div class="kpi-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)">
        <i class="bi bi-people-fill"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-label">Total Users</div>
        <div class="kpi-value">{{ totalUsersDisplay }}</div>
        <div class="kpi-sublabel">{{ activeUsersCount }} active</div>
      </div>
    </div>

    <div class="kpi-card" style="--delay: 0.1s">
      <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%)">
        <i class="bi bi-person-check-fill"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-label">Active Users</div>
        <div class="kpi-value">{{ activeUsersCount }}</div>
        <div class="kpi-sublabel">{{ inactiveUsersCount }} inactive</div>
      </div>
    </div>

    <div class="kpi-card" style="--delay: 0.2s">
      <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%)">
        <i class="bi bi-shield-check"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-label">Roles</div>
        <div class="kpi-value">{{ rolesCount }}</div>
        <div class="kpi-sublabel">{{ multiRoleUsersCount }} with multiple</div>
      </div>
    </div>

    <div class="kpi-card" style="--delay: 0.3s">
      <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)">
        <i class="bi bi-diagram-3-fill"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-label">Departments</div>
        <div class="kpi-value">{{ departmentsCount }}</div>
        <div class="kpi-sublabel">{{ usersWithDeptCount }} assigned</div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  totalUsersDisplay: { type: [Number, String], default: 0 },
  activeUsersCount: { type: Number, default: 0 },
  inactiveUsersCount: { type: Number, default: 0 },
  rolesCount: { type: Number, default: 0 },
  multiRoleUsersCount: { type: Number, default: 0 },
  departmentsCount: { type: Number, default: 0 },
  usersWithDeptCount: { type: Number, default: 0 },
});
</script>

<style scoped>
.kpi-grid {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap
}

.kpi-card {
  background: var(--card-bg);
  padding: 1rem;
  border-radius: 8px;
  flex: 1
}
</style>
