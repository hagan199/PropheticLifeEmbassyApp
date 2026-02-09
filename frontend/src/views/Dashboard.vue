<template>
  <div class="dashboard-container">
    <PageHeader title="Dashboard" :subtitle="today">
      <!-- @ts-ignore -->
      <template #actions>
        <div class="md-filter-bar mb-0 p-0 bg-transparent shadow-none">
          <div class="btn-group filter-group">
            <button class="btn btn-sm" :class="range === 'today' ? 'btn-primary' : 'btn-light'"
              @click="setRange('today')">
              Today
            </button>
            <button class="btn btn-sm" :class="range === 'week' ? 'btn-primary' : 'btn-light'"
              @click="setRange('week')">
              Week
            </button>
            <button class="btn btn-sm" :class="range === 'month' ? 'btn-primary' : 'btn-light'"
              @click="setRange('month')">
              Month
            </button>
            <button class="btn btn-sm" :class="range === 'custom' ? 'btn-primary' : 'btn-light'"
              @click="showCustomRange = true">
              <i class="bi bi-calendar-range me-1"></i>Custom
            </button>
          </div>
          <div v-if="range === 'custom' && customDateLabel" class="badge bg-light text-dark px-3 py-2">
            <i class="bi bi-calendar3 me-1"></i>{{ customDateLabel }}
          </div>
          <button class="btn btn-primary" @click="$router.push('/users')">New Member</button>
          <button class="btn btn-success" @click="$router.push('/attendance')">
            Record Attendance
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Custom Date Range Modal -->
    <CModal :visible="showCustomRange" @close="showCustomRange = false">
      <CModalHeader>
        <CModalTitle><i class="bi bi-calendar-range me-2"></i>Select Date Range</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Start Date</label>
            <input v-model="customStartDate" type="date" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input v-model="customEndDate" type="date" class="form-control" />
          </div>
        </div>
        <div class="mt-3">
          <div class="fw-semibold mb-2">Quick Selections</div>
          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-light btn-sm" @click="setQuickRange('last7')">
              Last 7 Days
            </button>
            <button class="btn btn-light btn-sm" @click="setQuickRange('last30')">
              Last 30 Days
            </button>
            <button class="btn btn-light btn-sm" @click="setQuickRange('last90')">
              Last 90 Days
            </button>
            <button class="btn btn-light btn-sm" @click="setQuickRange('thisMonth')">
              This Month
            </button>
            <button class="btn btn-light btn-sm" @click="setQuickRange('lastMonth')">
              Last Month
            </button>
            <button class="btn btn-light btn-sm" @click="setQuickRange('thisYear')">
              This Year
            </button>
          </div>
        </div>
      </CModalBody>
      <CModalFooter>
        <button class="btn btn-secondary" @click="showCustomRange = false">Cancel</button>
        <button class="btn btn-primary" @click="applyCustomRange">
          <i class="bi bi-check-lg me-1"></i>Apply Range
        </button>
      </CModalFooter>
    </CModal>

    <!-- Loading State -->
    <div v-if="loading" class="md-loading-overlay">
      <div class="md-spinner"></div>
    </div>

    <template v-else>
      <!-- Stats Grid -->
      <div class="md-stats-grid md-stagger md-scale-in">
        <MaterialStatCard title="Members" :value="stats.members?.value || 0" :sub="stats.members?.sub || ''"
          icon="bi bi-people" :progress="stats.members?.progress || 0" color="primary"
          :trend="stats.members ? getTrendValue(stats.members) : null" />
        <MaterialStatCard title="Visitors" :value="stats.visitors?.value || 0" :sub="stats.visitors?.sub || ''"
          icon="bi bi-person-plus" :progress="stats.visitors?.progress || 0" color="info"
          :trend="stats.visitors ? getTrendValue(stats.visitors) : null" />
        <MaterialStatCard title="Attendance" :value="stats.attendance?.value || '0%'" :sub="stats.attendance?.sub || ''"
          icon="bi bi-bullseye" :progress="stats.attendance?.progress || 0" color="warning"
          :trend="stats.attendance ? getTrendValue(stats.attendance) : null" />
        <MaterialStatCard title="Finance" :value="formatFinance(stats.finance?.value ?? 0)"
          :sub="stats.finance?.sub || ''" icon="bi bi-graph-up-arrow" :progress="stats.finance?.progress || 0"
          color="success" :trend="stats.finance ? getTrendValue(stats.finance) : null" />
      </div>

      <!-- Second Row Stats -->
      <div class="md-stats-row">
        <MaterialStatCard title="Conversion" :value="(analytics.conversion_rate?.rate ?? 0) + '%'" :sub="(analytics.conversion_rate?.converted ?? 0) +
          ' converted of ' +
          (analytics.conversion_rate?.total ?? 0) +
          ' visitors'
          " icon="bi bi-bar-chart" :progress="analytics.conversion_rate?.rate ?? 0" color="primary" variant="primary"
          class="md-stats-card" />
        <MaterialStatCard title="Follow-ups" :value="analytics.follow_up_stats?.due ?? 0"
          :sub="(analytics.follow_up_stats?.completed ?? 0) + ' completed'" icon="bi bi-clipboard-check"
          :progress="analytics.follow_up_stats?.due ?? 0" color="warning" variant="warning" class="md-stats-card" />
        <MaterialStatCard title="Events" :value="stats.events?.value ?? 0" :sub="stats.events?.sub ?? 'Scheduled'"
          icon="bi bi-calendar-event" :progress="stats.events?.progress ?? 0" color="success" variant="success"
          class="md-stats-card" />
      </div>

      <!-- Main Content Grid -->
      <div class="md-content-grid">
        <!-- Main Charts Column -->
        <div class="md-col-8 md-slide-up" style="animation-delay: 300ms">
          <!-- Attendance Chart -->
          <MaterialCard class="mb-6">
            <!-- @ts-ignore -->
            <template #header>
              <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                  <h3 class="md-title-medium mb-1">Attendance Trend</h3>
                  <p class="md-body-small text-muted mb-0">Last 8 services</p>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-light"><i class="bi bi-download"></i></button>
                </div>
              </div>
            </template>
            <div class="md-chart-container">
              <CChartLine :data="attendanceData" :options="chartOptions" />
            </div>
          </MaterialCard>

          <!-- Finance Chart -->
          <MaterialCard class="mb-6">
            <!-- @ts-ignore -->
            <template #header>
              <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                  <h3 class="md-title-medium mb-1">Finance Overview</h3>
                  <p class="md-body-small text-muted mb-0">Last 6 weeks</p>
                </div>
              </div>
            </template>
            <div class="md-chart-container">
              <CChartBar :data="financeData" :options="financeOptions" />
            </div>
          </MaterialCard>

          <!-- Recent Members Table -->
          <MaterialCard title="Recent Members">
            <!-- @ts-ignore -->
            <template #header-actions>
              <button class="btn btn-light btn-sm">View all</button>
            </template>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-end">Joined</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="member in recentMembers" :key="member.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="md-avatar sm me-3" :class="getRandomColor(member.name)">
                          {{ getInitials(member.name) }}
                        </div>
                        <div class="fw-bold">{{ member.name }}</div>
                      </div>
                    </td>
                    <td>{{ member.phone || '-' }}</td>
                    <td>
                      <span class="badge" :class="getStatusClass(member.status ?? '')">{{
                        member.status
                        }}</span>
                    </td>
                    <td class="text-end text-muted small">{{ member.date || member.joined }}</td>
                    <td class="text-end">
                      <button class="btn btn-light btn-sm rounded-circle" @click="openEditMember(member)">
                        <i class="bi bi-pencil"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="recentMembers.length === 0">
                    <td colspan="4" class="text-center py-4 text-muted">No recent members</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </MaterialCard>
        </div>

        <!-- Sidebar Column -->
        <div class="md-col-4 md-slide-up" style="animation-delay: 400ms">
          <!-- Quick Actions -->
          <MaterialCard title="Quick Actions" class="mb-6">
            <div class="awesome-actions">
              <button class="awesome-action add-member" @click="$router.push('/users')">
                <span class="icon-bg"><i class="bi bi-person-plus-fill"></i></span>
                <span class="label">Add Member</span>
              </button>
              <button class="awesome-action record-offering" @click="$router.push('/contributions')">
                <span class="icon-bg"><i class="bi bi-cash-stack"></i></span>
                <span class="label">Record Offering</span>
              </button>
              <button class="awesome-action schedule-event" @click="$router.push('/attendance')">
                <span class="icon-bg"><i class="bi bi-calendar-check"></i></span>
                <span class="label">Schedule Event</span>
              </button>
              <button class="awesome-action send-message" @click="$router.push('/broadcasts')">
                <span class="icon-bg"><i class="bi bi-chat-dots-fill"></i></span>
                <span class="label">Send Message</span>
              </button>
            </div>
          </MaterialCard>

          <!-- Tasks -->
          <MaterialCard title="Tasks" class="mb-6">
            <ul class="md-list">
              <li v-for="task in tasks" :key="task.label" class="md-list-item cursor-pointer"
                @click="task.link && $router.push(task.link)">
                <div class="md-list-item-icon" :class="`bg-${task.color}-subtle text-${task.color}`">
                  <i class="bi bi-check-circle"></i>
                </div>
                <div class="md-list-item-content">
                  <div class="md-list-item-title">{{ task.label }}</div>
                </div>
                <div class="md-list-item-action">
                  <span class="badge rounded-pill" :class="`bg-${task.color}`">{{ task.due }}</span>
                </div>
              </li>
              <li v-if="tasks.length === 0" class="md-list-item justify-content-center text-muted">
                No tasks
              </li>
            </ul>
          </MaterialCard>

          <!-- Activity -->
          <MaterialCard title="Recent Activity">
            <ul class="md-list">
              <li v-for="(activity, i) in recentActivities" :key="i" class="md-list-item">
                <div class="md-list-item-icon bg-light">
                  <i class="bi bi-clock-history"></i>
                </div>
                <div class="md-list-item-content">
                  <div class="md-list-item-title small">{{ activity.action }}</div>
                  <div class="md-list-item-subtitle">{{ activity.time }}</div>
                </div>
              </li>
              <li v-if="recentActivities.length === 0" class="md-list-item justify-content-center text-muted">
                No recent activity
              </li>
            </ul>
          </MaterialCard>
        </div>
      </div>
    </template>

    <!-- Edit Member Modal -->
    <CModal :visible="editModalVisible" alignment="center" @close="editModalVisible = false">
      <div class="p-0 overflow-hidden">
        <div class="modal-header border-0 bg-surface-container-high p-4">
          <div>
            <h5 class="fw-bold mb-1">Edit Member Details</h5>
            <p class="small text-muted mb-0">Update information for {{ editMember.name }}</p>
          </div>
          <button type="button" class="btn-close" @click="editModalVisible = false"></button>
        </div>
        <div class="modal-body p-4">
          <div class="md-input-wrapper mb-4">
            <input v-model="editMember.name" type="text" class="md-input" placeholder=" " />
            <label class="md-label-floating">Full Name</label>
          </div>
          <div class="md-input-wrapper mb-4">
            <input v-model="editMember.phone" type="text" class="md-input" placeholder=" " />
            <label class="md-label-floating">Phone Number</label>
          </div>
          <div class="md-input-wrapper mb-4">
            <select v-model="editMember.status" class="md-input form-select">
              <option value="visitor">Visitor</option>
              <option value="partner">Partner</option>
              <option value="active">Active Member</option>
            </select>
            <label class="md-label-floating">Status/Category</label>
          </div>
          <!-- Date (Read-only or editable?) -->
          <div class="md-input-wrapper mb-2">
            <input v-model="editMember.date" type="text" class="md-input" placeholder=" " readonly />
            <label class="md-label-floating">Joined Date</label>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-3">
            <button class="md-btn md-btn-text text-muted" :disabled="isSavingMember" @click="editModalVisible = false">
              Cancel
            </button>
            <button class="md-btn md-btn-filled" :disabled="isSavingMember" @click="saveEditMember">
              <span v-if="isSavingMember" class="spinner-border spinner-border-sm me-1"></span>
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </CModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter } from '@coreui/vue';
import { CChartLine, CChartBar } from '@coreui/vue-chartjs';
import PageHeader from '../components/shared/PageHeader.vue';
import MaterialCard from '../components/material/MaterialCard.vue';
import MaterialStatCard from '../components/StatCard.vue';
import { useThemeStore } from '../store/theme';
import { dashboardApi, usersApi } from '../api';

