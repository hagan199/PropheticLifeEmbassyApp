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
          <CChart 
            v-else
            type="line" 
            :data="chartData" 
            :options="chartOptions" 
            height="300"
          />
        </div>
      </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-0 bg-primary text-white mb-3">
        <div class="card-body">
          <h6 class="text-uppercase small opacity-75">Avg. Sunday Attendance</h6>
          <h2 class="display-6 fw-bold mb-0">148</h2>
          <small class="text-white-50">↑ 12% vs last month</small>
        </div>
      </div>
      
      <div class="card shadow-sm border-0 bg-info text-white mb-3">
        <div class="card-body">
          <h6 class="text-uppercase small opacity-75">Retention Rate</h6>
          <h2 class="display-6 fw-bold mb-0">85%</h2>
          <small class="text-white-50">Stable trend</small>
        </div>
      </div>

       <div class="card shadow-sm border-0">
        <div class="card-body">
           <h6 class="text-uppercase small text-muted mb-3">Demographics</h6>
           <CChart 
            type="doughnut" 
            :data="{
              labels: ['Adults', 'Children', 'Youth'],
              datasets: [{
                backgroundColor: ['#321fdb', '#f9b115', '#3399ff'],
                data: [65, 20, 15]
              }]
            }"
            :options="{ plugins: { legend: { position: 'bottom' } } }"
           />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { CChart } from '@coreui/vue-chartjs';
import { useAnalyticsStore } from '@/store/analytics';
import { storeToRefs } from 'pinia';

const analyticsStore = useAnalyticsStore();
const { attendanceData, isLoading } = storeToRefs(analyticsStore);

const chartData = computed(() => attendanceData.value);

const chartOptions = {
  plugins: {
    legend: {
      display: true,
      position: 'top'
    }
  },
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#f0f2f5'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  },
  elements: {
    line: {
      tension: 0.4 // Smooth curves
    }
  }
};

onMounted(() => {
  analyticsStore.fetchAttendancemetrics();
});

</script>

<style scoped>
.card {
  transition: transform 0.2s;
}
</style>
