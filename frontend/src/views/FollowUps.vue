<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Follow-ups</h2>
        <Breadcrumbs />
        <div class="text-muted">Track visitor engagement and outreach</div>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" @click="exportVisitors">
          <i class="bi bi-file-earmark-excel me-1"></i> Export
        </CButton>
        <CButton color="primary" @click="openAddVisitor">
          <i class="bi bi-person-plus me-1"></i> Add Visitor
        </CButton>
      </div>
    </div>

    <!-- Stats Cards -->
    <CRow class="g-4 mb-4">
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm" @click="filterByStatus('')">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Total Visitors</div>
                <div class="fs-3 fw-bold">{{ stats.total }}</div>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-people"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm" @click="filterByStatus('not_contacted')">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Not Contacted</div>
                <div class="fs-3 fw-bold text-danger">{{ stats.notContacted }}</div>
              </div>
              <div class="stat-icon bg-danger-subtle text-danger">
                <i class="bi bi-exclamation-circle"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm" @click="filterByStatus('engaged')">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Engaged</div>
                <div class="fs-3 fw-bold text-warning">{{ stats.engaged }}</div>
              </div>
              <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-chat-heart"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm" @click="filterByStatus('converted')">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Converted</div>
                <div class="fs-3 fw-bold text-success">{{ stats.converted }}</div>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-person-check"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Due This Week Alert -->
    <CAlert v-if="dueThisWeek.length" color="warning" class="mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <i class="bi bi-bell me-2"></i>
          <strong>{{ dueThisWeek.length }} follow-ups due this week</strong>
          <span class="ms-2 text-muted">{{ overdueCount }} overdue</span>
        </div>
        <CButton color="warning" size="sm" @click="showDueList = true"> View Due List </CButton>
      </div>
    </CAlert>

    <CRow class="g-4">
      <!-- Visitors List -->
      <CCol lg="7">
        <CCard>
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Visitors</div>
            <div class="d-flex gap-2">
              <CFormInput v-model="search" placeholder="Search..." style="width: 200px" />
              <CFormSelect v-model="statusFilter" style="width: 150px">
                <option value="">All Status</option>
                <option value="not_contacted">Not Contacted</option>
                <option value="contacted">Contacted</option>
                <option value="engaged">Engaged</option>
                <option value="converted">Converted</option>
              </CFormSelect>
            </div>
          </CCardHeader>
          <CCardBody class="p-0">
            <CListGroup flush>
              <CListGroupItem
                v-for="v in filteredVisitors"
                :key="v.id"
                class="visitor-item d-flex justify-content-between align-items-start py-3"
                :class="{ active: selectedVisitor?.id === v.id }"
                @click="selectVisitor(v)"
              >
                <div class="d-flex">
                  <CAvatar :color="statusAvatarColor(v.status)" text-color="white" class="me-3">
                    {{ v.name.charAt(0) }}
                  </CAvatar>
                  <div>
                    <div class="fw-semibold">{{ v.name }}</div>
                    <div class="text-muted small">{{ v.phone }}</div>
                    <div class="mt-1">
                      <CBadge :color="statusColor(v.status)" size="sm">{{
                        statusLabel(v.status)
                      }}</CBadge>
                      <CBadge color="light" text-color="dark" size="sm" class="ms-1">{{
                        v.source
                      }}</CBadge>
                    </div>
                  </div>
                </div>
                <div class="text-end">
                  <div class="text-muted small">{{ formatDate(v.firstVisitDate) }}</div>
                  <div
                    v-if="v.nextFollowUp"
                    class="small"
                    :class="isOverdue(v.nextFollowUp) ? 'text-danger' : 'text-muted'"
                  >
                    <i class="bi bi-clock me-1"></i>{{ formatDate(v.nextFollowUp) }}
                  </div>
                </div>
              </CListGroupItem>
            </CListGroup>
            <div v-if="!filteredVisitors.length" class="text-center py-5 text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              No visitors found
            </div>
          </CCardBody>
        </CCard>
      </CCol>

      <!-- Detail Panel -->
      <CCol lg="5">
        <CCard v-if="selectedVisitor" class="sticky-top" style="top: 20px">
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Visitor Details</div>
            <CButton color="light" size="sm" @click="selectedVisitor = null">
              <i class="bi bi-x"></i>
            </CButton>
          </CCardHeader>
          <CCardBody>
            <!-- Visitor Info -->
            <div class="text-center mb-4">
              <CAvatar
                :color="statusAvatarColor(selectedVisitor.status)"
                text-color="white"
                size="xl"
              >
                {{ selectedVisitor.name.charAt(0) }}
              </CAvatar>
              <h5 class="mt-2 mb-1">{{ selectedVisitor.name }}</h5>
              <div class="text-muted">{{ selectedVisitor.phone }}</div>
              <CBadge :color="statusColor(selectedVisitor.status)" class="mt-2">
                {{ statusLabel(selectedVisitor.status) }}
              </CBadge>
            </div>

            <CRow class="g-3 mb-4">
              <CCol xs="6">
                <div class="text-muted small">Source</div>
                <div class="fw-semibold">{{ selectedVisitor.source }}</div>
              </CCol>
              <CCol xs="6">
                <div class="text-muted small">First Visit</div>
                <div class="fw-semibold">{{ formatDate(selectedVisitor.firstVisitDate) }}</div>
              </CCol>
              <CCol v-if="selectedVisitor.interests?.length" xs="12">
                <div class="text-muted small">Interests</div>
                <div>
                  <CBadge
                    v-for="i in selectedVisitor.interests"
                    :key="i"
                    color="info"
                    class="me-1"
                    >{{ i }}</CBadge
                  >
                </div>
              </CCol>
              <CCol v-if="selectedVisitor.notes" xs="12">
                <div class="text-muted small">Notes</div>
                <div>{{ selectedVisitor.notes }}</div>
              </CCol>
            </CRow>

            <!-- Quick Actions -->
            <div class="d-grid gap-2 mb-4">
              <CButton color="primary" @click="openFollowUpModal">
                <i class="bi bi-plus-circle me-1"></i> Log Follow-up
              </CButton>
              <div class="d-flex gap-2">
                <CButton color="success" class="flex-fill" @click="callVisitor">
                  <i class="bi bi-telephone"></i>
                </CButton>
                <CButton color="success" class="flex-fill" @click="whatsappVisitor">
                  <i class="bi bi-whatsapp"></i>
                </CButton>
                <CButton color="primary" class="flex-fill" @click="smsVisitor">
                  <i class="bi bi-chat-dots"></i>
                </CButton>
              </div>
              <CButton
                v-if="selectedVisitor.status === 'engaged'"
                color="success"
                variant="outline"
                @click="convertToMember"
              >
                <i class="bi bi-person-check me-1"></i> Convert to Member
              </CButton>
            </div>

            <!-- Follow-up History -->
            <div class="fw-semibold mb-2">Follow-up History</div>
            <div v-if="!selectedVisitor.followUps?.length" class="text-muted small">
              No follow-ups logged yet
            </div>
            <div v-else class="timeline">
              <div v-for="f in selectedVisitor.followUps" :key="f.id" class="timeline-item">
                <div class="timeline-marker" :class="'bg-' + methodColor(f.method)"></div>
                <div class="timeline-content">
                  <div class="d-flex justify-content-between">
                    <CBadge :color="methodColor(f.method)" size="sm">
                      <i :class="methodIcon(f.method)" class="me-1"></i>{{ f.method }}
                    </CBadge>
                    <span class="text-muted small">{{ formatDate(f.date) }}</span>
                  </div>
                  <div class="mt-1">{{ f.notes }}</div>
                  <div v-if="f.statusAfter" class="text-muted small mt-1">
                    Status → {{ statusLabel(f.statusAfter) }}
                  </div>
                </div>
              </div>
            </div>
          </CCardBody>
        </CCard>

        <CCard v-else>
          <CCardBody class="text-center py-5 text-muted">
            <i class="bi bi-hand-index fs-1 d-block mb-2"></i>
            Select a visitor to view details
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Add Visitor Modal -->
    <Teleport to="body">
      <CModal :visible="showAddVisitor" size="lg" @close="showAddVisitor = false">
        <CModalHeader>
          <CModalTitle>Add New Visitor</CModalTitle>
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
              <CFormLabel>Source <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="visitorForm.source">
                <option value="">Select...</option>
                <option value="Friend">Friend</option>
                <option value="Social Media">Social Media</option>
                <option value="Walk-in">Walk-in</option>
                <option value="Other">Other</option>
              </CFormSelect>
            </CCol>
            <CCol md="6">
              <CFormLabel>First Visit Date</CFormLabel>
              <CFormInput v-model="visitorForm.firstVisitDate" type="date" />
            </CCol>
            <CCol md="6">
              <CFormLabel>Ministry Interests</CFormLabel>
              <CFormSelect v-model="visitorForm.interests" multiple>
                <option value="Youth">Youth</option>
                <option value="Choir">Choir</option>
                <option value="Media">Media</option>
                <option value="Prayer">Prayer</option>
                <option value="Ushering">Ushering</option>
              </CFormSelect>
            </CCol>
            <CCol md="12">
              <CFormLabel>Notes</CFormLabel>
              <CFormTextarea v-model="visitorForm.notes" rows="3" />
            </CCol>
          </CRow>
        </CForm>
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" @click="showAddVisitor = false">Cancel</CButton>
          <CButton color="primary" @click="saveVisitor">Save Visitor</CButton>
        </CModalFooter>
      </CModal>
    </Teleport>

    <!-- Log Follow-up Modal -->
    <Teleport to="body">
      <CModal :visible="showFollowUpModal" @close="showFollowUpModal = false">
        <CModalHeader>
          <CModalTitle>Log Follow-up</CModalTitle>
        </CModalHeader>
        <CModalBody>
        <CForm>
          <div class="mb-3">
            <CFormLabel>Contact Method <span class="text-danger">*</span></CFormLabel>
            <div class="d-flex gap-2 flex-wrap">
              <CFormCheck
                id="m-whatsapp"
                v-model="followUpForm.method"
                type="radio"
                name="method"
                value="WhatsApp"
                label="WhatsApp"
                inline
              />
              <CFormCheck
                id="m-sms"
                v-model="followUpForm.method"
                type="radio"
                name="method"
                value="SMS"
                label="SMS"
                inline
              />
              <CFormCheck
                id="m-call"
                v-model="followUpForm.method"
                type="radio"
                name="method"
                value="Call"
                label="Call"
                inline
              />
              <CFormCheck
                id="m-person"
                v-model="followUpForm.method"
                type="radio"
                name="method"
                value="In-Person"
                label="In-Person"
                inline
              />
            </div>
          </div>
          <div class="mb-3">
            <CFormLabel>Notes <span class="text-danger">*</span></CFormLabel>
            <CFormTextarea
              v-model="followUpForm.notes"
              rows="3"
              placeholder="What was discussed?"
            />
          </div>
          <div class="mb-3">
            <CFormLabel>Update Status</CFormLabel>
            <CFormSelect v-model="followUpForm.statusAfter">
              <option value="">No change</option>
              <option value="contacted">Contacted</option>
              <option value="engaged">Engaged</option>
              <option value="converted">Converted</option>
            </CFormSelect>
          </div>
          <div class="mb-3">
            <CFormLabel>Next Follow-up Date</CFormLabel>
            <CFormInput v-model="followUpForm.nextDate" type="date" />
          </div>
          </CForm>
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" @click="showFollowUpModal = false">Cancel</CButton>
          <CButton color="primary" @click="saveFollowUp">Save Follow-up</CButton>
        </CModalFooter>
      </CModal>
    </Teleport>

    <!-- Due List Modal -->
    <Teleport to="body">
      <CModal :visible="showDueList" size="lg" @close="showDueList = false">
        <CModalHeader>
          <CModalTitle>Follow-ups Due This Week</CModalTitle>
        </CModalHeader>
        <CModalBody>
        <CTable hover responsive>
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Visitor</CTableHeaderCell>
              <CTableHeaderCell>Due Date</CTableHeaderCell>
              <CTableHeaderCell>Last Contact</CTableHeaderCell>
              <CTableHeaderCell>Status</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow
              v-for="v in dueThisWeek"
              :key="v.id"
              :class="{ 'table-danger': isOverdue(v.nextFollowUp) }"
            >
              <CTableDataCell>
                <div class="fw-semibold">{{ v.name }}</div>
                <div class="text-muted small">{{ v.phone }}</div>
              </CTableDataCell>
              <CTableDataCell>
                <span :class="isOverdue(v.nextFollowUp) ? 'text-danger fw-bold' : ''">
                  {{ formatDate(v.nextFollowUp) }}
                </span>
              </CTableDataCell>
              <CTableDataCell>{{ v.lastContactMethod || '—' }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="statusColor(v.status)">{{ statusLabel(v.status) }}</CBadge>
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="success" size="sm" class="me-1" @click="selectAndCall(v)">
                  <i class="bi bi-telephone"></i>
                </CButton>
                <CButton color="primary" size="sm" @click="selectAndLog(v)">
                  <i class="bi bi-pencil"></i> Log
                </CButton>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
        </CModalBody>
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
  CAvatar,
  CFormInput,
  CFormSelect,
  CFormLabel,
  CFormTextarea,
  CFormCheck,
  CInputGroup,
  CInputGroupText,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CAlert,
  CListGroup,
  CListGroupItem,
  CForm,
} from '@coreui/vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';
import { useToast } from '../composables/useToast';
import { exportToExcel, formatDateForExport } from '../utils/export.js';