// Types
interface StatItem {
  value: string | number;
  sub: string;
  progress: number;
  trend?: string | null;
  change?: string | number;
}

interface Stats {
  members: StatItem | null;
  visitors: StatItem | null;
  attendance: StatItem | null;
  finance: StatItem | null;
  conversion: StatItem | null;
  followUps: StatItem | null;
  events: StatItem | null;
}

interface Analytics {
  conversion_rate: { rate: number; converted: number; total: number };
  follow_up_stats: { due: number; completed: number };
  attendance_trend?: { labels: string[]; data: number[] };
  finance_trend?: { labels: string[]; contributions: number[] };
}

interface Member {
  id: number;
  name: string;
  phone?: string;
  role?: string;
  status?: string;
  date?: string;
  joined?: string;
}

interface Activity {
  id: number;
  description: string;
  date: string;
  action?: string;
  time?: string;
}

interface Task {
  label: string;
  due: string;
  color: string;
  link: string | null;
}

// Data state
const range = ref<string>('week');
const loading = ref<boolean>(true);
const stats = ref<Stats>({
  members: null,
  visitors: null,
  attendance: null,
  finance: null,
  conversion: null,
  followUps: null,
  events: null,
});
const analytics = ref<Analytics>({
  conversion_rate: { rate: 0, converted: 0, total: 0 },
  follow_up_stats: { due: 0, completed: 0 },
});
const recentMembers = ref<Member[]>([]);
const recentActivities = ref<Activity[]>([]);
const tasks = ref<Task[]>([]);
const editModalVisible = ref<boolean>(false);
const editMember = ref<Member>({ id: 0, name: '', phone: '', role: '', status: '' });

