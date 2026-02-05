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
        <CButton color="primary" @click="showCreateModal = true">Add</CButton>
      </div>
    </div>

    <CRow class="g-4">
      <CCol md="6">
        <CCard>
          <CCardHeader class="fw-semibold">Ministry Units</CCardHeader>
          <CCardBody>
            <CListGroup>
              <CListGroupItem 
                v-for="d in depts" 
                :key="d.id" 
                class="d-flex justify-content-between align-items-center dept-item"
                @click="viewDepartment(d)"
                style="cursor: pointer;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-people-fill me-2 text-primary"></i>
                  {{ d.name }}
                </div>
                <div class="d-flex align-items-center gap-2">
                  <CBadge color="secondary">{{ d.member_count || d.members }} members</CBadge>
                  <CButton color="light" size="sm" @click.stop="editDepartment(d)">
                    <i class="bi bi-pencil"></i>
                  </CButton>
                  <CButton color="danger" size="sm" variant="ghost" @click.stop="deleteDepartment(d)">
                    <i class="bi bi-trash"></i>
                  </CButton>
                </div>
              </CListGroupItem>
              <CListGroupItem v-if="depts.length === 0" class="text-center text-muted py-4">
                <i class="bi bi-folder-x fs-3 d-block mb-2"></i>
                No departments found
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
              <CFormInput v-model="name" placeholder="Enter department name" />
              <CFormLabel class="mt-3">Description</CFormLabel>
              <CFormTextarea v-model="description" placeholder="Department description" rows="3" />
              <div class="mt-3 d-flex justify-content-end">
                <CButton color="success" type="submit">Save</CButton>
              </div>
            </CForm>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

    <!-- View Department Members Modal -->
    <CModal :visible="showMembersModal" @close="showMembersModal = false" size="lg">
      <CModalHeader>
        <CModalTitle>
          <i class="bi bi-people me-2"></i>{{ selectedDept?.name }} - Members
        </CModalTitle>
      </CModalHeader>
      <CModalBody>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <CFormInput 
            v-model="memberSearch" 
            placeholder="Search members..." 
            class="w-50"
          />
          <CButton color="primary" size="sm" @click="showAddMemberModal = true">
            <i class="bi bi-person-plus me-1"></i> Add Member
          </CButton>
        </div>
        <CTable hover responsive>
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>#</CTableHeaderCell>
              <CTableHeaderCell>Name</CTableHeaderCell>
              <CTableHeaderCell>Phone</CTableHeaderCell>
              <CTableHeaderCell>Role</CTableHeaderCell>
              <CTableHeaderCell>Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="(member, idx) in filteredMembers" :key="member.id">
              <CTableDataCell>{{ idx + 1 }}</CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <CAvatar size="sm" color="primary" class="me-2">
                    {{ member.name?.charAt(0) }}
                  </CAvatar>
                  {{ member.name }}
                </div>
              </CTableDataCell>
              <CTableDataCell>{{ member.phone }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="member.role === 'Leader' ? 'success' : member.role === 'Assistant' ? 'info' : 'secondary'">
                  {{ member.role }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <CButton color="danger" size="sm" variant="ghost" @click="removeMember(member)">
                  <i class="bi bi-person-x"></i>
                </CButton>
              </CTableDataCell>
            </CTableRow>
            <CTableRow v-if="filteredMembers.length === 0">
              <CTableDataCell colspan="5" class="text-center text-muted py-4">
                <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                No members found
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showMembersModal = false">Close</CButton>
      </CModalFooter>
    </CModal>

    <!-- Edit Department Modal -->
    <CModal :visible="showEditModal" @close="showEditModal = false">
      <CModalHeader>
        <CModalTitle>Edit Department</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CFormLabel>Name</CFormLabel>
        <CFormInput v-model="editForm.name" />
        <CFormLabel class="mt-3">Description</CFormLabel>
        <CFormTextarea v-model="editForm.description" rows="3" />
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showEditModal = false">Cancel</CButton>
        <CButton color="primary" @click="saveEdit">Save Changes</CButton>
      </CModalFooter>
    </CModal>

    <!-- Add Member Modal -->
    <CModal :visible="showAddMemberModal" @close="showAddMemberModal = false">
      <CModalHeader>
        <CModalTitle>Add Member to {{ selectedDept?.name }}</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CFormLabel>Select Member</CFormLabel>
        <CFormSelect v-model="newMemberId">
          <option value="">Choose a member...</option>
          <option v-for="user in availableUsers" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </CFormSelect>
        <CFormLabel class="mt-3">Role in Department</CFormLabel>
        <CFormSelect v-model="newMemberRole">
          <option value="Member">Member</option>
          <option value="Assistant">Assistant</option>
          <option value="Leader">Leader</option>
        </CFormSelect>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showAddMemberModal = false">Cancel</CButton>
        <CButton color="primary" @click="addMember">Add Member</CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  CRow, CCol, CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CFormTextarea, CFormSelect,
  CButton, CBadge, CListGroup, CListGroupItem, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CAvatar
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { exportToExcel } from '../utils/export.js'
import { departmentsApi } from '../api/departments'
import { usersApi } from '../api/users'

const depts = ref([])
const name = ref('')
const description = ref('')
const loading = ref(false)

// Modals
const showMembersModal = ref(false)
const showEditModal = ref(false)
const showAddMemberModal = ref(false)
const showCreateModal = ref(false)

// Selected department
const selectedDept = ref(null)
const deptMembers = ref([])
const memberSearch = ref('')

// Edit form
const editForm = ref({ id: '', name: '', description: '' })

// Add member
const availableUsers = ref([])
const newMemberId = ref('')
const newMemberRole = ref('Member')

const filteredMembers = computed(() => {
  if (!memberSearch.value) return deptMembers.value
  const search = memberSearch.value.toLowerCase()
  return deptMembers.value.filter(m => 
    m.name?.toLowerCase().includes(search) || 
    m.phone?.includes(search)
  )
})

async function fetchDepartments() {
  loading.value = true
  try {
    const response = await departmentsApi.getAll()
    if (response.data.success) {
      depts.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch departments:', error)
    // Fallback to sample data
    depts.value = [
      { id: 1, name: 'Media', member_count: 12 },
      { id: 2, name: 'Prayer Team', member_count: 18 },
      { id: 3, name: 'Welfare', member_count: 9 }
    ]
  } finally {
    loading.value = false
  }
}

async function fetchUsers() {
  try {
    const response = await usersApi.getAll()
    if (response.data.success) {
      availableUsers.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

async function viewDepartment(dept) {
  selectedDept.value = dept
  showMembersModal.value = true
  try {
    const response = await departmentsApi.getMembers(dept.id)
    if (response.data.success) {
      deptMembers.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch members:', error)
    // Fallback sample members
    deptMembers.value = [
      { id: 1, name: 'Emmanuel Agyei', phone: '+233241111111', role: 'Leader' },
      { id: 2, name: 'Grace Addo', phone: '+233241111112', role: 'Assistant' },
      { id: 3, name: 'Peter Asare', phone: '+233241111117', role: 'Member' },
    ]
  }
}

function editDepartment(dept) {
  editForm.value = { id: dept.id, name: dept.name, description: dept.description || '' }
  showEditModal.value = true
}

async function saveEdit() {
  try {
    await departmentsApi.update(editForm.value.id, {
      name: editForm.value.name,
      description: editForm.value.description
    })
    const idx = depts.value.findIndex(d => d.id === editForm.value.id)
    if (idx !== -1) {
      depts.value[idx].name = editForm.value.name
      depts.value[idx].description = editForm.value.description
    }
    showEditModal.value = false
  } catch (error) {
    console.error('Failed to update department:', error)
  }
}

async function deleteDepartment(dept) {
  if (!confirm(`Are you sure you want to delete "${dept.name}"?`)) return
  try {
    await departmentsApi.delete(dept.id)
    depts.value = depts.value.filter(d => d.id !== dept.id)
  } catch (error) {
    console.error('Failed to delete department:', error)
  }
}

async function create() {
  if (!name.value.trim()) return
  try {
    const response = await departmentsApi.create({ 
      name: name.value.trim(),
      description: description.value.trim()
    })
    if (response.data.success) {
      depts.value.push(response.data.data)
    }
    name.value = ''
    description.value = ''
  } catch (error) {
    console.error('Failed to create department:', error)
    // Fallback local add
    const id = depts.value.length ? Math.max(...depts.value.map(d => d.id)) + 1 : 1
    depts.value.push({ id, name: name.value.trim(), member_count: 0 })
    name.value = ''
    description.value = ''
  }
}

async function addMember() {
  if (!newMemberId.value) return
  try {
    await departmentsApi.addMember(selectedDept.value.id, {
      user_id: newMemberId.value,
      role: newMemberRole.value
    })
    const user = availableUsers.value.find(u => u.id === newMemberId.value)
    if (user) {
      deptMembers.value.push({
        id: user.id,
        name: user.name,
        phone: user.phone,
        role: newMemberRole.value
      })
    }
    showAddMemberModal.value = false
    newMemberId.value = ''
    newMemberRole.value = 'Member'
  } catch (error) {
    console.error('Failed to add member:', error)
  }
}

async function removeMember(member) {
  if (!confirm(`Remove ${member.name} from this department?`)) return
  try {
    await departmentsApi.removeMember(selectedDept.value.id, member.id)
    deptMembers.value = deptMembers.value.filter(m => m.id !== member.id)
  } catch (error) {
    console.error('Failed to remove member:', error)
  }
}

function exportDepartments() {
  const columns = [
    { key: 'name', header: 'Department Name' },
    { key: 'member_count', header: 'Members Count' }
  ]
  exportToExcel(depts.value, columns, `Departments_${new Date().toISOString().split('T')[0]}`)
}

onMounted(() => {
  fetchDepartments()
  fetchUsers()
})
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.dept-item:hover {
  background-color: rgba(var(--cui-primary-rgb), 0.05);
}
</style>
