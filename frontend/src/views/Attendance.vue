<template>
  <div class="page-wrap attendance-ui-enhanced">
    <PageHeader title="Attendance" subtitle="Record and track service attendance">
      <template #actions>
        <CButton color="primary" @click="exportAttendance">
          <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </CButton>
      </template>
    </PageHeader>

    <CRow class="g-4 mt-3 mb-5">
      <!-- Left Column: Record Attendance -->
      <CCol lg="7">
        <MaterialCard class="attendance-card shadow-sm rounded-4">
          <template #header>
            <div class="d-flex align-items-center gap-3 py-2 px-2">
              <div class="header-icon-box bg-primary-subtle text-primary">
                <i class="bi bi-person-check-fill"></i>
              </div>
              <div>
                <h3 class="md-title-large mb-1">Minister Record Attendance</h3>
                <p class="text-muted small mb-0">
                  Select unit and service to record ministry headcount
                </p>
              </div>
              <div class="ms-auto d-none d-sm-block">
                <div class="headcount-badge">
                  <span class="label">HEAD COUNT</span>
                  <span class="value">{{
                    Object.values(form.present).filter(Boolean).length
                    }}</span>
                </div>
              </div>
            </div>
          </template>

          <CForm class="attendance-form" @submit.prevent="addEntry">
            <CRow class="g-4 mb-4">
              <CCol md="6">
                <div class="md-input-wrapper">
                  <select v-model="form.unit" class="md-input border-0" required>
                    <option value="" disabled selected>Select Ministry Unit</option>
                    <option v-for="unit in ministryUnits" :key="unit.id" :value="unit.id">
                      {{ unit.name }}
                    </option>
                  </select>
                  <label class="md-label-floating">Ministry Unit</label>
                </div>
              </CCol>
              <CCol md="6">
                <div class="md-input-wrapper">
                  <select v-model="form.service" class="md-input border-0" required>
                    <option value="Sunday">Sunday Service</option>
                    <option value="Wednesday">Wednesday Mid-week</option>
                    <option value="Special">Special Program</option>
                  </select>
                  <label class="md-label-floating">Service Type</label>
                </div>
              </CCol>
              <CCol md="6">
                <div class="md-input-wrapper">
                  <input v-model="form.date" type="date" class="md-input border-0" required />
                  <label class="md-label-floating">Date</label>
                </div>
              </CCol>
              <CCol md="6">
                <div class="md-input-wrapper">
                  <input v-model="form.time" type="time" class="md-input border-0" required />
                  <label class="md-label-floating">Service Time</label>
                </div>
              </CCol>
            </CRow>

            <!-- Member Search -->
            <div class="mb-4">
              <div class="search-input-group position-relative">
                <i
                  class="bi bi-search position-absolute start-0 top-50 translate-middle-y ms-3 text-muted opacity-50"></i>
                <input v-model="searchQuery" type="text" class="form-control rounded-pill border-0 bg-light py-3 ps-5"
                  placeholder="Filter members by name or phone..." />
              </div>
            </div>

            <!-- Member Marking Section -->
            <div class="marking-section rounded-4 p-4 bg-light border border-1">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="md-title-small mb-0 text-primary">
                  <i class="bi bi-people-fill me-2"></i>
                  Members List
                  <span v-if="searchQuery"
                    class="ms-2 badge bg-primary-subtle text-primary border-0 rounded-pill fs-xs">
                    Filtered
                  </span>
                </h6>
                <div class="attendance-stats small text-muted">
                  <span class="text-uppercase x-small fw-bold opacity-75">Total Head Count: </span>
                  <span class="fw-bold text-primary fs-5">{{
                    Object.values(form.present).filter(Boolean).length
                    }}</span>
                  /
                  <span class="fw-medium">{{ members.length }}</span>
                </div>
              </div>

              <div v-if="isLoadingMembers" class="py-5 text-center">
                <CSpinner color="primary" variant="grow" size="sm" />
                <p class="text-muted small mt-2">Loading members...</p>
              </div>

              <div v-else-if="filteredMembers.length > 0" class="member-grid row row-cols-1 row-cols-md-2 g-3">
                <div v-for="member in filteredMembers" :key="member.id"
                  class="member-item col rounded-3 bg-white shadow-sm p-3 d-flex align-items-center gap-3 border border-1"
                  :class="{ 'is-present': form.present[member.id] }" @click="togglePresent(member.id)">
                  <CAvatar :src="member.avatar" size="md" class="member-avatar border border-2">
                    <span v-if="!member.avatar">{{ member.name.charAt(0) }}</span>
                  </CAvatar>
                  <div class="member-info flex-grow-1">
                    <div class="member-name fw-bold">{{ member.name }}</div>
                    <div v-if="member.phone" class="member-phone text-muted small">
                      <i class="bi bi-telephone-fill me-1 opacity-50"></i>{{ member.phone }}
                    </div>
                  </div>
                  <div class="member-check ms-auto">
                    <i class="bi fs-4" :class="form.present[member.id]
                      ? 'bi-check-circle-fill text-success'
                      : 'bi-circle text-muted'
                      "></i>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-5">
                <i class="bi bi-search text-muted opacity-25 fs-1"></i>
                <p class="text-muted small mt-2">
                  {{ searchQuery ? `No matches for "${searchQuery}"` : 'No members in this unit' }}
                </p>
              </div>
            </div>

            <div class="mt-4 pt-2 d-flex justify-content-end">
              <button class="md-btn md-btn-filled px-5 py-3" type="submit" :disabled="isSaving || members.length === 0">
                <CSpinner v-if="isSaving" size="sm" class="me-2" />
                <i v-else class="bi bi-cloud-arrow-up-fill me-2"></i>
                Save Attendance
              </button>
            </div>
          </CForm>
        </MaterialCard>
      </CCol>

      <!-- Right Column: Recent Entries -->
      <CCol lg="5">
        <MaterialCard class="attendance-card shadow-sm rounded-4">
          <template #header>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-3">
                <div class="header-icon-box bg-success-subtle text-success">
                  <i class="bi bi-clock-history"></i>
                </div>
                <div>
                  <h3 class="md-title-large mb-1">Recent Activity</h3>
                  <p class="text-muted small mb-0">This week's summary</p>
                </div>
              </div>
              <div class="weekly-badge">
                <span class="label">WEEK TOTAL</span>
                <span class="val">{{ weeklyTotal }}</span>
              </div>
            </div>
          </template>

          <div class="entries-table-container mt-3">
            <CTable v-if="entries.length > 0" hover borderless align="middle" class="mb-0">
              <CTableHead>
                <CTableRow>
                  <CTableHeaderCell class="bg-transparent text-muted small pb-3">DETAILS</CTableHeaderCell>
                  <CTableHeaderCell class="bg-transparent text-muted small pb-3">MEMBER</CTableHeaderCell>
                  <CTableHeaderCell class="bg-transparent text-muted small pb-3">STATUS</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="e in entries" :key="e.id" class="entry-row">
                  <CTableDataCell>
                    <div class="entry-details">
                      <div class="fw-bold text-dark">{{ e.service || 'Sunday Service' }}</div>
                      <div class="text-muted small">{{ e.date }} • {{ e.time || '09:00' }}</div>
                    </div>
                  </CTableDataCell>
                  <CTableDataCell>
                    <div class="fw-medium text-secondary">{{ e.memberName || 'Anonymous' }}</div>
                    <div class="small opacity-50 text-truncate" style="max-width: 150px">
                      {{ e.unitName || 'Ministry Unit' }}
                    </div>
                  </CTableDataCell>
                  <CTableDataCell>
                    <div class="status-indicator" :class="e.present ? 'active' : 'inactive'">
                      <span class="status-dot"></span>
                      <span class="status-text">{{ e.present ? 'PRESENT' : 'ABSENT' }}</span>
                    </div>
                  </CTableDataCell>
                </CTableRow>
              </CTableBody>
            </CTable>

            <div v-else class="empty-state py-5 text-center">
              <div class="empty-icon-box mx-auto mb-3">
                <i class="bi bi-clipboard-x text-muted"></i>
              </div>
              <h6 class="md-title-small">No activity this week</h6>
              <p class="text-muted small px-4">Start recording attendance to see entries here.</p>
            </div>
          </div>
        </MaterialCard>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import {
  CRow,
  CCol,
  CForm,
  CAvatar,
  CSpinner,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableHeaderCell,
  CTableDataCell,
} from '@coreui/vue';
import PageHeader from '../components/shared/PageHeader.vue';
import MaterialCard from '../components/material/MaterialCard.vue';
import { useToast } from '../composables/useToast';
import { exportToExcel } from '../utils/export.js';
import { attendanceApi } from '../api/attendance';
import { ministryApi } from '../api/ministry';
import { useAuthStore } from '../store/auth';

