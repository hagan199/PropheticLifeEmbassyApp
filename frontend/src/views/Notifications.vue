<template>
  <div class="page-wrap">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h2 class="title">Notifications</h2>
        <Breadcrumbs />
        <div class="text-muted">View all your notifications and alerts</div>
      </div>
      <div class="d-flex gap-2">
        <CButton color="light" @click="markAllRead" :disabled="unreadCount === 0">
          <i class="bi bi-check-all me-1"></i> Mark All Read
        </CButton>
        <CButton color="danger" variant="outline" @click="clearAll" :disabled="notifications.length === 0">
          <i class="bi bi-trash me-1"></i> Clear All
        </CButton>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-4">
      <CButtonGroup>
        <CButton :color="filter === 'all' ? 'primary' : 'light'" @click="filter = 'all'">
          All <CBadge color="secondary" class="ms-1">{{ notifications.length }}</CBadge>
        </CButton>
        <CButton :color="filter === 'unread' ? 'primary' : 'light'" @click="filter = 'unread'">
          Unread <CBadge color="danger" class="ms-1">{{ unreadCount }}</CBadge>
        </CButton>
        <CButton :color="filter === 'approval' ? 'primary' : 'light'" @click="filter = 'approval'">
          Approvals
        </CButton>
        <CButton :color="filter === 'visitor' ? 'primary' : 'light'" @click="filter = 'visitor'">
          Visitors
        </CButton>
        <CButton :color="filter === 'expense' ? 'primary' : 'light'" @click="filter = 'expense'">
          Expenses
        </CButton>
      </CButtonGroup>
    </div>

    <!-- Notifications List -->
    <CCard>
      <CCardBody class="p-0">
        <CListGroup flush>
          <CListGroupItem 
            v-for="n in filteredNotifications" 
            :key="n.id"
            class="d-flex align-items-start py-3 px-4"
            :class="{ 'bg-light': !n.read }"
            style="cursor: pointer;"
            @click="handleNotification(n)">
            <div class="me-3">
              <div class="notification-icon" :style="{ backgroundColor: getNotificationBgColor(n.type) }">
                <i :class="getNotificationIcon(n.type)" :style="{ color: getNotificationColor(n.type) }"></i>
              </div>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="fw-semibold" :class="{ 'text-dark': !n.read }">{{ n.title }}</div>
                  <div class="text-muted">{{ n.message }}</div>
                </div>
                <div class="text-end">
                  <div class="text-muted small">{{ n.time }}</div>
                  <CBadge v-if="!n.read" color="primary" shape="rounded-pill" class="mt-1">New</CBadge>
                </div>
              </div>
              <div class="mt-2" v-if="n.link">
                <CButton color="primary" size="sm" variant="outline" @click.stop="$router.push(n.link)">
                  View Details <i class="bi bi-arrow-right ms-1"></i>
                </CButton>
              </div>
            </div>
            <div class="ms-2">
              <CButton color="light" size="sm" @click.stop="deleteNotification(n.id)">
                <i class="bi bi-x"></i>
              </CButton>
            </div>
          </CListGroupItem>
          <CListGroupItem v-if="filteredNotifications.length === 0" class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
            No notifications found
          </CListGroupItem>
        </CListGroup>
      </CCardBody>
    </CCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  CCard, CCardBody, CListGroup, CListGroupItem, CButton, CButtonGroup, CBadge 
} from '@coreui/vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import { dashboardApi } from '../api/dashboard'

const router = useRouter()
const filter = ref('all')
const notifications = ref([])

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

const filteredNotifications = computed(() => {
  if (filter.value === 'all') return notifications.value
  if (filter.value === 'unread') return notifications.value.filter(n => !n.read)
  return notifications.value.filter(n => n.type === filter.value)
})

async function fetchNotifications() {
  try {
    const response = await dashboardApi.getStats()
    if (response.data.success && response.data.data.quick_actions) {
      // Create notifications from quick actions
      const quickActions = response.data.data.quick_actions.filter(a => a.count > 0)
      notifications.value = quickActions.map((action, idx) => ({
        id: idx + 1,
        type: action.label.includes('Approval') ? 'approval' : action.label.includes('Visitor') ? 'visitor' : 'expense',
        title: action.label,
        message: `${action.count} items require your attention`,
        time: 'Just now',
        read: false,
        link: action.link
      }))

      // Add some sample notifications for demonstration
      notifications.value.push(
        { id: 100, type: 'approval', title: 'Attendance Submitted', message: 'New attendance record submitted for Sunday Service', time: '2 hours ago', read: true, link: '/attendance-approvals' },
        { id: 101, type: 'visitor', title: 'New Visitor Registered', message: 'A new visitor was registered at Sunday Service', time: '5 hours ago', read: true, link: '/visitors' },
        { id: 102, type: 'expense', title: 'Expense Approved', message: 'Your expense request for office supplies was approved', time: '1 day ago', read: true, link: '/expense' }
      )
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

function getNotificationIcon(type) {
  const icons = {
    approval: 'bi bi-clipboard-check',
    visitor: 'bi bi-person-plus',
    expense: 'bi bi-receipt',
    default: 'bi bi-bell'
  }
  return icons[type] || icons.default
}

function getNotificationColor(type) {
  const colors = {
    approval: '#f9a825',
    visitor: '#4caf50',
    expense: '#2196f3',
    default: '#9e9e9e'
  }
  return colors[type] || colors.default
}

function getNotificationBgColor(type) {
  const colors = {
    approval: 'rgba(249, 168, 37, 0.15)',
    visitor: 'rgba(76, 175, 80, 0.15)',
    expense: 'rgba(33, 150, 243, 0.15)',
    default: 'rgba(158, 158, 158, 0.15)'
  }
  return colors[type] || colors.default
}

function handleNotification(n) {
  n.read = true
  if (n.link) {
    router.push(n.link)
  }
}

function markAllRead() {
  notifications.value.forEach(n => n.read = true)
}

function clearAll() {
  notifications.value = []
}

function deleteNotification(id) {
  notifications.value = notifications.value.filter(n => n.id !== id)
}

onMounted(() => {
  fetchNotifications()
})
</script>

<style scoped>
.page-wrap {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.list-group-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}
</style>
