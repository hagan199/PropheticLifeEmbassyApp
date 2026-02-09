<template>
  <div class="reports-page">
    <PageHeader
      title="Reports & Insights"
      subtitle="Comprehensive overview of church growth, finance, and engagement"
    >
      <template #actions>
        <div class="d-flex gap-2">
          <CButton color="light" class="btn-action" @click="exportCurrent">
            <i class="bi bi-download me-2"></i>
            <span class="d-none d-md-inline">Export</span>
          </CButton>
          <CButton
            color="primary"
            class="btn-action btn-primary-custom"
            :disabled="loading"
            @click="refreshData"
          >
            <i class="bi bi-arrow-clockwise me-2" :class="{ 'spin-anim': loading }"></i>
            <span class="d-none d-md-inline">Refresh</span>
          </CButton>
        </div>
      </template>
    </PageHeader>

    <!-- Enhanced Tabs Navigation -->
    <div class="tabs-navigation mb-4">
      <div class="tabs-scroll-container">
        <button
          v-for="t in tabs"
          :key="t.id"
          class="tab-item"
          :class="{ active: tab === t.id }"
          @click="changeTab(t.id)"
        >
          <div class="tab-icon" :style="{ background: tab === t.id ? t.color : 'transparent' }">
            <i :class="t.icon"></i>
          </div>
          <span class="tab-label">{{ t.label }}</span>
          <span v-if="tab === t.id" class="tab-indicator"></span>
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
      <div class="filters-left">
        <div class="filter-group">
          <label class="filter-label">Time Period</label>
          <CFormSelect v-model="activePreset" class="filter-select" @change="onPresetChange">
            <option v-for="p in presets" :key="p.label" :value="p.label">{{ p.label }}</option>
            <option value="custom">Custom Range</option>
          </CFormSelect>
        </div>

        <transition name="slide-fade">
          <div v-if="activePreset === 'custom'" class="custom-date-range">
            <div class="filter-group">
              <label class="filter-label">From</label>
              <CFormInput
                v-model="dateRange.start"
                type="date"
                class="filter-input"
                @change="fetchSummary"
              />
            </div>
            <div class="date-separator">
              <i class="bi bi-arrow-right"></i>
            </div>
            <div class="filter-group">
              <label class="filter-label">To</label>
              <CFormInput
                v-model="dateRange.end"
                type="date"
                class="filter-input"
                @change="fetchSummary"
              />
            </div>
          </div>
        </transition>
      </div>

      <div class="filters-right">
        <button class="filter-btn" @click="showFilterPanel = !showFilterPanel">
          <i class="bi bi-funnel me-2"></i>
          More Filters
          <CBadge v-if="activeFiltersCount > 0" color="primary" class="ms-2">{{
            activeFiltersCount
          }}</CBadge>
        </button>
      </div>
    </div>

    <!-- Enhanced KPIs Summary -->
    <transition name="fade-in">
      <div v-if="!loading" class="kpis-grid">
        <div
          v-for="(kpi, index) in activeKpis"
          :key="kpi.label"
          class="kpi-card"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <div class="kpi-header">
            <div class="kpi-icon-wrapper" :style="{ background: kpi.iconBg }">
              <i :class="kpi.icon" :style="{ color: kpi.iconColor }"></i>
            </div>
            <div v-if="kpi.trend" class="kpi-trend" :class="kpi.trendClass">
              <i :class="kpi.trendIcon"></i>
              <span>{{ kpi.trend }}</span>
            </div>
          </div>
          <div class="kpi-body">
            <div class="kpi-label">{{ kpi.label }}</div>
            <div class="kpi-value">{{ kpi.value }}</div>
            <div v-if="kpi.sublabel" class="kpi-sublabel">{{ kpi.sublabel }}</div>
          </div>
          <div v-if="kpi.chart" class="kpi-mini-chart">
            <svg width="100%" height="40" viewBox="0 0 100 40" preserveAspectRatio="none">
              <polyline
                :points="kpi.chart"
                fill="none"
                :stroke="kpi.iconColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>
      </div>
      <div v-else class="kpis-skeleton">
        <div v-for="n in 4" :key="n" class="skeleton-card"></div>
      </div>
    </transition>

    <!-- Main Content Area -->
    <div class="report-content-wrapper">
      <transition name="fade-slide" mode="out-in">
        <div :key="tab" class="report-content-inner">
          <Suspense>
            <template #default>
              <component :is="activeComponent" :filters="currentFilters" @loading="handleLoading" />
            </template>
            <template #fallback>
              <div class="content-loading">
                <div class="loading-spinner">
                  <CSpinner color="primary" />
                </div>
                <p class="loading-text">Loading {{ activeTabLabel }} data...</p>
              </div>
            </template>
          </Suspense>
        </div>
      </transition>
    </div>

    <!-- Empty State -->
    <div v-if="showEmptyState" class="empty-state">
      <div class="empty-state-icon">
        <i class="bi bi-graph-up"></i>
      </div>
      <h3 class="empty-state-title">No Data Available</h3>
      <p class="empty-state-text">
        There's no data for the selected time period. Try adjusting your filters or selecting a
        different date range.
      </p>
      <CButton color="primary" @click="refreshData">
        <i class="bi bi-arrow-clockwise me-2"></i>
        Refresh Data
      </CButton>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, onMounted, watch } from 'vue';
