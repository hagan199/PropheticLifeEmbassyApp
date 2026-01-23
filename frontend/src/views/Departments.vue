<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">Departments</h2>
        <Breadcrumbs />
        <div class="text-muted">Organize ministry units and members</div>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" @click="exportDepartments">
          <i class="bi bi-file-earmark-excel me-1"></i> Export
        </CButton>
        <CButton color="primary" @click="addDept">Add</CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol md="6">
        <CCard>
          <CCardHeader class="fw-semibold">Ministry Units</CCardHeader>
          <CCardBody>
            <CListGroup>
              <CListGroupItem v-for="d in depts" :key="d.id" class="d-flex justify-content-between align-items-center">
                {{ d.name }}
                <CBadge color="secondary">{{ d.members }} members</CBadge>
              </CListGroupItem>
            </CListGroup>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol md="6">
        <CCard>
          <CCardHeader class="fw-semibold">Create Department</CCardHeader>
          <CCardBody>
            <CForm @submit.prevent="create">
              <CFormLabel>Name</CFormLabel>
              <CFormInput v-model="name" />
              <div class="mt-3 d-flex justify-content-end">
                <CButton color="success" type="submit">Save</CButton>
              </div>
            </CForm>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CButton, CBadge, CListGroup, CListGroupItem } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'

const depts = ref([
  { id: 1, name: 'Media', members: 12 },
  { id: 2, name: 'Prayer Team', members: 18 },
  { id: 3, name: 'Welfare', members: 9 }
])
const name = ref('')
function create() {
  if (!name.value.trim()) return
  const id = depts.value.length ? depts.value[depts.value.length - 1].id + 1 : 1
  depts.value = depts.value.concat([{ id, name: name.value.trim(), members: 0 }])
  name.value = ''
}
function addDept() { name.value = '' }

function exportDepartments() {
  const columns = [
    { key: 'name', header: 'Department Name' },
    { key: 'members', header: 'Members Count' }
  ]
  exportToExcel(depts.value, columns, `Departments_${new Date().toISOString().split('T')[0]}`)
}
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}
</style>
