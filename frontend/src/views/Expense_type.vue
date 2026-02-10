<template>
  <div class="page-wrap">
    <!-- PageHeader removed: title="Expense Categories" subtitle="Manage and organize financial categories for accurate tracking" -->
    <div class="d-flex gap-3 align-items-center flex-wrap mb-4">
      <div style="min-width: 300px">
        <MaterialInput v-model="newCat" label="New category name" placeholder="e.g. Welfare" :disabled="loading"
          class="mb-0" @keyup.enter="addCat" />
      </div>
      <MaterialButton :disabled="!newCat.trim() || loading" :loading="loading" icon="bi bi-plus-lg" @click="addCat">
        Add Category
      </MaterialButton>
    </div>

    <CRow class="g-4">
      <CCol lg="8">
        <MaterialCard>
          <template #header>
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h3 class="md-title-large mb-1">Expense Categories</h3>
                <p class="text-muted small mb-0">Organize expenses across the ministry</p>
              </div>
            </div>
          </template>

          <!-- Search Bar -->
          <div class="search-bar mb-4">
            <MaterialInput v-model="searchQuery" label="Search categories..." icon="bi bi-search" class="mb-0"
              @input="onSearchInput" />
          </div>

          <!-- Loading State -->
          <div v-if="loading && cats.length === 0" class="loading-state py-5 text-center">
            <div class="spinner-border text-primary mb-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Loading categories...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="cats.length === 0 && !loading" class="empty-state py-5 text-center">
            <div class="md-avatar md-avatar-xl bg-surface-container mx-auto mb-3">
              <i class="bi bi-tag text-muted"></i>
            </div>
            <h4 class="md-title-medium">
              {{ searchQuery ? 'No categories found' : 'No categories yet' }}
            </h4>
            <p class="text-muted small">
              {{
                searchQuery ? 'Try adjusting your search' : 'Add your first expense category above.'
              }}
            </p>
          </div>

          <!-- Categories List -->
          <div v-else class="category-list">
            <transition-group name="list" tag="div">
              <div v-for="c in cats" :key="c.id" class="category-item">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                  <div class="category-icon-wrapper">
                    <div class="category-icon" :class="getRandomColor(c.name)">
                      <i class="bi bi-tag-fill"></i>
                    </div>
                    <div v-if="!c.is_active" class="status-dot-overlay bg-danger"></div>
                  </div>
                  <div class="category-info flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                      <span class="category-name fw-bold">{{ c.name }}</span>
                      <CBadge v-if="!c.is_active" color="danger" variant="outline" size="sm" class="x-small">Inactive
                      </CBadge>
                    </div>
                    <div class="small text-muted text-truncate description-text" style="max-width: 300px">
                      {{ c.description || 'General ministry expense category' }}
                    </div>
                  </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                  <!-- Stats snippet in list -->
                  <div class="d-none d-md-flex flex-column align-items-end me-2">
                    <span class="fw-bold text-primary">{{ c.expenses_count || 0 }}</span>
                    <span class="x-small text-muted text-uppercase fw-semibold">Expenses</span>
                  </div>

                  <div class="category-actions">
                    <MaterialButton variant="text" icon="bi bi-pencil-square" icon-only title="Edit"
                      @click="renameCat(c)" />
                    <MaterialButton variant="text" icon="bi bi-trash-fill" icon-only class="text-danger" title="Delete"
                      @click="deleteCat(c)" />
                  </div>
                </div>
              </div>
            </transition-group>
          </div>

          <!-- Pagination -->
          <div v-if="meta && meta.last_page > 1"
            class="pagination-controls mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted small">
              Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to
              {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of
              {{ meta.total }} categories
            </div>
            <div class="d-flex gap-2">
              <MaterialButton variant="outlined" size="sm" icon="bi bi-chevron-left" icon-only
                :disabled="meta.current_page <= 1" @click="loadPage(meta.current_page - 1)" />
              <span class="align-self-center px-3 small fw-bold">
                {{ meta.current_page }} / {{ meta.last_page }}
              </span>
              <MaterialButton variant="outlined" size="sm" icon="bi bi-chevron-right" icon-only
                :disabled="meta.current_page >= meta.last_page" @click="loadPage(meta.current_page + 1)" />
            </div>
          </div>
        </MaterialCard>
      </CCol>

      <CCol lg="4">
        <MaterialCard title="Finance Guidance" class="sticky-top" style="top: 24px">
          <div class="tips-content">
            <div class="tip-item mb-4">
              <div class="d-flex gap-3 mb-2">
                <div class="tip-icon bg-primary-subtle text-primary">
                  <i class="bi bi-cloud-check-fill"></i>
                </div>
                <h6 class="mb-0 fw-bold">Enabled real-time server synchronization.</h6>
              </div>
              <p class="text-muted small mb-0">
                Categories help in organizing church expenses for better reporting and budgeting.
              </p>
            </div>

            <div class="tip-item mb-4">
              <div class="d-flex gap-3 mb-2">
                <div class="tip-icon bg-success-subtle text-success">
                  <i class="bi bi-check-circle-fill"></i>
                </div>
                <h6 class="mb-0 fw-bold">Common Categories</h6>
              </div>
              <p class="text-muted small mb-0">
                Utilities, Missions, Welfare, Repairs, Honorarium, Events, Staff Salary, Equipment,
                Transport.
              </p>
            </div>

            <div class="md-divider my-4"></div>

            <div class="status-summary p-4 bg-primary-subtle rounded-4 text-primary">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold text-uppercase opacity-75">Active Categories</span>
                <span class="fs-4 fw-black">{{ meta?.total || cats.length }}</span>
              </div>
              <p class="x-small mb-0 opacity-75">
                Categorizing your finances ensures accurate ministry audits and transparent
                reporting.
              </p>

              <div class="mt-4 pt-2 border-top border-primary border-opacity-10">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="x-small fw-bold">ORGANIZATION DEPTH</span>
                  <span class="x-small fw-bold">{{
                    Math.min(Math.round(((meta?.total || cats.length) / 30) * 100), 100)
                    }}%</span>
                </div>
                <div class="progress md-progress-linear bg-white bg-opacity-50" style="height: 6px">
                  <div class="progress-bar bg-primary" :style="{
                    width: Math.min(((meta?.total || cats.length) / 30) * 100, 100) + '%',
                  }"></div>
                </div>
              </div>
            </div>
          </div>
        </MaterialCard>
      </CCol>
    </CRow>

    <!-- Edit Modal -->
    <Teleport to="body">
      <CModal v-model:visible="showRenameModal" alignment="center" class="modal-bottom-sheet">
        <CModalHeader>
          <CModalTitle class="fw-bold fs-5">Edit Expense Type</CModalTitle>
        </CModalHeader>
        <CModalBody>
          <div class="p-2">
            <MaterialInput
              v-model="renameValue"
              label="Category Name"
              placeholder=" "
              required
              @keyup.enter="confirmRename"
            />
            <MaterialInput
              v-model="descriptionValue"
              label="Description (optional)"
              type="textarea"
              placeholder="Describe what kind of expenses fall under this category"
            />
          </div>
        </CModalBody>
        <CModalFooter class="border-0 pb-4 px-4">
          <MaterialButton variant="text" class="text-muted" @click="catToRename = null">
            Cancel
          </MaterialButton>
          <MaterialButton :disabled="!renameValue.trim() || loading" :loading="loading" @click="confirmRename">
            Save Changes
          </MaterialButton>
        </CModalFooter>
      </CModal>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  CRow,
  CCol,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CBadge,
} from '@coreui/vue';
import { MaterialCard, MaterialInput, MaterialButton } from '../components/material';
import { useToast } from '../composables/useToast';
import { expensesApi } from '../api/expenses';

