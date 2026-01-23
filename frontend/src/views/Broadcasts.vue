<template>
  <div class="page-wrap">
    <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false">
      {{ notification.message }}
    </CAlert>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Broadcasts</h2>
        <Breadcrumbs />
        <div class="text-muted">Send WhatsApp/SMS messages to members</div>
      </div>
      <CButton color="light" @click="exportBroadcastHistory">
        <i class="bi bi-file-earmark-excel me-1"></i> Export History
      </CButton>
    </div>

    <!-- Tabs -->
    <CNav variant="tabs" class="mb-4">
      <CNavItem>
        <CNavLink :active="activeTab === 'compose'" @click="activeTab = 'compose'">
          <i class="bi bi-pencil-square me-1"></i> Compose
        </CNavLink>
      </CNavItem>
      <CNavItem>
        <CNavLink :active="activeTab === 'history'" @click="activeTab = 'history'">
          <i class="bi bi-clock-history me-1"></i> History
          <CBadge color="secondary" class="ms-1">{{ history.length }}</CBadge>
        </CNavLink>
      </CNavItem>
      <CNavItem>
        <CNavLink :active="activeTab === 'scheduled'" @click="activeTab = 'scheduled'">
          <i class="bi bi-calendar-event me-1"></i> Scheduled
          <CBadge v-if="scheduledCount" color="primary" class="ms-1">{{ scheduledCount }}</CBadge>
        </CNavLink>
      </CNavItem>
    </CNav>

    <!-- Compose Tab -->
    <div v-show="activeTab === 'compose'">
      <CRow class="g-4">
        <CCol lg="8">
          <CCard>
            <CCardHeader class="fw-semibold">New Broadcast</CCardHeader>
            <CCardBody>
              <CForm @submit.prevent="sendBroadcast">
                <!-- Recipients -->
                <div class="mb-4">
                  <CFormLabel class="fw-semibold">Recipients <span class="text-danger">*</span></CFormLabel>
                  <div class="d-flex flex-wrap gap-2">
                    <CFormCheck type="radio" name="recipients" id="rec-all" value="all_members"
                      v-model="form.recipientGroup" label="All Members" inline />
                    <CFormCheck type="radio" name="recipients" id="rec-partners" value="partnerships"
                      v-model="form.recipientGroup" label="Partners Only" inline />
                    <CFormCheck type="radio" name="recipients" id="rec-dept" value="department"
                      v-model="form.recipientGroup" label="Department" inline />
                  </div>
                  <CFormSelect v-if="form.recipientGroup === 'department'" v-model="form.departmentId" class="mt-2"
                    style="max-width: 300px">
                    <option value="">Select department...</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                  </CFormSelect>
                  <div class="text-muted small mt-2">
                    <i class="bi bi-people me-1"></i> {{ recipientCount }} recipients
                  </div>
                </div>

                <!-- Channel -->
                <div class="mb-4">
                  <CFormLabel class="fw-semibold">Channel <span class="text-danger">*</span></CFormLabel>
                  <div class="d-flex gap-3">
                    <CCard :class="['channel-card', { selected: form.channel === 'whatsapp' }]"
                      @click="form.channel = 'whatsapp'">
                      <CCardBody class="text-center py-3">
                        <i class="bi bi-whatsapp fs-3 text-success"></i>
                        <div class="fw-semibold mt-1">WhatsApp</div>
                        <div class="text-muted small">No limit</div>
                      </CCardBody>
                    </CCard>
                    <CCard :class="['channel-card', { selected: form.channel === 'sms' }]"
                      @click="form.channel = 'sms'">
                      <CCardBody class="text-center py-3">
                        <i class="bi bi-chat-dots fs-3 text-primary"></i>
                        <div class="fw-semibold mt-1">SMS</div>
                        <div class="text-muted small">160 chars</div>
                      </CCardBody>
                    </CCard>
                  </div>
                </div>

                <!-- Message -->
                <div class="mb-4">
                  <CFormLabel class="fw-semibold">Message <span class="text-danger">*</span></CFormLabel>
                  <CFormTextarea v-model="form.message" rows="5" placeholder="Type your message..."
                    :class="{ 'is-invalid': form.channel === 'sms' && charCount > 160 }" />
                  <div class="d-flex justify-content-between mt-1">
                    <div :class="['small', charCountClass]">
                      {{ charCount }} / {{ form.channel === 'sms' ? 160 : '∞' }} characters
                    </div>
                    <div v-if="form.channel === 'sms' && charCount > 160" class="text-danger small">
                      Message will be split into {{ Math.ceil(charCount / 160) }} parts
                    </div>
                  </div>
                </div>

                <!-- Schedule -->
                <div class="mb-4">
                  <CFormCheck v-model="form.scheduled" label="Schedule for later" />
                  <CRow v-if="form.scheduled" class="g-2 mt-2">
                    <CCol md="6">
                      <CFormInput type="date" v-model="form.scheduleDate" :min="minDate" />
                    </CCol>
                    <CCol md="6">
                      <CFormInput type="time" v-model="form.scheduleTime" />
                    </CCol>
                  </CRow>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2">
                  <CButton color="light" @click="previewMessage">
                    <i class="bi bi-eye me-1"></i> Preview
                  </CButton>
                  <CButton color="primary" type="submit" :disabled="!canSend || sending">
                    <CSpinner v-if="sending" size="sm" class="me-1" />
                    <i v-else class="bi bi-send me-1"></i>
                    {{ form.scheduled ? 'Schedule' : 'Send Now' }}
                  </CButton>
                </div>
              </CForm>
            </CCardBody>
          </CCard>
        </CCol>

        <CCol lg="4">
          <CCard class="mb-4">
            <CCardHeader class="fw-semibold">Quick Templates</CCardHeader>
            <CCardBody>
              <CListGroup flush>
                <CListGroupItem v-for="t in templates" :key="t.id"
                  class="d-flex justify-content-between align-items-center cursor-pointer" @click="useTemplate(t)">
                  <div>
                    <div class="fw-semibold">{{ t.name }}</div>
                    <div class="text-muted small text-truncate" style="max-width: 180px">{{ t.message }}</div>
                  </div>
                  <i class="bi bi-chevron-right text-muted"></i>
                </CListGroupItem>
              </CListGroup>
            </CCardBody>
          </CCard>

          <CCard>
            <CCardHeader class="fw-semibold">Tips</CCardHeader>
            <CCardBody>
              <ul class="list-unstyled mb-0 small text-muted">
                <li class="mb-2"><i class="bi bi-lightbulb text-warning me-2"></i>WhatsApp is free and unlimited</li>
                <li class="mb-2"><i class="bi bi-lightbulb text-warning me-2"></i>SMS falls back if WhatsApp fails</li>
                <li class="mb-2"><i class="bi bi-lightbulb text-warning me-2"></i>Schedule for optimal delivery times
                </li>
                <li><i class="bi bi-lightbulb text-warning me-2"></i>Keep SMS under 160 chars</li>
              </ul>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </div>

    <!-- History Tab -->
    <div v-show="activeTab === 'history'">
      <CCard>
        <CCardHeader class="d-flex justify-content-between align-items-center">
          <div class="fw-semibold">Broadcast History</div>
          <CButton color="light" size="sm">
            <i class="bi bi-download me-1"></i> Export CSV
          </CButton>
        </CCardHeader>
        <CCardBody>
          <CTable hover responsive>
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell>Date</CTableHeaderCell>
                <CTableHeaderCell>Recipients</CTableHeaderCell>
                <CTableHeaderCell>Channel</CTableHeaderCell>
                <CTableHeaderCell>Message</CTableHeaderCell>
                <CTableHeaderCell>Delivery</CTableHeaderCell>
                <CTableHeaderCell>Status</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="h in history" :key="h.id">
                <CTableDataCell>
                  <div class="fw-semibold">{{ formatDate(h.sentAt) }}</div>
                  <div class="text-muted small">{{ h.sentAt }}</div>
                </CTableDataCell>
                <CTableDataCell>
                  <CBadge color="light" text-color="dark">{{ h.recipientGroup }}</CBadge>
                  <div class="text-muted small">{{ h.totalRecipients }} people</div>
                </CTableDataCell>
                <CTableDataCell>
                  <i
                    :class="h.channel === 'whatsapp' ? 'bi bi-whatsapp text-success' : 'bi bi-chat-dots text-primary'"></i>
                  {{ h.channel }}
                </CTableDataCell>
                <CTableDataCell>
                  <div class="text-truncate" style="max-width: 200px">{{ h.message }}</div>
                </CTableDataCell>
                <CTableDataCell>
                  <div class="d-flex align-items-center gap-2">
                    <CProgress :value="h.deliveryRate" height="6" style="width: 60px"
                      :color="h.deliveryRate === 100 ? 'success' : h.deliveryRate > 80 ? 'warning' : 'danger'" />
                    <span class="small">{{ h.deliveryRate }}%</span>
                  </div>
                </CTableDataCell>
                <CTableDataCell>
                  <CBadge :color="statusColor(h.status)">{{ h.status }}</CBadge>
                </CTableDataCell>
                <CTableDataCell class="text-end">
                  <CButton v-if="h.status === 'partially_sent'" color="warning" size="sm" @click="retryFailed(h)">
                    <i class="bi bi-arrow-clockwise"></i>
                  </CButton>
                  <CButton color="light" size="sm" class="ms-1" @click="viewBroadcastDetails(h)">
                    <i class="bi bi-eye"></i>
                  </CButton>
                </CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>
        </CCardBody>
      </CCard>
    </div>

    <!-- Scheduled Tab -->
    <div v-show="activeTab === 'scheduled'">
      <CCard>
        <CCardHeader class="fw-semibold">Scheduled Broadcasts</CCardHeader>
        <CCardBody>
          <div v-if="!scheduled.length" class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
            No scheduled broadcasts
          </div>
          <CListGroup v-else flush>
            <CListGroupItem v-for="s in scheduled" :key="s.id"
              class="d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold">{{ s.recipientGroup }}</div>
                <div class="text-muted small text-truncate" style="max-width: 300px">{{ s.message }}</div>
                <CBadge color="info" class="mt-1">
                  <i class="bi bi-clock me-1"></i> {{ formatDateTime(s.scheduledFor) }}
                </CBadge>
              </div>
              <div>
                <CButton color="light" size="sm" class="me-1" @click="editScheduled(s)">
                  <i class="bi bi-pencil"></i>
                </CButton>
                <CButton color="danger" size="sm" variant="ghost" @click="cancelScheduled(s)">
                  <i class="bi bi-x-lg"></i>
                </CButton>
              </div>
            </CListGroupItem>
          </CListGroup>
        </CCardBody>
      </CCard>
    </div>

    <!-- Preview Modal -->
    <CModal :visible="showPreview" @close="showPreview = false">
      <CModalHeader>
        <CModalTitle>Message Preview</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <div class="preview-phone">
          <div class="preview-header">
            <i :class="form.channel === 'whatsapp' ? 'bi bi-whatsapp' : 'bi bi-chat-dots'"></i>
            Prophetic Life Embassy
          </div>
          <div class="preview-bubble">
            {{ form.message || 'Your message here...' }}
          </div>
          <div class="preview-meta">
            To: {{ recipientLabel }} ({{ recipientCount }})
          </div>
        </div>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showPreview = false">Close</CButton>
        <CButton color="primary" @click="showPreview = false; sendBroadcast()">
          {{ form.scheduled ? 'Schedule' : 'Send Now' }}
        </CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CFormInput, CFormSelect, CFormLabel,
  CFormTextarea, CFormCheck, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CAlert, CNav, CNavItem, CNavLink, CListGroup, CListGroupItem, CForm, CProgress, CSpinner
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel, formatDateForExport } from '../utils/export.js'

