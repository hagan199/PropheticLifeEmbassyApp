<template>
  <Teleport to="body">
    <CModal v-model:visible="localVisible" size="lg" alignment="center">
      <div class="modal-header-gradient p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="header-icon-box">
            <i class="bi bi-people-fill"></i>
          </div>
          <div>
            <h5 class="fw-bold mb-0 text-white">{{ department?.name }}</h5>
            <small class="text-white-50">{{ members.length }} member{{ members.length !== 1 ? 's' : '' }}</small>
          </div>
        </div>
        <button class="btn-close btn-close-white" @click="localVisible = false"></button>
      </div>

      <div class="p-4">
        <!-- Add Member Section -->
        <div class="add-member-section mb-4">
          <label class="form-label fw-semibold small text-muted text-uppercase">Add Member</label>
          <div class="d-flex gap-2">
            <div class="flex-grow-1 position-relative">
              <input v-model="searchQuery" type="text" class="form-control rounded-pill ps-4"
                placeholder="Search users by name or phone..." @input="onSearchInput" />
              <div v-if="searchResults.length && searchQuery" class="search-dropdown">
                <div v-for="u in searchResults" :key="u.id" class="search-result-item"
                  @click="addMember(u)">
                  <div class="d-flex align-items-center gap-2">
                    <CAvatar color="primary" size="sm" text-color="white">{{ u.name?.charAt(0) }}</CAvatar>
                    <div>
                      <div class="fw-medium small">{{ u.name }}</div>
                      <div class="text-muted x-small">{{ u.phone }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Members List -->
        <div v-if="loading" class="text-center py-4">
          <CSpinner color="primary" size="sm" />
          <p class="text-muted small mt-2">Loading members...</p>
        </div>

        <div v-else-if="members.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
          <p class="mb-1 fw-medium">No members yet</p>
          <small>Search and add users above</small>
        </div>

        <div v-else class="members-list">
          <div v-for="m in members" :key="m.id"
            class="member-row d-flex align-items-center justify-content-between py-3 px-3">
            <div class="d-flex align-items-center gap-3">
              <CAvatar :color="m.id === department?.leader_id ? 'warning' : 'primary'" size="md" text-color="white"
                class="fw-bold">
                {{ m.name?.charAt(0) }}
              </CAvatar>
              <div>
                <div class="fw-semibold">
                  {{ m.name }}
                  <span v-if="m.id === department?.leader_id"
                    class="badge bg-warning-subtle text-warning ms-2 small">Leader</span>
                </div>
                <div class="text-muted small">
                  <i class="bi bi-telephone me-1"></i>{{ m.phone || 'No phone' }}
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a v-if="m.phone" :href="'tel:' + m.phone" class="btn btn-sm btn-outline-success rounded-circle"
                title="Call" @click.stop>
                <i class="bi bi-telephone"></i>
              </a>
              <button class="btn btn-sm btn-outline-danger rounded-circle" title="Remove from department"
                :disabled="removing === m.id" @click.stop="removeMember(m)">
                <i v-if="removing !== m.id" class="bi bi-x-lg"></i>
                <CSpinner v-else size="sm" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </CModal>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { CModal, CAvatar, CSpinner } from '@coreui/vue';
import { departmentsApi } from '../../api/departments';
import { usersApi } from '../../api/users';
import { useToast } from '../../composables/useToast';

const props = defineProps<{
  visible: boolean;
  department: any;
}>();

const emit = defineEmits(['update:visible', 'updated']);

const toast = useToast();
const localVisible = ref(props.visible);
const loading = ref(false);
const members = ref<any[]>([]);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const removing = ref<string | null>(null);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(() => props.visible, (v) => {
  localVisible.value = v;
});

watch(localVisible, (v) => {
  emit('update:visible', v);
});

watch(() => props.visible, async (v) => {
  if (v && props.department) {
    await fetchMembers();
    searchQuery.value = '';
    searchResults.value = [];
  }
});

async function fetchMembers() {
  if (!props.department?.id) return;
  loading.value = true;
  try {
    const res = await departmentsApi.getMembers(props.department.id);
    members.value = res.data?.data || [];
  } catch {
    toast.error('Failed to load members');
  } finally {
    loading.value = false;
  }
}

function onSearchInput() {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (!searchQuery.value.trim()) {
    searchResults.value = [];
    return;
  }
  searchTimeout = setTimeout(async () => {
    try {
      const res = await usersApi.getAll({ search: searchQuery.value, per_page: 10 });
      const allUsers = res.data?.data || [];
      // Filter out users already in this department
      const memberIds = new Set(members.value.map((m: any) => m.id));
      searchResults.value = allUsers.filter((u: any) => !memberIds.has(u.id));
    } catch {
      searchResults.value = [];
    }
  }, 300);
}

async function addMember(user: any) {
  try {
    const res = await departmentsApi.addMember(props.department.id, user.id);
    if (res.data?.success) {
      toast.success(`${user.name} added to ${props.department.name}`);
      members.value.push(user);
      searchQuery.value = '';
      searchResults.value = [];
      emit('updated');
    }
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Failed to add member');
  }
}

async function removeMember(member: any) {
  removing.value = member.id;
  try {
    const res = await departmentsApi.removeMember(props.department.id, member.id);
    if (res.data?.success) {
      members.value = members.value.filter((m: any) => m.id !== member.id);
      toast.success(`${member.name} removed`);
      emit('updated');
    }
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Failed to remove member');
  } finally {
    removing.value = null;
  }
}
</script>

<style scoped>
.modal-header-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px 8px 0 0;
}

.header-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: white;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  z-index: 10;
  max-height: 250px;
  overflow-y: auto;
  margin-top: 4px;
}

.search-result-item {
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.15s;
}

.search-result-item:hover {
  background: #f8fafc;
}

.search-result-item:first-child {
  border-radius: 12px 12px 0 0;
}

.search-result-item:last-child {
  border-radius: 0 0 12px 12px;
}

.members-list {
  max-height: 400px;
  overflow-y: auto;
}

.member-row {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
}

.member-row:hover {
  background: #f8fafc;
}

.member-row:last-child {
  border-bottom: none;
}

.x-small {
  font-size: 11px;
}
</style>