// Data
const visitors = ref([
  {
    id: 1,
    name: 'Ama Kwarteng',
    phone: '+233241234567',
    email: 'ama@email.com',
    source: 'Friend',
    firstVisitDate: '2026-01-15',
    status: 'engaged',
    nextFollowUp: '2026-01-24',
    lastContactMethod: 'WhatsApp',
    interests: ['Youth', 'Choir'],
    notes: 'Interested in joining the choir',
    followUps: [
      {
        id: 1,
        method: 'WhatsApp',
        date: '2026-01-18',
        notes: 'Had a great conversation, very interested in choir ministry',
        statusAfter: 'engaged',
      },
      {
        id: 2,
        method: 'Call',
        date: '2026-01-16',
        notes: 'Brief call, will follow up later',
        statusAfter: 'contacted',
      },
    ],
  },
  {
    id: 2,
    name: 'Kofi Asante',
    phone: '+233201234567',
    source: 'Social Media',
    firstVisitDate: '2026-01-12',
    status: 'not_contacted',
    nextFollowUp: '2026-01-20',
    interests: [],
    notes: '',
    followUps: [],
  },
  {
    id: 3,
    name: 'Yaa Mensah',
    phone: '+233551234567',
    source: 'Walk-in',
    firstVisitDate: '2026-01-19',
    status: 'contacted',
    nextFollowUp: '2026-01-26',
    lastContactMethod: 'SMS',
    interests: ['Prayer'],
    notes: 'First-time visitor, seemed interested',
    followUps: [
      {
        id: 1,
        method: 'SMS',
        date: '2026-01-20',
        notes: 'Sent welcome message',
        statusAfter: 'contacted',
      },
    ],
  },
  {
    id: 4,
    name: 'Kwame Boateng',
    phone: '+233271234567',
    source: 'Friend',
    firstVisitDate: '2025-12-08',
    status: 'converted',
    interests: ['Media'],
    notes: 'Now a regular member',
    followUps: [
      {
        id: 1,
        method: 'In-Person',
        date: '2026-01-05',
        notes: 'Completed membership class',
        statusAfter: 'converted',
      },
      {
        id: 2,
        method: 'Call',
        date: '2025-12-15',
        notes: 'Invited to membership class',
        statusAfter: 'engaged',
      },
    ],
  },
]);

