<template>
  <div class="page-wrap">
    <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false">
      {{ notification.message }}
    </CAlert>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Attendance Approvals</h2>
        <Breadcrumbs />
        <div class="text-muted">Review and approve attendance submissions</div>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" @click="exportApprovals">
          <i class="bi bi-file-earmark-excel me-1"></i> Export
        </CButton>
        <CButton color="success" :disabled="!selectedIds.length" @click="bulkApprove">
          <i class="bi bi-check-all me-1"></i> Approve Selected ({{ selectedIds.length }})
        </CButton>
      </div>
    </div>

    <!-- Stats Cards -->
    <CRow class="g-4 mb-4">
      <CCol sm="6" xl="3">
        <CCard class="border-start border-start-4 border-start-warning">
          <CCardBody>
            <div class="text-muted small text-uppercase fw-semibold">Pending</div>
            <div class="fs-4 fw-bold text-warning">{{ pendingCount }}</div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="border-start border-start-4 border-start-success">
          <CCardBody>
            <div class="text-muted small text-uppercase fw-semibold">Approved Today</div>
            <div class="fs-4 fw-bold text-success">{{ approvedTodayCount }}</div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="border-start border-start-4 border-start-danger">
          <CCardBody>
            <div class="text-muted small text-uppercase fw-semibold">Rejected</div>
            <div class="fs-4 fw-bold text-danger">{{ rejectedCount }}</div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="border-start border-start-4 border-start-primary">
          <CCardBody>
            <div class="text-muted small text-uppercase fw-semibold">Total This Week</div>
            <div class="fs-4 fw-bold text-primary">{{ totalWeekCount }}</div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Filters -->
    <CCard class="mb-4">
      <CCardBody>
        <CRow class="g-3 align-items-end">
          <CCol md="3">
            <CFormLabel>Date Range</CFormLabel>
            <CRow class="g-2">
              <CCol>
                <CFormInput type="date" v-model="filters.dateFrom" />
              </CCol>
              <CCol>
                <CFormInput type="date" v-model="filters.dateTo" />
              </CCol>
            </CRow>
          </CCol>
          <CCol md="2">
            <CFormLabel>Service</CFormLabel>
            <CFormSelect v-model="filters.service">
              <option value="">All Services</option>
              <option value="sunday">Sunday</option>
              <option value="friday">Friday Night</option>
              <option value="midweek">Midweek</option>
            </CFormSelect>
          </CCol>
          <CCol md="2">
            <CFormLabel>Status</CFormLabel>
            <CFormSelect v-model="filters.status">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="">All</option>
            </CFormSelect>
          </CCol>
          <CCol md="3">
            <CFormLabel>Submitted By</CFormLabel>
            <CFormInput v-model="filters.submittedBy" placeholder="Search usher..." />
          </CCol>
          <CCol md="2">
            <CButton color="light" class="w-100" @click="resetFilters">Reset</CButton>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>

    <!-- Attendance Table -->
    <CCard>
      <CCardHeader class="d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Attendance Records</div>
        <div class="d-flex align-items-center gap-2">
          <CFormCheck :checked="allSelected" @change="toggleSelectAll" label="Select All" />
        </div>
      </CCardHeader>
      <CCardBody>
        <CTable hover responsive align="middle">
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell style="width: 40px">
                <CFormCheck :checked="allSelected" @change="toggleSelectAll" />
              </CTableHeaderCell>
              <CTableHeaderCell>Service</CTableHeaderCell>
              <CTableHeaderCell>Date</CTableHeaderCell>
              <CTableHeaderCell class="text-center">Count</CTableHeaderCell>
              <CTableHeaderCell>Submitted By</CTableHeaderCell>
              <CTableHeaderCell>Submitted At</CTableHeaderCell>
              <CTableHeaderCell>Status</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="record in filteredRecords" :key="record.id"
              :class="{ 'table-warning': record.status === 'pending' }">
              <CTableDataCell>
                <CFormCheck :checked="selectedIds.includes(record.id)" :disabled="record.status !== 'pending'"
                  @change="toggleSelect(record.id)" />
              </CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <i :class="serviceIcon(record.serviceType)" class="me-2 text-muted"></i>
                  {{ serviceLabel(record.serviceType) }}
                </div>
              </CTableDataCell>
              <CTableDataCell>
                <div class="fw-semibold">{{ formatDate(record.date) }}</div>
                <div class="text-muted small">{{ relativeDays(record.date) }}</div>
              </CTableDataCell>
              <CTableDataCell class="text-center">
                <span class="fs-5 fw-bold">{{ record.count }}</span>
              </CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <CAvatar :src="record.submittedBy.avatar" size="sm" class="me-2" />
                  {{ record.submittedBy.name }}
                </div>
              </CTableDataCell>
              <CTableDataCell class="text-muted">{{ record.submittedAt }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="statusColor(record.status)">
                  <i :class="statusIcon(record.status)" class="me-1"></i>
                  {{ record.status }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <template v-if="record.status === 'pending'">
                  <CButton color="success" size="sm" class="me-1" @click="approveRecord(record)">
                    <i class="bi bi-check-lg"></i> Approve
                  </CButton>
                  <CButton color="danger" size="sm" variant="ghost" @click="openRejectModal(record)">
                    <i class="bi bi-x-lg"></i>
                  </CButton>
                </template>
                <template v-else>
                  <CButton color="light" size="sm" @click="viewDetails(record)">
                    <i class="bi bi-eye"></i>
                  </CButton>
                </template>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>

        <div v-if="!filteredRecords.length" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>
          No attendance records found
        </div>
      </CCardBody>
    </CCard>

    <!-- Reject Modal -->
    <CModal :visible="showRejectModal" @close="showRejectModal = false">
      <CModalHeader>
        <CModalTitle>Reject Attendance</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <p>Rejecting attendance for <strong>{{ rejectingRecord?.serviceType }}</strong> on
          <strong>{{ formatDate(rejectingRecord?.date) }}</strong>
        </p>
        <CAlert color="info" class="mb-3">
          <i class="bi bi-info-circle me-2"></i>
          The usher will be notified via SMS and can resubmit.
        </CAlert>
        <CFormLabel>Reason <span class="text-danger">*</span></CFormLabel>
        <CFormTextarea v-model="rejectReason" rows="3" placeholder="Provide a reason for rejection..."
          maxlength="255" />
        <div class="text-muted small mt-1">{{ rejectReason.length }}/255 characters</div>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showRejectModal = false">Cancel</CButton>
        <CButton color="danger" :disabled="!rejectReason.trim()" @click="rejectRecord">
          Reject
        </CButton>
      </CModalFooter>
    </CModal>

    <!-- Bulk Approve Confirmation -->
    <CModal :visible="showBulkApproveModal" @close="showBulkApproveModal = false">
      <CModalHeader>
        <CModalTitle>Confirm Bulk Approval</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <p>Approve <strong>{{ selectedIds.length }}</strong> attendance records?</p>
        <ul class="list-unstyled">
          <li v-for="id in selectedIds" :key="id" class="mb-1">
            <i class="bi bi-check-circle text-success me-2"></i>
            {{ getRecordSummary(id) }}
          </li>
        </ul>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showBulkApproveModal = false">Cancel</CButton>
        <CButton color="success" @click="confirmBulkApprove">
          Approve All
        </CButton>
      </CModalFooter>
    </CModal>

    <!-- Details Modal -->
    <CModal :visible="showDetailsModal" @close="showDetailsModal = false" size="lg">
      <CModalHeader>
        <CModalTitle>Attendance Details</CModalTitle>
      </CModalHeader>
      <CModalBody v-if="viewingRecord">
        <CRow class="g-3">
          <CCol md="6">
            <div class="text-muted small">Service</div>
            <div class="fw-semibold">{{ serviceLabel(viewingRecord.serviceType) }}</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Date</div>
            <div class="fw-semibold">{{ formatDate(viewingRecord.date) }}</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Count</div>
            <div class="fw-semibold fs-4">{{ viewingRecord.count }}</div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Status</div>
            <CBadge :color="statusColor(viewingRecord.status)" class="fs-6">
              {{ viewingRecord.status }}
            </CBadge>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Submitted By</div>
            <div class="d-flex align-items-center">
              <CAvatar :src="viewingRecord.submittedBy.avatar" size="sm" class="me-2" />
              {{ viewingRecord.submittedBy.name }}
            </div>
          </CCol>
          <CCol md="6">
            <div class="text-muted small">Submitted At</div>
            <div>{{ viewingRecord.submittedAt }}</div>
          </CCol>
          <CCol v-if="viewingRecord.approvedBy" md="6">
            <div class="text-muted small">Approved By</div>
            <div>{{ viewingRecord.approvedBy }}</div>
          </CCol>
          <CCol v-if="viewingRecord.rejectionReason" md="12">
            <div class="text-muted small">Rejection Reason</div>
            <CAlert color="danger">{{ viewingRecord.rejectionReason }}</CAlert>
          </CCol>
          <CCol v-if="viewingRecord.notes" md="12">
            <div class="text-muted small">Notes</div>
            <div>{{ viewingRecord.notes }}</div>
          </CCol>
        </CRow>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showDetailsModal = false">Close</CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CFormTextarea, CFormCheck, CModal, CModalHeader, CModalTitle, CModalBody,
  CModalFooter, CAlert
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel, formatDateForExport } from '../utils/export.js'
import { attendanceApi } from '../api/attendance.js'

// Data
const records = ref([])
const stats = ref({
  pending: 0,
  approved: 0,
  rejected: 0,
  approved_today: 0,
  total_week: 0
})
const loading = ref(false)

// Filters
const filters = reactive({
  dateFrom: '',
  dateTo: '',
  service: '',
  status: '',
  submittedBy: ''
})

// Fetch data from API
async function fetchAttendance() {
  loading.value = true
  try {
    const params = {}
    if (filters.status) params.status = filters.status
    if (filters.service) params.service_type = filters.service
    if (filters.dateFrom) params.date_from = filters.dateFrom
    if (filters.dateTo) params.date_to = filters.dateTo
    
    const response = await attendanceApi.getAll(params)
    if (response.data.success) {
      records.value = response.data.data.map(r => ({
        id: r.id,
        serviceType: r.service_type,
        date: r.service_date,
        count: r.count,
        status: r.status,
        submittedBy: r.submitted_by,
        submittedAt: r.submitted_at,
        approvedBy: r.approved_by,
        rejectionReason: r.rejection_reason,
        notes: r.notes
      }))
      if (response.data.stats) {
        stats.value = response.data.stats
      }
    }
  } catch (error) {
    console.error('Failed to fetch attendance:', error)
    showNotification('danger', 'Failed to load attendance records')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAttendance()
})

const filteredRecords = computed(() => {
  return records.value.filter(r => {
    if (filters.submittedBy && r.submittedBy?.name && !r.submittedBy.name.toLowerCase().includes(filters.submittedBy.toLowerCase())) return false
    return true
  })
})

function resetFilters() {
  filters.dateFrom = ''
  filters.dateTo = ''
  filters.service = ''
  filters.status = ''
  filters.submittedBy = ''
  fetchAttendance()
}

// Stats from API
const pendingCount = computed(() => stats.value.pending)
const approvedTodayCount = computed(() => stats.value.approved_today)
const rejectedCount = computed(() => stats.value.rejected)
const totalWeekCount = computed(() => stats.value.total_week)

// Selection
const selectedIds = ref([])
const allSelected = computed(() => {
  const pending = filteredRecords.value.filter(r => r.status === 'pending')
  return pending.length > 0 && pending.every(r => selectedIds.value.includes(r.id))
})

function toggleSelectAll() {
  const pending = filteredRecords.value.filter(r => r.status === 'pending').map(r => r.id)
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = pending
  }
}

