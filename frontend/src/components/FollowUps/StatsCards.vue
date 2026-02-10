<template>
  <CRow class="g-4 mb-4">
    <CCol sm="6" xl="3">
      <CCard class="stat-card border-0 shadow-sm" @click="$emit('filter', '')">
        <CCardBody>
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted small text-uppercase">Total Visitors</div>
              <div class="fs-3 fw-bold">{{ stats.total }}</div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
              <i class="bi bi-people"></i>
            </div>
          </div>
        </CCardBody>
      </CCard>
    </CCol>
    <CCol sm="6" xl="3">
      <CCard class="stat-card border-0 shadow-sm" @click="$emit('filter', 'not_contacted')">
        <CCardBody>
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted small text-uppercase">Not Contacted</div>
              <div class="fs-3 fw-bold text-danger">{{ stats.notContacted }}</div>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
              <i class="bi bi-exclamation-circle"></i>
            </div>
          </div>
        </CCardBody>
      </CCard>
    </CCol>
    <CCol sm="6" xl="3">
      <CCard class="stat-card border-0 shadow-sm" @click="$emit('filter', 'engaged')">
        <CCardBody>
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted small text-uppercase">Engaged</div>
              <div class="fs-3 fw-bold text-warning">{{ stats.engaged }}</div>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
              <i class="bi bi-chat-heart"></i>
            </div>
          </div>
        </CCardBody>
      </CCard>
    </CCol>
    <CCol sm="6" xl="3">
      <CCard class="stat-card border-0 shadow-sm" @click="$emit('filter', 'converted')">
        <CCardBody>
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted small text-uppercase">Converted</div>
              <div class="fs-3 fw-bold text-success">{{ stats.converted }}</div>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
              <i class="bi bi-person-check"></i>
            </div>
          </div>
        </CCardBody>
      </CCard>
    </CCol>
  </CRow>
</template>

<script setup>
import { CRow, CCol, CCard, CCardBody } from '@coreui/vue';

defineProps({
  stats: { type: Object, required: true },
});

defineEmits(['filter']);
</script>

<style scoped>
.stat-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
</style>
