<template>
  <div class="md-fade-in">
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="settings-loading">
      <CSpinner color="primary" />
      <span class="ms-2">Loading categories...</span>
    </div>

    <CRow v-else class="g-4">
      <CCol lg="6">
        <CCard class="h-100 md-card">
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <span class="fw-bold">Expense Categories</span>
            <div class="d-flex gap-2">
              <CFormInput v-model="catName" placeholder="New category..." size="sm" :disabled="isSaving"
                style="min-width: 160px" @keyup.enter="addCategory" />
              <CFormInput v-model="catDescription" placeholder="Description..." size="sm" :disabled="isSaving"
                style="min-width: 200px" @keyup.enter="addCategory" />
              <CButton color="primary" size="sm" :disabled="isSaving || !catName.trim()" @click="addCategory">
                <CSpinner v-if="isSaving && !editingCategory" size="sm" class="me-1" />
                <span>Add</span>
              </CButton>
            </div>
          </CCardHeader>
          <CCardBody>
            <CListGroup flush>
              <CListGroupItem v-for="c in categories" :key="c.id"
                class="d-flex justify-content-between align-items-center py-3">
                <div class="flex-grow-1">
                  <div v-if="editingCategory !== c.id">
                    <span class="fw-bold">{{ c.name }}</span>
                    <div class="small text-muted">{{ c.description || 'No description' }}</div>
                  </div>
                  <div v-else class="d-flex gap-2">
                    <CFormInput v-model="editCategoryName" size="sm" class="w-50" placeholder="Name" />
                    <CFormInput v-model="editCategoryDescription" size="sm" class="w-50"
                      placeholder="Description" />
                  </div>
                </div>
                <div class="action-btns">
                  <template v-if="editingCategory === c.id">
                    <CButton color="success" size="sm" variant="ghost" :disabled="isSaving"
                      @click="saveCategory(c.id)">
                      <CSpinner v-if="isSaving" size="sm" />
                      <i v-else class="bi bi-check-lg"></i>
                    </CButton>
                    <CButton color="secondary" size="sm" variant="ghost" :disabled="isSaving"
                      @click="cancelEditCategory">
                      <i class="bi bi-x-lg"></i>
                    </CButton>
                  </template>
                  <template v-else>
                    <CButton color="primary" size="sm" variant="ghost" @click="startEditCategory(c)"><i
                        class="bi bi-pencil"></i></CButton>
                    <CButton color="danger" size="sm" variant="ghost" @click="removeCategory(c.id)"><i
                        class="bi bi-trash"></i></CButton>
                  </template>
                </div>
              </CListGroupItem>
              <CListGroupItem v-if="categories.length === 0" class="text-center py-4 text-muted">
                No expense categories found.
              </CListGroupItem>
            </CListGroup>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol lg="6" class="d-flex align-items-stretch">
        <div class="md-help-card w-100 d-flex flex-column justify-content-center align-items-center text-center">
          <h5 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Finance Guidance</h5>
          <p class="mb-2">
            Categories help in organizing church expenses for better reporting and budgeting.
          </p>
          <div class="alert alert-success py-2 small mb-3 w-75 mx-auto">
            <i class="bi bi-cloud-check me-2"></i>Enabled real-time server synchronization.
          </div>
          <p class="mb-0">
            Common categories: Utilities, Missions, Welfare, Repairs, Honorarium.
          </p>
        </div>
      </CCol>
    </CRow>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
  CRow,
  CCol,
  CCard,
  CCardBody,
  CCardHeader,
  CListGroup,
  CListGroupItem,
  CButton,
  CFormInput,
  CSpinner,
} from '@coreui/vue';
import { expensesApi } from '../../api';
import { useToast } from '../../composables/useToast';
import type { Category } from '../../composables/useSettingsUtils';

const toast = useToast();

const categories = ref<Category[]>([]);
const catName = ref('');
const catDescription = ref('');
const editingCategory = ref<number | null>(null);
const editCategoryName = ref('');
const editCategoryDescription = ref('');
const isLoading = ref(false);
const isSaving = ref(false);

async function loadCategories(): Promise<void> {
  isLoading.value = true;
  try {
    const { data } = await expensesApi.getTypes();
    categories.value = data.data || [];
  } catch (error: any) {
    toast.error('Failed to load categories', { color: 'danger' });
    console.error(error);
  } finally {
    isLoading.value = false;
  }
}

async function addCategory() {
  if (!catName.value.trim()) return;
  isSaving.value = true;
  try {
    const { data } = await expensesApi.createType({
      name: catName.value.trim(),
      description: catDescription.value.trim(),
    });
    categories.value.push(data.data);
    catName.value = '';
    catDescription.value = '';
    toast.success('Category added successfully', { color: 'success' });
  } catch (error) {
    toast.error('Failed to add category', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

async function removeCategory(id: number) {
  if (!confirm('Are you sure you want to delete this category?')) return;
  isSaving.value = true;
  try {
    categories.value = categories.value.filter(c => c.id !== id);
    toast.info('Category removed locally', { color: 'danger' });
  } catch (error) {
    toast.error('Failed to remove category', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function startEditCategory(c: Category) {
  editingCategory.value = c.id;
  editCategoryName.value = c.name;
  editCategoryDescription.value = c.description || '';
}

async function saveCategory(id: number) {
  if (!editCategoryName.value.trim()) return;
  isSaving.value = true;
  try {
    const { data } = await expensesApi.updateType(id, {
      name: editCategoryName.value.trim(),
      description: editCategoryDescription.value.trim(),
    });
    const idx = categories.value.findIndex(x => x.id === id);
    if (idx !== -1) categories.value[idx] = data.data;
    toast.success('Category updated', { color: 'success' });
    editingCategory.value = null;
  } catch (error) {
    toast.error('Failed to update category', { color: 'danger' });
  } finally {
    isSaving.value = false;
  }
}

function cancelEditCategory() {
  editingCategory.value = null;
  editCategoryName.value = '';
}

function refresh() {
  loadCategories();
}

defineExpose({ refresh });

onMounted(() => {
  loadCategories();
});
</script>

<style scoped>
.md-card {
  border: none;
  border-radius: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: transform 0.2s;
}

.md-card:hover {
  transform: translateY(-2px);
}

.md-card .card-header {
  background: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  font-size: 1.1rem;
}

.action-btns {
  display: flex;
  gap: 0.25rem;
}

.md-help-card {
  background: #f8f9fa;
  border-radius: 1.5rem;
  padding: 2rem;
  border: 1px dashed #dee2e6;
  height: 100%;
}

.md-help-card h5 {
  color: var(--md-primary);
  margin-bottom: 1rem;
}

.settings-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--md-primary);
  opacity: 0.8;
}

.md-fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
