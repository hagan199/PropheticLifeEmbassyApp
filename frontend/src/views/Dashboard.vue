<template>
  <div class="dash-wrap">
    <div class="dash-header">
      <div>
        <h2 class="title">Dashboard</h2>
        <Breadcrumbs />
        <div class="date text-muted">{{ today }}</div>
      </div>
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="btn-group filter-group">
          <CButton :color="range === 'today' ? 'primary' : 'light'" size="sm" @click="setRange('today')">Today</CButton>
          <CButton :color="range === 'week' ? 'primary' : 'light'" size="sm" @click="setRange('week')">Week</CButton>
          <CButton :color="range === 'month' ? 'primary' : 'light'" size="sm" @click="setRange('month')">Month</CButton>
          <CButton :color="range === 'custom' ? 'primary' : 'light'" size="sm" @click="showCustomRange = true">
            <i class="bi bi-calendar-range me-1"></i>Custom
          </CButton>
        </div>
        <div v-if="range === 'custom' && customDateLabel" class="badge bg-light text-dark px-3 py-2">
          <i class="bi bi-calendar3 me-1"></i>{{ customDateLabel }}
        </div>
        <CButton color="primary" @click="$router.push('/users')">New Member</CButton>
        <CButton color="success" @click="$router.push('/attendance')">Record Attendance</CButton>
      </div>
    </div>

    <!-- Custom Date Range Modal -->
    <CModal :visible="showCustomRange" @close="showCustomRange = false">
      <CModalHeader>
        <CModalTitle><i class="bi bi-calendar-range me-2"></i>Select Date Range</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CRow class="g-3">
          <CCol md="6">
            <CFormLabel>Start Date</CFormLabel>
            <CFormInput type="date" v-model="customStartDate" />
          </CCol>
          <CCol md="6">
            <CFormLabel>End Date</CFormLabel>
            <CFormInput type="date" v-model="customEndDate" />
          </CCol>
        </CRow>
        <div class="mt-3">
          <div class="fw-semibold mb-2">Quick Selections</div>
          <div class="d-flex flex-wrap gap-2">
            <CButton color="light" size="sm" @click="setQuickRange('last7')">Last 7 Days</CButton>
            <CButton color="light" size="sm" @click="setQuickRange('last30')">Last 30 Days</CButton>
            <CButton color="light" size="sm" @click="setQuickRange('last90')">Last 90 Days</CButton>
            <CButton color="light" size="sm" @click="setQuickRange('thisMonth')">This Month</CButton>
            <CButton color="light" size="sm" @click="setQuickRange('lastMonth')">Last Month</CButton>
            <CButton color="light" size="sm" @click="setQuickRange('thisYear')">This Year</CButton>
          </div>
        </div>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showCustomRange = false">Cancel</CButton>
        <CButton color="primary" @click="applyCustomRange">
          <i class="bi bi-check-lg me-1"></i>Apply Range
        </CButton>
      </CModalFooter>
    </CModal>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <CSpinner color="primary" />
      <p class="mt-2 text-muted">Loading dashboard...</p>
    </div>

    <template v-else>
      <CRow class="g-4">
        <CCol sm="6" xl="3">
          <StatCard title="Members" :value="formatNumber(stats.members?.value || 0)" :sub="stats.members?.sub || ''"
            icon="bi bi-people" :progress="stats.members?.progress || 0" color="primary" />
        </CCol>
        <CCol sm="6" xl="3">
          <StatCard title="Visitors" :value="formatNumber(stats.visitors?.value || 0)" :sub="stats.visitors?.sub || ''"
            icon="bi bi-person-plus" :progress="stats.visitors?.progress || 0" color="info" />
        </CCol>
        <CCol sm="6" xl="3">
          <StatCard title="Attendance" :value="stats.attendance?.value || '0%'" :sub="stats.attendance?.sub || ''"
            icon="bi bi-bullseye" :progress="stats.attendance?.progress || 0" color="warning" />
        </CCol>
        <CCol sm="6" xl="3">
          <StatCard title="Finance" :value="stats.finance?.value || 'GHS 0'" :sub="stats.finance?.sub || ''"
            icon="bi bi-graph-up-arrow" :progress="stats.finance?.progress || 0" color="success" />
        </CCol>
      </CRow>

      <CRow class="g-4 mt-1">
        <CCol sm="6" xl="4">
          <StatCard title="Conversion" :value="stats.conversion?.value || '0%'"
            :sub="stats.conversion?.sub || 'This week'" icon="bi bi-bar-chart"
            :progress="stats.conversion?.progress || 0" color="primary" />
        </CCol>
        <CCol sm="6" xl="4">
          <StatCard title="Follow-ups" :value="formatNumber(stats.followUps?.value || 0)"
            :sub="stats.followUps?.sub || 'Pending'" icon="bi bi-clipboard-check"
            :progress="stats.followUps?.progress || 0" color="warning" />
        </CCol>
        <CCol sm="6" xl="4">
          <StatCard title="Events" :value="formatNumber(stats.events?.value || 0)"
            :sub="stats.events?.sub || 'Scheduled'" icon="bi bi-calendar-event" :progress="stats.events?.progress || 0"
            color="success" />
        </CCol>
        <CCol xl="8">
          <CCard class="mb-4">
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Attendance Trend</div>
              <div class="text-muted small">Last 8 services</div>
            </CCardHeader>
            <CCardBody style="min-height: 220px">
              <CChartLine :data="attendanceData" :options="chartOptions" />
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Finance Overview</div>
              <div class="text-muted small">Last 6 weeks</div>
            </CCardHeader>
            <CCardBody style="min-height: 220px">
              <CChartBar :data="financeData" :options="financeOptions" />
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Recent Members</div>
              <CButton color="light" size="sm">View all</CButton>
            </CCardHeader>
            <CCardBody>
              <CTable hover responsive>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell scope="col">Name</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                    <CTableHeaderCell scope="col" class="text-end">Joined</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  <CTableRow v-for="member in recentMembers" :key="member.id">
                    <CTableDataCell>{{ member.name }}</CTableDataCell>
                    <CTableDataCell>{{ member.phone || '-' }}</CTableDataCell>
                    <CTableDataCell>
                      <CBadge
                        :color="member.status === 'active' ? 'success' : member.status === 'visitor' ? 'info' : member.status === 'partner' ? 'primary' : 'warning'">
                        {{ member.status }}</CBadge>
                    </CTableDataCell>
                    <CTableDataCell>{{ member.date || member.joined }}</CTableDataCell>
                    <CTableDataCell>
                      <CButton color="light" size="sm" @click="openEditMember(member)"><i class="bi bi-pencil"></i> Edit
                      </CButton>
                    </CTableDataCell>
                  </CTableRow>
                  <CTableRow v-if="recentMembers.length === 0">
                    <CTableDataCell colspan="5" class="text-center text-muted">No recent members</CTableDataCell>
                  </CTableRow>
                  <!-- Edit Member Modal -->
                  <CModal :visible="editModalVisible" @close="editModalVisible = false">
                    <CModalHeader>
                      <CModalTitle>Edit Visitor/Partner</CModalTitle>
                    </CModalHeader>
                    <CModalBody>
                      <CFormLabel>Name</CFormLabel>
                      <CFormInput v-model="editMember.name" />
                      <CFormLabel class="mt-2">Phone</CFormLabel>
                      <CFormInput v-model="editMember.phone" />
                      <CFormLabel class="mt-2">Category</CFormLabel>
                      <select v-model="editMember.status" class="form-select mb-2">
                        <option value="visitor">Visitor</option>
                        <option value="partner">Partner</option>
                        <option value="active">Active</option>
                      </select>
                      <CFormLabel>Date</CFormLabel>
                      <CFormInput type="date" v-model="editMember.date" />
                    </CModalBody>
                    <CModalFooter>
                      <CButton color="secondary" @click="editModalVisible = false">Cancel</CButton>
                      <CButton color="primary" @click="saveEditMember">Save</CButton>
                    </CModalFooter>
                  </CModal>
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol xl="4">
          <CCard class="mb-4">
            <CCardHeader class="fw-semibold">Quick Actions</CCardHeader>
            <CCardBody>
              <div class="d-grid gap-2">
                <CButton color="primary" variant="outline" class="btn-glow" @click="$router.push('/users')">Add Member
                </CButton>
                <CButton color="success" variant="outline" class="btn-glow" @click="$router.push('/contributions')">
                  Record Offering</CButton>
                <CButton color="warning" variant="outline" class="btn-glow" @click="$router.push('/attendance')">
                  Schedule Event</CButton>
                <CButton color="info" variant="outline" class="btn-glow" @click="$router.push('/broadcasts')">Send
                  Message</CButton>
              </div>
            </CCardBody>
          </CCard>
          <CCard class="mb-4">
            <CCardHeader class="fw-semibold">Tasks</CCardHeader>
            <CCardBody>
              <CListGroup>
                <CListGroupItem v-for="task in tasks" :key="task.label"
                  class="d-flex justify-content-between align-items-center cursor-pointer"
                  @click="task.link && $router.push(task.link)" :style="task.link ? 'cursor: pointer;' : ''">
                  {{ task.label }}<CBadge :color="task.color">{{ task.due }}</CBadge>
                </CListGroupItem>
                <CListGroupItem v-if="tasks.length === 0" class="text-muted text-center">No tasks</CListGroupItem>
              </CListGroup>
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="fw-semibold">Activity</CCardHeader>
            <CCardBody>
              <CListGroup>
                <CListGroupItem v-for="activity in recentActivities" :key="activity.action">{{ activity.action }} • {{
                  activity.time }}</CListGroupItem>
                <CListGroupItem v-if="recentActivities.length === 0" class="text-muted text-center">No recent activity
                </CListGroupItem>
              </CListGroup>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </template>
  </div>

