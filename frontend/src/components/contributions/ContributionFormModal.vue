<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible" size="lg" backdrop="static">
      <CModalHeader>
        <CModalTitle>
          <i :class="editingId ? 'bi bi-pencil-square text-info' : 'bi bi-plus-circle text-primary'" class="me-2"></i>
          {{ editingId ? 'Edit Contribution' : 'Record New Contribution' }}
        </CModalTitle>
      </CModalHeader>
      <CModalBody class="p-4">
        <CForm @submit.prevent="onSave">
          <!-- Partner Section -->
          <div class="form-section mb-3">
            <label class="form-section-label mb-2">
              <i class="bi bi-person me-1 text-primary"></i> Partner / Contributor
            </label>
            <div class="d-flex gap-2">
              <CFormSelect v-model="form.memberId" class="flex-fill" :disabled="loadingMembers">
                <option value="">Anonymous (no partner selected)</option>
                <option v-for="m in members" :key="m.id" :value="m.id">
                  {{ m.name }}{{ m.phone ? ' - ' + m.phone : '' }}
                </option>
              </CFormSelect>
              <CButton color="primary" variant="ghost" title="Add New Partner" :disabled="loadingMembers"
                @click="$emit('add-partner')">
                <i class="bi bi-person-plus"></i>
              </CButton>
            </div>
            <div v-if="loadingMembers" class="text-muted small mt-1">
              <span class="spinner-border spinner-border-sm me-1"></span> Loading partners...
            </div>
          </div>

          <!-- Details -->
          <CRow class="g-3 mb-3">
            <CCol md="6">
              <CFormLabel>Type <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.type" :class="{ 'is-invalid': !form.type && formSubmitted }">
                <option value="">Select type...</option>
                <option value="tithe">Tithe</option>
                <option value="offering">Offering</option>
                <option value="special_seed">Special Seed</option>
                <option value="building_fund">Building Fund</option>
                <option value="missions">Missions</option>
                <option value="welfare">Welfare</option>
              </CFormSelect>
              <div v-if="!form.type && formSubmitted" class="invalid-feedback">Required</div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Amount (GH₵) <span class="text-danger">*</span></CFormLabel>
              <CInputGroup>
                <CInputGroupText>GH₵</CInputGroupText>
                <CFormInput v-model="form.amount" type="number" step="0.01" min="0.01" placeholder="0.00"
                  :class="{ 'is-invalid': (!form.amount || parseFloat(form.amount) <= 0) && formSubmitted }" />
              </CInputGroup>
              <div v-if="(!form.amount || parseFloat(form.amount) <= 0) && formSubmitted"
                class="text-danger small mt-1">
                Enter a valid amount
              </div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Date <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.date" type="date" :class="{ 'is-invalid': !form.date && formSubmitted }" />
              <div v-if="!form.date && formSubmitted" class="invalid-feedback">Required</div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Payment Method <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.paymentMethod" :class="{ 'is-invalid': !form.paymentMethod && formSubmitted }">
                <option value="">Select method...</option>
                <option value="cash">Cash</option>
                <option value="momo">Mobile Money</option>
                <option value="bank">Bank Transfer</option>
                <option value="cheque">Cheque</option>
              </CFormSelect>
              <div v-if="!form.paymentMethod && formSubmitted" class="invalid-feedback">Required</div>
            </CCol>
          </CRow>

          <!-- Reference (conditional) -->
          <CRow v-if="form.paymentMethod && form.paymentMethod !== 'cash'" class="g-3 mb-3">
            <CCol :md="form.paymentMethod === 'momo' ? 6 : 12">
              <CFormLabel>Reference Number</CFormLabel>
              <CFormInput v-model="form.reference"
                :placeholder="form.paymentMethod === 'momo' ? 'Transaction ID' : 'Reference number'" />
            </CCol>
            <CCol v-if="form.paymentMethod === 'momo'" md="6">
              <CFormLabel>Mobile Number</CFormLabel>
              <CInputGroup>
                <CInputGroupText>+233</CInputGroupText>
                <CFormInput v-model="form.mobileNumber" placeholder="501234567" />
              </CInputGroup>
            </CCol>
          </CRow>

          <!-- Notes -->
          <div>
            <CFormLabel>Notes <span class="text-muted small">(optional)</span></CFormLabel>
            <CFormTextarea v-model="form.notes" rows="2" placeholder="Additional notes..." />
          </div>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" :disabled="saving" @click="localVisible = false">Cancel</CButton>
        <CButton color="primary" :disabled="saving" @click="onSave">
          <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
          {{ editingId ? 'Update' : 'Record' }} Contribution
        </CButton>
      </CModalFooter>
    </CModal>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, reactive, onMounted } from 'vue';
import {
  CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CRow, CCol, CButton, CForm, CFormLabel, CFormSelect, CFormInput,
  CFormTextarea, CInputGroup, CInputGroupText,
} from '@coreui/vue';

interface Member { id: number; name: string; phone: string; }

interface FormData {
  memberId: string; type: string; amount: string; paymentMethod: string;
  date: string; reference: string; mobileNumber: string; notes: string;
}

const props = defineProps<{
  visible: boolean;
  editingId: number | null;
  initialForm: FormData;
  members: Member[];
  loadingMembers: boolean;
  saving: boolean;
}>();

const emit = defineEmits<{
  'update:visible': [value: boolean];
  'save': [form: FormData];
  'search-member': [];
  'add-partner': [];
}>();

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => {
  console.debug('[ContributionFormModal] props.visible ->', v);
  localVisible.value = v;
});
watch(localVisible, (v) => {
  console.debug('[ContributionFormModal] localVisible ->', v);
  emit('update:visible', v);
});

const form = reactive<FormData>({ ...props.initialForm });
const formSubmitted = ref(false);

// Sync form when modal opens
watch(() => props.visible, (v) => {
  console.debug('[ContributionFormModal] watch props.visible change:', v);
  if (v) {
    Object.assign(form, props.initialForm);
    formSubmitted.value = false;
  }
});

watch(() => props.initialForm, (f) => {
  Object.assign(form, f);
  formSubmitted.value = false;
}, { deep: true });

function onSave() {
  formSubmitted.value = true;
  if (!form.type || !form.amount || parseFloat(form.amount) <= 0 || !form.paymentMethod || !form.date) return;
  console.debug('[ContributionFormModal] emitting save', form);
  emit('save', { ...form });
}

onMounted(() => {
  console.debug('[ContributionFormModal] mounted props:', { visible: props.visible, editingId: props.editingId });
});
</script>

<style scoped>
.form-section-label {
  display: block;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
</style>
