<template>
  <div class="auth-bg">
    <div class="card-auth">
      <transition name="fade-up">
        <div class="card-wrap">
          <CCard :class="['shadow-lg border-0 elevate', { shake: hasError }]">
            <CCardHeader class="text-center py-4 bg-primary text-white">
              <div class="brand"><span class="logo-badge"></span> Prophetic Life Embassy</div>
              <div class="subtitle">Church Management System</div>
            </CCardHeader>
            <CCardBody class="p-4">
              <h4 class="mb-3">Sign in</h4>
              <CForm @submit.prevent="handleSignIn">
                <div class="mb-3">
                  <CFormLabel>Phone Number</CFormLabel>
                  <CInputGroup>
                    <CInputGroupText>+233</CInputGroupText>
                    <CFormInput v-model="phone" type="tel" placeholder="241234567" required maxlength="9"
                      pattern="[0-9]{9}" />
                  </CInputGroup>
                  <div class="form-text">Enter your 9-digit phone number</div>
                </div>
                <div class="mb-3">
                  <CFormLabel>Password</CFormLabel>
                  <CInputGroup>
                    <CInputGroupText><i class="bi bi-lock"></i></CInputGroupText>
                    <CFormInput v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Password"
                      required />
                    <CButton color="light" @click="showPassword = !showPassword">
                      <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </CButton>
                  </CInputGroup>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <CFormCheck id="remember" label="Remember me" v-model="rememberMe" />
                  <a href="#" class="text-decoration-none small" @click.prevent="showForgotPassword = true">
                    Forgot password?
                  </a>
                </div>
                <CButton type="submit" color="primary" class="w-100 py-2" :disabled="isLoading">
                  <template v-if="isLoading">
                    <CSpinner size="sm" class="me-2" /> Signing in...
                  </template>
                  <template v-else>
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                  </template>
                </CButton>
              </CForm>
              <div v-if="hasError" class="alert alert-danger mt-3 small py-2">
                <i class="bi bi-exclamation-circle me-1"></i> {{ errorMessage }}
              </div>

              <div class="text-center mt-3 text-muted small">
                <i class="bi bi-shield-check me-1"></i> Secure access powered by Sanctum
              </div>
            </CCardBody>
          </CCard>
          <div v-if="isLoading" class="overlay">
            <div class="overlay-center">
              <CSpinner color="primary" />
              <div class="mt-3 fw-semibold">Signing in...</div>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- Forgot Password Modal -->
    <CModal :visible="showForgotPassword" @close="showForgotPassword = false">
      <CModalHeader>
        <CModalTitle>Reset Password</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <p class="text-muted">Enter your phone number and we'll send you a reset code.</p>
        <CInputGroup>
          <CInputGroupText>+233</CInputGroupText>
          <CFormInput v-model="resetPhone" type="tel" placeholder="241234567" maxlength="9" />
        </CInputGroup>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showForgotPassword = false">Cancel</CButton>
        <CButton color="primary" @click="sendResetCode">Send Reset Code</CButton>
      </CModalFooter>
    </CModal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../store/auth'
import { useRouter } from 'vue-router'
import {
  CForm, CFormInput, CFormLabel, CFormCheck, CButton, CInputGroup, CInputGroupText,
  CCard, CCardBody, CCardHeader, CSpinner, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter
} from '@coreui/vue'

const phone = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const isLoading = ref(false)
const hasError = ref(false)
const errorMessage = ref('Invalid credentials. Please try again.')
// Forgot password
const showForgotPassword = ref(false)
const resetPhone = ref('')

const auth = useAuthStore()
const router = useRouter()

async function handleSignIn() {
  isLoading.value = true
  hasError.value = false

  try {
    const result = await auth.signIn(`+233${phone.value}`, password.value)

    if (result.success) {
      router.push('/dashboard')
    } else {
      hasError.value = true
      errorMessage.value = result.message || 'Invalid credentials. Please try again.'
    }
  } catch (error) {
    hasError.value = true
    errorMessage.value = 'Connection error. Please try again.'
  }

  isLoading.value = false
}

