<template>
  <MaterialCard title="Contribution History">
    <template #actions>
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <div class="search-input-wrap">
          <i class="bi bi-search search-icon"></i>
          <input v-model="localSearch" type="text" class="search-input"
            placeholder="Search contributor or reference..." />
          <button v-if="localSearch" class="search-clear" @click="localSearch = ''">
            <i class="bi bi-x"></i>
          </button>
        </div>
        <select v-model="localTypeFilter" class="filter-select">
          <option value="">All Types</option>
          <option value="tithe">Tithe</option>
          <option value="offering">Offering</option>
          <option value="special_seed">Special Seed</option>
          <option value="building_fund">Building Fund</option>
          <option value="missions">Missions</option>
          <option value="welfare">Welfare</option>
        </select>
        <select v-model="localPaymentFilter" class="filter-select">
          <option value="">All Methods</option>
          <option value="cash">Cash</option>
          <option value="momo">Mobile Money</option>
          <option value="bank">Bank Transfer</option>
          <option value="cheque">Cheque</option>
        </select>
      </div>
    </template>
    <div class="p-0">
      <!-- Desktop Table -->
      <CTable hover responsive align="middle" class="contributions-table d-none d-md-table mb-0">
        <CTableHead>
          <CTableRow>
            <CTableHeaderCell>Date</CTableHeaderCell>
            <CTableHeaderCell>Contributor</CTableHeaderCell>
            <CTableHeaderCell>Type</CTableHeaderCell>
            <CTableHeaderCell>Amount</CTableHeaderCell>
            <CTableHeaderCell>Method</CTableHeaderCell>
            <CTableHeaderCell>Reference</CTableHeaderCell>
            <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
          </CTableRow>
        </CTableHead>
        <CTableBody>
          <CTableRow v-for="c in paginatedItems" :key="c.id" class="table-row-hover">
            <CTableDataCell>
              <span class="text-muted small">{{ formatDate(c.date) }}</span>
            </CTableDataCell>
            <CTableDataCell>
              <div class="d-flex align-items-center">
                <CAvatar :color="c.memberName ? 'primary' : 'secondary'" text-color="white" size="sm" class="me-2">
                  {{ (c.memberName || 'A').charAt(0) }}
                </CAvatar>
                <div>
                  <div class="fw-semibold small">{{ c.memberName || 'Anonymous' }}</div>
                  <div v-if="c.memberPhone" class="text-muted x-small">{{ c.memberPhone }}</div>
                </div>
              </div>
            </CTableDataCell>
            <CTableDataCell>
              <CBadge :color="typeColor(c.type)" shape="rounded-pill" class="px-2 py-1">{{ typeLabel(c.type) }}</CBadge>
            </CTableDataCell>
            <CTableDataCell>
              <span class="fw-bold">GH₵ {{ formatMoney(c.amount) }}</span>
            </CTableDataCell>
            <CTableDataCell>
              <span class="d-inline-flex align-items-center gap-1 text-muted small">
                <i :class="paymentIcon(c.paymentMethod)"></i>
                {{ paymentLabel(c.paymentMethod) }}
              </span>
            </CTableDataCell>
            <CTableDataCell>
              <code v-if="c.reference" class="small">{{ c.reference }}</code>
              <span v-else class="text-muted">—</span>
            </CTableDataCell>
            <CTableDataCell class="text-end">
              <div class="d-flex gap-1 justify-content-end">
                <CButton color="primary" variant="ghost" size="sm" title="View Receipt"
                  @click="$emit('view-receipt', c)">
                  <i class="bi bi-receipt"></i>
                </CButton>
                <CButton color="info" variant="ghost" size="sm" title="Edit" @click="$emit('edit', c)">
                  <i class="bi bi-pencil"></i>
                </CButton>
                <CButton color="danger" variant="ghost" size="sm" title="Delete" @click="$emit('delete', c)">
                  <i class="bi bi-trash"></i>
                </CButton>
              </div>
            </CTableDataCell>
          </CTableRow>
        </CTableBody>
      </CTable>

      <!-- Mobile Cards -->
      <div class="d-md-none px-2">
        <div v-for="c in paginatedItems" :key="c.id"
          class="contribution-card mb-3 p-3 bg-white rounded-3 shadow-sm border">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center">
              <CAvatar :color="c.memberName ? 'primary' : 'secondary'" text-color="white" size="sm" class="me-2">
                {{ (c.memberName || 'A').charAt(0) }}
              </CAvatar>
              <div>
                <div class="fw-semibold small">{{ c.memberName || 'Anonymous' }}</div>
                <div class="text-muted x-small">{{ formatDate(c.date) }}</div>
              </div>
            </div>
            <div class="text-end">
              <div class="fw-bold text-primary">GH₵{{ formatMoney(c.amount) }}</div>
              <CBadge :color="typeColor(c.type)" shape="rounded-pill" class="mt-1">{{ typeLabel(c.type) }}</CBadge>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 border-top">
            <span class="d-inline-flex align-items-center gap-1 text-muted small">
              <i :class="paymentIcon(c.paymentMethod)"></i>
              {{ paymentLabel(c.paymentMethod) }}
              <code v-if="c.reference" class="ms-1 small">{{ c.reference }}</code>
            </span>
            <div class="d-flex gap-1">
              <CButton color="primary" variant="ghost" size="sm" @click="$emit('view-receipt', c)">
                <i class="bi bi-receipt"></i>
              </CButton>
              <CButton color="info" variant="ghost" size="sm" @click="$emit('edit', c)">
                <i class="bi bi-pencil"></i>
              </CButton>
              <CButton color="danger" variant="ghost" size="sm" @click="$emit('delete', c)">
                <i class="bi bi-trash"></i>
              </CButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!filteredItems.length" class="text-center py-5">
        <div class="empty-icon mx-auto mb-3">
          <i class="bi bi-journal-x"></i>
        </div>
        <h6 class="fw-bold mb-1">No contributions found</h6>
        <p class="text-muted small mb-0">Try adjusting your search or filters</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
      <div class="text-muted small">
        Showing {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredItems.length) }}
        of {{ filteredItems.length }}
      </div>
      <div class="d-flex gap-1">
        <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button v-for="page in visiblePages" :key="page" class="page-btn" :class="{ active: page === currentPage }"
          @click="currentPage = page">
          {{ page }}
        </button>
        <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
  </MaterialCard>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell,
  CButton, CBadge, CAvatar,
} from '@coreui/vue';
import MaterialCard from '../material/MaterialCard.vue';

