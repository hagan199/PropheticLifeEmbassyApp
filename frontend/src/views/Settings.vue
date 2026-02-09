<template>
  <div class="settings-page">
    <!-- Modern Page Header -->
    <PageHeader title="Settings" subtitle="Configure roles, permissions, and system preferences">
      <!-- @ts-ignore -->
      <template #actions>
        <button class="settings-action-btn" @click="refreshData">
          <i class="bi bi-arrow-clockwise" :class="{ rotating: isLoading }"></i>
          <span>Refresh</span>
        </button>
      </template>
    </PageHeader>

    <!-- Summary KPI Cards -->
    <div v-if="tab === 'roles' || tab === 'permissions'" class="kpi-grid mb-5">
      <div class="kpi-card" style="--delay: 0s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)">
          <i class="bi bi-shield-check"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Total Roles</div>
          <div class="kpi-value">{{ totalRoles }}</div>
          <div class="kpi-sublabel">{{ systemRoles }} system roles</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.1s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)">
          <i class="bi bi-key"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Permissions</div>
          <div class="kpi-value">{{ totalPermissions }}</div>
          <div class="kpi-sublabel">{{ modulesCount }} modules</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.2s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%)">
          <i class="bi bi-people"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Users</div>
          <div class="kpi-value">{{ totalUsers }}</div>
          <div class="kpi-sublabel">{{ multiRoleUsers }} with multiple roles</div>
        </div>
      </div>
    </div>

    <!-- Improved Tab Navigation -->
    <div class="settings-tabs mb-4">
      <button v-for="t in tabs" :key="t.id" :class="['tab-button', { active: tab === t.id }]" @click="tab = t.id">
        <i :class="t.icon" class="me-2"></i>
        <span>{{ t.label }}</span>
      </button>
    </div>

    <div class="settings-content">
      <!-- Loading Overlay -->
      <div v-if="isLoading && tab === 'categories'" class="settings-loading">
        <CSpinner color="primary" />
        <span class="ms-2">Loading categories...</span>
      </div>

      <!-- Service Types Tab -->
      <div v-if="tab === 'serviceType'" class="md-fade-in">
        <CRow class="g-4">
          <CCol lg="6">
            <CCard class="h-100 md-card">
              <CCardHeader class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">Service Types</span>
                <div class="d-flex gap-2">
                  <CFormInput v-model="serviceTypeName" placeholder="New type..." size="sm" :disabled="isSaving"
                    @keyup.enter="addServiceType" />
                  <CButton color="primary" size="sm" :disabled="isSaving" @click="addServiceType">
                    <CSpinner v-if="isSaving && !editingServiceType" size="sm" class="me-1" />
                    <span>Add</span>
                  </CButton>
                </div>
              </CCardHeader>
              <CCardBody>
                <CListGroup flush>
                  <CListGroupItem v-for="s in serviceTypes" :key="s.id"
                    class="d-flex justify-content-between align-items-center py-3">
                    <div v-if="editingServiceType !== s.id" class="fw-medium">{{ s.name }}</div>
                    <CFormInput v-else v-model="editServiceTypeName" size="sm" class="w-75" />

                    <div class="action-btns">
                      <template v-if="editingServiceType === s.id">
                        <CButton color="success" size="sm" variant="ghost" :disabled="isSaving"
                          @click="saveServiceType(s.id)">
                          <CSpinner v-if="isSaving" size="sm" />
                          <i v-else class="bi bi-check-lg"></i>
                        </CButton>
                        <CButton color="secondary" size="sm" variant="ghost" :disabled="isSaving"
                          @click="cancelEditServiceType">
                          <i class="bi bi-x-lg"></i>
                        </CButton>
                      </template>
                      <template v-else>
                        <CButton color="primary" size="sm" variant="ghost" @click="startEditServiceType(s)">
                          <i class="bi bi-pencil"></i>
                        </CButton>
                        <CButton color="danger" size="sm" variant="ghost" @click="removeServiceType(s.id)">
                          <i class="bi bi-trash"></i>
                        </CButton>
                      </template>
                    </div>
                  </CListGroupItem>
                  <CListGroupItem v-if="serviceTypes.length === 0" class="text-center py-5 text-muted">
                    No service types defined.
                  </CListGroupItem>
                </CListGroup>
              </CCardBody>
            </CCard>
          </CCol>
          <CCol lg="6">
            <div class="md-help-card">
              <h5><i class="bi bi-info-circle me-2"></i>Usage Tips</h5>
              <p>
                Service types define the kinds of gatherings your church hosts. These will appear in
                the Attendance recording module.
              </p>
              <div class="alert alert-info py-2 small mb-3">
                <i class="bi bi-info-circle me-2"></i>Note: These types are currently pre-defined in
                the database schema.
              </div>
              <ul>
                <li><strong>Sunday Service:</strong> Main weekly gathering.</li>
                <li><strong>Midweek:</strong> Wednesday or Thursday Bible study.</li>
                <li><strong>Special Event:</strong> Conferences or concerts.</li>
              </ul>
            </div>
          </CCol>
        </CRow>
      </div>

      <!-- Categories Tab -->
      <div v-else-if="tab === 'categories'" class="md-fade-in">
        <CRow class="g-4">
          <CCol lg="6">
            <CCard class="h-100 md-card">
              <CCardHeader class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">Expense Categories</span>
                <div class="d-flex gap-2">
                  <CFormInput v-model="catName" placeholder="New category..." size="sm" :disabled="isSaving"
                    style="min-width: 160px" @keyup.enter="addCategory" />
                  <CFormInput v-model="catDescription" placeholder="Description..." size="sm" :disabled="isSaving"
                    style="min-width: 200px" @keyup.enter="addCategory" />
                  <CButton color="primary" size="sm" :disabled="isSaving || !catName.trim()" @click="addCategory">
                    <CSpinner v-if="isSaving && !editingCategory" size="sm" class="me-1" />
                    <span>Add</span>
                  </CButton>
                </div>
              </CCardHeader>
              <CCardBody>
                <div v-if="isLoading" class="text-center py-4">
                  <CSpinner color="primary" />
                </div>
                <CListGroup v-else flush>
                  <CListGroupItem v-for="c in categories" :key="c.id"
                    class="d-flex justify-content-between align-items-center py-3">
                    <div class="flex-grow-1">
                      <div v-if="editingCategory !== c.id">
                        <span class="fw-bold">{{ c.name }}</span>
                        <div class="small text-muted">{{ c.description || 'No description' }}</div>
                      </div>
                      <div v-else class="d-flex gap-2">
                        <CFormInput v-model="editCategoryName" size="sm" class="w-50" placeholder="Name" />
                        <CFormInput v-model="editCategoryDescription" size="sm" class="w-50"
                          placeholder="Description" />
                      </div>
                    </div>
                    <div class="action-btns">
                      <template v-if="editingCategory === c.id">
                        <CButton color="success" size="sm" variant="ghost" :disabled="isSaving"
                          @click="saveCategory(c.id)">
                          <CSpinner v-if="isSaving" size="sm" />
                          <i v-else class="bi bi-check-lg"></i>
                        </CButton>
                        <CButton color="secondary" size="sm" variant="ghost" :disabled="isSaving"
                          @click="cancelEditCategory">
                          <i class="bi bi-x-lg"></i>
                        </CButton>
                      </template>
                      <template v-else>
                        <CButton color="primary" size="sm" variant="ghost" @click="startEditCategory(c)"><i
                            class="bi bi-pencil"></i></CButton>
                        <CButton color="danger" size="sm" variant="ghost" @click="removeCategory(c.id)"><i
                            class="bi bi-trash"></i></CButton>
                      </template>
                    </div>
                  </CListGroupItem>
                  <CListGroupItem v-if="categories.length === 0" class="text-center py-4 text-muted">
                    No expense categories found.
                  </CListGroupItem>
                </CListGroup>
              </CCardBody>
            </CCard>
          </CCol>
          <CCol lg="6" class="d-flex align-items-stretch">
            <div class="md-help-card w-100 d-flex flex-column justify-content-center align-items-center text-center">
              <h5 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Finance Guidance</h5>
              <p class="mb-2">
                Categories help in organizing church expenses for better reporting and budgeting.
              </p>
              <div class="alert alert-success py-2 small mb-3 w-75 mx-auto">
                <i class="bi bi-cloud-check me-2"></i>Enabled real-time server synchronization.
              </div>
              <p class="mb-0">
                Common categories: Utilities, Missions, Welfare, Repairs, Honorarium.
              </p>
            </div>
          </CCol>
        </CRow>
      </div>

      <!-- Roles Tab -->
      <div v-else-if="tab === 'roles'" class="md-fade-in">
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

      <!-- User Management Tab -->
      <div v-else-if="tab === 'permissions'" class="md-fade-in">
        <CRow class="g-4">
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
                        <!-- Fallback to old single role field -->
                        <span v-else class="user-role-badge" :class="getRoleColor(roleNameById(u.role))">
                          <i :class="roleIcon(roleNameById(u.role))" class="me-1"></i>
                          {{ roleNameById(u.role) }}
                        </span>
                      </div>
                    </div>
                    <i class="bi bi-chevron-right user-chevron"></i>
                  </div>
                </div>
              </div>

              <!-- Pagination -->
              <div v-if="totalPages > 1" class="users-pagination px-3 py-2 border-top">
                <CPagination align="center" size="sm" class="mb-0">
                  <CPaginationItem :disabled="currentPage === 1" @click="handlePageChange(currentPage - 1)">
                    <i class="bi bi-chevron-left"></i>
                  </CPaginationItem>
                  <CPaginationItem v-for="p in totalPages" :key="p" :active="p === currentPage"
                    @click="handlePageChange(p)">
                    {{ p }}
                  </CPaginationItem>
                  <CPaginationItem :disabled="currentPage === totalPages" @click="handlePageChange(currentPage + 1)">
                    <i class="bi bi-chevron-right"></i>
                  </CPaginationItem>
                </CPagination>
              </div>
              <!-- pagination -->
            </div>
            <!-- users-card-modern -->
          </CCol>
          <CCol lg="7">
            <div v-if="selectedUser" class="user-details-card-modern">
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

              <!-- Role Assignment -->
              <div class="user-details-body">
                <div class="role-section-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-shield-check me-2"></i>User Roles
                    <span class="badge bg-primary-subtle text-primary ms-2">
                      {{ selectedUserRoles.length }} selected
                    </span>
                  </label>

                  <!-- Multiple Role Selection Cards -->
                  <div class="role-selection-grid">
                    <div v-for="role in roles" :key="role.id" :class="[
                      'role-selection-card',
                      { selected: selectedUserRoles.includes(role.id) },
                    ]" @click="toggleUserRole(role.id)">
                      <div class="role-selection-checkbox">
                        <i v-if="selectedUserRoles.includes(role.id)" class="bi bi-check-circle-fill"></i>
                        <i v-else class="bi bi-circle"></i>
                      </div>
                      <div class="role-selection-content">
                        <div class="role-selection-icon" :class="getRoleColor(role.name)">
                          <i :class="roleIcon(role.name)"></i>
                        </div>
                        <div class="role-selection-info">
                          <div class="role-selection-name">
                            {{ role.display_name || role.name }}
                          </div>
                          <div class="role-selection-desc">{{ getRoleDescription(role.name) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <button class="sync-btn-modern mt-3" :disabled="isSaving || selectedUserRoles.length === 0"
                    @click="saveUserRoles">
                    <i class="bi bi-check-circle me-2"></i>Save Role Changes
                  </button>
                </div>

                <div class="divider-modern"></div>

                <!-- Specific Permissions -->
                <div class="permissions-section-modern">
                  <h6 class="mb-3 fw-bold">
                    <i class="bi bi-key-fill me-2"></i>Custom Permissions
                  </h6>
                  <div class="permissions-modern-grid">
                    <div v-for="p in allPermissions" :key="p" :class="['perm-card', { active: hasUserPerm(p) }]"
                      @click="toggleUserPerm(p)">
                      <div class="perm-checkbox-wrapper">
                        <input :id="'uprm-' + p" type="checkbox" :checked="hasUserPerm(p)" class="perm-checkbox-input"
                          @click.stop @change="toggleUserPerm(p)" />
                        <label :for="'uprm-' + p" class="perm-checkbox-label">
                          <i :class="getPermissionIcon(p)" class="me-2"></i>
                          <span>{{ labelFor(p) }}</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Save Button -->
                <div class="user-details-footer">
                  <CButton color="primary" size="lg" :disabled="isSaving" class="save-btn-modern"
                    @click="saveUserChanges">
                    <CSpinner v-if="isSaving" size="sm" class="me-2" />
                    <i v-else class="bi bi-check-lg me-2"></i>
                    Save User Privileges
                  </CButton>
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
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  CRow,
  CCol,
  CCard,
  CCardBody,
  CCardHeader,
  CListGroup,
  CListGroupItem,
  CBadge,
  CButton,
  CFormInput,
  CFormCheck,
  CFormLabel,
  CFormSelect,
  CSpinner,
} from '@coreui/vue';
import _Breadcrumbs from '../components/Breadcrumbs.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { expensesApi, rolesApi, usersApi } from '../api';
import { useToast } from '../composables/useToast';

// Types
interface Category {
  id: number;
  name: string;
  description?: string;
}

interface Role {
  id: number;
  name: string;
  is_system?: boolean;
}

interface Permission {
  id: number;
  name: string;
  display_name?: string;
  module?: string;
}

interface User {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  roles?: Role[];
}

const toast = useToast();

// Global states
const tab = ref<string>('categories');
const isLoading = ref<boolean>(false);
const isSaving = ref<boolean>(false);
const isLoadingPerms = ref<boolean>(false);
const isLoadingUsers = ref<boolean>(false);

const tabs = [
  { id: 'categories', label: 'Finance Categories', icon: 'bi bi-tags' },
  { id: 'serviceType', label: 'Service Types', icon: 'bi bi-calendar-event' },
  { id: 'roles', label: 'Roles & Access', icon: 'bi bi-shield-lock' },
  { id: 'permissions', label: 'User Management', icon: 'bi bi-people' },
];

// KPI Computed Properties
const totalRoles = computed<number>(() => roles.value?.length || 0);
const systemRoles = computed<number>(() => roles.value?.filter((r: Role) => r.is_system)?.length || 0);
const totalPermissions = computed<number>(() => allPermissions.value?.length || 0);
const modulesCount = computed<number>(() => {
  if (!allPermissions.value || allPermissions.value.length === 0) return 0;
  return new Set(allPermissions.value.map((p: Permission) => p.module || 'general')).size;
});
const multiRoleUsers = computed<number>(
  () => users.value?.filter((u: User) => u.roles && u.roles.length > 1)?.length || 0
);

// --- Expense Categories ---
const categories = ref<Category[]>([]);
const catName = ref<string>('');
const catDescription = ref<string>('');
const editingCategory = ref<Category | null>(null);
const editCategoryName = ref<string>('');
const editCategoryDescription = ref<string>('');

async function loadCategories(): Promise<void> {
  isLoading.value = true;
  try {
    const { data } = await expensesApi.getTypes();
    categories.value = data.data || [];
  } catch (error: any) {
    toast.error('Failed to load categories', { color: 'danger' });
    console.error(error);
  } finally {
    isLoading.value = false;
  }
}

async function addCategory() {
  if (!catName.value.trim()) return;
  isSaving.value = true;
  try {
    const { data } = await expensesApi.createType({
      name: catName.value.trim(),
      description: catDescription.value.trim(),
    });
    categories.value.push(data.data);
    catName.value = '';
    catDescription.value = '';
    toast.success('Category added successfully', { color: 'success' });
  } catch (error) {
    toast.error('Failed to add category', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

async function removeCategory(id) {
  if (!confirm('Are you sure you want to delete this category?')) return;
  isSaving.value = true;
  try {
    // Assuming delete endpoint might not be fully standard for types,
    // but trying to follow standard API patterns
    categories.value = categories.value.filter(c => c.id !== id);
    toast.info('Category removed locally', { color: 'danger' });
  } catch (error) {
    toast.error('Failed to remove category', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function startEditCategory(c) {
  editingCategory.value = c.id;
  editCategoryName.value = c.name;
  editCategoryDescription.value = c.description || '';
}

async function saveCategory(id) {
  if (!editCategoryName.value.trim()) return;
  isSaving.value = true;
  try {
    const { data } = await expensesApi.updateType(id, {
      name: editCategoryName.value.trim(),
      description: editCategoryDescription.value.trim(),
    });
    const idx = categories.value.findIndex(x => x.id === id);
    if (idx !== -1) categories.value[idx] = data.data;
    toast.success('Category updated', { color: 'success' });
    editingCategory.value = null;
  } catch (error) {
    toast.error('Failed to update category', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function cancelEditCategory() {
  editingCategory.value = null;
  editCategoryName.value = '';
}

// --- Service Types ---
const serviceTypes = ref([]);
const serviceTypeName = ref('');
const editingServiceType = ref(null);
const editServiceTypeName = ref('');

function loadServiceTypes() {
  const raw = localStorage.getItem('service_types');
  const defaults = [
    { id: 1, name: 'Sunday Service' },
    { id: 2, name: 'Midweek Service' },
    { id: 3, name: 'Prayer Meeting' },
  ];
  try {
    serviceTypes.value = raw ? JSON.parse(raw) : defaults;
  } catch {
    serviceTypes.value = defaults;
  }
}

function saveServiceTypes() {
  localStorage.setItem('service_types', JSON.stringify(serviceTypes.value));
}

function addServiceType() {
  if (!serviceTypeName.value.trim()) return;
  isSaving.value = true;
  setTimeout(() => {
    serviceTypes.value.push({ id: Date.now(), name: serviceTypeName.value.trim() });
    serviceTypeName.value = '';
    saveServiceTypes();
    toast.success('Service type added', { color: 'success' });
    isSaving.value = false;
  }, 400);
}

function removeServiceType(id) {
  if (!confirm('Delete this service type?')) return;
  serviceTypes.value = serviceTypes.value.filter(s => s.id !== id);
  saveServiceTypes();
  toast.info('Service type removed', { color: 'danger' });
}

function startEditServiceType(s) {
  editingServiceType.value = s.id;
  editServiceTypeName.value = s.name;
}

function saveServiceType(id) {
  isSaving.value = true;
  setTimeout(() => {
    const s = serviceTypes.value.find(x => x.id === id);
    if (s && editServiceTypeName.value.trim()) {
      s.name = editServiceTypeName.value.trim();
      saveServiceTypes();
      toast.success('Service type updated', { color: 'success' });
    }
    editingServiceType.value = null;
    isSaving.value = false;
  }, 400);
}

function cancelEditServiceType() {
  editingServiceType.value = null;
  editServiceTypeName.value = '';
}

// --- Roles & Permissions ---
const roles = ref<Role[]>([]);
const roleCounts = ref<Record<string, number>>({});
const allPermissions = ref<Permission[]>([
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
const selectedRole = ref(null);
const rolePerms = ref([]);

async function loadRoles() {
  try {
    const { data } = await rolesApi.getAll();
    roles.value = data.data || [];

    // Load permission counts for all roles
    for (const role of roles.value) {
      try {
        const permsData = await rolesApi.getPermissions(role.id);
        roleCounts.value[role.id] = permsData.data.permissions?.length || 0;
      } catch (err) {
        roleCounts.value[role.id] = 0;
      }
    }
  } catch (error) {
    console.error('Failed to load roles');
  }
}

async function fetchRolePermissions(role) {
  selectedRole.value = role;
  isLoadingPerms.value = true;
  try {
    const { data } = await rolesApi.getPermissions(role.id);
    // Update rolePerms with permission names
    rolePerms.value = data.permissions?.map(p => p.name) || [];
    // Update allPermissions from API if available
    if (data.all_permissions && data.all_permissions.length > 0) {
      allPermissions.value = data.all_permissions;
    }
    roleCounts.value[role.id] = rolePerms.value.length;
  } catch (error) {
    toast.error('Failed to load permissions', { color: 'danger' });
  } finally {
    isLoadingPerms.value = false;
  }
}

function toggleRolePerm(p) {
  const idx = rolePerms.value.indexOf(p);
  if (idx > -1) rolePerms.value.splice(idx, 1);
  else rolePerms.value.push(p);
}

function hasRolePerm(p) {
  return rolePerms.value.includes(p) || rolePerms.value.includes('all');
}

async function saveRolePerms() {
  if (!selectedRole.value) return;
  isSaving.value = true;
  try {
    await rolesApi.updatePermissions(selectedRole.value.id, rolePerms.value);
    roleCounts.value[selectedRole.value.id] = rolePerms.value.length;
    toast.success(`${selectedRole.value.name} permissions updated`, { color: 'success' });
  } catch (error) {
    toast.error('Failed to save permissions', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function labelFor(p) {
  return p
    .split('.')
    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
    .join(': ');
}

// --- Users ---
const users = ref<User[]>([]);
const selectedUser = ref(null);
const selectedRoleId = ref(null);
const selectedUserRoles = ref([]); // New: for multiple role selection
const userSearch = ref('');
const userPerms = ref([]);
const userRoleFilter = ref(null);

// Pagination state
const currentPage = ref(1);
const perPage = ref(10);
const totalUsers = ref(0);
const totalPages = computed(() => Math.ceil(totalUsers.value / perPage.value));

async function loadUsers(search = '', roleFilter = null) {
  isLoadingUsers.value = true;
  try {
    const params = {
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
  } catch (error) {
    console.error('Failed to load users');
  } finally {
    isLoadingUsers.value = false;
  }
}

function handlePageChange(page) {
  currentPage.value = page;
  loadUsers(userSearch.value, userRoleFilter.value);
}

let searchTimeout = null;
function handleUserSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadUsers(userSearch.value, userRoleFilter.value);
  }, 300);
}

function clearUserSearch() {
  userSearch.value = '';
  loadUsers('', userRoleFilter.value);
}

function filterByRole(roleId) {
  userRoleFilter.value = roleId;
  loadUsers(userSearch.value, roleId);
}

function selectUser(u) {
  selectedUser.value = u;
  selectedRoleId.value = u.role;
  userPerms.value = u.permissions || [];

  // Populate selectedUserRoles from user's roles array
  if (u.roles && u.roles.length > 0) {
    selectedUserRoles.value = u.roles.map(role => role.id);
  } else if (u.role) {
    // Fallback: find role by name for backward compatibility
    const role = roles.value.find(r => r.id === u.role || r.name === u.role);
    selectedUserRoles.value = role ? [role.id] : [];
  } else {
    selectedUserRoles.value = [];
  }
}

function roleNameById(roleKey) {
  return roles.value.find(r => r.id === roleKey)?.name || roleKey;
}

function hasUserPerm(p) {
  return userPerms.value.includes(p) || userPerms.value.includes('all');
}

function toggleUserPerm(p) {
  const idx = userPerms.value.indexOf(p);
  if (idx > -1) userPerms.value.splice(idx, 1);
  else userPerms.value.push(p);
}

function _applyRoleDefaults() {
  toast.info('Synchronizing with standard role defaults...', { color: 'info' });
}

/**
 * Toggle a role for the selected user
 */
function toggleUserRole(roleId) {
  const index = selectedUserRoles.value.indexOf(roleId);
  if (index > -1) {
    selectedUserRoles.value.splice(index, 1);
  } else {
    selectedUserRoles.value.push(roleId);
  }
}

/**
 * Save multiple roles for the selected user
 */
async function saveUserRoles() {
  if (!selectedUser.value || selectedUserRoles.value.length === 0) return;
  isSaving.value = true;
  try {
    await usersApi.update(selectedUser.value.id, {
      role: selectedUserRoles.value[0], // Keep first role as primary for backward compatibility
      role_ids: selectedUserRoles.value, // Send all role IDs
    });
    toast.success(`Roles updated for ${selectedUser.value.name}`, { color: 'success' });

    // Refresh users list to show updated roles
    await loadUsers(userSearch.value, userRoleFilter.value);

    // Update selected user data
    const updatedUser = users.value.find(u => u.id === selectedUser.value.id);
    if (updatedUser) {
      selectUser(updatedUser);
    }
  } catch (error) {
    toast.error('Failed to update roles', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

/**
 * Get role description for UI display
 */
function getRoleDescription(roleName) {
  const descriptions = {
    admin: 'Full system access and management',
    Administrator: 'Full system access and management',
    pastor: 'Ministry oversight and leadership',
    Pastor: 'Ministry oversight and leadership',
    usher: 'Attendance recording and guest services',
    Usher: 'Attendance recording and guest services',
    finance: 'Financial management and reporting',
    'Finance Officer': 'Financial management and reporting',
    pr_follow_up: 'Visitor tracking and follow-ups',
    'PR/Follow-up': 'Visitor tracking and follow-ups',
    department_leader: 'Department coordination',
    'Department Leader': 'Department coordination',
  };
  return descriptions[roleName] || 'Standard access';
}

async function saveUserChanges() {
  if (!selectedUser.value) return;
  isSaving.value = true;
  try {
    await usersApi.update(selectedUser.value.id, {
      role: selectedRoleId.value,
    });
    toast.success(`Access privileges updated for ${selectedUser.value.name}`, { color: 'success' });
  } catch (error) {
    toast.error('Update failed', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function getRandomColor(str) {
  const colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger'];
  if (!str) return colors[0];
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
}

// Role icon mapping
function roleIcon(name) {
  const map = {
    Administrator: 'bi bi-person-badge',
    Pastor: 'bi bi-book',
    Usher: 'bi bi-people',
    'Finance Officer': 'bi bi-cash-coin',
    'PR/Follow-up': 'bi bi-megaphone',
    'Department Leader': 'bi bi-diagram-3',
  };
  return map[name] || 'bi bi-person';
}

// Role color scheme
function getRoleColor(name) {
  const colorMap = {
    Administrator: 'role-admin',
    Pastor: 'role-pastor',
    Usher: 'role-usher',
    'Finance Officer': 'role-finance',
    'PR/Follow-up': 'role-pr',
    'Department Leader': 'role-dept',
  };
  return colorMap[name] || 'role-default';
}

// Permission icon mapping

function getPermissionIcon(perm) {
  let permName = perm;
  if (typeof perm === 'object' && perm !== null) {
    permName = perm.name || '';
  }
  const [category] = String(permName).split('.');
  const iconMap = {
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
  return iconMap[category] || 'bi bi-check-circle-fill';
}

/**
 * Refresh all data
 */
async function refreshData() {
  isLoading.value = true;
  try {
    await Promise.all([loadCategories(), loadRoles(), loadUsers()]);
    toast.success('Settings refreshed successfully');
  } catch (error) {
    toast.error('Failed to refresh settings');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  loadCategories();
  loadServiceTypes();
  loadRoles();
  loadUsers();
});
</script>

<style scoped>
/* ======== SETTINGS PAGE LAYOUT ======== */
.settings-page {
  padding: 2rem 0;
}

/* ======== KPI GRID ======== */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideUp 0.5s ease-out forwards;
  opacity: 0;
  animation-delay: var(--delay, 0s);
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

.kpi-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.kpi-content {
  flex: 1;
}

.kpi-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.kpi-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.kpi-sublabel {
  font-size: 0.8rem;
  color: #94a3b8;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ======== IMPROVED TAB NAVIGATION ======== */
.settings-tabs {
  display: flex;
  gap: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
  overflow-x: auto;
  padding-bottom: 0;
  margin-bottom: 2rem;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  color: #64748b;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  white-space: nowrap;
  font-size: 0.95rem;
}

.tab-button:hover {
  color: #1e293b;
}

.tab-button.active {
  color: #6366f1;
}

.tab-button.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #6366f1;
}

/* ======== ACTION BUTTONS ======== */
.settings-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.settings-action-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.04);
}

.settings-action-btn i {
  font-size: 1rem;
}

.rotating {
  animation: rotate 1s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.settings-loading {
  .role-item {
    transition: background 0.2s, box-shadow 0.2s;
    cursor: pointer;
    border-radius: 0.75rem;
    margin-bottom: 0.5rem;
    outline: none;
  }

  .role-item:hover,
  .role-item.active,
  .role-item:focus {
    background: #f0f4ff;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.07);
    border-left: 4px solid #6366f1;
  }

  .role-icon {
    font-size: 1.5rem;
    color: #6366f1;
  }

  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--md-primary);
  opacity: 0.8;
}

.settings-content {
  position: relative;
  min-height: 400px;
}

.md-card {
  border: none;
  border-radius: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: transform 0.2s;
}

.md-card:hover {
  transform: translateY(-2px);
}

.md-card .card-header {
  background: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  font-size: 1.1rem;
}

.md-chip-group {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.md-chip {
  padding: 0.6rem 1.25rem;
  border-radius: 50rem;
  border: 1px solid #e0e0e0;
  background: white;
  white-space: nowrap;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
}

.md-chip.active {
  background: var(--md-primary, #0d6efd);
  color: white;
  border-color: var(--md-primary, #0d6efd);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
}

.action-btns {
  display: flex;
  gap: 0.25rem;
}

.md-help-card {
  background: #f8f9fa;
  border-radius: 1.5rem;
  padding: 2rem;
  border: 1px dashed #dee2e6;
  height: 100%;
}

.md-help-card h5 {
  color: var(--md-primary);
  margin-bottom: 1rem;
}

.cursor-pointer {
  cursor: pointer;
}

.active-item {
  background-color: rgba(13, 110, 253, 0.05) !important;
  border-left: 4px solid var(--md-primary, #0d6efd) !important;
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.user-avatar-sm {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #495057;
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

/* ========================================
   MODERN ROLES & ACCESS UI
   ======================================== */

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

.perm-checkbox-input {
  width: 20px;
  height: 20px;
  border-radius: 6px;
  border: 2px solid #cbd5e1;
  margin-right: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.perm-checkbox-input:checked {
  background-color: #667eea;
  border-color: #667eea;
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

@media (max-width: 768px) {
  .permissions-grid {
    grid-template-columns: 1fr;
  }

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

/* ========================================
   MODERN USER MANAGEMENT UI
   ======================================== */

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
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
  background: white;
  border-color: rgba(102, 126, 234, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateX(4px);
}

.user-card-modern.active {
  background: white;
  border-color: #667eea;
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

/* Multiple Role Selection Cards */
.role-selection-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.role-selection-card {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: white;
  border: 2px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.role-selection-card:hover {
  border-color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
}

.role-selection-card.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.role-selection-checkbox {
  flex-shrink: 0;
  font-size: 1.5rem;
  margin-top: 0.25rem;
  color: #cbd5e1;
  transition: all 0.3s ease;
}

.role-selection-card.selected .role-selection-checkbox {
  color: white;
  transform: scale(1.1);
}

.role-selection-content {
  display: flex;
  gap: 0.75rem;
  flex: 1;
}

.role-selection-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.role-selection-card.selected .role-selection-icon {
  background: rgba(255, 255, 255, 0.2) !important;
  color: white !important;
}

.role-selection-info {
  flex: 1;
  min-width: 0;
}

.role-selection-name {
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.role-selection-desc {
  font-size: 0.75rem;
  opacity: 0.7;
  line-height: 1.4;
}

.role-selection-card.selected .role-selection-desc {
  opacity: 0.9;
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

/* User Details Card */
.user-details-card-modern {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.04);
  animation: slideInRight 0.3s ease-out;
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

/* Role Section */
.role-section-modern {
  background: #f8f9fa;
  padding: 1.25rem;
  border-radius: 14px;
  margin-bottom: 1.5rem;
}

.form-label-modern {
  display: block;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.select-modern {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  background: white;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 1rem;
}

.select-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.sync-btn-modern {
  padding: 0.625rem 1.25rem;
  border-radius: 10px;
  border: 2px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
}

.sync-btn-modern:hover {
  border-color: #667eea;
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}

.divider-modern {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
  margin: 1.5rem 0;
}

/* Permissions Section */
.permissions-section-modern h6 {
  color: #1e293b;
}

.user-details-footer {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  display: flex;
  justify-content: flex-end;
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

/* Improved Transitions */
.md-fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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

/* Improved User Cards */
.user-card-modern {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.user-card-modern:hover {
  transform: translateX(8px) translateY(-2px);
  background: #f1f5f9;
}

.user-card-modern.active {
  border-left-width: 5px !important;
  background: #f0f4ff !important;
}

/* Pagination Styling */
.users-pagination :deep(.page-link) {
  border-radius: 8px;
  margin: 0 2px;
}
</style>
