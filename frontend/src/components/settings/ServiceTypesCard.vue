<template>
  <CRow class="g-4">
    <CCol lg="6">
      <CCard class="h-100 md-card">
        <CCardHeader class="d-flex justify-content-between align-items-center">
          <span class="fw-bold">Service Types</span>
          <div class="d-flex gap-2">
            <CFormInput v-model="serviceTypeName" placeholder="New type..." size="sm" :disabled="isSaving"
              @keyup.enter="addServiceType" />
            <CButton color="primary" size="sm" :disabled="isSaving" @click="addServiceType">Add</CButton>
          </div>
        </CCardHeader>
        <CCardBody>
          <CListGroup flush>
            <CListGroupItem v-for="s in serviceTypes" :key="s.id"
              class="d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold">{{ s.name }}</div>
              </div>
              <div class="d-flex gap-2">
                <CButton size="sm" color="outline" @click="startEditServiceType(s)" :disabled="isSaving">Edit</CButton>
                <CButton size="sm" color="danger" @click="removeServiceType(s.id)" :disabled="isSaving">Delete</CButton>
              </div>
            </CListGroupItem>
            <CListGroupItem v-if="serviceTypes.length === 0" class="text-center py-5 text-muted">
              No service types defined.
            </CListGroupItem>
          </CListGroup>
        </CCardBody>
      </CCard>
    </CCol>
    <CCol lg="6">
      <div class="md-help-card">
        <h5><i class="bi bi-info-circle me-2"></i>Usage Tips</h5>
        <p>
          Service types define the kinds of gatherings your church hosts. These will appear in
          the Attendance recording module.
        </p>
        <div class="alert alert-info py-2 small mb-3">
          <i class="bi bi-info-circle me-2"></i>Note: These types are stored locally (localStorage).
        </div>
        <ul>
          <li><strong>Sunday Service:</strong> Main weekly gathering.</li>
          <li><strong>Midweek:</strong> Wednesday or Thursday Bible study.</li>
          <li><strong>Special Event:</strong> Conferences or concerts.</li>
        </ul>
      </div>
    </CCol>
  </CRow>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { CRow, CCol, CCard, CCardHeader, CCardBody, CListGroup, CListGroupItem, CFormInput, CButton } from '@coreui/vue';
import { useToast } from '@/composables/useToast';
import { serviceTypesApi } from '@/api/serviceTypes';

const toast = useToast();
const serviceTypes = ref([] as Array<any>);
const serviceTypeName = ref('');
const isSaving = ref(false);
const editingServiceType = ref(null as null | number);
const editServiceTypeName = ref('');

async function loadServiceTypes() {
  try {
    const res = await serviceTypesApi.getAll();
    serviceTypes.value = res.data?.data || [];
  } catch (err) {
    // fallback to defaults when API fails
    serviceTypes.value = [
      { id: 1, name: 'Sunday Service' },
      { id: 2, name: 'Midweek Service' },
      { id: 3, name: 'Prayer Meeting' },
    ];
    toast.error('Failed to load service types');
  }
}

async function addServiceType() {
  if (!serviceTypeName.value.trim()) return;
  isSaving.value = true;
  try {
    const res = await serviceTypesApi.create({ name: serviceTypeName.value.trim() });
    serviceTypes.value.push(res.data.data);
    serviceTypeName.value = '';
    toast.success('Service type added');
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to add service type');
  } finally {
    isSaving.value = false;
  }
}

async function removeServiceType(id: number) {
  if (!confirm('Delete this service type?')) return;
  isSaving.value = true;
  try {
    await serviceTypesApi.delete(id);
    serviceTypes.value = serviceTypes.value.filter(s => s.id !== id);
    toast.info('Service type removed');
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to remove service type');
  } finally {
    isSaving.value = false;
  }
}

function startEditServiceType(s: any) {
  editingServiceType.value = s.id;
  editServiceTypeName.value = s.name;
}

async function saveServiceType(id: number) {
  if (!editServiceTypeName.value.trim()) return;
  isSaving.value = true;
  try {
    const res = await serviceTypesApi.update(id, { name: editServiceTypeName.value.trim() });
    const idx = serviceTypes.value.findIndex(x => x.id === id);
    if (idx !== -1) serviceTypes.value[idx] = res.data.data;
    toast.success('Service type updated');
    editingServiceType.value = null;
    editServiceTypeName.value = '';
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to update service type');
  } finally {
    isSaving.value = false;
  }
}

function cancelEditServiceType() {
  editingServiceType.value = null;
  editServiceTypeName.value = '';
}

onMounted(() => loadServiceTypes());

// Allow parent to refresh
const refresh = () => loadServiceTypes();

// expose refresh to parent via ref
defineExpose({ refresh });
</script>

<style scoped>
.md-help-card {
  background: #f8f9fa;
  border-radius: 1.5rem;
  padding: 2rem;
  border: 1px dashed #dee2e6;
}
</style>