<template>
  <div class="md-fade-in">
    <CRow class="g-4">
      <CCol lg="5">
        <div class="roles-card-modern">
          <div class="roles-header">
            <h5 class="mb-1 fw-bold"><i class="bi bi-shield-lock me-2"></i>System Roles</h5>
            <p class="small text-muted mb-0">{{ roles.length }} roles configured</p>
          </div>
          <div class="roles-list">
            <div v-for="r in roles" :key="r.id" :class="['role-card-item', { active: selectedRole?.id === r.id }]"
              tabindex="0" @click="fetchRolePermissions(r)" @keyup.enter="fetchRolePermissions(r)">
              <div class="role-icon-wrapper" :class="getRoleColor(r.name)">
                <i :class="roleIcon(r.name)" class="role-icon-lg"></i>
              </div>
              <div class="role-details">
                <div class="role-name">{{ r.name }}</div>
                <div class="role-count">
                  <i class="bi bi-check-circle-fill me-1"></i>
                  <span>{{ roleCounts[r.id] || 0 }} permissions</span>
                </div>
              </div>
              <i class="bi bi-chevron-right role-chevron"></i>
            </div>
          </div>
        </div>
      </CCol>
      <CCol lg="7">
        <div v-if="selectedRole" class="permissions-card-modern">
          <div class="permissions-header">
            <div class="d-flex align-items-center gap-3">
              <div class="selected-role-icon" :class="getRoleColor(selectedRole.name)">
                <i :class="roleIcon(selectedRole.name)"></i>
              </div>
              <div>
                <h5 class="mb-1 fw-bold">{{ selectedRole.name }}</h5>
                <p class="small text-muted mb-0">Manage role permissions</p>
              </div>
            </div>
          </div>
          <div class="permissions-body">
            <div v-if="isLoadingPerms" class="text-center py-5">
              <CSpinner color="primary" />
              <p class="text-muted mt-3">Loading permissions...</p>
            </div>
            <template v-else>
              <div class="permissions-modern-grid">
                <div v-for="p in allPermissions" :key="p" :class="['perm-card', { active: hasRolePerm(p) }]"
                  @click="toggleRolePerm(p)">
                  <div class="perm-checkbox-wrapper">
                    <div class="custom-checkbox" :class="{ checked: hasRolePerm(p) }">
                      <i v-if="hasRolePerm(p)" class="bi bi-check"></i>
                    </div>
                    <label :for="'perm-' + p" class="perm-checkbox-label ms-2">
                      <i :class="getPermissionIcon(p)" class="me-2"></i>
                      <span>{{ labelFor(p) }}</span>
                    </label>
                  </div>
                </div>
              </div>
              <div class="permissions-footer">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-info-circle text-muted"></i>
                  <span class="small text-muted">
                    {{ rolePerms.length }} of {{ allPermissions.length }} permissions enabled
                  </span>
                </div>
                <CButton color="primary" size="lg" :disabled="isSaving" class="save-btn-modern"
                  @click="saveRolePerms">
                  <CSpinner v-if="isSaving" size="sm" class="me-2" />
                  <i v-else class="bi bi-check-lg me-2"></i>
                  Save Permissions
                </CButton>
              </div>
            </template>
          </div>
        </div>
        <div v-else class="empty-state-modern">
          <div class="empty-icon-wrapper">
            <i class="bi bi-shield-lock"></i>
          </div>
          <h5 class="mt-4 mb-2">Select a Role</h5>
          <p class="text-muted mb-0">
            Choose a role from the list to view and manage permissions
          </p>
        </div>
      </CCol>
    </CRow>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { CRow, CCol, CButton, CSpinner } from '@coreui/vue';
import { rolesApi } from '../../api';
import { useToast } from '../../composables/useToast';
import { useSettingsUtils } from '../../composables/useSettingsUtils';
import type { Role, Permission } from '../../composables/useSettingsUtils';

const props = defineProps<{
  roles: Role[];
}>();

const emit = defineEmits<{
  (e: 'stats-updated', payload: { totalPermissions: number; modulesCount: number }): void;
  (e: 'roles-updated'): void;
}>();

