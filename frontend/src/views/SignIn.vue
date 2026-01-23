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
              <!-- Step 1: Phone + Password -->
              <div v-if="step === 'login'">
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
              </div>

              <!-- Step 2: 2FA Code (for Admin/Finance) -->
              <div v-if="step === '2fa'">
                <h4 class="mb-2">Two-Factor Authentication</h4>
                <p class="text-muted small mb-3">
                  We sent a verification code to your phone ending in {{ phone.slice(-4) }}
                </p>
                <CForm @submit.prevent="verify2FA">
                  <div class="mb-3">
                    <CFormLabel>Verification Code</CFormLabel>
                    <div class="otp-inputs d-flex gap-2 justify-content-center">
                      <CFormInput v-for="(digit, idx) in otpDigits" :key="idx" v-model="otpDigits[idx]"
                        class="otp-input text-center" maxlength="1" @input="handleOtpInput(idx, $event)"
                        @keydown="handleOtpKeydown(idx, $event)" :ref="el => otpRefs[idx] = el" />
                    </div>
                  </div>
                  <CButton type="submit" color="primary" class="w-100 py-2" :disabled="isLoading || otpCode.length < 6">
                    <template v-if="isLoading">
                      <CSpinner size="sm" class="me-2" /> Verifying...
                    </template>
                    <template v-else>
                      Verify & Sign In
                    </template>
                  </CButton>
                </CForm>
                <div class="text-center mt-3">
                  <span class="text-muted small">Didn't receive code?</span>
                  <CButton color="link" size="sm" :disabled="resendTimer > 0" @click="resendCode">
                    {{ resendTimer > 0 ? `Resend in ${resendTimer}s` : 'Resend Code' }}
                  </CButton>
                </div>
                <CButton color="light" class="w-100 mt-2" @click="step = 'login'">
                  <i class="bi bi-arrow-left me-1"></i> Back to Login
                </CButton>
                <div v-if="hasError" class="alert alert-danger mt-3 small py-2">
                  <i class="bi bi-exclamation-circle me-1"></i> {{ errorMessage }}
                </div>
              </div>

              <div class="text-center mt-3 text-muted small">
                <i class="bi bi-shield-check me-1"></i> Secure access powered by Sanctum
              </div>
            </CCardBody>
          </CCard>
          <div v-if="isLoading" class="overlay">
            <div class="overlay-center">
              <CSpinner color="primary" />
              <div class="mt-3 fw-semibold">{{ step === '2fa' ? 'Verifying...' : 'Signing in...' }}</div>
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
import { ref, computed } from 'vue'
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
const step = ref('login') // 'login' | '2fa'

// 2FA
const otpDigits = ref(['', '', '', '', '', ''])
const otpRefs = ref([])
const otpCode = computed(() => otpDigits.value.join(''))
const resendTimer = ref(0)

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

    if (result.requires2FA) {
      // Admin or Finance role needs 2FA
      step.value = '2fa'
      startResendTimer()
    } else if (result.success) {
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

async function verify2FA() {
  isLoading.value = true
  hasError.value = false

  try {
    const result = await auth.verify2FA(otpCode.value)

    if (result.success) {
      router.push('/dashboard')
    } else {
      hasError.value = true
      errorMessage.value = result.message || 'Invalid code. Please try again.'
      otpDigits.value = ['', '', '', '', '', '']
      otpRefs.value[0]?.focus()
    }
  } catch (error) {
    hasError.value = true
    errorMessage.value = 'Verification failed. Please try again.'
  }

  isLoading.value = false
}

function handleOtpInput(idx, event) {
  const value = event.target.value
  if (value && idx < 5) {
    otpRefs.value[idx + 1]?.focus()
  }
}

function handleOtpKeydown(idx, event) {
  if (event.key === 'Backspace' && !otpDigits.value[idx] && idx > 0) {
    otpRefs.value[idx - 1]?.focus()
  }
}

function startResendTimer() {
  resendTimer.value = 60
  const interval = setInterval(() => {
    resendTimer.value--
    if (resendTimer.value <= 0) clearInterval(interval)
  }, 1000)
}

async function resendCode() {
  await auth.resend2FA()
  startResendTimer()
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
  background: rgba(0, 0, 0, .35);
}

.card-auth {
  width: 100%;
  max-width: 440px;
  position: relative;
  z-index: 1;
}

.card-wrap {
  position: relative;
}

.elevate {
  border-radius: 14px;
  background: rgba(255, 255, 255, .85);
  backdrop-filter: saturate(160%) blur(6px);
}

.logo-badge {
  display: inline-block;
  width: 26px;
  height: 26px;
  margin-right: 8px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #fff, rgba(255, 255, 255, .2));
  box-shadow: 0 0 0 2px rgba(255, 255, 255, .25), 0 4px 12px rgba(0, 0, 0, .15);
  animation: pulse 2.4s ease-in-out infinite;
  vertical-align: middle;
}

.brand {
  font-weight: 700;
  letter-spacing: 0.3px;
}

.subtitle {
  opacity: 0.9;
  font-size: 0.9rem;
}

.fade-up-enter-active {
  transition: opacity .4s ease, transform .4s ease;
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(.98);
}

.fade-up-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, .75);
  backdrop-filter: blur(2px);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: overlayFade .2s ease;
}

.overlay-center {
  display: flex;
  flex-direction: column;
  align-items: center;
}

@keyframes overlayFade {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}


@keyframes pulse {

  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.06);
    opacity: .9;
  }
}

.shake {
  animation: shake .45s cubic-bezier(.36, .07, .19, .97);
}

@keyframes shake {

  10%,
  90% {
    transform: translateX(-1px);
  }

  20%,
  80% {
    transform: translateX(2px);
  }

  30%,
  50%,
  70% {
    transform: translateX(-4px);
  }

  40%,
  60% {
    transform: translateX(4px);
  }
}

:deep(.form-control) {
  transition: box-shadow .2s ease, border-color .2s ease;
}

:deep(.form-control:focus) {
  box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25);
  border-color: #0d6efd;
}

:deep(.btn-primary) {
  transition: transform .15s ease, box-shadow .15s ease;
}

:deep(.btn-primary:hover) {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(13, 110, 253, .35);
}

.otp-input {
  width: 45px;
  height: 50px;
  font-size: 1.5rem;
  font-weight: 600;
  border-radius: 8px;
}

.otp-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25);
}
</style>
