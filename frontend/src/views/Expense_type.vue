<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">Expense Types</h2>
        <Breadcrumbs />
        <div class="text-muted">Manage expense categories</div>
      </div>
      <div class="d-flex">
        <CFormInput v-model="newCat" placeholder="New category" class="me-2" />
        <CButton color="success" @click="addCat">Add</CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol lg="8">
        <CCard>
          <CCardHeader class="fw-semibold">Categories</CCardHeader>
          <CCardBody>
            <CListGroup>
              <CListGroupItem v-for="c in cats" :key="c.id" class="d-flex justify-content-between align-items-center">
                <span>{{ c.name }}</span>
                <div>
                  <CButton color="light" size="sm" class="me-2" @click="renameCat(c.id)">Rename</CButton>
                  <CButton color="danger" size="sm" @click="deleteCat(c.id)">Delete</CButton>
                </div>
              </CListGroupItem>
            </CListGroup>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol lg="4">
        <CCard>
          <CCardHeader class="fw-semibold">Notes</CCardHeader>
          <CCardBody>
            <div class="text-muted">These categories appear in the Expense page.</div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CListGroup, CListGroupItem, CButton, CFormInput } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'

const newCat = ref('')
const cats = ref([])
function loadCats() {
  const raw = localStorage.getItem('expense_categories')
  const defaults = [
    { id: 1, name: 'Utilities' },
    { id: 2, name: 'Welfare' },
    { id: 3, name: 'Maintenance' },
    { id: 4, name: 'Outreach' }
  ]
  try {
    cats.value = raw ? JSON.parse(raw) : defaults
  } catch {
    cats.value = defaults
  }
}
function saveCats() { localStorage.setItem('expense_categories', JSON.stringify(cats.value)) }
function addCat() {
  if (!newCat.value.trim()) return
  const id = cats.value.length ? cats.value[cats.value.length - 1].id + 1 : 1
  cats.value = cats.value.concat([{ id, name: newCat.value.trim() }])
  newCat.value = ''
  saveCats()
}
function renameCat(id) {
  const c = cats.value.find(x => x.id === id)
  if (!c) return
  const nn = prompt('Rename category', c.name)
  if (nn && nn.trim()) { c.name = nn.trim(); saveCats() }
}
function deleteCat(id) { cats.value = cats.value.filter(c => c.id !== id); saveCats() }
onMounted(loadCats)
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}
</style>
