<template>
  <div class="page-wrap">
    <!-- Page Header -->
    <PageHeader title="Visitors" subtitle="Register first-time visitors and partners">
      <template #actions>
        <button class="action-btn" @click="exportVisitors">
          <i class="bi bi-download me-2"></i>
          <span>Export</span>
        </button>
      </template>
    </PageHeader>

    <!-- KPI Summary Cards -->
    <div class="kpi-grid mb-4">
      <div class="kpi-card" style="--delay: 0s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)">
          <i class="bi bi-people-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Total Visitors</div>
          <div class="kpi-value">{{ total }}</div>
          <div class="kpi-sublabel">{{ visitorCount }} visitors</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.1s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%)">
          <i class="bi bi-star-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Partners</div>
          <div class="kpi-value">{{ partnerCount }}</div>
          <div class="kpi-sublabel">Active partners</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.2s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)">
          <i class="bi bi-person-heart"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Want to be Members</div>
          <div class="kpi-value">{{ memberCount }}</div>
          <div class="kpi-sublabel">Membership interest</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.3s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%)">
          <i class="bi bi-calendar-check"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">This Week</div>
          <div class="kpi-value">{{ weeklyCount }}</div>
          <div class="kpi-sublabel">New this week</div>
        </div>
      </div>
    </div>

    <CRow class="g-4">
      <!-- Register Form Card -->
      <CCol lg="4">
        <MaterialCard>
          <template #header>
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box bg-primary-subtle text-primary">
                <i class="bi bi-person-plus-fill"></i>
              </div>
              <div>
                <h3 class="md-title-large mb-1">Register Visitor</h3>
                <p class="text-muted small mb-0">Add a new visitor or partner</p>
              </div>
            </div>
          </template>

          <CForm class="visitor-form" @submit.prevent="addVisitor">
            <div class="mb-3">
              <MaterialInput v-model="form.name" label="Full Name" required />
            </div>

            <CRow class="g-3 mb-3">
              <CCol sm="6">
                <MaterialInput v-model="form.phone" label="Phone Number" required :error="phoneError" />
              </CCol>
              <CCol sm="6">
                <MaterialInput v-model="form.occupation" label="Occupation/Job" />
              </CCol>
            </CRow>

            <CRow class="g-3 mb-3">
              <CCol sm="6">
                <MaterialInput v-model="form.category" label="Category" type="select" required>
                  <option value="Visitor">Visitor</option>
                  <option value="Partner">Partner</option>
                  <option value="Wants to be a Member">Wants to be a Member</option>
                </MaterialInput>
              </CCol>
              <CCol sm="6">
                <MaterialInput v-model="form.service_type" label="Service Type" type="select" required>
                  <option v-for="s in serviceTypes" :key="s.id" :value="s.name">{{ s.name }}</option>
                </MaterialInput>
              </CCol>
            </CRow>

            <div class="mb-4">
              <MaterialInput v-model="form.date" type="date" label="First Visit Date" required />
            </div>

            <MaterialButton type="submit" class="w-100" :loading="isSubmitting" icon="bi bi-person-plus-fill">
              Register Visitor
            </MaterialButton>
          </CForm>
        </MaterialCard>

        <!-- Dynamic Stats Card -->
        <div class="stats-grid mt-4">
          <div class="md-mini-card animate-fadeInUp stagger-2">
            <div class="icon-box visitors">
              <i class="bi bi-people"></i>
            </div>
            <div class="details">
              <div class="value">{{ visitorCount }}</div>
              <div class="label">Visitors</div>
            </div>
          </div>
          <div class="md-mini-card animate-fadeInUp stagger-3">
            <div class="icon-box partners">
              <i class="bi bi-star"></i>
            </div>
            <div class="details">
              <div class="value">{{ partnerCount }}</div>
              <div class="label">Partners</div>
            </div>
          </div>
          <div class="md-mini-card animate-fadeInUp stagger-4 w-100 mt-2">
            <div class="icon-box members">
              <i class="bi bi-person-heart"></i>
            </div>
            <div class="details">
              <div class="value">{{ memberCount }}</div>
              <div class="label">Want to be Members</div>
            </div>
          </div>
        </div>
      </CCol>

      <!-- Recent Visitors Card -->
      <CCol lg="8">
        <MaterialCard>
          <template #header>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-3">
                <div class="header-icon-box bg-success-subtle text-success">
                  <i class="bi bi-clock-history"></i>
                </div>
                <div>
                  <h3 class="md-title-large mb-1">Recent Activity</h3>
                  <p class="text-muted small mb-0">Follow up with your visitors</p>
                </div>
              </div>
              <div class="search-box">
                <div class="input-group">
                  <span class="input-group-text bg-transparent border-end-0">
                    <i class="bi bi-search text-muted opacity-50"></i>
                  </span>
                  <input v-model="searchQuery" type="text" class="form-control border-start-0 ps-0"
                    placeholder="Search name/phone..." />
                </div>
              </div>
            </div>
          </template>

          <div class="visitors-table-container">
            <div v-if="isLoading" class="skeleton-container">
              <div v-for="i in 5" :key="i" class="skeleton-row">
                <div class="skeleton skeleton-avatar"></div>
                <div class="skeleton skeleton-text" style="width: 35%"></div>
                <div class="skeleton skeleton-text" style="width: 20%"></div>
                <div class="skeleton skeleton-badge" style="width: 15%"></div>
                <div class="skeleton skeleton-text" style="width: 15%"></div>
                <div class="skeleton skeleton-actions" style="width: 10%"></div>
              </div>
            </div>

            <div v-else-if="visitors.length === 0" class="empty-state p-5 text-center">
              <div class="empty-icon-wrap mb-3">
                <i class="bi bi-person-x"></i>
              </div>
              <h4 class="md-title-medium">No results found</h4>
              <p class="text-muted small">Try a different search or register a new visitor</p>
            </div>

            <div v-else>
              <CTable hover borderless align="middle" class="mb-0">
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell class="bg-transparent text-muted small pb-3">VISITOR</CTableHeaderCell>
                    <CTableHeaderCell class="bg-transparent text-muted small pb-3">OCCUPATION</CTableHeaderCell>
                    <CTableHeaderCell class="bg-transparent text-muted small pb-3">CATEGORY</CTableHeaderCell>
                    <CTableHeaderCell class="bg-transparent text-muted small pb-3">DATE</CTableHeaderCell>
                    <CTableHeaderCell class="bg-transparent text-muted small pb-3 text-end">ACTIONS</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  <CTableRow v-for="v in visitors" :key="v.id" class="visitor-row">
                    <CTableDataCell>
                      <div class="d-flex align-items-center gap-3">
                        <CAvatar :color="v.category === 'Partner'
                          ? 'success'
                          : v.category === 'Wants to be a Member'
                            ? 'info'
                            : 'primary'
                          " size="md" class="visitor-avatar">
                          {{ getInitials(v.name) }}
                        </CAvatar>
                        <div>
                          <div class="fw-bold text-dark">{{ v.name }}</div>
                          <div class="text-muted x-small">
                            <i class="bi bi-telephone-fill me-1 opacity-50"></i>{{ v.phone }}
                          </div>
                        </div>
                      </div>
                    </CTableDataCell>
                    <CTableDataCell>
                      <div class="text-dark small">{{ v.occupation || 'Not specified' }}</div>
                      <div class="text-muted x-small opacity-50">
                        {{ v.service_type || 'Sunday' }} Service
                      </div>
                    </CTableDataCell>
                    <CTableDataCell>
                      <span v-if="v.category === 'Wants to be a Member'"
                        class="badge rounded-pill px-3 py-2 border-0 bg-info-subtle text-info">
                        <i class="bi bi-person-heart me-1"></i>Member Int.
                      </span>
                      <span v-else class="badge rounded-pill px-3 py-2 border-0" :class="v.category === 'Partner'
                        ? 'bg-success-subtle text-success'
                        : 'bg-primary-subtle text-primary'
                        ">
                        <i :class="[
                          'bi me-1',
                          v.category === 'Partner' ? 'bi-star-fill' : 'bi-person',
                        ]"></i>
                        {{ v.category }}
                      </span>
                    </CTableDataCell>
                    <CTableDataCell>
                      <div class="text-dark fw-medium small">
                        {{ formatDate(v.first_visit_date || v.date) }}
                      </div>
                      <div class="text-muted x-small opacity-50">Registered</div>
                    </CTableDataCell>
                    <CTableDataCell class="text-end">
                      <div class="d-flex justify-content-end gap-2 action-buttons-wrapper">
                        <button type="button" class="md-icon-btn shadow-none bg-light text-primary"
                          :disabled="isLoading" title="Edit" @click.stop="openEditVisitor(v)">
                          <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button type="button" class="md-icon-btn shadow-none bg-light text-danger" :disabled="isLoading"
                          title="Delete" @click.stop="confirmDelete(v)">
                          <i class="bi bi-trash3-fill"></i>
                        </button>
                        <button type="button" class="md-icon-btn shadow-none bg-light text-success"
                          :disabled="isLoading" title="Convert to Member" @click.stop="openConvertModal(v)">
                          <i class="bi bi-person-check-fill"></i>
                        </button>
                      </div>
                    </CTableDataCell>
                  </CTableRow>
                </CTableBody>
              </CTable>

              <!-- Improved Pagination -->
              <div class="pagination-container">
                <div class="pagination-info">
                  Showing
                  <span class="fw-bold text-dark">{{
                    (pagination.current_page - 1) * pagination.per_page + 1
                  }}</span>
                  to
                  <span class="fw-bold text-dark">{{
                    Math.min(pagination.current_page * pagination.per_page, pagination.total)
                  }}</span>
                  of <span class="fw-bold text-dark">{{ pagination.total }}</span> records
                </div>
                <CPagination class="mb-0">
                  <CPaginationItem :disabled="pagination.current_page === 1"
                    @click="changePage(pagination.current_page - 1)">
                    <i class="bi bi-chevron-left"></i>
                  </CPaginationItem>
                  <CPaginationItem v-for="page in displayPages" :key="page" :active="page === pagination.current_page"
                    @click="page !== '...' && changePage(page)">
                    {{ page }}
                  </CPaginationItem>
                  <CPaginationItem :disabled="pagination.current_page === pagination.last_page"
                    @click="changePage(pagination.current_page + 1)">
                    <i class="bi bi-chevron-right"></i>
                  </CPaginationItem>
                </CPagination>
              </div>
            </div>
          </div>
        </MaterialCard>
      </CCol>
    </CRow>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <CModal v-model:visible="deleteModalVisible" alignment="center" backdrop="static" class="modal-bottom-sheet">
        <MaterialCard class="mb-0 border-0">
          <template #header>
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-3">
                <div class="header-icon-box bg-danger-subtle text-danger">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                  <h3 class="md-title-large mb-1">Confirm Deletion</h3>
                  <p class="text-muted small mb-0">This action cannot be undone</p>
                </div>
              </div>
              <button type="button" class="btn-close" :disabled="isDeleting" @click="closeDeleteModal"></button>
            </div>
          </template>

          <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>
            Are you sure you want to remove <strong>{{ visitorToDelete?.name }}</strong>?
          </div>

          <p class="text-muted">This will permanently delete the visitor record from the system.</p>

          <div class="d-flex gap-3 mt-4">
            <button class="md-btn md-btn-tonal flex-grow-1 py-3" :disabled="isDeleting" @click="closeDeleteModal">
              <i class="bi bi-x-lg me-2"></i>
              Cancel
            </button>
            <button class="md-btn md-btn-filled bg-danger flex-grow-1 py-3" :disabled="isDeleting"
              @click="deleteVisitor">
              <CSpinner v-if="isDeleting" size="sm" class="me-2" />
              <i v-else class="bi bi-trash3 me-2"></i>
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </MaterialCard>
      </CModal>
    </Teleport>

    <!-- Edit Modal -->
    <Teleport to="body">
      <CModal v-model:visible="editModalVisible" alignment="center" backdrop="static"
        class="modal-bottom-sheet edit-modal">
        <MaterialCard class="mb-0 border-0">
          <template #header>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="d-flex align-items-center gap-3">
                <div class="header-icon-box bg-primary-subtle text-primary">
                  <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                  <h3 class="md-title-large mb-1">Edit Visitor</h3>
                  <p class="text-muted small mb-0">Update visitor information</p>
                </div>
              </div>
              <button type="button" class="btn-close" :disabled="isSavingEdit" @click="closeEditModal"></button>
            </div>
          </template>

          <div class="modal-form-content">
            <div class="mb-3">
              <MaterialInput v-model="editVisitor.name" label="Full Name" required />
            </div>

            <CRow class="g-3 mb-3">
              <CCol sm="6">
                <MaterialInput v-model="editVisitor.phone" label="Phone Number" required />
              </CCol>
              <CCol sm="6">
                <MaterialInput v-model="editVisitor.occupation" label="Occupation/Job" />
              </CCol>
            </CRow>

            <CRow class="g-3 mb-3">
              <CCol sm="6">
                <MaterialInput v-model="editVisitor.category" label="Category" type="select" required>
                  <option value="Visitor">Visitor</option>
                  <option value="Partner">Partner</option>
                  <option value="Wants to be a Member">Wants to be a Member</option>
                </MaterialInput>
              </CCol>
              <CCol sm="6">
                <MaterialInput v-model="editVisitor.service_type" label="Service Type" type="select" required>
                  <option v-for="s in serviceTypes" :key="s.id" :value="s.name">{{ s.name }}</option>
                </MaterialInput>
              </CCol>
            </CRow>

            <div class="mb-4">
              <MaterialInput v-model="editVisitor.date" type="date" label="First Visit Date" required />
            </div>

            <div class="d-flex gap-3 mt-4">
              <button class="md-btn md-btn-tonal flex-grow-1 py-3" :disabled="isSavingEdit" @click="closeEditModal">
                <i class="bi bi-x-lg me-2"></i>
                Cancel
              </button>
              <button class="md-btn md-btn-filled flex-grow-1 py-3" :disabled="isSavingEdit" @click="saveEditVisitor">
                <CSpinner v-if="isSavingEdit" size="sm" class="me-2" />
                <i v-else class="bi bi-check-lg me-2"></i>
                {{ isSavingEdit ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </MaterialCard>
      </CModal>
    </Teleport>
    <!-- Convert To Member Modal -->
    <Teleport to="body">
      <CModal v-model:visible="convertModalVisible" alignment="center" backdrop="static" class="modal-bottom-sheet">
        <MaterialCard class="mb-0 border-0">
          <template #header>
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-3">
                <div class="header-icon-box bg-success-subtle text-success">
                  <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                  <h3 class="md-title-large mb-1">Convert to Member</h3>
                  <p class="text-muted small mb-0">Choose roles to attach to the new member</p>
                </div>
              </div>
              <button type="button" class="btn-close" :disabled="isConverting" @click="closeConvertModal"></button>
            </div>
          </template>

          <div class="modal-form-content p-3">
            <div class="mb-3">
              <label class="form-label">Roles</label>
              <CFormSelect multiple v-model="selectedRoles">
                <option v-for="r in availableRoles" :key="r.id || r.name" :value="r.name || r">{{ r.display_name ||
                  r.name
                  || r }}</option>
              </CFormSelect>
              <div class="form-text mt-2 text-muted">Selected roles will be attached to the converted user.</div>
            </div>

            <div class="d-flex gap-3 mt-4">
              <button class="md-btn md-btn-tonal flex-grow-1 py-3" :disabled="isConverting" @click="closeConvertModal">
                <i class="bi bi-x-lg me-2"></i>
                Cancel
              </button>
              <button class="md-btn md-btn-filled flex-grow-1 py-3" :disabled="isConverting" @click="doConvert">
                <CSpinner v-if="isConverting" size="sm" class="me-2" />
                <i v-else class="bi bi-person-check me-2"></i>
                {{ isConverting ? 'Converting...' : 'Convert' }}
              </button>
            </div>
          </div>
        </MaterialCard>
      </CModal>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import {
  CRow,
  CCol,
  CForm,
  CFormSelect,
  CModal,
  CSpinner,
  CTable,
  CTableHead,
  CTableRow,
  CTableHeaderCell,
  CTableBody,
  CTableDataCell,
  CAvatar,
  CPagination,
  CPaginationItem,
} from '@coreui/vue';
import PageHeader from '../components/shared/PageHeader.vue';
import { MaterialCard, MaterialInput, MaterialButton } from '../components/material';
import { useToast } from '../composables/useToast';
import { exportToExcel, formatDateForExport } from '../utils/export.js';
import { visitorsApi } from '../api/visitors';
import { serviceTypesApi } from '@/api/serviceTypes';
import { rolesApi } from '@/api/roles';

// State
const form = ref({
  name: '',
  phone: '',
  category: 'Visitor',
  service_type: 'Sunday',
  occupation: '',
  date: new Date().toISOString().slice(0, 10),
});
const phoneError = ref('');
const isSubmitting = ref(false);
const isLoading = ref(false);
const searchQuery = ref('');
const visitors = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10,
});
const counts = ref({ visitor_count: 0, partner_count: 0, member_count: 0 });

