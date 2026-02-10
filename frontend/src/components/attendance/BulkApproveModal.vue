<template>
  <CModal v-model:visible="localVisible">
    <CModalHeader>
      <CModalTitle>Confirm Bulk Approval</CModalTitle>
    </CModalHeader>
    <CModalBody>
      <p>Approve <strong>{{ selectedCount }}</strong> attendance records?</p>
      <ul class="list-unstyled">
        <li v-for="(s, idx) in summaries" :key="idx" class="mb-1">
          <i class="bi bi-check-circle text-success me-2"></i>
          {{ s }}
        </li>
      </ul>
    </CModalBody>
    <CModalFooter>
      <CButton color="secondary" @click="localVisible = false">Cancel</CButton>
      <CButton color="success" @click="confirm"> Approve All </CButton>
    </CModalFooter>
  </CModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter, CButton } from '@coreui/vue';
const props = defineProps({
  visible: { type: Boolean, default: false },
  selectedCount: { type: Number, default: 0 },
  summaries: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:visible', 'confirmed']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => { localVisible.value = v; });
watch(localVisible, (v) => { emit('update:visible', v); });

function confirm() {
  emit('confirmed');
  localVisible.value = false;
}
</script>

<style scoped></style>