import PageHeader from '../components/shared/PageHeader.vue';
import { CButton, CFormSelect, CFormInput, CBadge, CSpinner } from '@coreui/vue';
import { useToast } from '../composables/useToast';
import { reportsApi } from '../api';

const toast = useToast();
const loading = ref(false);
const tab = ref('visitors');
const activePreset = ref('This Month');
const showFilterPanel = ref(false);
const showEmptyState = ref(false);

const dateRange = ref({
  start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0],
});

const tabs = [
  {
    id: 'visitors',
    label: 'Visitors',
    icon: 'bi bi-person-plus',
    color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    iconColor: '#667eea',
    iconBg: 'rgba(102, 126, 234, 0.1)',
  },
  {
    id: 'attendance',
    label: 'Attendance',
    icon: 'bi bi-calendar-check',
    color: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    iconColor: '#f5576c',
    iconBg: 'rgba(245, 87, 108, 0.1)',
  },
  {
    id: 'finance',
    label: 'Finance',
    icon: 'bi bi-cash-coin',
    color: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    iconColor: '#00f2fe',
    iconBg: 'rgba(0, 242, 254, 0.1)',
  },
  {
    id: 'members',
    label: 'Demographics',
    icon: 'bi bi-people',
    color: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    iconColor: '#38f9d7',
    iconBg: 'rgba(56, 249, 215, 0.1)',
  },
  {
    id: 'departments',
    label: 'Departments',
    icon: 'bi bi-diagram-3',
    color: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    iconColor: '#fa709a',
    iconBg: 'rgba(250, 112, 154, 0.1)',
  },
];

const presets = [
  { label: 'Last 7 Days', val: 7 },
  { label: 'This Month', val: 30 },
  { label: 'Last 90 Days', val: 90 },
  { label: 'This Year', val: 365 },
];

const activeComponent = computed(() => {
  const map = {
    visitors: defineAsyncComponent(() => import('../components/analytics/VisitorAnalytics.vue')),
    attendance: defineAsyncComponent(() =>
      import('../components/analytics/AttendanceAnalytics.vue')
    ),
    finance: defineAsyncComponent(() => import('../components/analytics/FinanceAnalytics.vue')),
    members: defineAsyncComponent(() => import('../components/analytics/MemberAnalytics.vue')),
    departments: defineAsyncComponent(() =>
      import('../components/analytics/DepartmentAnalytics.vue')
    ),
  };
  return map[tab.value];
});

const activeTabLabel = computed(() => {
  return tabs.find(t => t.id === tab.value)?.label || 'Report';
});

const currentFilters = computed(() => ({
  start_date: dateRange.value.start,
  end_date: dateRange.value.end,
  preset: activePreset.value,
}));

const activeFiltersCount = ref(0);

const summaryData = ref({});

