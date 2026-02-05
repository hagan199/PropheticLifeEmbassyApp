<template>
  <div class="page-wrap">
    <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false">
      {{ notification.message }}
    </CAlert>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Users</h2>
        <Breadcrumbs />
        <div class="text-muted">Manage users, roles, and access control</div>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" @click="exportUsers">
          <i class="bi bi-file-earmark-excel me-1"></i> Export
        </CButton>
        <CButton color="primary" @click="openAddModal">
          <i class="bi bi-plus-lg me-1"></i> Add User
        </CButton>
      </div>
    </div>

    <!-- Filters -->
    <CCard class="mb-4">
      <CCardBody>
        <CRow class="g-3 align-items-end">
          <CCol md="4">
            <CFormLabel>Search</CFormLabel>
            <CFormInput v-model="filters.search" placeholder="Name or phone..." @input="applyFilters" />
          </CCol>
          <CCol md="3">
            <CFormLabel>Role</CFormLabel>
            <CFormSelect v-model="filters.role" @change="applyFilters">
              <option value="">All Roles</option>
              <option v-for="r in roleOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
            </CFormSelect>
          </CCol>
          <CCol md="3">
            <CFormLabel>Status</CFormLabel>
            <CFormSelect v-model="filters.status" @change="applyFilters">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </CFormSelect>
          </CCol>
          <CCol md="2">
            <CButton color="light" class="w-100" @click="resetFilters">Reset</CButton>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>

    <!-- Users Table -->
    <CCard>
      <CCardHeader class="d-flex justify-content-between align-items-center">
        <div class="fw-semibold">All Users</div>
        <CBadge color="primary">{{ filteredUsers.length }} / {{ totalUsersDisplay }} users</CBadge>
      </CCardHeader>
      <CCardBody>
        <div v-if="isLoadingUsers" class="text-center py-5">
          <CSpinner color="primary" size="lg" />
        </div>
        <template v-else>
          <CTable v-if="filteredUsers.length" hover responsive align="middle">
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Name</CTableHeaderCell>
              <CTableHeaderCell>Phone</CTableHeaderCell>
              <CTableHeaderCell>Role</CTableHeaderCell>
              <CTableHeaderCell>Department</CTableHeaderCell>
              <CTableHeaderCell>Status</CTableHeaderCell>
              <CTableHeaderCell>Last Login</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="user in paginatedUsers" :key="user.id">
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <CAvatar :src="user.avatar" size="sm" class="me-2" />
                  <div>
                    <div class="fw-semibold">{{ user.name }}</div>
                    <div class="text-muted small">{{ user.email }}</div>
                  </div>
                </div>
              </CTableDataCell>
              <CTableDataCell>{{ user.phone }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="roleColor(user.role)">{{ roleLabel(user.role) }}</CBadge>
              </CTableDataCell>
              <CTableDataCell>{{ user.departmentName || '—' }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="user.status === 'active' ? 'success' : 'secondary'">
                  {{ user.status }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell class="text-muted">{{ user.lastLogin || 'Never' }}</CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="light" size="sm" class="me-1" @click="editUser(user)">
                  <i class="bi bi-pencil"></i>
                </CButton>
                <CButton v-if="user.status === 'active'" color="danger" size="sm" variant="ghost"
                  @click="confirmDeactivate(user)">
                  <i class="bi bi-person-slash"></i>
                </CButton>
                <CButton v-else color="success" size="sm" variant="ghost" @click="reactivateUser(user)">
                  <i class="bi bi-person-check"></i>
                </CButton>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
          </CTable>
          <div v-else class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No users found for the current filters.
          </div>

          <!-- Pagination -->
          <div v-if="filteredUsers.length" class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Showing {{ (currentPage - 1) * perPage + 1 }} to
              {{ Math.min(currentPage * perPage, filteredUsers.length) }}
              of {{ filteredUsers.length }}
            </div>
            <CPagination>
              <CPaginationItem :disabled="currentPage === 1" @click="currentPage--">Previous</CPaginationItem>
              <CPaginationItem v-for="p in totalPages" :key="p" :active="p === currentPage"
                @click="currentPage = p">
                {{ p }}
              </CPaginationItem>
              <CPaginationItem :disabled="currentPage === totalPages" @click="currentPage++">Next</CPaginationItem>
            </CPagination>
          </div>
        </template>

        <CAlert v-if="tableError" color="danger" class="mt-3">
          {{ tableError }}
        </CAlert>
      </CCardBody>
    </CCard>

    <!-- Add/Edit Modal -->
    <CModal :visible="showModal" @close="closeModal" size="lg">
      <CModalHeader>
        <CModalTitle>{{ isEditing ? 'Edit User' : 'Add New User' }}</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm @submit.prevent="saveUser">
          <CRow class="g-3">
            <CCol md="6">
              <CFormLabel>Phone <span class="text-danger">*</span></CFormLabel>
              <CInputGroup>
                <CInputGroupText>+233</CInputGroupText>
                <CFormInput v-model="form.phone" :disabled="isEditing" placeholder="XXXXXXXXX"
                  :invalid="!!errors.phone" />
              </CInputGroup>
              <div v-if="errors.phone" class="text-danger small mt-1">{{ errors.phone }}</div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Name <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.name" :invalid="!!errors.name" />
              <div v-if="errors.name" class="text-danger small mt-1">{{ errors.name }}</div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Email</CFormLabel>
              <CFormInput v-model="form.email" type="email" />
            </CCol>
            <CCol md="6">
              <CFormLabel>Role <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.role" :invalid="!!errors.role">
                <option value="">Select role...</option>
                <option v-for="r in roleOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
              </CFormSelect>
              <div v-if="errors.role" class="text-danger small mt-1">{{ errors.role }}</div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Department <span v-if="requiresDepartment" class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.departmentId" :invalid="!!errors.departmentId" :disabled="isLoadingDepartments">
                <option value="">Select department...</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </CFormSelect>
              <div v-if="errors.departmentId" class="text-danger small mt-1">{{ errors.departmentId }}</div>
              <div v-if="departmentError" class="text-danger small mt-1">{{ departmentError }}</div>
            </CCol>
            <CCol md="6" v-if="!isEditing">
              <CFormLabel>Password <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.password" type="password" autocomplete="new-password"
                :invalid="!!errors.password" />
              <div v-if="errors.password" class="text-danger small mt-1">{{ errors.password }}</div>
            </CCol>
          </CRow>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="closeModal">Cancel</CButton>
        <CButton color="primary" :disabled="saving" @click="saveUser">
          <CSpinner v-if="saving" size="sm" class="me-1" />
          {{ isEditing ? 'Update' : 'Create' }}
        </CButton>
      </CModalFooter>
    </CModal>

    <!-- Deactivate Confirmation Modal -->
    <CModal :visible="showDeactivateModal" @close="showDeactivateModal = false">
      <CModalHeader>
        <CModalTitle>Deactivate User</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <p>Are you sure you want to deactivate <strong>{{ userToDeactivate?.name }}</strong>?</p>
        <CAlert color="warning" class="mb-3">
          <i class="bi bi-exclamation-triangle me-2"></i>
          This user will no longer be able to log in.
        </CAlert>
        <CFormLabel>Reason <span class="text-danger">*</span></CFormLabel>
        <CFormTextarea v-model="deactivateReason" rows="3" placeholder="Provide a reason for deactivation..." />
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showDeactivateModal = false">Cancel</CButton>
        <CButton color="danger" :disabled="!deactivateReason.trim()" @click="deactivateUser">
          Deactivate
        </CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CFormTextarea, CInputGroup, CInputGroupText, CModal, CModalHeader,
  CModalTitle, CModalBody, CModalFooter, CForm, CAlert, CPagination, CPaginationItem, CSpinner
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'
import { usersApi, departmentsApi, rolesApi } from '../api'

const roleColorMap = {
  admin: { label: 'Admin', color: 'danger' },
  pastor: { label: 'Pastor', color: 'primary' },
  usher: { label: 'Usher', color: 'info' },
  finance: { label: 'Finance Officer', color: 'success' },
  pr_follow_up: { label: 'PR / Follow-up', color: 'warning' },
  department_leader: { label: 'Department Leader', color: 'secondary' }
}

const roleOptions = ref(
  Object.entries(roleColorMap).map(([value, meta]) => ({
    value,
    label: meta.label,
    color: meta.color
  }))
)

const departments = ref([])
const users = ref([])
const totalUsers = ref(0)
const isLoadingUsers = ref(false)
const tableError = ref('')
const isLoadingDepartments = ref(false)
const departmentError = ref('')

const filters = reactive({ search: '', role: '', status: '' })
const currentPage = ref(1)
const perPage = 25

const filteredUsers = computed(() => {
  return users.value.filter((u) => {
    if (filters.search) {
      const term = filters.search.toLowerCase()
      const matches =
        u.name?.toLowerCase().includes(term) ||
        u.phone?.toLowerCase().includes(term) ||
        u.email?.toLowerCase().includes(term)
      if (!matches) return false
    }
    if (filters.role && u.role !== filters.role) return false
    if (filters.status && u.status !== filters.status) return false
    return true
  })
})

const totalUsersDisplay = computed(() => totalUsers.value || users.value.length || 0)

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / perPage)))
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

