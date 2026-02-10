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

    <StatsCards :stats="stats" @filter="filterByStatus" />

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

    <CRow class="g-8">
      <CCol lg="5">
        <VisitorListPanel
          :paged-visitors="pagedVisitors"
          :selected-visitor-id="selectedVisitor?.id"
          v-model:search="search"
          v-model:status-filter="statusFilter"
          v-model:date-from="dateFrom"
          v-model:date-to="dateTo"
          v-model:current-page="currentPage"
          :total-pages="totalPages"
          :total-items="filteredVisitors.length"
          :per-page="perPage"
          @select="selectVisitor"
          @apply-date-filter="applyDateFilter"
          @clear-date-filter="clearDateFilter"
        />
      </CCol>

      <CCol lg="7">
        <VisitorDetailPanel
          :visitor="selectedVisitor"
          @close="selectedVisitor = null"
          @log-followup="openFollowUpModal"
          @call="callVisitor"
          @whatsapp="whatsappVisitor"
          @sms="smsVisitor"
          @convert="convertToMember"
        />
      </CCol>
    </CRow>

    <AddVisitorModal
      :visible="showAddVisitor"
      :form="visitorForm"
      @close="showAddVisitor = false"
      @save="saveVisitor"
      @update-field="updateVisitorField"
    />

    <LogFollowUpModal
      v-model:visible="showFollowUpModal"
      :form="followUpForm"
      @save="saveFollowUp"
    />

    <DueListModal
      v-model:visible="showDueList"
      :due-list="dueThisWeek"
      :format-date="formatDate"
      :is-overdue="isOverdue"
      :status-color="statusColor"
      :status-label="statusLabel"
      @call="selectAndCall"
      @log="selectAndLog"
    />
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue';
import { CRow, CCol, CButton, CAlert } from '@coreui/vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';
import StatsCards from '../components/FollowUps/StatsCards.vue';
import VisitorListPanel from '../components/FollowUps/VisitorListPanel.vue';
import VisitorDetailPanel from '../components/FollowUps/VisitorDetailPanel.vue';
import AddVisitorModal from '../components/FollowUps/AddVisitorModal.vue';
import LogFollowUpModal from '../components/FollowUps/LogFollowUpModal.vue';
import DueListModal from '../components/FollowUps/DueListModal.vue';
import { useToast } from '../composables/useToast';
import { visitorsApi } from '../api/visitors';
import { exportToExcel, formatDateForExport } from '../utils/export.js';

// Data
const visitors = ref([]);
const dateFrom = ref('');
const dateTo = ref('');
const search = ref('');
const statusFilter = ref('');
const selectedVisitor = ref(null);
const showAddVisitor = ref(false);
const showFollowUpModal = ref(false);
const showDueList = ref(false);
const toast = useToast();

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

// Pagination
const currentPage = ref(1);
const perPage = 15;
const totalPages = computed(() => Math.max(1, Math.ceil(filteredVisitors.value.length / perPage)));

const pagedVisitors = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredVisitors.value.slice(start, start + perPage);
});

watch([filteredVisitors], () => {
  currentPage.value = 1;
});

function applyDateFilter() {
  currentPage.value = 1;
  loadVisitors();
}

function clearDateFilter() {
  dateFrom.value = '';
  dateTo.value = '';
  currentPage.value = 1;
  loadVisitors();
}

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
  name: '', phone: '', email: '', source: '',
  firstVisitDate: '', interests: [], notes: '',
});

const followUpForm = reactive({
  method: '', notes: '', statusAfter: '', nextDate: '',
});

function updateVisitorField({ field, value }) {
  visitorForm[field] = value;
}



// Methods
function selectVisitor(v) {
  selectedVisitor.value = v;
}

function filterByStatus(status) {
  statusFilter.value = status;
}

function openAddVisitor() {
  Object.assign(visitorForm, {
    name: '', phone: '', email: '', source: '',
    firstVisitDate: new Date().toISOString().split('T')[0],
    interests: [], notes: '',
  });
  showAddVisitor.value = true;
}

async function saveVisitor() {
  try {
    const payload = {
      name: visitorForm.name,
      phone: visitorForm.phone.startsWith('+233') ? visitorForm.phone : '+233' + visitorForm.phone,
      email: visitorForm.email,
      category: 'Visitor',
      service_type: visitorForm.source,
      date: visitorForm.firstVisitDate,
    };
    const res = await visitorsApi.create(payload);
    if (res.data.success) {
      toast.success('Visitor added successfully');
      showAddVisitor.value = false;
      await loadVisitors();
    }
  } catch (err) {
    console.error('Save visitor error:', err);
    toast.error(err.response?.data?.message || 'Failed to add visitor');
  }
}

async function loadVisitors() {
  try {
    const params = { per_page: perPage, page: currentPage.value };
    if (search.value) params.search = search.value;
    if (statusFilter.value) params.status = statusFilter.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    const res = await visitorsApi.getAll(params);
    if (res.data && res.data.data) {
      visitors.value = res.data.data.map(v => ({
        id: v.id,
        name: v.name,
        phone: v.phone,
        email: v.email || '',
        source: v.service_type || v.category || 'Unknown',
        firstVisitDate: v.first_visit_date,
        status: v.status || 'not_contacted',
        nextFollowUp: v.next_follow_up,
        lastContactMethod: v.last_contact_method,
        interests: v.interests || [],
        notes: v.notes || '',
        followUps: v.follow_ups || [],
      }));
    }
  } catch (err) {
    console.error('Load visitors error:', err);
    toast.error('Failed to load visitors');
  }
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

async function convertToMember() {
  if (!selectedVisitor.value) return;
  try {
    const res = await visitorsApi.convert(selectedVisitor.value.id, { roles: ['member'] });
    if (res && res.data && res.data.success) {
      selectedVisitor.value.status = 'converted';
      selectedVisitor.value.convertedUser = res.data.user || null;
      toast.success(res.data.message || `${selectedVisitor.value.name} converted to member!`);
    } else {
      toast.error('Conversion failed');
    }
  } catch (err) {
    console.error('convert error', err);
    toast.error(err?.response?.data?.message || 'Conversion failed');
  }
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
    day: 'numeric', month: 'short', year: 'numeric',
  });
}

function isOverdue(date) {
  if (!date) return false;
  return new Date(date) < new Date();
}

function statusColor(status) {
  const colors = { not_contacted: 'danger', contacted: 'info', engaged: 'warning', converted: 'success' };
  return colors[status] || 'secondary';
}

function statusLabel(status) {
  const labels = { not_contacted: 'Not Contacted', contacted: 'Contacted', engaged: 'Engaged', converted: 'Converted' };
  return labels[status] || status;
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

onMounted(() => {
  loadVisitors();
});
</script>

<style scoped>
.page-wrap {
  padding: 24px;
}

.page-header {
  margin-bottom: 20px;
}
</style>
