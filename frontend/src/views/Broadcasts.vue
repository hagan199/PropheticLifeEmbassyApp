<template>
  <div class="page-wrap">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2 class="title">Broadcast Messaging</h2>
        <Breadcrumbs />
        <p class="text-muted small mb-0">
          Send WhatsApp/SMS messages to members, partners, and departments
        </p>
      </div>
      <CButton color="light" @click="exportBroadcastHistory">
        <i class="bi bi-download me-1"></i> Export History
      </CButton>
    </div>

    <!-- KPI Summary Cards -->
    <div class="kpi-grid mb-4">
      <div class="kpi-card" style="--delay: 0s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)">
          <i class="bi bi-send-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Total Sent</div>
          <div class="kpi-value">{{ history.length }}</div>
          <div class="kpi-sublabel">All broadcasts</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.1s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%)">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Delivery Rate</div>
          <div class="kpi-value">{{ averageDeliveryRate }}%</div>
          <div class="kpi-sublabel">Average success</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.2s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%)">
          <i class="bi bi-people-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Recipients</div>
          <div class="kpi-value">{{ totalRecipients }}</div>
          <div class="kpi-sublabel">Messages delivered</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.3s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Scheduled</div>
          <div class="kpi-value">{{ scheduledCount }}</div>
          <div class="kpi-sublabel">Pending sends</div>
        </div>
      </div>
    </div>

    <!-- Compose Section -->
    <CCard class="compose-card mb-4">
      <CCardHeader class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
          <div class="header-icon-box bg-primary-subtle text-primary">
            <i class="bi bi-megaphone-fill"></i>
          </div>
          <div>
            <h3 class="md-title-medium mb-0">New Broadcast</h3>
            <p class="text-muted small mb-0">Compose and send messages to your audience</p>
          </div>
        </div>
        <CButton v-if="!showComposer" color="primary" variant="ghost" @click="showComposer = true">
          <i class="bi bi-plus-lg me-1"></i> Compose
        </CButton>
        <CButton v-else color="light" size="sm" @click="collapseComposer">
          <i class="bi bi-chevron-up"></i>
        </CButton>
      </CCardHeader>

      <CCardBody v-if="showComposer">
        <CForm @submit.prevent="sendBroadcast">
          <!-- Step 1: Recipients -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number" :class="{ complete: recipientCount > 0 }">
                <i v-if="recipientCount > 0" class="bi bi-check-lg"></i>
                <span v-else>1</span>
              </div>
              <div class="step-title">
                <h4>Select Recipients</h4>
                <p class="text-muted small mb-0">Choose who will receive this message</p>
              </div>
            </div>
            <div class="step-content">
              <div class="recipient-options">
                <div class="recipient-option" :class="{ selected: form.recipientGroup === 'all_members' }"
                  @click="form.recipientGroup = 'all_members'">
                  <i class="bi bi-people-fill"></i>
                  <div class="option-content">
                    <div class="option-label">All Members</div>
                    <div class="option-count">234 recipients</div>
                  </div>
                  <div v-if="form.recipientGroup === 'all_members'" class="option-check">
                    <i class="bi bi-check-circle-fill text-primary"></i>
                  </div>
                </div>

                <div class="recipient-option" :class="{ selected: form.recipientGroup === 'partnerships' }"
                  @click="form.recipientGroup = 'partnerships'">
                  <i class="bi bi-star-fill text-warning"></i>
                  <div class="option-content">
                    <div class="option-label">Partners Only</div>
                    <div class="option-count">45 recipients</div>
                  </div>
                  <div v-if="form.recipientGroup === 'partnerships'" class="option-check">
                    <i class="bi bi-check-circle-fill text-primary"></i>
                  </div>
                </div>

                <div class="recipient-option" :class="{ selected: form.recipientGroup === 'department' }"
                  @click="form.recipientGroup = 'department'">
                  <i class="bi bi-building"></i>
                  <div class="option-content">
                    <div class="option-label">Specific Department</div>
                    <div class="option-count">Select below</div>
                  </div>
                  <div v-if="form.recipientGroup === 'department'" class="option-check">
                    <i class="bi bi-check-circle-fill text-primary"></i>
                  </div>
                </div>
              </div>

              <!-- Department Selector -->
              <div v-if="form.recipientGroup === 'department'" class="mt-3 animate-fadeIn">
                <CFormSelect v-model="form.departmentId" size="lg" class="department-select">
                  <option value="">Select department...</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                    {{ dept.name }} ({{ dept.memberCount || 0 }} members)
                  </option>
                </CFormSelect>
              </div>

              <!-- Recipient Summary -->
              <div v-if="recipientCount > 0" class="recipient-summary animate-fadeIn">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                <span>
                  This message will be sent to <strong>{{ recipientCount }}</strong>
                  {{ recipientCount === 1 ? 'person' : 'people' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Step 2: Channel -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number" :class="{ complete: !!form.channel }">
                <i v-if="form.channel" class="bi bi-check-lg"></i>
                <span v-else>2</span>
              </div>
              <div class="step-title">
                <h4>Choose Channel</h4>
                <p class="text-muted small mb-0">Select WhatsApp or SMS</p>
              </div>
            </div>
            <div class="step-content">
              <div class="channel-grid">
                <div class="channel-option" :class="{ selected: form.channel === 'whatsapp' }"
                  @click="form.channel = 'whatsapp'">
                  <div class="channel-icon-lg">
                    <i class="bi bi-whatsapp text-success"></i>
                  </div>
                  <h5 class="channel-name">WhatsApp</h5>
                  <div class="channel-features">
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-success"></i>
                      <span>Free & Unlimited</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-success"></i>
                      <span>Rich Formatting</span>
                    </div>
                  </div>
                  <div class="channel-cost">
                    <span class="cost-label">Cost:</span>
                    <span class="cost-value text-success fw-bold">FREE</span>
                  </div>
                </div>

                <div class="channel-option" :class="{ selected: form.channel === 'sms' }" @click="form.channel = 'sms'">
                  <div class="channel-icon-lg">
                    <i class="bi bi-chat-dots text-primary"></i>
                  </div>
                  <h5 class="channel-name">SMS</h5>
                  <div class="channel-features">
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-primary"></i>
                      <span>Universal Reach</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-info-circle-fill text-warning"></i>
                      <span>160 char limit</span>
                    </div>
                  </div>
                  <div class="channel-cost">
                    <span class="cost-label">Cost:</span>
                    <span class="cost-value text-primary fw-bold">~{{ estimatedCost }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Message -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number" :class="{ complete: form.message.trim().length > 0 }">
                <i v-if="form.message.trim().length > 0" class="bi bi-check-lg"></i>
                <span v-else>3</span>
              </div>
              <div class="step-title">
                <h4>Compose Message</h4>
                <p class="text-muted small mb-0">Write your message or use a template</p>
              </div>
            </div>
            <div class="step-content">
              <!-- Templates -->
              <div class="templates-bar mb-3">
                <label class="small fw-semibold text-muted mb-2 d-block">
                  <i class="bi bi-layout-text-sidebar me-1"></i> Quick Templates
                </label>
                <div class="template-pills">
                  <button v-for="template in templates" :key="template.id" type="button" class="template-pill"
                    @click="useTemplate(template)">
                    <i class="bi bi-file-text"></i>
                    {{ template.name }}
                  </button>
                </div>
              </div>

              <!-- Message Editor -->
              <CFormTextarea v-model="form.message" rows="6" placeholder="Type your message here..."
                :class="{ 'is-invalid': form.channel === 'sms' && charCount > 160 }" class="message-input" />

              <!-- Character Counter -->
              <div class="d-flex justify-content-between align-items-center mt-2">
                <div :class="['small', charCountClass]">
                  <i class="bi bi-type me-1"></i>
                  {{ charCount }} / {{ form.channel === 'sms' ? '160' : '∞' }} characters
                </div>
                <div v-if="form.channel === 'sms' && charCount > 160" class="text-warning small">
                  <i class="bi bi-exclamation-triangle me-1"></i>
                  Will be sent as {{ Math.ceil(charCount / 160) }} messages
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Schedule (Optional) -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number optional">
                <i class="bi bi-clock"></i>
              </div>
              <div class="step-title">
                <h4>Schedule Delivery <span class="text-muted small">(Optional)</span></h4>
                <p class="text-muted small mb-0">Send now or schedule for later</p>
              </div>
            </div>
            <div class="step-content">
              <CFormCheck v-model="form.scheduled" label="Schedule for a specific date and time"
                class="schedule-toggle mb-3" />

              <div v-if="form.scheduled" class="schedule-inputs animate-fadeIn">
                <CRow class="g-3">
                  <CCol md="6">
                    <label class="form-label small fw-semibold">Date</label>
                    <CFormInput v-model="form.scheduleDate" type="date" :min="minDate" size="lg" />
                  </CCol>
                  <CCol md="6">
                    <label class="form-label small fw-semibold">Time</label>
                    <CFormInput v-model="form.scheduleTime" type="time" size="lg" />
                  </CCol>
                </CRow>
                <div v-if="form.scheduleDate && form.scheduleTime" class="schedule-preview mt-3">
                  <i class="bi bi-calendar-check text-primary me-2"></i>
                  <span>
                    Will be sent on <strong>{{ formatScheduledDateTime }}</strong>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="compose-actions">
            <CButton color="light" size="lg" :disabled="sending" @click="resetForm">
              <i class="bi bi-x-circle me-2"></i>
              Cancel
            </CButton>

            <CButton color="light" size="lg" variant="outline" :disabled="!canPreview || sending"
              @click="previewMessage">
              <i class="bi bi-eye me-2"></i>
              Preview
            </CButton>

            <CButton color="primary" size="lg" type="submit" :disabled="!canSend || sending" class="ms-auto">
              <CSpinner v-if="sending" size="sm" class="me-2" />
              <i v-else :class="form.scheduled ? 'bi bi-calendar-plus' : 'bi bi-send-fill'" class="me-2"></i>
              {{ form.scheduled ? 'Schedule Broadcast' : 'Send Now' }}
            </CButton>
          </div>
        </CForm>
      </CCardBody>
    </CCard>

    <!-- Recent Broadcasts -->
    <CCard>
      <CCardHeader class="d-flex justify-content-between align-items-center">
        <h3 class="md-title-medium mb-0">Recent Broadcasts</h3>
        <div class="d-flex gap-2">
          <CButton color="light" size="sm" @click="activeTab = 'history'">
            <i class="bi bi-clock-history me-1"></i> View All ({{ history.length }})
          </CButton>
          <CButton v-if="scheduledCount > 0" color="light" size="sm" @click="activeTab = 'scheduled'">
            <i class="bi bi-calendar-event me-1"></i> Scheduled ({{ scheduledCount }})
          </CButton>
        </div>
      </CCardHeader>

      <CCardBody class="p-0">
        <div v-if="history.length > 0" class="broadcasts-list">
          <div v-for="broadcast in recentBroadcasts" :key="broadcast.id" class="broadcast-item">
            <div class="broadcast-icon" :class="`broadcast-icon-${broadcast.status}`">
              <i :class="getChannelIcon(broadcast.channel)"></i>
            </div>

            <div class="broadcast-content">
              <div class="broadcast-header">
                <h5 class="broadcast-title">{{ truncate(broadcast.message, 60) }}</h5>
                <CBadge :color="statusColor(broadcast.status)" class="status-badge">
                  {{ broadcast.status.replace('_', ' ') }}
                </CBadge>
              </div>
              <div class="broadcast-meta">
                <span class="meta-item">
                  <i class="bi bi-people"></i>
                  {{ broadcast.recipientGroup }}
                </span>
                <span class="meta-divider">•</span>
                <span class="meta-item">
                  <i class="bi bi-person-lines-fill"></i>
                  {{ broadcast.totalRecipients }} recipients
                </span>
                <span class="meta-divider">•</span>
                <span class="meta-item text-muted">
                  <i class="bi bi-clock"></i>
                  {{ formatRelativeTime(broadcast.sentAt) }}
                </span>
              </div>
            </div>

            <div class="broadcast-stats">
              <div class="delivery-progress">
                <div class="progress-info">
                  <span class="progress-label">Delivery</span>
                  <span class="progress-value">{{ broadcast.deliveryRate }}%</span>
                </div>
                <CProgress :value="broadcast.deliveryRate" height="6" :color="getProgressColor(broadcast.deliveryRate)"
                  class="mb-2" />
                <div class="delivery-counts">
                  <span class="count-success">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ Math.round((broadcast.totalRecipients * broadcast.deliveryRate) / 100) }}
                  </span>
                  <span v-if="broadcast.deliveryRate < 100" class="count-failed">
                    <i class="bi bi-x-circle-fill"></i>
                    {{
                      broadcast.totalRecipients -
                      Math.round((broadcast.totalRecipients * broadcast.deliveryRate) / 100)
                    }}
                  </span>
                </div>
              </div>
            </div>

            <div class="broadcast-actions">
              <CButton v-if="broadcast.status === 'partially_sent'" color="warning" size="sm" variant="ghost"
                title="Retry failed deliveries" @click="retryFailed(broadcast)">
                <i class="bi bi-arrow-clockwise"></i>
              </CButton>
              <CButton color="light" size="sm" variant="ghost" title="View details"
                @click="viewBroadcastDetails(broadcast)">
                <i class="bi bi-eye"></i>
              </CButton>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <i class="bi bi-inbox empty-icon"></i>
          <h4 class="empty-title">No broadcasts yet</h4>
          <p class="empty-text">Start by composing your first broadcast message</p>
          <CButton color="primary" @click="showComposer = true">
            <i class="bi bi-plus-lg me-2"></i>
            Create Broadcast
          </CButton>
        </div>
      </CCardBody>
    </CCard>

    <!-- History Modal -->
    <Teleport to="body">
      <CModal v-model:visible="showHistoryModal" size="xl">
        <CModalHeader>
          <CModalTitle>Broadcast History</CModalTitle>
        </CModalHeader>
        <CModalBody>
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
                <i :class="h.channel === 'whatsapp'
                    ? 'bi bi-whatsapp text-success'
                    : 'bi bi-chat-dots text-primary'
                  "></i>
                {{ h.channel }}
              </CTableDataCell>
              <CTableDataCell>
                <div class="text-truncate" style="max-width: 200px">{{ h.message }}</div>
              </CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center gap-2">
                  <CProgress :value="h.deliveryRate" height="6" style="width: 60px"
                    :color="getProgressColor(h.deliveryRate)" />
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
        </CModalBody>
      </CModal>
    </Teleport>

    <!-- Scheduled Modal -->
    <Teleport to="body">
      <CModal v-model:visible="showScheduledModal">
        <CModalHeader>
          <CModalTitle>Scheduled Broadcasts</CModalTitle>
        </CModalHeader>
        <CModalBody>
        <div v-if="!scheduled.length" class="text-center py-5 text-muted">
          <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
          No scheduled broadcasts
        </div>
        <CListGroup v-else flush>
          <CListGroupItem v-for="s in scheduled" :key="s.id" class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">{{ s.recipientGroup }}</div>
              <div class="text-muted small text-truncate" style="max-width: 300px">
                {{ s.message }}
              </div>
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
        </CModalBody>
      </CModal>
    </Teleport>

    <!-- Preview Modal -->
    <Teleport to="body">
      <CModal v-model:visible="showPreview" size="md" alignment="center">
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
            {{ form.message || 'Your message will appear here...' }}
          </div>
          <div class="preview-meta">To: {{ recipientLabel }} ({{ recipientCount }})</div>
        </div>
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" @click="showPreview = false">Close</CButton>
          <CButton
            color="primary"
            @click="
              showPreview = false;
              sendBroadcast();
            "
          >
            {{ form.scheduled ? 'Schedule' : 'Send Now' }}
          </CButton>
        </CModalFooter>
      </CModal>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import {
  CCard,
  CCardBody,
  CCardHeader,
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
  CFormInput,
  CFormSelect,
  CFormTextarea,
  CFormCheck,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CListGroup,
  CListGroupItem,
  CForm,
  CProgress,
  CSpinner,
} from '@coreui/vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';
import { exportToExcel, formatDateForExport } from '../utils/export.js';
import { useToast } from '../composables/useToast';

// State
const activeTab = ref('compose');
const showComposer = ref(true);
const sending = ref(false);
const showPreview = ref(false);
const showHistoryModal = computed({
  get: () => activeTab.value === 'history',
  set: (v) => { if (!v) activeTab.value = 'compose'; },
});
const showScheduledModal = computed({
  get: () => activeTab.value === 'scheduled',
  set: (v) => { if (!v) activeTab.value = 'compose'; },
});

// Form
const form = reactive({
  recipientGroup: 'all_members',
  departmentId: '',
  channel: 'whatsapp',
  message: '',
  scheduled: false,
  scheduleDate: '',
  scheduleTime: '',
});

// Data
const departments = ref([
  { id: 1, name: 'Media', memberCount: 12 },
  { id: 2, name: 'Prayer Team', memberCount: 18 },
  { id: 3, name: 'Ushering', memberCount: 24 },
  { id: 4, name: 'Choir', memberCount: 32 },
]);

const templates = ref([
  {
    id: 1,
    name: 'Service Reminder',
    message: 'Join us this Sunday at 9am for an uplifting service!',
  },
  {
    id: 2,
    name: 'Event Announcement',
    message: 'Special event coming up! Mark your calendars for...',
  },
  { id: 3, name: 'Prayer Request', message: 'Please keep our church family in your prayers...' },
  {
    id: 4,
    name: 'Thanksgiving',
    message: 'We are grateful for your continued support and partnership...',
  },
]);

const history = ref([
  {
    id: 1,
    sentAt: '2026-02-08 10:30',
    recipientGroup: 'All Members',
    totalRecipients: 234,
    channel: 'whatsapp',
    message: 'Join us this Sunday at 9am!',
    deliveryRate: 98,
    status: 'sent',
  },
  {
    id: 2,
    sentAt: '2026-02-07 14:00',
    recipientGroup: 'Partners',
    totalRecipients: 45,
    channel: 'sms',
    message: 'Thank you for your faithfulness...',
    deliveryRate: 100,
    status: 'sent',
  },
  {
    id: 3,
    sentAt: '2026-02-06 09:00',
    recipientGroup: 'Media',
    totalRecipients: 12,
    channel: 'whatsapp',
    message: 'Reminder: rehearsal at 2pm',
    deliveryRate: 75,
    status: 'partially_sent',
  },
]);

const scheduled = ref([
  {
    id: 1,
    recipientGroup: 'All Members',
    message: 'Friday night service at 7pm!',
    scheduledFor: '2026-02-11T18:00',
    channel: 'whatsapp',
  },
]);

// Toast
const toast = useToast();

// Computed
const charCount = computed(() => form.message.length);

const charCountClass = computed(() => {
  if (form.channel !== 'sms') return 'text-muted';
  if (charCount.value > 160) return 'text-danger fw-bold';
  if (charCount.value > 140) return 'text-warning';
  return 'text-muted';
});

const recipientCount = computed(() => {
  if (form.recipientGroup === 'all_members') return 234;
  if (form.recipientGroup === 'partnerships') return 45;
  if (form.recipientGroup === 'department') {
    const dept = departments.value.find(d => d.id === form.departmentId);
    return dept?.memberCount || 0;
  }
  return 0;
});

const recipientLabel = computed(() => {
  if (form.recipientGroup === 'all_members') return 'All Members';
  if (form.recipientGroup === 'partnerships') return 'Partners Only';
  if (form.recipientGroup === 'department') {
    const dept = departments.value.find(d => d.id === form.departmentId);
    return dept?.name || 'Department';
  }
  return '';
});

const estimatedCost = computed(() => {
  if (form.channel === 'whatsapp') return 'FREE';
  const smsParts = Math.ceil(charCount.value / 160) || 1;
  const cost = recipientCount.value * smsParts * 0.05;
  return `$${cost.toFixed(2)}`;
});

const canSend = computed(() => {
  if (!form.message.trim()) return false;
  if (!recipientCount.value) return false;
  if (form.recipientGroup === 'department' && !form.departmentId) return false;
  if (form.scheduled && (!form.scheduleDate || !form.scheduleTime)) return false;
  return true;
});

const canPreview = computed(() => {
  return form.message.trim().length > 0 && recipientCount.value > 0;
});

const minDate = computed(() => new Date().toISOString().split('T')[0]);
const scheduledCount = computed(() => scheduled.value.length);

const averageDeliveryRate = computed(() => {
  if (history.value.length === 0) return 0;
  const sum = history.value.reduce((acc, h) => acc + h.deliveryRate, 0);
  return Math.round(sum / history.value.length);
});

const totalRecipients = computed(() => {
  return history.value.reduce(
    (acc, h) => acc + Math.round((h.totalRecipients * h.deliveryRate) / 100),
    0
  );
});

const recentBroadcasts = computed(() => {
  return history.value.slice(0, 3);
});

const formatScheduledDateTime = computed(() => {
  if (!form.scheduleDate || !form.scheduleTime) return '';
  const date = new Date(`${form.scheduleDate}T${form.scheduleTime}`);
  return date.toLocaleString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});

// Methods
function useTemplate(template) {
  form.message = template.message;
  toast.success(`Template "${template.name}" applied`);
}

function previewMessage() {
  showPreview.value = true;
}

function sendBroadcast() {
  sending.value = true;
  setTimeout(() => {
    if (form.scheduled) {
      scheduled.value.push({
        id: scheduled.value.length + 1,
        recipientGroup: recipientLabel.value,
        message: form.message,
        scheduledFor: `${form.scheduleDate}T${form.scheduleTime}`,
        channel: form.channel,
      });
      toast.success('Broadcast scheduled successfully!');
    } else {
      history.value.unshift({
        id: history.value.length + 1,
        sentAt: new Date().toLocaleString('en-CA', { hour12: false }).replace(',', ''),
        recipientGroup: recipientLabel.value,
        totalRecipients: recipientCount.value,
        channel: form.channel,
        message: form.message,
        deliveryRate: 100,
        status: 'sent',
      });
      toast.success(`Message sent to ${recipientCount.value} recipients!`);
    }
    resetForm();
    sending.value = false;
    showPreview.value = false;
    showComposer.value = false;
  }, 1500);
}

function resetForm() {
  form.recipientGroup = 'all_members';
  form.departmentId = '';
  form.channel = 'whatsapp';
  form.message = '';
  form.scheduled = false;
  form.scheduleDate = '';
  form.scheduleTime = '';
}

function collapseComposer() {
  showComposer.value = false;
}

function retryFailed(_broadcast) {
  toast.info('Retrying failed deliveries...');
}

function viewBroadcastDetails(_broadcast) {
  toast.info('Broadcast details view coming soon!');
}

function editScheduled(item) {
  form.recipientGroup = 'all_members';
  form.message = item.message;
  form.scheduled = true;
  const dt = new Date(item.scheduledFor);
  form.scheduleDate = dt.toISOString().split('T')[0];
  form.scheduleTime = dt.toTimeString().slice(0, 5);
  activeTab.value = 'compose';
  showComposer.value = true;
  toast.info('Editing scheduled broadcast');
}

function cancelScheduled(item) {
  scheduled.value = scheduled.value.filter(s => s.id !== item.id);
  toast.info('Scheduled broadcast cancelled');
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

function formatDateTime(dateStr) {
  return new Date(dateStr).toLocaleString('en-GB', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatRelativeTime(date) {
  const now = new Date();
  const then = new Date(date);
  const diffMs = now - then;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return then.toLocaleDateString();
}

function truncate(str, length) {
  if (!str) return '';
  return str.length > length ? str.substring(0, length) + '...' : str;
}

function statusColor(status) {
  const colors = {
    sent: 'success',
    partially_sent: 'warning',
    failed: 'danger',
    scheduled: 'info',
  };
  return colors[status] || 'secondary';
}

function getChannelIcon(channel) {
  const icons = {
    whatsapp: 'bi bi-whatsapp',
    sms: 'bi bi-chat-dots',
  };
  return icons[channel] || 'bi bi-chat';
}

function getProgressColor(rate) {
  if (rate >= 95) return 'success';
  if (rate >= 80) return 'warning';
  return 'danger';
}

function exportBroadcastHistory() {
  const columns = [
    { key: 'sentAt', header: 'Sent Date/Time', transform: v => formatDateForExport(v) },
    { key: 'recipientGroup', header: 'Recipients' },
    { key: 'totalRecipients', header: 'Total Recipients' },
    { key: 'channel', header: 'Channel', transform: v => v?.charAt(0).toUpperCase() + v?.slice(1) },
    { key: 'message', header: 'Message' },
    { key: 'deliveryRate', header: 'Delivery Rate (%)', transform: v => v + '%' },
    {
      key: 'status',
      header: 'Status',
      transform: v => v?.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()),
    },
  ];
  exportToExcel(
    history.value,
    columns,
    `Broadcast_History_${new Date().toISOString().split('T')[0]}`
  );
  toast.success('Broadcast history exported successfully');
}
</script>

<style scoped>
/* Import the comprehensive styles */
@import '../assets/styles/broadcasts.css';

/* Additional page-specific overrides */
.page-wrap {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.md-title-medium {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.bg-primary-subtle {
  background-color: #eff6ff !important;
}

.header-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .compose-actions {
    flex-direction: column;
  }

  .compose-actions .ms-auto {
    margin-left: 0 !important;
    margin-top: 0.5rem;
  }
}
</style>
