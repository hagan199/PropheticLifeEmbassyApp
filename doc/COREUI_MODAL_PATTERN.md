# CoreUI Modal Pattern Guide

> **Required Pattern for ALL CoreUI Modals in Vue 3**

## The Problem

CoreUI modals directly manipulate the DOM and expect to be at the body level. When nested inside component hierarchies, they throw errors:

```
Uncaught TypeError: Cannot read properties of null (reading 'classList')
```

## The Solution: Always Use Teleport

**EVERY** `CModal` component MUST be wrapped with `<Teleport to="body">`.

---

## ✅ Correct Pattern

```vue
<template>
  <div class="page-content">
    <!-- Your page content -->
    <button @click="showModal = true">Open Modal</button>
  </div>

  <!-- Modal (OUTSIDE main content, WITH Teleport) -->
  <Teleport to="body">
    <CModal
      :visible="showModal"
      @close="showModal = false"
      alignment="center"
      backdrop="static">
      <div class="modal-header">
        <h5>Modal Title</h5>
        <button type="button" class="btn-close" @click="showModal = false"></button>
      </div>
      <div class="modal-body">
        <p>Modal content goes here</p>
      </div>
      <div class="modal-footer">
        <button @click="showModal = false">Close</button>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { CModal } from '@coreui/vue'

const showModal = ref(false)
</script>
```

---

## ❌ Incorrect Pattern (Will Break)

```vue
<template>
  <div class="page-content">
    <button @click="showModal = true">Open Modal</button>

    <!-- ❌ NO TELEPORT - THIS WILL FAIL! -->
    <CModal :visible="showModal" @close="showModal = false">
      <p>This won't work!</p>
    </CModal>
  </div>
</template>
```

---

## Complete Examples

### 1. Simple Confirmation Modal

```vue
<template>
  <div class="page-wrap">
    <button @click="confirmDelete(item)">Delete</button>
  </div>

  <Teleport to="body">
    <CModal
      :visible="deleteModalVisible"
      @close="closeDeleteModal"
      alignment="center"
      backdrop="static">
      <div class="modal-header">
        <h5>Confirm Deletion</h5>
        <button type="button" class="btn-close" @click="closeDeleteModal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong>{{ itemToDelete?.name }}</strong>?</p>
      </div>
      <div class="modal-footer">
        <button @click="closeDeleteModal">Cancel</button>
        <button @click="performDelete" class="btn-danger">Delete</button>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { CModal } from '@coreui/vue'

const deleteModalVisible = ref(false)
const itemToDelete = ref(null)

function confirmDelete(item) {
  itemToDelete.value = item
  deleteModalVisible.value = true
}

function closeDeleteModal() {
  deleteModalVisible.value = false
  itemToDelete.value = null
}

async function performDelete() {
  // Delete logic here
  closeDeleteModal()
}
</script>
```

### 2. Edit Form Modal

```vue
<template>
  <div class="page-wrap">
    <button @click="openEditModal(item)">Edit</button>
  </div>

  <Teleport to="body">
    <CModal
      :visible="editModalVisible"
      @close="closeEditModal"
      alignment="center"
      backdrop="static"
      size="lg">
      <div class="modal-header">
        <h5>Edit Item</h5>
        <button type="button" class="btn-close" @click="closeEditModal" :disabled="isSaving"></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="saveEdit">
          <div class="mb-3">
            <label>Name</label>
            <input v-model="editForm.name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input v-model="editForm.email" type="email" class="form-control" required />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button @click="closeEditModal" :disabled="isSaving">Cancel</button>
        <button @click="saveEdit" :disabled="isSaving">
          <span v-if="isSaving">Saving...</span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { CModal } from '@coreui/vue'

const editModalVisible = ref(false)
const editForm = ref({ name: '', email: '' })
const isSaving = ref(false)

function openEditModal(item) {
  editForm.value = { ...item }
  editModalVisible.value = true
}

function closeEditModal() {
  if (isSaving.value) return
  editModalVisible.value = false
  editForm.value = { name: '', email: '' }
}

async function saveEdit() {
  isSaving.value = true
  try {
    // Save logic here
    closeEditModal()
  } finally {
    isSaving.value = false
  }
}
</script>
```