// Tabs
const activeTab = ref('compose')

// Form
const form = reactive({
  recipientGroup: 'all_members',
  departmentId: '',
  channel: 'whatsapp',
  message: '',
  scheduled: false,
  scheduleDate: '',
  scheduleTime: ''
})

const sending = ref(false)
const showPreview = ref(false)

// Data
const departments = ref([
  { id: 1, name: 'Media', memberCount: 12 },
  { id: 2, name: 'Prayer Team', memberCount: 18 },
  { id: 3, name: 'Ushering', memberCount: 24 },
  { id: 4, name: 'Choir', memberCount: 32 }
])

const templates = ref([
  { id: 1, name: 'Service Reminder', message: 'Join us this Sunday at 9am for an uplifting service!' },
  { id: 2, name: 'Event Announcement', message: 'Special event coming up! Mark your calendars for...' },
  { id: 3, name: 'Prayer Request', message: 'Please keep our church family in your prayers...' }
])

const history = ref([
  { id: 1, sentAt: '2026-01-22 10:30', recipientGroup: 'All Members', totalRecipients: 234, channel: 'whatsapp', message: 'Join us this Sunday!', deliveryRate: 98, status: 'sent' },
  { id: 2, sentAt: '2026-01-20 14:00', recipientGroup: 'Partners', totalRecipients: 45, channel: 'sms', message: 'Thank you for your faithfulness...', deliveryRate: 100, status: 'sent' },
  { id: 3, sentAt: '2026-01-18 09:00', recipientGroup: 'Media', totalRecipients: 12, channel: 'whatsapp', message: 'Reminder: rehearsal at 2pm', deliveryRate: 75, status: 'partially_sent' }
])

