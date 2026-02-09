<template>
  <div class="page-wrap">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
      <div>
        <h2 class="title mb-1 fw-bold" style="font-size:2rem;">Expense</h2>
        <Breadcrumbs />
        <div class="text-muted small">Track expenses and spending</div>
      </div>
      <div>
        <CButton color="primary" class="shadow-sm px-4 py-2" title="Export all expenses to Excel"
          @click="exportExpenses">
          <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </CButton>
        <div class="page-wrap expense-page">
          <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="header-title-group">
              <h2 class="title">Expense</h2>
              <Breadcrumbs />
              <div class="text-muted">Track expenses and spending</div>
            </div>
            <div class="header-action-group">
              <CButton color="primary" @click="exportExpenses">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
              </CButton>
            </div>
          </div>

          <div class="expense-cards-layout">
            <div class="expense-card add-expense-card">
              <div class="card-header fw-semibold">{{ isEditing ? 'Edit Expense' : 'Add Expense' }}</div>
              <div class="card-body">
                <CForm @submit.prevent="saveExpense">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <CFormLabel for="category">Category</CFormLabel>
                      <CFormSelect id="category" v-model="form.category">
                        <option v-for="c in categories" :key="c.id" :value="c.name">{{ c.name }}</option>
                      </CFormSelect>
                    </div>
                    <div class="col-md-6">
                      <CFormLabel for="date">Date</CFormLabel>
                      <CFormInput id="date" v-model="form.date" type="date" />
                    </div>
                    <div class="col-md-6">
                      <CFormLabel for="amount">Amount</CFormLabel>
                      <CFormInput id="amount" v-model.number="form.amount" type="number" min="0" step="0.01" />
                    </div>
                    <div class="col-md-6">
                      <CFormLabel for="note">Note</CFormLabel>
                      <CFormInput id="note" v-model="form.note" />
                    </div>
                  </div>
                  <div class="mt-4 d-flex justify-content-end gap-2">
                    <CButton v-if="isEditing" color="secondary" variant="outline" @click="cancelEdit">Cancel</CButton>
                    <CButton color="success" type="submit" :disabled="saving">
                      <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status"
                        aria-hidden="true"></span>
                      {{ isEditing ? 'Update' : 'Save' }}
                    </CButton>
                  </div>
                </CForm>
              </div>
            </div>
            <div class="expense-card recent-expense-card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Recent</div>
                <CBadge color="success">Total GHS {{ total }}</CBadge>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table expense-table align-middle">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Note</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <transition-group name="fade" tag="template">
                        <tr v-for="x in expenses" :key="x.id" class="expense-row">
                          <td>{{ formatDate(x.date) }}</td>
                          <td>{{ x.category }}</td>
                          <td>{{ x.note }}</td>
                          <td class="text-end">{{ fmt(x.amount) }}</td>
                          <td class="text-center">
                            <CButton color="primary" variant="ghost" size="sm" title="Edit" aria-label="Edit expense"
                              @click="editExpense(x)">
                              <i class="bi bi-pencil"></i>
                            </CButton>
                            <CButton color="danger" variant="ghost" size="sm" title="Delete" aria-label="Delete expense"
                              @click="confirmDelete(x)">
                              <i class="bi bi-trash"></i>
                            </CButton>
                          </td>
                        </tr>
                      </transition-group>
                      <tr v-if="expenses.length === 0">
                        <td colspan="5" class="text-center text-muted">No expenses recorded</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Delete Confirmation Modal -->
          <Teleport to="body">
            <CModal :visible="showDeleteModal" alignment="center" @close="showDeleteModal = false">
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
          </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CForm, CFormLabel, CFormInput, CFormSelect, CButton, CBadge, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'


const form = ref({ id: null, category: 'Utilities', date: new Date().toISOString().slice(0, 10), amount: 0, note: '' })
const isEditing = ref(false)
const showDeleteModal = ref(false)
const expenseToDelete = ref(null)
const saving = ref(false)
// hoveredRow removed (not currently used)

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

async function saveExpense() {
  saving.value = true
  await new Promise(r => setTimeout(r, 500)) // Simulate loading
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
  saving.value = false
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
    // Animate row removal
    const id = expenseToDelete.value.id
    expenses.value = expenses.value.filter(e => e.id !== id)
    expenseToDelete.value = null
    showDeleteModal.value = false
    // If we were editing the deleted expense, reset the form
    if (isEditing.value && form.value.id === id) {
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
.expense-page {
  padding: 24px 12px 32px 12px;
}

.page-header {
  margin-bottom: 24px;
  gap: 1.5rem;
}

.header-title-group {
  min-width: 220px;
}

.header-action-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.expense-cards-layout {
  display: flex;
  gap: 2.5rem;
  align-items: flex-start;
  justify-content: center;
}

.expense-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 12px 0 rgba(102, 126, 234, 0.08);
  border: 1px solid #e0e7ef;
  min-width: 340px;
  max-width: 480px;
  flex: 1 1 340px;
  display: flex;
  flex-direction: column;
  min-height: 420px;
  margin-bottom: 1.5rem;
}

.add-expense-card {
  max-width: 420px;
}

.recent-expense-card {
  max-width: 540px;
}

.expense-card .card-header {
  background: #f8fafc;
  border-bottom: 1px solid #e0e7ef;
  border-radius: 18px 18px 0 0;
  padding: 1.25rem 1.5rem;
  font-size: 1.15rem;
}

.expense-card .card-body {
  padding: 1.5rem;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
}

.expense-table {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
}

.expense-table th,
.expense-table td {
  padding: 0.75rem 1rem;
}

.expense-table th {
  background: #f3f6fa;
  font-weight: 600;
  font-size: 1rem;
}

.expense-table tbody tr {
  transition: background 0.18s;
}

.expense-table tbody tr:nth-child(even) {
  background: #f8fafc;
}

.expense-table tbody tr:hover {
  background: #e0e7ff;
}

.expense-table .expense-row {
  transition: all 0.25s cubic-bezier(.4, 0, .2, 1);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.fade-leave-active {
  position: absolute;
}

.expense-card .btn,
.expense-card .form-control,
.expense-card .form-select {
  border-radius: 10px;
  font-size: 1rem;
}

.expense-card .btn:focus,
.expense-card .form-control:focus,
.expense-card .form-select:focus {
  box-shadow: 0 0 0 2px #a5b4fc;
  border-color: #6366f1;
}

.expense-card .btn-success {
  min-width: 110px;
  font-weight: 600;
}

.expense-card .btn[disabled] {
  opacity: 0.7;
  pointer-events: none;
}

@media (max-width: 991.98px) {
  .expense-cards-layout {
    flex-direction: column;
    gap: 1.5rem;
    align-items: stretch;
  }

  .expense-card {
    max-width: 100%;
    min-width: 0;
  }
}

@media (max-width: 576px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
  }

  .header-action-group {
    justify-content: flex-end;
  }

  .expense-card .btn-success {
    width: 100%;
    min-width: 0;
  }

  .expense-card .btn {
    font-size: 1.05rem;
  }

  .expense-table th,
  .expense-table td {
    padding: 0.6rem 0.5rem;
    font-size: 0.97rem;
  }
}
</style>
