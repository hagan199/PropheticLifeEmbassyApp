<template>
  <div class="page-wrap">
    <PageHeader title="Partnership Contributions" subtitle="Track tithes, offerings, and partnership contributions">
      <template #actions>
        <div class="d-flex gap-2 flex-wrap">
          <CButton color="primary" variant="outline" size="sm" @click="exportReport">
            <i class="bi bi-download me-2"></i> Export
          </CButton>
          <CButton color="success" @click="openAddContribution">
            <i class="bi bi-plus-circle me-2"></i> Record Contribution
          </CButton>
        </div>
      </template>
    </PageHeader>

    <!-- Quick Filter Chips -->
    <div class="filter-chips mb-4 d-flex gap-2 flex-wrap align-items-center">
      <span class="text-muted small fw-semibold me-1">Filter:</span>
      <button class="chip-btn" :class="{ active: typeFilter === 'tithe' }"
        @click="typeFilter = typeFilter === 'tithe' ? '' : 'tithe'">
        <i class="bi bi-bookmark me-1"></i> Tithes
      </button>
      <button class="chip-btn" :class="{ active: typeFilter === 'offering' }"
        @click="typeFilter = typeFilter === 'offering' ? '' : 'offering'">
        <i class="bi bi-heart me-1"></i> Offerings
      </button>
      <button class="chip-btn" :class="{ active: typeFilter === 'special_seed' }"
        @click="typeFilter = typeFilter === 'special_seed' ? '' : 'special_seed'">
        <i class="bi bi-star me-1"></i> Special Seeds
      </button>
      <button v-if="typeFilter || paymentFilter || search" class="chip-btn chip-clear"
        @click="typeFilter = ''; paymentFilter = ''; search = ''">
        <i class="bi bi-x-lg me-1"></i> Clear All
      </button>
    </div>

    <!-- Summary Cards -->
    <ContributionStats :today-total="todayTotal" :today-count="todayCount" :week-total="weekTotal"
      :week-count="weekCount" :month-total="monthTotal" :month-count="monthCount" :year-total="yearTotal"
      :year-count="yearCount" />

    <!-- Breakdown Charts -->
    <ContributionBreakdown v-model:selected-month="selectedMonth" :months="months" :type-breakdown="typeBreakdown"
      :payment-method-stats="paymentMethodStats" :month-total="monthTotal" :chart-data="chartData"
      :chart-options="chartOptions" />

    <!-- Contributions Table -->
    <ContributionTable v-model:search="search" v-model:type-filter="typeFilter" v-model:payment-filter="paymentFilter"
      :contributions="contributions" @view-receipt="viewReceipt" @edit="editContribution" @delete="confirmDelete" />

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <CModal v-model:visible="showDeleteModal" alignment="center">
        <div class="p-4 text-center">
          <div class="delete-icon mx-auto mb-3">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <h5 class="fw-bold mb-2">Delete Contribution?</h5>
          <p class="text-muted small mb-1">
            <strong>{{ deletingContribution?.memberName || 'Anonymous' }}</strong> —
            GH₵{{ formatMoney(deletingContribution?.amount || 0) }}
          </p>
          <p class="text-muted small mb-4">This action cannot be undone.</p>
          <div class="d-flex gap-2 justify-content-center">
            <CButton color="secondary" variant="ghost" @click="showDeleteModal = false">Cancel</CButton>
            <CButton color="danger" @click="deleteContribution">
              <i class="bi bi-trash me-1"></i> Delete
            </CButton>
          </div>
        </div>
      </CModal>
    </Teleport>

    <!-- Record/Edit Contribution Modal (component) -->
    <ContributionFormModal v-model:visible="showContributionModal" :editingId="editingId" :initialForm="formData"
      :members="members" :loadingMembers="loadingMembers" :saving="saving" @save="saveContribution"
      @add-partner="showAddPartnerModal = true" />

    <ContributionReceiptModal v-model:visible="showReceiptModal" :receipt="selectedReceipt" @print="printReceipt" />

    <AddPartnerModal v-model:visible="showAddPartnerModal" :saving="savingPartner" @save="saveNewPartner" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import { CButton, CModal } from '@coreui/vue';
import PageHeader from '../components/shared/PageHeader.vue';
import ContributionStats from '../components/contributions/ContributionStats.vue';


