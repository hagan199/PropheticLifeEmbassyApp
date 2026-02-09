<template>
  <div class="users-filters">
    <CRow class="g-3 align-items-end">
      <CCol md="4">
        <CFormLabel>Search</CFormLabel>
        <CFormInput
          v-model="localFilters.search"
          placeholder="Name or phone..."
          @input="handleSearch"
        />
      </CCol>
      <CCol md="3">
        <CFormLabel>Role</CFormLabel>
        <CFormSelect v-model="localFilters.role" @change="handleFilterChange">
          <option value="">All Roles</option>
          <option v-for="r in roleOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
        </CFormSelect>
      </CCol>
      <CCol md="3">
        <CFormLabel>Status</CFormLabel>
        <CFormSelect v-model="localFilters.status" @change="handleFilterChange">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </CFormSelect>
      </CCol>
      <CCol md="2">
        <CButton color="light" class="w-100" @click="handleReset">Reset</CButton>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CRow, CCol, CFormLabel, CFormInput, CFormSelect, CButton } from '@coreui/vue';

const props = defineProps({
  filters: {
    type: Object,
    required: true
  },
  roleOptions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:filters', 'search', 'filter-change', 'reset']);

const localFilters = ref({ ...props.filters });

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = { ...newFilters };
}, { deep: true });

// Handle search with debounce
let searchTimeout;
function handleSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit('search', localFilters.value.search);
  }, 400);
}

function handleFilterChange() {
  emit('filter-change', localFilters.value);
}

function handleReset() {
  localFilters.value = { search: '', role: '', status: '' };
  emit('reset');
}
</script>

<style scoped>
.users-filters {
  width: 100%;
}
</style>