</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CListGroup, CListGroupItem, CSpinner, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter, CFormLabel, CFormInput } from '@coreui/vue'
import { CChartLine, CChartBar } from '@coreui/vue-chartjs'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import StatCard from '../components/StatCard.vue'
import { useThemeStore } from '../store/theme'
import { dashboardApi } from '../api'

const range = ref('week')
const loading = ref(true)
const stats = ref({})
const analytics = ref({})
const recentMembers = ref([])
const recentActivities = ref([])
const tasks = ref([])

// Custom date range
const showCustomRange = ref(false)
const customStartDate = ref('')
const customEndDate = ref('')
const customDateLabel = ref('')

const today = computed(() => new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))

// Format date for display
function formatDateLabel(start, end) {
  const startDate = new Date(start)
  const endDate = new Date(end)
  const options = { month: 'short', day: 'numeric' }
  return `${startDate.toLocaleDateString(undefined, options)} - ${endDate.toLocaleDateString(undefined, options)}`
}

// Quick range selections
function setQuickRange(type) {
  const today = new Date()
  let start, end

  switch (type) {
    case 'last7':
      end = new Date(today)
      start = new Date(today)
      start.setDate(end.getDate() - 6)
      break
    case 'last30':
      end = new Date(today)
      start = new Date(today)
      start.setDate(end.getDate() - 29)
      break
    case 'last90':
      end = new Date(today)
      start = new Date(today)
      start.setDate(end.getDate() - 89)
      break
    case 'thisMonth':
      end = new Date(today)
      start = new Date(today.getFullYear(), today.getMonth(), 1)
      break
    case 'lastMonth':
      start = new Date(today.getFullYear(), today.getMonth() - 1, 1)
      end = new Date(today.getFullYear(), today.getMonth(), 0)
      break
    case 'thisYear':
      end = new Date(today)
      start = new Date(today.getFullYear(), 0, 1)
      break
  }

  // Always ensure start <= end
  if (start > end) {
    const temp = start
    start = end
    end = temp
  }

  customStartDate.value = start.toISOString().split('T')[0]
  customEndDate.value = end.toISOString().split('T')[0]
}

