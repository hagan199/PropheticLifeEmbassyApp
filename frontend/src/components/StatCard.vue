<template>
  <CCard class="stat-card hoverable">
    <CCardBody>
      <div class="d-flex justify-content-between align-items-center">
        <div class="stat-title">{{ title }}</div>
        <span class="stat-icon"><i :class="icon"></i></span>
      </div>
      <div class="stat-value">{{ value }}</div>
      <CProgress class="mt-2" :value="progress" :color="color" />
      <div class="stat-sub">{{ sub }}</div>
    </CCardBody>
  </CCard>
</template>

<script setup>
import { CCard, CCardBody, CProgress } from '@coreui/vue'
defineProps({
  title: String,
  value: [String, Number],
  sub: String,
  icon: String,
  progress: Number,
  color: String
})
</script>

<style scoped>
.stat-title { font-size: .92rem; color: #64748b; }
.stat-value { font-size: 1.7rem; font-weight: 800; letter-spacing: .2px; color: #0f172a; }
.stat-icon { opacity: .7; color: var(--app-accent); }
</style>
