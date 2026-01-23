<template>
  <div class="page-wrap">
    <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false">
      {{ notification.message }}
    </CAlert>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">{{ department?.name || 'My Department' }}</h2>
        <Breadcrumbs />
        <div class="text-muted">Manage your department members and activities</div>
      </div>
      <CButton color="primary" @click="openAddMember">
        <i class="bi bi-person-plus me-1"></i> Add Member
      </CButton>
    </div>

    <!-- Stats Overview -->
    <CRow class="g-4 mb-4">
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Total Members</div>
                <div class="fs-3 fw-bold">{{ members.length }}</div>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-people"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Active</div>
                <div class="fs-3 fw-bold text-success">{{ activeCount }}</div>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-person-check"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Last Sunday</div>
                <div class="fs-3 fw-bold">{{ lastSundayAttendance }}</div>
                <div class="text-muted small">attended</div>
              </div>
              <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-calendar-check"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Avg Attendance</div>
                <div class="fs-3 fw-bold">{{ avgAttendancePercent }}%</div>
                <div class="text-muted small">this month</div>
              </div>
              <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-graph-up"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <CRow class="g-4">
      <!-- Members List -->
      <CCol lg="8">
        <CCard>
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Department Members</div>
            <div class="d-flex gap-2">
              <CFormInput v-model="search" placeholder="Search..." style="width: 200px" />
              <CFormSelect v-model="statusFilter" style="width: 130px">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </CFormSelect>
            </div>
          </CCardHeader>
          <CCardBody class="p-0">
            <CTable hover responsive align="middle">
              <CTableHead color="light">
                <CTableRow>
                  <CTableHeaderCell>Member</CTableHeaderCell>
                  <CTableHeaderCell>Position</CTableHeaderCell>
                  <CTableHeaderCell class="text-center">Attendance Rate</CTableHeaderCell>
                  <CTableHeaderCell>Status</CTableHeaderCell>
                  <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="m in filteredMembers" :key="m.id">
                  <CTableDataCell>
                    <div class="d-flex align-items-center">
                      <CAvatar :color="m.status === 'active' ? 'primary' : 'secondary'" text-color="white" size="sm"
                        class="me-2">
                        {{ m.name.charAt(0) }}
                      </CAvatar>
                      <div>
                        <div class="fw-semibold">{{ m.name }}</div>
                        <div class="text-muted small">{{ m.phone }}</div>
                      </div>
                    </div>
                  </CTableDataCell>
                  <CTableDataCell>
                    <CBadge v-if="m.isLeader" color="warning">Leader</CBadge>
                    <span v-else>{{ m.position || 'Member' }}</span>
                  </CTableDataCell>
                  <CTableDataCell class="text-center">
                    <CProgress :value="m.attendanceRate" height="8" class="mb-1" />
                    <span class="small">{{ m.attendanceRate }}%</span>
                  </CTableDataCell>
                  <CTableDataCell>
                    <CBadge :color="m.status === 'active' ? 'success' : 'secondary'">{{ m.status }}</CBadge>
                  </CTableDataCell>
                  <CTableDataCell class="text-end">
                    <CButton color="success" size="sm" class="me-1" @click="callMember(m)">
                      <i class="bi bi-telephone"></i>
                    </CButton>
                    <CButton color="success" size="sm" class="me-1" @click="whatsappMember(m)">
                      <i class="bi bi-whatsapp"></i>
                    </CButton>
                    <CDropdown variant="btn-group">
                      <CDropdownToggle color="light" size="sm" caret-="false">
                        <i class="bi bi-three-dots-vertical"></i>
                      </CDropdownToggle>
                      <CDropdownMenu>
                        <CDropdownItem @click="editMember(m)">Edit</CDropdownItem>
                        <CDropdownItem @click="changeMemberStatus(m)">
                          {{ m.status === 'active' ? 'Mark Inactive' : 'Mark Active' }}
                        </CDropdownItem>
                        <CDropdownDivider />
                        <CDropdownItem class="text-danger" @click="removeMember(m)">Remove</CDropdownItem>
                      </CDropdownMenu>
                    </CDropdown>
                  </CTableDataCell>
                </CTableRow>
              </CTableBody>
            </CTable>
            <div v-if="!filteredMembers.length" class="text-center py-5 text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              No members found
            </div>
          </CCardBody>
        </CCard>
      </CCol>

      <!-- Sidebar -->
      <CCol lg="4">
        <!-- Quick Actions -->
        <CCard class="mb-4">
          <CCardHeader class="fw-semibold">Quick Actions</CCardHeader>
          <CCardBody class="d-grid gap-2">
            <CButton color="primary" variant="outline" @click="messageAll">
              <i class="bi bi-broadcast me-1"></i> Message All Members
            </CButton>
            <CButton color="info" variant="outline" @click="openMeetingModal">
              <i class="bi bi-calendar-plus me-1"></i> Schedule Meeting
            </CButton>
            <CButton color="success" variant="outline" @click="downloadReport">
              <i class="bi bi-download me-1"></i> Export Member List
            </CButton>
          </CCardBody>
        </CCard>

        <!-- Recent Activity -->
        <CCard class="mb-4">
          <CCardHeader class="fw-semibold">Recent Activity</CCardHeader>
          <CCardBody class="p-0">
            <CListGroup flush>
              <CListGroupItem v-for="a in recentActivity" :key="a.id" class="d-flex align-items-start py-3">
                <div class="activity-icon me-3" :class="'bg-' + a.color + '-subtle text-' + a.color">
                  <i :class="a.icon"></i>
                </div>
                <div>
                  <div>{{ a.description }}</div>
                  <div class="text-muted small">{{ a.time }}</div>
                </div>
              </CListGroupItem>
            </CListGroup>
          </CCardBody>
        </CCard>

        <!-- Upcoming -->
        <CCard>
          <CCardHeader class="fw-semibold">Upcoming Events</CCardHeader>
          <CCardBody>
            <div v-for="e in upcomingEvents" :key="e.id" class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <div class="fw-semibold">{{ e.title }}</div>
                <div class="text-muted small">{{ e.date }}</div>
              </div>
              <CBadge :color="e.color">{{ e.type }}</CBadge>
            </div>
            <div v-if="!upcomingEvents.length" class="text-muted text-center py-3">
              No upcoming events
            </div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Add/Edit Member Modal -->
    <CModal :visible="showMemberModal" @close="showMemberModal = false">
      <CModalHeader>
        <CModalTitle>{{ editingId ? 'Edit Member' : 'Add Member' }}</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <div class="mb-3">
            <CFormLabel>Select Member <span class="text-danger">*</span></CFormLabel>
            <CFormSelect v-model="memberForm.memberId">
              <option value="">Select a church member...</option>
              <option v-for="m in availableMembers" :key="m.id" :value="m.id">{{ m.name }} - {{ m.phone }}</option>
            </CFormSelect>
          </div>
          <div class="mb-3">
            <CFormLabel>Position</CFormLabel>
            <CFormInput v-model="memberForm.position" placeholder="e.g., Secretary, Treasurer" />
          </div>
          <div class="mb-3">
            <CFormCheck v-model="memberForm.isLeader" label="Department Leader" />
          </div>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showMemberModal = false">Cancel</CButton>
        <CButton color="primary" @click="saveMember">{{ editingId ? 'Update' : 'Add' }} Member</CButton>
      </CModalFooter>
    </CModal>

    <!-- Meeting Modal -->
    <CModal :visible="showMeetingModal" @close="showMeetingModal = false">
      <CModalHeader>
        <CModalTitle>Schedule Meeting</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <div class="mb-3">
            <CFormLabel>Meeting Title <span class="text-danger">*</span></CFormLabel>
            <CFormInput v-model="meetingForm.title" placeholder="e.g., Monthly Planning Meeting" />
          </div>
          <div class="mb-3">
            <CFormLabel>Date & Time <span class="text-danger">*</span></CFormLabel>
            <CFormInput v-model="meetingForm.datetime" type="datetime-local" />
          </div>
          <div class="mb-3">
            <CFormLabel>Location</CFormLabel>
            <CFormInput v-model="meetingForm.location" placeholder="e.g., Church Hall" />
          </div>
          <div class="mb-3">
            <CFormLabel>Agenda</CFormLabel>
            <CFormTextarea v-model="meetingForm.agenda" rows="3" />
          </div>
          <div class="mb-3">
            <CFormCheck v-model="meetingForm.sendReminder" label="Send reminder to all members" />
          </div>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showMeetingModal = false">Cancel</CButton>
        <CButton color="primary" @click="scheduleMeeting">Schedule Meeting</CButton>
      </CModalFooter>
    </CModal>

    <!-- Message Modal -->
    <CModal :visible="showMessageModal" @close="showMessageModal = false">
      <CModalHeader>
        <CModalTitle>Message All Members</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <div class="mb-3">
            <CFormLabel>Channel</CFormLabel>
            <div class="d-flex gap-3">
              <CFormCheck type="radio" name="channel" id="ch-whatsapp" value="whatsapp" v-model="messageForm.channel"
                label="WhatsApp" inline />
              <CFormCheck type="radio" name="channel" id="ch-sms" value="sms" v-model="messageForm.channel" label="SMS"
                inline />
            </div>
          </div>
          <div class="mb-3">
            <CFormLabel>Message <span class="text-danger">*</span></CFormLabel>
            <CFormTextarea v-model="messageForm.message" rows="4" placeholder="Type your message..." />
            <div class="text-muted small mt-1">{{ messageForm.message.length }}/500 characters</div>
          </div>
          <CAlert color="info" class="mb-0">
            <small>This will send to {{ activeCount }} active members</small>
          </CAlert>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showMessageModal = false">Cancel</CButton>
        <CButton color="primary" @click="sendMessage">Send Message</CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CFormTextarea, CFormCheck, CModal, CModalHeader, CModalTitle, CModalBody,
  CModalFooter, CAlert, CListGroup, CListGroupItem, CProgress, CDropdown, CDropdownToggle,
  CDropdownMenu, CDropdownItem, CDropdownDivider, CForm
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'

