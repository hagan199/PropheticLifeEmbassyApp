<template>
  <div class="page-wrap">
    <PageHeader title="Partnership Contributions" subtitle="Track tithes, offerings, and partnership contributions">
      <template #actions>
        <div class="d-flex gap-2 flex-wrap">
          <CButton color="outline-primary" size="sm" @click="exportReport">
            <i class="bi bi-download me-2"></i> Export
          </CButton>
          <CButton color="success" @click="openAddContribution">
            <i class="bi bi-plus-circle me-2"></i> Record Contribution
          </CButton>
        </div>
      </template>
    </PageHeader>

    <!-- Quick Actions Bar -->
    <div class="quick-actions bg-light rounded-3 p-3 mb-4 d-flex gap-2 flex-wrap align-items-center">
      <div class="text-muted small fw-semibold me-2">Quick Actions:</div>
      <CButton color="outline-primary" size="sm" @click="typeFilter = 'tithe'">
        <i class="bi bi-filter me-1"></i> Tithes
      </CButton>
      <CButton color="outline-success" size="sm" @click="typeFilter = 'offering'">
        <i class="bi bi-filter me-1"></i> Offerings
      </CButton>
      <CButton color="outline-warning" size="sm" @click="typeFilter = 'special_seed'">
        <i class="bi bi-filter me-1"></i> Special Seeds
      </CButton>
      <CButton color="outline-secondary" size="sm" @click="typeFilter = ''; paymentFilter = ''; search = ''">
        <i class="bi bi-x-circle me-1"></i> Clear Filters
      </CButton>
    </div>

    <!-- Summary Cards -->
    <CRow class="g-4 mb-4">
      <CCol sm="6" xl="3">
        <MaterialCard
class="stat-card border-0 shadow-sm h-100 animate__animated animate__fadeInUp"
          style="animation-delay: 0.1s">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted x-small text-uppercase mb-1 fw-bold letter-spacing-1">
                Today
              </div>
              <div class="fs-4 fw-bold text-primary">GH₵{{ formatMoney(todayTotal) }}</div>
              <div class="text-muted small">{{ todayCount }} records</div>
            </div>
            <div class="stat-icon bg-primary-container text-primary shadow-sm">
              <i class="bi bi-calendar-day"></i>
            </div>
          </div>
        </MaterialCard>
      </CCol>
      <CCol sm="6" xl="3">
        <MaterialCard
class="stat-card border-0 shadow-sm h-100 animate__animated animate__fadeInUp"
          style="animation-delay: 0.2s">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted x-small text-uppercase mb-1 fw-bold letter-spacing-1">
                This Week
              </div>
              <div class="fs-4 fw-bold text-success">GH₵{{ formatMoney(weekTotal) }}</div>
              <div class="text-muted small">{{ weekCount }} records</div>
            </div>
            <div class="stat-icon bg-success-container text-success shadow-sm">
              <i class="bi bi-calendar-week"></i>
            </div>
          </div>
        </MaterialCard>
      </CCol>
      <CCol sm="6" xl="3">
        <MaterialCard
class="stat-card border-0 shadow-sm h-100 animate__animated animate__fadeInUp"
          style="animation-delay: 0.3s">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted x-small text-uppercase mb-1 fw-bold letter-spacing-1">
                This Month
              </div>
              <div class="fs-4 fw-bold text-warning">GH₵{{ formatMoney(monthTotal) }}</div>
              <div class="text-muted small">{{ monthCount }} records</div>
            </div>
            <div class="stat-icon bg-warning-container text-warning shadow-sm">
              <i class="bi bi-calendar-month"></i>
            </div>
          </div>
        </MaterialCard>
      </CCol>
      <CCol sm="6" xl="3">
        <MaterialCard