// Custom date range
const showCustomRange = ref<boolean>(false);
const customStartDate = ref<string>('');
const customEndDate = ref<string>('');
const customDateLabel = ref<string>('');

const today = computed<string>(() =>
  new Date().toLocaleDateString(undefined, {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
);

// Formatters
function formatNumber(num: number | string): string {
  if (typeof num === 'string') return num;
  return new Intl.NumberFormat().format(num);
}

function formatFinance(val: string | number): string {
  if (!val) return 'GHS 0';
  if (typeof val === 'string' && val.startsWith('GHS')) return val;
  return 'GHS ' + formatNumber(val);
}

function formatDateLabel(start: string, end: string): string {
  const startDate = new Date(start);
  const endDate = new Date(end);
  const options: Intl.DateTimeFormatOptions = { month: 'short', day: 'numeric' };
  return `${startDate.toLocaleDateString(undefined, options)} - ${endDate.toLocaleDateString(
    undefined,
    options
  )}`;
}

function getInitials(name: string): string {
  if (!name) return '';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
}

function getRandomColor(str: string): string {
  const colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger'];
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length]!;
}

function getStatusClass(status: string): string {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'bg-success';
    case 'visitor':
      return 'bg-info';
    case 'partner':
      return 'bg-primary';
    default:
      return 'bg-warning';
  }
}

