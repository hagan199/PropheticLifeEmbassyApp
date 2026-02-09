<template>
  <div class="page-wrap">
    <PageHeader title="Users" subtitle="Manage users, roles, and access control">
      <template #actions>
        <UsersActions @export="exportUsers" @add-user="openAddModal" />
      </template>
    </PageHeader>

    <UsersKPI :totalUsersDisplay="totalUsersDisplay" :activeUsersCount="activeUsersCount"
      :inactiveUsersCount="inactiveUsersCount" :rolesCount="roleOptions.length"
      :multiRoleUsersCount="multiRoleUsersCount" :departmentsCount="departments.length"
      :usersWithDeptCount="usersWithDeptCount" />

    <!-- Filters -->
    <MaterialCard class="mb-4">
      <template #header></template>
      <UsersFilters :filters="filters" :role-options="roleOptions" @update:filters="updateFilters"
        @search="handleSearch" @filter-change="handleFilterChange" @reset="resetFilters" />
    </MaterialCard>

    <!-- Users Table -->
    <MaterialCard>
      <template #header>
        <div class="fw-semibold">All Users</div>
        <CBadge color="primary">{{ users.length }} of {{ totalUsers }} users</CBadge>
      </template>
      <LoadingSkeleton v-if="isLoadingUsers" :rows="5" />
      <template v-else>
        <UserTable v-if="users.length" :users="paginatedUsers" :roleColor="roleColor" :roleIcon="roleIcon"
          :roleLabel="roleLabel" @edit="editUser" @deactivate="confirmDeactivate" @reactivate="reactivateUser" />
        <EmptyState v-else :title="emptyStateTitle" :subtitle="emptyStateSubtitle"
          :primary-action="emptyStatePrimaryAction" :secondary-action="emptyStateSecondaryAction"
          @primary-action="openAddModal" @secondary-action="resetFilters" />

        <!-- Pagination -->
        <UsersPagination v-if="users.length" :current-page="currentPage" :per-page="perPage" :total-items="totalUsers"
          item-label="users" @page-change="currentPage = $event" />
      </template>

      <CAlert v-if="tableError" color="danger" class="mt-3">
        {{ tableError }}
      </CAlert>
    </MaterialCard>

    <UserFormModal :visible="showModal" :isEditing="isEditing" :form="form" :errors="errors" :roleOptions="roleOptions"
      :departments="departments" :isLoadingDepartments="isLoadingDepartments" :saving="saving" @close="closeModal"
      @save="saveUser" @update-field="updateFormField" @update-role-ids="updateRoleIds"
      @update-department="updateDepartment" />

    <DeactivateModal :visible="showDeactivateModal" :user="userToDeactivate" @close="closeDeactivateModal"
      @deactivate="handleDeactivate" />
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue';
import {
  CRow,
  CCol,
  CButton,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableHeaderCell,
  CTableDataCell,
  CBadge,
  CAvatar,
  CFormInput,
  CFormSelect,
  CFormLabel,
  CFormTextarea,
  CInputGroup,
  CInputGroupText,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CForm,
  CAlert,
  CPagination,
  CPaginationItem,
  CSpinner,
} from '@coreui/vue';
import PageHeader from '../components/shared/PageHeader.vue';
import MaterialCard from '../components/material/MaterialCard.vue';
import _Breadcrumbs from '../components/Breadcrumbs.vue';
import UserFormModal from '../components/users/UserFormModal.vue';
import UsersKPI from '../components/users/UsersKPI.vue';
import UserTable from '../components/users/UserTable.vue';
import UsersFilters from '../components/users/UsersFilters.vue';
import UsersPagination from '../components/users/UsersPagination.vue';
import UsersActions from '../components/users/UsersActions.vue';
import DeactivateModal from '../components/users/DeactivateModal.vue';
import EmptyState from '../components/users/EmptyState.vue';
import LoadingSkeleton from '../components/users/LoadingSkeleton.vue';
import { exportToExcel } from '../utils/export.js';
import { usersApi, departmentsApi, rolesApi } from '../api';
import { useToast } from '../composables/useToast';

