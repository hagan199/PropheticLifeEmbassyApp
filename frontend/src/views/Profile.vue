<template>
  <div class="page-wrap">
    <!-- Notification -->
    <transition name="alert-slide">
      <CAlert v-if="notification.show" :color="notification.type" dismissible @close="notification.show = false" class="notification-alert">
        <i :class="notificationIcon" class="me-2"></i>
        {{ notification.message }}
      </CAlert>
    </transition>

    <!-- Page Header -->
    <div class="page-header">
      <h2 class="title">My Profile</h2>
      <Breadcrumbs />
      <p class="subtitle">View and update your account information</p>
    </div>

    <CRow class="g-4">
      <!-- Profile Card -->
      <CCol lg="4" md="5">
        <div class="profile-card">
          <div class="profile-card-body">
            <div class="profile-avatar-wrapper">
              <div class="profile-avatar-large">
                {{ initials }}
              </div>
              <span class="online-status"></span>
            </div>
            <h4 class="profile-name">{{ auth.user?.name || 'User' }}</h4>
            <p class="profile-phone">
              <i class="bi bi-telephone me-2"></i>{{ auth.user?.phone }}
            </p>
            <span class="role-badge" :class="roleColor">
              <i class="bi bi-shield-check me-1"></i>{{ roleLabel }}
            </span>
            <div class="profile-meta">
              <i class="bi bi-calendar3 me-1"></i>
              Member since {{ memberSince }}
            </div>
          </div>
        </div>
      </CCol>

      <!-- Profile Details -->
      <CCol lg="8" md="7">
        <!-- Account Info Card -->
        <div class="info-card">
          <div class="info-card-header">
            <div class="header-icon">
              <i class="bi bi-person-badge"></i>
            </div>
            <div>
              <h5 class="mb-0">Account Information</h5>
              <small class="text-muted">Your personal details</small>
            </div>
          </div>
          <div class="info-card-body">
            <CForm @submit.prevent="updateProfile">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label-modern">
                    <i class="bi bi-person me-2"></i>Full Name
                  </label>
                  <input
                    v-model="form.name"
                    class="form-input-modern"
                    :class="{ 'disabled': !isEditing }"
                    :disabled="!isEditing"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label-modern">
                    <i class="bi bi-phone me-2"></i>Phone Number
                  </label>
                  <input
                    v-model="form.phone"
                    class="form-input-modern disabled"
                    disabled
                  />
                  <small class="form-hint">Phone number cannot be changed</small>
                </div>
                <div class="form-group">
                  <label class="form-label-modern">
                    <i class="bi bi-envelope me-2"></i>Email Address
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="form-input-modern"
                    :class="{ 'disabled': !isEditing }"
                    :disabled="!isEditing"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label-modern">
                    <i class="bi bi-shield me-2"></i>Role
                  </label>
                  <input
                    :value="roleLabel"
                    class="form-input-modern disabled"
                    disabled
                  />
                </div>
              </div>
              <div class="form-actions">
                <button v-if="!isEditing" type="button" class="btn-primary-modern" @click="isEditing = true">
                  <i class="bi bi-pencil me-2"></i>Edit Profile
                </button>
                <template v-else>
                  <button type="submit" class="btn-primary-modern" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="bi bi-check-lg me-2"></i>
                    Save Changes
                  </button>
                  <button type="button" class="btn-secondary-modern" @click="cancelEdit">
                    Cancel
                  </button>
                </template>
              </div>
            </CForm>
          </div>
        </div>

        <!-- Change Password Card -->
        <div class="info-card mt-4">
          <div class="info-card-header">
            <div class="header-icon warning">
              <i class="bi bi-key"></i>
            </div>
            <div>
              <h5 class="mb-0">Change Password</h5>
              <small class="text-muted">Update your security credentials</small>
            </div>
          </div>
          <div class="info-card-body">
            <CForm @submit.prevent="changePassword">
              <div class="form-grid">
                <div class="form-group full-width">
                  <label class="form-label-modern">
                    <i class="bi bi-lock me-2"></i>Current Password
                  </label>
                  <input
                    v-model="passwordForm.current"
                    type="password"
                    class="form-input-modern"
                    autocomplete="current-password"
                    placeholder="Enter current password"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label-modern">
                    <i class="bi bi-lock-fill me-2"></i>New Password
                  </label>
                  <input
                    v-model="passwordForm.new"
                    type="password"
                    class="form-input-modern"
                    autocomplete="new-password"
                    placeholder="Enter new password"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label-modern">
                    <i class="bi bi-check-circle me-2"></i>Confirm Password
                  </label>
                  <input
                    v-model="passwordForm.confirm"
                    type="password"
                    class="form-input-modern"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                  />
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-warning-modern" :disabled="changingPassword">
                  <span v-if="changingPassword" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-shield-check me-2"></i>
                  Update Password
                </button>
              </div>
            </CForm>
          </div>
        </div>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { CRow, CCol, CAlert } from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { useAuthStore } from '../store/auth'