// State
const search = ref('');
const statusFilter = ref('');
const selectedVisitor = ref(null);
const showAddVisitor = ref(false);
const showFollowUpModal = ref(false);
const showDueList = ref(false);

// Stats
const stats = computed(() => ({
  total: visitors.value.length,
  notContacted: visitors.value.filter(v => v.status === 'not_contacted').length,
  engaged: visitors.value.filter(v => v.status === 'engaged').length,
  converted: visitors.value.filter(v => v.status === 'converted').length,
}));

// Filtered
const filteredVisitors = computed(() => {
  return visitors.value.filter(v => {
    if (statusFilter.value && v.status !== statusFilter.value) return false;
    if (
      search.value &&
      !v.name.toLowerCase().includes(search.value.toLowerCase()) &&
      !v.phone.includes(search.value)
    )
      return false;
    return true;
  });
});

// Due this week
const dueThisWeek = computed(() => {
  const today = new Date();
  const weekFromNow = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
  return visitors.value
    .filter(v => {
      if (!v.nextFollowUp || v.status === 'converted') return false;
      const dueDate = new Date(v.nextFollowUp);
      return dueDate <= weekFromNow;
    })
    .sort((a, b) => new Date(a.nextFollowUp) - new Date(b.nextFollowUp));
});

const overdueCount = computed(
  () => dueThisWeek.value.filter(v => isOverdue(v.nextFollowUp)).length
);

