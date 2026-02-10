<template>
  <div class="page-container">
    <div class="page-wrap animate-fade-in">
      <PageHeader title="Departments" subtitle="Organize ministry units and members">
        <!-- @ts-ignore -->
        <template #actions>
          <div class="d-flex gap-2">
            <button class="md-btn md-btn-outlined ripple" @click="exportDepartments">
              <i class="bi bi-file-earmark-excel me-2"></i> Export
            </button>
            <button class="md-btn md-btn-filled ripple" @click="openDeptModal()">
              <i class="bi bi-plus-lg me-2"></i> Add Department
            </button>
          </div>
        </template>
      </PageHeader>

      <div class="md-content-grid">
        <div class="md-col-12">
          <MaterialCard title="Ministry Units" class="ministry-units-card shadow-sm">
            <div v-if="loading" class="d-flex flex-column align-items-center py-5">
              <div class="md-progress-circular mb-3">
                <svg viewBox="0 0 50 50">
                  <circle cx="25" cy="25" r="20"></circle>
                </svg>
              </div>
              <span class="text-muted">Loading units...</span>
            </div>

            <div v-else-if="depts.length === 0" class="text-center py-5 animate-up">
              <div class="empty-state-icon mx-auto mb-3">
                <i class="bi bi-folder-x"></i>
              </div>
              <h3 class="md-title-medium">No departments found</h3>
              <p class="text-muted">Get started by creating your first ministry unit.</p>
            </div>

            <div v-else class="dept-list-container">
              <TransitionGroup name="list-complete" tag="div" class="dept-grid">
                <div v-for="d in paginatedDepartments" :key="d.id" class="dept-card list-complete-item" tabindex="0"
                  @click="viewDepartment(d)">
                  <div class="d-flex align-items-center flex-grow-1 gap-3">
                    <div class="md-avatar md-avatar-lg shadow-sm" :class="getRandomColor(d.name)">
                      <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                      <h4 class="md-title-medium mb-1 fw-bold text-dark">{{ d.name }}</h4>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-primary-subtle text-primary">
                          <i class="bi bi-person me-1"></i> {{ d.member_count || 0 }} Members
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="dept-actions">
                    <button class="md-btn md-btn-icon md-btn-text" title="Edit" @click.stop="openDeptModal(d)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="md-btn md-btn-icon md-btn-text text-danger" title="Delete"
                      @click.stop="confirmDeleteRequest(d)">
                      <i class="bi bi-trash"></i>
                    </button>
                    <i class="bi bi-chevron-right text-muted ms-2"></i>
                  </div>
                </div>
              </TransitionGroup>

              <div v-if="totalPages > 1" class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <span class="text-muted small">Page {{ currentPage }} of {{ totalPages }}</span>
                <div class="d-flex gap-2">
                  <button class="md-btn md-btn-outlined md-btn-sm" :disabled="currentPage === 1" @click="currentPage--">
                    Prev
                  </button>
                  <button class="md-btn md-btn-outlined md-btn-sm" :disabled="currentPage === totalPages"
                    @click="currentPage++">
                    Next
                  </button>
                </div>
              </div>
            </div>
          </MaterialCard>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <CModal v-model:visible="showEditModal" alignment="center">
        <div
          class="modal-header-custom border-bottom p-3"
          :class="deptForm.id ? 'bg-info-container' : 'bg-primary-container'"
        >
          <h5 class="fw-bold mb-0">{{ deptForm.id ? 'Update' : 'New' }} Department</h5>
          <button class="btn-close" @click="showEditModal = false"></button>
        </div>
        <div class="modal-body p-4">
          <form @submit.prevent="handleDeptSubmit">
            <div class="md-input-wrapper mb-4">
              <input v-model="deptForm.name" type="text" class="md-input" placeholder=" " required />
              <label class="md-label-floating">Department Name</label>
            </div>
            <div class="md-input-wrapper mb-4">
              <textarea v-model="deptForm.description" class="md-input" placeholder=" " rows="3"></textarea>
              <label class="md-label-floating">Description</label>
            </div>
            <button type="submit" class="md-btn md-btn-filled w-100 ripple" :disabled="isSubmitting">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
              {{ isSubmitting ? 'Saving...' : deptForm.id ? 'Save Changes' : 'Create Department' }}
            </button>
          </form>
        </div>
      </CModal>
    </Teleport>

    <DepartmentMembersModal
      v-model:visible="showMembersModal"
      :department="selectedDept"
      @updated="fetchDepartments"
    />

    <Teleport to="body">
      <CModal v-model:visible="confirmModal.show" alignment="center">
        <div class="p-4 text-center">
          <div class="md-avatar md-avatar-xl bg-error-container text-error mx-auto mb-3">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <h4 class="fw-bold mb-2">{{ confirmModal.title }}</h4>
          <p class="text-muted mb-4">{{ confirmModal.message }}</p>
          <div class="d-flex gap-2 justify-content-center">
            <button class="md-btn md-btn-text" @click="confirmModal.show = false">Cancel</button>
            <button
              class="md-btn md-btn-filled bg-danger text-white border-0"
              :disabled="isDeleting"
              @click="confirmModal.onConfirm"
            >
              {{ isDeleting ? 'Processing...' : 'Confirm Delete' }}
            </button>
          </div>
        </div>
      </CModal>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import { CModal } from '@coreui/vue';
