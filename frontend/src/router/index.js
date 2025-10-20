import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('../views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/edicts',
    name: 'Edicts',
    component: () => import('../views/EdictsList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/tenders',
    name: 'Tenders',
    component: () => import('../views/KanbanBoard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/documents',
    name: 'Documents',
    component: () => import('../views/DocumentsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/financeiro',
    name: 'Financial',
    component: () => import('../views/FinancialView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFoundView.vue'),
    meta: { requiresAuth: false }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Se a rota requer autenticação
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Redirecionar para login
      next({ name: 'Login', query: { redirect: to.fullPath } })
    } else {
      next()
    }
  } else if (to.name === 'Login' && authStore.isAuthenticated) {
    // Se está autenticado e tenta acessar login, redireciona para home
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router
