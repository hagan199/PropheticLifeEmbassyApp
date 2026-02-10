<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="visitor-modal">
        <header class="modal-header">
          <div class="header-content">
            <div class="icon-box" :class="isEditing ? 'edit' : 'add'">
              <i :class="isEditing ? 'bi bi-pencil-square' : 'bi bi-person-plus-fill'"></i>
            </div>
            <div class="title-area">
              <h3>{{ isEditing ? 'Edit Visitor' : 'Register New Visitor' }}</h3>
              <p>{{ isEditing ? 'Update visitor details' : 'Fill in the details to register a new visitor' }}</p>
            </div>
          </div>
          <button class="close-pill" @click="$emit('close')" aria-label="Close">
            <i class="bi bi-x"></i>
          </button>
        </header>

        <div class="modal-body">
          <form id="visitorForm" @submit.prevent="$emit('save')">

            <div class="form-section">
              <div class="section-header">
                <i class="bi bi-person-badge"></i>
                <span>Personal Information</span>
              </div>

              <div class="row g-3">
                <div class="col-md-7">
                  <label class="custom-label">Full Name <span>*</span></label>
                  <div class="input-wrapper">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" class="custom-input" :value="form.name"
                      @input="$emit('update-field', { field: 'name', value: $event.target.value })"
                      placeholder="Enter full name" required :disabled="saving" />
                  </div>
                </div>

                <div class="col-md-5">
                  <label class="custom-label">Phone Number <span>*</span></label>
                  <div class="input-wrapper">
                    <i class="bi bi-telephone input-icon"></i>
                    <input type="tel" class="custom-input" :class="{ 'has-error': errors.phone }" :value="form.phone"
                      @input="onPhoneInput" placeholder="0241234567" required :disabled="saving" inputmode="numeric"
                      pattern="[0-9]{9,10}" maxlength="10" />
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
                      <small v-else class="hint-text">Format: 0241234567 (9-10 digits)</small>
                    </div>
                    <small class="text-muted">{{ (form.phone || '').toString().length }} / 10</small>
                  </div>
                </div>

                <div class="col-12">
                  <label class="custom-label">Occupation</label>
                  <div class="input-wrapper">
                    <i class="bi bi-briefcase input-icon"></i>
                    <input type="text" class="custom-input" :value="form.occupation"
                      @input="$emit('update-field', { field: 'occupation', value: $event.target.value })"
                      placeholder="Current job title" :disabled="saving" />
                  </div>
                </div>
              </div>
            </div>

            <hr class="divider" />

            <div class="form-section">
              <div class="section-header">
                <i class="bi bi-calendar-event"></i>
                <span>Visit Details</span>
              </div>

              <div class="row g-3">
                <div class="col-md-4">
                  <label class="custom-label">Category <span>*</span></label>
                  <div class="input-wrapper">
                    <i class="bi bi-tag input-icon"></i>
                    <select class="custom-input select-input" :value="form.category"
                      @change="$emit('update-field', { field: 'category', value: $event.target.value })" required>
                      <option value="" disabled>Select Category</option>
                      <option v-for="t in visitorTypes" :key="t.id" :value="t.name">{{ t.name }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="custom-label">Service Type <span>*</span></label>
                  <div class="input-wrapper">
                    <i class="bi bi-gear input-icon"></i>
                    <select class="custom-input select-input" :value="form.service_type"
                      @change="$emit('update-field', { field: 'service_type', value: $event.target.value })" required>
                      <option value="" disabled>Select Service</option>
                      <option v-for="s in serviceTypes" :key="s.id" :value="s.name">{{ s.name }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="custom-label">Visit Date <span>*</span></label>
                  <div class="input-wrapper">
                    <i class="bi bi-calendar-check input-icon"></i>
                    <input type="date" class="custom-input" :value="form.date"
                      @input="$emit('update-field', { field: 'date', value: $event.target.value })" required />
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <footer class="modal-footer">
          <button type="button" class="btn-secondary-custom" @click="$emit('close')">Cancel</button>
          <button type="submit" form="visitorForm" class="btn-primary-custom" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else :class="isEditing ? 'bi bi-check-lg' : 'bi bi-plus-lg'"></i>
            {{ isEditing ? 'Update Details' : 'Register Visitor' }}
          </button>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  visible: Boolean,
  isEditing: Boolean,
  form: Object,
  errors: { type: Object, default: () => ({}) },
  visitorTypes: Array,
  serviceTypes: Array,
  saving: Boolean
});

const emit = defineEmits(['close', 'save', 'update-field']);

function onPhoneInput(e) {
  // Strip non-digits and limit to 10 characters (backend expects 9-10 digits)
  const cleaned = (e.target.value || '').toString().replace(/\D/g, '').slice(0, 10);
  emit('update-field', { field: 'phone', value: cleaned });
}
</script>

<style scoped>
/* 1. Backdrop */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(6px);
  z-index: 1055;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  animation: fadeIn 0.2s ease-out;
}

/* 2. Container */
.visitor-modal {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 680px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* 3. Header */
.modal-header {
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.title-area h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  letter-spacing: -0.02em;
}

.title-area p {
  margin: 0;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.8);
}

.close-pill {
  border: none;
  background: rgba(0, 0, 0, 0.1);
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.close-pill:hover {
  background: rgba(0, 0, 0, 0.2);
  transform: rotate(90deg);
}

/* 4. Body & Form */
.modal-body {
  padding: 2rem;
  max-height: 75vh;
  overflow-y: auto;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.75rem;
  font-weight: 800;
  color: #6366f1;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1.25rem;
}

.divider {
  border: 0;
  border-top: 1px dashed #e2e8f0;
  margin: 1.5rem 0;
}

.custom-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 6px;
}

.custom-label span {
  color: #ef4444;
}

/* Icon Positioning Fix */
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  font-size: 1.1rem;
  pointer-events: none;
  z-index: 5;
}

.custom-input {
  width: 100%;
  padding: 0.65rem 1rem 0.65rem 2.8rem;
  /* Wide left padding for icon */
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background-color: #f8fafc;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.custom-input:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.select-input {
  cursor: pointer;
}

/* 5. Footer & Buttons */
.modal-footer {
  padding: 1.5rem 2rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn-primary-custom {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.75rem;
  border-radius: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  transition: all 0.2s;
}

.btn-primary-custom:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary-custom {
  background: white;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-secondary-custom:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.error-text {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 4px;
  display: block;
}

.hint-text {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 4px;
  display: block;
}

/* Animations */
@keyframes modalPop {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}
</style>