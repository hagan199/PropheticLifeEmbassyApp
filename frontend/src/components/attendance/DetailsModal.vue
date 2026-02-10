<template>
  <CModal v-model:visible="localVisible" size="lg">
    <CModalHeader>
      <CModalTitle>Attendance Details</CModalTitle>
    </CModalHeader>
    <CModalBody v-if="record">
      <CRow class="g-3">
        <CCol md="6">
          <div class="text-muted small">Service</div>
          <div class="fw-semibold">{{ serviceLabel(record.serviceType) }}</div>
        </CCol>
        <CCol md="6">
          <div class="text-muted small">Date</div>
          <div class="fw-semibold">{{ formatDate(record.date) }}</div>
        </CCol>
        <CCol md="6">
          <div class="text-muted small">Count</div>
          <div class="fw-semibold fs-4">{{ record.count }}</div>
        </CCol>
        <CCol md="6">
          <div class="text-muted small">Status</div>
          <CBadge :color="statusColor(record.status)" class="fs-6">{{ record.status }}</CBadge>
        </CCol>
        <CCol md="6">
          <div class="text-muted small">Submitted By</div>
          <div class="d-flex align-items-center">
            <CAvatar :src="record.submittedBy?.avatar" size="sm" class="me-2" />
            {{ record.submittedBy?.name }}
          </div>
        </CCol>
        <CCol md="6">
          <div class="text-muted small">Submitted At</div>
          <div>{{ record.submittedAt }}</div>
        </CCol>
        <CCol v-if="record.approvedBy" md="6">
          <div class="text-muted small">Approved By</div>
          <div>{{ record.approvedBy }}</div>
        </CCol>
        <CCol v-if="record.rejectionReason" md="12">
          <div class="text-muted small">Rejection Reason</div>
          <CAlert color="danger">{{ record.rejectionReason }}</CAlert>
        </CCol>
        <CCol v-if="record.notes" md="12">
          <div class="text-muted small">Notes</div>
          <div>{{ record.notes }}</div>
        </CCol>
      </CRow>
    </CModalBody>
    <CModalFooter>
      <CButton color="secondary" @click="localVisible = false">Close</CButton>
    </CModalFooter>
  </CModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter, CRow, CCol, CButton, CAlert, CBadge, CAvatar } from '@coreui/vue';
const props = defineProps({ visible: { type: Boolean, default: false }, record: { type: Object, default: null } });
const emit = defineEmits(['update:visible']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => { localVisible.value = v; });
watch(localVisible, (v) => { emit('update:visible', v); });

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}

function serviceLabel(type) {
  const labels = { sunday: 'Sunday Service', friday: 'Friday Night', midweek: 'Midweek Service' };
  return labels[type] || type;
}

function statusColor(status) {
  const colors = { pending: 'warning', approved: 'success', rejected: 'danger' };
  return colors[status] || 'secondary';
}
</script>

<style scoped></style>