useAuthStore();
const toast = useToast();
const ministryUnits = ref([]);
const members = ref([]);
const isLoadingMembers = ref(false);
const isSaving = ref(false);
const isLoadingEntries = ref(false);
const searchQuery = ref('');

const form = ref({
  unit: '',
  service: 'Sunday',
  date: new Date().toISOString().slice(0, 10),
  time: '09:00',
  present: {},
});
const entries = ref([]);

const filteredMembers = computed(() => {
  if (!searchQuery.value.trim()) return members.value;
  const q = searchQuery.value.toLowerCase().trim();
  return members.value.filter(
    m => m.name.toLowerCase().includes(q) || (m.phone && m.phone.includes(q))
  );
});

onMounted(async () => {
  await loadData();
});

async function loadData() {
  try {
    const res = await ministryApi.getUnits();
    // ministryApi.getUnits() now returns the list directly
    ministryUnits.value = res || [];

    // Fetch recent weekly attendance
    fetchWeeklyEntries();

    if (ministryUnits.value.length) {
      // Default to first unit - this triggers the watch
      form.value.unit = ministryUnits.value[0].id;
    }
  } catch (err) {
    console.error('Failed to load initial data', err);
    toast.error('Failed to load mission units');
  }
}

async function fetchWeeklyEntries() {
  isLoadingEntries.value = true;
  try {
    const res = await attendanceApi.getWeeklyReport();
    entries.value = res.data?.data || [];
  } catch (err) {
    console.error('Failed to load entries', err);
  } finally {
    isLoadingEntries.value = false;
  }
}

