<template>
  <div class="kpi-container mb-4">
    <div class="kpi-grid">
      <div class="kpi-card" style="--delay: 0s">
        <div class="kpi-inner">
          <div class="kpi-icon icon-purple">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="kpi-content">
            <span class="kpi-label">Total Visitors</span>
            <div class="d-flex align-items-baseline gap-2">
              <h2 class="kpi-value mb-0">{{ total }}</h2>
              <span class="trend-badge positive">
                <i class="bi bi-graph-up-arrow"></i> 12%
              </span>
            </div>
            <p class="kpi-sublabel mt-1 text-truncate">
              {{ visitorCount }} unique visitors
            </p>
          </div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.1s">
        <div class="kpi-inner">
          <div class="kpi-icon icon-emerald">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="kpi-content">
            <span class="kpi-label">Partners</span>
            <div class="d-flex align-items-baseline gap-2">
              <h2 class="kpi-value mb-0">{{ partnerCount }}</h2>
            </div>
            <p class="kpi-sublabel mt-1">High-engagement users</p>
          </div>
        </div>
      </div>

      <div class="kpi-card" style="--delay: 0.2s">
        <div class="kpi-inner">
          <div class="kpi-icon icon-rose">
            <i class="bi bi-person-heart"></i>
          </div>
          <div class="kpi-content">
            <span class="kpi-label">Member Interest</span>
            <div class="d-flex align-items-baseline gap-2">
              <h2 class="kpi-value mb-0">{{ memberCount }}</h2>
            </div>
            <p class="kpi-sublabel mt-1">Ready for follow-up</p>
          </div>
        </div>
      </div>

      <div class="kpi-card highlight" style="--delay: 0.3s">
        <div class="kpi-inner">
          <div class="kpi-icon icon-amber">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <div class="kpi-content">
            <span class="kpi-label">This Week</span>
            <div class="d-flex align-items-baseline gap-2">
              <h2 class="kpi-value mb-0 text-amber-900">{{ weeklyCount }}</h2>
            </div>
            <p class="kpi-sublabel mt-1">New registrations</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  total: { type: [Number, String], default: 0 },
  visitorCount: { type: Number, default: 0 },
  partnerCount: { type: Number, default: 0 },
  memberCount: { type: Number, default: 0 },
  weeklyCount: { type: Number, default: 0 },
});
</script>

<style scoped>
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
}

.kpi-card {
  opacity: 0;
  animation: slideIn 0.5s ease forwards;
  animation-delay: var(--delay);
  perspective: 1000px;
}

.kpi-inner {
  background: #ffffff;
  border: 1px solid #eef2f6;
  border-radius: 1.25rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.kpi-card:hover .kpi-inner {
  transform: translateY(-5px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  border-color: #d1d5db;
}

/* Icon Styles */
.kpi-icon {
  width: 56px;
  height: 56px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.kpi-card:hover .kpi-icon {
  transform: rotate(-5deg) scale(1.1);
}

.icon-purple { background: linear-gradient(135deg, #6366f1, #a855f7); }
.icon-emerald { background: linear-gradient(135deg, #10b981, #059669); }
.icon-rose    { background: linear-gradient(135deg, #f43f5e, #fb7185); }
.icon-amber   { background: linear-gradient(135deg, #f59e0b, #fbbf24); }

/* Typography */
.kpi-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  display: block;
}

.kpi-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
}

.kpi-sublabel {
  font-size: 0.813rem;
  color: #94a3b8;
  margin: 0;
}

/* Trends & Badges */
.trend-badge {
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
}

.trend-badge.positive {
  background: #ecfdf5;
  color: #059669;
}

.highlight .kpi-inner {
  background: #fffbeb;
  border-color: #fde68a;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjust for smaller screens */
@media (max-width: 576px) {
  .kpi-inner {
    padding: 1rem;
    gap: 0.75rem;
  }
  .kpi-icon {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }
}
</style>