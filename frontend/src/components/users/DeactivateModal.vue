<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible" alignment="center" backdrop="static" class="modal-bottom-sheet">
      <div class="modal-header-custom">
        <div class="modal-header-icon deactivate">
          <i class="bi bi-person-slash"></i>
        </div>
        <div>
          <h5 class="modal-header-title">Deactivate User</h5>
          <p class="modal-header-sub">This action can be reversed later</p>
        </div>
        <button class="modal-close-btn" @click="$emit('close')"><i class="bi bi-x-lg"></i></button>
      </div>

      <CModalBody>
        <div class="deactivate-warning">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div>
            <strong>{{ user?.name }}</strong> will no longer be able to log in or access the system.
          </div>
        </div>
        <label class="form-field-label mt-3">Reason <span class="req">*</span></label>
        <CFormTextarea v-model="reason" rows="3" placeholder="Why is this user being deactivated?" />
      </CModalBody>

      <div class="modal-footer-custom">
        <button class="modal-btn-cancel" @click="$emit('close')">Cancel</button>
        <button class="modal-btn-danger" :disabled="!reason.trim()" @click="handleDeactivate">
          <i class="bi bi-person-slash me-1"></i> Deactivate User
        </button>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CModal, CModalBody, CFormTextarea } from '@coreui/vue';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:visible', 'close', 'deactivate']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => { localVisible.value = v; });
watch(localVisible, (v) => {
  emit('update:visible', v);
  if (!v) emit('close');
});

const reason = ref('');

// Reset reason when modal opens
watch(() => props.visible, (visible) => {
  if (visible) {
    reason.value = '';
  }
});

function handleDeactivate() {
  if (!reason.value.trim()) return;
  emit('deactivate', reason.value);
}
</script>

<style scoped>
/* Component-specific styles are handled globally */
</style>