<template>
  <CRow class="g-3">
    <CCol lg="5">
      <div class="users-card-modern">
        <div class="users-header">
          <h5 class="mb-1 fw-bold"><i class="bi bi-people-fill me-2"></i>User Management</h5>
          <p class="small text-muted mb-0">{{ users.length }} users loaded</p>
        </div>

        <div class="users-search-section">
          <div class="search-input-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input v-model="userSearch" type="text" placeholder="Search by name, email, or phone..."
              class="search-input-modern" @input="handleUserSearch" />
          </div>
          <div class="filter-chips">
            <button class="md-chip" :class="{ active: !userRoleFilter }" @click="filterByRole(null)">All</button>
            <button v-for="r in roleOptions" :key="r.value" class="md-chip"
              :class="{ active: userRoleFilter === r.value }" @click="filterByRole(r.value)">{{ r.label }}</button>
          </div>
        </div>

        <div class="users-list-wrapper">
          <div v-if="isLoadingUsers" class="loading-users">
            <CSpinner />
          </div>
          <div v-else-if="users.length === 0" class="empty-users">
            <i class="bi bi-people"></i>
            <div class="mt-3">No users found</div>
          </div>
          <div v-else class="users-list">
            <div v-for="u in users" :key="u.id" class="user-card-modern" @click="selectUser(u)">
              <div class="user-avatar-modern" :class="getRandomColor(u.name)">{{ u.name.charAt(0) }}</div>
              <div class="user-info-modern">
                <div class="user-name-modern">{{ u.name }}</div>
                <div class="user-meta-modern">
                  <div class="user-role-badge">{{ (u.roles && u.roles[0]?.name) || u.role || '—' }}</div>
                  <div v-if="u.phone" class="user-phone-badge">{{ u.phone }}</div>
                </div>
              </div>
              <i class="bi bi-chevron-right user-chevron"></i>
            </div>
          </div>
        </div>

        <div v-if="totalPages > 1" class="users-pagination">
          <UsersPagination :current-page="currentPage" :total-items="totalUsers" item-label="users"
            @page-change="page => { currentPage = page; loadUsers(userSearch, userRoleFilter); }" />
        </div>
      </div>
    </CCol>

    <CCol lg="7">
      <div v-if="selectedUser" class="user-details-card-modern">
        <div class="user-details-header">
          <div class="d-flex align-items-center gap-3">
            <div class="user-avatar-large" :class="getRandomColor(selectedUser.name)">{{ selectedUser.name.charAt(0) }}
            </div>
            <div>
              <div class="fw-semibold">{{ selectedUser.name }}</div>
              <div class="small text-muted">{{ selectedUser.email || 'No email' }}</div>
            </div>
          </div>
        </div>
        <div class="user-details-body">
          <div class="role-section-compact">
            <div class="roles-quick-select">
              <button v-for="r in roleOptions" :key="r.value"
                :class="['role-quick-btn', { selected: selectedUserRoles.includes(r.value) }]"
                @click="toggleUserRole(r.value)">{{ r.label }}</button>
            </div>
            <div class="d-flex justify-content-end">
              <button class="save-btn-role" :disabled="isSavingRoles" @click="saveUserRoles">Save Roles</button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="empty-state-modern">
        <div class="empty-icon-wrapper"><i class="bi bi-person-badge"></i></div>
        <h5 class="mt-4 mb-2">Select a User</h5>
        <p class="text-muted mb-0">Choose a user from the list to customize their access and permissions</p>
      </div>
    </CCol>
  </CRow>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
const emit = defineEmits(['stats-updated']);
import { usersApi, rolesApi } from '@/api';
import { useToast } from '@/composables/useToast';
import UsersPagination from '@/components/users/UsersPagination.vue';
import { CRow, CCol, CSpinner } from '@coreui/vue';

const toast = useToast();
const users = ref([] as any[]);
const selectedUser = ref<any | null>(null);
const selectedUserRoles = ref<number[]>([]);
const userSearch = ref('');
const userRoleFilter = ref<number | null>(null);
const roleOptions = ref([] as any[]);
const isLoadingUsers = ref(false);
const isSavingRoles = ref(false);

const currentPage = ref(1);
const perPage = ref(10);
const totalUsers = ref(0);
const totalPages = computed(() => Math.ceil(totalUsers.value / perPage.value));

async function loadUsers(search = '', roleFilter: number | null = null) {
  isLoadingUsers.value = true;
  try {
    const params: any = { search, page: currentPage.value, per_page: perPage.value };
    if (roleFilter) params.role = roleFilter;
    const { data } = await usersApi.getAll(params);
    users.value = data.data || [];
    totalUsers.value = data.total || users.value.length;
    // Emit stats for parent
    const multi = users.value.filter(u => u.roles && u.roles.length > 1).length;
    emit('stats-updated', { totalUsers: totalUsers.value, multiRoleUsers: multi });
  } catch (error) {
    toast.error('Failed to load users');
  } finally {
    isLoadingUsers.value = false;
  }
}

async function loadRoles() {
  try {
    const { data } = await rolesApi.getAll();
    roleOptions.value = (data.data || []).map((r: any) => ({ value: r.id, label: r.name }));
  } catch {
    // ignore
  }
}

function handleUserSearch() {
  clearTimeout((handleUserSearch as any)._timeout);
  (handleUserSearch as any)._timeout = setTimeout(() => loadUsers(userSearch.value, userRoleFilter.value), 300);
}

function filterByRole(roleId: number | null) {
  userRoleFilter.value = roleId;
  loadUsers(userSearch.value, roleId);
}

function selectUser(u: any) {
  selectedUser.value = u;
  selectedUserRoles.value = u.roles?.map((r: any) => r.id) || [];
}

function toggleUserRole(roleId: number) {
  const idx = selectedUserRoles.value.indexOf(roleId);
  if (idx > -1) selectedUserRoles.value.splice(idx, 1);
  else selectedUserRoles.value.push(roleId);
}

async function saveUserRoles() {
  if (!selectedUser.value) return;
  isSavingRoles.value = true;
  try {
    await usersApi.update(selectedUser.value.id, { role_ids: selectedUserRoles.value });
    toast.success('Roles updated');
    await loadUsers(userSearch.value, userRoleFilter.value);
    const updated = users.value.find(u => u.id === selectedUser.value.id);
    if (updated) selectUser(updated);
  } catch {
    toast.error('Failed to update roles');
  } finally {
    isSavingRoles.value = false;
  }
}

function getRandomColor(name: string) {
  const colors = ['avatar-purple', 'avatar-blue', 'avatar-green', 'avatar-orange', 'avatar-pink', 'avatar-red'];
  let hash = 0;
  for (let i = 0; i < name.length; i++) hash = hash + name.charCodeAt(i);
  return colors[Math.abs(hash) % colors.length];
}

onMounted(() => { loadRoles(); loadUsers(); });

const refresh = () => { loadRoles(); loadUsers(); };

defineExpose({ refresh });
</script>

<style scoped>
.users-list {
  max-height: 420px;
  overflow: auto;
}
</style>