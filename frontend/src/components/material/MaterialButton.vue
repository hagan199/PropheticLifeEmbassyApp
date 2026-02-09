<template>
  <button
    v-bind="$attrs"
    class="md-button"
    :class="[
      `md-button-${variant}`,
      `md-button-${size}`,
      { 'md-button-icon-only': iconOnly, 'is-loading': loading },
    ]"
    :disabled="disabled || loading"
  >
    <div v-if="loading" class="spinner-border spinner-border-sm me-2" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>

    <span v-if="icon && !loading" :class="['md-button-icon', { 'me-2': !iconOnly }]">
      <i :class="icon"></i>
    </span>

    <span v-if="!iconOnly" class="md-button-label">
      <slot></slot>
    </span>
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'filled',
    validator: v => ['filled', 'tonal', 'outlined', 'text', 'error', 'success'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: v => ['sm', 'md', 'lg'].includes(v),
  },
  icon: { type: String, default: '' },
  iconOnly: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});
</script>

<style scoped>
.md-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: var(--md-shape-full);
  font-family: inherit;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  white-space: nowrap;
}

/* Sizes */
.md-button-sm {
  height: 32px;
  padding: 0 12px;
  font-size: 0.8125rem;
}

.md-button-md {
  height: 48px;
  padding: 0 24px;
  font-size: 0.9375rem;
}

.md-button-lg {
  height: 56px;
  padding: 0 32px;
  font-size: 1rem;
}

.md-button-icon-only.md-button-sm {
  width: 32px;
  padding: 0;
}

.md-button-icon-only.md-button-md {
  width: 48px;
  padding: 0;
}

.md-button-icon-only.md-button-lg {
  width: 56px;
  padding: 0;
}

/* Variants */
.md-button-filled {
  background: var(--md-primary);
  color: var(--md-on-primary);
  box-shadow: var(--md-elevation-1);
}

.md-button-filled:hover:not(:disabled) {
  background: var(--md-primary-hover);
  box-shadow: var(--md-elevation-2);
}

.md-button-tonal {
  background: var(--md-secondary-container);
  color: var(--md-on-secondary-container);
}

.md-button-tonal:hover:not(:disabled) {
  background: var(--md-secondary-container-hover);
}

.md-button-outlined {
  background: transparent;
  border: 1px solid var(--md-outline);
  color: var(--md-primary);
}

.md-button-outlined:hover:not(:disabled) {
  background: var(--md-primary-container-low);
  border-color: var(--md-primary);
}

.md-button-text {
  background: transparent;
  color: var(--md-primary);
}

.md-button-text:hover:not(:disabled) {
  background: var(--md-primary-container-low);
}

.md-button-error {
  background: var(--md-error);
  color: var(--md-on-error);
}

.md-button-success {
  background: var(--md-success);
  color: white;
}

/* States */
.md-button:disabled {
  opacity: 0.38;
  cursor: not-allowed;
  box-shadow: none;
}

.md-button-icon {
  font-size: 1.25em;
  display: flex;
  align-items: center;
}

.is-loading {
  pointer-events: none;
}
</style>