// Edit Modal State
const editModalVisible = ref(false);
const editVisitor = ref({
  id: '',
  name: '',
  phone: '',
  category: '',
  service_type: '',
  occupation: '',
  date: '',
});

// Delete Modal State
const deleteModalVisible = ref(false);
const visitorToDelete = ref(null);
const isDeleting = ref(false);

// Convert modal state
const convertModalVisible = ref(false);
const convertTarget = ref(null);
const availableRoles = ref([]);
const selectedRoles = ref([]);
const isConverting = ref(false);

// Edit loading state
const isSavingEdit = ref(false);

// Toast State
const toast = useToast();

// Computed
const total = computed(() => pagination.value.total);
const serviceTypes = ref([]);
const visitorCount = computed(() => counts.value.visitor_count);
const partnerCount = computed(() => counts.value.partner_count);
const memberCount = computed(() => counts.value.member_count);
const weeklyCount = computed(() => {
  const oneWeekAgo = new Date();
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
  return visitors.value.filter(v => {
    const visitDate = new Date(v.first_visit_date || v.date || v.created_at);
    return visitDate >= oneWeekAgo;
  }).length;
});

// Smart pagination display (show max 7 pages with ellipsis)
const displayPages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1);
  }

  if (current <= 3) {
    return [1, 2, 3, 4, 5, '...', last];
  }

  if (current >= last - 2) {
    return [1, '...', last - 4, last - 3, last - 2, last - 1, last];
  }

  return [1, '...', current - 1, current, current + 1, '...', last];
});

