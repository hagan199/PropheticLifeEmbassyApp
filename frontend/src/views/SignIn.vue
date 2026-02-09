<template>
  <div class="auth-viewport">
    <!-- Animated Background Elements -->
    <div class="bg-decoration">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
      <div class="glass-overlay"></div>
    </div>

    <div class="auth-container">
      <transition name="auth-mount" appear>
        <div class="auth-card-wrap">
          <div :class="['auth-card shadow-2xl', { 'shake-it': hasError }]">
            <!-- Card Header with Dynamic Gradient -->
            <div class="auth-header">
              <div class="logo-area">
                <div class="logo-glow"></div>
                <div class="logo-spinner"></div>
                <div class="logo-inner">
                  <i class="bi bi-lightning-charge-fill"></i>
                </div>
              </div>
              <h2 class="brand-name">Prophetic Life</h2>
              <p class="brand-sub">Church Management System</p>
            </div>

            <div class="auth-body">
              <h3 class="auth-title">Welcome Back</h3>
              <p class="auth-desc">Sign in to your account to continue</p>

              <form class="auth-form" @submit.prevent="handleSignIn">
                <!-- Phone Input Group -->
                <div class="form-group stagger-1">
                  <label class="form-label">Phone Number</label>
                  <div class="input-wrapper" :class="{ focused: activeField === 'phone', error: hasError }">
                    <span class="input-prefix">+233</span>
                    <input v-model="phone" type="tel" placeholder="24 000 0000" required maxlength="9"
                      pattern="[0-9]{9}" @focus="activeField = 'phone'" @blur="activeField = null" />
                    <i class="bi bi-phone input-icon"></i>
                  </div>
                </div>

                <!-- Password Input Group -->
                <div class="form-group stagger-2">
                  <label class="form-label">Password</label>
                  <div class="input-wrapper" :class="{ focused: activeField === 'password', error: hasError }">
                    <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" required
                      @focus="activeField = 'password'" @blur="activeField = null" />
                    <button type="button" class="btn-toggle-eye" @click="showPassword = !showPassword">
                      <i :class="showPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                    </button>
                    <i class="bi bi-shield-lock input-icon"></i>
                  </div>
                </div>

                <!-- Extras -->
                <div class="form-extras stagger-3">
                  <CFormCheck id="remember" v-model="rememberMe" label="Remember me" class="custom-check" />
                  <a href="#" class="forgot-link" @click.prevent="showForgotPassword = true">
                    Forgot Password?
                  </a>
                </div>

                <!-- Action Button -->
                <div class="form-actions stagger-4">
                  <button type="submit" class="btn-signin" :disabled="isLoading">
                    <div v-if="isLoading" class="btn-loader">
                      <span></span><span></span><span></span>
                    </div>
                    <span v-else> <i class="bi bi-box-arrow-in-right me-2"></i>Sign In </span>
                  </button>
                </div>
              </form>

              <!-- Error Toast -->
              <transition name="toast">
                <div v-if="hasError" class="auth-error">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i>
                  {{ errorMessage }}
                </div>
              </transition>

              <div class="auth-footer stagger-5">
                <div class="secure-badge">
                  <i class="bi bi-patch-check-fill"></i>
                  <span>Secure 256-bit SSL Session</span>
                </div>
              </div>
            </div>

            <!-- Loading Overlay -->
            <transition name="fade">
              <div v-if="isLoading" class="auth-loading-screen">
                <div class="loading-content">
                  <div class="pulse-loader"></div>
                  <p>Authenticating...</p>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </transition>
    </div>

    <!-- Forgot Password Modal (Enhanced) -->
    <Teleport to="body">
      <CModal
        :visible="showForgotPassword"
        alignment="center"
        class="premium-modal"
        @close="showForgotPassword = false"
      >
        <CModalBody class="p-4 text-center">
          <div class="modal-icon-header mb-3">
            <i class="bi bi-envelope-paper-heart"></i>
          </div>
          <h4 class="fw-bold mb-2">Reset Password</h4>
          <p class="text-muted mb-4 small">
            Enter your phone number registered with your account and we'll send a reset code.
          </p>

          <div class="input-wrapper focused mb-4">
            <span class="input-prefix text-primary">+233</span>
            <input v-model="resetPhone" type="tel" placeholder="24 000 0000" maxlength="9" />
            <i class="bi bi-phone input-icon"></i>
          </div>

          <div class="d-grid gap-2">
            <button class="btn btn-primary btn-lg rounded-pill" @click="sendResetCode">
              Send Reset Instructions
            </button>
            <button class="btn btn-link text-muted" @click="showForgotPassword = false">
              Maybe later
            </button>
          </div>
        </CModalBody>
      </CModal>
    </Teleport>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../store/auth';