const roleColorMap = {
  admin: { label: 'Administrator', color: 'danger', icon: 'bi bi-shield-check' },
  pastor: { label: 'Pastor', color: 'primary', icon: 'bi bi-book' },
  usher: { label: 'Usher', color: 'info', icon: 'bi bi-people' },
  finance: { color: 'success', label: 'Finance Officer', icon: 'bi bi-cash-coin' },
  pr_follow_up: { color: 'warning', label: 'PR/Follow-up', icon: 'bi bi-megaphone' },
  department_leader: { color: 'dark', label: 'Department Leader', icon: 'bi bi-diagram-3' },
};

function roleColor(role) {
  return roleColorMap[role]?.color || 'secondary';
}

function roleIcon(role) {
  return roleColorMap[role]?.icon || 'bi bi-person';
}

function roleLabel(role) {
  return roleColorMap[role]?.label || role;
}

const roleOptions = ref(
  Object.entries(roleColorMap).map(([value, meta]) => ({
    value,
    label: meta.label,
    color: meta.color,
  }))
);

const departments = ref([]);
const users = ref([]);
const totalUsers = ref(0);
const isLoadingUsers = ref(false);
const tableError = ref('');
const isLoadingDepartments = ref(false);
const departmentError = ref('');

const filters = reactive({ search: '', role: '', status: '' });
const currentPage = ref(1);
const perPage = 25;

const totalUsersDisplay = computed(() => totalUsers.value);
const totalPages = computed(() => Math.max(1, Math.ceil(totalUsers.value / perPage)));

const paginatedUsers = computed(() => users.value);

// KPI Computed Properties
const activeUsersCount = computed(() => users.value.filter(u => u.status === 'active').length);
const inactiveUsersCount = computed(() => users.value.filter(u => u.status === 'inactive').length);
const multiRoleUsersCount = computed(
  () => users.value.filter(u => u.roles && u.roles.length > 1).length
);
const usersWithDeptCount = computed(() => users.value.filter(u => u.departmentName).length);

// Empty State Computed Properties
const emptyStateTitle = computed(() => 'No Users Found');

const emptyStateSubtitle = computed(() => {
  return filters.search || filters.role || filters.status
    ? 'No users match your current filters. Try adjusting your search criteria.'
    : 'Get started by adding your first user to the system.';
});

const emptyStatePrimaryAction = computed(() => {
  return filters.search || filters.role || filters.status
    ? null
    : { label: 'Add Your First User', icon: 'bi-plus-lg' };
});

const emptyStateSecondaryAction = computed(() => {
  return filters.search || filters.role || filters.status
    ? { label: 'Clear Filters', icon: 'bi-arrow-counterclockwise' }
    : null;
});

// Handle search with debounce
let searchTimeout;
watch(
  () => filters.search,
  () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      currentPage.value = 1;
      fetchUsers();
    }, 400);
  }
);

watch(
  () => [filters.role, filters.status],
  () => {
    currentPage.value = 1;
    fetchUsers();
  }
);

watch(currentPage, () => {
  fetchUsers();
});

function applyFilters() {
  currentPage.value = 1;
}

function resetFilters() {
  filters.search = '';
  filters.role = '';
  filters.status = '';
  currentPage.value = 1;
  fetchUsers();
}

function updateFilters(newFilters) {
  Object.assign(filters, newFilters);
}

function updateFormField({ field, value }) {
  form[field] = value;
}

function updateRoleIds(value) {
  form.role_ids = value;
}

function updateDepartment(value) {
  form.departmentId = value;
}

function closeDeactivateModal() {
  showDeactivateModal.value = false;
}

const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const form = reactive({
  id: null,
  phone: '',
  name: '',
  email: '',
  role: '', // Legacy
  role_ids: [], // Multi-role support
  departmentId: '',
  password: '',
});
const errors = reactive({ phone: '', name: '', role_ids: '', departmentId: '', password: '' });

const requiresDepartment = computed(() => {
  // If any selected role is usher or dept leader
  const selectedRoleNames = form.role_ids.map(
    id => roleOptions.value.find(o => o.value === id)?.label?.toLowerCase() || ''
  );
  return selectedRoleNames.some(name => ['usher', 'department leader'].includes(name));
});

const showDeactivateModal = ref(false);
const userToDeactivate = ref(null);