class="stat-card border-0 shadow-sm h-100 animate__animated animate__fadeInUp"
          style="animation-delay: 0.4s">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted x-small text-uppercase mb-1 fw-bold letter-spacing-1">
                Year to Date
              </div>
              <div class="fs-4 fw-bold text-info">GH₵{{ formatMoney(yearTotal) }}</div>
              <div class="text-muted small">{{ yearCount }} records</div>
            </div>
            <div class="stat-icon bg-info-container text-info shadow-sm">
              <i class="bi bi-calendar"></i>
            </div>
          </div>
        </MaterialCard>
      </CCol>
    </CRow>

    <!-- Breakdown by Type -->
    <CRow class="g-4 mb-4">
      <CCol lg="8">
        <MaterialCard title="Monthly Breakdown">
          <template #actions>
            <select v-model="selectedMonth" class="md-input border-0 bg-light py-1 px-2 rounded-pill small">
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </template>
          <div class="chart-container" style="height: 250px">
            <CChartBar :data="chartData" :options="chartOptions" />
          </div>
          <CRow class="g-3 mt-2">
            <CCol sm="4">
              <div class="p-3 bg-surface-container rounded-4">
                <div class="text-muted x-small fw-bold">TITHES</div>
                <div class="fs-5 fw-bold text-primary">
                  GH₵{{ formatMoney(typeBreakdown.tithe) }}
                </div>
              </div>
            </CCol>
            <CCol sm="4">
              <div class="p-3 bg-surface-container rounded-4">
                <div class="text-muted x-small fw-bold">OFFERING</div>
                <div class="fs-5 fw-bold text-success">
                  GH₵{{ formatMoney(typeBreakdown.offering) }}
                </div>
              </div>
            </CCol>
            <CCol sm="4">
              <div class="p-3 bg-surface-container rounded-4">
                <div class="text-muted x-small fw-bold">SPECIAL SEED</div>
                <div class="fs-5 fw-bold text-warning">
                  GH₵{{ formatMoney(typeBreakdown.specialSeed) }}
                </div>
              </div>
            </CCol>
          </CRow>
        </MaterialCard>
      </CCol>
      <CCol lg="4">
        <MaterialCard title="Payment Methods" class="h-100">
          <div class="mt-2">
            <div