// Forms
const visitorForm = reactive({
  name: '',
  phone: '',
  email: '',
  source: '',
  firstVisitDate: '',
  interests: [],
  notes: '',
});

const followUpForm = reactive({
  method: '',
  notes: '',
  statusAfter: '',
  nextDate: '',
});

// Notification
const toast = useToast();

// Methods
function selectVisitor(v) {
  selectedVisitor.value = v;
}

function filterByStatus(status) {
  statusFilter.value = status;
}

function openAddVisitor() {
  Object.assign(visitorForm, {
    name: '',
    phone: '',
    email: '',
    source: '',
    firstVisitDate: new Date().toISOString().split('T')[0],
    interests: [],
    notes: '',
  });
  showAddVisitor.value = true;
}

function saveVisitor() {
  const newId = Math.max(...visitors.value.map(v => v.id)) + 1;
  visitors.value.push({
    id: newId,
    name: visitorForm.name,
    phone: '+233' + visitorForm.phone,
    email: visitorForm.email,
    source: visitorForm.source,
    firstVisitDate: visitorForm.firstVisitDate,
    status: 'not_contacted',
    interests: Array.isArray(visitorForm.interests) ? visitorForm.interests : [],
    notes: visitorForm.notes,
    followUps: [],
  });
  showAddVisitor.value = false;
  toast.success('Visitor added successfully');
}

