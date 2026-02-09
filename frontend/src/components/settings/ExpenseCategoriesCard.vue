<template>
  <CCard class="h-100 md-card">
    <CCardHeader class="d-flex justify-content-between align-items-center">
      <span class="fw-bold">Expense Categories</span>
      <div class="d-flex gap-2">
        <CFormInput v-model="catName" placeholder="New category..." size="sm" :disabled="isSaving"
          style="min-width:160px" @keyup.enter="addCategory" />
        <CFormInput v-model="catDescription" placeholder="Description..." size="sm" :disabled="isSaving"
          style="min-width:200px" @keyup.enter="addCategory" />
        <CButton color="primary" size="sm" :disabled="isSaving || !catName.trim()" @click="addCategory">Add</CButton>
      </div>
    </CCardHeader>
    <CCardBody>
      <div v-if="isLoading" class="text-center py-4">
        <CSpinner color="primary" />
      </div>
      <CListGroup v-else flush>
        <CListGroupItem v-for="c in categories" :key="c.id" class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">{{ c.name }}</div>
            <div class="text-muted small">{{ c.description }}</div>
          </div>
          <div class="d-flex gap-2 align-items-start">
            <CButton size="sm" color="outline" @click="startEditCategory(c)" :disabled="isSaving">Edit</CButton>
            <CButton size="sm" color="danger" @click="removeCategory(c.id)" :disabled="isSaving">Delete</CButton>
          </div>
        </CListGroupItem>
        <CListGroupItem v-if="categories.length === 0" class="text-center py-4 text-muted">No categories yet.
        </CListGroupItem>
      </CListGroup>
    </CCardBody>
  </CCard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { CCard, CCardHeader, CCardBody, CListGroup, CListGroupItem, CFormInput, CButton, CSpinner } from '@coreui/vue';
import { expensesApi } from '@/api';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const categories = ref([] as any[]);
const catName = ref('');
const catDescription = ref('');
const isSaving = ref(false);
const isLoading = ref(false);
const editingCategory = ref<number | null>(null);
const editCategoryName = ref('');
const editCategoryDescription = ref('');

async function loadCategories() {
  isLoading.value = true;
  try {
    const { data } = await expensesApi.getTypes();
    categories.value = data.data || [];
  } catch (error) {
    toast.error('Failed to load categories');
  } finally {
    isLoading.value = false;
  }
}

async function addCategory() {
  if (!catName.value.trim()) return;
  isSaving.value = true;
  try {
    const { data } = await expensesApi.createType({ name: catName.value.trim(), description: catDescription.value.trim() });
    categories.value.push(data.data);
    catName.value = '';
    catDescription.value = '';
    toast.success('Category added');
  } catch (error) {
    toast.error('Failed to add category');
  } finally {
    isSaving.value = false;
  }
}

async function removeCategory(id: number) {
  if (!confirm('Are you sure you want to delete this category?')) return;
  isSaving.value = true;
  try {
    // Optimistic
    categories.value = categories.value.filter(c => c.id !== id);
    toast.info('Category removed locally');
  } catch (error) {
    toast.error('Failed to remove category');
  } finally {
    isSaving.value = false;
  }
}

function startEditCategory(c: any) {
  editingCategory.value = c.id;
  editCategoryName.value = c.name;
  editCategoryDescription.value = c.description || '';
}

async function saveCategory(id: number) {
  if (!editCategoryName.value.trim()) return;
  isSaving.value = true;
  try {
    const { data } = await expensesApi.updateType(id, { name: editCategoryName.value.trim(), description: editCategoryDescription.value.trim() });
    const idx = categories.value.findIndex(x => x.id === id);
    if (idx !== -1) categories.value[idx] = data.data;
    toast.success('Category updated');
    editingCategory.value = null;
  } catch (error) {
    toast.error('Failed to update category');
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => loadCategories());

const refresh = () => loadCategories();

defineExpose({ refresh });
</script>

<style scoped>
.md-card {
  border-radius: 1rem;
}
</style>