<template>
  <div class="page-wrap">
    <!-- Page Header with KPI Cards -->
    <div class="page-header">
      <div>
        <h2 class="title">Broadcast Messaging</h2>
        <Breadcrumbs />
        <p class="text-muted small mb-0">
          Send WhatsApp/SMS messages to members, partners, and departments
        </p>
      </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="kpi-grid">
      <div class="kpi-card" style="--delay: 0s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)">
          <i class="bi bi-send-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Total Sent</div>
          <div class="kpi-value">{{ stats.total_sent.toLocaleString() }}</div>
          <div class="kpi-sublabel">All time broadcasts</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.1s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%)">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Delivery Rate</div>
          <div class="kpi-value">{{ stats.average_delivery_rate?.toFixed(1) || 0 }}%</div>
          <div class="kpi-sublabel">Average success rate</div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.2s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%)">
          <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">This Month</div>
          <div class="kpi-value">{{ stats.this_month }}</div>
          <div class="kpi-sublabel">
            {{ new Date().toLocaleString('default', { month: 'long' }) }}
          </div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.3s">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Scheduled</div>
          <div class="kpi-value">{{ stats.scheduled_count }}</div>
          <div class="kpi-sublabel">Pending broadcasts</div>
        </div>
      </div>
    </div>

    <!-- Compose Broadcast Section -->
    <CCard class="compose-card mb-4">
      <CCardHeader class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
          <div class="header-icon-box bg-primary-subtle text-primary">
            <i class="bi bi-megaphone-fill"></i>
          </div>
          <div>
            <h3 class="md-title-medium mb-0">New Broadcast</h3>
            <p class="text-muted small mb-0">Compose and send messages to your audience</p>
          </div>
        </div>
        <CButton v-if="!showComposer" color="primary" variant="ghost" @click="showComposer = true">
          <i class="bi bi-plus-lg me-1"></i> Compose
        </CButton>
        <CButton v-else color="light" size="sm" @click="showComposer = false">
          <i class="bi bi-x-lg"></i>
        </CButton>
      </CCardHeader>

      <CCardBody v-if="showComposer">
        <CForm @submit.prevent="handleSendBroadcast">
          <!-- Step 1: Recipients -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number" :class="{ complete: stepStatus.recipients }">
                <i v-if="stepStatus.recipients" class="bi bi-check-lg"></i>
                <span v-else>1</span>
              </div>
              <div class="step-title">
                <h4>Select Recipients</h4>
                <p class="text-muted small mb-0">Choose who will receive this message</p>
              </div>
            </div>
            <div class="step-content">
              <div class="recipient-options">
                <div
                  class="recipient-option"
                  :class="{ selected: form.recipientType === 'all_members' }"
                  @click="form.recipientType = 'all_members'"
                >
                  <i class="bi bi-people-fill"></i>
                  <div class="option-content">
                    <div class="option-label">All Members</div>
                    <div class="option-count">{{ recipientCounts.all_members }} recipients</div>
                  </div>
                  <div v-if="form.recipientType === 'all_members'" class="option-check">
                    <i class="bi bi-check-circle-fill text-primary"></i>
                  </div>
                </div>

                <div
                  class="recipient-option"
                  :class="{ selected: form.recipientType === 'partners' }"
                  @click="form.recipientType = 'partners'"
                >
                  <i class="bi bi-star-fill text-warning"></i>
                  <div class="option-content">
                    <div class="option-label">Partners Only</div>
                    <div class="option-count">{{ recipientCounts.partners }} recipients</div>
                  </div>
                  <div v-if="form.recipientType === 'partners'" class="option-check">
                    <i class="bi bi-check-circle-fill text-primary"></i>
                  </div>
                </div>

                <div
                  class="recipient-option"
                  :class="{ selected: form.recipientType === 'department' }"
                  @click="form.recipientType = 'department'"
                >
                  <i class="bi bi-building"></i>
                  <div class="option-content">
                    <div class="option-label">Specific Department</div>
                    <div class="option-count">Select below</div>
                  </div>
                  <div v-if="form.recipientType === 'department'" class="option-check">
                    <i class="bi bi-check-circle-fill text-primary"></i>
                  </div>
                </div>
              </div>

              <!-- Department Selector -->
              <div v-if="form.recipientType === 'department'" class="mt-3 animate-fadeIn">
                <CFormSelect v-model="form.departmentId" size="lg" class="department-select">
                  <option value="">Select department...</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                    {{ dept.name }} ({{ dept.member_count || 0 }} members)
                  </option>
                </CFormSelect>
              </div>

              <!-- Recipient Summary -->
              <div v-if="recipientCount > 0" class="recipient-summary animate-fadeIn">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                <span>
                  This message will be sent to <strong>{{ recipientCount }}</strong>
                  {{ recipientCount === 1 ? 'person' : 'people' }}
                </span>
                <CButton
                  color="light"
                  size="sm"
                  variant="ghost"
                  class="ms-2"
                  @click="showRecipientPreview"
                >
                  <i class="bi bi-eye me-1"></i> Preview
                </CButton>
              </div>
            </div>
          </div>

          <!-- Step 2: Channel -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number" :class="{ complete: stepStatus.channel }">
                <i v-if="stepStatus.channel" class="bi bi-check-lg"></i>
                <span v-else>2</span>
              </div>
              <div class="step-title">
                <h4>Choose Delivery Channel</h4>
                <p class="text-muted small mb-0">Select WhatsApp, SMS, or both</p>
              </div>
            </div>
            <div class="step-content">
              <div class="channel-grid">
                <div
                  class="channel-option"
                  :class="{ selected: form.channel === 'whatsapp' }"
                  @click="form.channel = 'whatsapp'"
                >
                  <div class="channel-icon bg-success-subtle">
                    <i class="bi bi-whatsapp fs-2 text-success"></i>
                  </div>
                  <h5 class="channel-name">WhatsApp</h5>
                  <div class="channel-features">
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-success"></i>
                      <span>Free & Unlimited</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-success"></i>
                      <span>Rich Formatting</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-success"></i>
                      <span>Read Receipts</span>
                    </div>
                  </div>
                  <div class="channel-cost">
                    <span class="cost-label">Cost:</span>
                    <span class="cost-value text-success fw-bold">FREE</span>
                  </div>
                </div>

                <div
                  class="channel-option"
                  :class="{ selected: form.channel === 'sms' }"
                  @click="form.channel = 'sms'"
                >
                  <div class="channel-icon bg-primary-subtle">
                    <i class="bi bi-chat-dots-fill fs-2 text-primary"></i>
                  </div>
                  <h5 class="channel-name">SMS</h5>
                  <div class="channel-features">
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-primary"></i>
                      <span>Universal Reach</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-primary"></i>
                      <span>No App Required</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-info-circle-fill text-warning"></i>
                      <span>160 char limit</span>
                    </div>
                  </div>
                  <div class="channel-cost">
                    <span class="cost-label">Cost:</span>
                    <span class="cost-value text-primary fw-bold">{{ estimatedCost }}</span>
                  </div>
                </div>

                <div
                  class="channel-option"
                  :class="{ selected: form.channel === 'both' }"
                  @click="form.channel = 'both'"
                >
                  <div class="channel-icon bg-info-subtle">
                    <i class="bi bi-broadcast fs-2 text-info"></i>
                  </div>
                  <h5 class="channel-name">Both Channels</h5>
                  <div class="channel-features">
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-info"></i>
                      <span>Max Coverage</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-check-circle-fill text-info"></i>
                      <span>Auto Fallback</span>
                    </div>
                    <div class="feature-item">
                      <i class="bi bi-info-circle-fill text-muted"></i>
                      <span>Tries WhatsApp first</span>
                    </div>
                  </div>
                  <div class="channel-cost">
                    <span class="cost-label">Max Cost:</span>
                    <span class="cost-value text-info fw-bold">{{ estimatedCost }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Message -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number" :class="{ complete: stepStatus.message }">
                <i v-if="stepStatus.message" class="bi bi-check-lg"></i>
                <span v-else>3</span>
              </div>
              <div class="step-title">
                <h4>Compose Your Message</h4>
                <p class="text-muted small mb-0">Write your message or use a template</p>
              </div>
            </div>
            <div class="step-content">
              <!-- Templates Quick Access -->
              <div class="templates-bar">
                <label class="small fw-semibold text-muted mb-2 d-block">
                  <i class="bi bi-layout-text-sidebar me-1"></i> Quick Templates
                </label>
                <div class="template-pills">
                  <button
                    v-for="template in templates"
                    :key="template.id"
                    type="button"
                    class="template-pill"
                    @click="useTemplate(template)"
                  >
                    <i class="bi bi-file-text"></i>
                    {{ template.name }}
                  </button>
                  <button type="button" class="template-pill template-pill-manage">
                    <i class="bi bi-gear"></i>
                    Manage
                  </button>
                </div>
              </div>

              <!-- Message Textarea -->
              <div class="message-editor">
                <CFormTextarea
                  v-model="form.message"
                  rows="6"
                  placeholder="Type your message here..."
                  :class="{ 'is-invalid': messageErrors.length > 0 }"
                  class="message-input"
                />

                <!-- Character Counter -->
                <div class="message-meta">
                  <div class="char-counter" :class="charCounterClass">
                    <i class="bi bi-type"></i>
                    <span
                      >{{ charCount }} / {{ form.channel === 'sms' ? '160' : '∞' }} characters</span
                    >
                  </div>

                  <div v-if="form.channel === 'sms' && smsParts > 1" class="sms-parts">
                    <i class="bi bi-segmented-nav text-warning"></i>
                    <span class="text-warning"> Will be sent as {{ smsParts }} messages </span>
                  </div>
                </div>

                <!-- Validation Errors -->
                <div v-if="messageErrors.length > 0" class="message-errors">
                  <div v-for="error in messageErrors" :key="error" class="error-item">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ error }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Schedule (Optional) -->
          <div class="compose-step">
            <div class="step-header">
              <div class="step-number optional">
                <i class="bi bi-clock"></i>
              </div>
              <div class="step-title">
                <h4>Schedule Delivery <span class="text-muted small">(Optional)</span></h4>
                <p class="text-muted small mb-0">Send now or schedule for later</p>
              </div>
            </div>
            <div class="step-content">
              <CFormCheck
                v-model="form.scheduled"
                label="Schedule for a specific date and time"
                class="schedule-toggle"
              />

              <div v-if="form.scheduled" class="schedule-inputs animate-fadeIn">
                <CRow class="g-3">
                  <CCol md="6">
                    <label class="form-label small fw-semibold">Date</label>
                    <CFormInput v-model="form.scheduleDate" type="date" :min="minDate" size="lg" />
                  </CCol>
                  <CCol md="6">
                    <label class="form-label small fw-semibold">Time</label>
                    <CFormInput v-model="form.scheduleTime" type="time" size="lg" />
                  </CCol>
                </CRow>
                <div class="schedule-preview">
                  <i class="bi bi-calendar-check text-primary"></i>
                  <span>
                    Will be sent on <strong>{{ scheduledDateTime }}</strong>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="compose-actions">
            <CButton color="light" size="lg" :disabled="sending" @click="resetForm">
              <i class="bi bi-x-circle me-2"></i>
              Cancel
            </CButton>

            <CButton
              color="secondary"
              size="lg"
              variant="outline"
              :disabled="!canSaveDraft || sending"
              @click="saveDraft"
            >
              <i class="bi bi-save me-2"></i>
              Save Draft
            </CButton>

            <CButton
              color="light"
              size="lg"
              variant="outline"
              :disabled="!canPreview"
              @click="previewMessage"
            >
              <i class="bi bi-eye me-2"></i>
              Preview
            </CButton>

            <CButton
              color="primary"
              size="lg"
              type="submit"
              :disabled="!canSend || sending"
              class="ms-auto"
            >
              <CSpinner v-if="sending" size="sm" class="me-2" />
              <i
                v-else
                :class="form.scheduled ? 'bi bi-calendar-plus' : 'bi bi-send-fill'"
                class="me-2"
              ></i>
              {{ form.scheduled ? 'Schedule Broadcast' : 'Send Now' }}
            </CButton>
          </div>
        </CForm>
      </CCardBody>
    </CCard>

    <!-- Recent Broadcasts -->
    <CCard>
      <CCardHeader class="d-flex justify-content-between align-items-center">
        <h3 class="md-title-medium mb-0">Recent Broadcasts</h3>
        <div class="d-flex gap-2 align-items-center">
          <CFormInput
            v-model="searchQuery"
            placeholder="Search broadcasts..."
            class="search-input"
            size="sm"
          >
            <template #prepend>
              <span class="input-group-text bg-transparent border-0">
                <i class="bi bi-search"></i>
              </span>
            </template>
          </CFormInput>

          <CDropdown variant="btn-group">
            <CDropdownToggle color="light" size="sm">
              <i class="bi bi-funnel me-1"></i> {{ filterLabel }}
            </CDropdownToggle>
            <CDropdownMenu>
              <CDropdownItem @click="statusFilter = null">All Broadcasts</CDropdownItem>
              <CDropdownDivider />
              <CDropdownItem @click="statusFilter = 'sent'">Sent</CDropdownItem>
              <CDropdownItem @click="statusFilter = 'partially_sent'">Partially Sent</CDropdownItem>
              <CDropdownItem @click="statusFilter = 'failed'">Failed</CDropdownItem>
              <CDropdownItem @click="statusFilter = 'scheduled'">Scheduled</CDropdownItem>
            </CDropdownMenu>
          </CDropdown>

          <CButton color="light" size="sm" @click="exportHistory">
            <i class="bi bi-download me-1"></i> Export
          </CButton>
        </div>
      </CCardHeader>

      <CCardBody class="p-0">
        <!-- Loading State -->
        <div v-if="isLoading" class="skeleton-container">
          <div v-for="i in 5" :key="i" class="skeleton-broadcast-item">
            <div class="skeleton skeleton-icon"></div>
            <div class="skeleton-content">
              <div class="skeleton skeleton-title"></div>
              <div class="skeleton skeleton-meta"></div>
            </div>
            <div class="skeleton skeleton-stats"></div>
          </div>
        </div>

        <!-- Broadcast List -->
        <div v-else-if="broadcasts.length > 0" class="broadcasts-list">
          <div
            v-for="broadcast in broadcasts"
            :key="broadcast.id"
            class="broadcast-item"
            @click="viewBroadcastDetails(broadcast)"
          >
            <div class="broadcast-icon" :class="`broadcast-icon-${broadcast.status}`">
              <i :class="getChannelIcon(broadcast.channel)"></i>
            </div>

            <div class="broadcast-content">
              <div class="broadcast-header">
                <h5 class="broadcast-title">{{ truncate(broadcast.message, 60) }}</h5>
                <CBadge :color="getStatusColor(broadcast.status)" class="status-badge">
                  {{ broadcast.status.replace('_', ' ') }}
                </CBadge>
              </div>
              <div class="broadcast-meta">
                <span class="meta-item">
                  <i class="bi bi-people"></i>
                  {{ broadcast.recipient_type.replace('_', ' ') }}
                </span>
                <span class="meta-divider">•</span>
                <span class="meta-item">
                  <i :class="getChannelIcon(broadcast.channel)"></i>
                  {{ broadcast.channel }}
                </span>
                <span class="meta-divider">•</span>
                <span class="meta-item">
                  <i class="bi bi-person-lines-fill"></i>
                  {{ broadcast.total_recipients }} recipients
                </span>
                <span class="meta-divider">•</span>
                <span class="meta-item text-muted">
                  <i class="bi bi-clock"></i>
                  {{ formatRelativeTime(broadcast.created_at) }}
                </span>
              </div>
            </div>

            <div class="broadcast-stats">
              <div class="delivery-progress">
                <div class="progress-info">
                  <span class="progress-label">Delivery</span>
                  <span class="progress-value"
                    >{{ broadcast.delivery_rate?.toFixed(0) || 0 }}%</span
                  >
                </div>
                <CProgress
                  :value="broadcast.delivery_rate || 0"
                  height="6"
                  :color="getProgressColor(broadcast.delivery_rate)"
                  class="delivery-progress-bar"
                />
                <div class="delivery-counts">
                  <span class="count-success">
                    <i class="bi bi-check-circle-fill"></i> {{ broadcast.delivered_count || 0 }}
                  </span>
                  <span v-if="broadcast.failed_count > 0" class="count-failed">
                    <i class="bi bi-x-circle-fill"></i> {{ broadcast.failed_count }}
                  </span>
                </div>
              </div>
            </div>

            <div class="broadcast-actions" @click.stop>
              <CButton
                v-if="broadcast.status === 'partially_sent'"
                color="warning"
                size="sm"
                variant="ghost"
                @click="retryFailed(broadcast)"
              >
                <i class="bi bi-arrow-clockwise"></i>
              </CButton>
              <CButton
                color="light"
                size="sm"
                variant="ghost"
                @click="viewBroadcastDetails(broadcast)"
              >
                <i class="bi bi-eye"></i>
              </CButton>
              <CButton
                color="light"
                size="sm"
                variant="ghost"
                @click="duplicateBroadcast(broadcast)"
              >
                <i class="bi bi-copy"></i>
              </CButton>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <i class="bi bi-inbox empty-icon"></i>
          <h4 class="empty-title">No broadcasts yet</h4>
          <p class="empty-text">Start by composing your first broadcast message</p>
          <CButton color="primary" @click="showComposer = true">
            <i class="bi bi-plus-lg me-2"></i>
            Create Broadcast
          </CButton>
        </div>
      </CCardBody>

      <!-- Pagination -->
      <div v-if="broadcasts.length > 0 && !isLoading" class="pagination-container">
        <div class="pagination-info">
          Showing <strong>{{ paginationStart }}</strong> to <strong>{{ paginationEnd }}</strong> of
          <strong>{{ pagination.total }}</strong> broadcasts
        </div>
        <CPagination class="mb-0">
          <CPaginationItem
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
          >
            <i class="bi bi-chevron-left"></i>
          </CPaginationItem>
          <CPaginationItem
            v-for="page in paginationPages"
            :key="page"
            :active="page === pagination.current_page"
            @click="page !== '...' && changePage(page)"
          >
            {{ page }}
          </CPaginationItem>
          <CPaginationItem
            :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
          >
            <i class="bi bi-chevron-right"></i>
          </CPaginationItem>
        </CPagination>
      </div>
    </CCard>

    <!-- Preview Modal -->
    <CModal
      :visible="showPreviewModal"
      size="md"
      alignment="center"
      @close="showPreviewModal = false"
    >
      <CModalHeader>
        <CModalTitle>Message Preview</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <div class="message-preview">
          <div class="preview-phone">
            <div class="phone-header">
              <i :class="form.channel === 'whatsapp' ? 'bi bi-whatsapp' : 'bi bi-chat-dots'"></i>
              <span>Prophetic Life Embassy</span>
            </div>
            <div class="phone-content">
              <div class="message-bubble">
                {{ form.message || 'Your message will appear here...' }}
              </div>
              <div class="message-time">
                {{ new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}
              </div>
            </div>
          </div>
          <div class="preview-details">
            <div class="detail-item">
              <i class="bi bi-people-fill text-primary"></i>
              <span
                >{{ recipientCount }} {{ recipientCount === 1 ? 'recipient' : 'recipients' }}</span
              >
            </div>
            <div class="detail-item">
              <i :class="getChannelIcon(form.channel) + ' text-success'"></i>
              <span>{{ form.channel.toUpperCase() }}</span>
            </div>
            <div v-if="form.scheduled" class="detail-item">
              <i class="bi bi-calendar-check text-warning"></i>
              <span>{{ scheduledDateTime }}</span>
            </div>
          </div>
        </div>
      </CModalBody>
      <CModalFooter>
        <CButton color="secondary" @click="showPreviewModal = false"> Close </CButton>
        <CButton
          color="primary"
          @click="
            showPreviewModal = false;
            handleSendBroadcast();
          "
        >
          {{ form.scheduled ? 'Schedule' : 'Send Now' }}
        </CButton>
      </CModalFooter>
    </CModal>

    <!-- Broadcast Details Modal -->
    <CModal :visible="showDetailsModal" size="xl" @close="showDetailsModal = false">
      <CModalHeader>
        <CModalTitle>Broadcast Details</CModalTitle>
      </CModalHeader>
      <CModalBody>
        <div v-if="selectedBroadcast">
          <!-- Details content here -->
          <h5>{{ selectedBroadcast.message }}</h5>
          <!-- Add delivery tracking, stats, etc. -->
        </div>
      </CModalBody>
    </CModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import {
  CCard,
  CCardBody,
  CCardHeader,
  CRow,
  CCol,
  CButton,
  CBadge,
  CFormInput,
  CFormSelect,
  CFormTextarea,
  CFormCheck,
  CForm,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CProgress,
  CSpinner,
  CPagination,
  CPaginationItem,
  CDropdown,
  CDropdownToggle,
  CDropdownMenu,
  CDropdownItem,
  CDropdownDivider,
} from '@coreui/vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';
import { useToast } from '../composables/useToast';
import { broadcastsApi } from '../api/broadcasts';
import { exportToExcel, formatDateForExport } from '../utils/export';

// Toast
const toast = useToast();

// UI State
const showComposer = ref(false);
const showPreviewModal = ref(false);
const showDetailsModal = ref(false);
const isLoading = ref(false);
const sending = ref(false);

// Data
const broadcasts = ref([]);
const departments = ref([]);
const templates = ref([]);
const stats = ref({
  total_sent: 0,
  average_delivery_rate: 0,
  this_month: 0,
  scheduled_count: 0,
});
const recipientCounts = ref({
  all_members: 0,
  partners: 0,
});
const selectedBroadcast = ref(null);

// Filters & Search
const searchQuery = ref('');
const statusFilter = ref(null);

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
});