function openFollowUpModal() {
  Object.assign(followUpForm, { method: '', notes: '', statusAfter: '', nextDate: '' });
  showFollowUpModal.value = true;
}

function saveFollowUp() {
  if (!selectedVisitor.value) return;
  const newFollowUp = {
    id: (selectedVisitor.value.followUps?.length || 0) + 1,
    method: followUpForm.method,
    date: new Date().toISOString().split('T')[0],
    notes: followUpForm.notes,
    statusAfter: followUpForm.statusAfter,
  };
  if (!selectedVisitor.value.followUps) selectedVisitor.value.followUps = [];
  selectedVisitor.value.followUps.unshift(newFollowUp);
  selectedVisitor.value.lastContactMethod = followUpForm.method;
  if (followUpForm.statusAfter) selectedVisitor.value.status = followUpForm.statusAfter;
  if (followUpForm.nextDate) selectedVisitor.value.nextFollowUp = followUpForm.nextDate;
  showFollowUpModal.value = false;
  toast.success('Follow-up logged');
}

function callVisitor() {
  window.open(`tel:${selectedVisitor.value.phone}`);
}

function whatsappVisitor() {
  const phone = selectedVisitor.value.phone.replace('+', '');
  window.open(`https://wa.me/${phone}`);
}

function smsVisitor() {
  window.open(`sms:${selectedVisitor.value.phone}`);
}

