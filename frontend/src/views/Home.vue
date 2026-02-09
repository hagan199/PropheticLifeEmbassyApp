<template>
  <div class="home-wrap">
    <div class="home-hero">
      <h2 class="title">Home</h2>
      <Breadcrumbs />
      <div class="text-muted">Welcome back</div>
    </div>

    <CRow class="g-4 mt-1">
      <CCol sm="6" xl="3">
        <CCard class="stat-card hoverable">
          <CCardBody>
            <div class="stat-title">Quick Tip</div>
            <div>Use the sidebar to navigate sections.</div>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  </div>
</template>

<script setup>
import Breadcrumbs from '../components/Breadcrumbs.vue';
import { CRow, CCol, CCard, CCardBody } from '@coreui/vue';
</script>

<style scoped>
.home-wrap {
  padding: 20px;
}
.home-hero {
  background: linear-gradient(90deg, rgba(13, 110, 253, 0.08), transparent);
  border-radius: 12px;
  padding: 12px;
}
.title {
  margin: 0;
  font-weight: 700;
}
.stat-card .stat-title {
  font-size: 0.85rem;
  color: #6c757d;
}
.hoverable {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.hoverable:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}
</style>