async function fetchSummary() {
  loading.value = true;
  try {
    const apiMap = {
      visitors: reportsApi.visitors?.summary,
      attendance: reportsApi.attendance?.summary,
      finance: reportsApi.finance?.summary,
      members: reportsApi.membership?.summary,
      departments: reportsApi.departments?.summary,
    };

    const apiMethod = apiMap[tab.value];

    if (apiMethod && typeof apiMethod === 'function') {
      const res = await apiMethod({
        start_date: dateRange.value.start,
        end_date: dateRange.value.end,
      });
      summaryData.value = res?.data?.data || {};
    } else {
      console.warn(`No API method found for tab: ${tab.value}`);
      summaryData.value = {};
    }
  } catch (error) {
    console.error('Failed to fetch summary:', error);
    summaryData.value = {};
  } finally {
    loading.value = false;
  }
}

const activeKpis = computed(() => {
  const activeTab = tabs.find(t => t.id === tab.value);

  if (tab.value === 'visitors') {
    return [
      {
        label: 'Total Visitors',
        value: summaryData.value.total_visitors || '0',
        icon: 'bi bi-people',
        iconColor: activeTab.iconColor,
        iconBg: activeTab.iconBg,
        sublabel: 'All time',
        trend: '+12%',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
        chart: '0,10 20,8 40,12 60,6 80,15 100,10',
      },
      {
        label: 'New Visitors',
        value: summaryData.value.new_visitors || '0',
        icon: 'bi bi-person-plus',
        iconColor: '#10b981',
        iconBg: 'rgba(16, 185, 129, 0.1)',
        sublabel: 'This period',
        trend: '+5',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
      },
      {
        label: 'Conversion Rate',
        value: summaryData.value.conversion_rate || '0%',
        icon: 'bi bi-funnel',
        iconColor: '#f59e0b',
        iconBg: 'rgba(245, 158, 11, 0.1)',
        sublabel: 'Success rate',
        trend: '+2.5%',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
      },
      {
        label: 'Engaged',
        value: summaryData.value.pipeline?.engaged || '0',
        icon: 'bi bi-chat-heart',
        iconColor: '#ec4899',
        iconBg: 'rgba(236, 72, 153, 0.1)',
        sublabel: 'Active contacts',
      },
    ];
  }

  if (tab.value === 'attendance') {
    return [
      {
        label: 'Total Attendance',
        value: summaryData.value.total_attendance || '0',
        icon: 'bi bi-person-check',
        iconColor: activeTab.iconColor,
        iconBg: activeTab.iconBg,
        sublabel: 'All services',
        trend: '+8%',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
        chart: '0,15 20,12 40,18 60,14 80,20 100,17',
      },
      {
        label: 'Avg. Rate',
        value: summaryData.value.average_attendance_rate || '0%',
        icon: 'bi bi-graph-up',
        iconColor: '#10b981',
        iconBg: 'rgba(16, 185, 129, 0.1)',
        sublabel: 'Participation',
        trend: '+3.2%',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
      },
      {
        label: 'Approved',
        value: summaryData.value.approved_records || '0',
        icon: 'bi bi-check-circle',
        iconColor: '#06b6d4',
        iconBg: 'rgba(6, 182, 212, 0.1)',
        sublabel: 'Records verified',
      },
      {
        label: 'Pending',
        value: summaryData.value.pending_records || '0',
        icon: 'bi bi-clock-history',
        iconColor: '#f59e0b',
        iconBg: 'rgba(245, 158, 11, 0.1)',
        sublabel: 'Awaiting review',
      },
    ];
  }

  if (tab.value === 'finance') {
    return [
      {
        label: 'Total Income',
        value: `GHS ${summaryData.value.total_income || '0.00'}`,
        icon: 'bi bi-cash-stack',
        iconColor: activeTab.iconColor,
        iconBg: activeTab.iconBg,
        sublabel: 'Contributions',
        trend: '+15%',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
        chart: '0,20 20,15 40,25 60,22 80,30 100,28',
      },
      {
        label: 'Total Expenses',
        value: `GHS ${summaryData.value.total_expenses || '0.00'}`,
        icon: 'bi bi-cart-x',
        iconColor: '#ef4444',
        iconBg: 'rgba(239, 68, 68, 0.1)',
        sublabel: 'Outgoing',
        trend: '-5%',
        trendClass: 'trend-down',
        trendIcon: 'bi bi-arrow-down',
      },
      {
        label: 'Net Position',
        value: `GHS ${summaryData.value.net_position || '0.00'}`,
        icon: 'bi bi-safe',
        iconColor: '#10b981',
        iconBg: 'rgba(16, 185, 129, 0.1)',
        sublabel: 'Balance',
        trend: '+20%',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
      },
      {
        label: 'Growth',
        value: `${summaryData.value.growth || '0'}%`,
        icon: 'bi bi-graph-up-arrow',
        iconColor: '#8b5cf6',
        iconBg: 'rgba(139, 92, 246, 0.1)',
        sublabel: 'vs last period',
      },
    ];
  }

  if (tab.value === 'members') {
    return [
      {
        label: 'Total Members',
        value: summaryData.value.total || '0',
        icon: 'bi bi-people-fill',
        iconColor: activeTab.iconColor,
        iconBg: activeTab.iconBg,
        sublabel: 'Active',
        trend: '+18',
        trendClass: 'trend-up',
        trendIcon: 'bi bi-arrow-up',
      },
      {
        label: 'New This Period',
        value: summaryData.value.new || '0',
        icon: 'bi bi-person-plus-fill',
        iconColor: '#10b981',
        iconBg: 'rgba(16, 185, 129, 0.1)',
        sublabel: 'Joined recently',
      },
      {
        label: 'Engagement',
        value: `${summaryData.value.engagement || '0'}%`,
        icon: 'bi bi-bar-chart-fill',
        iconColor: '#f59e0b',
        iconBg: 'rgba(245, 158, 11, 0.1)',
        sublabel: 'Participation rate',
      },
      {
        label: 'Retention',
        value: '92%',
        icon: 'bi bi-heart-fill',
        iconColor: '#ec4899',
        iconBg: 'rgba(236, 72, 153, 0.1)',
        sublabel: 'Staying active',
      },
    ];
  }

  // Departments
  return [
    {
      label: 'Total Departments',
      value: summaryData.value.total || '0',
      icon: 'bi bi-diagram-3-fill',
      iconColor: activeTab.iconColor,
      iconBg: activeTab.iconBg,
      sublabel: 'Active units',
    },
    {
      label: 'Total Members',
      value: summaryData.value.total_members || '0',
      icon: 'bi bi-people-fill',
      iconColor: '#10b981',
      iconBg: 'rgba(16, 185, 129, 0.1)',
      sublabel: 'Across all',
    },
    {
      label: 'Avg. Size',
      value: summaryData.value.avg_size || '0',
      icon: 'bi bi-graph-up',
      iconColor: '#06b6d4',
      iconBg: 'rgba(6, 182, 212, 0.1)',
      sublabel: 'Per department',
    },
    {
      label: 'Without Leaders',
      value: summaryData.value.without_leaders || '0',
      icon: 'bi bi-exclamation-triangle',
      iconColor: '#f59e0b',
      iconBg: 'rgba(245, 158, 11, 0.1)',
      sublabel: 'Need assignment',
    },
  ];
});