const toast = useToast();

onMounted(() => {
  fetchUsers();
  fetchDepartments();
  fetchRoles();
});

async function fetchUsers() {
  isLoadingUsers.value = true;
  tableError.value = '';
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage,
    };
    if (filters.search) params.search = filters.search;
    if (filters.role) params.role = filters.role;
    if (filters.status) params.is_active = filters.status === 'active';

    const { data } = await usersApi.getAll(params);
    users.value = (data.data || []).map(mapUser);
    totalUsers.value = data.total ?? users.value.length;
  } catch (error) {
    const message = error.response?.data?.message || 'Unable to load users.';
    tableError.value = message;
    toast.error(message);
  } finally {
    isLoadingUsers.value = false;
  }
}

async function fetchDepartments() {
  isLoadingDepartments.value = true;
  departmentError.value = '';
  try {
    const { data } = await departmentsApi.getAll();
    departments.value = data.data || [];
  } catch (error) {
    departmentError.value = error.response?.data?.message || 'Failed to load departments.';
  } finally {
    isLoadingDepartments.value = false;
  }
}

async function fetchRoles() {
  try {
    const { data } = await rolesApi.getAll();
    if (Array.isArray(data.data)) {
      roleOptions.value = data.data.map(role => ({
        value: role.id,
        label: role.name,
        color: roleColorMap[role.id]?.color || 'secondary',
      }));
    }
  } catch (error) {
    console.warn('Failed to load roles', error);
  }
}

function clearErrors() {
  Object.assign(errors, {
    phone: '',
    name: '',
    role_ids: '',
    departmentId: '',
    password: '',
  });
}

function openAddModal() {
  isEditing.value = false;
  Object.assign(form, {
    id: null,
    phone: '',
    name: '',
    email: '',
    role: '',
    role_ids: [],
    departmentId: '',
    password: '',
  });
  clearErrors();
  showModal.value = true;
}

function editUser(user) {
  isEditing.value = true;
  const roleIds = user.roles?.map(r => r.id) || [];

  // If no roles array, fallback to single role mapping
  if (roleIds.length === 0 && user.role) {
    const roleOpt = roleOptions.value.find(
      o => o.value === user.role || o.label.toLowerCase() === user.role.toLowerCase()
    );
    if (roleOpt) roleIds.push(roleOpt.value);
  }

  Object.assign(form, {
    id: user.id,
    phone: stripCountryCode(user.phone),
    name: user.name,
    email: user.email || '',
    role: user.role,
    role_ids: roleIds,
    departmentId: getDepartmentIdByName(user.departmentName),
    password: '',
  });
  clearErrors();
  showModal.value = true;
}

function toggleFormRole(roleId) {
  const index = form.role_ids.indexOf(roleId);
  if (index > -1) {
    form.role_ids.splice(index, 1);
  } else {
    form.role_ids.push(roleId);
  }
}

// Close the user form modal and reset transient state
function closeModal() {
  showModal.value = false;
  clearErrors();
}

function stripCountryCode(phone) {
  if (!phone) return '';
  return phone.replace(/^\+?233/, '');
}

function getDepartmentIdByName(name) {
  if (!name) return '';
  const dept = departments.value.find(d => d.name === name);
  return dept?.id || '';
}

function departmentNameById(id) {
  if (!id) return null;
  return departments.value.find(d => d.id === id)?.name || null;
}