const scheduled = ref([
  { id: 1, recipientGroup: 'All Members', message: 'Friday night service at 7pm!', scheduledFor: '2026-01-24T18:00', channel: 'whatsapp' }
])

// Computed
const charCount = computed(() => form.message.length)
const charCountClass = computed(() => {
  if (form.channel !== 'sms') return 'text-muted'
  if (charCount.value > 160) return 'text-danger fw-bold'
  if (charCount.value > 140) return 'text-warning'
  return 'text-muted'
})

const recipientCount = computed(() => {
  if (form.recipientGroup === 'all_members') return 234
  if (form.recipientGroup === 'partnerships') return 45
  if (form.recipientGroup === 'department') {
    const dept = departments.value.find(d => d.id === form.departmentId)
    return dept?.memberCount || 0
  }
  return 0
})

const recipientLabel = computed(() => {
  if (form.recipientGroup === 'all_members') return 'All Members'
  if (form.recipientGroup === 'partnerships') return 'Partners Only'
  if (form.recipientGroup === 'department') {
    const dept = departments.value.find(d => d.id === form.departmentId)
    return dept?.name || 'Department'
  }
  return ''
})

const canSend = computed(() => {
  if (!form.message.trim()) return false
  if (!recipientCount.value) return false
  if (form.recipientGroup === 'department' && !form.departmentId) return false
  if (form.scheduled && (!form.scheduleDate || !form.scheduleTime)) return false
  return true
})