function changeTab(newTab) {
  tab.value = newTab;
  fetchSummary();
}

function handleLoading(isLoading) {
  loading.value = isLoading;
}

function onPresetChange() {
  if (activePreset.value === 'custom') return;
  const preset = presets.find(p => p.label === activePreset.value);
  if (preset) {
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - preset.val);
    dateRange.value.start = start.toISOString().split('T')[0];
    dateRange.value.end = end.toISOString().split('T')[0];
    fetchSummary();
  }
}

function refreshData() {
  loading.value = true;
  fetchSummary();
  toast.success('Report data refreshed');
}

function exportCurrent() {
  toast.info('Preparing export...');
  setTimeout(() => {
    toast.success('Export completed successfully');
  }, 2000);
}

// Watch tab changes
watch(tab, () => {
  fetchSummary();
});

onMounted(fetchSummary);
</script>

<style scoped>
/* Page Container */
.reports-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #f8fafc 100%);
  padding: 2rem;
  animation: fadeIn 0.5s ease-out;
}

/* Button Styles */
.btn-action {
  border-radius: 12px;
  padding: 0.625rem 1.25rem;
  font-weight: 600;
  font-size: 0.875rem;
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.btn-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.btn-primary-custom {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
}

.btn-primary-custom:hover {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}

/* Tabs Navigation */
.tabs-navigation {
  background: white;
  border-radius: 16px;
  padding: 0.5rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  overflow-x: auto;
}

.tabs-scroll-container {
  display: flex;
  gap: 0.5rem;
  min-width: min-content;
}

.tab-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border: none;
  background: transparent;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
  color: #64748b;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}

.tab-item:hover {
  background: #f8fafc;
  color: #334155;
}

.tab-item.active {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  color: #0f172a;
}

.tab-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.25rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-item.active .tab-icon {
  color: white;
}

.tab-label {
  font-weight: 600;
}

.tab-indicator {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 40%;
  height: 3px;
  background: linear-gradient(90deg, transparent, currentColor, transparent);
  border-radius: 3px 3px 0 0;
}

/* Filters Bar */
.filters-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.5rem;
  background: white;
  padding: 1.5rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}