onMounted(() => {
  fetchVisitors();
  fetchRoles();
});

// Search watcher
watch(searchQuery, () => {
  pagination.value.current_page = 1;
  fetchVisitors();
});

async function fetchVisitors(page = 1) {
  isLoading.value = true;
  try {
    const res = await visitorsApi.getAll({
      page,
      search: searchQuery.value,
      per_page: pagination.value.per_page,
    });

    visitors.value = res.data?.data || [];
    pagination.value = {
      current_page: res.data?.current_page || 1,
      last_page: res.data?.last_page || 1,
      total: res.data?.total || 0,
      per_page: res.data?.per_page || 10,
    };
    counts.value.visitor_count = res.data?.visitor_count || 0;
    counts.value.partner_count = res.data?.partner_count || 0;
    counts.value.member_count = res.data?.member_count || 0;
  } catch (err) {
    toast.error('Failed to load visitors');
  } finally {
    isLoading.value = false;
  }
}

function openConvertModal(v) {
  convertTarget.value = v;
  // default to member role if available
  selectedRoles.value = ['member'];
  if (availableRoles.value && availableRoles.value.length) {
    // if 'member' exists, preselect it, else select the first role
    const names = availableRoles.value.map(r => (r.name ? r.name : r));
    if (names.includes('member')) selectedRoles.value = ['member'];
    else selectedRoles.value = [names[0]];
  }
  convertModalVisible.value = true;
}

