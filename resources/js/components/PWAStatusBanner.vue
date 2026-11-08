<template>
  <div v-if="needRefresh || offlineReady" class="fixed bottom-4 right-4 z-50">
    <!-- Banner de atualização disponível -->
    <div v-if="needRefresh" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-4 rounded-lg shadow-lg max-w-sm">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div>
          <div>
            <h3 class="font-semibold">Nova versão disponível</h3>
            <p class="text-sm opacity-90">Clique para atualizar a aplicação</p>
          </div>
        </div>
        <button 
          @click="updateServiceWorker"
          class="bg-white text-cyan-600 px-3 py-1 rounded-md text-sm font-medium hover:bg-gray-100 transition-colors"
        >
          Atualizar
        </button>
      </div>
    </div>

    <!-- Banner de pronto para offline -->
    <div v-else-if="offlineReady" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4 rounded-lg shadow-lg max-w-sm">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <h3 class="font-semibold">Pronto para offline</h3>
            <p class="text-sm opacity-90">Aplicação pode ser usada sem internet</p>
          </div>
        </div>
        <button 
          @click="offlineReady = false"
          class="bg-white text-green-600 px-3 py-1 rounded-md text-sm font-medium hover:bg-gray-100 transition-colors"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { usePWA } from '../composables/usePWA';

export default {
  name: 'PWAStatusBanner',
  setup() {
    const { needRefresh, offlineReady, updateServiceWorker } = usePWA();

    return {
      needRefresh,
      offlineReady,
      updateServiceWorker
    };
  }
};
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style> 