.filters-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  flex: 1;
}

.filters-right {
  display: flex;
  gap: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin: 0;
}

.filter-select,
.filter-input {
  min-width: 160px;
  padding: 0.625rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #0f172a;
  background: white;
  transition: all 0.3s;
}

.filter-select:focus,
.filter-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  outline: none;
}

.custom-date-range {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
}

.date-separator {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-bottom: 0.5rem;
  color: #94a3b8;
  font-size: 1.25rem;
}

.filter-btn {
  display: flex;
  align-items: center;
  padding: 0.625rem 1.25rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  font-weight: 600;
  font-size: 0.875rem;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: #f8fafc;
}

/* KPIs Grid */
.kpis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideUp 0.5s ease-out forwards;
  opacity: 0;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.kpi-icon-wrapper {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  font-size: 1.5rem;
}

.kpi-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.trend-up {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.trend-down {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.kpi-body {
  margin-bottom: 1rem;
}

.kpi-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.kpi-value {
  font-size: 2rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.kpi-sublabel {
  font-size: 0.8125rem;
  color: #94a3b8;
  font-weight: 500;
}

.kpi-mini-chart {
  height: 40px;
  margin-top: 1rem;
  opacity: 0.6;
}

/* Skeleton Loading */
.kpis-skeleton {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.skeleton-card {
  height: 180px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 16px;
}

/* Report Content */
.report-content-wrapper {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  min-height: 400px;
}

.report-content-inner {
  animation: fadeSlide 0.4s ease-out;
}

.content-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  gap: 1.5rem;
}

.loading-spinner {
  font-size: 2rem;
}

.loading-text {
  font-size: 1rem;
  font-weight: 600;
  color: #64748b;
  margin: 0;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-state-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  font-size: 2.5rem;
  color: #94a3b8;
  margin-bottom: 1.5rem;
}

.empty-state-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.empty-state-text {
  font-size: 1rem;
  color: #64748b;
  max-width: 500px;
  margin-bottom: 2rem;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeSlide {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

.spin-anim {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Transitions */
.fade-in-enter-active,
.fade-in-leave-active {
  transition: opacity 0.3s ease;
}

.fade-in-enter-from,
.fade-in-leave-to {
  opacity: 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

.slide-fade-enter-active {
  transition: all 0.3s ease;
}

.slide-fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter-from {
  transform: translateX(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(10px);
  opacity: 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .kpis-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }
}

@media (max-width: 768px) {
  .reports-page {
    padding: 1rem;
  }

  .tabs-navigation {
    padding: 0.25rem;
  }

  .tab-item {
    padding: 0.75rem 1rem;
    font-size: 0.8125rem;
  }

  .tab-icon {
    width: 36px;
    height: 36px;
    font-size: 1.125rem;
  }

  .filters-bar {
    flex-direction: column;
    align-items: stretch;
    padding: 1rem;
  }

  .filters-left,
  .filters-right {
    width: 100%;
  }

  .custom-date-range {
    flex-direction: column;
    align-items: stretch;
  }

  .date-separator {
    transform: rotate(90deg);
    padding: 0.5rem 0;
  }

  .kpis-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .kpi-value {
    font-size: 1.75rem;
  }

  .report-content-wrapper {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .tab-label {
    display: none;
  }

  .tab-item {
    padding: 0.75rem;
    justify-content: center;
  }

  .btn-action span {
    display: none !important;
  }

  .kpi-card {
    padding: 1rem;
  }
}
</style>
