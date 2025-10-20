<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">Financeiro</h1>
          <p class="text-gray-600 mt-2">Gestão financeira da empresa</p>
        </div>
        <button @click="showAddModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold flex items-center gap-2">
          <span>+</span> Nova Transação
        </button>
      </div>

      <!-- Dashboard Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <button
          @click="filterByCard('receitas')"
          :class="activeFilter === 'receitas' ? 'ring-4 ring-green-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 transition-all cursor-pointer text-left"
        >
          <p class="text-sm text-gray-600 mb-2">Total Receitas</p>
          <p class="text-3xl font-bold text-green-600">{{ formatCurrency(stats.total_receitas) }}</p>
        </button>

        <button
          @click="filterByCard('despesas')"
          :class="activeFilter === 'despesas' ? 'ring-4 ring-red-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 transition-all cursor-pointer text-left"
        >
          <p class="text-sm text-gray-600 mb-2">Total Despesas</p>
          <p class="text-3xl font-bold text-red-600">{{ formatCurrency(stats.total_despesas) }}</p>
        </button>

        <button
          @click="filterByCard('saldo')"
          :class="activeFilter === 'saldo' ? 'ring-4 ring-blue-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 transition-all cursor-pointer text-left"
        >
          <p class="text-sm text-gray-600 mb-2">Saldo</p>
          <p class="text-3xl font-bold" :class="stats.saldo >= 0 ? 'text-blue-600' : 'text-red-600'">
            {{ formatCurrency(stats.saldo) }}
          </p>
        </button>

        <button
          @click="filterByCard('receitas_pendentes')"
          :class="activeFilter === 'receitas_pendentes' ? 'ring-4 ring-yellow-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 transition-all cursor-pointer text-left"
        >
          <p class="text-sm text-gray-600 mb-2">Receitas Pendentes</p>
          <p class="text-3xl font-bold text-yellow-600">{{ formatCurrency(stats.receitas_pendentes) }}</p>
        </button>

        <button
          @click="filterByCard('despesas_pendentes')"
          :class="activeFilter === 'despesas_pendentes' ? 'ring-4 ring-orange-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500 transition-all cursor-pointer text-left"
        >
          <p class="text-sm text-gray-600 mb-2">Despesas Pendentes</p>
          <p class="text-3xl font-bold text-orange-600">{{ formatCurrency(stats.despesas_pendentes) }}</p>
        </button>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">Filtros e Busca</h3>
          <div class="flex gap-2">
            <button @click="exportCsv" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-bold text-sm">
              Exportar CSV
            </button>
            <button @click="exportPdf" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold text-sm">
              Exportar PDF
            </button>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Campo de Busca -->
          <input
            v-model="filters.search"
            type="text"
            placeholder="Buscar por descrição ou número..."
            class="px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />

          <select v-model="filters.type" class="px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os Tipos</option>
            <option value="receita">Receitas</option>
            <option value="despesa">Despesas</option>
          </select>

          <select v-model="filters.status" class="px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os Status</option>
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
            <option value="recebido">Recebido</option>
            <option value="atrasado">Atrasado</option>
            <option value="cancelado">Cancelado</option>
          </select>

          <select v-model="filters.category" class="px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Todas as Categorias</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.name">
              {{ cat.name }}
            </option>
          </select>

          <button @click="clearFilters" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-bold">
            Limpar Filtros
          </button>
        </div>
        <p class="text-sm text-gray-500 mt-3">{{ transactions.length }} transação(ões) encontrada(s)</p>
      </div>

      <!-- Tabela de Transações -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Data Venc.</th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Tipo</th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Categoria</th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Descrição</th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Valor</th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Status</th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="trans in transactions" :key="trans.id" class="border-b hover:bg-gray-50">
              <td class="px-6 py-4 text-sm">{{ formatDate(trans.due_date) }}</td>
              <td class="px-6 py-4">
                <span v-if="trans.type === 'receita'" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                  Receita
                </span>
                <span v-else class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                  Despesa
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="getCategoryClass(trans.category)" class="px-3 py-1 rounded-full text-xs font-bold border">
                  {{ trans.category }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">{{ trans.description }}</td>
              <td class="px-6 py-4 text-sm font-bold" :class="trans.type === 'receita' ? 'text-green-600' : 'text-red-600'">
                {{ formatCurrency(trans.amount) }}
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(trans.status)" class="px-3 py-1 rounded-full text-xs font-bold">
                  {{ getStatusLabel(trans.status) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  <button @click="editTransaction(trans)" class="text-blue-600 hover:text-blue-800">Editar</button>
                  <button @click="deleteTransaction(trans.id)" class="text-red-600 hover:text-red-800">Excluir</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal Adicionar/Editar -->
      <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h2 class="text-2xl font-bold mb-6">{{ showEditModal ? 'Editar' : 'Nova' }} Transação</h2>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tipo *</label>
                <select v-model="form.type" class="w-full px-4 py-3 border rounded-lg">
                  <option value="receita">Receita</option>
                  <option value="despesa">Despesa</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Categoria *</label>
                <select v-model="form.category" class="w-full px-4 py-3 border rounded-lg">
                  <option value="">Selecione uma categoria</option>
                  <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.name">
                    {{ cat.name }}
                  </option>
                </select>
                <!-- Preview da categoria selecionada com cor -->
                <div v-if="form.category" class="mt-2">
                  <span :class="getCategoryClass(form.category)" class="inline-block px-3 py-1 rounded-full text-xs font-bold border">
                    {{ form.category }}
                  </span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Descrição *</label>
              <input v-model="form.description" type="text" class="w-full px-4 py-3 border rounded-lg" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Valor *</label>
                <input
                  v-model="displayAmount"
                  @input="formatAmountInput"
                  @blur="formatAmountBlur"
                  type="text"
                  placeholder="R$ 0,00"
                  class="w-full px-4 py-3 border rounded-lg"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Data de Vencimento *</label>
                <input v-model="form.due_date" type="date" class="w-full px-4 py-3 border rounded-lg" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                <select v-model="form.status" class="w-full px-4 py-3 border rounded-lg">
                  <option value="pendente">Pendente</option>
                  <option value="pago">Pago</option>
                  <option value="recebido">Recebido</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Método de Pagamento</label>
                <select v-model="form.payment_method" class="w-full px-4 py-3 border rounded-lg">
                  <option value="">Selecione...</option>
                  <option v-for="(label, key) in paymentMethods" :key="key" :value="key">{{ label }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Observações</label>
              <textarea v-model="form.notes" class="w-full px-4 py-3 border rounded-lg" rows="3"></textarea>
            </div>
          </div>

          <div class="flex gap-3 justify-end mt-6">
            <button @click="closeModal" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold">
              Cancelar
            </button>
            <button @click="saveTransaction" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold">
              Salvar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import api from '../services/api'

const transactions = ref([])
const categories = ref([])
const paymentMethods = ref({})
const stats = ref({
  total_receitas: 0,
  total_despesas: 0,
  receitas_pendentes: 0,
  despesas_pendentes: 0,
  saldo: 0,
  contas_atrasadas: 0
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const activeFilter = ref('')
const displayAmount = ref('')

const filters = ref({
  type: '',
  status: '',
  category: '',
  search: ''
})

const form = ref({
  type: 'despesa',
  category: '',
  description: '',
  amount: '',
  due_date: '',
  payment_date: '',
  status: 'pendente',
  payment_method: '',
  notes: '',
  company_id: 1
})

const filteredCategories = computed(() => {
  if (!form.value.type) return []
  const filtered = categories.value.filter(cat => cat.type === form.value.type || cat.type === 'ambos')
  console.log('Tipo selecionado:', form.value.type, 'Categorias filtradas:', filtered)
  return filtered
})

// Watch para resetar categoria quando o tipo mudar
watch(() => form.value.type, (newType, oldType) => {
  if (newType !== oldType) {
    form.value.category = ''
    console.log('Tipo mudou para:', newType, '- Categoria resetada')
  }
})

// Watch para aplicar filtros automaticamente
watch(() => filters.value.type, () => {
  fetchTransactions()
})

watch(() => filters.value.status, () => {
  fetchTransactions()
})

watch(() => filters.value.category, () => {
  fetchTransactions()
})

watch(() => filters.value.search, () => {
  fetchTransactions()
})

async function fetchTransactions() {
  try {
    const params = {
      company_id: 1,
      ...filters.value
    }
    const response = await api.get('/financial/transactions', { params })
    transactions.value = response.data.data || []
  } catch (error) {
    console.error('Erro ao carregar transações:', error)
  }
}

async function fetchDashboard() {
  try {
    const response = await api.get('/financial/dashboard', {
      params: { company_id: 1 }
    })
    stats.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  }
}

async function fetchCategories() {
  try {
    const response = await api.get('/financial/categories')
    categories.value = response.data.data || []
    console.log('Categorias carregadas:', categories.value)
  } catch (error) {
    console.error('Erro ao carregar categorias:', error)
    if (error.response?.status === 401) {
      alert('Sessão expirada. Por favor, faça login novamente.')
      setTimeout(() => {
        window.location.href = '/login'
      }, 2000)
    }
  }
}

async function fetchPaymentMethods() {
  try {
    const response = await api.get('/financial/payment-methods')
    paymentMethods.value = response.data.data || {}
  } catch (error) {
    console.error('Erro ao carregar métodos de pagamento:', error)
  }
}

async function saveTransaction() {
  try {
    // Validar campos obrigatórios
    if (!form.value.type || !form.value.category || !form.value.description ||
        !form.value.amount || !form.value.due_date) {
      alert('Por favor, preencha todos os campos obrigatórios (*).')
      return
    }

    console.log('Salvando transação:', form.value)

    if (showEditModal.value) {
      await api.put(`/financial/transactions/${form.value.id}`, form.value)
      alert('Transação atualizada com sucesso!')
    } else {
      const response = await api.post('/financial/transactions', form.value)
      console.log('Resposta da criação:', response.data)
      alert('Transação criada com sucesso!')
    }
    closeModal()
    fetchTransactions()
    fetchDashboard()
  } catch (error) {
    console.error('Erro completo ao salvar:', error)
    console.error('Resposta do erro:', error.response)
    console.error('Status do erro:', error.response?.status)

    // Mostrar mensagem de erro mais detalhada
    let errorMessage = 'Erro ao salvar transação'

    if (error.response?.status === 401) {
      errorMessage = 'Sessão expirada. Por favor, faça login novamente.'
      setTimeout(() => {
        window.location.href = '/login'
      }, 2000)
    } else if (error.response?.data) {
      if (error.response.data.message) {
        errorMessage = error.response.data.message
      }

      // Se houver erros de validação, mostrar detalhes
      if (error.response.data.errors) {
        const validationErrors = Object.values(error.response.data.errors).flat()
        errorMessage += ':\n' + validationErrors.join('\n')
      }

      // Se houver erro específico
      if (error.response.data.error) {
        errorMessage += '\n\nDetalhes: ' + error.response.data.error
      }
    }

    alert(errorMessage)
  }
}

function editTransaction(trans) {
  form.value = { ...trans }
  // Formatar o valor para exibição
  if (trans.amount) {
    displayAmount.value = 'R$ ' + parseFloat(trans.amount).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }
  showEditModal.value = true
}

async function deleteTransaction(id) {
  if (!confirm('Tem certeza que deseja excluir esta transação?')) return

  try {
    await api.delete(`/financial/transactions/${id}`)
    alert('Transação excluída com sucesso!')
    fetchTransactions()
    fetchDashboard()
  } catch (error) {
    console.error('Erro ao excluir:', error)
    alert('Erro ao excluir transação')
  }
}

function closeModal() {
  showAddModal.value = false
  showEditModal.value = false
  displayAmount.value = ''
  form.value = {
    type: 'despesa',
    category: '',
    description: '',
    amount: '',
    due_date: '',
    payment_date: '',
    status: 'pendente',
    payment_method: '',
    notes: '',
    company_id: 1
  }
}

function filterByCard(filterType) {
  // Toggle: se clicar no mesmo filtro, desativa
  if (activeFilter.value === filterType) {
    activeFilter.value = ''
    filters.value.type = ''
    filters.value.status = ''
  } else {
    activeFilter.value = filterType

    // Aplicar filtros conforme o card clicado
    switch (filterType) {
      case 'receitas':
        filters.value.type = 'receita'
        filters.value.status = ''
        break
      case 'despesas':
        filters.value.type = 'despesa'
        filters.value.status = ''
        break
      case 'saldo':
        filters.value.type = ''
        filters.value.status = ''
        break
      case 'receitas_pendentes':
        filters.value.type = 'receita'
        filters.value.status = 'pendente'
        break
      case 'despesas_pendentes':
        filters.value.type = 'despesa'
        filters.value.status = 'pendente'
        break
    }
  }

  fetchTransactions()
}

function applyFilters() {
  activeFilter.value = '' // Limpar filtro de card ao usar filtros manuais
  fetchTransactions()
}

function clearFilters() {
  activeFilter.value = ''
  filters.value = {
    type: '',
    status: '',
    category: '',
    search: ''
  }
  fetchTransactions()
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

function formatAmountInput(event) {
  let value = event.target.value

  // Remove tudo exceto números
  value = value.replace(/\D/g, '')

  // Se vazio, limpa
  if (!value) {
    displayAmount.value = ''
    form.value.amount = ''
    return
  }

  // Converte para número (centavos)
  const numberValue = parseInt(value) / 100

  // Atualiza o valor numérico no form
  form.value.amount = numberValue

  // Formata para exibição
  displayAmount.value = 'R$ ' + numberValue.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

function formatAmountBlur() {
  // Quando perde o foco, garante formatação completa
  if (form.value.amount) {
    displayAmount.value = 'R$ ' + parseFloat(form.value.amount).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  } else {
    displayAmount.value = ''
  }
}

function getStatusLabel(status) {
  const labels = {
    pendente: 'Pendente',
    pago: 'Pago',
    recebido: 'Recebido',
    atrasado: 'Atrasado',
    cancelado: 'Cancelado'
  }
  return labels[status] || status
}

function getStatusClass(status) {
  const classes = {
    pendente: 'bg-yellow-100 text-yellow-800',
    pago: 'bg-green-100 text-green-800',
    recebido: 'bg-green-100 text-green-800',
    atrasado: 'bg-red-100 text-red-800',
    cancelado: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getCategoryColor(categoryName) {
  const category = categories.value.find(cat => cat.name === categoryName)
  return category?.color || 'gray'
}

function getCategoryClass(categoryName) {
  // Mapeamento direto das categorias para cores (fixo para o Tailwind JIT gerar)
  const categoryColors = {
    'Licitação Ganha': 'bg-green-100 text-green-800 border-green-300',
    'Serviços Prestados': 'bg-blue-100 text-blue-800 border-blue-300',
    'Outras Receitas': 'bg-teal-100 text-teal-800 border-teal-300',
    'Salários': 'bg-orange-100 text-orange-800 border-orange-300',
    'Fornecedores': 'bg-red-100 text-red-800 border-red-300',
    'Impostos e Taxas': 'bg-purple-100 text-purple-800 border-purple-300',
    'Aluguel': 'bg-indigo-100 text-indigo-800 border-indigo-300',
    'Contas de Consumo': 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'Material de Escritório': 'bg-gray-100 text-gray-800 border-gray-300',
    'Manutenção': 'bg-pink-100 text-pink-800 border-pink-300',
    'Outras Despesas': 'bg-red-100 text-red-800 border-red-300'
  }
  return categoryColors[categoryName] || 'bg-gray-100 text-gray-800 border-gray-300'
}

async function exportCsv() {
  try {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      alert('Sessão expirada. Por favor, faça login novamente.')
      window.location.href = '/login'
      return
    }

    // Construir parâmetros apenas com valores preenchidos
    const params = new URLSearchParams({ company_id: '1' })

    if (filters.value.type) params.append('type', filters.value.type)
    if (filters.value.status) params.append('status', filters.value.status)
    if (filters.value.category) params.append('category', filters.value.category)
    if (filters.value.search) params.append('search', filters.value.search)

    const response = await fetch(`${import.meta.env.VITE_API_URL}/financial/export/csv?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'text/csv'
      }
    })

    if (!response.ok) {
      if (response.status === 401) {
        alert('Sessão expirada. Por favor, faça login novamente.')
        localStorage.removeItem('token')
        window.location.href = '/login'
        return
      }
      const errorData = await response.text()
      console.error('Erro da API:', errorData)
      throw new Error(`Erro ao exportar CSV: ${response.status}`)
    }

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `transacoes_financeiras_${new Date().toISOString().split('T')[0]}.csv`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  } catch (error) {
    console.error('Erro ao exportar CSV:', error)
    alert('Erro ao exportar dados para CSV: ' + error.message)
  }
}

async function exportPdf() {
  try {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      alert('Sessão expirada. Por favor, faça login novamente.')
      window.location.href = '/login'
      return
    }

    // Construir parâmetros apenas com valores preenchidos
    const params = new URLSearchParams({ company_id: '1' })

    if (filters.value.type) params.append('type', filters.value.type)
    if (filters.value.status) params.append('status', filters.value.status)
    if (filters.value.category) params.append('category', filters.value.category)
    if (filters.value.search) params.append('search', filters.value.search)

    const response = await fetch(`${import.meta.env.VITE_API_URL}/financial/export/pdf?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'text/html'
      }
    })

    if (!response.ok) {
      if (response.status === 401) {
        alert('Sessão expirada. Por favor, faça login novamente.')
        localStorage.removeItem('token')
        window.location.href = '/login'
        return
      }
      const errorData = await response.text()
      console.error('Erro da API:', errorData)
      throw new Error(`Erro ao exportar PDF: ${response.status}`)
    }

    const html = await response.text()

    // Abrir em nova janela para permitir impressão/salvamento como PDF
    const newWindow = window.open('', '_blank')
    newWindow.document.write(html)
    newWindow.document.close()

    // Dar tempo para o conteúdo carregar antes de chamar print
    setTimeout(() => {
      newWindow.print()
    }, 500)
  } catch (error) {
    console.error('Erro ao exportar PDF:', error)
    alert('Erro ao exportar dados para PDF: ' + error.message)
  }
}

onMounted(() => {
  fetchTransactions()
  fetchDashboard()
  fetchCategories()
  fetchPaymentMethods()
})
</script>
