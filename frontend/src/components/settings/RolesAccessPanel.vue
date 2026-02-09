<template>
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
              <i class="role-icon-lg bi bi-person-badge"></i>
            </div>
            <div class="role-details">
              <div class="role-name">{{ r.name }}</div>
              <div class="role-count"><i class="bi bi-people"></i> {{ roleCounts[r.id] || 0 }}</div>
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
              <i class="bi bi-shield-lock"></i>
            </div>
            <div>
              <div class="fw-semibold">{{ selectedRole.name }}</div>
              <div class="small text-muted">{{ roleCounts[selectedRole.id] || 0 }} permissions</div>
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
              <div v-for="p in allPermissions" :key="p.id" :class="['perm-card', { active: hasRolePerm(p.name) }]"
                @click="toggleRolePerm(p.name)">
                <div class="perm-checkbox-wrapper">
                  <input class="perm-checkbox-input" type="checkbox" :checked="hasRolePerm(p.name)" readonly />
                  <label class="perm-checkbox-label"> <i :class="getPermissionIcon(p)"></i>&nbsp; {{ labelFor(p)
                  }}</label>
                </div>
              </div>
            </div>
            <div class="permissions-footer">
              <div></div>
              <div>
                <CButton color="secondary" class="me-2" @click="fetchRolePermissions(selectedRole)">Reset</CButton>
                <CButton color="primary" :disabled="isSaving" @click="saveRolePerms">Save</CButton>
              </div>
            </div>
          </template>
        </div>
      </div>
      <div v-else class="empty-state-modern">
        <div class="empty-icon-wrapper"><i class="bi bi-shield-lock"></i></div>
        <h5 class="mt-4 mb-2">Select a Role</h5>
        <p class="text-muted mb-0">Choose a role from the list to view and manage permissions</p>
      </div>
    </CCol>
  </CRow>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
const emit = defineEmits(['stats-updated']);
import { rolesApi } from '@/api';
import { useToast } from '@/composables/useToast';
import { CRow, CCol, CButton, CSpinner } from '@coreui/vue';

const toast = useToast();
const roles = ref([] as any[]);
const roleCounts = ref<Record<string, number>>({});
const allPermissions = ref<any[]>([]);
const selectedRole = ref<any | null>(null);
const rolePerms = ref<string[]>([]);
const isLoadingPerms = ref(false);
const isSaving = ref(false);

async function loadRoles() {
  try {
    const { data } = await rolesApi.getAll();
    roles.value = data.data || [];
    for (const role of roles.value) {
      try {
        const permsData = await rolesApi.getPermissions(role.id);
        roleCounts.value[role.id] = permsData.data.permissions?.length || 0;
      } catch {
        roleCounts.value[role.id] = 0;
      }
    }
  } catch (error) {
    console.error('Failed to load roles');
  }
}

async function fetchRolePermissions(role: any) {
  selectedRole.value = role;
  isLoadingPerms.value = true;
  try {
    const { data } = await rolesApi.getPermissions(role.id);
    rolePerms.value = data.permissions?.map((p: any) => p.name) || [];
    if (data.all_permissions && data.all_permissions.length > 0) {
      allPermissions.value = data.all_permissions;
    }
    roleCounts.value[role.id] = rolePerms.value.length;
  } catch (error) {
    toast.error('Failed to load permissions');
  } finally {
    isLoadingPerms.value = false;
  }
}

function toggleRolePerm(p: string) {
  const idx = rolePerms.value.indexOf(p);
  if (idx > -1) rolePerms.value.splice(idx, 1);
  else rolePerms.value.push(p);
}

function hasRolePerm(p: string) {
  return rolePerms.value.includes(p) || rolePerms.value.includes('all');
}

async function saveRolePerms() {
  if (!selectedRole.value) return;
  isSaving.value = true;
  try {
    await rolesApi.updatePermissions(selectedRole.value.id, rolePerms.value);
    roleCounts.value[selectedRole.value.id] = rolePerms.value.length;
    toast.success(`${selectedRole.value.name} permissions updated`);
  } catch (error) {
    toast.error('Failed to save permissions');
  } finally {
    isSaving.value = false;
  }
}

function labelFor(p: any) {
  if (typeof p === 'object' && p !== null && p.display_name) return p.display_name;
  const permString = typeof p === 'string' ? p : p?.name || '';
  return permString.split('.').map((w: string) => w.charAt(0).toUpperCase() + w.slice(1)).join(': ');
}

function getPermissionIcon(p: any) {
  const permName = typeof p === 'string' ? p : p?.name || '';
  const [cat] = permName.split('.');
  const map: Record<string, string> = {
    users: 'bi bi-people-fill',
    attendance: 'bi bi-calendar-check-fill',
    finance: 'bi bi-cash-stack',
    contributions: 'bi bi-wallet2',
    expenses: 'bi bi-receipt',
    visitors: 'bi bi-person-plus-fill',
    followups: 'bi bi-telephone-fill',
    broadcasts: 'bi bi-megaphone-fill',
    department: 'bi bi-diagram-3-fill',
    audit: 'bi bi-file-earmark-text-fill',
    reports: 'bi bi-graph-up',
  };
  return map[cat] || 'bi bi-check-circle-fill';
}

function getRoleColor(name: string) {
  const colorMap: Record<string, string> = {
    Administrator: 'role-admin',
    Pastor: 'role-pastor',
    Usher: 'role-usher',
    'Finance Officer': 'role-finance',
    'PR/Follow-up': 'role-pr',
    'Department Leader': 'role-dept',
  };
  return colorMap[name] || 'role-default';
}

onMounted(() => loadRoles());

// Emit stats for parent KPI when data changes
function emitStats() {
  const totalPermissions = allPermissions.value.length || 0;
  const modules = new Set((allPermissions.value || []).map(p => p.module || 'general'));
  emit('stats-updated', { totalPermissions, modulesCount: modules.size });
}

// expose refresh
const refresh = () => loadRoles();

defineExpose({ refresh });

// emit helper when roles or permissions change
watch([allPermissions, roles], () => emitStats(), { immediate: true });
</script>

<style scoped>
.permissions-modern-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.perm-card {
  padding: 1rem;
  border-radius: 12px;
  background: #f8f9fa;
  cursor: pointer;
}

.perm-card.active {
  border: 2px solid #667eea;
  background: linear-gradient(135deg, rgba(102, 118, 234, 0.08), rgba(118, 75, 162, 0.05));
}
</style>