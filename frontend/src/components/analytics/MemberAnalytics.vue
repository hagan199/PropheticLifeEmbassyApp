<template>
  <div class="row">
    <!-- Member Growth Line Chart -->
    <div class="col-md-8 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold text-dark">Member Growth (Last 6 Months)</h5>
        </div>
        <div class="card-body">
          <CSpinner v-if="isLoading" color="dark" />
          <CChart v-else type="line" :data="growthData" :options="growthOptions" height="300" />
        </div>
      </div>
    </div>

    <!-- Role Distribution Polar Area -->
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold text-dark">Role Distribution</h5>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center">
          <CSpinner v-if="isLoading" color="dark" />
          <CChart
v-else type="polarArea" :data="roleData" :options="{ plugins: { legend: { position: 'bottom' } } }"
            style="max-height: 280px" />
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

const { data, isLoading } = useQuery({
  queryKey: ['analytics', 'members'],
  queryFn: () => dashboardApi.analytics({ range: 'month' }),
  staleTime: 10 * 60 * 1000, // 10 mins cache
});

const growthData = computed(() => {
  const analytics = data.value?.data?.data || {};
  const growth = analytics.user_growth || { labels: [], data: [] };

  return {
    labels: growth.labels || ['Jan', 'Feb', 'Mar'],
    datasets: [
      {
        label: 'New Members',
        backgroundColor: 'rgba(50, 31, 219, 0.2)',
        borderColor: '#321fdb',
        pointBackgroundColor: '#321fdb',
        pointBorderColor: '#fff',
        data: growth.data || [5, 10, 8],
        fill: true,
      },
    ],
  };
});

const growthOptions = {
  plugins: { legend: { display: false } },
  maintainAspectRatio: false,
  scales: {
    y: { beginAtZero: true, grid: { color: '#f8f9fa' } },
    x: { grid: { display: false } },
  },
};

const roleData = computed(() => {
  const analytics = data.value?.data?.data || {};
  const roles = analytics.user_roles || { labels: [], data: [] };

  // Capitalize labels
  const labels = (roles.labels || []).map(
    r => r.charAt(0).toUpperCase() + r.slice(1).replace('_', ' ')
  );

  return {
    labels: labels.length ? labels : ['Admin', 'Usher', 'Pastor'],
    datasets: [
      {
        backgroundColor: ['#e55353', '#f9b115', '#2eb85c', '#321fdb', '#3399ff', '#636f83'],
        data: roles.data || [2, 5, 1],
      },
    ],
  };
});
</script>
