<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="max-w-[1600px] mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-2">Visão geral e estatísticas do sistema</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Cards de Estatísticas Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Editais -->
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <span class="text-2xl font-bold">{{ dashboardData.editais?.total || 0 }}</span>
            </div>
            <h3 class="text-sm font-medium opacity-90">Total de Editais</h3>
            <p class="text-xs opacity-75 mt-1">{{ dashboardData.editais?.this_month || 0 }} este mês</p>
          </div>

          <!-- Documentos -->
          <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <span class="text-2xl font-bold">{{ dashboardData.documentos?.total || 0 }}</span>
            </div>
            <h3 class="text-sm font-medium opacity-90">Documentos</h3>
            <p class="text-xs opacity-75 mt-1">{{ dashboardData.documentos?.expiring_soon || 0 }} vencendo em 30 dias</p>
          </div>

          <!-- Saldo Financeiro -->
          <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="text-2xl font-bold">{{ formatCurrency(dashboardData.financeiro?.stats?.saldo || 0) }}</span>
            </div>
            <h3 class="text-sm font-medium opacity-90">Saldo Financeiro</h3>
            <p class="text-xs opacity-75 mt-1">Receitas - Despesas</p>
          </div>

          <!-- Tarefas Kanban -->
          <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-white bg-opacity-20 rounded-lg p-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <span class="text-2xl font-bold">{{ dashboardData.kanban?.total_cards || 0 }}</span>
            </div>
            <h3 class="text-sm font-medium opacity-90">Tarefas Ativas</h3>
            <p class="text-xs opacity-75 mt-1">Gerenciamento de projetos</p>
          </div>
        </div>

        <!-- Estatísticas Financeiras Detalhadas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <h4 class="text-sm font-bold text-gray-600 mb-2">Total Receitas</h4>
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(dashboardData.financeiro?.stats?.total_receitas || 0) }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendente: {{ formatCurrency(dashboardData.financeiro?.stats?.receitas_pendentes || 0) }}</p>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
            <h4 class="text-sm font-bold text-gray-600 mb-2">Total Despesas</h4>
            <p class="text-2xl font-bold text-red-600">{{ formatCurrency(dashboardData.financeiro?.stats?.total_despesas || 0) }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendente: {{ formatCurrency(dashboardData.financeiro?.stats?.despesas_pendentes || 0) }}</p>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <h4 class="text-sm font-bold text-gray-600 mb-2">Contas Atrasadas</h4>
            <p class="text-2xl font-bold text-yellow-600">{{ dashboardData.financeiro?.stats?.contas_atrasadas || 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Necessitam atenção</p>
          </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Evolução Financeira -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Evolução Financeira (6 meses)</h3>
            <canvas ref="financialChart"></canvas>
          </div>

          <!-- Despesas por Categoria -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top 5 Despesas por Categoria</h3>
            <canvas ref="expensesChart"></canvas>
          </div>
        </div>

        <!-- Editais por Status e Receitas por Categoria -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Editais por Status -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Editais por Status</h3>
            <canvas ref="edictsChart"></canvas>
          </div>

          <!-- Receitas por Categoria -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top 5 Receitas por Categoria</h3>
            <canvas ref="incomeChart"></canvas>
          </div>
        </div>

        <!-- Atividades Recentes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Editais Recentes -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
              <h3 class="text-lg font-bold text-gray-800">Editais Recentes</h3>
            </div>
            <div class="p-6">
              <div v-if="dashboardData.editais?.recent?.length > 0" class="space-y-4">
                <div v-for="edict in dashboardData.editais.recent" :key="edict.id"
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                  <div>
                    <p class="font-medium text-gray-900">{{ edict.edict_number }} - {{ edict.organ }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(edict.created_at) }}</p>
                  </div>
                  <span :class="getStatusBadgeClass(edict.status)"
                        class="px-3 py-1 rounded-full text-xs font-bold">
                    {{ edict.status }}
                  </span>
                </div>
              </div>
              <p v-else class="text-gray-500 text-center py-8">Nenhum edital cadastrado</p>
            </div>
          </div>

          <!-- Transações Recentes -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
              <h3 class="text-lg font-bold text-gray-800">Transações Financeiras Recentes</h3>
            </div>
            <div class="p-6">
              <div v-if="dashboardData.financeiro?.recent_transactions?.length > 0" class="space-y-4">
                <div v-for="trans in dashboardData.financeiro.recent_transactions" :key="trans.id"
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                  <div>
                    <p class="font-medium text-gray-900">{{ trans.description }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(trans.created_at) }}</p>
                  </div>
                  <div class="text-right">
                    <p :class="trans.type === 'receita' ? 'text-green-600' : 'text-red-600'"
                       class="font-bold">
                      {{ formatCurrency(trans.amount) }}
                    </p>
                    <p class="text-xs text-gray-500">{{ trans.status }}</p>
                  </div>
                </div>
              </div>
              <p v-else class="text-gray-500 text-center py-8">Nenhuma transação cadastrada</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import api from '../services/api'

Chart.register(...registerables)

const loading = ref(true)
const dashboardData = ref({})
const financialChart = ref(null)
const expensesChart = ref(null)
const edictsChart = ref(null)
const incomeChart = ref(null)

async function fetchDashboard() {
  try {
    const response = await api.get('/dashboard', {
      params: { company_id: 1 }
    })
    dashboardData.value = response.data.data

    // Aguarda o próximo tick para garantir que os canvas foram renderizados
    await new Promise(resolve => setTimeout(resolve, 100))

    createCharts()
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  } finally {
    loading.value = false
  }
}

function createCharts() {
  createFinancialEvolutionChart()
  createExpensesByCategoryChart()
  createEdictsByStatusChart()
  createIncomeByCategoryChart()
}

function createFinancialEvolutionChart() {
  if (!financialChart.value) return

  const evolution = dashboardData.value.financeiro?.evolution || []

  new Chart(financialChart.value, {
    type: 'line',
    data: {
      labels: evolution.map(e => e.month),
      datasets: [
        {
          label: 'Receitas',
          data: evolution.map(e => e.receitas),
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Despesas',
          data: evolution.map(e => e.despesas),
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'R$ ' + value.toLocaleString('pt-BR')
            }
          }
        }
      }
    }
  })
}

