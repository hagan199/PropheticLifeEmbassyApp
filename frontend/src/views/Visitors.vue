<template>
  <div class="page-wrap">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">
          <span class="gradient-text">Visitors</span>
        </h2>
        <Breadcrumbs />
        <p class="subtitle mt-1">Register first-time visitors and partners</p>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" class="hover-lift" @click="exportVisitors">
          <i class="bi bi-download me-2"></i>
          <span class="desktop-only">Export</span>
        </CButton>
      </div>
    </div>

    <CRow class="g-4">
      <!-- Register Form Card -->
      <CCol lg="5">
        <div class="card hover-lift animate-fadeInUp stagger-1">
          <div class="card-header d-flex align-items-center gap-3">
            <div class="header-icon">
              <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
              <h5 class="mb-0 fw-semibold">Register Visitor</h5>
              <small class="text-muted">Add a new visitor or partner</small>
            </div>
          </div>
          <CCardBody>
            <CForm @submit.prevent="addVisitor">
              <div class="form-group mb-3">
                <CFormLabel class="form-label-modern">
                  <i class="bi bi-person me-2"></i>Full Name
                </CFormLabel>
                <CFormInput
                  v-model="form.name"
                  placeholder="Enter full name"
                  class="form-input-modern"
                />
              </div>

              <div class="form-group mb-3">
                <CFormLabel class="form-label-modern">
                  <i class="bi bi-phone me-2"></i>Phone Number
                  <span class="text-danger ms-1">*</span>
                </CFormLabel>
                <CFormInput
                  v-model="form.phone"
                  placeholder="e.g. 0241234567"
                  :class="['form-input-modern', { 'is-invalid': phoneError }]"
                />
                <transition name="error-fade">
                  <div v-if="phoneError" class="error-message">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ phoneError }}
                  </div>
                </transition>
              </div>

              <CRow class="g-3 mb-4">
                <CCol sm="6">
                  <CFormLabel class="form-label-modern">
                    <i class="bi bi-tag me-2"></i>Category
                  </CFormLabel>
                  <CFormSelect v-model="form.category" class="form-input-modern">
                    <option value="Visitor">Visitor</option>
                    <option value="Partner">Partner</option>
                  </CFormSelect>
                </CCol>
                <CCol sm="6">
                  <CFormLabel class="form-label-modern">
                    <i class="bi bi-calendar3 me-2"></i>Date
                  </CFormLabel>
                  <CFormInput type="date" v-model="form.date" class="form-input-modern" />
                </CCol>
              </CRow>

              <CButton
                color="primary"
                type="submit"
                class="w-100 btn-submit"
                :disabled="isSubmitting"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-plus-lg me-2"></i>
                {{ isSubmitting ? 'Saving...' : 'Register Visitor' }}
              </CButton>
            </CForm>
          </CCardBody>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid mt-4">
          <div class="mini-stat animate-fadeInUp stagger-2">
            <div class="mini-stat-icon visitors">
              <i class="bi bi-people"></i>
            </div>
            <div class="mini-stat-content">
              <div class="mini-stat-value">{{ visitorCount }}</div>
              <div class="mini-stat-label">Visitors</div>
            </div>
          </div>
          <div class="mini-stat animate-fadeInUp stagger-3">
            <div class="mini-stat-icon partners">
              <i class="bi bi-star"></i>
            </div>
            <div class="mini-stat-content">
              <div class="mini-stat-value">{{ partnerCount }}</div>
              <div class="mini-stat-label">Partners</div>
            </div>
          </div>
        </div>
      </CCol>

      <!-- Recent Visitors Card -->
      <CCol lg="7">
        <div class="card hover-lift animate-fadeInUp stagger-2">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon">
                <i class="bi bi-clock-history"></i>
              </div>
              <div>
                <h5 class="mb-0 fw-semibold">Recent Visitors</h5>
                <small class="text-muted">Latest registered visitors</small>
              </div>
            </div>
            <span class="badge-count">
              <i class="bi bi-people-fill me-1"></i>
              {{ total }}
            </span>
          </div>
          <CCardBody class="p-0">
            <div v-if="isLoading" class="table-loading">
              <div v-for="i in 3" :key="i" class="skeleton-row">
                <div class="skeleton skeleton-avatar"></div>
                <div class="skeleton-content">
                  <div class="skeleton skeleton-text" style="width: 60%"></div>
                  <div class="skeleton skeleton-text" style="width: 40%"></div>
                </div>
              </div>
            </div>
            <div v-else-if="visitors.length === 0" class="empty-state">
              <div class="empty-state-icon">
                <i class="bi bi-person-x"></i>
              </div>
              <h5 class="empty-state-title">No visitors yet</h5>
              <p class="empty-state-text">Start by registering your first visitor using the form</p>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-modern">
                <thead>
                  <tr>
                    <th>Visitor</th>
                    <th>Phone</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(v, index) in visitors"
                    :key="v.id"
                    class="table-row-animated"
                    :style="{ animationDelay: `${index * 0.05}s` }"
                  >
                    <td>
                      <div class="visitor-info">
                        <div class="visitor-avatar" :class="v.category.toLowerCase()">
                          {{ getInitials(v.name) }}
                        </div>
                        <div class="visitor-name">{{ v.name || 'Unknown' }}</div>
                      </div>
                    </td>
                    <td>
                      <span class="phone-number">
                        <i class="bi bi-telephone me-1"></i>{{ v.phone }}
                      </span>
                    </td>
                    <td>
                      <span :class="['category-badge', v.category.toLowerCase()]">
                        <i :class="['bi', v.category === 'Partner' ? 'bi-star-fill' : 'bi-person']"></i>
                        {{ v.category }}
                      </span>
                    </td>
                    <td>
                      <span class="date-text">
                        <i class="bi bi-calendar3 me-1"></i>{{ formatDate(v.date) }}
                      </span>
                    </td>
                    <td class="text-end">
                      <button class="btn-action" @click="openEditVisitor(v)">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn-action btn-action-danger" @click="confirmDelete(v)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CCardBody>
        </div>
      </CCol>
    </CRow>

    <!-- Edit Modal -->
    <CModal
      :visible="editModalVisible"
      @close="editModalVisible = false"
      alignment="center"
      backdrop="static"
    >
      <div class="modal-content-modern">
        <CModalHeader class="modal-header-modern">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon">
              <i class="bi bi-pencil-square"></i>
            </div>
            <div>
              <CModalTitle class="fw-semibold">Edit Visitor</CModalTitle>
              <small class="text-muted">Update visitor information</small>
            </div>
          </div>
        </CModalHeader>
        <CModalBody class="modal-body-modern">
          <div class="form-group mb-3">
            <CFormLabel class="form-label-modern">
              <i class="bi bi-person me-2"></i>Name
            </CFormLabel>
            <CFormInput v-model="editVisitor.name" class="form-input-modern" />
          </div>
          <div class="form-group mb-3">
            <CFormLabel class="form-label-modern">
              <i class="bi bi-phone me-2"></i>Phone
            </CFormLabel>
            <CFormInput v-model="editVisitor.phone" class="form-input-modern" />
          </div>
          <div class="form-group mb-3">
            <CFormLabel class="form-label-modern">
              <i class="bi bi-tag me-2"></i>Category
            </CFormLabel>
            <CFormSelect v-model="editVisitor.category" class="form-input-modern">
              <option value="Visitor">Visitor</option>
              <option value="Partner">Partner</option>
            </CFormSelect>
          </div>
          <div class="form-group">
            <CFormLabel class="form-label-modern">
              <i class="bi bi-calendar3 me-2"></i>Date
            </CFormLabel>
            <CFormInput type="date" v-model="editVisitor.date" class="form-input-modern" />
          </div>
        </CModalBody>
        <CModalFooter class="modal-footer-modern">
          <CButton color="light" @click="editModalVisible = false">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </CButton>
          <CButton color="primary" @click="saveEditVisitor">
            <i class="bi bi-check-lg me-2"></i>Save Changes
          </CButton>
        </CModalFooter>
      </div>
    </CModal>

    <!-- Success Toast -->
    <transition name="toast-slide">
      <div v-if="showToast" class="toast-notification" :class="toastType">
        <div class="toast-icon">
          <i :class="toastType === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'"></i>
        </div>
        <div class="toast-content">
          <div class="toast-title">{{ toastTitle }}</div>
          <div class="toast-message">{{ toastMessage }}</div>
        </div>
        <button class="toast-close" @click="showToast = false">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import {
  CRow, CCol, CCardBody, CForm, CFormLabel,
  CFormInput, CFormSelect, CButton, CModal, CModalHeader,
  CModalTitle, CModalBody, CModalFooter
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel, formatDateForExport } from '../utils/export.js'

