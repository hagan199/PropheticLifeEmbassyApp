<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible" class="modal-bottom-sheet">
      <CModalHeader class="border-0 pb-0">
        <CModalTitle class="text-center w-100">
          <i class="bi bi-receipt-cutoff fs-2 text-primary mb-2"></i>
          <div class="fw-bold">Contribution Receipt</div>
        </CModalTitle>
      </CModalHeader>
      <CModalBody v-if="receipt" class="receipt-body px-4">
        <!-- Church Header -->
        <div class="text-center mb-4">
          <div class="church-logo mb-2">
            <i class="bi bi-church fs-1 text-primary"></i>
          </div>
          <h5 class="mb-1 fw-bold text-primary">Prophetic Life Embassy</h5>
          <div class="text-muted small">Building Lives Through Christ</div>
        </div>

        <!-- Receipt Details -->
        <div class="receipt-details bg-light rounded-3 p-3 mb-3">
          <CRow class="g-2 small">
            <CCol xs="6">
              <div class="text-muted">Receipt No:</div>
            </CCol>
            <CCol xs="6" class="text-end fw-semibold">
              {{ receipt.reference || `RCP-${receipt.id}` }}
            </CCol>
            <CCol xs="6">
              <div class="text-muted">Date:</div>
            </CCol>
            <CCol xs="6" class="text-end">{{ formatDate(receipt.date) }}</CCol>
            <CCol xs="6">
              <div class="text-muted">Contributor:</div>
            </CCol>
            <CCol xs="6" class="text-end">{{ receipt.memberName || 'Anonymous' }}</CCol>
            <CCol xs="6">
              <div class="text-muted">Type:</div>
            </CCol>
            <CCol xs="6" class="text-end">
              <CBadge :color="typeColor(receipt.type)" class="badge-sm">{{ typeLabel(receipt.type) }}</CBadge>
            </CCol>
            <CCol xs="6">
              <div class="text-muted">Method:</div>
            </CCol>
            <CCol xs="6" class="text-end">
              <CBadge color="light" text-color="dark" class="badge-sm">
                <i :class="paymentIcon(receipt.paymentMethod)" class="me-1"></i>
                {{ paymentLabel(receipt.paymentMethod) }}
              </CBadge>
            </CCol>
          </CRow>
        </div>

        <!-- Amount Section -->
        <div class="amount-section text-center bg-primary-container rounded-3 p-4 mb-3">
          <div class="text-muted small mb-1">Contribution Amount</div>
          <div class="fs-1 fw-bold text-primary">GH₵ {{ formatMoney(receipt.amount) }}</div>
          <div class="text-muted x-small mt-1">{{ receipt.notes || 'No additional notes' }}</div>
        </div>

        <!-- Footer Message -->
        <div class="text-center text-muted small mb-3">
          <div class="mb-2">
            <i class="bi bi-heart-fill text-danger me-1"></i>
            Thank you for your generous contribution!
          </div>
          <div class="fst-italic">
            "Give, and it will be given to you. A good measure, pressed down, shaken together and running over, will be
            poured into your lap." - Luke 6:38
          </div>
        </div>

        <!-- Recorded By -->
        <div class="text-center text-muted x-small border-top pt-2">
          Recorded by: {{ receipt.recordedBy }} | {{ new Date().toLocaleDateString() }}
        </div>
      </CModalBody>
      <CModalFooter class="border-0 pt-0">
        <div class="d-flex gap-2 w-100">
          <CButton color="primary" variant="outline" class="flex-fill" @click="$emit('print')">
            <i class="bi bi-printer me-1"></i> Print
          </CButton>
          <CButton color="primary" class="flex-fill" @click="localVisible = false">
            <i class="bi bi-check-circle me-1"></i> Done
          </CButton>
        </div>
      </CModalFooter>
    </CModal>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter, CRow, CCol, CButton, CBadge } from '@coreui/vue';

const props = defineProps<{
  visible: boolean;
  receipt: any;
}>();

const emit = defineEmits<{
  'update:visible': [value: boolean];
  'print': [];
}>();

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => { localVisible.value = v; });
watch(localVisible, (v) => { emit('update:visible', v); });

function formatDate(date: string): string {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatMoney(amount: number): string {
  return (amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function typeColor(type: string): string {
  const colors: Record<string, string> = { tithe: 'primary', offering: 'success', special_seed: 'warning', building_fund: 'info', missions: 'purple', welfare: 'danger' };
  return colors[type] || 'secondary';
}

function typeLabel(type: string): string {
  const labels: Record<string, string> = { tithe: 'Tithe', offering: 'Offering', special_seed: 'Special Seed', building_fund: 'Building Fund', missions: 'Missions', welfare: 'Welfare' };
  return labels[type] || type;
}

function paymentIcon(method: string): string {
  const icons: Record<string, string> = { cash: 'bi bi-cash', momo: 'bi bi-phone', bank: 'bi bi-bank', cheque: 'bi bi-file-text' };
  return icons[method] || 'bi bi-credit-card';
}

function paymentLabel(method: string): string {
  const labels: Record<string, string> = { cash: 'Cash', momo: 'Mobile Money', bank: 'Bank Transfer', cheque: 'Cheque' };
  return labels[method] || method;
}
</script>

<style scoped>
.receipt-body {
  font-family: 'Courier New', monospace;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.church-logo {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  color: white;
}

.receipt-details {
  border: 1px dashed #dee2e6;
}

.amount-section {
  border: 2px solid #007bff;
  position: relative;
}

.amount-section::before {
  content: '';
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 20px;
  background: white;
  border: 2px solid #007bff;
  border-radius: 50%;
}

.badge-sm {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}
</style>
