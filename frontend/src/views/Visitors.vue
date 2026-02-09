<template>
  <div class="page-wrap bg-light min-vh-100 p-3 p-md-4">
    <PageHeader title="Visitor Management" subtitle="Track registrations and engagement growth">
      <template #actions>
        <button class="btn-action-outline shadow-sm" @click="exportVisitors" :disabled="isLoading">
          <i class="bi bi-cloud-arrow-down fs-5"></i>
          <span>Export Data</span>
        </button>
      </template>
    </PageHeader>

    <VisitorsKpi 
      :total="total" 
      :visitorCount="visitorCount" 
      :partnerCount="partnerCount" 
      :memberCount="memberCount"
      :weeklyCount="weeklyCount" 
    />

    <CRow class="g-4">
      <CCol lg="4" xl="3">
        <MaterialCard class="border-0 shadow-sm sticky-lg-top" style="top: 1rem">
          <template #header>
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon bg-primary text-white rounded-3 shadow-sm">
                <i class="bi bi-person-plus-fill"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">Quick Register</h6>
                <small class="text-muted">New entry</small>
              </div>
            </div>
          </template>

          <CForm class="visitor-form p-1" @submit.prevent="addVisitor">
            <div class="mb-3">
              <MaterialInput v-model="form.name" label="Full Name" placeholder="Full name of visitor" required />
            </div>

            <div class="mb-3">
              <MaterialInput v-model="form.phone" label="Phone Number" required :error="phoneError" placeholder="024 XXX XXXX" />
            </div>

            <div class="mb-3">
              <MaterialInput v-model="form.occupation" label="Occupation" placeholder="Current job title" />
            </div>

            <CRow class="g-2 mb-3">
              <CCol sm="12">
                <MaterialInput v-model="form.category" label="Category" type="select" required>
                  <option disabled value="">Select Category</option>
                  <option v-for="t in visitorTypes" :key="t.id" :value="t.name">{{ t.name }}</option>
                </MaterialInput>
              </CCol>
              <CCol sm="12">
                <MaterialInput v-model="form.service_type" label="Service Type" type="select" required>
                  <option disabled value="">Select Service</option>
                  <option v-for="s in serviceTypes" :key="s.id" :value="s.name">{{ s.name }}</option>
                </MaterialInput>
              </CCol>
            </CRow>

            <div class="mb-4">
              <MaterialInput v-model="form.date" type="date" label="First Visit Date" required />
            </div>

            <MaterialButton 
              type="submit" 
              class="w-100 py-2 fw-bold" 
              :loading="isSubmitting" 
              icon="bi bi-plus-circle"
            >
              {{ isSubmitting ? 'Registering...' : 'Complete Registration' }}
            </MaterialButton>
          </CForm>
        </MaterialCard>
      </CCol>

      <CCol lg="8" xl="9">
        <MaterialCard class="border-0 shadow-sm">
          <template #header>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="header-icon bg-success-subtle text-success rounded-3">
                  <i class="bi bi-clock-history"></i>
                </div>
                <h6 class="fw-bold mb-0">Recent Activity</h6>
              </div>
              
              <div class="search-field position-relative">
                <i class="bi bi-search position-absolute start-0 ms-3 top-50 translate-middle-y text-muted opacity-50"></i>
                <input v-model="searchQuery" type="text" class="form-control form-control-sm ps-5 border-0 bg-light rounded-pill" placeholder="Search by name or phone..." />
              </div>
            </div>
          </template>

          <div class="table-responsive">
            <CTable hover borderless align="middle" class="mb-0">
              <CTableHead>
                <CTableRow>
                  <CTableHeaderCell class="bg-transparent text-muted small px-4 py-3">VISITOR</CTableHeaderCell>
                  <CTableHeaderCell class="bg-transparent text-muted small py-3">CONTEXT</CTableHeaderCell>
                  <CTableHeaderCell class="bg-transparent text-muted small py-3 text-center">STATUS</CTableHeaderCell>
                  <CTableHeaderCell class="bg-transparent text-muted small py-3 text-end pe-4">ACTIONS</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              
              <CTableBody>
                <tr v-if="isLoading" v-for="i in 5" :key="i">
                  <td colspan="4" class="p-3"><div class="skeleton-line shimmer"></div></td>
                </tr>

                <CTableRow v-for="v in visitors" :key="v.id" class="align-middle border-top border-light">
                  <CTableDataCell class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                      <CAvatar :color="getAvatarColor(v.category)" size="md" class="fw-bold text-white shadow-sm border-0">
                        {{ getInitials(v.name) }}
                      </CAvatar>
                      <div>
                        <div class="fw-bold text-dark">{{ v.name }}</div>
                        <div class="text-muted small"><i class="bi bi-phone me-1"></i>{{ v.phone }}</div>
                      </div>
                    </div>
                  </CTableDataCell>

                  <CTableDataCell>
                    <div class="small fw-medium">{{ v.occupation || 'N/A' }}</div>
                    <div class="text-muted x-small text-uppercase">{{ v.service_type || 'Sunday' }} Service</div>
                  </CTableDataCell>

                  <CTableDataCell class="text-center">
                    <span :class="getBadgeClass(v.category)">
                      <i :class="getCategoryIcon(v.category)" class="me-1"></i>
                      {{ v.category }}
                    </span>
                  </CTableDataCell>

                  <CTableDataCell class="text-end pe-4">
                    <div class="d-flex justify-content-end gap-1">
                      <button class="btn btn-icon-flat text-primary" @click="openEditVisitor(v)" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button class="btn btn-icon-flat text-success" @click="openConvertModal(v)" title="Make Member">
                        <i class="bi bi-person-check-fill"></i>
                      </button>
                      <button class="btn btn-icon-flat text-danger" @click="confirmDelete(v)" title="Delete">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </div>
                  </CTableDataCell>
                </CTableRow>
              </CTableBody>
            </CTable>
          </div>

          <div class="p-4 bg-light-subtle d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <span class="text-muted small">Showing {{ resultsInfo }}</span>
            <CPagination class="mb-0 custom-pagination">
              <CPaginationItem :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
                <i class="bi bi-chevron-left"></i>
              </CPaginationItem>
              <CPaginationItem v-for="p in displayPages" :key="p" :active="p === pagination.current_page" @click="p !== '...' && changePage(p)">
                {{ p }}
              </CPaginationItem>
              <CPaginationItem :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">
                <i class="bi bi-chevron-right"></i>
              </CPaginationItem>
            </CPagination>
          </div>
        </MaterialCard>
      </CCol>
    </CRow>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useToast } from '../composables/useToast';