function closeConvertModal() {
  if (isConverting.value) return;
  convertModalVisible.value = false;
  convertTarget.value = null;
  selectedRoles.value = [];
}

async function doConvert() {
  if (!convertTarget.value) return;
  isConverting.value = true;
  try {
    const vid = String(convertTarget.value.id);
    const res = await visitorsApi.convert(vid, { roles: selectedRoles.value });
    if (res.data && res.data.success) {
      toast.success(res.data.message || 'Converted to member');
      // update local visitor representation
      convertTarget.value.status = 'converted';
      convertTarget.value.convertedUser = res.data.user || null;
      await fetchVisitors(pagination.value.current_page);
      closeConvertModal();
    } else {
      toast.error(res.data?.message || 'Conversion failed');
    }
  } catch (err) {
    console.error('Conversion error', err);
    toast.error(err?.response?.data?.message || 'Conversion failed');
  } finally {
    isConverting.value = false;
  }
}

async function fetchRoles() {
  try {
    const res = await rolesApi.getAll();
    // rolesApi may return { data: [...] } or { data: { data: [...] } }
    availableRoles.value = res.data?.data || res.data || [];
  } catch (err) {
    console.warn('Failed to load roles', err);
    availableRoles.value = [];
  }
}

function changePage(page) {
  if (page < 1 || page > pagination.value.last_page) return;
  fetchVisitors(page);
}