interface Contribution {
  id: number;
  date: string;
  memberId: number | null;
  memberName: string | null;
  memberPhone: string | null;
  type: string;
  amount: number;
  paymentMethod: string;
  reference: string;
  recordedBy: string;
  notes?: string;
}

const props = defineProps<{
  contributions: Contribution[];
  search: string;
  typeFilter: string;
  paymentFilter: string;
}>();

const emit = defineEmits<{
  'update:search': [value: string];
  'update:typeFilter': [value: string];
  'update:paymentFilter': [value: string];
  'view-receipt': [contribution: Contribution];
  'edit': [contribution: Contribution];
  'delete': [contribution: Contribution];
}>();

const localSearch = ref(props.search);
const localTypeFilter = ref(props.typeFilter);
const localPaymentFilter = ref(props.paymentFilter);

watch(() => props.search, (v) => { localSearch.value = v; });
watch(localSearch, (v) => { emit('update:search', v); });
watch(() => props.typeFilter, (v) => { localTypeFilter.value = v; });
watch(localTypeFilter, (v) => { emit('update:typeFilter', v); });
watch(() => props.paymentFilter, (v) => { localPaymentFilter.value = v; });
watch(localPaymentFilter, (v) => { emit('update:paymentFilter', v); });

const perPage = 10;
const currentPage = ref(1);