// Form
const form = ref({
  recipientType: 'all_members',
  departmentId: '',
  channel: 'whatsapp',
  message: '',
  scheduled: false,
  scheduleDate: '',
  scheduleTime: '',
});

// Computed
const recipientCount = computed(() => {
  if (form.value.recipientType === 'all_members') {
    return recipientCounts.value.all_members;
  }
  if (form.value.recipientType === 'partners') {
    return recipientCounts.value.partners;
  }
  if (form.value.recipientType === 'department' && form.value.departmentId) {
    const dept = departments.value.find(d => d.id === form.value.departmentId);
    return dept?.member_count || 0;
  }
  return 0;
});

const charCount = computed(() => form.value.message.length);

const smsParts = computed(() => {
  const len = charCount.value;
  if (len === 0) return 0;
  if (len <= 160) return 1;
  if (len <= 306) return 2;
  if (len <= 459) return 3;
  return Math.ceil(len / 153);
});

const estimatedCost = computed(() => {
  if (form.value.channel === 'whatsapp') return 'FREE';
  const cost = recipientCount.value * smsParts.value * 0.05;
  return `$${cost.toFixed(2)}`;
});

const charCounterClass = computed(() => {
  if (form.value.channel !== 'sms') return 'text-muted';
  if (charCount.value > 160) return 'text-danger';
  if (charCount.value > 140) return 'text-warning';
  return 'text-muted';
});