// Apply custom date range
function applyCustomRange() {
  if (!customStartDate.value || !customEndDate.value) {
    alert('Please select both start and end dates')
    return
  }

  if (new Date(customStartDate.value) > new Date(customEndDate.value)) {
    alert('Start date cannot be after end date')
    return
  }

  customDateLabel.value = formatDateLabel(customStartDate.value, customEndDate.value)
  range.value = 'custom'
  showCustomRange.value = false
  fetchDashboardData()
}

// Fetch dashboard data
async function fetchDashboardData() {
  loading.value = true
  try {
    // Build params based on range
    const params = { range: range.value }
    if (range.value === 'custom' && customStartDate.value && customEndDate.value) {
      params.start_date = customStartDate.value
      params.end_date = customEndDate.value
    }

    const [statsRes, analyticsRes] = await Promise.all([
      dashboardApi.getStats(params),
      dashboardApi.getAnalytics(params)
    ])

    console.log('Dashboard API response:', statsRes.data)

    const data = statsRes.data?.data || {}
    const analyticsData = analyticsRes.data?.data || {}

    console.log('Overview data:', data.overview)

    // Map API data to stats format
    const overview = data.overview || []
    stats.value = {
      members: mapOverviewItem(overview, 'Total Members', 'members'),
      visitors: mapOverviewItem(overview, 'New Visitors', 'visitors'),
      attendance: mapOverviewItem(overview, 'Attendance', 'attendance', true),
      finance: mapOverviewItem(overview, 'Contributions', 'finance', true),
      conversion: mapOverviewItem(overview, 'Conversion Rate', 'conversion') || { value: '38%', sub: 'This week', progress: 38 },
      followUps: mapOverviewItem(overview, 'Pending Follow-ups', 'followups') || { value: 5, sub: 'Pending', progress: 60 },
      events: mapOverviewItem(overview, 'Scheduled Events', 'events') || { value: 5, sub: 'Scheduled', progress: 40 }
    }

    // Map analytics for charts
    analytics.value = analyticsData

    // Map recent activities
    recentActivities.value = data.recent_activities || []

    // Map recent members
    recentMembers.value = data.recent_members || []

    // Map quick actions as tasks
    const quickActions = data.quick_actions || []
    tasks.value = quickActions.map(action => ({
      label: action.label,
      due: action.count?.toString() || '',
      color: action.count > 5 ? 'warning' : 'primary',
      link: action.link || null
    }))

  } catch (error) {
    console.error('Failed to fetch dashboard data:', error)
    // If API fails, show friendly error
    if (error.response?.status === 401) {
      console.error('Not authenticated - redirecting to login')
    }
  } finally {
    loading.value = false
  }
}

