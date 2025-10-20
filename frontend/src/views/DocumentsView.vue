<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            Documentos da Empresa
          </h1>
          <p class="text-gray-600 mt-1">Gerencie todos os documentos necessários para licitações</p>
        </div>
        <button
          @click="showUploadModal = true"
          class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Documento
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <button
          @click="filterByStatus('valid')"
          :class="statusFilter === 'valid' ? 'ring-4 ring-green-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Válidos</p>
              <p class="text-3xl font-bold text-green-600">{{ stats.valid }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </button>

        <button
          @click="filterByStatus('expiring_soon')"
          :class="statusFilter === 'expiring_soon' ? 'ring-4 ring-yellow-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Vencendo</p>
              <p class="text-3xl font-bold text-yellow-600">{{ stats.expiring }}</p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </button>

        <button
          @click="filterByStatus('expired')"
          :class="statusFilter === 'expired' ? 'ring-4 ring-red-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Vencidos</p>
              <p class="text-3xl font-bold text-red-600">{{ stats.expired }}</p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
        </button>

        <button
          @click="filterByStatus('')"
          :class="statusFilter === '' ? 'ring-4 ring-blue-300 scale-105' : 'hover:scale-105'"
          class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total</p>
              <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="documents.length === 0" class="bg-white rounded-xl shadow-md p-12 text-center">
      <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-2xl font-bold text-gray-800 mb-2">Nenhum documento encontrado</h3>
      <p class="text-gray-600 mb-6">Comece fazendo upload dos documentos da sua empresa</p>
      <button
        @click="showUploadModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition-all inline-flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Adicionar Primeiro Documento
      </button>
    </div>

    <!-- Search and Filters -->
    <div v-if="!loading && documents.length > 0" class="mb-6 space-y-4">
      <!-- Search Bar -->
      <div class="bg-white rounded-xl shadow-md p-4">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Search Input -->
          <div class="flex-1 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Pesquisar por nome, tipo ou categoria..."
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <svg class="w-6 h-6 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Advanced Filters Toggle -->
          <button
            @click="showAdvancedFilters = !showAdvancedFilters"
            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition-all flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Filtros Avançados
          </button>

          <!-- Clear Filters Button -->
          <button
            v-if="hasActiveFilters"
            @click="clearAllFilters"
            class="px-6 py-3 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-semibold transition-all flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar Filtros
          </button>
        </div>

        <!-- Advanced Filters Panel -->
        <div v-if="showAdvancedFilters" class="mt-4 pt-4 border-t grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Filter by Date Range -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Período de Validade</label>
            <select v-model="dateRangeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="">Todos os períodos</option>
              <option value="expired">Já vencidos</option>
              <option value="expiring_7">Vence em 7 dias</option>
              <option value="expiring_30">Vence em 30 dias</option>
              <option value="expiring_60">Vence em 60 dias</option>
              <option value="valid">Válidos (30+ dias)</option>
            </select>
          </div>

          <!-- Sort By -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Ordenar Por</label>
            <select v-model="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="created_at">Data de criação</option>
              <option value="document_name">Nome (A-Z)</option>
              <option value="expiry_date">Data de validade</option>
              <option value="status">Status</option>
            </select>
          </div>

          <!-- Sort Order -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Ordem</label>
            <select v-model="sortOrder" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="desc">Decrescente</option>
              <option value="asc">Crescente</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Category Filter Buttons -->
      <div class="flex gap-3 flex-wrap">
        <button
          @click="categoryFilter = ''"
          :class="categoryFilter === '' ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-200'"
          class="px-5 py-2.5 rounded-lg font-semibold transition-all hover:shadow-md flex items-center gap-2"
        >
          <span>📁</span>
          <span>Todos ({{ filteredDocuments.length }})</span>
        </button>
        <button
          v-for="(label, key) in documentCategories"
          :key="key"
          @click="categoryFilter = key"
          :class="categoryFilter === key ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-200'"
          class="px-5 py-2.5 rounded-lg font-semibold transition-all hover:shadow-md flex items-center gap-2"
        >
          <span>{{ getCategoryIconByKey(key) }}</span>
          <span>{{ label }} ({{ getCategoryCount(key) }})</span>
        </button>
      </div>

      <!-- Active Filters Summary -->
      <div v-if="hasActiveFilters" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-blue-900 mb-2">Filtros Ativos:</p>
            <div class="flex flex-wrap gap-2">
              <span v-if="statusFilter" class="px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-700">
                Status: {{ getStatusLabel(statusFilter) }}
              </span>
              <span v-if="categoryFilter" class="px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-700">
                Categoria: {{ documentCategories[categoryFilter] }}
              </span>
              <span v-if="searchQuery" class="px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-700">
                Busca: "{{ searchQuery }}"
              </span>
              <span v-if="dateRangeFilter" class="px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-700">
                Período: {{ getDateRangeLabel(dateRangeFilter) }}
              </span>
            </div>
            <p class="text-sm text-blue-700 mt-2">
              Mostrando <strong>{{ filteredDocuments.length }}</strong> de <strong>{{ documents.length }}</strong> documentos
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents Grid -->
    <div v-if="!loading && documents.length > 0" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="doc in filteredDocuments"
        :key="doc.id"
        class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all p-6 border-l-4"
        :class="getStatusBorderClass(doc.status)"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-2xl">{{ getCategoryIcon(doc.document_type) }}</span>
              <span class="px-2 py-1 bg-gray-100 rounded text-xs font-medium text-gray-600">
                {{ getCategoryLabel(doc.document_type) }}
              </span>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-1 truncate">{{ doc.document_name || 'Documento' }}</h3>
            <p class="text-sm text-gray-600">{{ getDocumentTypeLabel(doc.document_type) }}</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0" :class="getStatusClass(doc.status)">
            {{ getStatusLabel(doc.status) }}
          </span>
        </div>

        <div class="space-y-2 text-sm text-gray-600 mb-4">
          <div v-if="doc.expiry_date" class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Validade: {{ formatDate(doc.expiry_date) }}</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <button @click="viewDocument(doc)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Ver
          </button>
          <button @click="downloadDocument(doc)" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Baixar
          </button>
          <button @click="openEditModal(doc)" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Editar
          </button>
          <button @click="confirmDelete(doc)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showUploadModal = false">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full" @click.stop>
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 rounded-t-2xl flex items-center justify-between">
          <h2 class="text-2xl font-bold text-white">Novo Documento</h2>
          <button @click="showUploadModal = false" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <!-- Categoria de Documento -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Categoria *</label>
            <select v-model="selectedCategory" @change="uploadForm.document_type = ''" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="">Selecione a categoria...</option>
              <option v-for="(label, key) in documentCategories" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <!-- Tipo de Documento -->
          <div v-if="selectedCategory">
            <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Documento *</label>
            <select v-model="uploadForm.document_type" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
              <option value="">Selecione o tipo...</option>
              <option v-for="(label, key) in documentTypes[selectedCategory]" :key="key" :value="key">{{ label }}</option>
              <option value="custom">✏️ Outro tipo (especificar)</option>
            </select>
          </div>

          <!-- Campo customizado para tipo "Outros" -->
          <div v-if="uploadForm.document_type === 'custom'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <label class="block text-sm font-bold text-blue-900 mb-2">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Especifique o tipo de documento *
            </label>
            <input
              v-model="uploadForm.custom_document_type"
              type="text"
              placeholder="Ex: Certidão de Regularidade Ambiental"
              class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white"
            />
            <p class="text-xs text-blue-600 mt-2">
              Este tipo será salvo na categoria <strong>{{ documentCategories[selectedCategory] }}</strong>
            </p>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Nome do Documento *</label>
            <input v-model="uploadForm.document_name" type="text" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Ex: Certidão Federal - Janeiro 2025" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Data de Emissão</label>
              <input v-model="uploadForm.issue_date" type="date" class="w-full px-4 py-3 border rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Data de Validade</label>
              <input v-model="uploadForm.expiry_date" type="date" class="w-full px-4 py-3 border rounded-lg" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Arquivo *</label>
            <input ref="fileInput" type="file" @change="handleFileSelect" accept=".pdf,.doc,.docx" class="w-full px-4 py-3 border rounded-lg" />
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex gap-3 justify-end">
          <button @click="showUploadModal = false" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold">
            Cancelar
          </button>
          <button @click="uploadDocument" :disabled="uploading" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold disabled:opacity-50">
            {{ uploading ? 'Enviando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeEditModal">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full" @click.stop>
        <div class="bg-gradient-to-r from-yellow-600 to-orange-600 p-6 rounded-t-2xl flex items-center justify-between">
          <h2 class="text-2xl font-bold text-white">Editar Documento</h2>
          <button @click="closeEditModal" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <!-- Categoria de Documento -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Categoria *</label>
            <select v-model="editSelectedCategory" @change="editForm.document_type = ''" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-yellow-500">
              <option value="">Selecione a categoria...</option>
              <option v-for="(label, key) in documentCategories" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <!-- Tipo de Documento -->
          <div v-if="editSelectedCategory">
            <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Documento *</label>
            <select v-model="editForm.document_type" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-yellow-500">
              <option value="">Selecione o tipo...</option>
              <option v-for="(label, key) in documentTypes[editSelectedCategory]" :key="key" :value="key">{{ label }}</option>
              <option value="custom">✏️ Outro tipo (especificar)</option>
            </select>
          </div>

          <!-- Campo customizado para tipo "Outros" -->
          <div v-if="editForm.document_type === 'custom'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <label class="block text-sm font-bold text-yellow-900 mb-2">
              Especifique o tipo de documento *
            </label>
            <input
              v-model="editForm.custom_document_type"
              type="text"
              placeholder="Ex: Certidão de Regularidade Ambiental"
              class="w-full px-4 py-3 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
            />
            <p class="text-xs text-yellow-600 mt-2">
              Este tipo será salvo na categoria <strong>{{ documentCategories[editSelectedCategory] }}</strong>
            </p>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Nome do Documento *</label>
            <input v-model="editForm.document_name" type="text" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-yellow-500" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Data de Emissão</label>
              <input v-model="editForm.issue_date" type="date" class="w-full px-4 py-3 border rounded-lg" />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Data de Validade</label>
              <input v-model="editForm.expiry_date" type="date" class="w-full px-4 py-3 border rounded-lg" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">
              Substituir Arquivo (Opcional)
            </label>
            <div class="space-y-2">
              <div class="text-sm text-gray-600">
                Arquivo atual: <strong>{{ editForm.file_name }}</strong>
              </div>
              <input ref="editFileInput" type="file" @change="handleEditFileSelect" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full px-4 py-3 border rounded-lg" />
              <p class="text-xs text-gray-500">Deixe em branco para manter o arquivo atual</p>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex gap-3 justify-end">
          <button @click="closeEditModal" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold">
            Cancelar
          </button>
          <button @click="updateDocument" :disabled="updating" class="px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-bold disabled:opacity-50">
            {{ updating ? 'Salvando...' : 'Salvar Alterações' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'

const documents = ref([])
const loading = ref(true)
const showUploadModal = ref(false)
const uploading = ref(false)
const showEditModal = ref(false)
const updating = ref(false)

const uploadForm = ref({
  document_type: '',
  document_name: '',
  issue_date: '',
  expiry_date: '',
  file: null,
  custom_document_type: ''
})

const editForm = ref({
  id: null,
  document_type: '',
  document_name: '',
  issue_date: '',
  expiry_date: '',
  file_name: '',
  file: null,
  custom_document_type: ''
})

const documentCategories = ref({})
const documentTypes = ref({})
const selectedCategory = ref('')
const editSelectedCategory = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const searchQuery = ref('')
const dateRangeFilter = ref('')
const sortBy = ref('created_at')
const sortOrder = ref('desc')
const showAdvancedFilters = ref(false)

const hasActiveFilters = computed(() => {
  return !!(statusFilter.value || categoryFilter.value || searchQuery.value || dateRangeFilter.value)
})

const filteredDocuments = computed(() => {
  let filtered = [...documents.value]

  // Filter by status
  if (statusFilter.value) {
    filtered = filtered.filter(doc => doc.status === statusFilter.value)
  }

  // Filter by category
  if (categoryFilter.value) {
    filtered = filtered.filter(doc => {
      const docCategory = getDocumentCategory(doc)
      return docCategory === categoryFilter.value
    })
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(doc => {
      const name = (doc.document_name || '').toLowerCase()
      const type = getDocumentTypeLabel(doc.document_type).toLowerCase()
      const category = getCategoryLabel(doc.document_type).toLowerCase()
      return name.includes(query) || type.includes(query) || category.includes(query)
    })
  }

  // Filter by date range
  if (dateRangeFilter.value) {
    const now = new Date()
    filtered = filtered.filter(doc => {
      if (!doc.expiry_date) return false
      const expiryDate = new Date(doc.expiry_date)
      const daysUntilExpiry = Math.ceil((expiryDate - now) / (1000 * 60 * 60 * 24))

      switch (dateRangeFilter.value) {
        case 'expired':
          return daysUntilExpiry < 0
        case 'expiring_7':
          return daysUntilExpiry >= 0 && daysUntilExpiry <= 7
        case 'expiring_30':
          return daysUntilExpiry >= 0 && daysUntilExpiry <= 30
        case 'expiring_60':
          return daysUntilExpiry >= 0 && daysUntilExpiry <= 60
        case 'valid':
          return daysUntilExpiry > 30
        default:
          return true
      }
    })
  }

  // Sort documents
  filtered.sort((a, b) => {
    let aValue, bValue

    switch (sortBy.value) {
      case 'document_name':
        aValue = (a.document_name || '').toLowerCase()
        bValue = (b.document_name || '').toLowerCase()
        break
      case 'expiry_date':
        aValue = a.expiry_date ? new Date(a.expiry_date) : new Date('9999-12-31')
        bValue = b.expiry_date ? new Date(b.expiry_date) : new Date('9999-12-31')
        break
      case 'status':
        aValue = a.status || ''
        bValue = b.status || ''
        break
      case 'created_at':
      default:
        aValue = new Date(a.created_at || 0)
        bValue = new Date(b.created_at || 0)
    }

    if (sortOrder.value === 'asc') {
      return aValue > bValue ? 1 : -1
    } else {
      return aValue < bValue ? 1 : -1
    }
  })

  return filtered
})

const stats = computed(() => ({
  total: documents.value.length,
  valid: documents.value.filter(d => d.status === 'valid').length,
  expiring: documents.value.filter(d => d.status === 'expiring_soon').length,
  expired: documents.value.filter(d => d.status === 'expired').length
}))

async function fetchDocuments() {
  loading.value = true
  try {
    const response = await api.get('/company-documents')
    documents.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Erro ao carregar documentos:', err)
  } finally {
    loading.value = false
  }
}

async function fetchDocumentTypes() {
  try {
    const response = await api.get('/company-documents/types')
    documentCategories.value = response.data.categories || {}
    documentTypes.value = response.data.types || {}
  } catch (err) {
    console.error('Erro ao carregar tipos de documentos:', err)
  }
}

function handleFileSelect(event) {
  uploadForm.value.file = event.target.files[0]
}

function handleEditFileSelect(event) {
  editForm.value.file = event.target.files[0]
}

function openEditModal(doc) {
  // Verificar se o documento é de tipo customizado
  const category = getDocumentCategory(doc)
  const isCustomType = !isKnownDocumentType(doc.document_type)

  // Preencher o formulário com dados do documento
  editForm.value = {
    id: doc.id,
    document_type: isCustomType ? 'custom' : doc.document_type,
    document_name: doc.document_name,
    issue_date: doc.issue_date || '',
    expiry_date: doc.expiry_date || '',
    file_name: doc.file_name || doc.document_name,
    file: null,
    custom_document_type: isCustomType ? doc.document_type : ''
  }

  // Definir categoria selecionada
  editSelectedCategory.value = category

  // Abrir modal
  showEditModal.value = true
}

function closeEditModal() {
  showEditModal.value = false
  editForm.value = {
    id: null,
    document_type: '',
    document_name: '',
    issue_date: '',
    expiry_date: '',
    file_name: '',
    file: null,
    custom_document_type: ''
  }
  editSelectedCategory.value = ''
}

async function updateDocument() {
  if (!editForm.value.document_name || !editForm.value.document_type) {
    alert('⚠️ Preencha todos os campos obrigatórios')
    return
  }

  // Validar tipo customizado
  if (editForm.value.document_type === 'custom' && !editForm.value.custom_document_type) {
    alert('⚠️ Por favor, especifique o tipo de documento')
    return
  }

  updating.value = true
  try {
    const formData = new FormData()

    // Se for tipo customizado, usar o valor do campo custom_document_type
    const documentType = editForm.value.document_type === 'custom'
      ? editForm.value.custom_document_type
      : editForm.value.document_type

    // Adicionar campos do formulário
    formData.append('document_type', documentType)
    formData.append('document_name', editForm.value.document_name)
    formData.append('company_id', 1)

    // Se for tipo customizado, enviar também a categoria selecionada
    if (editForm.value.document_type === 'custom' && editSelectedCategory.value) {
      formData.append('custom_category', editSelectedCategory.value)
    }

    if (editForm.value.issue_date) formData.append('issue_date', editForm.value.issue_date)
    if (editForm.value.expiry_date) formData.append('expiry_date', editForm.value.expiry_date)

    // Adicionar novo arquivo se foi selecionado
    if (editForm.value.file) {
      formData.append('file', editForm.value.file)
    }

    // Fazer requisição PUT
    await api.post(`/company-documents/${editForm.value.id}?_method=PUT`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    closeEditModal()
    alert('✅ Documento atualizado com sucesso!')
    fetchDocuments()
  } catch (err) {
    console.error('Erro ao atualizar documento:', err)
    const errorMsg = err.response?.data?.message || 'Erro desconhecido'
    alert(`❌ Erro ao atualizar: ${errorMsg}`)
  } finally {
    updating.value = false
  }
}

async function confirmDelete(doc) {
  const confirmed = confirm(
    `⚠️ Tem certeza que deseja excluir o documento "${doc.document_name}"?\n\nEsta ação não pode ser desfeita.`
  )

  if (!confirmed) return

  try {
    await api.delete(`/company-documents/${doc.id}`)
    alert('✅ Documento excluído com sucesso!')
    fetchDocuments()
  } catch (err) {
    console.error('Erro ao excluir documento:', err)
    const errorMsg = err.response?.data?.message || 'Erro desconhecido'
    alert(`❌ Erro ao excluir: ${errorMsg}`)
  }
}

async function uploadDocument() {
  if (!uploadForm.value.file) return

  // Validar tipo customizado
  if (uploadForm.value.document_type === 'custom' && !uploadForm.value.custom_document_type) {
    alert('⚠️ Por favor, especifique o tipo de documento')
    return
  }

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', uploadForm.value.file)

    // Se for tipo customizado, usar o valor do campo custom_document_type
    const documentType = uploadForm.value.document_type === 'custom'
      ? uploadForm.value.custom_document_type
      : uploadForm.value.document_type

    formData.append('document_type', documentType)
    formData.append('document_name', uploadForm.value.document_name)
    formData.append('company_id', 1)

    // Se for tipo customizado, enviar também a categoria selecionada
    if (uploadForm.value.document_type === 'custom' && selectedCategory.value) {
      formData.append('custom_category', selectedCategory.value)
    }

    if (uploadForm.value.issue_date) formData.append('issue_date', uploadForm.value.issue_date)
    if (uploadForm.value.expiry_date) formData.append('expiry_date', uploadForm.value.expiry_date)

    await api.post('/company-documents', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    showUploadModal.value = false
    uploadForm.value = { document_type: '', document_name: '', issue_date: '', expiry_date: '', file: null, custom_document_type: '' }
    selectedCategory.value = ''
    alert('✅ Documento enviado com sucesso!')
    fetchDocuments()
  } catch (err) {
    console.error('Erro:', err)
    alert('❌ Erro ao enviar documento')
  } finally {
    uploading.value = false
  }
}

function getStatusLabel(status) {
  const labels = { valid: '✓ Válido', expiring_soon: '⚠ Vencendo', expired: '✗ Vencido', pending_review: '⏳ Pendente' }
  return labels[status] || status
}

function getStatusClass(status) {
  const classes = { valid: 'bg-green-100 text-green-800', expiring_soon: 'bg-yellow-100 text-yellow-800', expired: 'bg-red-100 text-red-800', pending_review: 'bg-blue-100 text-blue-800' }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusBorderClass(status) {
  const classes = { valid: 'border-green-500', expiring_soon: 'border-yellow-500', expired: 'border-red-500', pending_review: 'border-blue-500' }
  return classes[status] || 'border-gray-500'
}

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('pt-BR') : '-'
}

function getDocumentCategory(documentOrType) {
  // Se for um objeto (documento), usar o campo category se disponível
  if (typeof documentOrType === 'object' && documentOrType.category) {
    return documentOrType.category
  }

  // Se for string (document_type), procurar na tabela
  const documentType = typeof documentOrType === 'object' ? documentOrType.document_type : documentOrType

  for (const [category, types] of Object.entries(documentTypes.value)) {
    if (types && types[documentType]) {
      return category
    }
  }
  return 'Outros'
}

function getCategoryLabel(documentOrType) {
  const category = getDocumentCategory(documentOrType)
  return documentCategories.value[category] || 'Outros'
}

function getCategoryIcon(documentOrType) {
  const category = getDocumentCategory(documentOrType)
  const icons = {
    'Certidoes': '📜',
    'Habilitacao': '📋',
    'Atestados': '✓',
    'Outros': '📄'
  }
  return icons[category] || '📄'
}

function getDocumentTypeLabel(documentType) {
  for (const types of Object.values(documentTypes.value)) {
    if (types && types[documentType]) {
      return types[documentType]
    }
  }
  return documentType
}

function isKnownDocumentType(documentType) {
  // Verificar se o tipo de documento existe na lista de tipos predefinidos
  for (const types of Object.values(documentTypes.value)) {
    if (types && types[documentType]) {
      return true
    }
  }
  return false
}

function getCategoryIconByKey(categoryKey) {
  const icons = {
    'Certidoes': '📜',
    'Habilitacao': '📋',
    'Atestados': '✓',
    'Outros': '📄'
  }
  return icons[categoryKey] || '📄'
}

function getCategoryCount(categoryKey) {
  return documents.value.filter(doc => {
    const category = getDocumentCategory(doc)
    return category === categoryKey
  }).length
}

function filterByStatus(status) {
  statusFilter.value = statusFilter.value === status ? '' : status
  categoryFilter.value = ''
}

function clearAllFilters() {
  statusFilter.value = ''
  categoryFilter.value = ''
  searchQuery.value = ''
  dateRangeFilter.value = ''
  sortBy.value = 'created_at'
  sortOrder.value = 'desc'
}

function getDateRangeLabel(range) {
  const labels = {
    'expired': 'Já vencidos',
    'expiring_7': 'Vence em 7 dias',
    'expiring_30': 'Vence em 30 dias',
    'expiring_60': 'Vence em 60 dias',
    'valid': 'Válidos (30+ dias)'
  }
  return labels[range] || range
}

async function viewDocument(doc) {
  try {
    // Baixar o arquivo com autenticação
    const response = await api.get(`/company-documents/${doc.id}/download`, {
      responseType: 'blob'
    })

    // Verificar se recebeu dados
    if (!response.data || response.data.size === 0) {
      alert('❌ Arquivo não encontrado ou vazio')
      return
    }

    // Criar URL temporária e abrir em nova aba
    const blob = new Blob([response.data], { type: doc.mime_type || 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    window.open(url, '_blank')

    // Limpar URL após 1 minuto
    setTimeout(() => window.URL.revokeObjectURL(url), 60000)
  } catch (err) {
    console.error('Erro ao visualizar documento:', err)

    if (err.response?.status === 401) {
      alert('❌ Sessão expirada. Faça login novamente.')
    } else if (err.response?.status === 404) {
      alert('❌ Arquivo não encontrado no servidor')
    } else {
      const errorMsg = err.response?.data?.message || err.message || 'Erro desconhecido'
      alert(`❌ Erro ao visualizar: ${errorMsg}`)
    }
  }
}

async function downloadDocument(doc) {
  try {
    const response = await api.get(`/company-documents/${doc.id}/download`, {
      responseType: 'blob'
    })

    // Verificar se recebeu dados
    if (!response.data || response.data.size === 0) {
      alert('❌ Arquivo não encontrado ou vazio')
      return
    }

    // Criar link de download
    const blob = new Blob([response.data], { type: doc.mime_type || 'application/octet-stream' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url

    // Determinar nome do arquivo
    const fileName = doc.file_name || `${doc.document_name}.pdf`
    link.setAttribute('download', fileName)

    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    // Mensagem de sucesso
    alert('✅ Download iniciado com sucesso!')
  } catch (err) {
    console.error('Erro ao baixar documento:', err)

    if (err.response?.status === 401) {
      alert('❌ Sessão expirada. Faça login novamente.')
    } else if (err.response?.status === 404) {
      alert('❌ Arquivo não encontrado no servidor')
    } else {
      const errorMsg = err.response?.data?.message || err.message || 'Erro desconhecido'
      alert(`❌ Erro ao baixar: ${errorMsg}`)
    }
  }
}

onMounted(() => {
  fetchDocuments()
  fetchDocumentTypes()
})
</script>