import { useRouter } from 'vue-router';
import { CFormCheck, CModal, CModalBody } from '@coreui/vue';

const phone = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);
const isLoading = ref(false);
const hasError = ref(false);
const errorMessage = ref('Invalid credentials. Please try again.');
const activeField = ref(null);

// Forgot password
const showForgotPassword = ref(false);
const resetPhone = ref('');

const auth = useAuthStore();
const router = useRouter();

async function handleSignIn() {
  isLoading.value = true;
  hasError.value = false;

  try {
    const result = await auth.signIn(`+233${phone.value}`, password.value);

    if (result.success) {
      router.push('/dashboard');
    } else {
      triggerError(result.message || 'Invalid credentials. Please try again.');
    }
  } catch (error) {
    triggerError('Connection error. Please try again.');
  } finally {
    isLoading.value = false;
  }
}

function triggerError(msg) {
  hasError.value = true;
  errorMessage.value = msg;
  setTimeout(() => {
    // Keep error visible but stop shaking after a bit
  }, 500);
}

function sendResetCode() {
  alert(`Reset instructions sent to +233${resetPhone.value}. In a real app, you'd receive an SMS.`);
  showForgotPassword.value = false;
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

.auth-viewport {
  font-family: 'Outfit', sans-serif;
  min-height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  overflow: hidden;
  position: relative;
}

/* Animated Background Decorations */
.bg-decoration {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.blob {
  position: absolute;
  width: 500px;
  height: 500px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.4) 0%, rgba(139, 92, 246, 0.4) 100%);
  filter: blur(80px);
  border-radius: 50%;
  animation: move-blobs 20s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
}

.blob-1 {
  top: -10%;
  left: -10%;
  background: rgba(99, 102, 241, 0.3);
}

.blob-2 {
  bottom: -10%;
  right: -10%;
  background: rgba(139, 92, 246, 0.3);
  animation-delay: -5s;
}

.blob-3 {
  top: 40%;
  left: 40%;
  width: 300px;
  height: 300px;
  background: rgba(244, 63, 94, 0.15);
  animation-delay: -10s;
}

@keyframes move-blobs {
  0% {
    transform: translate(0, 0) rotate(0deg) scale(1);
  }

  50% {
    transform: translate(100px, 50px) rotate(90deg) scale(1.1);
  }

  100% {
    transform: translate(-50px, 150px) rotate(180deg) scale(0.9);
  }
}

.glass-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, transparent 0%, rgba(15, 23, 42, 0.4) 100%);
  backdrop-filter: blur(2px);
}

.auth-container {
  width: 100%;
  max-width: 460px;
  padding: 20px;
  position: relative;
  z-index: 10;
}

/* Card Styling */
.auth-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 32px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  flex-direction: column;
}

.auth-header {
  padding: 40px 40px 30px;
  text-align: center;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  color: white;
  position: relative;
}

