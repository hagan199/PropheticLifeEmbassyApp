<template>
  <Teleport to="body">
    <CModal :visible="props.visible" alignment="center" backdrop="static" class="modal-bottom-sheet user-modal"
      size="lg" @close="$emit('close')">
      <div class="modal-header-custom enhanced">
        <div class="modal-header-icon" :class="props.isEditing ? 'edit' : 'add'">
          <i :class="props.isEditing ? 'bi bi-pencil-square' : 'bi bi-person-plus-fill'"></i>
        </div>
        <div>
          <h5 class="modal-header-title gradient-text">{{ props.isEditing ? 'Edit User' : 'Add New User' }}</h5>
          <p class="modal-header-sub">{{ subtitle }}</p>
        </div>
        <button class="modal-close-btn" @click="$emit('close')"><i class="bi bi-x-lg"></i></button>
      </div>

      <CModalBody class="modal-body-enhanced">
        <CForm @submit.prevent="$emit('save')">
          <div class="form-section section-divider">
            <div class="form-section-label"><i class="bi bi-person me-2"></i>Basic Information</div>
            <CRow class="g-4">
              <CCol md="4">
                <label class="form-field-label">Full Name <span class="req">*</span></label>
                <CFormInput :value="props.form.name" @input="$emit('update-field', { field: 'name', value: $event })"
                  placeholder="Enter full name" :invalid="!!props.errors.name" />
                <div v-if="props.errors.name" class="field-error">{{ props.errors.name }}</div>
              </CCol>
              <CCol md="4">
                <label class="form-field-label">Phone <span class="req">*</span></label>
                <CInputGroup>
                  <CInputGroupText class="phone-prefix"><i class="bi bi-telephone-fill"></i>&nbsp;+233</CInputGroupText>
                  <CFormInput :value="phoneDisplay" @input="onPhoneInput" :disabled="props.isEditing"
                    placeholder="24 123 4567" inputmode="tel" maxlength="10" :aria-invalid="!!props.errors.phone"
                    :aria-describedby="props.errors.phone ? 'phone-error' : 'phone-hint'"
                    :invalid="!!props.errors.phone" />
                </CInputGroup>
                <div v-if="props.errors.phone" id="phone-error" class="field-error">{{ props.errors.phone }}</div>
                <div v-else id="phone-hint" class="field-hint">Enter local number (without country code). e.g., 24 123
                  4567</div>
              </CCol>
              <CCol md="6">
                <label class="form-field-label">Email</label>
                <CFormInput :value="props.form.email" @input="$emit('update-field', { field: 'email', value: $event })"
                  type="email" placeholder="user@example.com" />
              </CCol>
              <CCol v-if="!props.isEditing" md="6">
                <label class="form-field-label">Password <span class="req">*</span></label>
                <CFormInput :value="props.form.password"
                  @input="$emit('update-field', { field: 'password', value: $event })" type="password"
                  autocomplete="new-password" placeholder="Min 8 characters" :invalid="!!props.errors.password" />
                <div v-if="props.errors.password" class="field-error">{{ props.errors.password }}</div>
              </CCol>
            </CRow>
          </div>

          <div class="form-section section-divider">
            <div class="form-section-label"><i class="bi bi-shield-check me-2"></i>Roles <span class="req">*</span>
            </div>
            <RoleSelector :roleOptions="props.roleOptions" :selected="props.form.role_ids"
              @update:selected="$emit('update-role-ids', $event)" />
            <div class="selected-roles-preview mt-3">
              <label class="form-field-label">Selected Roles:</label>
              <SelectedRolesDisplay :selectedRoleIds="props.form.role_ids" :roleOptions="props.roleOptions" />
            </div>
            <div v-if="props.errors.role_ids" class="field-error">{{ props.errors.role_ids }}</div>
          </div>

          <div class="form-section">
            <div class="form-section-label"><i class="bi bi-building me-2"></i>Department</div>
            <DepartmentSelect :departments="props.departments" :modelValue="props.form.departmentId"
              :disabled="props.isLoadingDepartments" @update:modelValue="$emit('update-department', $event)" />
            <div v-if="props.errors.departmentId" class="field-error">{{ props.errors.departmentId }}</div>
          </div>
        </CForm>
      </CModalBody>

      <div class="modal-footer-custom enhanced">
        <button class="modal-btn-cancel enhanced" @click="$emit('close')">Cancel</button>
        <button class="modal-btn-save enhanced" :disabled="props.saving" @click="$emit('save')">
          <CSpinner v-if="props.saving" size="sm" class="me-2" />
          <i v-else :class="props.isEditing ? 'bi bi-check-lg me-1' : 'bi bi-plus-lg me-1'"></i>
          {{ props.isEditing ? 'Save Changes' : 'Create User' }}
        </button>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import RoleSelector from './RoleSelector.vue';
