<template>
  <div
    class="md-input-container"
    :class="{ 'has-value': !!modelValue, 'has-error': !!error, 'is-focused': isFocused }"
  >
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
        @blur="isFocused = false"
      ></textarea>

      <!-- Select -->
      <select
        v-else-if="type === 'select'"
        :id="id"
        v-bind="$attrs"
        :value="modelValue"
        class="md-input md-select"
        @change="$emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false"
      >
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
        @blur="isFocused = false"
      />

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
import { ref } from 'vue';

defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  id: { type: String, default: () => `md-input-${Math.random().toString(36).substr(2, 9)}` },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  icon: { type: String, default: '' },
  placeholder: { type: String, default: ' ' },
  required: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

const isFocused = ref(false);
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
  background: var(--md-surface-container-highest);
  border: none;
  border-radius: 12px 12px 0 0;
  font-size: 1rem;
  color: var(--md-on-surface);
  transition: background-color 0.2s;
  outline: none;
}

.md-input:hover {
  background: var(--md-surface-container-high);
}

.md-input:focus {
  background: var(--md-surface-container-low);
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
  color: var(--md-on-surface-variant);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  pointer-events: none;
  display: flex;
  align-items: center;
  gap: 4px;
}

.md-input:focus ~ .md-label-floating,
.md-input:not(:placeholder-shown) ~ .md-label-floating,
.md-input-container.has-value .md-label-floating,
.md-select:not([value='']) ~ .md-label-floating {
  top: 8px;
  font-size: 0.75rem;
  color: var(--md-primary);
  font-weight: 600;
}

.md-input-indicator {
  height: 2px;
  width: 100%;
  background: var(--md-outline-variant);
  transform: scaleX(1);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s;
}

.md-input:focus ~ .md-input-indicator {
  background: var(--md-primary);
  transform: scaleX(1);
  height: 3px;
}

.md-input-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--md-on-surface-variant);
  opacity: 0.5;
}

.md-input-error-text {
  color: var(--md-error);
  font-size: 0.75rem;
  margin-top: 4px;
  padding-left: 4px;
  font-weight: 500;
}

.md-input-hint-text {
  color: var(--md-on-surface-variant);
  font-size: 0.75rem;
  margin-top: 4px;
  padding-left: 4px;
  opacity: 0.7;
}

.required-star {
  color: var(--md-error);
}

.has-error .md-input-indicator {
  background: var(--md-error) !important;
}

.has-error .md-label-floating {
  color: var(--md-error) !important;
}
</style>