const minDate = computed(() => new Date().toISOString().split('T')[0])
const scheduledCount = computed(() => scheduled.value.length)

// Methods
const notification = reactive({ show: false, type: 'success', message: '' })

function useTemplate(template) {
  form.message = template.message
}

function previewMessage() {
  showPreview.value = true
}

function sendBroadcast() {
  sending.value = true
  setTimeout(() => {
    if (form.scheduled) {
      scheduled.value.push({
        id: scheduled.value.length + 1,
        recipientGroup: recipientLabel.value,
        message: form.message,
        scheduledFor: `${form.scheduleDate}T${form.scheduleTime}`,
        channel: form.channel
      })
      showNotification('success', 'Broadcast scheduled successfully!')
    } else {
      history.value.unshift({
        id: history.value.length + 1,
        sentAt: new Date().toLocaleString(),
        recipientGroup: recipientLabel.value,
        totalRecipients: recipientCount.value,
        channel: form.channel,
        message: form.message,
        deliveryRate: 100,
        status: 'sent'
      })
      showNotification('success', `Message sent to ${recipientCount.value} recipients!`)
    }
    resetForm()
    sending.value = false
    showPreview.value = false
  }, 1500)
}

function resetForm() {
  form.recipientGroup = 'all_members'
  form.departmentId = ''
  form.channel = 'whatsapp'
  form.message = ''
  form.scheduled = false
  form.scheduleDate = ''
  form.scheduleTime = ''
}

function retryFailed(broadcast) {
  showNotification('info', 'Retrying failed deliveries...')
}

function viewBroadcastDetails(broadcast) {
  // Open details modal
}

function editScheduled(item) {
  form.recipientGroup = 'department'
  form.message = item.message
  form.scheduled = true
  activeTab.value = 'compose'
}

function cancelScheduled(item) {
  scheduled.value = scheduled.value.filter(s => s.id !== item.id)
  showNotification('info', 'Scheduled broadcast cancelled')
}

function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
}

function formatDateTime(dateStr) {
  return new Date(dateStr).toLocaleString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function statusColor(status) {
  const colors = { sent: 'success', partially_sent: 'warning', failed: 'danger', scheduled: 'info' }
  return colors[status] || 'secondary'
}

function exportBroadcastHistory() {
  const columns = [
    { key: 'sentAt', header: 'Sent Date/Time', transform: (v) => formatDateForExport(v) },
    { key: 'recipientGroup', header: 'Recipients' },
    { key: 'totalRecipients', header: 'Total Recipients' },
    { key: 'channel', header: 'Channel', transform: (v) => v?.charAt(0).toUpperCase() + v?.slice(1) },
    { key: 'message', header: 'Message' },
    { key: 'deliveryRate', header: 'Delivery Rate (%)', transform: (v) => v + '%' },
    { key: 'status', header: 'Status', transform: (v) => v?.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) }
  ]
  exportToExcel(history.value, columns, `Broadcast_History_${new Date().toISOString().split('T')[0]}`)
  showNotification('success', 'Broadcast history exported successfully')
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.channel-card {
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
  min-width: 120px;
}

.channel-card:hover {
  border-color: #dee2e6;
}

.channel-card.selected {
  border-color: var(--cui-primary);
  background: rgba(13, 110, 253, 0.05);
}

.cursor-pointer {
  cursor: pointer;
}

.preview-phone {
  background: #e5ddd5;
  border-radius: 12px;
  padding: 16px;
  max-width: 320px;
  margin: 0 auto;
}

.preview-header {
  background: #075e54;
  color: white;
  padding: 8px 12px;
  border-radius: 8px 8px 0 0;
  font-weight: 600;
  margin: -16px -16px 12px -16px;
}

.preview-bubble {
  background: white;
  padding: 12px;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  white-space: pre-wrap;
}

.preview-meta {
  margin-top: 12px;
  font-size: 0.85rem;
  color: #666;
  text-align: center;
}
</style>
