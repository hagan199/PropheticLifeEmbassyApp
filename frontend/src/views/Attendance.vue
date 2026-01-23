<template>
  <div class="page-wrap">
    <CAlert v-if="showNotification" color="info" class="mb-3" dismissible>
      {{ notificationMessage }}
    </CAlert>
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">Attendance</h2>
        <Breadcrumbs />
        <div class="text-muted">Record and track service attendance</div>
      </div>
      <div>
        <CButton color="primary" @click="exportAttendance">
          <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol md="6">
        <CCard>
          <CCardHeader class="fw-semibold">Record Attendance</CCardHeader>
          <CCardBody>
            <CForm @submit.prevent="addEntry">
              <CRow class="g-3">
                <CCol md="6">
                  <CFormLabel>Service</CFormLabel>
                  <CFormSelect v-model="form.service">
                    <option value="Sunday">Sunday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Special">Special</option>
                  </CFormSelect>
                </CCol>
                <CCol md="6">
                  <CFormLabel>Date</CFormLabel>
                  <CFormInput type="date" v-model="form.date" />
                </CCol>
                <CCol md="6">
                  <CFormLabel>Count</CFormLabel>
                  <CFormInput type="number" min="0" v-model.number="form.count" />
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
            <div class="fw-semibold">This Week</div>
            <CBadge color="primary">Total {{ weeklyTotal }}</CBadge>
          </CCardHeader>
          <CCardBody>
            <CTable hover responsive>
              <CTableHead>
                <CTableRow>
                  <CTableHeaderCell scope="col">Service</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Date</CTableHeaderCell>
                  <CTableHeaderCell scope="col" class="text-end">Count</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="e in entries" :key="e.id">
                  <CTableDataCell>{{ e.service }}</CTableDataCell>
                  <CTableDataCell>{{ e.date }}</CTableDataCell>
                  <CTableDataCell class="text-end">{{ e.count }}</CTableDataCell>
                  <CTableDataCell v-if="auth.user && auth.user.role === 'Super Admin'">
                    <CButton v-if="!e.approved" color="primary" size="sm" @click="approveEntry(e.id)">Approve</CButton>
                    <CBadge v-else color="success">Approved</CBadge>
                  </CTableDataCell>
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
import { ref, computed } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CFormSelect, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CAlert } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'

import { useAuthStore } from '../store/auth'
const auth = useAuthStore()
const form = ref({ service: 'Sunday', date: new Date().toISOString().slice(0, 10), count: 0 })
const entries = ref([
  { id: 1, service: 'Sunday', date: new Date().toISOString().slice(0, 10), count: 120, approved: false },
  { id: 2, service: 'Wednesday', date: new Date(Date.now() - 86400000).toISOString().slice(0, 10), count: 85, approved: false }
])
const notificationMessage = ref('')
const showNotification = ref(false)
function addEntry() {
  const id = entries.value.length ? entries.value[entries.value.length - 1].id + 1 : 1
  entries.value = entries.value.concat([{ id, service: form.value.service, date: form.value.date, count: form.value.count, approved: false }])
  form.value = { service: 'Sunday', date: new Date().toISOString().slice(0, 10), count: 0 }
  notificationMessage.value = 'Attendance entry added successfully!'
  showNotification.value = true
  setTimeout(() => { showNotification.value = false }, 2500)
}
function approveEntry(id) {
  const entry = entries.value.find(e => e.id === id)
  if (entry) entry.approved = true
}

function exportAttendance() {
  const columns = [
    { key: 'date', label: 'Date' },
    { key: 'service', label: 'Service Type' },
    { key: 'count', label: 'Attendance Count' },
    { key: 'status', label: 'Status' }
  ]

  const exportData = entries.value.map(e => ({
    ...e,
    status: e.approved ? 'Approved' : 'Pending'
  }))

  exportToExcel(exportData, columns, 'attendance_report')
  notificationMessage.value = `Exported ${exportData.length} records to Excel`
  showNotification.value = true
  setTimeout(() => { showNotification.value = false }, 2500)
}

const weeklyTotal = computed(() => entries.value.reduce((a, b) => a + (b.count || 0), 0))
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
