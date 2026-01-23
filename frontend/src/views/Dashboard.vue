<template>
  <div class="dash-wrap">
    <div class="dash-header">
      <div>
        <h2 class="title">Dashboard</h2>
        <Breadcrumbs />
        <div class="date text-muted">{{ today }}</div>
      </div>
      <div class="d-flex align-items-center">
        <div class="btn-group me-3 filter-group">
          <CButton :color="range === 'today' ? 'primary' : 'light'" size="sm" @click="range = 'today'">Today</CButton>
          <CButton :color="range === 'week' ? 'primary' : 'light'" size="sm" @click="range = 'week'">Week</CButton>
          <CButton :color="range === 'month' ? 'primary' : 'light'" size="sm" @click="range = 'month'">Month</CButton>
        </div>
        <CButton color="primary">New Member</CButton>
        <CButton color="success" class="ms-2">Record Attendance</CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol sm="6" xl="3">
        <StatCard title="Members" value="1,248" :sub="subText('members')" icon="bi bi-people" :progress="72"
          color="primary" />
      </CCol>
      <CCol sm="6" xl="3">
        <StatCard title="Visitors" value="86" :sub="subText('visitors')" icon="bi bi-person-plus" :progress="54"
          color="info" />
      </CCol>
      <CCol sm="6" xl="3">
        <StatCard title="Attendance" value="78%" :sub="subText('attendance')" icon="bi bi-bullseye" :progress="78"
          color="warning" />
      </CCol>
      <CCol sm="6" xl="3">
        <StatCard title="Finance" value="GHS 12.4k" :sub="subText('finance')" icon="bi bi-graph-up-arrow" :progress="64"
          color="success" />
      </CCol>
    </CRow>

    <CRow class="g-4 mt-1">
      <CCol sm="6" xl="4">
        <StatCard title="Conversion" value="3.2%" sub="This week" icon="bi bi-bar-chart" :progress="32"
          color="primary" />
      </CCol>
      <CCol sm="6" xl="4">
        <StatCard title="Follow-ups" value="18" sub="Pending" icon="bi bi-clipboard-check" :progress="60"
          color="warning" />
      </CCol>
      <CCol sm="6" xl="4">
        <StatCard title="Events" value="5" sub="Scheduled" icon="bi bi-calendar-event" :progress="40" color="success" />
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
                <CTableRow>
                  <CTableDataCell>Kwame Mensah</CTableDataCell>
                  <CTableDataCell>
                    <CBadge color="success">Active</CBadge>
                  </CTableDataCell>
                  <CTableDataCell class="text-end">2d ago</CTableDataCell>
                </CTableRow>
                <CTableRow>
                  <CTableDataCell>Akosua Boateng</CTableDataCell>
                  <CTableDataCell>
                    <CBadge color="info">Visitor</CBadge>
                  </CTableDataCell>
                  <CTableDataCell class="text-end">3d ago</CTableDataCell>
                </CTableRow>
                <CTableRow>
                  <CTableDataCell>Joseph Owusu</CTableDataCell>
                  <CTableDataCell>
                    <CBadge color="warning">Follow-up</CBadge>
                  </CTableDataCell>
                  <CTableDataCell class="text-end">5d ago</CTableDataCell>
                </CTableRow>
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
              <CButton color="primary" variant="outline" class="btn-glow">Add Member</CButton>
              <CButton color="success" variant="outline" class="btn-glow">Record Offering</CButton>
              <CButton color="warning" variant="outline" class="btn-glow">Schedule Event</CButton>
              <CButton color="info" variant="outline" class="btn-glow">Send Message</CButton>
            </div>
          </CCardBody>
        </CCard>
        <CCard class="mb-4">
          <CCardHeader class="fw-semibold">Tasks</CCardHeader>
          <CCardBody>
            <CListGroup>
              <CListGroupItem class="d-flex justify-content-between align-items-center">
                Call new visitors<CBadge color="primary">Today</CBadge>
              </CListGroupItem>
              <CListGroupItem class="d-flex justify-content-between align-items-center">
                Prepare finance report<CBadge color="warning">Due Fri</CBadge>
              </CListGroupItem>
              <CListGroupItem class="d-flex justify-content-between align-items-center">
                Choir rehearsal plan<CBadge color="success">Done</CBadge>
              </CListGroupItem>
            </CListGroup>
          </CCardBody>
        </CCard>
        <CCard>
          <CCardHeader class="fw-semibold">Activity</CCardHeader>
          <CCardBody>
            <CListGroup>
              <CListGroupItem>Visitor registered • 11:15 AM</CListGroupItem>
              <CListGroupItem>Offering recorded • 10:02 AM</CListGroupItem>
              <CListGroupItem>Member updated • 9:30 AM</CListGroupItem>
            </CListGroup>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  </div>

