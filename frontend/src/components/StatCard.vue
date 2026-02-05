<template>
  <div class="stat-card-wrapper" :class="{ 'animate': animate }">
    <CCard class="stat-card">
      <CCardBody class="p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div class="stat-content">
            <div class="stat-title">{{ title }}</div>
            <div class="stat-value">
              <span class="value-text">{{ displayValue }}</span>
              <span v-if="trend" class="stat-trend" :class="trend">
                <i :class="trend === 'up' ? 'bi bi-arrow-up-right' : 'bi bi-arrow-down-right'"></i>
              </span>
            </div>
          </div>
          <div class="stat-icon-wrapper">
            <div class="stat-icon" :class="`bg-${color}`">
              <i :class="icon"></i>
            </div>
            <div class="stat-icon-glow" :class="`glow-${color}`"></div>
          </div>
        </div>
        <div class="progress-wrapper">
          <CProgress class="stat-progress" :value="progress" :color="color" />
          <div class="progress-shine"></div>
        </div>
        <div class="stat-sub">
          <span>{{ sub }}</span>
          <span v-if="changeValue" class="change-value" :class="changeType">
            {{ changeValue }}
          </span>
        </div>
      </CCardBody>
      <div class="card-shine"></div>
    </CCard>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { CCard, CCardBody, CProgress } from '@coreui/vue'

const props = defineProps({
  title: String,
  value: [String, Number],
  sub: String,
  icon: String,
  progress: Number,
  color: String,
  trend: String,
  changeValue: String,
  changeType: String
})

const animate = ref(false)
const displayValue = ref(props.value)

onMounted(() => {
  animate.value = true

  // Animate number counting if value is a number
  if (typeof props.value === 'number') {
    animateValue(0, props.value, 1000)
  }
})

function animateValue(start, end, duration) {
  const startTime = performance.now()

  function update(currentTime) {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)

    // Easing function for smooth animation
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)
    const current = Math.round(start + (end - start) * easeOutQuart)

    displayValue.value = current

    if (progress < 1) {
      requestAnimationFrame(update)
    } else {
      displayValue.value = props.value
    }
  }

  requestAnimationFrame(update)
}
</script>

<style scoped>
.stat-card-wrapper {
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.stat-card-wrapper.animate {
  opacity: 1;
  transform: translateY(0);
}

.stat-card {
  position: relative;
  overflow: hidden;
  border: none !important;
  background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
  border-radius: 20px !important;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.theme-dark .stat-card {
  background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
  border: 1px solid rgba(71, 85, 105, 0.3) !important;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -30%;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
  transition: all 0.5s ease;
}

.stat-card:hover::before {
  transform: scale(1.2);
  opacity: 0.8;
}

.stat-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow:
    0 20px 40px rgba(0, 0, 0, 0.12),
    0 8px 16px rgba(99, 102, 241, 0.08) !important;
}

.card-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.1),
    transparent
  );
  transition: left 0.5s ease;
  pointer-events: none;
}

.stat-card:hover .card-shine {
  left: 100%;
}

.stat-content {
  position: relative;
  z-index: 1;
}

.stat-title {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.5rem;
}

.theme-dark .stat-title {
  color: #94a3b8;
}

.stat-value {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.value-text {
  font-size: 2rem;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.03em;
  line-height: 1.1;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.theme-dark .value-text {
  background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-trend {
  font-size: 0.875rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 8px;
}

.stat-trend.up {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.stat-trend.down {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.stat-icon-wrapper {
  position: relative;
  flex-shrink: 0;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #fff;
  position: relative;
  z-index: 2;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.stat-card:hover .stat-icon {
  transform: scale(1.1) rotate(5deg);
}

.stat-icon-glow {
  position: absolute;
  inset: -4px;
  border-radius: 20px;
  opacity: 0;
  transition: opacity 0.3s ease;
  filter: blur(12px);
  z-index: 1;
}

.stat-card:hover .stat-icon-glow {
  opacity: 0.6;
}

.stat-icon.bg-primary {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.glow-primary {
  background: #6366f1;
}

.stat-icon.bg-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
}

.glow-success {
  background: #10b981;
}

.stat-icon.bg-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
}

.glow-warning {
  background: #f59e0b;
}

.stat-icon.bg-info {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
}

.glow-info {
  background: #06b6d4;
}

.stat-icon.bg-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.glow-danger {
  background: #ef4444;
}

.progress-wrapper {
  position: relative;
  margin-bottom: 0.75rem;
}

.stat-progress {
  height: 6px;
  border-radius: 50px;
  background: #e2e8f0;
  overflow: hidden;
}

.theme-dark .stat-progress {
  background: rgba(51, 65, 85, 0.5);
}

:deep(.stat-progress .progress-bar) {
  border-radius: 50px;
  background-size: 200% 100%;
  animation: shimmer 2s infinite linear;
}

:deep(.stat-progress .progress-bar.bg-primary) {
  background: linear-gradient(90deg, #6366f1, #8b5cf6, #6366f1);
}

:deep(.stat-progress .progress-bar.bg-success) {
  background: linear-gradient(90deg, #10b981, #34d399, #10b981);
}

:deep(.stat-progress .progress-bar.bg-warning) {
  background: linear-gradient(90deg, #f59e0b, #fbbf24, #f59e0b);
}

:deep(.stat-progress .progress-bar.bg-info) {
  background: linear-gradient(90deg, #06b6d4, #22d3ee, #06b6d4);
}

:deep(.stat-progress .progress-bar.bg-danger) {
  background: linear-gradient(90deg, #ef4444, #f87171, #ef4444);
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.progress-shine {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.4) 50%,
    transparent 100%
  );
  animation: progressShine 2s ease-in-out infinite;
  pointer-events: none;
}

@keyframes progressShine {
  0% { transform: translateX(-100%); }
  50%, 100% { transform: translateX(100%); }
}

.stat-sub {
  font-size: 0.8rem;
  color: #94a3b8;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.change-value {
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
}

.change-value.positive {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.change-value.negative {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
  .stat-card {
    border-radius: 16px !important;
  }

  .value-text {
    font-size: 1.5rem;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
    border-radius: 12px;
  }

  .stat-title {
    font-size: 0.75rem;
  }
}
</style>
