<template>
  <div class="md-fade-in">
    <CRow class="g-4">
      <CCol lg="6">
        <CCard class="h-100 md-card">
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <span class="fw-bold">Service Types</span>
            <div class="d-flex gap-2">
              <CFormInput v-model="serviceTypeName" placeholder="New type..." size="sm" :disabled="isSaving"
                @keyup.enter="addServiceType" />
              <CButton color="primary" size="sm" :disabled="isSaving" @click="addServiceType">
                <CSpinner v-if="isSaving && !editingServiceType" size="sm" class="me-1" />
                <span>Add</span>
              </CButton>
            </div>
          </CCardHeader>
          <CCardBody>
            <CListGroup flush>
              <CListGroupItem v-for="s in serviceTypes" :key="s.id"
                class="d-flex justify-content-between align-items-center py-3">
                <div v-if="editingServiceType !== s.id" class="fw-medium">{{ s.name }}</div>
                <CFormInput v-else v-model="editServiceTypeName" size="sm" class="w-75" />

                <div class="action-btns">
                  <template v-if="editingServiceType === s.id">
                    <CButton color="success" size="sm" variant="ghost" :disabled="isSaving"
                      @click="saveServiceType(s.id)">
                      <CSpinner v-if="isSaving" size="sm" />
                      <i v-else class="bi bi-check-lg"></i>
                    </CButton>
                    <CButton color="secondary" size="sm" variant="ghost" :disabled="isSaving"
                      @click="cancelEditServiceType">
                      <i class="bi bi-x-lg"></i>
                    </CButton>
                  </template>
                  <template v-else>
                    <CButton color="primary" size="sm" variant="ghost" @click="startEditServiceType(s)">
                      <i class="bi bi-pencil"></i>
                    </CButton>
                    <CButton color="danger" size="sm" variant="ghost" @click="removeServiceType(s.id)">
                      <i class="bi bi-trash"></i>
                    </CButton>
                  </template>
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
            <i class="bi bi-info-circle me-2"></i>Note: These types are currently pre-defined in
            the database schema.
          </div>
          <ul>
            <li><strong>Sunday Service:</strong> Main weekly gathering.</li>
            <li><strong>Midweek:</strong> Wednesday or Thursday Bible study.</li>
            <li><strong>Special Event:</strong> Conferences or concerts.</li>
          </ul>
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
import { useToast } from '../../composables/useToast';

const toast = useToast();

const serviceTypes = ref<{ id: number; name: string }[]>([]);
const serviceTypeName = ref('');
const editingServiceType = ref<number | null>(null);
const editServiceTypeName = ref('');
const isSaving = ref(false);

function loadServiceTypes() {
  const raw = localStorage.getItem('service_types');
  const defaults = [
    { id: 1, name: 'Sunday Service' },
    { id: 2, name: 'Midweek Service' },
    { id: 3, name: 'Prayer Meeting' },
  ];
  try {
    serviceTypes.value = raw ? JSON.parse(raw) : defaults;
  } catch {
    serviceTypes.value = defaults;
  }
}

function persistServiceTypes() {
  localStorage.setItem('service_types', JSON.stringify(serviceTypes.value));
}

function addServiceType() {
  if (!serviceTypeName.value.trim()) return;
  isSaving.value = true;
  setTimeout(() => {
    serviceTypes.value.push({ id: Date.now(), name: serviceTypeName.value.trim() });
    serviceTypeName.value = '';
    persistServiceTypes();
    toast.success('Service type added', { color: 'success' });
    isSaving.value = false;
  }, 400);
}

function removeServiceType(id: number) {
  if (!confirm('Delete this service type?')) return;
  serviceTypes.value = serviceTypes.value.filter(s => s.id !== id);
  persistServiceTypes();
  toast.info('Service type removed', { color: 'danger' });
}

function startEditServiceType(s: { id: number; name: string }) {
  editingServiceType.value = s.id;
  editServiceTypeName.value = s.name;
}

function saveServiceType(id: number) {
  isSaving.value = true;
  setTimeout(() => {
    const s = serviceTypes.value.find(x => x.id === id);
    if (s && editServiceTypeName.value.trim()) {
      s.name = editServiceTypeName.value.trim();
      persistServiceTypes();
      toast.success('Service type updated', { color: 'success' });
    }
    editingServiceType.value = null;
    isSaving.value = false;
  }, 400);
}

function cancelEditServiceType() {
  editingServiceType.value = null;
  editServiceTypeName.value = '';
}

function refresh() {
  loadServiceTypes();
}

defineExpose({ refresh });

onMounted(() => {
  loadServiceTypes();
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