function toggleSelect(id) {
  const idx = selectedIds.value.indexOf(id)
  if (idx === -1) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value.splice(idx, 1)
  }
}

// Approve/Reject
const notification = reactive({ show: false, type: 'success', message: '' })

async function approveRecord(record) {
  try {
    await attendanceApi.approve(record.id)
    showNotification('success', `Approved attendance for ${formatDate(record.date)}`)
    fetchAttendance() // Refresh data
  } catch (error) {
    console.error('Failed to approve:', error)
    showNotification('danger', 'Failed to approve attendance')
  }
}

const showRejectModal = ref(false)
const rejectingRecord = ref(null)
const rejectReason = ref('')

function openRejectModal(record) {
  rejectingRecord.value = record
  rejectReason.value = ''
  showRejectModal.value = true
}

async function rejectRecord() {
  if (rejectingRecord.value) {
    try {
      await attendanceApi.reject(rejectingRecord.value.id, rejectReason.value)
      showNotification('info', `Rejected attendance. Usher notified.`)
      fetchAttendance() // Refresh data
    } catch (error) {
      console.error('Failed to reject:', error)
      showNotification('danger', 'Failed to reject attendance')
    }
  }
  showRejectModal.value = false
}

// Bulk Approve
const showBulkApproveModal = ref(false)