const filteredItems = computed(() => {
  let result = props.contributions;
  if (localSearch.value) {
    const s = localSearch.value.toLowerCase();
    result = result.filter(c => c.memberName?.toLowerCase().includes(s) || c.reference?.toLowerCase().includes(s));
  }
  if (localTypeFilter.value) result = result.filter(c => c.type === localTypeFilter.value);
  if (localPaymentFilter.value) result = result.filter(c => c.paymentMethod === localPaymentFilter.value);
  return result.sort((a, b) => b.date.localeCompare(a.date));
});

const totalPages = computed(() => Math.ceil(filteredItems.value.length / perPage));

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredItems.value.slice(start, start + perPage);
});

const visiblePages = computed(() => {
  const pages: number[] = [];
  const total = totalPages.value;
  const current = currentPage.value;
  const maxVisible = 5;
  if (total <= maxVisible) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    let start = Math.max(1, current - Math.floor(maxVisible / 2));
    let end = Math.min(total, start + maxVisible - 1);
    if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);
    for (let i = start; i <= end; i++) pages.push(i);
  }
  return pages;
});

watch([localSearch, localTypeFilter, localPaymentFilter], () => { currentPage.value = 1; });

function formatDate(date: string): string {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatMoney(amount: number): string {
  return (amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function typeColor(type: string): string {
  const colors: Record<string, string> = { tithe: 'primary', offering: 'success', special_seed: 'warning', building_fund: 'info', missions: 'purple', welfare: 'danger' };
  return colors[type] || 'secondary';
}

function typeLabel(type: string): string {
  const labels: Record<string, string> = { tithe: 'Tithe', offering: 'Offering', special_seed: 'Special Seed', building_fund: 'Building Fund', missions: 'Missions', welfare: 'Welfare' };
  return labels[type] || type;
}

function paymentIcon(method: string): string {
  const icons: Record<string, string> = { cash: 'bi bi-cash', momo: 'bi bi-phone', bank: 'bi bi-bank', cheque: 'bi bi-file-text' };
  return icons[method] || 'bi bi-credit-card';
}

function paymentLabel(method: string): string {
  const labels: Record<string, string> = { cash: 'Cash', momo: 'Mobile Money', bank: 'Bank Transfer', cheque: 'Cheque' };
  return labels[method] || method;
}
</script>
<script lang="ts">
export default {};
</script>

<style scoped>
.search-input-wrap {
  position: relative;
  min-width: 200px;
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
  font-size: 0.85rem;
}

.search-input {
  width: 100%;
  border: 1.5px solid #e0e7ef;
  border-radius: 8px;
  padding: 0.4rem 2rem 0.4rem 2rem;
  font-size: 0.85rem;
  background: #f8fafc;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #6366f1;
  background: #fff;
}

.search-clear {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #adb5bd;
  cursor: pointer;
  padding: 2px;
}

.filter-select {
  border: 1.5px solid #e0e7ef;
  border-radius: 8px;
  padding: 0.4rem 0.75rem;
  font-size: 0.85rem;
  background: #f8fafc;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #6366f1;
}

.contributions-table :deep(thead th) {
  background: #f8fafc;
  font-weight: 600;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

.table-row-hover {
  transition: background 0.15s;
}

.table-row-hover:hover {
  background: #f0f4ff !important;
}

.contribution-card {
  transition: all 0.2s ease;
  border-left: 4px solid #6366f1 !important;
}

.contribution-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.empty-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #f0f4ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #6366f1;
}

.page-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  border: 1.5px solid #e0e7ef;
  border-radius: 8px;
  background: #fff;
  font-size: 0.82rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  transition: all 0.15s;
}

.page-btn:hover:not(:disabled) {
  border-color: #6366f1;
  color: #6366f1;
}

.page-btn.active {
  background: #6366f1;
  border-color: #6366f1;
  color: #fff;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
