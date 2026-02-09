<template>
  <div class="row">
    <!-- Attendance Trend Chart -->
    <div class="col-md-8 mb-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-primary">Attendance Trends</h5>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active">Monthly</button>
            <button class="btn btn-outline-secondary">Weekly</button>
          </div>
        </div>
        <div class="card-body">
          <div v-if="isLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <CChart v-else type="line" :data="chartData" :options="chartOptions" height="300" />
        </div>
      </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-0 bg-primary text-white mb-3">
        <div class="card-body">
          <h6 class="text-uppercase small opacity-75">Avg. Sunday Attendance</h6>
          <h2 class="display-6 fw-bold mb-0">{{ summary.avg_attendance }}</h2>
          <small class="text-white-50">↑ {{ summary.trend || 0 }}% vs last month</small>
        </div>
      </div>

      <div class="card shadow-sm border-0 bg-info text-white mb-3">
        <div class="card-body">
          <h6 class="text-uppercase small opacity-75">Retention Rate</h6>
          <h2 class="display-6 fw-bold mb-0">{{ summary.retention_rate }}%</h2>
          <small class="text-white-50">Stable trend</small>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h6 class="text-uppercase small text-muted mb-3">Demographics</h6>
          <CChart type="doughnut" :data="{
            labels: ['Adults', 'Children', 'Youth'],
            datasets: [
              {
                backgroundColor: ['#321fdb', '#f9b115', '#3399ff'],
                data: [65, 20, 15],
              },
            ],
          }" :options="{ plugins: { legend: { position: 'bottom' } } }" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { CChart } from '@coreui/vue-chartjs';
import { reportsApi } from '@/api/reports';
// CSpinner not used here; using native spinner markup instead

const isLoading = ref(false);
const attendanceTrends = ref({ labels: [], datasets: [] });
const summary = ref({
  avg_attendance: 148,
  retention_rate: 85,
  demographics: [65, 20, 15],
});

async function fetchAttendanceData() {
  isLoading.value = true;
  try {
    // Prefer explicit chart endpoint, but fall back to weekly data if unavailable
    let trendRes = null;
    if (reportsApi.charts && typeof reportsApi.charts.attendanceTrend === 'function') {
      trendRes = await reportsApi.charts.attendanceTrend();
    } else if (reportsApi.attendance && typeof reportsApi.attendance.weekly === 'function') {
      // normalize weekly response shape to { labels, datasets }
      const res = await reportsApi.attendance.weekly();
      if (res?.data) {
        const data = res.data.data || {};
        // If backend returns arrays of dates and values, try to map them
        if (data.labels && data.datasets) {
          trendRes = { data: { data } };
        } else if (Array.isArray(data)) {
          trendRes = {
            data: {
              data: {
                labels: data.map((d, i) => d.label || `Day ${i + 1}`),
                datasets: [
                  {
                    label: 'Attendance',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: '#6366f1',
                    pointBackgroundColor: '#6366f1',
                    data: data.map((d) => d.value || 0),
                    fill: true,
                    tension: 0.4,
                  },
                ],
              },
            },
          };
        }
      }
    }

    if (trendRes?.data?.data) {
      attendanceTrends.value = trendRes.data.data;
    } else {
      // leave defaults if we couldn't fetch
      console.warn('No attendance trend data available, using defaults');
    }

    // Summary: try to fetch summary endpoint if available, else compute from trend
    if (reportsApi.attendance && typeof reportsApi.attendance.summary === 'function') {
      const summaryRes = await reportsApi.attendance.summary();
      if (summaryRes?.data?.data) {
        summary.value = { ...summary.value, ...summaryRes.data.data };
      }
    } else {
      // compute a simple summary from fetched trend datasets
      const ds = attendanceTrends.value.datasets?.[0]?.data || [];
      if (ds.length) {
        const avg = Math.round(ds.reduce((a: number, b: number) => a + (b || 0), 0) / ds.length);
        summary.value.avg_attendance = avg;
        // compute a dummy trend percentage compared to previous period if available
        const trend = Math.round((ds[ds.length - 1] - (ds[ds.length - 2] || ds[0] || 0)) / ((ds[ds.length - 2] || 1)) * 100);
        summary.value.trend = Number.isFinite(trend) ? trend : 0;
      }
    }
  } catch (error) {
    console.error('Failed to fetch attendance data:', error);
  } finally {
    isLoading.value = false;
  }
}

const chartData = computed(() => ({
  labels: attendanceTrends.value.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  datasets: attendanceTrends.value.datasets || [
    {
      label: 'Attendance',
      backgroundColor: 'rgba(99, 102, 241, 0.1)',
      borderColor: '#6366f1',
      pointBackgroundColor: '#6366f1',
      data: [0, 0, 0, 0, 0, 0, 0],
      fill: true,
      tension: 0.4,
    },
  ],
}));

const chartOptions = {
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        usePointStyle: true,
        padding: 20,
        font: { size: 12, weight: '500' },
      },
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.9)',
      titleColor: '#1e293b',
      bodyColor: '#475569',
      borderColor: '#e2e8f0',
      borderWidth: 1,
      padding: 12,
      displayColors: true,
      callbacks: {
        label: context => ` ${context.dataset.label}: ${context.raw}`,
      },
    },
  },
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#f1f5f9', drawBorder: false },
      ticks: { color: '#94a3b8', font: { size: 11 } },
    },
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11 } },
    },
  },
};

onMounted(fetchAttendanceData);
</script>

<style scoped>
.card {
  transition: transform 0.2s;
}
</style>
