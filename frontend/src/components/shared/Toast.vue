<template>
  <div class="md-toast-container">
    <transition-group name="toast-slide" tag="div" class="toast-stack">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="md-toast-item"
        :class="[`is-${toast.type}`]"
        role="alert"
      >
        <div class="md-toast-content">
          <!-- Status Icon -->
          <div class="md-toast-icon">
            <i v-if="toast.type === 'success'" class="bi bi-check2-circle"></i>
            <i v-else-if="toast.type === 'danger'" class="bi bi-x-circle"></i>
            <i v-else-if="toast.type === 'warning'" class="bi bi-exclamation-triangle"></i>
            <i v-else class="bi bi-info-circle"></i>
          </div>

          <!-- Message -->
          <div class="md-toast-message">
            <span class="md-toast-title">{{ getTitle(toast.type) }}</span>
            <p class="md-toast-text">{{ toast.message }}</p>
          </div>

          <!-- Close Button -->
          <button class="md-toast-close" @click="dismiss(toast.id)">
            <i class="bi bi-x"></i>
          </button>
        </div>

        <!-- Progress Bar -->
        <div class="md-toast-progress">
          <div
            class="md-toast-progress-bar"
            :style="{ animationDuration: (toast.duration || 3000) + 'ms' }"
          ></div>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '../../composables/useToast';

const { toasts, dismiss } = useToast();

const getTitle = type => {
  switch (type) {
    case 'success':
      return 'Success';
    case 'danger':
      return 'Error';
    case 'warning':
      return 'Warning';
    default:
      return 'Notification';
  }
};
</script>

<style scoped>
/* ========================================
   MODERN MATERIAL TOAST SYSTEM
   ======================================== */

.md-toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 10000;
  width: 100%;
  max-width: 380px;
  pointer-events: none;
}

.toast-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.md-toast-item {
  pointer-events: auto;
  position: relative;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-radius: 16px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05),
    inset 0 0 0 1px rgba(255, 255, 255, 0.5);
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

/* Dark Mode Support */
:global(.theme-dark) .md-toast-item {
  background: rgba(30, 41, 59, 0.85);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2),
    inset 0 0 0 1px rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.05);
}

.md-toast-content {
  display: flex;
  align-items: flex-start;
  padding: 16px 20px;
  gap: 14px;
}

/* Icon Styles */
.md-toast-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.is-success .md-toast-icon {
  background: #dcfce7;
  color: #15803d;
}

.is-danger .md-toast-icon {
  background: #fee2e2;
  color: #b91c1c;
}

.is-warning .md-toast-icon {
  background: #fef9c3;
  color: #a16207;
}

.is-info .md-toast-icon {
  background: #e0f2fe;
  color: #0369a1;
}

:global(.theme-dark) .is-success .md-toast-icon {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
}

:global(.theme-dark) .is-danger .md-toast-icon {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
}

:global(.theme-dark) .is-warning .md-toast-icon {
  background: rgba(234, 179, 8, 0.2);
  color: #fbbf24;
}

:global(.theme-dark) .is-info .md-toast-icon {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
}

/* Text Content */
.md-toast-message {
  flex: 1;
  padding-top: 2px;
}

.md-toast-title {
  display: block;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2px;
  font-family: 'Outfit', sans-serif;
}

:global(.theme-dark) .md-toast-title {
  color: #f1f5f9;
}

.md-toast-text {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin: 0;
  font-family: 'Outfit', sans-serif;
}

:global(.theme-dark) .md-toast-text {
  color: #94a3b8;
}

/* Close Button */
.md-toast-close {
  background: transparent;
  border: none;
  padding: 4px;
  color: #94a3b8;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: -4px;
  margin-right: -8px;
}

.md-toast-close:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #1e293b;
}

:global(.theme-dark) .md-toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

/* Progress Bar */
.md-toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: rgba(0, 0, 0, 0.03);
}

.md-toast-progress-bar {
  height: 100%;
  width: 100%;
  transform-origin: left;
  animation: progress linear forwards;
}

.is-success .md-toast-progress-bar {
  background: #22c55e;
}

.is-danger .md-toast-progress-bar {
  background: #ef4444;
}

.is-warning .md-toast-progress-bar {
  background: #eab308;
}

.is-info .md-toast-progress-bar {
  background: #3b82f6;
}

@keyframes progress {
  from {
    transform: scaleX(1);
  }

  to {
    transform: scaleX(0);
  }
}

/* Transitions */
.toast-slide-enter-active {
  transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
}

.toast-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1);
  position: absolute;
  /* Needed for move transition */
  width: 100%;
}

.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(100px) translateY(0);
}

.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(50px) scale(0.9);
}

.toast-slide-move {
  transition: transform 0.4s ease;
}

/* Responsive */
@media (max-width: 480px) {
  .md-toast-container {
    top: auto;
    bottom: 24px;
    right: 16px;
    left: 16px;
    max-width: none;
    width: auto;
  }

  .toast-slide-enter-from {
    transform: translateY(20px);
  }
}
</style>
