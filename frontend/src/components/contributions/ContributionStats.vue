<template>
  <CRow class="g-4 mb-4">
    <CCol v-for="(stat, i) in stats" :key="stat.label" sm="6" xl="3">
      <MaterialCard class="stat-card border-0 shadow-sm h-100 animate__animated animate__fadeInUp"
        :style="{ animationDelay: `${(i + 1) * 0.1}s` }">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted x-small text-uppercase mb-1 fw-bold letter-spacing-1">{{ stat.label }}</div>
            <div class="fs-4 fw-bold" :class="'text-' + stat.color">GH₵{{ formatMoney(stat.total) }}</div>
            <div class="text-muted small">{{ stat.count }} records</div>
          </div>
          <div class="stat-icon shadow-sm" :class="'bg-' + stat.color + '-container text-' + stat.color">
            <i :class="stat.icon"></i>
          </div>
        </div>
      </MaterialCard>
    </CCol>
  </CRow>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { CRow, CCol } from '@coreui/vue';
import MaterialCard from '../material/MaterialCard.vue';

const props = defineProps<{
  todayTotal: number;
  todayCount: number;
  weekTotal: number;
  weekCount: number;
  monthTotal: number;
  monthCount: number;
  yearTotal: number;
  yearCount: number;
}>();

const stats = computed(() => [
  { label: 'Today', total: props.todayTotal, count: props.todayCount, color: 'primary', icon: 'bi bi-calendar-day' },
  { label: 'This Week', total: props.weekTotal, count: props.weekCount, color: 'success', icon: 'bi bi-calendar-week' },
  { label: 'This Month', total: props.monthTotal, count: props.monthCount, color: 'warning', icon: 'bi bi-calendar-month' },
  { label: 'Year to Date', total: props.yearTotal, count: props.yearCount, color: 'info', icon: 'bi bi-calendar' },
]);

function formatMoney(amount: number): string {
  return (amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<style scoped>
.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
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

<script lang="ts">
// provide an explicit default export for IDEs/linters that don't fully support <script setup>
export default {};
</script>