v-for="pm in paymentMethodStats" :key="pm.method"
              class="d-flex justify-content-between align-items-center mb-4 p-2 rounded-3 hover-bg-light transition-all">
              <div class="d-flex align-items-center">
                <div class="stat-icon-sm me-3" :class="'bg-' + pm.color + '-container text-' + pm.color">
                  <i :class="pm.icon"></i>
                </div>
                <div>
                  <div class="fw-bold small">{{ pm.method }}</div>
                  <div class="text-muted x-small">{{ pm.count }} transactions</div>
                </div>
              </div>
              <div class="text-end">
                <div class="fw-bold text-dark">GH₵{{ formatMoney(pm.amount) }}</div>
                <div class="md-badge bg-light text-dark x-small py-0">
                  {{ Math.round((pm.amount / monthTotal) * 100 || 0) }}%
                </div>
              </div>
            </div>
          </div>
        </MaterialCard>
      </CCol>
    </CRow>

    <!-- Contributions Table -->
    <MaterialCard title="Contribution History">
      <template #actions>
        <div class="d-flex gap-2 flex-wrap align-items-center">
          <div class="md-input-wrapper mb-0" style="min-width: 180px">
            <input v-model="search" type="text" class="md-input bg-light border-0 py-1" placeholder=" " />
            <label class="md-label-floating">Search record...</label>
          </div>
          <select v-model="typeFilter" class="md-input border-0 bg-light py-1 px-3 rounded-pill small">
            <option value="">All Types</option>
            <option value="tithe">Tithe</option>
            <option value="offering">Offering</option>
            <option value="special_seed">Special Seed</option>
            <option value="building_fund">Building Fund</option>
            <option value="missions">Missions</option>
            <option value="welfare">Welfare</option>
          </select>
          <select v-model="paymentFilter" class="md-input border-0 bg-light py-1 px-3 rounded-pill small">
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
        <CTable hover responsive align="middle" class="d-none d-md-table">
          <CTableHead color="light">
            <CTableRow>
              <CTableHeaderCell>Date</CTableHeaderCell>
              <CTableHeaderCell>Contributor</CTableHeaderCell>
              <CTableHeaderCell>Type</CTableHeaderCell>
              <CTableHeaderCell>Amount</CTableHeaderCell>
              <CTableHeaderCell>Method</CTableHeaderCell>
              <CTableHeaderCell>Reference</CTableHeaderCell>
              <CTableHeaderCell>Recorded By</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="c in paginatedContributions" :key="c.id">
              <CTableDataCell>{{ formatDate(c.date) }}</CTableDataCell>
              <CTableDataCell>
                <div class="d-flex align-items-center">
                  <CAvatar :color="c.memberName ? 'primary' : 'secondary'" text-color="white" size="sm" class="me-2">
                    {{ (c.memberName || 'A').charAt(0) }}
                  </CAvatar>
                  <div>
                    <div class="fw-semibold">{{ c.memberName || 'Anonymous' }}</div>
                    <div v-if="c.memberPhone" class="text-muted small">{{ c.memberPhone }}</div>
                  </div>
                </div>
              </CTableDataCell>
              <CTableDataCell>
                <CBadge :color="typeColor(c.type)">{{ typeLabel(c.type) }}</CBadge>
              </CTableDataCell>
              <CTableDataCell class="fw-semibold">GH₵ {{ formatMoney(c.amount) }}</CTableDataCell>
              <CTableDataCell>
                <CBadge color="light" text-color="dark">
                  <i :class="paymentIcon(c.paymentMethod)" class="me-1"></i>
                  {{ paymentLabel(c.paymentMethod) }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <span class="text-muted">{{ c.reference || '—' }}</span>
              </CTableDataCell>
              <CTableDataCell>
                <span class="text-muted small">{{ c.recordedBy }}</span>
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="light" size="sm" class="me-1" title="View Receipt" @click="viewReceipt(c)">
                  <i class="bi bi-receipt"></i>
                </CButton>
                <CButton color="light" size="sm" title="Edit" @click="editContribution(c)">
                  <i class="bi bi-pencil"></i>
                </CButton>
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>

        <!-- Mobile Cards -->
        <div class="d-md-none">
          <div
v-for="c in paginatedContributions" :key="c.id"
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
                <CBadge :color="typeColor(c.type)" class="mt-1">{{ typeLabel(c.type) }}</CBadge>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <CBadge color="light" text-color="dark" class="me-2">
                  <i :class="paymentIcon(c.paymentMethod)" class="me-1"></i>
                  {{ paymentLabel(c.paymentMethod) }}
                </CBadge>
                <span v-if="c.reference" class="text-muted small">{{ c.reference }}</span>
              </div>
              <div class="d-flex gap-1">
                <CButton color="light" size="sm" title="View Receipt" @click="viewReceipt(c)">
                  <i class="bi bi-receipt"></i>
                </CButton>
                <CButton color="light" size="sm" title="Edit" @click="editContribution(c)">
                  <i class="bi bi-pencil"></i>
                </CButton>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!filteredContributions.length" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2 text-primary opacity-50"></i>
          <div class="fw-semibold mb-1">No contributions found</div>
          <div class="small">Try adjusting your search or filters</div>
        </div>
      </div>
      <div v-if="totalPages > 1" class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">Showing {{ paginatedContributions.length }} of {{ filteredContributions.length }}
          records</div>
        <CPagination>
          <CPaginationItem :disabled="currentPage === 1" class="cursor-pointer" @click="currentPage--">
            <i class="bi bi-chevron-left"></i>
          </CPaginationItem>
          <CPaginationItem
v-for="page in visiblePages" :key="page" :active="page === currentPage"
            class="cursor-pointer" @click="currentPage = page">
            {{ page }}
          </CPaginationItem>
          <CPaginationItem :disabled="currentPage === totalPages" class="cursor-pointer" @click="currentPage++">
            <i class="bi bi-chevron-right"></i>
          </CPaginationItem>
        </CPagination>
      </div>
    </MaterialCard>

    <!-- Add/Edit Contribution Modal -->
    <Teleport to="body">
      <CModal
        :visible="showContributionModal"
        class="modal-bottom-sheet"
        size="lg"
        @close="showContributionModal = false"
      >
        <CModalHeader>
          <CModalTitle>
            <i class="bi bi-plus-circle me-2 text-primary"></i>
            {{ editingId ? 'Edit Contribution' : 'Record New Contribution' }}
          </CModalTitle>
        </CModalHeader>
        <CModalBody>
        <CForm @submit.prevent="saveContribution">
          <CRow class="g-3">
            <CCol md="12">
              <CFormLabel>Partner/Contributor</CFormLabel>
              <div class="d-flex gap-2">
                <CFormSelect
v-model="form.memberId" class="flex-fill"
                  :class="{ 'is-invalid': !form.memberId && formSubmitted }" :disabled="loadingMembers">
                  <option value="">Select a partner or leave empty for anonymous</option>
                  <option v-for="m in members" :key="m.id" :value="m.id">
                    {{ m.name }}{{ m.phone ? ' - ' + m.phone : '' }}
                  </option>
                </CFormSelect>
                <CButton color="light" title="Search" :disabled="loadingMembers" @click="showMemberSearch = true">
                  <i class="bi bi-search"></i>
                </CButton>
                <CButton
color="success" title="Add New Partner" :disabled="loadingMembers"
                  @click="showAddPartnerModal = true">
                  <i class="bi bi-person-plus"></i>
                </CButton>
              </div>
              <div v-if="loadingMembers" class="text-muted small mt-1">
                <i class="bi bi-spinner bi-spin me-1"></i> Loading partners...
              </div>
              <div v-if="!form.memberId && formSubmitted" class="invalid-feedback d-block">
                Please select a partner or leave empty for anonymous contributions.
              </div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Contribution Type <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.type" :class="{ 'is-invalid': !form.type && formSubmitted }">
                <option value="">Select type...</option>
                <option value="tithe">Tithe (10% of income)</option>
                <option value="offering">Offering (Free will)</option>
                <option value="special_seed">Special Seed</option>
                <option value="building_fund">Building Fund</option>
                <option value="missions">Missions</option>
                <option value="welfare">Welfare</option>
              </CFormSelect>
              <div v-if="!form.type && formSubmitted" class="invalid-feedback">
                Contribution type is required.
              </div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Amount (GH₵) <span class="text-danger">*</span></CFormLabel>
              <CInputGroup :class="{ 'is-invalid': (!form.amount || parseFloat(form.amount) <= 0) && formSubmitted }">
                <CInputGroupText>GH₵</CInputGroupText>
                <CFormInput v-model="form.amount" type="number" step="0.01" min="0.01" placeholder="0.00" />
              </CInputGroup>
              <div v-if="(!form.amount || parseFloat(form.amount) <= 0) && formSubmitted" class="invalid-feedback">
                Please enter a valid amount greater than 0.
              </div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Payment Method <span class="text-danger">*</span></CFormLabel>
              <CFormSelect v-model="form.paymentMethod" :class="{ 'is-invalid': !form.paymentMethod && formSubmitted }">
                <option value="">Select method...</option>
                <option value="cash">💵 Cash</option>
                <option value="momo">📱 Mobile Money</option>
                <option value="bank">🏦 Bank Transfer</option>
                <option value="cheque">📄 Cheque</option>
              </CFormSelect>
              <div v-if="!form.paymentMethod && formSubmitted" class="invalid-feedback">
                Payment method is required.
              </div>
            </CCol>
            <CCol md="6">
              <CFormLabel>Date <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="form.date" type="date" :class="{ 'is-invalid': !form.date && formSubmitted }" />
              <div v-if="!form.date && formSubmitted" class="invalid-feedback">
                Contribution date is required.
              </div>
            </CCol>
            <CCol v-if="form.paymentMethod && form.paymentMethod !== 'cash'" md="6">
              <CFormLabel>Reference Number</CFormLabel>
              <CFormInput
v-model="form.reference"
                :placeholder="form.paymentMethod === 'momo' ? 'Transaction ID' : 'Reference number'" />
            </CCol>
            <CCol v-if="form.paymentMethod === 'momo'" md="6">
              <CFormLabel>Mobile Number</CFormLabel>
              <CInputGroup>
                <CInputGroupText>+233</CInputGroupText>
                <CFormInput v-model="form.mobileNumber" placeholder="501234567" />
              </CInputGroup>
            </CCol>
            <CCol md="12">
              <CFormLabel>Notes</CFormLabel>
              <CFormTextarea v-model="form.notes" rows="2" placeholder="Additional notes (optional)" />
            </CCol>
          </CRow>
        </CForm>
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" :disabled="saving" @click="showContributionModal = false">
            Cancel
          </CButton>
          <CButton color="primary" :disabled="saving" @click="saveContribution">
            <i v-if="saving" class="bi bi-spinner bi-spin me-2"></i>
            {{ editingId ? 'Update' : 'Record' }} Contribution
          </CButton>
        </CModalFooter>
      </CModal>
    </Teleport>

    <!-- Receipt Modal -->
    <Teleport to="body">
      <CModal :visible="showReceiptModal" class="modal-bottom-sheet" size="md" @close="showReceiptModal = false">
        <CModalHeader class="border-0 pb-0">
          <CModalTitle class="text-center w-100">
            <i class="bi bi-receipt-cutoff fs-2 text-primary mb-2"></i>
            <div class="fw-bold">Contribution Receipt</div>
          </CModalTitle>
        </CModalHeader>
        <CModalBody v-if="selectedReceipt" class="receipt-body px-4">
        <!-- Church Header -->
        <div class="text-center mb-4">
          <div class="church-logo mb-2">
            <i class="bi bi-church fs-1 text-primary"></i>
          </div>
          <h5 class="mb-1 fw-bold text-primary">Prophetic Life Embassy</h5>
          <div class="text-muted small">Building Lives Through Christ</div>
        </div>

        <!-- Receipt Details -->
        <div class="receipt-details bg-light rounded-3 p-3 mb-3">
          <CRow class="g-2 small">
            <CCol xs="6">
              <div class="text-muted">Receipt No:</div>
            </CCol>
            <CCol xs="6" class="text-end fw-semibold">
              {{ selectedReceipt.reference || `RCP-${selectedReceipt.id}` }}
            </CCol>
            <CCol xs="6">
              <div class="text-muted">Date:</div>
            </CCol>
            <CCol xs="6" class="text-end">
              {{ formatDate(selectedReceipt.date) }}
            </CCol>
            <CCol xs="6">
              <div class="text-muted">Contributor:</div>
            </CCol>
            <CCol xs="6" class="text-end">
              {{ selectedReceipt.memberName || 'Anonymous' }}
            </CCol>
            <CCol xs="6">
              <div class="text-muted">Type:</div>
            </CCol>
            <CCol xs="6" class="text-end">
              <CBadge :color="typeColor(selectedReceipt.type)" class="badge-sm">
                {{ typeLabel(selectedReceipt.type) }}
              </CBadge>
            </CCol>
            <CCol xs="6">
              <div class="text-muted">Method:</div>
            </CCol>
            <CCol xs="6" class="text-end">
              <CBadge color="light" text-color="dark" class="badge-sm">
                <i :class="paymentIcon(selectedReceipt.paymentMethod)" class="me-1"></i>
                {{ paymentLabel(selectedReceipt.paymentMethod) }}
              </CBadge>
            </CCol>
          </CRow>
        </div>

        <!-- Amount Section -->
        <div class="amount-section text-center bg-primary-container rounded-3 p-4 mb-3">
          <div class="text-muted small mb-1">Contribution Amount</div>
          <div class="fs-1 fw-bold text-primary">GH₵ {{ formatMoney(selectedReceipt.amount) }}</div>
          <div class="text-muted x-small mt-1">{{ selectedReceipt.notes || 'No additional notes' }}</div>
        </div>

        <!-- Footer Message -->
        <div class="text-center text-muted small mb-3">
          <div class="mb-2">
            <i class="bi bi-heart-fill text-danger me-1"></i>
            Thank you for your generous contribution!
          </div>
          <div class="fst-italic">
            "Give, and it will be given to you. A good measure, pressed down, shaken together and running over, will be
            poured into your lap." - Luke 6:38
          </div>
        </div>

        <!-- Recorded By -->
        <div class="text-center text-muted x-small border-top pt-2">
          Recorded by: {{ selectedReceipt.recordedBy }} | {{ new Date().toLocaleDateString() }}
        </div>
        </CModalBody>
        <CModalFooter class="border-0 pt-0">
          <div class="d-flex gap-2 w-100">
            <CButton color="outline-primary" class="flex-fill" @click="printReceipt">
              <i class="bi bi-printer me-1"></i> Print
            </CButton>
            <CButton color="primary" class="flex-fill" @click="showReceiptModal = false">
              <i class="bi bi-check-circle me-1"></i> Done
            </CButton>
          </div>
        </CModalFooter>
      </CModal>
    </Teleport>

    <!-- Add Partner Modal -->
    <Teleport to="body">
      <CModal :visible="showAddPartnerModal" class="modal-bottom-sheet" @close="showAddPartnerModal = false">
        <CModalHeader>
          <CModalTitle>Add New Partner</CModalTitle>
        </CModalHeader>
        <CModalBody>
          <CForm>
          <CRow class="g-3">
            <CCol md="12">
              <CFormLabel>Name <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="newPartner.name" placeholder="Enter partner's full name" />
            </CCol>
            <CCol md="12">
              <CFormLabel>Phone Number <span class="text-danger">*</span></CFormLabel>
              <CFormInput v-model="newPartner.phone" placeholder="e.g. 0241234567" />
            </CCol>
            <CCol md="12">
              <CFormLabel>Email</CFormLabel>
              <CFormInput v-model="newPartner.email" type="email" placeholder="Enter email address" />
            </CCol>
          </CRow>
          </CForm>
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" @click="showAddPartnerModal = false">Cancel</CButton>
          <CButton color="primary" :disabled="!newPartner.name || !newPartner.phone" @click="saveNewPartner">
            <i class="bi bi-check me-1"></i> Add Partner
          </CButton>
        </CModalFooter>
      </CModal>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue';
import {
  CRow,
  CCol,
  CButton,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableHeaderCell,
  CTableDataCell,
  CBadge,
  CAvatar,
  CFormInput,
  CFormSelect,
  CFormLabel,
  CFormTextarea,
  CInputGroup,
  CInputGroupText,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CPagination,
  CPaginationItem,
  CForm,
} from '@coreui/vue';
import { CChartBar } from '@coreui/vue-chartjs';
import PageHeader from '../components/shared/PageHeader.vue';
import MaterialCard from '../components/material/MaterialCard.vue';
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

interface PaymentMethodStat {
  method: string;
  color: string;
  icon: string;
  amount: number;
  count: number;
}

interface NewPartner {
  name: string;
  phone: string;
  email: string;
}

// Toast notification system
const toast = useToast();
const members = ref<Member[]>([]);
const loadingMembers = ref<boolean>(false);

// Fetch users/partners from API
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

onMounted(() => {
  fetchMembers();
});

// Contributions data
const contributions = ref<Contribution[]>([
  {
    id: 1,
    date: '2026-01-26',
    memberId: 1,
    memberName: 'Kwame Asante',
    memberPhone: '0241234567',
    type: 'tithe',
    amount: 500,
    paymentMethod: 'momo',
    reference: 'TXN123456',
    recordedBy: 'Finance User',
  },
  {
    id: 2,
    date: '2026-01-26',
    memberId: 2,
    memberName: 'Ama Mensah',
    memberPhone: '0201234567',
    type: 'offering',
    amount: 100,
    paymentMethod: 'cash',
    reference: '',
    recordedBy: 'Finance User',
  },
  {
    id: 3,
    date: '2026-01-26',
    memberId: null,
    memberName: null,
    memberPhone: null,
    type: 'offering',
    amount: 50,
    paymentMethod: 'cash',
    reference: '',
    recordedBy: 'Finance User',
  },
  {
    id: 4,
    date: '2026-01-25',
    memberId: 3,
    memberName: 'Kofi Boateng',
    memberPhone: '0271234567',
    type: 'special_seed',
    amount: 1000,
    paymentMethod: 'bank',
    reference: 'BNK789012',
    recordedBy: 'Finance User',
  },
  {
    id: 5,
    date: '2026-01-19',
    memberId: 1,
    memberName: 'Kwame Asante',
    memberPhone: '0241234567',
    type: 'tithe',
    amount: 500,
    paymentMethod: 'momo',
    reference: 'TXN654321',
    recordedBy: 'Finance User',
  },
  {
    id: 6,
    date: '2026-01-12',
    memberId: 2,
    memberName: 'Ama Mensah',
    memberPhone: '0201234567',
    type: 'building_fund',
    amount: 200,
    paymentMethod: 'momo',
    reference: 'TXN111222',
    recordedBy: 'Finance User',
  },
]);

// State
const search = ref<string>('');
const typeFilter = ref<string>('');
const paymentFilter = ref<string>('');
const dateFrom = ref<string>('');
const dateTo = ref<string>('');
const currentPage = ref<number>(1);
const perPage = 10;

const showContributionModal = ref<boolean>(false);
const showReceiptModal = ref<boolean>(false);
const showMemberSearch = ref<boolean>(false);
const showAddPartnerModal = ref<boolean>(false);
const editingId = ref<number | null>(null);
const formSubmitted = ref<boolean>(false);
const saving = ref<boolean>(false);

// New Partner form
const newPartner = reactive<NewPartner>({
  name: '',
  phone: '',
  email: '',
});

// Save new partner
async function saveNewPartner(): Promise<void> {
  if (!newPartner.name || !newPartner.phone) return;
  try {
    const response = await usersApi.create({
      name: newPartner.name,
      phone: newPartner.phone,
      email: newPartner.email || undefined,
      role: 'member',
    });
    if (response.data.success) {
      toast.success('Partner added successfully');
      // Add to local list and select
      const newMember = {
        id: response.data.data.id,
        name: newPartner.name,
        phone: newPartner.phone,
      };
      members.value.push(newMember);
      form.memberId = newMember.id;
      // Reset form
      newPartner.name = '';
      newPartner.phone = '';
      newPartner.email = '';
      showAddPartnerModal.value = false;
    }
  } catch (error) {
    console.error('Failed to add partner:', error);
    toast.error((error as any).response?.data?.message || 'Failed to add partner');
  }
}
const selectedReceipt = ref<Contribution | null>(null);
const selectedMonth = ref('2026-01');

const months = [
  { value: '2026-01', label: 'January 2026' },
  { value: '2025-12', label: 'December 2025' },
  { value: '2025-11', label: 'November 2025' },
];

// Form
const form = reactive<FormData>({
  memberId: '',
  type: '',
  amount: '',
  paymentMethod: '',
  date: '',
  reference: '',
  mobileNumber: '',
  notes: '',
});

// Computed Stats
const todayContributions = computed<Contribution[]>(() => {
  const today = new Date().toISOString().split('T')[0];
  return contributions.value.filter(c => c.date === today);
});
const todayTotal = computed<number>(() => todayContributions.value.reduce((s, c) => s + c.amount, 0));
const todayCount = computed<number>(() => todayContributions.value.length);

const weekContributions = computed<Contribution[]>(() => {
  const today = new Date();
  const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
  return contributions.value.filter(c => new Date(c.date) >= weekAgo);
});
const weekTotal = computed<number>(() => weekContributions.value.reduce((s, c) => s + c.amount, 0));
const weekCount = computed<number>(() => weekContributions.value.length);

const monthContributions = computed<Contribution[]>(() => {
  const thisMonth = new Date().toISOString().slice(0, 7);
  return contributions.value.filter(c => c.date.startsWith(thisMonth));
});
const monthTotal = computed<number>(() => monthContributions.value.reduce((s, c) => s + c.amount, 0));
const monthCount = computed<number>(() => monthContributions.value.length);

const yearContributions = computed<Contribution[]>(() => {
  const thisYear = new Date().getFullYear().toString();
  return contributions.value.filter(c => c.date.startsWith(thisYear));
});
const yearTotal = computed<number>(() => yearContributions.value.reduce((s, c) => s + c.amount, 0));
const yearCount = computed<number>(() => yearContributions.value.length);

const typeBreakdown = computed<{ tithe: number; offering: number; specialSeed: number }>(() => ({
  tithe: monthContributions.value.filter(c => c.type === 'tithe').reduce((s, c) => s + c.amount, 0),
  offering: monthContributions.value
    .filter(c => c.type === 'offering')
    .reduce((s, c) => s + c.amount, 0),
  specialSeed: monthContributions.value
    .filter(c => c.type === 'special_seed')
    .reduce((s, c) => s + c.amount, 0),
}));

// Chart data
const chartData = computed(() => ({
  labels: ['Tithe', 'Offering', 'Special Seed', 'Building Fund', 'Missions', 'Welfare'],
  datasets: [
    {
      label: 'Amount (GH₵)',
      backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#6f42c1', '#dc3545'],
      data: [
        typeBreakdown.value.tithe,
        typeBreakdown.value.offering,
        typeBreakdown.value.specialSeed,
        monthContributions.value.filter(c => c.type === 'building_fund').reduce((s, c) => s + c.amount, 0),
        monthContributions.value.filter(c => c.type === 'missions').reduce((s, c) => s + c.amount, 0),
        monthContributions.value.filter(c => c.type === 'welfare').reduce((s, c) => s + c.amount, 0),
      ],
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value: number) => 'GH₵' + value.toLocaleString(),
      },
    },
  },
};

const paymentMethodStats = computed<PaymentMethodStat[]>(() => [
  {
    method: 'Mobile Money',
    color: 'warning',
    icon: 'bi bi-phone',
    amount: monthContributions.value
      .filter(c => c.paymentMethod === 'momo')
      .reduce((s, c) => s + c.amount, 0),
    count: monthContributions.value.filter(c => c.paymentMethod === 'momo').length,
  },
  {
    method: 'Cash',
    color: 'success',
    icon: 'bi bi-cash',
    amount: monthContributions.value
      .filter(c => c.paymentMethod === 'cash')
      .reduce((s, c) => s + c.amount, 0),
    count: monthContributions.value.filter(c => c.paymentMethod === 'cash').length,
  },
  {
    method: 'Bank Transfer',
    color: 'primary',
    icon: 'bi bi-bank',
    amount: monthContributions.value
      .filter(c => c.paymentMethod === 'bank')
      .reduce((s, c) => s + c.amount, 0),
    count: monthContributions.value.filter(c => c.paymentMethod === 'bank').length,
  },
]);

// Filtered contributions
const filteredContributions = computed<Contribution[]>(() => {
  let result = contributions.value;
  if (search.value) {
    const s = search.value.toLowerCase();
    result = result.filter(
      c => c.memberName?.toLowerCase().includes(s) || c.reference?.includes(s)
    );
  }
  if (typeFilter.value) result = result.filter(c => c.type === typeFilter.value);
  if (paymentFilter.value) result = result.filter(c => c.paymentMethod === paymentFilter.value);
  if (dateFrom.value) result = result.filter(c => c.date >= dateFrom.value);
  if (dateTo.value) result = result.filter(c => c.date <= dateTo.value);
  return result.sort((a, b) => b.date.localeCompare(a.date));
});

const totalPages = computed<number>(() => Math.ceil(filteredContributions.value.length / perPage));

const paginatedContributions = computed<Contribution[]>(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return filteredContributions.value.slice(start, end);
});

