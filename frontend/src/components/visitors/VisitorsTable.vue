<template>
  <MaterialCard class="border-0 shadow-sm overflow-hidden">
    <template #header>
      <div class="header-container p-2">
        <div class="header-content">
          <div class="icon-stack bg-success-subtle text-success">
            <i class="bi bi-clock-history"></i>
          </div>
          <div class="title-group">
            <h3 class="md-title-large mb-0">Recent Activity</h3>
            <p class="text-muted x-small mb-0">Manage and track visitor engagement</p>
          </div>
        </div>

        <div class="action-group">
          <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input :value="searchQuery" @input="$emit('update:searchQuery', $event.target.value)" type="text"
              class="search-input" placeholder="Search visitors..." />
          </div>

          <div class="date-filter">
            <div class="date-input-wrap">
              <i class="bi bi-calendar2-week date-icon"></i>
              <input type="date" class="date-input" :value="dateFrom"
                @change="$emit('update:dateFrom', $event.target.value)" />
            </div>
            <span class="date-sep">—</span>
            <div class="date-input-wrap">
              <i class="bi bi-calendar2-event date-icon"></i>
              <input type="date" class="date-input" :value="dateTo"
                @change="$emit('update:dateTo', $event.target.value)" />
            </div>
            <button class="btn-apply" @click="$emit('change-page', 1)">Apply</button>
          </div>
        </div>
      </div>
    </template>

    <div class="visitors-container">
      <div v-if="isLoading" class="p-4">
        <div v-for="i in 5" :key="i" class="skeleton-row">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-text long"></div>
          <div class="skeleton-text short"></div>
          <div class="skeleton-button"></div>
        </div>
      </div>

      <div v-else-if="visitors.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="bi bi-person-exclamation"></i>
        </div>
        <h5 class="fw-bold">No visitors found</h5>
        <p class="text-muted">Adjust your search or add a new registration.</p>
      </div>

      <div v-else class="table-responsive">
        <CTable hover borderless align="middle" class="mb-0 custom-table">
          <CTableHead>
            <CTableRow class="border-bottom">
              <CTableHeaderCell class="table-head-label ps-4">Visitor Info</CTableHeaderCell>
              <CTableHeaderCell class="table-head-label">Details</CTableHeaderCell>
              <CTableHeaderCell class="table-head-label">Service</CTableHeaderCell>
              <CTableHeaderCell class="table-head-label">Category</CTableHeaderCell>
              <CTableHeaderCell class="table-head-label">Visit Date</CTableHeaderCell>
              <CTableHeaderCell class="table-head-label text-end pe-4">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="v in visitors" :key="v.id" class="visitor-row">
              <CTableDataCell class="ps-4">
                <div class="d-flex align-items-center gap-3">
                  <CAvatar :color="getCategoryColor(v.category)" size="md" class="avatar-custom">
                    {{ getInitials(v.name) }}
                  </CAvatar>
                  <div>
                    <div class="visitor-name">{{ v.name }}</div>
                    <div class="visitor-subtext">
                      <i class="bi bi-telephone me-1"></i>{{ v.phone }}
                    </div>
                  </div>
                </div>
              </CTableDataCell>

              <CTableDataCell>
                <div class="text-dark small fw-medium">{{ v.occupation || 'Not Specified' }}</div>
              </CTableDataCell>

              <CTableDataCell>
                <div class="text-dark small fw-medium">{{ v.service_type || 'Sunday' }}</div>
                <div class="text-muted x-small text-uppercase ls-1">Service</div>
              </CTableDataCell>

              <CTableDataCell>
                <span :class="getBadgeClasses(v.category)">
                  <i :class="getCategoryIcon(v.category)" class="me-1"></i>
                  {{ v.category === 'Wants to be a Member' ? 'Member Prospect' : v.category }}
                </span>
              </CTableDataCell>

              <CTableDataCell>
                <div class="text-dark small fw-bold">{{ formatDate(v.first_visit_date || v.date) }}</div>
                <div class="text-muted x-small">Date Joined</div>
              </CTableDataCell>

              <CTableDataCell class="text-end pe-4">
                <div class="action-btns">
                  <button class="action-btn edit" @click.stop="$emit('edit', v)" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="action-btn convert" @click.stop="$emit('convert', v)" title="Convert">
                    <i class="bi bi-person-check"></i>
                  </button>
                  <button class="action-btn delete" @click.stop="$emit('delete', v)" title="Delete">
                    <i class="bi bi-trash3"></i>
                  </button>
                </div>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
      </div>

      <div class="pagination-footer border-top">
        <div class="pagination-meta">
          Showing <strong>{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</strong>
          to <strong>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</strong>
          of <strong>{{ pagination.total }}</strong>
        </div>

        <div class="pagination-controls">
          <div class="per-page">
            <label class="per-label">Per page</label>
            <select class="per-select" :value="pagination.per_page"
              @change="$emit('change-per-page', Number($event.target.value))">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

          <CPagination class="mb-0 custom-pagination">
            <CPaginationItem :disabled="pagination.current_page === 1" @click="$emit('change-page', 1)">
              <i class="bi bi-chevron-bar-left"></i>
            </CPaginationItem>

            <CPaginationItem :disabled="pagination.current_page === 1"
              @click="$emit('change-page', pagination.current_page - 1)">
              <i class="bi bi-chevron-left"></i>
            </CPaginationItem>

            <CPaginationItem v-for="page in displayPages" :key="page" :active="page === pagination.current_page"
              @click="page !== '...' && $emit('change-page', page)">
              {{ page }}
            </CPaginationItem>

            <CPaginationItem :disabled="pagination.current_page === pagination.last_page"
              @click="$emit('change-page', pagination.current_page + 1)">
              <i class="bi bi-chevron-right"></i>
            </CPaginationItem>

            <CPaginationItem :disabled="pagination.current_page === pagination.last_page"
              @click="$emit('change-page', pagination.last_page)">
              <i class="bi bi-chevron-bar-right"></i>
            </CPaginationItem>
          </CPagination>
        </div>
      </div>
    </div>
  </MaterialCard>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import {
  CTable, CTableHead, CTableRow, CTableHeaderCell,
  CTableBody, CTableDataCell, CAvatar, CPagination, CPaginationItem
} from '@coreui/vue';
import { MaterialCard } from '../../components/material';

const props = defineProps({
  visitors: { type: Array, default: () => [] },
  isLoading: { type: Boolean, default: false },
  pagination: { type: Object, required: true },
  displayPages: { type: Array, default: () => [] },
  searchQuery: { type: String, default: '' },
  dateFrom: { type: String, default: '' },
  dateTo: { type: String, default: '' },
});

const emit = defineEmits([
  'update:searchQuery',
  'edit',
  'delete',
  'convert',
  'change-page',
  'update:dateFrom',
  'update:dateTo',
  'change-per-page'
]);

// Helper Logic
const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
  if (!date) return '-';
  const d = new Date(date);
  return isNaN(d.getTime()) ? '-' : d.toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric'
  });
};

