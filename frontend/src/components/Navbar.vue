<template>
  <CHeader class="header">
    <div class="d-flex align-items-center w-100">
      <CButton color="light" class="me-2" @click="toggleSidebar">☰</CButton>
      <CHeaderBrand>
        <span class="fw-bold">Prophetic Life Embassy</span>
      </CHeaderBrand>
      <div class="flex-grow-1 d-none d-md-block px-3">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" placeholder="Search..." />
        </div>
      </div>
      <CHeaderNav class="ms-auto">
        <CButton color="light" class="me-2" @click="theme.toggle()">
          <i :class="theme.mode === 'dark' ? 'bi bi-brightness-high' : 'bi bi-moon'" />
        </CButton>
        <CButton color="light" class="me-2"><i class="bi bi-bell"></i></CButton>
        <CDropdown alignment="end">
          <CDropdownToggle color="light" class="d-flex align-items-center">
            <CAvatar color="primary" text-color="white" size="md">AD</CAvatar>
            <span class="ms-2 d-none d-sm-inline">Admin</span>
          </CDropdownToggle>
          <CDropdownMenu class="dropdown-menu-end">
            <CDropdownHeader class="fw-semibold">Account</CDropdownHeader>
            <CDropdownItem href="#">Profile</CDropdownItem>
            <CDropdownItem href="#">Settings</CDropdownItem>
            <CDropdownDivider />
            <CDropdownItem @click="signOut">Sign Out</CDropdownItem>
          </CDropdownMenu>
        </CDropdown>
      </CHeaderNav>
    </div>
  </CHeader>
</template>

<script setup>
import { CHeader, CHeaderBrand, CHeaderNav, CNavItem, CButton, CDropdown, CDropdownToggle, CDropdownMenu, CDropdownItem, CDropdownDivider, CAvatar, CDropdownHeader } from '@coreui/vue'
import { useAuthStore } from '../store/auth'
import { useThemeStore } from '../store/theme'
const props = defineProps({ toggleSidebar: { type: Function, default: () => { } } })
const auth = useAuthStore()
const theme = useThemeStore()
function signOut() {
  auth.signOut()
}
function toggleSidebar() { props.toggleSidebar() }
</script>
