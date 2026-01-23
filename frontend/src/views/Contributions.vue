<template>
  <div class="page-wrap">
    <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false">
      {{ notification.message }}
    </CAlert>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Contributions</h2>
        <Breadcrumbs />
        <div class="text-muted">Track tithes, offerings, and special contributions</div>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" @click="exportReport">
          <i class="bi bi-download me-1"></i> Export
        </CButton>
        <CButton color="primary" @click="openAddContribution">
          <i class="bi bi-plus-lg me-1"></i> Record Contribution
        </CButton>
      </div>
    </div>

    <!-- Summary Cards -->
    <CRow class="g-4 mb-4">
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Today</div>
                <div class="fs-3 fw-bold">GH₵ {{ formatMoney(todayTotal) }}</div>
                <div class="text-muted small">{{ todayCount }} transactions</div>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-calendar-day"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">This Week</div>
                <div class="fs-3 fw-bold">GH₵ {{ formatMoney(weekTotal) }}</div>
                <div class="text-muted small">{{ weekCount }} transactions</div>
              </div>
              <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-calendar-week"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">This Month</div>
                <div class="fs-3 fw-bold">GH₵ {{ formatMoney(monthTotal) }}</div>
                <div class="text-muted small">{{ monthCount }} transactions</div>
              </div>
              <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-calendar-month"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol sm="6" xl="3">
        <CCard class="stat-card border-0 shadow-sm h-100">
          <CCardBody>
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small text-uppercase">Year to Date</div>
                <div class="fs-3 fw-bold">GH₵ {{ formatMoney(yearTotal) }}</div>
                <div class="text-muted small">{{ yearCount }} transactions</div>
              </div>
              <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-calendar"></i>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Breakdown by Type -->
    <CRow class="g-4 mb-4">
      <CCol lg="8">
        <CCard>
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Monthly Breakdown</div>
            <CFormSelect v-model="selectedMonth" style="width: 150px">
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </CFormSelect>
          </CCardHeader>
          <CCardBody>
            <div class="chart-placeholder d-flex align-items-center justify-content-center" style="height: 250px">
              <div class="text-center text-muted">
                <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                Chart will render here (Chart.js integration)
              </div>
            </div>
            <CRow class="g-3 mt-2">
              <CCol sm="4">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Tithes</div>
                  <div class="fs-5 fw-bold">GH₵ {{ formatMoney(typeBreakdown.tithe) }}</div>
                </div>
              </CCol>
              <CCol sm="4">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Offering</div>
                  <div class="fs-5 fw-bold">GH₵ {{ formatMoney(typeBreakdown.offering) }}</div>
                </div>
              </CCol>
              <CCol sm="4">
                <div class="p-3 bg-light rounded">
                  <div class="text-muted small">Special Seed</div>
                  <div class="fs-5 fw-bold">GH₵ {{ formatMoney(typeBreakdown.specialSeed) }}</div>
                </div>
              </CCol>
            </CRow>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol lg="4">
        <CCard class="h-100">
          <CCardHeader class="fw-semibold">Payment Methods</CCardHeader>
          <CCardBody>
            <div v-for="pm in paymentMethodStats" :key="pm.method"
              class="d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center">
                <div class="method-icon me-2" :class="'bg-' + pm.color + '-subtle text-' + pm.color">
                  <i :class="pm.icon"></i>
                </div>
                <span>{{ pm.method }}</span>
              </div>
              <div class="text-end">
                <div class="fw-semibold">GH₵ {{ formatMoney(pm.amount) }}</div>
                <div class="text-muted small">{{ pm.count }} transactions</div>
              </div>
            </div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Contributions Table -->
    <CCard>
      <CCardHeader class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-semibold">Contribution Records</div>
        <div class="d-flex gap-2 flex-wrap">
          <CFormInput v-model="search" placeholder="Search..." style="width: 200px" />
          <CFormSelect v-model="typeFilter" style="width: 140px">
            <option value="">All Types</option>
            <option value="tithe">Tithe</option>
            <option value="offering">Offering</option>
            <option value="special_seed">Special Seed</option>
            <option value="building_fund">Building Fund</option>
            <option value="missions">Missions</option>
          </CFormSelect>
          <CFormSelect v-model="paymentFilter" style="width: 140px">
            <option value="">All Methods</option>
            <option value="cash">Cash</option>
            <option value="momo">Mobile Money</option>
            <option value="bank">Bank Transfer</option>
            <option value="cheque">Cheque</option>
          </CFormSelect>
          <CFormInput v-model="dateFrom" type="date" style="width: 150px" />
          <CFormInput v-model="dateTo" type="date" style="width: 150px" />
        </div>
      </CCardHeader>
      <CCardBody class="p-0">
        <CTable hover responsive align="middle">
          <CTableHead color="light">
            <CTableRow>
              <CTableHeaderCell>Date</CTableHeaderCell>
              <CTableHeaderCell>Contributor</CTableHeaderCell>
              <CTableHeaderCell>Type</CTableHeaderCell>
              <CTableHeaderCell>Amount</CTableHeaderCell>
              <CTableHeaderCell>Method</CTableHeaderCell>
              <CTableHeaderCell>Reference</CTableHeaderCell>
              <CTableHeaderCell>Recorded By</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="c in filteredContributions" :key="c.id">
              <CTableDataCell>{{ formatDate(c.date) }}</CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <CAvatar :color="c.memberName ? 'primary' : 'secondary'" text-color="white" size="sm" class="me-2">
                    {{ (c.memberName || 'A').charAt(0) }}
                  </CAvatar>
                  <div>
                    <div class="fw-semibold">{{ c.memberName || 'Anonymous' }}</div>
                    <div class="text-muted small" v-if="c.memberPhone">{{ c.memberPhone }}</div>
                  </div>
                </div>
              </CTableDataCell>
              <CTableDataCell>
                <CBadge :color="typeColor(c.type)">{{ typeLabel(c.type) }}</CBadge>
              </CTableDataCell>
              <CTableDataCell class="fw-semibold">GH₵ {{ formatMoney(c.amount) }}</CTableDataCell>
              <CTableDataCell>
                <CBadge color="light" text-color="dark">
                  <i :class="paymentIcon(c.paymentMethod)" class="me-1"></i>
                  {{ paymentLabel(c.paymentMethod) }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <span class="text-muted">{{ c.reference || '—' }}</span>
              </CTableDataCell>
              <CTableDataCell>
                <span class="text-muted small">{{ c.recordedBy }}</span>
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="light" size="sm" class="me-1" @click="viewReceipt(c)">
                  <i class="bi bi-receipt"></i>
                </CButton>
                <CButton color="light" size="sm" @click="editContribution(c)">
                  <i class="bi bi-pencil"></i>
                </CButton>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
        <div v-if="!filteredContributions.length" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2"></i>
          No contributions found
        </div>
      </CCardBody>
      <CCardFooter class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Showing {{ filteredContributions.length }} of {{ contributions.length }} records
        </div>
        <CPagination v-if="totalPages > 1">
          <CPaginationItem :disabled="currentPage === 1" @click="currentPage--">Previous</CPaginationItem>
          <CPaginationItem v-for="p in totalPages" :key="p" :active="p === currentPage" @click="currentPage = p">
            {{ p }}
          </CPaginationItem>
          <CPaginationItem :disabled="currentPage === totalPages" @click="currentPage++">Next</CPaginationItem>
        </CPagination>
      </CCardFooter>
    </CCard>

    <!-- Add/Edit Contribution Modal -->
    <CModal :visible="showContributionModal" @close="showContributionModal = false" size="lg">
      <CModalHeader>
        <CModalTitle>{{ editingId ? 'Edit Contribution' : 'Record Contribution' }}</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <CRow class="g-3">
            <CCol md="12">
              <CFormLabel>Contributor</CFormLabel>
              <div class="d-flex gap-2">
                <CFormSelect v-model="form.memberId" class="flex-fill">
                  <option value="">Anonymous</option>
                  <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }} - {{ m.phone }}</option>
                </CFormSelect>
                <CButton color="light" @click="showMemberSearch = true">
                  <i class="bi bi-search"></i>
                </CButton>
              </div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Contribution Type <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.type">
                <option value="">Select type...</option>
                <option value="tithe">Tithe</option>
                <option value="offering">Offering</option>
                <option value="special_seed">Special Seed</option>
                <option value="building_fund">Building Fund</option>
                <option value="missions">Missions</option>
                <option value="welfare">Welfare</option>
              </CFormSelect>
            </CCol>
            <CCol md="6">
              <CFormLabel>Amount (GH₵) <span class="text-danger">*</span></CFormLabel>
              <CInputGroup>
                <CInputGroupText>GH₵</CInputGroupText>
                <CFormInput v-model.number="form.amount" type="number" step="0.01" min="0" />
              </CInputGroup>
            </CCol>
            <CCol md="6">
              <CFormLabel>Payment Method <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.paymentMethod">
                <option value="">Select method...</option>
                <option value="cash">Cash</option>
                <option value="momo">Mobile Money</option>
                <option value="bank">Bank Transfer</option>
                <option value="cheque">Cheque</option>
              </CFormSelect>
            </CCol>
            <CCol md="6">
              <CFormLabel>Date <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.date" type="date" />
            </CCol>
            <CCol md="6" v-if="form.paymentMethod && form.paymentMethod !== 'cash'">
              <CFormLabel>Reference Number</CFormLabel>
              <CFormInput v-model="form.reference" placeholder="Transaction ID" />
            </CCol>
            <CCol md="6" v-if="form.paymentMethod === 'momo'">
              <CFormLabel>Mobile Number</CFormLabel>
              <CInputGroup>
                <CInputGroupText>+233</CInputGroupText>
                <CFormInput v-model="form.mobileNumber" />
              </CInputGroup>
            </CCol>
            <CCol md="12">
              <CFormLabel>Notes</CFormLabel>
              <CFormTextarea v-model="form.notes" rows="2" placeholder="Additional notes (optional)" />
            </CCol>
          </CRow>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showContributionModal = false">Cancel</CButton>
        <CButton color="primary" @click="saveContribution">
          {{ editingId ? 'Update' : 'Record' }} Contribution
        </CButton>
      </CModalFooter>
    </CModal>

    <!-- Receipt Modal -->
    <CModal :visible="showReceiptModal" @close="showReceiptModal = false">
      <CModalHeader>
        <CModalTitle>Contribution Receipt</CModalTitle>
      </CModalHeader>
      <CModalBody v-if="selectedReceipt" class="receipt-body">
        <div class="text-center mb-4">
          <h4 class="mb-1">Prophetic Life Embassy</h4>
          <div class="text-muted">Contribution Receipt</div>
        </div>
        <hr />
        <CRow class="g-2">
          <CCol xs="6"><span class="text-muted">Receipt No:</span></CCol>
          <CCol xs="6" class="text-end fw-semibold">{{ selectedReceipt.reference || `RCP-${selectedReceipt.id}` }}
          </CCol>
          <CCol xs="6"><span class="text-muted">Date:</span></CCol>
          <CCol xs="6" class="text-end">{{ formatDate(selectedReceipt.date) }}</CCol>
          <CCol xs="6"><span class="text-muted">Contributor:</span></CCol>
          <CCol xs="6" class="text-end">{{ selectedReceipt.memberName || 'Anonymous' }}</CCol>
          <CCol xs="6"><span class="text-muted">Type:</span></CCol>
          <CCol xs="6" class="text-end">{{ typeLabel(selectedReceipt.type) }}</CCol>
          <CCol xs="6"><span class="text-muted">Method:</span></CCol>
          <CCol xs="6" class="text-end">{{ paymentLabel(selectedReceipt.paymentMethod) }}</CCol>
        </CRow>
        <hr />
        <div class="text-center">
          <div class="text-muted small">Amount</div>
          <div class="fs-2 fw-bold">GH₵ {{ formatMoney(selectedReceipt.amount) }}</div>
        </div>
        <hr />
        <div class="text-center text-muted small">
          Thank you for your generous contribution!<br />
          God bless you abundantly.
        </div>
      </CModalBody>
      <CModalFooter>
        <CButton color="primary" @click="printReceipt">
          <i class="bi bi-printer me-1"></i> Print Receipt
        </CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CCardFooter, CRow, CCol, CButton, CTable, CTableHead, CTableBody,
  CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CFormInput, CFormSelect,
  CFormLabel, CFormTextarea, CInputGroup, CInputGroupText, CModal, CModalHeader, CModalTitle,
  CModalBody, CModalFooter, CAlert, CPagination, CPaginationItem, CForm
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'

