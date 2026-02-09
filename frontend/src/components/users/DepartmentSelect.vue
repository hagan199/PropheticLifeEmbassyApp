<template>
  <CFormSelect v-model:value="localValue" :disabled="disabled">
    <option value="">Select department...</option>
    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
  </CFormSelect>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CFormSelect } from '@coreui/vue';
const props = defineProps({
  departments: { type: Array, default: () => [] },
  modelValue: { type: [String, Number, null], default: null },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);
watch(() => props.modelValue, v => (localValue.value = v));
watch(localValue, v => emit('update:modelValue', v));
</script>

<style scoped>
/* rely on parent styles */
</style>
