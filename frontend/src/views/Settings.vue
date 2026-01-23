<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="title">Settings</h2>
        <Breadcrumbs />
        <div class="text-muted">Configure expense categories, roles, and permissions</div>
      </div>
    </div>

    <div class="mb-3 d-flex gap-2">
      <CButton :color="tab === 'categories' ? 'primary' : 'light'" size="sm" @click="tab = 'categories'">Expense
        Categories</CButton>
      <CButton :color="tab === 'roles' ? 'primary' : 'light'" size="sm" @click="tab = 'roles'">Roles</CButton>
      <CButton :color="tab === 'permissions' ? 'primary' : 'light'" size="sm" @click="tab = 'permissions'">Permissions
      </CButton>
      <CButton :color="tab === 'serviceType' ? 'primary' : 'light'" size="sm" @click="tab = 'serviceType'">Service</CButton>
    </div>

    
    <div v-if="tab === 'service'">
      <CRow class="g-4">
        <CCol lg="6">
          <CCard>
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Service Type</div>
              <div class="d-flex">
                <CFormInput v-model="catName" placeholder="New category" class="me-2" />
                <CButton color="success" @click="addCategory">Add</CButton>
              </div>
            </CCardHeader>
            <CCardBody>
              <CListGroup>
                <CListGroupItem v-for="c in serviceTypes" :key="c.id"
                  class="d-flex justify-content-between align-items-center">
                  <span>{{ c.name }}</span>
                  <div>
                    <CButton color="light" size="sm" class="me-2" @click="renameCategory(c.id)">Rename</CButton>
                    <CButton color="danger" size="sm" @click="removeCategory(c.id)">Delete</CButton>
                  </div>
                </CListGroupItem>
              </CListGroup>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol lg="6">
          <CCard>
            <CCardHeader class="fw-semibold">Tips</CCardHeader>
            <CCardBody>
              <div class="text-muted">Use clear names like Utilities, Welfare, Maintenance, Outreach.</div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </div>

    <div v-else-if="tab === 'roles'">
      <CRow class="g-4">
        <CCol lg="6">
          <CCard>
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Roles</div>
              <div class="d-flex">
                <CFormInput v-model="roleName" placeholder="New role name" class="me-2" />
                <CButton color="success" @click="addRole">Add</CButton>
              </div>
            </CCardHeader>
            <CCardBody>
              <CListGroup>
                <CListGroupItem v-for="r in roles" :key="r.id" class="d-flex justify-content-between align-items-center"
                  :class="{ active: selectedRole && selectedRole.id === r.id }" @click="selectRole(r)">
                  <span>{{ r.name }}</span>
                  <CBadge color="secondary">{{ r.permissions.length }} perms</CBadge>
                </CListGroupItem>
              </CListGroup>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol lg="6">
          <CCard>
            <CCardHeader class="fw-semibold">Role Permissions (Preview)</CCardHeader>
            <CCardBody>
              <div v-if="!selectedRole" class="text-muted">Select a role to preview its permissions.</div>
              <div v-else>
                <ul class="mb-0">
                  <li v-for="p in selectedRole.permissions" :key="p">{{ labelFor(p) }}</li>
                </ul>
              </div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </div>

    <div v-else>
      <CRow class="g-4">
        <CCol lg="5">
          <CCard>
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Users</div>
              <div class="d-flex">
                <CFormInput v-model="userSearch" placeholder="Search users" />
              </div>
            </CCardHeader>
            <CCardBody>
              <CListGroup>
                <CListGroupItem v-for="u in filteredUsers" :key="u.id"
                  class="d-flex justify-content-between align-items-center"
                  :class="{ active: selectedUser && selectedUser.id === u.id }" @click="selectUser(u)">
                  <span>{{ u.name }}</span>
                  <CBadge color="secondary">{{ roleNameById(u.roleId) }}</CBadge>
                </CListGroupItem>
              </CListGroup>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol lg="7">
          <CCard>
            <CCardHeader class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Permissions</div>
              <div class="text-muted" v-if="selectedUser">Editing: {{ selectedUser.name }}</div>
            </CCardHeader>
            <CCardBody>
              <div v-if="!selectedUser" class="text-muted">Select a user to configure permissions</div>
              <div v-else>
                <CRow class="g-3 mb-2">
                  <CCol md="6">
                    <CFormLabel>Role</CFormLabel>
                    <CFormSelect v-model="selectedRoleId" @change="applyRole">
                      <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </CFormSelect>
                  </CCol>
                </CRow>
                <CRow class="g-3">
                  <CCol md="6" v-for="p in permissions" :key="p">
                    <CFormCheck :id="'perm-' + p" :label="labelFor(p)" :checked="hasUserPerm(p)"
                      @change="toggleUserPerm(p)" />
                  </CCol>
                </CRow>
                <div class="mt-3 d-flex justify-content-end">
                  <CButton color="primary" @click="saveUser">Save</CButton>
                </div>
              </div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { CRow, CCol, CCard, CCardBody, CCardHeader, CListGroup, CListGroupItem, CBadge, CButton, CFormInput, CFormCheck, CFormLabel, CFormSelect } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'