.logo-area {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-glow {
  position: absolute;
  inset: -10px;
  background: white;
  filter: blur(20px);
  opacity: 0.3;
  border-radius: 50%;
}

.logo-spinner {
  position: absolute;
  inset: -5px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 3s linear infinite;
}

.logo-inner {
  width: 100%;
  height: 100%;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6366f1;
  font-size: 2.5rem;
  z-index: 2;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.brand-name {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -1px;
}

.brand-sub {
  font-size: 0.875rem;
  opacity: 0.85;
  margin: 5px 0 0;
}

.auth-body {
  padding: 40px;
}

.auth-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.auth-desc {
  color: #64748b;
  font-size: 0.95rem;
  margin-bottom: 32px;
}

/* Form Styling */
.form-group {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 8px;
  margin-left: 4px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 2px solid #f1f5f9;
  border-radius: 16px;
  padding: 0 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.input-wrapper.focused {
  background: white;
  border-color: #6366f1;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
  transform: translateY(-2px);
}

.input-wrapper.error {
  border-color: #f43f5e;
  animation: shake 0.4s ease;
}

.input-prefix {
  font-weight: 700;
  color: #64748b;
  margin-right: 12px;
  font-size: 0.95rem;
}

input {
  width: 100%;
  padding: 14px 0;
  border: none;
  background: transparent;
  font-size: 1rem;
  font-weight: 500;
  color: #1e293b;
  outline: none;
}

input::placeholder {
  color: #94a3b8;
}

.input-icon {
  font-size: 1.25rem;
  color: #94a3b8;
  margin-left: 12px;
}

.focused .input-icon {
  color: #6366f1;
}

.btn-toggle-eye {
  background: none;
  border: none;
  color: #94a3b8;
  padding: 8px;
  cursor: pointer;
  transition: color 0.2s;
}

.btn-toggle-eye:hover {
  color: #6366f1;
}

.form-extras {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 32px;
}

.forgot-link {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6366f1;
  text-decoration: none;
  transition: color 0.2s;
}

.forgot-link:hover {
  color: #4f46e5;
  text-decoration: underline;
}

.btn-signin {
  width: 100%;
  padding: 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  border: none;
  font-weight: 700;
  font-size: 1.125rem;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-signin:hover:not(:disabled) {
  transform: translateY(-4px);
  box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4);
}

.btn-signin:active:not(:disabled) {
  transform: translateY(0);
}

.btn-signin:disabled {
  opacity: 0.8;
  cursor: not-allowed;
}

/* Button Loader */
.btn-loader {
  display: flex;
  gap: 6px;
}

.btn-loader span {
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
  animation: bounce 0.6s infinite alternate;
}

.btn-loader span:nth-child(2) {
  animation-delay: 0.2s;
}

.btn-loader span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes bounce {
  from {
    transform: translateY(0);
    opacity: 0.6;
  }

  to {
    transform: translateY(-8px);
    opacity: 1;
  }
}

/* Error States */
.auth-error {
  margin-top: 24px;
  padding: 12px 16px;
  background: #fff1f2;
  border-left: 4px solid #f43f5e;
  border-radius: 8px;
  color: #be123c;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.auth-footer {
  margin-top: 40px;
  text-align: center;
}

.secure-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #f1f5f9;
  border-radius: 50rem;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 600;
}

.secure-badge i {
  color: #10b981;
}

/* Transitions & Animations */
.stagger-1 {
  animation: fade-slide 0.5s ease backwards 0.2s;
}

.stagger-2 {
  animation: fade-slide 0.5s ease backwards 0.3s;
}

.stagger-3 {
  animation: fade-slide 0.5s ease backwards 0.4s;
}

.stagger-4 {
  animation: fade-slide 0.5s ease backwards 0.5s;
}

.stagger-5 {
  animation: fade-slide 0.5s ease backwards 0.6s;
}

@keyframes fade-slide {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-mount-enter-active {
  transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.auth-mount-enter-from {
  opacity: 0;
  transform: translateY(40px) scale(0.95);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

@keyframes shake {

  0%,
  100% {
    transform: translateX(0);
  }

  25% {
    transform: translateX(-8px);
  }

  75% {
    transform: translateX(8px);
  }
}

/* Modal Enhancements */
.premium-modal :deep(.modal-content) {
  border-radius: 32px;
  border: none;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.modal-icon-header {
  width: 64px;
  height: 64px;
  background: #eef2ff;
  color: #6366f1;
  border-radius: 20px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.auth-loading-screen {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pulse-loader {
  width: 60px;
  height: 60px;
  border: 4px solid #f1f5f9;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: fast-spin 0.8s infinite linear;
}

@keyframes fast-spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-content {
  text-align: center;
}

.loading-content p {
  margin-top: 16px;
  font-weight: 700;
  color: #6366f1;
  letter-spacing: 1px;
}
</style>