// Sample members for dropdown
const members = ref([
  { id: 1, name: 'Kwame Asante', phone: '0241234567' },
  { id: 2, name: 'Ama Mensah', phone: '0201234567' },
  { id: 3, name: 'Kofi Boateng', phone: '0551234567' }
])

// Contributions data
const contributions = ref([
  { id: 1, date: '2026-01-26', memberId: 1, memberName: 'Kwame Asante', memberPhone: '0241234567', type: 'tithe', amount: 500, paymentMethod: 'momo', reference: 'TXN123456', recordedBy: 'Finance User' },
  { id: 2, date: '2026-01-26', memberId: 2, memberName: 'Ama Mensah', memberPhone: '0201234567', type: 'offering', amount: 100, paymentMethod: 'cash', reference: '', recordedBy: 'Finance User' },
  { id: 3, date: '2026-01-26', memberId: null, memberName: null, type: 'offering', amount: 50, paymentMethod: 'cash', reference: '', recordedBy: 'Finance User' },
  { id: 4, date: '2026-01-25', memberId: 3, memberName: 'Kofi Boateng', type: 'special_seed', amount: 1000, paymentMethod: 'bank', reference: 'BNK789012', recordedBy: 'Finance User' },
  { id: 5, date: '2026-01-19', memberId: 1, memberName: 'Kwame Asante', type: 'tithe', amount: 500, paymentMethod: 'momo', reference: 'TXN654321', recordedBy: 'Finance User' },
  { id: 6, date: '2026-01-12', memberId: 2, memberName: 'Ama Mensah', type: 'building_fund', amount: 200, paymentMethod: 'momo', reference: 'TXN111222', recordedBy: 'Finance User' }
])