import { visitorsApi } from '../api/visitors.js';
import { CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CPagination, CPaginationItem, CAvatar, CForm, CRow, CCol } from '@coreui/vue';
import { MaterialCard, MaterialInput, MaterialButton } from '../components/material';
import PageHeader from '../components/PageHeader.vue';
import VisitorsKpi from '../components/VisitorsKpi.vue';
import visitorTypes from '../components/visitors/visitorTypes.js';
import serviceTypes from '../components/visitors/serviceTypes.js';



// State
const getDefaultForm = () => ({
  name: '',
  phone: '',
  category: '',
  service_type: '',
  occupation: '',
  date: new Date().toISOString().slice(0, 10),
});

const form = ref(getDefaultForm());
const isLoading = ref(false);
const isSubmitting = ref(false);
const searchQuery = ref('');
const visitors = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 10 });
const toast = useToast();
const phoneError = ref('');

// Dynamic metadata
const visitorTypes = ref([]);
const serviceTypes = ref([]);
const counts = ref({ visitor_count: 0, partner_count: 0, member_count: 0 });

// Computed 
const total = computed(() => pagination.value.total);
const visitorCount = computed(() => counts.value.visitor_count);
const partnerCount = computed(() => counts.value.partner_count);
const memberCount = computed(() => counts.value.member_count);
const resultsInfo = computed(() => {
  const start = (pagination.value.current_page - 1) * pagination.value.per_page + 1;
  const end = Math.min(pagination.value.current_page * pagination.value.per_page, pagination.value.total);
  return `${start}-${end} of ${pagination.value.total}`;
});

// Methods
async function fetchVisitors(page = 1) {
  isLoading.value = true;
  try {
    const res = await visitorsApi.getAll({ page, search: searchQuery.value });
    visitors.value = res.data?.data || [];
    pagination.value = {
      current_page: res.data?.current_page || 1,
      last_page: res.data?.last_page || 1,
      total: res.data?.total || 0,
      per_page: res.data?.per_page || 10,
    };
    counts.value = {
      visitor_count: res.data?.visitor_count || 0,
      partner_count: res.data?.partner_count || 0,
      member_count: res.data?.member_count || 0
    };
  } catch (err) {
    toast.error('Could not sync data');
  } finally {
    isLoading.value = false;
  }
}

async function addVisitor() {
  phoneError.value = '';
  if (validatePhone(form.value.phone)) {
    phoneError.value = validatePhone(form.value.phone);
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = { ...form.value, first_visit_date: form.value.date };
    const res = await visitorsApi.create(payload);
    if (res.data.success) {
      toast.success(`${form.value.name} registered!`);
      form.value = getDefaultForm();
      fetchVisitors();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Registration failed');
  } finally {
    isSubmitting.value = false;
  }
}

// Styling Helpers
const getBadgeClass = (cat) => {
  const base = "badge rounded-pill px-3 py-2 border-0 ";
  if (cat === 'Partner') return base + "bg-success-subtle text-success";
  if (cat === 'Wants to be a Member') return base + "bg-info-subtle text-info";
  return base + "bg-primary-subtle text-primary";
};

const getAvatarColor = (cat) => cat === 'Partner' ? 'success' : cat === 'Wants to be a Member' ? 'info' : 'primary';

const getCategoryIcon = (cat) => cat === 'Partner' ? 'bi bi-star-fill' : cat === 'Wants to be a Member' ? 'bi bi-heart-fill' : 'bi bi-person-fill';

onMounted(() => {
  fetchVisitors();
  // Call other fetchers for types...
});
</script>

<style scoped>
.header-icon {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.search-field input:focus {
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
  background-color: white !important;
  border: 1px solid #dee2e6 !important;
}

.btn-icon-flat {
  padding: 8px;
  border-radius: 8px;
  background: transparent;
  border: 0;
  transition: background 0.2s;
}

.btn-icon-flat:hover {
  background: rgba(0,0,0,0.05);
}

.skeleton-line {
  height: 20px;
  background: #eee;
  border-radius: 4px;
  width: 100%;
}

.shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.btn-action-outline {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 20px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: white;
  font-weight: 600;
  color: #475569;
  transition: all 0.2s;
}

.btn-action-outline:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.x-small { font-size: 11px; }
</style>