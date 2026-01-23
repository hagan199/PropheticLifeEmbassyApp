<template>
  <div class="page-wrap">
    <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false">
      {{ notification.message }}
    </CAlert>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">My Submissions</h2>
        <Breadcrumbs />
        <div class="text-muted">Track your attendance and visitor submissions</div>
      </div>
    </div>

    <!-- Tabs -->
    <CNav variant="tabs" class="mb-4">
      <CNavItem>
        <CNavLink :active="activeTab === 'attendance'" @click="activeTab = 'attendance'" href="javascript:void(0)">
          <i class="bi bi-clipboard-check me-1"></i> Attendance
        </CNavLink>
      </CNavItem>
      <CNavItem>
        <CNavLink :active="activeTab === 'visitors'" @click="activeTab = 'visitors'" href="javascript:void(0)">
          <i class="bi bi-person-plus me-1"></i> Visitors
        </CNavLink>
      </CNavItem>
    </CNav>

    <!-- Attendance Tab -->
    <div v-if="activeTab === 'attendance'">
      <!-- Quick Actions -->
      <CRow class="g-4 mb-4">
        <CCol md="8">
          <CCard class="border-0 shadow-sm">
            <CCardBody class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-1">Record Today's Attendance</h5>
                <div class="text-muted">{{ currentService }} - {{ formatDate(today) }}</div>
              </div>
              <CButton color="primary" size="lg" @click="openNewAttendance">
                <i class="bi bi-plus-lg me-1"></i> Submit Attendance
              </CButton>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol md="4">
          <CCard class="border-0 shadow-sm h-100">
            <CCardBody class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Total Submissions</div>
                <div class="fs-3 fw-bold">{{ attendanceSubmissions.length }}</div>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-clipboard-data"></i>
              </div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <!-- Submissions List -->
      <CCard>
        <CCardHeader class="d-flex justify-content-between align-items-center">
          <div class="fw-semibold">My Attendance Submissions</div>
          <CFormSelect v-model="attendanceStatusFilter" style="width: 150px">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </CFormSelect>
        </CCardHeader>
        <CCardBody class="p-0">
          <CTable hover responsive align="middle">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell>Date</CTableHeaderCell>
                <CTableHeaderCell>Service</CTableHeaderCell>
                <CTableHeaderCell class="text-center">Adults</CTableHeaderCell>
                <CTableHeaderCell class="text-center">Children</CTableHeaderCell>
                <CTableHeaderCell class="text-center">Visitors</CTableHeaderCell>
                <CTableHeaderCell class="text-center">Total</CTableHeaderCell>
                <CTableHeaderCell>Status</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="a in filteredAttendance" :key="a.id">
                <CTableDataCell>{{ formatDate(a.date) }}</CTableDataCell>
                <CTableDataCell>{{ a.serviceType }}</CTableDataCell>
                <CTableDataCell class="text-center">{{ a.adults }}</CTableDataCell>
                <CTableDataCell class="text-center">{{ a.children }}</CTableDataCell>
                <CTableDataCell class="text-center">{{ a.visitors }}</CTableDataCell>
                <CTableDataCell class="text-center fw-bold">{{ a.adults + a.children + a.visitors }}</CTableDataCell>
                <CTableDataCell>
                  <CBadge :color="statusColor(a.status)">
                    <i :class="statusIcon(a.status)" class="me-1"></i>
                    {{ a.status }}
                  </CBadge>
                </CTableDataCell>
                <CTableDataCell class="text-end">
                  <CButton v-if="a.status === 'pending'" color="light" size="sm" class="me-1"
                    @click="editAttendance(a)">
                    <i class="bi bi-pencil"></i>
                  </CButton>
                  <CButton color="light" size="sm" @click="viewDetails(a)">
                    <i class="bi bi-eye"></i>
                  </CButton>
                </CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>
          <div v-if="!filteredAttendance.length" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No submissions found
          </div>
        </CCardBody>
      </CCard>
    </div>

    <!-- Visitors Tab -->
    <div v-if="activeTab === 'visitors'">
      <!-- Quick Actions -->
      <CRow class="g-4 mb-4">
        <CCol md="8">
          <CCard class="border-0 shadow-sm">
            <CCardBody class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-1">Register New Visitor</h5>
                <div class="text-muted">Add visitor details for today's service</div>
              </div>
              <CButton color="primary" size="lg" @click="openNewVisitor">
                <i class="bi bi-person-plus me-1"></i> Add Visitor
              </CButton>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol md="4">
          <CCard class="border-0 shadow-sm h-100">
            <CCardBody class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted small">Visitors Registered</div>
                <div class="fs-3 fw-bold">{{ visitorSubmissions.length }}</div>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-people"></i>
              </div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <!-- Visitors List -->
      <CCard>
        <CCardHeader class="d-flex justify-content-between align-items-center">
          <div class="fw-semibold">Visitors I've Registered</div>
          <CFormInput v-model="visitorSearch" placeholder="Search..." style="width: 200px" />
        </CCardHeader>
        <CCardBody class="p-0">
          <CTable hover responsive align="middle">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell>Visitor</CTableHeaderCell>
                <CTableHeaderCell>Phone</CTableHeaderCell>
                <CTableHeaderCell>Source</CTableHeaderCell>
                <CTableHeaderCell>Service Date</CTableHeaderCell>
                <CTableHeaderCell>Follow-up Status</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="v in filteredVisitors" :key="v.id">
                <CTableDataCell>
                  <div class="d-flex align-items-center">
                    <CAvatar color="success" text-color="white" size="sm" class="me-2">
                      {{ v.name.charAt(0) }}
                    </CAvatar>
                    <span class="fw-semibold">{{ v.name }}</span>
                  </div>
                </CTableDataCell>
                <CTableDataCell>{{ v.phone }}</CTableDataCell>
                <CTableDataCell>
                  <CBadge color="light" text-color="dark">{{ v.source }}</CBadge>
                </CTableDataCell>
                <CTableDataCell>{{ formatDate(v.serviceDate) }}</CTableDataCell>
                <CTableDataCell>
                  <CBadge :color="followUpStatusColor(v.followUpStatus)">{{ v.followUpStatus }}</CBadge>
                </CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>
          <div v-if="!filteredVisitors.length" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No visitors registered yet
          </div>
        </CCardBody>
      </CCard>
    </div>

    <!-- Attendance Modal -->
    <CModal :visible="showAttendanceModal" @close="showAttendanceModal = false" size="lg">
      <CModalHeader>
        <CModalTitle>{{ editingId ? 'Edit Attendance' : 'Submit Attendance' }}</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <CRow class="g-3">
            <CCol md="6">
              <CFormLabel>Date <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="attendanceForm.date" type="date" />
            </CCol>
            <CCol md="6">
              <CFormLabel>Service Type <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="attendanceForm.serviceType">
                <option value="">Select service...</option>
                <option value="Sunday First Service">Sunday First Service</option>
                <option value="Sunday Second Service">Sunday Second Service</option>
                <option value="Midweek Service">Midweek Service</option>
                <option value="Friday Prayer">Friday Prayer</option>
                <option value="Special Program">Special Program</option>
              </CFormSelect>
            </CCol>
            <CCol md="4">
              <CFormLabel>Adults <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model.number="attendanceForm.adults" type="number" min="0" />
            </CCol>
            <CCol md="4">
              <CFormLabel>Children <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model.number="attendanceForm.children" type="number" min="0" />
            </CCol>
            <CCol md="4">
              <CFormLabel>First-time Visitors</CFormLabel>
              <CFormInput v-model.number="attendanceForm.visitors" type="number" min="0" />
            </CCol>
            <CCol md="12">
              <CFormLabel>Notes</CFormLabel>
              <CFormTextarea v-model="attendanceForm.notes" rows="2" placeholder="Any additional notes..." />
            </CCol>
          </CRow>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showAttendanceModal = false">Cancel</CButton>
        <CButton color="primary" @click="saveAttendance">Submit for Approval</CButton>
      </CModalFooter>
    </CModal>

    <!-- Visitor Modal -->
    <CModal :visible="showVisitorModal" @close="showVisitorModal = false" size="lg">
      <CModalHeader>
        <CModalTitle>Register Visitor</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <CRow class="g-3">
            <CCol md="6">
              <CFormLabel>Name <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="visitorForm.name" />
            </CCol>
            <CCol md="6">
              <CFormLabel>Phone <span class="text-danger">*</span></CFormLabel>
              <CInputGroup>
                <CInputGroupText>+233</CInputGroupText>
                <CFormInput v-model="visitorForm.phone" />
              </CInputGroup>
            </CCol>
            <CCol md="6">
              <CFormLabel>Email</CFormLabel>
              <CFormInput v-model="visitorForm.email" type="email" />
            </CCol>
            <CCol md="6">
              <CFormLabel>How did they hear about us?</CFormLabel>
              <CFormSelect v-model="visitorForm.source">
                <option value="">Select...</option>
                <option value="Friend">Friend/Family</option>
                <option value="Social Media">Social Media</option>
                <option value="Walk-in">Walk-in</option>
                <option value="Crusade">Crusade/Outreach</option>
                <option value="Other">Other</option>
              </CFormSelect>
            </CCol>
            <CCol md="12">
              <CFormLabel>Prayer Request / Notes</CFormLabel>
              <CFormTextarea v-model="visitorForm.notes" rows="3"
                placeholder="Any prayer requests or additional information..." />
            </CCol>
          </CRow>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showVisitorModal = false">Cancel</CButton>
        <CButton color="primary" @click="saveVisitor">Save Visitor</CButton>
      </CModalFooter>
    </CModal>

    <!-- Details Modal -->
    <CModal :visible="showDetailsModal" @close="showDetailsModal = false">
      <CModalHeader>
        <CModalTitle>Submission Details</CModalTitle>
      </CModalHeader>
      <CModalBody v-if="selectedSubmission">
        <CRow class="g-3">
          <CCol xs="6">
            <div class="text-muted small">Date</div>
            <div class="fw-semibold">{{ formatDate(selectedSubmission.date) }}</div>
          </CCol>
          <CCol xs="6">
            <div class="text-muted small">Service</div>
            <div class="fw-semibold">{{ selectedSubmission.serviceType }}</div>
          </CCol>
          <CCol xs="4">
            <div class="text-muted small">Adults</div>
            <div class="fw-semibold fs-5">{{ selectedSubmission.adults }}</div>
          </CCol>
          <CCol xs="4">
            <div class="text-muted small">Children</div>
            <div class="fw-semibold fs-5">{{ selectedSubmission.children }}</div>
          </CCol>
          <CCol xs="4">
            <div class="text-muted small">Visitors</div>
            <div class="fw-semibold fs-5">{{ selectedSubmission.visitors }}</div>
          </CCol>
          <CCol xs="12">
            <div class="text-muted small">Status</div>
            <CBadge :color="statusColor(selectedSubmission.status)" size="lg">
              {{ selectedSubmission.status }}
            </CBadge>
          </CCol>
          <CCol xs="12" v-if="selectedSubmission.status === 'rejected'">
            <CAlert color="danger" class="mb-0">
              <strong>Rejection Reason:</strong> {{ selectedSubmission.rejectReason }}
            </CAlert>
          </CCol>
          <CCol xs="12" v-if="selectedSubmission.notes">
            <div class="text-muted small">Notes</div>
            <div>{{ selectedSubmission.notes }}</div>
          </CCol>
          <CCol xs="12">
            <div class="text-muted small">Submitted</div>
            <div>{{ formatDateTime(selectedSubmission.submittedAt) }}</div>
          </CCol>
        </CRow>
      </CModalBody>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CFormTextarea, CInputGroup, CInputGroupText, CModal, CModalHeader, CModalTitle,
  CModalBody, CModalFooter, CAlert, CNav, CNavItem, CNavLink, CForm
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'