function bulkApprove() {
  showBulkApproveModal.value = true
}

async function confirmBulkApprove() {
  try {
    await attendanceApi.bulkApprove(selectedIds.value)
    showNotification('success', `Approved ${selectedIds.value.length} records`)
    selectedIds.value = []
    fetchAttendance() // Refresh data
  } catch (error) {
    console.error('Failed to bulk approve:', error)
    showNotification('danger', 'Failed to approve records')
  }
  showBulkApproveModal.value = false
}

function getRecordSummary(id) {
  const r = records.value.find(rec => rec.id === id)
  return r ? `${serviceLabel(r.serviceType)} - ${formatDate(r.date)} (${r.count})` : ''
}

// Details
const showDetailsModal = ref(false)
const viewingRecord = ref(null)

function viewDetails(record) {
  viewingRecord.value = record
  showDetailsModal.value = true
}

// Helpers
function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}

function relativeDays(date) {
  const diff = Math.floor((new Date() - new Date(date)) / (1000 * 60 * 60 * 24))
  if (diff === 0) return 'Today'
  if (diff === 1) return 'Yesterday'
  return `${diff} days ago`
}

function serviceLabel(type) {
  const labels = { sunday: 'Sunday Service', friday: 'Friday Night', midweek: 'Midweek Service' }
  return labels[type] || type
}