### 3. Multiple Modals on Same Page

```vue
<template>
  <div class="page-wrap">
    <button @click="showEditModal = true">Edit</button>
    <button @click="showDeleteModal = true">Delete</button>
    <button @click="showInfoModal = true">Info</button>
  </div>

  <!-- EACH modal needs its own Teleport -->
  <Teleport to="body">
    <CModal :visible="showEditModal" @close="showEditModal = false">
      <p>Edit Modal</p>
    </CModal>
  </Teleport>

  <Teleport to="body">
    <CModal :visible="showDeleteModal" @close="showDeleteModal = false">
      <p>Delete Modal</p>
    </CModal>
  </Teleport>

  <Teleport to="body">
    <CModal :visible="showInfoModal" @close="showInfoModal = false">
      <p>Info Modal</p>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { CModal } from '@coreui/vue'

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showInfoModal = ref(false)
</script>
```

---

## Modal Props Reference

### Essential Props

```vue
<CModal
  :visible="boolean"           <!-- Required: Controls visibility -->
  @close="handleClose"         <!-- Required: Called when user closes modal -->
  alignment="center"           <!-- Optional: 'top' | 'center' -->
  backdrop="static"            <!-- Optional: 'static' prevents close on backdrop click -->
  size="lg"                    <!-- Optional: 'sm' | 'lg' | 'xl' -->
  scrollable                   <!-- Optional: Makes modal body scrollable -->
>
</CModal>
```

### Common Configurations