const getCategoryColor = (cat) => {
  if (cat === 'Partner') return 'success';
  if (cat === 'Wants to be a Member') return 'info';
  return 'primary';
};

const getCategoryIcon = (cat) => {
  if (cat === 'Partner') return 'bi bi-award-fill';
  if (cat === 'Wants to be a Member') return 'bi bi-person-plus-fill';
  return 'bi bi-person-fill';
};

const getBadgeClasses = (cat) => {
  const base = "badge rounded-pill px-3 py-2 fw-medium ";
  if (cat === 'Partner') return base + "bg-success-subtle text-success";
  if (cat === 'Wants to be a Member') return base + "bg-info-subtle text-info";
  return base + "bg-primary-subtle text-primary";
};
</script>

<style scoped>
/* Layout */
.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Icon Stack */
.icon-stack {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  font-size: 1.5rem;
}

/* Search Box */
.search-wrapper {
  position: relative;
  min-width: 300px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  background-color: #f8f9fa;
  border: 1px solid transparent;
  border-radius: 10px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.search-input:focus {
  background-color: #fff;
  border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
  outline: none;
}

/* Table Styling */
.table-head-label {
  background-color: transparent !important;
  font-size: 0.75rem;
  letter-spacing: 0.05rem;
  font-weight: 700;
  color: #6c757d;
  text-transform: uppercase;
  padding-bottom: 1rem;
}

.visitor-row {
  transition: all 0.2s ease;
}

.visitor-row:hover {
  background-color: rgba(0, 0, 0, 0.02) !important;
}

.visitor-name {
  font-weight: 600;
  color: #2d3436;
}

.visitor-subtext {
  font-size: 0.8rem;
  color: #636e72;
}

.ls-1 {
  letter-spacing: 0.5px;
}

/* Action Buttons */
.action-btns {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.action-btn {
  border: none;
  background: #f1f3f5;
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-btn.edit:hover {
  background: #e7f1ff;
  color: #0d6efd;
}

.action-btn.convert:hover {
  background: #e6fcf5;
  color: #087f5b;
}

.action-btn.delete:hover {
  background: #fff5f5;
  color: #fa5252;
}

/* Pagination Footer */
.pagination-footer {
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-meta {
  font-size: 0.85rem;
  color: #6c757d;
}

/* Skeletons */
.skeleton-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.skeleton-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #eee;
}

.skeleton-text {
  height: 12px;
  background: #eee;
  border-radius: 4px;
}

.skeleton-text.long {
  width: 40%;
}

.skeleton-text.short {
  width: 15%;
}

.skeleton-button {
  margin-left: auto;
  width: 80px;
  height: 30px;
  background: #eee;
  border-radius: 4px;
}

/* Empty State */
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  color: #dee2e6;
  margin-bottom: 1rem;
}

/* Pagination */
.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.per-label {
  font-size: 0.85rem;
  color: #6c757d;
  white-space: nowrap;
}

.per-select {
  padding: 6px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.85rem;
  background: white;
  color: #475569;
  cursor: pointer;
}

.custom-pagination {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 4px;
}

.custom-pagination :deep(ul) {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 4px;
}

.custom-pagination :deep(li) {
  list-style: none;
}

.custom-pagination :deep(.page-item) {
  margin: 0;
}

.custom-pagination :deep(.page-link) {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
  height: 36px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

.custom-pagination :deep(.page-link:hover) {
  background-color: #f8fafc;
  border-color: #6366f1;
  color: #6366f1;
}

.custom-pagination :deep(.page-item.active .page-link),
.custom-pagination :deep(li.active .page-link) {
  background-color: #6366f1;
  border-color: #6366f1;
  color: white;
}

.custom-pagination :deep(.page-item.disabled .page-link),
.custom-pagination :deep(li.disabled .page-link) {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    align-items: stretch;
  }

  .search-wrapper {
    min-width: 100%;
  }

  .pagination-footer {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
}
</style>