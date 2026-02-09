<template>
  <div class="users-pagination">
    <div class="pagination-info">
      Showing <span class="fw-bold text-dark">{{ startItem }}</span> to
      <span class="fw-bold text-dark">{{ endItem }}</span>
      of <span class="fw-bold text-dark">{{ totalItems }}</span> {{ itemLabel }}
    </div>
    <CPagination class="mb-0">
      <CPaginationItem :disabled="currentPage === 1" aria-label="Previous"
        @click="$emit('page-change', currentPage - 1)">
        <i class="bi bi-chevron-left"></i>
      </CPaginationItem>
      <CPaginationItem v-for="p in totalPages" :key="p" :active="p === currentPage" @click="$emit('page-change', p)">
        {{ p }}
      </CPaginationItem>
      <CPaginationItem :disabled="currentPage === totalPages" aria-label="Next"
        @click="$emit('page-change', currentPage + 1)">
        <i class="bi bi-chevron-right"></i>
      </CPaginationItem>
    </CPagination>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { CPagination, CPaginationItem } from '@coreui/vue';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 25
  },
  totalItems: {
    type: Number,
    required: true
  },
  itemLabel: {
    type: String,
    default: 'items'
  }
});

const emit = defineEmits(['page-change']);

const totalPages = computed(() => Math.max(1, Math.ceil(props.totalItems / props.perPage)));

const startItem = computed(() => (props.currentPage - 1) * props.perPage + 1);

const endItem = computed(() => Math.min(props.currentPage * props.perPage, props.totalItems));
</script>

<style scoped>
.users-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
  padding: 1.5rem 1rem;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.pagination-info {
  font-size: 0.875rem;
  color: #64748b;
}
</style>