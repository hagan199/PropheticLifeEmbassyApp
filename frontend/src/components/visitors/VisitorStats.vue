<template>
  <div class="stats-grid mt-4">
    <div class="md-mini-card animate-fadeInUp stagger-2">
      <div class="icon-box visitors">
        <i class="bi bi-people"></i>
      </div>
      <div class="details">
        <div class="value">{{ visitorCount }}</div>
        <div class="label">Visitors</div>
      </div>
    </div>
    <div class="md-mini-card animate-fadeInUp stagger-3">
      <div class="icon-box partners">
        <i class="bi bi-star"></i>
      </div>
      <div class="details">
        <div class="value">{{ partnerCount }}</div>
        <div class="label">Partners</div>
      </div>
    </div>
    <div class="md-mini-card animate-fadeInUp stagger-4 w-100 mt-2">
      <div class="icon-box members">
        <i class="bi bi-person-heart"></i>
      </div>
      <div class="details">
        <div class="value">{{ memberCount }}</div>
        <div class="label">Want to be Members</div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  visitorCount: { type: Number, default: 0 },
  partnerCount: { type: Number, default: 0 },
  memberCount: { type: Number, default: 0 },
});
</script>

<style scoped></style>