import PageHeader from '../components/shared/PageHeader.vue';
import MaterialCard from '../components/material/MaterialCard.vue';
import { exportToExcel } from '../utils/export.js';
import { departmentsApi } from '../api/departments';
import { useToast } from '../composables/useToast';
import DepartmentMembersModal from '../components/departments/DepartmentMembersModal.vue';

// Types
interface Department {
  id: number;
  name: string;
  description?: string;
}

interface DeptForm {
  id: number | null;
  name: string;
  description: string;
}

interface ConfirmModal {
  show: boolean;
  title: string;
  message: string;
  onConfirm: () => void;
}

const toast = useToast();

// Unified States
const depts = ref<Department[]>([]);
const loading = ref<boolean>(false);
const currentPage = ref<number>(1);
const pageSize = 6;
const isSubmitting = ref<boolean>(false);
const isDeleting = ref<boolean>(false);

// Modal Logic
const showEditModal = ref<boolean>(false);
const showMembersModal = ref<boolean>(false);
const confirmModal = reactive<ConfirmModal>({ show: false, title: '', message: '', onConfirm: () => { } });
const deptForm = reactive<DeptForm>({ id: null, name: '', description: '' });
const selectedDept = ref<Department | null>(null);

// Computed Pagination
const paginatedDepartments = computed<Department[]>(() => {
  const start = (currentPage.value - 1) * pageSize;
  return depts.value.slice(start, start + pageSize);
});
const totalPages = computed<number>(() => Math.ceil(depts.value.length / pageSize));

// API Methods
async function fetchDepartments(): Promise<void> {
  loading.value = true;
  try {
    const res = await departmentsApi.getAll();
    if (res.data.success) depts.value = res.data.data;
  } catch (e) {
    toast.error('Failed to load departments');
  } finally {
    loading.value = false;
  }
}

const openDeptModal = (dept: Department | null = null): void => {
  if (dept) {
    Object.assign(deptForm, { id: dept.id, name: dept.name, description: dept.description || '' });
  } else {
    Object.assign(deptForm, { id: null, name: '', description: '' });
  }
  showEditModal.value = true;
};

async function handleDeptSubmit(): Promise<void> {
  isSubmitting.value = true;
  try {
    const payload = { name: deptForm.name, description: deptForm.description };
    if (deptForm.id) {
      await departmentsApi.update(deptForm.id, payload);
      toast.success('Department updated');
    } else {
      const res = await departmentsApi.create(payload);
      depts.value.unshift(res.data.data);
      toast.success('Department created');
    }
    showEditModal.value = false;
    fetchDepartments();
  } catch (e) {
    toast.error('Operation failed');
  } finally {
    isSubmitting.value = false;
  }
}

function confirmDeleteRequest(dept: Department): void {
  confirmModal.title = 'Delete Department?';
  confirmModal.message = `Remove ${dept.name}? This cannot be undone.`;
  confirmModal.onConfirm = async () => {
    isDeleting.value = true;
    try {
      await departmentsApi.delete(dept.id);
      depts.value = depts.value.filter(d => d.id !== dept.id);
      toast.success('Deleted');
      confirmModal.show = false;
    } finally {
      isDeleting.value = false;
    }
  };
  confirmModal.show = true;
}

function viewDepartment(dept: Department): void {
  selectedDept.value = dept;
  showMembersModal.value = true;
}

// Helpers
const getRandomColor = (s: string): string => {
  const variants = [
    'bg-primary-subtle text-primary',
    'bg-success-subtle text-success',
    'bg-info-subtle text-info',
  ];
  return variants[s.length % variants.length];
};

function exportDepartments(): void {
  exportToExcel(depts.value, [{ key: 'name', header: 'Name' }], 'Departments');
}

onMounted(fetchDepartments);
</script>

<style scoped>
.page-container {
  min-height: 100vh;
}

.dept-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.dept-card {
  background: #fff;
  border: 1px solid #edf2f7;
  border-radius: 12px;
  padding: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: all 0.2s ease;
}

.dept-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border-color: #6366f1;
}

.list-complete-item {
  transition: all 0.5s ease;
}

.list-complete-enter-from,
.list-complete-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.modal-header-custom {
  border-radius: 16px 16px 0 0;
}
</style>