watch(
  () => form.value.unit,
  async unitId => {
    if (unitId) {
      form.value.present = {};
      await fetchMembers(unitId);
    }
  }
);

async function fetchMembers(unitId) {
  if (!unitId) {
    members.value = [];
    return;
  }

  isLoadingMembers.value = true;
  try {
    const res = await ministryApi.getMembers(unitId);
    // ministryApi.getMembers() returns the list directly
    members.value = res || [];

    // Auto-mark all as present by default
    form.value.present = {};
    members.value.forEach(m => {
      form.value.present[m.id] = true;
    });
  } catch (err) {
    console.error('Failed to load members', err);
    toast.error('Failed to load unit members');
  } finally {
    isLoadingMembers.value = false;
  }
}

async function addEntry() {
  if (Object.keys(form.value.present).length === 0) {
    return toast.warning('Please mark attendance for members');
  }

  isSaving.value = true;
  try {
    for (const member of members.value) {
      await attendanceApi.createUnitAttendance({
        unit: form.value.unit,
        service: form.value.service,
        date: form.value.date,
        time: form.value.time,
        member_id: member.id,
        member_name: member.name,
        present: !!form.value.present[member.id],
      });
    }

    toast.success('Attendance recorded successfully!');

    // Refresh activity list
    await fetchWeeklyEntries();

    // Reset form for next entry but keep unit/date
    form.value.present = {};
    members.value.forEach(m => {
      form.value.present[m.id] = true;
    });
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to record attendance');
  } finally {
    isSaving.value = false;
  }
}

function togglePresent(memberId) {
  form.value.present[memberId] = !form.value.present[memberId];
}
function _approveEntry(id) {
  const entry = entries.value.find(e => e.id === id);
  if (entry) entry.approved = true;
}

function exportAttendance() {
  const columns = [
    { key: 'date', label: 'Date' },
    { key: 'service', label: 'Service Type' },
    { key: 'count', label: 'Attendance Count' },
    { key: 'status', label: 'Status' },
  ];

  const exportData = entries.value.map(e => ({
    ...e,
    status: e.approved ? 'Approved' : 'Pending',
  }));

  exportToExcel(exportData, columns, 'attendance_report');
  toast.success(`Exported ${exportData.length} records to Excel`);
}

