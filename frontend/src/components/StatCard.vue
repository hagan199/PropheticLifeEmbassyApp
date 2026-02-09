<template>
  <div class="stat-card" :class="[`stat-card-${color}`, { animate: animate }]">
    <div class="stat-card-inner">
      <!-- Content Section -->
      <div class="stat-content">
        <div class="stat-title">{{ title }}</div>
        <div class="stat-value">
          <span class="value-text">{{ displayValue }}</span>
          <span v-if="trend" class="stat-trend" :class="trend">
            <i :class="trend === 'up' ? 'bi bi-arrow-up-right' : 'bi bi-arrow-down-right'"></i>
          </span>
        </div>
        <div class="stat-sub">
          <span>{{ sub }}</span>
          <span v-if="changeValue" class="change-value" :class="changeType">
            {{ changeValue }}
          </span>
        </div>
      </div>

      <!-- Icon Section -->
      <div class="stat-icon-wrapper">
        <div class="stat-icon" :class="`stat-icon-${color}`">
          <i :class="icon"></i>
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div v-if="progress !== undefined" class="stat-progress-wrapper">
      <div class="stat-progress-track">
        <div class="stat-progress-fill" :class="`stat-progress-${color}`" :style="{ width: `${progress}%` }"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
/* eslint-disable vue/require-default-prop */
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  title: String,
  value: [String, Number],
  sub: String,
  icon: String,
  progress: Number,
  color: { type: String, default: 'primary' },
  trend: String,
  changeValue: String,
  changeType: String,
});

const animate = ref(false);
const displayValue = ref(props.value);

watch(
  () => props.value,
  newVal => {
    displayValue.value = newVal;
  }
);

onMounted(() => {
  setTimeout(() => {
    animate.value = true;
  }, 50);

  // Animate number counting if value is a number
  if (typeof props.value === 'number') {
    animateValue(0, props.value, 1000);
  }
});

function animateValue(start, end, duration) {
  const startTime = performance.now();

  function update(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
    const current = Math.round(start + (end - start) * easeOutQuart);
    displayValue.value = current;

    if (progress < 1) {
      requestAnimationFrame(update);
    } else {
      displayValue.value = props.value;
    }
  }

  requestAnimationFrame(update);
}
</script>

<style scoped>
.stat-card {
  background: var(--md-surface);
  border-radius: var(--md-shape-xl);
  padding: var(--md-space-5);
  box-shadow: var(--md-elevation-2);
  position: relative;
  overflow: hidden;
  will-change: transform, box-shadow, opacity;
}

.stat-card.animate {
  opacity: 1;
  transform: translateZ(0);
  /* Hardware accelerate */
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--md-elevation-4);
}

/* Top accent line */
.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-card-primary::before {
  background: var(--md-primary);
}

.stat-card-success::before {
  background: var(--md-success);
}

.stat-card-warning::before {
  background: var(--md-warning);
}

.stat-card-info::before {
  background: var(--md-info);
}

.stat-card-danger::before {
  background: var(--md-error);
}

.stat-card-inner {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: var(--md-space-4);
}

/* Content */
.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-title {
  font: var(--md-label-medium);
  color: var(--md-on-surface-variant);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: var(--md-space-2);
}

.stat-value {
  display: flex;
  align-items: center;
  gap: var(--md-space-2);
  margin-bottom: var(--md-space-2);
}

.value-text {
  font: var(--md-headline-medium);
  font-weight: 700;
  color: var(--md-on-surface);
  letter-spacing: -0.02em;
}

.stat-trend {
  font: var(--md-label-small);
  font-weight: 600;
  padding: var(--md-space-1) var(--md-space-2);
  border-radius: var(--md-shape-sm);
}

.stat-trend.up {
  color: var(--md-success);
  background: var(--md-success-container);
}

.stat-trend.down {
  color: var(--md-error);
  background: var(--md-error-container);
}

.stat-sub {
  font: var(--md-body-small);
  color: var(--md-on-surface-muted);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.change-value {
  font-weight: 600;
  padding: var(--md-space-1) var(--md-space-2);
  border-radius: var(--md-shape-xs);
  font: var(--md-label-small);
}

.change-value.positive {
  color: var(--md-success);
  background: var(--md-success-container);
}

.change-value.negative {
  color: var(--md-error);
  background: var(--md-error-container);
}

/* Icon */
.stat-icon-wrapper {
  flex-shrink: 0;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--md-shape-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #fff;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.stat-card:hover .stat-icon {
  transform: scale(1.1) rotate(5deg);
}

.stat-icon-primary {
  background: var(--md-gradient-primary);
  box-shadow: var(--md-shadow-primary);
}

.stat-icon-success {
  background: var(--md-gradient-success);
  box-shadow: var(--md-shadow-success);
}

.stat-icon-warning {
  background: var(--md-gradient-warning);
  box-shadow: var(--md-shadow-warning);
}

.stat-icon-info {
  background: var(--md-gradient-info);
  box-shadow: var(--md-shadow-info);
}

.stat-icon-danger {
  background: var(--md-gradient-danger);
  box-shadow: var(--md-shadow-danger);
}

/* Progress */
.stat-progress-wrapper {
  margin-top: var(--md-space-4);
}

.stat-progress-track {
  height: 6px;
  background: var(--md-surface-container-high);
  border-radius: var(--md-shape-full);
  overflow: hidden;
}

.stat-progress-fill {
  height: 100%;
  border-radius: var(--md-shape-full);
  transition: width 1s cubic-bezier(0.34, 1.56, 0.64, 1);
  background-size: 200% 100%;
  /* animation: shimmer 2s infinite linear; Removed for performance */
}

.stat-progress-primary {
  background: linear-gradient(90deg, var(--md-primary), var(--md-secondary), var(--md-primary));
}

.stat-progress-success {
  background: linear-gradient(90deg, #10b981, #34d399, #10b981);
}

.stat-progress-warning {
  background: linear-gradient(90deg, #f59e0b, #fbbf24, #f59e0b);
}

.stat-progress-info {
  background: linear-gradient(90deg, #06b6d4, #22d3ee, #06b6d4);
}

.stat-progress-danger {
  background: linear-gradient(90deg, #ef4444, #f87171, #ef4444);
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .stat-card {
    padding: var(--md-space-4);
  }

  .value-text {
    font: var(--md-title-large);
    font-weight: 700;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
    border-radius: var(--md-shape-md);
  }
}
</style>
