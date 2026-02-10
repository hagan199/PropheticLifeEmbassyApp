<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible" class="modal-bottom-sheet">
      <CModalHeader>
        <CModalTitle>Add New Partner</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <CRow class="g-3">
            <CCol md="12">
              <CFormLabel>Name <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.name" placeholder="Enter partner's full name" />
            </CCol>
            <CCol md="12">
              <CFormLabel>Phone Number <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.phone" placeholder="e.g. 0241234567" />
            </CCol>
            <CCol md="12">
              <CFormLabel>Email</CFormLabel>
              <CFormInput v-model="form.email" type="email" placeholder="Enter email address" />
            </CCol>
          </CRow>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="localVisible = false">Cancel</CButton>
        <CButton color="primary" :disabled="!form.name || !form.phone || saving" @click="onSave">
          <i v-if="saving" class="bi bi-spinner bi-spin me-1"></i>
          <i v-else class="bi bi-check me-1"></i> Add Partner
        </CButton>
      </CModalFooter>
    </CModal>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, reactive } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter, CRow, CCol, CButton, CForm, CFormLabel, CFormInput } from '@coreui/vue';

const props = defineProps<{
  visible: boolean;
  saving?: boolean;
}>();

const emit = defineEmits<{
  'update:visible': [value: boolean];
  'save': [partner: { name: string; phone: string; email: string }];
}>();

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => { localVisible.value = v; });
watch(localVisible, (v) => { emit('update:visible', v); });

const form = reactive({ name: '', phone: '', email: '' });

// Reset form when modal opens
watch(() => props.visible, (v) => {
  if (v) {
    form.name = '';
    form.phone = '';
    form.email = '';
  }
});

function onSave() {
  if (!form.name || !form.phone) return;
  emit('save', { ...form });
}
</script>

<script lang="ts">
export default {};
</script>
