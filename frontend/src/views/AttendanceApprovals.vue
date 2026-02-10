<template>
  <div class="page-wrap">
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
                <CFormInput v-model="filters.dateFrom" type="date" />
              </CCol>
              <CCol>
                <CFormInput v-model="filters.dateTo" type="date" />
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
          <CFormCheck :checked="allSelected" label="Select All" @change="toggleSelectAll" />
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

    <!-- Reject Modal (component) -->
    <RejectModal v-model:visible="showRejectModal" :record="rejectingRecord"
      @rejected="rejectRecord" />

    <!-- Bulk Approve Confirmation (component) -->
    <BulkApproveModal v-model:visible="showBulkApproveModal" :selectedCount="selectedIds.length" :summaries="selectedSummaries"
      @confirmed="confirmBulkApprove" />

    <!-- Details Modal (component) -->
    <DetailsModal v-model:visible="showDetailsModal" :record="viewingRecord" />
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
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
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CAlert,
} from '@coreui/vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';
import { exportToExcel, formatDateForExport } from '../utils/export.js';
import { attendanceApi } from '../api/attendance';
import { useToast } from '../composables/useToast';
import RejectModal from '../components/attendance/RejectModal.vue';
import BulkApproveModal from '../components/attendance/BulkApproveModal.vue';
import DetailsModal from '../components/attendance/DetailsModal.vue';

// Data
const records = ref([]);
const stats = ref({
  pending: 0,
  approved: 0,
  rejected: 0,
  approved_today: 0,
  total_week: 0,
});
const loading = ref(false);

// Filters
const filters = reactive({
  dateFrom: '',
  dateTo: '',
  service: '',
  status: '',
  submittedBy: '',
});

// Fetch data from API
async function fetchAttendance() {
  loading.value = true;
  try {
    const params = {};
    if (filters.status) params.status = filters.status;
    if (filters.service) params.service_type = filters.service;
    if (filters.dateFrom) params.date_from = filters.dateFrom;
    if (filters.dateTo) params.date_to = filters.dateTo;

    const response = await attendanceApi.getAll(params);
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
        notes: r.notes,
      }));
      if (response.data.stats) {
        stats.value = response.data.stats;
      }
    }
  } catch (error) {
    console.error('Failed to fetch attendance:', error);
    toast.error('Failed to load attendance records');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchAttendance();
});

const filteredRecords = computed(() => {
  return records.value.filter(r => {
    if (
      filters.submittedBy &&
      r.submittedBy?.name &&
      !r.submittedBy.name.toLowerCase().includes(filters.submittedBy.toLowerCase())
    )
      return false;
    return true;
  });
});

function resetFilters() {
  filters.dateFrom = '';
  filters.dateTo = '';
  filters.service = '';
  filters.status = '';
  filters.submittedBy = '';
  fetchAttendance();
}

// Stats from API
const pendingCount = computed(() => stats.value.pending);
const approvedTodayCount = computed(() => stats.value.approved_today);
const rejectedCount = computed(() => stats.value.rejected);
const totalWeekCount = computed(() => stats.value.total_week);

// Selection
const selectedIds = ref([]);
const allSelected = computed(() => {
  const pending = filteredRecords.value.filter(r => r.status === 'pending');
  return pending.length > 0 && pending.every(r => selectedIds.value.includes(r.id));
});

function toggleSelectAll() {
  const pending = filteredRecords.value.filter(r => r.status === 'pending').map(r => r.id);
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = pending;
  }
}

function toggleSelect(id) {
  const idx = selectedIds.value.indexOf(id);
  if (idx === -1) {
    selectedIds.value.push(id);
  } else {
    selectedIds.value.splice(idx, 1);
  }
}

// Approve/Reject
const toast = useToast();

async function approveRecord(record) {
  try {
    await attendanceApi.approve(record.id);
    toast.success(`Approved attendance for ${formatDate(record.date)}`);
    fetchAttendance(); // Refresh data
  } catch (error) {
    console.error('Failed to approve:', error);
    toast.error('Failed to approve attendance');
  }
}

const showRejectModal = ref(false);
const rejectingRecord = ref(null);

function openRejectModal(record) {
  rejectingRecord.value = record;
  showRejectModal.value = true;
}

async function rejectRecord(reason) {
  if (rejectingRecord.value) {
    try {
      await attendanceApi.reject(rejectingRecord.value.id, reason);
      toast.info(`Rejected attendance. Usher notified.`);
      fetchAttendance(); // Refresh data
    } catch (error) {
      console.error('Failed to reject:', error);
      toast.error('Failed to reject attendance');
    }
  }
  showRejectModal.value = false;
}

// Bulk Approve
const showBulkApproveModal = ref(false);

const selectedSummaries = computed(() => selectedIds.value.map(id => getRecordSummary(id)));

function bulkApprove() {
  showBulkApproveModal.value = true;
}

async function confirmBulkApprove() {
  try {
    await attendanceApi.bulkApprove(selectedIds.value);
    toast.success(`Approved ${selectedIds.value.length} records`);
    selectedIds.value = [];
    fetchAttendance(); // Refresh data
  } catch (error) {
    console.error('Failed to bulk approve:', error);
    toast.error('Failed to approve records');
  }
  showBulkApproveModal.value = false;
}

function getRecordSummary(id) {
  const r = records.value.find(rec => rec.id === id);
  return r ? `${serviceLabel(r.serviceType)} - ${formatDate(r.date)} (${r.count})` : '';
}

// Details
const showDetailsModal = ref(false);
const viewingRecord = ref(null);

