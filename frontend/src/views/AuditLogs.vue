<template>
  <div class="audit-logs-page">
    <PageHeader
      title="Audit Logs"
      subtitle="Track all system activities, security events, and data changes"
    >
      <template #actions>
        <button class="md-btn md-btn-gradient" @click="exportLogs">
          <i class="bi bi-download"></i>
          <span class="btn-text">Export</span>
        </button>
        <button class="md-btn md-btn-glass" @click="fetchLogs">
          <i class="bi bi-arrow-clockwise" :class="{ rotating: loading }"></i>
          <span class="btn-text">Refresh</span>
        </button>
      </template>
    </PageHeader>

    <!-- Summary Stats -->
    <div class="kpi-grid">
      <div
        v-for="(stat, index) in summaryStats"
        :key="stat.label"
        class="kpi-card"
        :class="{ 'active-filter': filters.action === stat.actionValue }"
        :style="{ '--delay': index * 0.1 + 's' }"
        @click="toggleActionFilter(stat.actionValue)"
      >
        <div class="kpi-icon" :class="stat.iconClass">
          <i :class="stat.icon"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">{{ stat.label }}</div>
          <div class="kpi-value">{{ stat.value }}</div>
          <div class="kpi-sublabel">{{ stat.sublabel }}</div>
        </div>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
      <div class="filter-group">
        <label class="filter-label">Time Period</label>
        <div class="date-range-inputs">
          <input
            v-model="filters.dateFrom"
            type="date"
            class="md-input"
            :max="filters.dateTo || today"
          />
          <span class="date-separator">to</span>
          <input
            v-model="filters.dateTo"
            type="date"
            class="md-input"
            :min="filters.dateFrom"
            :max="today"
          />
        </div>
      </div>

      <div class="filter-group">
        <label class="filter-label">User</label>
        <div ref="userSelectRef" class="searchable-select">
          <div class="select-trigger" @click="toggleUserDropdown">
            <span class="select-value">{{ selectedUserName || 'All Users' }}</span>
            <i class="bi bi-chevron-down select-icon"></i>
          </div>
          <transition name="dropdown">
            <div v-if="showUserDropdown" class="select-dropdown">
              <div class="select-search">
                <i class="bi bi-search"></i>
                <input
                  ref="userSearchInput"
                  v-model="userSearch"
                  type="text"
                  placeholder="Search users..."
                  class="search-input-small"
                  @click.stop
                />
              </div>
              <div class="select-options">
                <div
                  class="select-option"
                  :class="{ active: filters.userId === '' }"
                  @click="selectUser('')"
                >
                  <i class="bi bi-people"></i>
                  <span>All Users</span>
                </div>
                <div
                  v-for="u in filteredUsers"
                  :key="u.id"
                  class="select-option"
                  :class="{ active: filters.userId === u.id }"
                  @click="selectUser(u.id, u.name)"
                >
                  <div class="user-avatar-small">{{ u.name.charAt(0) }}</div>
                  <span>{{ u.name }}</span>
                </div>
                <div v-if="filteredUsers.length === 0" class="select-option-empty">
                  No users found
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <div class="filter-group">
        <label class="filter-label">Action Type</label>
        <select v-model="filters.action" class="md-select">
          <option value="">All Actions</option>
          <option value="create">Create</option>
          <option value="update">Update</option>
          <option value="delete">Delete</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
        </select>
      </div>

      <div class="filter-group flex-grow">
        <label class="filter-label">Search</label>
        <div class="search-input-wrapper">
          <i class="bi bi-search search-icon"></i>
          <input
            v-model="filters.search"
            type="text"
            class="md-input search-input"
            placeholder="Search by description, IP, or module..."
          />
          <button v-if="filters.search" class="clear-search" @click="filters.search = ''">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>

      <button
        v-if="hasActiveFilters"
        class="md-btn md-btn-text clear-filters-btn"
        @click="clearFilters"
      >
        <i class="bi bi-x-circle"></i>
        Clear Filters
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="skeleton-container">
      <div v-for="i in 8" :key="i" class="skeleton-row">
        <div class="skeleton skeleton-avatar"></div>
        <div class="skeleton skeleton-text skeleton-text-lg"></div>
        <div class="skeleton skeleton-badge"></div>
        <div class="skeleton skeleton-text"></div>
        <div class="skeleton skeleton-button"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredLogs.length === 0" class="empty-state">
      <div class="empty-icon">
        <i class="bi bi-inbox"></i>
      </div>
      <h3 class="empty-title">No audit logs found</h3>
      <p class="empty-text">
        {{
          hasActiveFilters
            ? 'Try adjusting your filters'
            : 'No system activities have been logged yet'
        }}
      </p>
      <button v-if="hasActiveFilters" class="md-btn md-btn-filled" @click="clearFilters">
        Clear Filters
      </button>
    </div>

    <!-- Logs Table -->
    <div v-else class="logs-table-container">
      <div class="table-wrapper">
        <table class="logs-table">
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>User</th>
              <th>Action</th>
              <th class="hide-mobile">Module</th>
              <th class="hide-tablet">IP Address</th>
              <th class="hide-mobile">Description</th>
              <th class="text-end">Details</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in paginatedLogs"
              :key="log.id"
              class="log-row"
              @click="viewLogDetails(log)"
            >
              <td class="timestamp-cell">
                <div class="timestamp-date">{{ formatDate(log.createdAt) }}</div>
                <div class="timestamp-time">{{ formatTime(log.createdAt) }}</div>
              </td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar" :class="`avatar-${roleColor(log.userRole)}`">
                    {{ log.userName?.charAt(0) || '?' }}
                  </div>
                  <div class="user-info">
                    <div class="user-name">{{ log.userName }}</div>
                    <div class="user-role">{{ log.userRole }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="action-badge" :class="`badge-${actionColor(log.action)}`">
                  {{ log.action }}
                </span>
              </td>
              <td class="hide-mobile">
                <div class="module-cell">
                  <i :class="moduleIcon(log.module)" class="module-icon"></i>
                  <span>{{ log.module }}</span>
                </div>
              </td>
              <td class="hide-tablet">
                <code class="ip-address">{{ log.ipAddress || 'N/A' }}</code>
              </td>
              <td class="hide-mobile description-cell">
                {{ log.description }}
              </td>
              <td class="text-end">
                <button class="icon-btn" @click.stop="viewLogDetails(log)">
                  <i class="bi bi-chevron-right"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination-container">
        <div class="pagination-info">
          Showing
          <strong
            >{{ (currentPage - 1) * perPage + 1 }}-{{
              Math.min(currentPage * perPage, filteredLogs.length)
            }}</strong
          >
          of <strong>{{ filteredLogs.length }}</strong> entries
        </div>
        <div class="pagination">
          <button class="pagination-btn" :disabled="currentPage === 1" @click="currentPage--">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button
            v-for="p in visiblePages"
            :key="p"
            class="pagination-btn"
            :class="{ active: p === currentPage }"
            @click="currentPage = p"
          >
            {{ p }}
          </button>
          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <Teleport to="body">
      <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h3 class="modal-title">Audit Log Details</h3>
            <button class="modal-close" @click="closeDetailsModal">
              <i class="bi bi-x"></i>
            </button>
          </div>
          <div v-if="selectedLog" class="modal-body">
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">Timestamp</div>
                <div class="detail-value">{{ formatDateTime(selectedLog.createdAt) }}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">User</div>
                <div class="detail-value">
                  <div class="user-cell">
                    <div class="user-avatar" :class="`avatar-${roleColor(selectedLog.userRole)}`">
                      {{ selectedLog.userName?.charAt(0) || '?' }}
                    </div>
                    <div class="user-info">
                      <div class="user-name">{{ selectedLog.userName }}</div>
                      <div class="user-role">{{ selectedLog.userRole }}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Action</div>
                <div class="detail-value">
                  <span class="action-badge" :class="`badge-${actionColor(selectedLog.action)}`">
                    {{ selectedLog.action }}
                  </span>
                </div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Module</div>
                <div class="detail-value">
                  <i :class="moduleIcon(selectedLog.module)" class="me-2"></i>
                  {{ selectedLog.module }}
                </div>
              </div>
              <div class="detail-item">
                <div class="detail-label">IP Address</div>
                <div class="detail-value">
                  <code class="ip-address">{{ selectedLog.ipAddress || 'N/A' }}</code>
                </div>
              </div>
              <div class="detail-item">
                <div class="detail-label">User Agent</div>
                <div class="detail-value text-muted small">
                  {{ selectedLog.userAgent || 'N/A' }}
                </div>
              </div>
              <div class="detail-item full-width">
                <div class="detail-label">Description</div>
                <div class="detail-value">{{ selectedLog.description }}</div>
              </div>
              <div v-if="selectedLog.changes" class="detail-item full-width">
                <div class="detail-label">Changes</div>
                <pre class="changes-json">{{ JSON.stringify(selectedLog.changes, null, 2) }}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useToast } from '../composables/useToast';
