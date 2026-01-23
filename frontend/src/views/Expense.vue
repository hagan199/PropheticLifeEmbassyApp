<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">Expense</h2>
        <Breadcrumbs />
        <div class="text-muted">Track expenses and spending</div>
      </div>
      <div>
        <CButton color="primary" @click="exportExpenses">
          <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol md="6">
        <CCard>
          <CCardHeader class="fw-semibold">Add Expense</CCardHeader>
          <CCardBody>
            <CForm @submit.prevent="addExpense">
              <CRow class="g-3">
                <CCol md="6">
                  <CFormLabel>Category</CFormLabel>
                  <CFormSelect v-model="form.category">
                    <option v-for="c in categories" :key="c.id" :value="c.name">{{ c.name }}</option>
                  </CFormSelect>
                </CCol>
                <CCol md="6">
                  <CFormLabel>Date</CFormLabel>
                  <CFormInput type="date" v-model="form.date" />
                </CCol>
                <CCol md="6">
                  <CFormLabel>Amount</CFormLabel>
                  <CFormInput type="number" min="0" step="0.01" v-model.number="form.amount" />
                </CCol>
                <CCol md="6">
                  <CFormLabel>Note</CFormLabel>
                  <CFormInput v-model="form.note" />
                </CCol>
              </CRow>
              <div class="mt-3 d-flex justify-content-end">
                <CButton color="success" type="submit">Save</CButton>
              </div>
            </CForm>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol md="6">
        <CCard>
          <CCardHeader class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Recent</div>
            <CBadge color="success">Total GHS {{ total }}</CBadge>
          </CCardHeader>
          <CCardBody>
            <CTable hover responsive>
              <CTableHead>
                <CTableRow>
                  <CTableHeaderCell scope="col">Category</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Note</CTableHeaderCell>
                  <CTableHeaderCell scope="col" class="text-end">Amount</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="x in expenses" :key="x.id">
                  <CTableDataCell>{{ x.category }}</CTableDataCell>
                  <CTableDataCell>{{ x.note }}</CTableDataCell>
                  <CTableDataCell class="text-end">{{ fmt(x.amount) }}</CTableDataCell>
                </CTableRow>
              </CTableBody>
            </CTable>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CFormSelect, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'

const form = ref({ category: 'Utilities', date: new Date().toISOString().slice(0, 10), amount: 0, note: '' })
const categories = ref([])
function loadCategories() {
  const raw = localStorage.getItem('expense_categories')
  const defaults = [
    { id: 1, name: 'Utilities' },
    { id: 2, name: 'Welfare' },
    { id: 3, name: 'Maintenance' },
    { id: 4, name: 'Outreach' }
  ]
  try { categories.value = raw ? JSON.parse(raw) : defaults } catch { categories.value = defaults }
}
const expenses = ref([
  { id: 1, category: 'Utilities', note: 'Electricity', amount: 320.5 },
  { id: 2, category: 'Welfare', note: 'Support', amount: 200 }
])
function addExpense() {
  const id = expenses.value.length ? expenses.value[expenses.value.length - 1].id + 1 : 1
  expenses.value = expenses.value.concat([{ id, category: form.value.category, note: form.value.note, amount: form.value.amount }])
  form.value = { category: 'Utilities', date: new Date().toISOString().slice(0, 10), amount: 0, note: '' }
}
const total = computed(() => expenses.value.reduce((a, b) => a + (b.amount || 0), 0).toFixed(2))
function fmt(n) { return Intl.NumberFormat().format(n) }

function exportExpenses() {
  const columns = [
    { key: 'date', label: 'Date' },
    { key: 'category', label: 'Category' },
    { key: 'note', label: 'Description' },
    { key: 'amount', label: 'Amount (GH₵)' }
  ]
  
  exportToExcel(expenses.value, columns, 'expenses_report')
}

onMounted(loadCategories)
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

@media (max-width: 576px) {
  .page-header .btn {
    width: 100%;
  }

  :deep(.form-control),
  :deep(select.form-select) {
    font-size: .95rem;
  }

  :deep(.table) {
    font-size: .92rem;
  }
}
</style>