function viewDetails(record) {
  viewingRecord.value = record;
  showDetailsModal.value = true;
}

// Helpers
function formatDate(date) {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-GB', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function relativeDays(date) {
  const diff = Math.floor((new Date() - new Date(date)) / (1000 * 60 * 60 * 24));
  if (diff === 0) return 'Today';
  if (diff === 1) return 'Yesterday';
  return `${diff} days ago`;
}

function serviceLabel(type) {
  const labels = { sunday: 'Sunday Service', friday: 'Friday Night', midweek: 'Midweek Service' };
  return labels[type] || type;
}

function serviceIcon(type) {
  const icons = { sunday: 'bi bi-sun', friday: 'bi bi-moon-stars', midweek: 'bi bi-book' };
  return icons[type] || 'bi bi-calendar';
}

function statusColor(status) {
  const colors = { pending: 'warning', approved: 'success', rejected: 'danger' };
  return colors[status] || 'secondary';
}

function statusIcon(status) {
  const icons = {
    pending: 'bi bi-clock',
    approved: 'bi bi-check-circle',
    rejected: 'bi bi-x-circle',
  };
  return icons[status] || '';
}

function exportApprovals() {
  const columns = [
    { key: 'date', header: 'Date', transform: v => formatDateForExport(v) },
    { key: 'serviceType', header: 'Service', transform: v => serviceLabel(v) },
    { key: 'count', header: 'Attendance Count' },
    { key: 'submittedBy', header: 'Submitted By', transform: v => v?.name || '' },
    { key: 'submittedAt', header: 'Submitted At' },
    { key: 'status', header: 'Status', transform: v => v?.charAt(0).toUpperCase() + v?.slice(1) },
    { key: 'approvedBy', header: 'Approved By' },
    { key: 'rejectionReason', header: 'Rejection Reason' },
    { key: 'notes', header: 'Notes' },
  ];
  exportToExcel(
    filteredRecords.value,
    columns,
    `Attendance_Approvals_${new Date().toISOString().split('T')[0]}`
  );
  toast.success('Attendance approvals exported successfully');
}
</script>

<style scoped>
.page-wrap {
  padding: 24px;
  background: #f8fafc;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 24px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.page-header .title {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.page-header .text-muted {
  font-size: 14px;
  color: #64748b;
}

/* Optimized Card Styling */
:deep(.card) {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
  background: white;
}

:deep(.card-header) {
  background: #4f46e5;
  color: white;
  border: none;
  padding: 14px 20px;
  font-weight: 600;
}

:deep(.card-body) {
  padding: 20px;
}

/* Stat Cards */
:deep(.border-start-warning) {
  border-left: 4px solid #f59e0b !important;
}

:deep(.border-start-success) {
  border-left: 4px solid #10b981 !important;
}

:deep(.border-start-danger) {
  border-left: 4px solid #ef4444 !important;
}

:deep(.border-start-primary) {
  border-left: 4px solid #4f46e5 !important;
}

/* Optimized Button Styling */
:deep(.btn) {
  border-radius: 8px;
  padding: 9px 18px;
  font-weight: 600;
  font-size: 14px;
  transition: opacity 0.15s ease;
  border: none;
}

:deep(.btn-primary) {
  background: #4f46e5;
}

:deep(.btn-success) {
  background: #10b981;
}

:deep(.btn-light) {
  background: white;
  border: 1px solid #e2e8f0;
  color: #475569;
}

:deep(.btn:hover) {
  opacity: 0.9;
}

:deep(.btn-sm) {
  padding: 6px 12px;
  font-size: 13px;
}

/* Optimized Form Controls */
:deep(.form-control),
:deep(.form-select) {
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  padding: 9px 12px;
  font-size: 14px;
}

:deep(.form-control:focus),
:deep(.form-select:focus) {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Optimized Table */
:deep(.table thead) {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}

:deep(.table thead th) {
  padding: 12px 14px;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  color: #475569;
  border: none;
}

:deep(.table tbody td) {
  padding: 14px;
  color: #334155;
  font-size: 14px;
  border-bottom: 1px solid #f1f5f9;
}

:deep(.table tbody tr:hover) {
  background: #f8fafc;
}

.table-warning {
  background-color: rgba(255, 193, 7, 0.08) !important;
}

/* Optimized Badge */
:deep(.badge) {
  padding: 5px 10px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 12px;
}

/* Avatar */
:deep(.avatar) {
  border-radius: 8px;
  border: 2px solid #f1f5f9;
}

/* Modal */
:deep(.modal-content) {
  border-radius: 12px;
  border: none;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

:deep(.modal-header) {
  border-bottom: 1px solid #f1f5f9;
  padding: 18px 24px;
}

:deep(.modal-title) {
  font-weight: 700;
  color: #1e293b;
  font-size: 18px;
}

:deep(.modal-body) {
  padding: 24px;
}

:deep(.modal-footer) {
  border-top: 1px solid #f1f5f9;
  padding: 16px 24px;
}

/* Alert */
:deep(.alert) {
  border-radius: 10px;
  border: none;
  padding: 12px 16px;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
  .page-wrap {
    padding: 16px;
  }

  .page-header {
    padding: 16px;
  }

  .page-header .title {
    font-size: 24px;
  }

  :deep(.card-body) {
    padding: 16px;
  }

  :deep(.table thead th) {
    font-size: 11px;
    padding: 10px 8px;
  }

  :deep(.table tbody td) {
    padding: 12px 8px;
    font-size: 13px;
  }
}
</style>