const tab = ref('categories')

// Expense categories
import { onMounted } from 'vue'
const categories = ref([])
const catName = ref('')
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
function saveCategories() { localStorage.setItem('expense_categories', JSON.stringify(categories.value)) }
function addCategory() {
  if (!catName.value.trim()) return
  const id = categories.value.length ? categories.value[categories.value.length - 1].id + 1 : 1
  categories.value = categories.value.concat([{ id, name: catName.value.trim() }])
  catName.value = ''
  saveCategories()
}
function removeCategory(id) { categories.value = categories.value.filter(c => c.id !== id); saveCategories() }
function renameCategory(id) {
  const c = categories.value.find(x => x.id === id)
  if (!c) return
  const nn = prompt('Rename category', c.name)
  if (nn && nn.trim()) { c.name = nn.trim(); saveCategories() }
}
onMounted(loadCategories)

// Roles
const roles = ref([
  { id: 1, name: 'Pastor', permissions: ['attendance.read', 'attendance.write', 'finance.read', 'visitors.read'] },
  { id: 2, name: 'Finance', permissions: ['finance.read', 'finance.write', 'expenses.read', 'expenses.write'] },
  { id: 3, name: 'Usher', permissions: ['attendance.read', 'visitors.read'] }
])
const permissions = ref([
  'attendance.read', 'attendance.write',
  'visitors.read', 'visitors.write',
  'finance.read', 'finance.write',
  'expenses.read', 'expenses.write',
  'departments.read', 'departments.write',
  'appointments.read', 'appointments.write',
  'membership.read', 'membership.write',
  'roles.manage'
])
const selectedRole = ref(null)
const roleName = ref('')
function selectRole(r) { selectedRole.value = { ...r, permissions: [...r.permissions] } }
function addRole() {
  if (!roleName.value.trim()) return
  const id = roles.value.length ? roles.value[roles.value.length - 1].id + 1 : 1
  roles.value = roles.value.concat([{ id, name: roleName.value.trim(), permissions: [] }])
  roleName.value = ''
}
function labelFor(p) {
  return p
    .replace('.read', ' — View')
    .replace('.write', ' — Manage')
    .replace('roles.manage', 'Roles — Manage')
}
function hasPerm(p) { return selectedRole.value?.permissions.includes(p) }
function togglePerm(p) {
  if (!selectedRole.value) return
  const perms = new Set(selectedRole.value.permissions)
  if (perms.has(p)) perms.delete(p)
  else perms.add(p)
  selectedRole.value.permissions = Array.from(perms)
}
function savePerms() {
  if (!selectedRole.value) return
  roles.value = roles.value.map(r => r.id === selectedRole.value.id ? { ...selectedRole.value } : r)
}

// Users and permissions assignment
const users = ref([
  { id: 1, name: 'Admin User', roleId: 1, perms: ['attendance.read', 'visitors.read', 'finance.read'] },
  { id: 2, name: 'Finance Officer', roleId: 2, perms: ['finance.read', 'finance.write', 'expenses.read'] },
  { id: 3, name: 'Usher Team Lead', roleId: 3, perms: ['attendance.read', 'visitors.read'] }
])
const selectedUser = ref(null)
const selectedRoleId = ref(null)
const userSearch = ref('')
const filteredUsers = computed(() => users.value.filter(u => u.name.toLowerCase().includes(userSearch.value.toLowerCase())))
function roleNameById(id) { const r = roles.value.find(r => r.id === id); return r ? r.name : '—' }
function selectUser(u) { selectedUser.value = { ...u, perms: [...u.perms] }; selectedRoleId.value = u.roleId }
function applyRole() {
  if (!selectedUser.value) return
  const role = roles.value.find(r => r.id === selectedRoleId.value)
  if (role) selectedUser.value.perms = [...role.permissions]
}
function hasUserPerm(p) { return selectedUser.value?.perms.includes(p) }
function toggleUserPerm(p) {
  if (!selectedUser.value) return
  const perms = new Set(selectedUser.value.perms)
  if (perms.has(p)) perms.delete(p)
  else perms.add(p)
  selectedUser.value.perms = Array.from(perms)
}
function saveUser() {
  if (!selectedUser.value) return
  users.value = users.value.map(u => u.id === selectedUser.value.id ? { ...selectedUser.value, roleId: selectedRoleId.value } : u)
}
</script>

<style scoped>
.settings-tabs {
  display: flex;
  gap: 8px;
}
@media (max-width: 576px) {
  .settings-tabs {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 6px;
  }
  .settings-tabs::-webkit-scrollbar {
    height: 0;
  }
}
</style>
<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 16px;
}

.list-group-item.active {
  background: rgba(13, 110, 253, .12);
  color: #0d6efd;
}
</style>
