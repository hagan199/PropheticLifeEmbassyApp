<template>
  <div class="md-fade-in">
    <CRow class="g-3">
      <CCol lg="5">
        <div class="users-card-modern">
          <!-- Search & Filter Header -->
          <div class="users-header">
            <h5 class="mb-1 fw-bold"><i class="bi bi-people-fill me-2"></i>User Management</h5>
            <p class="small text-muted mb-0">{{ users.length }} users loaded</p>
          </div>

          <!-- Advanced Search -->
          <div class="users-search-section">
            <div class="search-input-wrapper">
              <i class="bi bi-search search-icon"></i>
              <input v-model="userSearch" type="text" placeholder="Search by name, email, or phone..."
                class="search-input-modern" @input="handleUserSearch" />
              <button v-if="userSearch" class="search-clear-btn" @click="clearUserSearch">
                <i class="bi bi-x-circle-fill"></i>
              </button>
            </div>

            <!-- Quick Filter Chips -->
            <div class="filter-chips">
              <button :class="['filter-chip', { active: userRoleFilter === null }]" @click="filterByRole(null)">
                <i class="bi bi-grid-fill me-1"></i>All Roles
              </button>
              <button v-for="r in roles" :key="r.id" :class="['filter-chip', { active: userRoleFilter === r.id }]"
                @click="filterByRole(r.id)">
                <i :class="roleIcon(r.name)" class="me-1"></i>{{ r.name }}
              </button>
            </div>
          </div>

          <!-- Users List -->
          <div class="users-list-wrapper">
            <div v-if="isLoadingUsers" class="loading-users">
              <CSpinner color="primary" />
              <p class="text-muted mt-3">Loading users...</p>
            </div>
            <div v-else-if="users.length === 0" class="empty-users">
              <i class="bi bi-person-x"></i>
              <p class="mt-3 mb-0">No users found</p>
              <small class="text-muted">Try adjusting your filters</small>
            </div>
            <div v-else class="users-list">
              <div v-for="u in users" :key="u.id"
                :class="['user-card-modern', { active: selectedUser?.id === u.id }]" @click="selectUser(u)">
                <div class="user-avatar-modern" :class="getRandomColor(u.name)">
                  {{ u.name.charAt(0).toUpperCase() }}
                </div>
                <div class="user-info-modern">
                  <div class="user-name-modern">
                    {{ u.name }}
                    <span v-if="u.phone" class="user-phone-badge">
                      <i class="bi bi-telephone-fill me-1"></i>{{ u.phone }}
                    </span>
                  </div>
                  <div class="user-meta-modern">
                    <!-- Multiple Roles Support -->
                    <template v-if="u.roles && u.roles.length > 0">
                      <span v-for="role in u.roles" :key="role.id" class="user-role-badge"
                        :class="getRoleColor(role.name)">
                        <i :class="roleIcon(role.name)" class="me-1"></i>
                        {{ role.display_name || role.name }}
                      </span>
                    </template>
                    <!-- No roles assigned -->
                    <span v-else class="user-role-badge badge-secondary">
                      No roles
                    </span>
                  </div>
                </div>
                <i class="bi bi-chevron-right user-chevron"></i>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="users-pagination">
            <div class="pagination-controls">
              <button class="pagination-btn" :disabled="currentPage === 1"
                @click="handlePageChange(currentPage - 1)">
                <i class="bi bi-chevron-left"></i>
              </button>
              <div class="pagination-numbers">
                <button v-for="p in Math.min(totalPages, 5)" :key="p"
                  :class="['pagination-number', { active: p === currentPage }]" @click="handlePageChange(p)">
                  {{ p }}
                </button>
                <span v-if="totalPages > 5" class="pagination-ellipsis">...</span>
              </div>
              <button class="pagination-btn" :disabled="currentPage === totalPages"
                @click="handlePageChange(currentPage + 1)">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
            <div class="pagination-info">
              <span class="text-muted small">
                Page {{ currentPage }} of {{ totalPages }}
              </span>
            </div>
          </div>
        </div>
        <!-- users-card-modern -->
      </CCol>
      <CCol lg="7">
        <div v-if="selectedUser" class="user-details-card-modern" style="max-height: 700px; overflow-y: auto;">
          <!-- User Header -->
          <div class="user-details-header">
            <div class="d-flex align-items-center gap-3">
              <div class="user-avatar-large" :class="getRandomColor(selectedUser.name)">
                {{ selectedUser.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h5 class="mb-1 fw-bold">{{ selectedUser.name }}</h5>
                <p class="small text-muted mb-1">
                  <i class="bi bi-envelope me-1"></i>{{ selectedUser.email || 'No email' }}
                </p>
                <p v-if="selectedUser.phone" class="small text-muted mb-0">
                  <i class="bi bi-telephone-fill me-1"></i>{{ selectedUser.phone }}
                </p>
              </div>
            </div>
          </div>

          <!-- Role Assignment - Simple & Clean -->
          <div class="user-details-body">
            <div class="role-section-compact">
              <label class="form-label-modern mb-3">
                <i class="bi bi-shield-check me-2"></i>Assign Roles
                <span class="badge bg-primary-subtle text-primary ms-2">
                  {{ selectedUserRoles.length }}
                </span>
              </label>
              <div class="roles-quick-select">
                <button v-for="role in roles" :key="role.id" :class="[
                  'role-quick-btn',
                  { selected: selectedUserRoles.includes(role.id) },
                ]" @click="toggleUserRole(role.id)" :title="getRoleDescription(role.name)">
                  <i :class="roleIcon(role.name)"></i>
                  <span class="role-name-short">{{ role.name }}</span>
                </button>
              </div>
              <button class="save-btn-role w-100 mt-3" :disabled="isSaving || selectedUserRoles.length === 0"
                @click="saveUserRoles">
                <CSpinner v-if="isSaving" size="sm" class="me-2" />
                <i v-else class="bi bi-check-circle me-2"></i>
                {{ isSaving ? 'Saving...' : 'Save Roles' }}
              </button>
            </div>
          </div>
        </div>
        <div v-else class="empty-state-modern">
          <div class="empty-icon-wrapper">
            <i class="bi bi-person-badge"></i>
          </div>
          <h5 class="mt-4 mb-2">Select a User</h5>
          <p class="text-muted mb-0">
            Choose a user from the list to customize their access and permissions
          </p>
        </div>
      </CCol>
    </CRow>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { CRow, CCol, CSpinner } from '@coreui/vue';
import { usersApi } from '../../api';
import { useToast } from '../../composables/useToast';
import { useSettingsUtils } from '../../composables/useSettingsUtils';
import type { Role, User } from '../../composables/useSettingsUtils';

const props = defineProps<{
  roles: Role[];
}>();

const emit = defineEmits<{
  (e: 'stats-updated', payload: { totalUsers: number; multiRoleUsers: number }): void;
}>();

const toast = useToast();
const { roleIcon, getRoleColor, getRandomColor, getRoleDescription } = useSettingsUtils();

const users = ref<User[]>([]);
const selectedUser = ref<User | null>(null);
const selectedUserRoles = ref<number[]>([]);
const userSearch = ref('');
const userRoleFilter = ref<number | null>(null);
const isLoadingUsers = ref(false);
const isSaving = ref(false);

// Pagination state
const currentPage = ref(1);
const perPage = ref(10);
const totalUsers = ref(0);
const totalPages = computed(() => Math.ceil(totalUsers.value / perPage.value));

function emitStats() {
  const multiRoleUsers = users.value?.filter((u: User) => u.roles && u.roles.length > 1)?.length || 0;
  emit('stats-updated', { totalUsers: totalUsers.value, multiRoleUsers });
}

async function loadUsers(search: string = '', roleFilter: number | null = null) {
  isLoadingUsers.value = true;
  try {
    const params: any = {
      search,
      page: currentPage.value,
      per_page: perPage.value,
    };
    if (roleFilter) {
      params.role = roleFilter;
    }
    const { data } = await usersApi.getAll(params);
    users.value = data.data || [];
    totalUsers.value = data.total || users.value.length;
    emitStats();
  } catch (error) {
    console.error('Failed to load users');
  } finally {
    isLoadingUsers.value = false;
  }
}

function handlePageChange(page: number) {
  currentPage.value = page;
  loadUsers(userSearch.value, userRoleFilter.value);
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
function handleUserSearch() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadUsers(userSearch.value, userRoleFilter.value);
  }, 300);
}

