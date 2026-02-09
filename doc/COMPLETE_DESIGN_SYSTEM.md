# Complete Design System & Architecture Guide

> **Prophetic Life Embassy Church Management System**
> Comprehensive UI/UX, API Integration, and Component Architecture Reference

---

## Table of Contents

1. [Design System Foundation](#design-system-foundation)
2. [Component Library](#component-library)
3. [API Architecture](#api-architecture)
4. [State Management Patterns](#state-management-patterns)
5. [Animation & Transitions](#animation--transitions)
6. [Responsive Design](#responsive-design)
7. [Accessibility Guidelines](#accessibility-guidelines)
8. [Implementation Checklist](#implementation-checklist)

---

## Design System Foundation

### Color Palette

#### Primary Colors
```css
--primary: #6366f1;           /* Indigo - Primary brand color */
--primary-dark: #4f46e5;      /* Darker indigo for hovers */
--primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
```

#### Semantic Colors
```css
/* Success - Used for partners, confirmations */
--success: #10b981;
--success-gradient: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
--success-subtle: #f0fdf4;

/* Info - Used for membership interest */
--info: #ec4899;
--info-gradient: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
--info-subtle: #ecfeff;

/* Warning - Used for alerts, weekly metrics */
--warning: #f59e0b;
--warning-gradient: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
--warning-subtle: #fef3c7;

/* Error/Danger - Used for deletions, errors */
--error: #ef4444;
--error-subtle: #fee2e2;
```

#### Surface & Background Colors
```css
--surface: #ffffff;
--surface-container-highest: #f8fafc;
--surface-container-high: #f1f5f9;
--surface-container-low: #e2e8f0;
--glass-background: rgba(255, 255, 255, 0.8);
```

#### Text Colors
```css
--text-primary: #1e293b;      /* Main headings */
--text-secondary: #475569;    /* Body text */
--text-muted: #64748b;        /* Labels, secondary info */
--text-subtle: #94a3b8;       /* Hints, placeholder */
--text-disabled: #cbd5e1;     /* Disabled states */
```

#### Border & Outline
```css
--outline-variant: #e2e8f0;
--outline-strong: #cbd5e1;
```

### Typography System

#### Font Weights
```css
--font-weight-extra-bold: 800;  /* Page titles, KPI values */
--font-weight-bold: 700;        /* Card titles */
--font-weight-semibold: 600;    /* Labels, buttons */
--font-weight-medium: 500;      /* Body text emphasis */
--font-weight-regular: 400;     /* Body text */
```

#### Font Sizes
```css
--font-size-3xl: 2.25rem;      /* 36px - Page titles */
--font-size-2xl: 1.875rem;     /* 30px - KPI values */
--font-size-xl: 1.5rem;        /* 24px - Section headers */
--font-size-lg: 1.25rem;       /* 20px - Card titles */
--font-size-base: 1rem;        /* 16px - Body text */
--font-size-sm: 0.875rem;      /* 14px - Labels */
--font-size-xs: 0.8rem;        /* 12.8px - Sublabels */
--font-size-xxs: 0.7rem;       /* 11.2px - Metadata */
```

#### Typography Classes
```css
.md-title-large {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--text-primary);
}

.md-title-medium {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.md-body-medium {
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-regular);
  color: var(--text-secondary);
}
```

### Spacing System

#### Space Scale (8px base)
```css
--md-space-1: 0.25rem;   /* 4px */
--md-space-2: 0.5rem;    /* 8px */
--md-space-3: 0.75rem;   /* 12px */
--md-space-4: 1rem;      /* 16px */
--md-space-5: 1.25rem;   /* 20px */
--md-space-6: 1.5rem;    /* 24px */
--md-space-8: 2rem;      /* 32px */
--md-space-10: 2.5rem;   /* 40px */
```

### Border Radius System

```css
--md-shape-xs: 8px;      /* Small elements */
--md-shape-sm: 10px;     /* Buttons, badges */
--md-shape-md: 12px;     /* Inputs, mini cards */
--md-shape-lg: 16px;     /* Cards, containers */
--md-shape-xl: 20px;     /* Large cards */
--md-shape-full: 50%;    /* Circular elements */
```

### Elevation System (Box Shadows)

```css
--md-elevation-1: 0 2px 4px rgba(0, 0, 0, 0.04);
--md-elevation-2: 0 4px 20px rgba(0, 0, 0, 0.05);
--md-elevation-3: 0 8px 30px rgba(0, 0, 0, 0.08);
--md-elevation-4: 0 12px 40px rgba(0, 0, 0, 0.1);

/* Colored shadows for emphasis */
--md-elevation-primary: 0 4px 12px rgba(99, 102, 241, 0.2);
--md-elevation-primary-hover: 0 8px 20px rgba(99, 102, 241, 0.3);
```

---

## Component Library

### 1. KPI Summary Cards

#### Usage
Display key performance indicators with animated entrance and gradient icons.

#### Component Structure
```vue
<template>
  <div class="kpi-grid">
    <div class="kpi-card" style="--delay: 0s">
      <div class="kpi-icon" :style="iconGradient">
        <i :class="icon"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-label">{{ label }}</div>
        <div class="kpi-value">{{ value }}</div>
        <div class="kpi-sublabel">{{ sublabel }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideUp 0.5s ease-out forwards;
  opacity: 0;
  animation-delay: var(--delay, 0s);
}

.kpi-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
}

.kpi-icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.kpi-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.kpi-value {
  font-size: 1.875rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.25rem;
  line-height: 1;
}

.kpi-sublabel {
  font-size: 0.8rem;
  color: #94a3b8;
  font-weight: 500;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .kpi-card {
    padding: 1.25rem;
  }
}
</style>
```

#### Gradient Presets
```javascript
const gradients = {
  primary: 'background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
  success: 'background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%)',
  info: 'background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)',
  warning: 'background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%)'
}
```

#### Stagger Animation Pattern
```vue
<!-- Delay each card by 0.1s increments -->
<div class="kpi-card" style="--delay: 0s"></div>
<div class="kpi-card" style="--delay: 0.1s"></div>
<div class="kpi-card" style="--delay: 0.2s"></div>
<div class="kpi-card" style="--delay: 0.3s"></div>
```

---

### 2. Material Card Component

#### Component File: `MaterialCard.vue`

```vue
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
    validator: (v) => ['elevated', 'filled', 'outlined', 'glass'].includes(v)
  },
  interactive: { type: Boolean, default: false },
  flush: { type: Boolean, default: false },
  loading: { type: Boolean, default: false }
})
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
  backdrop-filter: blur(10px) saturate(150%);
  -webkit-backdrop-filter: blur(10px) saturate(150%);
  border: 1px solid rgba(255, 255, 255, 0.2);
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
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.md-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f1f5f9;
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
```

#### Usage Examples

**Basic Card**
```vue
<MaterialCard title="Card Title" subtitle="Card subtitle">
  Content goes here
</MaterialCard>
```

**Card with Custom Header**
```vue
<MaterialCard>
  <template #header>
    <div class="d-flex align-items-center gap-3">
      <div class="header-icon-box bg-primary-subtle text-primary">
        <i class="bi bi-person-plus-fill"></i>
      </div>
      <div>
        <h3 class="md-title-large mb-1">Register Visitor</h3>
        <p class="text-muted small mb-0">Add a new visitor or partner</p>
      </div>
    </div>
  </template>
  <CForm @submit.prevent="handleSubmit">
    <!-- Form content -->
  </CForm>
</MaterialCard>
```

**Interactive Card with Loading**
```vue
<MaterialCard variant="elevated" :interactive="true" :loading="isLoading">
  Content
</MaterialCard>
```

---

### 3. Material Input Component

#### Component File: `MaterialInput.vue`

```vue
<template>
  <div class="md-input-container" :class="{ 'has-value': !!modelValue, 'has-error': !!error, 'is-focused': isFocused }">
    <div class="md-input-wrapper">
      <!-- Textarea -->
      <textarea
        v-if="type === 'textarea'"
        :id="id"
        v-bind="$attrs"
        :value="modelValue"
        class="md-input"
        :placeholder="placeholder || ' '"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false">
      </textarea>

      <!-- Select -->
      <select
        v-else-if="type === 'select'"
        :id="id"
        v-bind="$attrs"
        :value="modelValue"
        class="md-input md-select"
        @change="$emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false">
        <slot></slot>
      </select>

      <!-- Input -->
      <input
        v-else
        :id="id"
        :type="type"
        v-bind="$attrs"
        :value="modelValue"
        class="md-input"
        :placeholder="placeholder || ' '"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false" />

      <label v-if="label" :for="id" class="md-label-floating">
        {{ label }}
        <span v-if="required" class="required-star">*</span>
      </label>

      <div v-if="icon" class="md-input-icon">
        <i :class="icon"></i>
      </div>

      <div class="md-input-indicator"></div>
    </div>

    <div v-if="error" class="md-input-error-text animate-fadeInUp">
      <i class="bi bi-exclamation-circle-fill me-1"></i> {{ error }}
    </div>
    <div v-else-if="hint" class="md-input-hint-text">
      {{ hint }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  id: { type: String, default: () => `md-input-${Math.random().toString(36).substr(2, 9)}` },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  icon: { type: String, default: '' },
  placeholder: { type: String, default: ' ' },
  required: { type: Boolean, default: false }
})

defineEmits(['update:modelValue'])

const isFocused = ref(false)
</script>

<style scoped>
.md-input-container {
  margin-bottom: var(--md-space-4);
  width: 100%;
}

.md-input-wrapper {
  position: relative;
  display: flex;
  flex-direction: column;
}

.md-input {
  width: 100%;
  padding: 24px 16px 8px;
  background: #f8fafc;
  border: none;
  border-radius: 12px 12px 0 0;
  font-size: 1rem;
  color: #1e293b;
  transition: background-color 0.2s;
  outline: none;
}

.md-input:hover {
  background: #f1f5f9;
}

.md-input:focus {
  background: #e2e8f0;
}

.md-select {
  appearance: none;
  cursor: pointer;
}

textarea.md-input {
  min-height: 100px;
  resize: vertical;
}

.md-label-floating {
  position: absolute;
  top: 18px;
  left: 16px;
  font-size: 1rem;
  color: #64748b;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
  display: flex;
  align-items: center;
  gap: 4px;
}

.md-input:focus ~ .md-label-floating,
.md-input:not(:placeholder-shown) ~ .md-label-floating,
.md-input-container.has-value .md-label-floating,
.md-select:not([value=""]) ~ .md-label-floating {
  top: 8px;
  font-size: 0.75rem;
  color: #6366f1;
  font-weight: 600;
}

.md-input-indicator {
  height: 2px;
  width: 100%;
  background: #e2e8f0;
  transform: scaleX(1);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s;
}

.md-input:focus ~ .md-input-indicator {
  background: #6366f1;
  transform: scaleX(1);
  height: 3px;
}

.md-input-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  opacity: 0.5;
}

.md-input-error-text {
  color: #ef4444;
  font-size: 0.75rem;
  margin-top: 4px;
  padding-left: 4px;
  font-weight: 500;
}

.md-input-hint-text {
  color: #64748b;
  font-size: 0.75rem;
  margin-top: 4px;
  padding-left: 4px;
  opacity: 0.7;
}

.required-star {
  color: #ef4444;
}

.has-error .md-input-indicator {
  background: #ef4444 !important;
}

.has-error .md-label-floating {
  color: #ef4444 !important;
}
</style>
```

#### Usage Examples

**Text Input**
```vue
<MaterialInput v-model="form.name" label="Full Name" required />
```

**Select Dropdown**
```vue
<MaterialInput v-model="form.category" label="Category" type="select" required>
  <option value="Visitor">Visitor</option>
  <option value="Partner">Partner</option>
  <option value="Wants to be a Member">Wants to be a Member</option>
</MaterialInput>
```

**Input with Error**
```vue
<MaterialInput
  v-model="form.phone"
  label="Phone Number"
  required
  :error="phoneError"
/>
```

**Date Input**
```vue
<MaterialInput
  type="date"
  v-model="form.date"
  label="First Visit Date"
  required
/>
```

---

### 4. Material Button Component

#### Component File: `MaterialButton.vue`

```vue
<template>
  <button
    class="md-btn"
    :class="[
      `md-btn-${variant}`,
      { 'md-btn-loading': loading, 'md-btn-icon-only': iconOnly }
    ]"
    :disabled="disabled || loading"
    v-bind="$attrs">
    <CSpinner v-if="loading" size="sm" class="me-2" />
    <i v-else-if="icon && !iconOnly" :class="icon" class="me-2"></i>
    <i v-else-if="icon && iconOnly" :class="icon"></i>
    <span v-if="!iconOnly"><slot /></span>
  </button>
</template>

<script setup>
import { CSpinner } from '@coreui/vue'

defineProps({
  variant: {
    type: String,
    default: 'filled',
    validator: (v) => ['filled', 'outlined', 'tonal', 'text'].includes(v)
  },
  icon: { type: String, default: '' },
  iconOnly: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false }
})
</script>

<style scoped>
.md-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.625rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  cursor: pointer;
  line-height: 1.5;
}

.md-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.md-btn-icon-only {
  padding: 0.625rem;
  width: 40px;
  height: 40px;
}

/* Filled variant - Primary action */
.md-btn-filled {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.md-btn-filled:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
}

/* Outlined variant - Secondary action */
.md-btn-outlined {
  background: transparent;
  color: #6366f1;
  border: 1.5px solid #6366f1;
}

.md-btn-outlined:hover:not(:disabled) {
  background: rgba(99, 102, 241, 0.04);
  border-color: #4f46e5;
}

/* Tonal variant - Tertiary action */
.md-btn-tonal {
  background: #f1f5f9;
  color: #475569;
  border: none;
}

.md-btn-tonal:hover:not(:disabled) {
  background: #e2e8f0;
}

/* Text variant - Low emphasis */
.md-btn-text {
  background: transparent;
  color: #6366f1;
  border: none;
}

.md-btn-text:hover:not(:disabled) {
  background: rgba(99, 102, 241, 0.04);
}

/* Danger variants */
.md-btn-filled.bg-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.md-btn-filled.bg-danger:hover:not(:disabled) {
  background: #dc2626;
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}
</style>
```

#### Usage Examples

```vue
<!-- Primary action -->
<MaterialButton type="submit" :loading="isSubmitting" icon="bi bi-person-plus-fill">
  Register Visitor
</MaterialButton>

<!-- Secondary action -->
<MaterialButton variant="outlined" icon="bi bi-download">
  Export
</MaterialButton>

<!-- Tertiary action -->
<MaterialButton variant="tonal" @click="cancel">
  Cancel
</MaterialButton>

<!-- Icon only button -->
<MaterialButton variant="text" icon="bi bi-pencil-fill" :iconOnly="true" />

<!-- Danger action -->
<MaterialButton variant="filled" class="bg-danger" :loading="isDeleting">
  Delete
</MaterialButton>
```

---

### 5. Skeleton Loader Component

#### Component Structure

```vue
<template>
  <div v-if="isLoading" class="skeleton-container">
    <div v-for="i in rows" :key="i" class="skeleton-row">
      <div class="skeleton skeleton-avatar"></div>
      <div class="skeleton skeleton-text" style="width: 35%"></div>
      <div class="skeleton skeleton-text" style="width: 20%"></div>
      <div class="skeleton skeleton-badge" style="width: 15%"></div>
      <div class="skeleton skeleton-text" style="width: 15%"></div>
      <div class="skeleton skeleton-actions" style="width: 10%"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isLoading: { type: Boolean, default: false },
  rows: { type: Number, default: 5 }
})
</script>

<style scoped>
.skeleton-container {
  padding: 1.5rem 0;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  margin-bottom: 0.75rem;
  background: white;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.04);
}

.skeleton {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  flex-shrink: 0;
}

.skeleton-text {
  height: 16px;
  flex: 1;
}

.skeleton-badge {
  height: 28px;
  border-radius: 14px;
}

.skeleton-actions {
  height: 36px;
  border-radius: 10px;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>
```

---

### 6. Smart Pagination Component

#### Component Structure

```vue
<template>
  <div class="pagination-container">
    <div class="pagination-info">
      Showing <span class="fw-bold text-dark">{{ startRecord }}</span> to
      <span class="fw-bold text-dark">{{ endRecord }}</span>
      of <span class="fw-bold text-dark">{{ total }}</span> records
    </div>
    <CPagination class="mb-0">
      <CPaginationItem
        :disabled="currentPage === 1"
        @click="$emit('page-change', currentPage - 1)">
        <i class="bi bi-chevron-left"></i>
      </CPaginationItem>
      <CPaginationItem
        v-for="page in displayPages"
        :key="page"
        :active="page === currentPage"
        @click="page !== '...' && $emit('page-change', page)">
        {{ page }}
      </CPaginationItem>
      <CPaginationItem
        :disabled="currentPage === lastPage"
        @click="$emit('page-change', currentPage + 1)">
        <i class="bi bi-chevron-right"></i>
      </CPaginationItem>
    </CPagination>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CPagination, CPaginationItem } from '@coreui/vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  total: { type: Number, required: true },
  perPage: { type: Number, default: 10 }
})

defineEmits(['page-change'])

const startRecord = computed(() => (props.currentPage - 1) * props.perPage + 1)
const endRecord = computed(() => Math.min(props.currentPage * props.perPage, props.total))

// Smart pagination display (show max 7 pages with ellipsis)
const displayPages = computed(() => {
  const current = props.currentPage
  const last = props.lastPage

  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  if (current <= 3) {
    return [1, 2, 3, 4, 5, '...', last]
  }

  if (current >= last - 2) {
    return [1, '...', last - 4, last - 3, last - 2, last - 1, last]
  }

  return [1, '...', current - 1, current, current + 1, '...', last]
})
</script>

<style scoped>
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.25rem;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  background: #fafbfc;
}

.pagination-info {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

:deep(.pagination) {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 6px;
}

:deep(.pagination .page-item) {
  margin: 0;
}

:deep(.page-link) {
  border-radius: 10px !important;
  border: none !important;
  background: white !important;
  color: #64748b !important;
  min-width: 38px !important;
  height: 38px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-weight: 600 !important;
  font-size: 0.875rem !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  padding: 0 12px !important;
}

:deep(.page-link:hover) {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%) !important;
  color: #6366f1 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.15) !important;
}

:deep(.page-item.active .page-link) {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
  color: white !important;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
  transform: translateY(-2px) !important;
}

:deep(.page-item.disabled .page-link) {
  opacity: 0.4 !important;
  cursor: not-allowed !important;
  background: #f1f5f9 !important;
}

@media (max-width: 768px) {
  .pagination-container {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-info {
    font-size: 0.8rem;
  }
}
</style>
```

---

### 7. Badge Components

#### Usage Examples

```vue
<!-- Partner badge -->
<span class="badge rounded-pill px-3 py-2 border-0 bg-success-subtle text-success">
  <i class="bi bi-star-fill me-1"></i>
  Partner
</span>

<!-- Visitor badge -->
<span class="badge rounded-pill px-3 py-2 border-0 bg-primary-subtle text-primary">
  <i class="bi bi-person me-1"></i>
  Visitor
</span>

<!-- Member Interest badge -->
<span class="badge rounded-pill px-3 py-2 border-0 bg-info-subtle text-info">
  <i class="bi bi-person-heart me-1"></i>
  Member Int.
</span>
```

#### Badge Styles

```css
.badge {
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.bg-success-subtle {
  background-color: #f0fdf4 !important;
}

.bg-primary-subtle {
  background-color: #eff6ff !important;
}

.bg-info-subtle {
  background-color: #ecfeff !important;
}

.text-success {
  color: #10b981 !important;
}

.text-primary {
  color: #6366f1 !important;
}

.text-info {
  color: #0891b2 !important;
}
```

---

### 8. Icon Button Component

```vue
<template>
  <button
    class="md-icon-btn"
    :class="colorClass"
    :title="title"
    @click="$emit('click')">
    <i :class="icon"></i>
  </button>
</template>

<script setup>
defineProps({
  icon: { type: String, required: true },
  title: { type: String, default: '' },
  colorClass: { type: String, default: 'bg-light text-primary' }
})

defineEmits(['click'])
</script>

<style scoped>
.md-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  transition: all 0.2s;
  font-size: 1rem;
  cursor: pointer;
}

.md-icon-btn:hover {
  transform: translateY(-2px);
  filter: brightness(0.95);
}

.bg-light {
  background-color: #f8fafc;
}

.text-primary {
  color: #6366f1;
}

.text-danger {
  color: #ef4444;
}
</style>
```

#### Usage

```vue
<button class="md-icon-btn bg-light text-primary" @click="editItem" title="Edit">
  <i class="bi bi-pencil-fill"></i>
</button>

<button class="md-icon-btn bg-light text-danger" @click="deleteItem" title="Delete">
  <i class="bi bi-trash3-fill"></i>
</button>
```

---

## API Architecture

### API Instance Configuration

#### File: `frontend/src/api/index.js`

```javascript
import axios from "axios";

// Token cache for performance
let cachedToken = localStorage.getItem("auth_token");

// Create axios instance with base configuration
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  withCredentials: true, // Important for Sanctum cookies
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    if (cachedToken) {
      config.headers.Authorization = `Bearer ${cachedToken}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// Response interceptor to handle errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle 401 Unauthorized - token expired or invalid
    if (error.response?.status === 401) {
      // Clear stored auth data
      cachedToken = null;
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");

      // Redirect to signin if not already there
      if (window.location.pathname !== "/signin" && window.location.pathname !== "/login") {
        window.location.href = "/signin";
      }
    }

    return Promise.reject(error);
  },
);

/**
 * Update the cached token (used by auth store)
 * @param {string|null} token
 */
export const updateApiToken = (token) => {
  cachedToken = token;
};

export default api;
```

---

### API Service Pattern

#### Generic Resource API Template

```javascript
import api from "./index";

/**
 * [Resource] API endpoints
 */
export const resourceApi = {
  /**
   * Get paginated list
   * @param {Object} params - Query parameters (page, per_page, search, etc.)
   * @returns {Promise} API response with data list
   */
  getAll(params = {}) {
    return api.get("/resources", { params });
  },

  /**
   * Get a single resource by ID
   * @param {number|string} id - Resource ID
   * @returns {Promise} API response with resource data
   */
  get(id) {
    return api.get(`/resources/${id}`);
  },

  /**
   * Create a new resource
   * @param {Object} data - Resource data
   * @returns {Promise} API response
   */
  create(data) {
    return api.post("/resources", data);
  },

  /**
   * Update an existing resource
   * @param {number|string} id - Resource ID
   * @param {Object} data - Updated resource data
   * @returns {Promise} API response
   */
  update(id, data) {
    return api.put(`/resources/${id}`, data);
  },

  /**
   * Delete a resource
   * @param {number|string} id - Resource ID
   * @returns {Promise} API response
   */
  delete(id) {
    return api.delete(`/resources/${id}`);
  },
};

export default resourceApi;
```

---

### Component Integration Pattern

#### Complete CRUD Example

```vue
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { resourceApi } from '../api/resource'
import { useToast } from '../composables/useToast'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')
const items = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
})

// Form state
const form = ref({
  name: '',
  email: '',
  category: ''
})

// Modal state
const editModalVisible = ref(false)
const deleteModalVisible = ref(false)
const editItem = ref(null)
const itemToDelete = ref(null)
const isSavingEdit = ref(false)
const isDeleting = ref(false)

// Toast
const toast = useToast()

// Lifecycle
onMounted(() => {
  fetchItems()
})

// Watch search query
watch(searchQuery, () => {
  pagination.value.current_page = 1
  fetchItems()
})

// Fetch items
async function fetchItems(page = 1) {
  isLoading.value = true
  try {
    const res = await resourceApi.getAll({
      page,
      search: searchQuery.value,
      per_page: pagination.value.per_page
    })

    items.value = res.data?.data || []
    pagination.value = {
      current_page: res.data?.current_page || 1,
      last_page: res.data?.last_page || 1,
      total: res.data?.total || 0,
      per_page: res.data?.per_page || 10
    }
  } catch (err) {
    toast.error('Failed to load items')
  } finally {
    isLoading.value = false
  }
}

// Create item
async function createItem() {
  if (!form.value.name.trim()) return

  isSubmitting.value = true
  try {
    const res = await resourceApi.create(form.value)

    if (res.data.success) {
      toast.success('Item created successfully')
      // Reset form
      form.value = { name: '', email: '', category: '' }
      fetchItems()
    }
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to create item'
    toast.error(message)
  } finally {
    isSubmitting.value = false
  }
}

// Open edit modal
function openEditModal(item) {
  editItem.value = { ...item }
  editModalVisible.value = true
}

// Save edited item
async function saveEdit() {
  if (!editItem.value?.id) return

  isSavingEdit.value = true
  try {
    const res = await resourceApi.update(editItem.value.id, editItem.value)

    if (res.data.success) {
      toast.success('Item updated successfully')
      closeEditModal()
      await fetchItems(pagination.value.current_page)
    }
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to update item'
    toast.error(message)
  } finally {
    isSavingEdit.value = false
  }
}

// Close edit modal
function closeEditModal() {
  if (isSavingEdit.value) return
  editModalVisible.value = false
  editItem.value = null
}

// Confirm delete
function confirmDelete(item) {
  itemToDelete.value = item
  deleteModalVisible.value = true
}

// Delete item
async function deleteItem() {
  if (!itemToDelete.value) return

  isDeleting.value = true
  try {
    const res = await resourceApi.delete(itemToDelete.value.id)

    if (res.data.success) {
      toast.success('Item deleted successfully')
      closeDeleteModal()

      // Adjust page if needed
      const maxPage = Math.ceil((pagination.value.total - 1) / pagination.value.per_page)
      const targetPage = Math.min(pagination.value.current_page, Math.max(1, maxPage))

      await fetchItems(targetPage)
    }
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to delete item'
    toast.error(message)
  } finally {
    isDeleting.value = false
  }
}

// Close delete modal
function closeDeleteModal() {
  if (isDeleting.value) return
  deleteModalVisible.value = false
  itemToDelete.value = null
}

// Change page
function changePage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  fetchItems(page)
}
</script>
```

---

### Error Handling Patterns

#### Standard Error Response Structure

```javascript
{
  success: false,
  message: "Error message here",
  errors: {
    field_name: ["Validation error 1", "Validation error 2"]
  }
}
```

#### Error Handler Utility

```javascript
/**
 * Extract error message from API response
 * @param {Error} error - Axios error object
 * @param {string} defaultMessage - Fallback message
 * @returns {string} Error message
 */
export function getErrorMessage(error, defaultMessage = 'An error occurred') {
  if (error.response?.data?.message) {
    return error.response.data.message
  }

  if (error.response?.data?.errors) {
    const errors = error.response.data.errors
    const firstError = Object.values(errors)[0]
    return Array.isArray(firstError) ? firstError[0] : firstError
  }

  if (error.message) {
    return error.message
  }

  return defaultMessage
}
```

#### Usage in Components

```javascript
import { getErrorMessage } from '../utils/errorHandler'

try {
  await resourceApi.create(data)
} catch (err) {
  toast.error(getErrorMessage(err, 'Failed to create item'))
}
```

---

## State Management Patterns

### Composable Pattern (Recommended)

#### Example: `useToast.js`

```javascript
import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const success = (message, duration = 3000) => {
    addToast('success', message, duration)
  }

  const error = (message, duration = 4000) => {
    addToast('error', message, duration)
  }

  const info = (message, duration = 3000) => {
    addToast('info', message, duration)
  }

  const warning = (message, duration = 3000) => {
    addToast('warning', message, duration)
  }

  const addToast = (type, message, duration) => {
    const id = ++toastId
    toasts.value.push({ id, type, message })

    setTimeout(() => {
      removeToast(id)
    }, duration)
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  return {
    toasts,
    success,
    error,
    info,
    warning,
    removeToast
  }
}
```

---

### Vuex Store Pattern

#### Example Store Module: `visitors.js`

```javascript
import { visitorsApi } from '@/api/visitors'

export default {
  namespaced: true,

  state: {
    visitors: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 10
    },
    counts: {
      visitor_count: 0,
      partner_count: 0,
      member_count: 0
    },
    isLoading: false,
    error: null
  },

  getters: {
    visitorsList: state => state.visitors,
    totalVisitors: state => state.pagination.total,
    visitorCount: state => state.counts.visitor_count,
    partnerCount: state => state.counts.partner_count,
    memberCount: state => state.counts.member_count,
    isLoading: state => state.isLoading
  },

  mutations: {
    SET_VISITORS(state, visitors) {
      state.visitors = visitors
    },
    SET_PAGINATION(state, pagination) {
      state.pagination = pagination
    },
    SET_COUNTS(state, counts) {
      state.counts = counts
    },
    SET_LOADING(state, isLoading) {
      state.isLoading = isLoading
    },
    SET_ERROR(state, error) {
      state.error = error
    }
  },

  actions: {
    async fetchVisitors({ commit }, { page = 1, search = '', per_page = 10 } = {}) {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      try {
        const res = await visitorsApi.getAll({ page, search, per_page })

        commit('SET_VISITORS', res.data?.data || [])
        commit('SET_PAGINATION', {
          current_page: res.data?.current_page || 1,
          last_page: res.data?.last_page || 1,
          total: res.data?.total || 0,
          per_page: res.data?.per_page || 10
        })
        commit('SET_COUNTS', {
          visitor_count: res.data?.visitor_count || 0,
          partner_count: res.data?.partner_count || 0,
          member_count: res.data?.member_count || 0
        })
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Failed to load visitors')
        throw error
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async createVisitor({ dispatch }, data) {
      const res = await visitorsApi.create(data)
      await dispatch('fetchVisitors')
      return res
    },

    async updateVisitor({ dispatch }, { id, data }) {
      const res = await visitorsApi.update(id, data)
      await dispatch('fetchVisitors')
      return res
    },

    async deleteVisitor({ dispatch }, id) {
      const res = await visitorsApi.delete(id)
      await dispatch('fetchVisitors')
      return res
    }
  }
}
```

---

## Animation & Transitions

### CSS Transitions

```css
/* Standard transition timing */
.transition-standard {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Fast transition for micro-interactions */
.transition-fast {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Slow transition for emphasis */
.transition-slow {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Keyframe Animations

```css
/* Slide up animation for cards */
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Fade in animation */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Shimmer animation for skeleton loaders */
@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Spin animation for loaders */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Scale in animation */
@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
```

### Animation Classes

```css
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

.animate-fadeInUp {
  animation: slideUp 0.4s ease-out;
}

.animate-scaleIn {
  animation: scaleIn 0.3s ease-out;
}

/* Stagger delays for sequential animations */
.stagger-1 {
  animation-delay: 0.1s;
}

.stagger-2 {
  animation-delay: 0.2s;
}

.stagger-3 {
  animation-delay: 0.3s;
}

.stagger-4 {
  animation-delay: 0.4s;
}
```

---

## Responsive Design

### Breakpoints

```css
/* Mobile first approach */
/* Extra small devices (phones, less than 576px) */
/* No media query needed - this is the default */

/* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) {
  /* Styles */
}

/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
  /* Styles */
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
  /* Styles */
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
  /* Styles */
}

/* XXL devices (larger desktops, 1400px and up) */
@media (min-width: 1400px) {
  /* Styles */
}
```

### Responsive Patterns

#### Grid Stacking

```css
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
}

@media (max-width: 768px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }
}
```

#### Flexbox Wrapping

```css
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

@media (max-width: 768px) {
  .pagination-container {
    flex-direction: column;
  }
}
```

#### Responsive Typography

```css
.page-title {
  font-size: 2.25rem;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 1.75rem;
  }
}
```

---

## Accessibility Guidelines

### Keyboard Navigation

```vue
<!-- Ensure all interactive elements are keyboard accessible -->
<button
  @click="handleClick"
  @keydown.enter="handleClick"
  @keydown.space.prevent="handleClick"
  tabindex="0">
  Click me
</button>
```

### ARIA Labels

```vue
<!-- Screen reader support -->
<button aria-label="Close modal" @click="close">
  <i class="bi bi-x"></i>
</button>

<input
  type="search"
  aria-label="Search visitors"
  placeholder="Search name/phone..."
/>
```

### Focus States

```css
/* Visible focus indicators */
.md-btn:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

.md-input:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: -2px;
}
```

### Color Contrast

All text must meet WCAG AA standards:
- Normal text: 4.5:1 contrast ratio
- Large text (18pt+): 3:1 contrast ratio
- Interactive elements: 3:1 contrast ratio

```css
/* Good contrast examples */
.text-on-white {
  color: #1e293b; /* Dark slate on white - 13:1 ratio */
}

.text-muted-on-white {
  color: #64748b; /* Slate 500 on white - 5:1 ratio */
}

.primary-button {
  background: #6366f1;
  color: white; /* White on indigo - 7:1 ratio */
}
```

---

## Implementation Checklist

### Phase 1: Foundation Setup

- [ ] Install required dependencies (`@coreui/vue`, `axios`, `vue-router`, etc.)
- [ ] Set up CSS custom properties for design tokens
- [ ] Create base layout structure
- [ ] Configure API instance with interceptors
- [ ] Set up environment variables (`.env`)

### Phase 2: Core Components

- [ ] MaterialCard component
- [ ] MaterialInput component
- [ ] MaterialButton component
- [ ] Skeleton loader component
- [ ] Pagination component
- [ ] Badge components
- [ ] Icon button component
- [ ] Toast notification system

### Phase 3: Feature Implementation

- [ ] KPI summary cards
- [ ] Data tables with search
- [ ] CRUD forms
- [ ] Modal dialogs (edit/delete)
- [ ] Loading states
- [ ] Empty states
- [ ] Error handling

### Phase 4: Polish & Optimization

- [ ] Add animations and transitions
- [ ] Responsive design adjustments
- [ ] Accessibility audit (keyboard nav, ARIA labels)
- [ ] Performance optimization (lazy loading, code splitting)
- [ ] Cross-browser testing

### Phase 5: Documentation

- [ ] Component usage documentation
- [ ] API integration guide
- [ ] State management patterns
- [ ] Deployment guide

---

## Best Practices Summary

### Component Design

1. **Single Responsibility**: Each component should do one thing well
2. **Reusability**: Build generic components, customize via props
3. **Composition**: Use slots for flexible content injection
4. **Props Validation**: Always validate props with types and defaults
5. **Event Naming**: Use kebab-case for event names (`@update-value`)

### State Management

1. **Local First**: Use local state for UI-only concerns
2. **Composables**: Use composables for shared stateful logic
3. **Store for Global**: Use Vuex/Pinia only for truly global state
4. **Computed Properties**: Derive state rather than duplicate it
5. **Reactive References**: Use `ref()` for primitives, `reactive()` for objects

### API Integration

1. **Service Layer**: Centralize API calls in service modules
2. **Error Handling**: Handle errors at multiple levels (global + local)
3. **Loading States**: Always show feedback during async operations
4. **Caching**: Cache auth tokens and frequently accessed data
5. **Retry Logic**: Implement retry for transient failures

### Performance

1. **Lazy Loading**: Load routes and heavy components on demand
2. **Virtual Scrolling**: For long lists (100+ items)
3. **Debouncing**: Debounce search inputs (300-500ms)
4. **Pagination**: Limit data fetched per request
5. **Image Optimization**: Use appropriate formats and sizes

### Security

1. **XSS Prevention**: Sanitize user input, use `v-text` over `v-html`
2. **CSRF Protection**: Use Laravel Sanctum with `withCredentials: true`
3. **Token Security**: Store tokens in memory or httpOnly cookies
4. **Input Validation**: Validate on both client and server
5. **Rate Limiting**: Implement on critical endpoints

---

## Conclusion

This design system provides a complete foundation for building consistent, accessible, and performant church management interfaces. All components follow Material Design 3 principles adapted for this specific domain, ensuring a modern and cohesive user experience.

For questions or contributions, refer to the project documentation or contact the development team.

---

**Version**: 1.0.0
**Last Updated**: 2026-02-09
**Maintained By**: Prophetic Life Embassy Development Team
