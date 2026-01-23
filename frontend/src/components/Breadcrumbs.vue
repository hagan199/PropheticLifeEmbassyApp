<template>
  <CBreadcrumb class="mb-2">
    <CBreadcrumbItem href="#">Home</CBreadcrumbItem>
    <CBreadcrumbItem v-for="(m, i) in items" :key="i" :active="i === items.length - 1">
      {{ m.meta?.breadcrumb || m.name || m.path }}
    </CBreadcrumbItem>
  </CBreadcrumb>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import { CBreadcrumb, CBreadcrumbItem } from '@coreui/vue'
const route = useRoute()
const items = computed(() => route.matched.filter(r => r.path))
</script>

<style scoped>
</style>