// State
const activeTab = ref('attendance')
const today = new Date().toISOString().split('T')[0]
const currentService = 'Sunday First Service'

// Attendance data
const attendanceSubmissions = ref([
  { id: 1, date: '2026-01-26', serviceType: 'Sunday First Service', adults: 120, children: 35, visitors: 8, status: 'pending', submittedAt: '2026-01-26T10:30:00', notes: '' },
  { id: 2, date: '2026-01-22', serviceType: 'Midweek Service', adults: 65, children: 12, visitors: 2, status: 'approved', submittedAt: '2026-01-22T19:45:00', notes: '' },
  { id: 3, date: '2026-01-19', serviceType: 'Sunday Second Service', adults: 95, children: 28, visitors: 5, status: 'approved', submittedAt: '2026-01-19T13:15:00', notes: '' },
  { id: 4, date: '2026-01-12', serviceType: 'Sunday First Service', adults: 110, children: 32, visitors: 10, status: 'rejected', rejectReason: 'Numbers don\'t match physical count', submittedAt: '2026-01-12T10:30:00', notes: '' }
])

// Visitor data
const visitorSubmissions = ref([
  { id: 1, name: 'Ama Kwarteng', phone: '+233241234567', source: 'Friend', serviceDate: '2026-01-26', followUpStatus: 'Pending' },
  { id: 2, name: 'Kofi Asante', phone: '+233201234567', source: 'Social Media', serviceDate: '2026-01-19', followUpStatus: 'Contacted' },
  { id: 3, name: 'Yaa Mensah', phone: '+233551234567', source: 'Walk-in', serviceDate: '2026-01-12', followUpStatus: 'Engaged' }
])

