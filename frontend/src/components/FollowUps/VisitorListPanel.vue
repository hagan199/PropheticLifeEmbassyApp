<template>
  <CCard>
    <CCardHeader class="d-flex justify-content-between align-items-center">
      <div class="fw-semibold">Visitors</div>
      <div class="d-flex gap-3 align-items-center flex-wrap">
        <CFormInput :modelValue="search" @update:modelValue="$emit('update:search', $event)" placeholder="Search..."
          style="width: 200px" />
        <CFormSelect :modelValue="statusFilter" @update:modelValue="$emit('update:statusFilter', $event)"
          style="width: 150px">
          <option value="">All Status</option>
          <option value="not_contacted">Not Contacted</option>
          <option value="contacted">Contacted</option>
          <option value="engaged">Engaged</option>
          <option value="converted">Converted</option>
        </CFormSelect>
        <div class="d-flex align-items-center gap-2">
          <CFormInput :modelValue="dateFrom" @update:modelValue="$emit('update:dateFrom', $event)" type="date"
            style="width: 150px" />
          <CFormInput :modelValue="dateTo" @update:modelValue="$emit('update:dateTo', $event)" type="date"
            style="width: 150px" />
          <CButton size="sm" color="primary" @click="$emit('apply-date-filter')">Apply</CButton>
          <CButton size="sm" color="light" @click="$emit('clear-date-filter')">Clear</CButton>
        </div>
      </div>
    </CCardHeader>
    <CCardBody class="p-0">
      <CListGroup flush>
        <CListGroupItem v-for="v in pagedVisitors" :key="v.id"
          class="visitor-item d-flex justify-content-between align-items-start py-3"
          :class="{ active: selectedVisitorId === v.id }" @click="$emit('select', v)">
          <div class="d-flex">
            <CAvatar :color="statusAvatarColor(v.status)" text-color="white" class="me-3">
              {{ v.name.charAt(0) }}
            </CAvatar>
            <div>
              <div class="fw-semibold">{{ v.name }}</div>
              <div class="text-muted small">{{ v.phone }}</div>
              <div class="mt-1">
                <CBadge :color="statusColor(v.status)" size="sm">{{ statusLabel(v.status) }}</CBadge>
                <CBadge color="light" text-color="dark" size="sm" class="ms-1">{{ v.source }}</CBadge>
              </div>
            </div>
          </div>
          <div class="text-end">
            <div class="text-muted small">{{ formatDate(v.firstVisitDate) }}</div>
            <div v-if="v.nextFollowUp" class="small"
              :class="isOverdue(v.nextFollowUp) ? 'text-danger' : 'text-muted'">
              <i class="bi bi-clock me-1"></i>{{ formatDate(v.nextFollowUp) }}
            </div>
          </div>
        </CListGroupItem>
      </CListGroup>

      <div v-if="totalItems > perPage" class="d-flex justify-content-between align-items-center p-3">
        <div class="text-muted small">
          Showing <strong>{{ (currentPage - 1) * perPage + 1 }}</strong>
          to <strong>{{ Math.min(currentPage * perPage, totalItems) }}</strong>
          of <strong>{{ totalItems }}</strong>
        </div>
        <div class="d-flex align-items-center">
          <CButton size="sm" color="light" :disabled="currentPage === 1"
            @click="$emit('update:currentPage', currentPage - 1)">
            <i class="bi bi-chevron-left"></i>
          </CButton>
          <div class="mx-2 small">Page {{ currentPage }} / {{ totalPages }}</div>
          <CButton size="sm" color="light" :disabled="currentPage === totalPages"
            @click="$emit('update:currentPage', currentPage + 1)">
            <i class="bi bi-chevron-right"></i>
          </CButton>
        </div>
      </div>

      <div v-if="!totalItems" class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        No visitors found
      </div>
    </CCardBody>
  </CCard>
</template>

<script setup>
import {
  CCard, CCardHeader, CCardBody, CFormInput, CFormSelect,
  CButton, CListGroup, CListGroupItem, CAvatar, CBadge,
} from '@coreui/vue';

defineProps({
  pagedVisitors: { type: Array, required: true },
  selectedVisitorId: { type: [String, Number, null], default: null },
  search: { type: String, default: '' },
  statusFilter: { type: String, default: '' },
  dateFrom: { type: String, default: '' },
  dateTo: { type: String, default: '' },
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
  totalItems: { type: Number, default: 0 },
  perPage: { type: Number, default: 15 },
});

defineEmits([
  'select',
  'update:search',
  'update:statusFilter',
  'update:dateFrom',
  'update:dateTo',
  'update:currentPage',
  'apply-date-filter',
  'clear-date-filter',
]);

function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
  });
}

function isOverdue(date) {
  if (!date) return false;
  return new Date(date) < new Date();
}

function statusColor(status) {
  const colors = { not_contacted: 'danger', contacted: 'info', engaged: 'warning', converted: 'success' };
  return colors[status] || 'secondary';
}

function statusAvatarColor(status) {
  return statusColor(status);
}

function statusLabel(status) {
  const labels = { not_contacted: 'Not Contacted', contacted: 'Contacted', engaged: 'Engaged', converted: 'Converted' };
  return labels[status] || status;
}
</script>

<style scoped>
.visitor-item {
  cursor: pointer;
  transition: background 0.15s ease;
}

.visitor-item:hover {
  background: rgba(13, 110, 253, 0.04);
}

.visitor-item.active {
  background: rgba(13, 110, 253, 0.08);
  border-left: 3px solid var(--cui-primary);
}
</style>
