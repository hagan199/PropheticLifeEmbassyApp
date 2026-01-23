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
        <CBadge color="primary">{{ filteredUsers.length }} users</CBadge>
      </CCardHeader>
      <CCardBody>
        <CTable hover responsive align="middle">
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
              <CTableDataCell>{{ user.department || '—' }}</CTableDataCell>
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

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">
            Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredUsers.length) }}
            of {{ filteredUsers.length }}
          </div>
          <CPagination>
            <CPaginationItem :disabled="currentPage === 1" @click="currentPage--">Previous</CPaginationItem>
            <CPaginationItem v-for="p in totalPages" :key="p" :active="p === currentPage" @click="currentPage = p">
              {{ p }}
            </CPaginationItem>
            <CPaginationItem :disabled="currentPage === totalPages" @click="currentPage++">Next</CPaginationItem>
          </CPagination>
        </div>
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
              <CFormSelect v-model="form.department" :invalid="!!errors.department">
                <option value="">Select department...</option>
                <option v-for="d in departments" :key="d.id" :value="d.name">{{ d.name }}</option>
              </CFormSelect>
              <div v-if="errors.department" class="text-danger small mt-1">{{ errors.department }}</div>
            </CCol>
            <CCol md="6">
              <CFormLabel>2FA Enabled</CFormLabel>
              <CFormSwitch v-model="form.has2fa" label="Require two-factor authentication" />
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
import { ref, computed, reactive } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CFormTextarea, CFormSwitch, CInputGroup, CInputGroupText, CModal, CModalHeader,
  CModalTitle, CModalBody, CModalFooter, CForm, CAlert, CPagination, CPaginationItem, CSpinner
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'

// Data
const roleOptions = [
  { value: 'admin', label: 'Admin', color: 'danger' },
  { value: 'pastor', label: 'Pastor', color: 'primary' },
  { value: 'usher', label: 'Usher', color: 'info' },
  { value: 'finance', label: 'Finance Officer', color: 'success' },
  { value: 'pr_follow_up', label: 'PR / Follow-up', color: 'warning' },
  { value: 'department_leader', label: 'Department Leader', color: 'secondary' }
]

const departments = ref([
  { id: 1, name: 'Media' },
  { id: 2, name: 'Prayer Team' },
  { id: 3, name: 'Ushering' },
  { id: 4, name: 'Choir' },
  { id: 5, name: 'Welfare' }
])

const users = ref([
  { id: 1, name: 'Admin User', phone: '+233241234567', email: 'admin@ple.org', role: 'admin', department: null, status: 'active', lastLogin: '2h ago', avatar: 'https://i.pravatar.cc/40?img=1' },
  { id: 2, name: 'Pastor Tower', phone: '+233201234567', email: 'pastor@ple.org', role: 'pastor', department: null, status: 'active', lastLogin: '1d ago', avatar: 'https://i.pravatar.cc/40?img=2' },
  { id: 3, name: 'Kofi Mensah', phone: '+233551234567', email: 'kofi@ple.org', role: 'usher', department: 'Ushering', status: 'active', lastLogin: '3h ago', avatar: 'https://i.pravatar.cc/40?img=3' },
  { id: 4, name: 'Ama Serwaa', phone: '+233271234567', email: 'ama@ple.org', role: 'finance', department: null, status: 'active', lastLogin: '5h ago', avatar: 'https://i.pravatar.cc/40?img=4' },
  { id: 5, name: 'Yaw Boateng', phone: '+233501234567', email: 'yaw@ple.org', role: 'pr_follow_up', department: null, status: 'active', lastLogin: '1h ago', avatar: 'https://i.pravatar.cc/40?img=5' },
  { id: 6, name: 'Akosua Darko', phone: '+233261234567', email: 'akosua@ple.org', role: 'department_leader', department: 'Media', status: 'inactive', lastLogin: '1w ago', avatar: 'https://i.pravatar.cc/40?img=6' }
])

