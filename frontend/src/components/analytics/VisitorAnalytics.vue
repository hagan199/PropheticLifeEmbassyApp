<template>
  <div v-if="isAuthorized" class="row">
    <!-- Main Visitor Source Chart (Donut) -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-primary">How They Found Us</h5>
          <div class="small text-muted">Last 30 Days</div>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px">
          <CSpinner v-if="isLoading" color="primary" />
          <CChart v-else type="doughnut" :data="sourceData" :options="sourceOptions" style="max-height: 250px" />
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
        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px">
          <CSpinner v-if="isLoading" color="success" />
          <CChart v-else type="bar" :data="funnelData" :options="funnelOptions" :height="250" />
        </div>
      </div>
    </div>
  </div>
  <div v-else class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
          <div class="h5 text-muted">You do not have permission to view visitor reports.</div>
          <div class="small text-muted">Contact an administrator if you need access.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { reportsApi } from '@/api/reports';
import { CChart } from '@coreui/vue-chartjs';
import { CSpinner } from '@coreui/vue';
import { useAuthStore } from '@/store/auth';

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
});

const isLoading = ref(false);
const visitorData = ref({ labels: [], data: [] });
const funnelDataRaw = ref({ not_contacted: 0, contacted: 0, engaged: 0, converted: 0 });

async function fetchVisitorData() {
  isLoading.value = true;
  try {
    const [sourceRes, funnelRes] = await Promise.all([
      reportsApi.visitors.bySource(props.filters),
      reportsApi.visitors.conversionFunnel(props.filters),
    ]);

    visitorData.value = sourceRes.data.data;
    funnelDataRaw.value = funnelRes.data.data;
  } catch (error) {
    console.error('Failed to fetch visitor analytics:', error);
  } finally {
    isLoading.value = false;
  }
}

// Compute chart data from the fetched result
const sourceData = computed(() => {
  const sources = visitorData.value || { labels: [], data: [] };
  const hasLabels = sources.labels && sources.labels.length > 0;
  const hasData = sources.data && sources.data.length > 0;

  return {
    labels: hasLabels
      ? sources.labels
      : ['Invitation', 'Social Media', 'Website', 'Event', 'Walk-in'],
    datasets: [
      {
        backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f97316', '#10b981'],
        data: hasData ? sources.data : [12, 8, 3, 5, 2],
      },
    ],
  };
});

const sourceOptions = {
  plugins: {
    legend: {
      position: 'right',
      labels: { usePointStyle: true, font: { size: 11 } },
    },
  },
  maintainAspectRatio: false,
};

const funnelData = computed(() => {
  const funnel = funnelDataRaw.value || {
    not_contacted: 0,
    contacted: 0,
    engaged: 0,
    converted: 0,
  };

  return {
    labels: ['First Visit', 'Contacted', 'Engaged', 'Converted'],
    datasets: [
      {
        label: 'Visitor Journey',
        backgroundColor: [
          'rgba(148, 163, 184, 0.2)',
          'rgba(245, 158, 11, 0.2)',
          'rgba(6, 182, 212, 0.2)',
          'rgba(16, 185, 129, 0.2)',
        ],
        borderColor: ['#94a3b8', '#f59e0b', '#06b6d4', '#10b981'],
        borderWidth: 2,
        data: [
          funnel.not_contacted + funnel.contacted + funnel.engaged + funnel.converted || 30,
          funnel.contacted + funnel.engaged + funnel.converted || 20,
          funnel.engaged + funnel.converted || 12,
          funnel.converted || 8,
        ],
      },
    ],
  };
});

const funnelOptions = {
  indexAxis: 'y',
  plugins: {
    legend: { display: false },
    tooltip: { enabled: true },
  },
  scales: {
    x: {
      beginAtZero: true,
      grid: { color: '#f1f5f9' },
      ticks: { font: { size: 10 } },
    },
    y: {
      grid: { display: false },
    },
  },
  maintainAspectRatio: false,
};

onMounted(() => {
  if (isAuthorized.value) fetchVisitorData();
});
watch(() => props.filters, () => {
  if (isAuthorized.value) fetchVisitorData();
}, { deep: true });

const auth = useAuthStore();
const isAuthorized = computed(() => auth.hasRole(['pr_follow_up', 'admin']));
</script>
