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
          <CChart type="bar" :data="cashflowData" :options="cashflowOptions" height="300" />
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
          <CChart type="doughnut" :data="incomeSourceData" :options="{ plugins: { legend: { position: 'right' } } }"
            style="max-height: 250px" />
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
          <CChart type="pie" :data="expenseCategoryData" :options="{ plugins: { legend: { position: 'right' } } }"
            style="max-height: 250px" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { CChart } from '@coreui/vue-chartjs';
import { reportsApi } from '@/api/reports';
// CSpinner not used in this component

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
});

const isLoading = ref(false);
const financeTrends = ref({ labels: [], income: [], expenses: [] });
const incomeCategories = ref({ labels: [], data: [] });
const expenseCategories = ref({ labels: [], data: [] });

async function fetchFinanceData() {
  isLoading.value = true;
  try {
    const [trendRes, incomeRes, expenseRes] = await Promise.all([
      reportsApi.finance.trends(props.filters),
      reportsApi.finance.contributionsByType(props.filters),
      reportsApi.finance.expensesByCategory(props.filters),
    ]);

    financeTrends.value = trendRes.data.data;
    incomeCategories.value = incomeRes.data.data;
    expenseCategories.value = expenseRes.data.data;
  } catch (error) {
    console.error('Failed to fetch finance data:', error);
  } finally {
    isLoading.value = false;
  }
}

// Compute chart data structures
const cashflowData = computed(() => ({
  labels: financeTrends.value.labels || ['Jan', 'Feb', 'Mar'],
  datasets: [
    {
      label: 'Tithes & Offering',
      backgroundColor: '#10b981',
      data: financeTrends.value.income || [0, 0, 0],
    },
    {
      label: 'Expenses',
      backgroundColor: '#ef4444',
      data: financeTrends.value.expenses || [0, 0, 0],
    },
  ],
}));

const cashflowOptions = {
  plugins: {
    legend: {
      position: 'top',
      labels: { usePointStyle: true, padding: 20 },
    },
  },
  maintainAspectRatio: false,
  scales: {
    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
    x: { grid: { display: false } },
  },
};

const incomeSourceData = computed(() => ({
  labels: incomeCategories.value.labels || ['Tithes', 'Offering', 'Building'],
  datasets: [
    {
      backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f97316', '#f59e0b'],
      data: incomeCategories.value.data || [0, 0, 0],
    },
  ],
}));

const expenseCategoryData = computed(() => ({
  labels: expenseCategories.value.labels || ['Salaries', 'Utilities', 'Outreach'],
  datasets: [
    {
      backgroundColor: ['#ef4444', '#f97316', '#f59e0b', '#10b981', '#3b82f6'],
      data: expenseCategories.value.data || [0, 0, 0],
    },
  ],
}));

onMounted(fetchFinanceData);
</script>