// Helper Functions
function getInitials(name) {
  if (!name) return '?';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
}

function formatDate(date) {
  if (!date) return '-';

  // Parse date string (treat as local to avoid offset shifts)
  const d = new Date(date);
  if (isNaN(d.getTime())) return '-';

  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  // Use local matching for Today/Yesterday
  const dateStr = d.toLocaleDateString();
  if (dateStr === today.toLocaleDateString()) return 'Today';
  if (dateStr === yesterday.toLocaleDateString()) return 'Yesterday';

  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Validation
function validatePhone(phone) {
  const digits = (phone || '').replace(/\D/g, '');

  if (!phone || digits.length < 9) {
    return 'Phone number must be at least 9 digits';
  }

  if (/(.)\1{5,}/.test(digits)) {
    return 'Invalid phone number - too many repeating digits';
  }

  const lastEight = digits.slice(-8);
  if (/^(\d{2})\1{3}$/.test(lastEight)) {
    return 'Invalid phone number - repeating pattern detected';
  }

  const ghanaPrefixes = [
    '20',
    '23',
    '24',
    '25',
    '26',
    '27',
    '28',
    '29',
    '50',
    '54',
    '55',
    '56',
    '57',
    '59',
  ];

  let localNumber = digits;
  if (digits.startsWith('233')) {
    localNumber = digits.slice(3);
  } else if (digits.startsWith('0')) {
    localNumber = digits.slice(1);
  }

  if (localNumber.length === 9) {
    const prefix = localNumber.slice(0, 2);
    if (!ghanaPrefixes.includes(prefix) && !phone.startsWith('+')) {
      return 'Invalid Ghana mobile number prefix';
    }
  }

  return null;
}

// Actions
async function addVisitor() {
  phoneError.value = '';

  const error = validatePhone(form.value.phone);
  if (error) {
    phoneError.value = error;
    return;
  }

  if (!form.value.name.trim()) return;

  isSubmitting.value = true;
  try {
    const res = await visitorsApi.create({
      name: form.value.name,
      phone: form.value.phone,
      category: form.value.category,
      service_type: form.value.service_type,
      occupation: form.value.occupation,
      date: form.value.date,
    });

    if (res.data.success) {
      toast.success('Visitor registered successfully');
      form.value = {
        name: '',
        phone: '',
        category: 'Visitor',
        service_type: 'Sunday',
        occupation: '',
        date: new Date().toISOString().slice(0, 10),
      };
      fetchVisitors();
    }
  } catch (err) {
    toast.error('Failed to register visitor');
  } finally {
    isSubmitting.value = false;
  }
}

function openEditVisitor(v) {
  // Ensure we get a clean date string (YYYY-MM-DD)
  let dateVal = '';
  if (v.first_visit_date) {
    dateVal = String(v.first_visit_date).slice(0, 10);
  } else if (v.created_at) {
    dateVal = String(v.created_at).slice(0, 10);
  } else {
    dateVal = new Date().toISOString().slice(0, 10);
  }

  editVisitor.value = {
    ...v,
    service_type: v.service_type || 'Sunday',
    occupation: v.occupation || '',
    date: dateVal,
  };

  editModalVisible.value = true;
}

async function saveEditVisitor() {
  if (!editVisitor.value.id) {
    toast.error('No visitor selected for editing');
    return;
  }

  isSavingEdit.value = true;
  try {
    const vid = String(editVisitor.value.id);

    const res = await visitorsApi.update(vid, {
      name: editVisitor.value.name,
      phone: editVisitor.value.phone,
      category: editVisitor.value.category,
      service_type: editVisitor.value.service_type,
      occupation: editVisitor.value.occupation,
      date: editVisitor.value.date,
    });

    if (res.data.success) {
      toast.success(`${editVisitor.value.name} updated successfully`);
      closeEditModal();
      await fetchVisitors(pagination.value.current_page);
    } else {
      toast.error(res.data.message || 'Failed to update visitor');
    }
  } catch (err) {
    console.error('Update error:', err);
    const message = err.response?.data?.message || err.message || 'Failed to update visitor';
    toast.error(message);
  } finally {
    isSavingEdit.value = false;
  }
}

function confirmDelete(v) {
  visitorToDelete.value = v;
  deleteModalVisible.value = true;
}

function closeDeleteModal() {
  if (isDeleting.value) return;
  deleteModalVisible.value = false;
  visitorToDelete.value = null;
}

function closeEditModal() {
  if (isSavingEdit.value) return;
  editModalVisible.value = false;
  editVisitor.value = {
    id: '',
    name: '',
    phone: '',
    category: '',
    service_type: '',
    occupation: '',
    date: '',
  };
}

async function deleteVisitor() {
  if (!visitorToDelete.value) {
    toast.error('No visitor selected for deletion');
    return;
  }

  isDeleting.value = true;
  try {
    const vid = String(visitorToDelete.value.id);

    const res = await visitorsApi.delete(vid);

    if (res.data.success) {
      const name = visitorToDelete.value.name;
      toast.success(`${name} removed successfully`);

      closeDeleteModal();

      // Stay on current page if possible
      const maxPage = Math.ceil((pagination.value.total - 1) / pagination.value.per_page);
      const targetPage = Math.min(pagination.value.current_page, Math.max(1, maxPage));

      await fetchVisitors(targetPage);
    } else {
      toast.error(res.data.message || 'Failed to remove visitor');
    }
  } catch (err) {
    console.error('Delete error:', err);
    const message = err.response?.data?.message || err.message || 'Failed to remove visitor';
    toast.error(message);
  } finally {
    isDeleting.value = false;
  }
}

function exportVisitors() {
  const columns = [
    { key: 'name', header: 'Name' },
    { key: 'phone', header: 'Phone' },
    { key: 'category', header: 'Category' },
    { key: 'date', header: 'Date', transform: v => formatDateForExport(v) },
  ];
  exportToExcel(visitors.value, columns, `Visitors_${new Date().toISOString().split('T')[0]}`);
  toast.success('Visitors data has been exported to Excel');
}
</script>

<style scoped>
.page-wrap {
  padding: var(--md-space-6);
  min-height: 100vh;
}

.title {
  font-size: 2.25rem;
  font-weight: 800;
  margin-bottom: 0.25rem;
}

.gradient-text {
  background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
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

/* Form Styles */
.visitor-form .md-input-container {
  margin-bottom: 0;
}

.md-btn {
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.2s;
  border: none;
}

.md-btn-filled {
  background: var(--primary);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.md-btn-filled:hover {
  background: #4f46e5;
  transform: translateY(-1px);
}

.md-btn-tonal {
  background: #f1f5f9;
  color: #475569;
}

.md-btn-tonal:hover {
  background: #e2e8f0;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.md-mini-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #e2e8f0;
}

.icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.visitors .icon-box {
  background: #eff6ff;
  color: #3b82f6;
}

.partners .icon-box {
  background: #f0fdf4;
  color: #10b981;
}

.members .icon-box {
  background: #ecfeff;
  color: #0891b2;
}

.md-mini-card .value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1;
}

.md-mini-card .label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

/* Table */
.visitor-row {
  transition: all 0.2s;
  border-bottom: 1px solid #f1f5f9;
}

.visitor-row:hover {
  background: rgba(99, 102, 241, 0.02);
}

.x-small {
  font-size: 0.7rem;
}

.empty-icon-wrap {
  width: 80px;
  height: 80px;
  background: #f8fafc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  font-size: 2.5rem;
  color: #cbd5e1;
}

.search-box {
  width: 300px;
}

.search-box .input-group {
  background: #f8fafc;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.search-box .form-control {
  background: transparent;
  border: none;
  padding: 8px 12px;
}

.search-box .form-control:focus {
  box-shadow: none;
}

.action-buttons-wrapper {
  position: relative;
  z-index: 10;
}

.md-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  transition: all 0.2s;
  font-size: 1rem;
  cursor: pointer;
  background: transparent;
  position: relative;
  z-index: 1;
}

.md-icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.md-icon-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(0.95);
}

