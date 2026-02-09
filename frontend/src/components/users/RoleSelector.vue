<template>
  <div class="role-grid">
    <button v-for="r in roleOptions" :key="r.value" type="button" class="role-select-btn"
      :class="{ selected: selected.includes(r.value) }" @click="toggle(r.value)
        ">
      <i class="role-select-icon"></i>
      <span class="role-select-name">{{ r.label }}</span>
      <i v-if="selected.includes(r.value)" class="bi bi-check-circle-fill role-check"></i>
    </button>
  </div>
</template>

<script setup>
import { toRefs } from 'vue';
const props = defineProps({
  roleOptions: { type: Array, default: () => [] },
  selected: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:selected']);

function toggle(val) {
  const copy = [...props.selected];
  const idx = copy.indexOf(val);
  if (idx > -1) copy.splice(idx, 1);
  else copy.push(val);
  emit('update:selected', copy);
}
</script>

<style scoped>
.role-grid {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap
}

.role-select-btn {
  padding: .5rem 0.75rem;
  border-radius: 6px;
  background: #fff
}

.role-select-btn.selected {
  box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05)
}
</style>
