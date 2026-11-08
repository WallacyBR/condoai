<template>
  <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200 dark:border-indigo-800/40">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Informações da Correspondência</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatApartmentDescription(pkg.apartment) }}</p>
          </div>
        </div>
        <button 
          @click="$emit('close')" 
          class="h-9 w-9 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors"
          title="Fechar"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6 overflow-y-auto flex-1 text-slate-700 dark:text-slate-300">
        <!-- Status Badge -->
        <div class="flex justify-center">
          <span class="px-3.5 py-1.5 rounded-full text-xs font-semibold"
                :class="getStatusBadgeClass(pkg)">
            {{ getStatusLabel(pkg) }}
          </span>
        </div>

        <!-- Foto da Correspondência -->
        <div v-if="pkg.photo" class="space-y-2">
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 flex items-center space-x-2">
            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Foto da Correspondência</span>
          </h3>
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-2 border border-slate-200 dark:border-slate-800">
            <img 
              :src="pkg.photo" 
              class="w-full h-auto rounded-md object-cover max-h-64 mx-auto" 
              alt="Foto da correspondência"
            />
          </div>
        </div>

        <!-- Informações Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Data de Chegada -->
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-800">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Data de Chegada</h4>
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ formatDate(pkg.created_at) }}</p>
          </div>

          <!-- Código -->
          <div v-if="pkg.code" class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-800">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Código de Rastreio</h4>
            <p class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ pkg.code }}</p>
          </div>

          <!-- Local de Retirada -->
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-800">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Local de Retirada</h4>
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ pkg.pickup_location }}</p>
          </div>

          <!-- Responsável -->
          <div v-if="pkg.user" class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-800">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Registrado por</h4>
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ pkg.user.name }}</p>
          </div>
        </div>

        <!-- Observação -->
        <div v-if="pkg.observation" class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-800">
          <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Observação</h4>
          <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ pkg.observation }}</p>
        </div>

        <!-- Informações de Retirada -->
        <div v-if="pkg.retrieved_at" class="bg-emerald-50/50 dark:bg-emerald-950/20 rounded-lg p-4 border border-emerald-200 dark:border-emerald-800/40">
          <h4 class="text-xs font-semibold text-emerald-800 dark:text-emerald-400 mb-2">Informações de Retirada</h4>
          <p class="text-sm text-slate-800 dark:text-slate-200">
            <span class="font-medium text-slate-500 dark:text-slate-400">Data:</span>
            {{ formatDate(pkg.retrieved_at) }}
          </p>
          <div v-if="pkg.signature" class="mt-3">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Assinatura:</p>
            <img :src="pkg.signature" class="w-full max-w-xs h-20 object-contain bg-white rounded-md border border-slate-200 dark:border-slate-700" alt="Assinatura" />
          </div>
          <div v-if="pkg.document_photo" class="mt-3">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Documento:</p>
            <img :src="pkg.document_photo" class="w-full max-w-xs h-32 object-cover rounded-md border border-slate-200 dark:border-slate-700" alt="Documento" />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
        <button 
          @click="$emit('close')" 
          class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-medium rounded-lg text-sm transition-colors"
        >
          Fechar
        </button>
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