import PageHeader from '@/components/shared/PageHeader.vue';
import { auditLogsApi } from '@/api/auditLogs';

const toast = useToast();

// Reactive States
const loading = ref(false);
const logs = ref([]);
const users = ref([]);
const currentPage = ref(1);
const perPage = ref(15);
const showDetailsModal = ref(false);
const selectedLog = ref(null);

// Searchable User Dropdown
const showUserDropdown = ref(false);
const userSearch = ref('');
const selectedUserName = ref('');
const userSelectRef = ref(null);
const userSearchInput = ref(null);

// Filters
const filters = ref({
  dateFrom: '',
  dateTo: '',
  userId: '',
  action: '',
  search: '',
});

// Today's date for max date restriction
const today = computed(() => {
  const date = new Date();
  return date.toISOString().split('T')[0];
});

// Check if filters are active
const hasActiveFilters = computed(() => {
  return (
    filters.value.dateFrom ||
    filters.value.dateTo ||
    filters.value.userId ||
    filters.value.action ||
    filters.value.search
  );
});

// Filtered Users for Searchable Dropdown
const filteredUsers = computed(() => {
  if (!userSearch.value) return users.value;

  const search = userSearch.value.toLowerCase();
  return users.value.filter(u => u.name?.toLowerCase().includes(search));
});

