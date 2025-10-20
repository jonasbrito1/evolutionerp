<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

defineProps({
  open: Boolean
})

defineEmits(['toggle', 'close'])

const router = useRouter()
const route = useRoute()

const menuItems = [
  { name: 'Dashboard', route: '/', icon: 'M3 12a9 9 0 1118 0 9 9 0 01-18 0z' },
  { name: 'Editais', route: '/edicts', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { name: 'Licitações', route: '/tenders', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
  { name: 'Financeiro', route: '/financeiro', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { name: 'Documentos', route: '/documents', icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' },
]

const isActive = (path) => route.path === path
</script>

<template>
  <aside :class="['sidebar', { 'sidebar-open': open }]">
    <div class="sidebar-header">
      <h2 class="sidebar-logo">Evolution</h2>
      <button v-if="open" class="sidebar-close" @click="$emit('close')" title="Fechar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
    </div>

    <nav class="sidebar-nav">
      <RouterLink
        v-for="item in menuItems"
        :key="item.route"
        :to="item.route"
        :class="['nav-link', { 'nav-link-active': isActive(item.route) }]"
        @click="$emit('close')"
      >
        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path :d="item.icon" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="nav-label">{{ item.name }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>

<style scoped>
.sidebar {
  position: fixed;
  left: 0;
  top: 60px;
  width: 250px;
  height: calc(100% - 60px);
  background: #1f2937;
  color: white;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #374151;
  z-index: 40;
  transition: transform 0.3s ease;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: 0;
    top: 60px;
    height: calc(100vh - 60px);
    transform: translateX(-100%);
    z-index: 40;
  }

  .sidebar-open {
    transform: translateX(0);
  }
}

@media (min-width: 769px) {
  .sidebar {
    position: relative;
    height: 100%;
    top: 0;
    transform: translateX(0) !important;
  }
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #374151;
  flex-shrink: 0;
}

.sidebar-logo {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.sidebar-close {
  display: none;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.sidebar-close:hover {
  background: #374151;
  color: white;
}

@media (max-width: 768px) {
  .sidebar-close {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 15px;
  flex: 1;
  overflow-y: auto;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 15px;
  color: #d1d5db;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.2s;
  cursor: pointer;
}

.nav-link:hover {
  background: #374151;
  color: white;
}

.nav-link-active {
  background: #3b82f6;
  color: white;
  font-weight: 600;
}

.nav-icon {
  flex-shrink: 0;
  stroke: currentColor;
  fill: none;
}

.nav-label {
  font-size: 14px;
  font-weight: 500;
}

@media (max-width: 480px) {
  .sidebar {
    width: 100%;
  }

  .nav-label {
    font-size: 13px;
  }
}

/* Scrollbar */
.sidebar-nav::-webkit-scrollbar {
  width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>
