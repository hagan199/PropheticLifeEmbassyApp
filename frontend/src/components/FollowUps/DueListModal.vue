<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible" size="lg">
      <CModalHeader>
        <CModalTitle>Follow-ups Due This Week</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CTable hover responsive>
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Visitor</CTableHeaderCell>
              <CTableHeaderCell>Due Date</CTableHeaderCell>
              <CTableHeaderCell>Last Contact</CTableHeaderCell>
              <CTableHeaderCell>Status</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="v in dueList" :key="v.id" :class="{ 'table-danger': isOverdue(v.nextFollowUp) }">
              <CTableDataCell>
                <div class="fw-semibold">{{ v.name }}</div>
                <div class="text-muted small">{{ v.phone }}</div>
              </CTableDataCell>
              <CTableDataCell>
                <span :class="isOverdue(v.nextFollowUp) ? 'text-danger fw-bold' : ''">
                  {{ formatDate(v.nextFollowUp) }}
                </span>
              </CTableDataCell>
              <CTableDataCell>{{ v.lastContactMethod || '—' }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="statusColor(v.status)">{{ statusLabel(v.status) }}</CBadge>
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="success" size="sm" class="me-1" @click="emitCall(v)">
                  <i class="bi bi-telephone"></i>
                </CButton>
                <CButton color="primary" size="sm" @click="emitLog(v)">
                  <i class="bi bi-pencil"></i> Log
                </CButton>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
      </CModalBody>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import {
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableHeaderCell,
  CTableDataCell,
  CButton,
  CBadge,
} from '@coreui/vue';

const props = defineProps({
  visible: { type: Boolean, default: false },
  dueList: { type: Array, default: () => [] },
  formatDate: { type: Function, required: true },
  isOverdue: { type: Function, required: true },
  statusColor: { type: Function, required: true },
  statusLabel: { type: Function, required: true },
});

const emit = defineEmits(['update:visible', 'call', 'log']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => (localVisible.value = v));
watch(localVisible, (v) => emit('update:visible', v));

function emitCall(v) {
  emit('call', v);
}

function emitLog(v) {
  emit('log', v);
}
</script>

<style scoped>
.table-danger {
  --cui-bg: #fff5f5;
}
</style>