import ContributionBreakdown from '../components/contributions/ContributionBreakdown.vue';
import ContributionTable from '../components/contributions/ContributionTable.vue';
import ContributionFormModal from '../components/contributions/ContributionFormModal.vue';
import ContributionReceiptModal from '../components/contributions/ContributionReceiptModal.vue';
import AddPartnerModal from '../components/contributions/AddPartnerModal.vue';
import { exportToExcel } from '../utils/export.js';
import { usersApi } from '../api/users';
import { useToast } from '../composables/useToast';

// Types
interface Member {
  id: number;
  name: string;
  phone: string;
}

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

interface FormData {
  memberId: string;
  type: string;
  amount: string;
  paymentMethod: string;
  date: string;
  reference: string;
  mobileNumber: string;
  notes: string;
}

const toast = useToast();
const members = ref<Member[]>([]);
const loadingMembers = ref(false);

async function fetchMembers(): Promise<void> {
  loadingMembers.value = true;
  try {
    const response = await usersApi.getAll({ is_active: true });
    if (response.data.success) {
      members.value = response.data.data.map((u: any) => ({
        id: u.id,
        name: u.name,
        phone: u.phone || '',
      }));
    }
  } catch (error) {
    console.error('Failed to fetch members:', error);
  } finally {
    loadingMembers.value = false;
  }
}

onMounted(fetchMembers);

// Contributions data
const contributions = ref<Contribution[]>([
  { id: 1, date: '2026-01-26', memberId: 1, memberName: 'Kwame Asante', memberPhone: '0241234567', type: 'tithe', amount: 500, paymentMethod: 'momo', reference: 'TXN123456', recordedBy: 'Finance User' },
  { id: 2, date: '2026-01-26', memberId: 2, memberName: 'Ama Mensah', memberPhone: '0201234567', type: 'offering', amount: 100, paymentMethod: 'cash', reference: '', recordedBy: 'Finance User' },
  { id: 3, date: '2026-01-26', memberId: null, memberName: null, memberPhone: null, type: 'offering', amount: 50, paymentMethod: 'cash', reference: '', recordedBy: 'Finance User' },
  { id: 4, date: '2026-01-25', memberId: 3, memberName: 'Kofi Boateng', memberPhone: '0271234567', type: 'special_seed', amount: 1000, paymentMethod: 'bank', reference: 'BNK789012', recordedBy: 'Finance User' },
  { id: 5, date: '2026-01-19', memberId: 1, memberName: 'Kwame Asante', memberPhone: '0241234567', type: 'tithe', amount: 500, paymentMethod: 'momo', reference: 'TXN654321', recordedBy: 'Finance User' },
  { id: 6, date: '2026-01-12', memberId: 2, memberName: 'Ama Mensah', memberPhone: '0201234567', type: 'building_fund', amount: 200, paymentMethod: 'momo', reference: 'TXN111222', recordedBy: 'Finance User' },
]);

// Filter state
const search = ref('');
const typeFilter = ref('');
const paymentFilter = ref('');

// Modal state
const showContributionModal = ref(false);
const showReceiptModal = ref(false);
const showMemberSearch = ref(false);
const showAddPartnerModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref<number | null>(null);
const saving = ref(false);
const savingPartner = ref(false);
const selectedReceipt = ref<Contribution | null>(null);
const deletingContribution = ref<Contribution | null>(null);
const selectedMonth = ref('2026-01');

const months = [
  { value: '2026-01', label: 'January 2026' },
  { value: '2025-12', label: 'December 2025' },
  { value: '2025-11', label: 'November 2025' },
];

// Form data (passed to modal)
const defaultForm: FormData = {
  memberId: '', type: '', amount: '', paymentMethod: '',
  date: new Date().toISOString().split('T')[0],
  reference: '', mobileNumber: '', notes: '',
};
const formData = reactive<FormData>({ ...defaultForm });

// Computed Stats
const todayContributions = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return contributions.value.filter(c => c.date === today);
});
const todayTotal = computed(() => todayContributions.value.reduce((s, c) => s + c.amount, 0));
const todayCount = computed(() => todayContributions.value.length);