const weeklyTotal = computed(() => {
  return entries.value.filter(e => e.present).length;
});
</script>

<style scoped>
/* Member Search */
.search-input-group .form-control {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  transition: all 0.2s;
}

.search-input-group .form-control:focus {
  background: white !important;
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
}

.fs-xs {
  font-size: 0.65rem;
}

.page-wrap {
  padding: var(--md-space-6);
  min-height: 100vh;
}

.header-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.bg-light {
  background: #f8fafc !important;
}

/* Member Marking UI */
.member-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 500px;
  overflow-y: auto;
  padding-right: 8px;
}

/* Custom Scrollbar */
.member-grid::-webkit-scrollbar {
  width: 6px;
}

.member-grid::-webkit-scrollbar-track {
  background: transparent;
}

.member-grid::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.member-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 20px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.member-item:hover {
  transform: translateX(4px);
  border-color: var(--md-primary);
  background: #fdfdfd;
}

.member-item.is-present {
  background: rgba(16, 185, 129, 0.04);
  border-color: #10b981;
}

.member-avatar {
  width: 40px !important;
  height: 40px !important;
  border-radius: 12px !important;
  font-weight: bold;
  background: #e2e8f0;
  color: #64748b;
}

.is-present .member-avatar {
  background: #10b981;
  color: white;
}

.member-info {
  flex-grow: 1;
}

.member-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
}

.member-status-text {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #94a3b8;
}

.is-present .member-status-text {
  color: #059669;
}

.member-check i {
  font-size: 1.25rem;
  transition: all 0.2s;
}

/* Entries Table */
.entries-table-container {
  margin-top: 1rem;
}

.entry-row {
  transition: all 0.2s;
  border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
}

.entry-row:hover {
  background: #f8fafc !important;
}

/* Weekly Badge */
.weekly-badge {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  padding: 8px 16px;
  align-items: center;
}

.weekly-badge .label {
  font-size: 0.65rem;
  font-weight: 800;
  color: #94a3b8;
  letter-spacing: 0.5px;
}

.weekly-badge .val {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--md-primary);
}

/* Status Indicator (Same as Users view) */
.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 30px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.status-indicator.active {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.status-indicator.inactive {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.active .status-dot {
  background: #10b981;
}

.inactive .status-dot {
  background: #ef4444;
}

.empty-icon-box {
  width: 64px;
  height: 64px;
  background: #f1f5f9;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

@media (max-width: 768px) {
  .page-wrap {
    padding: var(--md-space-4);
  }

  .marking-section {
    padding: 16px !important;
  }
}

/* Headcount Badge */
.headcount-badge {
  background: var(--primary);
  color: white;
  padding: 8px 16px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 80px;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.headcount-badge .label {
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  opacity: 0.8;
}

.headcount-badge .value {
  font-size: 1.25rem;
  font-weight: 800;
  line-height: 1;
  margin-top: 2px;
}

/* Attendance UI Enhancements */
.attendance-ui-enhanced .attendance-card {
  background: #f8fafc;
  border-radius: 1.5rem;
  box-shadow: 0 2px 16px rgba(99, 102, 241, 0.08);
}

.attendance-ui-enhanced .marking-section {
  background: #f4f6fb;
  border-radius: 1.25rem;
  border: 1px solid #e0e7ef;
}

.attendance-ui-enhanced .member-item {
  transition: box-shadow 0.2s, border-color 0.2s;
  cursor: pointer;
}

.attendance-ui-enhanced .member-item.is-present {
  border-color: #38bdf8;
  box-shadow: 0 2px 8px rgba(56, 189, 248, 0.12);
}

.attendance-ui-enhanced .member-avatar {
  background: #eef2ff;
  border-radius: 50%;
  border: 2px solid #c7d2fe;
}

.attendance-ui-enhanced .member-info {
  min-width: 0;
}

.attendance-ui-enhanced .member-name {
  font-size: 1rem;
  font-weight: 600;
}

.attendance-ui-enhanced .member-phone {
  font-size: 0.85rem;
}

.attendance-ui-enhanced .member-check {
  font-size: 1.25rem;
}

.attendance-ui-enhanced .entries-table-container {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.06);
  padding: 1rem;
}
</style>
