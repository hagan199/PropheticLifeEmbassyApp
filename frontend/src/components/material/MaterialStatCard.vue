<template>
  <div
    class="md-stat-card"
    :class="[`md-stat-card-${variant}`, { 'md-stat-card-gradient': gradient }]"
  >
    <!-- Icon -->
    <div class="md-stat-icon" :class="`md-stat-icon-${variant}`">
      <i :class="icon"></i>
    </div>

    <!-- Content -->
    <div class="md-stat-content">
      <span class="md-stat-label">{{ label }}</span>
      <div class="md-stat-value-row">
        <span v-if="prefix" class="md-stat-prefix">{{ prefix }}</span>
        <span class="md-stat-value">{{ formattedValue }}</span>
        <span v-if="suffix" class="md-stat-suffix">{{ suffix }}</span>
      </div>

      <!-- Trend -->
      <div v-if="trend !== null && trend !== undefined" class="md-stat-trend" :class="trendClass">
        <i :class="trendIcon"></i>
        <span>{{ Math.abs(trend) }}% {{ trendLabel }}</span>
      </div>
    </div>

    <!-- Sparkline (optional) -->
    <div v-if="sparkline" class="md-stat-sparkline">
      <svg viewBox="0 0 100 30" preserveAspectRatio="none">
        <path :d="sparklinePath" fill="none" :stroke="sparklineColor" stroke-width="2" />
      </svg>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [Number, String], required: true },
  icon: { type: String, default: 'bi bi-graph-up' },
  variant: {
    type: String,
    default: 'primary',
    validator: v => ['primary', 'success', 'warning', 'error', 'info'].includes(v),
  },
  trend: { type: Number, default: null },
  trendLabel: { type: String, default: 'vs last period' },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
  gradient: { type: Boolean, default: false },
  sparkline: { type: Array, default: null },
});

// Format large numbers
const formattedValue = computed(() => {
  const val = Number(props.value);
  if (isNaN(val)) return props.value;

  if (val >= 1000000) {
    return (val / 1000000).toFixed(1) + 'M';
  } else if (val >= 1000) {
    return (val / 1000).toFixed(1) + 'K';
  }
  return val.toLocaleString();
});

// Trend styling
const trendClass = computed(() => {
  if (props.trend > 0) return 'md-stat-trend-up';
  if (props.trend < 0) return 'md-stat-trend-down';
  return 'md-stat-trend-neutral';
});

const trendIcon = computed(() => {
  if (props.trend > 0) return 'bi bi-arrow-up-short';
  if (props.trend < 0) return 'bi bi-arrow-down-short';
  return 'bi bi-dash';
});

// Sparkline
const sparklinePath = computed(() => {
  if (!props.sparkline || props.sparkline.length < 2) return '';

  const data = props.sparkline;
  const max = Math.max(...data);
  const min = Math.min(...data);
  const range = max - min || 1;

  const points = data.map((val, i) => {
    const x = (i / (data.length - 1)) * 100;
    const y = 30 - ((val - min) / range) * 26;
    return `${x},${y}`;
  });

  return `M${points.join(' L')}`;
});

const sparklineColor = computed(() => {
  const colors = {
    primary: 'var(--md-primary)',
    success: 'var(--md-success)',
    warning: 'var(--md-warning)',
    error: 'var(--md-error)',
    info: 'var(--md-info)',
  };
  return colors[props.variant];
});
</script>

<style scoped>
.md-stat-card {
  background: var(--md-surface);
  border-radius: var(--md-shape-xl);
  padding: var(--md-space-6);
  box-shadow: var(--md-elevation-2);
  position: relative;
  overflow: hidden;
  transition: all var(--md-motion-duration-medium2) var(--md-motion-easing-standard);
  display: flex;
  flex-direction: column;
  gap: var(--md-space-4);
}

.md-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--md-primary);
  opacity: 0;
  transition: opacity var(--md-motion-duration-medium2) var(--md-motion-easing-standard);
}

.md-stat-card:hover {
  box-shadow: var(--md-elevation-4);
  transform: translateY(-4px);
}

.md-stat-card:hover::before {
  opacity: 1;
}

/* Variant top border colors */
.md-stat-card-primary::before {
  background: var(--md-primary);
}
.md-stat-card-success::before {
  background: var(--md-success);
}
.md-stat-card-warning::before {
  background: var(--md-warning);
}
.md-stat-card-error::before {
  background: var(--md-error);
}
.md-stat-card-info::before {
  background: var(--md-info);
}

/* Gradient variant */
.md-stat-card-gradient.md-stat-card-primary {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
}

.md-stat-card-gradient.md-stat-card-success {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(6, 182, 212, 0.05) 100%);
}

.md-stat-card-gradient.md-stat-card-warning {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
}

.md-stat-card-gradient.md-stat-card-error {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%);
}

/* Icon */
.md-stat-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--md-shape-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.md-stat-icon-primary {
  background: var(--md-primary-container);
  color: var(--md-primary);
}

.md-stat-icon-success {
  background: var(--md-success-container);
  color: var(--md-success);
}

.md-stat-icon-warning {
  background: var(--md-warning-container);
  color: var(--md-warning);
}

.md-stat-icon-error {
  background: var(--md-error-container);
  color: var(--md-error);
}

.md-stat-icon-info {
  background: var(--md-info-container);
  color: var(--md-info);
}

/* Content */
.md-stat-content {
  flex: 1;
}

.md-stat-label {
  font: var(--md-body-medium);
  color: var(--md-on-surface-variant);
  display: block;
  margin-bottom: var(--md-space-1);
}

.md-stat-value-row {
  display: flex;
  align-items: baseline;
  gap: var(--md-space-1);
}

.md-stat-prefix {
  font: var(--md-title-medium);
  color: var(--md-on-surface-muted);
}

.md-stat-value {
  font: var(--md-headline-medium);
  color: var(--md-on-surface);
  font-weight: 700;
}

.md-stat-suffix {
  font: var(--md-body-medium);
  color: var(--md-on-surface-muted);
}

/* Trend */
.md-stat-trend {
  display: inline-flex;
  align-items: center;
  gap: var(--md-space-1);
  font: var(--md-label-medium);
  padding: var(--md-space-1) var(--md-space-2);
  border-radius: var(--md-shape-full);
  margin-top: var(--md-space-3);
}

.md-stat-trend-up {
  background: var(--md-success-container);
  color: var(--md-success);
}

.md-stat-trend-down {
  background: var(--md-error-container);
  color: var(--md-error);
}

.md-stat-trend-neutral {
  background: var(--md-surface-container-high);
  color: var(--md-on-surface-muted);
}

/* Sparkline */
.md-stat-sparkline {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 40px;
  opacity: 0.5;
}

.md-stat-sparkline svg {
  width: 100%;
  height: 100%;
}
</style>
