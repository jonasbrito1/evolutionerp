<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/authStore'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const isLoading = ref(false)
const error = ref('')

const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  error.value = ''

  if (!email.value || !password.value) {
    error.value = 'Por favor, preencha email e senha'
    return
  }

  isLoading.value = true

  try {
    await authStore.login(email.value, password.value)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao fazer login'
  } finally {
    isLoading.value = false
  }
}

const handleDemoLogin = async () => {
  email.value = 'admin@licitaevolution.local'
  password.value = 'admin123456'
  await handleLogin()
}
</script>

<template>
  <div class="login-page">
    <div class="login-container">
      <!-- Header -->
      <div class="login-header">
        <div class="logo">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
          </svg>
        </div>
        <h1 class="login-title">Evolution CRM</h1>
        <p class="login-subtitle">Gerenciamento de Licitações Públicas</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <!-- Email Input -->
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="seu@email.com"
            required
            :disabled="isLoading"
          />
        </div>

        <!-- Password Input -->
        <div class="form-group">
          <label for="password">Senha</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            required
            :disabled="isLoading"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary" :disabled="isLoading">
          <span v-if="!isLoading">Entrar</span>
          <span v-else>Autenticando...</span>
        </button>

        <!-- Demo Button -->
        <button type="button" class="btn btn-secondary" @click="handleDemoLogin" :disabled="isLoading">
          👤 Demo
        </button>
      </form>

      <!-- Divider -->
      <div class="divider">Credenciais de Teste</div>

      <!-- Demo Credentials -->
      <div class="credentials">
        <div class="credential-box">
          <strong>Admin</strong>
          <small>admin@licitaevolution.local</small>
          <small>admin123456</small>
        </div>
        <div class="credential-box">
          <strong>Gerente</strong>
          <small>gerente@licitaevolution.local</small>
          <small>gerente123456</small>
        </div>
      </div>

      <!-- Footer -->
      <p class="login-footer">v1.0.0 • © 2025 Evolution CRM</p>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-container {
  width: 100%;
  max-width: 420px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  padding: 40px 30px;
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 12px;
  color: white;
  margin-bottom: 15px;
}

.login-title {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 5px 0;
}

.login-subtitle {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.login-form {
  margin-bottom: 25px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 10px 14px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
  font-family: inherit;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.error-message {
  padding: 10px 14px;
  background: #fee2e2;
  border: 1px solid #fca5a5;
  border-radius: 8px;
  color: #dc2626;
  font-size: 14px;
  margin-bottom: 16px;
}

.btn {
  width: 100%;
  padding: 11px 16px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
  margin-bottom: 10px;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
  background: white;
  color: #374151;
  border: 2px solid #e5e7eb;
}

.btn-secondary:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #d1d5db;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.divider {
  text-align: center;
  font-size: 12px;
  color: #9ca3af;
  margin: 20px 0;
  position: relative;
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 40%;
  height: 1px;
  background: #e5e7eb;
}

.divider::before {
  left: 0;
}

.divider::after {
  right: 0;
}

.credentials {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.credential-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.credential-box strong {
  font-size: 13px;
  color: #374151;
}

.credential-box small {
  font-size: 11px;
  color: #6b7280;
  word-break: break-all;
}

.login-footer {
  text-align: center;
  font-size: 12px;
  color: #9ca3af;
  margin: 0;
}

@media (max-width: 480px) {
  .login-container {
    padding: 30px 20px;
  }

  .login-title {
    font-size: 24px;
  }

  .credentials {
    grid-template-columns: 1fr;
  }
}
</style>
