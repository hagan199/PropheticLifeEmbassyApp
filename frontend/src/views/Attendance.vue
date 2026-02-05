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
          <CCardHeader class="fw-semibold">Record Ministry Unit Attendance</CCardHeader>
          <CCardBody>
            <CForm @submit.prevent="addEntry">
              <CRow class="g-3">
                <CCol md="6">
                  <CFormLabel>Ministry Unit</CFormLabel>
                  <CFormSelect v-model="form.unit">
                    <option v-for="unit in ministryUnits" :key="unit" :value="unit">{{ unit }}</option>
                  </CFormSelect>
                </CCol>
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
                  <CFormLabel>Time</CFormLabel>
                  <CFormInput type="time" v-model="form.time" />
                </CCol>
              </CRow>
              <div class="mt-3">
                <div class="fw-semibold mb-2">Mark Attendance</div>
                <div v-for="member in members" :key="member.id" class="d-flex align-items-center mb-2 gap-2">
                  <span>{{ member.name }}</span>
                  <CButton :color="form.present[member.id] ? 'success' : 'secondary'" size="sm"
                    @click="togglePresent(member.id)">
                    {{ form.present[member.id] ? 'Present' : 'Absent' }}
                  </CButton>
                </div>
              </div>
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
                  <CTableHeaderCell scope="col">Unit</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Service</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Date</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Time</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Member</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow v-for="e in entries" :key="e.id">
                  <CTableDataCell>{{ e.unit }}</CTableDataCell>
                  <CTableDataCell>{{ e.service }}</CTableDataCell>
                  <CTableDataCell>{{ e.date }}</CTableDataCell>
                  <CTableDataCell>{{ e.time }}</CTableDataCell>
                  <CTableDataCell>{{ e.memberName }}</CTableDataCell>
                  <CTableDataCell>
                    <CBadge :color="e.present ? 'success' : 'danger'">{{ e.present ? 'Present' : 'Absent' }}</CBadge>
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
import { ref, computed, onMounted, watch } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CFormSelect, CButton, CBadge, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CAlert } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'
import { attendanceApi } from '../api/attendance.js'
import { ministryApi } from '../api/ministry.js'

import { useAuthStore } from '../store/auth'
const auth = useAuthStore()
const ministryUnits = ref([])
const members = ref([])

onMounted(async () => {
  // Fetch ministry units from API
  const res = await ministryApi.getUnits()
  ministryUnits.value = res.data?.data || []
  // Fetch members for first unit
  if (ministryUnits.value.length) {
    await fetchMembers(ministryUnits.value[0].id)
    form.value.unit = ministryUnits.value[0].id
  }
})

watch(() => form.value.unit, async (unitId) => {
  if (unitId) await fetchMembers(unitId)
})

async function fetchMembers(unitId) {
  const res = await ministryApi.getUnitMembers(unitId)
  members.value = res.data?.data || []
}

const form = ref({ unit: ministryUnits.value[0], service: 'Sunday', date: new Date().toISOString().slice(0, 10), time: '09:00', present: {} })
const entries = ref([])
const notificationMessage = ref('')
const showNotification = ref(false)
async function addEntry() {
  const idStart = entries.value.length ? entries.value[entries.value.length - 1].id + 1 : 1
  let added = 0
  for (const member of members.value) {
    if (form.value.present[member.id] !== undefined) {
      // Save to backend
      try {
        await attendanceApi.createUnitAttendance({
          unit: form.value.unit,
          service: form.value.service,
          date: form.value.date,
          time: form.value.time,
          member_id: member.id,
          member_name: member.name,
          present: !!form.value.present[member.id]
        })
      } catch (err) {
        // Optionally handle error
      }
      entries.value.push({
        id: idStart + added,
        unit: form.value.unit,
        service: form.value.service,
        date: form.value.date,
        time: form.value.time,
        memberId: member.id,
        memberName: member.name,
        present: !!form.value.present[member.id]
      })
      added++
    }
  }
  // Reset form
  form.value = { unit: ministryUnits.value[0], service: 'Sunday', date: new Date().toISOString().slice(0, 10), time: '09:00', present: {} }
  notificationMessage.value = 'Attendance recorded!'
  showNotification.value = true
  setTimeout(() => { showNotification.value = false }, 2500)
}

function togglePresent(memberId) {
  form.value.present[memberId] = !form.value.present[memberId]
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