function createExpensesByCategoryChart() {
  if (!expensesChart.value) return

  const expenses = dashboardData.value.financeiro?.expenses_by_category || []

  new Chart(expensesChart.value, {
    type: 'doughnut',
    data: {
      labels: expenses.map(e => e.category),
      datasets: [{
        data: expenses.map(e => e.total),
        backgroundColor: [
          '#ef4444',
          '#f59e0b',
          '#8b5cf6',
          '#3b82f6',
          '#10b981'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }
  })
}

function createEdictsByStatusChart() {
  if (!edictsChart.value) return

  const edicts = dashboardData.value.editais?.by_status || []

  new Chart(edictsChart.value, {
    type: 'bar',
    data: {
      labels: edicts.map(e => e.status),
      datasets: [{
        label: 'Quantidade',
        data: edicts.map(e => e.total),
        backgroundColor: '#3b82f6'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  })
}

function createIncomeByCategoryChart() {
  if (!incomeChart.value) return

  const income = dashboardData.value.financeiro?.income_by_category || []

  new Chart(incomeChart.value, {
    type: 'doughnut',
    data: {
      labels: income.map(i => i.category),
      datasets: [{
        data: income.map(i => i.total),
        backgroundColor: [
          '#10b981',
          '#14b8a6',
          '#06b6d4',
          '#3b82f6',
          '#8b5cf6'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }
  })
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0)
}

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('pt-BR') : '-'
}

function getStatusBadgeClass(status) {
  const classes = {
    'recommended': 'bg-green-100 text-green-800',
    'not_recommended': 'bg-red-100 text-red-800',
    'processing': 'bg-yellow-100 text-yellow-800',
    'pending': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchDashboard()
})
</script>