const messageErrors = computed(() => {
  const errors = [];
  if (form.value.channel === 'sms' && charCount.value > 640) {
    errors.push('SMS messages cannot exceed 640 characters (4 parts)');
  }
  return errors;
});

const stepStatus = computed(() => ({
  recipients: recipientCount.value > 0,
  channel: !!form.value.channel,
  message: form.value.message.trim().length > 0,
}));

const canSend = computed(() => {
  if (!stepStatus.value.recipients || !stepStatus.value.channel || !stepStatus.value.message) {
    return false;
  }
  if (messageErrors.value.length > 0) return false;
  if (form.value.scheduled && (!form.value.scheduleDate || !form.value.scheduleTime)) {
    return false;
  }
  return true;
});

const canSaveDraft = computed(() => {
  return form.value.message.trim().length > 0;
});

const canPreview = computed(() => {
  return form.value.message.trim().length > 0 && recipientCount.value > 0;
});

const minDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const scheduledDateTime = computed(() => {
  if (!form.value.scheduleDate || !form.value.scheduleTime) return '';
  const date = new Date(`${form.value.scheduleDate}T${form.value.scheduleTime}`);
  return date.toLocaleString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});

const filterLabel = computed(() => {
  if (!statusFilter.value) return 'All';
  return statusFilter.value.replace('_', ' ');
});

