<template>
  <div class="md-shared-card">
    <div v-if="title || $slots.header" class="md-card-header">
      <div class="md-card-header-content">
        <h5 v-if="title" class="md-card-title">{{ title }}</h5>
        <slot name="header"></slot>
      </div>
      <div class="md-card-header-actions">
        <slot name="header-actions"></slot>
      </div>
    </div>
    <div class="md-card-body">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="md-card-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
});
</script>

<style scoped>
.md-shared-card {
  background: var(--md-surface);
  border-radius: var(--md-shape-lg);
  box-shadow: var(--md-elevation-2);
  overflow: hidden;
  margin-bottom: var(--md-space-4);
}

.md-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--md-space-4);
  padding: var(--md-space-5) var(--md-space-6);
  border-bottom: 1px solid var(--md-outline-variant);
}

.md-card-header-content {
  flex: 1;
  min-width: 0;
}

.md-card-title {
  font: var(--md-title-medium);
  color: var(--md-on-surface);
  margin: 0;
}

.md-card-header-actions {
  display: flex;
  align-items: center;
  gap: var(--md-space-2);
  flex-shrink: 0;
}

.md-card-body {
  padding: var(--md-space-5) var(--md-space-6);
}

.md-card-footer {
  padding: var(--md-space-4) var(--md-space-6);
  border-top: 1px solid var(--md-outline-variant);
  background: var(--md-surface-container-low);
}
</style>
