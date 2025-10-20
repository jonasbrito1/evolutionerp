<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl sm:text-4xl font-black bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
          ⚔️ Licitações em Andamento
        </h1>
        <p class="text-gray-600 mt-1">Acompanhe todas as etapas das licitações que você está participando</p>
      </div>

      <div class="flex gap-3">
        <button
          @click="showNewCardModal = true"
          class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition-all flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Adicionar Licitação
        </button>

        <button
          @click="fetchBoard"
          class="bg-white border-2 border-gray-300 text-gray-700 px-4 py-3 rounded-xl font-semibold hover:border-gray-400 transition-all"
          title="Atualizar"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Kanban Board -->
    <div v-if="!loading && columns.length > 0" class="flex gap-4 overflow-x-auto pb-4">
      <div
        v-for="column in columns"
        :key="column.id"
        class="flex-shrink-0 w-80 bg-white rounded-xl shadow-lg p-4"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between mb-4 pb-3 border-b-2" :style="`border-color: ${column.color}`">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full" :style="`background-color: ${column.color}`"></div>
            <h3 class="font-bold text-gray-800 text-lg">{{ column.name }}</h3>
            <span class="bg-gray-100 px-2 py-1 rounded-full text-xs font-semibold text-gray-600">
              {{ column.cards?.length || 0 }}
            </span>
          </div>
        </div>

        <!-- Cards Container (Drag & Drop) -->
        <div
          @drop="handleDrop($event, column.id)"
          @dragover.prevent
          @dragenter.prevent
          class="space-y-3 min-h-[200px]"
        >
          <!-- Card -->
          <div
            v-for="card in column.cards"
            :key="card.id"
            draggable="true"
            @dragstart="handleDragStart($event, card)"
            @dragend="handleDragEnd"
            class="bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-lg p-4 cursor-move hover:shadow-xl transition-all hover:border-blue-400"
            :class="getPriorityClass(card.priority)"
          >
            <!-- Card Header -->
            <div class="flex items-start justify-between mb-2">
              <h4 class="font-bold text-gray-800 flex-1 line-clamp-2">{{ card.title }}</h4>
              <span
                v-if="card.priority === 'urgent'"
                class="text-red-500 text-xl"
                title="Urgente!"
              >
                🔥
              </span>
            </div>

            <!-- Edict Number -->
            <div v-if="card.edict_number" class="text-sm text-blue-600 font-semibold mb-2">
              📄 {{ card.edict_number }}
            </div>

            <!-- Description -->
            <p v-if="card.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
              {{ card.description }}
            </p>

            <!-- Estimated Value -->
            <div v-if="card.estimated_value" class="text-sm font-bold text-green-600 mb-3">
              💰 {{ formatCurrency(card.estimated_value) }}
            </div>

            <!-- Progress Bar -->
            <div class="mb-3">
              <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-gray-600 font-medium">Progresso</span>
                <span class="font-bold" :class="card.completion_percentage === 100 ? 'text-green-600' : 'text-blue-600'">
                  {{ card.completion_percentage }}%
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all"
                  :class="card.completion_percentage === 100 ? 'bg-green-500' : 'bg-blue-500'"
                  :style="`width: ${card.completion_percentage}%`"
                ></div>
              </div>
            </div>

            <!-- Checklist Icons -->
            <div class="flex gap-2 mb-3 flex-wrap">
              <span :class="card.has_budget ? 'text-green-500' : 'text-gray-300'" title="Orçamento">💰</span>
              <span :class="card.has_suppliers ? 'text-green-500' : 'text-gray-300'" title="Fornecedores">🏭</span>
              <span :class="card.has_certificates ? 'text-green-500' : 'text-gray-300'" title="Atestados">📜</span>
              <span :class="card.has_documents ? 'text-green-500' : 'text-gray-300'" title="Documentação">📄</span>
              <span :class="card.team_approved ? 'text-green-500' : 'text-gray-300'" title="Aprovado">✅</span>
            </div>

            <!-- Deadlines -->
            <div class="space-y-1 text-xs mb-3">
              <div v-if="card.deadline" class="flex items-center gap-1" :class="isOverdue(card.deadline) ? 'text-red-600 font-bold' : 'text-gray-600'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Prazo: {{ formatDate(card.deadline) }}
              </div>
              <div v-if="card.session_date" class="flex items-center gap-1 text-purple-600">
                ⚔️ Disputa: {{ formatDate(card.session_date) }}
              </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
              <div v-if="card.assigned_user" class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold">
                  {{ card.assigned_user.name?.charAt(0) || '?' }}
                </div>
                <span class="text-xs text-gray-600">{{ card.assigned_user.name }}</span>
              </div>

              <button
                @click="openCardDetails(card)"
                class="text-blue-600 hover:text-blue-800 text-sm font-semibold"
              >
                Ver detalhes →
              </button>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-if="!column.cards || column.cards.length === 0"
            class="text-center py-8 text-gray-400"
          >
            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-sm">Nenhum card aqui</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-500 border-t-transparent mx-auto mb-4"></div>
        <p class="text-gray-600">Carregando Kanban...</p>
      </div>
    </div>

    <!-- Empty State - No Columns -->
    <div v-if="!loading && columns.length === 0" class="bg-white rounded-2xl p-12 text-center">
      <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <h3 class="text-2xl font-bold text-gray-700 mb-2">Kanban não inicializado</h3>
      <p class="text-gray-600 mb-6">Clique no botão abaixo para criar as colunas padrão</p>
      <button
        @click="initializeKanban"
        class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-4 rounded-xl font-bold hover:shadow-lg transition-all"
      >
        🚀 Inicializar Kanban
      </button>
    </div>

    <!-- Modal: Adicionar Licitação -->
    <div v-if="showNewCardModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="showNewCardModal = false">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">➕ Adicionar Licitação ao Painel</h2>

        <form @submit.prevent="createCard" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Título *</label>
            <input
              v-model="newCard.title"
              type="text"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
              placeholder="Ex: Pregão 081/2025 - Exames Laboratoriais"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Descrição</label>
            <textarea
              v-model="newCard.description"
              rows="3"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
              placeholder="Breve descrição do pregão..."
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Número do Edital</label>
              <input
                v-model="newCard.edict_number"
                type="text"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                placeholder="Ex: 081/2025"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Valor Estimado</label>
              <input
                v-model="newCard.estimated_value"
                @input="handleCurrencyInput($event, 'estimated_value')"
                @blur="formatCurrencyOnBlur"
                @focus="removeCurrencyOnFocus"
                type="text"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                placeholder="R$ 0,00"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Prazo</label>
              <input
                v-model="newCard.deadline"
                type="date"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Data da Disputa</label>
              <input
                v-model="newCard.session_date"
                type="date"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Prioridade</label>
            <select
              v-model="newCard.priority"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
            >
              <option value="low">🟢 Baixa</option>
              <option value="medium">🟡 Média</option>
              <option value="high">🟠 Alta</option>
              <option value="urgent">🔴 Urgente</option>
            </select>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="saving"
              class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-xl font-bold hover:shadow-lg transition-all disabled:opacity-50"
            >
              {{ saving ? 'Salvando...' : '✅ Adicionar ao Kanban' }}
            </button>
            <button
              type="button"
              @click="showNewCardModal = false"
              class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Detalhes do Card -->
    <div v-if="selectedCard" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="selectedCard = null">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-start justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-800">{{ selectedCard.title }}</h2>
          <button @click="selectedCard = null" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Checklist Completo -->
        <div class="bg-gray-50 rounded-xl p-6 mb-6">
          <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Checklist de Validações
          </h3>

          <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 bg-white rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
              <input
                type="checkbox"
                v-model="selectedCard.has_budget"
                @change="updateCardChecklist"
                class="w-5 h-5 text-blue-600 rounded"
              />
              <span class="flex-1 font-medium">💰 Orçamento realizado</span>
            </label>

            <label class="flex items-center gap-3 p-3 bg-white rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
              <input
                type="checkbox"
                v-model="selectedCard.has_suppliers"
                @change="updateCardChecklist"
                class="w-5 h-5 text-blue-600 rounded"
              />
              <span class="flex-1 font-medium">🏭 Fornecedores confirmados</span>
            </label>

            <label class="flex items-center gap-3 p-3 bg-white rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
              <input
                type="checkbox"
                v-model="selectedCard.has_certificates"
                @change="updateCardChecklist"
                class="w-5 h-5 text-blue-600 rounded"
              />
              <span class="flex-1 font-medium">📜 Atestados disponíveis</span>
            </label>

            <label class="flex items-center gap-3 p-3 bg-white rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
              <input
                type="checkbox"
                v-model="selectedCard.has_documents"
                @change="updateCardChecklist"
                class="w-5 h-5 text-blue-600 rounded"
              />
              <span class="flex-1 font-medium">📄 Documentação completa</span>
            </label>

            <label class="flex items-center gap-3 p-3 bg-white rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
              <input
                type="checkbox"
                v-model="selectedCard.team_approved"
                @change="updateCardChecklist"
                class="w-5 h-5 text-blue-600 rounded"
              />
              <span class="flex-1 font-medium">✅ Aprovado pela equipe</span>
            </label>
          </div>

          <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center justify-between">
              <span class="font-semibold text-gray-700">Progresso Total:</span>
              <span class="text-2xl font-bold text-blue-600">{{ selectedCard.completion_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
              <div
                class="bg-blue-500 h-3 rounded-full transition-all"
                :style="`width: ${selectedCard.completion_percentage}%`"
              ></div>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="mb-6">
          <label class="block font-bold text-gray-700 mb-2">📝 Observações</label>
          <textarea
            v-model="selectedCard.notes"
            @blur="updateCardNotes"
            rows="4"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
            placeholder="Adicione observações importantes..."
          ></textarea>
        </div>

        <!-- Botões de Ação -->
        <div class="flex gap-3">
          <button
            v-if="selectedCard.edict_id"
            @click="goToEdict"
            class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-bold hover:bg-blue-600 transition-all"
          >
            📄 Ver Edital Completo
          </button>
          <button
            @click="deleteCard(selectedCard.id)"
            class="px-6 py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 transition-all"
          >
            🗑️ Excluir
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const columns = ref([])
const loading = ref(true)
const showNewCardModal = ref(false)
const selectedCard = ref(null)
const saving = ref(false)
const draggedCard = ref(null)

const newCard = ref({
  title: '',
  description: '',
  edict_number: '',
  estimated_value: null,
  deadline: '',
  session_date: '',
  priority: 'medium',
  kanban_column_id: null
})

// Fetch Kanban Board
async function fetchBoard() {
  try {
    loading.value = true
    const response = await api.get('/kanban')
    columns.value = response.data.columns
  } catch (error) {
    console.error('Erro ao carregar Kanban:', error)
  } finally {
    loading.value = false
  }
}

// Initialize Kanban
async function initializeKanban() {
  try {
    await api.post('/kanban/initialize')
    await fetchBoard()
    alert('✅ Kanban inicializado com sucesso!')
  } catch (error) {
    console.error('Erro ao inicializar Kanban:', error)
    alert('❌ Erro ao inicializar Kanban')
  }
}

// Create New Card
async function createCard() {
  if (!newCard.value.title) return

  try {
    saving.value = true

    // Se não especificou coluna, usar a primeira
    if (!newCard.value.kanban_column_id && columns.value.length > 0) {
      newCard.value.kanban_column_id = columns.value[0].id
    }

    await api.post('/kanban/cards', newCard.value)

    showNewCardModal.value = false
    newCard.value = {
      title: '',
      description: '',
      edict_number: '',
      estimated_value: null,
      deadline: '',
      session_date: '',
      priority: 'medium',
      kanban_column_id: null
    }

    await fetchBoard()
    alert('✅ Licitação adicionada ao painel!')
  } catch (error) {
    console.error('Erro ao criar card:', error)
    alert('❌ Erro ao criar card')
  } finally {
    saving.value = false
  }
}

// Drag & Drop Handlers
function handleDragStart(event, card) {
  draggedCard.value = card
  event.dataTransfer.effectAllowed = 'move'
}

function handleDragEnd() {
  draggedCard.value = null
}

async function handleDrop(event, columnId) {
  if (!draggedCard.value) return

  try {
    await api.post(`/kanban/cards/${draggedCard.value.id}/move`, {
      kanban_column_id: columnId
    })

    await fetchBoard()
  } catch (error) {
    console.error('Erro ao mover card:', error)
    alert('❌ Erro ao mover card')
  }
}

// Open Card Details
async function openCardDetails(card) {
  try {
    const response = await api.get(`/kanban/cards/${card.id}`)
    selectedCard.value = response.data.card
  } catch (error) {
    console.error('Erro ao carregar detalhes:', error)
  }
}

// Update Checklist
async function updateCardChecklist() {
  if (!selectedCard.value) return

  try {
    await api.post(`/kanban/cards/${selectedCard.value.id}/checklist`, {
      has_budget: selectedCard.value.has_budget,
      has_suppliers: selectedCard.value.has_suppliers,
      has_certificates: selectedCard.value.has_certificates,
      has_documents: selectedCard.value.has_documents,
      team_approved: selectedCard.value.team_approved
    })

    await fetchBoard()
  } catch (error) {
    console.error('Erro ao atualizar checklist:', error)
  }
}

// Update Notes
async function updateCardNotes() {
  if (!selectedCard.value) return

  try {
    await api.put(`/kanban/cards/${selectedCard.value.id}`, {
      notes: selectedCard.value.notes
    })
  } catch (error) {
    console.error('Erro ao salvar observações:', error)
  }
}

// Delete Card
async function deleteCard(cardId) {
  if (!confirm('Tem certeza que deseja excluir este card?')) return

  try {
    await api.delete(`/kanban/cards/${cardId}`)
    selectedCard.value = null
    await fetchBoard()
    alert('✅ Card removido')
  } catch (error) {
    console.error('Erro ao deletar card:', error)
    alert('❌ Erro ao deletar card')
  }
}

// Utils
function formatCurrency(value) {
  if (!value && value !== 0) return 'R$ 0,00'
  const numValue = typeof value === 'string' ? parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.')) : value
  if (isNaN(numValue)) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numValue)
}

// Handler para entrada de valores monetários com máscara
function handleCurrencyInput(event, field) {
  const input = event.target
  let value = input.value

  // Remove tudo que não é número
  value = value.replace(/\D/g, '')

  if (!value) {
    newCard.value[field] = 0
    input.value = ''
    return
  }

  // Converte para número com centavos
  const number = parseInt(value, 10) / 100

  // Atualiza o valor no modelo
  newCard.value[field] = number

  // Formata para exibição
  input.value = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

// Formata com R$ quando perde foco
function formatCurrencyOnBlur(event) {
  const input = event.target
  let value = input.value.replace(/\D/g, '')

  if (!value) {
    input.value = 'R$ 0,00'
    return
  }

  const number = parseInt(value, 10) / 100
  input.value = formatCurrency(number)
}

// Remove R$ quando ganha foco
function removeCurrencyOnFocus(event) {
  const input = event.target
  const value = input.value.replace('R$ ', '').replace('R$', '')
  input.value = value
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-BR')
}

function isOverdue(deadline) {
  return new Date(deadline) < new Date()
}

function getPriorityClass(priority) {
  const classes = {
    low: 'border-l-4 border-l-green-500',
    medium: 'border-l-4 border-l-yellow-500',
    high: 'border-l-4 border-l-orange-500',
    urgent: 'border-l-4 border-l-red-500 animate-pulse'
  }
  return classes[priority] || ''
}

function goToEdict() {
  if (selectedCard.value?.edict_id) {
    window.location.href = `/edicts/${selectedCard.value.edict_id}`
  }
}

onMounted(() => {
  fetchBoard()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
