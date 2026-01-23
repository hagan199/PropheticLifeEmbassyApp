<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">Visitors</h2>
        <Breadcrumbs />
        <div class="text-muted">Register first-time visitors and partners</div>
      </div>
      <div>
        <CButton color="light" @click="exportVisitors">
          <i class="bi bi-file-earmark-excel me-1"></i> Export
        </CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol md="6">
        <CCard>
          <CCardHeader class="fw-semibold">Register Visitor</CCardHeader>
          <CCardBody>
            <CForm @submit.prevent="addVisitor">
              <CRow class="g-3">
                <CCol md="6">
                  <CFormLabel>Name</CFormLabel>
                  <CFormInput v-model="form.name" />
                </CCol>
                <CCol md="6">
                  <CFormLabel>Phone</CFormLabel>
                  <CFormInput v-model="form.phone" />
                </CCol>
                <CCol md="6">
                  <CFormLabel>Category</CFormLabel>
                  <CFormSelect v-model="form.category">
                    <option value="Visitor">Visitor</option>
                    <option value="Partner">Partner</option>
                  </CFormSelect>
                </CCol>
                <CCol md="6">
                  <CFormLabel>Date</CFormLabel>
                  <CFormInput type="date" v-model="form.date" />
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
            <CBadge color="info">Total {{ total }}</CBadge>
          </CCardHeader>
          <CCardBody>
            <CTable hover responsive>
              <CTableHead>
                <CTableRow>
                  <CTableHeaderCell scope="col">Name</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Phone</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Category</CTableHeaderCell>
                  <CTableHeaderCell scope="col" class="text-end">Date</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="v in visitors" :key="v.id">
                  <CTableDataCell>{{ v.name }}</CTableDataCell>
                  <CTableDataCell>{{ v.phone }}</CTableDataCell>
                  <CTableDataCell>
                    <CBadge :color="v.category === 'Partner' ? 'success' : 'secondary'">{{ v.category }}</CBadge>
                  </CTableDataCell>
                  <CTableDataCell class="text-end">{{ v.date }}</CTableDataCell>
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
import { CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CFormSelect, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel, formatDateForExport } from '../utils/export.js'

const form = ref({ name: '', phone: '', category: 'Visitor', date: new Date().toISOString().slice(0, 10) })
const visitors = ref([
  { id: 1, name: 'Ama K.', phone: '0241234567', category: 'Visitor', date: new Date().toISOString().slice(0, 10) },
  { id: 2, name: 'Yaw P.', phone: '0202223333', category: 'Partner', date: new Date(Date.now() - 86400000).toISOString().slice(0, 10) }
])
function addVisitor() {
  const id = visitors.value.length ? visitors.value[visitors.value.length - 1].id + 1 : 1
  visitors.value = visitors.value.concat([{ id, name: form.value.name, phone: form.value.phone, category: form.value.category, date: form.value.date }])
  form.value = { name: '', phone: '', category: 'Visitor', date: new Date().toISOString().slice(0, 10) }
}
const total = computed(() => visitors.value.length)

function exportVisitors() {
  const columns = [
    { key: 'name', header: 'Name' },
    { key: 'phone', header: 'Phone' },
    { key: 'category', header: 'Category' },
    { key: 'date', header: 'Date', transform: (v) => formatDateForExport(v) }
  ]
  exportToExcel(visitors.value, columns, `Visitors_${new Date().toISOString().split('T')[0]}`)
}
</script>

<style scoped>
.page-wrap { padding: 20px; }
.page-header { margin-bottom: 16px; }
@media (max-width: 576px) {
  .page-header .btn { width: 100%; }
  :deep(.form-control), :deep(select.form-select) { font-size: .95rem; }
  :deep(.table) { font-size: .92rem; }
}
</style>
