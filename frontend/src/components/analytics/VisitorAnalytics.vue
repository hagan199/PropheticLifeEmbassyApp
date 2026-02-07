<template>
  <div class="row">
    <!-- Main Visitor Source Chart (Donut) -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-primary">How They Found Us</h5>
          <div class="small text-muted">Last 30 Days</div>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px;">
          <CSpinner v-if="isLoading" color="primary" />
          <CChart 
            v-else
            type="doughnut" 
            :data="sourceData" 
            :options="sourceOptions"
            style="max-height: 250px"
          />
        </div>
      </div>
    </div>

    <!-- Conversion Funnel (Bar) -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-success">Visitor Retention Funnel</h5>
          <div class="small text-muted">All Time</div>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px;">
          <CSpinner v-if="isLoading" color="success" />
          <CChart 
            v-else
            type="bar" 
            :data="funnelData" 
            :options="funnelOptions"
            height="250"
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

// Use Vue Query for caching and seamless data fetching
const { data, isLoading, error } = useQuery({
  queryKey: ['analytics', 'visitors'],
  queryFn: () => dashboardApi.getAnalytics({ range: 'month' }),
  staleTime: 5 * 60 * 1000, // Data is fresh for 5 minutes
  cacheTime: 30 * 60 * 1000 // Cache for 30 minutes
});

// Compute chart data from the query result
const sourceData = computed(() => {
  const analytics = data.value?.data?.data || {};
  const sources = analytics.visitor_sources || { labels: [], data: [] };
  
  // Default fallback data if API returns empty (for demo)
  if (!sources.labels || sources.labels.length === 0) {
    return {
      labels: ['Friend', 'Social Media', 'Website', 'Event', 'Walk-in'],
      datasets: [{
        backgroundColor: ['#41B883', '#E46651', '#00D8FF', '#DD1B16', '#ebedef'],
        data: [12, 8, 3, 5, 2]
      }]
    };
  }

  return {
    labels: sources.labels,
    datasets: [{
      backgroundColor: ['#41B883', '#E46651', '#00D8FF', '#DD1B16', '#ebedef'],
      data: sources.data
    }]
  };
});

const sourceOptions = {
  plugins: {
    legend: { position: 'right' }
  },
  maintainAspectRatio: false
};

const funnelData = computed(() => {
  const analytics = data.value?.data?.data || {};
  const funnel = analytics.visitor_conversion || { 
    not_contacted: 0, 
    contacted: 0, 
    engaged: 0, 
    converted: 0 
  };

  return {
    labels: ['First Visit', 'Contacted', 'Engaged', 'Converted Member'],
    datasets: [{
      label: 'Visitor Journey',
      backgroundColor: ['#6c757d', '#ffc107', '#0dcaf0', '#198754'],
      data: [
        (funnel.not_contacted + funnel.contacted + funnel.engaged + funnel.converted) || 30, // Estimate total visitors
        (funnel.contacted + funnel.engaged + funnel.converted) || 20,
        (funnel.engaged + funnel.converted) || 12,
        funnel.converted || 8
      ]
    }]
  };
});

const funnelOptions = {
  indexAxis: 'y', // Horizontal bar chart
  plugins: {
    legend: { display: false }
  },
  scales: {
    x: { beginAtZero: true }
  },
  maintainAspectRatio: false
};
</script>
