<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-light">
    <div class="text-center">
      <h1 class="text-6xl font-bold text-primary">404</h1>
      <p class="text-xl text-gray-600 mt-4">Página não encontrada</p>
      <button @click="router.push('/')"
        class="mt-8 px-6 py-3 bg-primary text-white rounded-lg hover:bg-blue-700 transition"
      >
        Voltar ao Dashboard
      </button>
    </div>
  </div>
</template>