function applyFilters() {
  currentPage.value = 1
}

function resetFilters() {
  filters.search = ''
  filters.role = ''
  filters.status = ''
  currentPage.value = 1
  fetchUsers()
}

watch(
  () => [filters.role, filters.status],
  () => {
    currentPage.value = 1
    fetchUsers()
  }
)

const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const form = reactive({
  id: null,
  phone: '',
  name: '',
  email: '',
  role: '',
  departmentId: '',
  password: ''
})
const errors = reactive({ phone: '', name: '', role: '', departmentId: '', password: '' })

const requiresDepartment = computed(() => ['usher', 'department_leader'].includes(form.role))

const showDeactivateModal = ref(false)
const userToDeactivate = ref(null)
const deactivateReason = ref('')

const notification = reactive({ show: false, type: 'success', message: '' })

onMounted(() => {
  fetchUsers()
  fetchDepartments()
  fetchRoles()
})

async function fetchUsers() {
  isLoadingUsers.value = true
  tableError.value = ''
  try {
    const params = {}
    if (filters.role) params.role = filters.role
    if (filters.status) params.is_active = filters.status === 'active'
    const { data } = await usersApi.getAll(params)
    users.value = (data.data || []).map(mapUser)
    totalUsers.value = data.total ?? users.value.length
  } catch (error) {
    const message = error.response?.data?.message || 'Unable to load users.'
    tableError.value = message
    showNotification('danger', message)
  } finally {
    isLoadingUsers.value = false
  }
}