const toast = useToast();
const { roleIcon, getRoleColor, getPermissionIcon, labelFor } = useSettingsUtils();

const roleCounts = ref<Record<number, number>>({});
const allPermissions = ref<(Permission | string)[]>([
  { id: 1, name: 'users.manage', display_name: 'Manage Users', module: 'users' },
  { id: 2, name: 'attendance.approve', display_name: 'Approve Attendance', module: 'attendance' },
  { id: 3, name: 'attendance.view', display_name: 'View Attendance', module: 'attendance' },
  { id: 4, name: 'attendance.record', display_name: 'Record Attendance', module: 'attendance' },
  { id: 5, name: 'finance.manage', display_name: 'Manage Finance', module: 'finance' },
  { id: 6, name: 'contributions.manage', display_name: 'Manage Contributions', module: 'contributions' },
  { id: 7, name: 'expenses.manage', display_name: 'Manage Expenses', module: 'expenses' },
  { id: 8, name: 'visitors.manage', display_name: 'Manage Visitors', module: 'visitors' },
  { id: 9, name: 'followups.manage', display_name: 'Manage Follow-ups', module: 'followups' },
  { id: 10, name: 'broadcasts.send', display_name: 'Send Broadcasts', module: 'broadcasts' },
  { id: 11, name: 'department.manage', display_name: 'Manage Departments', module: 'department' },
  { id: 12, name: 'audit.view', display_name: 'View Audit Logs', module: 'audit' },
  { id: 13, name: 'reports.view', display_name: 'View Reports', module: 'reports' },
]);
const selectedRole = ref<Role | null>(null);
const rolePerms = ref<string[]>([]);
const isLoadingPerms = ref(false);
const isSaving = ref(false);

function emitStats() {
  const perms = allPermissions.value;
  const totalPermissions = perms.length;
  const modulesCount = perms.length > 0
    ? new Set(perms.map((p: any) => (typeof p === 'object' ? p.module : 'general') || 'general')).size
    : 0;
  emit('stats-updated', { totalPermissions, modulesCount });
}

async function loadPermissionCounts() {
  for (const role of props.roles) {
    try {
      const permsData = await rolesApi.getPermissions(role.id);
      roleCounts.value[role.id] = permsData.data.permissions?.length || 0;
    } catch {
      roleCounts.value[role.id] = 0;
    }
  }
}

watch(() => props.roles, async (newRoles) => {
  if (newRoles && newRoles.length > 0) {
    await loadPermissionCounts();
    emitStats();
  }
}, { immediate: true });

async function fetchRolePermissions(role: Role) {
  selectedRole.value = role;
  isLoadingPerms.value = true;
  try {
    const { data } = await rolesApi.getPermissions(role.id);
    rolePerms.value = data.permissions?.map((p: any) => p.name) || [];
    if (data.all_permissions && data.all_permissions.length > 0) {
      allPermissions.value = data.all_permissions;
      emitStats();
    }
    roleCounts.value[role.id] = rolePerms.value.length;
  } catch (error) {
    toast.error('Failed to load permissions', { color: 'danger' });
  } finally {
    isLoadingPerms.value = false;
  }
}

function toggleRolePerm(p: any) {
  const idx = rolePerms.value.indexOf(p);
  if (idx > -1) rolePerms.value.splice(idx, 1);
  else rolePerms.value.push(p);
}

function hasRolePerm(p: any) {
  return rolePerms.value.includes(p) || rolePerms.value.includes('all');
}