function mapOverviewItem(overview, label, type, partialMatch = false) {
  let item
  if (partialMatch) {
    item = overview.find(o => o.label.includes(label))
  } else {
    item = overview.find(o => o.label === label)
  }
  if (!item) return null

  let progress = 50
  let sub = `${item.change} ${item.trend === 'up' ? '↑' : item.trend === 'down' ? '↓' : ''}`

  if (type === 'attendance') {
    const val = parseInt(item.value) || 0
    progress = Math.min(100, (val / 100) * 100) // Normalize for progress bar
    return { value: val, sub: item.change, progress: Math.min(100, val) }
  }

  if (type === 'conversion') {
    const val = parseInt(item.value) || 0
    return { value: item.value, sub: item.change, progress: val }
  }

  if (type === 'followups') {
    return { value: item.value, sub: 'Pending', progress: 60 }
  }

  if (type === 'events') {
    return { value: item.value, sub: 'Scheduled', progress: 40 }
  }

  // Calculate progress based on change percentage
  progress = Math.min(100, Math.abs(parseInt(item.change) || 0) * 5 + 50)

  return { value: item.value, sub, progress }
}

function setRange(newRange) {
  if (newRange !== 'custom') {
    customDateLabel.value = ''
    customStartDate.value = ''
    customEndDate.value = ''
  }
  range.value = newRange
}

// Watch for range changes and refetch
watch(range, (newVal, oldVal) => {
  // Don't refetch if switching to custom (will be handled by applyCustomRange)
  if (newVal !== 'custom') {
    fetchDashboardData()
  }
})

function formatNumber(num) {
  if (typeof num === 'string') return num
  return Intl.NumberFormat().format(num)
}

const theme = useThemeStore()
const legendColor = computed(() => theme.mode === 'dark' ? '#e5e7eb' : '#334155')
const gridColor = computed(() => theme.mode === 'dark' ? 'rgba(255,255,255,.08)' : '#eef2f7')
const tickColor = computed(() => theme.mode === 'dark' ? '#cbd5e1' : '#475569')

const attendanceData = computed(() => ({
  labels: analytics.value.attendance_trend?.labels || ['W1', 'W2', 'W3', 'W4'],
  datasets: [
    {
      label: 'Attendance %',
      backgroundColor: 'rgba(13,110,253,.2)',
      borderColor: '#0d6efd',
      pointBackgroundColor: '#0d6efd',
      data: analytics.value.attendance_trend?.data || [],
      tension: .35,
      fill: true
    }
  ]
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { labels: { color: legendColor.value } } },
  scales: {
    x: { ticks: { color: tickColor.value }, grid: { color: gridColor.value } },
    y: { beginAtZero: true, max: 100, ticks: { color: tickColor.value }, grid: { color: gridColor.value } }
  }
}))

