<script setup>
import { useAuthStore } from '../stores/authStore'
import { useRouter } from 'vue-router'

defineProps({
  toggle: {
    type: Function,
    required: false,
    default: () => {}
  }
})

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <nav class="navbar">
    <div class="navbar-left">
      <button class="menu-btn" @click="toggle" title="Menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
      <h1 class="navbar-title">Evolution CRM</h1>
    </div>

    <div class="navbar-right">
      <div class="user-info">
        <span class="user-name">{{ authStore.user?.name || 'Usuário' }}</span>
        <span class="user-role">{{ authStore.user?.role || 'user' }}</span>
      </div>
      <button class="logout-btn" @click="handleLogout" title="Sair">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
  padding: 0 20px;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  flex-shrink: 0;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.menu-btn {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.2s;
}

.menu-btn:hover {
  background: #f1f5f9;
  color: #2563eb;
}

@media (max-width: 768px) {
  .menu-btn {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.navbar-title {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

.user-role {
  font-size: 12px;
  color: #64748b;
  text-transform: capitalize;
}

.logout-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logout-btn:hover {
  background: #fee2e2;
  color: #dc2626;
}

@media (max-width: 480px) {
  .navbar {
    padding: 0 12px;
  }

  .user-info {
    display: none;
  }

  .navbar-title {
    font-size: 16px;
  }
}
</style>