// Department info
const department = ref({
  id: 1,
  name: 'Media Department',
  description: 'Handles all audio-visual and digital media needs'
})

// Members
const members = ref([
  { id: 1, name: 'Kwame Asante', phone: '+233241234567', position: 'Camera Operator', isLeader: false, status: 'active', attendanceRate: 92 },
  { id: 2, name: 'Ama Mensah', phone: '+233201234567', position: 'Sound Engineer', isLeader: true, status: 'active', attendanceRate: 100 },
  { id: 3, name: 'Kofi Boateng', phone: '+233551234567', position: 'Graphics Designer', isLeader: false, status: 'active', attendanceRate: 85 },
  { id: 4, name: 'Yaa Osei', phone: '+233271234567', position: 'Editor', isLeader: false, status: 'inactive', attendanceRate: 40 },
  { id: 5, name: 'Kweku Appiah', phone: '+233501234567', position: 'Member', isLeader: false, status: 'active', attendanceRate: 78 }
])

// Available church members (not in department)
const availableMembers = ref([
  { id: 101, name: 'Akosua Darko', phone: '+233241111111' },
  { id: 102, name: 'Nana Agyeman', phone: '+233202222222' },
  { id: 103, name: 'Kojo Mensah', phone: '+233553333333' }
])

// State
const search = ref('')
const statusFilter = ref('')
const showMemberModal = ref(false)
const showMeetingModal = ref(false)
const showMessageModal = ref(false)
const editingId = ref(null)

