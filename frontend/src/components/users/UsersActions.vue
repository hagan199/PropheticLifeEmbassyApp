<template>
  <div class="users-actions">
    <div class="d-flex gap-2">
      <CButton color="light" @click="$emit('export')">
        <i class="bi bi-file-earmark-excel me-1"></i> Export
      </CButton>
      <CButton color="primary" @click="$emit('add-user')">
        <i class="bi bi-plus-lg me-1"></i> Add User
      </CButton>
    </div>
  </div>
</template>

<script setup>
import { CButton } from '@coreui/vue';

const emit = defineEmits(['export', 'add-user']);
</script>

<style scoped>
.users-actions {
  display: flex;
  gap: 0.5rem;
}
</style>