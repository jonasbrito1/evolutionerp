import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const token = ref(localStorage.getItem('auth_token') || null)
  const user = ref(localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null)
  const isLoading = ref(false)
  const error = ref(null)

  // Computed
  const isAuthenticated = computed(() => !!token.value)

  // Actions
  async function login(email, password) {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.post('/auth/login', {
        email,
        password,
      })

      const { token: newToken, user: userData } = response.data

      token.value = newToken
      user.value = userData

      localStorage.setItem('auth_token', newToken)
      localStorage.setItem('user', JSON.stringify(userData))

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erro ao fazer login'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (err) {
      console.error('Erro ao fazer logout:', err)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  }

  async function fetchUser() {
    if (!token.value) return null

    try {
      const response = await api.get('/auth/me')
      user.value = response.data
      localStorage.setItem('user', JSON.stringify(response.data))
      return response.data
    } catch (err) {
      console.error('Erro ao buscar dados do usuário:', err)
      throw err
    }
  }

  return {
    token,
    user,
    isLoading,
    error,
    isAuthenticated,
    login,
    logout,
    fetchUser,
  }
})