const weekContributions = computed(() => {
  const today = new Date();
  const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
  return contributions.value.filter(c => new Date(c.date) >= weekAgo);
});
const weekTotal = computed(() => weekContributions.value.reduce((s, c) => s + c.amount, 0));
const weekCount = computed(() => weekContributions.value.length);

const monthContributions = computed(() => {
  const thisMonth = new Date().toISOString().slice(0, 7);
  return contributions.value.filter(c => c.date.startsWith(thisMonth));
});
const monthTotal = computed(() => monthContributions.value.reduce((s, c) => s + c.amount, 0));
const monthCount = computed(() => monthContributions.value.length);

const yearContributions = computed(() => {
  const thisYear = new Date().getFullYear().toString();
  return contributions.value.filter(c => c.date.startsWith(thisYear));
});
const yearTotal = computed(() => yearContributions.value.reduce((s, c) => s + c.amount, 0));
const yearCount = computed(() => yearContributions.value.length);

const typeBreakdown = computed(() => ({
  tithe: monthContributions.value.filter(c => c.type === 'tithe').reduce((s, c) => s + c.amount, 0),
  offering: monthContributions.value.filter(c => c.type === 'offering').reduce((s, c) => s + c.amount, 0),
  specialSeed: monthContributions.value.filter(c => c.type === 'special_seed').reduce((s, c) => s + c.amount, 0),
  buildingFund: monthContributions.value.filter(c => c.type === 'building_fund').reduce((s, c) => s + c.amount, 0),
  missions: monthContributions.value.filter(c => c.type === 'missions').reduce((s, c) => s + c.amount, 0),
  welfare: monthContributions.value.filter(c => c.type === 'welfare').reduce((s, c) => s + c.amount, 0),
}));

const chartData = computed(() => ({
  labels: ['Tithe', 'Offering', 'Special Seed', 'Building Fund', 'Missions', 'Welfare'],
  datasets: [{
    label: 'Amount (GH₵)',
    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#6f42c1', '#dc3545'],
    data: [
      typeBreakdown.value.tithe, typeBreakdown.value.offering, typeBreakdown.value.specialSeed,
      typeBreakdown.value.buildingFund, typeBreakdown.value.missions, typeBreakdown.value.welfare,
    ],
  }],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { callback: (value: number) => 'GH₵' + value.toLocaleString() },
    },
  },
};

const paymentMethodStats = computed(() => [
  { method: 'Mobile Money', color: 'warning', icon: 'bi bi-phone', amount: monthContributions.value.filter(c => c.paymentMethod === 'momo').reduce((s, c) => s + c.amount, 0), count: monthContributions.value.filter(c => c.paymentMethod === 'momo').length },
  { method: 'Cash', color: 'success', icon: 'bi bi-cash', amount: monthContributions.value.filter(c => c.paymentMethod === 'cash').reduce((s, c) => s + c.amount, 0), count: monthContributions.value.filter(c => c.paymentMethod === 'cash').length },
  { method: 'Bank Transfer', color: 'primary', icon: 'bi bi-bank', amount: monthContributions.value.filter(c => c.paymentMethod === 'bank').reduce((s, c) => s + c.amount, 0), count: monthContributions.value.filter(c => c.paymentMethod === 'bank').length },
]);

// Methods
function openAddContribution() {
  editingId.value = null;
  Object.assign(formData, { ...defaultForm, date: new Date().toISOString().split('T')[0] });
  showContributionModal.value = true;
}

function editContribution(c: Contribution) {
  editingId.value = c.id;
  Object.assign(formData, {
    memberId: c.memberId ? String(c.memberId) : '',
    type: c.type,
    amount: String(c.amount),
    paymentMethod: c.paymentMethod,
    date: c.date,
    reference: c.reference || '',
    mobileNumber: '',
    notes: c.notes || '',
  });
  showContributionModal.value = true;
}