async function saveRolePerms() {
  if (!selectedRole.value) return;
  isSaving.value = true;
  try {
    await rolesApi.updatePermissions(selectedRole.value.id, rolePerms.value);
    roleCounts.value[selectedRole.value.id] = rolePerms.value.length;
    toast.success(`${selectedRole.value.name} permissions updated`, { color: 'success' });
    emit('roles-updated');
  } catch (error) {
    toast.error('Failed to save permissions', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function refresh() {
  loadPermissionCounts();
  selectedRole.value = null;
  rolePerms.value = [];
}

defineExpose({ refresh });
</script>

<style scoped>
/* Roles Card */
.roles-card-modern {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.04);
}

.roles-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.roles-header h5 {
  color: white;
}

.roles-header .text-muted {
  color: rgba(255, 255, 255, 0.8) !important;
}

.roles-list {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Role Card Item */
.role-card-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 16px;
  background: #f8f9fa;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.role-card-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background: currentColor;
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.role-card-item:hover {
  background: white;
  border-color: rgba(102, 126, 234, 0.2);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transform: translateX(4px);
}

.role-card-item.active {
  background: white;
  border-color: #667eea;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
  transform: translateX(4px);
}

.role-card-item.active::before {
  transform: scaleY(1);
}

.role-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.role-card-item:hover .role-icon-wrapper {
  transform: scale(1.1);
}

.role-icon-lg {
  font-size: 1.5rem;
  color: white;
}

.role-details {
  flex: 1;
  min-width: 0;
}

.role-name {
  font-weight: 600;
  font-size: 1rem;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.role-count {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: #64748b;
  gap: 0.25rem;
}

.role-count i {
  font-size: 0.75rem;
}

.role-chevron {
  color: #cbd5e1;
  transition: transform 0.3s ease, color 0.3s ease;
}

.role-card-item:hover .role-chevron,
.role-card-item.active .role-chevron {
  transform: translateX(4px);
  color: #667eea;
}

/* Role Colors */
.role-admin {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.role-pastor {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.role-usher {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.role-finance {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.role-pr {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.role-dept {
  background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
}

.role-default {
  background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

/* Permissions Card */
.permissions-card-modern {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.04);
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(20px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.permissions-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: linear-gradient(to bottom, white, #f8f9fa);
}

.selected-role-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.permissions-body {
  padding: 1.5rem;
}

/* Modern Permissions Grid */
.permissions-modern-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.perm-card {
  background: #f8f9fa;
  border: 2px solid transparent;
  border-radius: 12px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.perm-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}

.perm-card:hover {
  background: white;
  border-color: rgba(102, 126, 234, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.perm-card.active {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.05) 100%);
  border-color: #667eea;
}

.perm-card.active::before {
  transform: scaleX(1);
}

.perm-checkbox-wrapper {
  display: flex;
  align-items: center;
}

.perm-checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-weight: 500;
  color: #334155;
  font-size: 0.9rem;
  margin: 0;
  flex: 1;
  user-select: none;
}

.perm-card.active .perm-checkbox-label {
  color: #667eea;
}

.perm-checkbox-label i {
  color: #94a3b8;
  font-size: 1.1rem;
}

.perm-card.active .perm-checkbox-label i {
  color: #667eea;
}

/* Permissions Footer */
.permissions-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.save-btn-modern {
  padding: 0.75rem 2rem;
  border-radius: 12px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
}

.save-btn-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Custom Checkbox */
.custom-checkbox {
  width: 22px;
  height: 22px;
  border: 2px solid #e2e8f0;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  background: white;
  flex-shrink: 0;
}

.custom-checkbox.checked {
  background: #667eea;
  border-color: #667eea;
  color: white;
  transform: scale(1.1);
}

.custom-checkbox i {
  font-size: 16px;
  font-weight: bold;
}

/* Empty State */
.empty-state-modern {
  background: white;
  border-radius: 20px;
  padding: 4rem 2rem;
  text-align: center;
  border: 2px dashed #e2e8f0;
  animation: slideInRight 0.3s ease-out;
}

.empty-icon-wrapper {
  width: 80px;
  height: 80px;
  margin: 0 auto;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: #667eea;
}

.empty-state-modern h5 {
  color: #1e293b;
  font-weight: 600;
}

.empty-state-modern p {
  max-width: 400px;
  margin: 0 auto;
}

.md-fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .permissions-modern-grid {
    grid-template-columns: 1fr;
  }

  .permissions-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .save-btn-modern {
    width: 100%;
  }

  .role-card-item {
    padding: 0.875rem 1rem;
  }

  .role-icon-wrapper {
    width: 40px;
    height: 40px;
  }

  .role-icon-lg {
    font-size: 1.25rem;
  }
}
</style>