.md-icon-btn:active:not(:disabled) {
  transform: translateY(0);
}

.md-icon-btn i {
  pointer-events: none;
}

.pointer {
  cursor: pointer;
}

/* ======== KPI SUMMARY CARDS ======== */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideUp 0.5s ease-out forwards;
  opacity: 0;
  animation-delay: var(--delay, 0s);
}

.kpi-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
}

.kpi-icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.kpi-content {
  flex: 1;
}

.kpi-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.kpi-value {
  font-size: 1.875rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.25rem;
  line-height: 1;
}

.kpi-sublabel {
  font-size: 0.8rem;
  color: #94a3b8;
  font-weight: 500;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ======== SKELETON LOADERS ======== */
.skeleton-container {
  padding: 1.5rem 0;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  margin-bottom: 0.75rem;
  background: white;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.04);
}

.skeleton {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  flex-shrink: 0;
}

.skeleton-text {
  height: 16px;
  flex: 1;
}

.skeleton-badge {
  height: 28px;
  border-radius: 14px;
}

.skeleton-actions {
  height: 36px;
  border-radius: 10px;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

/* ======== IMPROVED PAGINATION ======== */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.25rem;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  background: #fafbfc;
}

.pagination-info {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

:deep(.pagination) {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 6px;
}

:deep(.pagination .page-item) {
  margin: 0;
}

:deep(.page-link) {
  border-radius: 10px !important;
  border: none !important;
  background: white !important;
  color: #64748b !important;
  min-width: 38px !important;
  height: 38px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-weight: 600 !important;
  font-size: 0.875rem !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  padding: 0 12px !important;
}

:deep(.page-link:hover) {
  background: linear-gradient(135deg,
      rgba(99, 102, 241, 0.1) 0%,
      rgba(139, 92, 246, 0.1) 100%) !important;
  color: #6366f1 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.15) !important;
}

:deep(.page-item.active .page-link) {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
  color: white !important;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
  transform: translateY(-2px) !important;
}

:deep(.page-item.disabled .page-link) {
  opacity: 0.4 !important;
  cursor: not-allowed !important;
  background: #f1f5f9 !important;
}

/* ======== ACTION BUTTON ======== */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.04);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.15);
}

