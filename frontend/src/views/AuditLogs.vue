<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Audit Logs</h2>
        <Breadcrumbs />
        <div class="text-muted">Track all system activities and changes</div>
      </div>
      <CButton color="light" @click="exportLogs">
        <i class="bi bi-download me-1"></i> Export Logs
      </CButton>
    </div>

    <!-- Filters -->
    <CCard class="mb-4">
      <CCardBody>
        <CRow class="g-3 align-items-end">
          <CCol md="3">
            <CFormLabel>Date Range</CFormLabel>
            <div class="d-flex gap-2">
              <CFormInput v-model="filters.dateFrom" type="date" />
              <CFormInput v-model="filters.dateTo" type="date" />
            </div>
          </CCol>
          <CCol md="2">
            <CFormLabel>User</CFormLabel>
            <CFormSelect v-model="filters.userId">
              <option value="">All Users</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </CFormSelect>
          </CCol>
          <CCol md="2">
            <CFormLabel>Action Type</CFormLabel>
            <CFormSelect v-model="filters.action">
              <option value="">All Actions</option>
              <option value="create">Create</option>
              <option value="update">Update</option>
              <option value="delete">Delete</option>
              <option value="login">Login</option>
              <option value="logout">Logout</option>
              <option value="approve">Approve</option>
              <option value="reject">Reject</option>
            </CFormSelect>
          </CCol>
          <CCol md="2">
            <CFormLabel>Module</CFormLabel>
            <CFormSelect v-model="filters.module">
              <option value="">All Modules</option>
              <option value="users">Users</option>
              <option value="members">Members</option>
              <option value="attendance">Attendance</option>
              <option value="contributions">Contributions</option>
              <option value="expenses">Expenses</option>
              <option value="visitors">Visitors</option>
              <option value="broadcasts">Broadcasts</option>
            </CFormSelect>
          </CCol>
          <CCol md="3">
            <CFormLabel>Search</CFormLabel>
            <CFormInput v-model="filters.search" placeholder="Search logs..." />
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>

    <!-- Summary Stats -->
    <CRow class="g-4 mb-4">
      <CCol sm="6" xl="3">
        <CCard class="border-0 shadow-sm h-100">
          <CCardBody class="d-flex justify-content-between">
            <div>
              <div class="text-muted small">Total Events</div>
              <div class="fs-3 fw-bold">{{ filteredLogs.length }}</div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
              <i class="bi bi-journal-text"></i>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="border-0 shadow-sm h-100 cursor-pointer" @click="filters.action = 'create'">
          <CCardBody class="d-flex justify-content-between">
            <div>
              <div class="text-muted small">Creates</div>
              <div class="fs-3 fw-bold text-success">{{ actionCounts.create }}</div>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
              <i class="bi bi-plus-circle"></i>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="border-0 shadow-sm h-100 cursor-pointer" @click="filters.action = 'update'">
          <CCardBody class="d-flex justify-content-between">
            <div>
              <div class="text-muted small">Updates</div>
              <div class="fs-3 fw-bold text-info">{{ actionCounts.update }}</div>
            </div>
            <div class="stat-icon bg-info-subtle text-info">
              <i class="bi bi-pencil"></i>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="border-0 shadow-sm h-100 cursor-pointer" @click="filters.action = 'delete'">
          <CCardBody class="d-flex justify-content-between">
            <div>
              <div class="text-muted small">Deletes</div>
              <div class="fs-3 fw-bold text-danger">{{ actionCounts.delete }}</div>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
              <i class="bi bi-trash"></i>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Logs Table -->
    <CCard>
      <CCardBody class="p-0">
        <CTable hover responsive align="middle">
          <CTableHead color="light">
            <CTableRow>
              <CTableHeaderCell style="width: 160px">Timestamp</CTableHeaderCell>
              <CTableHeaderCell>User</CTableHeaderCell>
              <CTableHeaderCell style="width: 100px">Action</CTableHeaderCell>
              <CTableHeaderCell>Module</CTableHeaderCell>
              <CTableHeaderCell>Description</CTableHeaderCell>
              <CTableHeaderCell>IP Address</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Details</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="log in paginatedLogs" :key="log.id">
              <CTableDataCell>
                <div class="fw-semibold">{{ formatDate(log.createdAt) }}</div>
                <div class="text-muted small">{{ formatTime(log.createdAt) }}</div>
              </CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <CAvatar :color="roleColor(log.userRole)" text-color="white" size="sm" class="me-2">
                    {{ log.userName.charAt(0) }}
                  </CAvatar>
                  <div>
                    <div class="fw-semibold">{{ log.userName }}</div>
                    <div class="text-muted small">{{ log.userRole }}</div>
                  </div>
                </div>
              </CTableDataCell>
              <CTableDataCell>
                <CBadge :color="actionColor(log.action)">{{ log.action }}</CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <CBadge color="light" text-color="dark">
                  <i :class="moduleIcon(log.module)" class="me-1"></i>
                  {{ log.module }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <div class="text-truncate" style="max-width: 300px" :title="log.description">
                  {{ log.description }}
                </div>
              </CTableDataCell>
              <CTableDataCell>
                <span class="text-muted small font-monospace">{{ log.ipAddress }}</span>
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="light" size="sm" @click="viewLogDetails(log)">
                  <i class="bi bi-eye"></i>
                </CButton>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
        <div v-if="!filteredLogs.length" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>
          No audit logs found
        </div>
      </CCardBody>
      <CCardFooter v-if="totalPages > 1" class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Showing {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, filteredLogs.length) }} of
          {{ filteredLogs.length }}
        </div>
        <CPagination>
          <CPaginationItem :disabled="currentPage === 1" @click="currentPage--">Previous</CPaginationItem>
          <CPaginationItem v-for="p in visiblePages" :key="p" :active="p === currentPage" @click="currentPage = p">
            {{ p }}
          </CPaginationItem>
          <CPaginationItem :disabled="currentPage === totalPages" @click="currentPage++">Next</CPaginationItem>
        </CPagination>
      </CCardFooter>
    </CCard>

    <!-- Details Modal -->
    <CModal :visible="showDetailsModal" @close="showDetailsModal = false" size="lg">
      <CModalHeader>
        <CModalTitle>Audit Log Details</CModalTitle>
      </CModalHeader>
      <CModalBody v-if="selectedLog">
        <CRow class="g-3 mb-4">
          <CCol md="6">
            <div class="text-muted small">Timestamp</div>
            <div class="fw-semibold">{{ formatDateTime(selectedLog.createdAt) }}</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">User</div>
            <div class="fw-semibold">{{ selectedLog.userName }} ({{ selectedLog.userRole }})</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Action</div>
            <CBadge :color="actionColor(selectedLog.action)">{{ selectedLog.action }}</CBadge>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Module</div>
            <div>{{ selectedLog.module }}</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">IP Address</div>
            <div class="font-monospace">{{ selectedLog.ipAddress }}</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">User Agent</div>
            <div class="small text-truncate" :title="selectedLog.userAgent">{{ selectedLog.userAgent }}</div>
          </CCol>
          <CCol md="12">
            <div class="text-muted small">Description</div>
            <div>{{ selectedLog.description }}</div>
          </CCol>
        </CRow>

        <!-- Changes Detail -->
        <div v-if="selectedLog.changes">
          <div class="fw-semibold mb-2">Changes</div>
          <div class="bg-light rounded p-3">
            <CRow v-for="(value, key) in selectedLog.changes" :key="key" class="mb-2">
              <CCol xs="4" class="text-muted">{{ key }}</CCol>
              <CCol xs="4">
                <del v-if="value.old" class="text-danger">{{ value.old }}</del>
                <span v-else class="text-muted">—</span>
              </CCol>
              <CCol xs="4">
                <span class="text-success fw-semibold">{{ value.new }}</span>
              </CCol>
            </CRow>
          </div>
        </div>

        <!-- Metadata -->
        <div v-if="selectedLog.metadata" class="mt-3">
          <div class="fw-semibold mb-2">Additional Data</div>
          <pre class="bg-light p-3 rounded small">{{ JSON.stringify(selectedLog.metadata, null, 2) }}</pre>
        </div>
      </CModalBody>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue'