// State
const search = ref('')
const typeFilter = ref('')
const paymentFilter = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)
const perPage = 10

const showContributionModal = ref(false)
const showReceiptModal = ref(false)
const showMemberSearch = ref(false)
const editingId = ref(null)
const selectedReceipt = ref(null)
const selectedMonth = ref('2026-01')

const months = [
  { value: '2026-01', label: 'January 2026' },
  { value: '2025-12', label: 'December 2025' },
  { value: '2025-11', label: 'November 2025' }
]

// Form
const form = reactive({
  memberId: '', type: '', amount: '', paymentMethod: '', date: '', reference: '', mobileNumber: '', notes: ''
})

// Notification
const notification = reactive({ show: false, type: 'success', message: '' })

// Computed Stats
const todayContributions = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return contributions.value.filter(c => c.date === today)
})
const todayTotal = computed(() => todayContributions.value.reduce((s, c) => s + c.amount, 0))
const todayCount = computed(() => todayContributions.value.length)

const weekContributions = computed(() => {
  const today = new Date()
  const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
  return contributions.value.filter(c => new Date(c.date) >= weekAgo)
})
const weekTotal = computed(() => weekContributions.value.reduce((s, c) => s + c.amount, 0))
const weekCount = computed(() => weekContributions.value.length)