// Filters
const attendanceStatusFilter = ref('')
const visitorSearch = ref('')

// Modals
const showAttendanceModal = ref(false)
const showVisitorModal = ref(false)
const showDetailsModal = ref(false)
const editingId = ref(null)
const selectedSubmission = ref(null)

// Forms
const attendanceForm = reactive({
  date: '', serviceType: '', adults: 0, children: 0, visitors: 0, notes: ''
})

const visitorForm = reactive({
  name: '', phone: '', email: '', source: '', notes: ''
})

// Notification
const notification = reactive({ show: false, type: 'success', message: '' })

// Computed
const filteredAttendance = computed(() => {
  if (!attendanceStatusFilter.value) return attendanceSubmissions.value
  return attendanceSubmissions.value.filter(a => a.status === attendanceStatusFilter.value)
})

const filteredVisitors = computed(() => {
  if (!visitorSearch.value) return visitorSubmissions.value
  const s = visitorSearch.value.toLowerCase()
  return visitorSubmissions.value.filter(v =>
    v.name.toLowerCase().includes(s) || v.phone.includes(s)
  )
})

// Methods
function openNewAttendance() {
  editingId.value = null
  Object.assign(attendanceForm, { date: today, serviceType: currentService, adults: 0, children: 0, visitors: 0, notes: '' })
  showAttendanceModal.value = true
}