**Confirmation Modal (can't dismiss by clicking outside)**
```vue
<CModal
  :visible="showModal"
  @close="closeModal"
  alignment="center"
  backdrop="static">
</CModal>
```

**Large Form Modal**
```vue
<CModal
  :visible="showModal"
  @close="closeModal"
  alignment="center"
  backdrop="static"
  size="lg"
  scrollable>
</CModal>
```

**Quick Info Modal (can dismiss easily)**
```vue
<CModal
  :visible="showModal"
  @close="closeModal"
  alignment="center">
</CModal>
```

---

## Best Practices

### 1. **State Management**

```vue
<script setup>
// Use separate ref for each modal
const editModalVisible = ref(false)
const deleteModalVisible = ref(false)

// Use separate data for each modal
const editItem = ref(null)
const deleteItem = ref(null)
</script>
```

### 2. **Prevent Closing During Operations**

```vue
function closeModal() {
  // Don't allow closing while saving
  if (isSaving.value) return

  modalVisible.value = false
  resetForm()
}
```

### 3. **Disable Close Button During Operations**

```vue
<button
  type="button"
  class="btn-close"
  @click="closeModal"
  :disabled="isSaving">
</button>
```

### 4. **Clean Up on Close**

```vue
function closeModal() {
  modalVisible.value = false

  // Reset all modal state
  editForm.value = { name: '', email: '' }
  errors.value = []
  isSaving.value = false
}
```

### 5. **Handle Backdrop Click**

```vue
<CModal
  :visible="showModal"
  @close="handleModalClose"
  backdrop="static">  <!-- User must click cancel/close button -->
</CModal>

<!-- OR allow backdrop close -->
<CModal
  :visible="showModal"
  @close="handleModalClose">  <!-- Click outside to close -->
</CModal>
```

---

## Styling Modals

### Custom Modal Classes

```vue
<Teleport to="body">
  <CModal
    :visible="showModal"
    @close="closeModal"
    class="custom-modal">
    <!-- Content -->
  </CModal>
</Teleport>

<style scoped>
:deep(.custom-modal .modal-content) {
  max-width: 600px;
  border-radius: 16px;
}

:deep(.custom-modal .modal-header) {
  background: var(--primary);
  color: white;
}
</style>
```

### Material Design Modal (From Our App)

```vue
<Teleport to="body">
  <CModal
    :visible="editModalVisible"
    @close="closeEditModal"
    alignment="center"
    backdrop="static"
    class="modal-bottom-sheet edit-modal">
    <MaterialCard class="mb-0 border-0">
      <template #header>
        <div class="d-flex align-items-center gap-3">
          <div class="header-icon-box bg-primary-subtle text-primary">
            <i class="bi bi-pencil-square"></i>
          </div>
          <div>
            <h3 class="md-title-large mb-1">Edit Visitor</h3>
            <p class="text-muted small mb-0">Update information</p>
          </div>
        </div>
      </template>
      <!-- Form content -->
    </MaterialCard>
  </CModal>
</Teleport>
```

---

## Troubleshooting

### Problem: Modal doesn't appear

**Check:**
1. ✅ Is `<Teleport to="body">` wrapping the modal?
2. ✅ Is `:visible` prop reactive (using `ref` or `reactive`)?
3. ✅ Is `:visible` actually set to `true`?
4. ✅ Check browser console for errors

### Problem: Modal appears but backdrop doesn't work

**Check:**
1. ✅ Is `@close` handler defined?
2. ✅ Is `backdrop` prop set correctly?
3. ✅ Check z-index in CSS

### Problem: Modal content is invisible

**Check:**
1. ✅ Check CSS - modal content might be hidden
2. ✅ Verify MaterialCard or custom components render correctly
3. ✅ Use browser DevTools to inspect modal DOM

### Problem: Multiple modals interfere with each other

**Solution:**
- Each modal needs separate `ref` for visibility
- Each modal needs separate data refs
- Each modal needs its own `<Teleport>` wrapper

---

## Migration Checklist

When adding modals to existing pages:

- [ ] Import `CModal` from `@coreui/vue`
- [ ] Wrap modal with `<Teleport to="body">`
- [ ] Create reactive ref for visibility: `const modalVisible = ref(false)`
- [ ] Add `:visible="modalVisible"` prop
- [ ] Add `@close="closeModal"` handler
- [ ] Implement `closeModal()` function
- [ ] Add `backdrop="static"` if needed
- [ ] Test opening and closing modal
- [ ] Test with loading/saving states
- [ ] Verify modal works on mobile

---

## Quick Copy-Paste Template

```vue
<template>
  <div class="page-wrap">
    <!-- Trigger button -->
    <button @click="openModal">Open Modal</button>
  </div>

  <!-- Modal (with Teleport) -->
  <Teleport to="body">
    <CModal
      :visible="modalVisible"
      @close="closeModal"
      alignment="center"
      backdrop="static">
      <div class="modal-header">
        <h5>Modal Title</h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>
      <div class="modal-body">
        <p>Content here</p>
      </div>
      <div class="modal-footer">
        <button @click="closeModal">Cancel</button>
        <button @click="handleSubmit">Submit</button>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { CModal } from '@coreui/vue'

const modalVisible = ref(false)

function openModal() {
  modalVisible.value = true
}

function closeModal() {
  modalVisible.value = false
}

function handleSubmit() {
  // Your logic here
  closeModal()
}
</script>
```

---

## Summary

🎯 **Golden Rule**: ALWAYS wrap `CModal` with `<Teleport to="body">`

✅ **Do:**
- Use Teleport for every modal
- Create separate refs for each modal
- Handle closing properly
- Disable actions during loading

❌ **Don't:**
- Nest modals without Teleport
- Share state between different modals
- Allow closing during operations
- Forget to reset state on close

---

**Last Updated:** 2026-02-09
**Applies To:** All Vue 3 projects using CoreUI Vue 5+
