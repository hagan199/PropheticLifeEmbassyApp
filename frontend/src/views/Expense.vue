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
          <CCardHeader class="fw-semibold">{{ isEditing ? 'Edit Expense' : 'Add Expense' }}</CCardHeader>
          <CCardBody>
            <CForm @submit.prevent="saveExpense">
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
              <div class="mt-3 d-flex justify-content-end gap-2">
                <CButton v-if="isEditing" color="secondary" variant="outline" @click="cancelEdit">Cancel</CButton>
                <CButton color="success" type="submit">{{ isEditing ? 'Update' : 'Save' }}</CButton>
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
                  <CTableHeaderCell scope="col">Date</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Category</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Note</CTableHeaderCell>
                  <CTableHeaderCell scope="col" class="text-end">Amount</CTableHeaderCell>
                  <CTableHeaderCell scope="col" class="text-center">Actions</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="x in expenses" :key="x.id">
                  <CTableDataCell>{{ formatDate(x.date) }}</CTableDataCell>
                  <CTableDataCell>{{ x.category }}</CTableDataCell>
                  <CTableDataCell>{{ x.note }}</CTableDataCell>
                  <CTableDataCell class="text-end">{{ fmt(x.amount) }}</CTableDataCell>
                  <CTableDataCell class="text-center">
                    <CButton color="primary" variant="ghost" size="sm" @click="editExpense(x)" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </CButton>
                    <CButton color="danger" variant="ghost" size="sm" @click="confirmDelete(x)" title="Delete">
                      <i class="bi bi-trash"></i>
                    </CButton>
                  </CTableDataCell>
                </CTableRow>
                <CTableRow v-if="expenses.length === 0">
                  <CTableDataCell colspan="5" class="text-center text-muted">No expenses recorded</CTableDataCell>
                </CTableRow>
              </CTableBody>
            </CTable>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- Delete Confirmation Modal -->
    <CModal :visible="showDeleteModal" @close="showDeleteModal = false" alignment="center">
      <CModalHeader>
        <CModalTitle>Confirm Delete</CModalTitle>
      </CModalHeader>
      <CModalBody>
        Are you sure you want to delete this expense?
        <div class="mt-2 p-2 bg-light rounded">
          <strong>{{ expenseToDelete?.category }}</strong> - {{ expenseToDelete?.note }}<br>
          <span class="text-muted">GHS {{ fmt(expenseToDelete?.amount || 0) }}</span>
        </div>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showDeleteModal = false">Cancel</CButton>
        <CButton color="danger" @click="deleteExpense">Delete</CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CFormSelect, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'

const form = ref({ id: null, category: 'Utilities', date: new Date().toISOString().slice(0, 10), amount: 0, note: '' })
const isEditing = ref(false)
const showDeleteModal = ref(false)
const expenseToDelete = ref(null)

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
  { id: 1, date: '2026-02-04', category: 'Utilities', note: 'Electricity', amount: 320.5 },
  { id: 2, date: '2026-02-03', category: 'Welfare', note: 'Support', amount: 200 },
  { id: 3, date: '2026-02-02', category: 'Maintenance', note: 'Bulk', amount: 50 }
])

function saveExpense() {
  if (isEditing.value) {
    // Update existing expense
    const index = expenses.value.findIndex(e => e.id === form.value.id)
    if (index !== -1) {
      expenses.value[index] = { ...form.value }
    }
  } else {
    // Add new expense
    const id = expenses.value.length ? Math.max(...expenses.value.map(e => e.id)) + 1 : 1
    expenses.value.push({ id, date: form.value.date, category: form.value.category, note: form.value.note, amount: form.value.amount })
  }
  resetForm()
}

function editExpense(expense) {
  form.value = { ...expense }
  isEditing.value = true
}

function cancelEdit() {
  resetForm()
}

function resetForm() {
  form.value = { id: null, category: 'Utilities', date: new Date().toISOString().slice(0, 10), amount: 0, note: '' }
  isEditing.value = false
}

function confirmDelete(expense) {
  expenseToDelete.value = expense
  showDeleteModal.value = true
}

function deleteExpense() {
  if (expenseToDelete.value) {
    expenses.value = expenses.value.filter(e => e.id !== expenseToDelete.value.id)
    expenseToDelete.value = null
    showDeleteModal.value = false
    
    // If we were editing the deleted expense, reset the form
    if (isEditing.value && form.value.id === expenseToDelete.value?.id) {
      resetForm()
    }
  }
}

const total = computed(() => expenses.value.reduce((a, b) => a + (b.amount || 0), 0).toFixed(2))
function fmt(n) { return Intl.NumberFormat().format(n) }
function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

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