// State
const form = ref({
  name: '',
  phone: '',
  category: 'Visitor',
  date: new Date().toISOString().slice(0, 10)
})
const phoneError = ref('')
const isSubmitting = ref(false)
const isLoading = ref(false)
const visitors = ref([
  { id: 1, name: 'Ama Kofi', phone: '0241234567', category: 'Visitor', date: new Date().toISOString().slice(0, 10) },
  { id: 2, name: 'Yaw Poku', phone: '0202223333', category: 'Partner', date: new Date(Date.now() - 86400000).toISOString().slice(0, 10) }
])

// Edit Modal State
const editModalVisible = ref(false)
const editVisitor = reactive({ id: '', name: '', phone: '', category: '', date: '' })

// Toast State
const showToast = ref(false)
const toastType = ref('success')
const toastTitle = ref('')
const toastMessage = ref('')

// Computed
const total = computed(() => visitors.value.length)
const visitorCount = computed(() => visitors.value.filter(v => v.category === 'Visitor').length)
const partnerCount = computed(() => visitors.value.filter(v => v.category === 'Partner').length)

// Helper Functions
function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  if (d.toDateString() === today.toDateString()) return 'Today'
  if (d.toDateString() === yesterday.toDateString()) return 'Yesterday'

  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function showNotification(type, title, message) {
  toastType.value = type
  toastTitle.value = title
  toastMessage.value = message
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 4000)
}