function editAttendance(a) {
  editingId.value = a.id
  Object.assign(attendanceForm, { date: a.date, serviceType: a.serviceType, adults: a.adults, children: a.children, visitors: a.visitors, notes: a.notes || '' })
  showAttendanceModal.value = true
}

function saveAttendance() {
  if (editingId.value) {
    const idx = attendanceSubmissions.value.findIndex(a => a.id === editingId.value)
    if (idx !== -1) {
      attendanceSubmissions.value[idx] = { ...attendanceSubmissions.value[idx], ...attendanceForm, status: 'pending' }
    }
    showNotification('success', 'Attendance updated and resubmitted')
  } else {
    const newId = Math.max(...attendanceSubmissions.value.map(a => a.id)) + 1
    attendanceSubmissions.value.unshift({
      id: newId, ...attendanceForm, status: 'pending', submittedAt: new Date().toISOString()
    })
    showNotification('success', 'Attendance submitted for approval')
  }
  showAttendanceModal.value = false
}

function viewDetails(a) {
  selectedSubmission.value = a
  showDetailsModal.value = true
}

function openNewVisitor() {
  Object.assign(visitorForm, { name: '', phone: '', email: '', source: '', notes: '' })
  showVisitorModal.value = true
}

function saveVisitor() {
  const newId = Math.max(...visitorSubmissions.value.map(v => v.id)) + 1
  visitorSubmissions.value.unshift({
    id: newId,
    name: visitorForm.name,
    phone: '+233' + visitorForm.phone,
    email: visitorForm.email,
    source: visitorForm.source,
    serviceDate: today,
    followUpStatus: 'Pending'
  })
  showVisitorModal.value = false
  showNotification('success', 'Visitor registered successfully')
}

function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}

// Helpers
function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatDateTime(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function statusColor(status) {
  const colors = { pending: 'warning', approved: 'success', rejected: 'danger' }
  return colors[status] || 'secondary'
}

function statusIcon(status) {
  const icons = { pending: 'bi bi-clock', approved: 'bi bi-check-circle', rejected: 'bi bi-x-circle' }
  return icons[status] || ''
}

function followUpStatusColor(status) {
  const colors = { Pending: 'warning', Contacted: 'info', Engaged: 'success', Converted: 'primary' }
  return colors[status] || 'secondary'
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
</style>