const visiblePages = computed<number[]>(() => {
  const pages: number[] = [];
  const total = totalPages.value;
  const current = currentPage.value;
  const maxVisible = 5;

  if (total <= maxVisible) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    let start = Math.max(1, current - Math.floor(maxVisible / 2));
    let end = Math.min(total, start + maxVisible - 1);

    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1);
    }

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
  }

  return pages;
});

// Methods
function openAddContribution(): void {
  editingId.value = null;
  Object.assign(form, {
    memberId: '',
    type: '',
    amount: '',
    paymentMethod: '',
    date: new Date().toISOString().split('T')[0],
    reference: '',
    mobileNumber: '',
    notes: '',
  });
  showContributionModal.value = true;
}

function editContribution(c: Contribution): void {
  editingId.value = c.id;
  Object.assign(form, {
    memberId: c.memberId || '',
    type: c.type,
    amount: c.amount,
    paymentMethod: c.paymentMethod,
    date: c.date,
    reference: c.reference || '',
    mobileNumber: '',
    notes: c.notes || '',
  });
  showContributionModal.value = true;
}

function saveContribution(): void {
  formSubmitted.value = true;

  // Validation
  if (!form.type || !form.amount || parseFloat(form.amount) <= 0 || !form.paymentMethod || !form.date) {
    toast.error('Please fill in all required fields correctly');
    return;
  }

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
      const newId = Math.max(...contributions.value.map(c => c.id)) + 1;
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
    formSubmitted.value = false;
  } catch (error) {
    console.error('Error saving contribution:', error);
    toast.error('Failed to save contribution');
  } finally {
    saving.value = false;
  }
}