function convertToMember() {
  selectedVisitor.value.status = 'converted';
  toast.success(`${selectedVisitor.value.name} converted to member!`);
}

function selectAndCall(v) {
  showDueList.value = false;
  selectVisitor(v);
  setTimeout(callVisitor, 300);
}

function selectAndLog(v) {
  showDueList.value = false;
  selectVisitor(v);
  setTimeout(openFollowUpModal, 300);
}

// Helpers
function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function isOverdue(date) {
  if (!date) return false;
  return new Date(date) < new Date();
}

function statusColor(status) {
  const colors = {
    not_contacted: 'danger',
    contacted: 'info',
    engaged: 'warning',
    converted: 'success',
  };
  return colors[status] || 'secondary';
}

function statusAvatarColor(status) {
  const colors = {
    not_contacted: 'danger',
    contacted: 'info',
    engaged: 'warning',
    converted: 'success',
  };
  return colors[status] || 'secondary';
}

function statusLabel(status) {
  const labels = {
    not_contacted: 'Not Contacted',
    contacted: 'Contacted',
    engaged: 'Engaged',
    converted: 'Converted',
  };
  return labels[status] || status;
}

function methodColor(method) {
  const colors = { WhatsApp: 'success', SMS: 'primary', Call: 'info', 'In-Person': 'warning' };
  return colors[method] || 'secondary';
}

function methodIcon(method) {
  const icons = {
    WhatsApp: 'bi bi-whatsapp',
    SMS: 'bi bi-chat-dots',
    Call: 'bi bi-telephone',
    'In-Person': 'bi bi-person',
  };
  return icons[method] || 'bi bi-circle';
}

function exportVisitors() {
  const columns = [
    { key: 'name', header: 'Name' },
    { key: 'phone', header: 'Phone' },
    { key: 'email', header: 'Email' },
    { key: 'source', header: 'Source' },
    { key: 'firstVisitDate', header: 'First Visit', transform: v => formatDateForExport(v) },
    { key: 'status', header: 'Status', transform: v => statusLabel(v) },
    { key: 'lastContactMethod', header: 'Last Contact Method' },
    { key: 'nextFollowUp', header: 'Next Follow-up', transform: v => formatDateForExport(v) },
    { key: 'interests', header: 'Interests', transform: v => v?.join(', ') || '' },
    { key: 'notes', header: 'Notes' },
  ];
  exportToExcel(
    filteredVisitors.value,
    columns,
    `Visitors_FollowUps_${new Date().toISOString().split('T')[0]}`
  );
  toast.success('Visitor data exported successfully');
}
</script>

<style scoped>
.page-wrap {
  padding: 24px;
}

.page-header {
  margin-bottom: 20px;
}

.stat-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
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

.visitor-item {
  cursor: pointer;
  transition: background 0.15s ease;
}

.visitor-item:hover {
  background: rgba(13, 110, 253, 0.04);
}

.visitor-item.active {
  background: rgba(13, 110, 253, 0.08);
  border-left: 3px solid var(--cui-primary);
}

.timeline {
  position: relative;
  padding-left: 24px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 6px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e9ecef;
}

.timeline-item {
  position: relative;
  padding-bottom: 16px;
}

.timeline-marker {
  position: absolute;
  left: -24px;
  top: 4px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
}

.timeline-content {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 8px;
}
</style>