import {
  CCard, CCardBody, CCardFooter, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CModal, CModalHeader, CModalTitle, CModalBody, CPagination, CPaginationItem
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel, formatDateForExport } from '../utils/export.js'

// Users for filter
const users = ref([
  { id: 1, name: 'Admin User' },
  { id: 2, name: 'Pastor John' },
  { id: 3, name: 'Finance User' },
  { id: 4, name: 'Usher Mary' }
])

// Audit logs data
const logs = ref([
  { id: 1, createdAt: '2026-01-26T14:32:00', userId: 1, userName: 'Admin User', userRole: 'admin', action: 'approve', module: 'attendance', description: 'Approved attendance for Sunday First Service', ipAddress: '192.168.1.100', userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0' },
  { id: 2, createdAt: '2026-01-26T14:15:00', userId: 3, userName: 'Finance User', userRole: 'finance', action: 'create', module: 'contributions', description: 'Recorded tithe contribution of GH₵ 500 from Kwame Asante', ipAddress: '192.168.1.105', userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Safari/605.1.15', metadata: { amount: 500, type: 'tithe', memberId: 'M001' } },
  { id: 3, createdAt: '2026-01-26T12:45:00', userId: 1, userName: 'Admin User', userRole: 'admin', action: 'update', module: 'users', description: 'Updated role for Mary Owusu from usher to finance', ipAddress: '192.168.1.100', userAgent: 'Mozilla/5.0 Chrome/120.0.0.0', changes: { role: { old: 'usher', new: 'finance' } } },
  { id: 4, createdAt: '2026-01-26T10:30:00', userId: 4, userName: 'Usher Mary', userRole: 'usher', action: 'create', module: 'attendance', description: 'Submitted attendance: 120 adults, 35 children, 8 visitors', ipAddress: '192.168.1.110', userAgent: 'Mozilla/5.0 Android/10 Mobile' },
  { id: 5, createdAt: '2026-01-26T09:00:00', userId: 2, userName: 'Pastor John', userRole: 'pastor', action: 'login', module: 'auth', description: 'User logged in successfully', ipAddress: '41.189.45.67', userAgent: 'Mozilla/5.0 Windows NT 10.0 Firefox/120.0' },
  { id: 6, createdAt: '2026-01-25T18:30:00', userId: 1, userName: 'Admin User', userRole: 'admin', action: 'create', module: 'broadcasts', description: 'Sent WhatsApp broadcast to 156 members', ipAddress: '192.168.1.100', userAgent: 'Mozilla/5.0 Chrome/120.0.0.0', metadata: { channel: 'whatsapp', recipientCount: 156 } },
  { id: 7, createdAt: '2026-01-25T16:00:00', userId: 3, userName: 'Finance User', userRole: 'finance', action: 'create', module: 'expenses', description: 'Recorded expense: Electricity bill - GH₵ 450', ipAddress: '192.168.1.105', userAgent: 'Mozilla/5.0 Safari/605.1.15' },
  { id: 8, createdAt: '2026-01-25T14:20:00', userId: 1, userName: 'Admin User', userRole: 'admin', action: 'reject', module: 'attendance', description: 'Rejected attendance submission: Numbers mismatch', ipAddress: '192.168.1.100', userAgent: 'Mozilla/5.0 Chrome/120.0.0.0' },
  { id: 9, createdAt: '2026-01-25T11:00:00', userId: 1, userName: 'Admin User', userRole: 'admin', action: 'delete', module: 'members', description: 'Deleted member: John Doe (Duplicate entry)', ipAddress: '192.168.1.100', userAgent: 'Mozilla/5.0 Chrome/120.0.0.0' },
  { id: 10, createdAt: '2026-01-24T09:15:00', userId: 2, userName: 'Pastor John', userRole: 'pastor', action: 'logout', module: 'auth', description: 'User logged out', ipAddress: '41.189.45.67', userAgent: 'Mozilla/5.0 Firefox/120.0' }
])

// Filters
const filters = reactive({
  dateFrom: '',
  dateTo: '',
  userId: '',
  action: '',
  module: '',
  search: ''
})

// Pagination
const currentPage = ref(1)
const perPage = 15

// Modal
const showDetailsModal = ref(false)
const selectedLog = ref(null)

// Computed
const filteredLogs = computed(() => {
  return logs.value.filter(log => {
    if (filters.dateFrom && log.createdAt.split('T')[0] < filters.dateFrom) return false
    if (filters.dateTo && log.createdAt.split('T')[0] > filters.dateTo) return false
    if (filters.userId && log.userId !== parseInt(filters.userId)) return false
    if (filters.action && log.action !== filters.action) return false
    if (filters.module && log.module !== filters.module) return false
    if (filters.search) {
      const s = filters.search.toLowerCase()
      if (!log.description.toLowerCase().includes(s) &&
        !log.userName.toLowerCase().includes(s) &&
        !log.module.toLowerCase().includes(s)) return false
    }
    return true
  }).sort((a, b) => b.createdAt.localeCompare(a.createdAt))
})

const totalPages = computed(() => Math.ceil(filteredLogs.value.length / perPage))

const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredLogs.value.slice(start, start + perPage)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const actionCounts = computed(() => ({
  create: logs.value.filter(l => l.action === 'create').length,
  update: logs.value.filter(l => l.action === 'update').length,
  delete: logs.value.filter(l => l.action === 'delete').length
}))

// Reset page when filters change
watch(filters, () => { currentPage.value = 1 })

// Methods
function viewLogDetails(log) {
  selectedLog.value = log
  showDetailsModal.value = true
}

function exportLogs() {
  const columns = [
    { key: 'createdAt', header: 'Timestamp', transform: (v) => formatDateForExport(v) },
    { key: 'userName', header: 'User' },
    { key: 'userRole', header: 'Role' },
    { key: 'action', header: 'Action', transform: (v) => v.charAt(0).toUpperCase() + v.slice(1) },
    { key: 'module', header: 'Module', transform: (v) => v.charAt(0).toUpperCase() + v.slice(1) },
    { key: 'description', header: 'Description' },
    { key: 'ipAddress', header: 'IP Address' }
  ]
  exportToExcel(filteredLogs.value, columns, `Audit_Logs_${new Date().toISOString().split('T')[0]}`)
}

// Helpers
function formatDate(dt) {
  return new Date(dt).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(dt) {
  return new Date(dt).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

function formatDateTime(dt) {
  return new Date(dt).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

function actionColor(action) {
  const colors = { create: 'success', update: 'info', delete: 'danger', login: 'primary', logout: 'secondary', approve: 'success', reject: 'danger' }
  return colors[action] || 'secondary'
}

function roleColor(role) {
  const colors = { admin: 'danger', pastor: 'primary', finance: 'success', usher: 'info', pr_follow_up: 'warning', department_leader: 'purple' }
  return colors[role] || 'secondary'
}

function moduleIcon(module) {
  const icons = { users: 'bi bi-people', members: 'bi bi-person', attendance: 'bi bi-clipboard-check', contributions: 'bi bi-cash-coin', expenses: 'bi bi-receipt', visitors: 'bi bi-person-plus', broadcasts: 'bi bi-broadcast', auth: 'bi bi-shield-lock' }
  return icons[module] || 'bi bi-circle'
}
</script>

<style scoped>
.page-wrap {
  padding: 1.5rem;
  max-width: 100%;
  overflow-x: hidden;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header .title {
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.cursor-pointer {
  cursor: pointer;
  transition: all 0.2s ease;
}

.cursor-pointer:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .page-wrap {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 1rem;
  }

  .page-header .title {
    font-size: 1.5rem;
  }

  .page-header > div:last-child {
    width: 100%;
  }

  .page-header .btn {
    width: 100%;
  }

  /* Filters responsive */
  :deep(.card-body .row) {
    gap: 0.75rem;
  }

  /* Stats cards */
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }

  /* Table adjustments */
  :deep(.table) {
    font-size: 0.8125rem;
  }

  :deep(.table th),
  :deep(.table td) {
    padding: 0.625rem 0.5rem !important;
  }

  :deep(.table .text-truncate) {
    max-width: 150px !important;
  }
}

@media (max-width: 576px) {
  .page-wrap {
    padding: 0.875rem;
  }

  .page-header .title {
    font-size: 1.375rem;
  }

  /* Hide some columns on very small screens */
  :deep(.table th:nth-child(5)),
  :deep(.table td:nth-child(5)),
  :deep(.table th:nth-child(6)),
  :deep(.table td:nth-child(6)) {
    display: none;
  }

  /* Filter layout */
  :deep(.card-body .col-md-2),
  :deep(.card-body .col-md-3) {
    flex: 0 0 100%;
    max-width: 100%;
  }
}

/* Card styling */
:deep(.card) {
  border: none;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
}

:deep(.card:hover) {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06), 0 12px 24px rgba(0, 0, 0, 0.06);
}

:deep(.card-body) {
  padding: 1.25rem;
}

/* Table styling */
:deep(.table) {
  margin: 0;
}

:deep(.table thead th) {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  border: none;
  padding: 1rem !important;
}

.theme-dark :deep(.table thead th) {
  background: rgba(15, 23, 42, 0.8);
  color: #94a3b8;
}

:deep(.table tbody tr) {
  transition: background 0.2s ease;
}

:deep(.table tbody tr:hover) {
  background: rgba(99, 102, 241, 0.03);
}

:deep(.table tbody td) {
  padding: 1rem !important;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
}

.theme-dark :deep(.table tbody td) {
  border-color: #334155;
}

/* Form controls */
:deep(.form-control),
:deep(.form-select) {
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  padding: 0.625rem 1rem;
  transition: all 0.2s ease;
}

:deep(.form-control:focus),
:deep(.form-select:focus) {
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

:deep(.form-label) {
  font-size: 0.8125rem;
  font-weight: 500;
  color: #64748b;
  margin-bottom: 0.375rem;
}

/* Button styling */
:deep(.btn-light) {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  border-radius: 10px;
  transition: all 0.2s ease;
}

:deep(.btn-light:hover) {
  background: #e2e8f0;
  transform: translateY(-1px);
}

/* Pagination */
:deep(.pagination) {
  gap: 0.25rem;
}

:deep(.page-item .page-link) {
  border-radius: 8px;
  border: none;
  padding: 0.5rem 0.875rem;
  font-size: 0.875rem;
  color: #475569;
}

:deep(.page-item.active .page-link) {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
}

/* Badge styling */
:deep(.badge) {
  font-weight: 500;
  border-radius: 6px;
  padding: 0.375em 0.625em;
}

/* Modal styling */
:deep(.modal-content) {
  border: none;
  border-radius: 16px;
}

:deep(.modal-header) {
  border-bottom: 1px solid #e2e8f0;
  padding: 1.25rem 1.5rem;
}

:deep(.modal-body) {
  padding: 1.5rem;
}

.theme-dark :deep(.modal-content) {
  background: #1e293b;
}

.theme-dark :deep(.modal-header) {
  border-color: #334155;
}
</style>