function viewReceipt(c: Contribution): void {
  selectedReceipt.value = c;
  showReceiptModal.value = true;
}

function printReceipt(): void {
  window.print();
}

function exportReport(): void {
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

  const exportData = filteredContributions.value.map(c => ({
    ...c,
    memberName: c.memberName || 'Anonymous',
    type: typeLabel(c.type),
    paymentMethod: paymentLabel(c.paymentMethod),
  }));

  exportToExcel(exportData, columns, 'contributions_report');
  toast.success(`Exported ${exportData.length} contributions to Excel`);
}

// Helpers
function formatDate(date: string): string {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function formatMoney(amount: number): string {
  return (amount || 0).toLocaleString('en-GH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function typeColor(type: string): string {
  const colors: Record<string, string> = {
    tithe: 'primary',
    offering: 'success',
    special_seed: 'warning',
    building_fund: 'info',
    missions: 'purple',
    welfare: 'danger',
  };
  return colors[type] || 'secondary';
}

function typeLabel(type: string): string {
  const labels: Record<string, string> = {
    tithe: 'Tithe',
    offering: 'Offering',
    special_seed: 'Special Seed',
    building_fund: 'Building Fund',
    missions: 'Missions',
    welfare: 'Welfare',
  };
  return labels[type] || type;
}

function paymentIcon(method: string): string {
  const icons: Record<string, string> = {
    cash: 'bi bi-cash',
    momo: 'bi bi-phone',
    bank: 'bi bi-bank',
    cheque: 'bi bi-file-text',
  };
  return icons[method] || 'bi bi-credit-card';
}

function paymentLabel(method: string): string {
  const labels: Record<string, string> = { cash: 'Cash', momo: 'Mobile Money', bank: 'Bank Transfer', cheque: 'Cheque' };
  return labels[method] || method;
}
</script>

<style scoped>
.page-wrap {
  padding: 24px;
}

.page-header {
  margin-bottom: 20px;
}

.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.stat-icon-sm {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.contribution-card {
  transition: all 0.2s ease;
  border-left: 4px solid #007bff;
}

.contribution-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.receipt-body {
  font-family: 'Courier New', monospace;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.church-logo {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  color: white;
}

.receipt-details {
  border: 1px dashed #dee2e6;
}

.amount-section {
  border: 2px solid #007bff;
  position: relative;
}

.amount-section::before {
  content: '';
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 20px;
  background: white;
  border: 2px solid #007bff;
  border-radius: 50%;
}

.badge-sm {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}

.hover-bg-light:hover {
  background-color: #f8f9fa !important;
}

.transition-all {
  transition: all 0.2s ease;
}

.cursor-pointer {
  cursor: pointer;
}

.chart-container {
  position: relative;
  height: 250px;
}

@media (max-width: 768px) {
  .page-wrap {
    padding: 10px;
  }

  .stat-card {
    margin-bottom: 1rem;
  }

  .contribution-card {
    margin-bottom: 0.5rem;
  }
}
</style>
