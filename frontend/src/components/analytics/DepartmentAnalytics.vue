<template>
  <div class="row">
    <!-- Department Distribution Bar Chart -->
    <div class="col-md-12 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-dark">Ministry Distribution</h5>
          <div class="small text-muted">Members Per Department</div>
        </div>
        <div class="card-body">
          <CSpinner v-if="isLoading" color="dark" />
          <CChart 
            v-else
            type="bar" 
            :data="departmentData" 
            :options="options"
            height="300"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import { dashboardApi } from '@/api/dashboard';
import { CChart } from '@coreui/vue-chartjs';
import { CSpinner } from '@coreui/vue';

// Using the same cached key to avoid extra API hits
const { data, isLoading } = useQuery({
  queryKey: ['analytics', 'departments'],
  queryFn: () => dashboardApi.getAnalytics({ range: 'month' }),
  staleTime: 10 * 60 * 1000 
});

const departmentData = computed(() => {
  const analytics = data.value?.data?.data || {};
  const depts = analytics.department_distribution || { labels: [], data: [] };

  // Generate random colors for each bar
  const colors = (depts.labels || []).map(() => '#' + Math.floor(Math.random()*16777215).toString(16));

  return {
    labels: depts.labels || ['Choir', 'Ushering', 'Media'],
    datasets: [{
      label: 'Members',
      backgroundColor: '#6610f2',
      data: depts.data || [15, 8, 5],
      barPercentage: 0.5
    }]
  };
});

const options = {
  plugins: { legend: { display: false } },
  maintainAspectRatio: false,
  scales: {
    y: { beginAtZero: true },
    x: { grid: { display: false } }
  }
};
</script>
