<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-6">
    <!-- Header with Gradient -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
          Editais Inteligentes
        </h1>
        <p class="text-gray-600">Análise automática com Inteligência Artificial</p>
      </div>
      <button
        @click="showUploadModal = true"
        class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        Novo Edital
      </button>
    </div>

    <!-- Stats Cards with Modern Design -->
    <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-blue-100 hover:border-blue-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total de Editais</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total }}</p>
          </div>
          <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-3 rounded-xl shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-green-100 hover:border-green-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Recomendados</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.recommended }}</p>
          </div>
          <div class="bg-gradient-to-br from-green-400 to-green-600 p-3 rounded-xl shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-yellow-100 hover:border-yellow-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Processamento</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ stats.processing }}</p>
          </div>
          <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-3 rounded-xl shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-purple-100 hover:border-purple-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Valor Total</p>
            <p class="text-2xl font-bold text-purple-600 mt-2">{{ formatCurrency(stats.total_estimated_value) }}</p>
          </div>
          <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-3 rounded-xl shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters with Modern Design -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-100">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        <h3 class="text-lg font-semibold text-gray-800">Filtros</h3>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="relative">
          <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            type="text"
            placeholder="Buscar editais..."
            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>

        <select
          v-model="filters.status"
          @change="fetchEdicts"
          class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white"
        >
          <option value="">📋 Todos os Status</option>
          <option value="draft">📝 Rascunho</option>
          <option value="imported">📥 Importado</option>
          <option value="analyzed">🔍 Analisado</option>
          <option value="participated">✅ Participado</option>
          <option value="closed">🔒 Fechado</option>
        </select>

        <select
          v-model="filters.worth_participating"
          @change="fetchEdicts"
          class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white"
        >
          <option value="">⭐ Todas Recomendações</option>
          <option value="true">✅ Recomendados</option>
          <option value="false">❌ Não Recomendados</option>
        </select>

        <select
          v-model="filters.category"
          @change="fetchEdicts"
          class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white"
        >
          <option value="">🏷️ Todas Categorias</option>
          <option value="Tecnologia">💻 Tecnologia</option>
          <option value="Serviços">🛠️ Serviços</option>
          <option value="Infraestrutura">🏗️ Infraestrutura</option>
          <option value="Saúde">🏥 Saúde</option>
          <option value="Consultoria">💼 Consultoria</option>
          <option value="Segurança">🔒 Segurança</option>
          <option value="Design">🎨 Design</option>
        </select>
      </div>
    </div>

    <!-- Loading with Modern Animation -->
    <div v-if="loading" class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-100">
      <div class="relative w-16 h-16 mx-auto mb-4">
        <div class="absolute inset-0 border-4 border-blue-200 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
      </div>
      <p class="text-gray-600 font-medium">Carregando editais inteligentes...</p>
      <p class="text-gray-400 text-sm mt-2">Aguarde enquanto buscamos os dados</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg shadow p-8 text-center">
      <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="text-xl font-bold text-red-700 mb-2">Erro ao Carregar Editais</h3>
      <p class="text-red-600 mb-4">{{ error }}</p>
      <div class="space-y-2">
        <button
          @click="fetchEdicts(); fetchStats()"
          class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition"
        >
          Tentar Novamente
        </button>
        <p class="text-sm text-red-500">Verifique o console do navegador (F12) para mais detalhes</p>
      </div>
    </div>

    <!-- Edicts List with Modern Cards -->
    <div v-else-if="edicts.length > 0" class="space-y-6">
      <div
        v-for="edict in edicts"
        :key="edict.id"
        class="bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 p-6 border border-gray-100 hover:border-blue-200 transform hover:-translate-y-1"
      >
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-3">
              <h3 class="text-2xl font-bold text-gray-800">{{ edict.edict_number }}</h3>
              <span
                :class="getStatusColor(edict.status)"
                class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
              >
                {{ getStatusLabel(edict.status) }}
              </span>
              <span
                v-if="edict.worth_participating !== null"
                :class="edict.worth_participating ? 'bg-gradient-to-r from-green-400 to-green-600 text-white shadow-md' : 'bg-gradient-to-r from-red-400 to-red-600 text-white shadow-md'"
                class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1"
              >
                <span v-if="edict.worth_participating">✓</span>
                <span v-else>✗</span>
                {{ edict.worth_participating ? 'Recomendado' : 'Não Recomendado' }}
              </span>
            </div>

            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <p class="text-gray-700 font-bold text-lg">{{ edict.organ }}</p>
            </div>
            <p class="text-gray-600 mb-4 leading-relaxed">{{ edict.description || edict.object_description || 'Sem descrição' }}</p>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-3 rounded-lg border border-gray-200">
                <p class="text-gray-500 text-xs font-medium uppercase tracking-wide mb-1">Categoria</p>
                <p class="font-bold text-gray-800">{{ edict.category || '-' }}</p>
              </div>
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-3 rounded-lg border border-blue-200">
                <p class="text-blue-600 text-xs font-medium uppercase tracking-wide mb-1">Valor Estimado</p>
                <p class="font-bold text-blue-700">{{ formatCurrency(edict.estimated_value) }}</p>
              </div>
              <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-3 rounded-lg border border-purple-200">
                <p class="text-purple-600 text-xs font-medium uppercase tracking-wide mb-1">Abertura</p>
                <p class="font-bold text-purple-700">{{ formatDate(edict.opening_date) }}</p>
              </div>
              <div v-if="edict.ai_score" class="bg-gradient-to-br from-green-50 to-green-100 p-3 rounded-lg border border-green-200">
                <p class="text-green-600 text-xs font-medium uppercase tracking-wide mb-1">AI Score</p>
                <p class="font-bold text-green-700">{{ edict.ai_score }}/100</p>
              </div>
            </div>
          </div>

          <div class="flex lg:flex-col gap-2">
            <button
              @click="viewDetails(edict)"
              class="flex-1 lg:flex-none bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-5 py-3 rounded-lg text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Detalhes
            </button>
            <button
              v-if="edict.file_path"
              @click="downloadPDF(edict.id)"
              class="flex-1 lg:flex-none bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-lg text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              PDF
            </button>
            <button
              @click="confirmDelete(edict)"
              class="flex-1 lg:flex-none bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-lg text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="bg-white rounded-lg shadow p-4 flex justify-center gap-2">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="changePage(page)"
          :class="page === pagination.current_page ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700'"
          class="px-4 py-2 rounded-lg font-semibold hover:opacity-80 transition"
        >
          {{ page }}
        </button>
      </div>
    </div>

    <!-- Empty State with Modern Design -->
    <div v-else class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-lg p-16 text-center border border-blue-100">
      <div class="bg-gradient-to-br from-blue-100 to-purple-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
        <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
        Nenhum edital encontrado
      </h3>
      <p class="text-gray-600 mb-6 max-w-md mx-auto leading-relaxed">
        Faça upload do primeiro edital em PDF e deixe nossa IA analisar automaticamente viabilidade, custos e recomendações!
      </p>
      <button
        @click="showUploadModal = true"
        class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-4 rounded-lg font-bold transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1 inline-flex items-center gap-3"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        Fazer Primeiro Upload
      </button>
    </div>

    <!-- Details Modal with Modern Design -->
    <div v-if="selectedEdict" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-2 sm:p-4 overflow-y-auto" @click="selectedEdict = null">
      <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8 max-w-7xl w-full my-4 sm:my-8 max-h-[95vh] overflow-y-auto transform transition-all" @click.stop>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start justify-between mb-4 sm:mb-6 pb-4 sm:pb-6 border-b border-gray-200 gap-3">
          <div class="flex-1 w-full">
            <div class="flex items-center gap-2 sm:gap-3 mb-2">
              <div class="bg-gradient-to-br from-blue-400 to-purple-600 p-2 sm:p-3 rounded-lg sm:rounded-xl flex-shrink-0">
                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <h2 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-800 truncate">{{ selectedEdict.edict_number }}</h2>
                <p class="text-xs sm:text-sm text-gray-500 truncate">{{ selectedEdict.organ }}</p>
              </div>
            </div>
            <div class="flex flex-wrap gap-1.5 sm:gap-2 mt-2 sm:mt-3">
              <span :class="getStatusColor(selectedEdict.status)" class="px-2 sm:px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide whitespace-nowrap">
                {{ getStatusLabel(selectedEdict.status) }}
              </span>
              <span v-if="selectedEdict.worth_participating !== null"
                :class="selectedEdict.worth_participating ? 'bg-gradient-to-r from-green-400 to-green-600 text-white shadow-md' : 'bg-gradient-to-r from-red-400 to-red-600 text-white shadow-md'"
                class="px-2 sm:px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1 whitespace-nowrap">
                <span v-if="selectedEdict.worth_participating">✓</span>
                <span v-else>✗</span>
                {{ selectedEdict.worth_participating ? 'Recomendado' : 'Não Recomendado' }}
              </span>
              <span v-if="selectedEdict.category" class="bg-blue-100 text-blue-700 px-2 sm:px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap">
                {{ selectedEdict.category }}
              </span>
            </div>
          </div>
          <button @click="selectedEdict = null" class="absolute top-2 right-2 sm:relative sm:top-0 sm:right-0 text-gray-400 hover:text-gray-600 transition p-2 bg-white sm:bg-transparent rounded-full sm:rounded-none">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="space-y-4 sm:space-y-6 max-h-[70vh] overflow-y-auto pr-1 sm:pr-2">
          <!-- Recomendação da IA -->
          <div v-if="selectedEdict.participation_recommendation" class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-3 sm:p-4 lg:p-6 border border-blue-200">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
              <div class="flex items-center gap-2 flex-1">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <h3 class="text-base sm:text-lg font-bold text-gray-800">Recomendação da IA</h3>
              </div>
              <span v-if="selectedEdict.ai_score" class="bg-white px-3 py-1 rounded-full text-xs sm:text-sm font-bold text-purple-600 shadow-md self-start sm:self-auto">
                Score: {{ selectedEdict.ai_score }}/100
              </span>
            </div>
            <div class="bg-white rounded-lg p-3 sm:p-4 whitespace-pre-line text-sm sm:text-base text-gray-700 leading-relaxed">{{ selectedEdict.participation_recommendation }}</div>
          </div>

          <!-- Informações Gerais -->
          <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 lg:p-6">
            <div class="flex items-center gap-2 mb-3 sm:mb-4">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 class="text-base sm:text-lg font-bold text-gray-800">Informações Gerais</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
              <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Número do Processo</p>
                <p class="font-semibold text-sm sm:text-base text-gray-800 break-all">{{ selectedEdict.process_number || '-' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">UASG</p>
                <p class="font-semibold text-sm sm:text-base text-gray-800">{{ selectedEdict.uasg_number || '-' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Modalidade</p>
                <p class="font-semibold text-sm sm:text-base text-gray-800">{{ selectedEdict.modality || '-' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Categoria</p>
                <p class="font-semibold text-sm sm:text-base text-gray-800">{{ selectedEdict.category || '-' }}</p>
              </div>
            </div>
            <div v-if="selectedEdict.object_description" class="mt-3 sm:mt-4 bg-gray-50 rounded-lg p-3 sm:p-4">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Objeto</p>
              <p class="text-sm sm:text-base text-gray-700 leading-relaxed">{{ selectedEdict.object_description }}</p>
            </div>
          </div>

          <!-- Prazos e Datas -->
          <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 lg:p-6">
            <div class="flex items-center gap-2 mb-3 sm:mb-4">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <h3 class="text-base sm:text-lg font-bold text-gray-800">Prazos e Datas</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
              <div class="bg-blue-50 rounded-lg p-3 sm:p-4 border border-blue-200">
                <p class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-1">Publicação</p>
                <p class="font-bold text-sm sm:text-base text-blue-700">{{ formatDate(selectedEdict.publication_date) }}</p>
              </div>
              <div class="bg-purple-50 rounded-lg p-3 sm:p-4 border border-purple-200">
                <p class="text-xs font-medium text-purple-600 uppercase tracking-wide mb-1">Prazo Proposta</p>
                <p class="font-bold text-sm sm:text-base text-purple-700">{{ formatDate(selectedEdict.proposal_deadline) }}</p>
              </div>
              <div class="bg-green-50 rounded-lg p-3 sm:p-4 border border-green-200">
                <p class="text-xs font-medium text-green-600 uppercase tracking-wide mb-1">Abertura</p>
                <p class="font-bold text-sm sm:text-base text-green-700">{{ formatDate(selectedEdict.opening_date) }}</p>
              </div>
              <div class="bg-yellow-50 rounded-lg p-3 sm:p-4 border border-yellow-200">
                <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide mb-1">Sessão Pública</p>
                <p class="font-bold text-sm sm:text-base text-yellow-700">{{ formatDate(selectedEdict.session_date) }}</p>
              </div>
              <div class="bg-red-50 rounded-lg p-3 sm:p-4 border border-red-200 sm:col-span-2 lg:col-span-1">
                <p class="text-xs font-medium text-red-600 uppercase tracking-wide mb-1">Encerramento</p>
                <p class="font-bold text-sm sm:text-base text-red-700">{{ formatDate(selectedEdict.closing_date) }}</p>
              </div>
            </div>
          </div>

          <!-- Análise Financeira Detalhada -->
          <div class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 rounded-2xl border-2 border-blue-200 p-4 sm:p-6 lg:p-8 shadow-xl">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-blue-500 to-purple-600 p-3 rounded-xl shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Análise Financeira</h3>
                  <p class="text-xs sm:text-sm text-gray-600">Visão completa de custos e viabilidade</p>
                </div>
              </div>
              <div class="hidden sm:block">
                <svg class="w-12 h-12 text-blue-200" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
                </svg>
              </div>
            </div>

            <!-- Resumo Visual Principais -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">
              <!-- Valor Estimado do Edital -->
              <div class="bg-white rounded-xl p-4 sm:p-5 shadow-lg border-2 border-blue-300 transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs font-bold text-blue-600 uppercase tracking-wide">Valor Edital</p>
                  <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-blue-700">{{ formatCurrency(selectedEdict.estimated_value) }}</p>
                <p class="text-xs text-gray-500 mt-1">Valor máximo aceito</p>
              </div>

              <!-- Seu Lance -->
              <div class="bg-white rounded-xl p-4 sm:p-5 shadow-lg border-2 border-green-300 transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs font-bold text-green-600 uppercase tracking-wide">Seu Lance</p>
                  <div class="bg-green-100 p-2 rounded-lg">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                  </div>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-green-700">{{ formatCurrency(selectedEdict.bid_value) }}</p>
                <p v-if="selectedEdict.estimated_value && selectedEdict.bid_value" class="text-xs mt-1" :class="getDiscountPercentage(selectedEdict) > 0 ? 'text-green-600 font-semibold' : 'text-orange-600 font-semibold'">
                  {{ getDiscountPercentage(selectedEdict) > 0 ? '↓' : '↑' }} {{ Math.abs(getDiscountPercentage(selectedEdict)).toFixed(1) }}% do edital
                </p>
              </div>

              <!-- Lucro Líquido Estimado -->
              <div class="bg-white rounded-xl p-4 sm:p-5 shadow-lg border-2 border-purple-300 transform hover:scale-105 transition-all sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs font-bold text-purple-600 uppercase tracking-wide">Lucro Líquido</p>
                  <div class="bg-purple-100 p-2 rounded-lg">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                  </div>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-purple-700">{{ formatCurrency(calculateNetProfit(selectedEdict)) }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all" :style="`width: ${selectedEdict.profit_margin || 0}%`"></div>
                  </div>
                  <p class="text-xs font-bold text-purple-700">{{ selectedEdict.profit_margin || 0 }}%</p>
                </div>
              </div>
            </div>

            <!-- Breakdown de Custos -->
            <div class="bg-white rounded-xl p-4 sm:p-5 shadow-md mb-6">
              <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h4 class="text-base sm:text-lg font-bold text-gray-800">Composição de Custos</h4>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Mão de Obra -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-3 sm:p-4 border border-amber-200">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="bg-amber-200 p-1.5 rounded">
                      <svg class="w-3 h-3 text-amber-700" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                      </svg>
                    </div>
                    <p class="text-xs font-bold text-amber-700 uppercase">Mão de Obra</p>
                  </div>
                  <p class="text-lg sm:text-xl font-bold text-amber-900">{{ formatCurrency(selectedEdict.labor_cost) }}</p>
                  <p v-if="selectedEdict.total_investment" class="text-xs text-amber-600 mt-1">{{ getCostPercentage(selectedEdict.labor_cost, selectedEdict.total_investment) }}% do total</p>
                </div>

                <!-- Material -->
                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg p-3 sm:p-4 border border-cyan-200">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="bg-cyan-200 p-1.5 rounded">
                      <svg class="w-3 h-3 text-cyan-700" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/>
                      </svg>
                    </div>
                    <p class="text-xs font-bold text-cyan-700 uppercase">Material</p>
                  </div>
                  <p class="text-lg sm:text-xl font-bold text-cyan-900">{{ formatCurrency(selectedEdict.material_cost) }}</p>
                  <p v-if="selectedEdict.total_investment" class="text-xs text-cyan-600 mt-1">{{ getCostPercentage(selectedEdict.material_cost, selectedEdict.total_investment) }}% do total</p>
                </div>

                <!-- Impostos -->
                <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-lg p-3 sm:p-4 border border-red-200">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="bg-red-200 p-1.5 rounded">
                      <svg class="w-3 h-3 text-red-700" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                      </svg>
                    </div>
                    <p class="text-xs font-bold text-red-700 uppercase">Impostos</p>
                  </div>
                  <p class="text-lg sm:text-xl font-bold text-red-900">{{ formatCurrency(selectedEdict.tax_cost) }}</p>
                  <p v-if="selectedEdict.total_investment" class="text-xs text-red-600 mt-1">{{ getCostPercentage(selectedEdict.tax_cost, selectedEdict.total_investment) }}% do total</p>
                </div>

                <!-- Investimento Total -->
                <div class="bg-gradient-to-br from-slate-50 to-gray-100 rounded-lg p-3 sm:p-4 border-2 border-slate-300">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="bg-slate-300 p-1.5 rounded">
                      <svg class="w-3 h-3 text-slate-700" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                      </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700 uppercase">Total Custos</p>
                  </div>
                  <p class="text-lg sm:text-xl font-bold text-slate-900">{{ formatCurrency(selectedEdict.total_investment) }}</p>
                  <p class="text-xs text-slate-600 mt-1">Investimento necessário</p>
                </div>
              </div>
            </div>

            <!-- Insights Financeiros -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 sm:p-5 border border-indigo-200">
              <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <h4 class="text-sm sm:text-base font-bold text-indigo-900">Insights Financeiros</h4>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <!-- ROI -->
                <div class="bg-white rounded-lg p-3 shadow-sm">
                  <p class="text-xs text-gray-600 mb-1">Retorno sobre Investimento (ROI)</p>
                  <p class="text-xl font-bold" :class="calculateROI(selectedEdict) > 20 ? 'text-green-600' : calculateROI(selectedEdict) > 10 ? 'text-yellow-600' : 'text-red-600'">
                    {{ calculateROI(selectedEdict).toFixed(1) }}%
                  </p>
                </div>

                <!-- Margem Líquida -->
                <div class="bg-white rounded-lg p-3 shadow-sm">
                  <p class="text-xs text-gray-600 mb-1">Margem Líquida</p>
                  <p class="text-xl font-bold text-purple-600">{{ selectedEdict.profit_margin || 0 }}%</p>
                </div>

                <!-- Break-even -->
                <div class="bg-white rounded-lg p-3 shadow-sm sm:col-span-2 lg:col-span-1">
                  <p class="text-xs text-gray-600 mb-1">Ponto de Equilíbrio</p>
                  <p class="text-xl font-bold text-blue-600">{{ formatCurrency(selectedEdict.total_investment) }}</p>
                </div>
              </div>

              <!-- Recomendação Financeira -->
              <div v-if="getFinancialRecommendation(selectedEdict)" class="mt-4 p-3 rounded-lg" :class="getFinancialRecommendation(selectedEdict).class">
                <div class="flex items-start gap-2">
                  <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :class="getFinancialRecommendation(selectedEdict).iconClass" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                  </svg>
                  <div>
                    <p class="font-bold text-sm mb-1" :class="getFinancialRecommendation(selectedEdict).textClass">{{ getFinancialRecommendation(selectedEdict).title }}</p>
                    <p class="text-xs" :class="getFinancialRecommendation(selectedEdict).textClass">{{ getFinancialRecommendation(selectedEdict).message }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Requisitos e Compliance -->
          <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0 mb-3 sm:mb-4">
              <div class="flex items-center gap-2 flex-1">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-base sm:text-lg font-bold text-gray-800">Requisitos e Documentação</h3>
              </div>
              <span v-if="selectedEdict.company_compliance" class="px-3 py-1 rounded-full text-xs font-bold self-start sm:self-auto"
                :class="selectedEdict.company_compliance.percentage >= 100 ? 'bg-green-100 text-green-700' : selectedEdict.company_compliance.percentage >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'">
                {{ selectedEdict.company_compliance.percentage }}% Completo
              </span>
            </div>

            <!-- Requisitos -->
            <div v-if="selectedEdict.requirements && selectedEdict.requirements.length" class="mb-3 sm:mb-4">
              <p class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Requisitos do Edital:</p>
              <div class="flex flex-wrap gap-1.5 sm:gap-2">
                <span v-for="(req, i) in selectedEdict.requirements" :key="i" class="bg-blue-100 text-blue-700 px-2 sm:px-3 py-1 rounded-lg text-xs sm:text-sm">
                  {{ req }}
                </span>
              </div>
            </div>

            <!-- Documentos Disponíveis -->
            <div v-if="selectedEdict.company_compliance?.available && selectedEdict.company_compliance.available.length" class="mb-3 sm:mb-4">
              <p class="text-xs sm:text-sm font-semibold text-green-700 mb-2">✓ Documentos Disponíveis:</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <div v-for="doc in selectedEdict.company_compliance.available" :key="doc.type" class="bg-green-50 border border-green-200 rounded-lg p-2 sm:p-3 flex items-center gap-2">
                  <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-xs sm:text-sm text-green-700 font-medium truncate">{{ doc.name }}</span>
                </div>
              </div>
            </div>

            <!-- Documentos Faltantes -->
            <div v-if="selectedEdict.missing_requirements && selectedEdict.missing_requirements.length">
              <p class="text-xs sm:text-sm font-semibold text-red-700 mb-2">✗ Documentos Faltantes:</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <div v-for="doc in selectedEdict.missing_requirements" :key="doc.type" class="bg-red-50 border border-red-200 rounded-lg p-2 sm:p-3 flex items-center gap-2">
                  <svg class="w-3 h-3 sm:w-4 sm:h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span class="text-xs sm:text-sm text-red-700 font-medium truncate">{{ doc.name }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Links -->
          <div v-if="selectedEdict.bidding_portal_url || selectedEdict.source_url" class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 lg:p-6">
            <div class="flex items-center gap-2 mb-3 sm:mb-4">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
              <h3 class="text-base sm:text-lg font-bold text-gray-800">Links Importantes</h3>
            </div>
            <div class="space-y-2 sm:space-y-3">
              <a v-if="selectedEdict.bidding_portal_url && isValidUrl(selectedEdict.bidding_portal_url)" :href="ensureAbsoluteUrl(selectedEdict.bidding_portal_url)" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-3 sm:p-4 transition group">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="text-sm sm:text-base text-blue-700 font-medium group-hover:underline truncate">Portal de Licitações</span>
              </a>
              <a v-if="selectedEdict.source_url && isValidUrl(selectedEdict.source_url)" :href="ensureAbsoluteUrl(selectedEdict.source_url)" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-3 sm:p-4 transition group">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="text-sm sm:text-base text-purple-700 font-medium group-hover:underline truncate">Fonte do Edital</span>
              </a>
              <p v-if="(!selectedEdict.bidding_portal_url || !isValidUrl(selectedEdict.bidding_portal_url)) && (!selectedEdict.source_url || !isValidUrl(selectedEdict.source_url))" class="text-sm text-gray-500 italic p-3">Nenhum link externo cadastrado</p>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
          <button v-if="selectedEdict.file_path" @click="downloadPDF(selectedEdict.id)" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-4 sm:px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm sm:text-base">Baixar Documento</span>
          </button>
          <div v-else class="flex-1 bg-gray-100 text-gray-500 px-4 sm:px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 cursor-not-allowed">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm sm:text-base">Documento não disponível</span>
          </div>
          <button @click="confirmDelete(selectedEdict)" class="sm:flex-none bg-red-600 hover:bg-red-700 text-white px-4 sm:px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="text-sm sm:text-base">Excluir</span>
          </button>
          <button @click="selectedEdict = null" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 sm:px-6 py-3 rounded-xl font-bold transition-all">
            <span class="text-sm sm:text-base">Fechar</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full transform transition-all" @click.stop>
        <div class="flex items-center gap-3 mb-6">
          <div class="bg-gradient-to-br from-red-400 to-red-600 p-3 rounded-xl">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Confirmar Exclusão</h2>
            <p class="text-sm text-gray-500">Esta ação não pode ser desfeita</p>
          </div>
        </div>

        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
          <p class="text-gray-700 mb-3">Tem certeza que deseja excluir este edital?</p>
          <div v-if="edictToDelete" class="bg-white rounded-lg p-3 border border-red-200">
            <p class="font-bold text-gray-800">{{ edictToDelete.edict_number }}</p>
            <p class="text-sm text-gray-600 mt-1">{{ edictToDelete.organ }}</p>
          </div>
          <p class="text-sm text-red-600 font-semibold mt-3">⚠️ O arquivo e todos os dados associados serão permanentemente removidos.</p>
        </div>

        <div class="flex gap-3">
          <button
            @click="deleteEdict"
            :disabled="deleting"
            class="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <div v-if="deleting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            {{ deleting ? 'Excluindo...' : 'Sim, Excluir' }}
          </button>
          <button
            @click="cancelDelete"
            :disabled="deleting"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Modal with Modern Design -->
    <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-lg w-full transform transition-all" @click.stop>
        <div class="flex items-center gap-3 mb-6">
          <div class="bg-gradient-to-br from-blue-400 to-purple-600 p-3 rounded-xl">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Upload de Edital</h2>
            <p class="text-sm text-gray-500">Envie um PDF para análise inteligente</p>
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 font-semibold mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Selecione o Arquivo do Edital
          </label>
          <div class="border-2 border-dashed border-gray-300 hover:border-blue-400 rounded-xl p-6 text-center transition cursor-pointer bg-gradient-to-br from-gray-50 to-blue-50" @click="$refs.fileInput?.click()">
            <input
              type="file"
              ref="fileInput"
              @change="handleFileSelect"
              accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
              class="hidden"
            />
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p v-if="!selectedFile" class="text-gray-600 font-medium">Clique para selecionar um arquivo</p>
            <div v-else class="flex flex-col items-center gap-2">
              <div class="flex items-center justify-center gap-2 text-blue-600 font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ selectedFile.name }}
              </div>
              <p class="text-xs text-gray-500">{{ getFileType(selectedFile.name) }} - {{ formatFileSize(selectedFile.size) }}</p>
            </div>
            <div class="mt-3 flex flex-wrap justify-center gap-2">
              <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">PDF</span>
              <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">Word</span>
              <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Excel</span>
              <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">CSV</span>
              <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">TXT</span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Máximo 50MB</p>
          </div>
        </div>

        <div v-if="uploading" class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-semibold text-blue-700">{{ uploadMessage }}</p>
            <p class="text-sm font-bold text-blue-700">{{ uploadProgress }}%</p>
          </div>
          <div class="w-full bg-blue-200 rounded-full h-3 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-300 shadow-lg" :style="`width: ${uploadProgress}%`"></div>
          </div>
        </div>

        <div class="flex gap-3">
          <button
            @click="uploadEdict"
            :disabled="!selectedFile || uploading"
            class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            {{ uploading ? 'Enviando...' : 'Fazer Upload' }}
          </button>
          <button
            @click="closeUploadModal"
            :disabled="uploading"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Review Modal - Revisão e Edição de Dados Antes de Salvar -->
    <div v-if="showReviewModal && reviewData" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center z-50 p-2 sm:p-4 overflow-y-auto">
      <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8 max-w-6xl w-full my-4 sm:my-8 max-h-[95vh] overflow-y-auto transform transition-all" @click.stop>
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 -m-4 sm:-m-6 lg:-m-8 mb-6 p-4 sm:p-6 rounded-t-xl sm:rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl sm:text-3xl font-bold text-white mb-1">Revisar Dados do Edital</h2>
              <p class="text-blue-100 text-sm sm:text-base">Confira e ajuste os dados extraídos pela IA antes de salvar</p>
            </div>
            <button
              @click="closeReviewModal"
              class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Informações Básicas -->
        <div class="bg-blue-50 rounded-xl p-4 sm:p-6 mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Informações Básicas
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Número do Edital *</label>
              <input
                v-model="reviewData.edict_number"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Ex: 001/2025"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Número do Processo</label>
              <input
                v-model="reviewData.process_number"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Ex: 23106.001234/2025-78"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">UASG</label>
              <input
                v-model="reviewData.uasg_number"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Ex: 110244"
              />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Órgão Licitante *</label>
              <input
                v-model="reviewData.organ"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Ex: Prefeitura Municipal de São Paulo"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Modalidade</label>
              <select
                v-model="reviewData.modality"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Selecione...</option>
                <option value="Pregão Eletrônico">Pregão Eletrônico</option>
                <option value="Pregão Presencial">Pregão Presencial</option>
                <option value="Concorrência">Concorrência</option>
                <option value="Tomada de Preços">Tomada de Preços</option>
                <option value="Convite">Convite</option>
                <option value="Dispensa">Dispensa</option>
                <option value="Inexigibilidade">Inexigibilidade</option>
              </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Objeto da Licitação *</label>
              <textarea
                v-model="reviewData.object_description"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Descreva resumidamente o objeto da licitação..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Valores e Custos - SEÇÃO CRÍTICA -->
        <div class="bg-gradient-to-br from-purple-50 via-pink-50 to-orange-50 rounded-xl p-4 sm:p-6 mb-6 border-2 border-purple-200">
          <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Valores e Custos (Editáveis com Recálculo Automático)
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 shadow-sm">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Valor Estimado do Edital</label>
              <input
                v-model="reviewData.estimated_value"
                @input="handleCurrencyInput($event, 'estimated_value')"
                @blur="formatCurrencyField($event, 'estimated_value')"
                @focus="removeCurrencyOnFocus"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                placeholder="R$ 0,00"
              />
              <p class="text-xs text-gray-500 mt-1">Valor estimado pelo órgão</p>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 shadow-sm border border-amber-200">
              <label class="block text-sm font-semibold text-amber-800 mb-2">Custo de Mão de Obra</label>
              <input
                v-model="reviewData.labor_cost"
                @input="handleCurrencyInput($event, 'labor_cost')"
                @blur="formatCurrencyField($event, 'labor_cost')"
                @focus="removeCurrencyOnFocus"
                type="text"
                class="w-full px-4 py-2 border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                placeholder="R$ 0,00"
              />
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 shadow-sm border border-blue-200">
              <label class="block text-sm font-semibold text-blue-800 mb-2">Custo de Material</label>
              <input
                v-model="reviewData.material_cost"
                @input="handleCurrencyInput($event, 'material_cost')"
                @blur="formatCurrencyField($event, 'material_cost')"
                @focus="removeCurrencyOnFocus"
                type="text"
                class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="R$ 0,00"
              />
            </div>

            <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-lg p-4 shadow-sm border border-red-200">
              <label class="block text-sm font-semibold text-red-800 mb-2">Impostos e Taxas</label>
              <input
                v-model="reviewData.tax_cost"
                @input="handleCurrencyInput($event, 'tax_cost')"
                @blur="formatCurrencyField($event, 'tax_cost')"
                @focus="removeCurrencyOnFocus"
                type="text"
                class="w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="R$ 0,00"
              />
            </div>

            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 shadow-sm border border-green-200">
              <label class="block text-sm font-semibold text-green-800 mb-2">Margem de Lucro (%)</label>
              <input
                v-model.number="reviewData.profit_margin"
                @input="recalculateFinancials"
                type="number"
                step="0.1"
                min="0"
                max="100"
                class="w-full px-4 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="0.0"
              />
            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Investimento Total (Auto)</label>
              <input
                :value="formatCurrency(reviewData.total_investment || 0)"
                type="text"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 font-bold"
              />
              <p class="text-xs text-gray-500 mt-1">Calculado automaticamente</p>
            </div>

            <div class="bg-gradient-to-br from-purple-100 to-purple-50 rounded-lg p-4 shadow-md border-2 border-purple-300">
              <label class="block text-sm font-bold text-purple-800 mb-2">Valor da Proposta (Auto)</label>
              <input
                :value="formatCurrency(reviewData.bid_value || 0)"
                type="text"
                disabled
                class="w-full px-4 py-2 bg-white border-2 border-purple-400 rounded-lg text-purple-700 font-bold text-lg"
              />
              <p class="text-xs text-purple-600 mt-1">Com margem de lucro aplicada</p>
            </div>

            <div class="bg-gradient-to-br from-blue-100 to-blue-50 rounded-lg p-4 shadow-md border-2 border-blue-300">
              <label class="block text-sm font-bold text-blue-800 mb-2">Desconto (%) Auto</label>
              <input
                :value="(reviewData.discount_percentage || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%'"
                type="text"
                disabled
                class="w-full px-4 py-2 bg-white border-2 border-blue-400 rounded-lg text-blue-700 font-bold text-lg"
              />
              <p class="text-xs text-blue-600 mt-1">Desconto sobre valor estimado</p>
            </div>
          </div>

          <!-- Preview dos Cálculos -->
          <div class="bg-white rounded-lg p-4 border-2 border-purple-300">
            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              Preview Financeiro
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
              <div>
                <p class="text-gray-600">Lucro Estimado:</p>
                <p class="font-bold text-green-600 text-lg">
                  {{ formatCurrency((reviewData.bid_value || 0) - (reviewData.total_investment || 0)) }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">ROI Estimado:</p>
                <p class="font-bold text-purple-600 text-lg">
                  {{ reviewData.total_investment > 0 ? (((reviewData.bid_value - reviewData.total_investment) / reviewData.total_investment) * 100).toFixed(1) : '0.0' }}%
                </p>
              </div>
              <div>
                <p class="text-gray-600">Competitividade:</p>
                <p class="font-bold text-blue-600 text-lg">
                  {{ reviewData.discount_percentage > 0 ? reviewData.discount_percentage.toFixed(1) + '% de desconto' : 'Sem desconto' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Análise e Recomendação -->
        <div class="bg-gray-50 rounded-xl p-4 sm:p-6 mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Análise da IA
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Score da IA (0-100)</label>
              <input
                v-model.number="reviewData.ai_score"
                type="number"
                min="0"
                max="100"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Recomendação da IA</label>
              <textarea
                v-model="reviewData.ai_recommendation"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Recomendações da inteligência artificial..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t-2 border-gray-200">
          <button
            @click="saveReviewedEdict(true)"
            :disabled="savingReview"
            class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-4 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="!savingReview" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            {{ savingReview ? 'Salvando...' : 'Salvar e PARTICIPAR' }}
          </button>
          <button
            @click="saveReviewedEdict(false)"
            :disabled="savingReview"
            class="flex-1 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-white px-6 py-4 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="!savingReview" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            {{ savingReview ? 'Salvando...' : 'Salvar e NÃO PARTICIPAR' }}
          </button>
          <button
            @click="closeReviewModal"
            :disabled="savingReview"
            class="sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-4 rounded-xl font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'
import { formatCurrency as formatCurrencyUtil, parseCurrency, applyCurrencyMask, formatCurrencyOnBlur, removeCurrencyOnFocus } from '../utils/currency'
import { useDebounce } from '../composables/useDebounce'

const edicts = ref([])
const stats = ref(null)
const loading = ref(true)
const error = ref(null)
const showUploadModal = ref(false)
const selectedFile = ref(null)
const uploading = ref(false)
const uploadProgress = ref(0)
const uploadMessage = ref('')
const fileInput = ref(null)
const selectedEdict = ref(null)
const showDeleteModal = ref(false)
const edictToDelete = ref(null)
const deleting = ref(false)

// Review modal states
const showReviewModal = ref(false)
const reviewData = ref(null)
const savingReview = ref(false)

const filters = ref({
  search: '',
  status: '',
  worth_participating: '',
  category: '',
  page: 1
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
})

async function fetchEdicts() {
  loading.value = true
  error.value = null
  try {
    const params = new URLSearchParams()
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.status) params.append('status', filters.value.status)
    if (filters.value.worth_participating) params.append('worth_participating', filters.value.worth_participating)
    if (filters.value.category) params.append('category', filters.value.category)
    params.append('page', filters.value.page)

    console.log('🔍 Buscando editais com token:', localStorage.getItem('auth_token') ? 'Token presente' : 'Token ausente')

    const response = await api.get(`/edicts?${params}`)

    console.log('✅ Editais carregados:', response.data)

    edicts.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total
    }
  } catch (err) {
    console.error('❌ Erro ao carregar editais:', err)
    console.error('❌ Detalhes do erro:', {
      message: err.message,
      response: err.response?.data,
      status: err.response?.status,
      headers: err.response?.headers
    })
    error.value = err.response?.data?.message || err.message || 'Erro ao carregar editais'
  } finally {
    loading.value = false
  }
}

// Debounce para a busca - evita requests excessivos
const debouncedSearch = useDebounce(() => {
  filters.value.page = 1 // Reset para primeira página ao buscar
  fetchEdicts()
}, 500)

async function fetchStats() {
  try {
    console.log('📊 Buscando estatísticas...')
    const response = await api.get('/edicts/stats')
    console.log('✅ Estatísticas carregadas:', response.data)
    stats.value = response.data.stats
  } catch (err) {
    console.error('❌ Erro ao carregar estatísticas:', err)
    console.error('❌ Detalhes:', err.response?.data)
  }
}

function handleFileSelect(event) {
  selectedFile.value = event.target.files[0]
}

async function uploadEdict() {
  if (!selectedFile.value) return

  uploading.value = true
  uploadProgress.value = 0
  uploadMessage.value = 'Enviando arquivo...'

  const formData = new FormData()
  formData.append('pdf', selectedFile.value)
  formData.append('company_id', 1) // TODO: Pegar do usuário logado

  try {
    uploadProgress.value = 30
    uploadMessage.value = 'Analisando com IA...'

    console.log('📤 Fazendo upload de edital...')

    const response = await api.post('/edicts/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        uploadProgress.value = Math.min(percentCompleted, 90)
      }
    })

    uploadProgress.value = 100
    uploadMessage.value = 'Concluído!'

    console.log('✅ Upload concluído:', response.data)

    closeUploadModal()

    // Verificar se requer revisão
    if (response.data.requires_review && response.data.analysis) {
      // Abrir modal de revisão com dados editáveis
      reviewData.value = {
        edict_id: response.data.edict_id,
        file_path: response.data.file_path,
        ...response.data.analysis
      }
      showReviewModal.value = true
    } else {
      // Fluxo antigo - salvar direto
      await fetchEdicts()
      await fetchStats()

      // Abrir modal de detalhes automaticamente
      if (response.data.edict) {
        selectedEdict.value = response.data.edict
      }
    }
  } catch (err) {
    console.error('❌ Erro ao fazer upload:', err)
    console.error('❌ Detalhes completos:', {
      message: err.message,
      response: err.response?.data,
      status: err.response?.status
    })

    let errorMessage = 'Erro ao fazer upload: '
    let canRetry = false

    if (err.response?.data?.message) {
      errorMessage = err.response.data.message
      canRetry = err.response.data.can_retry || false
    } else if (err.response?.data?.error) {
      errorMessage = 'Erro ao fazer upload: ' + err.response.data.error
    } else if (err.message) {
      errorMessage = 'Erro ao fazer upload: ' + err.message
    } else {
      errorMessage = 'Erro desconhecido ao fazer upload. Verifique o console (F12) para mais detalhes.'
    }

    // Exibir erro com detalhes técnicos no console
    if (err.response?.data?.error) {
      console.error('📋 Detalhes técnicos:', err.response.data.error)
    }

    // Mostrar mensagem amigável
    if (canRetry) {
      const retry = confirm(`${errorMessage}\n\nDeseja tentar novamente?`)
      if (retry) {
        uploadEdict()
        return
      }
    } else {
      alert(errorMessage)
    }
  } finally {
    uploading.value = false
  }
}

function closeUploadModal() {
  showUploadModal.value = false
  selectedFile.value = null
  uploadProgress.value = 0
  uploadMessage.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

// Recalcular valores automaticamente quando custos mudam
function recalculateFinancials() {
  if (!reviewData.value) return

  // Calcular investimento total
  const laborCost = parseFloat(reviewData.value.labor_cost) || 0
  const materialCost = parseFloat(reviewData.value.material_cost) || 0
  const taxCost = parseFloat(reviewData.value.tax_cost) || 0

  reviewData.value.total_investment = laborCost + materialCost + taxCost

  // Calcular valor da proposta baseado na margem de lucro
  const profitMargin = parseFloat(reviewData.value.profit_margin) || 0
  if (profitMargin > 0 && reviewData.value.total_investment > 0) {
    reviewData.value.bid_value = reviewData.value.total_investment * (1 + profitMargin / 100)
  }

  // Recalcular desconto se houver valor estimado
  if (reviewData.value.estimated_value && reviewData.value.bid_value) {
    const discount = ((reviewData.value.estimated_value - reviewData.value.bid_value) / reviewData.value.estimated_value) * 100
    reviewData.value.discount_percentage = Math.max(0, discount)
  }
}

// Validar campos obrigatórios
function validateReviewData() {
  if (!reviewData.value) return { valid: false, message: 'Dados não encontrados' }

  const errors = []

  if (!reviewData.value.edict_number || reviewData.value.edict_number.trim() === '') {
    errors.push('Número do Edital é obrigatório')
  }

  if (!reviewData.value.organ || reviewData.value.organ.trim() === '') {
    errors.push('Órgão Licitante é obrigatório')
  }

  if (!reviewData.value.object_description || reviewData.value.object_description.trim() === '') {
    errors.push('Objeto da Licitação é obrigatório')
  }

  // Validações de valores numéricos
  if (reviewData.value.estimated_value && reviewData.value.estimated_value < 0) {
    errors.push('Valor Estimado não pode ser negativo')
  }

  if (reviewData.value.labor_cost && reviewData.value.labor_cost < 0) {
    errors.push('Custo de Mão de Obra não pode ser negativo')
  }

  if (reviewData.value.material_cost && reviewData.value.material_cost < 0) {
    errors.push('Custo de Material não pode ser negativo')
  }

  if (reviewData.value.tax_cost && reviewData.value.tax_cost < 0) {
    errors.push('Impostos não podem ser negativos')
  }

  if (reviewData.value.profit_margin && (reviewData.value.profit_margin < 0 || reviewData.value.profit_margin > 100)) {
    errors.push('Margem de Lucro deve estar entre 0% e 100%')
  }

  if (errors.length > 0) {
    return {
      valid: false,
      message: 'Corrija os seguintes erros:\n\n• ' + errors.join('\n• ')
    }
  }

  return { valid: true }
}

// Salvar edital após revisão
async function saveReviewedEdict(participar = true) {
  if (!reviewData.value) return

  // Validar dados antes de salvar
  const validation = validateReviewData()
  if (!validation.valid) {
    alert(validation.message)
    return
  }

  savingReview.value = true

  try {
    const dataToSave = {
      ...reviewData.value,
      worth_participating: participar,
      status: participar ? 'analyzed' : 'draft',
      processing_status: 'completed',
      processed_at: new Date().toISOString()
    }

    console.log('💾 Salvando edital revisado:', dataToSave)

    const response = await api.put(`/edicts/${reviewData.value.edict_id}`, dataToSave)

    console.log('✅ Edital salvo:', response.data)

    // Fechar modal de revisão
    showReviewModal.value = false
    reviewData.value = null

    // Atualizar lista
    await fetchEdicts()
    await fetchStats()

    // Se escolheu PARTICIPAR, redirecionar para o Kanban de Licitações
    if (participar) {
      alert('✅ Edital salvo! Redirecionando para o painel de Licitações...')
      // Aguardar um pouco para o usuário ver a mensagem
      setTimeout(() => {
        window.location.href = '/tenders'
      }, 1500)
    } else {
      alert('✅ Edital salvo com sucesso! Você optou por NÃO PARTICIPAR desta licitação.')
    }

  } catch (err) {
    console.error('❌ Erro ao salvar edital:', err)

    // Tratamento especial para edital duplicado
    if (err.response?.data?.error === 'duplicate_edict_number') {
      alert('⚠️ ATENÇÃO: ' + err.response.data.message + '\n\nEste edital já foi cadastrado anteriormente. Verifique a lista de editais.')
    } else {
      alert('❌ Erro ao salvar edital: ' + (err.response?.data?.message || err.message))
    }
  } finally {
    savingReview.value = false
  }
}

function closeReviewModal() {
  showReviewModal.value = false
  reviewData.value = null
}

function changePage(page) {
  filters.value.page = page
  fetchEdicts()
}

function viewDetails(edict) {
  selectedEdict.value = edict
}

async function downloadPDF(id) {
  try {
    console.log('📥 Baixando PDF do edital:', id)
    const response = await api.get(`/edicts/${id}/download`, {
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `edital_${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    console.log('✅ PDF baixado com sucesso')
  } catch (err) {
    console.error('❌ Erro ao baixar PDF:', err)
    alert('Erro ao baixar PDF. O arquivo pode não estar disponível.')
  }
}

function isValidUrl(url) {
  if (!url || typeof url !== 'string') return false
  try {
    // Aceita URLs absolutas (http/https) ou relativas que comecem com /
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) {
      return true
    }
    // Tenta criar um objeto URL para validar
    new URL(url)
    return true
  } catch {
    return false
  }
}

function ensureAbsoluteUrl(url) {
  if (!url) return '#'
  // Se já é uma URL absoluta, retorna como está
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url
  }
  // Se é uma URL relativa que começa com /, considera como caminho do backend
  if (url.startsWith('/')) {
    return `${import.meta.env.VITE_API_URL}${url}`
  }
  // Se não tem protocolo, adiciona https://
  return `https://${url}`
}

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
    reviewData.value[field] = 0
    input.value = ''
    recalculateFinancials()
    return
  }

  // Converte para número com centavos
  const number = parseInt(value, 10) / 100

  // Atualiza o valor no modelo
  reviewData.value[field] = number

  // Formata para exibição
  input.value = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)

  // Recalcula valores
  recalculateFinancials()
}

// Formata com R$ quando perde foco
function formatCurrencyField(event, field) {
  const input = event.target
  const value = reviewData.value[field] || 0
  input.value = formatCurrency(value)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR')
}

function getStatusLabel(status) {
  const labels = {
    draft: 'Rascunho',
    imported: 'Importado',
    analyzed: 'Analisado',
    participated: 'Participado',
    closed: 'Fechado'
  }
  return labels[status] || status
}

function getStatusColor(status) {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    imported: 'bg-blue-100 text-blue-800',
    analyzed: 'bg-green-100 text-green-800',
    participated: 'bg-purple-100 text-purple-800',
    closed: 'bg-red-100 text-red-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

function getFileType(filename) {
  const extension = filename.split('.').pop().toLowerCase()
  const types = {
    'pdf': 'PDF',
    'doc': 'Word 97-2003',
    'docx': 'Microsoft Word',
    'xls': 'Excel 97-2003',
    'xlsx': 'Microsoft Excel',
    'csv': 'CSV',
    'txt': 'Texto'
  }
  return types[extension] || extension.toUpperCase()
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// Funções de cálculo financeiro
function calculateNetProfit(edict) {
  if (!edict.bid_value || !edict.total_investment) return 0
  return edict.bid_value - edict.total_investment
}

function calculateROI(edict) {
  if (!edict.bid_value || !edict.total_investment || edict.total_investment === 0) return 0
  const profit = edict.bid_value - edict.total_investment
  return (profit / edict.total_investment) * 100
}

function getDiscountPercentage(edict) {
  if (!edict.estimated_value || !edict.bid_value || edict.estimated_value === 0) return 0
  return ((edict.estimated_value - edict.bid_value) / edict.estimated_value) * 100
}

function getCostPercentage(cost, total) {
  if (!cost || !total || total === 0) return 0
  return ((cost / total) * 100).toFixed(1)
}

function getFinancialRecommendation(edict) {
  const roi = calculateROI(edict)
  const margin = edict.profit_margin || 0

  if (roi >= 20 && margin >= 15) {
    return {
      title: 'Excelente Oportunidade Financeira! 🎯',
      message: `ROI de ${roi.toFixed(1)}% e margem de ${margin}% indicam alta rentabilidade. Recomendação: PARTICIPAR com prioridade alta.`,
      class: 'bg-green-50 border border-green-200',
      textClass: 'text-green-800',
      iconClass: 'text-green-600'
    }
  } else if (roi >= 10 && margin >= 8) {
    return {
      title: 'Boa Oportunidade de Negócio ⚡',
      message: `ROI de ${roi.toFixed(1)}% e margem de ${margin}% são satisfatórios. Recomendação: AVALIAR participação conforme capacidade operacional.`,
      class: 'bg-yellow-50 border border-yellow-200',
      textClass: 'text-yellow-800',
      iconClass: 'text-yellow-600'
    }
  } else if (roi > 0) {
    return {
      title: 'Oportunidade com Baixo Retorno ⚠️',
      message: `ROI de ${roi.toFixed(1)}% e margem de ${margin}% são limitados. Recomendação: Participar apenas se houver interesse estratégico.`,
      class: 'bg-orange-50 border border-orange-200',
      textClass: 'text-orange-800',
      iconClass: 'text-orange-600'
    }
  } else {
    return {
      title: 'Risco Financeiro Elevado 🚫',
      message: `ROI negativo ou margem insuficiente. Custos podem superar receita. Recomendação: NÃO PARTICIPAR - rever estimativa de custos.`,
      class: 'bg-red-50 border border-red-200',
      textClass: 'text-red-800',
      iconClass: 'text-red-600'
    }
  }
}

function confirmDelete(edict) {
  edictToDelete.value = edict
  showDeleteModal.value = true
  // Fechar modal de detalhes se estiver aberto
  selectedEdict.value = null
}

function cancelDelete() {
  showDeleteModal.value = false
  edictToDelete.value = null
}

async function deleteEdict() {
  if (!edictToDelete.value) return

  deleting.value = true

  try {
    console.log('🗑️ Excluindo edital:', edictToDelete.value.id)

    await api.delete(`/edicts/${edictToDelete.value.id}`)

    console.log('✅ Edital excluído com sucesso')

    // Fechar modal
    showDeleteModal.value = false
    edictToDelete.value = null

    // Recarregar lista
    await fetchEdicts()
    await fetchStats()

    // Mostrar mensagem de sucesso (opcional)
    alert('Edital excluído com sucesso!')
  } catch (err) {
    console.error('❌ Erro ao excluir edital:', err)
    console.error('❌ Detalhes:', err.response?.data)

    let errorMessage = 'Erro ao excluir edital: '
    if (err.response?.data?.message) {
      errorMessage += err.response.data.message
    } else if (err.response?.data?.error) {
      errorMessage += err.response.data.error
    } else if (err.message) {
      errorMessage += err.message
    } else {
      errorMessage += 'Erro desconhecido'
    }

    alert(errorMessage)
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchEdicts()
  fetchStats()
})
</script>