import { authApi } from '../api/auth'

const auth = useAuthStore()

const isEditing = ref(false)
const saving = ref(false)
const changingPassword = ref(false)
const notification = reactive({ show: false, type: 'success', message: '' })

const form = reactive({
  name: '',
  phone: '',
  email: ''
})

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})

const roleColorMap = {
  admin: { label: 'Administrator', color: 'danger' },
  pastor: { label: 'Pastor', color: 'primary' },
  usher: { label: 'Usher', color: 'info' },
  finance: { label: 'Finance Officer', color: 'success' },
  pr_follow_up: { label: 'PR / Follow-up', color: 'warning' },
  department_leader: { label: 'Department Leader', color: 'secondary' }
}

const initials = computed(() => {
  const name = auth.user?.name || 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const roleLabel = computed(() => {
  return roleColorMap[auth.user?.role]?.label || auth.user?.role || 'User'
})

const roleColor = computed(() => {
  return roleColorMap[auth.user?.role]?.color || 'secondary'
})

const notificationIcon = computed(() => {
  const icons = {
    success: 'bi bi-check-circle-fill',
    danger: 'bi bi-exclamation-circle-fill',
    warning: 'bi bi-exclamation-triangle-fill',
    info: 'bi bi-info-circle-fill'
  }
  return icons[notification.type] || icons.info
})

const memberSince = computed(() => {
  if (!auth.user?.created_at) return 'N/A'
  return new Date(auth.user.created_at).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long'
  })
})

onMounted(() => {
  loadProfile()
})

function loadProfile() {
  form.name = auth.user?.name || ''
  form.phone = auth.user?.phone || ''
  form.email = auth.user?.email || ''
}

function cancelEdit() {
  isEditing.value = false
  loadProfile()
}

async function updateProfile() {
  saving.value = true
  try {
    const response = await authApi.updateProfile({
      name: form.name.trim(),
      email: form.email || null
    })

    if (response.data?.data?.user) {
      auth.user = response.data.data.user
      localStorage.setItem('auth_user', JSON.stringify(auth.user))
    } else {
      auth.user.name = form.name
      auth.user.email = form.email
      localStorage.setItem('auth_user', JSON.stringify(auth.user))
    }

    showNotification('success', response.data?.message || 'Profile updated successfully')
    isEditing.value = false
  } catch (error) {
    showNotification('danger', error.response?.data?.message || 'Failed to update profile')
  } finally {
    saving.value = false
  }
}

async function changePassword() {
  if (!passwordForm.current || !passwordForm.new || !passwordForm.confirm) {
    showNotification('warning', 'Please fill in all password fields')
    return
  }

  if (passwordForm.new !== passwordForm.confirm) {
    showNotification('danger', 'New passwords do not match')
    return
  }

  if (passwordForm.new.length < 8) {
    showNotification('danger', 'Password must be at least 8 characters')
    return
  }

  changingPassword.value = true
  try {
    await authApi.changePassword(passwordForm.current, passwordForm.new, passwordForm.confirm)
    showNotification('success', 'Password changed successfully')
    passwordForm.current = ''
    passwordForm.new = ''
    passwordForm.confirm = ''
  } catch (error) {
    showNotification('danger', error.response?.data?.message || 'Failed to change password')
  } finally {
    changingPassword.value = false
  }
}

function showNotification(type, message) {
  notification.type = type
  notification.message = message
  notification.show = true
  setTimeout(() => { notification.show = false }, 4000)
}
</script>

