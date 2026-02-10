<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible">
      <CModalHeader>
        <CModalTitle>Log Follow-up</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <CForm>
          <div class="mb-3">
            <CFormLabel>Contact Method <span class="text-danger">*</span></CFormLabel>
            <div class="d-flex gap-2 flex-wrap">
              <CFormCheck id="m-whatsapp" v-model="form.method" type="radio" name="method" value="WhatsApp"
                label="WhatsApp" inline />
              <CFormCheck id="m-sms" v-model="form.method" type="radio" name="method" value="SMS" label="SMS"
                inline />
              <CFormCheck id="m-call" v-model="form.method" type="radio" name="method" value="Call"
                label="Call" inline />
              <CFormCheck id="m-person" v-model="form.method" type="radio" name="method" value="In-Person"
                label="In-Person" inline />
            </div>
          </div>
          <div class="mb-3">
            <CFormLabel>Notes <span class="text-danger">*</span></CFormLabel>
            <CFormTextarea v-model="form.notes" rows="3" placeholder="What was discussed?" />
          </div>
          <div class="mb-3">
            <CFormLabel>Update Status</CFormLabel>
            <CFormSelect v-model="form.statusAfter">
              <option value="">No change</option>
              <option value="contacted">Contacted</option>
              <option value="engaged">Engaged</option>
              <option value="converted">Converted</option>
            </CFormSelect>
          </div>
          <div class="mb-3">
            <CFormLabel>Next Follow-up Date</CFormLabel>
            <CFormInput v-model="form.nextDate" type="date" />
          </div>
        </CForm>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="localVisible = false">Cancel</CButton>
        <CButton color="primary" @click="$emit('save')">Save Follow-up</CButton>
      </CModalFooter>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import {
  CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CButton, CForm, CFormLabel, CFormTextarea, CFormSelect, CFormInput, CFormCheck,
} from '@coreui/vue';

const props = defineProps({
  visible: { type: Boolean, default: false },
  form: { type: Object, required: true },
});

const emit = defineEmits(['update:visible', 'save']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => (localVisible.value = v));
watch(localVisible, (v) => emit('update:visible', v));
</script>