// Stats
const activeCount = computed(() => members.value.filter(m => m.status === 'active').length)
const lastSundayAttendance = ref(4)
const avgAttendancePercent = computed(() => {
  const active = members.value.filter(m => m.status === 'active')
  if (!active.length) return 0
  return Math.round(active.reduce((sum, m) => sum + m.attendanceRate, 0) / active.length)
})

// Filtered
const filteredMembers = computed(() => {
  return members.value.filter(m => {
    if (statusFilter.value && m.status !== statusFilter.value) return false
    if (search.value && !m.name.toLowerCase().includes(search.value.toLowerCase()) &&
      !m.phone.includes(search.value)) return false
    return true
  })
})

// Recent activity
const recentActivity = ref([
  { id: 1, description: 'Ama Mensah attended Sunday service', icon: 'bi bi-check-circle', color: 'success', time: '2 hours ago' },
  { id: 2, description: 'Yaa Osei marked inactive', icon: 'bi bi-person-x', color: 'warning', time: 'Yesterday' },
  { id: 3, description: 'Monthly meeting held', icon: 'bi bi-calendar-check', color: 'info', time: '3 days ago' }
])

// Upcoming events
const upcomingEvents = ref([
  { id: 1, title: 'Equipment Training', date: 'Sat, Jan 31', type: 'Training', color: 'primary' },
  { id: 2, title: 'Monthly Planning', date: 'Sun, Feb 2', type: 'Meeting', color: 'info' }
])