function getTrendValue(stat: StatItem): string | null {
  if (!stat?.change) return null;
  return stat.trend === 'up' ? 'up' : stat.trend === 'down' ? 'down' : null;
}

// Quick range selections
function setQuickRange(type: string): void {
  const today = new Date();
  let start = new Date();
  let end = new Date();

  switch (type) {
    case 'last7':
      end = new Date(today);
      start = new Date(today);
      start.setDate(end.getDate() - 6);
      break;
    case 'last30':
      end = new Date(today);
      start = new Date(today);
      start.setDate(end.getDate() - 29);
      break;
    case 'last90':
      end = new Date(today);
      start = new Date(today);
      start.setDate(end.getDate() - 89);
      break;
    case 'thisMonth':
      end = new Date(today);
      start = new Date(today.getFullYear(), today.getMonth(), 1);
      break;
    case 'lastMonth':
      start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      end = new Date(today.getFullYear(), today.getMonth(), 0);
      break;
    case 'thisYear':
      end = new Date(today);
      start = new Date(today.getFullYear(), 0, 1);
      break;
  }

  // Always ensure start <= end
  if (start > end) {
    const temp = start;
    start = end;
    end = temp;
  }

  customStartDate.value = start.toISOString().split('T')[0]!;
  customEndDate.value = end.toISOString().split('T')[0]!;
}