import DepartmentSelect from './DepartmentSelect.vue';
import SelectedRolesDisplay from './SelectedRolesDisplay.vue';

const props = defineProps({
  visible: { type: Boolean, required: true },
  isEditing: { type: Boolean, default: false },
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  roleOptions: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  isLoadingDepartments: { type: Boolean, default: false },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'save', 'update-field', 'update-role-ids', 'update-department']);

const phoneDisplay = computed({
  get() {
    const raw = props.form.phone || '';
    const digits = String(raw).replace(/\D/g, '');
    if (!digits) return '';
    if (digits.length <= 2) return digits;
    if (digits.length <= 5) return digits.slice(0, 2) + ' ' + digits.slice(2);
    if (digits.length <= 9) return digits.slice(0, 2) + ' ' + digits.slice(2, 5) + ' ' + digits.slice(5);
    return digits.slice(0, 2) + ' ' + digits.slice(2, 6) + ' ' + digits.slice(6, 10);
  },
  set(val) {
    const digits = String(val || '').replace(/\D/g, '').slice(0, 10);
    emit('update-field', { field: 'phone', value: digits });
  }
});

function onPhoneInput(e) {
  // assign to computed setter which will emit cleaned digits
  phoneDisplay.value = e.target ? e.target.value : e;
}

const subtitle = computed(() => props.isEditing ? 'Update user details and roles' : 'Fill in the details to create a new user');
</script>

<style scoped>
.user-modal .modal-content {
  background: linear-gradient(135deg, #f8fafc 0%, #e9ecef 100%);
  border-radius: 24px;
  border: none;
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.10);
}

.modal-header-custom.enhanced {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  border-bottom: 1px solid #e0e7ef;
  border-radius: 24px 24px 0 0;
  padding: 2rem 2rem 1.25rem 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.modal-header-custom.enhanced .modal-header-title.gradient-text {
  background: linear-gradient(135deg, #fff 0%, #e0e7ef 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800;
  font-size: 1.3rem;
}

.modal-header-custom.enhanced .modal-header-sub {
  color: #e0e7ef;
  font-size: 0.95rem;
  margin-top: 0.25rem;
}

.modal-header-custom.enhanced .modal-header-icon {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  background: rgba(255, 255, 255, 0.12);
}

.modal-header-custom.enhanced .modal-header-icon.add {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.modal-header-custom.enhanced .modal-header-icon.edit {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
}

.modal-close-btn {
  margin-left: auto;
  background: #f1f5f9;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-close-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.modal-body-enhanced {
  padding: 2.5rem 2rem 2rem 2rem !important;
  background: transparent;
}

.form-section.section-divider {
  border-bottom: 1.5px dashed #e0e7ef;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
}

.form-section-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #667eea;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.selected-roles-preview {
  padding: 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 8px;
  border: 1px solid #dee2e6;
  margin-top: 1rem;
}

.selected-roles-preview .form-field-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  display: block;
}

.phone-prefix {
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.field-hint {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 0.5rem;
}

/* Ensure input groups align with standard form inputs */
.user-modal .form-section .c-input-group,
.user-modal .form-section .c-input-group .c-form-control,
.user-modal .form-section .c-input-group .form-control {
  height: 44px;
  /* match input height */
}

.user-modal .form-section .c-input-group .c-input-group-text {
  display: flex;
  align-items: center;
}

.user-modal .form-section .c-form-input {
  height: 44px;
  padding: 0.5rem 0.75rem;
}

.modal-footer-custom.enhanced {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid #e0e7ef;
  background: #f8fafc;
  border-radius: 0 0 24px 24px;
}

.modal-btn-cancel.enhanced {
  padding: 0.7rem 1.5rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  color: #64748b;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-btn-cancel.enhanced:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}

.modal-btn-save.enhanced {
  padding: 0.7rem 1.7rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 10px;
  color: white;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.18);
}

.modal-btn-save.enhanced:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.22);
}

.modal-btn-save.enhanced:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
