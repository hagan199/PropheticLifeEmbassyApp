<template>
  <div class="empty-state">
    <div class="empty-icon">
      <i :class="icon"></i>
    </div>
    <h5 class="empty-title">{{ title }}</h5>
    <p class="empty-subtitle">
      {{ subtitle }}
    </p>
    <div v-if="showAction" class="d-flex gap-2 justify-content-center">
      <CButton v-if="primaryAction" :color="primaryAction.color || 'primary'" @click="$emit('primary-action')">
        <i :class="primaryAction.icon + ' me-2'" v-if="primaryAction.icon"></i>{{ primaryAction.label }}
      </CButton>
      <CButton v-if="secondaryAction" :color="secondaryAction.color || 'secondary'" variant="outline"
        @click="$emit('secondary-action')">
        <i :class="secondaryAction.icon + ' me-2'" v-if="secondaryAction.icon"></i>{{ secondaryAction.label }}
      </CButton>
    </div>
  </div>
</template>

<script setup>
import { CButton } from '@coreui/vue';

const props = defineProps({
  icon: {
    type: String,
    default: 'bi bi-person-x-fill'
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    required: true
  },
  showAction: {
    type: Boolean,
    default: true
  },
  primaryAction: {
    type: Object,
    default: null
  },
  secondaryAction: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['primary-action', 'secondary-action']);
</script>

<style scoped>
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
  border-radius: 20px;
  border: 2px dashed rgba(102, 126, 234, 0.2);
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.empty-icon i {
  font-size: 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.empty-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin-bottom: 1.5rem;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .empty-state {
    padding: 3rem 1.5rem;
  }

  .empty-icon {
    width: 60px;
    height: 60px;
  }

  .empty-icon i {
    font-size: 2rem;
  }

  .empty-title {
    font-size: 1.25rem;
  }

  .empty-subtitle {
    font-size: 0.9rem;
  }
}
</style>