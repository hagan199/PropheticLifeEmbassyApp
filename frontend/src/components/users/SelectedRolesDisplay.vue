<template>
  <div class="selected-roles-display">
    <div class="roles-list">
      <CBadge
        v-for="roleId in selectedRoleIds"
        :key="roleId"
        :color="getRoleColor(roleId)"
        class="selected-role-badge"
      >
        <i :class="getRoleIcon(roleId)" class="me-1"></i>
        {{ getRoleLabel(roleId) }}
        <button
          v-if="removable"
          class="role-remove-btn"
          @click="$emit('remove', roleId)"
          type="button"
        >
          <i class="bi bi-x"></i>
        </button>
      </CBadge>
      <span v-if="selectedRoleIds.length === 0" class="no-roles-text">
        No roles selected
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { CBadge } from '@coreui/vue';

const props = defineProps({
  selectedRoleIds: {
    type: Array,
    default: () => []
  },
  roleOptions: {
    type: Array,
    default: () => []
  },
  removable: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['remove']);

const roleColorMap = {
  admin: 'danger',
  pastor: 'primary',
  usher: 'info',
  finance: 'success',
  pr_follow_up: 'warning',
  department_leader: 'dark'
};

const roleIconMap = {
  admin: 'bi bi-shield-check',
  pastor: 'bi bi-book',
  usher: 'bi bi-people',
  finance: 'bi bi-cash-coin',
  pr_follow_up: 'bi bi-megaphone',
  department_leader: 'bi bi-diagram-3'
};

const roleLabelMap = {
  admin: 'Administrator',
  pastor: 'Pastor',
  usher: 'Usher',
  finance: 'Finance Officer',
  pr_follow_up: 'PR/Follow-up',
  department_leader: 'Department Leader'
};

function getRoleColor(roleId) {
  return roleColorMap[roleId] || 'secondary';
}

function getRoleIcon(roleId) {
  return roleIconMap[roleId] || 'bi bi-person';
}

function getRoleLabel(roleId) {
  const roleOption = props.roleOptions.find(r => r.value === roleId);
  return roleOption?.label || roleLabelMap[roleId] || roleId;
}
</script>

<style scoped>
.selected-roles-display {
  width: 100%;
}

.roles-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.selected-role-badge {
  display: inline-flex;
  align-items: center;
  font-size: 0.875rem;
  font-weight: 500;
  padding: 0.5rem 0.75rem;
  border-radius: 20px;
  position: relative;
  transition: all 0.2s ease;
}

.selected-role-badge:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.role-remove-btn {
  background: none;
  border: none;
  color: inherit;
  opacity: 0.7;
  padding: 0;
  margin-left: 0.5rem;
  cursor: pointer;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.role-remove-btn:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.2);
}

.no-roles-text {
  color: #6c757d;
  font-style: italic;
  font-size: 0.875rem;
}
</style>