const paginationStart = computed(() => {
  return (pagination.value.current_page - 1) * pagination.value.per_page + 1;
});

const paginationEnd = computed(() => {
  return Math.min(
    pagination.value.current_page * pagination.value.per_page,
    pagination.value.total
  );
});

const paginationPages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1);
  }

  if (current <= 3) {
    return [1, 2, 3, 4, 5, '...', last];
  }

  if (current >= last - 2) {
    return [1, '...', last - 4, last - 3, last - 2, last - 1, last];
  }

  return [1, '...', current - 1, current, current + 1, '...', last];
});

// Watchers
watch(searchQuery, () => {
  fetchBroadcasts(1);
});

watch(statusFilter, () => {
  fetchBroadcasts(1);
});

// Lifecycle
onMounted(() => {
  fetchStatistics();
  fetchBroadcasts();
  fetchDepartments();
  fetchTemplates();
  fetchRecipientCounts();
});

// Methods
async function fetchStatistics() {
  try {
    const res = await broadcastsApi.getStatistics();
    if (res.data.success) {
      stats.value = res.data.data;
    }
  } catch (err) {
    console.error('Failed to fetch statistics', err);
  }
}

async function fetchBroadcasts(page = 1) {
  isLoading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value,
    };
    if (statusFilter.value) {
      params.status = statusFilter.value;
    }

    const res = await broadcastsApi.getAll(params);
    if (res.data.success) {
      broadcasts.value = res.data.data;
      pagination.value = res.data.meta;
    }
  } catch (err) {
    toast.error('Failed to load broadcasts');
  } finally {
    isLoading.value = false;
  }
}

