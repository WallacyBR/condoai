<template>
  <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-md border-2 border-indigo-500/30 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 p-6 border-b border-gray-700/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
              <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-white">Informações da Correspondência</h2>
              <p class="text-sm text-gray-400">{{ formatApartmentDescription(pkg.apartment) }}</p>
            </div>
          </div>
          <button 
            @click="$emit('close')" 
            class="h-10 w-10 rounded-full bg-gray-700/50 hover:bg-gray-600/50 flex items-center justify-center transition-colors border border-gray-600/50 hover:border-gray-500/50"
          >
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(90vh-120px)]">
        <!-- Status Badge -->
        <div class="flex justify-center">
          <span class="px-4 py-2 rounded-full text-sm font-semibold"
                :class="getStatusBadgeClass(pkg)">
            {{ getStatusLabel(pkg) }}
          </span>
        </div>

        <!-- Foto da Correspondência -->
        <div v-if="pkg.photo" class="space-y-3">
          <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Foto da Correspondência</span>
          </h3>
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
            <img 
              :src="pkg.photo" 
              class="w-full h-auto rounded-lg shadow-lg object-cover max-h-64" 
              alt="Foto da correspondência"
            />
          </div>
        </div>

        <!-- Informações Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Data de Chegada -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
            <div class="flex items-center space-x-3 mb-2">
              <div class="h-8 w-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4M8 7h8M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2" />
                </svg>
              </div>
              <h4 class="font-semibold text-blue-300">Data de Chegada</h4>
            </div>
            <p class="text-white font-medium">{{ formatDate(pkg.created_at) }}</p>
          </div>

          <!-- Código -->
          <div v-if="pkg.code" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
            <div class="flex items-center space-x-3 mb-2">
              <div class="h-8 w-8 rounded-full bg-purple-500/20 flex items-center justify-center">
                <svg class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                </svg>
              </div>
              <h4 class="font-semibold text-purple-300">Código</h4>
            </div>
            <p class="text-white font-mono font-bold text-lg">{{ pkg.code }}</p>
          </div>

          <!-- Local de Retirada -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
            <div class="flex items-center space-x-3 mb-2">
              <div class="h-8 w-8 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h4 class="font-semibold text-green-300">Local de Retirada</h4>
            </div>
            <p class="text-white font-medium">{{ pkg.pickup_location }}</p>
          </div>

          <!-- Responsável -->
          <div v-if="pkg.user" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
            <div class="flex items-center space-x-3 mb-2">
              <div class="h-8 w-8 rounded-full bg-yellow-500/20 flex items-center justify-center">
                <svg class="h-4 w-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <h4 class="font-semibold text-yellow-300">Registrado por</h4>
            </div>
            <p class="text-white font-medium">{{ pkg.user.name }}</p>
          </div>
        </div>

        <!-- Observação -->
        <div v-if="pkg.observation" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
          <div class="flex items-center space-x-3 mb-3">
            <div class="h-8 w-8 rounded-full bg-orange-500/20 flex items-center justify-center">
              <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4M7 16h10M3 4h18a2 2 0 012 2v12a2 2 0 01-2 2H3a2 2 0 01-2-2V6a2 2 0 012-2z" />
              </svg>
            </div>
            <h4 class="font-semibold text-orange-300">Observação</h4>
          </div>
          <p class="text-white leading-relaxed">{{ pkg.observation }}</p>
        </div>

        <!-- Informações de Retirada -->
        <div v-if="pkg.retrieved_at" class="bg-gradient-to-br from-green-800/30 to-green-900/30 rounded-xl p-4 border border-green-500/30">
          <div class="flex items-center space-x-3 mb-3">
            <div class="h-8 w-8 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h4 class="font-semibold text-green-300">Informações de Retirada</h4>
          </div>
          <div class="space-y-2">
            <p class="text-white">
              <span class="text-green-300 font-medium">Data de Retirada:</span>
              {{ formatDate(pkg.retrieved_at) }}
            </p>
            <div v-if="pkg.signature" class="mt-4">
              <p class="text-green-300 font-medium mb-2">Assinatura:</p>
              <img :src="pkg.signature" class="w-full max-w-xs h-20 object-contain bg-white rounded-lg border" alt="Assinatura" />
            </div>
            <div v-if="pkg.document_photo" class="mt-4">
              <p class="text-green-300 font-medium mb-2">Documento:</p>
              <img :src="pkg.document_photo" class="w-full max-w-xs h-32 object-cover rounded-lg border" alt="Documento" />
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-gradient-to-r from-gray-800/50 to-gray-900/50 p-4 border-t border-gray-700/50">
        <div class="flex justify-end">
          <button 
            @click="$emit('close')" 
            class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { formatApartmentDescription } from '../utils/formatters';

export default {
  name: 'PackageInfoModal',
  props: { 
    pkg: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  setup() {
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const getStatusLabel = (pkg) => {
      return pkg.retrieved_at ? 'Retirada' : 'Pendente';
    };

    const getStatusBadgeClass = (pkg) => {
      if (pkg.retrieved_at) {
        return 'bg-green-500/20 text-green-300 border-2 border-green-500/50';
      }
      return 'bg-orange-500/20 text-orange-300 border-2 border-orange-500/50';
    };

    return { 
      formatApartmentDescription, 
      formatDate,
      getStatusLabel,
      getStatusBadgeClass
    };
  }
};
</script>
