<template>
  <div class="md-card" :class="[`md-card-${variant}`, { 'md-card-interactive': interactive }]">
    <!-- Header (optional) -->
    <div v-if="title || $slots.header" class="md-card-header">
      <slot name="header">
        <div class="md-card-header-content">
          <h3 class="md-card-title">{{ title }}</h3>
          <p v-if="subtitle" class="md-card-subtitle">{{ subtitle }}</p>
        </div>
      </slot>
      <div v-if="$slots.actions" class="md-card-header-actions">
        <slot name="actions" />
      </div>
    </div>

    <!-- Body -->
    <div class="md-card-body" :class="{ 'md-card-body-flush': flush }">
      <slot />
    </div>

    <!-- Footer (optional) -->
    <div v-if="$slots.footer" class="md-card-footer">
      <slot name="footer" />
    </div>

    <!-- Loading overlay -->
    <div v-if="loading" class="md-card-loading">
      <div class="md-spinner"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  variant: {
    type: String,
    default: 'elevated',
    validator: v => ['elevated', 'filled', 'outlined', 'glass'].includes(v),
  },
  interactive: { type: Boolean, default: false },
  flush: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
});
</script>

<style scoped>
.md-card {
  background: var(--md-surface);
  border-radius: var(--md-shape-lg);
  position: relative;
  overflow: hidden;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
  will-change: transform, box-shadow;
}

/* Variants */
.md-card-elevated {
  box-shadow: var(--md-elevation-2);
}

.md-card-elevated.md-card-interactive:hover {
  box-shadow: var(--md-elevation-4);
  transform: translateY(-2px);
}

.md-card-filled {
  background: var(--md-surface-container-highest);
  box-shadow: none;
}

.md-card-outlined {
  background: var(--md-surface);
  box-shadow: none;
  border: 1px solid var(--md-outline-variant);
}

.md-card-glass {
  background: var(--md-glass-background);
  backdrop-filter: var(--md-glass-blur) var(--md-glass-saturate);
  -webkit-backdrop-filter: var(--md-glass-blur) var(--md-glass-saturate);
  border: 1px solid var(--md-glass-border);
  box-shadow: var(--md-elevation-2);
}

/* Header */
.md-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--md-space-4);
  padding: var(--md-space-6);
  border-bottom: 1px solid var(--md-outline-variant);
}

.md-card-header-content {
  flex: 1;
  min-width: 0;
}

.md-card-title {
  font: var(--md-title-large);
  color: var(--md-on-surface);
  margin: 0;
}

.md-card-subtitle {
  font: var(--md-body-medium);
  color: var(--md-on-surface-variant);
  margin: var(--md-space-1) 0 0;
}

.md-card-header-actions {
  display: flex;
  align-items: center;
  gap: var(--md-space-2);
  flex-shrink: 0;
}

/* Body */
.md-card-body {
  padding: var(--md-space-6);
}

.md-card-body-flush {
  padding: 0;
}

/* Footer */
.md-card-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--md-space-3);
  padding: var(--md-space-4) var(--md-space-6);
  border-top: 1px solid var(--md-outline-variant);
  background: var(--md-surface-container-low);
}

/* Loading */
.md-card-loading {
  position: absolute;
  inset: 0;
  background: var(--md-glass-background);
  backdrop-filter: var(--md-glass-blur);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.md-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--md-surface-container-high);
  border-top-color: var(--md-primary);
  border-radius: var(--md-shape-full);
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