const toast = useToast();
const newCat = ref('');
const cats = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const meta = ref(null);

// Edit modal state
const catToRename = ref(null);
const showRenameModal = computed({
  get: () => !!catToRename.value,
  set: (v) => { if (!v) catToRename.value = null; },
});
const renameValue = ref('');
const descriptionValue = ref('');

// Debounce timer for search
let searchTimeout = null;

/**
 * Load categories from API
 */
async function loadCats(params = {}) {
  try {
    loading.value = true;
    const response = await expensesApi.getTypes({
      per_page: 15,
      ...params,
    });

    if (response.data.success) {
      cats.value = response.data.data;
      meta.value = response.data.meta || null;
    }
  } catch (error) {
    console.error('Error loading categories:', error);
    toast.error('Failed to load categories');
  } finally {
    loading.value = false;
  }
}

/**
 * Load specific page
 */
function loadPage(page) {
  loadCats({
    page,
    search: searchQuery.value,
  });
}

/**
 * Handle search input with debounce
 */
function onSearchInput() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadCats({ search: searchQuery.value });
  }, 300);
}

/**
 * Add new category
 */
async function addCat() {
  if (!newCat.value.trim()) return;

  try {
    loading.value = true;
    const response = await expensesApi.createType({
      name: newCat.value.trim(),
      description: null,
    });

    if (response.data.success) {
      toast.success(`Category "${newCat.value.trim()}" added`);
      newCat.value = '';
      await loadCats({ search: searchQuery.value }); // Reload to reflect changes
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to add category';
    toast.error(message);
  } finally {
    loading.value = false;
  }
}

/**
 * Open edit modal
 */
function renameCat(cat) {
  catToRename.value = cat;
  renameValue.value = cat.name;
  descriptionValue.value = cat.description || '';
}

/**
 * Save edited category
 */
async function confirmRename() {
  if (!renameValue.value.trim() || !catToRename.value) return;

  try {
    loading.value = true;
    const response = await expensesApi.updateType(catToRename.value.id, {
      name: renameValue.value.trim(),
      description: descriptionValue.value.trim() || null,
    });

    if (response.data.success) {
      toast.success('Category updated successfully');
      catToRename.value = null;
      await loadCats({ search: searchQuery.value });
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to update category';
    toast.error(message);
  } finally {
    loading.value = false;
  }
}

/**
 * Delete category
 */
async function deleteCat(cat) {
  if (
    !confirm(
      `Are you sure you want to delete "${cat.name}"?\n\nIf this category is used by expenses, it will be deactivated instead.`
    )
  ) {
    return;
  }

  try {
    loading.value = true;
    const response = await expensesApi.deleteType(cat.id);

    if (response.data.success) {
      toast.info(response.data.message);
      await loadCats({ search: searchQuery.value });
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to delete category';
    toast.error(message);
  } finally {
    loading.value = false;
  }
}

/**
 * Generate color based on string
 */
function getRandomColor(str) {
  const colors = [
    'bg-primary-subtle text-primary',
    'bg-success-subtle text-success',
    'bg-info-subtle text-info',
    'bg-warning-subtle text-warning',
    'bg-danger-subtle text-danger',
  ];
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
}

onMounted(() => {
  loadCats();
});
</script>

<style scoped>
.page-wrap {
  padding: var(--md-space-6);
  min-height: 100vh;
}

.search-bar {
  max-width: 500px;
}

.category-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.category-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  background: var(--md-surface);
  border: 1px solid var(--md-outline-variant);
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.category-item:hover {
  border-color: var(--md-primary);
  transform: translateX(4px);
  background: var(--md-surface-container-low);
  box-shadow: var(--md-elevation-1);
}

.category-icon-wrapper {
  position: relative;
}

.status-dot-overlay {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid var(--md-surface);
}

.category-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.category-info {
  min-width: 0;
}

.category-name {
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.category-actions {
  display: flex;
  gap: 4px;
  opacity: 0.2;
  transition: opacity 0.3s;
  flex-shrink: 0;
}

.category-item:hover .category-actions {
  opacity: 1;
}

/* List Transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.list-move {
  transition: transform 0.3s ease;
}

.tip-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.x-small {
  font-size: 0.75rem;
}

.loading-state,
.empty-state {
  min-height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.pagination-controls {
  padding-top: var(--md-space-4);
  border-top: 1px solid var(--md-outline-variant);
}

:global(.theme-dark) .category-item {
  background: var(--md-surface-container-lowest);
}

:global(.theme-dark) .bg-light {
  background: var(--md-surface-container-low) !important;
}

@media (max-width: 768px) {
  .page-wrap {
    padding: var(--md-space-4);
  }

  .category-item {
    flex-wrap: wrap;
    gap: 12px;
  }

  .category-actions {
    opacity: 1;
    margin-left: auto;
  }
}
</style>