const monthContributions = computed(() => {
  const thisMonth = new Date().toISOString().slice(0, 7)
  return contributions.value.filter(c => c.date.startsWith(thisMonth))
})
const monthTotal = computed(() => monthContributions.value.reduce((s, c) => s + c.amount, 0))
const monthCount = computed(() => monthContributions.value.length)

const yearContributions = computed(() => {
  const thisYear = new Date().getFullYear().toString()
  return contributions.value.filter(c => c.date.startsWith(thisYear))
})
const yearTotal = computed(() => yearContributions.value.reduce((s, c) => s + c.amount, 0))
const yearCount = computed(() => yearContributions.value.length)

const typeBreakdown = computed(() => ({
  tithe: monthContributions.value.filter(c => c.type === 'tithe').reduce((s, c) => s + c.amount, 0),
  offering: monthContributions.value.filter(c => c.type === 'offering').reduce((s, c) => s + c.amount, 0),
  specialSeed: monthContributions.value.filter(c => c.type === 'special_seed').reduce((s, c) => s + c.amount, 0)
}))

const paymentMethodStats = computed(() => [
  { method: 'Mobile Money', color: 'warning', icon: 'bi bi-phone', amount: monthContributions.value.filter(c => c.paymentMethod === 'momo').reduce((s, c) => s + c.amount, 0), count: monthContributions.value.filter(c => c.paymentMethod === 'momo').length },
  { method: 'Cash', color: 'success', icon: 'bi bi-cash', amount: monthContributions.value.filter(c => c.paymentMethod === 'cash').reduce((s, c) => s + c.amount, 0), count: monthContributions.value.filter(c => c.paymentMethod === 'cash').length },
  { method: 'Bank Transfer', color: 'primary', icon: 'bi bi-bank', amount: monthContributions.value.filter(c => c.paymentMethod === 'bank').reduce((s, c) => s + c.amount, 0), count: monthContributions.value.filter(c => c.paymentMethod === 'bank').length }
])