// Filters
const filters = reactive({ search: '', role: '', status: '' })
const currentPage = ref(1)
const perPage = 25

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    if (filters.search && !u.name.toLowerCase().includes(filters.search.toLowerCase()) &&
      !u.phone.includes(filters.search)) return false
    if (filters.role && u.role !== filters.role) return false
    if (filters.status && u.status !== filters.status) return false
    return true
  })
})

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage))
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

function applyFilters() { currentPage.value = 1 }
function resetFilters() {
  filters.search = ''
  filters.role = ''
  filters.status = ''
  currentPage.value = 1
}

// Modal
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const form = reactive({ id: null, phone: '', name: '', email: '', role: '', department: '', has2fa: false })
const errors = reactive({ phone: '', name: '', role: '', department: '' })

const requiresDepartment = computed(() => ['usher', 'department_leader'].includes(form.role))

function openAddModal() {
  isEditing.value = false
  Object.assign(form, { id: null, phone: '', name: '', email: '', role: '', department: '', has2fa: false })
  Object.assign(errors, { phone: '', name: '', role: '', department: '' })
  showModal.value = true
}

function editUser(user) {
  isEditing.value = true
  Object.assign(form, {
    id: user.id,
    phone: user.phone.replace('+233', ''),
    name: user.name,
    email: user.email,
    role: user.role,
    department: user.department || '',
    has2fa: user.has2fa || false
  })
  Object.assign(errors, { phone: '', name: '', role: '', department: '' })
  showModal.value = true
}

function closeModal() { showModal.value = false }

function validateForm() {
  let valid = true
  Object.assign(errors, { phone: '', name: '', role: '', department: '' })

  if (!form.phone.trim() || form.phone.length < 9) {
    errors.phone = 'Valid phone number required'
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
  if (requiresDepartment.value && !form.department) {
    errors.department = 'Department required for this role'
    valid = false
  }
  return valid
}

function saveUser() {
  if (!validateForm()) return
  saving.value = true

  setTimeout(() => {
    if (isEditing.value) {
      const idx = users.value.findIndex(u => u.id === form.id)
      if (idx !== -1) {
        users.value[idx] = {
          ...users.value[idx],
          name: form.name,
          email: form.email,
          role: form.role,
          department: form.department || null,
          has2fa: form.has2fa
        }
      }
      showNotification('success', 'User updated successfully')
    } else {
      const newId = Math.max(...users.value.map(u => u.id)) + 1
      users.value.push({
        id: newId,
        name: form.name,
        phone: '+233' + form.phone,
        email: form.email,
        role: form.role,
        department: form.department || null,
        status: 'active',
        lastLogin: null,
        has2fa: form.has2fa,
        avatar: `https://i.pravatar.cc/40?img=${newId + 10}`
      })
      showNotification('success', 'User created successfully')
    }
    saving.value = false
    closeModal()
  }, 500)
}

// Deactivate
const showDeactivateModal = ref(false)
const userToDeactivate = ref(null)
const deactivateReason = ref('')

function confirmDeactivate(user) {
  userToDeactivate.value = user
  deactivateReason.value = ''
  showDeactivateModal.value = true
}

function deactivateUser() {
  const user = users.value.find(u => u.id === userToDeactivate.value.id)
  if (user) user.status = 'inactive'
  showDeactivateModal.value = false
  showNotification('info', `${userToDeactivate.value.name} has been deactivated`)
}

function reactivateUser(user) {
  user.status = 'active'
  showNotification('success', `${user.name} has been reactivated`)
}

// Notification
const notification = reactive({ show: false, type: 'success', message: '' })
function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}

// Helpers
function roleColor(role) {
  return roleOptions.find(r => r.value === role)?.color || 'secondary'
}
function roleLabel(role) {
  return roleOptions.find(r => r.value === role)?.label || role
}

// Export Users
function exportUsers() {
  const columns = [
    { key: 'name', header: 'Name' },
    { key: 'phone', header: 'Phone' },
    { key: 'email', header: 'Email' },
    { key: 'role', header: 'Role', transform: (v) => roleLabel(v) },
    { key: 'department', header: 'Department' },
    { key: 'status', header: 'Status', transform: (v) => v?.charAt(0).toUpperCase() + v?.slice(1) },
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