function serviceIcon(type) {
  const icons = { sunday: 'bi bi-sun', friday: 'bi bi-moon-stars', midweek: 'bi bi-book' }
  return icons[type] || 'bi bi-calendar'
}

function statusColor(status) {
  const colors = { pending: 'warning', approved: 'success', rejected: 'danger' }
  return colors[status] || 'secondary'
}

function statusIcon(status) {
  const icons = { pending: 'bi bi-clock', approved: 'bi bi-check-circle', rejected: 'bi bi-x-circle' }
  return icons[status] || ''
}

function exportApprovals() {
  const columns = [
    { key: 'date', header: 'Date', transform: (v) => formatDateForExport(v) },
    { key: 'serviceType', header: 'Service', transform: (v) => serviceLabel(v) },
    { key: 'count', header: 'Attendance Count' },
    { key: 'submittedBy', header: 'Submitted By', transform: (v) => v?.name || '' },
    { key: 'submittedAt', header: 'Submitted At' },
    { key: 'status', header: 'Status', transform: (v) => v?.charAt(0).toUpperCase() + v?.slice(1) },
    { key: 'approvedBy', header: 'Approved By' },
    { key: 'rejectionReason', header: 'Rejection Reason' },
    { key: 'notes', header: 'Notes' }
  ]
  exportToExcel(filteredRecords.value, columns, `Attendance_Approvals_${new Date().toISOString().split('T')[0]}`)
  showNotification('success', 'Attendance approvals exported successfully')
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.table-warning {
  background-color: rgba(255, 193, 7, 0.08) !important;
}
</style>