function sendResetCode() {
  // API call to send reset code
  alert(`Reset code sent to +233${resetPhone.value}`)
  showForgotPassword.value = false
}
</script>

<style scoped>
.auth-bg {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background-image: url('/image/background.jpeg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  padding: 24px;
}

.auth-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(15, 23, 42, 0.85) 100%);
}

.card-auth {
  width: 100%;
  max-width: 440px;
  position: relative;
  z-index: 1;
  animation: slideUp 0.6s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card-wrap {
  position: relative;
}

.elevate {
  border-radius: 20px !important;
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
  overflow: hidden;
}

:deep(.card-header) {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
  border: none !important;
  padding: 2rem !important;
}

.logo-badge {
  display: inline-block;
  width: 32px;
  height: 32px;
  margin-right: 10px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #fff, rgba(255, 255, 255, 0.4));
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2), 0 8px 24px rgba(0, 0, 0, 0.2);
  animation: float 3s ease-in-out infinite;
  vertical-align: middle;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.brand {
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: -0.02em;
}

.subtitle {
  opacity: 0.85;
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

:deep(.card-body) {
  padding: 2rem !important;
}

:deep(.card-body h4) {
  font-weight: 700;
  color: #1e293b;
  font-size: 1.5rem;
  margin-bottom: 1.5rem !important;
}

.fade-up-enter-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
}

.fade-up-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: overlayFade 0.25s ease;
}

.overlay-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #1e293b;
}

@keyframes overlayFade {
  from { opacity: 0; }
  to { opacity: 1; }
}

.shake {
  animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}

@keyframes shake {
  10%, 90% { transform: translateX(-2px); }
  20%, 80% { transform: translateX(3px); }
  30%, 50%, 70% { transform: translateX(-5px); }
  40%, 60% { transform: translateX(5px); }
}

:deep(.form-label) {
  font-weight: 600;
  color: #475569;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

:deep(.form-control),
:deep(.input-group-text) {
  border-radius: 10px !important;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  border-color: #e2e8f0 !important;
  transition: all 0.2s ease;
}

:deep(.input-group-text) {
  background: #f8fafc !important;
  border-right: none !important;
  font-weight: 600;
  color: #64748b;
}

:deep(.input-group .form-control) {
  border-left: none !important;
}

:deep(.form-control:focus) {
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
  border-color: #6366f1 !important;
}

:deep(.form-text) {
  color: #94a3b8;
  font-size: 0.8rem;
  margin-top: 0.5rem;
}

:deep(.btn-primary) {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
  border: none !important;
  border-radius: 12px !important;
  font-weight: 600;
  font-size: 1rem;
  padding: 0.875rem 1.5rem !important;
  transition: all 0.25s ease;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}

:deep(.btn-primary:hover) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.45);
}

:deep(.btn-primary:active) {
  transform: translateY(0);
}

:deep(.btn-light) {
  background: #f1f5f9 !important;
  border: none !important;
  color: #64748b !important;
}

:deep(.form-check-input:checked) {
  background-color: #6366f1;
  border-color: #6366f1;
}

.alert-danger {
  background: rgba(239, 68, 68, 0.1) !important;
  border: 1px solid rgba(239, 68, 68, 0.2) !important;
  color: #dc2626 !important;
  border-radius: 10px !important;
}

.otp-input {
  width: 48px;
  height: 54px;
  font-size: 1.5rem;
  font-weight: 700;
  border-radius: 12px;
  text-align: center;
  border: 2px solid #e2e8f0;
  transition: all 0.2s ease;
}

.otp-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
  outline: none;
}

/* Modal styling */
:deep(.modal-content) {
  border-radius: 16px !important;
  border: none !important;
}

:deep(.modal-header) {
  border-bottom: 1px solid #e2e8f0 !important;
}

:deep(.modal-footer) {
  border-top: 1px solid #e2e8f0 !important;
}
</style>