// Forms
const memberForm = reactive({ memberId: '', position: '', isLeader: false })
const meetingForm = reactive({ title: '', datetime: '', location: '', agenda: '', sendReminder: true })
const messageForm = reactive({ channel: 'whatsapp', message: '' })

// Notification
const notification = reactive({ show: false, type: 'success', message: '' })

// Methods
function openAddMember() {
  editingId.value = null
  Object.assign(memberForm, { memberId: '', position: '', isLeader: false })
  showMemberModal.value = true
}

function editMember(m) {
  editingId.value = m.id
  Object.assign(memberForm, { memberId: m.id.toString(), position: m.position, isLeader: m.isLeader })
  showMemberModal.value = true
}

function saveMember() {
  if (editingId.value) {
    const idx = members.value.findIndex(m => m.id === editingId.value)
    if (idx !== -1) {
      members.value[idx].position = memberForm.position
      members.value[idx].isLeader = memberForm.isLeader
    }
    showNotification('success', 'Member updated')
  } else {
    const selected = availableMembers.value.find(m => m.id === parseInt(memberForm.memberId))
    if (selected) {
      members.value.push({
        id: selected.id,
        name: selected.name,
        phone: selected.phone,
        position: memberForm.position || 'Member',
        isLeader: memberForm.isLeader,
        status: 'active',
        attendanceRate: 0
      })
      showNotification('success', 'Member added to department')
    }
  }
  showMemberModal.value = false
}

function changeMemberStatus(m) {
  m.status = m.status === 'active' ? 'inactive' : 'active'
  showNotification('success', `${m.name} marked as ${m.status}`)
}

function removeMember(m) {
  if (confirm(`Remove ${m.name} from the department?`)) {
    const idx = members.value.findIndex(x => x.id === m.id)
    if (idx !== -1) {
      members.value.splice(idx, 1)
      showNotification('success', 'Member removed from department')
    }
  }
}

function callMember(m) {
  window.open(`tel:${m.phone}`)
}

function whatsappMember(m) {
  const phone = m.phone.replace('+', '')
  window.open(`https://wa.me/${phone}`)
}

function messageAll() {
  Object.assign(messageForm, { channel: 'whatsapp', message: '' })
  showMessageModal.value = true
}

function sendMessage() {
  showMessageModal.value = false
  showNotification('success', `Message sent to ${activeCount.value} members`)
}

function openMeetingModal() {
  Object.assign(meetingForm, { title: '', datetime: '', location: '', agenda: '', sendReminder: true })
  showMeetingModal.value = true
}

function scheduleMeeting() {
  showMeetingModal.value = false
  showNotification('success', 'Meeting scheduled and reminders sent')
}

function downloadReport() {
  showNotification('info', 'Member list export started')
}

function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.stat-card {
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
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

.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>