// Summary Stats
const summaryStats = computed(() => {
  const total = logs.value.length;
  const creates = logs.value.filter(l => l.action === 'create').length;
  const updates = logs.value.filter(l => l.action === 'update').length;
  const deletes = logs.value.filter(l => l.action === 'delete').length;

  return [
    {
      label: 'Total Activities',
      value: total,
      sublabel: 'All time',
      icon: 'bi bi-activity',
      iconClass: 'icon-primary',
      actionValue: '',
    },
    {
      label: 'Created',
      value: creates,
      sublabel: 'New records',
      icon: 'bi bi-plus-circle',
      iconClass: 'icon-success',
      actionValue: 'create',
    },
    {
      label: 'Updated',
      value: updates,
      sublabel: 'Modifications',
      icon: 'bi bi-pencil-square',
      iconClass: 'icon-info',
      actionValue: 'update',
    },
    {
      label: 'Deleted',
      value: deletes,
      sublabel: 'Removed items',
      icon: 'bi bi-trash',
      iconClass: 'icon-danger',
      actionValue: 'delete',
    },
  ];
});

// Filtered Logs
const filteredLogs = computed(() => {
  let result = [...logs.value];

  if (filters.value.dateFrom) {
    result = result.filter(log => {
      const logDate = new Date(log.createdAt).toISOString().split('T')[0];
      return logDate >= filters.value.dateFrom;
    });
  }

  if (filters.value.dateTo) {
    result = result.filter(log => {
      const logDate = new Date(log.createdAt).toISOString().split('T')[0];
      return logDate <= filters.value.dateTo;
    });
  }

  if (filters.value.userId) {
    result = result.filter(log => log.userId === filters.value.userId);
  }

  if (filters.value.action) {
    result = result.filter(log => log.action === filters.value.action);
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    result = result.filter(
      log =>
        log.description?.toLowerCase().includes(search) ||
        log.ipAddress?.toLowerCase().includes(search) ||
        log.module?.toLowerCase().includes(search) ||
        log.userName?.toLowerCase().includes(search)
    );
  }

  return result;
});