async function fetchDepartments() {
  isLoadingDepartments.value = true
  departmentError.value = ''
  try {
    const { data } = await departmentsApi.getAll()
    departments.value = data.data || []
  } catch (error) {
    departmentError.value = error.response?.data?.message || 'Failed to load departments.'
  } finally {
    isLoadingDepartments.value = false
  }
}

async function fetchRoles() {
  try {
    const { data } = await rolesApi.getAll()
    if (Array.isArray(data.data)) {
      roleOptions.value = data.data.map((role) => ({
        value: role.id,
        label: role.name,
        color: roleColorMap[role.id]?.color || 'secondary'
      }))
    }
  } catch (error) {
    console.warn('Failed to load roles', error)
  }
}

function openAddModal() {
  isEditing.value = false
  Object.assign(form, { id: null, phone: '', name: '', email: '', role: '', departmentId: '', password: '' })
  clearErrors()
  showModal.value = true
}

function editUser(user) {
  isEditing.value = true
  Object.assign(form, {
    id: user.id,
    phone: stripCountryCode(user.phone),
    name: user.name,
    email: user.email || '',
    role: user.role,
    departmentId: getDepartmentIdByName(user.departmentName),
    password: ''
  })
  clearErrors()
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function clearErrors() {
  Object.assign(errors, { phone: '', name: '', role: '', departmentId: '', password: '' })
}

/**
 * Validate phone number
 * - Must be at least 9 digits
 * - No repeating patterns (e.g., 0504040404, 0555555555)
 * - Allows Ghana numbers (0XX) and international (+XXX)
 */
function validatePhone(phone) {
  const digits = (phone || '').replace(/\D/g, '')
  
  // Must have at least 9 digits
  if (digits.length < 9) {
    return 'Phone number must be at least 9 digits'
  }
  
  // Check for excessive repeating digits (more than 5 same digits in a row)
  if (/(.)\1{5,}/.test(digits)) {
    return 'Invalid phone number - too many repeating digits'
  }
  
  // Check for repeating patterns like 040404 or 123123
  const lastEight = digits.slice(-8)
  // Check for 2-digit repeating pattern (e.g., 04040404)
  if (/^(\d{2})\1{3}$/.test(lastEight)) {
    return 'Invalid phone number - repeating pattern detected'
  }
  // Check for 4-digit repeating pattern (e.g., 12341234)
  if (/^(\d{4})\1$/.test(lastEight)) {
    return 'Invalid phone number - repeating pattern detected'
  }
  
  // Valid Ghana mobile prefixes
  const ghanaPrefixes = ['20', '23', '24', '25', '26', '27', '28', '29', '50', '54', '55', '56', '57', '59']
  
  // Check if it's a Ghana number
  let localNumber = digits
  if (digits.startsWith('233')) {
    localNumber = digits.slice(3)
  } else if (digits.startsWith('0')) {
    localNumber = digits.slice(1)
  }
  
  // If it looks like a Ghana number, validate the prefix
  if (localNumber.length === 9) {
    const prefix = localNumber.slice(0, 2)
    if (!ghanaPrefixes.includes(prefix)) {
      // Allow it as international number if not matching Ghana prefix
      if (!phone.startsWith('+') && !digits.startsWith('00')) {
        return 'Invalid Ghana mobile number prefix'
      }
    }
  }
  
  return null // Valid
}

function validateForm() {
  let valid = true
  clearErrors()

  // Phone validation
  const phoneError = validatePhone(form.phone)
  if (phoneError) {
    errors.phone = phoneError
    valid = false
  }
  
  if (!form.name.trim()) {
    errors.name = 'Name is required'
    valid = false
  }
  if (!form.role) {
    errors.role = 'Role is required'
    valid = false
  }
  if (requiresDepartment.value && !form.departmentId) {
    errors.departmentId = 'Department required for this role'
    valid = false
  }
  if (!isEditing.value && !form.password.trim()) {
    errors.password = 'Password is required'
    valid = false
  }
  return valid
}

function normalizePhone(input) {
  if (!input) return ''
  
  // Remove all non-digit characters except +
  const cleaned = input.replace(/[^\d+]/g, '')
  
  // If already starts with +, it's international - keep as is
  if (cleaned.startsWith('+')) {
    return cleaned
  }
  
  const digits = cleaned.replace(/\D/g, '')
  if (!digits) return ''
  
  // If starts with 00, convert to + (international format)
  if (digits.startsWith('00')) {
    return `+${digits.slice(2)}`
  }
  
  // If starts with 233 (Ghana), add +
  if (digits.startsWith('233')) {
    return `+${digits}`
  }
  
  // If starts with 0, it's a local Ghana number - convert to +233
  if (digits.startsWith('0')) {
    return `+233${digits.slice(1)}`
  }
  
  // Otherwise assume it's Ghana without leading 0
  return `+233${digits}`
}

function stripCountryCode(phone) {
  if (!phone) return ''
  return phone.replace(/^\+?233/, '')
}

function getDepartmentIdByName(name) {
  if (!name) return ''
  const dept = departments.value.find((d) => d.name === name)
  return dept?.id || ''
}

function departmentNameById(id) {
  if (!id) return null
  return departments.value.find((d) => d.id === id)?.name || null
}

function mapUser(user) {
  return {
    id: user.id,
    name: user.name,
    phone: user.phone,
    email: user.email,
    role: user.role,
    departmentName: user.department ?? user.department_name ?? null,
    status: user.is_active === false ? 'inactive' : 'active',
    lastLogin: user.last_login ?? user.lastLogin ?? null,
    avatar: user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}`
  }
}

async function saveUser() {
  if (!validateForm()) return
  saving.value = true

  const basePayload = {
    phone: normalizePhone(form.phone),
    name: form.name.trim(),
    email: form.email || null,
    role: form.role,
    department_id: form.departmentId || null
  }

  try {
    if (isEditing.value) {
      const response = await usersApi.update(form.id, basePayload)
      updateUserInList(form.id, {
        name: basePayload.name,
        email: basePayload.email,
        role: basePayload.role,
        departmentName: departmentNameById(form.departmentId) ||
          users.value.find((u) => u.id === form.id)?.departmentName
      })
      showNotification('success', response.data?.message || 'User updated successfully')
    } else {
      const response = await usersApi.create({ ...basePayload, password: form.password })
      const createdUser = mapUser({
        ...response.data?.data,
        department: departmentNameById(form.departmentId)
      })
      users.value = [createdUser, ...users.value]
      totalUsers.value += 1
      showNotification('success', response.data?.message || 'User created successfully')
    }
    closeModal()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to save user'
    showNotification('danger', message)
  } finally {
    saving.value = false
  }
}

function updateUserInList(id, payload) {
  const index = users.value.findIndex((u) => u.id === id)
  if (index !== -1) {
    users.value[index] = { ...users.value[index], ...payload }
  }
}

function confirmDeactivate(user) {
  userToDeactivate.value = user
  deactivateReason.value = ''
  showDeactivateModal.value = true
}

async function deactivateUser() {
  if (!userToDeactivate.value) return
  try {
    await usersApi.deactivate(userToDeactivate.value.id, { reason: deactivateReason.value || undefined })
    updateUserInList(userToDeactivate.value.id, { status: 'inactive' })
    showNotification('info', `${userToDeactivate.value.name} has been deactivated`)
    showDeactivateModal.value = false
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to deactivate user'
    showNotification('danger', message)
  }
}

async function reactivateUser(user) {
  try {
    await usersApi.reactivate(user.id)
    updateUserInList(user.id, { status: 'active' })
    showNotification('success', `${user.name} has been reactivated`)
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to reactivate user'
    showNotification('danger', message)
  }
}

function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}

function roleColor(role) {
  return roleOptions.value.find(r => r.value === role)?.color || 'secondary'
}
function roleLabel(role) {
  return roleOptions.value.find(r => r.value === role)?.label || role
}

function exportUsers() {
  const columns = [
    { key: 'name', header: 'Name' },
    { key: 'phone', header: 'Phone' },
    { key: 'email', header: 'Email' },
    { key: 'role', header: 'Role', transform: (v) => roleLabel(v) },
    { key: 'departmentName', header: 'Department' },
    { key: 'status', header: 'Status', transform: (v) => v ? v.charAt(0).toUpperCase() + v.slice(1) : v },
    { key: 'lastLogin', header: 'Last Login' }
  ]
  exportToExcel(filteredUsers.value, columns, `Users_Report_${new Date().toISOString().split('T')[0]}`)
  showNotification('success', 'Users exported successfully')
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .page-header>div:last-child {
    margin-top: 12px;
    width: 100%;
  }
}
</style>
