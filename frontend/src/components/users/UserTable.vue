<template>
  <div class="user-table-container">
    <CTable hover responsive align="middle" class="user-table">
      <CTableHead class="user-table-header">
        <CTableRow>
          <CTableHeaderCell class="user-header-cell">
            <i class="bi bi-person-circle me-2"></i>User
          </CTableHeaderCell>
          <CTableHeaderCell class="user-header-cell">
            <i class="bi bi-telephone me-2"></i>Phone
          </CTableHeaderCell>
          <CTableHeaderCell class="user-header-cell">
            <i class="bi bi-shield-check me-2"></i>Role
          </CTableHeaderCell>
          <CTableHeaderCell class="user-header-cell">
            <i class="bi bi-building me-2"></i>Department
          </CTableHeaderCell>
          <CTableHeaderCell class="user-header-cell">
            <i class="bi bi-activity me-2"></i>Status
          </CTableHeaderCell>
          <CTableHeaderCell class="user-header-cell">
            <i class="bi bi-clock me-2"></i>Last Login
          </CTableHeaderCell>
          <CTableHeaderCell class="user-header-cell text-end">
            <i class="bi bi-gear me-2"></i>Actions
          </CTableHeaderCell>
        </CTableRow>
      </CTableHead>
      <CTableBody>
        <CTableRow v-for="user in users" :key="user.id" class="user-row">
          <CTableDataCell class="user-profile-cell">
            <div class="user-profile-wrapper">
              <CAvatar :src="user.avatar" size="md" class="user-avatar-modern" :class="getRandomColor(user.name)">
                <span v-if="!user.avatar" class="avatar-initial">{{ user.name.charAt(0).toUpperCase() }}</span>
              </CAvatar>
              <div class="user-identity">
                <div class="user-name fw-semibold">{{ user.name }}</div>
                <div class="user-email text-muted small">{{ user.email || 'No email' }}</div>
              </div>
            </div>
          </CTableDataCell>
          <CTableDataCell class="user-phone-cell">
            <div class="phone-wrapper">
              <i class="bi bi-telephone-fill text-muted me-2"></i>
              <span class="phone-text">{{ user.phone || '—' }}</span>
            </div>
          </CTableDataCell>
          <CTableDataCell class="user-role-cell">
            <div class="role-badges-container">
              <template v-if="user.roles && user.roles.length > 0">
                <CBadge v-for="r in user.roles.slice(0, 2)" :key="r.id" :color="roleColor(r.name)"
                  class="role-badge-modern">
                  <i :class="roleIcon(r.name)" class="me-1"></i>
                  <span class="role-text">{{ r.display_name || r.name }}</span>
                </CBadge>
                <CBadge v-if="user.roles.length > 2" color="secondary" class="role-badge-modern">
                  +{{ user.roles.length - 2 }}
                </CBadge>
              </template>
              <CBadge v-else :color="roleColor(user.role)" class="role-badge-modern">
                <i :class="roleIcon(user.role)" class="me-1"></i>
                <span class="role-text">{{ roleLabel(user.role) }}</span>
              </CBadge>
            </div>
          </CTableDataCell>
          <CTableDataCell class="user-dept-cell">
            <div class="department-wrapper">
              <i class="bi bi-building text-muted me-2"></i>
              <span class="dept-text fw-medium">{{ user.departmentName || 'Not assigned' }}</span>
            </div>
          </CTableDataCell>
          <CTableDataCell class="user-status-cell">
            <div class="status-indicator-modern" :class="user.status">
              <div class="status-dot"></div>
              <span class="status-text fw-medium">{{ user.status === 'active' ? 'Active' : 'Inactive' }}</span>
            </div>
          </CTableDataCell>
          <CTableDataCell class="user-login-cell">
            <div class="last-login-wrapper">
              <i class="bi bi-clock-history text-muted me-2"></i>
              <div v-if="user.lastLogin" class="login-info">
                <span class="login-date">{{ user.lastLogin }}</span>
              </div>
              <span v-else class="no-login text-muted">Never</span>
            </div>
          </CTableDataCell>
          <CTableDataCell class="user-actions-cell text-end">
            <div class="action-buttons">
              <CButton color="light" size="sm" class="action-btn edit-btn me-1" @click="$emit('edit', user)">
                <i class="bi bi-pencil-square"></i>
              </CButton>
              <CButton v-if="user.status === 'active'" color="danger" size="sm" variant="ghost"
                class="action-btn deactivate-btn" @click="$emit('deactivate', user)">
                <i class="bi bi-person-slash"></i>
              </CButton>
              <CButton v-else color="success" size="sm" variant="ghost" class="action-btn reactivate-btn"
                @click="$emit('reactivate', user)">
                <i class="bi bi-person-check"></i>
              </CButton>
            </div>
          </CTableDataCell>
        </CTableRow>
      </CTableBody>
    </CTable>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell, CBadge, CAvatar, CButton } from '@coreui/vue';

