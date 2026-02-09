# Material Dashboard Vue Migration Plan

## Current State
- **UI Framework**: CoreUI Vue 5 + Bootstrap 5
- **Components**: Custom Sidebar, Navbar, MobileTabBar, StatCard
- **Views**: 22 views (Dashboard, Users, Contributions, etc.)
- **Styling**: Custom CSS in App.vue + style.css

## Target State
- **UI Framework**: Material Dashboard Vue 3 (Vuetify-based) OR Custom Material Design System
- **Design Language**: Google Material Design 3 with premium glassmorphism effects
- **Goal**: Fast, responsive, seamless, modern admin experience

---

## Migration Strategy: Hybrid Approach
Instead of fully replacing CoreUI, we'll create a **custom Material-inspired design system** that:
1. Uses existing Vue 3 + Vite setup (no framework switch overhead)
2. Implements Material Design 3 principles (elevation, motion, color)
3. Adds premium glassmorphism and micro-animations
4. Maintains all existing functionality

---

## Phase 1: Design System Foundation
### 1.1 Create CSS Variables & Theme
- [ ] Material color palette (primary, secondary, surface, on-surface)
- [ ] Elevation shadows (0-24 levels)
- [ ] Typography scale (Material Design type scale)
- [ ] Spacing scale (4px grid system)
- [ ] Border radius tokens (rounded corners)
- [ ] Motion/animation tokens (durations, easings)

### 1.2 Core Component Styles
- [ ] Cards with elevation and glassmorphism
- [ ] Buttons (filled, tonal, outlined, text)
- [ ] Form inputs with floating labels
- [ ] Data tables with hover states
- [ ] Chips and badges

---

## Phase 2: Layout Refactor
### 2.1 Sidebar Redesign
- [ ] Material navigation rail for collapsed state
- [ ] Smooth slide transitions
- [ ] Active state with pill indicator
- [ ] Icon-only mode with tooltips

### 2.2 Navbar/Header Redesign  
- [ ] App bar with elevated surface
- [ ] Search with expandable animation
- [ ] Notification popover with card design
- [ ] Profile menu with avatar

### 2.3 Responsive Breakpoints
- [ ] Navigation drawer for mobile
- [ ] Bottom navigation for mobile
- [ ] Adaptive layouts for tablets

---

## Phase 3: View Updates
### 3.1 Dashboard
- [ ] Material stat cards with gradients
- [ ] Chart cards with proper headers
- [ ] Quick action FAB

### 3.2 Data Views (Users, Contributions, etc.)
- [ ] Material data tables
- [ ] Filter chips
- [ ] Search bar integration
- [ ] Action menus

### 3.3 Forms
- [ ] Floating label inputs
- [ ] Validation states
- [ ] Date pickers
- [ ] Select dropdowns

---

## Phase 4: Motion & Polish
- [ ] Page transitions (shared element)
- [ ] Skeleton loaders
- [ ] Ripple effects
- [ ] Scroll animations
- [ ] Loading states

---

## Implementation Order
1. **theme.css** - Design tokens and variables
2. **material-components.css** - Component styles
3. **Sidebar.vue** - Navigation refactor
4. **Navbar.vue** - Header refactor
5. **Dashboard.vue** - First view migration
6. **Remaining views** - Pattern replication

---

## Files to Create/Modify
```
frontend/src/
├── assets/styles/
│   ├── theme.css          # Design tokens [NEW]
│   ├── material.css       # Component styles [NEW]
│   └── utilities.css      # Helper classes [NEW]
├── components/
│   ├── material/          # New Material components [NEW]
│   │   ├── MCard.vue
│   │   ├── MButton.vue
│   │   ├── MInput.vue
│   │   └── MTable.vue
│   ├── Sidebar.vue        # [REFACTOR]
│   └── Navbar.vue         # [REFACTOR]
└── App.vue                # [REFACTOR imports]
```

---

## Timeline Estimate
- Phase 1: Design System - 1 hour
- Phase 2: Layout Refactor - 2 hours
- Phase 3: View Updates - 3 hours
- Phase 4: Polish - 1 hour

**Total: ~7 hours for complete migration**

---

## Starting Now: Phase 1.1 - Design System
