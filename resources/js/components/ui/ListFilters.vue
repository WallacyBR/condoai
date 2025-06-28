<template>
  <div class="space-y-4">
    <!-- Filtros -->
    <div class="flex space-x-4">
      <div class="relative">
        <input
          type="text"
          :value="modelValue.search"
          @input="updateFilter('search', $event.target.value)"
          :placeholder="searchPlaceholder"
          class="w-64 pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
      
      <select
        :value="modelValue.statusFilter"
        @change="updateFilter('statusFilter', $event.target.value)"
        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
      >
        <option value="all">Todos os Status</option>
        <option v-for="status in statusOptions" :key="status.value" :value="status.value">
          {{ status.label }}
        </option>
      </select>
      
      <select
        v-if="towerOptions.length > 0"
        :value="modelValue.towerFilter"
        @change="updateFilter('towerFilter', $event.target.value)"
        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
      >
        <option value="all">Todas as Torres</option>
        <option v-for="tower in towerOptions" :key="tower.id" :value="tower.id">
          {{ tower.name }}
        </option>
      </select>
      
      <select
        v-if="blockOptions.length > 0"
        :value="modelValue.blockFilter"
        @change="updateFilter('blockFilter', $event.target.value)"
        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
      >
        <option value="all">Todos os Blocos</option>
        <option v-for="block in blockOptions" :key="block.id" :value="block.id">
          {{ block.name }}
        </option>
      </select>
    </div>

    <!-- Status dos Filtros -->
    <div v-if="isFilterActive" class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
            </svg>
            <span class="text-indigo-300 font-medium">Filtros Ativos:</span>
          </div>
          <div class="flex space-x-2">
            <span v-if="modelValue.search" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
              Busca: "{{ modelValue.search }}"
            </span>
            <span v-if="modelValue.statusFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
              Status: {{ getStatusLabel(modelValue.statusFilter) }}
            </span>
            <span v-if="modelValue.towerFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
              Torre: {{ getTowerLabel(modelValue.towerFilter) }}
            </span>
            <span v-if="modelValue.blockFilter !== 'all'" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-sm rounded-full border border-indigo-500/30">
              Bloco: {{ getBlockLabel(modelValue.blockFilter) }}
            </span>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <div class="text-sm text-indigo-400">
            <slot name="summary"></slot>
          </div>
          <NeonButton 
            @click="clearFilters" 
            size="sm" 
            class="flex items-center bg-gray-600/40 hover:bg-gray-600/60"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar
          </NeonButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import NeonButton from '../NeonButton.vue';

export default {
  name: 'ListFilters',
  components: { NeonButton },
  props: {
    modelValue: {
      type: Object,
      required: true
    },
    searchPlaceholder: {
      type: String,
      default: 'Buscar...'
    },
    statusOptions: {
      type: Array,
      default: () => [
        { value: 'occupied', label: 'Ocupadas' },
        { value: 'vacant', label: 'Vazias' }
      ]
    },
    towerOptions: {
      type: Array,
      default: () => []
    },
    blockOptions: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:modelValue', 'clear-filters'],
  computed: {
    isFilterActive() {
      return this.modelValue.search || 
             this.modelValue.statusFilter !== 'all' || 
             this.modelValue.towerFilter !== 'all' || 
             this.modelValue.blockFilter !== 'all';
    }
  },
  methods: {
    updateFilter(key, value) {
      this.$emit('update:modelValue', {
        ...this.modelValue,
        [key]: value
      });
    },
    clearFilters() {
      this.$emit('clear-filters');
    },
    getStatusLabel(value) {
      const status = this.statusOptions.find(s => s.value === value);
      return status ? status.label : value;
    },
    getTowerLabel(value) {
      const tower = this.towerOptions.find(t => t.id === value);
      return tower ? tower.name : 'Todas as Torres';
    },
    getBlockLabel(value) {
      const block = this.blockOptions.find(b => b.id === value);
      return block ? block.name : 'Todos os Blocos';
    }
  }
};
</script> 