<template>
  <CCard v-if="visitor" class="sticky-top" style="top: 30px">
    <CCardHeader class="d-flex justify-content-between align-items-center">
      <div class="fw-semibold">Visitor Details</div>
      <CButton color="light" size="sm" @click="$emit('close')">
        <i class="bi bi-x"></i>
      </CButton>
    </CCardHeader>
    <CCardBody>
      <!-- Visitor Info -->
      <div class="text-center mb-4 detail-header">
        <CAvatar :color="statusAvatarColor(visitor.status)" text-color="white" class="detail-avatar">
          {{ visitor.name.charAt(0) }}
        </CAvatar>
        <h4 class="mt-3 mb-1 detail-name">{{ visitor.name }}</h4>
        <div class="text-muted detail-phone">{{ visitor.phone }}</div>
        <CBadge :color="statusColor(visitor.status)" class="mt-2 detail-badge">
          {{ statusLabel(visitor.status) }}
        </CBadge>
      </div>

      <CRow class="g-3 mb-4">
        <CCol xs="6">
          <div class="text-muted small">Source</div>
          <div class="fw-semibold">{{ visitor.source }}</div>
        </CCol>
        <CCol xs="6">
          <div class="text-muted small">First Visit</div>
          <div class="fw-semibold">{{ formatDate(visitor.firstVisitDate) }}</div>
        </CCol>
        <CCol v-if="visitor.interests?.length" xs="12">
          <div class="text-muted small">Interests</div>
          <div>
            <CBadge v-for="i in visitor.interests" :key="i" color="info" class="me-1">{{ i }}</CBadge>
          </div>
        </CCol>
        <CCol v-if="visitor.notes" xs="12">
          <div class="text-muted small">Notes</div>
          <div>{{ visitor.notes }}</div>
        </CCol>
      </CRow>

      <!-- Quick Actions -->
      <div class="d-grid gap-2 mb-4">
        <CButton color="primary" @click="$emit('log-followup')">
          <i class="bi bi-plus-circle me-1"></i> Log Follow-up
        </CButton>
        <div class="d-flex gap-2">
          <CButton color="success" class="flex-fill" @click="$emit('call')">
            <i class="bi bi-telephone"></i>
          </CButton>
          <CButton color="success" class="flex-fill" @click="$emit('whatsapp')">
            <i class="bi bi-whatsapp"></i>
          </CButton>
          <CButton color="primary" class="flex-fill" @click="$emit('sms')">
            <i class="bi bi-chat-dots"></i>
          </CButton>
        </div>
        <CButton v-if="visitor.status === 'engaged'" color="success" variant="outline" @click="$emit('convert')">
          <i class="bi bi-person-check me-1"></i> Convert to Member
        </CButton>
      </div>

      <!-- Follow-up History -->
      <div class="fw-semibold mb-2">Follow-up History</div>
      <div v-if="!visitor.followUps?.length" class="text-muted small">
        No follow-ups logged yet
      </div>
      <div v-else class="timeline">
        <div v-for="f in visitor.followUps" :key="f.id" class="timeline-item">
          <div class="timeline-marker" :class="'bg-' + methodColor(f.method)"></div>
          <div class="timeline-content">
            <div class="d-flex justify-content-between">
              <CBadge :color="methodColor(f.method)" size="sm">
                <i :class="methodIcon(f.method)" class="me-1"></i>{{ f.method }}
              </CBadge>
              <span class="text-muted small">{{ formatDate(f.date) }}</span>
            </div>
            <div class="mt-1">{{ f.notes }}</div>
            <div v-if="f.statusAfter" class="text-muted small mt-1">
              Status → {{ statusLabel(f.statusAfter) }}
            </div>
          </div>
        </div>
      </div>
    </CCardBody>
  </CCard>

  <CCard v-else>
    <CCardBody class="text-center py-5 text-muted">
      <i class="bi bi-hand-index fs-1 d-block mb-2"></i>
      Select a visitor to view details
    </CCardBody>
  </CCard>
</template>

<script setup>
import { CCard, CCardHeader, CCardBody, CRow, CCol, CButton, CAvatar, CBadge } from '@coreui/vue';

defineProps({
  visitor: { type: [Object, null], default: null },
});

defineEmits(['close', 'log-followup', 'call', 'whatsapp', 'sms', 'convert']);

function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
  });
}

function statusColor(status) {
  const colors = { not_contacted: 'danger', contacted: 'info', engaged: 'warning', converted: 'success' };
  return colors[status] || 'secondary';
}

function statusAvatarColor(status) {
  return statusColor(status);
}

function statusLabel(status) {
  const labels = { not_contacted: 'Not Contacted', contacted: 'Contacted', engaged: 'Engaged', converted: 'Converted' };
  return labels[status] || status;
}

function methodColor(method) {
  const colors = { WhatsApp: 'success', SMS: 'primary', Call: 'info', 'In-Person': 'warning' };
  return colors[method] || 'secondary';
}

function methodIcon(method) {
  const icons = { WhatsApp: 'bi bi-whatsapp', SMS: 'bi bi-chat-dots', Call: 'bi bi-telephone', 'In-Person': 'bi bi-person' };
  return icons[method] || 'bi bi-circle';
}
</script>

<style scoped>
.detail-avatar {
  width: 96px;
  height: 96px;
  font-size: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.detail-name {
  font-size: 1.25rem;
  font-weight: 700;
}

.detail-phone {
  color: #6c757d;
  margin-bottom: 6px;
}

.detail-badge {
  font-size: 0.85rem;
  padding: 0.35rem 0.6rem;
}

.detail-header {
  padding-top: 8px;
}

.timeline {
  position: relative;
  padding-left: 24px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 6px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e9ecef;
}

.timeline-item {
  position: relative;
  padding-bottom: 16px;
}

.timeline-marker {
  position: absolute;
  left: -24px;
  top: 4px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
}

.timeline-content {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 8px;
}
</style>
