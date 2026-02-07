<template>
  <div class="row">
    <!-- Main Cashflow Chart -->
    <div class="col-md-12 mb-4">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-success">Cashflow Overview</h5>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-success active">Last 6 Months</button>
            <button class="btn btn-outline-success">Yearly</button>
          </div>
        </div>
        <div class="card-body">
           <CChart 
            type="bar" 
            :data="cashflowData" 
            :options="cashflowOptions"
            height="300"
          />
        </div>
      </div>
    </div>

    <!-- Income Breakdown (Donut) -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold">Income Sources</h6>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center">
           <CChart 
            type="doughnut" 
            :data="incomeSourceData" 
            :options="{ plugins: { legend: { position: 'right' } } }"
            style="max-height: 250px"
          />
        </div>
      </div>
    </div>

    <!-- Expense Breakdown (Pie) -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold">Expense Distribution</h6>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center">
           <CChart 
            type="pie" 
            :data="expenseCategoryData" 
            :options="{ plugins: { legend: { position: 'right' } } }"
            style="max-height: 250px"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { CChart } from '@coreui/vue-chartjs';
import { useAnalyticsStore } from '@/store/analytics'; // Requires update to store
import { storeToRefs } from 'pinia';

const analyticsStore = useAnalyticsStore();
const { financeData } = storeToRefs(analyticsStore);

// Compute chart data structures
const cashflowData = computed(() => ({
  labels: financeData.value?.trend?.labels || ['Jan', 'Feb', 'Mar'],
  datasets: [
    {
      label: 'Tithes & Offering',
      backgroundColor: '#198754',
      data: financeData.value?.trend?.income || [5000, 6000, 5500]
    },
    {
      label: 'Expenses',
      backgroundColor: '#dc3545',
      data: financeData.value?.trend?.expenses || [3000, 2500, 4000]
    }
  ]
}));

const cashflowOptions = {
  plugins: { legend: { position: 'top' } },
  maintainAspectRatio: false,
  scales: {
    y: { beginAtZero: true, grid: { color: '#f0f2f5' } },
    x: { grid: { display: false } }
  }
};

const incomeSourceData = computed(() => ({
  labels: financeData.value?.categories?.labels || ['Tithes', 'Offering', 'Building'],
  datasets: [{
    backgroundColor: ['#0d6efd', '#0dcaf0', '#ffc107'],
    data: financeData.value?.categories?.data || [60, 30, 10]
  }]
}));

const expenseCategoryData = computed(() => ({
  labels: financeData.value?.expenses?.labels || ['Salaries', 'Utilities', 'Outreach'],
  datasets: [{
    backgroundColor: ['#fd7e14', '#20c997', '#6610f2'],
    data: financeData.value?.expenses?.data || [50, 20, 30]
  }]
}));

onMounted(() => {
  // Trigger a store action to fetch finance specific data
  // analyticsStore.fetchFinanceMetrics() 
});
</script>
