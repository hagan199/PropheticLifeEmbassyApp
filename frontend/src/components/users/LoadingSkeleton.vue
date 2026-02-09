<template>
  <div class="skeleton-container">
    <div v-for="i in rows" :key="i" class="skeleton-row">
      <div class="skeleton skeleton-avatar"></div>
      <div class="skeleton skeleton-text" style="width: 30%"></div>
      <div class="skeleton skeleton-text" style="width: 20%"></div>
      <div class="skeleton skeleton-badge" style="width: 15%"></div>
      <div class="skeleton skeleton-text" style="width: 15%"></div>
      <div class="skeleton skeleton-text" style="width: 10%"></div>
      <div class="skeleton skeleton-text" style="width: 20%"></div>
      <div class="skeleton skeleton-actions" style="width: 10%"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  rows: {
    type: Number,
    default: 5
  }
});
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
  width: 44px;
  height: 44px;
  border-radius: 12px;
  flex-shrink: 0;
}

.skeleton-text {
  height: 16px;
  flex: 1;
}

.skeleton-badge {
  height: 24px;
  border-radius: 12px;
}

.skeleton-actions {
  height: 32px;
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