/* ======== RESPONSIVE ======== */
@media (max-width: 768px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .kpi-card {
    padding: 1.25rem;
  }

  .pagination-container {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-info {
    font-size: 0.8rem;
  }
}
</style>

<!-- GLOBAL styles for Teleported modals (scoped CSS cannot reach Teleported elements) -->
<style>
.modal-bottom-sheet.modal {
  z-index: 9999 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  overflow-x: hidden !important;
  overflow-y: auto !important;
}

.modal-bottom-sheet.modal.show {
  display: block !important;
  opacity: 1 !important;
}

.modal-bottom-sheet .modal-dialog {
  position: relative;
  margin: 5vh auto !important;
  max-width: 600px;
  pointer-events: none;
}

.modal-bottom-sheet.modal.show .modal-dialog {
  transform: none !important;
  pointer-events: auto;
}

.modal-bottom-sheet .modal-content {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  pointer-events: auto;
  background-color: #fff !important;
  border: none !important;
  border-radius: 16px !important;
  outline: 0;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
}

.modal-bottom-sheet .modal-body {
  padding: 0 !important;
}

.modal-bottom-sheet .md-card {
  box-shadow: none !important;
}

/* Backdrop */
.modal-backdrop {
  z-index: 9998 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background-color: rgba(0, 0, 0, 0.5) !important;
}

.modal-backdrop.show {
  opacity: 1 !important;
}

/* Edit modal specific */
.edit-modal .modal-dialog {
  max-width: 600px;
}

/* Form content inside modal */
.modal-bottom-sheet .modal-form-content {
  padding: 0.5rem 0;
}

.modal-bottom-sheet .modal-form-content .md-input-container {
  margin-bottom: 0;
}

/* Close button in modals */
.modal-bottom-sheet .btn-close {
  background: transparent;
  border: none;
  opacity: 0.5;
  transition: opacity 0.2s;
  font-size: 1.25rem;
  padding: 0.5rem;
  cursor: pointer;
}

.modal-bottom-sheet .btn-close:hover:not(:disabled) {
  opacity: 1;
}

.modal-bottom-sheet .btn-close:disabled {
  opacity: 0.25;
  cursor: not-allowed;
}

/* Modal buttons */
.modal-bottom-sheet .md-btn {
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.2s;
  border: none;
}

.modal-bottom-sheet .md-btn-filled {
  background: var(--md-primary, #6366f1);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.modal-bottom-sheet .md-btn-filled:hover:not(:disabled) {
  transform: translateY(-1px);
}

.modal-bottom-sheet .md-btn-filled.bg-danger {
  background: #ef4444 !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.modal-bottom-sheet .md-btn-tonal {
  background: #f1f5f9;
  color: #475569;
}

.modal-bottom-sheet .md-btn-tonal:hover:not(:disabled) {
  background: #e2e8f0;
}

/* Header icon in modals */
.modal-bottom-sheet .header-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

/* Responsive modal */
@media (max-width: 768px) {
  .modal-bottom-sheet .modal-dialog {
    max-width: 95% !important;
    margin: 1rem auto !important;
  }
}
</style>
