<template>
  <CRow class="g-4 mb-4">
    <CCol lg="8">
      <MaterialCard title="Monthly Breakdown">
        <template #actions>
          <select v-model="localMonth" class="md-input border-0 bg-light py-1 px-2 rounded-pill small">
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </template>
        <div class="chart-container" style="height: 250px">
          <CChartBar :data="chartData" :options="chartOptions" />
        </div>
        <CRow class="g-3 mt-2">
          <CCol sm="4">
            <div class="p-3 bg-surface-container rounded-4">
              <div class="text-muted x-small fw-bold">TITHES</div>
              <div class="fs-5 fw-bold text-primary">GH₵{{ formatMoney(typeBreakdown.tithe) }}</div>
            </div>
          </CCol>
          <CCol sm="4">
            <div class="p-3 bg-surface-container rounded-4">
              <div class="text-muted x-small fw-bold">OFFERING</div>
              <div class="fs-5 fw-bold text-success">GH₵{{ formatMoney(typeBreakdown.offering) }}</div>
            </div>
          </CCol>
          <CCol sm="4">
            <div class="p-3 bg-surface-container rounded-4">
              <div class="text-muted x-small fw-bold">SPECIAL SEED</div>
              <div class="fs-5 fw-bold text-warning">GH₵{{ formatMoney(typeBreakdown.specialSeed) }}</div>
            </div>
          </CCol>
        </CRow>
      </MaterialCard>
    </CCol>
    <CCol lg="4">
      <MaterialCard title="Payment Methods" class="h-100">
        <div class="mt-2">
          <div v-for="pm in paymentMethodStats" :key="pm.method"
            class="d-flex justify-content-between align-items-center mb-4 p-2 rounded-3 hover-bg-light transition-all">
            <div class="d-flex align-items-center">
              <div class="stat-icon-sm me-3" :class="'bg-' + pm.color + '-container text-' + pm.color">
                <i :class="pm.icon"></i>
              </div>
              <div>
                <div class="fw-bold small">{{ pm.method }}</div>
                <div class="text-muted x-small">{{ pm.count }} transactions</div>
              </div>
            </div>
            <div class="text-end">
              <div class="fw-bold text-dark">GH₵{{ formatMoney(pm.amount) }}</div>
              <div class="md-badge bg-light text-dark x-small py-0">
                {{ Math.round((pm.amount / (monthTotal || 1)) * 100) }}%
              </div>
            </div>
          </div>
        </div>
      </MaterialCard>
    </CCol>
  </CRow>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { CRow, CCol } from '@coreui/vue';
import { CChartBar } from '@coreui/vue-chartjs';
import MaterialCard from '../material/MaterialCard.vue';

interface TypeBreakdown {
  tithe: number;
  offering: number;
  specialSeed: number;
  buildingFund: number;
  missions: number;
  welfare: number;
}

interface PaymentMethodStat {
  method: string;
  color: string;
  icon: string;
  amount: number;
  count: number;
}

const props = defineProps<{
  selectedMonth: string;
  months: Array<{ value: string; label: string }>;
  typeBreakdown: TypeBreakdown;
  paymentMethodStats: PaymentMethodStat[];
  monthTotal: number;
  chartData: any;
  chartOptions: any;
}>();

const emit = defineEmits<{
  'update:selectedMonth': [value: string];
}>();

const localMonth = ref(props.selectedMonth);
watch(() => props.selectedMonth, (v) => { localMonth.value = v; });
watch(localMonth, (v) => { emit('update:selectedMonth', v); });

function formatMoney(amount: number): string {
  return (amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
<script lang="ts">
export default {};
</script>

<style scoped>
.stat-icon-sm {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.hover-bg-light:hover {
  background-color: #f8f9fa !important;
}

.transition-all {
  transition: all 0.2s ease;
}

.chart-container {
  position: relative;
  height: 250px;
}
</style>