// Filtered contributions
const filteredContributions = computed(() => {
  let result = contributions.value
  if (search.value) {
    const s = search.value.toLowerCase()
    result = result.filter(c => (c.memberName?.toLowerCase().includes(s)) || c.reference?.includes(s))
  }
  if (typeFilter.value) result = result.filter(c => c.type === typeFilter.value)
  if (paymentFilter.value) result = result.filter(c => c.paymentMethod === paymentFilter.value)
  if (dateFrom.value) result = result.filter(c => c.date >= dateFrom.value)
  if (dateTo.value) result = result.filter(c => c.date <= dateTo.value)
  return result.sort((a, b) => b.date.localeCompare(a.date))
})

const totalPages = computed(() => Math.ceil(filteredContributions.value.length / perPage))

// Methods
function openAddContribution() {
  editingId.value = null
  Object.assign(form, { memberId: '', type: '', amount: '', paymentMethod: '', date: new Date().toISOString().split('T')[0], reference: '', mobileNumber: '', notes: '' })
  showContributionModal.value = true
}

function editContribution(c) {
  editingId.value = c.id
  Object.assign(form, { memberId: c.memberId || '', type: c.type, amount: c.amount, paymentMethod: c.paymentMethod, date: c.date, reference: c.reference || '', mobileNumber: '', notes: c.notes || '' })
  showContributionModal.value = true
}

function saveContribution() {
  const member = form.memberId ? members.value.find(m => m.id === form.memberId) : null
  if (editingId.value) {
    const idx = contributions.value.findIndex(c => c.id === editingId.value)
    if (idx !== -1) {
      contributions.value[idx] = { ...contributions.value[idx], ...form, memberName: member?.name, memberPhone: member?.phone }
    }
    showNotification('success', 'Contribution updated')
  } else {
    const newId = Math.max(...contributions.value.map(c => c.id)) + 1
    contributions.value.push({
      id: newId, ...form, memberId: form.memberId || null, memberName: member?.name || null, memberPhone: member?.phone || null, recordedBy: 'Finance User'
    })
    showNotification('success', 'Contribution recorded')
  }
  showContributionModal.value = false
}

function viewReceipt(c) {
  selectedReceipt.value = c
  showReceiptModal.value = true
}

function printReceipt() {
  window.print()
}

function exportReport() {
  const columns = [
    { key: 'date', label: 'Date' },
    { key: 'memberName', label: 'Contributor' },
    { key: 'memberPhone', label: 'Phone' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount (GH₵)' },
    { key: 'paymentMethod', label: 'Payment Method' },
    { key: 'reference', label: 'Reference' },
    { key: 'recordedBy', label: 'Recorded By' }
  ]

  const exportData = filteredContributions.value.map(c => ({
    ...c,
    memberName: c.memberName || 'Anonymous',
    type: typeLabel(c.type),
    paymentMethod: paymentLabel(c.paymentMethod)
  }))

  exportToExcel(exportData, columns, 'contributions_report')
  showNotification('success', `Exported ${exportData.length} contributions to Excel`)
}

function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 3000)
}

// Helpers
function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatMoney(amount) {
  return (amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function typeColor(type) {
  const colors = { tithe: 'primary', offering: 'success', special_seed: 'warning', building_fund: 'info', missions: 'purple', welfare: 'danger' }
  return colors[type] || 'secondary'
}

function typeLabel(type) {
  const labels = { tithe: 'Tithe', offering: 'Offering', special_seed: 'Special Seed', building_fund: 'Building Fund', missions: 'Missions', welfare: 'Welfare' }
  return labels[type] || type
}

function paymentIcon(method) {
  const icons = { cash: 'bi bi-cash', momo: 'bi bi-phone', bank: 'bi bi-bank', cheque: 'bi bi-file-text' }
  return icons[method] || 'bi bi-credit-card'
}

function paymentLabel(method) {
  const labels = { cash: 'Cash', momo: 'Mobile Money', bank: 'Bank Transfer', cheque: 'Cheque' }
  return labels[method] || method
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.stat-card {
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.method-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.receipt-body {
  font-family: 'Courier New', monospace;
}

.chart-placeholder {
  background: #f8f9fa;
  border-radius: 8px;
}
</style>