// Paginated Logs
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredLogs.value.slice(start, end);
});

// Total Pages
const totalPages = computed(() => {
  return Math.ceil(filteredLogs.value.length / perPage.value);
});

// Visible Pages for Pagination
const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);

  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Helper Functions
const roleColor = role => {
  const colors = {
    admin: 'danger',
    pastor: 'primary',
    leader: 'info',
    member: 'success',
    default: 'secondary',
  };
  return colors[role?.toLowerCase()] || colors.default;
};

const actionColor = action => {
  const colors = {
    create: 'success',
    update: 'info',
    delete: 'danger',
    login: 'primary',
    logout: 'warning',
    default: 'secondary',
  };
  return colors[action?.toLowerCase()] || colors.default;
};

const moduleIcon = module => {
  const icons = {
    users: 'bi bi-people',
    attendance: 'bi bi-calendar-check',
    visitors: 'bi bi-person-plus',
    finance: 'bi bi-currency-dollar',
    departments: 'bi bi-building',
    broadcasts: 'bi bi-megaphone',
    settings: 'bi bi-gear',
    auth: 'bi bi-shield-lock',
    default: 'bi bi-box',
  };
  return icons[module?.toLowerCase()] || icons.default;
};

const formatDate = dateStr => {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const formatTime = dateStr => {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatDateTime = dateStr => {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

// Actions
const fetchLogs = async () => {
  loading.value = true;
  try {
    const response = await auditLogsApi.getAll({
      date_from: filters.value.dateFrom,
      date_to: filters.value.dateTo,
      user_id: filters.value.userId,
      action: filters.value.action,
      search: filters.value.search,
    });

    console.log('API Response:', response); // Debug log

    // Handle different response structures
    const responseData = response.data || response;

    if (responseData.success !== undefined && responseData.success === false) {
      throw new Error(responseData.message || 'API returned error');
    }

    // Extract data from response
    logs.value = responseData.data || [];
    users.value = responseData.users || [];
  } catch (error) {
    console.error('Failed to fetch audit logs:', error);
    console.error('Error details:', error.response);

    // Fallback to empty state on error
    logs.value = [];
    users.value = [];

    toast.error(error.response?.data?.message || 'Failed to load audit logs');
  } finally {
    loading.value = false;
  }
};

const toggleActionFilter = actionValue => {
  if (filters.value.action === actionValue) {
    filters.value.action = '';
  } else {
    filters.value.action = actionValue;
  }
  currentPage.value = 1;
};

const clearFilters = () => {
  filters.value = {
    dateFrom: '',
    dateTo: '',
    userId: '',
    action: '',
    search: '',
  };
  selectedUserName.value = '';
  currentPage.value = 1;
};

// Searchable Dropdown Functions
const toggleUserDropdown = async () => {
  showUserDropdown.value = !showUserDropdown.value;
  if (showUserDropdown.value) {
    await nextTick();
    userSearchInput.value?.focus();
  }
};

const selectUser = (userId, userName = '') => {
  filters.value.userId = userId;
  selectedUserName.value = userName;
  showUserDropdown.value = false;
  userSearch.value = '';
  currentPage.value = 1;
};

const closeUserDropdown = event => {
  if (userSelectRef.value && !userSelectRef.value.contains(event.target)) {
    showUserDropdown.value = false;
    userSearch.value = '';
  }
};

const viewLogDetails = log => {
  selectedLog.value = log;
  showDetailsModal.value = true;
};

const closeDetailsModal = () => {
  showDetailsModal.value = false;
  selectedLog.value = null;
};

const exportLogs = async () => {
  try {
    // Use filtered data for local export
    const dataStr = JSON.stringify(filteredLogs.value, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `audit-logs-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    URL.revokeObjectURL(url);

    toast.success('Audit logs exported successfully');
  } catch (error) {
    console.error('Failed to export logs:', error);
    toast.error('Failed to export audit logs');
  }
};

// Watch filters to reset pagination
watch(
  () => filters.value,
  () => {
    currentPage.value = 1;
  },
  { deep: true }
);

// Lifecycle
onMounted(() => {
  fetchLogs();
  document.addEventListener('click', closeUserDropdown);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', closeUserDropdown);
});
</script>

<style scoped>
/* Variables */
:root {
  --md-primary: #6366f1;
  --md-primary-dark: #4f46e5;
  --md-success: #10b981;
  --md-danger: #ef4444;
  --md-warning: #f59e0b;
  --md-info: #3b82f6;
  --md-surface: #ffffff;
  --md-surface-variant: #f8fafc;
  --md-on-surface: #1e293b;
  --md-on-surface-muted: #64748b;
  --md-border: #e2e8f0;
  --md-elevation-1: 0 1px 3px rgba(0, 0, 0, 0.08);
  --md-elevation-2: 0 4px 12px rgba(0, 0, 0, 0.1);
  --md-elevation-3: 0 8px 24px rgba(0, 0, 0, 0.12);
}

/* Layout */
.audit-logs-page {
  padding: 2rem;
  background: linear-gradient(135deg, #667eea11 0%, #764ba222 100%);
  min-height: 100vh;
  animation: fadeIn 0.4s ease;
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.5rem;
  background: var(--md-surface);
  border-radius: 16px;
  border: 2px solid transparent;
  box-shadow: var(--md-elevation-1);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: slideUp 0.5s ease backwards;
  animation-delay: var(--delay);
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--md-elevation-2);
}

.kpi-card.active-filter {
  border-color: var(--md-primary);
  background: linear-gradient(135deg, #6366f111 0%, #8b5cf611 100%);
}

.kpi-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.icon-primary {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
}

.icon-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.icon-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.icon-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.kpi-content {
  flex: 1;
  min-width: 0;
}

.kpi-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--md-on-surface-muted);
  margin-bottom: 0.25rem;
}

.kpi-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--md-on-surface);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.kpi-sublabel {
  font-size: 0.875rem;
  color: var(--md-on-surface-muted);
}

/* Filters Bar */
.filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--md-surface);
  border-radius: 16px;
  box-shadow: var(--md-elevation-1);
  margin-bottom: 2rem;
  animation: slideUp 0.5s ease 0.2s backwards;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 140px;
}

.filter-group.flex-grow {
  flex: 1;
  min-width: 200px;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--md-on-surface-muted);
}

.date-range-inputs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-separator {
  font-size: 0.875rem;
  color: var(--md-on-surface-muted);
  padding: 0 0.25rem;
}

.md-input,
.md-select {
  padding: 0.625rem 0.875rem;
  border: 1.5px solid var(--md-border);
  border-radius: 10px;
  font-size: 0.875rem;
  color: var(--md-on-surface);
  background: var(--md-surface);
  transition: all 0.2s ease;
  width: 100%;
}

.md-input:focus,
.md-select:focus {
  outline: none;
  border-color: var(--md-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-input-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--md-on-surface-muted);
  pointer-events: none;
}

.search-input {
  padding-left: 2.5rem;
  padding-right: 2.5rem;
}

.clear-search {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--md-on-surface-muted);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.clear-search:hover {
  background: var(--md-surface-variant);
  color: var(--md-on-surface);
}

.clear-filters-btn {
  align-self: flex-end;
}

/* Searchable Select */
.searchable-select {
  position: relative;
  min-width: 180px;
}

.select-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.625rem 0.875rem;
  border: 1.5px solid var(--md-border);
  border-radius: 10px;
  font-size: 0.875rem;
  color: var(--md-on-surface);
  background: var(--md-surface);
  cursor: pointer;
  transition: all 0.2s ease;
}

.select-trigger:hover {
  border-color: var(--md-primary);
}

.select-value {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.select-icon {
  margin-left: 0.5rem;
  font-size: 0.75rem;
  color: var(--md-on-surface-muted);
  transition: transform 0.2s ease;
}

.searchable-select:hover .select-icon {
  color: var(--md-primary);
}

.select-dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: var(--md-surface);
  border: 1.5px solid var(--md-border);
  border-radius: 10px;
  box-shadow: var(--md-elevation-3);
  z-index: 100;
  max-height: 300px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.select-search {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-bottom: 1.5px solid var(--md-border);
  background: var(--md-surface-variant);
}

.select-search i {
  color: var(--md-on-surface-muted);
  font-size: 0.875rem;
}

.search-input-small {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 0.875rem;
  color: var(--md-on-surface);
  outline: none;
}

.search-input-small::placeholder {
  color: var(--md-on-surface-muted);
}

.select-options {
  overflow-y: auto;
  max-height: 240px;
}

.select-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid var(--md-border);
}

.select-option:last-child {
  border-bottom: none;
}

.select-option:hover {
  background: var(--md-surface-variant);
}

.select-option.active {
  background: rgba(99, 102, 241, 0.1);
  color: var(--md-primary);
  font-weight: 600;
}

.select-option i {
  font-size: 1.125rem;
  color: var(--md-on-surface-muted);
}

.select-option.active i {
  color: var(--md-primary);
}

.user-avatar-small {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.75rem;
  color: white;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  flex-shrink: 0;
}

.select-option-empty {
  padding: 1rem;
  text-align: center;
  color: var(--md-on-surface-muted);
  font-size: 0.875rem;
}

/* Dropdown Transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

/* Buttons */
.md-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.md-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.md-btn-gradient {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.md-btn-gradient:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
  transform: translateY(-2px);
}

.md-btn-glass {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  color: var(--md-on-surface);
}

.md-btn-glass:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.9);
  transform: translateY(-2px);
}

.md-btn-text {
  background: none;
  color: var(--md-primary);
}

.md-btn-text:hover:not(:disabled) {
  background: rgba(99, 102, 241, 0.1);
}

.md-btn-filled {
  background: var(--md-primary);
  color: white;
}

.md-btn-filled:hover:not(:disabled) {
  background: var(--md-primary-dark);
  transform: translateY(-2px);
}

/* Skeleton Loading */
.skeleton-container {
  background: var(--md-surface);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: var(--md-elevation-1);
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--md-border);
}

.skeleton-row:last-child {
  border-bottom: none;
}

.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

.skeleton-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-text {
  height: 16px;
  flex: 1;
}

.skeleton-text-lg {
  height: 20px;
}

.skeleton-badge {
  width: 80px;
  height: 24px;
}

.skeleton-button {
  width: 32px;
  height: 32px;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }

  100% {
    background-position: 200% 0;
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: var(--md-surface);
  border-radius: 16px;
  box-shadow: var(--md-elevation-1);
  animation: fadeIn 0.5s ease;
}

.empty-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 1.5rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea22 0%, #764ba233 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: var(--md-primary);
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--md-on-surface);
  margin-bottom: 0.5rem;
}

.empty-text {
  color: var(--md-on-surface-muted);
  margin-bottom: 1.5rem;
}

/* Logs Table */
.logs-table-container {
  background: var(--md-surface);
  border-radius: 16px;
  box-shadow: var(--md-elevation-1);
  overflow: hidden;
  animation: slideUp 0.5s ease 0.3s backwards;
}

.table-wrapper {
  overflow-x: auto;
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
}

.logs-table thead {
  background: var(--md-surface-variant);
  border-bottom: 2px solid var(--md-border);
}

.logs-table th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--md-on-surface-muted);
  white-space: nowrap;
}

.logs-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--md-border);
}

.log-row {
  cursor: pointer;
  transition: background 0.2s ease;
}

.log-row:hover {
  background: var(--md-surface-variant);
}

.timestamp-cell {
  min-width: 120px;
}

.timestamp-date {
  font-weight: 600;
  color: var(--md-on-surface);
  font-size: 0.875rem;
}

.timestamp-time {
  font-size: 0.75rem;
  color: var(--md-on-surface-muted);
  margin-top: 0.125rem;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 150px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
  color: white;
  flex-shrink: 0;
}

.avatar-primary {
  background: var(--md-primary);
}

.avatar-success {
  background: var(--md-success);
}

.avatar-danger {
  background: var(--md-danger);
}

.avatar-info {
  background: var(--md-info);
}

.avatar-secondary {
  background: var(--md-on-surface-muted);
}

.user-info {
  min-width: 0;
}

.user-name {
  font-weight: 600;
  color: var(--md-on-surface);
  font-size: 0.875rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.75rem;
  color: var(--md-on-surface-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.action-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.875rem;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.badge-success {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.badge-info {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.badge-danger {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.badge-primary {
  background: rgba(99, 102, 241, 0.1);
  color: #4f46e5;
}

.badge-warning {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.badge-secondary {
  background: rgba(100, 116, 139, 0.1);
  color: #475569;
}

.module-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 120px;
}

.module-icon {
  color: var(--md-on-surface-muted);
}

.ip-address {
  font-family: 'Courier New', monospace;
  font-size: 0.8125rem;
  padding: 0.25rem 0.5rem;
  background: var(--md-surface-variant);
  border-radius: 4px;
  color: var(--md-primary);
}

.description-cell {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--md-on-surface-muted);
  font-size: 0.875rem;
}

.icon-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: var(--md-surface-variant);
  color: var(--md-on-surface-muted);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-btn:hover {
  background: var(--md-primary);
  color: white;
  transform: scale(1.1);
}

.text-end {
  text-align: right;
}

/* Pagination */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-top: 1px solid var(--md-border);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  font-size: 0.875rem;
  color: var(--md-on-surface-muted);
}

.pagination-info strong {
  color: var(--md-on-surface);
  font-weight: 600;
}

.pagination {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  width: 36px;
  height: 36px;
  border: 1.5px solid var(--md-border);
  background: var(--md-surface);
  color: var(--md-on-surface);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 600;
}

.pagination-btn:hover:not(:disabled):not(.active) {
  background: var(--md-surface-variant);
  border-color: var(--md-primary);
}

.pagination-btn.active {
  background: var(--md-primary);
  color: white;
  border-color: var(--md-primary);
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  animation: fadeIn 0.2s ease;
}

.modal-dialog {
  background: var(--md-surface);
  border-radius: 20px;
  box-shadow: var(--md-elevation-3);
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--md-border);
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--md-on-surface);
  margin: 0;
}

.modal-close {
  width: 36px;
  height: 36px;
  border: none;
  background: var(--md-surface-variant);
  color: var(--md-on-surface-muted);
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.modal-close:hover {
  background: var(--md-danger);
  color: white;
  transform: rotate(90deg);
}

.modal-body {
  padding: 2rem;
  overflow-y: auto;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-item.full-width {
  grid-column: 1 / -1;
}

.detail-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--md-on-surface-muted);
}

.detail-value {
  font-size: 0.9375rem;
  color: var(--md-on-surface);
}

.changes-json {
  background: var(--md-surface-variant);
  padding: 1rem;
  border-radius: 10px;
  font-family: 'Courier New', monospace;
  font-size: 0.8125rem;
  color: var(--md-on-surface);
  overflow-x: auto;
  margin: 0;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.rotating {
  animation: rotate 1s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .hide-tablet {
    display: none !important;
  }
}

@media (max-width: 768px) {
  .audit-logs-page {
    padding: 1rem;
  }

  .kpi-grid {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
  }

  .kpi-card {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .kpi-value {
    font-size: 1.75rem;
  }

  .filters-bar {
    padding: 1rem;
  }

  .filter-group {
    width: 100%;
  }

  .hide-mobile {
    display: none !important;
  }

  .logs-table th,
  .logs-table td {
    padding: 0.75rem 1rem;
  }

  .btn-text {
    display: none;
  }

  .modal-dialog {
    max-height: 95vh;
  }

  .modal-body {
    padding: 1rem;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .date-range-inputs {
    flex-direction: column;
    align-items: stretch;
  }

  .date-separator {
    text-align: center;
  }

  .pagination-container {
    flex-direction: column;
    text-align: center;
  }
}
</style>