// Apply custom date range
function applyCustomRange(): void {
  if (!customStartDate.value || !customEndDate.value) {
    alert('Please select both start and end dates');
    return;
  }

  if (new Date(customStartDate.value) > new Date(customEndDate.value)) {
    alert('Start date cannot be after end date');
    return;
  }

  customDateLabel.value = formatDateLabel(customStartDate.value, customEndDate.value);
  range.value = 'custom';
  showCustomRange.value = false;
  fetchDashboardData();
}

// Fetch dashboard data
async function fetchDashboardData(): Promise<void> {
  loading.value = true;
  try {
    const params: Record<string, string> = { range: range.value };
    if (range.value === 'custom' && customStartDate.value && customEndDate.value) {
      params.start_date = customStartDate.value;
      params.end_date = customEndDate.value;
    }

    const [statsRes, analyticsRes] = await Promise.all([
      dashboardApi.stats(params),
      dashboardApi.analytics(params),
    ]);

    const data = statsRes.data?.data || {};
    const analyticsData = analyticsRes.data?.data || {};

    // Map stats
    const overview = data.overview || [];
    stats.value = {
      members: mapOverviewItem(overview, 'Total Members', 'members'),
      visitors: mapOverviewItem(overview, 'New Visitors', 'visitors'),
      attendance: mapOverviewItem(overview, 'Attendance', 'attendance', true),
      finance: mapOverviewItem(overview, 'Contributions', 'finance', true),
      conversion: mapOverviewItem(overview, 'Conversion Rate', 'conversion') || {
        value: '38%',
        sub: 'This week',
        progress: 38,
      },
      followUps: mapOverviewItem(overview, 'Pending Follow-ups', 'followups') || {
        value: 5,
        sub: 'Pending',
        progress: 60,
      },
      events: mapOverviewItem(overview, 'Scheduled Events', 'events') || {
        value: 5,
        sub: 'Scheduled',
        progress: 40,
      },
    };

    analytics.value = analyticsData ?? {
      conversion_rate: { rate: 0, converted: 0, total: 0 },
      follow_up_stats: { due: 0, completed: 0 },
    };
    recentActivities.value = data.recent_activities || [];
    recentMembers.value = data.recent_members || [];

    // Tasks
    const quickActions = data.quick_actions || [];
    tasks.value = quickActions.map((action: any) => ({
      label: action.label,
      due: action.count?.toString() || '',
      color: action.count > 5 ? 'warning' : 'primary',
      link: action.link || null,
    }));
  } catch (error: any) {
    console.error('Failed to fetch dashboard data:', error);
    // Set default values so the UI doesn't break
    analytics.value = {
      conversion_rate: { rate: 0, converted: 0, total: 0 },
      follow_up_stats: { due: 0, completed: 0 },
    };
    stats.value = {
      members: null,
      visitors: null,
      attendance: null,
      finance: null,
      conversion: null,
      followUps: null,
      events: { value: 0, sub: 'Scheduled', progress: 0 },
    };
    // Optionally: show a toast notification to the user
  } finally {
    loading.value = false;
  }
}

