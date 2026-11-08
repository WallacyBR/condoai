<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-2xl p-6 w-full max-w-md mx-4 border-2 border-gray-500/30 shadow-2xl">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white flex items-center">
          <svg 
            class="h-6 w-6 mr-3" 
            :class="iconColorClass" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              :d="iconPath"
            />
          </svg>
          {{ title }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-white">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-6">
        <!-- Mensagem -->
        <div class="text-center">
          <p class="text-gray-300 text-lg">{{ message }}</p>
        </div>

        <!-- Botões -->
        <div class="flex space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="$emit('confirm')"
            class="flex-1 px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
            :class="confirmButtonClass"
            :disabled="isLoading"
          >
            <span v-if="isLoading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processando...
            </span>
            <span v-else>{{ confirmButtonText }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: { type: Boolean, required: true },
  title: { type: String, default: 'Confirmar Ação' },
  message: { type: String, required: true },
  confirmButtonText: { type: String, default: 'Confirmar' },
  confirmButtonVariant: { 
    type: String, 
    default: 'primary',
    validator: (value) => ['primary', 'danger', 'success', 'warning', 'info'].includes(value)
  },
  isLoading: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'confirm']);

const confirmButtonClass = computed(() => {
  const variants = {
    primary: 'bg-blue-600 text-white hover:bg-blue-500',
    danger: 'bg-red-600 text-white hover:bg-red-500',
    success: 'bg-green-600 text-white hover:bg-green-500',
    warning: 'bg-yellow-600 text-white hover:bg-yellow-500',
    info: 'bg-cyan-600 text-white hover:bg-cyan-500'
  };
  
  return variants[props.confirmButtonVariant] || variants.primary;
});

const iconColorClass = computed(() => {
  const variants = {
    primary: 'text-blue-400',
    danger: 'text-red-400',
    success: 'text-green-400',
    warning: 'text-yellow-400',
    info: 'text-cyan-400'
  };
  
  return variants[props.confirmButtonVariant] || variants.primary;
});

const iconPath = computed(() => {
  const variants = {
    primary: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    danger: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
  };
  
  return variants[props.confirmButtonVariant] || variants.primary;
});
</script> 