<template>
  <CModal v-model:visible="localVisible">
    <CModalHeader>
      <CModalTitle>Reject Attendance</CModalTitle>
    </CModalHeader>
    <CModalBody>
      <p>
        Rejecting attendance for <strong>{{ record?.serviceType }}</strong> on
        <strong>{{ formatDate(record?.date) }}</strong>
      </p>
      <CAlert color="info" class="mb-3">
        <i class="bi bi-info-circle me-2"></i>
        The usher will be notified via SMS and can resubmit.
      </CAlert>
      <CFormLabel>Reason <span class="text-danger">*</span></CFormLabel>
      <CFormTextarea v-model="reason" rows="3" placeholder="Provide a reason for rejection..." maxlength="255" />
      <div class="text-muted small mt-1">{{ reason.length }}/255 characters</div>
    </CModalBody>
    <CModalFooter>
      <CButton color="secondary" @click="localVisible = false">Cancel</CButton>
      <CButton color="danger" :disabled="!reason.trim()" @click="onReject">Reject</CButton>
    </CModalFooter>
  </CModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter, CAlert, CFormLabel, CFormTextarea, CButton } from '@coreui/vue';

const props = defineProps({
  visible: { type: Boolean, default: false },
  record: { type: Object, default: null },
});
const emit = defineEmits(['update:visible', 'rejected']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => { localVisible.value = v; });
watch(localVisible, (v) => { emit('update:visible', v); });

const reason = ref('');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('en-GB', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function onReject() {
  emit('rejected', reason.value);
  reason.value = '';
  localVisible.value = false;
}
</script>

<style scoped></style>