// Validation
function validatePhone(phone) {
  const digits = (phone || '').replace(/\D/g, '')

  if (!phone || digits.length < 9) {
    return 'Phone number must be at least 9 digits'
  }

  if (/(.)\1{5,}/.test(digits)) {
    return 'Invalid phone number - too many repeating digits'
  }

  const lastEight = digits.slice(-8)
  if (/^(\d{2})\1{3}$/.test(lastEight)) {
    return 'Invalid phone number - repeating pattern detected'
  }

  const ghanaPrefixes = ['20', '23', '24', '25', '26', '27', '28', '29', '50', '54', '55', '56', '57', '59']

  let localNumber = digits
  if (digits.startsWith('233')) {
    localNumber = digits.slice(3)
  } else if (digits.startsWith('0')) {
    localNumber = digits.slice(1)
  }

  if (localNumber.length === 9) {
    const prefix = localNumber.slice(0, 2)
    if (!ghanaPrefixes.includes(prefix) && !phone.startsWith('+')) {
      return 'Invalid Ghana mobile number prefix'
    }
  }

  return null
}

// Actions
async function addVisitor() {
  phoneError.value = ''

  const error = validatePhone(form.value.phone)
  if (error) {
    phoneError.value = error
    return
  }

  if (!form.value.name.trim()) {
    return
  }

  isSubmitting.value = true

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 800))

  const id = visitors.value.length ? Math.max(...visitors.value.map(v => v.id)) + 1 : 1
  visitors.value = [{
    id,
    name: form.value.name,
    phone: form.value.phone,
    category: form.value.category,
    date: form.value.date
  }, ...visitors.value]

  form.value = { name: '', phone: '', category: 'Visitor', date: new Date().toISOString().slice(0, 10) }
  isSubmitting.value = false

  showNotification('success', 'Success!', 'Visitor has been registered successfully')
}

function openEditVisitor(v) {
  Object.assign(editVisitor, v)
  if (!editVisitor.date) {
    editVisitor.date = new Date().toISOString().slice(0, 10)
  }
  editModalVisible.value = true
}

function saveEditVisitor() {
  const idx = visitors.value.findIndex(vis => vis.id === editVisitor.id)
  if (idx !== -1) {
    visitors.value[idx] = { ...editVisitor }
  }
  editModalVisible.value = false
  showNotification('success', 'Updated!', 'Visitor information has been updated')
}

function confirmDelete(v) {
  if (confirm(`Are you sure you want to delete ${v.name || 'this visitor'}?`)) {
    visitors.value = visitors.value.filter(vis => vis.id !== v.id)
    showNotification('success', 'Deleted!', 'Visitor has been removed')
  }
}

function exportVisitors() {
  const columns = [
    { key: 'name', header: 'Name' },
    { key: 'phone', header: 'Phone' },
    { key: 'category', header: 'Category' },
    { key: 'date', header: 'Date', transform: (v) => formatDateForExport(v) }
  ]
  exportToExcel(visitors.value, columns, `Visitors_${new Date().toISOString().split('T')[0]}`)
  showNotification('success', 'Exported!', 'Visitors data has been exported to Excel')
}
</script>