function saveContribution(form: FormData) {
  saving.value = true;
  try {
    const member = form.memberId ? members.value.find(m => m.id === parseInt(form.memberId)) : null;
    if (editingId.value) {
      const idx = contributions.value.findIndex(c => c.id === editingId.value);
      if (idx !== -1) {
        contributions.value[idx] = {
          id: contributions.value[idx].id,
          date: form.date,
          memberId: form.memberId ? parseInt(form.memberId) : null,
          memberName: member?.name || null,
          memberPhone: member?.phone || null,
          type: form.type,
          amount: parseFloat(form.amount),
          paymentMethod: form.paymentMethod,
          reference: form.reference,
          recordedBy: contributions.value[idx].recordedBy,
          notes: form.notes,
        };
      }
      toast.success('Contribution updated successfully');
    } else {
      const newId = contributions.value.length ? Math.max(...contributions.value.map(c => c.id)) + 1 : 1;
      contributions.value.push({
        id: newId,
        memberId: form.memberId ? parseInt(form.memberId) : null,
        memberName: member?.name || null,
        memberPhone: member?.phone || null,
        type: form.type,
        amount: parseFloat(form.amount),
        paymentMethod: form.paymentMethod,
        date: form.date,
        reference: form.reference,
        recordedBy: 'Finance User',
        notes: form.notes,
      });
      toast.success('Contribution recorded successfully');
    }
    showContributionModal.value = false;
  } catch (error) {
    toast.error('Failed to save contribution');
  } finally {
    saving.value = false;
  }
}

function viewReceipt(c: Contribution) {
  selectedReceipt.value = c;
  showReceiptModal.value = true;
}

function printReceipt() {
  window.print();
}

async function saveNewPartner(partner: { name: string; phone: string; email: string }) {
  savingPartner.value = true;
  try {
    const response = await usersApi.create({
      name: partner.name,
      phone: partner.phone,
      email: partner.email || undefined,
      role: 'member',
    });
    if (response.data.success) {
      toast.success('Partner added successfully');
      members.value.push({ id: response.data.data.id, name: partner.name, phone: partner.phone });
      Object.assign(formData, { ...formData, memberId: String(response.data.data.id) });
      showAddPartnerModal.value = false;
    }
  } catch (error) {
    toast.error((error as any).response?.data?.message || 'Failed to add partner');
  } finally {
    savingPartner.value = false;
  }
}

function formatMoney(amount: number): string {
  return (amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function confirmDelete(c: Contribution) {
  deletingContribution.value = c;
  showDeleteModal.value = true;
}

function deleteContribution() {
  if (deletingContribution.value) {
    contributions.value = contributions.value.filter(c => c.id !== deletingContribution.value!.id);
    toast.success('Contribution deleted');
    deletingContribution.value = null;
    showDeleteModal.value = false;
  }
}

function exportReport() {
  const columns = [
    { key: 'date', label: 'Date' },
    { key: 'memberName', label: 'Contributor' },
    { key: 'memberPhone', label: 'Phone' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount (GH₵)' },
    { key: 'paymentMethod', label: 'Payment Method' },
    { key: 'reference', label: 'Reference' },
    { key: 'recordedBy', label: 'Recorded By' },
  ];

  const typeLabels: Record<string, string> = { tithe: 'Tithe', offering: 'Offering', special_seed: 'Special Seed', building_fund: 'Building Fund', missions: 'Missions', welfare: 'Welfare' };
  const paymentLabels: Record<string, string> = { cash: 'Cash', momo: 'Mobile Money', bank: 'Bank Transfer', cheque: 'Cheque' };

  const exportData = contributions.value.map(c => ({
    ...c,
    memberName: c.memberName || 'Anonymous',
    type: typeLabels[c.type] || c.type,
    paymentMethod: paymentLabels[c.paymentMethod] || c.paymentMethod,
  }));

  exportToExcel(exportData, columns, 'contributions_report');
  toast.success(`Exported ${exportData.length} contributions to Excel`);
}
</script>

<style scoped>
.page-wrap {
  padding: 24px;
}

.filter-chips {
  padding: 0.5rem 0;
}

.chip-btn {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.85rem;
  border-radius: 20px;
  border: 1.5px solid #dee2e6;
  background: #fff;
  font-size: 0.82rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chip-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: #f0f0ff;
}

.chip-btn.active {
  border-color: #6366f1;
  background: #6366f1;
  color: #fff;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
}

.chip-clear {
  border-color: #dc3545;
  color: #dc3545;
}

.chip-clear:hover {
  background: #dc3545;
  color: #fff;
  border-color: #dc3545;
}

@media (max-width: 768px) {
  .page-wrap {
    padding: 10px;
  }
}
</style>
