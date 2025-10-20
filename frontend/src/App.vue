<script setup>
import { useAuthStore } from './stores/authStore'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from './components/Navbar.vue'
import Sidebar from './components/Sidebar.vue'
import ChatBot from './components/ChatBot.vue'

const authStore = useAuthStore()
const route = useRoute()
const sidebarOpen = ref(false)

const isLoginPage = computed(() => route.name === 'Login')

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}
</script>

<template>
  <div id="app">
    <!-- Login Page -->
    <template v-if="isLoginPage">
      <RouterView />
    </template>

    <!-- App Layout -->
    <template v-else>
      <div class="app-layout">
        <!-- Sidebar -->
        <Sidebar :open="sidebarOpen" @toggle="toggleSidebar" @close="closeSidebar" />

        <!-- Mobile Overlay -->
        <div v-if="sidebarOpen" class="sidebar-overlay" @click="closeSidebar" />

        <!-- Main Content -->
        <div class="main-wrapper">
          <Navbar @toggle="toggleSidebar" />
          <main class="main-content">
            <RouterView />
          </main>
        </div>

        <!-- ChatBot - Disponível em todo o sistema -->
        <ChatBot />
      </div>
    </template>
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html,
body {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

#app {
  width: 100%;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background: #f8f9fa;
}

.app-layout {
  display: flex;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.main-wrapper {
  display: flex;
  flex-direction: column;
  flex: 1;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.main-content {
  flex: 1;
  width: 100%;
  height: 100%;
  overflow: auto;
  background: #f8f9fa;
}

.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 30;
  animation: fadeIn 0.2s ease;
}

@media (max-width: 768px) {
  .sidebar-overlay {
    display: block;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Custom Scrollbar */
.main-content::-webkit-scrollbar {
  width: 8px;
}

.main-content::-webkit-scrollbar-track {
  background: transparent;
}

.main-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.main-content::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