<style scoped>
/* Header Icon */
.header-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  font-size: 1.25rem;
}

/* Badge Count */
.badge-count {
  background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}

/* Form Styles */
.form-label-modern {
  font-weight: 500;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
}

.form-input-modern {
  background: var(--bg-input);
  border: 1.5px solid var(--border-color);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  transition: all 0.2s ease;
}

.form-input-modern:hover {
  border-color: var(--primary-light);
}

.form-input-modern:focus {
  background: var(--bg-card);
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.form-input-modern.is-invalid {
  border-color: var(--danger);
}

.form-input-modern.is-invalid:focus {
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

/* Error Message */
.error-message {
  color: var(--danger);
  font-size: 0.8125rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
}

.error-fade-enter-active,
.error-fade-leave-active {
  transition: all 0.3s ease;
}

.error-fade-enter-from,
.error-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Submit Button */
.btn-submit {
  padding: 0.875rem 1.5rem;
  font-weight: 600;
  border-radius: 12px;
  background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
  border: none;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
  transition: all 0.3s ease;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Mini Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.mini-stat {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: 14px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.875rem;
  transition: all 0.3s ease;
}

.mini-stat:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.mini-stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.mini-stat-icon.visitors {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.mini-stat-icon.partners {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.mini-stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
}

.mini-stat-label {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 2px;
}

/* Table Styles */
.table-modern {
  margin: 0;
}

.table-modern thead th {
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  padding: 1rem 1.25rem;
  background: var(--bg-hover);
  border-bottom: 1px solid var(--border-color);
}

.table-modern tbody td {
  padding: 1rem 1.25rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--border-light);
}

.table-modern tbody tr:last-child td {
  border-bottom: none;
}

.table-row-animated {
  animation: fadeInUp 0.4s ease-out forwards;
  opacity: 0;
}

/* Visitor Info */
.visitor-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.visitor-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8125rem;
  font-weight: 600;
  color: white;
}

.visitor-avatar.visitor {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.visitor-avatar.partner {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.visitor-name {
  font-weight: 500;
  color: var(--text-primary);
}

/* Phone Number */
.phone-number {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

/* Category Badge */
.category-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.category-badge.visitor {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.category-badge.partner {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

/* Date Text */
.date-text {
  color: var(--text-muted);
  font-size: 0.875rem;
}

/* Action Buttons */
.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: var(--bg-hover);
  color: var(--text-secondary);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-left: 0.5rem;
}

.btn-action:hover {
  background: var(--primary);
  color: white;
  transform: scale(1.05);
}

.btn-action-danger:hover {
  background: var(--danger);
}

/* Loading Skeleton */
.table-loading {
  padding: 1rem;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid var(--border-light);
}

.skeleton-row:last-child {
  border-bottom: none;
}

.skeleton-content {
  flex: 1;
}

/* Modal Styles */
.modal-content-modern {
  border-radius: 20px;
  overflow: hidden;
}

.modal-header-modern {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-light);
}

.modal-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  font-size: 1.25rem;
}

.modal-body-modern {
  padding: 1.5rem;
}

.modal-footer-modern {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-light);
  gap: 0.75rem;
}

/* Toast Notification */
.toast-notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: var(--bg-card);
  border-radius: 14px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  max-width: 400px;
  border-left: 4px solid;
}

.toast-notification.success {
  border-left-color: var(--success);
}

.toast-notification.error {
  border-left-color: var(--danger);
}

.toast-icon {
  font-size: 1.5rem;
}

.toast-notification.success .toast-icon {
  color: var(--success);
}

.toast-notification.error .toast-icon {
  color: var(--danger);
}

.toast-title {
  font-weight: 600;
  color: var(--text-primary);
}

.toast-message {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.toast-close {
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.25rem;
  margin-left: auto;
  opacity: 0.5;
  transition: opacity 0.2s;
}

.toast-close:hover {
  opacity: 1;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-slide-enter-from,
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(100px);
}

/* Responsive */
@media (max-width: 576px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }

  .toast-notification {
    left: 1rem;
    right: 1rem;
    bottom: 5rem;
    max-width: none;
  }

  .table-modern thead th,
  .table-modern tbody td {
    padding: 0.75rem;
  }

  .visitor-avatar {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
  }
}

/* Dark Mode Adjustments */
.theme-dark .mini-stat {
  background: var(--bg-card);
  border-color: var(--border-light);
}

.theme-dark .toast-notification {
  background: var(--bg-elevated);
}
</style>