async function fetchDepartments() {
  // Mock data for now
  departments.value = [
    { id: 1, name: 'Media', member_count: 12 },
    { id: 2, name: 'Prayer Team', member_count: 18 },
    { id: 3, name: 'Ushering', member_count: 24 },
    { id: 4, name: 'Choir', member_count: 32 },
  ];
}

async function fetchTemplates() {
  templates.value = [
    {
      id: 1,
      name: 'Service Reminder',
      message: 'Join us this Sunday at 9am for an uplifting service!',
    },
    {
      id: 2,
      name: 'Event Announcement',
      message: 'Special event coming up! Mark your calendars for...',
    },
    { id: 3, name: 'Prayer Request', message: 'Please keep our church family in your prayers...' },
    {
      id: 4,
      name: 'Thanksgiving',
      message: 'We are grateful for your continued support and partnership...',
    },
  ];
}

async function fetchRecipientCounts() {
  recipientCounts.value = {
    all_members: 234,
    partners: 45,
  };
}

function useTemplate(template) {
  form.value.message = template.message;
  toast.success(`Template "${template.name}" applied`);
}

function previewMessage() {
  showPreviewModal.value = true;
}

async function handleSendBroadcast() {
  if (!canSend.value) return;

  sending.value = true;
  try {
    const payload = {
      recipient_type: form.value.recipientType,
      department_id: form.value.departmentId || null,
      channel: form.value.channel,
      message: form.value.message,
      scheduled_for: form.value.scheduled
        ? `${form.value.scheduleDate} ${form.value.scheduleTime}:00`
        : null,
    };

    const res = await broadcastsApi.create(payload);

    if (res.data.success) {
      if (form.value.scheduled) {
        toast.success('Broadcast scheduled successfully!');
      } else {
        toast.success('Broadcast sent successfully!');
      }
      resetForm();
      fetchBroadcasts();
      fetchStatistics();
      showComposer.value = false;
    }
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to send broadcast';
    toast.error(message);
  } finally {
    sending.value = false;
  }
}

