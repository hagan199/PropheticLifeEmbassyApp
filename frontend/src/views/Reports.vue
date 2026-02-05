<template>
  <div class="page-wrap">
    <Breadcrumbs :items="[{ text: 'Reports', active: true }]" />

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div>
        <h1 class="title">Reports & Analytics</h1>
        <p class="subtitle">Generate insights from your church data</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn-export" @click="exportCurrentReport">
          <i class="bi bi-file-earmark-excel"></i>
          Export Excel
        </button>
        <button class="btn-refresh" @click="refreshData">
          <i class="bi bi-arrow-clockwise" :class="{ 'spinning': loading }"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Date Range & Filters -->
    <div class="filter-card mb-4">
      <div class="filter-row">
        <div class="filter-group">
          <label>Date Range</label>
          <div class="date-range-picker">
            <div class="date-input-wrap">
              <i class="bi bi-calendar3"></i>
              <input type="date" v-model="filters.startDate" />
            </div>
            <span class="date-separator">to</span>
            <div class="date-input-wrap">
              <i class="bi bi-calendar3"></i>
              <input type="date" v-model="filters.endDate" />
            </div>
          </div>
        </div>
        <div class="filter-group">
          <label>Quick Select</label>
          <div class="quick-dates">
            <button
              v-for="preset in datePresets"
              :key="preset.label"
              class="preset-btn"
              :class="{ active: activePreset === preset.label }"
              @click="applyPreset(preset)"
            >
              {{ preset.label }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Tabs -->
    <div class="report-tabs mb-4">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="tab-btn"
        :class="{ active: activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        <i :class="tab.icon"></i>
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Generating report...</span>
    </div>

    <!-- Visitor Reports -->
    <div v-else-if="activeTab === 'visitors'" class="report-section">
      <!-- Summary Cards -->
      <div class="summary-grid mb-4">
        <div class="summary-card visitors">
          <div class="card-icon">
            <i class="bi bi-person-plus-fill"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ visitorStats.total }}</div>
            <div class="card-label">Total Visitors</div>
            <div class="card-trend" :class="visitorStats.trend >= 0 ? 'up' : 'down'">
              <i :class="visitorStats.trend >= 0 ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
              {{ Math.abs(visitorStats.trend) }}% vs last period
            </div>
          </div>
        </div>
        <div class="summary-card first-timers">
          <div class="card-icon">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ visitorStats.firstTimers }}</div>
            <div class="card-label">First-Time Visitors</div>
            <div class="card-sub">{{ visitorStats.firstTimerPercent }}% of total</div>
          </div>
        </div>
        <div class="summary-card returning">
          <div class="card-icon">
            <i class="bi bi-arrow-repeat"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ visitorStats.returning }}</div>
            <div class="card-label">Returning Visitors</div>
            <div class="card-sub">{{ visitorStats.returningPercent }}% retention</div>
          </div>
        </div>
        <div class="summary-card converted">
          <div class="card-icon">
            <i class="bi bi-person-check-fill"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ visitorStats.converted }}</div>
            <div class="card-label">Converted to Members</div>
            <div class="card-sub">{{ visitorStats.conversionRate }}% conversion</div>
          </div>
        </div>
      </div>

      <!-- Weekly Breakdown Chart -->
      <div class="chart-card mb-4">
        <div class="chart-header">
          <h3><i class="bi bi-bar-chart-fill"></i> Weekly Visitor Breakdown</h3>
          <div class="chart-legend">
            <span class="legend-item first-time"><span class="dot"></span> First-Time</span>
            <span class="legend-item returning"><span class="dot"></span> Returning</span>
          </div>
        </div>
        <div class="chart-body">
          <div class="bar-chart">
            <div
              v-for="(week, idx) in weeklyVisitors"
              :key="idx"
              class="bar-group"
            >
              <div class="bars">
                <div
                  class="bar first-time"
                  :style="{ height: getBarHeight(week.firstTime) + '%' }"
                  :title="`First-Time: ${week.firstTime}`"
                >
                  <span class="bar-value">{{ week.firstTime }}</span>
                </div>
                <div
                  class="bar returning"
                  :style="{ height: getBarHeight(week.returning) + '%' }"
                  :title="`Returning: ${week.returning}`"
                >
                  <span class="bar-value">{{ week.returning }}</span>
                </div>
              </div>
              <div class="bar-label">{{ week.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Visitor Table -->
      <div class="data-card">
        <div class="card-header">
          <h3><i class="bi bi-table"></i> Visitor Details</h3>
          <div class="header-actions">
            <input
              type="text"
              class="search-input"
              placeholder="Search visitors..."
              v-model="visitorSearch"
            />
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Visit Date</th>
                <th>Type</th>
                <th>Source</th>
                <th>Follow-up Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="visitor in filteredVisitors" :key="visitor.id">
                <td>
                  <div class="cell-with-avatar">
                    <div class="avatar">{{ getInitials(visitor.name) }}</div>
                    <div>
                      <div class="primary-text">{{ visitor.name }}</div>
                      <div class="secondary-text">{{ visitor.phone }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ formatDate(visitor.visitDate) }}</td>
                <td>
                  <span class="badge" :class="visitor.type === 'first_time' ? 'badge-primary' : 'badge-secondary'">
                    {{ visitor.type === 'first_time' ? 'First Time' : 'Returning' }}
                  </span>
                </td>
                <td>{{ visitor.source || 'Walk-in' }}</td>
                <td>
                  <span class="status-badge" :class="getStatusClass(visitor.followUpStatus)">
                    {{ visitor.followUpStatus || 'Pending' }}
                  </span>
                </td>
                <td>
                  <button class="action-btn" title="View Details">
                    <i class="bi bi-eye"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredVisitors.length === 0">
                <td colspan="6" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <span>No visitors found</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Partner/Contribution Reports -->
    <div v-else-if="activeTab === 'partners'" class="report-section">
      <!-- Summary Cards -->
      <div class="summary-grid mb-4">
        <div class="summary-card contributions">
          <div class="card-icon">
            <i class="bi bi-cash-coin"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ formatCurrency(partnerStats.totalContributions) }}</div>
            <div class="card-label">Total Contributions</div>
            <div class="card-trend" :class="partnerStats.trend >= 0 ? 'up' : 'down'">
              <i :class="partnerStats.trend >= 0 ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
              {{ Math.abs(partnerStats.trend) }}% vs last period
            </div>
          </div>
        </div>
        <div class="summary-card partners">
          <div class="card-icon">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ partnerStats.totalPartners }}</div>
            <div class="card-label">Active Partners</div>
            <div class="card-sub">{{ partnerStats.newPartners }} new this period</div>
          </div>
        </div>
        <div class="summary-card average">
          <div class="card-icon">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ formatCurrency(partnerStats.averageContribution) }}</div>
            <div class="card-label">Average Contribution</div>
            <div class="card-sub">Per transaction</div>
          </div>
        </div>
        <div class="summary-card tithes">
          <div class="card-icon">
            <i class="bi bi-percent"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ formatCurrency(partnerStats.tithes) }}</div>
            <div class="card-label">Tithes</div>
            <div class="card-sub">{{ partnerStats.tithePercent }}% of total</div>
          </div>
        </div>
      </div>

      <!-- Contribution Breakdown -->
      <div class="row g-4 mb-4">
        <div class="col-lg-6">
          <div class="chart-card h-100">
            <div class="chart-header">
              <h3><i class="bi bi-pie-chart-fill"></i> Contribution Types</h3>
            </div>
            <div class="chart-body">
              <div class="donut-chart-wrap">
                <div class="donut-chart">
                  <svg viewBox="0 0 36 36">
                    <circle
                      v-for="(segment, idx) in contributionTypes"
                      :key="idx"
                      cx="18"
                      cy="18"
                      r="15.915"
                      fill="transparent"
                      :stroke="segment.color"
                      stroke-width="3"
                      :stroke-dasharray="`${segment.percent} ${100 - segment.percent}`"
                      :stroke-dashoffset="getStrokeOffset(idx)"
                    />
                  </svg>
                  <div class="donut-center">
                    <div class="center-value">{{ contributionTypes.length }}</div>
                    <div class="center-label">Types</div>
                  </div>
                </div>
                <div class="donut-legend">
                  <div v-for="type in contributionTypes" :key="type.name" class="legend-row">
                    <span class="legend-color" :style="{ background: type.color }"></span>
                    <span class="legend-name">{{ type.name }}</span>
                    <span class="legend-value">{{ formatCurrency(type.amount) }}</span>
                    <span class="legend-percent">{{ type.percent }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="chart-card h-100">
            <div class="chart-header">
              <h3><i class="bi bi-graph-up"></i> Weekly Trend</h3>
            </div>
            <div class="chart-body">
              <div class="line-chart">
                <div class="chart-grid">
                  <div v-for="i in 5" :key="i" class="grid-line"></div>
                </div>
                <svg class="line-svg" viewBox="0 0 400 200" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                      <stop offset="0%" style="stop-color:#6366f1;stop-opacity:0.3" />
                      <stop offset="100%" style="stop-color:#6366f1;stop-opacity:0" />
                    </linearGradient>
                  </defs>
                  <path
                    :d="areaPath"
                    fill="url(#lineGradient)"
                  />
                  <path
                    :d="linePath"
                    fill="none"
                    stroke="#6366f1"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <circle
                    v-for="(point, idx) in weeklyContributions"
                    :key="idx"
                    :cx="getPointX(idx)"
                    :cy="getPointY(point.amount)"
                    r="4"
                    fill="#6366f1"
                    stroke="#fff"
                    stroke-width="2"
                  />
                </svg>
                <div class="chart-labels">
                  <span v-for="week in weeklyContributions" :key="week.label">{{ week.label }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Partners Table -->
      <div class="data-card">
        <div class="card-header">
          <h3><i class="bi bi-trophy-fill"></i> Top Contributors</h3>
          <div class="header-actions">
            <select v-model="partnerFilter" class="filter-select">
              <option value="all">All Types</option>
              <option value="tithe">Tithes</option>
              <option value="offering">Offerings</option>
              <option value="seed">Seeds</option>
            </select>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Rank</th>
                <th>Partner</th>
                <th>Total Contributions</th>
                <th>Transactions</th>
                <th>Last Contribution</th>
                <th>Trend</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(partner, idx) in topPartners" :key="partner.id">
                <td>
                  <div class="rank-badge" :class="getRankClass(idx)">
                    {{ idx + 1 }}
                  </div>
                </td>
                <td>
                  <div class="cell-with-avatar">
                    <div class="avatar partner">{{ getInitials(partner.name) }}</div>
                    <div>
                      <div class="primary-text">{{ partner.name }}</div>
                      <div class="secondary-text">Member since {{ partner.memberSince }}</div>
                    </div>
                  </div>
                </td>
                <td class="amount-cell">{{ formatCurrency(partner.totalAmount) }}</td>
                <td>{{ partner.transactions }}</td>
                <td>{{ formatDate(partner.lastContribution) }}</td>
                <td>
                  <div class="trend-indicator" :class="partner.trend >= 0 ? 'up' : 'down'">
                    <i :class="partner.trend >= 0 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                    {{ Math.abs(partner.trend) }}%
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Attendance Reports -->
    <div v-else-if="activeTab === 'attendance'" class="report-section">
      <!-- Summary Cards -->
      <div class="summary-grid mb-4">
        <div class="summary-card attendance">
          <div class="card-icon">
            <i class="bi bi-calendar-check-fill"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ attendanceStats.avgAttendance }}</div>
            <div class="card-label">Average Attendance</div>
            <div class="card-trend" :class="attendanceStats.trend >= 0 ? 'up' : 'down'">
              <i :class="attendanceStats.trend >= 0 ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
              {{ Math.abs(attendanceStats.trend) }}% vs last period
            </div>
          </div>
        </div>
        <div class="summary-card peak">
          <div class="card-icon">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ attendanceStats.peakAttendance }}</div>
            <div class="card-label">Peak Attendance</div>
            <div class="card-sub">{{ attendanceStats.peakDate }}</div>
          </div>
        </div>
        <div class="summary-card services">
          <div class="card-icon">
            <i class="bi bi-collection-fill"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ attendanceStats.totalServices }}</div>
            <div class="card-label">Total Services</div>
            <div class="card-sub">In selected period</div>
          </div>
        </div>
        <div class="summary-card growth">
          <div class="card-icon">
            <i class="bi bi-trending-up"></i>
          </div>
          <div class="card-content">
            <div class="card-value">{{ attendanceStats.growthRate }}%</div>
            <div class="card-label">Growth Rate</div>
            <div class="card-sub">Month over month</div>
          </div>
        </div>
      </div>

      <!-- Attendance by Service Type -->
      <div class="chart-card mb-4">
        <div class="chart-header">
          <h3><i class="bi bi-bar-chart-line-fill"></i> Attendance by Service Type</h3>
        </div>
        <div class="chart-body">
          <div class="horizontal-bars">
            <div v-for="service in serviceAttendance" :key="service.name" class="h-bar-row">
              <div class="h-bar-label">{{ service.name }}</div>
              <div class="h-bar-track">
                <div
                  class="h-bar-fill"
                  :style="{ width: service.percent + '%', background: service.color }"
                >
                  <span class="h-bar-value">{{ service.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Attendance Table -->
      <div class="data-card">
        <div class="card-header">
          <h3><i class="bi bi-calendar-week"></i> Weekly Breakdown</h3>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Week</th>
                <th>Sunday Service</th>
                <th>Midweek</th>
                <th>Special Events</th>
                <th>Total</th>
                <th>Change</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="week in weeklyAttendance" :key="week.week">
                <td class="week-cell">{{ week.week }}</td>
                <td>{{ week.sunday }}</td>
                <td>{{ week.midweek }}</td>
                <td>{{ week.special }}</td>
                <td class="total-cell">{{ week.total }}</td>
                <td>
                  <div class="trend-indicator" :class="week.change >= 0 ? 'up' : 'down'">
                    <i :class="week.change >= 0 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                    {{ Math.abs(week.change) }}%
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">
        <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'"></i>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { visitorApi, financeApi, attendanceApi } from '../api'

// State
const loading = ref(false)
const activeTab = ref('visitors')
const activePreset = ref('This Month')
const visitorSearch = ref('')
const partnerFilter = ref('all')

const toast = ref({ show: false, message: '', type: 'success' })

// Date filters
const today = new Date()
const filters = ref({
  startDate: new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0],
  endDate: today.toISOString().split('T')[0]
})

// Tabs
const tabs = [
  { id: 'visitors', label: 'Visitors', icon: 'bi bi-person-plus' },
  { id: 'partners', label: 'Contributions', icon: 'bi bi-cash-coin' },
  { id: 'attendance', label: 'Attendance', icon: 'bi bi-calendar-check' }
]

// Date presets
const datePresets = [
  { label: 'This Week', days: 7, getRange: () => {
    const end = new Date()
    const start = new Date()
    start.setDate(end.getDate() - end.getDay())
    return { start, end }
  }},
  { label: 'This Month', days: 30, getRange: () => {
    const end = new Date()
    const start = new Date(end.getFullYear(), end.getMonth(), 1)
    return { start, end }
  }},
  { label: 'Last 3 Months', days: 90, getRange: () => {
    const end = new Date()
    const start = new Date()
    start.setMonth(end.getMonth() - 3)
    return { start, end }
  }},
  { label: 'This Year', days: 365, getRange: () => {
    const end = new Date()
    const start = new Date(end.getFullYear(), 0, 1)
    return { start, end }
  }}
]

// Mock Data - Replace with API calls
const visitorStats = ref({
  total: 156,
  firstTimers: 89,
  returning: 67,
  converted: 23,
  trend: 12,
  firstTimerPercent: 57,
  returningPercent: 43,
  conversionRate: 15
})

const weeklyVisitors = ref([
  { label: 'Week 1', firstTime: 22, returning: 15 },
  { label: 'Week 2', firstTime: 18, returning: 20 },
  { label: 'Week 3', firstTime: 25, returning: 18 },
  { label: 'Week 4', firstTime: 24, returning: 14 }
])

const visitors = ref([
  { id: 1, name: 'John Smith', phone: '+233 24 123 4567', visitDate: '2024-01-15', type: 'first_time', source: 'Social Media', followUpStatus: 'Completed' },
  { id: 2, name: 'Sarah Johnson', phone: '+233 20 987 6543', visitDate: '2024-01-14', type: 'returning', source: 'Invitation', followUpStatus: 'In Progress' },
  { id: 3, name: 'Michael Brown', phone: '+233 27 456 7890', visitDate: '2024-01-13', type: 'first_time', source: 'Walk-in', followUpStatus: 'Pending' },
  { id: 4, name: 'Emily Davis', phone: '+233 24 321 0987', visitDate: '2024-01-12', type: 'first_time', source: 'Website', followUpStatus: 'Completed' },
  { id: 5, name: 'David Wilson', phone: '+233 20 654 3210', visitDate: '2024-01-11', type: 'returning', source: 'Invitation', followUpStatus: 'In Progress' }
])

const partnerStats = ref({
  totalContributions: 125000,
  totalPartners: 234,
  newPartners: 18,
  averageContribution: 534.19,
  tithes: 75000,
  tithePercent: 60,
  trend: 8
})

const contributionTypes = ref([
  { name: 'Tithes', amount: 75000, percent: 60, color: '#6366f1' },
  { name: 'Offerings', amount: 30000, percent: 24, color: '#10b981' },
  { name: 'Seeds', amount: 15000, percent: 12, color: '#f59e0b' },
  { name: 'Projects', amount: 5000, percent: 4, color: '#ef4444' }
])

const weeklyContributions = ref([
  { label: 'W1', amount: 28000 },
  { label: 'W2', amount: 32000 },
  { label: 'W3', amount: 29000 },
  { label: 'W4', amount: 36000 }
])

const topPartners = ref([
  { id: 1, name: 'Grace Mensah', memberSince: '2020', totalAmount: 15000, transactions: 24, lastContribution: '2024-01-15', trend: 15 },
  { id: 2, name: 'Kofi Asante', memberSince: '2019', totalAmount: 12500, transactions: 20, lastContribution: '2024-01-14', trend: 8 },
  { id: 3, name: 'Ama Serwaa', memberSince: '2021', totalAmount: 10000, transactions: 18, lastContribution: '2024-01-13', trend: -5 },
  { id: 4, name: 'Kwame Boateng', memberSince: '2018', totalAmount: 8500, transactions: 15, lastContribution: '2024-01-12', trend: 12 },
  { id: 5, name: 'Abena Osei', memberSince: '2022', totalAmount: 7500, transactions: 12, lastContribution: '2024-01-11', trend: 20 }
])

const attendanceStats = ref({
  avgAttendance: 342,
  peakAttendance: 450,
  peakDate: 'Jan 7, 2024',
  totalServices: 16,
  trend: 5,
  growthRate: 8
})

const serviceAttendance = ref([
  { name: 'Sunday Service', count: 380, percent: 100, color: '#6366f1' },
  { name: 'Midweek Service', count: 180, percent: 47, color: '#10b981' },
  { name: 'Youth Service', count: 120, percent: 32, color: '#f59e0b' },
  { name: 'Prayer Meeting', count: 85, percent: 22, color: '#8b5cf6' }
])

const weeklyAttendance = ref([
  { week: 'Jan 1-7', sunday: 380, midweek: 175, special: 0, total: 555, change: 8 },
  { week: 'Jan 8-14', sunday: 395, midweek: 180, special: 120, total: 695, change: 25 },
  { week: 'Jan 15-21', sunday: 370, midweek: 165, special: 0, total: 535, change: -23 },
  { week: 'Jan 22-28', sunday: 410, midweek: 190, special: 85, total: 685, change: 28 }
])

// Computed
const filteredVisitors = computed(() => {
  if (!visitorSearch.value) return visitors.value
  const search = visitorSearch.value.toLowerCase()
  return visitors.value.filter(v =>
    v.name.toLowerCase().includes(search) ||
    v.phone.includes(search)
  )
})

const maxWeeklyVisitors = computed(() => {
  return Math.max(...weeklyVisitors.value.map(w => Math.max(w.firstTime, w.returning)))
})

const maxContribution = computed(() => {
  return Math.max(...weeklyContributions.value.map(w => w.amount))
})

const linePath = computed(() => {
  const points = weeklyContributions.value.map((week, idx) => {
    const x = getPointX(idx)
    const y = getPointY(week.amount)
    return `${x},${y}`
  })
  return `M ${points.join(' L ')}`
})

const areaPath = computed(() => {
  const points = weeklyContributions.value.map((week, idx) => {
    const x = getPointX(idx)
    const y = getPointY(week.amount)
    return `${x},${y}`
  })
  const firstX = getPointX(0)
  const lastX = getPointX(weeklyContributions.value.length - 1)
  return `M ${firstX},200 L ${points.join(' L ')} L ${lastX},200 Z`
})

// Methods
function applyPreset(preset) {
  activePreset.value = preset.label
  const { start, end } = preset.getRange()
  filters.value.startDate = start.toISOString().split('T')[0]
  filters.value.endDate = end.toISOString().split('T')[0]
}

function getBarHeight(value) {
  return (value / maxWeeklyVisitors.value) * 100
}

function getPointX(idx) {
  const padding = 40
  const width = 400 - padding * 2
  const step = width / (weeklyContributions.value.length - 1)
  return padding + idx * step
}

function getPointY(amount) {
  const padding = 20
  const height = 200 - padding * 2
  const ratio = amount / maxContribution.value
  return padding + height * (1 - ratio)
}

function getStrokeOffset(idx) {
  let offset = 25
  for (let i = 0; i < idx; i++) {
    offset -= contributionTypes.value[i].percent
  }
  return offset
}

function getInitials(name) {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-GH', { style: 'currency', currency: 'GHS' }).format(amount)
}

function getStatusClass(status) {
  const classes = {
    'Completed': 'success',
    'In Progress': 'warning',
    'Pending': 'secondary'
  }
  return classes[status] || 'secondary'
}

function getRankClass(idx) {
  if (idx === 0) return 'gold'
  if (idx === 1) return 'silver'
  if (idx === 2) return 'bronze'
  return ''
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

async function refreshData() {
  loading.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    showToast('Data refreshed successfully')
  } catch (error) {
    showToast('Failed to refresh data', 'error')
  } finally {
    loading.value = false
  }
}

function exportCurrentReport() {
  const reportData = activeTab.value === 'visitors' ? visitors.value :
                     activeTab.value === 'partners' ? topPartners.value :
                     weeklyAttendance.value

  exportToExcel(reportData, `${activeTab.value}-report`)
  showToast('Report exported successfully')
}

function exportToExcel(data, filename) {
  // Convert data to CSV
  if (!data.length) return

  const headers = Object.keys(data[0])
  const csvContent = [
    headers.join(','),
    ...data.map(row => headers.map(h => {
      let cell = row[h]
      if (typeof cell === 'string' && cell.includes(',')) {
        cell = `"${cell}"`
      }
      return cell
    }).join(','))
  ].join('\n')

  // Create download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `${filename}-${filters.value.startDate}-to-${filters.value.endDate}.csv`
  link.click()
}

// Watch for date changes
watch(() => [filters.value.startDate, filters.value.endDate], () => {
  activePreset.value = ''
  // refreshData()
})

onMounted(() => {
  // Initial data load
  // refreshData()
})
</script>

<style scoped>
.page-wrap {
  padding: 1.5rem;
  max-width: 100%;
}

.page-header .title {
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.page-header .subtitle {
  color: #64748b;
  margin: 0.25rem 0 0;
  font-size: 0.9rem;
}

/* Buttons */
.btn-export, .btn-refresh {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border-radius: 10px;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-export {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
}

.btn-export:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-refresh {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-refresh:hover {
  background: #e2e8f0;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Filter Card */
.filter-card {
  background: white;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.theme-dark .filter-card {
  background: #1e293b;
}

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  align-items: flex-end;
}

.filter-group label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 0.5rem;
}

.date-range-picker {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.date-input-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}

.theme-dark .date-input-wrap {
  background: #0f172a;
  border-color: #334155;
}

.date-input-wrap i {
  color: #94a3b8;
}

.date-input-wrap input {
  border: none;
  background: transparent;
  font-size: 0.875rem;
  color: #334155;
  outline: none;
}

.theme-dark .date-input-wrap input {
  color: #e2e8f0;
}

.date-separator {
  color: #94a3b8;
  font-size: 0.875rem;
}

.quick-dates {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.preset-btn {
  padding: 0.5rem 0.875rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #475569;
  font-size: 0.8125rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-dark .preset-btn {
  background: #0f172a;
  border-color: #334155;
  color: #94a3b8;
}

.preset-btn:hover, .preset-btn.active {
  background: #6366f1;
  border-color: #6366f1;
  color: white;
}

/* Report Tabs */
.report-tabs {
  display: flex;
  gap: 0.5rem;
  background: #f1f5f9;
  padding: 0.375rem;
  border-radius: 12px;
  width: fit-content;
}

.theme-dark .report-tabs {
  background: #1e293b;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #64748b;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  color: #334155;
}

.tab-btn.active {
  background: white;
  color: #6366f1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.theme-dark .tab-btn.active {
  background: #334155;
  color: #a5b4fc;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: #64748b;
  gap: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Summary Grid */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

@media (max-width: 1200px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}

.summary-card {
  display: flex;
  gap: 1rem;
  padding: 1.25rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
}

.theme-dark .summary-card {
  background: #1e293b;
}

.summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.summary-card .card-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.summary-card.visitors .card-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.summary-card.first-timers .card-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.summary-card.returning .card-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.summary-card.converted .card-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.summary-card.contributions .card-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.summary-card.partners .card-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.summary-card.average .card-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.summary-card.tithes .card-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.summary-card.attendance .card-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.summary-card.peak .card-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.summary-card.services .card-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.summary-card.growth .card-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

.card-content {
  flex: 1;
  min-width: 0;
}

.card-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.2;
}

.theme-dark .card-value {
  color: #f1f5f9;
}

.card-label {
  font-size: 0.8125rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.card-trend, .card-sub {
  font-size: 0.75rem;
  margin-top: 0.375rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.card-trend.up { color: #10b981; }
.card-trend.down { color: #ef4444; }
.card-sub { color: #94a3b8; }

/* Chart Card */
.chart-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.theme-dark .chart-card {
  background: #1e293b;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e2e8f0;
}

.theme-dark .chart-header {
  border-color: #334155;
}

.chart-header h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #334155;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.theme-dark .chart-header h3 {
  color: #e2e8f0;
}

.chart-header h3 i {
  color: #6366f1;
}

.chart-legend {
  display: flex;
  gap: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #64748b;
}

.legend-item .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.legend-item.first-time .dot { background: #6366f1; }
.legend-item.returning .dot { background: #10b981; }

.chart-body {
  padding: 1.25rem;
}

/* Bar Chart */
.bar-chart {
  display: flex;
  justify-content: space-around;
  align-items: flex-end;
  height: 200px;
  padding-top: 20px;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}

.bars {
  display: flex;
  gap: 6px;
  align-items: flex-end;
  height: 160px;
}

.bar {
  width: 28px;
  border-radius: 6px 6px 0 0;
  position: relative;
  transition: all 0.3s ease;
  min-height: 4px;
}

.bar.first-time { background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%); }
.bar.returning { background: linear-gradient(180deg, #10b981 0%, #059669 100%); }

.bar:hover {
  filter: brightness(1.1);
}

.bar-value {
  position: absolute;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.bar:hover .bar-value {
  opacity: 1;
}

.bar-label {
  margin-top: 0.75rem;
  font-size: 0.75rem;
  color: #64748b;
}

/* Donut Chart */
.donut-chart-wrap {
  display: flex;
  align-items: center;
  gap: 2rem;
}

@media (max-width: 576px) {
  .donut-chart-wrap {
    flex-direction: column;
  }
}

.donut-chart {
  position: relative;
  width: 140px;
  height: 140px;
  flex-shrink: 0;
}

.donut-chart svg {
  transform: rotate(-90deg);
}

.donut-center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.center-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #334155;
}

.theme-dark .center-value {
  color: #e2e8f0;
}

.center-label {
  font-size: 0.75rem;
  color: #94a3b8;
}

.donut-legend {
  flex: 1;
}

.legend-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.theme-dark .legend-row {
  border-color: #334155;
}

.legend-row:last-child {
  border-bottom: none;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 4px;
  flex-shrink: 0;
}

.legend-name {
  flex: 1;
  font-size: 0.8125rem;
  color: #475569;
}

.theme-dark .legend-name {
  color: #cbd5e1;
}

.legend-value {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #334155;
}

.theme-dark .legend-value {
  color: #e2e8f0;
}

.legend-percent {
  font-size: 0.75rem;
  color: #94a3b8;
  min-width: 40px;
  text-align: right;
}

/* Line Chart */
.line-chart {
  position: relative;
  height: 220px;
}

.chart-grid {
  position: absolute;
  inset: 20px 40px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.grid-line {
  height: 1px;
  background: #e2e8f0;
}

.theme-dark .grid-line {
  background: #334155;
}

.line-svg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 200px;
}

.chart-labels {
  position: absolute;
  bottom: 0;
  left: 40px;
  right: 40px;
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Horizontal Bars */
.horizontal-bars {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.h-bar-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.h-bar-label {
  width: 120px;
  font-size: 0.8125rem;
  color: #475569;
  flex-shrink: 0;
}

.theme-dark .h-bar-label {
  color: #cbd5e1;
}

.h-bar-track {
  flex: 1;
  height: 32px;
  background: #f1f5f9;
  border-radius: 8px;
  overflow: hidden;
}

.theme-dark .h-bar-track {
  background: #0f172a;
}

.h-bar-fill {
  height: 100%;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 0.75rem;
  transition: width 0.5s ease;
}

.h-bar-value {
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

/* Data Card */
.data-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.theme-dark .data-card {
  background: #1e293b;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 1rem;
}

.theme-dark .card-header {
  border-color: #334155;
}

.card-header h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #334155;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.theme-dark .card-header h3 {
  color: #e2e8f0;
}

.card-header h3 i {
  color: #6366f1;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.search-input, .filter-select {
  padding: 0.5rem 0.875rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.8125rem;
  background: #f8fafc;
  color: #334155;
  outline: none;
  transition: all 0.2s ease;
}

.theme-dark .search-input,
.theme-dark .filter-select {
  background: #0f172a;
  border-color: #334155;
  color: #e2e8f0;
}

.search-input:focus, .filter-select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Data Table */
.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  padding: 0.875rem 1rem;
  text-align: left;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.theme-dark .data-table th {
  background: #0f172a;
  border-color: #334155;
  color: #94a3b8;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
  color: #475569;
}

.theme-dark .data-table td {
  border-color: #334155;
  color: #cbd5e1;
}

.data-table tr:hover td {
  background: rgba(99, 102, 241, 0.03);
}

.cell-with-avatar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar.partner {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.primary-text {
  font-weight: 500;
  color: #334155;
}

.theme-dark .primary-text {
  color: #e2e8f0;
}

.secondary-text {
  font-size: 0.75rem;
  color: #94a3b8;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 500;
}

.badge-primary {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.badge-secondary {
  background: rgba(100, 116, 139, 0.1);
  color: #64748b;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 500;
}

.status-badge.success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.status-badge.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.status-badge.secondary { background: rgba(100, 116, 139, 0.1); color: #64748b; }

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: #f1f5f9;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-dark .action-btn {
  background: #334155;
  color: #94a3b8;
}

.action-btn:hover {
  background: #6366f1;
  color: white;
}

.rank-badge {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rank-badge.gold { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; }
.rank-badge.silver { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); color: white; }
.rank-badge.bronze { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: white; }

.amount-cell {
  font-weight: 600;
  color: #10b981 !important;
}

.week-cell {
  font-weight: 500;
}

.total-cell {
  font-weight: 600;
  color: #6366f1 !important;
}

.trend-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
}

.trend-indicator.up {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.trend-indicator.down {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.empty-state {
  text-align: center;
  padding: 3rem !important;
  color: #94a3b8;
}

.empty-state i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  display: block;
}

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  background: white;
  color: #334155;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  z-index: 9999;
}

.toast-notification.success i { color: #10b981; }
.toast-notification.error i { color: #ef4444; }

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Responsive */
@media (max-width: 768px) {
  .page-wrap {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .page-header .title {
    font-size: 1.5rem;
  }

  .filter-row {
    flex-direction: column;
    gap: 1rem;
  }

  .date-range-picker {
    flex-wrap: wrap;
  }

  .quick-dates {
    flex-wrap: wrap;
  }

  .report-tabs {
    width: 100%;
    overflow-x: auto;
  }

  .tab-btn {
    white-space: nowrap;
  }

  .bar-chart {
    overflow-x: auto;
    min-width: 400px;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
  }

  .search-input {
    width: 100%;
  }
}
</style>
</template>
