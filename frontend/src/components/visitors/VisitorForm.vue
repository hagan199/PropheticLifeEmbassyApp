<template>
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

    <CForm class="visitor-form" @submit.prevent="handleSubmit">
      <div class="mb-3">
        <MaterialInput v-model="form.name" label="Full Name" required />
      </div>

      <CRow class="g-3 mb-3">
        <CCol sm="6">
          <MaterialInput v-model="form.phone" label="Phone Number" required :error="phoneError" />
        </CCol>
        <CCol sm="6">
          <MaterialInput v-model="form.occupation" label="Occupation/Job" />
        </CCol>
      </CRow>

      <CRow class="g-3 mb-3">
        <CCol sm="6">
          <MaterialInput v-model="form.category" label="Category" type="select" required>
            <option v-for="t in visitorTypes" :key="t.id" :value="t.name">{{ t.name }}</option>
          </MaterialInput>
        </CCol>
        <CCol sm="6">
          <MaterialInput v-model="form.service_type" label="Service Type" type="select" required>
            <option v-for="s in serviceTypes" :key="s.id" :value="s.name">{{ s.name }}</option>
          </MaterialInput>
        </CCol>
      </CRow>

      <div class="mb-4">
        <MaterialInput v-model="form.date" type="date" label="First Visit Date" required />
      </div>

      <MaterialButton type="submit" class="w-100" :loading="isSubmitting" icon="bi bi-person-plus-fill">
        Register Visitor
      </MaterialButton>
    </CForm>
  </MaterialCard>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { CForm, CRow, CCol } from '@coreui/vue';
import { MaterialCard, MaterialInput, MaterialButton } from '../../components/material';

const props = defineProps({
  form: { type: Object, required: true },
  phoneError: { type: String, default: '' },
  isSubmitting: { type: Boolean, default: false },
  serviceTypes: { type: Array, default: () => [] },
  visitorTypes: { type: Array, default: () => [] },
});

const emit = defineEmits(['submit']);

function handleSubmit() {
  emit('submit');
}
</script>

<style scoped>
/* minimal - parent stylesheet covers styles */
</style>