<style scoped>
/* Page Layout */
.page-wrap {
  padding: 1.5rem;
  max-width: 100%;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header .title {
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 0.25rem 0;
}

.subtitle {
  color: var(--text-muted, #94a3b8);
  margin: 0.5rem 0 0 0;
  font-size: 0.9375rem;
}

/* Profile Card */
.profile-card {
  background: var(--bg-card, #fff);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid var(--border-light, #e2e8f0);
}

.profile-card-body {
  padding: 2rem 1.5rem;
  text-align: center;
}

.profile-avatar-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.profile-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  font-weight: 700;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
}

.online-status {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  background: #10b981;
  border-radius: 50%;
  border: 3px solid var(--bg-card, #fff);
}

.profile-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary, #0f172a);
  margin: 0 0 0.25rem 0;
}

.profile-phone {
  color: var(--text-muted, #64748b);
  font-size: 0.9375rem;
  margin: 0 0 0.75rem 0;
}

.role-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.role-badge.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.role-badge.primary {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.role-badge.success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.role-badge.info {
  background: rgba(6, 182, 212, 0.1);
  color: #06b6d4;
}

.role-badge.warning {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.role-badge.secondary {
  background: rgba(100, 116, 139, 0.1);
  color: #64748b;
}

.profile-meta {
  margin-top: 1rem;
  font-size: 0.8125rem;
  color: var(--text-muted, #94a3b8);
}

/* Info Card */
.info-card {
  background: var(--bg-card, #fff);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid var(--border-light, #e2e8f0);
}

.info-card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-light, #e2e8f0);
}

.header-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6366f1;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.header-icon.warning {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(234, 88, 12, 0.1) 100%);
  color: #f59e0b;
}

.info-card-header h5 {
  font-weight: 600;
  color: var(--text-primary, #0f172a);
}

.info-card-body {
  padding: 1.5rem;
}

/* Form Styles */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label-modern {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary, #64748b);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
}

.form-input-modern {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1.5px solid var(--border-color, #e2e8f0);
  border-radius: 12px;
  background: var(--bg-input, #f8fafc);
  font-size: 0.9375rem;
  color: var(--text-primary, #0f172a);
  transition: all 0.2s ease;
}

.form-input-modern:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
  background: var(--bg-card, #fff);
}

.form-input-modern.disabled {
  background: var(--bg-hover, #f1f5f9);
  color: var(--text-muted, #94a3b8);
  cursor: not-allowed;
}

.form-hint {
  font-size: 0.75rem;
  color: var(--text-muted, #94a3b8);
  margin-top: 0.375rem;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
}

.btn-primary-modern,
.btn-secondary-modern,
.btn-warning-modern {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-size: 0.9375rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-primary-modern {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}

.btn-primary-modern:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
}

.btn-secondary-modern {
  background: var(--bg-hover, #f1f5f9);
  color: var(--text-secondary, #64748b);
}

.btn-secondary-modern:hover {
  background: var(--bg-active, #e2e8f0);
}

.btn-warning-modern {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
}

.btn-warning-modern:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
}

.btn-primary-modern:disabled,
.btn-warning-modern:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* Notification Alert */
.notification-alert {
  position: fixed;
  top: 80px;
  right: 1.5rem;
  left: auto;
  z-index: 1050;
  max-width: 400px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.alert-slide-enter-active,
.alert-slide-leave-active {
  transition: all 0.3s ease;
}

.alert-slide-enter-from,
.alert-slide-leave-to {
  opacity: 0;
  transform: translateX(100px);
}

/* Responsive */
@media (max-width: 991.98px) {
  .page-wrap {
    padding: 1rem;
  }

  .profile-card-body {
    padding: 1.5rem 1rem;
  }

  .info-card-header {
    padding: 1rem;
  }

  .info-card-body {
    padding: 1rem;
  }
}

@media (max-width: 767.98px) {
  .page-header .title {
    font-size: 1.5rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.full-width {
    grid-column: 1;
  }

  .profile-avatar-large {
    width: 70px;
    height: 70px;
    font-size: 1.5rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-primary-modern,
  .btn-secondary-modern,
  .btn-warning-modern {
    width: 100%;
  }

  .notification-alert {
    right: 1rem;
    left: 1rem;
    max-width: none;
  }
}

@media (max-width: 575.98px) {
  .page-wrap {
    padding: 0.875rem;
  }

  .page-header .title {
    font-size: 1.375rem;
  }

  .info-card {
    border-radius: 16px;
  }

  .profile-card {
    border-radius: 16px;
  }

  .header-icon {
    width: 40px;
    height: 40px;
    font-size: 1.125rem;
  }

  .info-card-header h5 {
    font-size: 1rem;
  }

  .notification-alert {
    top: 70px;
  }
}

/* Dark Theme */
.theme-dark .profile-card,
.theme-dark .info-card {
  background: var(--bg-card, rgba(30, 41, 59, 0.8));
  border-color: var(--border-light, rgba(51, 65, 85, 0.5));
}

.theme-dark .info-card-header {
  border-color: var(--border-light, rgba(51, 65, 85, 0.5));
}

.theme-dark .form-input-modern {
  background: var(--bg-input, rgba(30, 41, 59, 0.6));
  border-color: var(--border-color, rgba(71, 85, 105, 0.5));
  color: var(--text-primary, #f1f5f9);
}

.theme-dark .form-input-modern:focus {
  background: var(--bg-elevated, #1e293b);
}

.theme-dark .form-input-modern.disabled {
  background: var(--bg-hover, rgba(51, 65, 85, 0.5));
}

.theme-dark .btn-secondary-modern {
  background: var(--bg-hover, rgba(51, 65, 85, 0.5));
  color: var(--text-primary, #e2e8f0);
}

.theme-dark .profile-name,
.theme-dark .info-card-header h5 {
  color: var(--text-primary, #f1f5f9);
}

.theme-dark .online-status {
  border-color: var(--bg-card, #1e293b);
}
</style>