function mapUser(user) {
  return {
    id: user.id,
    name: user.name,
    phone: user.phone,
    email: user.email,
    role: user.role,
    roles: user.roles || [], // Multi-role support
    departmentName: user.department?.name || (user.department ?? user.department_name ?? null),
    status: user.is_active === false ? 'inactive' : 'active',
    lastLogin: user.last_login ?? user.lastLogin ?? null,
    avatar:
      user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}`,
  };
}

async function saveUser() {
  if (!validateForm()) return;
  saving.value = true;

  // Get the primary role name for backward compatibility
  const primaryRole = roleOptions.value.find(o => o.value === form.role_ids[0]);
  const roleName = primaryRole?.label || form.role_ids[0];

  const basePayload = {
    phone: normalizePhone(form.phone),
    name: form.name.trim(),
    email: form.email || null,
    role: roleName,
    role_ids: form.role_ids,
    department_id: form.departmentId || null,
  };

  try {
    if (isEditing.value) {
      const response = await usersApi.update(form.id, basePayload);
      updateUserInList(form.id, {
        name: basePayload.name,
        email: basePayload.email,
        role: roleName,
        roles: response.data?.data?.roles || users.value.find(u => u.id === form.id)?.roles || [],
        departmentName:
          departmentNameById(form.departmentId) ||
          users.value.find(u => u.id === form.id)?.departmentName,
      });
      toast.success(response.data?.message || 'User updated successfully');
    } else {
      const response = await usersApi.create({ ...basePayload, password: form.password });
      const createdUser = mapUser({
        ...response.data?.data,
        department: departmentNameById(form.departmentId),
      });
      users.value = [createdUser, ...users.value];
      totalUsers.value += 1;
      toast.success(response.data?.message || 'User created successfully');
    }
    closeModal();
  } catch (error) {
    // Show backend validation errors per-field
    const resp = error.response?.data;
    if (resp?.errors) {
      if (resp.errors.phone) errors.phone = resp.errors.phone[0];
      if (resp.errors.name) errors.name = resp.errors.name[0];
      if (resp.errors.email) errors.name = resp.errors.email[0];
      if (resp.errors.role) errors.role_ids = resp.errors.role[0];
      if (resp.errors.password) errors.password = resp.errors.password[0];
      if (resp.errors.department_id) errors.departmentId = resp.errors.department_id[0];
    }
    toast.error(resp?.message || 'Failed to save user');
  } finally {
    saving.value = false;
  }
}

function updateUserInList(id, payload) {
  const index = users.value.findIndex(u => u.id === id);
  if (index !== -1) {
    users.value[index] = { ...users.value[index], ...payload };
  }
}

function confirmDeactivate(user) {
  userToDeactivate.value = user;
  showDeactivateModal.value = true;
}

async function deactivateUser() {
  if (!userToDeactivate.value) return;
  try {
    await usersApi.deactivate(userToDeactivate.value.id, {
      reason: deactivateReason.value || undefined,
    });
    updateUserInList(userToDeactivate.value.id, { status: 'inactive' });
    toast.info(`${userToDeactivate.value.name} has been deactivated`);
    showDeactivateModal.value = false;
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to deactivate user';
    toast.error(message);
  }
}

async function reactivateUser(user) {
  try {
    await usersApi.reactivate(user.id);
    updateUserInList(user.id, { status: 'active' });
    toast.success(`${user.name} has been reactivated`);
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to reactivate user';
    toast.error(message);
  }
}

function getRandomColor(name) {
  const colors = [
    'avatar-purple',
    'avatar-blue',
    'avatar-green',
    'avatar-orange',
    'avatar-pink',
    'avatar-red',
  ];
  // Generate consistent color based on name
  const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
  return colors[hash % colors.length];
}

function handleSearch(searchTerm) {
  filters.search = searchTerm;
  currentPage.value = 1;
  fetchUsers();
}

function handleFilterChange(newFilters) {
  Object.assign(filters, newFilters);
  currentPage.value = 1;
  fetchUsers();
}

function handleDeactivate(reason) {
  if (!userToDeactivate.value) return;
  deactivateReason.value = reason;
  deactivateUser();
}
</script>

<style scoped>
/* Minimal overrides */
.page-wrap {
  padding: var(--md-space-6);
}

:deep(.table tbody tr) {
  border-bottom: 1px solid #f1f5f9;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.table tbody tr:hover) {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
  transform: scale(1.01);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

:deep(.table tbody td) {
  padding: 18px;
  vertical-align: middle;
  color: #334155;
  font-size: 14px;
  font-weight: 500;
}

/* Stunning Avatar Styling */
:deep(.avatar) {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 3px solid transparent;
  background: linear-gradient(white, white) padding-box,
    linear-gradient(135deg, #667eea, #764ba2) border-box;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.avatar:hover) {
  transform: scale(1.15) rotate(5deg);
  box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
}

/* Beautiful Badge Styling */
:deep(.badge) {
  padding: 8px 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 11px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  position: relative;
  overflow: hidden;
}

:deep(.badge)::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s;
}

:deep(.badge:hover)::before {
  left: 100%;
}

:deep(.badge-danger) {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

:deep(.badge-primary) {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

:deep(.badge-success) {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

:deep(.badge-warning) {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

:deep(.badge-info) {
  background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
  color: #334155;
}

:deep(.badge-secondary) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Stunning Button Styling */
:deep(.btn) {
  border-radius: 14px;
  padding: 12px 24px;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  position: relative;
  overflow: hidden;
  letter-spacing: 0.02em;
}

:deep(.btn)::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s;
}

:deep(.btn:hover)::before {
  left: 100%;
}

:deep(.btn-primary) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.5);
  color: white;
}

:deep(.btn-primary:hover) {
  background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
  box-shadow: 0 15px 30px -5px rgba(102, 126, 234, 0.6);
  transform: translateY(-3px);
}

:deep(.btn-light) {
  background: white;
  border: 2px solid rgba(102, 126, 234, 0.2);
  color: #667eea;
  font-weight: 700;
}

:deep(.btn-light:hover) {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.2);
}

:deep(.btn-success) {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  box-shadow: 0 10px 20px -5px rgba(67, 233, 123, 0.5);
}

:deep(.btn-success:hover) {
  box-shadow: 0 15px 30px -5px rgba(67, 233, 123, 0.6);
  transform: translateY(-3px);
}

:deep(.btn-danger) {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  box-shadow: 0 10px 20px -5px rgba(245, 87, 108, 0.5);
}

:deep(.btn-danger:hover) {
  box-shadow: 0 15px 30px -5px rgba(245, 87, 108, 0.6);
  transform: translateY(-3px);
}

:deep(.btn-sm) {
  padding: 8px 16px;
  font-size: 12px;
  border-radius: 10px;
}

/* Beautiful Form Controls */
:deep(.form-control),
:deep(.form-select) {
  border-radius: 14px;
  border: 2px solid #e2e8f0;
  padding: 12px 18px;
  font-size: 14px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  font-weight: 500;
}

:deep(.form-control:focus),
:deep(.form-select:focus) {
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 10px 20px -5px rgba(102, 126, 234, 0.2);
  background: white;
  transform: translateY(-2px);
}

:deep(.form-label) {
  font-weight: 700;
  color: #475569;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

/* Stunning Pagination */
:deep(.pagination) {
  gap: 8px;
}

:deep(.page-item) {
  margin: 0;
}

:deep(.page-link) {
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  color: #667eea;
  padding: 10px 18px;
  font-weight: 700;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
}

:deep(.page-link:hover) {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.2);
}

:deep(.page-item.active .page-link) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: transparent;
  color: white;
  box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.5);
  transform: translateY(-2px);
}

/* Beautiful Loading Spinner */
:deep(.spinner-border) {
  width: 56px;
  height: 56px;
  border-width: 5px;
  border-color: rgba(102, 126, 234, 0.2);
  border-top-color: #667eea;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Stunning Empty State */
.text-muted.py-5 {
  padding: 64px 0 !important;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
  border-radius: 20px;
}

.text-muted.py-5 .bi {
  font-size: 80px !important;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 20px;
  opacity: 0.6;
}

/* Stunning Modal Design */
:deep(.modal-content) {
  border-radius: 24px;
  border: none;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

:deep(.modal-header) {
  border-bottom: 2px solid #f1f5f9;
  padding: 24px 32px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}

:deep(.modal-title) {
  font-weight: 800;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 24px;
}

:deep(.modal-body) {
  padding: 32px;
}

:deep(.modal-footer) {
  border-top: 2px solid #f1f5f9;
  padding: 20px 32px;
  background: #f8fafc;
}

/* Beautiful Alert */
:deep(.alert) {
  border-radius: 16px;
  border: none;
  padding: 16px 24px;
  font-weight: 600;
  font-size: 14px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

:deep(.alert-success) {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

:deep(.alert-danger) {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

:deep(.alert-info) {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

/* Responsive Improvements */
@media (max-width: 768px) {
  .page-wrap {
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    padding: 16px;
  }

  .page-header .title {
    font-size: 24px;
  }

  .page-header>div:last-child {
    margin-top: 16px;
    width: 100%;
  }

  :deep(.card-body) {
    padding: 16px;
  }

  :deep(.table thead th) {
    font-size: 11px;
    padding: 12px 10px;
  }

  :deep(.table tbody td) {
    padding: 12px 10px;
    font-size: 13px;
  }
}

/* Pagination Layout */
.pagination-container {
  display: flex !important;
  justify-content: space-between !important;
  align-items: center !important;
  margin-top: 2rem !important;
  padding: 1.5rem 1rem !important;
  border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
}

.pagination-info {
  font-size: 0.875rem !important;
  color: #64748b !important;
}

/* --- Pagination Fix --- */
:deep(.pagination) {
  display: flex !important;
  list-style: none !important;
  padding: 0 !important;
  margin: 0 !important;
  gap: 8px !important;
}

:deep(.pagination .page-item) {
  margin: 0 !important;
  display: list-item !important;
  /* Ensure block/flex doesn't break bootstrap logic */
}

/* Remove bullets if they somehow persist */
:deep(.pagination .page-item::before) {
  display: none !important;
}

:deep(.page-link) {
  border-radius: 12px !important;
  border: none !important;
  background: #f8fafc !important;
  color: #64748b !important;
  width: 40px !important;
  height: 40px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-weight: 600 !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
}

:deep(.page-item.active .page-link) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  color: white !important;
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3) !important;
  transform: translateY(-2px) !important;
}

:deep(.page-link:hover:not(.active)) {
  background: #f1f5f9 !important;
  color: #334155 !important;
  transform: translateY(-1px) !important;
}

/* --- Table Refinement --- */
.user-row {
  transition: all 0.3s ease;
}

.user-row:hover {
  background-color: rgba(102, 126, 234, 0.04) !important;
}

.user-profile-cell {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 0;
}

.user-avatar-modern {
  width: 48px !important;
  height: 48px !important;
  border-radius: 14px !important;
  font-weight: bold;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.user-identity {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
}

.user-email {
  font-size: 0.85rem;
}

.role-badge-modern {
  padding: 8px 12px !important;
  border-radius: 10px !important;
  font-weight: 600 !important;
  letter-spacing: 0.3px;
  font-size: 0.75rem !important;
  text-transform: uppercase;
}

/* Status Indicator */
.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 30px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.status-indicator.active {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.status-indicator.inactive {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  position: relative;
}

.active .status-dot {
  background: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.active .status-dot::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: inherit;
  animation: pulse 2s infinite;
}

.inactive .status-dot {
  background: #ef4444;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 0.8;
  }

  100% {
    transform: scale(2.5);
    opacity: 0;
  }
}

/* Role Multi-Select */
.role-multi-select {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}

.role-chip {
  padding: 8px 16px;
  border-radius: 12px;
  background: #f1f5f9;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 600;
  font-size: 0.85rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}

.role-chip:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.role-chip.active {
  background: rgba(102, 126, 234, 0.1);
  border-color: #667eea;
  color: #667eea;
}

.role-chip.active i.bi-check-lg {
  color: #667eea;
}

/* Scrollbar and rest persist below... */

/* ======== KPI SUMMARY CARDS ======== */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
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
  transform: translateY(-6px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
}

.kpi-icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.kpi-content {
  flex: 1;
}

.kpi-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.kpi-value {
  font-size: 1.875rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.25rem;
  line-height: 1;
}

.kpi-sublabel {
  font-size: 0.8rem;
  color: #94a3b8;
  font-weight: 500;
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

/* ======== RESPONSIVE IMPROVEMENTS ======== */
@media (max-width: 768px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .kpi-card {
    padding: 1.25rem;
  }

  .kpi-icon {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }

  .kpi-value {
    font-size: 1.5rem;
  }
}

/* ======== AVATAR COLORS ======== */
:deep(.avatar-purple) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  color: white !important;
}

:deep(.avatar-blue) {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
  color: white !important;
}

:deep(.avatar-green) {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important;
  color: white !important;
}

:deep(.avatar-orange) {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important;
  color: white !important;
}

:deep(.avatar-pink) {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
  color: white !important;
}

:deep(.avatar-red) {
  background: linear-gradient(135deg, #ff6a00 0%, #ee0979 100%) !important;
  color: white !important;
}
</style>

<!-- GLOBAL styles for Teleported modals (scoped CSS cannot reach Teleported elements) -->
<style>
.modal-bottom-sheet.modal {
  z-index: 9999 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  overflow-x: hidden !important;
  overflow-y: auto !important;
}

.modal-bottom-sheet.modal.show {
  display: block !important;
  opacity: 1 !important;
}

.modal-bottom-sheet .modal-dialog {
  position: relative;
  margin: 4vh auto !important;
  pointer-events: none;
}

.modal-bottom-sheet .modal-dialog.modal-lg {
  max-width: 680px;
}

.modal-bottom-sheet.modal.show .modal-dialog {
  transform: none !important;
  pointer-events: auto;
}

.modal-bottom-sheet .modal-content {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  pointer-events: auto;
  background-color: #fff !important;
  border: none !important;
  border-radius: 20px !important;
  outline: 0;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2) !important;
  overflow: hidden;
}

/* Hide default CModal header since we use custom */
.modal-bottom-sheet .modal-header {
  display: none !important;
}

.modal-bottom-sheet .modal-body {
  padding: 1rem 1.5rem !important;
}

.modal-bottom-sheet .modal-footer {
  display: none !important;
}

.modal-backdrop {
  z-index: 9998 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background-color: rgba(0, 0, 0, 0.45) !important;
  backdrop-filter: blur(4px);
}

.modal-backdrop.show {
  opacity: 1 !important;
}

/* ---- Custom Modal Header ---- */
.modal-header-custom {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal-header-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.modal-header-icon.add {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.modal-header-icon.edit {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  color: white;
}

.modal-header-icon.deactivate {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.modal-header-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.modal-header-sub {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 2px 0 0;
}

.modal-close-btn {
  margin-left: auto;
  background: #f1f5f9;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-close-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

/* ---- Form Sections ---- */
.form-section {
  padding: 0.25rem 0 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.form-section:last-child {
  border-bottom: none;
  padding-bottom: 0.5rem;
}

.form-section-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 0.75rem;
}

.form-field-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.35rem;
}

.form-field-label .req {
  color: #ef4444;
}

.field-error {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 4px;
  font-weight: 500;
}

.phone-prefix {
  font-weight: 600;
  color: #64748b;
  background: #f8fafc !important;
  border-color: #e2e8f0 !important;
}

/* ---- Role Grid ---- */
.role-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.role-select-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 0.75rem;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
  position: relative;
}

.role-select-btn:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}

.role-select-btn.selected {
  background: rgba(102, 126, 234, 0.08);
  border-color: #667eea;
  color: #4f46e5;
}

.role-select-icon {
  font-size: 1rem;
  opacity: 0.7;
}

.role-select-btn.selected .role-select-icon {
  opacity: 1;
}

.role-select-name {
  flex: 1;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.role-check {
  color: #667eea;
  font-size: 0.9rem;
}

/* ---- Custom Modal Footer ---- */
.modal-footer-custom {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  background: #fafbfc;
}

.modal-btn-cancel {
  padding: 0.6rem 1.25rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  color: #64748b;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-btn-cancel:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
}

.modal-btn-save {
  padding: 0.6rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 10px;
  color: white;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.modal-btn-save:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.modal-btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-btn-danger {
  padding: 0.6rem 1.5rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border: none;
  border-radius: 10px;
  color: white;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.modal-btn-danger:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.modal-btn-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ---- Deactivate Warning ---- */
.deactivate-warning {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: #fef3c7;
  border-radius: 12px;
  border: 1px solid #fcd34d;
  color: #92400e;
  font-size: 0.88rem;
  line-height: 1.5;
}

.deactivate-warning i {
  font-size: 1.2rem;
  color: #f59e0b;
  margin-top: 2px;
  flex-shrink: 0;
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
  .modal-bottom-sheet .modal-dialog {
    max-width: 95% !important;
    margin: 2vh auto !important;
  }

  .role-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .role-grid {
    grid-template-columns: 1fr;
  }
}
</style>