const props = defineProps({
  users: { type: Array, default: () => [] },
  roleColor: { type: Function, required: true },
  roleIcon: { type: Function, required: true },
  roleLabel: { type: Function, required: true },
});

function getRandomColor(name) {
  const colors = [
    'avatar-purple',
    'avatar-blue',
    'avatar-green',
    'avatar-orange',
    'avatar-pink',
    'avatar-red',
  ];
  // Generate consistent color based on name
  const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
  return colors[hash % colors.length];
}
</script>

<style scoped>
.user-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.user-table {
  margin-bottom: 0;
  border: none;
}

.user-table-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
}

.user-header-cell {
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 1rem 1.5rem;
  border: none;
  vertical-align: middle;
}

.user-header-cell i {
  opacity: 0.8;
}

.user-row {
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.user-row:hover {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.02) 0%, rgba(118, 75, 162, 0.02) 100%);
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.user-row:last-child {
  border-bottom: none;
}

.user-profile-cell {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
}

.user-profile-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar-modern {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  border: 3px solid transparent;
  background: linear-gradient(white, white) padding-box,
    linear-gradient(135deg, #667eea, #764ba2) border-box;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.user-avatar-modern:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 8px 16px -2px rgba(102, 126, 234, 0.3);
}

.avatar-initial {
  font-weight: 700;
  font-size: 1.1rem;
  color: white;
}

.avatar-purple {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.avatar-blue {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.avatar-green {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.avatar-orange {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.avatar-pink {
  background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.avatar-red {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.user-identity {
  flex: 1;
}

.user-name {
  font-size: 1rem;
  color: #1a202c;
  margin-bottom: 0.125rem;
}

.user-email {
  font-size: 0.875rem;
  color: #718096;
}

.user-phone-cell,
.user-dept-cell,
.user-status-cell,
.user-login-cell {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
}

.phone-wrapper,
.department-wrapper,
.last-login-wrapper {
  display: flex;
  align-items: center;
}

.phone-text,
.dept-text,
.login-date {
  font-weight: 500;
  color: #2d3748;
}

.no-login {
  font-style: italic;
}

.user-role-cell {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
}

.role-badges-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.role-badge-modern {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.2s ease;
}

.role-badge-modern:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.role-text {
  font-size: 0.75rem;
}

.status-indicator-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: all 0.2s ease;
}

.status-indicator-modern.active {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(67, 233, 123, 0.3);
}

.status-indicator-modern.inactive {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(245, 87, 108, 0.3);
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
  opacity: 0.8;
}

.user-actions-cell {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  border: none;
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s;
}

.action-btn:hover::before {
  left: 100%;
}

.edit-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.edit-btn:hover {
  background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}

.deactivate-btn {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(245, 87, 108, 0.3);
}

.deactivate-btn:hover {
  background: linear-gradient(135deg, #e87fb8 0%, #f34a5f 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(245, 87, 108, 0.4);
}

.reactivate-btn {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(67, 233, 123, 0.3);
}

.reactivate-btn:hover {
  background: linear-gradient(135deg, #3bd96d 0%, #32e4c9 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(67, 233, 123, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
  .user-profile-wrapper {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .user-header-cell {
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
  }

  .user-profile-cell,
  .user-phone-cell,
  .user-dept-cell,
  .user-status-cell,
  .user-login-cell,
  .user-actions-cell {
    padding: 1rem;
  }

  .role-badges-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }

  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }

  .user-row:hover {
    transform: none;
  }
}

@media (max-width: 576px) {
  .user-table-container {
    border-radius: 8px;
  }

  .user-avatar-modern {
    width: 40px;
    height: 40px;
  }

  .user-name {
    font-size: 0.875rem;
  }

  .user-email {
    font-size: 0.75rem;
  }
}

/* Loading state styles */
.skeleton-row {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.5;
  }
}
</style>