</template>

<script setup>
import { ref, computed } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CListGroup, CListGroupItem } from '@coreui/vue'
import { CChartLine, CChartBar } from '@coreui/vue-chartjs'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import StatCard from '../components/StatCard.vue'
import { useThemeStore } from '../store/theme'

const range = ref('week')
const today = computed(() => new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))
const attendanceData = {
  labels: ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8'],
  datasets: [
    {
      label: 'Attendance %',
      backgroundColor: 'rgba(13,110,253,.2)',
      borderColor: '#0d6efd',
      pointBackgroundColor: '#0d6efd',
      data: [70, 74, 72, 79, 81, 78, 80, 84],
      tension: .35,
      fill: true
    }
  ]
}
const theme = useThemeStore()
const legendColor = computed(() => theme.mode === 'dark' ? '#e5e7eb' : '#334155')
const gridColor = computed(() => theme.mode === 'dark' ? 'rgba(255,255,255,.08)' : '#eef2f7')
const tickColor = computed(() => theme.mode === 'dark' ? '#cbd5e1' : '#475569')
const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: legendColor.value } } }, scales: { x: { ticks: { color: tickColor.value }, grid: { color: gridColor.value } }, y: { beginAtZero: true, max: 100, ticks: { color: tickColor.value }, grid: { color: gridColor.value } } } }
const financeData = {
  labels: ['W1', 'W2', 'W3', 'W4', 'W5', 'W6'],
  datasets: [
    {
      label: 'Offerings (GHS)',
      backgroundColor: 'rgba(25,135,84,.6)',
      borderColor: '#198754',
      data: [12400, 13050, 11800, 14200, 13550, 14880]
    }
  ]
}
const financeOptions = {
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
}
function subText(section) {
  if (range.value === 'today') {
    if (section === 'members') return '+3 today'
    if (section === 'visitors') return '+4 today'
    if (section === 'attendance') return 'Service 78%'
    if (section === 'finance') return 'GHS 1,120 today'
  }
  if (range.value === 'month') {
    if (section === 'members') return '+96 this month'
    if (section === 'visitors') return '+240 this month'
    if (section === 'attendance') return 'Avg 82%'
    if (section === 'finance') return '+12% MoM'
  }
  if (section === 'members') return '+24 this week'
  if (section === 'visitors') return '+12 Sun Service'
  if (section === 'attendance') return 'Target 85%'
  if (section === 'finance') return '+8% WoW'
  return ''
}
</script>

<style scoped>
.dash-wrap {
  padding: 20px;
}

.dash-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.dash-header {
  background: linear-gradient(90deg, rgba(255, 255, 255, .06), transparent);
  border-radius: 12px;
  padding: 12px;
}

.title {
  margin: 0;
  font-weight: 700;
}

.date {
  font-size: .9rem;
}

.stat-card {
  position: relative;
  overflow: hidden;
}

.stat-card .stat-icon {
  position: absolute;
  right: 12px;
  top: 12px;
  font-size: 1.4rem;
  opacity: .35;
}

.stat-card .stat-title {
  font-size: .85rem;
  color: #9aa0a6;
}

.stat-card .stat-value {
  font-size: 1.6rem;
  font-weight: 700;
}

.stat-card .stat-sub {
  font-size: .8rem;
  color: #aeb6c1;
}

.hoverable {
  transition: transform .15s ease, box-shadow .15s ease;
}

.hoverable:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
}
</style>