function clearUserSearch() {
  userSearch.value = '';
  loadUsers('', userRoleFilter.value);
}

function filterByRole(roleId: number | null) {
  userRoleFilter.value = roleId;
  loadUsers(userSearch.value, roleId);
}

function selectUser(u: User) {
  selectedUser.value = u;
  if (u.roles && u.roles.length > 0) {
    selectedUserRoles.value = u.roles.map(role => role.id);
  } else {
    selectedUserRoles.value = [];
  }
}

function toggleUserRole(roleId: number) {
  const index = selectedUserRoles.value.indexOf(roleId);
  if (index > -1) {
    selectedUserRoles.value.splice(index, 1);
  } else {
    selectedUserRoles.value.push(roleId);
  }
}

async function saveUserRoles() {
  if (!selectedUser.value || selectedUserRoles.value.length === 0) return;
  const userId = selectedUser.value.id;
  const userName = selectedUser.value.name;
  const userEmail = selectedUser.value.email || '';
  const userPhone = selectedUser.value.phone || '';

  isSaving.value = true;
  try {
    await usersApi.update(userId, {
      name: userName,
      email: userEmail,
      phone: userPhone,
      role_ids: selectedUserRoles.value,
    });
    toast.success(`Roles updated for ${userName}`, { color: 'success' });

    await loadUsers(userSearch.value, userRoleFilter.value);

    const updatedUser = users.value.find(u => u.id === userId);
    if (updatedUser) {
      selectUser(updatedUser);
    }
  } catch (error) {
    console.error('Error updating roles:', error);
    toast.error('Failed to update roles', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function refresh() {
  loadUsers(userSearch.value, userRoleFilter.value);
}

defineExpose({ refresh });

onMounted(() => {
  loadUsers();
});
</script>

<style scoped>
/* Users Card */
.users-card-modern {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.users-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.users-header h5 {
  color: white;
}

.users-header .text-muted {
  color: rgba(255, 255, 255, 0.8) !important;
}

/* Search Section */
.users-search-section {
  padding: 1.25rem;
  background: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.search-input-wrapper {
  position: relative;
  margin-bottom: 1rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.1rem;
  pointer-events: none;
}

.search-input-modern {
  width: 100%;
  padding: 0.875rem 3rem 0.875rem 3rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
}

.search-input-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-input-modern::placeholder {
  color: #cbd5e1;
}

.search-clear-btn {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.25rem;
  transition: color 0.2s ease;
}

.search-clear-btn:hover {
  color: #64748b;
}

/* Filter Chips */
.filter-chips {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  overflow-x: auto;
  padding-bottom: 0.25rem;
}

.filter-chip {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  border: 2px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  display: flex;
  align-items: center;
}

.filter-chip:hover {
  border-color: #cbd5e1;
  background: #f8f9fa;
}

.filter-chip.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.filter-chip i {
  font-size: 0.85rem;
}

/* Users List Wrapper */
.users-list-wrapper {
  flex: 1;
  overflow-y: auto;
  min-height: 400px;
  max-height: 600px;
}

.loading-users,
.empty-users {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: #94a3b8;
}

.empty-users i {
  font-size: 3rem;
  color: #cbd5e1;
}

/* Users List */
.users-list {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* User Card */
.user-card-modern {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 14px;
  background: #f8f9fa;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
  position: relative;
  overflow: hidden;
}

.user-card-modern::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.user-card-modern:hover {
  transform: translateX(8px) translateY(-2px);
  background: #f1f5f9;
}

.user-card-modern.active {
  background: #f0f4ff !important;
  border-color: #667eea;
  border-left-width: 5px !important;
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.15);
  transform: translateX(4px);
}

.user-card-modern.active::before {
  transform: scaleY(1);
}

.user-avatar-modern {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  color: white;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.user-card-modern:hover .user-avatar-modern {
  transform: scale(1.1);
}

.user-info-modern {
  flex: 1;
  min-width: 0;
}

.user-name-modern {
  font-weight: 600;
  font-size: 0.95rem;
  color: #1e293b;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-meta-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.user-role-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.user-role-badge i {
  font-size: 0.7rem;
}

.user-phone-badge {
  display: inline-flex;
  align-items: center;
  margin-left: 0.75rem;
  padding: 0.25rem 0.625rem;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 500;
  color: #667eea;
}

.user-phone-badge i {
  font-size: 0.65rem;
}

.user-chevron {
  color: #cbd5e1;
  transition: transform 0.3s ease, color 0.3s ease;
  flex-shrink: 0;
}

.user-card-modern:hover .user-chevron,
.user-card-modern.active .user-chevron {
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

/* User Details Card */
.user-details-card-modern {
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

.user-details-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: linear-gradient(to bottom, white, #f8f9fa);
}

.user-avatar-large {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.75rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.user-details-body {
  padding: 1.5rem;
}

/* Compact User Details Layout */
.role-section-compact {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.form-label-modern {
  display: block;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.roles-quick-select {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.role-quick-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  flex: 1;
  min-width: 120px;
  justify-content: center;
}

.role-quick-btn:hover {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}

.role-quick-btn.selected {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.role-name-short {
  font-weight: 500;
}

.save-btn-role {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
}

.save-btn-role:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.save-btn-role:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

/* Pagination Styling */
.users-pagination {
  padding: 1rem;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  background: #f8f9fa;
}

.pagination-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.pagination-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
  transform: translateY(-2px);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f1f5f9;
}

.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.pagination-number {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 2px solid transparent;
  background: white;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pagination-number:hover {
  border-color: #667eea;
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}

.pagination-number.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.pagination-ellipsis {
  color: #cbd5e1;
  font-weight: 500;
  margin: 0 0.25rem;
}

.pagination-info {
  text-align: center;
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
  .users-list-wrapper {
    max-height: 400px;
  }

  .filter-chips {
    overflow-x: scroll;
    -webkit-overflow-scrolling: touch;
  }

  .user-card-modern {
    padding: 0.875rem 1rem;
  }

  .user-avatar-modern {
    width: 36px;
    height: 36px;
    font-size: 0.95rem;
  }

  .user-name-modern {
    font-size: 0.9rem;
  }

  .user-role-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
  }
}
</style>