function mapOverviewItem(overview: any[], label: string, type: string, partialMatch: boolean = false): StatItem | null {
  let item;
  if (partialMatch) {
    item = overview.find(o => o.label.includes(label));
  } else {
    item = overview.find(o => o.label === label);
  }
  if (!item) return null;

  let progress = 50;
  let sub = `${item.change} ${item.trend === 'up' ? '↑' : item.trend === 'down' ? '↓' : ''}`;

  if (type === 'attendance') {
    const val = parseInt(item.value) || 0;
    progress = Math.min(100, (val / 100) * 100);
    return { value: val + '%', sub: item.change, progress: Math.min(100, val), trend: item.trend };
  }

  if (type === 'conversion') {
    const val = parseInt(item.value) || 0;
    return { value: item.value, sub: item.change, progress: val, trend: 'up' };
  }

  // Finance
  if (type === 'finance') {
    return { value: item.value, sub: item.change, progress: 65, trend: item.trend };
  }

  if (type === 'followups') {
    return { value: item.value, sub: 'Pending', progress: 60, trend: null };
  }

  if (type === 'events') {
    return { value: item.value, sub: 'Scheduled', progress: 40, trend: null };
  }

  progress = Math.min(100, Math.abs(parseInt(item.change) || 0) * 5 + 50);
  return { value: item.value, sub, progress, trend: item.trend };
}

function setRange(newRange: string): void {
  if (newRange !== 'custom') {
    customDateLabel.value = '';
    customStartDate.value = '';
    customEndDate.value = '';
  }
  range.value = newRange;
}

watch(range, (newVal: string) => {
  if (newVal !== 'custom') fetchDashboardData();
});

const theme = useThemeStore();
const legendColor = computed<string>(() => (theme.mode === 'dark' ? '#e5e7eb' : '#334155'));
const gridColor = computed<string>(() => (theme.mode === 'dark' ? 'rgba(255,255,255,.08)' : '#eef2f7'));
const tickColor = computed<string>(() => (theme.mode === 'dark' ? '#cbd5e1' : '#475569'));

const attendanceData = computed(() => ({
  labels: analytics.value.attendance_trend?.labels || ['W1', 'W2', 'W3', 'W4'],
  datasets: [
    {
      label: 'Attendance %',
      backgroundColor: 'rgba(13,110,253,.2)',
      borderColor: '#0d6efd',
      pointBackgroundColor: '#0d6efd',
      data: analytics.value.attendance_trend?.data || [],
      tension: 0.35,
      fill: true,
    },
  ],
}));

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { labels: { color: legendColor.value } } },
  scales: {
    x: { ticks: { color: tickColor.value }, grid: { color: gridColor.value } },
    y: {
      beginAtZero: true,
      max: 100,
      ticks: { color: tickColor.value },
      grid: { color: gridColor.value },
    },
  },
}));

const financeData = computed(() => ({
  labels: analytics.value.finance_trend?.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Contributions (GHS)',
      backgroundColor: 'rgba(25,135,84,.6)',
      borderColor: '#198754',
      data: analytics.value.finance_trend?.contributions || [],
    },
  ],
}));

const financeOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: legendColor.value } },
    tooltip: {
      callbacks: { label: (ctx: any) => 'GHS ' + Intl.NumberFormat().format(ctx.parsed.y) },
    },
  },
  scales: {
    x: { ticks: { color: tickColor.value }, grid: { color: gridColor.value } },
    y: {
      ticks: {
        color: tickColor.value,
        callback: (v: any) => 'GHS ' + Intl.NumberFormat().format(v),
      },
      grid: { color: gridColor.value },
    },
  },
}));

const isSavingMember = ref<boolean>(false);

function openEditMember(member: Member): void {
  editMember.value = { ...member };
  editModalVisible.value = true;
}