const financeData = computed(() => ({
  labels: analytics.value.finance_trend?.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Contributions (GHS)',
      backgroundColor: 'rgba(25,135,84,.6)',
      borderColor: '#198754',
      data: analytics.value.finance_trend?.contributions || []
    }
  ]
}))

const financeOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: legendColor.value } },
    tooltip: {
      callbacks: {
        label: ctx => 'GHS ' + Intl.NumberFormat().format(ctx.parsed.y)
      }
    }
  },
  scales: {
    x: { ticks: { color: tickColor.value }, grid: { color: gridColor.value } },
    y: {
      ticks: {
        color: tickColor.value,
        callback: v => 'GHS ' + Intl.NumberFormat().format(v)
      },
      grid: { color: gridColor.value }
    }
  }
}))

const chartTabs = [
  { key: 'tithes', label: 'Tithes' },
  { key: 'offering', label: 'Offering' },
  { key: 'special_seed', label: 'Special Seed' }
]
const activeChartTab = ref('tithes')
const chartData = {
  tithes: {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [{
      label: 'Tithes',
      backgroundColor: '#6366f1',
      data: [120, 150, 100, 180]
    }]
  },
  offering: {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [{
      label: 'Offering',
      backgroundColor: '#10b981',
      data: [80, 90, 110, 95]
    }]
  },
  special_seed: {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [{
      label: 'Special Seed',
      backgroundColor: '#f59e42',
      data: [30, 40, 25, 50]
    }]
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
.dash-wrap {
  padding: 1.5rem;
  animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(12px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dash-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.03) 100%);
  border-radius: 16px;
  border: 1px solid rgba(99, 102, 241, 0.1);
}

.theme-dark .dash-header {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
  border-color: rgba(99, 102, 241, 0.15);
}

.title {
  margin: 0;
  font-weight: 700;
  font-size: 1.75rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.date {
  font-size: 0.9rem;
  color: #64748b;
}

.filter-group .btn {
  border-radius: 8px !important;
  font-size: 0.85rem;
  padding: 0.4rem 0.75rem;
  font-weight: 500;
}

:deep(.card) {
  border: none !important;
  border-radius: 16px !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.04) !important;
  transition: all 0.3s ease;
}

:deep(.card:hover) {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06), 0 12px 24px rgba(0, 0, 0, 0.06) !important;
  transform: translateY(-2px);
}

:deep(.card-header) {
  background: transparent !important;
  border-bottom: 1px solid #e2e8f0 !important;
  padding: 1rem 1.25rem !important;
  font-weight: 600;
  font-size: 0.95rem;
}

.theme-dark :deep(.card-header) {
  border-color: #334155 !important;
}

:deep(.card-body) {
  padding: 1.25rem !important;
}

/* Quick Actions */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
}

.quick-actions .btn {
  padding: 1rem;
  font-weight: 500;
  border-radius: 12px !important;
  transition: all 0.25s ease;
}

.quick-actions .btn:hover {
  transform: translateY(-3px);
}

/* Tasks list */
.task-item {
  padding: 0.875rem 0;
  border-bottom: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-dark .task-item {
  border-color: #334155;
}

.task-item:hover {
  background: rgba(99, 102, 241, 0.04);
  padding-left: 0.5rem;
}

.task-item:last-child {
  border-bottom: none;
}

/* Table styles */
:deep(.table) {
  margin: 0;
}

:deep(.table thead th) {
  background: #f8fafc !important;
  border: none !important;
  padding: 0.875rem 1rem !important;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  font-weight: 600;
}

.theme-dark :deep(.table thead th) {
  background: #0f172a !important;
  color: #94a3b8;
}

:deep(.table tbody td) {
  padding: 0.875rem 1rem !important;
  border-bottom: 1px solid #e2e8f0 !important;
  vertical-align: middle;
}

.theme-dark :deep(.table tbody td) {
  border-color: #334155 !important;
}

:deep(.table tbody tr:hover) {
  background: rgba(99, 102, 241, 0.03) !important;
}

/* Responsive */
@media (max-width: 768px) {
  .dash-wrap {
    padding: 1rem;
  }

  .dash-header {
    padding: 1rem;
  }

  .title {
    font-size: 1.5rem;
  }
}
</style>