async function saveDraft() {
  toast.info('Draft saving feature coming soon!');
}

function resetForm() {
  form.value = {
    recipientType: 'all_members',
    departmentId: '',
    channel: 'whatsapp',
    message: '',
    scheduled: false,
    scheduleDate: '',
    scheduleTime: '',
  };
}

function changePage(page) {
  if (page < 1 || page > pagination.value.last_page) return;
  fetchBroadcasts(page);
}

function viewBroadcastDetails(broadcast) {
  selectedBroadcast.value = broadcast;
  showDetailsModal.value = true;
}

function duplicateBroadcast(broadcast) {
  form.value.message = broadcast.message;
  form.value.channel = broadcast.channel;
  form.value.recipientType = broadcast.recipient_type;
  showComposer.value = true;
  toast.info('Broadcast duplicated, review and send');
}

async function retryFailed(broadcast) {
  try {
    await broadcastsApi.retryFailed(broadcast.id);
    toast.success('Retrying failed deliveries...');
    fetchBroadcasts(pagination.value.current_page);
  } catch (err) {
    toast.error('Failed to retry deliveries');
  }
}

function showRecipientPreview() {
  toast.info('Recipient preview modal coming soon!');
}

function exportHistory() {
  const columns = [
    { key: 'created_at', header: 'Date', transform: v => formatDateForExport(v) },
    { key: 'recipient_type', header: 'Recipients' },
    { key: 'total_recipients', header: 'Count' },
    { key: 'channel', header: 'Channel' },
    { key: 'message', header: 'Message' },
    { key: 'delivery_rate', header: 'Delivery %' },
    { key: 'status', header: 'Status' },
  ];
  exportToExcel(
    broadcasts.value,
    columns,
    `Broadcast_History_${new Date().toISOString().split('T')[0]}`
  );
  toast.success('Broadcast history exported');
}

// Helpers
function getChannelIcon(channel) {
  const icons = {
    whatsapp: 'bi bi-whatsapp',
    sms: 'bi bi-chat-dots',
    both: 'bi bi-broadcast',
  };
  return icons[channel] || 'bi bi-chat';
}

function getStatusColor(status) {
  const colors = {
    sent: 'success',
    partially_sent: 'warning',
    failed: 'danger',
    scheduled: 'info',
    queued: 'secondary',
    sending: 'primary',
  };
  return colors[status] || 'secondary';
}

function getProgressColor(rate) {
  if (rate >= 95) return 'success';
  if (rate >= 80) return 'warning';
  return 'danger';
}

function formatRelativeTime(date) {
  const now = new Date();
  const then = new Date(date);
  const diffMs = now - then;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return then.toLocaleDateString();
}

function truncate(str, length) {
  if (!str) return '';
  return str.length > length ? str.substring(0, length) + '...' : str;
}
</script>

<style scoped>
/* Continue in next message due to length... */
</style>