async function saveEditMember(): Promise<void> {
  if (!editMember.value.id) return;
  isSavingMember.value = true;
  try {
    await usersApi.update(editMember.value.id, {
      name: editMember.value.name,
      phone: editMember.value.phone,
    });
    editModalVisible.value = false;
    fetchDashboardData();
  } catch (error: any) {
    console.error('Failed to save member:', error);
    alert(error.response?.data?.message || 'Failed to update member');
  } finally {
    isSavingMember.value = false;
  }
}

onMounted(() => {
  fetchDashboardData();
});

</script>

<style scoped>
.dashboard-container {
  max-width: 1600px;
  margin: 0 auto;
}

/* Avatar with colors */
.bg-primary {
  background-color: var(--md-primary) !important;
  color: white;
}

.bg-success {
  background-color: var(--md-success) !important;
  color: white;
}

.bg-warning {
  background-color: var(--md-warning) !important;
  color: white;
}

.bg-info {
  background-color: var(--md-info) !important;
  color: white;
}

.bg-danger {
  background-color: var(--md-error) !important;
  color: white;
}

.bg-primary-subtle {
  background-color: var(--md-primary-container) !important;
}

.bg-warning-subtle {
  background-color: var(--md-warning-container) !important;
}

.text-primary {
  color: var(--md-primary) !important;
}

.text-warning {
  color: var(--md-warning) !important;
}

/* Dashboard Stat Row Custom Spacing */
.md-stats-row {
  display: flex;
  gap: 2.2rem;
  margin-bottom: 2.5rem;
  margin-top: 0.5rem;
  justify-content: flex-start;
  align-items: stretch;
}

.md-stats-card {
  flex: 1 1 0;
  min-width: 260px;
  max-width: 420px;
  display: flex;
}

@media (max-width: 991.98px) {
  .md-stats-row {
    flex-direction: column;
    gap: 1.2rem;
    margin-bottom: 1.5rem;
  }

  .md-stats-card {
    max-width: 100%;
    min-width: 0;
  }
}

/* Awesome Quick Actions Styles */
.awesome-actions {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
  margin-top: 0.5rem;
}

.awesome-action {
  display: flex;
  align-items: center;
  gap: 1.1rem;
  background: rgba(255, 255, 255, 0.7);
  border: none;
  border-radius: 2rem;
  box-shadow: 0 4px 24px 0 rgba(102, 126, 234, 0.1);
  padding: 1.1rem 1.5rem;
  font-size: 1.13rem;
  font-weight: 500;
  color: #222;
  cursor: pointer;
  transition: box-shadow 0.18s, background 0.18s, transform 0.18s;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.awesome-action .icon-bg {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.6rem;
  height: 2.6rem;
  border-radius: 50%;
  font-size: 1.45rem;
  background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
  margin-right: 0.5rem;
  transition: background 0.18s, box-shadow 0.18s;
}

.awesome-action.add-member .icon-bg {
  background: linear-gradient(135deg, #a78bfa 0%, #6366f1 100%);
  color: #fff;
}

.awesome-action.record-offering .icon-bg {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e42 100%);
  color: #fff;
}

.awesome-action.schedule-event .icon-bg {
  background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
  color: #fff;
}

.awesome-action.send-message .icon-bg {
  background: linear-gradient(135deg, #38bdf8 0%, #6366f1 100%);
  color: #fff;
}

.awesome-action:hover,
.awesome-action:focus {
  box-shadow: 0 8px 32px 0 rgba(99, 102, 241, 0.16);
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px) scale(1.025);
}

.awesome-action:active {
  transform: scale(0.98);
}

.awesome-action .label {
  flex: 1;
  font-size: 1.13rem;
  font-weight: 500;
  color: #222;
}

@media (max-width: 991.98px) {
  .awesome-actions {
    gap: 0.7rem;
  }

  .awesome-action {
    font-size: 1.05rem;
    padding: 0.9rem 1.1rem;
  }

  .awesome-action .icon-bg {
    width: 2.1rem;
    height: 2.1rem;
    font-size: 1.1rem;
  }
}
</style>
