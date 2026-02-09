<template>
  <header class="md-page-header">
    <div class="md-page-header-content">
      <h1 class="md-page-title">{{ title }}</h1>
      <p v-if="subtitle" class="md-page-subtitle">{{ subtitle }}</p>
    </div>
    <div class="md-page-actions">
      <slot name="actions"></slot>
    </div>
  </header>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
});
</script>

<style scoped>
.md-page-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--md-space-4);
  margin-bottom: var(--md-space-6);
  padding: var(--md-space-6);
  background: var(--md-surface);
  border-radius: var(--md-shape-xl);
  box-shadow: var(--md-elevation-2);
}

.md-page-header-content {
  flex: 1;
  min-width: 0;
}

.md-page-title {
  font: var(--md-headline-medium);
  font-weight: 800;
  background: var(--md-gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 var(--md-space-1) 0;
  letter-spacing: -0.02em;
}

.md-page-subtitle {
  font: var(--md-body-large);
  color: var(--md-on-surface-muted);
  margin: 0;
}

.md-page-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--md-space-3);
}

/* Responsive */
@media (max-width: 991.98px) {
  .md-page-header {
    padding: var(--md-space-4);
    border-radius: var(--md-shape-lg);
  }

  .md-page-title {
    font: var(--md-title-large);
    font-weight: 800;
  }
}

@media (max-width: 768px) {
  .md-page-header {
    flex-direction: column;
    align-items: flex-start;
    padding: var(--md-space-4);
    border-radius: var(--md-shape-md);
  }

  .md-page-title {
    font: var(--md-title-medium);
    font-weight: 700;
  }

  .md-page-actions {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
