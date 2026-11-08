<template>
  <div class="space-y-2">
    <label class="block text-sm font-medium text-gray-300">
      {{ label }}
      <span class="text-xs text-gray-400 ml-1">(Clique para marcar/desmarcar)</span>
    </label>
    
    <!-- Campo de busca (apenas se houver muitas opções) -->
    <div v-if="options.length > 5" class="relative">
      <input
        v-model="searchTerm"
        type="text"
        :placeholder="getSearchPlaceholder()"
        class="w-full px-4 py-2 pl-10 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm"
      >
      <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>
    
    <!-- Botões de ação rápida (apenas se houver opções filtradas) -->
    <div v-if="filteredOptions.length > 1" class="flex gap-2 text-xs">
      <button 
        @click="selectAllFiltered"
        type="button"
        class="px-3 py-1 bg-indigo-600/20 text-indigo-300 rounded-full hover:bg-indigo-600/30 transition-colors border border-indigo-500/30"
        :disabled="allFilteredSelected"
        :class="{ 'opacity-50 cursor-not-allowed': allFilteredSelected }"
      >
        Selecionar {{ searchTerm ? 'encontradas' : 'todas' }}
      </button>
      <button 
        @click="deselectAllFiltered"
        type="button"
        class="px-3 py-1 bg-red-600/20 text-red-300 rounded-full hover:bg-red-600/30 transition-colors border border-red-500/30"
        :disabled="noFilteredSelected"
        :class="{ 'opacity-50 cursor-not-allowed': noFilteredSelected }"
      >
        Desmarcar {{ searchTerm ? 'encontradas' : 'todas' }}
      </button>
    </div>

    <!-- Container com scroll para as opções -->
    <div 
      class="w-full bg-gray-800 border rounded-lg transition-colors max-h-40 overflow-y-auto glass-scrollbar glass-scrollbar-purple"
      :class="{ 'border-red-500': hasError, 'border-gray-700': !hasError, 'focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500': !hasError }"
    >
      <div v-if="filteredOptions.length === 0" class="p-4 text-center text-gray-500 text-sm">
        {{ options.length === 0 ? 'Nenhuma opção disponível' : 'Nenhuma opção encontrada' }}
      </div>
      
      <div v-else class="divide-y divide-gray-700">
        <label 
          v-for="option in filteredOptions" 
          :key="option.id"
          class="flex items-center p-3 hover:bg-gray-700/50 cursor-pointer transition-colors select-none"
          :class="{ 'bg-indigo-600/20': isSelected(option.id) }"
        >
          <input
            type="checkbox"
            :value="option.id"
            :checked="isSelected(option.id)"
            @change="toggleOption(option.id)"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800 mr-3"
          >
          <span class="text-white text-sm flex-1">{{ option.name }}</span>
          <svg 
            v-if="isSelected(option.id)" 
            class="h-4 w-4 text-indigo-400 ml-2 flex-shrink-0" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </label>
      </div>
    </div>
    
    <!-- Seção de seleções ativas (mostra apenas se houver seleções) -->
    <div v-if="selectedCount > 0" class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-medium text-gray-300">Selecionadas:</span>
        <button 
          @click="clearAll"
          type="button"
          class="text-xs text-red-400 hover:text-red-300 underline transition-colors"
        >
          Limpar todas
        </button>
      </div>
      <div class="flex flex-wrap gap-2">
        <span 
          v-for="selectedOption in selectedOptions" 
          :key="selectedOption.id"
          class="inline-flex items-center px-2 py-1 bg-indigo-600/20 text-indigo-300 text-xs rounded-full border border-indigo-500/30"
        >
          {{ selectedOption.name }}
          <button 
            @click="toggleOption(selectedOption.id)"
            type="button"
            class="ml-1 hover:text-indigo-200 transition-colors"
          >
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </div>
    </div>
    
    <p class="text-xs text-gray-400">
      {{ selectedCount }} {{ countLabel }} selecionada(s)
    </p>
    <p v-if="errorMessage" class="mt-1 text-sm text-red-400">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const searchTerm = ref('');

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    required: true
  },
  name: {
    type: String,
    required: true
  },
  countLabel: {
    type: String,
    required: true
  },
  errorMessage: {
    type: String,
    default: ''
  },
  inputId: {
    type: String,
    required: false // Não é mais necessário
  }
});

const emit = defineEmits(['update:modelValue']);

const selectedCount = computed(() => props.modelValue.length);
const hasError = computed(() => !!props.errorMessage);

// Computed para filtrar opções baseado no termo de busca
const filteredOptions = computed(() => {
  if (!searchTerm.value.trim()) {
    return props.options;
  }
  
  const searchTerms = searchTerm.value.toLowerCase().split(' ').filter(term => term.length > 0);
  
  return props.options.filter(option => {
    const optionText = option.name.toLowerCase();
    
    // Se todos os termos de busca estiverem presentes no nome da opção
    return searchTerms.every(term => optionText.includes(term));
  });
});

// Computed para obter as opções selecionadas com seus nomes
const selectedOptions = computed(() => 
  props.options.filter(option => props.modelValue.includes(option.id))
);

// Função para verificar se uma opção está selecionada
function isSelected(optionId) {
  return props.modelValue.includes(optionId);
}

// Função para alternar uma opção (marcar/desmarcar)
function toggleOption(optionId) {
  let newValue;
  if (isSelected(optionId)) {
    // Remove da seleção
    newValue = props.modelValue.filter(id => id !== optionId);
  } else {
    // Adiciona à seleção
    newValue = [...props.modelValue, optionId];
  }
  emit('update:modelValue', newValue);
}

// Função para limpar todas as seleções
function clearAll() {
  emit('update:modelValue', []);
}

// Computed properties para os botões de ação rápida
const allFilteredSelected = computed(() => {
  return filteredOptions.value.every(option => props.modelValue.includes(option.id));
});

const noFilteredSelected = computed(() => {
  return !filteredOptions.value.some(option => props.modelValue.includes(option.id));
});

// Função para selecionar todas as opções filtradas
function selectAllFiltered() {
  const filteredIds = filteredOptions.value.map(option => option.id);
  const newValue = [...new Set([...props.modelValue, ...filteredIds])];
  emit('update:modelValue', newValue);
}

// Função para desselecionar todas as opções filtradas
function deselectAllFiltered() {
  const filteredIds = filteredOptions.value.map(option => option.id);
  const newValue = props.modelValue.filter(id => !filteredIds.includes(id));
  emit('update:modelValue', newValue);
}

// Função para gerar placeholder dinâmico baseado no tipo de dados
function getSearchPlaceholder() {
  const sampleNames = props.options.slice(0, 3).map(option => option.name);
  
  // Detectar se são unidades (contém "Unidade", "Torre", "Bloco")
  const isApartments = sampleNames.some(name => 
    name.includes('Unidade') || name.includes('Torre') || name.includes('Bloco')
  );
  
  if (isApartments) {
    return 'Ex: 101, Torre A, Bloco B...';
  }
  
  // Detectar se são torres (contém "Torre")
  const isTowers = sampleNames.some(name => name.includes('Torre'));
  if (isTowers) {
    return 'Ex: Torre A, Norte...';
  }
  
  // Detectar se são blocos (contém "Bloco")  
  const isBlocks = sampleNames.some(name => name.includes('Bloco'));
  if (isBlocks) {
    return 'Ex: Bloco A, Principal...';
  }
  
  return 'Buscar...';
}
</script> 