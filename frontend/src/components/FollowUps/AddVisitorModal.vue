<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-backdrop-custom" @click.self="$emit('close')">
      <div class="add-visitor-dialog">
        <div class="add-visitor-header">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box">
              <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
              <h5 class="mb-0 fw-bold text-white">Add New Visitor</h5>
              <small class="text-white-50">Register a new visitor</small>
            </div>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="add-visitor-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" :value="form.name"
                @input="$emit('update-field', { field: 'name', value: $event.target.value })" placeholder="Full name" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">+233</span>
                <input type="text" class="form-control" :value="form.phone"
                  @input="$emit('update-field', { field: 'phone', value: $event.target.value })"
                  placeholder="24 XXX XXXX" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" class="form-control" :value="form.email"
                @input="$emit('update-field', { field: 'email', value: $event.target.value })"
                placeholder="email@example.com" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Source <span class="text-danger">*</span></label>
              <select class="form-select" :value="form.source"
                @change="$emit('update-field', { field: 'source', value: $event.target.value })">
                <option value="">Select...</option>
                <option value="Friend">Friend</option>
                <option value="Social Media">Social Media</option>
                <option value="Walk-in">Walk-in</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">First Visit Date</label>
              <input type="date" class="form-control" :value="form.firstVisitDate"
                @input="$emit('update-field', { field: 'firstVisitDate', value: $event.target.value })" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ministry Interests</label>
              <select class="form-select" :value="form.interests"
                @change="$emit('update-field', { field: 'interests', value: Array.from($event.target.selectedOptions, o => o.value) })"
                multiple>
                <option value="Youth">Youth</option>
                <option value="Choir">Choir</option>
                <option value="Media">Media</option>
                <option value="Prayer">Prayer</option>
                <option value="Ushering">Ushering</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Notes</label>
              <textarea class="form-control" :value="form.notes"
                @input="$emit('update-field', { field: 'notes', value: $event.target.value })" rows="3"
                placeholder="Any additional notes..."></textarea>
            </div>
          </div>
        </div>
        <div class="add-visitor-footer">
          <button class="btn btn-light" @click="$emit('close')">Cancel</button>
          <button class="btn btn-primary" @click="$emit('save')">
            <i class="bi bi-plus-lg me-1"></i> Save Visitor
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  visible: { type: Boolean, default: false },
  form: { type: Object, required: true },
});

defineEmits(['close', 'save', 'update-field']);
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1055;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.2s ease;
}

.add-visitor-dialog {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 680px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  animation: slideUp 0.3s ease;
}

.add-visitor-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px 20px 0 0;
  padding: 1.5rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.add-visitor-header .header-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: white;
}

.add-visitor-header .close-btn {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: background 0.2s;
}

.add-visitor-header .close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.add-visitor-body {
  padding: 1.75rem 2rem;
}

.add-visitor-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 2rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 0 0 20px 20px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
